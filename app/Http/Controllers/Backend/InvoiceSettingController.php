<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\InvoiceSetting;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Variable;
use Illuminate\Http\Request;

class InvoiceSettingController extends Controller
{
    public function index()
    {
    	$invoice_setting = InvoiceSetting::first();
    	return $this->render('backend.invoice_setting.index' ,compact('invoice_setting'));
    } 

    public function updateinvoiceSetting(Request $request)
    {
        // dd($request->all());
        $invoice_setting = InvoiceSetting::where('id' , $request->invoice_setting_id)->first();
        // dd($invoice_setting);
        if($request->company_name)
        {
            $invoice_setting->company_name = $request->company_name;
        }

        elseif($request->billing_email)
        {
            $invoice_setting->billing_email = $request->billing_email;
        }

        elseif($request->billing_phone)
        {
            $invoice_setting->billing_phone = $request->billing_phone;
        }

        elseif($request->billing_fax)
        {
            $invoice_setting->billing_fax = $request->billing_fax;
        }

        elseif($request->billing_address)
        {
            $invoice_setting->billing_address = $request->billing_address;
        }

        elseif($request->billing_zip)
        {
            $invoice_setting->billing_zip = $request->billing_zip;
        }

        elseif($request->billing_country)
        {
            $invoice_setting->billing_country = $request->billing_country;
        }

        elseif($request->billing_state)
        {
            $invoice_setting->billing_state = $request->billing_state;
        }

        elseif($request->billing_city)
        {
            $invoice_setting->billing_city = $request->billing_city;
        }

        elseif($request->created_by)
        {
            $invoice_setting->created_by = $request->created_by;
        }

        $invoice_setting->save();
        return response()->json(['success' => true]);
        
    }

    public function addNew(Request $request)
    {
      $countries = Country::all();
      $states = State::all();

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
      <h3 class="text-capitalize fontmed">ADD Invoice Setting</h3>
      <form method="post" id="add-invoice-settings" class="add-invoice-settings" enctype="multipart/form-data">
          ' . csrf_field() . '
        <div class="form-row">
            <label>'.$global_terminologies["company_name"].'</label>
            <div class="form-group col-12">
            <input type="text" name="company_name" placeholder="Company (Required)" class="font-weight-bold form-control-lg form-control" value="" required="">
            </div>
        </div>

        <div class="form-row">
            <label>Email</label>
            <div class="form-group col-12">
            <input type="email" name="billing_email" placeholder="Email" class="font-weight-bold form-control-lg form-control" value="">
            </div>
        </div>
        

        <div class="form-row">
          <div class="form-group col-6">
          <label style="float: left;">Phone</label>
            <input type="text" name="billing_phone" placeholder="Phone number (Required)" class="font-weight-bold form-control-lg form-control" value="" required="">
          </div>
          <div class="form-group col-6">
          <label style="float: left;">Fax</label>
            <input type="text" name="billing_fax" placeholder="Fax (Required)" class="font-weight-bold form-control-lg form-control" value="" required="">
          </div>
        </div>

        <div class="form-row">
          <label>Address</label>
          <div class="form-group col-12">
          <input type="text" name="billing_address" placeholder="Address (Required)" class="font-weight-bold form-control-lg form-control" value="" required="">
          </div>
        </div>

        <div class="form-row">
          <label>Zip</label>
          <div class="form-group col-12">
          <input type="number" name="billing_zip" placeholder="Zip (Required)" class="font-weight-bold form-control-lg form-control" value="" required="">
          </div>
        </div>


        <div class="form-row">
          <label>Country</label>
          <div class="form-group col-12">
            <select class="billing_country form-control-lg form-control font-weight-bold" id="billing_country" name="billing_country" required="">';
              $html = $html.'<option value="">Select Country</option>';
              foreach($countries as $country)
              {
                $html = $html.'<option value="'.$country->id.'">'.$country->name.'</option>';
              }
              $html = $html . ' </select>
          </div>
        </div>

        <div class="form-row">
          <label>State</label>
          <div class="form-group col-12">
            <span id="load_opt"></span>
            <select class="billing_state form-control-lg form-control font-weight-bold" id="billing_state" name="billing_state" required="">';
              $html = $html.'<option value="">Select State</option>';
              // foreach($states as $state)
              // {
              //   $html = $html.'<option value="'.$state->id.'">'.$state->name.'</option>';
              // }
              $html = $html . ' </select>
          </div>
        </div>

        <div class="form-row">
          <label>City</label>
          <div class="form-group col-12">
            <input type="text" name="billing_city" placeholder="City (Required)" class="font-weight-bold form-control-lg form-control" value="" required="">
          </div>
        </div>

        <div class="form-row">
          <label>Company Logo</label>
          <div class="form-group col-12">
            <input type="file" class="form-control" name="logo" value="" >
          </div>
        </div>
                               
        <div class="form-submit">
          <input type="submit" value="ADD" class="btn btn-bg add-inv-btn">
          <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
        </div>
      </form>
      ';

      return $html;
    }

    public function addNewInvoiceSetting(Request $request)
    {
      $validator = $request->validate([
         'company_name'    => 'required',
         'billing_email'   => 'required',
         'billing_phone'   => 'required',
         'billing_fax'     => 'required',
         'billing_address' => 'required',
         'billing_zip'     => 'required',
         'billing_country' => 'required',
         'billing_state'   => 'required',
         'billing_city'    => 'required',
      ]);

      $invoice_setting = new InvoiceSetting;

      if($request->hasfile('logo'))
      {
        $fileNameWithExt = $request->file('logo')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('logo')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path            = $request->file('logo')->move('public/uploads/logo/',$fileNameToStore);
        $invoice_setting->logo    = $fileNameToStore;
      }
        
      $invoice_setting->company_name    = $request->company_name;
      $invoice_setting->billing_email   = $request->billing_email;
      $invoice_setting->billing_phone   = $request->billing_phone;    
      $invoice_setting->billing_fax     = $request->billing_fax;
      $invoice_setting->billing_address = $request->billing_address;
      $invoice_setting->billing_zip     = $request->billing_zip;
      $invoice_setting->billing_country = $request->billing_country;
      $invoice_setting->billing_state   = $request->billing_state;
      $invoice_setting->billing_city    = $request->billing_city;
      $invoice_setting->save();
      return response()->json(['success' => true]);
    }

    public function edit(Request $request)
    {
      $invoice_setting = InvoiceSetting::find($request->id);
      $countries = Country::all();
      $states = State::where('country_id',$invoice_setting->billing_country)->get();

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
      <h3 class="text-capitalize fontmed">Edit Invoice Setting</h3>
      <form method="post"  id="edit-con" class="edit_con_form" enctype="multipart/form-data">
          ' . csrf_field() . '
          <input type="hidden" name="configuration_id" value="'.$invoice_setting->id.'">
        <div class="form-row">
            <label>'.$global_terminologies["company_name"].'</label>
            <div class="form-group col-12">
            <input type="text" name="company_name" placeholder="Company (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->company_name . '">
            </div>
        </div>

        <div class="form-row">
            <label>Email</label>
            <div class="form-group col-12">
            <input type="email" name="billing_email" placeholder="Company (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->billing_email . '">
            </div>
        </div>
        

        <div class="form-row">
         
          <div class="form-group col-6">
          <label style="float: left;">Phone</label>
            <input type="text" name="billing_phone" placeholder="Phone number" class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->billing_phone. '">
          </div>
          <div class="form-group col-6">
          <label style="float: left;">Fax</label>
            <input type="text" name="billing_fax" placeholder="Fax" class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->billing_fax. '">
          </div>
        </div>

        <div class="form-row">
            <label>Address</label>
            <div class="form-group col-12">
            <input type="text" name="billing_address" placeholder="Company (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->billing_address . '">
            </div>
        </div>

        <div class="form-row">
            <label>Zip</label>
            <div class="form-group col-12">
            <input type="text" name="billing_zip" placeholder="Company (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->billing_zip . '">
            </div>
        </div>


        <div class="form-row">
            <label>Country</label>
            <div class="form-group col-12">
            <select class="billing_country form-control-lg form-control font-weight-bold" id="billing_country" name="billing_country">';
              foreach($countries as $country)
              {
                if($invoice_setting->billing_country == $country->id)
                {
                  $html = $html.'<option selected value="'.$country->id.'">'.$country->name.'</option>';
                }
                else
                {
                  $html = $html.'<option value="'.$country->id.'">'.$country->name.'</option>';
                }
              }
              $html = $html . ' </select>
          </div>
        </div>

        <div class="form-row">
            <label>State</label>

            <div class="form-group col-12">
            <span id="load_opt"></span>
            <select class="billing_state form-control-lg form-control font-weight-bold" id="billing_state" name="billing_state">';
              foreach($states as $state)
              {
                if($invoice_setting->billing_state == $state->id)
                {
                 $html = $html.'<option selected value="'.$state->id.'">'.$state->name.'</option>';
                }
                else
                {
                 $html = $html.'<option value="'.$state->id.'">'.$state->name.'</option>';
                }
              }
              $html = $html . ' </select>
          </div>
        </div>

        <div class="form-row">
            <label>City</label>
            <div class="form-group col-12">
            <input type="text" name="billing_city" placeholder="Company (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $invoice_setting->billing_city . '">
            </div>
        </div>

        <div class="form-row">
            <label>Company Logo</label>
            <div class="form-group col-12">
            <input type="file" class="form-control" name="logo" value="" >
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
      $invoice_setting_id = $request->configuration_id;
      $validator = $request->validate([
         'company_name'         => 'required',
         'billing_email'        => 'required',
         'billing_phone'   => 'required',
         'billing_fax'     => 'required',
         'billing_address' => 'required',
         'billing_zip'       => 'required',
         'billing_country'         => 'required',
         'billing_state'         => 'required',
         'billing_city'         => 'required',
         // 'logo' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
      ]);

      $invoice_setting = InvoiceSetting::find($invoice_setting_id);

      if($request->hasfile('logo'))
      {
        $fileNameWithExt = $request->file('logo')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('logo')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path            = $request->file('logo')->move('public/uploads/logo/',$fileNameToStore);
        $invoice_setting->logo    = $fileNameToStore;
      }
        
      $invoice_setting->company_name    = $request->company_name;
      $invoice_setting->billing_email   = $request->billing_email;
      $invoice_setting->billing_phone   = $request->billing_phone;    
      $invoice_setting->billing_fax     = $request->billing_fax;
      $invoice_setting->billing_address = $request->billing_address;
      $invoice_setting->billing_zip     = $request->billing_zip;
      $invoice_setting->billing_country = $request->billing_country;
      $invoice_setting->billing_state   = $request->billing_state;
      $invoice_setting->billing_city    = $request->billing_city;
      $invoice_setting->update();
      return response()->json(['success' => true]);
    }

    function fetchStates(Request $request)
    {
        // dd($request->all());
        
        $states = State::where('country_id' , $request->country_id)->get();
        // dd($states);
        $output = '<option value="">Select District</option>';

        foreach($states as $state)
        {
            $output .= '<option value="'.$state->id.'">'.$state->name.'</option>';
        }
        
        echo $output;
    }
}
