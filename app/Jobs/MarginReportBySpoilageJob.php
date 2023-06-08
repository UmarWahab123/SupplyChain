<?php

namespace App\Jobs;
use Carbon\Carbon;
use App\Models\Common\StockManagementOut;
use App\Exports\MarginReportBySpoilageExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;
use Exception;
use DB;
use App\ExportStatus;
use App\FailedJobException;
use App\Helpers\MarginReportHelper;

class MarginReportBySpoilageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $user_id)
    {
        $this->request=$request;
        $this->user_id=$user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $request = $this->request;
            $user_id = $this->user_id;
            $from_date = $request['from_date_exp'];
            $to_date = $request['to_date_exp'];
            $spoilageStock = MarginReportHelper::getSpoilageData($from_date,$to_date);
            $return=\Excel::store(new MarginReportBySpoilageExport($spoilageStock, $request), 'Margin-Report-By-Spoilage.xlsx');
            if($return)
            {
                ExportStatus::where('type','margin_report_by_spoilage')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
        }catch(Exception $e) {
            $this->failed($e);
        }catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }
    public function failed($exception)
    {
      ExportStatus::where('type','margin_report_by_spoilage')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="margin_report_by_spoilage";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
