<?php

namespace App\Jobs;
use DB;
use Artisan;
use App\User;
use Exception;
use App\Variable;
use Carbon\Carbon;
use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Bus\Queueable;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use App\SoldProductsReportRecord;
use MaxAttemptsExceededException;
use App\Exports\soldProductExport;
use App\Models\Common\Order\Order;
use App\FiltersForSoldProductReport;
use App\Models\Common\ProductCategory;
use App\Models\Common\TableHideColumn;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Configuration;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\StatusCheckForSoldProductsExport;
use App\Helpers\ProductConfigurationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;
use App\Helpers\ExportHelper;

class SoldProductsExportJob implements ShouldQueue
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
     * @return void */
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
          $query = OrderProduct::with('get_order','from_supplier','product.productType','get_order_product_notes','get_order.statuses','get_order.customer.getbilling','purchase_order_detail.PurchaseOrder','warehouse_products','product.product_fixed_price','get_order.user','from_warehouse','product.productCategory','product.productSubCategory','product.productType2')->select('order_products.product_id','order_products.order_id','order_products.supplier_id','order_products.from_warehouse_id','order_products.id','order_products.vat','order_products.total_price','order_products.unit_price','order_products.qty_shipped','order_products.pcs_shipped','order_products.number_of_pieces','order_products.total_price_with_vat','order_products.quantity','order_products.created_at','order_products.actual_cost','order_products.vat_amount_total','order_products.type_id','order_products.discount','order_products.user_warehouse_id','order_products.status','order_products.remarks')->whereNotNull('order_products.product_id')->whereHas('get_order',function($q){
            $q->whereIn('primary_status',[2,3,37])->where('dont_show',0);
          });
        //   getting customers against current sale person
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

          if($request['warehouse_id_exp'] != null)
          {
            if($request['draft'] != null)
            {
              $w_id = $request['warehouse_id_exp'];
              $query = $query->where(function($p) use($w_id){
                $p->where('from_warehouse_id',$w_id)->orWhere(function($z) use($w_id){
                  $z->whereNull('from_warehouse_id')->whereHas('get_order',function($y) use($w_id){
                    $y->whereHas('user_created',function($x) use($w_id){
                      $x->where('warehouse_id',$w_id);
                    });
                  });
                });
              });
            }
            else
            {
              if($role_id == 9 && $request['warehouse_id_exp'] == 1)
              {
                $w_id = $request['warehouse_id_exp'];
                if($request['product_id_exp'] != '')
                {

                  $query = $query->where('order_products.product_id' , $request['product_id_exp']);
                  $query = $query->whereHas('get_order',function($q){
                    $q->where('ecommerce_order',1);
                  });
                }
                else
                {

                  $query = $query->where(function($p) use($w_id){
                    $p->where('from_warehouse_id',$w_id)->orWhere(function($z) use($w_id){
                      $z->whereNull('from_warehouse_id')->whereHas('get_order',function($y) use($w_id){
                        $y->whereHas('user_created',function($x) use($w_id){
                          $x->where('warehouse_id',$w_id);
                        });
                      });
                    });
                  });
                  $query = $query->whereHas('get_order' ,function($q){
                    $q->where('ecommerce_order', 1);
                  });
                }
              }
              else
              {
                $w_id = $request['warehouse_id_exp'];
                $query = $query->where(function($p) use($w_id){
                  $p->where('from_warehouse_id',$w_id)->orWhere(function($z) use($w_id){
                    $z->whereNull('from_warehouse_id')->whereHas('get_order',function($y) use($w_id){
                      $y->whereHas('user_created',function($x) use($w_id){
                        $x->where('warehouse_id',$w_id);
                      });
                    });
                  });
                });
              }
            }
          }

          if($request['c_ty_id_exp'] != "null" && $request['c_ty_id_exp'] != null)
          {
            $getCustByCat = Order::with('customer')->whereHas('customer', function($q) use($request){
              $q->whereHas('CustomerCategory', function($q1) use($request) {
                $q1->where('customer_categories.id',$request['c_ty_id_exp']);
              });
            })->where('primary_status',3)->pluck('id');
            $query = $query->whereIn('order_products.order_id', $getCustByCat );
          }

          if($request['saleid_exp'] != "null" && $request['saleid_exp'] != null)
          {
            // $getOrderBySale = Order::with('customer')->whereHas('customer', function($q) use($request){
            //   $q->whereHas('primary_sale_person', function($q1) use($request) {
            //     $q1->where('customers.primary_sale_id',$request['saleid_exp']);
            //     // $q1->orWhere('customers.secondary_sale_id',$request['saleid_exp']);
            //   });
            // })->where('primary_status',3)->pluck('id');
            // $query = $query->whereIn('order_products.order_id', $getOrderBySale );
            $user = User::find($request['saleid_exp']);
            if($user)
            {
              $primaryCustomers = $user->customersByPrimarySalePerson   ? $user->customersByPrimarySalePerson()->pluck('id')->toArray() : [];
              $secondaryCustomers=$user->customersBySecondarySalePerson ? $user->customersBySecondarySalePerson()->pluck('customer_id')->toArray():[];
            }
            else
            {
                $primaryCustomers = [];
                $secondaryCustomers = [];
            }
            $query = $query->whereHas('get_order',function($z) use ($request, $customersRelatedToSalesPerson){
                // $z->where('user_id',$request['saleid_exp'])->where('primary_status',3);
                $z->where(function($z) use ($request, $customersRelatedToSalesPerson){
                  $z->where('user_id',$request['saleid_exp'])->orWhereIn('customer_id',$customersRelatedToSalesPerson);
                })->where('primary_status',3);
            });
          }
          else if($role_id == 3)
          {

            $query = $query->whereHas('get_order',function($z) use ($user_id,$customersRelatedToSalesPerson){
                $z->where(function($op) use ($user_id,$customersRelatedToSalesPerson){
                    $op->where('user_id',$user_id)->orWhereIn('customer_id',$customersRelatedToSalesPerson);
                });
                });
          }

          if($request['sale_person_id_exp'] != "null" && $request['sale_person_id_exp'] != null)
          {
            $user_primary_customers = Customer::where('primary_sale_id',$request['sale_person_id_exp'])->pluck('id')->toArray();
            $query = $query->whereHas('get_order',function($z) use ($request, $user_primary_customers){
                $z->where('user_id',$request['sale_person_id_exp'])->orWhereIn('customer_id', $user_primary_customers);
            });

            // $query = $query->whereHas('get_order',function($z) use ($request){
            //   $z->where('user_id',$request['sale_person_id_exp']);
            // });
          }
          else if($role_id == 3)
          {

            $query = $query->whereHas('get_order',function($z) use ($user_id,$customersRelatedToSalesPerson, $customer_ids){
                $z->where(function($op) use ($user_id,$customersRelatedToSalesPerson, $customer_ids){
                    $op->where('user_id',$user_id)->orWhereIn('customer_id',$customersRelatedToSalesPerson)->orWhereIn('customer_id', $customer_ids);
                });
              });
          }

          if($request['customer_id_exp'] != null)
          {
            // $query = $query->whereIn('order_products.order_id', Order::where('customer_id',$request['customer_id_exp'])->pluck('id') );
             $str = $request['customer_id_exp'];
             $split= (explode("-",$str));
         if($split[0]=='cus')
         {
           $customer_id = $split[1];
           $query = $query->whereIn('order_products.order_id', Order::where('customer_id',$customer_id)->pluck('id') );
         }else{
           $cat_id = $split[1];
           $query = $query->whereIn('order_products.order_id', Order::with('customer')->whereHas('customer',function($q)use($cat_id){
            $q->where('category_id',$cat_id);
           })->pluck('id'));
         }

          }
          $config = Configuration::first();
          if($request['order_type_exp'] != null)
          {
            if($request['order_type_exp'] == 0 || $request['order_type_exp'] == 1)
            {
              $query = $query->whereHas('get_order',function($or) use ($request){
                $or->where('primary_status',3)->where('is_vat',$request['order_type_exp']);
              });
            }
            elseif($request['order_type_exp'] == 10)
            {
              // $query = $query->whereIn('order_products.order_id', Order::where('primary_status',2)->pluck('id') );
              $query = $query->whereHas('get_order',function($or){
                $or->where('primary_status',2);
              });
            }
            elseif($request['order_type_exp'] == 38)
            {
              $query = $query->whereHas('get_order',function($or){
                $or->whereIn('primary_status',[3,37]);
              });
            }
            else
            {
              // $query = $query->whereIn('order_products.order_id', Order::where('primary_status',$request['order_type_exp'])->pluck('id') );
              $query = $query->whereHas('get_order',function($or) use ($request,$config){
                $or->where('primary_status',$request['order_type_exp']);
                if(@$config->server == 'lucilla'){
                  $or->where('is_vat', 0);
                }
              });
            }
          }

          if($request['product_id_exp'] != '')
          {
            $query = $query->where('order_products.product_id' , $request['product_id_exp']);
          }

          if($request['p_c_id_exp'] != "null" && $request['p_c_id_exp'] != null)
          {
            $p_cat_id = ProductCategory::select('id','parent_id')->where('parent_id',$request['p_c_id_exp'])->pluck('id')->toArray();
            $product_ids = Product::select('id','category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereIn('order_products.product_id',$product_ids);
          }
          else
          {
            // if($request['product_id_select'] != null)
            // {
            //   $id_split = explode('-', $request['product_id_select']);
            //   $id_split = (int)$id_split[1];
            //   if($request['className'] == 'parent')
            //   {
            //     $p_cat_ids = Product::select('id','primary_category','status')->where('primary_category', $id_split)->where('status',1)->pluck('id');
            //     $query = $query->whereIn('order_products.product_id',$p_cat_ids);
            //   }
            //   else if ($request['className'] == 'child') {
            //     $product_ids = Product::select('id','category_id','status')->where('category_id',$id_split)->where('status',1)->pluck('id');
            //     $query = $query->whereIn('order_products.product_id',$product_ids);
            //   }
            //   else
            //   {
            //     $query = $query->where('order_products.product_id' , $id_split);
            //   }

            //   // $filter_sub_categories = ProductCategory::where('parent_id','!=',0)->where('title',$request['prod_sub_category_exp'])->pluck('id')->toArray();

            //   // $product_ids = Product::select('id','category_id','status')->whereIn('category_id',$filter_sub_categories)->where('status',1)->pluck('id');
            //   // $query = $query->whereIn('order_products.product_id',$product_ids);
            // }
            if($request["prod_category_exp"] != null)
            {
              $cat_id_split = explode('-',$request["prod_category_exp"]);
              // dd($cat_id_split);
              if($cat_id_split[0] == 'sub')
              {
                // $filter_sub_categories = ProductCategory::where('parent_id','!=',0)->where('title',$request->prod_sub_category)->pluck('id')->toArray();
                $product_ids = Product::select('id','category_id','status')->where('category_id', $cat_id_split[1])->where('status',1)->pluck('id');
                $query = $query->whereIn('order_products.product_id',$product_ids);
              }
              else
              {

                $p_cat_ids = Product::select('id','primary_category','status')->where('primary_category', $cat_id_split[1])->where('status',1)->pluck('id');
                $query = $query->whereIn('order_products.product_id',$p_cat_ids);
              }
            }
          }

          if($request['supplier_id_exp'] != null)
          {
            $products_ids = SupplierProducts::select('id','supplier_id','is_deleted','product_id')->where('supplier_id',$request['supplier_id_exp'])->where('is_deleted',0)->pluck('product_id');
            $query = $query->whereIn('order_products.product_id',$products_ids);
          }

          if($request['filter_exp'] != null)
          {
            if($request['filter_exp'] == 'stock')
            {
              $query = $query->whereIn('order_products.product_id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request['filter_exp'] == 'reorder')
            {
              $product_ids = Product::select('id','status','min_stock')->where('min_stock','>',0)->where('status',1)->pluck('id');
              $query = $query->whereIn('order_products.product_id',$product_ids);
            }
            elseif ($request['filter_exp'] == 'dicount_items')
            {
              $query = $query->where('order_products.discount','>',0);
            }
          }
          if($request['product_type_exp'] != null)
          {
            $query = $query->whereHas('product',function($p) use ($request){
              $p->where('type_id',$request['product_type_exp']);
            });
          }

          if($request['product_type_2_exp'] != null)
          {
            $query = $query->whereHas('product',function($p) use ($request){
              $p->where('type_id_2',$request['product_type_2_exp']);
            });
          }

          if($request['product_type_3_exp'] != null)
          {
            $query = $query->whereHas('product',function($p) use ($request){
              $p->where('type_id_3',$request['product_type_3_exp']);
            });
          }

          if($request['from_date_exp'] != null)
          {
            // if($request['draft'] == null)
            // {
              $date = str_replace("/","-",$request['from_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              if($request['date_radio_exp'] == 2)
              {
                $query->whereHas('get_order' ,function($q) use ($date){
                  $q->where('converted_to_invoice_on', '>=', $date.' 00:00:00');
              });
              }
              else if($request['date_radio_exp'] == 1)
              {
                $query->whereHas('get_order' ,function($q) use ($date){
                  $q->where('delivery_request_date', '>=', $date);
                });
              }
              else
              {
                $query->whereHas('get_order' ,function($q) use ($date){
                  $q->where('target_ship_date', '>=', $date);
                });
              }
            // }
          }

          if($request['to_date_exp'] != null)
          {
            // if($request['draft'] == null)
            // {
              $date = str_replace("/","-",$request['to_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              if($request['date_radio_exp'] == 2)
              {
                $query->whereHas('get_order' ,function($q) use ($date){
                  $q->where('converted_to_invoice_on', '<=', $date.' 23:59:59');
                });
              }
              else if($request['date_radio_exp'] == 1)
              {
                $query->whereHas('get_order' ,function($q) use ($date){
                  $q->where('delivery_request_date', '<=', $date);
                });
              }
              else
              {
                $query->whereHas('get_order' ,function($q) use ($date){
                  $q->where('target_ship_date', '<=', $date);
                });
              }
            // }
          }
          $query = OrderProduct::doSortby($request, $query);
          /***********/
        $current_date = date("Y-m-d");
        //to find the manual adjustments which effect the COGS
        $new_request = new \Illuminate\Http\Request();
        $new_request->replace(['warehouse_id' => $request['warehouse_id_exp'], 'product_id' => $request['product_id_exp'],'p_c_id' => $request['p_c_id_exp'],'prod_category' => $request["prod_category_exp"], 'from_date' => $request['from_date_exp'],'to_date' => $request['to_date_exp']]);
        // dd($request);
        // $stock = (new StockManagementOut)->get_manual_adjustments($new_request);
        $stock = null;
        $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
        // $return=\Excel::store(new soldProductExport($query,$global_terminologies,$not_visible_arr,$available_stock,$getCategories,$role_id,$stock, $request, $product_detail_section),'Sold-Products-Report.xlsx');
        $return = ExportHelper::prepareExcel($query,$global_terminologies,$not_visible_arr,$available_stock,$getCategories,$role_id,$stock, $request, $product_detail_section);
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
