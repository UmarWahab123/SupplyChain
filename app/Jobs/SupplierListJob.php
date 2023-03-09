<?php

namespace App\Jobs;

use App\Exports\SupplierListExport;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Supplier;
use App\Models\Common\TableHideColumn;
use App\Variable;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;

class SupplierListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request = null;
    protected $user = null;
    public $timeout = 1800;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $request = $this->request;
            $user = $this->user;
            $query = Supplier::query();
            $query->with('getcountry:id,name', 'getstate:id,name', 'supplier_po:id,supplier_id,confirm_date', 'getnotes:id,supplier_id,note_description')->select('suppliers.*');

            if ($request['supplier_status'] != '') {
                $query->where('suppliers.status', $request['supplier_status']);
            }

            $vairables=Variable::select('slug','standard_name','terminology')->get();
            $global_terminologies=[];
            foreach($vairables as $variable)
            {
              if($variable->terminology != null)
              {
                $global_terminologies[$variable->slug]=$variable->terminology;
              }
              else
              {
                $global_terminologies[$variable->slug]=$variable->standard_name;
              }
            }
            $not_visible_arr = [];
            $not_visible_columns = TableHideColumn::where('user_id', $user->id)->where('type', 'supplier_list')->first();
            if($not_visible_columns!=null)
            {
                $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
            }
            $query = Supplier::SupplierListSorting($request, $query);
            $return=\Excel::store(new SupplierListExport($query, $global_terminologies, $request, $not_visible_arr), 'Supplier-list-export.xlsx');
            $job_status = ExportStatus::where('type', 'supplier_list_export_job')->where('user_id', $user->id)->first();
            $job_status->status = 0;
            $job_status->exception = null;
            $job_status->error_msgs = null;
            $job_status->save();
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
      ExportStatus::where('type','supplier_list_export_job')->where('user_id', $this->user->id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="supplier_list_export_job";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
