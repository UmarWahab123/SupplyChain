<?php

namespace App\Http\Controllers\Accounting;

use App\General;
use App\Helpers\Datatables\AccountingDashboardDatatable;
use App\Http\Controllers\Controller;
use App\Models\Common\Company;
use App\Models\Common\Configuration;
use App\Models\Common\Country;
use App\Models\Common\Courier;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderAttachment;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Sales\Customer;
use App\User;
use App\Variable;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

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
        $current_version='4.2.2';
        // current controller constructor
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;
        $extra_space_for_select2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data,'extra_space' => $extra_space_for_select2]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDashboard()
    {
        $warehouse_id = Auth::user()->warehouse_id;
        $customer_categories = CustomerCategory::where('is_deleted',0)->get();
        $ids = User::select('id')->where('role_id',3)->orWhere('role_id',4)->orWhere('role_id',1)->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->get();
        $statuses = Status::where('parent_id',25)->get();


        $admin_total_sales_draft = 0;
        foreach ($admin_orders_draft as  $sales_order)
        {
            $admin_total_sales_draft += $sales_order->total_amount;
            // $admin_total_sales_draft -= $sales_order->discount;
        }

        $customers = Customer::where('status',1)->get();
        $sales_persons = User::where('status',1)->where('role_id',3)->whereNull('parent_id')->orderBy('name')->get();
        $month = date('m');
                      $day = '01';
                      $year = date('Y');

                      $start_of_month = $year . '-' . $month . '-' . $day;
                      $today = date('Y-m-d');
        $credit_notes_total = Order::where('primary_status',25)->where('status',27)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
        $debit_notes_total = Order::where('primary_status',28)->where('status',30)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');

          //For admin total_invoice
                      $month = date('m');
                      $day = '01';
                      $year = date('Y');

                      $start_of_month = $year . '-' . $month . '-' . $day;
                      $today = date('Y-m-d');
                      $admin_orders = Order::select('id','total_amount')->where('primary_status',3)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();


                      $admin_total_sales = 0;
                    foreach ($admin_orders as  $sales_order)
                    {
                        $admin_total_sales += $sales_order->total_amount;
                        // $admin_total_sales -= $sales_order->discount;
                    }
                    // dd($admin_total_sales);
        // dd($credit_notes_total);
        $suppliers = Supplier::select('id', 'reference_name')->get();
        return $this->render('accounting.dashboard.index',compact('admin_total_sales_draft','customers','customer_categories','sales_persons','credit_notes_total','debit_notes_total','admin_total_sales','statuses', 'suppliers'));
    }


    public function createCreditNote()
    {
        // dd('here');
        $quot_status     = Status::where('id',25)->first();

        $company_prefix  = @Auth::user()->getCompany->prefix;
        $counter_formula = $quot_status->counter_formula;
        $counter_formula = explode('-',$counter_formula);
        $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
        $date = Carbon::now();
        $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
                // $date = 2005;
        $quot_status_prefix    = $quot_status->prefix.$company_prefix;

        $c_p_ref = Order::where('status_prefix',$quot_status_prefix)->where('ref_id','LIKE',"$date%")->orderby('id','DESC')->first();
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if($str == NULL)
        {
          $onlyIncrementGet = 0;
        }
        $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
        $system_gen_no = $date.$system_gen_no;
        // dd($system_gen_no);
        $credit_note = Order::create(['created_by'=>Auth::user()->id,'user_id'=>Auth::user()->id,'primary_status'=>25,'status'=>26,'ref_id'=>$system_gen_no,'status_prefix'=>$quot_status_prefix]);
        return redirect()->route("get-credit-note-detail",$credit_note->id);
        // dd('done');
    }

    public function createDebitNote()
    {
        // dd('here');
        $quot_status     = Status::where('id',28)->first();

        $company_prefix  = @Auth::user()->getCompany->prefix;
        $counter_formula = $quot_status->counter_formula;
        $counter_formula = explode('-',$counter_formula);
        $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
        $date = Carbon::now();
        $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
                // $date = 2005;
        $quot_status_prefix    = $quot_status->prefix.$company_prefix;

        $c_p_ref = Order::where('status_prefix',$quot_status_prefix)->where('ref_id','LIKE',"$date%")->orderby('id','DESC')->first();
        // dd($c_p_ref);
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if($str == NULL)
        {
          $onlyIncrementGet = 0;
        }
        $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
        $system_gen_no = $date.$system_gen_no;
        // dd($system_gen_no);
        $debit_note = Order::create(['created_by'=>Auth::user()->id,'user_id'=>Auth::user()->id,'primary_status'=>28,'status'=>29,'ref_id'=>$system_gen_no,'status_prefix'=>$quot_status_prefix]);
        return redirect()->route("get-debit-note-detail",$debit_note->id);
        // dd('done');
    }

    public function getCreditNoteDetail($id){
        // dd($id);

      $states = State::select('id','name')->orderby('name', 'ASC')->where('country_id',217)->get();

      $billing_address = null;
      $shipping_address = null;
      $order = Order::select(['id','user_id','status_prefix','shipping','discount','status','memo','credit_note_date','customer_id','created_at','ref_id','ref_prefix'])->with(['user:id,user_name','customer:id,reference_name,address_line_1,address_line_2,phone,email,city,postalcode,primary_sale_id','customer.primary_sale_person','statuses'])->where('id',$id)->first();
      $order_product=OrderProduct::query();

    //   dd($order);
      $payment_term = PaymentTerm::all();
      $company_info = Company::where('id',$order->user->company_id)->first();
      if($order->billing_address_id != null){
      $billing_address = CustomerBillingDetail::where('id',$order->billing_address_id)->first();
      }
      if($order->shipping_address_id){
      $shipping_address = CustomerBillingDetail::where('id',$order->shipping_address_id)->first();
      }
      $total_products = $order->order_products->count('id');
      $vat = 0 ;
      $sub_total = 0 ;
      $sub_total_w_w = 0;
      $query = $order_product->where('order_id',$id)->get();
      foreach ($query as  $value) {
          $sub_total += $value->total_price;
          $sub_total_w_w += $value->total_price_with_vat;

          // $vat += $value->total_price_with_vat-$value->total_price;
          // $vat += @$value->total_price * (@$value->vat / 100);

          $vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);

      }
      $check_inquiry = $order_product->where('order_id',$id)->where('is_billed','Inquiry')->count();
      // dd($check_inquiry);
      $grand_total = ($sub_total_w_w)-($order->discount)+($order->shipping);
      $status_history = OrderStatusHistory::with('get_user')->where('order_id',$id)->get();
      $checkDocs = OrderAttachment::where('order_id',$order->id)->get()->count();
      $inv_note = OrderNote::where('order_id', $order->id)->where('type','customer')->first();
      $warehouse_note = OrderNote::where('order_id', $order->id)->where('type','warehouse')->first();

      $warehouse_id = Auth::user()->warehouse_id;
        $users = User::select('id')->where('warehouse_id',$warehouse_id)->whereNull('parent_id')->where('role_id',3)->get();
        $query = Customer::query();
      $ids = array();
        foreach ($users as $user) {
          // $query = $query->where('status', 1)->where('user_id',$user->id)->orderBy('id', 'DESC');
          // dd($query->get());
          array_push($ids, $user->id);
        }
        $sales_coordinator_customers = $query->where('status', 1)->whereIn('primary_sale_id',$ids)->orderBy('id', 'DESC')->get();
      $admin_customers = Customer::where('status',1)->get();
      $customers     = Customer::where(function($query){
          $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
        })->where('status',1)->get();


      return view('accounting.notes.credit-note-detail', compact('order','company_info','total_products','sub_total','grand_total','status_history','vat', 'id','checkDocs','inv_note','billing_address','shipping_address','states','warehouse_note','check_inquiry','payment_term','customers','admin_customers','sales_coordinator_customers'));
    }

    public function getDebitNoteDetail($id){
        // dd($id);

      $states = State::select('id','name')->orderby('name', 'ASC')->where('country_id',217)->get();

      $billing_address = null;
      $shipping_address = null;
      $order = Order::with('user')->where('id',$id)->first();
    //   $payment_term = PaymentTerm::all();
      $company_info = Company::where('id',$order->user->company_id)->first();
      if($order->billing_address_id != null){
      $billing_address = CustomerBillingDetail::where('id',$order->billing_address_id)->first();
      }
      if($order->shipping_address_id){
      $shipping_address = CustomerBillingDetail::where('id',$order->shipping_address_id)->first();
      }
      $total_products = $order->order_products->count('id');
      $vat = 0 ;
      $sub_total = 0 ;
      $sub_total_w_w = 0;
      $order_product=OrderProduct::where('order_id',$id)->get();
      $query = $order_product;
      foreach ($query as  $value) {

          $sub_total += $value->total_price;
          $sub_total_w_w += $value->total_price_with_vat;
          // $vat += $value->total_price_with_vat-$value->total_price;
          // $vat += @$value->total_price * (@$value->vat / 100);
          $vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);

      }
      $check_inquiry = $order_product->where('is_billed','Inquiry')->count();
      // dd($check_inquiry);
      $grand_total = ($sub_total_w_w)-($order->discount)+($order->shipping);
      $status_history = OrderStatusHistory::with('get_user')->where('order_id',$id)->get();
      $checkDocs = OrderAttachment::where('order_id',$order->id)->get()->count();
      $order_note=OrderNote::where('order_id', $order->id)->get();
      $inv_note = $order_note->where('type','customer')->first();
      $warehouse_note =$order_note->where('type','warehouse')->first();

      $warehouse_id = Auth::user()->warehouse_id;
        $users = User::select('id')->where('warehouse_id',$warehouse_id)->whereNull('parent_id')->where('role_id',3)->get();
        $query = Customer::query();
      $ids = array();
        foreach ($users as $user) {
          // $query = $query->where('status', 1)->where('user_id',$user->id)->orderBy('id', 'DESC');
          // dd($query->get());
          array_push($ids, $user->id);
        }
        $sales_coordinator_customers = $query->where('status', 1)->whereIn('primary_sale_id',$ids)->orderBy('id', 'DESC')->get();
      $admin_customers = Customer::where('status',1)->get();
      $customers     = Customer::where(function($query){
          $query->where('primary_sale_id',Auth::user()->id)->orWhere('secondary_sale_id',Auth::user()->id);
        })->where('status',1)->get();


      return view('accounting.notes.debit-note-detail', compact('order','company_info','total_products','sub_total','grand_total','status_history','vat', 'id','checkDocs','inv_note','billing_address','shipping_address','states','warehouse_note','check_inquiry','payment_term','customers','admin_customers','sales_coordinator_customers'));
    }

    public function completeCreditNote(Request $request){
        // dd($request->all());

        $credit_note = Order::with('order_products')->find($request->inv_id);

        $credit_note->status = 27;
        $credit_note->converted_to_invoice_on = Carbon::now();
        $credit_note->save();
         $products = $credit_note->order_products;

        foreach ($products as $prod) {
            if($prod->is_billed == 'Product')
            {
                $prod->status = 27;
            }
           $prod->save();

           if($prod->return_to_stock == 1){
            $stock = StockManagementIn::where('product_id', $prod->product_id)->where('warehouse_id', auth()->user()->warehouse_id)->whereNull('expiration_date')->orderBy('id', 'desc')->first();
            $shipped = $prod->quantity;
            if($stock){
              $stock_out                   = new StockManagementOut;
              $stock_out->smi_id           = $stock->id;
              $stock_out->order_id         = $prod->order_id;
              $stock_out->order_product_id = $prod->id;
              $stock_out->product_id       = $prod->product_id;
              $stock_out->original_order_id = @$prod->order_id;
              $stock_out->title       = 'Quantity returned from credit note '.@$prod->get_order->status_prefix.''.@$prod->get_order->ref_id;
              $stock_out->quantity_in     = $shipped;
              $stock_out->available_stock = $shipped;
              $stock_out->created_by       = @Auth::user()->id;
              $stock_out->warehouse_id     = auth()->user()->warehouse_id;
              $stock_out->save();
              $stock_out->cost     = $stock_out->cost == null ? ($stock_out->get_product != null ? round($stock_out->get_product->selling_price,3) : null) : $stock_out->cost;
              $stock_out->save();
            }
           }
        }

        return response()->json(['success'=>true]);
    }

     public function completeDebitNote(Request $request){
        // dd($request->all());

        $debit_note = Order::find($request->inv_id);
        // dd($debit_note->order_products);
        $products = $debit_note->order_products;

        $debit_note->status = 30;
        $debit_note->save();

        foreach ($products as $prod) {
            if($prod->is_billed == 'Product')
            {
                $prod->status = 30;
            }
           $prod->save();
        }

        return response()->json(['success'=>true]);
    }

    public function getCreditNotes(Request $request)
    {
      $query = Order::where('primary_status',25);
      Order::doSort($request, $query);

      if($request->dosortby == 25)
      {
        $query->where(function($q){
         $q->where('primary_status', 25);
        });
      }
      else if($request->dosortby == 2)
      {
        $query->where(function($q){
         $q->where('primary_status', 2);
        });
      }
      else if($request->dosortby == 3)
      {
        $query->where(function($q){
         $q->where('primary_status', 3);
        });
      }
      else if($request->dosortby == 26)
      {
        $query->where(function($q){
         $q->where('primary_status', 25)->where('status', 26);
        });
      }
      else if($request->dosortby == 27)
      {
        $query->where(function($q){
         $q->where('primary_status', 25)->where('status', 27);
        });
      }
      else if($request->dosortby == 33)
      {
        $query->where(function($q){
         $q->where('primary_status', 25)->where('status', 33);
        });
      }
      else if($request->dosortby == 7)
      {
        $query->where(function($q){
         $q->where('primary_status', 2)->where('status', 7);
        });
      }
      else if($request->dosortby == 8)
      {
        $query->where(function($q){
         $q->where('primary_status', 2)->where('status', 8);
        });
      }
      else if($request->dosortby == 9)
      {
        $query->where(function($q){
         $q->where('primary_status', 2)->where('status', 9);
        });
      }
      else if($request->dosortby == 10)
      {
        $query->where(function($q){
         $q->where('primary_status', 2)->where('status', 10);
        });
      }
      else if($request->dosortby == 11)
      {
        $query->where(function($q){
         $q->where('primary_status', 3)->where('status', 11);
        });
      }
      else if($request->dosortby == 24)
      {
        $query->where(function($q){
         $q->where('primary_status', 3)->where('status', 24);
        });
      }
      else if($request->dosortby == 31)
      {
        $query->where(function($q){
         $q->where('primary_status', 25)->where('status', 31);
        });
      }
      if($request->selecting_customer_group != null)
      {
        $id_split = explode('-', $request->selecting_customer_group);
        if ($id_split[0] == 'cat') {
          $query->whereHas('customer',function($q) use ($id_split){
            $q->where('category_id',$id_split[1]);
          });
        }
        else{
          $query->where('customer_id', $id_split[1]);
        }
      }
      if($request->selecting_sale != null)
      {
        $query->whereIn('customer_id',User::where('id',$request->selecting_sale)->first()->customer->pluck('id'));
      }
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('orders.created_at', '>=', $date);
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('orders.created_at', '<=', $date);
      }
      if(@$request->is_paid == 11 || @$request->is_paid == 24)
      {
        $query->where('orders.status',@$request->is_paid);
      }
      if($request->dosortby == 3)
      {
        $query->orderBy('converted_to_invoice_on','DESC');
      }
      $query = $query->with('customer.primary_sale_person', 'statuses', 'order_products');

      $dt =  DataTables::of($query);
      $add_columns = ['total_amount', 'ref_id', 'sales_person', 'status', 'memo', 'delivery_date', 'customer_ref_no', 'customer', 'action'];
      foreach ($add_columns as $column) {
        $dt->addColumn($column, function($item) use($column) {
            return AccountingDashboardDatatable::returnAddColumn($column, $item);
        });
      }

      $filter_columns = ['ref_id', 'sales_person', 'customer_ref_no', 'customer'];
      foreach ($filter_columns as $column) {
        $dt->filterColumn($column, function ($item, $keyword) use ($column) {
            return AccountingDashboardDatatable::returnFilterColumn($column, $item, $keyword);
        });
      }

        $dt->rawColumns(['action','ref_id','sales_person', 'customer','status','customer_ref_no']);
        $dt->with('post',$query->sum('total_amount'));
        return $dt->make(true);
    }

    public function debitNotesDashboard()
    {
      $warehouse_id = Auth::user()->warehouse_id;
        $customer_categories = CustomerCategory::where('is_deleted',0)->get();
        $ids = User::select('id')->where('role_id',3)->orWhere('role_id',4)->orWhere('role_id',1)->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->get();

        $statuses = Status::where('parent_id',28)->get();
        $admin_total_sales_draft = 0;
        foreach ($admin_orders_draft as  $sales_order)
        {
            $admin_total_sales_draft += $sales_order->total_amount;
            // $admin_total_sales_draft -= $sales_order->discount;
        }

        $customers = Customer::where('status',1)->get();
        $sales_persons = User::where('status',1)->where('role_id',3)->whereNull('parent_id')->orderBy('name')->get();
        $month = date('m');
                      $day = '01';
                      $year = date('Y');

                      $start_of_month = $year . '-' . $month . '-' . $day;
                      $today = date('Y-m-d');
        $credit_notes_total = Order::where('primary_status',25)->where('status',27)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
        $debit_notes_total = Order::where('primary_status',28)->where('status',30)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
        // dd($credit_notes_total);
        //For admin total_invoice
                      $month = date('m');
                      $day = '01';
                      $year = date('Y');

                      $start_of_month = $year . '-' . $month . '-' . $day;
                      $today = date('Y-m-d');
                      $admin_orders = Order::select('id','total_amount')->where('primary_status',3)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();


                      $admin_total_sales = 0;
                    foreach ($admin_orders as  $sales_order)
                    {
                        $admin_total_sales += $sales_order->total_amount;
                        // $admin_total_sales -= $sales_order->discount;
                    }
        $suppliers = Supplier::select('id', 'reference_name')->get();
        return $this->render('accounting.dashboard.debit-notes-dashboard',compact('admin_total_sales_draft','customers','customer_categories','sales_persons','credit_notes_total','debit_notes_total','admin_total_sales','statuses', 'suppliers'));
    }

    public function getDebitNotes(Request $request)
    {
      // dd($request->all());
      $query = Order::where('primary_status',28);
      // dd($query);


      Order::doSort($request, $query);

      if($request->dosortby == 1)
      {
        $query->where(function($q){
         $q->where('primary_status', 28);
        });
      }

      else if($request->dosortby == 29)
      {
        $query->where(function($q){
         $q->where('primary_status', 28)->where('status', 29);
        });
      }
      else if($request->dosortby == 30)
      {
        $query->where(function($q){
         $q->where('primary_status', 28)->where('status', 30);
        });
      }

      // if($request->selecting_customer != null)
      // {
      //   $query->where('customer_id', $request->selecting_customer);
      // }
       if($request->selecting_customer_group != null)
      {
        $id_split = explode('-', $request->selecting_customer_group);
        if ($id_split[0] == 'cat') {
          $query->whereHas('customer',function($q) use ($id_split){
            $q->where('category_id',$id_split[1]);
          });
        }
        else{
          $query->where('customer_id', $id_split[1]);
        }
      }
      if($request->selecting_sale != null)
      {
        // $query->where('user_id', $request->selecting_sale);
        $query->whereIn('customer_id',User::where('id',$request->selecting_sale)->first()->customer->pluck('id'));
      }
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('orders.created_at', '>=', $date);
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('orders.created_at', '<=', $date);
      }
      if(@$request->is_paid == 11 || @$request->is_paid == 24)
      {
        $query->where('orders.status',@$request->is_paid);
      }
      if($request->dosortby == 3)
      {
        $query->orderBy('converted_to_invoice_on','DESC');
        // dd($query->get());
      }
      else
      {
        // $query->orderBy('id','DESC');
      }
      // if(@$request->type == 'invoice'){
      //   $query->where('delivery_request_date', '>', Carbon::now()->subDays(30));
      // }
      $query = $query->with('customer.primary_sale_person', 'statuses', 'order_products');
        return Datatables::of($query)

            // ->addColumn('checkbox', function ($item) {
            //   // dd($item);

            //         $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
            //                         <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="quot_'.$item->id.'">
            //                         <label class="custom-control-label" for="quot_'.$item->id.'"></label>
            //                     </div>';
            //         return $html_string;
            //     })
            // ->addColumn('inv_no', function($item) {
            //   // dd($item->customer->primary_sale_person->get_warehouse->order_short_code);
            //   $ref_no = @$item->status_prefix.$item->ref_id;
            //   $html_string = '<a href="'.route('get-completed-invoices-details', ['id' => $item->id]).'" title="View Detail"><b>'.$ref_no.'</b></a>';
            //   return $html_string;
            // })

            ->addColumn('customer', function ($item) {
              if($item->customer_id != null)
              {
                if($item->customer['reference_name'] != null)
                {
                  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'.$item->customer['reference_name'].'</a>';
                }
                else
                {
                  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
                }
              }
              else{
                $html_string = 'N.A';
              }

              return $html_string;
            })

            ->filterColumn('customer', function( $query, $keyword ) {
             $query->whereHas('customer', function($q) use($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
            })

            ->addColumn('customer_ref_no',function($item){
              $ref_no = @$item->customer !== null ? @$item->customer->reference_number : '--';
              $html_string ='<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.@$ref_no.'</b></a>';
              return $html_string;

            })
             ->filterColumn('customer_ref_no', function( $query, $keyword ) {
             $query->whereHas('customer', function($q) use($keyword){
                    $q->where('reference_number','LIKE', "%$keyword%");
                });
            })


            // ->addColumn('target_ship_date',function($item){
            //   return @$item->target_ship_date != null ?  Carbon::parse($item->target_ship_date)->format('d/m/Y'): '--';
            // })
            ->addColumn('delivery_date',function($item){
              return @$item->created_at != null ?  Carbon::parse($item->created_at)->format('d/m/Y'): '--';
            })

            ->addColumn('memo',function($item){
              return @$item->memo != null ? @$item->memo : '--';
            })

            ->addColumn('status',function($item){
              $html = '<span class="sentverification">'.$item->statuses->title.'</span>';
              return $html;
            })

            // ->addColumn('number_of_products', function($item) {
            //   $html_string = $item->order_products->count();
            //   return $html_string;
            // })

            ->addColumn('sales_person', function($item) {
              return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
            })

            ->filterColumn('sales_person', function( $query, $keyword ) {
              $query->whereHas('customer', function($q) use($keyword){
                $q->whereHas('primary_sale_person', function($q) use($keyword){
                    $q->where('name','LIKE', "%$keyword%");
                });
              });
            },true )

            ->addColumn('ref_id', function($item) {
              // dd($item->customer->primary_sale_person->get_warehouse->order_short_code);
              $ref_no = @$item->status_prefix.$item->ref_id;
                $html_string = '<a href="'.route('get-debit-note-detail', ['id' => $item->id]).'" title="View Order"><b>'.$ref_no.'</b></a>';
              return $html_string;
              })

            ->filterColumn('ref_id', function( $query, $keyword ) {
              $result = $keyword;
              if (strstr($result,'-'))
              {
                $query = $query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $query = $query->orWhere('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
              }

            })

            // ->addColumn('reference_id_vat', function($item) {
            //   return $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
            // })
            // ->addColumn('sub_total_1', function($item) {
            //   return @$item->order_products != null ? @$item->getOrderTotalVat($item->id,0) : '--';
            // })

            // ->addColumn('vat_1', function($item) {
            //   return @$item->order_products != null ? @$item->getOrderTotalVat($item->id,1) : '--';
            // })

            // ->addColumn('reference_id_vat_2', function($item) {
            //   return @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
            // })

            // ->addColumn('sub_total_2', function($item) {
            //   return @$item->order_products != null ? @$item->getOrderTotalVat($item->id,2) : '--';
            // })


            // ->addColumn('invoice_date', function($item) {
            //   return Carbon::parse(@$item->updated_at)->format('d/m/Y');
            // })

            ->addColumn('total_amount', function($item) {
              return number_format($item->total_amount,2,'.',',');
            })

            ->addColumn('action', function ($item) {
              $html_string = '';
              $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
              return $html_string;
            })

            // ->rawColumns(['action','inv_no','ref_id','sales_person', 'customer', 'number_of_products','status','customer_ref_no','checkbox','reference_id_vat'])

            ->rawColumns(['action','ref_id','sales_person', 'customer','status','customer_ref_no'])
            ->with('post',$query->sum('total_amount'))
            ->make(true);
    }

    public function getProductsData(Request $request, $id)
    {
      // dd($request->all());
        $query = OrderProduct::with('product:id,refrence_code','get_order:id,primary_status,status,customer_id','product.units','product.supplier_products','purchase_order_detail','from_warehouse','from_supplier','order_product_note')->where('order_products.order_id', $id);
        $units = Unit::orderBy('title')->get();
        $warehouses = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
        $config = Configuration::first();
        // $SupplierProducts=SupplierProducts::all();
        OrderProduct::doSort($request,$query);
         return Datatables::of($query)

            ->addColumn('action', function ($item) use ($config) {
                $html_string = '';
                if(Auth::user()->role_id == 2)
                {
                  $disable = "disabled";
                }
                else
                {
                  $disable = "";
                }
                if($item->product_id == null && $item->is_billed != "Inquiry" && Auth::user()->role_id != 7)
                {
                  if(@$item->get_order->primary_status !== 2 && @$item->get_order->primary_status !== 3){

                    // dd($item);
                  $html_string = '
                      <a href="javascript:void(0);" class="actionicon viewIcon add-as-product" data-id="' . $item->id . '" title="Add as New Product "><i class="fa fa-envelope"></i></a>';
                }
              }
                // if($item->is_billed == "Inquiry")
                // {
                //   $html_string = '
                //       <a href="javascript:void(0);" class="actionicon viewIcon" title="That product will be show once its completed"><i class="fa fa-info"></i></a>';
                // }
                if($item->status < 8 && Auth::user()->role_id != 7)
                {

                  $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                else if(@$item->get_order->primary_status == 25)
                {
                  if(@$item->get_order->status == 26)
                  {
                    $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                  }
                  else if(@$item->get_order->status == 27)
                  {
                    $html_string .= '--';
                  }
                }
                else if(@$item->get_order->primary_status == 28)
                {
                  if(@$item->get_order->status == 29)
                  {
                    $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                  }
                  else if(@$item->get_order->status == 30)
                  {
                    $html_string .= '--';
                  }
                }
                else
                {
                  $html_string .= '--';
                }

                // functinality for lucilla only
                if(@$config->server == 'lucilla')
                {
                  $checked = $item->return_to_stock == 1 ? 'checked' : '';
                  $html_string .= '<div class="custom-control d-inline-block ml-2">
                  <input class="custom-control-input return_to_stock_check" type="checkbox"
                         id="return_to_stock_check_' . $item->id . '"
                         value="' . $item->id . '" data-id="'.$item->id.'" '.$checked.'>
                  <label class="custom-control-label" for="return_to_stock_check_' . $item->id . '"></label>
                  </div>';
                }
                return $html_string;
            })

            ->addColumn('refrence_code',function($item){
                if($item->product == null )
                {
                  return "N.A";
                }
                else
                {
                  $item->product->refrence_code ? $reference_code = $item->product->refrence_code : $reference_code = "N.A";
                  return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"  ><b>'.$reference_code.'<b></a>';
                }


            })
            ->addColumn('discount',function($item){
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }
              $html = '<span class="'.$class.'" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none" style="width:100%">';
              return $html.' %';
            })

            ->addColumn('description',function($item){
                // if($item->product == null)
                // {
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }
               if($item->short_desc == null)
                {
                    $style = "color:red;";
                }
                else
                {
                    $style = "";
                }
                $html = '<span class="'.$class.'" data-fieldvalue="'.$item->short_desc.'" style="'.@$style.'">'.($item->short_desc != null ? $item->short_desc : "--" ).'</span><input type="text" name="short_desc" value="'.$item->short_desc.'"  class="short_desc form-control input-height d-none" style="width:100%">';
                return $html;
                // }
                // else
                // {
                //   return $item->product->short_desc !== null ? $item->product->short_desc: "--";
                // }
            })

            ->addColumn('brand',function($item){
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  $class = "";
                }
                else
                {
                  $class = "inputDoubleClick";
                }
                  $html = '<span class="'.$class.'" data-fieldvalue="'.$item->brand.'">'.($item->brand != null ? $item->brand : "--" ).'</span><input type="text" name="brand" value="'.$item->brand.'" min="0" class="brand form-control input-height d-none" style="width:100%">';
                  return $html;
            })

            ->addColumn('quantity',function($item)use($units){
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
                $radio = "disabled";
              }
              else
              {
                $class = "inputDoubleClick";
                $radio = "";
              }

              if($item->is_retail == "qty")
              {
                $radio = "disabled";
              }
              else
              {
                $radio = "";
              }
              // Sales unit code shift here
               if($item->product_id !== NULL)
              {
                $sale_unit = $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               if(@$item->product_id === NULL){
                // $html =  '<span class="">'.@$unit.'</span>';
                $html =  '';
               }
               else{
               $html = '<span class="'.$class.'">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';

                foreach ($units as $w)
                {
                  if($item->selling_unit == $w->id)
                  {
                    $html = $html.'<option selected value="'.$w->id.'">'.$w->title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="'.$w->id.'">'.$w->title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Sale Unit">';

                $html .= ' </optgroup></select>';
              }
                $sale_unit = $html;
              }
$html = '';
              // sale unit code ends
              if(@$item->get_order->primary_status == 3){
                $html .= '<span class="" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span> ';
              }else{
              $html .= '<span class="inputDoubleClick" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span>';
              $html .= '<input type="number" name="quantity"  value="'.$item->quantity.'" class="quantity form-control input-height d-none" style="width:100%; border-radius:0px;"> ';
            }
              $html .= @$sale_unit;


          if(@$item->get_order->primary_status !== 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){


              $html .= '
              <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). '>';

              $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';

            // }else if(@$item->quantity != null && @$item->number_of_pieces != null){
            //     $html .= '
            //   <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
            //   $html .= '<input type="checkbox" class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' disabled>';

            //   $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
            //   }
            }
              return $html;
            })

            ->addColumn('number_of_pieces',function($item){

              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
              {
                $class = "";
                $radio = "disabled";
              }
              else
              {
                $class = "inputDoubleClick";
                $radio = "";
              }

              if($item->is_retail == "pieces")
              {
                $radio = "disabled";
              }
              else
              {
                $radio = "";
              }
              if(@$item->get_order->primary_status == 25 || @$item->get_order->primary_status == 28)
              {
                $class = 'inputDoubleClick';
              }
               if(@$item->get_order->primary_status == 3){
                if(@$item->is_billed !== 'Billed')
                {
                  $html = '<span class="" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span> ';
                }
                else
                {
                  $html = 'N.A';
                }
              }else if(@$item->is_billed !== 'Billed'){

                $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span><input type="number" name="number_of_pieces"  value="'.$item->number_of_pieces.'" class="number_of_pieces form-control input-height d-none" style="width:100%; border-radius:0px;">';
                if(@$item->get_order->primary_status != 3 && @$item->is_billed !== 'Billed' && $item->get_order->primary_status !== 25 && $item->get_order->primary_status !== 28){

                $html .= '
                <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). '>';

                $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';


              // else if(@$item->number_of_pieces != null && @$item->quantity != null){
              //     $html .= '
              //   <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              //   $html .= '<input type="checkbox" class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' disabled>';

              //   $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
              // }

            }else if(Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
               $html .= '
                <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). '>';

                $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
            }
          }
          else
          {
            $html = 'N.A';
          }
                return $html;
            })

            ->addColumn('pcs_shipped',function($item){
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
              {
                $class = "";
                $radio = "disabled";
              }
              else
              {
                $class = "inputDoubleClick";
                $radio = "";
              }

              if($item->is_retail == "pieces")
              {
                $radio = "disabled";
              }
              else
              {
                $radio = "";
              }
              if($item->is_billed !== 'Billed'){
               $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->pcs_shipped.'">'.($item->pcs_shipped != null ? $item->pcs_shipped : "--" ).'</span><input type="number" name="pcs_shipped"  value="'.$item->pcs_shipped.'" class="pcs_shipped form-control input-height d-none" style="width:100%; border-radius:0px;"> ';
                  if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7){
                   $html .= '
              <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->pcs_shipped.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). '>';

              $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
            }
          }
          else
          {
            $html = 'N.A';
          }

            return $html;
          })

            ->addColumn('quantity_ship',function($item){
              // if($item->is_retail == "qty")
              // {
              //   $checked = "disabled";
              // }
              // else
              // {
              //   $checked = "";
              // }
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
                $radio = "disabled";
              }
              else
              {
                $class = "inputDoubleClick";
                $radio = "";
              }

              if($item->is_retail == "qty")
              {
                $radio = "disabled";
              }
              else
              {
                $radio = "";
              }
              if(@$item->is_billed !== 'Billed')
              {
              // Sales unit code shift here
               if($item->product_id !== NULL)
              {
                $sale_unit = $item->product && $item->product->sellingUnits ? @$item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               if(@$item->product_id === NULL){
                $html =  '<span class="">'.@$unit.'</span>';
               }else{
               $html =  '<span class="'.$class.'">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
                $units = Unit::orderBy('title')->get();
                foreach ($units as $w)
                {
                  if($item->selling_unit == $w->id)
                  {
                    $html = $html.'<option selected value="'.$w->id.'">'.$w->title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="'.$w->id.'">'.$w->title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Sale Unit">';

                $html .= ' </optgroup></select>';
              }
                $sale_unit = $html;
              }
            }

              // sale unit code ends
                // return 4;
                // return ($item->product->units !== null ? $item->product->units->title : "N.A");
                // return ($item->qty_shipped != null ? @$item->qty_shipped : 0);

                 $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->qty_shipped.'">'.($item->qty_shipped != null ? $item->qty_shipped : "--" ).'</span><input type="number" name="qty_shipped"  value="'.$item->qty_shipped.'" class="qty_shipped form-control input-height d-none" style="width:100%; border-radius:0px;"> ';
                  $html .= @$sale_unit;
                  if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                   $html .= '
              <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->qty_shipped.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). '>';

              $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
            }
              return $html;
            })

            ->addColumn('sell_unit',function($item){
              if($item->product_id !== NULL)
              {
                return $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               $html =  '<span class="inputDoubleClick">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
                $units = Unit::orderBy('title')->get();
                foreach ($units as $w)
                {
                  if($item->selling_unit == $w->id)
                  {
                    $html = $html.'<option selected value="'.$w->id.'">'.$w->title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="'.$w->id.'">'.$w->title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Sale Unit">';

                $html .= ' </optgroup></select>';
                return $html;
              }

                // return $item->product->units ? $item->product->units->title : "N.A";
                // return $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
            })

            ->addColumn('buying_unit',function($item){
                // return 4;
                // return ($item->product->units !== null ? $item->product->units->title : "N.A");
                return ($item->product && $item->product->units !== null ? $item->product->units->title : "N.A");
            })

            // ->addColumn('total_amount',function($item){
            //     // return 4;
            //     // return ($item->product->units !== null ? $item->product->units->title : "N.A");
            //     return ($item->total_price_with_vat !== null ? number_format($item->total_price_with_vat,2, '.', ',') : "--");
            // })

            ->addColumn('total_amount',function($item){
                // return 4;
                // return ($item->product->units !== null ? $item->product->units->title : "N.A");
               $unit_price_with_vat2 =  preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);
                return ($item->total_price_with_vat !== null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $item->total_price_with_vat),2,'.','') : "--");
            })

            ->addColumn('po_quantity',function($item){
              // dd($item);
              // if($item->supplier_id != null && $item->status > 7){
              if($item->status > 7){
                return @$item->purchase_order_detail != null ?  $item->purchase_order_detail->quantity.' '.$item->product->units->title : '--';
              }else{
                return '--';

              }

            })

            ->addColumn('po_number',function($item){
              if($item->status > 7){
                return @$item->purchase_order_detail != null ?  $item->purchase_order_detail->PurchaseOrder->ref_id: '--';
              }else{
                return '--';

              }

            })




            ->addColumn('exp_unit_cost',function($item){
              if($item->exp_unit_cost == null)
              {
                return "N.A";
              }
              else
              {
               $html_string ='<span class="unit-price-'.$item->id.'"">'.number_format($item->exp_unit_cost, 2, '.', ',').'</span>';
              }
              return $html_string;
            })

            ->addColumn('margin',function($item){
              //margin is stored in draftqoutation product and we need to add % or $ based on Percentage or Fixed
              if($item->is_billed != "Product")
              {
                return "N.A";
              }
              if($item->margin == null)
              {
                return "Fixed Price";
              }
              else
              {
                if(is_numeric($item->margin))
                {
                  return $item->margin.'%';
                }
                else
                {
                  return $item->margin;
                }
              }
            })

            ->addColumn('unit_price',function($item){
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }
              $star = '';
              if(is_numeric($item->margin)){
                  $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
                  if($product_margin){
                      $star = '*';
                  }
              }
              $unit_price = number_format($item->unit_price, 2, '.', '');
              $html = '<span class="'.$class.'" data-fieldvalue="'.@$unit_price.'">'.$star.number_format($unit_price, 2, '.', ',').'</span><input type="number" name="unit_price" step="0.01"  value="'.$unit_price.'" class="unit_price form-control input-height d-none" style="width:100%;  border-radius:0px;">';
              return $html;
            })

            ->addColumn('unit_price_with_vat',function($item){
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }

              $unit_price = round($item->unit_price,2);
              $vat = $item->vat;
              $vat_amount = @$unit_price * ( @$vat / 100 );

              if($item->unit_price_with_vat !== null)
              {
                  $unit_price_with_vat = number_format(@$item->unit_price_with_vat,4,'.','');
                  $unit_price_with_vat2 =  preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);
              }
              else
              {
                  $unit_price_with_vat = number_format((@$unit_price+@$vat_amount),4,'.','');
                  $unit_price_with_vat2 = number_format(floor((@$unit_price+@$vat_amount)*100)/100,2);
              }

              // $unit_price = $item->unit_price;
              // $vat = $item->vat;
              // $vat_amount = @$unit_price * ( @$vat / 100 );

                // $unit_price_with_vat = number_format(@$unit_price+@$vat_amount,2,'.',',');
                // $unit_price_with_vat = number_format(@$unit_price+@$vat_amount,2,'.',',');
               // $unit_price_with_vat =  preg_replace('/(\.\d\d).*/', '$1', @$unit_price+@$vat_amount);

                 $html = '<span class="'.$class.'" data-fieldvalue="'.@$unit_price_with_vat.'">'.@$unit_price_with_vat2.'</span><input type="tel" name="unit_price_with_vat" step="0.01"  value="'.$unit_price_with_vat.'" class="unit_price_with_vat form-control input-height d-none" style="width:100%;  border-radius:0px;">';


                 return $html;
            })

            ->addColumn('total_price',function($item){
                if($item->total_price == null){ return $total_price = "N.A"; }
                else{
                  $total_price = $item->total_price;
                }
                $html_string ='<span class="total-price total-price-'.$item->id.'"">'.number_format($total_price, 2, '.', ',').'</span>';
                return $html_string;
            })

            ->addColumn('vat',function($item){
                // return $item->product->vat ? $item->product->vat.'%' : "N.A";
                // return $item->product ? $item->product->vat.'%' ? $item->product->vat.'%' :"N.A" : "N.A";
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  return $item->vat != null ? $item->vat : '--';
                }
                else
                {
                  if($item->unit_price != null)
                  {
                    $clickable = "inputDoubleClick";
                  }
                  else
                  {
                    $clickable = "";
                  }
                  $html = '<span class="'.$clickable.'" data-fieldvalue="'.$item->vat.'">'.($item->vat != null ? $item->vat : '--').'</span><input type="number" name="vat" value="'.@$item->vat.'"  class="vat form-control input-height d-none" style="width:90%"> %';
                  return $html;
                }
            })

            ->addColumn('supply_from',function($item)use($warehouses){
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }

              if($item->product_id == null)
              {
                return "N.A";
              }
              else
              {

                $label = $item->from_warehouse_id != null ? @$item->from_warehouse->warehouse_title : (@$item->from_supplier->reference_name != null ? $item->from_supplier->reference_name : "--");
                $html =  '<span class="'.$class.'">'.@$label.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" name="from_warehouse_id" >';
                $html .= '<option value="" selected disabled>Choose Supply From</option>';
                $html .= '<optgroup label="Select Warehouse">';

                foreach ($warehouses as $w)
                {
                  if($item->from_warehouse_id == $w->id)
                  {
                    $html = $html.'<option selected value="w-'.$w->id.'">'.$w->warehouse_title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="w-'.$w->id.'">'.$w->warehouse_title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Suppliers">';
                $getSuppliersByCat = SupplierProducts::where('product_id',$item->product->id)->pluck('supplier_id')->toArray();
                if(!empty($getSuppliersByCat))
                {
                    // foreach($getSuppliersByCat as $supplierCat)
                    // {
                      $getSuppliers = Supplier::whereIn('id',$getSuppliersByCat)->orderBy('reference_name')->get();
                      foreach ($getSuppliers as $getSupplier)
                      {
                        $value = $item->supplier_id == $getSupplier->id ? 'selected' : "";
                        $html .= '<option '.$value.' value="s-'.$getSupplier->id.'">'.$getSupplier->reference_name.'</option>';
                      }
                    // }
                }
                $html .= ' </optgroup></select>';
                return $html;
              }
            })

            ->addColumn('notes', function ($item) {
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                return "--";
              }
              else
              {
                // check already uploaded images //
                $notes = $item->order_product_note ?$item->order_product_note->count():' ';

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($notes > 0){
                  $note = $item->order_product_note->note;
                $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="font-weight-bold d-block show-notes mr-2" title="View Notes">'.mb_substr($note, 0, 30).' ...</a>';
                }
                if(@$item->status != 18){
                   $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
                          </div>';
                        }else{
                          $html_string .= '--';
                        }

                return $html_string;
              }
            })

            ->setRowId(function ($item) {
              return $item->id;
            })

             // yellowRow is a custom style in style.css file
            ->setRowClass(function ($item) {
              // return $item->product->status != 2 ? 'alert-success' : 'yellowRow';
              if($item->product == null)
              {
                return  'yellowRow';
              }
              elseif($item->is_billed == "Incomplete")
              {
                return  'yellowRow';
              }
            })
            ->rawColumns(['action','refrence_code','number_of_pieces','quantity','unit_price','total_price','exp_unit_cost','supply_from','notes','description','vat','brand','sell_unit','discount','quantity_ship','total_amount','unit_price_with_vat','pcs_shipped'])
            ->make(true);

    }


    public function changePassword()
    {

        return view('accounting.password-management.index');
    }

    public function deleteCreditNote(Request $request)
    {
      // dd($request->all());
      if ($request->type == 'supplier') {
        $order = PurchaseOrder::find($request->id);
        $order->PurchaseOrderDetail()->delete();
        $order->po_documents()->delete();
        $order->po_notes()->delete();
        $order->delete();

        return response()->json(['success' => true]);
      }
      $order = Order::find($request->id);
      // to check whether it's return to stock or not
      $check = OrderProduct::where('return_to_stock', 1)->where('order_id', $order->id)->first();
      if($check){
        return response()->json(['success' => false, 'msg' => 'Some / All items are returned into stock cannot delete this CN']);
      }
      $order->order_products()->delete();
      $order->order_attachment()->delete();
      $order->order_notes()->delete();
      $order->delete();

      return response()->json(['success' => true]);
    }

    public function deleteDebitNote(Request $request)
    {
      // dd($request->all());
      if ($request->type == 'supplier') {
        $order = PurchaseOrder::find($request->id);
        $order->PurchaseOrderDetail()->delete();
        $order->po_documents()->delete();
        $order->po_notes()->delete();
        $order->delete();

        return response()->json(['success' => true]);
      }
      $order = Order::find($request->id);
      $order->order_products()->delete();
      $order->order_attachment()->delete();
      $order->order_notes()->delete();
      $order->delete();

      return response()->json(['success' => true]);
    }

    public function getDraftInvoices()
    {
        $sales_coordinator_customers_count = 0;
        $salesCustomers = 0;
        $salesQuotations = 0;

        if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
        {
            $sales_orders = Order::whereIn('primary_status',[2,3])->whereMonth('created_at',Carbon::now()->month)->whereYear('created_at',Carbon::now()->year)->get();
        }

        $total_sales = 0;
        // foreach ($sales_orders as  $sales_order)
        // {
        //     $total_sales += $sales_order->total_amount;
        //     // $total_sales -= $sales_order->discount;
        // }

        $sales_persons = User::where('status',1)->where('role_id',3)->whereNull('parent_id')->get();
        $totalCustomers = Customer::where('status',1)->count();
        $customers = Customer::where('status',1)->get();
        $customer_categories = CustomerCategory::where('is_deleted',0)->get();
         //For admin total_invoice
          $month = date('m');
                      $day = '01';
                      $year = date('Y');

                      $start_of_month = $year . '-' . $month . '-' . $day;
                      $today = date('Y-m-d');
        if(Auth::user()->role_id == 7 || Auth::user()->role_id == 1 || Auth::user()->role_id == 11){
         $warehouse_id = Auth::user()->warehouse_id;
        $ids = User::select('id')->where('role_id',3)->orWhere('role_id',4)->orWhere('role_id',1)->where('warehouse_id',$warehouse_id)->pluck('id')->toArray();
        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->whereIn('user_id',$ids)->get();
        $credit_notes_total = Order::where('primary_status',25)->where('status',27)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
        $debit_notes_total = Order::where('primary_status',28)->where('status',30)->whereDate('created_at','>=',$start_of_month)->whereDate('created_at','<=',$today)->sum('total_amount');
       }
         // dd($admin_orders_draft);
          $admin_total_sales_draft = 0;
        foreach ($admin_orders_draft as  $sales_order)
        {
            $admin_total_sales_draft += $sales_order->total_amount;
            // $admin_total_sales_draft -= $sales_order->discount;
        }

         //For admin total_invoice
                      $month = date('m');
                      $day = '01';
                      $year = date('Y');

                      $start_of_month = $year . '-' . $month . '-' . $day;
                      $today = date('Y-m-d');
                      $admin_orders = Order::select('id','total_amount')->where('primary_status',3)->whereBetween('delivery_request_date', [$start_of_month, $today])->get();


                      $admin_total_sales = 0;
                    foreach ($admin_orders as  $sales_order)
                    {
                        $admin_total_sales += $sales_order->total_amount;
                        // $admin_total_sales -= $sales_order->discount;
                    }
        return $this->render('accounting.dashboard.draft_invoice_dashboard',compact('customers','sales_persons','totalCustomers','total_sales','sales_coordinator_customers_count','salesCustomers','salesQuotations','customer_categories','admin_total_sales','admin_total_sales_draft','credit_notes_total','debit_notes_total'));
    }

    public function accountingFetchCustomer(Request $request)
    {
        // dd($request->get('query'));
       $query = $request->get('query');
            // dd($search_box_value);
        $params = $request->except('_token');
        $detail = [];
        $customer_query  = Customer::with('CustomerCategory')->select('id','reference_name','category_id')->where('status',1);
            $category_query = CustomerCategory::query()->with('customer');
        if($query)
        {
          $query = $request->get('query');
          $customer_query = $customer_query->where('reference_number',$query)->orWhere('reference_name', 'LIKE', '%'.$query.'%')->orderBy('category_id', 'ASC' )->get();
        }
        if($query != null)
        {
          $category_query = $category_query->where('title', 'LIKE', '%'.$query.'%')->where('is_deleted',0)->get();
        }
        else
        {
          $category_query = $category_query->where('is_deleted',0)->get();
        }
        $category_all = $category_query->pluck('id')->toArray();
        if(!empty($customer_query) || !empty($category_query) )
        {
           $output = '<ul class="dropdown-menu search-dropdown customer_id state-tags select_customer_id" style="display:block; top:37px; left:15px; width:calc(100% - 30px); padding:0px; max-height: 380px;overflow-y: scroll;">';
            // dd($category_query);
            if(!empty($category_query))
            {
              $i = 1;
              foreach($category_query as $key)
              {
                 $output .= '
                  <li class="list-data parent" data-value="'.$key->title.'" data-id="cat-'.$key->id.'" style="padding:0px 4px;padding-top:2px;">';
                 $output .= '<a tabindex="'.$i.'" href="javascript:void(0);" value="'.$key->id.'" data-prod_ref_code="" class="select_customer_id"><b>'.$key->title.'</b></a></li>
                  ';
                 // $customers = Customer::select('id','reference_name')->where('category_id',$key->id)->get();
                 $customers = $key->customer;
                  foreach ($customers as $value) {
                   $output .= '
                    <li class="list-data child" data-value="'.$value->reference_name.'" data-id="cus-'.$value->id.'" style="padding:2px 15px;border-bottom: 1px solid #eee;">';
                   $output .= '<a tabindex="'.$i.'" href="javascript:void(0);" value="cus-'.$value->id.'" data-prod_ref_code="">'.$value->reference_name.'</a></li>
                    ';
                  }
                  $i++;
              }
            }
            if(!empty($customer_query))
            {
               $i = 1;
               $cat_id = '';
               // dd($customer_query);

               foreach ($customer_query as $value) {
                  if (!in_array($value->category_id, $category_all))
                  {
                    if($cat_id == '' || $cat_id != $value->category_id)
                    {
                      $output .= '<li class="list-data parent" data-value="" data-id="cat-'.$value->category_id.'" style="padding:0px 4px;padding-top:2px;>';
                      $output .= '<a tabindex="" href="javascript:void(0);" value="" data-prod_ref_code="" class="select_customer_id"><b>'.$value->CustomerCategory->title.'</b></a></li>';
                     }
                    $output .= '<li class="list-data child" data-value="'.$value->reference_name.'" data-id="cus-'.$value->id.'" style="padding:2px 15px;border-bottom: 1px solid #eee;"> ';
                    $output .= '<a tabindex="'.$i.'" href="javascript:void(0);" value="cus-'.$value->id.'" data-prod_ref_code="">'.$value->reference_name.'</a></li>
                    ';
                    $cat_id = $value->category_id;
                  }
                  $i++;
                }
            }
            $output .= '</ul>';
            echo $output;
        }
        else
        {
            $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
            $output .= '<li style="color:red;" align="center">No record found!!!</li>';
            $output .= '</ul>';
            echo $output;
        }
    }


    //getting Data for Supplier Credit Notes
    public function getSupplierCreditNotes(Request $request)
    {
      $query = PurchaseOrder::select('purchase_orders.*')->where('primary_status', 25);
      PurchaseOrder::doSort($request, $query);

      if($request->dosortby == 26)
      {
        $query->where(function($q){
         $q->where('status', 26);
        });
      }
      else if($request->dosortby == 27)
      {
        $query->where(function($q){
         $q->where('status', 27);
        });
      }
      else if($request->dosortby == 33)
      {
        $query->where(function($q){
         $q->where('status', 33);
        });
      }
      else if($request->dosortby == 31)
      {
        $query->where(function($q){
         $q->where('status', 31);
        });
      }
      if($request->supplier != null)
      {
        $query->where('supplier_id', $request->supplier);
      }
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('purchase_orders.created_at', '>=', $date);
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('purchase_orders.created_at', '<=', $date);
      }
      $query = $query->with('PoSupplier', 'p_o_statuses', 'PurchaseOrderDetail');

      $dt =  DataTables::of($query);
      $add_columns = ['total_amount', 'ref_id', 'status', 'memo', 'credit_note_date', 'supplier_ref_no', 'supplier', 'action'];
      foreach ($add_columns as $column) {
        $dt->addColumn($column, function($item) use($column) {
            return AccountingDashboardDatatable::returnSupplierCreditAddColumn($column, $item);
        });
      }

      $filter_columns = ['ref_id', 'supplier_ref_no', 'supplier'];
      foreach ($filter_columns as $column) {
        $dt->filterColumn($column, function ($item, $keyword) use ($column) {
            return AccountingDashboardDatatable::returnSupplierCreditFilterColumn($column, $item, $keyword);
        });
      }

        $dt->rawColumns(['action','ref_id', 'supplier','status','supplier_ref_no']);
        $dt->with('post',$query->sum('total_with_vat'));
        return $dt->make(true);
    }

    //getting Data for Supplier Debit Notes
    public function getSupplierDebitNotes(Request $request)
    {
      $query = PurchaseOrder::select('purchase_orders.*')->where('primary_status', 28);
      PurchaseOrder::doSort($request, $query);

      if($request->dosortby == 29)
      {
        $query->where(function($q){
         $q->where('status', 29);
        });
      }
      else if($request->dosortby == 30)
      {
        $query->where(function($q){
         $q->where('status', 30);
        });
      }
      if($request->supplier != null)
      {
        $query->where('supplier_id', $request->supplier);
      }
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('purchase_orders.created_at', '>=', $date);
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        $query->whereDate('purchase_orders.created_at', '<=', $date);
      }
      $query = $query->with('PoSupplier', 'p_o_statuses', 'PurchaseOrderDetail');

      $dt =  DataTables::of($query);
      $add_columns = ['total_amount', 'ref_id', 'status', 'memo', 'credit_note_date', 'supplier_ref_no', 'supplier', 'action'];
      foreach ($add_columns as $column) {
        $dt->addColumn($column, function($item) use($column) {
            return AccountingDashboardDatatable::returnSupplierDebitAddColumn($column, $item);
        });
      }

      $filter_columns = ['ref_id', 'supplier_ref_no', 'supplier'];
      foreach ($filter_columns as $column) {
        $dt->filterColumn($column, function ($item, $keyword) use ($column) {
            return AccountingDashboardDatatable::returnSupplierDebitFilterColumn($column, $item, $keyword);
        });
      }

        $dt->rawColumns(['action','ref_id', 'supplier','status','supplier_ref_no']);
        $dt->with('post',$query->sum('total_with_vat'));
        return $dt->make(true);
    }

    public function returnStockFromCreditNote(Request $request){

      $order_product = OrderProduct::find($request->id);
      if($order_product){
        $order_product->return_to_stock = $request->checked == "true" ? 1 : 0;
        $order_product->save();
      }
      return response()->json(['success' => true]);
    }
}
