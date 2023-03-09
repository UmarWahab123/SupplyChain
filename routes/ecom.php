<?php
use Codexshaper\WooCommerce\Facades\Order;
use App\Models\Sales\Customer;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\QuotationConfig;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\WooCom\EcomProduct;
use App\Models\Common\Product;
use App\Models\Common\CustomerTypeProductMargin;
use App\User;
use Carbon\Carbon;

Route::get('/get-ecom-orders',function(){
	$check_orders = \App\Models\Common\Order\Order::where('woo_com_id',1)->orderby('woo_com_id','desc')->first();
	// dd($check_orders);
	if($check_orders != null)
	{
		$orders = Order::where('id','>',$check_orders->ecommerce_order_id)->get();
	}
	else
	{
		$orders = Order::all();
	}
	// return 'checking ecom';
	DB::beginTransaction();
	foreach($orders as $order)
	{
		$check_if_order_already_created = \App\Models\Common\Order\Order::where('woo_com_id',$order->id)->first();
		if($check_if_order_already_created != null)
		{
			break;
		}
		if($order->billing->phone != "" )
		{
			$customer = Customer::where('phone',$order->billing->phone)->first();
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
		         $customer->first_name = @$order->billing->first_name;
		         $customer->last_name = @$order->billing->last_name;
		         $customer->reference_name = @$order->billing->first_name.' '.@$order->billing->last_name;
		         $customer->company = @$order->billing->company;
		         $customer->email = @$order->billing->email;
		         $customer->phone = @$order->billing->phone;
		         $customer->country = 217;
		         $customer->address_line_1 = @$order->billing->address_1;
		         $customer->address_line_2 = @$order->billing->address_2;
		         $customer->city = @$order->billing->city;
		         $customer->postalcode = @$order->billing->postcode;
		         $customer->save();

		         $finded_customer_id = $customer->id;
		    }
		}
		else
		{
			$customer = Customer::where('id',$order->customer_id)->first();

			$finded_customer_id = $customer->id;
			if($customer == null)
			{
				return response()->json(['success' => false, 'message' => 'Customer does not exits !!!']);
			}
		}
		if(@$order->billing->state != "")
        {
            $state = State::where('abbrevation',@$order->billing->state)->first();
            $state_id = $state != null ? $state->id : null;
        }
        else
        {
            $state_id = null;
        }
	        //To find customer shipping address
		    $customer_name = @$order->billing->first_name.' '.@$order->billing->last_name;
		    $shipping_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('company_name',@$order->billing->company)->where('billing_address',@$order->billing->address_1)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',@$order->billing->city)->where('billing_email',@$order->billing->email)->where('billing_zip',@$order->billing->postcode)->first();
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
		         $ecom_order_shipping_address->company_name = @$order->billing->company;
		         $ecom_order_shipping_address->show_title = 1;
		         $ecom_order_shipping_address->tax_id = '--';
		         $ecom_order_shipping_address->billing_phone = @$order->billing->phone;
		         $ecom_order_shipping_address->billing_email = @$order->billing->email;
		         $ecom_order_shipping_address->billing_address = @$order->billing->address_1;
		         $ecom_order_shipping_address->billing_country = 217;
		         $ecom_order_shipping_address->billing_city =  @$order->billing->city;
		         $ecom_order_shipping_address->billing_zip =  @$order->billing->postcode;
		         $ecom_order_shipping_address->billing_state =  $state_id;
		         $ecom_order_shipping_address->status = 1;
		         $ecom_order_shipping_address->save();
		         $order_shipping_address_id = $ecom_order_shipping_address->id;
		      }

		      $ecom_order_billing_address = null;
		      if(@$order->shipping->first_name != "")
		      {
		        if(@$order->shipping->state != "")
		        {
		            $state = State::where('abbrevation',@$order->shipping->state)->first();
		            $state_id = $state != null ? $state->id : null;
		        }
		        else
		        {
		            $state_id = null;
		        }
		       //To find customer billing address
		      $customer_name = @$order->shipping->first_name.' '.@$order->shipping->last_name;
		      $billing_address = CustomerBillingDetail::where('customer_id',$finded_customer_id)->where('company_name',@$order->shipping->company)->where('billing_address',@$order->shipping->address_1)->where('billing_country',217)->where('billing_state',$state_id)->where('billing_city',@$order->shipping->city)->where('billing_email',@$order->billing->email)->where('billing_zip',@$order->shipping->postcode)->where('billing_phone',@$order->shipping->phone)->first();
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
		         $ecom_order_billing_address->company_name = @$order->shipping->company;
		         $ecom_order_billing_address->show_title = 1;
		         $ecom_order_billing_address->billing_phone = @$order->shipping->phone;
		         $ecom_order_billing_address->billing_email = @$order->billing->email;
		         $ecom_order_billing_address->billing_address = @$order->shipping->address_1;
		         $ecom_order_billing_address->billing_country = 217;
		         $ecom_order_billing_address->billing_city =  @$order->shipping->city;
		         $ecom_order_billing_address->billing_zip =  @$order->shipping->postcode;
		         $ecom_order_billing_address->billing_state =  $state_id;
		         $ecom_order_billing_address->status = 1;
		         $ecom_order_billing_address->tax_id = @$order->shipping->tax_id;
		         // $ecom_order_billing_address->company_name = $customer_array['company_name'];
		         $ecom_order_billing_address->save();
		         $order_billing_address_id = $ecom_order_billing_address->id;
		      }
		    }
		    else
		    {
		        $order_billing_address_id = $order_shipping_address_id;
		    }

		    $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
	         $quotation_config =  unserialize($quotation_qry->print_prefrences);
	         $default_warehouse = $quotation_config['status'][5];

	         $warehouse_short_code = Warehouse::select('order_short_code')->where('id', $quotation_config['status'][5])->first();

	         $draf_status     = Status::where('id',2)->first();
	         $draft_status_prefix    = $draf_status->prefix.''.$warehouse_short_code->order_short_code;
	         $customer_category_prefix = @$customer->CustomerCategory->short_code;

	         $counter_formula = $draf_status->counter_formula;
	         $counter_formula = explode('-',$counter_formula);
	         $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

	         $date = Carbon::now();
	         $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
	         $current_date = Carbon::now();


	         $c_p_ref = \App\Models\Common\Order\Order::whereIn('status_prefix',[$draft_status_prefix])->where('ref_id','LIKE',"$date%")->where('ref_prefix',$customer_category_prefix)->orderby('id','DESC')->first();
	         $str = @$c_p_ref->ref_id;
	         $onlyIncrementGet = substr($str, 4);
	         if($str == NULL){
	            $onlyIncrementGet = 0;
	         }
	         $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
	         $system_gen_no = $date.$system_gen_no;

	         $final_order_total = 0;
	         $new_order = new \App\Models\Common\Order\Order;
	         $new_order->user_id = 1;
	         $new_order->status_prefix = $draft_status_prefix;
	         $new_order->ref_prefix = $customer_category_prefix;
	         $new_order->ref_id = $system_gen_no;
	         $new_order->customer_id = $customer->id;
	         $new_order->from_warehouse_id = $default_warehouse;
	         $new_order->total_amount = $order->total;
	         $new_order->created_by = 1;
	         $new_order->delivery_request_date = @$order->date_created;
	         $new_order->converted_to_invoice_on = Carbon::now();
	         $new_order->primary_status = 2;
	         $new_order->status = 34;
	         $new_order->ecommerce_order = 1;
	         $new_order->ecommerce_order_id = @$order->id;
	         $new_order->delivery_note = @$order->customer_note; 
	         $new_order->billing_address_id = $order_billing_address_id; 
	         $new_order->shipping_address_id = $order_shipping_address_id; 
	         $new_order->save();

	         if($order->customer_note != "")
	         {
	         	$orderID = $new_order->id;

		         $order_note = new OrderNote;
		         $order_note->order_id = $orderID;
		         $order_note->note = @$order->customer_note; 
		         $order_note->type = 'customer';
		         $order_note->save();
	         }
	        foreach(@$order->line_items as $key => $value){
	        $product = null;
            $order = \App\Models\Common\Order\Order::where('id',$new_order->id)->first();
            $customer = Customer::select('category_id')->where('id', $new_order->customer_id)->first();
            if($value->product_id != null && $value->product_id != "")
            {
            	$check_ecom_product = EcomProduct::where('ecom_product_id',$value->product_id)->first();
            	if($check_ecom_product != null)
            	{
            		$product = Product::find($check_ecom_product->web_product_id);
            	}
            }
            if($product == null)
            {
            	return false;
            }
            // $product = Product::where('id', $value['product_id'])->first();
            $user_warehouse = User::select('warehouse_id')->where('id', $order->user_id)->first();
            $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$product->id)->where('customer_type_id',$customer->category_id)->first();
            $is_mkt = CustomerTypeProductMargin::select('is_mkt')->where('product_id',$product->id)->where('customer_type_id',$customer->category_id)->first();
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
            $unit_price = $value->price;
            $price_type = $price_calculate_return[1];
            $price_date = $price_calculate_return[2];
            $o_products              = new OrderProduct;
            $o_products->order_id    = $order->id;
            // $o_products->ecommerce_order_id    = $order->ecommerce_order_id;
            $o_products->name        = $value->name;
            $o_products->product_id  = $product->id;
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
               $vat_amount_total_over_item = $vat_amount * $value->quantity;
               $o_products->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
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
            $o_products->discount    = null;
            if($product->ecom_selling_unit)
            {
               if($product->selling_unit != $product->ecom_selling_unit)
               {
                  $o_products->quantity    = round($value->quantity * $product->selling_unit_conversion_rate,4);
                  $o_products->number_of_pieces    = round($value->quantity,4);
                  $o_products->is_retail = 'pieces';
               }
               else
               {
                  $o_products->quantity    = round($value->quantity * $product->unit_conversion_rate,4);
               }
            }
            else
            {
               $o_products->quantity    = round($value->quantity * $product->unit_conversion_rate,4);
            }
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
            $total_p = ($value->quantity * $unit_price);
            $o_products->total_price = number_format($total_p,2,'.','');

            $total_discount_price = $unit_price - ((@$value->discount/100) * $unit_price);
            $o_products->unit_price_with_discount = $total_discount_price;
            $o_products->total_price_with_vat = number_format($total_p + $vat_amount_total_over_item,2,'.','');
            $o_products->warehouse_id= $default_warehouse;
            $o_products->save();
            $final_order_total += $o_products->total_price_with_vat;
            
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
	}
	DB::commit();
    return response()->json(['success' => true, 'message' => 'Success']);
});