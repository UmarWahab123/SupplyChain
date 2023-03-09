<?php

namespace App\Http\Controllers\Observer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use App\QuotationConfig;
use App\Models\Common\Status;
use App\Models\Common\Warehouse;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeProductMargin;
use Illuminate\Support\Carbon;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\WarehouseProduct;
use App\User;
use DB;
use App\Models\Common\Order\CustomerOrderAddressDetail;
use App\Models\Common\Order\OrderNote;
use App\Helpers\MyHelper;


class OrderObserverController extends Controller
{

   public function createecommerceorder(Request $request)
   {
      $order_detail = $request->orders;
      if($request->orders && $request->products)
      {
         $customer = Customer::where('ecommerce_customer_id', $order_detail['customer_id'])->first();
         $customer_billing_address = CustomerBillingDetail::where('customer_id',$customer->id)->where('title', 'Ecom Shipping Address')->orderBy('id','desc')->first();
         if($customer_billing_address == null)
         {
            $customer_billing_address = new CustomerBillingDetail;
            $customer_billing_address->title = 'Ecom Shipping Address';
            $customer_billing_address->customer_id = $order_detail['customer_id'];
            $customer_billing_address->save();
         }

         $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
         $quotation_config =  unserialize($quotation_qry->print_prefrences);
         $default_warehouse = $quotation_config['status'][5];

         $warehouse_short_code = Warehouse::select('order_short_code')->where('id', $quotation_config['status'][5])->first();

         $draf_status     = Status::where('id',2)->first();
         $draft_status_prefix    = $draf_status->prefix.''.$warehouse_short_code->order_short_code;
         $customer_category_prefix = $customer->CustomerCategory->short_code;

         $counter_formula = $draf_status->counter_formula;
         $counter_formula = explode('-',$counter_formula);
         $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

         $date = Carbon::now();
         $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
         $current_date = Carbon::now();


         $c_p_ref = Order::whereIn('status_prefix',[$draft_status_prefix])->where('ref_id','LIKE',"$date%")->where('ref_prefix',$customer_category_prefix)->orderby('id','DESC')->first();
         $str = @$c_p_ref->ref_id;
         $onlyIncrementGet = substr($str, 4);
         if($str == NULL){
            $onlyIncrementGet = 0;
         }
         $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
         $system_gen_no = $date.$system_gen_no;

         $final_order_total = 0;
         $order = new Order;
         $order->user_id = 1;
         $order->status_prefix = $draft_status_prefix;
         $order->ref_prefix = $customer_category_prefix;
         $order->ref_id = $system_gen_no;
         $order->customer_id = $customer->id;
         $order->from_warehouse_id = $default_warehouse;
         $order->total_amount = $order_detail['total_order_amount'];
         $order->delivery_request_date = $order_detail['delivery_date'];
         $order->converted_to_invoice_on = Carbon::now();
         // $order->shipping = $order_detail['shipping'];
         $order->primary_status = 2;
         $order->status = 34;
         $order->ecommerce_order = 1;
         $order->created_by = $order_detail['created_by'];
         $order->ecommerce_order_id = $order_detail['id'];
         $order->ecommerce_order_no = $order_detail['unique_order_no'];
         $order->billing_address_id = $customer_billing_address->id;
         $order->payment_image = $order_detail['payment_image']; 
         $order->delivery_note = $order_detail['delivery_note']; 
         $order->order_note_type = $order_detail['order_delivery_type']; 
         $order->is_tax_order = $order_detail['is_tax_order']; 

         $order->billing_address_id = $order_detail['sup_billing_id']; 
         $order->shipping_address_id = $order_detail['sup_shipping_id']; 
         
         if($order->save()){
            $ecom_order_no = $draft_status_prefix.'-'.$customer_category_prefix.$system_gen_no;
         }

         $orderID = $order->id;

         $order_note = new OrderNote;
         $order_note->order_id = $orderID;
         $order_note->note = $order_detail['delivery_note']; 
         $order_note->type = 'customer';
         $order_note->save();

         $customer_address_order = new CustomerOrderAddressDetail;
         $customer_address_order->order_id =  $orderID;
         $customer_address_order->customer_id =  $customer->id;
         $customer_address_order->customer_billing_id =  $customer_billing_address->id;
         $customer_address_order->customer_name =  $customer_billing_address->billing_contact_name;
         $customer_address_order->street_address = $customer_billing_address->billing_address;
         $customer_address_order->city =  $customer_billing_address->billing_city;
         $customer_address_order->state =  $customer_billing_address->billing_state;
         $customer_address_order->zipcode =  $customer_billing_address->billing_zip;
         $customer_address_order->country =  $customer_billing_address->billing_country;
         $customer_address_order->phone =  $customer_billing_address->billing_phone;
         $customer_address_order->is_tax_order =  $order_detail['is_tax_order'];
         $customer_address_order->tax_id =  $customer_billing_address->tax_id; 
         $customer_address_order->tax_name =  $customer_billing_address->company_name;
         $customer_address_order->tax_address =  $customer_billing_address->billing_address;
         $customer_address_order->save();
      


         foreach(@$request->products as $key => $value){

            $order = Order::where('ecommerce_order_id',$value['order_id'])->first();
            $customer = Customer::select('category_id')->where('id', $order->customer_id)->first();
            $product = Product::where('id', $value['product_id'])->first();
            $user_warehouse = User::select('warehouse_id')->where('id', $order->user_id)->first();
            $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$value['product_id'])->where('customer_type_id',$customer->category_id)->first();
            $is_mkt = CustomerTypeProductMargin::select('is_mkt')->where('product_id',$value['product_id'])->where('customer_type_id',$customer->category_id)->first();
            if($CustomerTypeProductMargin != null ){
               $margin      = $CustomerTypeProductMargin->default_value;
               $margin = (($margin/100)*$product->selling_price);
               $product_ref_price  = $margin+($product->selling_price);
               $exp_unit_cost = $product_ref_price;
            }

            if($product->ecom_selling_unit){
               $sell_unit = $product->ecom_selling_unit;
            }else{
               $sell_unit = $product->selling_unit;
            }

            $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
            $quotation_config =  unserialize($quotation_qry->print_prefrences);
            $default_warehouse = $quotation_config['status'][5];


            $price_calculate_return = $product->ecom_price_calculate($product,$order);
            $unit_price = $value['product_price'];
            $price_type = $price_calculate_return[1];
            $price_date = $price_calculate_return[2];
            $o_products              = new OrderProduct;
            $o_products->order_id    = $order->id;
            $o_products->ecommerce_order_id    = $order->ecommerce_order_id;
            $o_products->name        = $value['product_name'];
            $o_products->product_id  = $value['product_id'];
            $o_products->is_warehouse  = @$default_warehouse;
            $o_products->hs_code     = $product->hs_code;
            $o_products->brand       = $product->brand;
            $o_products->product_temprature_c = $product->product_temprature_c;
            $o_products->short_desc  = $product->short_desc;
            $o_products->category_id = $product->category_id;
            $o_products->type_id     = $product->type_id;
            $o_products->from_warehouse_id     = $default_warehouse;
            $o_products->user_warehouse_id     = $user_warehouse->warehouse_id;
            $o_products->supplier_id = $product->supplier_id;
            $o_products->selling_unit= $product->selling_unit;
            $o_products->ecom_selling_unit= $product->ecom_selling_unit;
            $o_products->exp_unit_cost  = $exp_unit_cost;
            $o_products->actual_unit_cost = $product->selling_price;
            $o_products->is_mkt      = $is_mkt->is_mkt;
            $vat_amount = 0;
            $vat_amount_total_over_item = 0;
            if($product->vat > 0 )
            {
               $total_vat_percent = $product->vat;
               $vat_amount = ($unit_price * 100) / (100 + $total_vat_percent);
               $unit_price = number_format($vat_amount,2,'.','');
               $vat = $product->vat;

               $vat_amountt = @$unit_price * ( @$vat / 100 );
               $vat_amount = number_format($vat_amountt,4,'.','');
               $vat_amount_total_over_item = $vat_amount * $value['products_quantity'];
               $o_products->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');

               // $o_products->vat_amount_total = $vat_amount * $value['products_quantity'];
               // $unit_price = $unit_price - $vat_amount;
               $o_products->unit_price  = number_format($unit_price,2,'.','');
               $o_products->vat = $product->vat;

            }
            else
            {
               $o_products->unit_price  = number_format($unit_price,2,'.','');
            }
            $o_products->unit_price_with_vat = number_format($unit_price + $vat_amount,2,'.','');
            $o_products->margin               = $price_type;
            $o_products->status               = 34;
            $o_products->last_updated_price_on = $price_date;
            // $o_products->total_price = number_format($unit_price,2,'.','');
            $o_products->discount    = $value['discount'];
            // $o_products->category_id = $value['category_id'];
            if($product->ecom_selling_unit)
            {
               if($product->selling_unit != $product->ecom_selling_unit)
               {
                  $o_products->quantity    = round($value['products_quantity'] * $product->selling_unit_conversion_rate,4);
                  $o_products->number_of_pieces    = round($value['products_quantity'],4);
                  $o_products->is_retail = 'pieces';
               }
               else
               {
                  $o_products->quantity    = round($value['products_quantity'] * $product->unit_conversion_rate,4);
               }
            }
            else
            {
               $o_products->quantity    = round($value['products_quantity'] * $product->unit_conversion_rate,4);
            }
            // $o_products->discount    = $value['discount'];
            // $o_products->unit_price  = $value['product_price'];

            // get cogs prices for locked
            if($product->selling_unit_conversion_rate != NULL && $product->selling_unit_conversion_rate != '')
            {
               $ecom_cogs = $product->selling_unit_conversion_rate * $product->selling_price;
            }
            else
            {
               $ecom_cogs = $product->selling_price;
            }


            $o_products->locked_actual_cost = number_format($ecom_cogs,2,'.','');
            $o_products->actual_cost = number_format($ecom_cogs,2,'.','');
            $total_p = ($value['products_quantity'] * $unit_price);
            $o_products->total_price = number_format($total_p,2,'.','');

            // $o_products->vat_amount_total = '0.000000';
            $total_discount_price = $unit_price - (($value['discount']/100) * $unit_price);
            $o_products->unit_price_with_discount = $total_discount_price;
            $o_products->total_price_with_vat = number_format($total_p + $vat_amount_total_over_item,2,'.','');
            $o_products->warehouse_id= $default_warehouse;
            $o_products->save();
            $final_order_total += $o_products->total_price_with_vat;

            // $wh_reserve_pro = WarehouseProduct::where('product_id',$o_products->product_id)->where('warehouse_id', $quotation_config['status'][5])->first();
            // $my_helper =  new MyHelper;
            // $res_wh_update = $my_helper->updateWarehouseProduct($wh_reserve_pro);
            
            $wh_reserve_pro = WarehouseProduct::where('product_id',$o_products->product_id)->where('warehouse_id', $quotation_config['status'][5])->first();
            if($product->ecom_selling_unit){
               $new_reserve_qty = $o_products->quantity;
            }else{
               $new_reserve_qty = $o_products->quantity;
            }

            $reserver_quan_update = $wh_reserve_pro->ecommerce_reserved_quantity + $new_reserve_qty;
            $new_reserve_qty_combine = $wh_reserve_pro->reserved_quantity + $reserver_quan_update;
            $new_available_qty  = $wh_reserve_pro->current_quantity - $new_reserve_qty_combine;
            $wh_reserve_pro->ecommerce_reserved_quantity =  $wh_reserve_pro->ecommerce_reserved_quantity + $new_reserve_qty;
            $wh_reserve_pro->available_quantity = $new_available_qty;
            $wh_reserve_pro->save();
            
         }
         // $order->total_amount = $final_order_total;
         // $order->save();

         if($order_detail['shipping'] != 0)
         {
            $new_billed_item = new OrderProduct;
            $new_billed_item->order_id    = $order->id;
            $new_billed_item->short_desc = 'Transportation Charge';
            $new_billed_item->ecommerce_order_id    = $order->ecommerce_order_id;
            $new_billed_item->is_warehouse    = 1;
            $new_billed_item->quantity    = 1;
            $new_billed_item->unit_price_with_vat    = $order_detail['shipping'];
            $new_billed_item->unit_price_with_discount    = $order_detail['shipping'];
            $new_billed_item->unit_price    = $order_detail['shipping'];
            $new_billed_item->total_price    = $order_detail['shipping'];
            $new_billed_item->total_price_with_vat    = $order_detail['shipping'];
            $new_billed_item->warehouse_id    = 1;
            $new_billed_item->qty_shipped    = 1;
            $new_billed_item->status    = 6;
            $new_billed_item->is_billed    = 'Billed';
            $new_billed_item->is_retail    = 'qty';
            $new_billed_item->save();

         }

         return $ecom_order_no;
      }else{
         return 'Order not created';
      }
     
   }


   public function customer(Request $request){
      // dd($request['request']);
      // dd($request['request']);
      $data = json_decode($request['request']);
      // dd($data->customer);
      $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
      $quotation_config =  unserialize(@$quotation_qry->print_prefrences);
      $customer = Customer::where('phone',$data->customer->phone)->first();
      if($customer)
      {
         $customer->ecommerce_customer_id = $data->customer->customer_id;
         $customer->save();
      }
      $customer = Customer::where('ecommerce_customer_id',$data->customer->customer_id)->first();
      if($customer)
      {
         $finded_customer_id = $customer->id;
      }
      else
      {
          $customer = new Customer;
         $customer->ecommerce_customer_id = $data->customer->customer_id;
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
         $customer->primary_sale_id = $data->customer->primary_sale_id;
         $customer->first_name = $data->customer->first_name;
         $customer->last_name = $data->customer->last_name;
         $customer->reference_name = $data->customer->first_name.' '.$data->customer->last_name;
         $customer->company = $data->customer->first_name.' '.$data->customer->last_name;
         $customer->email = $data->customer->email_address;
         $customer->phone = $data->customer->phone;
         $customer->country = 217;
         $customer->address_line_1 = $data->customer->street_address;
         $customer->city = $data->customer->city;
         $customer->postalcode = $data->customer->post_code;
         $customer->first_name = $data->customer->first_name;
         $customer->last_name = $data->customer->last_name;
         $customer->save();

         $finded_customer_id = $customer->id;
      }
      //To sync customer with Ecom customer
      $array_val = array();
      $link = "/api/get-new-premium-customer-id"."/".$customer->id."/".$data->customer->customer_id;
      $url  = config('app.ecom_url').$link;
      $curl = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_POSTFIELDS => json_encode($array_val),
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/pdf",
            "postman-token: 3c9a9b06-8be6-66e9-ea75-4a2b3acbd840"
        ),
      ));

      $response = curl_exec($curl);
      //To find customer shipping address
      $customer_name = $data->shipping->first_name.' '.$data->shipping->last_name;
      $shipping_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('company_name',$customer_name)->where('billing_address',$data->shipping->street_address)->where('billing_country',217)->where('billing_state',$data->shipping->state)->where('billing_city',$data->shipping->city)->where('billing_email',$data->shipping->email_address)->where('billing_zip',$data->shipping->post_code)->first();

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
         $ecom_order_shipping_address->billing_phone = $data->shipping->phone;
         $ecom_order_shipping_address->billing_email = $data->shipping->email_address;
         $ecom_order_shipping_address->billing_address = $data->shipping->street_address;
         $ecom_order_shipping_address->billing_country = 217;
         $ecom_order_shipping_address->billing_city =  $data->shipping->city;
         $ecom_order_shipping_address->billing_zip =  $data->shipping->post_code;
         $ecom_order_shipping_address->billing_state =  $data->shipping->state;
         $ecom_order_shipping_address->status = 1;
         // $ecom_order_shipping_address->tax_id = $customer_array['tax_id'];
         // $ecom_order_shipping_address->company_name = $customer_array['company_name'];
         $ecom_order_shipping_address->save();
         $order_shipping_address_id = $ecom_order_shipping_address->id;
      }

       //To find customer billing address
      $customer_name = $data->billing->first_name.' '.$data->billing->last_name;
      $billing_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('tax_id',$data->billing->tax_id)->where('company_name',$customer_name)->where('billing_address',$data->billing->street_address)->where('billing_country',217)->where('billing_state',$data->billing->state)->where('billing_city',$data->billing->city)->where('billing_email',$data->billing->email_address)->where('billing_zip',$data->billing->post_code)->where('billing_phone',$data->billing->phone)->first();

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
         $ecom_order_billing_address->billing_phone = $data->billing->phone;
         $ecom_order_billing_address->billing_email = $data->billing->email_address;
         $ecom_order_billing_address->billing_address = $data->billing->street_address;
         $ecom_order_billing_address->billing_country = 217;
         $ecom_order_billing_address->billing_city =  $data->billing->city;
         $ecom_order_billing_address->billing_zip =  $data->billing->post_code;
         $ecom_order_billing_address->billing_state =  $data->billing->state;
         $ecom_order_billing_address->status = 1;
         $ecom_order_billing_address->tax_id = $data->billing->tax_id;
         // $ecom_order_billing_address->company_name = $customer_array['company_name'];
         $ecom_order_billing_address->save();
         $order_billing_address_id = $ecom_order_billing_address->id;
      }

      return response()->json(['finded_customer_id' => $finded_customer_id, 'order_shipping_address_id' => $order_shipping_address_id, 'order_billing_address_id' => $order_billing_address_id,'customer_id' => $customer->id]);

    }

    public function sendcustomerstoecom()
    {
      $getCustomer = Customer::with('getbilling')->where('category_id',4)->get();
      $link = '/api/getsupplychaincustomer'; 
      $this->curl_call($link, $getCustomer);
    }


    public function curl_call($link, $data)
    {
      $url  =  config('app.ecom_url').$link;
      $curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "content-type: application/json",
          "postman-token: 08f91779-330f-bf8f-1a64-d425e13710f9",
        ),
      ));

      $response = curl_exec($curl);
      // dd($response);
      $err = curl_error($curl);

      curl_close($curl);
      if ($err) 
      {
        return "cURL Error #:" . $err;
      } 
      else 
      {
        return $response;
      }
    }

    public function  getProductStock($product_id, $warehouse_id)
    {
      $product = WarehouseProduct::where('product_id',$product_id)->where('warehouse_id',$warehouse_id)->first();

      if($product)
      {
         return response()->json(['success' => true,'stock' => $product]);
      }
      else
      {
         return response()->json(['success' => false,'msg' => $product_id]);
      }
    }


    public function checkIfUserExist($phone_number, $password)
    {
      $phone_number = str_replace('$$$', ' ', $phone_number);
      $cust_billing_detail = Customer::where('phone',$phone_number)->first();

      if($cust_billing_detail)
      {
         $getCustomerData = Customer::with('getDefaultBilling')->find($cust_billing_detail->id);
         return response()->json(['success' => true, 'customer' => $getCustomerData]);
      }
      else
      {
         return response()->json(['success' => false, 'customer' => 'No Customer Found']);
      }
    }

   public function getNewEcomCustId($cust_id, $ecom_cust_id)
   {
      $find_customer =  Customer::find($cust_id);
      if($find_customer)
      {
         $find_customer->ecommerce_customer_id = $ecom_cust_id;
         $find_customer->save();
         return response()->json(['success' => true, 'customer' => $find_customer]);
      }
      else
      {
         return response()->json(['success' => false, 'customer' => 'No Customer Found']);
      }
   }

   public function checkWarehouseFreeShip($id)
   {
      $warehouse = WarehouseZipCode::find($id);
      if($warehouse)
      {
         return response()->json(['success' => true, 'warehouse_zip_code' => $warehouse]);
      }
      else
      {
         return response()->json(['success' => false]);
      }
   }
}
