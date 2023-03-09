<?php

namespace App\Http\Controllers\Observer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\QuotationConfig;
use App\Models\Common\Order\CustomerBillingDetail;

class CustomerObserverController extends Controller
{

    public function updatedata(Request $request){
    	// dd($request->address_line_1);
    	
    	$quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        $quotation_config =  unserialize($quotation_qry->print_prefrences);

    	// dd($request->all());
    	$customer = Customer::where('ecommerce_customer_id', $request->id)->first();
        if($customer == null){
            $customer = new Customer;
        }
    	$customer->ecommerce_customer_id = $request->id;
        // 4 id is for Private Customer Category and PC is prefix for Private Customer Category
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


    	// $customer->user_id = $quotation_config['status'][7];
    	$customer->primary_sale_id = $quotation_config['status'][7];
    	$customer->first_name = $request->first_name;
    	$customer->last_name = $request->last_name;
        $customer->reference_name = $request->first_name.' '.$request->last_name;
    	$customer->email = $request->email;
    	$customer->phone = $request->phone;
        $customer->country= 217;
    	$customer->address_line_1 = $request->address_line_1;
    	// $customer->state = '';
    	$customer->city = $request->city;
    	$customer->postalcode = $request->postalcode;
    	$customer->status = 0;
    	$customer->ecommerce_customer = 1;
    	$customer->language = 'en';
    	$customer->save();

        $customer_id = $customer->id;
        $customer_billing_address = new CustomerBillingDetail;
        $customer_billing_address->customer_id = $customer_id;
        $customer_billing_address->title = 'Default Address';
        $customer_billing_address->show_title = 1;
        $customer_billing_address->billing_email = $request->email;
        $customer_billing_address->billing_address = $request->address_line_1;
        $customer_billing_address->billing_country = 217;
        $customer_billing_address->billing_city =  $request->city;
        $customer_billing_address->is_default = 1;    		
        $customer_billing_address->status = 1;
        $customer_billing_address->tax_id = $request->tax_id;
        $customer_billing_address->company_name = $request->company_name;
        $customer_billing_address->billing_address  = $request->company_address;
        $customer_billing_address->save();
        // return 1;

    }
    
}
