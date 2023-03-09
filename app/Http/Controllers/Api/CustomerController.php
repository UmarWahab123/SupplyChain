<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\Order\CustomerBillingDetail;
use App\QuotationConfig;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::select('id','reference_number','company','first_name','last_name','email','phone','address_line_1','address_line_2','country','state','city','postalcode')->with('getcountry:id,name,thai_name','getstate:id,name,thai_name','getshipping','getbilling:customer_id,title,billing_contact_name,billing_email,company_name,billing_phone,billing_address,billing_country,billing_state,billing_city','getbilling.getcountry:id,name,thai_name','getbilling.getstate:id,name,thai_name')->where('ecommerce_customer',1)->paginate(50);

        if($customers)
        return response()->json(['success' => true, 'customers' => $customers]);

        return response()->json(['success' => false, 'message' => 'No Customer Found .']);
    }
    public function show($id)
    {
        try
        {
            $customer = Customer::select('id','reference_number','company','first_name','last_name','email','phone','address_line_1','address_line_2','country','state','city','postalcode')->with('getcountry:id,name,thai_name','getstate:id,name,thai_name','getshipping','getbilling:customer_id,title,billing_contact_name,billing_email,company_name,billing_phone,billing_address,billing_country,billing_state,billing_city','getbilling.getcountry:id,name,thai_name','getbilling.getstate:id,name,thai_name')->where('ecommerce_customer',1)->find($id);

            if($customer)
            return response()->json(['success' => true, 'customer' => $customer]);

            return response()->json(['success' => false, 'message' => 'Customer '.$id.' not found .']);
        }
        catch(\Excepion $e)
        {
            return response()->json(['success' => false]);
        }
    }

    public function store(Request $request)
    {
        // dd($request->all());
        //check if customer exists
        $customer = Customer::where('phone',$request->phone)->first();
      if($customer)
      {
        $finded_customer_id = $customer->id;
      }
      else
      {
         $customer = new Customer;
         $prefix = 'EC';
         $c_p_ref = Customer::where('category_id',6)->orderby('reference_no','DESC')->first();
         $str = @$c_p_ref->reference_no;
         if($str  == NULL){
            $str = "0";
         }
         $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
         $customer->reference_number      = $prefix.$system_gen_no;
         $customer->reference_no          = $system_gen_no;
         $customer->category_id           = 6;
         $customer->status = 1;
         $customer->ecommerce_customer = 1;
         $customer->language = 'en';
         $customer->user_id = 1;
         $customer->primary_sale_id = 1;
         $customer->first_name = $request->first_name;
         $customer->last_name = $request->last_name;
         $customer->reference_name = $request->first_name.' '.$request->last_name;
         $customer->company = $request->first_name.' '.$request->last_name;
         $customer->email = $request->email_address;
         $customer->phone = $request->phone;
         $customer->country = 217;
         $customer->address_line_1 = $request->street_address;
         $customer->city = $request->city;
         $customer->postalcode = $request->post_code;
         $customer->save();

         $finded_customer_id = $customer->id;
      }
      if($request->has('state'))
        {
            $state = State::where('name',$request->state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }

      //To find customer shipping address
      $customer_name = $request->first_name.' '.$request->last_name;
      $shipping_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('company_name',$customer_name)->where('billing_address',$request->street_address)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',$request->city)->where('billing_email',$request->email_address)->where('billing_zip',$request->post_code)->first();
      $ecom_order_shipping_address = $shipping_address;
      if($shipping_address != null)
      {
         $order_shipping_address_id = $shipping_address->id;
      }
      else
      {
         $ecom_order_shipping_address = new CustomerBillingDetail;
         $ecom_order_shipping_address->title = 'Ecom Shipping Address';
         $ecom_order_shipping_address->customer_id = $finded_customer_id;
         $ecom_order_shipping_address->billing_contact_name = $customer_name;
         $ecom_order_shipping_address->company_name = $customer_name;
         $ecom_order_shipping_address->show_title = 1;
         $ecom_order_shipping_address->tax_id = '--';
         $ecom_order_shipping_address->billing_phone = $request->phone;
         $ecom_order_shipping_address->billing_email = $request->email_address;
         $ecom_order_shipping_address->billing_address = $request->street_address;
         $ecom_order_shipping_address->billing_country = 217;
         $ecom_order_shipping_address->billing_city =  $request->city;
         $ecom_order_shipping_address->billing_zip =  $request->post_code;
         $ecom_order_shipping_address->billing_state =  $state_id;
         $ecom_order_shipping_address->status = 1;
         $ecom_order_shipping_address->save();
         $order_shipping_address_id = $ecom_order_shipping_address->id;
      }
      $ecom_order_billing_address = null;
      if($request->billing_different_than_shipping == true)
      {
        if($request->has('billing_state'))
        {
            $state = State::where('name',$request->billing_state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }
       //To find customer billing address
      $customer_name = $request->first_name.' '.$request->last_name;
      $billing_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('tax_id',$request->tax_id)->where('company_name',$customer_name)->where('billing_address',$request->billing_street_address)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',$request->billing_city)->where('billing_email',$request->email_address)->where('billing_zip',$request->billing_post_code)->where('billing_phone',$request->phone)->first();
      $ecom_order_billing_address = $billing_address;

      if($billing_address != null)
      {
         $order_billing_address_id = $billing_address->id;
      }
      else
      {
         $ecom_order_billing_address = new CustomerBillingDetail;
         $ecom_order_billing_address->title = 'Ecom Billing Address';
         $ecom_order_billing_address->customer_id = $finded_customer_id;
         $ecom_order_billing_address->billing_contact_name = $customer_name;
         $ecom_order_billing_address->company_name = $customer_name;
         $ecom_order_billing_address->show_title = 1;
         $ecom_order_billing_address->billing_phone = $request->phone;
         $ecom_order_billing_address->billing_email = $request->email_address;
         $ecom_order_billing_address->billing_address = $request->billing_street_address;
         $ecom_order_billing_address->billing_country = 217;
         $ecom_order_billing_address->billing_city =  $request->billing_city;
         $ecom_order_billing_address->billing_zip =  $request->billing_post_code;
         $ecom_order_billing_address->billing_state =  $state_id;
         $ecom_order_billing_address->status = 1;
         $ecom_order_billing_address->tax_id = $request->tax_id;
         // $ecom_order_billing_address->company_name = $customer_array['company_name'];
         $ecom_order_billing_address->save();
         $order_billing_address_id = $ecom_order_billing_address->id;
      }
    }
    else
    {
        $order_billing_address_id = $order_shipping_address_id;
    }

      return response()->json(['customer' => $customer, 'shipping_address' => $ecom_order_shipping_address, 'billing_address' => $ecom_order_billing_address]);
    }

    public function addCustomerDuringCheckout(Request $request){
      // dd($request['request']);
      // dd($request['request']);
      // return response()->json(['customer' => $request->all()]);
      // $data = $request->all();
      // dd($request->phone);
      $customer = Customer::where('phone',$request->phone)->first();
      if($customer)
      {
        $finded_customer_id = $customer->id;
      }
      else
      {
         $customer = new Customer;
         $prefix = 'EC';
         $c_p_ref = Customer::where('category_id',6)->orderby('reference_no','DESC')->first();
         $str = @$c_p_ref->reference_no;
         if($str  == NULL){
            $str = "0";
         }
         $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
         $customer->reference_number      = $prefix.$system_gen_no;
         $customer->reference_no          = $system_gen_no;
         $customer->category_id           = 6;
         $customer->status = 1;
         $customer->ecommerce_customer = 1;
         $customer->language = 'en';
         $customer->user_id = 1;
         $customer->first_name = $request->first_name;
         $customer->last_name = $request->last_name;
         $customer->reference_name = $request->first_name.' '.$request->last_name;
         $customer->company = $request->first_name.' '.$request->last_name;
         $customer->email = $request->email_address;
         $customer->phone = $request->phone;
         $customer->country = 217;
         $customer->address_line_1 = $request->street_address;
         $customer->city = $request->city;
         $customer->postalcode = $request->post_code;
         $customer->save();

         $finded_customer_id = $customer->id;
      }
      if($request->has('state'))
        {
            $state = State::where('name',$request->state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }

      //To find customer shipping address
      $customer_name = $request->first_name.' '.$request->last_name;
      $shipping_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('company_name',$customer_name)->where('billing_address',$request->street_address)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',$request->city)->where('billing_email',$request->email_address)->where('billing_zip',$request->post_code)->first();

      if($shipping_address != null)
      {
         $order_shipping_address_id = $shipping_address->id;
      }
      else
      {
         $ecom_order_shipping_address = new CustomerBillingDetail;
         $ecom_order_shipping_address->title = 'Ecom Shipping Address';
         $ecom_order_shipping_address->customer_id = $finded_customer_id;
         $ecom_order_shipping_address->billing_contact_name = $customer_name;
         $ecom_order_shipping_address->company_name = $customer_name;
         $ecom_order_shipping_address->show_title = 1;
         $ecom_order_shipping_address->tax_id = '--';
         $ecom_order_shipping_address->billing_phone = $request->phone;
         $ecom_order_shipping_address->billing_email = $request->email_address;
         $ecom_order_shipping_address->billing_address = $request->street_address;
         $ecom_order_shipping_address->billing_country = 217;
         $ecom_order_shipping_address->billing_city =  $request->city;
         $ecom_order_shipping_address->billing_zip =  $request->post_code;
         $ecom_order_shipping_address->billing_state =  $state_id;
         $ecom_order_shipping_address->status = 1;
         $ecom_order_shipping_address->save();
         $order_shipping_address_id = $ecom_order_shipping_address->id;
      }
      if($request->billing_different_than_shipping == true)
      {
        if($request->has('billing_state'))
        {
            $state = State::where('name',$request->billing_state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }
       //To find customer billing address
      $customer_name = $request->first_name.' '.$request->last_name;
      $billing_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('tax_id',$request->tax_id)->where('company_name',$customer_name)->where('billing_address',$request->billing_street_address)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',$request->billing_city)->where('billing_email',$request->email_address)->where('billing_zip',$request->billing_post_code)->where('billing_phone',$request->phone)->first();

      if($billing_address != null)
      {
         $order_billing_address_id = $billing_address->id;
      }
      else
      {
         $ecom_order_billing_address = new CustomerBillingDetail;
         $ecom_order_billing_address->title = 'Ecom Billing Address';
         $ecom_order_billing_address->customer_id = $finded_customer_id;
         $ecom_order_billing_address->billing_contact_name = $customer_name;
         $ecom_order_billing_address->company_name = $customer_name;
         $ecom_order_billing_address->show_title = 1;
         $ecom_order_billing_address->billing_phone = $request->phone;
         $ecom_order_billing_address->billing_email = $request->email_address;
         $ecom_order_billing_address->billing_address = $request->billing_street_address;
         $ecom_order_billing_address->billing_country = 217;
         $ecom_order_billing_address->billing_city =  $request->billing_city;
         $ecom_order_billing_address->billing_zip =  $request->billing_post_code;
         $ecom_order_billing_address->billing_state =  $state_id;
         $ecom_order_billing_address->status = 1;
         $ecom_order_billing_address->tax_id = $request->tax_id;
         // $ecom_order_billing_address->company_name = $customer_array['company_name'];
         $ecom_order_billing_address->save();
         $order_billing_address_id = $ecom_order_billing_address->id;
      }
    }
    else
    {
        $order_billing_address_id = $order_shipping_address_id;
    }

      return response()->json(['customer' => $customer, 'order_shipping_address_id' => $order_shipping_address_id, 'order_billing_address_id' => $order_billing_address_id]);

    }

    public function addCustomerBillingAddress(Request $request) {

      //To find customer billing address
      $customer = Customer::find($request->customer_id);

      if($customer) {

        if($request->has('billing_state'))
        {
            $state = State::where('name',$request->billing_state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }

        $customer_name = $request->first_name.' '.$request->last_name;
        $billing_address = CustomerBillingDetail::where('customer_id',$request->customer_id)->where('tax_id',$request->tax_id)->where('company_name',$customer_name)->where('billing_address',$request->billing_street_address)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',$request->billing_city)->where('billing_email',$request->email_address)->where('billing_zip',$request->billing_post_code)->where('billing_phone',$request->phone)->first();
        $ecom_order_billing_address = $billing_address;

        if($billing_address != null)
        {
            $order_billing_address_id = $billing_address->id;
        }
        else
        {
            $ecom_order_billing_address = new CustomerBillingDetail;
            $ecom_order_billing_address->title = 'Ecom Billing Address';
            $ecom_order_billing_address->customer_id = $request->customer_id;
            $ecom_order_billing_address->billing_contact_name = $customer_name;
            $ecom_order_billing_address->company_name = $customer_name;
            $ecom_order_billing_address->show_title = 1;
            $ecom_order_billing_address->billing_phone = $request->phone;
            $ecom_order_billing_address->billing_email = $request->email_address;
            $ecom_order_billing_address->billing_address = $request->billing_street_address;
            $ecom_order_billing_address->billing_country = 217;
            $ecom_order_billing_address->billing_city =  $request->billing_city;
            $ecom_order_billing_address->billing_zip =  $request->billing_post_code;
            $ecom_order_billing_address->billing_state =  $state_id;
            $ecom_order_billing_address->status = 1;
            $ecom_order_billing_address->tax_id = $request->tax_id;
            $ecom_order_billing_address->save();
            $order_billing_address_id = $ecom_order_billing_address->id;
        }

      return response()->json(['success' => true, 'message' => 'Customer Billing Address Created Successfully!!', 'order_billing_address_id' => $order_billing_address_id]);

      } else {
        return response()->json(['error' => 'Customer does not exist']);
      }

    }

    public function addCustomerShippingAddress(Request $request) {

      //To find customer shipping address
      $customer = Customer::find($request->customer_id);

      if($customer) {

        if($request->has('shipping_state'))
        {
            $state = State::where('name',$request->shipping_state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }
        $customer_name = $request->first_name.' '.$request->last_name;
        $shipping_address = CustomerBillingDetail::where('customer_id',$request->customer_id)->where('company_name',$customer_name)->where('billing_address',$request->shipping_street_address)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',$request->shipping_city)->where('billing_email',$request->email_address)->where('billing_zip',$request->shipping_post_code)->first();

        if($shipping_address != null)
        {
            $order_shipping_address_id = $shipping_address->id;
        }
        else
        {
            $ecom_order_shipping_address = new CustomerBillingDetail;
            $ecom_order_shipping_address->title = 'Ecom Shipping Address';
            $ecom_order_shipping_address->customer_id = $request->customer_id;
            $ecom_order_shipping_address->billing_contact_name = $customer_name;
            $ecom_order_shipping_address->company_name = $customer_name;
            $ecom_order_shipping_address->show_title = 1;
            $ecom_order_shipping_address->tax_id = '--';
            $ecom_order_shipping_address->billing_phone = $request->phone;
            $ecom_order_shipping_address->billing_email = $request->email_address;
            $ecom_order_shipping_address->billing_address = $request->shipping_street_address;
            $ecom_order_shipping_address->billing_country = 217;
            $ecom_order_shipping_address->billing_city =  $request->shipping_city;
            $ecom_order_shipping_address->billing_zip =  $request->shipping_post_code;
            $ecom_order_shipping_address->billing_state =  $state_id;
            $ecom_order_shipping_address->status = 1;
            $ecom_order_shipping_address->save();
            $order_shipping_address_id = $ecom_order_shipping_address->id;
        }

        return response()->json(['success' => true, 'message' => 'Customer Shipping Address Created Successfully!!', 'order_shipping_address_id' => $order_shipping_address_id]);

        } else {
            return response()->json(['error' => 'Customer does not exist']);
        }

    }
}
