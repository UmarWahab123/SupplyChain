<?php

namespace App\Http\Controllers\Purchasing;

use DB;
use Auth;
use Hash;
use Session;
use App\User;
use App\General;
use App\Variable;
use Carbon\Carbon;
use App\ExportStatus;
use App\Notification;
use App\QuotationConfig;
use App\Models\Common\Unit;
use Illuminate\Support\Arr;
use App\Models\Common\State;
use App\ProductTypeTertiary;
use Illuminate\Http\Request;
use App\Models\Common\Status;
use App\Models\Common\Country;
use App\Models\Common\Courier;
use App\Models\Common\PoGroup;
use App\Models\Common\Product;
use App\Models\Common\Supplier;
use App\Models\Common\Warehouse;
use Yajra\Datatables\Datatables;
use App\Models\Common\UserDetail;
use App\Jobs\ReceivedIntoStockJob;
use App\Models\Common\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use App\Models\Common\PoGroupDetail;
use Illuminate\Support\Facades\View;
use App\Models\Common\ProductCategory;
use App\Models\Common\TableHideColumn;
use Illuminate\Support\Facades\Schema;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\Helpers\ProductConfigurationHelper;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\ProductSecondaryType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
// use App\QuotationConfig;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $targetShipDateConfig;

    // public function __construct()
    // {
    //     $this->middleware('auth');

    //     $general = new General();
    //     $targetShipDate = $general->getTargetShipDateConfig();
    //     $this->targetShipDateConfig = $targetShipDate;
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
        if($config)
        {
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
        }
        else
        {
            $sys_name = 'testing';
            $sys_logos = 'testing';
            $sys_color = 'testing';
            $sys_border_color = 'testing';
            $btn_hover_border = 'testing';
            $current_version = '1';
        }
        // current controller constructor
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;
        $product_detail_section = ProductConfigurationHelper::getProductConfigurations();

        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data, 'product_detail_section' => $product_detail_section]);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getHome()
    {
        $purchasingStatuses = Status::whereIn('id',[12,16])->get();
        $couriers = Courier::where('is_deleted',0)->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name', 'ASC')->get();
        $waitingConfirmPo = PurchaseOrder::where('status',12)->count();
        $shippedPo = PurchaseOrder::where('status',13)->count();
        $dispatchedPo = PurchaseOrder::where('status',14)->count();
        $currentMonth = date('m');
        $currentYear = date('Y');

        if($this->targetShipDateConfig['target_ship_date'] == 1)
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
        }
        else
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->count();
        }

        $inquiryProducts = OrderProduct::where('is_billed', 'Inquiry')->count();
        $supplierCount = Supplier::where('status',1)->count();

        $page_status = Status::select('title')->whereIn('id',[12,13,14,15])->pluck('title')->toArray();

        return $this->render('users.home.dashboard',compact('purchasingStatuses','suppliers','couriers','waitingConfirmPo','shippedPo','dispatchedPo','receivedPoCurrentMonth','inquiryProducts','supplierCount','allPo','page_status'));
    }

    public function getTransferDashboard()
    {
        $purchasingStatuses = Status::where('parent_id',19)->get();
        $couriers = Courier::all();

        if(Auth::user()->role_id == 6)
        {
            $waitingConfirmTd = PurchaseOrder::where('status',20)->where('to_warehouse_id',Auth::user()->warehouse_id)->count();
            $waitingTransfer  = PurchaseOrder::where('status',21)->where('to_warehouse_id',Auth::user()->warehouse_id)->count();
            $completetransfer = PurchaseOrder::where('status',22)->where('to_warehouse_id',Auth::user()->warehouse_id)->count();
        }
        else
        {
            $waitingConfirmTd = PurchaseOrder::where('status',20)->count();
            $waitingTransfer = PurchaseOrder::where('status',21)->count();
            $completetransfer = PurchaseOrder::where('status',22)->count();
        }

        $inquiryProducts = OrderProduct::where('is_billed', 'Inquiry')->count();
        $supplierCount = Supplier::where('status',1)->count();

        $page_status = Status::select('title')->whereIn('id',[20,21,22])->pluck('title')->toArray();

        return $this->render('users.home.transfer-document-dashboard',compact('purchasingStatuses','couriers','waitingConfirmTd','waitingTransfer','completetransfer','supplierCount','page_status'));
    }

    public function getWaitingShippingInfo()
    {
        $purchasingStatuses = Status::whereIn('id',[12,16])->get();
        $couriers = Courier::where('is_deleted',0)->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name', 'ASC')->get();
        $waitingConfirmPo = PurchaseOrder::where('status',12)->count();
        $shippedPo = PurchaseOrder::where('status',13)->count();
        $dispatchedPo = PurchaseOrder::where('status',14)->count();
        $currentMonth = date('m');
        $currentYear = date('Y');

        if($this->targetShipDateConfig['target_ship_date'] == 1)
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
        }
        else
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->count();
        }

        $inquiryProducts = OrderProduct::where('is_billed', 'Inquiry')->count();
        $supplierCount = Supplier::where('status',1)->count();
        $page_status = Status::select('title')->whereIn('id',[12,13,14,15])->pluck('title')->toArray();

        $po_groups = PoGroup::where('is_confirm',0)->whereNull('is_cancel')->whereNull('from_warehouse_id')->where('is_review',0)->latest()->get();

        return $this->render('users.purchasing.waiting-shipping-info',compact('purchasingStatuses','suppliers','couriers','waitingConfirmPo','shippedPo','dispatchedPo','receivedPoCurrentMonth','inquiryProducts','supplierCount','allPo','page_status','po_groups'));
    }

    public function getDispatchFromSupplier()
    {
        $purchasingStatuses = Status::whereIn('id',[12,16])->get();
        $couriers = Courier::where('is_deleted',0)->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name', 'ASC')->get();
        $waitingConfirmPo = PurchaseOrder::where('status',12)->count();
        $shippedPo = PurchaseOrder::where('status',13)->count();
        $dispatchedPo = PurchaseOrder::where('status',14)->count();
        $currentMonth = date('m');
        $currentYear = date('Y');

        if($this->targetShipDateConfig['target_ship_date'] == 1)
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
        }
        else
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->count();
        }

        $inquiryProducts = OrderProduct::where('is_billed', 'Inquiry')->count();
        $supplierCount = Supplier::where('status',1)->count();
        $page_status = Status::select('title')->whereIn('id',[12,13,14,15])->pluck('title')->toArray();

        return $this->render('users.purchasing.dispatch-from-supplier',compact('purchasingStatuses','suppliers','couriers','waitingConfirmPo','shippedPo','dispatchedPo','receivedPoCurrentMonth','inquiryProducts','supplierCount','allPo','page_status'));
    }

    public function getReceivedintoStock()
    {
        $purchasingStatuses = Status::whereIn('id',[12,16])->get();
        $couriers = Courier::where('is_deleted',0)->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name', 'ASC')->get();
        $waitingConfirmPo = PurchaseOrder::where('status',12)->count();
        $shippedPo = PurchaseOrder::where('status',13)->count();
        $dispatchedPo = PurchaseOrder::where('status',14)->count();
        $currentMonth = date('m');
        $currentYear = date('Y');

        if($this->targetShipDateConfig['target_ship_date'] == 1)
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
        }
        else
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->count();
        }

        $inquiryProducts = OrderProduct::where('is_billed', 'Inquiry')->count();
        $supplierCount = Supplier::where('status',1)->count();
        $page_status = Status::select('title')->whereIn('id',[12,13,14,15])->pluck('title')->toArray();

        return $this->render('users.purchasing.received-into-stock',compact('purchasingStatuses','suppliers','couriers','waitingConfirmPo','shippedPo','dispatchedPo','receivedPoCurrentMonth','inquiryProducts','supplierCount','allPo','page_status'));
    }

    public function allPos()
    {
        $purchasingStatuses = Status::whereIn('id',[12,16])->get();
        $couriers = Courier::where('is_deleted',0)->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name', 'ASC')->get();
        $waitingConfirmPo = PurchaseOrder::where('status',12)->count();
        $shippedPo = PurchaseOrder::where('status',13)->count();
        $dispatchedPo = PurchaseOrder::where('status',14)->count();
        $currentMonth = date('m');
        $currentYear = date('Y');

        if($this->targetShipDateConfig['target_ship_date'] == 1)
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->whereRaw('MONTH(target_receive_date) = ?',[$currentMonth])->whereRaw('YEAR(target_receive_date) = ?',[$currentYear])->count();
        }
        else
        {
            $receivedPoCurrentMonth = PurchaseOrder::where('status',15)->count();
            $allPo = PurchaseOrder::whereIn('status',[12,13,14,15])->count();
        }

        $inquiryProducts = OrderProduct::where('is_billed', 'Inquiry')->count();
        $supplierCount = Supplier::where('status',1)->count();

        $page_status = Status::select('title')->whereIn('id',[12,13,14,15])->pluck('title')->toArray();

        return $this->render('users.purchasing.all-pos',compact('purchasingStatuses','suppliers','couriers','waitingConfirmPo','shippedPo','dispatchedPo','receivedPoCurrentMonth','inquiryProducts','supplierCount','allPo','page_status'));
    }

    public function addCourier(Request $request){
        // dd("hello");
       $validator = $request->validate([
           'title' => 'required',
       ]);

       $courier = new Courier;
       $courier->title = $request->title;
       $courier->is_deleted = 0;
       $courier->save();
    //    dd($courier);
       return response()->json(['success' => true,
       'courier'=>$request->title,
       'id'=>$courier->id]);

   }

    public function completeProfile()
    {
        $check_profile_completed = UserDetail::where('user_id',Auth::user()->id)->count();
        if($check_profile_completed > 0){
            return redirect()->back();
        }
        $countries = Country::get();

        return $this->render('users.home.profile-complete', compact('countries'));
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

    public function generalSettings(){
        $setting_table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'product')->first();
        $setting_table_hide_columns_2 = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'purchase-list')->first();
        return $this->render('users.settings.index', compact('setting_table_hide_columns','setting_table_hide_columns_2'));
    }

    public function saveTableColumnDisplay(Request $request){
       // dd($request->all());
        if($request->table_col != null){
            $columns_hidden = implode(',', $request->table_col);
        }else{
            $columns_hidden = -1;
        }
        $check_type = TableHideColumn::where('user_id', Auth::user()->id)->where('type', $request->type)->first();
        if($check_type){
            $hideColumn = TableHideColumn::find($check_type->id);
        }else{
            $hideColumn = new TableHideColumn;
        }
        $hideColumn->user_id = Auth::user()->id;
        $hideColumn->type = $request->type;
        $hideColumn->hide_columns = $columns_hidden;
        $hideColumn->updated_by = Auth::user()->id;
        $hideColumn->save();

        return redirect()->back()->with('successmsg', 'Product Table Columns hidden successfully');
    }

     public function saveTableColumnDisplayPurchaseList(Request $request){
       // dd($request->all());
        if($request->type != null){
            $column_hidden = $request->column_id;
        }else{
            $column_hidden = -1;
        }
        $check_type = TableHideColumn::where('user_id', Auth::user()->id)->where('type', $request->type)->first();
        // dd($check_type);
        if($check_type){
            $hideColumn = TableHideColumn::find($check_type->id);
            $arr = $hideColumn->hide_columns;
            $some = explode(',', $arr);

            // dd($some);
            $contains = in_array($column_hidden, $some);
            // dd($contains);
            if($contains == true){
                // dd('here');
           $var = $this->remove_element($some,$column_hidden);
           $variable = implode(',',@$var);
           // dd($variable);
       }else{
        $variable = @$hideColumn->hide_columns.','.@$column_hidden;
           // dd($variable);
       }
        }else{
            $hideColumn = new TableHideColumn;
            $variable = $request->column_id;
        }
        $hideColumn->user_id = Auth::user()->id;
        $hideColumn->type = $request->type;
        $hideColumn->hide_columns = $variable;
        $hideColumn->updated_by = Auth::user()->id;
        // dd($hideColumn);
        $hideColumn->save();



        return redirect()->back()->with('successmsg', 'Product Table Columns hidden successfully');
    }
         public function remove_element($array,$value) {
         foreach (array_keys($array, $value) as $key) {
            unset($array[$key]);
         }
          return $array;
        }

        public function savePurchaseListTableColumnDisplay(Request $request){
        // dd($request->all());
        if($request->table_col_2 != null){
            $columns_hidden_2 = implode(',', $request->table_col_2);
        }else{
            $columns_hidden_2 = -1;
        }
        $check_type = TableHideColumn::where('user_id', Auth::user()->id)->where('type', $request->type)->first();
        if($check_type){
            $hideColumn = TableHideColumn::find($check_type->id);
        }else{
            $hideColumn = new TableHideColumn;
        }
        $hideColumn->user_id = Auth::user()->id;
        $hideColumn->type = $request->type;
        $hideColumn->hide_columns = $columns_hidden_2;
        $hideColumn->updated_by = Auth::user()->id;
        $hideColumn->save();

        return redirect()->back()->with('successmsg', 'Product Table Columns hidden successfully');

    }

    public function changePassword()
    {

        return view('users.password-management.index');
    }

    public function toggleTableColumnDisplay(Request $request){
        // dd($request->all());
        if($request->type != null){
            $column_hidden = $request->column_id;
        }else{
            $column_hidden = -1;
        }
        $check_type = TableHideColumn::where('user_id', auth()->user()->id)->where('type', $request->type)->first();
        // dd($check_type);
        if($check_type){
            $hideColumn = TableHideColumn::find($check_type->id);
            $arr = $hideColumn->hide_columns;
            $arr = explode(',', $arr);
            $contains = in_array($column_hidden, $arr);
            // dd($contains);
            if($contains == true){
            $var = $this->remove_element($arr,$column_hidden);

            $columns = implode(',',@$var);
        }else{
        $columns = @$hideColumn->hide_columns.','.@$column_hidden;
            // dd($columns);
        }
        }else{
            $hideColumn = new TableHideColumn;
            $columns = $request->column_id;
        }
        $hideColumn->user_id = auth()->user()->id;
        $hideColumn->type = $request->type;
        $hideColumn->hide_columns = $columns;
        $hideColumn->updated_by = auth()->user()->id;
        $hideColumn->save();

        return response()->json([
          "success"=> true
      ]);
      }

       public function sortColumnDisplay(Request $request){
        // dd($request->all());
          $check_type = ColumnDisplayPreference::where('type', $request->type)->where('user_id', Auth::user()->id)->first();
          if($check_type){
            $sortColumn = ColumnDisplayPreference::find($check_type->id);
          }else{
            $sortColumn = new ColumnDisplayPreference;
          }

          $sortColumn->user_id = Auth::user()->id;
          $sortColumn->type = $request->type;
          $sortColumn->display_order = $request->order;
          $sortColumn->updated_by = Auth::user()->id;
          $sortColumn->save();
        }


    public function checkOldPassword(Request $request)
    {
        $hashedPassword=Auth::user()->password;
        $old_password =  $request->old_password;
        if (Hash::check($old_password, $hashedPassword)) {
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

        // dd('here');

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
        if($user_detail){
            $user_states= State::where('country_id',$user_detail->country_id)->get();
        }
        return view('users.profile-setting.index',['countries'=>$countries,'user_detail'=>$user_detail,'user_states'=>$user_states]);
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
            'image' =>'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);

        // dd($request->all());
        $error = false;
        $user = User::where('id',Auth::user()->id)->first();
        if($user)
        {
            $user->name=$request['name'];
            $user->save();

            $user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
            if($user_detail)
            {
                $user_detail->address       = $request['address'];
                $user_detail->country_id    = $request['country'];
                $user_detail->state_id      = $request['state'];
                $user_detail->city_name     = $request['city'];
                $user_detail->zip_code      = $request['zip_code'];
                $user_detail->phone_no      = $request['phone_number'];
                $user_detail->company_name  = $request['company'];


                //image

                if($request->hasFile('image') && $request->image->isValid())
                {
                  $fileNameWithExt = $request->file('image')->getClientOriginalName();
                  $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
                  $extension = $request->file('image')->getClientOriginalExtension();
                  $fileNameToStore = $fileName.'_'.time().'.'.$extension;
                  $path = $request->file('image')->move('public/uploads/purchase/',$fileNameToStore);
                  $user_detail->image = $fileNameToStore;
                }

                $user_detail->save();

                return response()->json([
                    "error"=>$error
                ]);
            }

        }

    }

    public function stockReportDashboard()
    {
      $warehouses = Warehouse::where('status',1)->get();
      $suppliers = Supplier::where('status',1)->orderBy('reference_name')->get();
      $units = Unit::all();
      $product_parent_categories = ProductCategory::with('get_Child')->where('parent_id',0)->orderBy('title')->get();
      $statusCheck=ExportStatus::where('type','stock_movement_report')->first();
      $last_downloaded=null;
      if($statusCheck!=null)
      {
        $last_downloaded=$statusCheck->last_downloaded;
      }
      $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'stock_movement_report')->first();
      $product_types = ProductType::all();
      $product_types_2 = ProductSecondaryType::orderBy('title','asc')->get();
      $product_types_3 = ProductTypeTertiary::orderBy('title','asc')->get();
      return view('users.reports.stock-report',compact('warehouses','suppliers','product_parent_categories','last_downloaded','units','table_hide_columns','product_types','product_types_2', 'product_types_3'));
    }

    public function stockDetailReport(Request $request)
    {

        // dd($request->all());
        $p_id    = '';
        $o_id    = '';
        $is_type = '';
        $product_id = $request->id;
        $from_date  = $request->from_date;
        $to_date    = $request->to_date;
        $warehouses = Warehouse::where('status',1)->get();
        $warehouse_id = $request->warehouse_id;
        // dd($warehouse_id);
      return view('users.reports.stock-detail-report',compact('p_id','o_id','is_type','product_id','from_date','to_date','warehouses','warehouse_id'));
    }

    public function stockDetailReportWithPm(Request $request)
    {
        // dd($request->all());
        $p_id    = '';
        $o_id    = '';
        $is_type = '';
        $from_date  = '';
        $to_date    = '';
        $product_id = '';
        if(Session::get('product_id'))
        {
            if($p_id == 'null')
            {
              $p_id = '';
            }
            else
            {
              $p_id = Session::get('product_id');
            }
        }
        if(Session::get('order_id'))
        {
            if($o_id == 'null')
            {
              $o_id = '';
            }
            else
            {
              $o_id = Session::get('order_id');
            }
        }
        if(Session::get('is_type'))
        {
            if($is_type == 'null')
            {
              $is_type = '';
            }
            else
            {
              $is_type = Session::get('is_type');
            }
        }

        $p_id       = $p_id;
        $order_id   = $o_id;
        $is_type    = $is_type;
        $warehouses = Warehouse::where('status',1)->get();
        $warehouse_id = null;
      return view('users.reports.stock-detail-report',compact('p_id','o_id','is_type','warehouses','from_date','to_date','product_id','warehouse_id'));
    }

    public function getStockReport(Request $request)
    {
      $warehouse_id = $request->warehouse_id;
      $supplier_id  = $request->supplier_id;
      $unit_id      = $request->unit_id;

      $from_date = $request->from_date;
      if($from_date !== null)
      {
        $from_date = str_replace("/","-",$from_date);
        $from_date =  date('Y-m-d',strtotime($from_date));
      }


      $to_date = $request->to_date;
      if($to_date !== null)
      {
        $to_date = str_replace("/","-",$to_date);
        $to_date =  date('Y-m-d',strtotime($to_date));
      }
      $product_id   = $request->product_id != null ? $request->product_id : $request->p_id;


      // if($product_id != null)
      // {
      //   $ids = array();
      //   array_push($ids, $product_id);
      // }
      // else{
      //   $ids = StockManagementOut::distinct('product_id');
      //   if($from_date !== null)
      //   {
      //       $ids->where('created_at','>=',$from_date);
      //   }
      //   if($to_date !== null)
      //   {
      //       $ids->where('created_at','<=',$to_date.' 23:59:59');
      //   }
      //   if($warehouse_id != null)
      //   {
      //       $ids = $ids->where('warehouse_id',$warehouse_id)->pluck('product_id')->toArray();
      //   }
      //   else
      //   {
      //       $ids = $ids->pluck('product_id')->toArray();
      //   }

      // }
      // dd($ids);
      // $products = Product::select('products.id','products.short_desc','products.brand','products.refrence_code','products.min_stock','products.selling_unit','products.type_id','products.type_id_2', 'products.type_id_3','products.supplier_id', 'products.unit_conversion_rate', 'products.total_buy_unit_cost_price')->whereIn('products.id',$ids)->with([
      //               'sellingUnits' => function($u){
      //                   $u->select('id','title', 'decimal_places');
      //               },
      //               'stock_out' => function($q) use ($from_date, $to_date, $warehouse_id){
      //                   // dd($from_date, $to_date);
      //               $q->select('id','product_id','quantity_in','quantity_out','created_at','po_group_id','title','order_id')->whereDate('created_at','>=',$from_date)->whereDate('created_at','<=',$to_date);
      //               if($warehouse_id != null && $warehouse_id != ''){
      //                   $q->where('warehouse_id', $warehouse_id);
      //               }
      //               },'productType','productType2'
      //           ]);

      // $products = Product::select('products.id','products.short_desc','products.brand','products.refrence_code','products.min_stock','products.selling_unit','products.type_id','products.type_id_2', 'products.type_id_3','products.supplier_id', 'products.unit_conversion_rate', 'products.total_buy_unit_cost_price')
      //           ->whereHas('warehouse_products', function ($query) use ($warehouse_id) {
      //               if($warehouse_id != null && $warehouse_id != ''){
      //                   $query->where('warehouse_id', $warehouse_id);
      //               }
      //               $query->groupBy('product_id')
      //               ->havingRaw('SUM(current_quantity) < products.min_stock');
      //           })
      //           ->with([
      //               'sellingUnits' => function($u){
      //                   $u->select('id','title', 'decimal_places');
      //               },
      //               'stock_out' => function($q) use ($from_date, $to_date, $warehouse_id){
      //                   // dd($from_date, $to_date);
      //               $q->select('id','product_id','quantity_in','quantity_out','created_at','po_group_id','title','order_id')->whereDate('created_at','>=',$from_date)->whereDate('created_at','<=',$to_date);
      //               if($warehouse_id != null && $warehouse_id != ''){
      //                   $q->where('warehouse_id', $warehouse_id);
      //               }
      //               },'productType','productType2'
      //           ]);
        $products = Product::select('products.id', 'products.short_desc', 'products.brand', 'products.refrence_code', 'products.min_stock', 'products.selling_unit', 'products.type_id', 'products.type_id_2', 'products.type_id_3', 'products.supplier_id', 'products.unit_conversion_rate', 'products.total_buy_unit_cost_price')->where('products.status', 1);
        if($product_id != null){
            $products = $products->where('id', $product_id);
        }
        $products = $products->join('warehouse_products', 'products.id', '=', 'warehouse_products.product_id')
            ->groupBy('products.id')
            // ->havingRaw('SUM(warehouse_products.current_quantity) < products.min_stock')
            ->with([
                'sellingUnits' => function($u) {
                    $u->select('id', 'title', 'decimal_places');
                },
                'stock_out' => function($q) use ($from_date, $to_date, $warehouse_id) {
                    $q->select('id', 'product_id', 'quantity_in', 'quantity_out', 'created_at', 'po_group_id', 'title', 'order_id')
                      ->whereDate('created_at', '>=', $from_date)
                      ->whereDate('created_at', '<=', $to_date);
                    if ($warehouse_id != null && $warehouse_id != '') {
                        $q->where('warehouse_id', $warehouse_id);
                    }
                },
                'productType',
                'productType2',
                'def_or_last_supplier' => function($sup){
                    $sup->select('id', 'reference_name','country');
                }
            ]);

    //   dd($products->get()->take(1));
      if($warehouse_id != null)
      {
        //dd($warehouse_id);
        if(Auth::user()->role_id == 9 && $warehouse_id == 1){
            $products = $products->where('products.ecommerce_enabled',1);
        }

      }

      if($unit_id != null)
      {
        $products = $products->where('products.selling_unit',$unit_id);
      }

      if($supplier_id != null)
      {
        $products = $products->where('products.supplier_id',$request->supplier_id);
      }

      if($request->prod_category != '')
      {
        $products->where('products.category_id', $request->prod_category)->orderBy('refrence_no', 'ASC');
      }

      if($product_id != null)
      {
        $products->where('products.products.id',$product_id);
      }

      if($request->product_type != null)
      {
        $products->where('products.type_id',$request->product_type);
      }
      if($request->supplier_country != null)
      {
        $products->whereHas('def_or_last_supplier', function ($query) use ($request) {
            $query->where('country', $request->supplier_country);
        });
      }
      if($request->product_type_3 != null)
      {
        $products->where('products.type_id_3',$request->product_type_3);
      }

      if($request->p_id != null)
      {
        $products->where('products.id',$request->p_id);
      }

      if($request->all_movement != null && $request->all_movement == 1)
      {
        if($warehouse_id != null)
        {
            $products->whereHas('warehouse_products',function($q) use($warehouse_id){
                $q->where('warehouse_id',$warehouse_id);
                $q->where('current_quantity','>',0);
            });
        }
        else
        {
            $products->whereHas('warehouse_products',function($q){
                $q->where('current_quantity','>',0);
            });
        }

        $stock_items = true;
      }
      else
      {
        $stock_items = false;
      }
      if($request->all_movement != null && $request->all_movement == 2)
      {
        $products->where('products.min_stock','>',0);
      }

      if($request->all_movement != null && $request->all_movement == 3)
      {
        if($warehouse_id != null)
        {
            if(Auth::user()->role_id == 9 && $warehouse_id == 1){

                         $products->where('products.min_stock', '!=', 0)->whereHas('warehouse_products',function($q)use($warehouse_id){
                $q->where('warehouse_id',$warehouse_id)->groupBy('warehouse_products.product_id')->havingRaw('SUM(current_quantity) < products.min_stock');
            });

            }else{
                         $products->where('products.min_stock', '!=', 0)->whereHas('warehouse_products',function($q)use($warehouse_id){
                $q->where('warehouse_id',$warehouse_id)->groupBy('warehouse_products.product_id')->havingRaw('SUM(current_quantity) < products.min_stock');
            });

            }
        }
        else
        {
            $products->where('products.min_stock', '!=', 0)->whereHas('warehouse_products',function($q){
                $q->groupBy('warehouse_products.product_id')->havingRaw('SUM(floor(current_quantity)) < products.min_stock');
            });
        }

        $stock_min_current = true;
        $stock_items = true;

      }
      else
      {
        $stock_min_current = false;
        $stock_items = false;
      }

      //Sorting Code Starts Here
      $column_name = null;
      $sort_order = $request->sort_order;
      if ($request->column_name == 'pf')
      {
        $column_name = 'refrence_code';
      }
      if ($request->column_name == 'product_description')
      {
        $column_name = 'short_desc';
      }
      if ($request->column_name == 'brand')
      {
        $column_name = 'brand';
      }
      if ($request->column_name == 'min_stock')
      {
        $column_name = 'min_stock';
      }

      if ($request->column_name == 'supplier')
      {
        $products->leftjoin('suppliers as sup', 'sup.id', '=', 'products.supplier_id')
        ->where('products.status', 1)
        ->orderBy('sup.reference_name', $sort_order);
      }
      if ($request->column_name == 'supplier_country') {
        $products->leftJoin('suppliers as sup', 'products.supplier_id', '=', 'sup.id')
            ->leftJoin('countries', 'sup.country', '=', 'countries.id')
            ->where('products.status', 1) // Specify the table name or alias for the 'status' column
            ->orderBy('countries.name', $sort_order);
      }
      if ($request->column_name == 'type')
      {
        $products->leftjoin('types as pt', 'pt.id', '=', 'products.type_id')->orderBy('pt.title', $sort_order);
      }
     
      else if ($request->column_name == 'type_3')
      {
        $products->leftjoin('product_type_tertiaries as pt', 'pt.id', '=', 'products.type_id_3')->orderBy('pt.title', $sort_order);
      }
      else if ($request->column_name == 'unit')
      {
        $products->leftjoin('units as u', 'u.id', '=', 'products.selling_unit')->orderBy('u.title', $sort_order);
      }
      if($column_name != null)
      {
        $products->orderBy($column_name, $sort_order);
      }

      //sorting ends here

      if($unit_id != null)
      {
        $unit = Unit::find($unit_id);
        $unit_title = $unit->title;
        if($unit_title == 'Pack')
        {
            $unit_title = $unit_title.'.';
        }
        $total_prod = $products;

        $products_ids_prods = $products;
        $products_ids = $products_ids_prods->pluck('products.id')->toArray();

        if($warehouse_id != null)
        {
            $total_unit = WarehouseProduct::where('warehouse_id',$warehouse_id)->whereIn('product_id',$products_ids)->get()->sum('current_quantity');
        }
        else
        {
            $total_unit = WarehouseProduct::whereIn('product_id',$products_ids)->get()->sum('current_quantity');
        }
      }
      else
      {
        $unit_title = '';
        $total_unit = '';
      }

      $dt =  Datatables::of($products);
      $add_columns = ['history', 'cogs', 'stock_balance', 'stock_out', 'out_transfer_document', 'out_manual_adjustment', 'out_order', 'stock_in', 'in_orderUpdate', 'in_transferDocument', 'in_manualAdjusment', 'in_purchase', 'start_count', 'selling_unit', 'min_stock', 'supplier_country','product_type_3', 'product_type', 'brand', 'supplier'];

      foreach ($add_columns as $column) {
        $dt->addColumn($column, function ($item) use ($column, $from_date, $to_date, $warehouse_id) {
            return Product::returnAddColumnStockReport($column, $item, $from_date, $to_date, $warehouse_id);
        });
      }

      $edit_columns = ['refrence_code', 'short_desc'];

      foreach ($edit_columns as $column) {
        $dt->editColumn($column, function ($item) use ($column) {
            return Product::returnEditColumnStockReport($column, $item);
        });
      }

        $dt->rawColumns(['refrence_code','history','in_purchase','in_manualAdjusment','in_transferDocument','in_orderUpdate', 'out_order','out_manual_adjustment','out_transfer_document','product_type','supplier_country', 'supplier']);
        $dt->with(['title' => $unit_title,'total_unit' => number_format(floatval($total_unit),2,'.',','),'stock_items' => $stock_items, 'stock_min_current' => $stock_min_current]);
        return $dt->make(true);
    }


    public function getStockReportFromProductDetail(Request $request)
    {
       // dd($request->all());
      $warehouse_id = $request->warehouse_id;
      $supplier_id  = $request->supplier_id;
      $unit_id      = $request->unit_id;

      $from_date = $request->from_date;
      if($from_date !== null)
      {
        $from_date = str_replace("/","-",$from_date);
        $from_date =  date('Y-m-d',strtotime($from_date));
      }


      $to_date = $request->to_date;
      if($to_date !== null)
      {
        $to_date = str_replace("/","-",$to_date);
        $to_date =  date('Y-m-d',strtotime($to_date));
      }
      $product_id   = $request->product_id;

    if($from_date != null && $to_date != null)
    {
      $products = Product::select(DB::raw('SUM(CASE
      WHEN st.created_at<"'.$from_date.'" THEN st.quantity_out
      END) AS Start_count_out,
      SUM(CASE
      WHEN st.created_at<"'.$from_date.'" THEN st.quantity_in
      END) AS Start_count_in,
      SUM(CASE
      WHEN st.`created_at` >="'.$from_date.'" and st.`created_at` <= "'.$to_date.' 23:59:59" THEN st.quantity_out
      END) AS OUTs,
      SUM(CASE
      WHEN st.`created_at` >="'.$from_date.'" and st.`created_at` <= "'.$to_date.' 23:59:59" THEN st.quantity_in
      END) AS INS
      '),'products.refrence_code','products.short_desc','products.brand','products.selling_unit','st.product_id','products.id','products.min_stock')->groupBy('st.product_id')->havingRaw('OUTs < 0')->OrHavingRaw('INS > 0')->with([
            'sellingUnits' => function($u){
                $u->select('id','title', 'decimal_places');
            },
            'stock_out' => function($q) use ($from_date, $to_date){
                // dd($from_date, $to_date);
            $q->select('id','product_id','quantity_in','quantity_out','created_at','po_group_id','title','order_id')->whereDate('created_at','>=',$from_date)->whereDate('created_at','<=',$to_date);
            },'productType','productType2'
        ]);
      $products->join('stock_management_outs AS st','st.product_id','=','products.id');
    }elseif($from_date != null)
    {
        $products = Product::select(DB::raw('SUM(CASE
      WHEN st.created_at<"'.$from_date.'" THEN st.quantity_out
      END) AS Start_count_out,
      SUM(CASE
      WHEN st.created_at<"'.$from_date.'" THEN st.quantity_in
      END) AS Start_count_in,
      SUM(CASE
      WHEN 1 THEN st.quantity_out
      END) AS OUTs,
      SUM(CASE
      WHEN 1 THEN st.quantity_in
      END) AS INS
      '),'products.refrence_code','products.short_desc','products.brand','products.selling_unit','st.product_id','products.id','products.min_stock')->groupBy('st.product_id')->havingRaw('OUTs < 0')->OrHavingRaw('INS > 0')->with('sellingUnits');
      $products->join('stock_management_outs AS st','st.product_id','=','products.id');
    }
    else
    {
        $products = Product::select(DB::raw('SUM(CASE
      WHEN 1 THEN 0
      END) AS Start_count_out,
      SUM(CASE
      WHEN 1 THEN 0
      END) AS Start_count_in,
      SUM(CASE
      WHEN 1 THEN st.quantity_out
      END) AS OUTs,
      SUM(CASE
      WHEN 1 THEN st.quantity_in
      END) AS INS
      '),'products.refrence_code','products.short_desc','products.brand','products.selling_unit','st.product_id','products.id','products.min_stock')->groupBy('st.product_id')->havingRaw('OUTs < 0')->OrHavingRaw('INS > 0')->with('sellingUnits');
      $products->join('stock_management_outs AS st','st.product_id','=','products.id');
    }


      if($warehouse_id != null)
      {
        //dd($warehouse_id);
        if(Auth::user()->role_id == 9 && $warehouse_id == 1){
            $products = $products->where('ecommerce_enabled',1)->where('warehouse_id',$warehouse_id);
        }else{
            $products = $products->where('warehouse_id',$warehouse_id);
        }

      }

      if($unit_id != null)
      {
        $products = $products->where('selling_unit',$unit_id);
      }

      if($supplier_id != null)
      {
        $products = $products->where('supplier_id',$request->supplier_id);
      }

      if($request->prod_category != '')
      {
        $products->where('category_id', $request->prod_category)->orderBy('refrence_no', 'ASC');
      }

      if($product_id != null)
      {
        $products->where('products.id',$product_id);
      }

      if($request->p_id != null)
      {
        $products->where('products.id',$request->p_id);
      }

      if($request->all_movement != null && $request->all_movement == 1)
      {
        if($warehouse_id != null)
        {
            $products->whereHas('warehouse_products',function($q) use($warehouse_id){
                $q->where('warehouse_id',$warehouse_id);
                $q->where('current_quantity','>',0);
            });
        }
        else
        {
            $products->whereHas('warehouse_products',function($q){
                $q->where('current_quantity','>',0);
            });
        }

        $stock_items = true;
      }
      else
      {
        $stock_items = false;
      }
      if($request->all_movement != null && $request->all_movement == 2)
      {
        $products->where('min_stock','>',0);
      }

      if($request->all_movement != null && $request->all_movement == 3)
      {
        if($warehouse_id != null)
        {
            // $products->whereHas('warehouse_products',function($q) use($warehouse_id){
            //     $q->where('warehouse_id',$warehouse_id);
            //     $q->where('current_quantity','>',0);
            // });
            if(Auth::user()->role_id == 9 && $warehouse_id == 1){

                         $products->whereHas('warehouse_products',function($q)use($warehouse_id){
                $q->where('warehouse_id',$warehouse_id)->groupBy('warehouse_products.product_id')->havingRaw('SUM(current_quantity) < products.min_stock');
            });

            }else{
                         $products->whereHas('warehouse_products',function($q)use($warehouse_id){
                $q->where('warehouse_id',$warehouse_id)->groupBy('warehouse_products.product_id')->havingRaw('SUM(current_quantity) < products.min_stock');
            });

            }
        }
        else
        {
            $products->whereHas('warehouse_products',function($q){
                $q->groupBy('warehouse_products.product_id')->havingRaw('SUM(floor(current_quantity)) < products.min_stock');
            });
        }

        $stock_min_current = true;
        $stock_items = true;

      }
      else
      {
        $stock_min_current = false;
        $stock_items = false;

      }

      /*

      if($from_date != null)
      {
        $products = $products->whereIn('id',StockManagementOut::select('product_id')->whereDate('created_at','>=',$from_date)->where(function($q){
            $q->where('quantity_in','>',0)->orWhere('quantity_out','<',0);
        })->distinct('product_id')->pluck('product_id'));
      }

      if($to_date != null)
      {
        //$to_date=Carbon::parse($to_date);
        $products = $products->whereIn('id',StockManagementOut::select('product_id')->whereDate('created_at','<=',$to_date)->where(function($q){
            $q->where('quantity_in','>',0)->orWhere('quantity_out','<',0);
        })->distinct('product_id')->pluck('product_id'));
      }

      if($product_id != null)
      {
        $products->where('id',$product_id);
      }
      $products->with('stock_out');*/

      if($unit_id != null)
      {
        $unit = Unit::find($unit_id);
        $unit_title = $unit->title;
        if($unit_title == 'Pack')
        {
            $unit_title = $unit_title.'.';
        }
        $total_prod = $products;

        $products_ids_prods = $products;
        $products_ids = $products_ids_prods->pluck('products.id')->toArray();

        if($warehouse_id != null)
        {
            $total_unit = WarehouseProduct::where('warehouse_id',$warehouse_id)->whereIn('product_id',$products_ids)->get()->sum('current_quantity');
        }
        else
        {
            $total_unit = WarehouseProduct::whereIn('product_id',$products_ids)->get()->sum('current_quantity');
        }
      }
      else
      {
        $unit_title = '';
        $total_unit = '';
      }
      // dd($unit_title);
      // dd($products->get()->count());
      if($products->get()->count() == 0)
      {
        $products = Product::where('id',$request->product_id);
        // dd($products);
        return Datatables::of($products)

        ->editColumn('refrence_code', function ($item){
          $html_string = '
                 <a href="'.url('get-product-detail/'.$item->id).'" target="_blank" title="View Detail"><b>'.$item->refrence_code.'</b></a>
                 ';
          return $html_string;
        })

        ->editColumn('short_desc', function ($item){
          return $item->short_desc;
        })

        ->addColumn('brand', function ($item){
          return @$item->brand;
        })

        ->addColumn('min_stock', function ($item){
          return @$item->min_stock != null ? @$item->min_stock : null;
        })

        ->addColumn('selling_unit', function ($item){
          return @$item->sellingUnits->title;
        })

        ->addColumn('start_count', function ($item){
            return 0;
        })

        ->addColumn('stock_in', function ($item){
            return 0;
        })

        ->addColumn('stock_out', function ($item){
             return 0;
        })

        ->addColumn('stock_balance', function ($item){
            return 0;
        })

        ->addColumn('history', function ($item){
            $html_string = '
             <a class="actionicon historyIcon" style="cursor:pointer" title="View history" data-id='.$item->id.' target="_blank"><i class="fa fa-history"></i></a>';
            return $html_string;
        })

        ->rawColumns(['refrence_code','history'])
        ->with(['title' => 'unit','total_unit' => 0,'stock_items' => 0, 'stock_min_current' => 0])
        ->make(true);
      }
      return Datatables::of($products)

        ->editColumn('refrence_code', function ($item){
          $html_string = '
                 <a href="'.url('get-product-detail/'.$item->product_id).'" target="_blank" title="View Detail"><b>'.$item->refrence_code.'</b></a>
                 ';
          return $html_string;
        })

        ->editColumn('short_desc', function ($item){
          return $item->short_desc;
        })

        ->addColumn('brand', function ($item){
          return @$item->brand;
        })

        ->addColumn('min_stock', function ($item){
          return @$item->min_stock != null ? @$item->min_stock : null;
        })

        ->addColumn('selling_unit', function ($item){
          return @$item->sellingUnits->title;
        })

        ->addColumn('start_count', function ($item){
            $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
            return round($item->Start_count_out+$item->Start_count_in,$decimal_places);
        })

        ->addColumn('stock_in', function ($item){
            return round($item->INS,2);
        })

        ->addColumn('stock_out', function ($item){
             return round($item->OUTs,2);
        })

        ->addColumn('stock_balance', function ($item){
            $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
            return round($item->Start_count_out+$item->Start_count_in+$item->INS+$item->OUTs,$decimal_places);
        })

        ->addColumn('history', function ($item){
            $html_string = '
             <a class="actionicon historyIcon" style="cursor:pointer" title="View history" data-id='.$item->product_id.' target="_blank"><i class="fa fa-history"></i></a>';
            return $html_string;
        })

        ->rawColumns(['refrence_code','history'])
        ->with(['title' => $unit_title,'total_unit' => number_format(floatval($total_unit),2,'.',','),'stock_items' => $stock_items, 'stock_min_current' => $stock_min_current])
        ->make(true);
    }

    public function getStockDetailReport(Request $request)
    {
      //dd($request->all());
      $warehouse_id = $request->warehouse_id;
      $supplier_id  = $request->supplier_id;
      $from_date    = $request->from_date;
      $to_date      = $request->to_date;
      $product_id   = $request->product_id;
      $p_id         = $request->p_id;
      $o_id         = $request->o_id;

      $stock = StockManagementOut::query();
      if($warehouse_id != null)
      {
        $stock = $stock->where('warehouse_id',$warehouse_id);
      }
      // if($supplier_id != null)
      // {
      //   $stock->where('supplier_id',$request->supplier_id);
      // }

      // if($request->prod_category != '')
      // {
      //   $stock->where('category_id', $request->prod_category)->orderBy('refrence_no', 'DESC');
      // }

      if($from_date != null)
      {
        $date = str_replace("/","-",$from_date);
        $date =  date('Y-m-d',strtotime($date));
        $stock = $stock->where('created_at','>=',$date);
      }

      if($to_date != null)
      {
        $date = str_replace("/","-",$to_date);
        $date =  date('Y-m-d',strtotime($date));
        $stock = $stock->where('created_at','<=',$date.' 23:59:59');
      }

      if($product_id != null)
      {
        $stock->where('product_id',$product_id);
      }

      if($p_id != null)
      {
        $stock->where('product_id',$p_id);
      }

      if($o_id != null)
      {
        $stock->where('order_id',$o_id);
      }

      $stock->orderBy('id','DESC');
      return Datatables::of($stock)

        ->addColumn('warehouse_title', function ($item){
          $html_string = $item->get_warehouse->warehouse_title ;
          return $html_string;
        })

        ->addColumn('exp_date', function ($item){
          $html_string = $item->get_stock_in->expiration_date != null ? Carbon::parse($item->get_stock_in->expiration_date)->format('d/m/Y') :'--';
          return $html_string;
        })

        ->addColumn('customer_ref_name', function ($item){
            $html_sting = '--';
            if ($item->order_id !== null) {
                if($item->stock_out_order != null)
                {
                    if ($item->stock_out_order->customer != null)
                    {
                        $html_sting = '<a href="'.route('get-customer-detail', $item->stock_out_order->customer->id).'" target="_blank">
                    '.$item->stock_out_order->customer != null ? $item->stock_out_order->customer->reference_name : '--'.'</a>';
                    }
                    else
                    {
                        $html_sting = '--';
                    }
                }
                return $html_sting;
            }
            elseif($item->po_group_id != null){
                $groups = $item->get_po_group;
                if($item->stock_out_order != null){
                if ($item->stock_out_order->customer != null) {
                    if ($groups != null) {
                        $customers = $groups->find_customers($groups, $item->stock_out_order->customer->id);
                    }
                    if ($customers->count() > 0) {
                        $i = 1;
                        $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal'.$item->id.'" title="Customers">
                                    <i class="fa fa-user"></i>
                                   </a>';
                        $html_string .= '
                    <div class="modal fade" id="poNumberModal'.$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="exampleModalLabel">Customers</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                        <table class="bordered" style="width:100%;">
                            <thead style="border:1px solid #eee;text-align:center;">
                              <tr><th>S.No</th><th>Customer Ref #</th></tr>
                            </thead>
                            <tbody>';
                        foreach ($customers as $cust) {
                            $html_string .= '<a target="_blank" href="'.route('get-customer-detail', ['id' => $cust->id]).'" title="View Detail"><b>'.$cust->reference_name.'</b></a>';

                            $html_string .= '<tr>
                              <td>'.$i.'
                              </td>
                              <td>
                              <a target="_blank" href="'.route('get-customer-detail', ['id' => $cust->id]).'" title="View Detail"><b>'.$cust->reference_name.'</b></a>
                              </td>
                            </tr>';
                            $i++;
                        }

                        $html_string .= '</tbody>
                        </table>

                        </div>
                      </div>
                    </div>
                  </div>';
                        return $html_string;
                    } else {
                        $html_sting = '<span>--</span>';
                        return $html_sting;
                    }
                }
                }
            }
            else{
                $html_sting = '<span>--</span>';
                return $html_sting;
            }
            // return $html_sting;
        })

        ->addColumn('created_date', function ($item){
            $date = Carbon::parse(@$item->created_at)->format('d/m/Y');
          return $date;
        })

        ->addColumn('reason', function ($item){
            if($item->order_id != null && $item->po_group_id == null && $item->stock_out_order)
            {
                $ret = $item->stock_out_order->get_order_number_and_link($item->stock_out_order);
                $ref_no = $ret[0];
                $link = $ret[1];

                $title = '<b><span style="color:black;"><a target="_blank" href="'.route($link, ['id' => $item->order_id]).'" title="View Detail" class="">ORDER: '.$ref_no .'</a></span></b>';
                // $title = "ORDER:".$item->stock_out_order->ref_id ;
            }
            elseif($item->po_group_id  != null)
            {
                $title = '<b><span style="color:black;"><a target="_blank" href="'.route('warehouse-completed-receiving-queue-detail', ['id' => $item->po_group_id]).'" title="View Detail" class="">SHIPMENT: '.$item->get_po_group->ref_id .'</a></span></b>';
                // $title = "ORDER:".$item->stock_out_order->ref_id ;
            }
            elseif($item->p_o_d_id != null)
            {
                $title = '<b><span style="color:black;"><a target="_blank" href="'.url('get-purchase-order-detail',$item->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($item->title != null ? $item->title : (@$item->stock_out_purchase_order_detail->PurchaseOrder->supplier_id == null ? 'TD' : 'PO') ) .':'. $item->stock_out_purchase_order_detail->PurchaseOrder->ref_id .'</span></a></b>';
                // $title = "PO:".$item->stock_out_purchase_order_detail->PurchaseOrder->ref_id  ;
            }

            elseif($item->title)
            {
                $title = $item->title;
            }
            else
            {
                $title = 'Adjustmet';
            }
            return $title.'<b><span style="color:black;"> ('.$item->id.')<span></b>';
        })

        ->addColumn('in', function ($item){
          $decimal_places = $item->get_product != null ? ($item->get_product->sellingUnits != null ? $item->get_product->sellingUnits->decimal_places : 3) : 3;

          return number_format($item->quantity_in,$decimal_places,'.',',');
        })

        ->addColumn('out', function ($item){
            $decimal_places = $item->get_product != null ? ($item->get_product->sellingUnits != null ? $item->get_product->sellingUnits->decimal_places : 3) : 3;
            return number_format($item->quantity_out,$decimal_places,'.',',');
        })

        ->addColumn('custom_invoice_number', function ($item){
            if($item->po_group_id  != null)
            {
            // dd($item->get_po_group);
                return $item->get_po_group->custom_invoice_number != null ? $item->get_po_group->custom_invoice_number : '--';
            }
            elseif($item->order_id != null)
            {
                $ids_array = explode(',', $item->parent_id_in);
                $find_records = StockManagementOut::whereIn('id',$ids_array)->get();

                if($find_records->count() == 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                            if($record->po_group_id  != null)
                            {
                            // dd($record->get_po_group);
                                $html_string .= $record->get_po_group->custom_invoice_number != null ? $record->get_po_group->custom_invoice_number.', ' : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->custom_invoice_number != null ? $record->custom_invoice_number.', ' : '--'.', ';
                            }
                        }

                    return $html_string;
                }
                elseif($find_records->count() > 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                         if($record->po_group_id  != null)
                            {
                            // dd($record->get_po_group);
                                $html_string .= $record->get_po_group->custom_invoice_number != null ? $record->get_po_group->custom_invoice_number.', ' : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->custom_invoice_number != null ? $record->custom_invoice_number.', ' : '--'.', ';
                            }
                    }

                    return $html_string;
                }
                else
                {
                    return '--';
                }
            }
            elseif($item->p_o_d_id != null && $item->quantity_out != null)
            {
                $ids_array = explode(',', $item->parent_id_in);
                $find_records = StockManagementOut::whereIn('id',$ids_array)->get();

                if($find_records->count() == 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                            if($record->po_group_id  != null)
                            {
                            // dd($record->get_po_group);
                                $html_string .= $record->get_po_group->custom_invoice_number != null ? $record->get_po_group->custom_invoice_number.', ' : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->custom_invoice_number != null ? $record->custom_invoice_number.', ' : '--'.', ';
                            }
                        }

                    return $html_string;
                }
                elseif($find_records->count() > 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                         if($record->po_group_id  != null)
                            {
                            // dd($record->get_po_group);
                                $html_string .= $record->get_po_group->custom_invoice_number != null ? $record->get_po_group->custom_invoice_number.', ' : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->custom_invoice_number != null ? $record->custom_invoice_number.', ' : '--'.', ';
                            }
                    }

                    return $html_string;
                }
                else
                {
                    return '--';
                }
            }
            elseif($item->order_id == null && $item->po_group_id == null)
            {
                 $html_string = '
                    <input type="text" value="'.$item->custom_invoice_number.'" data-fieldvalue="'.$item->custom_invoice_number.'" data-id="'.$item->id.'" class="fieldFocus form-control custom_invoice_number" name="custom_invoice_number" >
                ';

                return $html_string;
            }
            else
            {
                return '--';
            }
        })

        ->addColumn('custom_line_number', function ($item) use($product_id, $p_id){
            if($item->po_group_id  != null)
            {
                if($p_id != null)
                {
                    return $item->get_po_group->po_group_product_details()->where('product_id',$p_id)->pluck('custom_line_number')->first() !== null ? $item->get_po_group->po_group_product_details()->where('product_id',$p_id)->pluck('custom_line_number')->first() : '--';
                }
                else
                {
                    return $item->get_po_group->po_group_product_details()->where('product_id',$product_id)->pluck('custom_line_number')->first() !== null ? $item->get_po_group->po_group_product_details()->where('product_id',$product_id)->pluck('custom_line_number')->first() : '--';
                }
            }
            elseif($item->order_id != null)
            {
                $ids_array = explode(',', $item->parent_id_in);
                $find_records = StockManagementOut::whereIn('id',$ids_array)->get();

                if($find_records->count() == 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                        if($record->po_group_id  != null)
                        {
                            if($p_id != null)
                            {
                                $html_string .= $record->get_po_group->po_group_product_details()->where('product_id',$p_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$p_id)->pluck('custom_line_number')->first().', ' : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->get_po_group->po_group_product_details()->where('product_id',$product_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$product_id)->pluck('custom_line_number')->first().', ' : '--'.', ';
                            }
                        }
                        else
                        {
                            $html_string .= $record->custom_line_number != null ? $record->custom_line_number.', ' : '--'.', ';
                        }
                    }

                    return $html_string;
                }
                else
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                        if($record->po_group_id  != null)
                        {
                            if($p_id != null)
                            {
                                $html_string .= $record->get_po_group->po_group_product_details()->where('product_id',$p_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$p_id)->pluck('custom_line_number')->first().', ' : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->get_po_group->po_group_product_details()->where('product_id',$product_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$product_id)->pluck('custom_line_number')->first().', ' : '--'.', ';
                            }
                        }
                        else
                        {
                            $html_string .= $record->custom_line_number != null ? $record->custom_line_number.', ' : '--'.', ';
                        }
                    }

                    return $html_string;
                }
            }
            elseif($item->p_o_d_id != null && $item->quantity_out != null)
            {
                $ids_array = explode(',', $item->parent_id_in);
                $find_records = StockManagementOut::whereIn('id',$ids_array)->get();

                if($find_records->count() == 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                            if($record->po_group_id  != null)
                            {
                            // dd($record->get_po_group);
                                $html_string .= $record->get_po_group->po_group_product_details()->where('product_id',$record->product_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$record->product_id)->pluck('custom_line_number')->first() : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->custom_line_number != null ? $record->custom_line_number.', ' : '--'.', ';
                            }
                        }

                    return $html_string;
                }
                elseif($find_records->count() > 1)
                {
                    $html_string = '';
                    foreach ($find_records as $record) {
                         if($record->po_group_id  != null)
                            {
                            // dd($record->get_po_group);
                                $html_string .= $record->get_po_group->po_group_product_details()->where('product_id',$record->product_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$record->product_id)->pluck('custom_line_number')->first() : '--'.', ';
                            }
                            else
                            {
                                $html_string .= $record->custom_line_number != null ? $record->custom_line_number.', ' : '--'.', ';
                            }
                    }

                    return $html_string;
                }
                else
                {
                    return '--';
                }
            }
            elseif($item->order_id == null && $item->po_group_id == null)
            {
                 $html_string = '
                    <input type="text" value="'.$item->custom_line_number.'" data-fieldvalue="'.$item->custom_line_number.'" data-id="'.$item->id.'" class="fieldFocus form-control custom_line_number" name="custom_line_number" >
                ';

                return $html_string;
            }
            else
            {
                return '--';
            }
        })

        ->addColumn('note', function ($item){
            return $item->note != null ? $item->note : '--';
        })

        ->addColumn('in_out_from', function ($item){
            if($item->parent_id_in != null && $item->po_group_id == null)
            {
                $html_string = '<p> <a href="javascript:void(0)" class="fa fa-eye load_in_out_detail" data-id="'.$item->id.'" data-toggle="modal" data-target="#in_out_modal_'.$item->id.'"></a></p>';
                // Order In/Out Detail Modal
                $html_string .= '
                    <div class="modal fade" id="in_out_modal_'.$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="false" style="top:50px;">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Stock Card In/Out Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body in_out_detail_table">

                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary refresh-table" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                ';

                return $html_string;
            }
            else
            {
                return '--';
            }
        })

        ->addColumn('cogs', function ($item)  use ($p_id, $product_id){

            if($item->cost == null)
            {
                // if($item->parent_id_in != null)
                if(false)
                {
                    $ids = explode(',',$item->parent_id_in);

                    $find_out_cogs = StockManagementOut::whereIn('id',$ids)->whereNotNull('cost')->get();

                    $count = $find_out_cogs->count();

                    $cogs_value = $find_out_cogs->sum('cost');

                    if($count != 0)
                    {
                        return 'false--'.number_format($cogs_value / $count,2,'.',',');
                    }
                    else
                    {
                        return 0;
                    }
                }
                else
                {
                    if($p_id != null)
                    {
                        // $calValue = $item->stock_out_order->order_products != null ? number_format($item->stock_out_order->order_products()->where('product_id',$p_id)->pluck('actual_cost')->first(),2) : '--';

                        $calValue = $item->stock_out_order != null ? ($item->stock_out_order->order_products != null ? number_format($item->stock_out_order->order_products()->where('product_id',$p_id)->pluck('actual_cost')->first(),2) : '--' )  : '--';

                        // $cogs_shipment = $item->stock_out_order->order_products != null ?($item->stock_out_order->order_products()->where('product_id',$p_id)->pluck('manual_cogs_shipment')->first()) : '';

                        $cogs_shipment = $item->stock_out_order != null ? ( $item->stock_out_order->order_products != null ? $item->stock_out_order->order_products()->where('product_id',$p_id)->pluck('manual_cogs_shipment')->first() : '' ) : '';

                        if($cogs_shipment !== null && $cogs_shipment !== '')
                        {
                            $po_group = PoGroup::find($cogs_shipment);
                            if($po_group)
                            {
                                if($po_group->is_review == 1)
                                {
                                    $link = 'importing-completed-receiving-queue-detail';
                                }
                                else
                                {
                                    $link = 'importing-receiving-queue-detail';
                                }
                                $title = '<a target="_blank" href="'.route( $link, ['id' => $cogs_shipment]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                            }
                            else
                            {
                                // $title = $calValue;
                                $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $p_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                            }
                        }
                        else
                        {
                            $p_o_detail = PurchaseOrderDetail::where('order_id',$item->stock_out_order->id)->where('product_id',$p_id)->first();
                            if($p_o_detail)
                            {
                                $po_id = $p_o_detail->PurchaseOrder->po_group_id;
                                if($po_id !== null)
                                {
                                    $po_group = PoGroup::find($po_id);
                                    if($po_group)
                                    {
                                        if($po_group->is_review == 1)
                                        {
                                            $link = 'importing-completed-receiving-queue-detail';
                                        }
                                        else
                                        {
                                            $link = 'importing-receiving-queue-detail';
                                        }
                                        $title = '<a target="_blank" href="'.route( $link, ['id' => $po_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                    }
                                    else
                                    {
                                        // $title = $calValue;
                                        $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $p_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                    }
                                }
                                else
                                {
                                    // $title = $calValue;
                                    $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $p_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                }
                            }
                            else
                            {
                                $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $p_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                            }
                        }

                        return $title;
                    }
                    else
                    {
                        $calValue = $item->stock_out_order != null ? ($item->stock_out_order->order_products != null ? number_format($item->stock_out_order->order_products()->where('product_id',$product_id)->pluck('actual_cost')->first(),2) : '--' )  : '--';

                        $cogs_shipment = $item->stock_out_order != null ? ( $item->stock_out_order->order_products != null ? $item->stock_out_order->order_products()->where('product_id',$product_id)->pluck('manual_cogs_shipment')->first() : '' ) : '';

                        if($cogs_shipment !== null && $cogs_shipment !== '')
                        {
                            $po_group = PoGroup::find($cogs_shipment);
                            if($po_group)
                            {
                                if($po_group->is_review == 1)
                                {
                                    $link = 'importing-completed-receiving-queue-detail';
                                }
                                else
                                {
                                    $link = 'importing-receiving-queue-detail';
                                }
                                $title = '<a target="_blank" href="'.route( $link, ['id' => $cogs_shipment]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                            }
                            else
                            {
                                // $title = $calValue;
                                if($calValue != "--")
                                {
                                    $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $product_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                }
                                else
                                {
                                    $title = $calValue;
                                }
                            }
                        }
                        else
                        {
                            if($item->stock_out_order != null)
                            {
                                $p_o_detail = PurchaseOrderDetail::where('order_id',$item->stock_out_order->id)->where('product_id',$product_id)->first();
                            }
                            else
                            {
                                $p_o_detail = null;
                            }

                            if($p_o_detail)
                            {
                                $po_id = $p_o_detail->PurchaseOrder->po_group_id;
                                if($po_id !== null)
                                {
                                    $po_group = PoGroup::find($po_id);
                                    if($po_group)
                                    {
                                        if($po_group->is_review == 1)
                                        {
                                            $link = 'importing-completed-receiving-queue-detail';
                                        }
                                        else
                                        {
                                            $link = 'importing-receiving-queue-detail';
                                        }
                                        $title = '<a target="_blank" href="'.route( $link, ['id' => $po_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                    }
                                    else
                                    {
                                        // $title = $calValue;
                                        if($calValue != "--")
                                        {
                                            $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $product_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                        }
                                        else
                                        {
                                            $title = $calValue;
                                        }
                                    }
                                }
                                else
                                {
                                    // $title = $calValue;
                                    if($calValue != "--")
                                    {
                                        $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $product_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                    }
                                    else
                                    {
                                        $title = $calValue;
                                    }
                                }
                            }
                            else
                            {
                                if($calValue != "--")
                                {
                                    $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $product_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                                }
                                else
                                {
                                    $title = $calValue;
                                }
                            }
                        }

                        return $title;
                        // return $item->stock_out_order != null ? 'product_id--'.number_format($item->stock_out_order->order_products()->where('product_id',$product_id)->pluck('actual_cost')->first(),2) : '--';
                    }
                }
            }
            else
            {
                // return number_format($item->cost,2,'.',',');
                $calValue = number_format($item->cost,2,'.',',');
                if($item->po_group_id != null)
                {
                    $po_group = PoGroup::find($item->po_group_id);
                    if($po_group)
                    {
                        if($po_group->is_review == 1)
                        {
                            $link = 'importing-completed-receiving-queue-detail';
                        }
                        else
                        {
                            $link = 'importing-receiving-queue-detail';
                        }

                        if($p_id != null)
                        {
                            $title = '<a target="_blank" href="'.route( $link, ['id' => $item->po_group_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                        }
                        else
                        {
                            $title = '<a target="_blank" href="'.route( $link, ['id' => $item->po_group_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                        }
                    }
                    else
                    {
                        if($p_id != null)
                        {
                            $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $p_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                        }
                        else
                        {
                            $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $product_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                        }
                    }
                }
                else
                {
                    if($p_id != null)
                    {
                        $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $p_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                    }
                    else
                    {
                        $title = '<a target="_blank" href="'.route('get-product-detail', ['id' => $product_id]).'" title="View Detail"><b>'.$calValue.'</b></a>';
                    }
                }

                return $title;
            }

        })

        ->addColumn('stock_balance', function ($item) use ($warehouse_id){
        /*->where('smi_id',$item->smi_id)*/
        if($warehouse_id != null)
        {
            $stock_out_in = StockManagementOut::where('product_id',$item->product_id)->where('warehouse_id',$item->warehouse_id)->where('id','<=',$item->id)->sum('quantity_in');
            $stock_out_out = StockManagementOut::where('product_id',$item->product_id)->where('warehouse_id',$item->warehouse_id)->where('id','<=',$item->id)->sum('quantity_out');
        }
        else
        {
            $stock_out_in = StockManagementOut::where('product_id',$item->product_id)->where('id','<=',$item->id)->sum('quantity_in');
            $stock_out_out = StockManagementOut::where('product_id',$item->product_id)->where('id','<=',$item->id)->sum('quantity_out');
        }
        $decimal_places = $item->get_product != null ? ($item->get_product->sellingUnits != null ? $item->get_product->sellingUnits->decimal_places : 3) : 3;
            return number_format(($stock_out_in+$stock_out_out),$decimal_places,'.',',');
        })
        ->addColumn('stock_balance_start', function ($item) use ($warehouse_id){
        /*->where('smi_id',$item->smi_id)*/
        if($warehouse_id != null)
        {
            $stock_out_in = StockManagementOut::where('product_id',$item->product_id)->where('warehouse_id',$item->warehouse_id)->where('id','<',$item->id)->sum('quantity_in');
            $stock_out_out = StockManagementOut::where('product_id',$item->product_id)->where('warehouse_id',$item->warehouse_id)->where('id','<',$item->id)->sum('quantity_out');
        }
        else
        {
            $stock_out_in = StockManagementOut::where('product_id',$item->product_id)->where('id','<',$item->id)->sum('quantity_in');
            $stock_out_out = StockManagementOut::where('product_id',$item->product_id)->where('id','<',$item->id)->sum('quantity_out');
        }
        $decimal_places = $item->get_product != null ? ($item->get_product->sellingUnits != null ? $item->get_product->sellingUnits->decimal_places : 3) : 3;
            return number_format(($stock_out_in+$stock_out_out),$decimal_places,'.',',');
        })
        ->rawColumns(['reason','customer_ref_name','custom_invoice_number','cogs','stock_balance_start','in_out_from','custom_line_number'])
        ->with(['total_in'=>$stock->get()->sum('quantity_in'),'total_out' => $stock->get()->sum('quantity_out')])
        ->make(true);
    }

    public function getCustomInvoiceNumber(Request $request)
    {
        $find = StockManagementOut::find($request->id);

        $ids_array = explode(',', $find->parent_id_in);


        $find_records = StockManagementOut::whereIn('id',$ids_array)->get();

        if($find_records->count() > 0)
        {
            $html_string = '<table class="bordered" style="width:100%;">
                            <thead style="border:1px solid #eee;text-align:center;">
                              <tr><th>Order ref#</th><th>Custom\'s Inv.#</th><th>Custom\'s Line# </th><th>COGS</th></tr>
                            </thead>
                            <tbody>';
            foreach ($find_records as $item) {
                $html_string .= '<tr><td>';
                if($item->order_id != null)
                {
                    $ret = $item->stock_out_order->get_order_number_and_link($item->stock_out_order);
                    $ref_no = $ret[0];
                    $link = $ret[1];

                    $html_string .= '<a target="_blank" href="'.route($link, ['id' => $item->order_id]).'" title="View Detail" class="">ORDER: '.$ref_no .'</a>';
                    // $title = "ORDER:".$item->stock_out_order->ref_id ;
                    $html_string .= ' ('.$item->id.')';

                }
                elseif($item->p_o_d_id != null)
                {
                    $html_string .= '<a target="_blank" href="'.url('get-purchase-order-detail',$item->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($item->title != null ? $item->title : 'PO' ) .':'. $item->stock_out_purchase_order_detail->PurchaseOrder->ref_id .'</a>';
                    // $title = "PO:".$item->stock_out_purchase_order_detail->PurchaseOrder->ref_id  ;
                    $html_string .= ' ('.$item->id.')';

                }
                elseif($item->po_group_id  != null)
                {
                    $html_string .= '<a target="_blank" href="'.route('warehouse-completed-receiving-queue-detail', ['id' => $item->po_group_id]).'" title="View Detail" class="">SHIPMENT: '.$item->get_po_group->ref_id .'</a>';
                    // $title = "ORDER:".$item->stock_out_order->ref_id ;

                    $html_string .= ' ('.$item->id.')';
                }
                elseif($item->title)
                {
                    $html_string .= $item->title.' ('.$item->id.')';
                }
                else
                {
                    $html_string .= 'Adjustmet'.' ('.$item->id.')';
                }
                $html_string .= '</td>';
                if($item->po_group_id  != null)
                {
                    $invoice_no = $item->get_po_group->custom_invoice_number != null ? $item->get_po_group->custom_invoice_number : '';
                }
                else
                {
                    $invoice_no = $item->custom_invoice_number != null ? $item->custom_invoice_number : '';
                }
                $html_string .= '<td>
                    <input type="text" value="'.$invoice_no.'" data-fieldvalue="'.$invoice_no.'" data-id="'.$item->id.'" class="fieldFocus form-control custom_invoice_number" name="custom_invoice_number" >
                </td>';
                if($item->po_group_id  != null)
                {
                    $line_no = $item->get_po_group->po_group_product_details()->where('product_id',$find->product_id)->pluck('custom_line_number')->first() !== null ? $item->get_po_group->po_group_product_details()->where('product_id',$find->product_id)->pluck('custom_line_number')->first() : '';
                }
                else
                {
                    $line_no = $item->custom_line_number != null ? $item->custom_line_number : '';
                }
                $html_string .= '<td>
                    <input type="text" value="'.$line_no.'" data-fieldvalue="'.$line_no.'" data-id="'.$item->id.'" class="fieldFocus form-control custom_line_number" name="custom_line_number" >
                </td>';

                if($item->cost == null)
                    {
                        $cogs = $item->stock_out_order != null ? number_format($item->stock_out_order->order_products()->where('product_id',$item->product_id)->pluck('actual_cost')->first(),2) : '--';
                    }
                    else
                    {
                        $cogs = number_format($item->cost,2,'.',',');
                    }

                    $html_string .= '<td>'.$cogs.'</td>';

                $html_string .= '</tr>';
            }

            $html_string .= '</tbody></table>';

            return response()->json(['html' => $html_string, 'success' => true]);
        }
        // dd($find_records);
        return response()->json(['html' => 'No Data Found !!!', 'success' => true]);
    }

    public function updateCustomInvoiceNumber(Request $request)
    {
        $find_stock = StockManagementOut::find($request->id);
        if($find_stock->po_group_id != null)
        {
            return response()->json(['group_find' => true]);
        }
        if($find_stock != null)
        {
            foreach($request->except('id','new_select_value') as $key => $value)
            {
              if($key == 'custom_invoice_number')
              {
                  $find_stock->$key = $value;
              }

              if($key == 'custom_line_number')
              {
                  $find_stock->$key = $value;
              }
            }

            $find_stock->save();

            return response()->json(['success' => true]);
        }
    }


    public function exportReceivedIntoStock(Request $request) {
        // dd();
        $data=$request->all();
        // dd($data);
        $type = $request->blade;
        $status=ExportStatus::where('type', $type)->first();
        if($status==null)
        {
            $new=new ExportStatus();
            $new->user_id=Auth::user()->id;
            $new->type=$type;
            $new->status=1;
            $new->save();
            ReceivedIntoStockJob::dispatch($data,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'recursive'=>true]);
        }
        elseif($status->status==1)
        {
            return response()->json(['msg'=>"File is already being prepared",'status'=>2]);
        }
        elseif($status->status==0 || $status->status==2)
        {
            ExportStatus::where('type',$type)->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);
            ReceivedIntoStockJob::dispatch($data,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'exception'=>null]);
        }
    }

    public function recursiveExportStatusReceivedIntoStock(Request $request) {
        $status=ExportStatus::where('type', $request->type)->first();
        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception]);
    }


    public function checkStatusForFirstTimeReceivedIntoStock(Request $request)
    {
        $status=ExportStatus::where('type',$request->type)->where('user_id',Auth::user()->id)->first();
        if($status!=null)
        {
          return response()->json(['status'=>$status->status]);
        }
        else
        {
          return response()->json(['status'=>0]);
        }
    }
}
