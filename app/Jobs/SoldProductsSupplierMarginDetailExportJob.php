<?php

namespace App\Jobs;
use App\ExportStatus;
use App\Exports\SoldProductExportSupplierMargin;
use App\FailedJobException;
use App\FiltersForSoldProductReport;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockOutHistory;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\SoldProductsReportRecord;
use App\StatusCheckForSoldProductsExport;
use App\User;
use App\Variable;
use Artisan;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use MaxAttemptsExceededException;

class SoldProductsSupplierMarginDetailExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    protected $role_id;
    protected $customer_ids;
    public $tries=1;
    public $timeout=3600;
    public $available_stock;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id,$customer_ids)
    {
      $this->request = $data;
      $this->user_id = $user_id;
      $this->role_id = $role_id;
      $this->customer_ids = $customer_ids;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        // dd($request);
        $user_id = $this->user_id;
        $role_id = $this->role_id;
        $customer_ids = $this->customer_ids;
        try {
          $available_stock = $request['available_stock_val'];
          $vairables       = Variable::select('slug','standard_name','terminology')->get();
          $getCategories   = CustomerCategory::where('is_deleted',0)->where('show',1)->get();

          $global_terminologies = [];
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

          $not_visible_arr=[];
          $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'sold_product_report')->where('user_id',$user_id)->first();

          if($not_visible_columns!=null)
          {
            $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
          }
          $query = StockOutHistory::whereNotNull('stock_out_histories.order_id')->where('stock_out_histories.supplier_id',$request['from_supplier_margin_id'])->select('stock_out_histories.*')->with('get_order.customer','get_order.user','purchase_order_detail.PurchaseOrder','get_order_product.from_supplier','get_order_product.from_warehouse','get_order_product.product.productCategory','get_order_product.product.productSubCategory','get_order_product.product.productType','get_order_product.product.productType2', 'get_order_product.product.productType3','get_order_product.warehouse_products','get_order_product.product.sellingUnits')->whereHas('get_order',function($q){
            $q->where('dont_show',0);
          });

          if($request['saleid_exp'] != "null" && $request['saleid_exp'] != null)
          {
            $user_primary_customers = Customer::where('primary_sale_id',$request['saleid_exp'])->pluck('id')->toArray();
            $query = $query->whereHas('get_order',function($z) use ($request, $user_primary_customers){
                $z->where('user_id',$request['saleid_exp'])->orWhereIn('customer_id', $user_primary_customers);
            });
          }
          else if($role_id == 3)
          {
            if($role_id == 3)
            {
              $user = User::find($user_id);
              $primaryCustomers= $user->customersByPrimarySalePerson ? $user->customersByPrimarySalePerson()->pluck('id')->toArray() :[];
              $secondaryCustomers=$user->customersBySecondarySalePerson ? $user->customersBySecondarySalePerson()->pluck('customer_id')->toArray() :[];
            }
            else
            {
              $primaryCustomers = [];
              $secondaryCustomers = [];
            }

          $customersRelatedToSalesPerson=array_merge($primaryCustomers,$secondaryCustomers);

            $user_i = $role_id;
            $query = $query->whereHas('get_order',function($z) use ($user_i,$customersRelatedToSalesPerson){
                $z->where(function($op) use ($user_i,$customersRelatedToSalesPerson){
                    $op->where('user_id',$user_i)->orWhereIn('customer_id',$customersRelatedToSalesPerson)->orWhereIn('customer_id', Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                });
              });
          }
          if($request['product_type_exp'] != null)
          {
            $query = $query->whereHas('get_order_product',function($p) use ($request){
              $p->whereHas('product',function($pr) use ($request){
                $pr->where('type_id',$request['product_type_exp']);
              });

            });
          }
          if($request['product_type_2_exp'] != null)
          {
            $query = $query->whereHas('get_order_product',function($p) use ($request){
              $p->whereHas('product',function($pr) use ($request){
                $pr->where('type_id_2',$request['product_type_2_exp']);
              });

            });
          }
          if($request['product_type_3_exp'] != null)
          {
            $query = $query->whereHas('get_order_product',function($p) use ($request){
              $p->whereHas('product',function($pr) use ($request){
                $pr->where('type_id_3',$request['product_type_3_exp']);
              });

            });
          }
          if($request['product_id_exp'] != '')
          {
            $query = $query->whereHas('get_order_product',function($p) use ($request){
              $p->where('product_id',$request['product_id_exp']);
            });
          }

          if($request['customer_id_exp'] != null)
          {
            $str = $request['customer_id_exp'];
             $split= (explode("-",$str));
             if($split[0]=='cus'){
              $customer_id = $split[1];
              $customer = Customer::find($customer_id);
              if($customer != null && @$customer->manual_customer == 1)
              {
                $query = $query->whereHas('get_order',function($z)use($customer_id){
                  $z->where('customer_id',$customer_id);
                });
              }
              else
              {
                $query = $query->whereHas('get_order',function($z)use($customer_id){
                  $z->where('customer_id',$customer_id);
                });
              }
             }else{
               $cat_id = $split[1];
               $query = $query->whereHas('get_order',function($z)use($cat_id){
                $z->whereHas('customer',function($cust)use($cat_id){
                  $cust->where('category_id',$cat_id);
                });
              });
             }
          }

          if($request['p_c_id_exp'] != "null" && $request['p_c_id_exp'] != null)
          {
            $p_cat_id = ProductCategory::select('id','parent_id')->where('parent_id',$request['p_c_id_exp'])->pluck('id')->toArray();
            $product_ids = Product::select('id','category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereHas('get_order_product',function($op) use ($product_ids){
              $op->whereIn('product_id',$product_ids);
            });
          }
          else
          {
            if($request['prod_category_exp'] != null)
            {
              $cat_id_split = explode('-',$request['prod_category_exp']);
              if($cat_id_split[0] == 'sub')
              {
                $product_ids = Product::select('id','category_id','status')->where('category_id', $cat_id_split[1])->where('status',1)->pluck('id');
                $query = $query->whereHas('get_order_product',function($op) use ($product_ids){
                  $op->whereIn('product_id',$product_ids);
                });
              }
              else
              {
                $p_cat_ids = Product::select('id','primary_category','status')->where('primary_category', $cat_id_split[1])->where('status',1)->pluck('id');
                $query = $query->whereHas('get_order_product',function($op) use ($p_cat_ids){
                  $op->whereIn('order_products.product_id',$p_cat_ids);
                });
              }
            }
          }

          if($request['from_date_exp'] != null)
          {
              $date = str_replace("/","-",$request['from_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              $query->where('stock_out_histories.created_at','>=',$date.' 00:00:00');
              // if($request->date_type == 2)
              // {
              //   $query->whereHas('get_order' ,function($q) use ($date){
              //     $q->where('converted_to_invoice_on', '>=', $date.' 00:00:00');
              //   });
              // }
              // else
              // {
              //   $query->whereHas('get_order' ,function($q) use ($date){
              //     $q->where('delivery_request_date', '>=', $date);
              //   });
              // }
          }
          if($request['to_date_exp'] != null)
          {
              $date = str_replace("/","-",$request['to_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              $query->where('stock_out_histories.created_at','<=',$date.' 23:59:59');

              // if($request->date_type == 2)
              // {
              //   $query->whereHas('get_order' ,function($q) use ($date){
              //     $q->where('converted_to_invoice_on', '<=', $date.' 23:59:59');
              //   });
              // }
              // else
              // {
              //   $query->whereHas('get_order' ,function($q) use ($date){
              //     $q->where('delivery_request_date', '<=', $date);
              //   });
              // }
          }
          // dd($query);
          $query = StockOutHistory::doSortby($request, $query);
          /***********/
        $current_date = date("Y-m-d");

        $stock = null;
        $return=\Excel::store(new SoldProductExportSupplierMargin($query,$global_terminologies,$not_visible_arr,$available_stock,$getCategories,$role_id,$stock, $request),'Sold-Products-Report.xlsx');
        if($return)
        {
          ExportStatus::where('type','sold_product_report')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
          return response()->json(['msg'=>'File Saved']);
        }

        }
        catch(Exception $e) {
          $this->failed($e);
        }
        catch(QueueMaxAttemptsExceededException $e) {
          $this->failed($e);
        }
    }
    public function failed( $exception)
    {
      ExportStatus::where('type','sold_product_report')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="Complete Products Export";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
