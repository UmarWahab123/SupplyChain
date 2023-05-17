<?php

namespace App\Http\Controllers\Backend;

use DB;
use Auth;
use App\User;
use App\General;
use App\Variable;
use App\ExportStatus;
use App\Notification;
use Illuminate\Http\Request;
use App\Models\Common\Product;
use App\Models\Common\Supplier;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use Illuminate\Support\Facades\View;
use App\Models\Common\ProductCategory;
use App\Models\Common\TableHideColumn;
use Illuminate\Support\Facades\Schema;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Order\OrderProduct;
use App\Helpers\ProductSaleReportByMonthHelper;

class ProductSaleReportByMonthController extends Controller
{
  protected $user;
  public function __construct()
  {

      $this->middleware('auth');
      $this->middleware(function ($request, $next) {
          $this->user= Auth::user();

          return $next($request);
      });
      $dummy_data=null;
      if($this->user && Schema::has('notifications')){
          $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
          }
      $general = new General();
      $targetShipDate = $general->getTargetShipDateConfig();
      $this->targetShipDateConfig = $targetShipDate;

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

      $config=Configuration::first();
      $sys_name = $config->company_name;
      $sys_color = $config;
      $sys_logos = $config;
      $part1=explode("#",$config->system_color);
      $part1=array_filter($part1);
      $value = implode(",",$part1);
      $num1 = hexdec($value);
      $num2 = hexdec('001500');
      $sum = $num1 + $num2;
      $sys_border_color = "#";
      $sys_border_color .= dechex($sum);
      $part1=explode("#",$config->btn_hover_color);
      $part1=array_filter($part1);
      $value = implode(",",$part1);
      $number = hexdec($value);
      $sum = $number + $num2;
      $btn_hover_border = "#";
      $btn_hover_border .= dechex($sum);
      $current_version='4.3';
      // current controller constructor
      $general = new General();
      $targetShipDate = $general->getTargetShipDateConfig();
      $this->targetShipDateConfig = $targetShipDate;
      // Sharing is caring
      View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data]);
  }

  public function ProductSalesReportByMonth(Request $request, $year = null)
  {
	$sales_years = [];
	$current_year = date('Y');
	for ($i=$current_year; $i >= 2020; $i--)
	{
	  $sales_years[$i] = $i;
	}

	$sales_ids = DB::table('customers')->where('secondary_sale_id',Auth::user()->id)->distinct()->pluck('primary_sale_id')->toArray();
	$sales_person_filter = User::select('id','name')->whereIn('id',$sales_ids)->whereNull('parent_id')->where('role_id',3)->where('status',1)->get();

	$sales_persons = User::select('id','name')->whereNull('parent_id')->where('role_id',3)->where('status',1)->get();
	$customer_categories = CustomerCategory::with('customer')->where('is_deleted',0)->get();
	$products = Product::select('id', 'refrence_code', 'short_desc')->get();
	$suppliers = Supplier::select('id', 'reference_number', 'reference_name')->get();
	$product_categories = ProductCategory::with('get_Child:id,title,parent_id')->select('id', 'title')->where('parent_id', 0)->get();
	if($year == null || $year == $current_year)
	{
	  for ($i=0; $i< date('m'); $i++ )
	  {
	    $months[] = date("M", strtotime( date( 'Y-m-01' )." -$i months"));
	  }
   
	  $months = array_reverse($months);
   
   
    
    
	}
	else if($year < $current_year)
	{
	  $months = array();
	  array_push($months, 'Jan');
	  array_push($months, 'Feb');
	  array_push($months, 'Mar');
	  array_push($months, 'Apr');
	  array_push($months, 'May');
	  array_push($months, 'Jun');
	  array_push($months, 'Jul');
	  array_push($months, 'Aug');
	  array_push($months, 'Sep');
	  array_push($months, 'Oct');
	  array_push($months, 'Nov');
	  array_push($months, 'Dec');
	}
	$year = $year == null ? $current_year : $year;
	$table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('year',$year)->where('type', 'customer_sale_report')->first();
	$file_name=ExportStatus::where('user_id',Auth::user()->id)->where('type','product_sales_report_by_month')->first();
	$extra_space = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	return view('backend.reports.product-sales-report-by-month' , compact('sales_years','sales_persons','customer_categories','table_hide_columns','months','file_name','sales_person_filter','year', 'products', 'suppliers', 'product_categories', 'extra_space'));
  }

  public function getProductSalesReportByMonth(Request $request)
  {
    $sale_year = $request->sale_year;
    $products = OrderProduct::from('order_products as op');
    if ($request->order_status == 2) {
        $products = $products->select(DB::raw('coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.number_of_pieces
        END),0) AS jan_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.number_of_pieces
        END),0) AS feb_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.number_of_pieces
        END),0) AS mar_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.number_of_pieces
        END),0) AS apr_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.number_of_pieces
        END),0) AS may_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.number_of_pieces
        END),0) AS jun_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.number_of_pieces
        END),0) AS jul_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.number_of_pieces
        END),0) AS aug_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.number_of_pieces
        END),0) AS sep_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.number_of_pieces
        END),0) AS oct_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.number_of_pieces
        END),0) AS nov_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.number_of_pieces
        END),0) AS dec_totalAmount
        '),
        'op.id', 'op.product_id')->where('o.primary_status', 2);
    }
    else if ($request->order_status == 3)
    {
        $products = $products->select(DB::raw('coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.pcs_shipped
        END),0) AS jan_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.pcs_shipped
        END),0) AS feb_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.pcs_shipped
        END),0) AS mar_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.pcs_shipped
        END),0) AS apr_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.pcs_shipped
        END),0) AS may_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.pcs_shipped
        END),0) AS jun_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.pcs_shipped
        END),0) AS jul_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.pcs_shipped
        END),0) AS aug_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.pcs_shipped
        END),0) AS sep_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.pcs_shipped
        END),0) AS oct_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.pcs_shipped
        END),0) AS nov_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.pcs_shipped
        END),0) AS dec_totalAmount
        '),
        'op.id', 'op.product_id')->where('o.primary_status', 3);
    }
    else{
        $products = $products->select(DB::raw('coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.number_of_pieces
        END),0)  +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.qty_shipped
        END),0)  +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN op.pcs_shipped
        END),0) AS jan_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN op.pcs_shipped
        END),0) AS feb_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN op.pcs_shipped
        END),0) AS mar_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN op.pcs_shipped
        END),0) AS apr_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN op.pcs_shipped
        END),0) AS may_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN op.pcs_shipped
        END),0) AS jun_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN op.pcs_shipped
        END),0) AS jul_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN op.pcs_shipped
        END),0) AS aug_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN op.pcs_shipped
        END),0) AS sep_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN op.pcs_shipped
        END),0) AS oct_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN op.pcs_shipped
        END),0) AS nov_totalAmount,
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.quantity
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=2 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.number_of_pieces
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.qty_shipped
        END),0) +
        coalesce(sum(CASE
        WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN op.pcs_shipped
        END),0) AS dec_totalAmount
        '),
        'op.id', 'op.product_id')->whereIn('o.primary_status', [2,3]);
    }
    $products = $products->with('product:id,refrence_code,brand,short_desc,selling_unit,supplier_id', 'product.sellingUnits:id,title');
    $products = $products->leftJoin('orders as o', 'op.order_id', '=', 'o.id');
    // dd($products->toSql());

    if($request->customer_categories != null)
    {
    	$id_split = explode('-', $request->customer_categories);
    	if ($id_split[0] == 'pri') {
            $products = $products->join('customers as c', 'o.customer_id', '=', 'c.id');
      		$products = $products->where('c.category_id',$id_split[1]);
    	}
    	else{
      		$products = $products->where('o.customer_id',$id_split[1]);
    	}
    }

    if($request->supplier != null)
    {
      	// $products = $products->where('op.supplier_id',$request->supplier);
        $products = $products->leftJoin('products as prod', 'op.product_id', '=', 'prod.id')->where('prod.supplier_id', $request->supplier);

        // $products = $products->whereHas('product',function($q) use ($request){
        //   $q->where('products.supplier_id', $request->supplier);
        // });
    }

    if($request->product != null)
    {
      	// $products = $products->where('products.id',$request->product);
      	$products = $products->where('op.product_id',$request->product);
    }

    if($request->product_category != null)
    {
      	$id_split = explode('-', $request->product_category);
        $products = $products->join('products as p', 'p.id', '=', 'op.product_id');
    	if ($id_split[0] == 'pri') {
      		$products = $products->where('p.primary_category',$id_split[1]);
    	}
    	else{
      		$products = $products->where('p.category_id',$id_split[1]);
    	}
    }

    if($request->sale_person != null)
    {
      // $customers->where('o.user_id',$request->sale_person);
      $products->where('o.user_id',$request->sale_person);
    }
    else
    {
      $products->where('o.dont_show',0);
    }

    if($request->sale_person_filter != null )
    {
      if(Auth::user()->id != $request->sale_person_filter)
      {
        $products = $products->where('o.user_id',$request->sale_person_filter);
      }
      else
      {
        $products = $products->where(function($q){
          $q->where('o.user_id',Auth::user()->id);
        });
      }
    }
    elseif(Auth::user()->role_id == 3)
    {
      $user_i = Auth::user()->id;
      $products = $products->where(function($q){
        $q->where('o.user_id',Auth::user()->id);
      });
    }

    $products = $products->where('is_billed', 'Product')->whereYear('o.converted_to_invoice_on',$sale_year)->groupBy('op.product_id');

    /*********************  Sorting code ************************/
    $products = Product::sortPSRByMonth($products, $request);
    

    /*********************************************/
    if ($request->type == 'footer') {
    	$total_product_qty = (clone $products)->get();
      $totalAllMonths = $total_product_qty->sum('jan_totalAmount')
      + $total_product_qty->sum('feb_totalAmount')
      + $total_product_qty->sum('mar_totalAmount')
      + $total_product_qty->sum('apr_totalAmount')
      + $total_product_qty->sum('may_totalAmount')
      + $total_product_qty->sum('jun_totalAmount')
      + $total_product_qty->sum('jul_totalAmount')
      + $total_product_qty->sum('aug_totalAmount')
      + $total_product_qty->sum('sep_totalAmount')
      + $total_product_qty->sum('oct_totalAmount')
      + $total_product_qty->sum('nov_totalAmount')
      + $total_product_qty->sum('dec_totalAmount');
    	return response()->json([
    		'janTotal' => $total_product_qty->sum('jan_totalAmount'),
    		'febTotal' => $total_product_qty->sum('feb_totalAmount'),
    		'marTotal' => $total_product_qty->sum('mar_totalAmount'),
    		'aprTotal' => $total_product_qty->sum('apr_totalAmount'),
    		'mayTotal' => $total_product_qty->sum('may_totalAmount'),
    		'junTotal' => $total_product_qty->sum('jun_totalAmount'),
    		'julTotal' => $total_product_qty->sum('jul_totalAmount'),
    		'augTotal' => $total_product_qty->sum('aug_totalAmount'),
    		'sepTotal' => $total_product_qty->sum('sep_totalAmount'),
    		'octTotal' => $total_product_qty->sum('oct_totalAmount'),
    		'novTotal' => $total_product_qty->sum('nov_totalAmount'),
    		'decTotal' => $total_product_qty->sum('dec_totalAmount'),
        'mTotal'=>$totalAllMonths,
    	]);
    }


    $ids = [];

    $not_visible_arr=[];
    $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('year',$sale_year)->where('type', 'product_sales_report_by_month')->first();
    if($table_hide_columns!=null)
    {
      $not_visible_arr = explode(',',$table_hide_columns->hide_columns);
    }

    $dt = Datatables::of($products);
    $add_columns = ['Jan', 'Dec', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov','total'];
    foreach ($add_columns as $column) {
        $dt->addColumn($column, function ($item) use ($column, $not_visible_arr) {
            return Product::returnAddColumnProductSaleReportByMonth($column, $item, $not_visible_arr);
        });
    }

    $edit_columns = ['selling_unit', 'short_desc', 'brand', 'refrence_code'];
    foreach ($edit_columns as $column) {
        $dt->editColumn($column, function ($item) use ($column, $not_visible_arr) {
            return Product::returnEditColumnProductSaleReportByMonth($column, $item, $not_visible_arr);
        });
    }

      $dt->rawColumns(['refrence_code', 'brand', 'short_desc', 'selling_unit', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec','total']);
      return $dt->make(true);
  }


  public function ExportProductSalesReportByMonth(Request $request)
  {
    return ProductSaleReportByMonthHelper::ExportProductSalesReportByMonth($request);
  }

  public function CheckStatusProductSalesReportByMonth(Request $request)
  {
	return ProductSaleReportByMonthHelper::CheckStatusProductSalesReportByMonth($request);
  }

  public function RecursiveExportStatusProductSalesReportByMonth(Request $request)
  {
    return ProductSaleReportByMonthHelper::RecursiveExportStatusProductSalesReportByMonth($request);
  }
}
