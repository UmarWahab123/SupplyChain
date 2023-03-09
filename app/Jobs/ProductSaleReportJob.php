<?php

namespace App\Jobs;

use DB;
use Auth;
use App\User;
use Exception;
use App\Variable;
use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use App\Models\Common\Warehouse;
use App\Models\Common\ProductCategory;
use App\Models\Common\TableHideColumn;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\CustomerCategory;
use Illuminate\Queue\InteractsWithQueue;
use App\Exports\ProductSalesReportExport;
use App\Helpers\ProductConfigurationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;

class ProductSaleReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $role_id;
    protected $supplier_id_exp;
    protected $order_type_exp;
    protected $customer_group_id_exp;
    protected $sales_person_exp;
    // protected $customer_id_exp;
    protected $product_id_exp;
    protected $category_id_exp;
    // protected $sub_category_id_exp;
    protected $from_date_exp;
    protected $to_date_exp;
    protected $date_type_exp;
    public $tries=1;
    public $timeout=1500;
    protected $customer_id_select;
    protected $product_id_select;
    protected $className;
    protected $productClassName;
    protected $product_type;
    protected $product_type_2;
    protected $product_type_3;
    protected $sortbyparam;
    protected $sortbyvalue;
    protected $warehouse_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($order_type_exp,$supplier_id_exp,$sales_person_exp,$from_date_exp,$to_date_exp,$date_type_exp, $user_id, $role_id, $customer_id_select, $product_id_select, $className, $productClassName,$product_id_exp,$category_id_exp,$customer_group_id_exp,$product_type,$product_type_2, $product_type_3, $sortbyparam, $sortbyvalue,$warehouse_id)
    {
      $this->user_id = $user_id;
      $this->role_id = $role_id;
      $this->supplier_id_exp = $supplier_id_exp;
      $this->order_type_exp = $order_type_exp;
      $this->sales_person_exp = $sales_person_exp;
      $this->from_date_exp = $from_date_exp;
      $this->to_date_exp = $to_date_exp;
      $this->date_type_exp = $date_type_exp;
      $this->customer_id_select = $customer_id_select;
      $this->product_id_select = $product_id_select;
      $this->className = $className;
      $this->productClassName = $productClassName;
      $this->product_id_exp = $product_id_exp;
      $this->category_id_exp = $category_id_exp;
      $this->customer_group_id_exp = $customer_group_id_exp;
      $this->product_type = $product_type;
      $this->product_type_2 = $product_type_2;
      $this->product_type_3 = $product_type_3;
      $this->sortbyparam = $sortbyparam;
      $this->sortbyvalue = $sortbyvalue;
      $this->warehouse_id = $warehouse_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{

        $getCategories = CustomerCategory::where('is_deleted',0)->where('show',1)->get();
        $getWarehouses = Warehouse::where('status',1)->get();
        $vairables=Variable::select('slug','standard_name','terminology')->get();
        $global_terminologies=[];
        foreach($vairables as $variable)
        {
          if($variable->terminology != null)
          {
            $global_terminologies[$variable->slug] = $variable->terminology;
          }
          else
          {
            $global_terminologies[$variable->slug] = $variable->standard_name;
          }
        }

        $user_id               = $this->user_id;
        $role_id               = $this->role_id;
        $supplier_id_exp       = $this->supplier_id_exp;
        $order_type_exp        = $this->order_type_exp;
        $customer_group_id_exp = $this->customer_group_id_exp;
        $sales_person_exp      = $this->sales_person_exp;
        // $customer_id_exp       = $this->customer_id_exp;
        $product_id_exp        = $this->product_id_exp;
        $category_id_exp       = $this->category_id_exp;
        // $sub_category_id_exp   = $this->sub_category_id_exp;
        $from_date_exp         = $this->from_date_exp;
        $to_date_exp           = $this->to_date_exp;
        $date_type_exp         = $this->date_type_exp;
        $customer_id_select    = $this->customer_id_select;
        $product_id_select     = $this->product_id_select;
        $className             = $this->className;
        $productClassName      = $this->productClassName;
        $product_type      = $this->product_type;
        $product_type_2      = $this->product_type_2;
        $product_type_3      = $this->product_type_3;
        $warehouse_id      = $this->warehouse_id;

        $products = Product::select(DB::raw('SUM(CASE
        WHEN o.primary_status="2" THEN op.quantity
        WHEN o.primary_status="3" THEN op.qty_shipped
        END) AS QuantityText,
        SUM(CASE
        WHEN o.primary_status="2" THEN op.number_of_pieces
        WHEN o.primary_status="3" THEN op.pcs_shipped
        END) AS PiecesText,
        SUM(CASE
        WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat
        END) AS TotalAmount,
        SUM(CASE
        WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.vat_amount_total
        END) AS VatTotalAmount,
        SUM(CASE
        WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price
        END) AS totalPriceSub,
        SUM(CASE
        WHEN (o.primary_status="3") THEN ((op.actual_cost * op.qty_shipped))
        END) AS totalCogs,
        (CASE
        WHEN (o.primary_status="2" OR o.primary_status="3") THEN (SUM(op.locked_actual_cost)/COUNT(op.id))
        END) AS TotalAverage,
        SUM(CASE
        WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat END)/SUM(CASE
        WHEN o.primary_status="2" THEN op.quantity
        WHEN o.primary_status="3" THEN op.qty_shipped
        END) AS avg_unit_price'),'products.refrence_code','products.selling_unit','products.short_desc','op.product_id','op.vat_amount_total','op.total_price','products.id','products.category_id','products.primary_category','products.brand','products.ecommerce_enabled','o.primary_status','o.created_by','o.dont_show','o.user_id','products.type_id','products.selling_price','products.type_id_2','products.type_id_3','o.customer_id')->whereIn('o.primary_status',[2,3])->groupBy('op.product_id');
        $products->join('order_products AS op','op.product_id','=','products.id');
        $products->join('orders AS o','o.id','=','op.order_id');

        if($supplier_id_exp != null)
        {
          $products = $products->where('products.supplier_id',$supplier_id_exp);
        }

        if($order_type_exp != null)
        {
          $products = $products->where('o.primary_status',$order_type_exp);
        }

        if($product_id_exp != null)
        {
          $products = $products->where('products.id',$product_id_exp);
        }

        if($from_date_exp != null)
        {
          $from_date = str_replace("/","-",$from_date_exp);
          $from_date =  date('Y-m-d',strtotime($from_date));
          if($date_type_exp == '2')
          {
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date.' 00:00:00');
          }
          if($date_type_exp == '1')
          {
            $products = $products->where('o.delivery_request_date', '>=', $from_date);
          }
        }

        if($category_id_exp != null)
        {
          $cat_id_split = explode('-',$category_id_exp);
          // dd($cat_id_split);
          if($cat_id_split[0] == 'sub')
          {
            $products = $products->where('products.category_id',$cat_id_split[1]);
          }
          else
          {
            $products = $products->where('products.primary_category',$cat_id_split[1]);
          }
        }

        if($to_date_exp != null)
        {
          $to_date = str_replace("/","-",$to_date_exp);
          $to_date =  date('Y-m-d',strtotime($to_date));

          if($date_type_exp == '2')
          {
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date.' 23:59:59');
          }
          if($date_type_exp == '1')
          {
            $products = $products->where('o.delivery_request_date', '<=', $to_date);
          }
        }

        if($customer_group_id_exp != null)
        {
          $cat_id_split = explode('-',$customer_group_id_exp);
          // dd($cat_id_split);
          if($cat_id_split[0] == 'cus')
          {
            $products = $products->where('o.customer_id',$cat_id_split[1]);
          }
          else
          {
            $customer_ids = Customer::where('category_id',$cat_id_split[1])->pluck('id');
            $products = $products->whereIn('o.customer_id',$customer_ids);
          }
          // $products = $products->where('o.customer_id',$customer_id_exp);
        }

        if($sales_person_exp !== NULL)
        {
          $products = $products->where('o.user_id',$sales_person_exp);
        }
        else
        {
          $products = $products->where('o.dont_show',0);
        }

        if($role_id == 3)
        {
          // $products = $products->where('o.user_id',$user_id);
          $user_primary_customers = Customer::where('primary_sale_id',$user_id)->pluck('id')->toArray();
          $products = $products->where(function($q) use ($user_id, $user_primary_customers){
            $q->where('o.user_id',$user_id)->orWhereIn('o.customer_id',$user_primary_customers);
          });
        }

        if($product_type != null)
        {
          $products->where('products.type_id',$product_type);
        }
        if($product_type_2 != null)
        {
          $products->where('products.type_id_2',$product_type_2);
        }

        if($product_type_3 != null)
        {
          $products->where('products.type_id_3',$product_type_3);
        }

        if($role_id == 9)
        {
          $products->Where('products.ecommerce_enabled',1);
        }

        $data = new Request();
        $data->replace(['sortbyparam' => $this->sortbyparam, 'sortbyvalue' => $this->sortbyvalue]);
        $products = Product::doSortby($data, $products);

        $products = $products->with('warehouse_products', 'productType', 'productType2', 'productType3', 'sellingUnits', 'product_fixed_price');
        $to_get_totals = (clone $products)->get();
        $products = $products->with('sellingUnits', 'productType', 'warehouse_products', 'product_fixed_price','productType2', 'productType3');

        $date_type = $date_type_exp;

        $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','product_sale_report')->where('user_id',$user_id)->first();
        if($not_visible_columns!=null)
        {
          $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
        }
        else
        {
          $not_visible_arr=[];
        }

        $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
        $return = \Excel::store(new ProductSalesReportExport($products,$global_terminologies,$role_id,$getCategories,$getWarehouses,$not_visible_arr, $warehouse_id, $product_detail_section), 'product-sale-export.xlsx');

        if($return)
        {
          ExportStatus::where('user_id',$user_id)->where('type','product_sale_report')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
          return response()->json(['msg'=>'File Saved']);
        }
      }
      catch(Exception $e)
      {
        $this->failed($e);
      }
      catch(MaxAttemptsExceededException $e)
      {
        $this->failed($e);
      }
    }

    public function failed( $exception)
    {
      ExportStatus::where('type','product_sale_report')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="Complete Products Export";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
