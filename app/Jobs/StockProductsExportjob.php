<?php

namespace App\Jobs;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use Excel;
use App\Exports\FilteredStockProductsExport;
use App\Variable;
use App\ExportStatus;
use App\FailedJobException;

class StockProductsExportjob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1; // Max tries
    public $timeout=500;
    protected $data,$name,$user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name,$data,$user_id)
    {
        $this->data=$data;
        $this->name=$name;
        $this->user_id=$user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $user_id=$this->user_id;
        $data=$this->data;
        $name=$this->name;
       try{
           
            $timestamp=time();
            // $last_downloaded=Carbon::parse($timestamp)->format('Y-m-d-H-i-s');
            $vairables=Variable::select('slug','standard_name','terminology')->get();
            $global_terminologies=[];
            foreach($vairables as $variable)
            {
                if($variable->terminology != null)
                {
                    $global_terminologies[$variable->slug]=$variable->terminology;
                }else{
                    $global_terminologies[$variable->slug]=$variable->standard_name;
                }
            }
            $return= \Excel::store(new FilteredStockProductsExport($data,$global_terminologies),'Stock-Adjustment-'.$user_id.'-'.$timestamp.'.xlsx');
            if($return)
            {
                $status=ExportStatus::where('type','stock_bulk_upload')->where('user_id',$this->user_id)->first();
                $status->status=0;
                $status->file_name=$timestamp;
                $status->save();
                // ExportStatus::where('type','stock_bulk_upload')->where('user_id',$this->user_id)->update(['status'=>0,'file_name'=>5]);
            }
       }
       catch(Exception $e) {
        $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
       
        ExportStatus::where('type','stock_bulk_upload')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Stock Bulk Upload";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
       
    }
}
