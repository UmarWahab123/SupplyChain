<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Carbon;
use DateTime;
use App\Models\Common\Status;
use App\Models\Common\CustomerCategory;
use App\User;
use App\Models\Common\Order\OrderStatusHistory;

class PartialShipmentController extends Controller
{
    public function index($order_id)
    {
        if(Auth::check())
        {
            return view('sales.invoice.partial_shipment_view',compact('order_id'));
        }
        else
        {
            return redirect('login');
        }
    }

    public function partialShipmentRequest($order_id)
    {
        if(Auth::check())
        {
            $find_order = Order::find($order_id);
            if($find_order)
            {
                $quot_status     = Status::where('id',1)->first();
                $draf_status     = Status::where('id',2)->first();
                $counter_formula = $quot_status->counter_formula;
                $counter_formula = explode('-',$counter_formula);
                $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

                $date = Carbon::now();
                $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
                $company_prefix          = @Auth::user()->getCompany->prefix;
                $draft_customer_category = $find_order->customer->CustomerCategory;                
                if($find_order->customer->category_id == 6)
                {
                    $p_cat      = CustomerCategory::where('id',4)->first();
                    $ref_prefix = $p_cat->short_code;
                } 
                else
                {
                    $ref_prefix       = $draft_customer_category->short_code;
                }
                $quot_status_prefix   = $quot_status->prefix.$company_prefix;
                $draft_status_prefix  = $draf_status->prefix.$company_prefix;

                $c_p_ref = Order::whereIn('status_prefix',[$quot_status_prefix,$draft_status_prefix])->where('ref_id','LIKE',"$date%")->where('ref_prefix',$ref_prefix)->orderby('id','DESC')->first();
                $str = @$c_p_ref->ref_id;
                $onlyIncrementGet = substr($str, 4);
                if($str == NULL)
                {
                    $onlyIncrementGet = 0;
                }
                $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
                $system_gen_no = $date.$system_gen_no;

                /*Creating a new order*/
                $order                          = new Order;
                $order->status_prefix           = $quot_status_prefix;
                $order->ref_prefix              = $ref_prefix;
                $order->ref_id                  = $system_gen_no;
                $order->customer_id             = $find_order->customer_id;
                $order->target_ship_date        = $find_order->target_ship_date;
                $order->memo                    = $find_order->memo;
                $order->from_warehouse_id       = $find_order->from_warehouse_id;
                $order->shipping                = $find_order->shipping;
                $order->target_ship_date        = $find_order->target_ship_date;
                $order->payment_due_date        = $find_order->payment_due_date;
                $order->payment_terms_id        = $find_order->payment_terms_id;
                $order->delivery_request_date   = $find_order->delivery_request_date;
                $order->billing_address_id      = $find_order->billing_address_id;
                $order->shipping_address_id     = $find_order->shipping_address_id;
                $order->user_id                 = $find_order->customer->primary_sale_person->id;
                $order->converted_to_invoice_on = Carbon::now();
                $order->manual_ref_no           = $find_order->manual_ref_no;
                $order->is_vat                  = $find_order->is_vat;
                $order->created_by              = Auth::user()->id;
                $order->primary_status          = 1;
                $order->status                  = 6;
                $order->save();

                $checkUserReport = User::find($order->user_id);
                if($checkUserReport)
                {
                    if($checkUserReport->is_include_in_reports == 0)
                    {
                        $order->dont_show        = 1;
                        $order->save();
                    }
                }

                $status_history             = new OrderStatusHistory;
                $status_history->user_id    = Auth::user()->id;
                $status_history->order_id   = $order->id;
                $status_history->status     = 'Created (Through Partial Shipment)';
                $status_history->new_status = 'Quotation';
                $status_history->save();

                /*Get all products of the order which partial shipment is creating*/
                $getAllProducts = OrderProduct::where('order_id',$order_id)->where('is_billed','Product')->get();
                if($getAllProducts->count() > 0)
                {
                    foreach ($getAllProducts as $o_products) 
                    {
                        if($o_products->qty_shipped < $o_products->quantity)
                        {
                            $new_qty_difference = ($o_products->quantity - $o_products->qty_shipped);
                            
                            $new_order_product = OrderProduct::create([
                                'order_id'                  => $order->id,
                                'product_id'                => $o_products->product_id,
                                'category_id'               => $o_products->category_id,
                                'short_desc'                => $o_products->short_desc,
                                'brand'                     => $o_products->brand,
                                'type_id'                   => $o_products->type_id,
                                'number_of_pieces'          => $o_products->number_of_pieces,
                                'quantity'                  => $new_qty_difference,
                                'selling_unit'              => $o_products->selling_unit,
                                'margin'                    => $o_products->margin,
                                'vat'                       => $o_products->vat,
                                'unit_price'                => $o_products->unit_price,
                                'last_updated_price_on'     => $o_products->last_updated_price_on,
                                'unit_price_with_vat'       => $o_products->unit_price_with_vat,
                                'unit_price_with_discount'  => $o_products->unit_price_with_discount,
                                'is_mkt'                    => $o_products->is_mkt,
                                'supplier_id'               => $o_products->supplier_id,
                                'from_warehouse_id'         => $o_products->from_warehouse_id,
                                'user_warehouse_id'         => $o_products->user_warehouse_id,
                                'warehouse_id'              => $o_products->warehouse_id,
                                'is_warehouse'              => $o_products->is_warehouse,
                                'status'                    => 6,
                                'is_billed'                 => $o_products->is_billed,
                                'default_supplier'          => $o_products->default_supplier,
                                'created_by'                => $o_products->created_by,
                                'discount'                  => $o_products->discount,
                                'is_retail'                 => $o_products->is_retail,
                                'import_vat_amount'         => $o_products->import_vat_amount,
                            ]);

                            $quantity = $new_qty_difference;
                            $request = new \Illuminate\Http\Request();
                            $request->replace(['order_id' => $new_order_product->id, 'quantity' => $quantity, 'old_value' => 0]);
                            app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                        }
                    }
                }

                $sub_total_w_w   = 0 ;
                $query           = OrderProduct::where('order_id',$order->id)->get();
                foreach ($query as  $value) 
                {      
                  $sub_total_w_w += number_format($value->total_price_with_vat,2,'.','');
                }
                $grand_total = ($sub_total_w_w)-($order->discount)+($order->shipping);
                $order->update(['total_amount' => number_format($grand_total,2,'.','')]);

                /*generating DI from quotation*/
                $request = new \Illuminate\Http\Request();
                $request->replace(['inv_id' => $order->id]);
                app('App\Http\Controllers\Sales\OrderController')->makeDraftInvoice($request);
            }

            return response()->json(['success' => true, 'order_id' => $order->id]);
            // return redirect()->route("get-completed-draft-invoices",$order->id);
        }
        else
        {
            return redirect('login');
        }
    }
}
