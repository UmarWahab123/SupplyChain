<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\FailedJobException;
use App\Exports\SupplierAllProductsExport;
use App\ExportStatus;
use App\Variable;
// use App\Models\Common\Supplier;

class AddBulkProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $suppliers;
    protected $primary_category;
    protected $sub_category;
    protected $type;
    protected $type_2;
    protected $type_3;
    public $tries=1;
    public $timeout=1500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($suppliers, $primary_category, $sub_category, $type, $type_2, $type_3, $user_id)
    {
         $this->user_id = $user_id;
         $this->suppliers = $suppliers;
         $this->primary_category = $primary_category;
         $this->sub_category = $sub_category;
         $this->type = $type;
         $this->type_2 = $type_2;
         $this->type_3 = $type_3;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_id = $this->user_id;
        $suppliers = $this->suppliers;
        $primary_category = $this->primary_category;
        $sub_category = $this->sub_category;
        $type = $this->type;
        $type_2 = $this->type_2;
        $type_3 = $this->type_3;
         try{

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
            $return = \Excel::store(new SupplierAllProductsExport($suppliers,$primary_category, $sub_category, $type, $type_2, $type_3, $global_terminologies), 'bulk_products_export.xlsx');

            if($return)
            {
                ExportStatus::where('user_id',$user_id)->where('type','bulk_product_export')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
        }
        catch(Exception $e) {
            $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed( $exception)
    {
        ExportStatus::where('type','bulk_product_export')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Complete Products Export";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
