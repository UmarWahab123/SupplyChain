<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\Product;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderNote;
use App\Models\Sales\Customer;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\CustomerCategory;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Configuration;
use PDF;
use App\User;
use Auth;
use Hash;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\PrintHistory;
use DB;
use App\Notification;
use App\Variable;
use App\QuotationConfig;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    protected $user = null;
    public function __construct()
    {
        $this->middleware('auth');
        // to get authenticated user
         $this->middleware(function ($request, $next) {
            $this->user= Auth::user();

            return $next($request);
        });
        $dummy_data=null;
        if($this->user && Schema::has('notifications')){
            $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
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

        $extra_space_for_select2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data,'extra_space' => $extra_space_for_select2]);
    }

    public function getHome()
    {
      $company_total_sales = 0;
      $total_sales = 0;
      $salesCustomers = 0;
      $salesQuotations = 0;
      $sales_persons = 0;
      $sales_coordinator_customers_count = 0;
      $salesQuotations = 0;
      $quotation_statuses = Status::select('id','title')->where('parent_id',1)->orderBy('id','desc')->get();
      $table_hide_columns = TableHideColumn::select('hide_columns')->where('user_id', Auth::user()->id)->where('type', 'quotation_dashboard')->first();
      $display_my_quotation = ColumnDisplayPreference::select('display_order')->where('type', 'quotation_dashboard')->where('user_id', Auth::user()->id)->first();
      $warehouse_id = Auth::user()->warehouse_id;
      // $users = User::whereIn('role_id',[3,4])->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();
      $customer_categories = CustomerCategory::select('id','title')->where('is_deleted',0)->get();
      $sales_coordinator_customers_count=0;
      if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4)
      {
        if(Auth::user()->role_id == 3)
        {
            $user_id=Auth::user()->id;
          $customers = Customer::where(function($query) use($user_id){
            $query->where('primary_sale_id',Auth::user()->id)->orWhereHas('CustomerSecondaryUser',function($query) use($user_id){
                $query->where('user_id',$user_id);
            });
          })->where('status',1)->get();
          // $all_customer_ids = array_merge($this->user->customer->pluck('id')->toArray(),$this->user->secondary_customer->pluck('id')->toArray());

        }
        else if(Auth::user()->role_id == 4)
        {
          $warehouse_id = Auth::user()->warehouse_id;
          $ids = User::select('id')->whereNull('parent_id')->where('warehouse_id',$warehouse_id)->where('role_id',3)->pluck('id')->toArray();
          $customers = Customer::where(function($query) use ($ids){
            $query->whereIn('primary_sale_id',$ids)->orWhereHas('CustomerSecondaryUser',function($query) use($ids){
                $query->whereIn('user_id',$ids);
            });
          })->where('status',1)->get();
        }
        $total_sales = 0;
      }

      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
      {
        $currentMonth = date('m');
        //For admin total_invoice
        $month = date('m');
        $day = '01';
        $year = date('Y');

        $start_of_month = $year . '-' . $month . '-' . $day;
        $today = date('Y-m-d');

        //for admin total draft
        $month = date('m');
        $day = '01';
        $year = date('Y');

        $start_of_month = $year . '-' . $month . '-' . $day;
        $today = date('Y-m-d');

        $customers = Customer::select('id','reference_name')->where('status',1)->get();
      }
      $sales_persons = User::select('id','name')->whereNull('parent_id')->where('status',1)->where('role_id',3)->orderBy('name')->get();
      $is_texica = Status::where('id',1)->pluck('is_texica')->first();
      return $this->render('sales.home.dashboard',compact('customers','sales_persons','table_hide_columns','display_my_quotation','warehouses','total_sales','salesCustomers','totalCustomers','sales_coordinator_customers_count','customer_categories','admin_total_sales','admin_total_sales_draft','company_total_sales','quotation_statuses','total_amount_of_quotation','total_number_of_draft_invoices','total_amount_overdue','total_amount_of_overdue_invoices_count','total_gross_profit','total_gross_profit_count','quotation','is_texica'));
    }

    public function getStats()
    {
      $max_products_quotation = $this->getMaxProductsOrder(1);
      $max_products_draft = $this->getMaxProductsOrder(2);
      $max_products_invoice = $this->getMaxProductsOrder(3);

      $purchasingStatuses = Status::where('parent_id',4)->get();
      $max_products_purchasing_order = [];
      foreach ($purchasingStatuses as $purchasingStatus)
      {
        $purchasing_orders = PurchaseOrder::select('id')->where('status', $purchasingStatus->id)->get();
        $purchasing_orders_array=[];
        foreach ($purchasing_orders as $order)
        {
          $purchasing_orders_array[$order->id]= $order->PurchaseOrderDetail()->count();
        }

        $maximum_products_in_order = count($purchasing_orders_array) ? max($purchasing_orders_array) : 0;
        $order_id_maximum_products = $maximum_products_in_order ?  array_search($maximum_products_in_order, $purchasing_orders_array) : 0;

        $max_products_purchasing_order[$purchasingStatus->id] = [
          "products" => $maximum_products_in_order,
          "order_id" => $order_id_maximum_products
        ];
      }

      return $this->render('sales.home.stats',
        compact(
          "max_products_quotation",
          "max_products_draft",
          "max_products_invoice",
          "max_products_purchasing_order",
          "purchasingStatuses"
        )
      );
    }

    protected function getMaxProductsOrder($primary_status)
    {
      $orders = Order::select('id','total_amount')->where('primary_status', $primary_status)->get();
      $orders_array=[];
      foreach ($orders as $order)
      {
        $orders_array[$order->id]= $order->order_products()->count();
      }

      $maximum_products_in_order = count($orders_array) ? max($orders_array) : 0;
      $order_with_maximum_products = $maximum_products_in_order ?  array_search($maximum_products_in_order, $orders_array) : 0;

      return [
        "products" => $maximum_products_in_order,
        "order_id" => $order_with_maximum_products
      ];
    }

    public function getSales(Request $request)
    {
      $sales_persons = User::where('role_id',3)->whereNull('parent_id')->where('warehouse_id',$request->id)->where('status',1)->get();
      $html_string = '';
      foreach ($sales_persons as $per)
      {
        $html_string .= '<option value="'.$per->id.'">'.@$per->name.'</option>';
      }
      return response()->json(['options'=>$html_string,'error'=>false]);
    }

    public function allNotifications()
    {
      $user = User::all();
      return $this->render('sales.notifications.index',compact('user'));
    }

    public function getNotifications()
    {
      $loop =0;
      $html_string = null;
      foreach(Auth()->user()->unreadNotifications as $notification)
      {
        $user = User::select('name')->where('id',@$notification->data['user_id'])->first();

        $html_string .= '<div class="usercol notinfo">
          <a href="'.url("sales/mark-read/".$loop."/".$notification->data["product_id"]).'" class="notiflink">
            <div class="notifi-name fontbold">"'.@$notification->data['reference_code'].'"
            </div>
            <div class="notifi-desc">"'.@$notification->data["product"].'"</div>
            <span class="notifi-date fontmed">By : "'.@$user->name.'"</span>
          </a>
          </div>';
        $loop++;
      }
      return response()->json(['html'=>$html_string,'count'=>$loop]);
    }

    public function getDraftInvoice()
    {
      $sales_coordinator_customers_count = 0;
      $company_total_sales = 0;
      $salesCustomers = 0;
      $salesQuotations = 0;
      $quotation_statuses = Status::whereNotIn('id',[34])->where('parent_id',2)->get();
      $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'draft_invoice_dashboard')->first();
      $display_draft_invoice = ColumnDisplayPreference::where('type', 'draft_invoice_dashboard')->where('user_id', Auth::user()->id)->first();
      if(Auth::user()->role_id == 3)
      {
        $user_id= Auth::user()->id;
        $customers = Customer::where(function($query) use($user_id){
          $query->where('primary_sale_id',Auth::user()->id)->orWhereHas('CustomerSecondaryUser',function($query) use ($user_id){
              $query->where('user_id',$user_id);
          });
        })->where('status',1)->get();
      }
      else
      {
        $warehouse_id = Auth::user()->warehouse_id;
          $ids = User::select('id')->where('warehouse_id',$warehouse_id)->where('role_id',3)->pluck('id')->toArray();
          $customers = Customer::where(function($query) use ($ids){
            $query->whereIn('primary_sale_id',$ids)->orWhereHas('CustomerSecondaryUser',function($query) use($ids){
                $query->whereIn('user_id',$ids);
            });
          })->where('status',1)->get();
      }

      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
      {
        $sales_orders = Order::whereIn('primary_status',[2,3])->whereMonth('created_at',Carbon::now()->month)->whereYear('created_at',Carbon::now()->year)->get();
        $customers = Customer::where('status',1)->get();
      }

      $total_sales = 0;

      $sales_persons = User::where('status',1)->whereNull('parent_id')->where('role_id',3)->orderBy('name')->get();
      // $totalCustomers = Customer::where('status',1)->count();
      $customer_categories = CustomerCategory::where('is_deleted',0)->get();
      //For admin total_invoice
      $month = date('m');
      $day = '01';
      $year = date('Y');

      $start_of_month = $year . '-' . $month . '-' . $day;
      $today = date('Y-m-d');
      $admin_total_sales = 0;

      //for admin total draft
      $month = date('m');
      $day = '01';
      $year = date('Y');

      $start_of_month = $year . '-' . $month . '-' . $day;
      $admin_total_sales_draft = 0;
      $today = date('Y-m-d');


       //Total overdue balance
      $today_date = date('Y-m-d H:i:s');
      $showPrintPickBtn = '';
      $globalAccessConfig = QuotationConfig::where('section','quotation')->first();
      if($globalAccessConfig)
      {
          if($globalAccessConfig->print_prefrences != null)
          {
              $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
              foreach ($globalaccessForConfig as $val)
              {

                  if($val['slug'] === "show_ppbtn")
                  {
                     $showPrintPickBtn = $val['status'];
                  }
              }
          }

      }
      $is_texica = Status::where('id',1)->pluck('is_texica')->first();
      return $this->render('sales.home.draft_invoice_dashboard',compact('customers','sales_persons','table_hide_columns','display_draft_invoice','totalCustomers','total_sales','sales_coordinator_customers_count','salesCustomers','salesQuotations','customer_categories','admin_total_sales','admin_total_sales_draft','company_total_sales','quotation_statuses','total_amount_of_quotation','total_number_of_draft_invoices','total_number_of_invoices','total_amount_overdue','total_amount_of_overdue_invoices_count','total_gross_profit','total_gross_profit_count','showPrintPickBtn','is_texica'));
    }

    public function getInvoice()
    {
      $totalCustomers = 0;
      $salesCustomers = 0;
      $salesQuotations = 0;
      $credit_notes_total = 0;
      $company_total_sales = 0;
      $debit_notes_total = 0;
      $sales_coordinator_customers_count = 0;
      $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'my_invoices')->first();
      $display_purchase_list = ColumnDisplayPreference::where('type', 'my_invoices')->where('user_id', Auth::user()->id)->first();
      $quotation_statuses = Status::select('id','title')->where('parent_id',3)->get();
      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
      {
        // $sales_orders = Order::whereIn('primary_status',[2,3])->whereMonth('created_at',Carbon::now()->month)->whereYear('created_at',Carbon::now()->year)->get();
        $totalCustomers = Customer::select('id')->where('status',1)->count();
        $customers = Customer::select('id','reference_name')->where('status', 1)->orderBy('id', 'DESC')->get();
      }
      elseif(Auth::user()->role_id == 3)
      {
          $user_id=Auth::user()->id;
        $customers = Customer::where(function($query) use ($user_id){
        $query->where('primary_sale_id',Auth::user()->id)->orWhereHas('CustomerSecondaryUser',function($query) use($user_id){
                $query->where('user_id',$user_id);
        });
        })->where('status',1)->get();
        // $salesCustomers = Customer::where(function($query){
        //     $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
        // })->where('status',1)->count();
        // $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
        // $salesQuotations = Order::select('id')->where('user_id',Auth::user()->id)->where('primary_status', 1)->count();
      }
      else
      {
        $warehouse_id = Auth::user()->warehouse_id;
        $ids = User::select('id')->where('warehouse_id',$warehouse_id)->where(function($query){
          $query->where('role_id',4)->orWhere('role_id',3)->orWhere('role_id',1);
        })->whereNull('parent_id')->pluck('id')->toArray();
        $query = Customer::query();
        $customers = Customer::where(function($query) use ($ids){
          $query->whereIn('primary_sale_id',$ids)->orWhereHas('CustomerSecondaryUser',function($query) use($ids){
            $query->whereIn('user_id',$ids);
          });
        })->where('status',1)->get();
        // $sales_coordinator_customers_count = Customer::where(function($query) use ($ids){
        //   $query->whereIn('primary_sale_id',$ids)->orWhereIn('secondary_sale_id',$ids);
        // })->where('status',1)->orderBy('reference_name')->count();
      }
      $sales_persons = User::select('id','name')->whereNull('parent_id')->where('status',1)->where('role_id',3)->orderBy('name')->get();
      $total_sales = 0;
      $customer_categories = CustomerCategory::select('id','title')->where('is_deleted',0)->get();

      //For admin total_invoice
      $month = date('m');
      $day = '01';
      $year = date('Y');

      $start_of_month = $year . '-' . $month . '-' . $day;
      $today = date('Y-m-d');
      $admin_total_sales = 0;

      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
      {
        // $admin_orders = Order::select('id')->where('dont_show',0)->where('primary_status',3)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count();

        // $admin_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.dont_show','orders.converted_to_invoice_on')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->where('orders.dont_show',0)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');
      }
      else if(Auth::user()->role_id == 3)
      {
        $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
        // $admin_orders = Order::select('id','total_amount')->where('primary_status',3)->whereIn('customer_id',$all_customer_ids)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count();

        // $admin_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.customer_id','orders.converted_to_invoice_on','orders.user_id')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->where('orders.user_id',Auth::user()->id)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');

         // $company_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.dont_show','orders.converted_to_invoice_on')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->where('orders.dont_show',0)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');

      }
      else if(Auth::user()->role_id == 4)
      {
        // $admin_orders = Order::select('id','total_amount')->where('primary_status',3)->whereIn('user_id',$ids)->whereBetween('delivery_request_date', [$start_of_month, $today])->count();

        // $admin_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.delivery_request_date')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->whereIn('orders.user_id',$ids)->whereBetween('delivery_request_date', [$start_of_month, $today])->sum('order_products.total_price');
      }

      //for admin total draft
      $month = date('m');
      $day = '01';
      $year = date('Y');

      $start_of_month = $year . '-' . $month . '-' . $day;
      $today = date('Y-m-d');
      $admin_total_sales_draft = 0;

      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
      {
        // $admin_orders_draft = Order::select('id','total_amount')->where('dont_show',0)->where('primary_status',2)->get();
        // $admin_total_sales_draft = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.dont_show')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',2)->whereNotIn('orders.status',[34])->where('dont_show',0)->sum('order_products.total_price');
      }
      else if (Auth::user()->role_id == 3)
      {
        // $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
        // $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('customer_id',$all_customer_ids)->get();
        // $admin_total_sales_draft = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.customer_id','orders.user_id')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',2)->whereNotIn('orders.status',[34])->where('orders.user_id',Auth::user()->id)->sum('order_products.total_price');
      }
      else
      {
        // $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->get();
        // $admin_total_sales_draft = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.dont_show')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',2)->whereNotIn('orders.status',[34])->where('orders.dont_show',0)->whereIn('orders.user_id',$ids)->sum('order_products.total_price');
      }
      if(Auth::user()->role_id == 7)
      {
        $warehouse_id = Auth::user()->warehouse_id;
        $ids = User::select('id')->where('role_id',3)->orWhere('role_id',4)->orWhere('role_id',1)->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->get();
        $credit_notes_total = Order::where('primary_status',25)->where('status',27)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
        $debit_notes_total = Order::where('primary_status',28)->where('status',30)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
      }
      // $total_amount_of_quotation = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',1)->sum('order_products.total_price');
      //total number of draft invoices count
      // $total_number_of_draft_invoices = Order::select('primary_status')->where('primary_status',2)->count();
      //Total number of invoices count
      // $total_number_of_invoices = $admin_orders;

       //Total overdue balance
      $today_date = date('Y-m-d H:i:s');
      // $total_amount_of_overdue_invoices_count = Order::select('primary_status','payment_due_date')->where('primary_status',3)->where('payment_due_date','<',$today_date)->count();

      // $total_amount_of_overdue_invoices = Order::select('primary_status','total_amount','payment_due_date')->where('primary_status',3)->where('payment_due_date','<',$today_date)->sum('total_amount');
      // $total_paid_amount_of_overdue_invoices = Order::select('primary_status','total_paid','payment_due_date')->where('primary_status',3)->where('payment_due_date','<',$today_date)->sum('total_paid');

      // $total_amount_overdue = $total_amount_of_overdue_invoices - $total_paid_amount_of_overdue_invoices;

      //total outstandings
      // if(Auth::user()->role_id == 1)
      // {
      //     $outstanding_orders = Order::select('id','total_amount','total_paid')->where('primary_status',3)->where('status','!=',24)->get();
      //     $total_gross_profit_count = $outstanding_orders->count();

      //     $total_salee = $outstanding_orders->sum('total_amount');
      //     $total_receii = $outstanding_orders->sum('total_paid');
      //     $total_gross_profit = $total_salee - $total_receii;
      //     $quotation = Order::where('dont_show',0)->where('primary_status', 1)->count('id');

      // }
      // else
      // {
      //   $total_gross_profit = 0;
      //   $total_gross_profit_count = 0;
      // }
      $showPrintPickBtn = '';
      $globalAccessConfig = QuotationConfig::where('section','quotation')->first();
      if($globalAccessConfig)
      {
          if($globalAccessConfig->print_prefrences != null)
          {
              $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
              foreach ($globalaccessForConfig as $val)
              {

                  if($val['slug'] === "show_ppbtn")
                  {
                     $showPrintPickBtn = $val['status'];
                  }
              }
          }

      }
      $is_texica = Status::where('id',1)->pluck('is_texica')->first();
      return $this->render('sales.home.invoices_dashboard',compact('customers','sales_persons','display_purchase_list','table_hide_columns','total_sales','totalCustomers','salesCustomers','salesQuotations','sales_coordinator_customers_count','customer_categories','admin_total_sales','admin_total_sales_draft','credit_notes_total','debit_notes_total','company_total_sales','quotation_statuses','total_amount_of_quotation','total_number_of_draft_invoices','total_number_of_invoices','total_amount_overdue','total_amount_of_overdue_invoices_count','total_gross_profit','total_gross_profit_count','quotation','showPrintPickBtn','is_texica'));
    }

    public function getOther()
    {
      $totalCustomers =0;
      $salesCustomers =0;
      $salesQuotations =0;
      $credit_notes_total = 0;
      $debit_notes_total = 0;
      $sales_coordinator_customers_count =0;
      $quotation_statuses = Status::where('parent_id',31)->get();
      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      {
        $sales_orders = Order::whereIn('primary_status',[2,3])->whereMonth('created_at',Carbon::now()->month)->whereYear('created_at',Carbon::now()->year)->get();
        $totalCustomers = Customer::where('status',1)->count();
        $customers = Customer::where('status', 1)->orderBy('id', 'DESC')->get();
      }
      elseif(Auth::user()->role_id == 3)
      {
        $customers = Customer::where(function($query){
        $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
        })->where('status',1)->get();
        $salesCustomers = Customer::where(function($query){
            $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
        })->where('status',1)->count();
        $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
        $salesQuotations = Order::select('id')->whereIn('customer_id',$all_customer_ids)->where('primary_status', 1)->count();
      }
      else
      {
        $warehouse_id = Auth::user()->warehouse_id;
        $ids = User::select('id')->where('warehouse_id',$warehouse_id)->where(function($query){
          $query->where('role_id',4)->orWhere('role_id',3)->orWhere('role_id',1);
        })->whereNull('parent_id')->pluck('id')->toArray();
        $query = Customer::query();
        $customers = Customer::where(function($query) use ($ids){
          $query->whereIn('primary_sale_id',$ids)->orWhereIn('secondary_sale_id',$ids);
        })->where('status',1)->get();
        $sales_coordinator_customers_count = Customer::where(function($query) use ($ids){
          $query->whereIn('primary_sale_id',$ids)->orWhereIn('secondary_sale_id',$ids);
        })->where('status',1)->orderBy('reference_name')->count();
      }
      $sales_persons = User::where('status',1)->whereNull('parent_id')->where('role_id',3)->orderBy('name')->get();
      $total_sales = 0;
      $customer_categories = CustomerCategory::where('is_deleted',0)->get();

      //For admin total_invoice
      $month = date('m');
      $day = '01';
      $year = date('Y');

      $start_of_month = $year . '-' . $month . '-' . $day;
      $today = date('Y-m-d');
      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      {
       $admin_orders = Order::select('id','total_amount')->where('primary_status',31)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();
      }
      else if(Auth::user()->role_id == 3)
      {
        $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
        $admin_orders = Order::select('id','total_amount')->where('primary_status',31)->whereIn('customer_id',$all_customer_ids)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();

        $company_orders = Order::select('id','total_amount')->where('primary_status',31)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();

        $company_total_sales = 0;
        foreach ($company_orders as  $sales_order)
        {
          $company_total_sales += $sales_order->total_amount;
        }
      }
      else if(Auth::user()->role_id == 4)
      {
        $admin_orders = Order::select('id','total_amount')->where('primary_status',31)->whereIn('user_id',$ids)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();
      }
      $admin_total_sales = 0;
      foreach ($admin_orders as  $sales_order)
      {
        $admin_total_sales += $sales_order->total_amount;
      }

      //for admin total draft
      $month = date('m');
      $day = '01';
      $year = date('Y');

      $start_of_month = $year . '-' . $month . '-' . $day;
      $today = date('Y-m-d');
      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      {
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();
      }
      else if (Auth::user()->role_id == 3)
      {
        $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('customer_id',$all_customer_ids)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();
      }
      else
      {
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();
      }
      if(Auth::user()->role_id == 7)
      {
        $warehouse_id = Auth::user()->warehouse_id;
        $ids = User::select('id')->where('role_id',3)->orWhere('role_id',4)->orWhere('role_id',1)->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->get();
        $credit_notes_total = Order::where('primary_status',25)->where('status',27)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
        $debit_notes_total = Order::where('primary_status',28)->where('status',30)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
       }
        $admin_total_sales_draft = 0;
      foreach ($admin_orders_draft as  $sales_order)
      {
        $admin_total_sales_draft += $sales_order->total_amount;
      }

      return $this->render('sales.home.others_dashboard',compact('customers','sales_persons','total_sales','totalCustomers','salesCustomers','salesQuotations','sales_coordinator_customers_count','customer_categories','admin_total_sales','admin_total_sales_draft','credit_notes_total','debit_notes_total','company_total_sales','quotation_statuses'));
    }

    public function completeProfile()
    {
      $check_profile_completed = UserDetail::where('user_id',Auth::user()->id)->count();
      if($check_profile_completed > 0)
      {
        return redirect()->back();
      }
      $countries = Country::get();
      return $this->render('sales.home.profile-complete', compact('countries'));
    }

    public function completeProfileProcess(Request $request){
      $validator = $request->validate([
        'name' => 'required',
        'company' => 'required',
        'address' => 'required',
        'country' =>'required',
        'state' =>'required',
        'city' =>'required',
        'zip_code' =>'required',
        'phone_number' =>'required',
        //'image' =>'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
      ]);

      $user_detail = new UserDetail;
      $user_detail->user_id = Auth::user()->id;
      $user_detail->company_name = $request['company'];
      $user_detail->address = $request['address'];
      $user_detail->country_id = $request['country'];
      $user_detail->state_id = $request['state'];
      $user_detail->city_name = $request['city'];
      $user_detail->zip_code = $request['zip_code'];
      $user_detail->phone_no = $request['phone_number'];
      $user_detail->save();
      return response()->json([
        "success"=>true
      ]);
    }

    public function changePassword()
    {
      return view('sales.password-management.index');
    }

    public function checkOldPassword(Request $request)
    {
      $hashedPassword=Auth::user()->password;
      $old_password =  $request->old_password;
      if (Hash::check($old_password, $hashedPassword))
      {
        $error = false;
      }
      else
      {
        $error = true;
      }
      return response()->json([
        "error"=>$error
      ]);
    }

    public function changePasswordProcess(Request $request)
    {
      $validator = $request->validate([
        'old_password' => 'required',
        'new_password' => 'required',
        'confirm_new_password'  => 'required',
      ]);
      $user= User::where('id',Auth::user()->id)->first();
      if($user)
      {

        $hashedPassword=Auth::user()->password;
        $old_password =  $request['old_password'];
        if (Hash::check($old_password, $hashedPassword))
        {
          if($request['new_password'] == $request['confirm_new_password'])
          {
            $user->password=bcrypt($request['new_password']);
          }
        }
        $user->save();
      }
      return response()->json(['success'=>true]);
    }

    public function profile()
    {
      $user_states=[];
      $countries = Country::orderBy('name','ASC')->get();
      $user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
      if($user_detail)
      {
        $user_states= State::where('country_id',$user_detail->country_id)->get();
      }
      return view('sales.profile-setting.index',['countries'=>$countries,'user_detail'=>$user_detail,'user_states'=>$user_states]);
    }

    public function updateProfile(Request $request)
    {
      $validator = $request->validate([
        'name' => 'required',
        'company' => 'required',
        'address' => 'required',
        'country' =>'required',
        'state' =>'required',
        'city' =>'required',
        'zip_code' =>'required',
        'phone_number' =>'required',
        'image' =>'mimes:jpeg,jpg,png,gif|required|max:10000',
      ]);

      $error = false;
      $user = User::where('id',Auth::user()->id)->first();
      if($user)
      {
        $user->name=$request['name'];
        $user->save();

        $user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
        if($user_detail)
        {
          $user_detail->address     = $request['address'];
          $user_detail->country_id  = $request['country'];
          $user_detail->state_id    = $request['state'];
          $user_detail->city_name   = $request['city'];
          $user_detail->zip_code    = $request['zip_code'];
          $user_detail->phone_no    = $request['phone_number'];
          $user_detail->company_name  = $request['company'];

          if($request->hasFile('image') && $request->image->isValid())
          {
            $fileNameWithExt = $request->file('image')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$extension;
            $path = $request->file('image')->move('public/uploads/sales/images/',$fileNameToStore);
            $user_detail->image = $fileNameToStore;
          }

          $user_detail->save();
          return response()->json([
            "error"=>$error
          ]);
        }
      }
    }

    public function exportDraftPi($id, $page_type,$column_name,$default_sort)
    {
      $orders_array = explode(",",$id);
      $id = $orders_array[0];

      foreach ($orders_array as $id) {
        $print_history             = new PrintHistory;
        $print_history->order_id    = $id;
        $print_history->user_id    = Auth::user()->id;
        $print_history->print_type = 'pick-instruction';
        $print_history->page_type = $page_type;
        $print_history->save();
      }

      $ordersProducts = OrderProduct::with('get_order')->where('order_id', $id)->whereNotNull('product_id');


      if ($column_name == 'reference_code' && $default_sort !== 'id_sort')
      {
        $ordersProducts = $ordersProducts->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
      }
      elseif($column_name == 'short_desc' && $default_sort != 'id_sort')
      {
        $ordersProducts = $ordersProducts->orderBy('short_desc', $default_sort)->get();
      }
      elseif($column_name == 'supply_from' && $default_sort !== 'id_sort')
      {
        $ordersProducts = $ordersProducts->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
      }
      elseif($column_name == 'type_id' && $default_sort !== 'id_sort')
      {
        $ordersProducts = $ordersProducts->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
      }
      elseif($column_name == 'brand' && $default_sort !== 'id_sort')
      {
        // dd('works');
        $ordersProducts = $ordersProducts->orderBy($column_name, $default_sort)->get();
      }
      else{
        $ordersProducts = $ordersProducts->orderBy('id', 'ASC')->get();
      }

      // if ($default_sort != 'id_sort') {
      //   $ordersProducts = $ordersProducts->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->select('order_products.*')->get();
      // }
      // else{
      //   $ordersProducts = $ordersProducts->orderBy('id', 'ASC')->get();
      // }

      $order = Order::find($id);
      $comment = OrderNote::select('note')->where('order_id',$order->id)->where('type','warehouse')->first();

      $comment_to_customer = OrderNote::select('note')->where('order_id',$order->id)->where('type','customer')->first();
      $cust_id = $order->customer_id;
      $customer = Customer::select('reference_number','company','first_name','last_name','reference_name')->where('id',$cust_id)->first();
      $config = Configuration::first();
      if ($config->server == 'lucilla') {
        $pdf = PDF::loadView('warehouse.pick-instruction.lucila-invoice',compact('ordersProducts','order','id','customer','comment','comment_to_customer','orders_array','default_sort','column_name'))->setPaper('letter', 'portrait');
      }
      else{
        $pdf = PDF::loadView('warehouse.pick-instruction.invoice',compact('ordersProducts','order','id','customer','comment','comment_to_customer','orders_array','default_sort','column_name'))->setPaper('letter', 'landscape');
      }
      $makePdfName='Pick Instruction-'.$id.'';
      return $pdf->stream(
        $makePdfName.'.pdf',
        array(
          'Attachment' => 0
        )
      );
      return $pdf->download($makePdfName.'.pdf');
    }

    public function getWidgetValues(Request $request)
    {
      $admin_orders = 0;
      $admin_total_sales = 0;
      $total_gross_profit = 0;
      $total_gross_profit_count = 0;
      $company_total_sales = 0;
      $salesCustomers = 0;
      $sales_coordinator_customers_count = 0;
      $salesCoordinateQuotations = 0;
      $salesCoordinateDraftInvoices = 0;
      $salesInvoice = 0;
      $salesDraft = 0;
      $Invoice1 = 0;
      $salesCoordinateInvoices = 0;
      $salesCoordinateInvoicesAmount = 0;
      $total_amount_of_quotation = 0;
      $salesQuotations = 0;
      $quotation = 0;
      $admin_total_sales_draft = 0;
      $total_number_of_draft_invoices = 0;
      $total_amount_of_overdue_invoices_count = 0;
      $total_amount_overdue = 0;
      $totalCustomers = 0;
      $month = date('m');
      $day = '01';
      $year = date('Y');
      $warehouse_id = Auth::user()->warehouse_id;

      $start_of_month = $year . '-' . $month . '-' . $day;
      $today = date('Y-m-d');
      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
      {
        $quotation = Order::where('dont_show',0)->where('primary_status', 1)->count('id');
      }
      else
      {
        $quotation = Order::whereHas('user',function($q){
                            $q->where('ecommerce_order',1);
                            })->where('primary_status', 1)->count('id');
      }
      $total_amount_of_quotation = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',1)->sum('order_products.total_price');
      $total_amount_of_quotation = number_format($total_amount_of_quotation,2,'.',',');
      $total_number_of_draft_invoices = Order::select('primary_status')->where('primary_status',2)->count();
      $today_date = date('Y-m-d H:i:s');
      $total_amount_of_overdue_invoices_count = Order::select('primary_status','payment_due_date')->where('primary_status',3)->where('payment_due_date','<',$today_date)->count();
      $total_amount_of_overdue_invoices = Order::select('total_amount')->where('primary_status',3)->where('payment_due_date','<',$today_date)->sum('total_amount');

      $total_paid_amount_of_overdue_invoices = Order::select('primary_status','total_paid','payment_due_date')->where('primary_status',3)->where('payment_due_date','<',$today_date)->sum('total_paid');

      $total_amount_overdue = $total_amount_of_overdue_invoices - $total_paid_amount_of_overdue_invoices;
      $ids = User::select('id')->where('role_id',3)->orWhere('role_id',4)->orWhere('role_id',1)->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();

      if(Auth::user()->role_id == 3)
      {

       $admin_total_sales_draft = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.customer_id','orders.user_id')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',2)->whereNotIn('orders.status',[34])->where('orders.user_id',Auth::user()->id)->sum('order_products.total_price');
       $admin_orders = Order::select('id','total_amount')->where('primary_status',3)->where('user_id',Auth::user()->id)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count();
       $admin_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.customer_id','orders.converted_to_invoice_on','orders.user_id')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->where('orders.user_id',Auth::user()->id)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');
       $company_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.dont_show','orders.converted_to_invoice_on')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->where('orders.dont_show',0)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');
       $salesCustomers = Customer::where(function($query){
            $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
          })->where('status',1)->count();
       $salesQuotations = Order::select('id')->where('user_id',Auth::user()->id)->where('primary_status', 1)->count();
       $salesDraft = Order::where('user_id',Auth::user()->id)->where('primary_status', 2)->count('id');
       $salesInvoice = Order::where('user_id', Auth::user()->id)->where('primary_status', 3)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count('id');

      }
      else if (Auth::user()->role_id == 4)
      {
        $admin_total_sales_draft = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.dont_show')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',2)->whereNotIn('orders.status',[34])->where('orders.dont_show',0)->whereIn('orders.user_id',$ids)->sum('order_products.total_price');
        $admin_orders = Order::select('id','total_amount')->where('dont_show',0)->where('primary_status',3)->whereIn('user_id',$ids)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count();
        $admin_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.delivery_request_date')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->whereIn('orders.user_id',$ids)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');
        $warehouse_id = Auth::user()->warehouse_id;
          $ids = User::select('id')->where('warehouse_id',$warehouse_id)->where('role_id',3)->pluck('id')->toArray();
        $sales_coordinator_customers_count = Customer::where(function($query) use ($ids){
            $query->whereIn('primary_sale_id',$ids)->orWhereIn('secondary_sale_id',$ids);
          })->where('status',1)->orderBy('reference_name')->count();
        $salesCoordinateInvoicesQuery =  Order::whereHas('customer',function($q){
            $q->whereHas('primary_sale_person',function($qq){
                $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
            })
            ->orWhereHas('secondary_sale_person',function($qq){
                $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
            });
        })->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->where('primary_status', 3);

        $salesCoordinateInvoices = (clone $salesCoordinateInvoicesQuery)->count('id');
        $salesCoordinateInvoicesAmount = (clone $salesCoordinateInvoicesQuery)->join('order_products','order_products.order_id','=','orders.id')->sum('order_products.total_price');
      }
      else
      {
        $admin_total_sales_draft = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.dont_show')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',2)->whereNotIn('orders.status',[34])->where('dont_show',0)->sum('order_products.total_price');
      }

      if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
      {
        $admin_orders = Order::select('id')->where('primary_status',3)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count('id');
        $admin_total_sales = Order::select('orders.primary_status','order_products.total_price','order_products.order_id','orders.id','orders.user_id','orders.dont_show','orders.converted_to_invoice_on')->join('order_products','order_products.order_id','=','orders.id')->where('orders.primary_status',3)->where('orders.dont_show',0)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->sum('order_products.total_price');

          $outstanding_orders = Order::select('id','total_amount','total_paid')->where('primary_status',3)->where('status','!=',24)->get();
          $total_gross_profit_count = $outstanding_orders->count();

          $total_salee = $outstanding_orders->sum('total_amount');
          $total_receii = $outstanding_orders->sum('total_paid');
          $total_gross_profit = $total_salee - $total_receii;

          $Draft1 = Order::select('id')->where('dont_show',0)->where('primary_status', 2)->count('id');

          $salesDraft = $Draft1;
          $Invoice1 = Order::where('dont_show',0)->where('primary_status', 3)->count('id');

      }
      $totalCustomers = Customer::select('id')->where('status',1)->count();
      if(Auth::user()->role_id == 9 || Auth::user()->role_id == 4)
      {
        $sales2 =  Order::whereHas('customer',function($q){
              $q->whereHas('primary_sale_person',function($qq){
                  $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
              })
              ->orWhereHas('secondary_sale_person',function($qq){
                  $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
              });
          })->where('primary_status', 1)->count('id');

        $salesCoordinateQuotations = $sales2;

        $salesCoordinateDraftInvoices =  Order::whereHas('customer',function($q){
            $q->whereHas('primary_sale_person',function($qq){
                $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
            })
            ->orWhereHas('secondary_sale_person',function($qq){
                $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
            });
        })->where('primary_status', 2)->whereNotIn('status',[34])->count('id');
      }

      return response()->json(['success' => true,'quotation' => $quotation,'total_amount_of_quotation' => $total_amount_of_quotation,'total_number_of_draft_invoices' => number_format($total_number_of_draft_invoices,0,'.',','),'admin_total_sales_draft' => number_format($admin_total_sales_draft,2,'.',','),'total_number_of_invoices' => number_format($admin_orders,0,'.',','),'admin_total_sales' => number_format($admin_total_sales,2,'.',','),'total_gross_profit_count' => number_format($total_gross_profit_count,0), 'total_gross_profit' => number_format($total_gross_profit,2,'.',','),'total_amount_of_overdue_invoices_count' => number_format($total_amount_of_overdue_invoices_count,0),'total_amount_overdue' => number_format($total_amount_overdue,2,'.',','),'company_total_sales' => number_format($company_total_sales,2,'.',','),'salesCustomers' => $salesCustomers,'totalCustomers' => $totalCustomers,'sales_coordinator_customers_count' => $sales_coordinator_customers_count,'salesQuotations' => $salesQuotations,'salesCoordinateQuotations' => $salesCoordinateQuotations,'salesDraft' => number_format($salesDraft,0),'salesCoordinateDraftInvoices' => $salesCoordinateDraftInvoices,'Invoice1' => number_format($Invoice1,0),'salesInvoice' => number_format($salesInvoice,0),'salesCoordinateInvoices' => number_format($salesCoordinateInvoices,0),'salesCoordinateInvoicesAmount' => number_format($salesCoordinateInvoicesAmount,2,'.',',')]);
    }
}
