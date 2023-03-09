<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Exports\UserSelectedProductsExport;
use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;
use App\Models\Common\Product;

class UserSelectedProductsExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 600;
    public $tries = 2;
    protected $data;
    protected $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $user_id)
    {
        $this->data = $data;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $user_id = $this->user_id;
        try {
            $query = Product::with('ecom_warehouse_stock','productCategory','productSubCategory','sellingUnits','productType','productType2', 'productType3','def_or_last_supplier.getstate','def_or_last_supplier.getcountry','ecomSellingUnits')->whereIn('id',$data);
            $return=\Excel::store(new UserSelectedProductsExport($query,$data),'User-Selected-Products.xlsx');
            if($return)
            {
              ExportStatus::where('type','complete_products_excel_user_selected')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
              return response()->json(['msg'=>'File Saved']);
            }
        } catch (\Exception $e) {
            $this->failed($e);
        }
        catch(QueueMaxAttemptsExceededException $e) {
          $this->failed($e);
        }

    }
    public function failed( $exception)
    {
      ExportStatus::where('type','complete_products_excel_user_selected')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="Complete Products Export User Selected";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
