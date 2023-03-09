<?php

namespace App\Http\Controllers\Ecom;

use App\Events\ProductCreated;
use App\ExportStatus;
use App\Exports\AllProductsExport;
use App\Exports\BulkProducts;
use App\Exports\FilteredProductsExport;
use App\Exports\SupplierAllProductsExport;
use App\Exports\completeProductExport;
use App\Exports\soldProductExport;
use App\FiltersForCompleteProduct;
use App\FiltersForSoldProductReport;
use App\Http\Controllers\Controller;
use App\Imports\ProductBulkImport;
use App\Imports\ProductPricesBulkImport;
use App\Imports\ProductSuppliersBulkImport;
use App\Jobs\CompleteProductsExportJob;
use App\Jobs\SoldProductsExportJob;
use App\Jobs\StockMovementReportExportJob;
use App\Models\Common\Brand;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Country;
use App\Models\Common\Currency;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PaymentType;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\ProductImage;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\TempProduct;
use App\Models\Common\Unit;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\Notifications\AddProductNotification;
use App\Jobs\ProductSaleReportJob;
use App\OrderTransaction;
use App\PoPaymentRef;
use App\PoTransactionHistory;
use App\ProductHistory;
use App\ProductQuantity;
use App\ProductsRecord;
use App\PurchaseOrderTransaction;
use App\QuotationConfig;
use App\StatusCheckForSoldProductsExport;
use App\TransactionHistory;
use App\ImportFileHistory;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Excel;
use File;
use Illuminate\Http\Request;
use Image;
use Notification;
use Session;
use Validate;
use Yajra\Datatables\Datatables;

class SoldProductReportController extends Controller
{
	public function ecomsoldproductreport(Request $request){

    $customer_id = null;
      $warehouses = Warehouse::where('status',1)->get();
      $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'product_sale_report')->first();
      $product_parent_categories = ProductCategory::where('parent_id',0)->orderBy('title')->get();
      $product_sub_categories = ProductCategory::where('parent_id','!=',0)->orderBy('title')->groupBy('title')->get();
      if(Auth::user()->role_id == 3)
      {
        $customers = Customer::where(function($query){
          $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
        })->where('status',1)->orderBy('reference_name')->get();
      }
      else
      {
        $customers = Customer::where('status',1)->orderBy('reference_name')->get();
      }
      $products            = Product::where('status',1)->get();
      $suppliers           = Supplier::where('status',1)->orderBy('reference_name')->get(); 
      $customer_categories = CustomerCategory::all();
      $sales_persons       = User::where('status',1)->where('role_id',3)->whereNull('parent_id')->get();

      $filter = @$request->from_margin_report;
      if(!empty($filter))
      {
        $customer_id = @$request->customer_id;
        Session::put('customer_id', $customer_id);
      }
      $getCategories = CustomerCategory::where('show',1)->get();

      return $this->render('ecom.home.ecom-sold-product-report',compact('warehouses','product_parent_categories','product_sub_categories','customers','products','suppliers','customer_categories','sales_persons','getCategories','table_hide_columns'));
		
    }


    public function ecomsoldproductreportdata(Request $request){
     // dd($request->category_id);

      if($request->sortbyparam == 1 && $request->sortbyvalue == 1)
      {
        $sort_variable  = 'refrence_code';
        $sort_order     = 'DESC';
      }
      elseif($request->sortbyparam == 1 && $request->sortbyvalue == 2)
      {
        $sort_variable  = 'refrence_code';
        $sort_order     = 'ASC';
      }

      if($request->sortbyparam == 2 && $request->sortbyvalue == 1)
      {
        $sort_variable  = 'short_desc';
        $sort_order     = 'DESC';
      }
      elseif($request->sortbyparam == 2 && $request->sortbyvalue == 2)
      {
        $sort_variable  = 'short_desc';
        $sort_order     = 'ASC';
      }

      if($request->sortbyparam == 5 && $request->sortbyvalue == 1)
      {
        $sort_variable  = 'QuantityText';
        $sort_order     = 'DESC';
      }
      elseif($request->sortbyparam == 5 && $request->sortbyvalue == 2)
      {
        $sort_variable  = 'QuantityText';
        $sort_order     = 'ASC';
      }

      if($request->sortbyparam == 7 && $request->sortbyvalue == 1)
      {
        $sort_variable  = 'TotalAmount';
        $sort_order     = 'DESC';
      }
      elseif($request->sortbyparam == 7 && $request->sortbyvalue == 2)
      {
        $sort_variable  = 'TotalAmount';
        $sort_order     = 'ASC';
      }

      $from_date = $request->from_date;
      $to_date = $request->to_date;
      $customer_id = $request->customer_id;
      $supplier_id = $request->supplier_id;
      $sales_person = $request->sales_person;
      $customer_orders_ids = NULL;
      $products = Product::select(DB::raw('SUM(CASE 
      WHEN o.primary_status="2" THEN op.quantity
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS QuantityText,
      SUM(CASE 
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat
      END) AS TotalAmount,
      (CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN (SUM(op.locked_actual_cost)/COUNT(op.id))
      END) AS TotalAverage,
      SUM(CASE 
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat END)/SUM(CASE 
      WHEN o.primary_status="2" THEN op.quantity
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS avg_unit_price'),'products.refrence_code','products.selling_unit','products.short_desc','op.product_id','op.vat_amount_total','op.total_price','products.id','products.category_id','products.primary_category','products.brand')->groupBy('op.product_id');
      $products->join('order_products AS op','op.product_id','=','products.id');
      $products->join('orders AS o','o.id','=','op.order_id');
      // dd($products->toSql());
       if($supplier_id != null) 
      {
        $products = $products->where('products.supplier_id',$supplier_id);
      }

      if($request->product_id != null) 
      {
        $products = $products->where('products.id',$request->product_id);
      }

      if($request->from_date != null)
      {
        $from_date = str_replace("/","-",$request->from_date);
        $from_date =  date('Y-m-d',strtotime($from_date));
        if($request->date_type == '2'){
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date.' 23:59:59');
          }
          if($request->date_type == '1'){
            $products = $products->where('o.delivery_request_date', '>=', $from_date);
          }
        // $products = $products->where('o.converted_to_invoice_on','>=',$from_date);
      }

      if($request->category_id != null){
        $products = $products->where('products.primary_category',$request->category_id);
      }

      if($request->sub_category_id != null){
        $products = $products->where('products.category_id',$request->sub_category_id);
      }

      if($request->to_date != null)
      {
        $to_date = str_replace("/","-",$request->to_date);
        $to_date =  date('Y-m-d',strtotime($to_date));

        if($request->date_type == '2'){
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date.' 23:59:59');
          }
          if($request->date_type == '1'){
            $products = $products->where('o.delivery_request_date', '<=', $to_date);
          }

        // $products = $products->where('o.converted_to_invoice_on','<=',$to_date.' 23:59:59');
      }

      if($customer_id != null)
      {
        $products = $products->where('o.customer_id',$customer_id);
      }

      if($request->customer_group != null)
      {
        $customer_ids = Customer::where('category_id',$request->customer_group)->pluck('id');
             
        $products = $products->whereIn('o.customer_id',$customer_ids);
      }

      if($request->sales_person !== NULL)
      {
        $sales_id = $request->sales_person;
        $salesCustomers = Customer::where(function($query) use($sales_id){
          $query->where('primary_sale_id',$sales_id)->orWhere('secondary_sale_id',$sales_id);
              })->where('status',1)->pluck('customers.id')->toArray();
        $products = $products->whereIn('o.customer_id',$salesCustomers);
      }

      if(Auth::user()->role_id == 3)
      {
        $sales_id = Auth::user()->id;
        $salesCustomers = Customer::where(function($query) use($sales_id){
          $query->where('primary_sale_id',$sales_id)->orWhere('secondary_sale_id',$sales_id);
              })->where('status',1)->pluck('customers.id')->toArray();
        $products = $products->whereIn('o.customer_id',$salesCustomers);
      }
      $products = $products->where('products.status',1)->Where('products.ecommerce_enabled',1);

      if($request->sortbyparam != NULL)
      {
        $products->orderBy($sort_variable, $sort_order);
      } 
      // dd($products->toSql());
      $to_get_totals = (clone $products)->get();
      $products = $products->with('sellingUnits');
      $date_type =  $request->date_type;

      $getCategories = CustomerCategory::all();

      // dd($date_type);
        $dt = Datatables::of($products);
        
        $dt->addColumn('view', function ($item) use ($from_date,$to_date,$supplier_id,$customer_id, $date_type){
             $customer_id == '' ? $customer_id = 'NA' : '';
             $supplier_id == '' ? $supplier_id = 'NA' : '';
             $from_date == '' ? $from_date = 'NoDate' : '';
             $to_date == '' ? $to_date = 'NoDate' : '';
             $date_type == '' ? $date_type = '1' : '';
            $html_string = '<a target="_blank" href="'.url('get-product-sales-report-detail/'.$customer_id.'/'.$supplier_id.'/'.$item->product_id.'/'.$from_date.'/'.$to_date.'/'.$date_type).'" class="actionicon" style="cursor:pointer" title="View history" data-id='.$item->product_id.'><i class="fa fa-history"></i></a>';  
            return $html_string;
        });

        $dt->editColumn('refrence_code', function ($item){
          $html_string = '<a href="'.url('get-product-detail/'.$item->product_id).'" target="_blank" title="View Detail"><b>'.$item->refrence_code.'</b></a>';
          return $html_string;
        });

        $dt->editColumn('short_desc', function ($item){
          return $item->short_desc;
        });

        $dt->addColumn('selling_unit', function ($item){
          // dd($item);
          return @$item->sellingUnits->title;
        });

        $dt->addColumn('brand', function ($item){
          return @$item->brand != null ? @$item->brand : '--';
        });

        $dt->addColumn('total_quantity', function ($item) {
          return number_format($item->QuantityText,2);
        });

        $dt->addColumn('total_cost', function ($item) {
          return number_format($item->TotalAverage,2);
        });

        $dt->addColumn('total_amount', function ($item) {
          return number_format($item->TotalAmount,2);
        });

        $dt->addColumn('total_stock', function ($item) {
          return number_format($item->warehouse_products()->sum('current_quantity'),2);
        });

        $dt->addColumn('sub_total', function ($item){
          return number_format($item->total_price,2);
        });

        $dt->addColumn('vat_thb', function ($item){
          return $item->vat_amount_total != null ? number_format($item->vat_amount_total,2) : '--';
        });

        $dt->addColumn('avg_unit_price', function ($item) {
          return number_format($item->avg_unit_price,2);
        });
        
        $dt->setRowId(function ($item) {
            return $item->id;
        });

        //Customer Category Dynamic Columns Starts Here
        if($getCategories->count() > 0)
        {
          foreach ($getCategories as $cat) 
          {
            $dt->addColumn($cat->title,function($item) use($cat){
              $fixed_value = $item->product_fixed_price()->where('product_id',$item->id)->where('customer_type_id',$cat->id)->first();
              $value = $fixed_value != null ? $fixed_value->fixed_price : '0.00'; 

               $formated_value = number_format($value,3,'.',',');

              return $formated_value;
            });
          }
        }

        $dt->escapeColumns([]);
        $dt->rawColumns(['view','refrence_code','short_desc','selling_unit','total_quantity','total_amount','total_stock']);
        $dt->with([
          'total_quantity'=>$to_get_totals->sum('QuantityText'),
          'total_amount'=>$to_get_totals->sum('TotalAmount'),
          'total_cost' => $to_get_totals->sum('TotalAverage'),
          'avg_unit_price'=>$to_get_totals->sum('avg_unit_price'),
        ]);
        return $dt->make(true);
}
}
