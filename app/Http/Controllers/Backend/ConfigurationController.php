<?php

namespace App\Http\Controllers\Backend;

use File;
use Validator;
use Milon\Barcode\DNS1D;
use App\Helpers\MyHelper;
use App\Helpers\GuzzuleRequestHelper;
use App\Models\Common\Role;
use Illuminate\Http\Request;
use App\Models\Common\Currency;
use App\Services\BarcodeService;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use App\Models\Common\Warehouse;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Deployment;
use App\General;
use App\Variable;
use Illuminate\Support\Facades\View;
use Auth;

class ConfigurationController extends Controller
{
    protected $user;
    protected $barcode_serv;
    public function __construct(BarcodeService $barcodeService)
    {
      $this->barcode_serv = $barcodeService;
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

        $extra_space_for_select2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data,'extra_space' => $extra_space_for_select2,'server' => @$config->server, 'config' => $config]);
    }

    public function index()
    {
      $query =  Configuration::all();
      $configuration = $query[0];
      $roles = Role::where('id','!=',8)->get();
      $customer_cats = CustomerCategory::select('id', 'title')->get();
      $warehouses = Warehouse::select('id', 'warehouse_title')->get();
    	return $this->render('backend.configuration.index',compact('configuration','roles', 'customer_cats', 'warehouses'));
    }

    public function getData()
    {
        $query =	Configuration::all();

        return Datatables::of($query)

            ->addColumn('action', function ($item) {
                $html_string = '<div class="icons">'.'
                              <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
                          </div>';
                return $html_string;
                })

            ->addColumn('image', function ($item) {
            $url= asset('public/uploads/logo/'.$item->logo);
            return '<img src="'.$url.'" border="0" width="80" class="img-rounded" align="center" />';
          })
          ->addColumn('currency_code', function ($item) {

            return $item->Currency->currency_code;
            })
            ->addColumn('currency_symbol', function ($item) {

              return $item->Currency->currency_symbol;
              })
            ->addColumn('email_notification', function ($item) {
              if($item->email_notification == 1)
              {
                $e_status = 'Enabled';
              }elseif ($item->email_notification == 0) {
                $e_status = 'Disabled';
              }

              return $e_status;
              })

            ->rawColumns(['action','image','currency_code','currency_symbol','email_notification'])
                    ->make(true);
    }

    public function add()
    {
    	dd("hello");
    }

    public function edit(Request $request)
    {
        $configuration = Configuration::find($request->id);
        $currency = Currency::all();
        if(@$configuration->email_notification == 1 || @$configuration->woo_commerce == 1)
        {
          $checked = 'checked';
        }
        elseif(@$configuration->email_notification == 0 || @$configuration->woo_commerce == 0)
        {
          $checked = '';
        }


        $html = '';
        $html = $html . '
        <h3 class="text-capitalize fontmed">Edit Configuration</h3>
        <form method="post" action="" id="edit-con" class="edit_con_form" enctype="multipart/form-data">
            ' . csrf_field() . '
            <input type="hidden" name="configuration_id" value="'.$configuration->id.'">


          <div class="form-row">
            <div class="form-group col-12">
            <label style="float:left">System Name</label>
	          <input type="text" name="company_name" placeholder="System (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $configuration->company_name . '">
            </div>


            <div class="form-group col-12">
            <label style="float:left">System Big Logo <span class="text-danger">(Image must be 189x40)</span></label>';
            if($configuration->logo != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->logo);
              if(File::exists($image_path))
              {
                $html = $html.'<input type="file" name="big_logo" accept="image/*" id="blogo"  class="form-control-lg form-control" onchange="return loadBigImg(event)">';
              }
              else
              {
                $html = $html.'<input type="file" name="big_logo" accept="image/*" id="blogo"  required="" class="form-control-lg form-control" onchange="return loadBigImg(event)">';
              }
            }
            else
            {
              $html = $html.'<input type="file" name="big_logo" accept="image/*" id="blogo" required=""  class="form-control-lg form-control" onchange="return loadBigImg(event)">';
            }

             $html = $html.'<div style="margin-bottom: 25px;"><span style="float:left;color:red;"><strong style="float:left;color:red;" id="big_logo-error"></strong></span></div>';
            if($configuration->logo != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->logo);
              if(File::exists($image_path))
              {
                $html = $html.'<img id="big_img" style="margin-top: 20px; margin-bottom: 10px;" width="" height="70px" src="'.asset('public/uploads/logo/'.$configuration->logo).'" alt="System Logo">';
              }
              else
              {
                $html = $html.'<img id="big_img" style="margin-top: 20px; margin-bottom: 10px;" width="" height="70px" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
              }
            }
            else
            {
              $html = $html.'<img id="big_img" style="margin-top: 20px; margin-bottom: 10px;" width="" height="70px" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
            }
            $html = $html.'</div>
          </div>




          <div class="form-row">
            <div class="form-group col-6">
            <label style="float:left">System Small Logo <span class="text-danger">(Image must be 40x40)</span></label>';
            if($configuration->small_logo != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->small_logo);
              if(File::exists($image_path))
              {
                $html = $html.'<input type="file" id="slogo" name="small_logo" accept="image/*"  class="form-control-lg form-control" onchange="loadSmallImg(event)">';
              }
              else
              {
                $html = $html.'<input type="file" id="slogo" name="small_logo" accept="image/*" required="" class="form-control-lg form-control" onchange="loadSmallImg(event)">';
              }
            }
            else
            {
              $html = $html.'<input type="file" id="slogo" name="small_logo" accept="image/*" required="" class="form-control-lg form-control" onchange="loadSmallImg(event)">';
            }

            $html = $html.'<div style="margin-bottom: 25px;"><span style="float:left;color:red;"><strong id="small_logo-error"></strong></span></div>';
            if($configuration->small_logo != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->small_logo);
              if(File::exists($image_path))
              {
                $html = $html.'<img id="small_img"  width="" height="50px" style="margin-top: 20px; margin-bottom: 10px;" src="'.asset('public/uploads/logo/'.$configuration->small_logo).'" alt="System Logo">';
              }
              else
              {
                $html = $html.'<img id="small_img"  width="" height="50px" style="margin-top: 20px; margin-bottom: 10px;" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
              }
            }
            else
            {
              $html = $html.'<img id="small_img"  width="" height="50px" style="margin-top: 20px; margin-bottom: 10px;" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
            }
            $html = $html.'</div>

            <div class="form-group col-6">
            <label style="float:left">System Favicon <span class="text-danger">(Image must be 40x40)</span></label>';
            if($configuration->favicon != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->favicon);
              if(File::exists($image_path))
              {
                $html = $html.'<input type="file" id="flogo" name="favicon" accept="image/*"  class="form-control-lg form-control" onchange="loadFaviconImg(event)">';
              }
              else
              {
                $html = $html.'<input type="file" id="flogo" name="favicon" accept="image/*" required="" class="form-control-lg form-control" onchange="loadFaviconImg(event)">';
              }
            }
            else
            {
              $html = $html.'<input type="file" id="flogo" name="favicon" accept="image/*" required="" class="form-control-lg form-control" onchange="loadFaviconImg(event)">';
            }
            $html = $html.'<div style="margin-bottom: 25px;"><span style="float:left;color:red;"><strong id="favicon-error"></strong></span></div>';

            if($configuration->favicon != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->favicon);
              if(File::exists($image_path))
              {
                $html = $html.'<img id="favicon_img"  width="" height="50px" style="margin-top: 20px; margin-bottom: 10px;" src="'.asset('public/uploads/logo/'.$configuration->favicon).'" alt="System Logo">';
              }
              else
              {
                $html = $html.'<img id="favicon_img"  width="" height="50px" style="margin-top: 20px; margin-bottom: 10px;" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
              }
            }
            else
            {
              $html = $html.'<img id="favicon_img"  width="" height="50px" style="margin-top: 20px; margin-bottom: 10px;" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
            }
	          $html = $html.'</div>
          </div>
          <div class="form-group col-12">
            <label style="float:left">Login Page Background</label>';
            if($configuration->login_background != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->login_background);
              if(File::exists($image_path))
              {
                $html = $html.'<input type="file" name="login_background" accept="image/*" id="blogin"  class="form-control-lg form-control" onchange="return loadLoginImg(event)">';
              }
              else
              {
                $html = $html.'<input type="file" name="login_background" accept="image/*" id="blogin"  required="" class="form-control-lg form-control" onchange="return loadLoginImg(event)">';
              }
            }
            else
            {
              $html = $html.'<input type="file" name="login_background" accept="image/*" id="blogin" required=""  class="form-control-lg form-control" onchange="return loadLoginImg(event)">';
            }

             $html = $html.'<div style="margin-bottom: 25px;"><span style="float:left;color:red;"><strong style="float:left;color:red;" id="login_bg_img-error"></strong></span></div>';
            if($configuration->login_background != Null)
            {
              $image_path = public_path('uploads/logo/'.$configuration->login_background);
              if(File::exists($image_path))
              {
                $html = $html.'<img id="login_bg_img" style="margin-top: 20px; margin-bottom: 10px;" width="" height="70px" src="'.asset('public/uploads/logo/'.$configuration->login_background).'" alt="System Logo">';
              }
              else
              {
                $html = $html.'<img id="login_bg_img" style="margin-top: 20px; margin-bottom: 10px;" width="" height="70px" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
              }
            }
            else
            {
              $html = $html.'<img id="login_bg_img" style="margin-top: 20px; margin-bottom: 10px;" width="" height="70px" src="'.asset('public/site/assets/backend/img/upload.jpg').'" alt="Image Not Found">';
            }
            $html = $html.'</div>
          </div>

          <div class="form-row">
	          <div class="form-group col-6">
              <label style="float:left">Currency Code</label>
              <select class="form-control-lg form-control font-weight-bold" id="currency_code" name="currency_code">';

              foreach($currency as $curr){

                if($configuration->currency_id == $curr->id)
                {
                 $html = $html.'<option selected value="'.$curr->id.'">'.$curr->currency_code.'</option>';
                }
                else
                {
                 $html = $html.'<option value="'.$curr->id.'">'.$curr->currency_code.'</option>';
                }
              }
                $html = $html . ' </select>
            </div>

            <div class="form-group col-6">
              <label style="float:left">System Email</label>
              <input type="text" name="system_email" placeholder="System Email" class="font-weight-bold form-control-lg form-control" value="' . $configuration->system_email. '">
            </div>
            <div class="form-group col-6">
              <label style="float:left">Purchasing Email</label>
              <input type="text" name="purchasing_email" placeholder="Purchasing Email" class="font-weight-bold form-control-lg form-control" value="' . $configuration->purchasing_email. '">
            </div>
            <div class="form-group col-6">
              <label style="float:left">Billing Email</label>
              <input type="text" name="billing_email" placeholder="Billing Email" class="font-weight-bold form-control-lg form-control" value="' . $configuration->billing_email. '">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              <label style="float:left">System Color</label>
              <input type="color" id="system_bg_color" name="system_bg_color" onchange="checkSysColor()" class="font-weight-bold form-control-lg form-control" value="'.$configuration->system_color.'">
            </div>

            <div class="form-group col-6">
              <label style="float:left">System Button Text Color</label>
              <input type="color" id="system_bg_txt_color" name="system_bg_txt_color" onchange="checkSysColor()" class="font-weight-bold form-control-lg form-control" value="'.$configuration->bg_txt_color.'">
            </div>

            <div class="form-group col-12" id="sys_color_div" style="display:none;">
              <p style="color:red;">"System Color" and "System Text Color" must be different</p>
            </div>

            <div class="form-group col-6">
              <label style="float:left">Button Hover Color</label>
              <input type="color" id="btn_hover_color" name="btn_hover_color" onchange="checkHoverColor()" class="font-weight-bold form-control-lg form-control" value="'.$configuration->btn_hover_color.'">
            </div>

            <div class="form-group col-6">
              <label style="float:left">Button Hover Text Color</label>
              <input type="color" id="btn_hover_txt_color" name="btn_hover_txt_color" onchange="checkHoverColor()" class="font-weight-bold form-control-lg form-control" value="'.$configuration->btn_hover_txt_color.'">
            </div>

            <div class="form-group col-6">
              <label style="float:left">Server</label>
              <input type="text" name="server_name" placeholder="Server" class="font-weight-bold form-control-lg form-control" value="' . $configuration->server. '">
            </div>

            <div class="form-group col-12" id="hover_color_div" style="display:none;">
              <p style="color:red;">"Button Hover Color" and "Button Hover Text Color" must be different</p>
            </div>


            <div class="form-group col-3" style="margin-top: 35px;">
              <input type="checkbox" name="email_notification" id="email_notification" '.@$checked.'>
              <label>Email Notification</label>
            </div>

          </div>

          <div class="form-row">

          </div>

          <div class="form-row d-none">

            <div class="form-group col-6">
              <input type="text" name="quotation_prefix" placeholder="Quotation Prefix" class="font-weight-bold form-control-lg form-control" value="' . $configuration->quotation_prefix. '">
            </div>
            <div class="form-group col-6">
              <input type="text" name="draft_invoice_prefix" placeholder="Draft Invoice Prefix" class="font-weight-bold form-control-lg form-control" value="' . $configuration->draft_invoice_prefix. '">
            </div>
          </div>

          <div class="form-row d-none">

            <div class="form-group col-6">
              <input type="text" name="invoice_prefix" placeholder="Invoice Prefix" class="font-weight-bold form-control-lg form-control" value="' . $configuration->invoice_prefix. '">
            </div>

          </div>



          <div class="form-submit">
            <input type="submit" value="update" class="btn btn-bg save-btn">
            <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
          </div>
        </form>
        ';

        return $html;
    }

    public function update(Request $request)
    {
      // dd($request->all());
      $configuration_id = $request->configuration_id;
      $validator = $request->validate([
        'company_name'         => 'required',
        'currency_code'        => 'required',
        // 'quotation_prefix'     => 'required',
        // 'draft_invoice_prefix' => 'required',
        // 'invoice_prefix'       => 'required',
        'system_email'         => 'required',
        'purchasing_email'     => 'required',
        'billing_email'     => 'required',
        // 'big_logo'             => 'required|mimes:jpeg,png,jpg,svg',
        // 'small_logo'           => 'required|mimes:jpeg,png,jpg,svg',
        // 'favicon'              => 'required|mimes:jpeg,png,jpg,svg',
        // 'logo' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
      ]
      // [
      //   'big_logo.required'  => 'The big logo image is required.',
      //   'big_logo.mimes'     => 'Uploaded file image should be jpeg, jpg, png or svg.',
      //   'small_logo.required'=> 'The small logo image is required.',
      //   'small_logo.mimes'   => 'Uploaded file image should be jpeg, jpg, png or svg.',
      //   'favicon.required'   => 'Favicon image is required.',
      //   'favicon.mimes'      => 'Uploaded file image should be jpeg, jpg, png or svg.',

      // ]
      );

      $config = Configuration::find($configuration_id);
      if($config->login_background != null)
      {
        $img_path = public_path('uploads/logo/'.$config->login_background);
        if(File::exists($img_path)) {
            File::delete($img_path);
        }
      }

      // if ($validator->fails())
      // {
      //   return response()->json(['errors' => $validator->errors()]);
      // }

      if($request->hasfile('big_logo'))
	    {
        $fileNameWithExt = $request->file('big_logo')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('big_logo')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path            = $request->file('big_logo')->move('public/uploads/logo/',$fileNameToStore);
        $config->logo    = $fileNameToStore;
      }
      if($request->hasfile('login_background'))
      {
        $fileNameWithExt = $request->file('login_background')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('login_background')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path            = $request->file('login_background')->move('public/uploads/logo/',$fileNameToStore);
        $config->login_background    = $fileNameToStore;
      }

      if($request->hasfile('small_logo'))
	    {
        $fileNameWithExt = $request->file('small_logo')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('small_logo')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path            = $request->file('small_logo')->move('public/uploads/logo/',$fileNameToStore);
        $config->small_logo    = $fileNameToStore;
      }

      if($request->hasfile('favicon'))
	    {
        $fileNameWithExt = $request->file('favicon')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('favicon')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path            = $request->file('favicon')->move('public/uploads/logo/',$fileNameToStore);
        $config->favicon = $fileNameToStore;
	    }

      if($request->email_notification)
      {
        $e_notification = 1;
      }else{
        $e_notification = 0;

      }
      // dd($request->woocommerce);

      if($request->woocommerce == "on") {
        $woocommerce = 1;
      } else {
        $woocommerce = 0;
      }
      // dd($request->server);
      // $ser_n = 'server';
        $config->company_name         = $request->company_name;
        $config->email_notification   = $e_notification;
        $config->currency_id          = $request->currency_code;
        $config->quotation_prefix     = $request->quotation_prefix;
        $config->draft_invoice_prefix = $request->draft_invoice_prefix;
        $config->invoice_prefix       = $request->invoice_prefix;
        $config->system_email         = $request->system_email;
        $config->purchasing_email     = $request->purchasing_email;
        $config->billing_email     = $request->billing_email;
        $config->system_color         = $request->system_bg_color;
        $config->bg_txt_color         = $request->system_bg_txt_color;
        $config->btn_hover_color      = $request->btn_hover_color;
        $config->btn_hover_txt_color  = $request->btn_hover_txt_color;
        $config->woo_commerce          = $woocommerce;
        $config->woocom_warehouse_id = $request->woocom_warehouse_id;
        $config->server               = $request->server_name;
        $config->update();



      return response()->json(['success' => true]);

    }

    public function editRolesConfiguration(Request $request)
    {
      $configuration = Configuration::find($request->id);
      if($configuration)
      {
        if($request->name == 'maximum_admin_accounts')
        {
          $configuration->maximum_admin_accounts = $request->val;
        }
        else if($request->name == 'maximum_staff_accounts')
        {
          $configuration->maximum_staff_accounts = $request->val;
        }
        $configuration->save();

        return response()->json(['success' => true]);
      }
      else
      {
        return response()->json(['success' => false]);
      }
    }
    public function barcodeView()
    {
      return view('backend.configuration.barcode_configuration');
    }

    public function barcodeSave(Request $request)
    {
      try
      {
        $response = $this->barcode_serv->saveBarcodeConfiguration($request);
        if($response)
        {
          return response()->json(['status'=>true]);
        }
        return response()->json(['status'=>false]);
      }
      catch (\Exception $ex)
      {
        return "Internal Server Error".$ex;
      }
    }

    public function GetDeploymentsData(Request $request)
    {
      $query = Deployment::latest();
      return Datatables::of($query)
      ->addColumn('action', function ($item) {
        $html_string = '<div class="icons">'.'
                      <a href="javascript:void(0);" data-id="'.$item->id.'" data-toggle="modal" data-target="#deployment-Modal" class="actionicon tickIcon btn-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                      <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon btn-delete" title="Delete"><i class="fa fa-trash"></i></a>
                      <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon btn-connect " title="Check Connection"><i class="fa fa-globe"></i></a>
                      </div>';
        return $html_string;
      })
      ->addColumn('type', function ($item){
        return $item->type != null ? $item->type : '--';
      })
      ->addColumn('url', function ($item){
        return $item->url != null ? $item->url : '--';
      })
      ->addColumn('token', function ($item){
        return $item->token != null ? $item->token : '--';
      })
      ->addColumn('price', function ($item){
        $customer_cat = $item->customerCategory;
        return $customer_cat != null ? $customer_cat->title : '--';
      })
      ->addColumn('warehouse', function ($item){
        $warehouse = $item->warehouse;
        return $warehouse != null ? $warehouse->warehouse_title : '--';
      })
      ->addColumn('created_by', function ($item){
        $user = $item->user;
        return $user != null ? $user->name : '--';
      })
      ->addColumn('status', function ($item){
        $status = $item->status == 1 ? 'checked' : '';
        $html_string = '<div class="icons">'.'
                      <input type="checkbox" name="status" id="status" data-id="'.$item->id.'" '.$status.'>
                  </div>';
        return $html_string;
      })
      ->rawColumns(['action', 'status'])
      ->make(true);
    }

    public function SaveDeploymentsData(Request $request)
    {
      if ($request->deployment_id == null)
      {
        $deployment = new Deployment();
        $deployment->token = $this->generatenumber(16);
        $deployment->status = 1;
      }
      else
      {
        $deployment = Deployment::find($request->deployment_id);
      }
      $deployment->type = $request->type;
      $deployment->url = $request->url;
      $deployment->price = $request->price;
      $deployment->warehouse_id = $request->warehouse;
      $deployment->user_id = Auth::user()->id;
      $deployment->save();
      return response()->json(['success' => true]);
    }

    public function SaveDeploymentsStatus(Request $request)
    {
      $deployment = Deployment::find($request->id);
      $deployment->status = $request->status;
      $deployment->save();
      return response()->json(['success' => true]);
    }

    public function CheckConnection(Request $request)
    {
      $token = config('app.external_token');
      $deployment = Deployment::find($request->id);
      $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/check-connection';
      $method = 'POST';
      $response = GuzzuleRequestHelper::guzzuleRequest($token,$url,$method);
      return response()->json(['success'=>true]);
    }

    private function generatenumber($limit)
    {
      $code = '';
      for($i = 0; $i < $limit; $i++)
      {
        $code .= mt_rand(0, 9);
      }
      return $code;
    }

    public function DeleteDeploymentsData(Request $request)
    {
      $deployment = Deployment::find($request->id);
      if ($deployment != null)
      {
        $deployment->delete();
        return response()->json(['success' => true]);
      }
      return response()->json(['success' => false]);
    }
}
