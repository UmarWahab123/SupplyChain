<?php

namespace App\Http\Controllers\Api;

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

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('ecommerce_order',1)->with('order_products:id,order_id,product_id,name,short_desc,brand,quantity,qty_shipped,unit_price,discount,total_price','customer:id,first_name,last_name,email,phone,address_line_1','customer_billing_address:id,customer_id,title,billing_contact_name,billing_email,company_name,billing_phone,billing_address,billing_country,billing_state,billing_city','customer_billing_address.getcountry:id,name,thai_name','customer_billing_address.getstate:id,name,thai_name','customer_shipping_address:id,customer_id,title,billing_contact_name,billing_email,company_name,billing_phone,billing_address,billing_country,billing_state,billing_city','customer_shipping_address.getcountry:id,name,thai_name','customer_shipping_address.getstate:id,name,thai_name')->whereIn('primary_status',[2,3])->paginate(50)->makeHidden(['in_status_prefix','in_ref_prefix','in_ref_id','manual_ref_no','user_id','from_warehouse_id','credit_note_date','payment_due_date','payment_terms_id','target_ship_date','memo','created_by','is_vat','is_manual','previous_primary_status','previous_status','order_note_type','ecom_order','dont_show','is_tax_order','created_at','updated_at']);
        if($orders->count() > 0)
        {
            return response()->json(['success' => true, 'orders' => $orders]);
        }

        return response()->json(['success' => false, 'message' => 'No orders found .']);
    }
    public function show($id)
    {
        try{
        $order = Order::where('ecommerce_order',1)->with('order_products:id,order_id,product_id,name,short_desc,brand,quantity,qty_shipped,unit_price,discount,total_price','customer:id,first_name,last_name,email,phone,address_line_1','customer_billing_address:id,customer_id,title,billing_contact_name,billing_email,company_name,billing_phone,billing_address,billing_country,billing_state,billing_city','customer_billing_address.getcountry:id,name,thai_name','customer_billing_address.getstate:id,name,thai_name','customer_shipping_address:id,customer_id,title,billing_contact_name,billing_email,company_name,billing_phone,billing_address,billing_country,billing_state,billing_city','customer_shipping_address.getcountry:id,name,thai_name','customer_shipping_address.getstate:id,name,thai_name')->whereIn('primary_status',[2,3])->find($id);
        if($order != null)
        {
            $order->makeHidden(['in_status_prefix','in_ref_prefix','in_ref_id','manual_ref_no','user_id','from_warehouse_id','credit_note_date','payment_due_date','payment_terms_id','target_ship_date','memo','created_by','is_vat','is_manual','previous_primary_status','previous_status','order_note_type','ecom_order','dont_show','is_tax_order','created_at','updated_at']);
        }
        if($order != null)
        {
            return response()->json(['success' => true, 'order' => $order]);
        }

        return response()->json(['success' => false, 'message' => 'Order '.$id.' not found .']);
        }
        catch(\Excepion $e)
        {
            return response()->json(['success' => false]);
        }
    }

    public function create(Request $request)
    {
        \Log::info('order created');
      $customer = Customer::where('id', $request->order_detail['customer_id'])->first();
      if($customer)
      {
        \DB::beginTransaction();
         $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
         $quotation_config =  unserialize(@$quotation_qry->print_prefrences);
         $default_warehouse = $quotation_config['status'][5];

         $warehouse_short_code = Warehouse::select('order_short_code')->where('id', $quotation_config['status'][5])->first();

         $draf_status     = Status::where('id',2)->first();
         $draft_status_prefix    = $draf_status->prefix.''.@$warehouse_short_code->order_short_code;
         $customer_category_prefix = @$customer->CustomerCategory->short_code;

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
         $order->total_amount = $request->order_detail['total_order_amount'];
         $order->created_by = 1;
         $order->delivery_request_date = $request->order_detail['delivery_date'];
         $order->converted_to_invoice_on = Carbon::now();
         $order->primary_status = 2;
         $order->status = 10;
         $order->ecommerce_order = 1;
         $order->delivery_note = $request->order_detail['delivery_note'];
         $order->billing_address_id = $request->order_detail['customer_billing_id'];
         $order->shipping_address_id = $request->order_detail['customer_shipping_id'];
         $order->save();
         if(isset($request->order_detail['remark'])){
          if($request->order_detail['remark'] && $order->save()) {
            $note = new OrderNote;
            $note->order_id = $order->id;
            $note->note = $request->order_detail['remark'];
            $note->type = 'warehouse';
            $note->save();
         }
        }

         if($order->save()){
            $ecom_order_no = $draft_status_prefix.'-'.$customer_category_prefix.$system_gen_no;
         }

         $orderID = $order->id;

         $order_note = new OrderNote;
         $order_note->order_id = $orderID;
         $order_note->note = $request->order_detail['delivery_note'];
         $order_note->type = 'customer';
         $order_note->save();

         foreach(@$request->order_products as $key => $value){

            $order = Order::where('id',$order->id)->first();
            $customer = Customer::select('category_id')->where('id', $order->customer_id)->first();
            $product = Product::where('id', $value['product_id'])->first();
            $exp_unit_cost = 0;
            $is_mkt = null;
            if($product){
                $user_warehouse = User::select('warehouse_id')->where('id', $order->user_id)->first();
                $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$value['product_id'])->where('customer_type_id',$customer->category_id)->first();
                $is_mkt = CustomerTypeProductMargin::select('is_mkt')->where('product_id',$value['product_id'])->where('customer_type_id',$customer->category_id)->first();
                if($CustomerTypeProductMargin != null ){
                   $margin      = $CustomerTypeProductMargin->default_value;
                   $margin = (($margin/100)*$product->selling_price);
                   $product_ref_price  = $margin+($product->selling_price);
                   $exp_unit_cost = $product_ref_price;
                }

                if(@$product->ecom_selling_unit){
                   $sell_unit = $product->ecom_selling_unit;
                }else{
                   $sell_unit = @$product->selling_unit;
                }

                $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
                $quotation_config =  unserialize($quotation_qry->print_prefrences);
                $default_warehouse = $quotation_config['status'][5];


                $price_calculate_return = @$product->ecom_price_calculate($product,$order);
                $unit_price = $value['product_price'];
                $price_type = $price_calculate_return[1];
                $price_date = $price_calculate_return[2];
                $o_products              = new OrderProduct;
                $o_products->order_id    = $order->id;
                // $o_products->ecommerce_order_id    = $order->ecommerce_order_id;
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
                $o_products->is_mkt      = @$is_mkt->is_mkt;
                $vat_amount = 0;
                $vat_amount_total_over_item = 0;
                //new code 
                    $vat = $product->vat;
                    $o_products->vat = $vat;

                    $unit_price_with_vat = $unit_price;
                    $unit_price = $unit_price / (1 + ($vat / 100));
                    $unit_price_after_discount = $unit_price - ($unit_price * ($value['discount'] / 100));
                    $unit_price_with_vat_after_discount = $unit_price_with_vat - ($unit_price_with_vat * ($value['discount'] / 100));
                    $vat_amount_total = ($unit_price_with_vat_after_discount - $unit_price_after_discount) * $value['products_quantity'];

                    $o_products->unit_price = round($unit_price,2);
                    $o_products->unit_price_with_vat = round($unit_price_with_vat,2);
                    $o_products->unit_price_with_discount = round($unit_price_after_discount,2);
                    $o_products->total_price = round($unit_price_after_discount * $value['products_quantity'],2);
                    $o_products->total_price_with_vat = round($unit_price_with_vat_after_discount * $value['products_quantity'],2);
                    $o_products->vat_amount_total = round($vat_amount_total,4);
                // end new code
                $o_products->margin               = $price_type;
                $o_products->status               = 10;
                $o_products->last_updated_price_on = $price_date;
                $o_products->discount    = $value['discount'];
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
                // get cogs prices for locked
                if($product->selling_unit_conversion_rate != NULL && $product->selling_unit_conversion_rate != '')
                {
                   $ecom_cogs = $product->selling_unit_conversion_rate * $product->selling_price;
                }
                else
                {
                   $ecom_cogs = $product->selling_price;
                }

                $total_p = ($value['products_quantity'] * $unit_price);
                $o_products->locked_actual_cost = number_format($ecom_cogs,2,'.','');
                $o_products->actual_cost = number_format($ecom_cogs,2,'.','');

                $total_discount_price = $unit_price - (($value['discount']/100) * $unit_price);
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
            }else{
                $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
                $quotation_config =  unserialize(@$quotation_qry->print_prefrences);
                $default_warehouse = isset($quotation_config['status'][5]) ? $quotation_config['status'][5] : 3;

                $order_products = new OrderProduct;
                $order_products->order_id         = $order->id;
                $order_products->name             = $value['product_name'];
                $order_products->short_desc       = $value['product_name'];
                $order_products->unit_price         = $value['product_price'];
                $order_products->unit_price_with_vat= $value['product_price'];
                $order_products->total_price         = $value['product_price'];
                $order_products->total_price_with_vat         = $value['product_price'];
                $order_products->quantity         = 1;
                $order_products->qty_shipped = 1;
                $order_products->warehouse_id     = @$default_warehouse;
                $order_products->status           = 6;
                $order_products->is_billed        = "Billed";
                $order_products->created_by       = 1;
                $order_products->save();
            }

         }

         $order = Order::with('order_products')->where('id',$order->id)->first();
         $order->update(['total_amount' => @$order->order_products->sum('total_price_with_vat')]);
         \DB::commit();
         return response()->json(['success' => true, 'order' => $order]);
      }
       \Log::info('order creation failed customer does not exist');
      return response()->json(['success' => false, 'message' => 'Customer does not exists !!!']);
    }
}
