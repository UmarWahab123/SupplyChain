<?php

namespace App\Http\Controllers\Backend;

use App\ExportStatus;
use App\Exports\CustomerSaleReportExport;
use App\Exports\UserLoginHistoryExport;
use App\GlobalAccessForRole;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\ImportFileHistory;
use App\Jobs\CustomerSalesReportJob;
use App\Jobs\UserLoginHistoryJob;
use App\Mail\Backend\UserAccountActivationEmail;
use App\Mail\Backend\UserAccountSuspensionEmail;
use App\Menu;
use App\Models\Common\Bank;
use App\Models\Common\Company;
use App\Models\Common\CompanyBank;
use App\Models\Common\Country;
use App\Models\Common\Courier;
use App\Models\Common\CustomerCategory;
use App\Models\Common\EmailTemplate;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderAttachment;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\Product;
use App\Models\Common\Role;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Supplier;
use App\Models\Common\TableHideColumn;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\QuotationConfig;
use App\RoleAccess;
use App\RoleMenu;
use App\User;
use App\UserLoginHistory;
use App\Variable;
use App\Notification;
use App\Models\Common\Configuration;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Mail;
use Yajra\Datatables\Datatables;
use App\Models\ProductQuantityHistory;
use App\General;
use App\CourierType;



class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */

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
  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */

  public function getHome()
  {
    if(Auth::user())
    {
      return redirect('/sales');
    }
    $customers = Customer::count();
    $products = Product::count();
    $suppliers = Supplier::count();
    $users = User::count();
    $orders = Order::count();
    $sellers = User::where('role_id' , 3)->whereNull('parent_id')->latest()->get();
    $sellers_array = [];
    foreach ($sellers as $key => $seller)
    {
      $sellers_array[] = [
        'name' => $seller->name,
        'total_sale' => $seller->get_total_sale($seller->id)
      ];
    }
    usort($sellers_array, function($a, $b) {
      return $a['total_sale'] <= $b['total_sale'];
    });

    $d_orders = Order::with('customer')->whereDate('created_at' , Carbon::now())->where('primary_status', 2)->orderBy('id','DESC')->get();
    $totalCustomers = Customer::where('status',1)->count();
    return $this->render('backend.home.dashboard',compact('customers','products','suppliers','users','orders','sellers_array','d_orders','totalCustomers'));
  }

  public function viewRoles()
  {
    $roles = Role::select('id', 'name')->get();
    return $this->render('backend.roles.index',['roles'=>$roles]);
  }

  public function viewRoleDetails(Request $request)
  {
    $role_id=$request->role_id;
    $role=$role_id;
    $role_menus = Menu::whereHas('rollmenus',function($q) use($role_id)
    {
      $q->where('role_id',$role_id);
    })->pluck('id')->toArray();
    $allBindedMenus=RoleMenu::with('get_menus')->whereHas('get_menus',function($q) use ($role_id) {
      $q->where('parent_id',0);
    })->where('role_id',$role_id)->orderBy('order','asc')->get();

    $allUnBindedMenus=Menu::where('parent_id',0)->whereDoesntHave('rollmenus',function($q) use ($role_id){
      $q->where('role_id',$role_id);
    })->get();

    $role_name=Role::where('id',$role_id)->pluck('name')->first();
    $global_access=GlobalAccessForRole::where('role_id',$role_id)->get();
    $ids=Menu::where('parent_id',0)->orderBy('id')->pluck('id')->toArray();
    return view('backend.roles.role-menus',compact('global_access','role_menus','allBindedMenus','allUnBindedMenus','role_name','role_id','ids'));
  }

  public function storeRoleMenu(Request $request)
  {
    RoleMenu::where('role_id',$request['role_id'])->delete();
    foreach(array_combine($request['menus'], $request['parents']) as $menu => $parent)
    {
      $rolemenu=new RoleMenu();
      $rolemenu->menu_id=$menu;
      $rolemenu->parent_id=$parent;
      $rolemenu->role_id=$request['role_id'];
      $rolemenu->save();
    }
    foreach(array_combine($request->order,$request->ids) as $order=>$id)
    {
      RoleMenu::where('role_id',$request['role_id'])->where('parent_id',$id)->update(['order'=>$order]);
    }
    return response(['success'=>true]);
  }

  public function storeRoleAccess(Request $request)
  {
    $role_id=$request->role_id;
    DB::table('global_access_for_roles')->where('role_id',$role_id)->update(['status'=>0]);

    if(isset($request['menus']))
    {
      foreach($request['menus'] as  $access)
      {
        GlobalAccessForRole::where('id',$access)->update(['status'=>1]);
      }
    }
    return response(['success'=>true]);
  }

  public function updateQuoteConfig(Request $request)
  {
    $checkConfig = QuotationConfig::where('section','quotation')->first();
    if($checkConfig)
    {
      $settings = unserialize($checkConfig->print_prefrences);
      $length = count($request->menus);
      for($i = 0; $i < $length; $i++)
      {
        if($settings[$i]['slug'] == $request->menus[$i])
        {
          $settings[$i]['status'] = $request->menu_stat[$i];
        }
      }
    }

    $checkConfig->print_prefrences = serialize($settings);
    $checkConfig->save();
    $checkConfig = QuotationConfig::where('section','target_ship_date')->first();
    $target_ship_date=[];
    foreach(array_combine($request->target_ship_date,$request->target_ship_date_status) as $date=> $status)
    {
      $target_ship_date[$date]=$status;
    }
    $checkConfig->print_prefrences=serialize($target_ship_date);
    $checkConfig->save();
    return response(['success'=>true]);
  }

  public function updatePrintConfig(Request $request)
  {
    DB::table('global_access_for_roles')->where('type','quote_print')->update(['status'=>0]);

    if(isset($request['menus']))
    {
      foreach($request['menus'] as  $access)
      {
        GlobalAccessForRole::where('id',$access)->update(['status'=>1]);
      }
    }
    return response(['success'=>true]);
  }

  public function showCompany()
  {
    $countries = Country::get();
    return $this->render('backend.company.index',compact('countries'));
  }

  public function updateCompanyLogo(Request $request)
  {
    $company = Company::find($request->comapny_id);
    if($request->hasFile('company_logo'))
    {
      $fileNameWithExt = $request->file('company_logo')->getClientOriginalName();
      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
      $extension = $request->file('company_logo')->getClientOriginalExtension();
      $fileNameToStore = $fileName.'_'.time().'.'.$extension;

      $path = $request->file('company_logo')->move('public/uploads/logo/',$fileNameToStore);
      $company->logo = $fileNameToStore;
      $company->save();
    }
    return response()->json(['error'=>false]);
  }

  public function getCountryStatesBackend(Request $request)
  {
    $getCompany = Company::find($request->comapny_id);
    $getCompany->billing_state = '';
    $getCompany->save();

    $states = State::where('country_id',$request->country_id)->get();
    $html_string = '';
    if($states)
    {
        $html_string .= '<option>States</option>';
        foreach ($states as $state)
        {
            $html_string.='<option value="'.$state->id.'">'.$state->name.'</option>';
        }
    }

    return response()->json([
        'html_string' => $html_string
    ]);
  }

  public function addNewCompany()
  {
    $addCompany = new Company;
    $addCompany->created_by = Auth::user()->id;
    $addCompany->counter = 1;
    $addCompany->save();
    return redirect()->route("company-detail-info",$addCompany->id);
  }

  public function companyDetailInfo($id)
  {
    $addCompany = Company::find($id);
    $states = State::select('id','name')->where('country_id',$addCompany->billing_country)->get();
    $countries = Country::get();
    $banks = Bank::select('id','title')->get();
    $customer_categories = CustomerCategory::select('id','title')->where('is_deleted',0)->get();
    return $this->render('backend.company.add-company',compact('countries','customer_categories','addCompany','states','banks'));
  }

  public function addCompanyBanks(Request $request)
  {
    $getOldRecord = CompanyBank::where('company_id',$request->comp_id)->get();
    if($getOldRecord->count() > 0)
    {
      foreach ($getOldRecord as $value)
      {
        $value->delete();
      }
    }

    for ($i=0; $i<sizeof($request->category_id); $i++)
    {
      for ($b=0; $b<sizeof($request->banks[$i]); $b++)
      {
        $company_banks                       = new CompanyBank;
        $company_banks->company_id           = $request->comp_id;
        $company_banks->customer_category_id = $request->category_id[$i];
        $company_banks->bank_id = $request->banks[$i][$b];
        $company_banks->save();
      }
    }

    return response()->json(['success' => true]);
  }

  public function saveCompanyData(Request $request)
  {
    $completed = 0;
    $reload    = 0;
    $comapny_detail = Company::find($request->comapny_id);

    foreach($request->except('comapny_id') as $key => $value)
    {
      if($key == 'company_name')
      {
        $checkSameName = Company::where('company_name',$value)->first();
        if($checkSameName)
        {
          $msg = "Company with this name already Exist";
          return response()->json(['success' => false, 'msg' => $msg]);
        }
        else
        {
          $comapny_detail->$key = $value;
        }
      }
      else if($key == 'thai_billing_name')
      {
        $checkSameName = Company::where('thai_billing_name',$value)->first();
        if($checkSameName)
        {
          $msg = "Company with this name already Exist";
          return response()->json(['success' => false, 'msg' => $msg]);
        }
        else
        {
          $comapny_detail->$key = $value;
        }
      }
      else
      {
        $comapny_detail->$key = $value;
      }
    }

    $comapny_detail->save();
    if($comapny_detail->status == 0)
    {
      $request->id = $request->comapny_id;
      $mark_as_complete = $this->doCompanyCompleted($request);
      $json_response = json_decode($mark_as_complete->getContent());
      if($json_response->success == true)
      {
        $company         = Company::find($request->id);
        $company->status = 1;
        $company->save();

        $completed = 1;
        $reload = 1;
      }
    }
    return response()->json(['success' => true, 'completed' => $completed, 'reload' => $reload]);
  }

  public function doCompanyCompleted(Request $request)
  {
    if($request->id)
    {
      $company = Company::find($request->id);
      $missingPrams = array();

      if($company->company_name == null)
      {
        $missingPrams[] = 'company name';
      }

      if($company->thai_billing_name == null)
      {
        $missingPrams[] = 'billing name in thai';
      }

      if($company->tax_id == null)
      {
        $missingPrams[] = 'Tax ID';
      }

      if($company->billing_country == null)
      {
        $missingPrams[] = 'Country';
      }

      if($company->billing_state == null)
      {
        $missingPrams[] = 'State';
      }

      if($company->billing_city == null)
      {
        $missingPrams[] = 'City';
      }

      if($company->prefix == null)
      {
        $missingPrams[] = 'Prefix';
      }

      if(sizeof($missingPrams) == 0)
      {
        $company->status = 1;
        $company->save();
        $message = "completed";
        return response()->json(['success' => true, 'message' => $message]);
      }
      else
      {
        $message = implode(', ', $missingPrams);
        return response()->json(['success' => false, 'message' => $message]);
      }
    }
  }

  public function showWarehouses()
  {
    $page_settings = QuotationConfig::where('section','warehouse_management_page')->first();
    $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();

    if($ecommerceconfig)
    {
      $check_status =  unserialize(@$ecommerceconfig->print_prefrences);
      $ecommerceconfig_status = $check_status['status'][0];
    }
    return $this->render('backend.warehouse.warehouses',compact('page_settings','ecommerceconfig_status'));
  }

  public function addCompany(Request $request)
  {
    $validator = $request->validate([
      'company_name' => 'required|unique:companies',
    ]);

    $company                  = new Company;
    if($request->hasFile('logo') && $request->logo->isValid())
    {
      $fileNameWithExt = $request->file('logo')->getClientOriginalName();
      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
      $extension = $request->file('logo')->getClientOriginalExtension();
      $fileNameToStore = $fileName.'_'.time().'.'.$extension;
      $path = $request->file('logo')->move('public/uploads/logo/',$fileNameToStore);
      $company->logo = $fileNameToStore;
    }
    $company->company_name    = $request->company_name;
    $company->billing_email   = $request->billing_email;
    $company->tax_id   = $request->tax_id;
    $company->billing_phone   = $request->billing_phone;
    $company->billing_fax     = $request->billing_fax;
    $company->billing_address = $request->billing_address;
    $company->billing_country = $request->billing_country;
    $company->billing_state   = $request->billing_state;
    $company->billing_city    = $request->billing_city;
    $company->billing_zip    = $request->billing_zip;
    $company->created_by    = Auth::user()->id;
    $company->save();
    return response()->json(['success' => true]);
  }

  public function showCouriers()
  {
    $courier_types = CourierType::select('id', 'type')->where('status', 1)->get();
    return $this->render('backend.couriers.index', compact('courier_types'));
  }

  public function getCompanyData()
  {
    $query = Company::all();

    return Datatables::of($query)
    ->addColumn('action', function ($item) {
      $html_string = '<div class="icons">'.'
        <a href="'.url('admin/company-detail-info/'.$item->id).'" class="actionicon editIcon text-center" title="Detail"><i class="fa fa-eye"></i></a>
      </div>';
      return $html_string;
    })
    ->addColumn('logo', function ($item) {
      $url= asset('public/uploads/logo/'.$item->logo);
      return '<img src="'.$url.'" border="0" width="80" height="80" class="img-rounded" align="center" />';
    })
    ->addColumn('billing_country', function ($item) {
      return $item->billing_country !== null ? $item->getcountry->name : '--';
    })
    ->addColumn('tax_id', function ($item) {
      return $item->tax_id !== null ? $item->tax_id : '--';
    })
    ->addColumn('billing_state', function ($item) {
      return $item->billing_state !== null ? $item->getstate->name : '--';
    })
    ->addColumn('thai_billing_name', function ($item) {
      return $item->thai_billing_name !== null ? $item->thai_billing_name : '--';
    })
    ->addColumn('thai_billing_address', function ($item) {
      return $item->thai_billing_address !== null ? $item->thai_billing_address : '--';
    })
    ->setRowId(function ($item) {
      return $item->id;
    })
    ->rawColumns(['action','logo'])
    ->make(true);
  }

  public function editCompany(Request $request)
  {
    $company = Company::find($request->id);
    $getVar = Variable::select('slug','standard_name','terminology')->where('slug','company_name')->first();
    $global_terminologies[] = '';
    if($getVar->terminology != NULL)
    {
      $global_terminologies['company_name'] = $getVar->terminology;
    }
    else
    {
      $global_terminologies['company_name'] = $getVar->standard_name;
    }

    $html = '';
    $html = $html . '
    <h3 class="text-capitalize fontmed">Edit Company</h3>
    <form method="post" action="" class="edit_comp_form" enctype="multipart/form-data">
        ' . csrf_field() . '
        <input type="hidden" name="company_id" value="'.$company->id.'">
      <div class="form-row">
      <div class="form-group col-6 input-group">
            <label class="col-12 p-0"><strong class="pull-left">'.$global_terminologies["company_name"].'</strong> </lable>
            <input type="text" name="company_name" value="'.$company->company_name.'" class=" form-control-lg form-control" placeholder="Enter '.$global_terminologies["company_name"].'">
          </div>
          <div class="form-group col-6 input-group">
      <label class="col-12 p-0"><strong class="pull-left">Email</strong> </lable>

          <input type="email" name="billing_email" value="'.$company->billing_email.'" class=" form-control-lg form-control" placeholder="Enter Company Email">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6 input-group">
      <label class="col-12 p-0"><strong class="pull-left">Phone No.</strong> </lable>
            <input type="text" name="billing_phone" value="'.$company->billing_phone.'" class=" form-control-lg form-control" placeholder="Enter Company Phone">
          </div>
          <div class="form-group col-6 input-group">
      <label class="col-12 p-0"><strong class="pull-left">Fax</strong> </lable>
          <input type="text" name="billing_fax" value="'.$company->billing_fax.'" class=" form-control-lg form-control" placeholder="Enter Company Fax">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6 input-group">
        <label class="col-12 p-0"><strong class="pull-left">Company Address</strong> </lable>
            <input type="text" name="billing_address" value="'.$company->billing_address.'" class=" form-control-lg form-control" placeholder="Enter Company Address">
          </div>
          <div class="form-group col-6 input-group">
          <label class="col-12 p-0"><strong class="pull-left">Zip</strong> </lable>
          <input type="text" name="billing_zip" value="'.$company->billing_zip.'" class=" form-control-lg form-control" placeholder="Enter Company ZIP">
        </div>
      </div>


      <div class="form-row">
      <div class="form-group col-6 input-group">
      <label class="col-12 p-0"><strong class="pull-left">District</strong> </lable>
        <input type="text" name="billing_city" value="'.$company->billing_city.'" class=" form-control-lg form-control" placeholder="Enter Company District">
      </div>

      <div class="form-group col-6">
      <label class="col-12 p-0"><strong class="pull-left">City</strong> </lable>
       <select class=" form-control-lg form-control" id="state2" name="billing_state">
      <option selected disabled>Select District</option>
      ';
        if($company->billing_country != null)
        {
            $states = State::where('country_id',$company->billing_country)->get();
            foreach ($states as $state)
            {
                if($company->billing_state != null)
                {
                    if($company->billing_state == $state->id)
                    {
                        $html = $html.'<option selected value="'.$state->id.'">'.$state->name.'</option>';
                    }
                    else
                    {
                        $html = $html.'<option value="'.$state->id.'">'.$state->name.'</option>';
                    }
                }
            }
        }
      $html= $html.'
      </select>
      </div>
      </div>

      <div class="form-row">
          <div class="form-group col-6">
          <label class="col-12 p-0"><strong class="pull-left">Country</strong> </lable>
          <select class=" form-control-lg form-control country" name="billing_country">
          <option selected disabled>Select Country</option>
           ';
            $countries = Country::all();
            foreach ($countries as $country)
            {
                if($company->billing_country == $country->id)
                {
                    $html = $html.'<option selected value="'.$country->id.'">'.$country->name.'</option>';
                }
                else
                {
                    $html = $html.'<option value="'.$country->id.'">'.$country->name.'</option>';
                }
            }

          $html = $html.'
          </select>
          </div>


           <div class="form-group col-6 input-group">
           <label class="col-12 p-0"><strong class="pull-left">Tax ID</strong> </lable>
            <input type="text" name="tax_id" value="'.$company->tax_id.'" class=" form-control-lg form-control" placeholder="Enter Company Tax ID">
          </div>

      </div>

      <div class="form-row">
           <div class="form-group col-12 input-group">
           <label class="col-12 p-0"><strong class="pull-left">Bank Address</strong> </label>
            <textarea name="bank_address" id="bank_address" class="form-control-lg form-control" placeholder="Enter Bank Address" rows="4" cols="50">'.@$company->bank_detail.'</textarea>
          </div>

      </div>

      <div class="form-row">
       <div class="form-group col-6 input-group">
           <label class="col-12 p-0"><strong class="pull-left">Company Logo</strong> </lable>
           <img src="'.url('public/uploads/logo/'.@$company->logo.'').'" class="pull-left col-12 p-0 mb-2 mb-1" style="width:270px !important;height:120px;"/>
            <input type="file" class="form-control form-control-lg" name="logo">
          </div></div>';

     $html = $html.'
      <div class="form-row">
      <div class="form-group col-12">
      <div class="form-submit">
          <input type="submit" value="update" class="btn btn-bg save-btn" id="edit_company">
          <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
      </div>
      </div>
      </div>
    </form>
    ';

    return $html;
  }

  public function updateCompany(Request $request)
  {
    $companies = Company::where('company_name',$request->company_name)->where('id','!=',$request->company_id)->get();
    if(@$companies->count() != 0)
    {
      $validator = $request->validate([
        'company_name' => 'required|unique:companies',
      ]);
    }
    $company_id = $request->company_id;
    $company = Company::find($company_id);
    if($request->hasFile('logo') && $request->logo->isValid())
    {
      $fileNameWithExt = $request->file('logo')->getClientOriginalName();
      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
      $extension = $request->file('logo')->getClientOriginalExtension();
      $fileNameToStore = $fileName.'_'.time().'.'.$extension;
      $path = $request->file('logo')->move('public/uploads/logo/',$fileNameToStore);
      $company->logo = $fileNameToStore;
    }
    $company->company_name    = $request->company_name;
    $company->billing_email   = $request->billing_email;
    $company->tax_id   = $request->tax_id;
    $company->billing_phone   = $request->billing_phone;
    $company->billing_fax     = $request->billing_fax;
    $company->billing_address = $request->billing_address;
    $company->billing_country = $request->billing_country;
    $company->billing_state   = $request->billing_state;
    $company->billing_city    = $request->billing_city;
    $company->billing_zip     = $request->billing_zip;
    $company->bank_detail     = $request->bank_address;
    $company->created_by      = Auth::user()->id;
    $company->save();
    return response()->json(['success' => true]);
  }

  public function addCourier(Request $request)
  {
    $query = Courier::where('is_deleted',1)->where('title',$request->title)->first();
    if($query)
    {
      $query->is_deleted = 0;
      $query->save();
     return response()->json(['success' => true]);
    }
    $validator = $request->validate([
      'title' => 'required|unique:couriers',
    ]);

    $courier = new Courier;
    $courier->title = $request->title;
    $courier->courier_type_id = $request->courier_type_select;
    $courier->is_deleted = 0;
    $courier->save();
    return response()->json(['success' => true]);
  }

  public function getCourierData()
  {
    $query = Courier::where('is_deleted',0)->get();
    return Datatables::of($query)
    ->addColumn('action', function ($item) {
      $html_string = '<div class="icons">'.'
        <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
        <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-icon" title="Delete"><i class="fa fa-ban"></i></a>
      </div>';
      return $html_string;
    })
    ->addColumn('created_at', function ($item) {
      return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
    })
    ->addColumn('updated_at', function ($item) {
      return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';
    })
    ->addColumn('type', function ($item) {
      return $item->courier_type != null ? $item->courier_type->type : '--';
    })
  	->setRowId(function ($item) {
      return $item->id;
    })
    ->rawColumns(['action','created_at','updated_at', 'type'])
    ->make(true);
  }

  public function editCourier(Request $request)
  {
    $courier = Courier::find($request->editid);
    if(strtolower($courier->title) != strtolower($request['title']))
    {
      $validator = $request->validate([
        'title' => 'required|unique:couriers',
      ]);
    }

    $courier->title = $request['title'];
    $courier->courier_type_id = $request['courier_type_select'];
    $courier->save();
    return response()->json(["success"=>true]);
  }

  public function deleteCourier(Request $request)
  {
    $suspend = Courier::where('id', $request->id)->update(['is_deleted' => 1]);
    return response()->json(['error' => false, 'successmsg' => 'Courier has been blocked']);
  }

  public function showDocNoSettings()
  {
    return $this->render('backend.statuses.doc-number-settings');
  }

  public function getDocNoSettings()
  {
    $query = Status::where('parent_id',0)->get();

    return Datatables::of($query)
    ->addColumn('title', function ($item) {
      $html_string = '<div>'.$item->title.'</div>';
      return $html_string;
    })

    ->addColumn('prefix', function ($item) {
      $html_string = '
      <span class="m-l-15 inputDoubleClick"  data-id="'.$item->id.'" data-fieldvalue="'.@$item->prefix.'">';
      $html_string .= @$item->prefix != null ? $item->prefix :'--';
      $html_string .= '</span>';

      $html_string .= '<input type="text"  name="prefix" style="width: 100%;" class="fieldFocus d-none" value="'.@$item->prefix.'">';
      return $html_string;
    })

    ->addColumn('counter_formula', function ($item) {
      return $item->counter_formula != null ? $item->counter_formula :'--';;
      $html_string = '
      <span class="m-l-15 inputDoubleClick"  data-id="'.$item->id.'" data-fieldvalue="'.@$item->counter_formula.'">';
      $html_string .= @$item->counter_formula != null ? $item->counter_formula :'--';
      $html_string .= '</span>';

      $html_string .= '<input type="text"  name="counter_formula" style="width: 100%;" class="fieldFocus d-none" value="'.@$item->counter_formula.'">';
      return $html_string;
    })
    ->setRowId(function ($item) {
      return $item->id;
    })
    ->rawColumns(['action','title','prefix','counter_formula'])
    ->make(true);
  }

  public function updateDocNoSettings(Request $request)
  {
    $status = Status::find($request->status_id);
    foreach($request->except('status_id') as $key => $value)
    {
      $status->$key = $value;
    }
    $status->save();
    return response()->json(['status' => true]);
  }

  public function completeProfile()
  {
    $check_profile_completed = UserDetail::where('user_id',Auth::user()->id)->count();
    if($check_profile_completed > 0)
    {
      return redirect()->back();
    }
    $countries = Country::get();
    return $this->render('backend.home.profile-complete', compact('countries'));
  }

  public function completeProfileProcess(Request $request)
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

  public function importHistoryFunction()
  {
    return view('backend.import_history.import_history');
  }

  public function reservedQuantityHistory()
  {
    return view('backend.import_history.reserved-quantity-history');
  }

  public function getHistories()
  {
    $query = ImportFileHistory::with('User')->orderBy('id','DESC');
    return Datatables::of($query)

    ->addColumn('file',function($item){
      $html = '<a href="'.url('public/uploads/import_files/'.$item->file_name).'" title="View Detail" download><b>'.$item->file_name.'</b></a>';
      return $html;
    })
    ->addColumn('created_at', function ($item) {
      return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
    })

    ->addColumn('page_name', function ($item) {
      return $item->page_name != null ? $item->page_name : '--';
    })
    ->addColumn('user_name', function ($item){
      return $item->user_id != null ? $item->User->name : '--';
    })
    ->addColumn('time', function ($item){
      return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('H:i:s') : '--';
    })
    ->rawColumns(['file','created_at','page_name','user_name'])
    ->make(true);
  }

  public function addRole(Request $request)
  {
    $validator = $request->validate([
      'name'   => 'required|unique:roles',
    ]);

    $role = new Role;
    $role->name = $request->name;
    $role->privilege = $request->role_privilege;
    $role->save();

    return response()->json(['success' => true]);
  }

  public function changePassword()
  {
    return view('backend.password-management.index');
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

  public function profile()
  {
  	$user_states=[];
  	$countries = Country::orderBy('name','ASC')->get();
    $user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
    if($user_detail)
    {
    	$user_states= State::where('country_id',$user_detail->country_id)->get();
	  }
  	return view('backend.profile-setting.index',['countries'=>$countries,'user_detail'=>$user_detail,'user_states'=>$user_states]);
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

    $error = false;
    $user = User::where('id',Auth::user()->id)->first();
    if($user)
	  {
		  $user->name=$request['name'];
			$user->save();
		  $user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
		  if($user_detail)
	    {
				$user_detail->address 		= $request['address'];
				$user_detail->country_id	= $request['country'];
				$user_detail->state_id 		= $request['state'];
				$user_detail->city_name 	= $request['city'];
				$user_detail->zip_code 		= $request['zip_code'];
				$user_detail->phone_no 		= $request['phone_number'];
				$user_detail->company_name	= $request['company'];
			  if($request->hasFile('image') && $request->image->isValid())
			  {
		      $fileNameWithExt = $request->file('image')->getClientOriginalName();
		      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
		      $extension = $request->file('image')->getClientOriginalExtension();
		      $fileNameToStore = $fileName.'_'.time().'.'.$extension;
		      $path = $request->file('image')->move('public/uploads/admin/images/',$fileNameToStore);
		      $user_detail->image = $fileNameToStore;
			  }

			  $user_detail->save();
				return response()->json([
					"error"=>$error
				]);
		  }
      else
      {
        $user_detail = new UserDetail;
        $user_detail->user_id = Auth::user()->id;
        $user_detail->address 		= $request['address'];
				$user_detail->country_id	= $request['country'];
				$user_detail->state_id 		= $request['state'];
				$user_detail->city_name 	= $request['city'];
				$user_detail->zip_code 		= $request['zip_code'];
				$user_detail->phone_no 		= $request['phone_number'];
				$user_detail->company_name	= $request['company'];

				if($request->hasFile('image') && $request->image->isValid())
				{
		      $fileNameWithExt = $request->file('image')->getClientOriginalName();
		      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
		      $extension = $request->file('image')->getClientOriginalExtension();
		      $fileNameToStore = $fileName.'_'.time().'.'.$extension;
		      $path = $request->file('image')->move('public/uploads/admin/images/',$fileNameToStore);
		      $user_detail->image = $fileNameToStore;
				}

			  $user_detail->save();

				return response()->json([
					"error"=>$error
				]);
      }
	  }
  }

  public function suspend(Request $request)
  {
    $suspend = User::whereHas('roles', function($query) use ($request){
      $query->where('name', '=', $request->type);
    })->where('id', $request->id)->update(['status' => 2]);

    $suspenduser = User::find($request->id);
    $template = EmailTemplate::where('type', 'account-suspension')->first();
    // send email //
    $my_helper =  new MyHelper;
    $result_helper=$my_helper->getApiCredentials();
    if($result_helper['email_notification'] == 1)
    {
      Mail::to($suspenduser->email, $suspenduser->name)->send(new UserAccountSuspensionEmail($suspenduser, $template));
    }
    return response()->json(['error' => false, 'successmsg' => ucfirst($request->type).' blocked.']);
  }

  public function activate(Request $request)
  {
    $activate = User::whereHas('roles', function($query) use ($request){
      $query->where('name', '=', $request->type);
    })->where('id', $request->id)->update(['status' => 1]);

    $activateuser = User::find($request->id);

    // get activation email //
    $template = EmailTemplate::where('type', 'account-activation')->first();

    // send email //
    $my_helper =  new MyHelper;
    $result_helper=$my_helper->getApiCredentials();
    if($result_helper['email_notification'] == 1)
    {
      Mail::to($activateuser->email, $activateuser->name)->send(new UserAccountActivationEmail($activateuser, $template));
    }
    return response()->json(['error' => false, 'successmsg' => ucfirst($request->type).' activated.']);
  }

  public function UpdateWarehouseOnProductLevel(Request $request)
  {
    $getWarehouses = Warehouse::where('status',1)->get();
    $getProducts = Product::all();

    foreach ($getProducts as $product)
    {
      foreach ($getWarehouses as $warehouse)
      {
        $checkWarehouse = WarehouseProduct::where('product_id',$product->id)->where('warehouse_id',$warehouse->id)->first();
        if($checkWarehouse)
        {
          // do nothing
        }
        else
        {
          $addNew = new WarehouseProduct;
          $addNew->warehouse_id = $warehouse->id;
          $addNew->product_id   = $product->id;
          $addNew->save();
        }
      }
    }
    return response()->json(['success' => true]);
  }

  public function getSaleDashboard()
  {
    $customers = Customer::where('status',1)->get();
    return $this->render('backend.dashboards.sale_dashboard',compact('customers'));
  }

  public function getCompletedQuotationsDataAdmin(Request $request)
  {
    if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
    {
      $warehouse_id = Auth::user()->warehouse_id;
      $users = User::select('id')->where('warehouse_id',$warehouse_id)->where(function($query){
        $query->where('role_id',3)->orWhere('role_id',4);
      })->whereNull('parent_id')->get();
      $query = Customer::query();
      $ids = array();
      foreach ($users as $user)
      {
        array_push($ids, $user->id);
      }

      $query = Order::with('customer')->whereIn('user_id', $ids)->orderBy('id','DESC');
    }
    else
    {
      $query = Order::with('customer')->where('user_id', auth()->user()->id)->orderBy('id','DESC');
    }

    if($request->dosortby == 1)
    {
      $query->where(function($q){
        $q->where('primary_status', 1)->orderBy('orders.id', 'DESC');
      });
    }
    else if($request->dosortby == 2)
    {
      $query->where(function($q){
        $q->where('primary_status', 2)->orderBy('id', 'DESC');
      });
    }
    else if($request->dosortby == 3)
    {
      $query->where(function($q){
        $q->where('primary_status', 3)->orderBy('id', 'DESC');
      });
    }

    if($request->dosortby == 6)
    {
      $query->where(function($q){
        $q->where('primary_status', 1)->where('status', 6)->orderBy('id', 'DESC');
      });
    }
    else if($request->dosortby == 7)
    {
      $query->where(function($q){
        $q->where('primary_status', 2)->where('status', 7)->orderBy('id', 'DESC');
      });
    }
    else if($request->dosortby == 8)
    {
      $query->where(function($q){
        $q->where('primary_status', 2)->where('status', 8)->orderBy('id', 'DESC');
      });
    }
    else if($request->dosortby == 9)
    {
      $query->where(function($q){
        $q->where('primary_status', 2)->where('status', 9)->orderBy('id', 'DESC');
      });
    }
    else if($request->dosortby == 10)
    {
      $query->where(function($q){
        $q->where('primary_status', 2)->where('status', 10)->orderBy('id', 'DESC');
      });
    }
    else if($request->dosortby == 11)
    {
      $query->where(function($q){
        $q->where('primary_status', 3)->where('status', 11)->orderBy('id', 'DESC');
      });
    }
    if($request->selecting_customer != null)
    {
      $query->where('customer_id', $request->selecting_customer);
    }
    if($request->from_date != null)
    {
      $query->where('orders.created_at', '>', $request->from_date.' 00:00:00');
    }
    if($request->to_date != null)
    {
      $query->where('orders.created_at', '<', $request->to_date.' 00:00:00');
    }
    return Datatables::of($query)

    ->addColumn('customer', function ($item) {
      if($item->customer_id != null)
      {
        if(Auth::user()->role_id == 3)
        {
          if($item->customer['company'] != null)
          {
            $html_string = '
            <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'.$item->customer['company'].'</a>';
          }
          else
          {
            $html_string = '
            <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
                  // $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
          }
        }
        else
        {
          if($item->customer['company'] != null)
          {
            $html_string = '
            <a href="'.url('common/get-common-customer-detail/'.@$item->customer_id).'">'.$item->customer['company'].'</a>';
          }
          else
          {
            $html_string = '
            <a href="'.url('common/get-common-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
          }
        }
      }
      else
      {
        $html_string = 'N.A';
      }
      return $html_string;
    })

    ->addColumn('customer_ref_no',function($item){
      if(Auth::user()->role_id == 3)
      {
        return '<a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'.$item->customer->reference_number.'</a>';
      }
      else
      {
        return '<a href="'.url('common/get-common-customer-detail/'.@$item->customer_id).'">'.$item->customer->reference_number.'</a>';
      }
    })

    ->addColumn('target_ship_date',function($item){
      return Carbon::parse(@$item->target_ship_date)->format('d/m/Y');
    })

    ->addColumn('status',function($item){
      $html = '<span class="sentverification">'.$item->statuses->title.'</span>';
      return $html;
    })

    ->addColumn('number_of_products', function($item) {
      $html_string = $item->order_products->count();
      return $html_string;
    })
    ->addColumn('ref_id', function($item) {
      return ($item->user_ref_id !== null ? $item->user_ref_id : $item->ref_id);
    })
    ->addColumn('payment_term', function($item) {
      return ($item->customer->getpayment_term->title);
    })
    ->addColumn('invoice_date', function($item) {
      return Carbon::parse(@$item->updated_at)->format('d/m/Y');
    })
    ->addColumn('total_amount', function($item) {
      return number_format($item->total_amount,2,'.',',');
    })
    ->addColumn('action', function ($item) {
      $html_string = '<a href="'.route('admin-get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon"><i class="fa fa-eye"></i>';
      return $html_string;
    })
   ->rawColumns(['action', 'customer', 'number_of_products','status','customer_ref_no'])
   ->make(true);
  }

  public function getPendingQuotationsDataAdmin(Request $request)
  {
    $query = DraftQuotation::with('customer')->orderBy('id', 'DESC')->whereNotNull('draft_quotations.customer_id')->where('created_by',Auth::user()->id);
    if($request->selecting_customer != null)
    {
      $query->where('customer_id', $request->selecting_customer);
    }
    if($request->from_date != null)
    {
      $query->where('draft_quotations.created_at', '>', $request->from_date.' 00:00:00');
    }
    if($request->to_date != null)
    {
      $query->where('draft_quotations.created_at', '<', $request->to_date.' 00:00:00');
    }
    return Datatables::of($query)
    ->addColumn('checkbox', function ($item) {
      $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
        <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="stone_check_'.$item->id.'">
        <label class="custom-control-label" for="stone_check_'.$item->id.'"></label>
      </div>';
      return $html_string;
    })
    ->addColumn('customer', function ($item) {
      if($item->customer_id != null)
      {
        if($item->customer['company'] != null)
        {
          $html_string = '<a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'.$item->customer['company'].'</a>';
        }
        else
        {
          $html_string = '<a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
        }
      }
      else
      {
        $html_string = 'N.A';
      }
      return $html_string;
    })
    ->addColumn('customer_ref_no',function($item){
      return '<a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'.$item->customer->reference_number.'</a>';
    })
    ->addColumn('number_of_products', function($item) {
      $html_string = $item->draft_quotation_products->count();
      return $html_string;
    })
    ->addColumn('status',function($item){
      $html = '<span class="sentverification">Unfinished Quotation</span>';
      return $html;
    })
    ->addColumn('ref_id', function($item) {
      return ($item->id);
    })
    ->addColumn('payment_term', function($item) {
      return ($item->customer->getpayment_term->title);
    })
    ->addColumn('invoice_date', function($item) {
      return Carbon::parse(@$item->updated_at)->format('d/m/Y');
    })
    ->addColumn('total_amount', function($item) {
      $total_amount = $item->draft_quotation_products->sum('total_price_with_vat');
      $total = number_format($total_amount, 2, '.', ',');

      return ($total);
    })
    ->addColumn('action', function ($item) {
      $html_string = '<a href="'.route('get-invoice', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon"><i class="fa fa-eye"></i>
      <a href="#" data-id="'.$item->id.'" class="actionicon delete-btn" title="Delete Draft Quotation"><i class="fa fa-times-circle"></i></a>';
      return $html_string;
    })
    ->rawColumns(['action','checkbox', 'customer', 'number_of_products','status','customer_ref_no'])
    ->make(true);
  }

  public function getDraftInvoiceAdmin()
  {
    $customers = Customer::where('status', 1)->orderBy('id', 'DESC')->get();
    return $this->render('backend.dashboards.draft_invoice_dashboard',compact('customers'));
  }

  public function getInvoiceAdmin()
  {
    $customers = Customer::where('status', 1)->orderBy('id', 'DESC')->get();
    return $this->render('backend.dashboards.invoices_dashboard',compact('customers'));
  }

  public function getCompletedQuotationProductsAdmin($id)
  {
    $states = State::select('id','name')->orderby('name', 'ASC')->where('country_id',217)->get();

    $billing_address = null;
    $shipping_address = null;
    $order = Order::find($id);
    $company_info = Company::where('id',$order->user->company_id)->first();
    if($order->billing_address_id != null)
    {
      $billing_address = CustomerBillingDetail::where('id',$order->billing_address_id)->first();
    }
    if($order->shipping_address_id)
    {
      $shipping_address = CustomerBillingDetail::where('id',$order->shipping_address_id)->first();
    }
    $total_products = $order->order_products->count('id');
    $vat = 0 ;
    $sub_total = 0 ;
    $query = OrderProduct::where('order_id',$id)->get();
    foreach ($query as  $value) {
      $sub_total += $value->quantity * $value->unit_price;
      $vat += $value->total_price_with_vat-$value->total_price;
    }
    $grand_total = ($sub_total)-($order->discount)+($order->shipping)+($vat);
    $status_history = OrderStatusHistory::with('get_user')->where('order_id',$id)->get();
    $checkDocs = OrderAttachment::where('order_id',$order->id)->get()->count();
    $inv_note = OrderNote::where('order_id', $order->id)->first();
    return view('backend.invoice.completed-quotation-products', compact('order','company_info','total_products','sub_total','grand_total','status_history','vat', 'id','checkDocs','inv_note','billing_address','shipping_address','states'));
  }

  public function getProductsDataAdmin($id)
  {
    $query = OrderProduct::with('product','get_order','product.units','product.supplier_products')->where('order_id', $id)->orderBy('id', 'ASC');
    return Datatables::of($query)

    ->addColumn('action', function ($item) {
      $html_string = '';
      if($item->product_id == null && $item->is_billed != "Inquiry")
      {
        $html_string = '<a href="javascript:void(0);" class="actionicon viewIcon add-as-product" data-id="' . $item->id . '" title="Add as New Product "><i class="fa fa-envelope"></i></a>';
      }
      $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
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
        if(Auth::user()->role_id == 3)
        {
          return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$product->refrence_code.'</b></a>';
        }
        else
        {
          return  $html_string = $reference_code;
        }
      }
    })

    ->addColumn('description',function($item){
      $html = '<span class="inputDoubleClick" data-fieldvalue="'.$item->short_desc.'">'.($item->short_desc != null ? $item->short_desc : "--" ).'</span><input type="text" name="short_desc" value="'.$item->short_desc.'"  class="short_desc form-control input-height d-none" style="width:100%">';
      return $html;
    })
    ->addColumn('brand',function($item){
      $html = '<span class="inputDoubleClick" data-fieldvalue="'.$item->brand.'">'.($item->brand != null ? $item->brand : "--" ).'</span><input type="text" name="brand" value="'.$item->brand.'" min="0" class="brand form-control input-height d-none" style="width:100%">';
      return $html;
    })
    ->addColumn('number_of_pieces',function($item){
      $html = '<span class="inputDoubleClick" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span><input type="number" name="number_of_pieces"  value="'.$item->number_of_pieces.'" class="number_of_pieces form-control input-height d-none" style="width:100%; border-radius:0px;">';
      return $html;
    })
    ->addColumn('sell_unit',function($item){
      return $item->product && $item->product->units ? $item->product->units->title : "N.A";
    })
    ->addColumn('buying_unit',function($item){
      return ($item->product && $item->product->units !== null ? $item->product->units->title : "N.A");
    })
    ->addColumn('quantity',function($item){
      $html = '<span class="inputDoubleClick" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span><input type="number" name="quantity"  value="'.$item->quantity.'" class="quantity form-control input-height d-none" style="width:100%; border-radius:0px;">';
      return $html;
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
      $star = '';
      if(is_numeric($item->margin))
      {
        $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
        if($product_margin)
        {
          $star = '*';
        }
      }
      $unit_price = number_format($item->unit_price, 2, '.', '');
      $html = '<span class="inputDoubleClick" data-fieldvalue="'.@$unit_price.'">'.$star.number_format($unit_price, 2, '.', ',').'</span><input type="number" name="unit_price" step="0.01"  value="'.$unit_price.'" class="unit_price form-control input-height d-none" style="width:100%;  border-radius:0px;">';
      return $html;
    })
    ->addColumn('total_price',function($item){
      if($item->total_price == null)
      {
        return $total_price = "N.A";
      }
      else
      {
        $total_price = $item->total_price;
      }
      $html_string ='<span class="total-price total-price-'.$item->id.'"">'.number_format($total_price, 2, '.', ',').'</span>';
      return $html_string;
    })
    ->addColumn('vat',function($item){
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
    })
    ->addColumn('supply_from',function($item){
      if($item->product_id == null)
      {
        return "N.A";
      }
      else
      {
        $label = $item->from_warehouse != null ? $item->from_warehouse->warehouse_title : 'Select a Warehouse';
        $html =  '<span class="inputDoubleClick">'.@$label.'</span><select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" name="from_warehouse_id" >
              <option selected value="0">Select warehouse</option>';
        $warehouses = Warehouse::where('status',1)->get();
        foreach ($warehouses as $w)
        {
          if($item->from_warehouse_id == $w->id)
          {
            $html = $html.'<option selected value="'.$w->id.'">'.$w->warehouse_title.'</option>';
          }
          else
          {
            $html = $html.'<option value="'.$w->id.'">'.$w->warehouse_title.'</option>';
          }
        }

        $html = $html.'
        </select>';
        return $html;
      }
    })

    ->addColumn('notes', function ($item) {
      // check already uploaded images //
      $notes = OrderProductNote::where('order_product_id', $item->id)->count();

      $html_string = '<div class="d-flex justify-content-center text-center">';
      if($notes > 0)
      {
        $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
      }

      $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a></div>';
      return $html_string;
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
    ->rawColumns(['action','refrence_code','number_of_pieces','quantity','unit_price','total_price','exp_unit_cost','supply_from','notes','description','vat','brand'])
    ->make(true);
  }

  public function customerSalesReport(Request $request, $year = null)
  {
    $sales_years = [];
    $current_year = date('Y');
    for ($i=$current_year; $i >= 2020; $i--)
    {
      $sales_years[$i] = $i;
    }

    $sales_ids = DB::table('customers')->where('secondary_sale_id',Auth::user()->id)->distinct()->pluck('primary_sale_id')->toArray();
    // $sales_person_filter = User::select('id','name')->whereIn('id',$sales_ids)->whereNull('parent_id')->where('role_id',3)->where('status',1)->get();

    $sales_person_filter = User::select('id','name')->whereNull('parent_id')->where('role_id',3)->where('status',1)->get();

    $sales_persons = User::select('id','name')->where('role_id',3)->whereNull('parent_id')->where('status',1)->get();
    $customer_categories = CustomerCategory::where('is_deleted',0)->get();
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
    $file_name=ExportStatus::where('user_id',Auth::user()->id)->where('type','customer_sales_report')->first();
    return view('backend.reports.customer-sales-report' , compact('sales_years','sales_persons','customer_categories','table_hide_columns','months','file_name','sales_person_filter','year'));
  }

  public function getCustomerSalesReport(Request $request)
  {
    $company_total = '';
    $overAllTotal = '';
    $sale_year = $request->sale_year;
    $customers = Customer::where('o.primary_status',3)->where('customers.status',1)
    ->select(DB::raw('sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_amount
    END) AS jan_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_paid
    END) AS jan_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_amount
    END) AS feb_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_paid
    END) AS feb_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_amount
    END) AS mar_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_paid
    END) AS mar_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_amount
    END) AS apr_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_paid
    END) AS apr_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_amount
    END) AS may_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_paid
    END) AS may_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_amount
    END) AS jun_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_paid
    END) AS jun_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_amount
    END) AS jul_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_paid
    END) AS jul_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_amount
    END) AS aug_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_paid
    END) AS aug_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_amount
    END) AS sep_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_paid
    END) AS sep_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_amount
    END) AS oct_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_paid
    END) AS oct_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_amount
    END) AS nov_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_paid
    END) AS nov_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_amount
    END) AS dec_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_paid
    END) AS dec_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" THEN o.total_amount
    END) AS customer_orders_total'),
    'customers.reference_name',
    'customers.id',
    'customers.primary_sale_id',
    'customers.credit_term','o.dont_show','o.user_id','o.customer_id')->whereYear('o.converted_to_invoice_on',$sale_year)->groupBy('o.customer_id');
    $customers->join('orders AS o','customers.id','=','o.customer_id');
    // $customers->get();

    if($request->customer_categories != null)
    {
      $customers = $customers->where('customers.category_id',$request->customer_categories);
    }

    $company_total = $customers;
    if($request->sale_person != null)
    {
      $customers->where('o.user_id',$request->sale_person);
    }
    else
    {
      $customers->where('o.dont_show',0);
    }

    if($request->sale_person_filter != null )
    {
      if(Auth::user()->id != $request->sale_person_filter)
      {
        $customers = $customers->where('o.user_id',$request->sale_person_filter);
      }
      else
      {
        $customers = $customers->where(function($q){
          $q->where('o.user_id',Auth::user()->id);
        });
      }
    }
    elseif(Auth::user()->role_id == 3)
    {
      $user_i = Auth::user()->id;
      $customers = $customers->where(function($or) use ($user_i){
            $or->where('o.user_id', $user_i)->orWhereIn('o.customer_id',$this->user->customer->pluck('id')->toArray())->orWhereIn('o.customer_id', $this->user->user_customers_secondary->pluck('customer_id')->toArray());
        });
    }

    /*********************  Sorting code ************************/
    $sort_variable = '';
    $sort_order = '';
    if($request->sortbyparam == 3 && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'reference_name';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 3 && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'reference_name';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Jan' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'jan_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Jan' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'jan_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Feb' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'feb_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Feb' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'feb_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Mar' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'mar_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Mar' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'mar_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Apr' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'apr_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Apr' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'apr_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'May' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'may_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'May' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'may_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Jun' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'jun_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Jun' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'jun_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Jul' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'jul_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Jul' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'jul_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Aug' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'aug_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Aug' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'aug_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Sep' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'sep_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Sep' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'sep_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Oct' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'oct_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Oct' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'oct_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Nov' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'nov_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Nov' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'nov_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Dec' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'dec_totalAmount';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Dec' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'dec_totalAmount';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'orders' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'customer_orders_total';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'orders' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'customer_orders_total';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'location_code')
    {
      $sort_order = $request->sortbyvalue == 1 ? 'DESC' : 'ASC';
      $customers->leftJoin('users as u', 'u.id', '=', 'customers.primary_sale_id')->join('warehouses as w', 'w.id', '=', 'u.warehouse_id')->orderBy('location_code', $sort_order);
    }

    if($request->sortbyparam == 'sales_person_code')
    {
      $sort_order = $request->sortbyvalue == 1 ? 'DESC' : 'ASC';
      $customers->leftJoin('users as u', 'u.id', '=', 'customers.primary_sale_id')->orderBy('name', $sort_order);
    }

    if($request->sortbyparam == 'payment_term_code')
    {
      $sort_order = $request->sortbyvalue == 1 ? 'DESC' : 'ASC';
      $customers->leftJoin('payment_terms as pt', 'pt.id', '=', 'customers.credit_term')->orderBy('title', $sort_order);
    }
    if($sort_variable != '')
    {
      $customers->orderBy($sort_variable, $sort_order);
    }
    /*********************************************/
    $get_total_customers = (clone $customers)->get();
    $customers->with('primary_sale_person','getpayment_term','primary_sale_person.get_warehouse');
    $ids = [];

    $not_visible_arr=[];
    $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('year',$sale_year)->where('type', 'customer_sale_report')->first();
    if($table_hide_columns!=null)
    {
      $not_visible_arr = explode(',',$table_hide_columns->hide_columns);
    }

    $dt = Datatables::of($customers);
    $add_columns = ['payment_term', 'sale_person', 'orders', 'location_code', 'Dec', 'Nov', 'Oct', 'Sep', 'Aug', 'Jul', 'Jun', 'May', 'Apr', 'Mar', 'Jan', 'Feb'];

    foreach ($add_columns as $column) {
        $dt->addColumn($column, function ($item) use ($column, $not_visible_arr) {
            return Customer::returnAddColumnCustomerReport($column, $item, $not_visible_arr);
        });
    }

    $edit_columns = ['reference_name'];
    foreach ($edit_columns as $column) {
        $dt->editColumn($column, function ($item) use ($column, $sale_year, $not_visible_arr) {
            return Customer::returnEditColumnCustomerReport($column, $item, $sale_year, $not_visible_arr);
        });
    }

    $filter_columns = ['sale_person'];
    foreach ($filter_columns as $column) {
        $dt->filterColumn($column, function ($item, $keyword) use ($column, $not_visible_arr, $sale_year) {
            return Customer::returnFilterColumnCustomerReport($column, $item, $keyword, $not_visible_arr, $sale_year);
        });
    }

      $dt->rawColumns(['checkbox','reference_name','orders' ,'location_code','sale_person','payment_term', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);

      return $dt->make(true);
  }

  public function getCustomerSalesReportFooter(Request $request)
  {
    $company_total = '';
    $overAllTotal = '';
    $sale_year = $request->sale_year;
    $customers = Customer::where('o.primary_status',3)->where('customers.status',1)
    ->select(DB::raw('sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_amount
    END) AS jan_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_amount
    END) AS feb_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_amount
    END) AS mar_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_amount
    END) AS apr_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_amount
    END) AS may_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_amount
    END) AS jun_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_amount
    END) AS jul_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_amount
    END) AS aug_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_amount
    END) AS sep_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_amount
    END) AS oct_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_amount
    END) AS nov_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_amount
    END) AS dec_ComtotalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" THEN o.total_amount
    END) AS customer_orders_total'),
    'customers.reference_name',
    'customers.id',
    'customers.primary_sale_id',
    'customers.credit_term','o.user_id','o.dont_show')->groupBy('o.customer_id');
    $customers->join('orders AS o','customers.id','=','o.customer_id');
    $customers->get();

    if($request->customer_categories != null)
    {
      $customers = $customers->where('customers.category_id',$request->customer_categories);
    }

    $company_total = $customers;
    $get_overall_total  = (clone $company_total)->get();
    $overAllTotal = $get_overall_total->sum('customer_orders_total');

    // if($request->sale_person != null)
    // {
    //   $customers->where('o.user_id',$request->sale_person);
    // }
    // else
    // {
    //   $customers->where('o.dont_show',0);
    // }

    // if($request->sale_person_filter != null )
    // {
    //   if(Auth::user()->id != $request->sale_person_filter)
    //   {
    //     $customers = $customers->where('o.user_id',$request->sale_person_filter);
    //   }
    //   else
    //   {
    //     $customers = $customers->where(function($q){
    //       $q->where('o.user_id',Auth::user()->id);
    //     });
    //   }
    // }
    // elseif(Auth::user()->role_id == 3)
    // {
    //   $customers = $customers->where(function($q){
    //     $q->where('o.user_id',Auth::user()->id);
    //   });
    // }

    $get_total_customers = (clone $customers)->get();
    return response()->json(["jan_com_total" => $get_total_customers->sum('jan_ComtotalAmount'),'fab_com_total' => $get_total_customers->sum('feb_ComtotalAmount'),'mar_com_total' => $get_total_customers->sum('mar_ComtotalAmount'),'apr_com_total' => $get_total_customers->sum('apr_ComtotalAmount'),'may_com_total' => $get_total_customers->sum('may_ComtotalAmount'),'jun_com_total' =>$get_total_customers->sum('jun_ComtotalAmount'),'jul_com_total' => $get_total_customers->sum('jul_ComtotalAmount'),'aug_com_total' => $get_total_customers->sum('aug_ComtotalAmount'),'sep_com_total' => $get_total_customers->sum('sep_ComtotalAmount'),'oct_com_total' => $get_total_customers->sum('oct_ComtotalAmount'),'nov_com_total' => $get_total_customers->sum('nov_ComtotalAmount'),'dec_com_total' => $get_total_customers->sum('dec_ComtotalAmount'),'year_com_total' => $get_total_customers->sum('customer_orders_total')]);
  }

  public function exportCustomerSalesReport(Request $request)
  {
    $months = explode(" ", $request->months);
    $sale_year = $request->sale_year;
    $customers = Customer::where('o.primary_status',3)->where('customers.status',1)
    ->select(DB::raw('sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_amount
    END) AS Jan,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_amount
    END) AS Feb,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_amount
    END) AS Mar,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_amount
    END) AS Apr,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_amount
    END) AS May,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_amount
    END) AS Jun,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_amount
    END) AS Jul,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_amount
    END) AS Aug,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_amount
    END) AS Sep,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_amount
    END) AS Oct,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_amount
    END) AS Nov,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_amount
    END) AS Dece,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" THEN o.total_amount
    END) AS customer_orders_total'),'customers.reference_name','customers.id','customers.primary_sale_id','customers.credit_term')->groupBy('o.customer_id');
    $customers->join('orders AS o','customers.id','=','o.customer_id');
    if($request->sale_person != null )
    {
      $customers = $customers->where('primary_sale_id',$request->sale_person);
    }

    if($request->customer_categories != null)
    {
      $customers = $customers->where('category_id',$request->customer_categories);
    }

    $current_date = date("Y-m-d");

    $selected_year = $request->sale_year;

    /*********************  Sorting code ************************/
    if($request->sortbyparam == 3 && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'reference_name';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 3 && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'reference_name';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Jan' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Jan';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Jan' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Jan';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Feb' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Feb';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Feb' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Feb';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Mar' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Mar';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Mar' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Mar';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Apr' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Apr';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Apr' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Apr';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'May' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'May';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'May' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'May';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Jun' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Jun';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Jun' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Jun';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Jul' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Jul';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Jul' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Jul';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Aug' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Aug';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Aug' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Aug';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Sep' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Sep';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Sep' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Sep';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Oct' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Oct';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Oct' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Oct';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Nov' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Nov';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Nov' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Nov';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam == 'Dec' && $request->sortbyvalue == 1)
    {
      $sort_variable  = 'Dec';
      $sort_order     = 'DESC';
    }
    elseif($request->sortbyparam == 'Dec' && $request->sortbyvalue == 2)
    {
      $sort_variable  = 'Dec';
      $sort_order     = 'ASC';
    }

    if($request->sortbyparam !== null)
    {
      $customers->orderBy($sort_variable, $sort_order);
    }
    else
    {
      $customers->orderBy('reference_name','ASC');
    }
    /*********************************************/
    $customers = $customers->with('primary_sale_person','getpayment_term','primary_sale_person.get_warehouse')->get();

    return \Excel::download(new CustomerSaleReportExport($customers , $selected_year, $months), 'Customer Sale Report '.$current_date.'.xlsx');
  }

  public function exportCustomerSalesReportWithJob(Request $request)
  {
    $statusCheck=ExportStatus::where('type','customer_sales_report')->where('user_id',Auth::user()->id)->first();
    if(Auth::user()->role_id == 3)
    {
      $request['sale_person'] = null;
    }

    if(Auth::user()->role_id != 3)
    {
      $request['sale_person_filter'] = null;
    }
    $data=$request->all();
    if($statusCheck==null)
    {
      $new=new ExportStatus();
      $new->type='customer_sales_report';
      $new->user_id=Auth::user()->id;
      $new->status=1;
      if($new->save())
      {
        CustomerSalesReportJob::dispatch($data,Auth::user()->id,Auth::user()->role_id);
        return response()->json(['status'=>1]);
      }
    }
    else if($statusCheck->status==0 || $statusCheck->status==2)
    {

      ExportStatus::where('type','customer_sales_report')->where('user_id',Auth::user()->id)->update(['status'=>1,'exception'=>null]);
      CustomerSalesReportJob::dispatch($data,Auth::user()->id,Auth::user()->role_id);
      return response()->json(['status'=>1]);
    }
    else
    {
      return response()->json(['msg'=>'Export already being prepared','status'=>2]);
    }
  }

  public function recursiveStatusCheck()
  {
    $status=ExportStatus::where('user_id',Auth::user()->id)->where('type','customer_sales_report')->first();
    return response()->json(['status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
  }

  public function recursiveStatusCheckAccountReceivable()
  {
    $status=ExportStatus::where('user_id',Auth::user()->id)->where('type','account_receivable_export')->first();
    return response()->json(['status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name,'last_downloaded' => $status->last_downloaded]);
  }

  public function checkStatusFirstTimeForAccountReceivableExport()
  {
    $status=ExportStatus::where('type','account_receivable_export')->where('user_id',Auth::user()->id)->first();
    if($status!=null)
    {
      return response()->json(['status'=>$status->status]);
    }
    else
    {
      return response()->json(['status'=>0]);
    }
  }

  public function checkStatusFirstTimeForCustomerSalesReport()
  {
    $status=ExportStatus::where('type','customer_sales_report')->where('user_id',Auth::user()->id)->first();
    if($status!=null)
    {
      return response()->json(['status'=>$status->status]);
    }
    else
    {
      return response()->json(['status'=>0]);
    }
  }

  public function updateProductsConfig(Request $request)
  {
    $checkConfig = QuotationConfig::where('section','products_management_page')->first();
    if($checkConfig)
    {
      $settings = unserialize($checkConfig->print_prefrences);
      $length = count($request->menus);
      for($i = 0; $i < $length; $i++)
      {
        if(isset($settings[$i]) && isset($request->menus[$i]))
        {
          if($settings[$i]['slug'] == $request->menus[$i])
          {
            $settings[$i]['status'] = $request->menu_stat[$i];
          }
        }
      }
    }

    $checkConfig->print_prefrences = serialize($settings);
    $checkConfig->save();
    return response(['success'=>true]);
  }
  public function historyView()
  {
    return view('backend.user-login-activity.index');
  }

  public function getUserLoginDetails(Request $request)
  {
    $query = UserLoginHistory::with('user_detail')->orderBy('id', 'DESC');

    if($request->from_login != null)
    {
      $from_login = str_replace("/","-",$request->from_login);
      $from_login =  date('Y-m-d',strtotime($from_login));

      $query->where('last_login', '>=', $from_login.' 00:00:00');
    }
    if($request->to_login != null)
    {
      $to_login = str_replace("/","-",$request->to_login);
      $to_login =  date('Y-m-d',strtotime($to_login));

      $query->where('last_login', '<=', $to_login.' 23:59:59');
    }

    return Datatables::of($query)
    ->addColumn('username',function($item){
      return $item->user_id != null ? ($item->user_detail->name != null ? $item->user_detail->name : 'N.A') : 'N.A';
    })
    ->filterColumn('username', function( $query, $keyword ) {
      $query->whereHas('user_detail', function($q) use($keyword){
        $q->where('users.name','LIKE', "%$keyword%");
      });
    },true )
    ->addColumn('number_of_logins', function($item) {
      return $item->number_of_login != null ? $item->number_of_login : 'N.A';
    })
    ->addColumn('first_login_date',function($item){
      return $item->first_login != null ? Carbon::parse($item->first_login)->format('d/m/Y h:i A') : "N.A";
    })
    ->addColumn('last_login_date', function($item) {
      return $item->last_login != null ? Carbon::parse($item->last_login)->format('d/m/Y h:i A') : "N.A";
    })
    ->rawColumns(['username','number_of_logins', 'first_login_date', 'last_login_date'])
    ->make(true);
  }

  public function exportUserLoginHistoryList(Request $request)
  {
    $status = ExportStatus::where('type','users_login_history_list')->first();
    if($status == null)
    {
      $new = new ExportStatus();
      $new->user_id = Auth::user()->id;
      $new->type    = 'users_login_history_list';
      $new->status  = 1;
      $new->save();
      UserLoginHistoryJob::dispatch($request->from_login_date_exp, $request->to_login_date_exp, Auth::user()->id);
      return response()->json(['msg'=>"file is exporting.",'status'=>1,'recursive'=>true]);
    }
    elseif($status->status == 1)
    {
      return response()->json(['msg'=>"File is ready to download.",'status'=>2]);
    }
    elseif($status->status == 0 || $status->status == 2)
    {
      ExportStatus::where('type','users_login_history_list')->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);
      UserLoginHistoryJob::dispatch($request->from_login_date_exp, $request->to_login_date_exp, Auth::user()->id);
      return response()->json(['msg'=>"File is donwloaded.",'status'=>1,'exception'=>null]);
    }
  }

  public function recursiveStatusCheckUserLogin()
  {
    $status=ExportStatus::where('type','users_login_history_list')->first();
    return response()->json(['msg'=>"File is now getting prepared",'status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
  }

  public function checkStatusFirstTimeForUserLogin()
  {
    $status=ExportStatus::where('type','users_login_history_list')->where('user_id',Auth::user()->id)->first();
    if($status!=null)
    {
      return response()->json(['status'=>$status->status]);
    }
    else
    {
      return response()->json(['status'=>0]);
    }
  }

  public function getReservedQuantityHistory()
  {
    $query = ProductQuantityHistory::with('User')->orderBy('id','DESC');
    return Datatables::of($query)

    ->addColumn('pf',function($item){
      $html = $item->product != null ? $item->product->refrence_code : 'N.A';
      return $html;
    })
    ->addColumn('ref_id', function ($item) {
      $order = $item->order_id.' ('.$item->order_type.')';

      return $order;
    })

    ->addColumn('quantity', function ($item) {
      return $item->quantity != null ? $item->quantity : '--';
    })
    ->addColumn('desc', function ($item){
      return $item->type != null ? $item->type : '--';
    })
    ->addColumn('user', function ($item){
      return $item->user_id != null ? $item->User->name : '--';
    })
    ->addColumn('warehouse', function ($item){
      return $item->warehouse != null ? $item->warehouse : '--';
    })
    ->addColumn('c_q', function ($item){
      return $item->current_quantity != null ? $item->current_quantity : '--';
    })
    ->addColumn('r_q', function ($item){
      return $item->reserved_quantity != null ? $item->reserved_quantity : '--';
    })
    ->addColumn('a_q', function ($item){
      return $item->available_quantity != null ? $item->available_quantity : '--';
    })
    ->rawColumns(['pf','ref_id','quantity','desc','user','warehouse','c_q','r_q','a_q'])
    ->make(true);
  }
// general footer
public function getCustomerSalesReportGeneralFooter(Request $request)
{
    $company_total = '';
    $overAllTotal = '';
    $sale_year = $request->sale_year;
    $customers = Customer::where('o.primary_status',3)->where('customers.status',1)
    ->select(DB::raw('sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_amount
    END) AS jan_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_paid
    END) AS jan_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_amount
    END) AS feb_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_paid
    END) AS feb_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_amount
    END) AS mar_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_paid
    END) AS mar_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_amount
    END) AS apr_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_paid
    END) AS apr_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_amount
    END) AS may_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_paid
    END) AS may_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_amount
    END) AS jun_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_paid
    END) AS jun_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_amount
    END) AS jul_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_paid
    END) AS jul_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_amount
    END) AS aug_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_paid
    END) AS aug_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_amount
    END) AS sep_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_paid
    END) AS sep_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_amount
    END) AS oct_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_paid
    END) AS oct_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_amount
    END) AS nov_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_paid
    END) AS nov_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_amount
    END) AS dec_totalAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_paid
    END) AS dec_paidAmount,
    sum(CASE
    WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" THEN o.total_amount
    END) AS customer_orders_total'),
    'customers.reference_name',
    'customers.id',
    'customers.primary_sale_id',
    'customers.credit_term','o.dont_show','o.user_id')->whereYear('o.converted_to_invoice_on',$sale_year)->groupBy('o.customer_id');
    $customers->join('orders AS o','customers.id','=','o.customer_id');
    // $customers->get();

    if($request->customer_categories != null)
    {
      $customers = $customers->where('customers.category_id',$request->customer_categories);
    }

    $company_total = $customers;
    $get_overall_total  = (clone $company_total)->get();
    $overAllTotal = $get_overall_total->sum('customer_orders_total');

    if($request->sale_person != null)
    {
      $customers->where('o.user_id',$request->sale_person);
    }
    else
    {
      $customers->where('o.dont_show',0);
    }

    if($request->sale_person_filter != null )
    {
      if(Auth::user()->id != $request->sale_person_filter)
      {
        $customers = $customers->where('o.user_id',$request->sale_person_filter);
      }
      else
      {
        $customers = $customers->where(function($q){
          $q->where('o.user_id',Auth::user()->id);
        });
      }
    }
    elseif(Auth::user()->role_id == 3)
    {
      $customers = $customers->where(function($q){
        $q->where('o.user_id',Auth::user()->id);
      });
    }
    $customers->with('primary_sale_person','getpayment_term','primary_sale_person.get_warehouse');
    $get_total_customers = $customers->get();
    // $dt->with([
        //     'janTotal'=>$get_total_customers->sum('jan_totalAmount'),
        //     'febTotal'=>$get_total_customers->sum('feb_totalAmount'),
        //     'marTotal'=>$get_total_customers->sum('mar_totalAmount'),
        //     'aprTotal'=>$get_total_customers->sum('apr_totalAmount'),
        //     'mayTotal'=>$get_total_customers->sum('may_totalAmount'),
        //     'junTotal'=>$get_total_customers->sum('jun_totalAmount'),
        //     'julTotal'=>$get_total_customers->sum('jul_totalAmount'),
        //     'augTotal'=>$get_total_customers->sum('aug_totalAmount'),
        //     'sepTotal'=>$get_total_customers->sum('sep_totalAmount'),
        //     'octTotal'=>$get_total_customers->sum('oct_totalAmount'),
        //     'novTotal'=>$get_total_customers->sum('nov_totalAmount'),
        //     'decTotal'=>$get_total_customers->sum('dec_totalAmount'),
        //     'yearTotal'=>$get_total_customers->sum('customer_orders_total'),
        //     'overAllTotal'=>$overAllTotal
        //   ]);
    return response()->json([
             'janTotal'=>$get_total_customers->sum('jan_totalAmount'),
            'febTotal'=>$get_total_customers->sum('feb_totalAmount'),
            'marTotal'=>$get_total_customers->sum('mar_totalAmount'),
            'aprTotal'=>$get_total_customers->sum('apr_totalAmount'),
            'mayTotal'=>$get_total_customers->sum('may_totalAmount'),
            'junTotal'=>$get_total_customers->sum('jun_totalAmount'),
            'julTotal'=>$get_total_customers->sum('jul_totalAmount'),
            'augTotal'=>$get_total_customers->sum('aug_totalAmount'),
            'sepTotal'=>$get_total_customers->sum('sep_totalAmount'),
            'octTotal'=>$get_total_customers->sum('oct_totalAmount'),
            'novTotal'=>$get_total_customers->sum('nov_totalAmount'),
            'decTotal'=>$get_total_customers->sum('dec_totalAmount'),
            'yearTotal'=>$get_total_customers->sum('customer_orders_total'),
            'overAllTotal'=>$overAllTotal
        ]);
}
  public function superAdminAsUser(Request $request)
  {
    $user = Auth::user();
    if($request->id)
    {
      $user->role_id = $request->id;
      $user->save();
      return response()->json(['success' => true]);
    }
      return response()->json(['success' => false]);
  }

  public function accountingConfig()
  {
    $auto_run_payment_ref_no = QuotationConfig::where('section','account_receiveable_auto_run_payment_ref_no')->first();
    return view('backend.accounting-config.index',compact('auto_run_payment_ref_no'));
  }

  public function saveAutoRunPaymentRefNo(Request $request)
  {
    if($request->auto_run_payment_ref_no == 1)
    {
        $value = 1;
    }
    else
    {
        $value = 0;
    }
    $auto_run_ref_no = QuotationConfig::where('section','account_receiveable_auto_run_payment_ref_no')->first();
    if($auto_run_ref_no)
    {
        $auto_run_ref_no->display_prefrences = $value;
        $auto_run_ref_no->save();
    }
    else
    {
        $auto_run_ref_no                     = new QuotationConfig;
        $auto_run_ref_no->section            = 'account_receiveable_auto_run_payment_ref_no';
        $auto_run_ref_no->display_prefrences = $value;
        $auto_run_ref_no->save();
    }

    return response()->json(['success' => true]);
}

}
