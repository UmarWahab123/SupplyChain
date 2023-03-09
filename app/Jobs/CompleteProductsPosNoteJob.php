<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Exports\completeProductPosNotesExport;
use App\Models\Common\StockManagementIn;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\SupplierProducts;
use App\Models\Common\ProductCategory;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Product;


class CompleteProductsPosNoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1500;
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
        try {
            $request = $this->data;
            $user_id = $this->user_id;

            $filter_products = Product::where('status','=',1);

            if ($request['default_supplier_exp'] != '') {
                $fileName = "Filtered";
                $supplier_query = $request['default_supplier_exp'];
                $filter_products = $filter_products->whereIn('products.id', SupplierProducts::select('product_id')->where('is_deleted', 0)->where('supplier_id', $supplier_query)->pluck('product_id'));
            }

            if ($request['prod_type_exp'] != '') {
                $fileName = "Filtered";
                $filter_products->where('products.type_id', $request['prod_type_exp'])->where('products.status', 1);
            }
            if ($request['prod_type_2_exp'] != '') {
                $fileName = "Filtered";
                $filter_products->where('products.type_id_2', $request['prod_type_2_exp'])->where('products.status', 1);
            }

            if ($request['prod_category_primary_exp'] != '') {
                $fileName = "Filtered";
                $id_split = explode('-', $request["prod_category_primary_exp"]);
                if ($id_split[0] == 'pri') {
                    $filter_products->where('products.primary_category', $id_split[1])->where('products.status', 1);
                } else {
                    $filter_products->whereIn('products.category_id', ProductCategory::select('id')->where('id', $id_split[1])->where('parent_id', '!=', 0)->pluck('id'))->where('products.status', 1);
                }
            }

            if ($request['filter-dropdown_exp'] != '') {
                $fileName = "Filtered";
                if ($request['filter-dropdown_exp'] == 'stock') {
                    $filter_products = $filter_products->whereIn('products.id', WarehouseProduct::select('product_id')->where('current_quantity', '>', 0.005)->pluck('product_id'));
                } elseif ($request['filter-dropdown_exp'] == 'reorder') {
                    $filter_products->where('products.min_stock', '>', 0);
                }
            }

            $filter_products = $filter_products->pluck('id')->toArray();

            $query = StockManagementIn::select('stock_management_ins.id', 'stock_management_ins.title', 'stock_management_ins.product_id', 'stock_management_ins.expiration_date');
            $query =  $query->with('stock_out_available', 'product')->whereIn('product_id', $filter_products);

            $return = \Excel::store(new completeProductPosNotesExport($query), 'Pos-notes-export.xlsx');

            if ($return) {
                ExportStatus::where('type', 'complete_products')->update(['status' => 0, 'last_downloaded' => date('Y-m-d H:i:s')]);
                return response()->json(['msg' => 'File Saved']);
            }
        } catch (Exception $e) {
            $this->failed($e);
        } catch (MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
        ExportStatus::where('type', 'complete_products')->update(['status' => 2, 'exception' => $exception->getMessage()]);
        $failedJobException = new FailedJobException();
        $failedJobException->type = "Complete Products Notes Export";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
