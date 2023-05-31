<?php

namespace App\Models\Common\PurchaseOrders;

use App\Helpers\MyHelper;
use App\Helpers\QuantityReservedHistory;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\QuotationConfig;
use App\TransferDocumentReservedQuantity;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;


class PurchaseOrderDetail extends Model
{

    public $fillable = ['po_id','customer_id','order_product_id','order_id','product_id','quantity','pod_import_tax_book','pod_unit_price','pod_gross_weight','pod_total_gross_weight','pod_total_unit_price','pod_import_tax_book_price','warehouse_id','temperature_c','good_type','billed_unit_per_package','supplier_packaging','discount','billed_desc','is_billed','created_by','trasnfer_num_of_pieces','trasnfer_pcs_Domestic Vehicleped','trasnfer_qty_shipped','trasnfer_expiration_date','desired_qty','pkg_billed_est','currency_conversion_rate','custom_line_number','custom_invoice_number','last_updated_price_on','supplier_invoice_number','pod_vat_actual','pod_vat_actual_price','pod_unit_price_with_vat','pod_total_unit_price_with_vat','pod_vat_actual_total_price','unit_price_with_vat_in_thb','total_unit_price_with_vat_in_thb','pod_vat_actual_price_in_thb','pod_vat_actual_total_price_in_thb','quantity_received'];

    public function PurchaseOrder()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }

    public function customer()
    {
    	return $this->belongsTo('App\Models\Sales\Customer','customer_id','id');
    }

    public function get_td_reserved()
    {
      return $this->hasMany('App\TransferDocumentReservedQuantity', 'pod_id', 'id');
    }

    public function get_inbound_td_reserved()
    {
      return $this->hasMany('App\TransferDocumentReservedQuantity', 'inbound_pod_id', 'id');
    }

    public function getOrder()
    {
        return $this->belongsTo('App\Models\Common\Order\Order','order_id','id');
    }

    public function order_product()
    {
        return $this->belongsTo('App\Models\Common\Order\OrderProduct','order_product_id','id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Common\Product','product_id','id');
    }

    public function getWarehouse(){
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function getProductStock()
    {
      return $this->hasMany('App\Models\Common\WarehouseProduct', 'product_id', 'product_id');
    }

    public function order_products_notes()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProductNote', 'order_product_id', 'id');
    }

    public function pod_notes()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProductNote', 'pod_id', 'id');
    }

    public function pod_histories()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrdersHistory', 'pod_id', 'id');
    }
    public function pod_quantity_history()
    {
        return $this->hasOne('App\Models\Common\PurchaseOrders\PurchaseOrdersHistory', 'pod_id', 'id')->where(function($q){
            $q->where('column_name', 'Quantity')->orWhere('column_name', 'QTY Inv');
        })->orderBy('id', 'asc');
    }

    public function update_stock_card($pod, $new_value)
    {
        // $warehouse_product = WarehouseProduct::where('warehouse_id',$pod->PurchaseOrder->from_warehouse_id)->where('product_id',$pod->product_id)->first();
        //   if($warehouse_product != null)
        //   {
        //     $warehouse_product->reserved_quantity -= ($pod->quantity-$new_value);
        //     $warehouse_product->available_quantity = ($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity));
        //     $warehouse_product->save();
        //     return true;
        //   }

          $quantity = ($pod->quantity-$new_value);
          DB::beginTransaction();
          try
          {
            $new_his = new QuantityReservedHistory;
            $re      = $new_his->updateTDReservedQuantity($pod->PurchaseOrder,$pod,$quantity,$new_value,'Reserved Quantity Updated because of changing qty inside waiting TD','subtract');
            DB::commit();
          }
          catch(\Excepion $e)
          {
            DB::rollBack();
          }

          return true;
    }

    public function update_stock_card_for_completed_td($pod, $new_value,$field)
    {
        if($field == 'quantity_received')
        {
            $warehouse_id = $pod->PurchaseOrder->to_warehouse_id != null ? $pod->PurchaseOrder->to_warehouse_id : Auth::user()->get_warehouse->id;

            $warehouse_product = WarehouseProduct::where('warehouse_id',$warehouse_id)->where('product_id',$pod->product_id)->first();
            $quantity_diff =  $pod->quantity_received - $new_value;
            $quantity_diff = round($quantity_diff,3);
              if($warehouse_product != null)
              {
                $warehouse_product->current_quantity -= round($quantity_diff,3);
                $warehouse_product->available_quantity = ($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity));
                $warehouse_product->save();
                // return true;
              }


              // $warehouse_product = WarehouseProduct::where('warehouse_id',$pod->PurchaseOrder->to_warehouse_id)->where('product_id',$pod->product_id)->first();
              // $my_helper =  new MyHelper;
              // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_product);

              // $warehouse_product = WarehouseProduct::where('warehouse_id',$warehouse_id)->where('product_id',$pod->product_id)->first();
              // if($warehouse_product != null)
              // {
              //   // $warehouse_product->reserved_quantity -= ($pod->quantity-$new_value);
              //   $warehouse_product->current_quantity -= round($quantity_diff,3);
              //   $warehouse_product->available_quantity = ($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity));
              //   $warehouse_product->save();
              //   // return true;
              // }

              return true;
        }

        if($field == 'quantity_received_2')
        {
            $warehouse_id = $pod->PurchaseOrder->to_warehouse_id != null ? $pod->PurchaseOrder->to_warehouse_id : Auth::user()->get_warehouse->id;

            $warehouse_product = WarehouseProduct::where('warehouse_id',$warehouse_id)->where('product_id',$pod->product_id)->first();
            $quantity_diff =  $pod->quantity_received_2 - $new_value;
            $quantity_diff = round($quantity_diff,3);
              if($warehouse_product != null)
              {
                $warehouse_product->current_quantity -= round($quantity_diff,3);
                $warehouse_product->available_quantity = ($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity));
                $warehouse_product->save();
                // return true;
              }


              // $warehouse_product = WarehouseProduct::where('warehouse_id',$pod->PurchaseOrder->to_warehouse_id)->where('product_id',$pod->product_id)->first();
              // $my_helper =  new MyHelper;
              // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_product);

              // $warehouse_product = WarehouseProduct::where('warehouse_id',$pod->PurchaseOrder->to_warehouse_id)->where('product_id',$pod->product_id)->first();
              // if($warehouse_product != null)
              // {
              //   // $warehouse_product->reserved_quantity -= ($pod->quantity-$new_value);
              //   $warehouse_product->current_quantity -= round($quantity_diff,3);
              //   $warehouse_product->available_quantity = ($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity));
              //   $warehouse_product->save();
              //   // return true;
              // }


              return true;
        }
    }

    public function updatePurchaseOrderVatFromGroup($po_id, $new_vat, $group_id)
    {
        $po_group = PoGroup::find($group_id);
        $getPoToUpdate = PurchaseOrder::find($po_id);
        if($getPoToUpdate)
        {
            $getPoDetails  = PurchaseOrderDetail::where('po_id',$getPoToUpdate->id)->get();
            if($getPoDetails->count() > 0)
            {
                // $hidden_columns_by_admin = [];
                // $quotation_config   = QuotationConfig::where('section','purchase_order')->first();
                // $hide_columns = $quotation_config->show_columns;
                // $hidden_columns = json_decode($hide_columns);
                // $hidden_columns = implode (",", $hidden_columns);
                // $hidden_columns_by_admin = explode (",", $hidden_columns);
                // if(in_array(17,$hidden_columns_by_admin))
                // {
                //     $new_vat = null;
                // }

                foreach ($getPoDetails as $p_o_details)
                {
                    $old_val = $p_o_details->pod_vat_actual;
                    $p_o_details->pod_vat_actual = $new_vat;

                    /*vat calculations*/
                    $vat_calculations                     = $p_o_details->calculateVat($p_o_details->pod_unit_price, $new_vat);
                    $p_o_details->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                    $p_o_details->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                    /*convert val to thb's*/
                    $converted_vals                           = $p_o_details->calculateVatToSystemCurrency($po_id, $vat_calculations['vat_amount']);
                    $p_o_details->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
                    $p_o_details->save();

                    if($p_o_details->is_billed == 'Product')
                    {
                        $getProduct = Product::find($p_o_details->product_id);
                        /*taking history of vat update*/
                        $pod_history                   = new PurchaseOrdersHistory;
                        $pod_history->user_id          = Auth::user()->id;
                        $pod_history->column_name      = "VAT update from ".@$po_group->ref_id;
                        $pod_history->reference_number = ($getProduct != null ? $getProduct->refrence_code : '');
                        $pod_history->old_value        = $old_val;
                        $pod_history->new_value        = $new_vat;
                        $pod_history->po_id            = $po_id;
                        $pod_history->save();
                    }

                    /*calulation through a function*/
                    $objectCreated     = new PurchaseOrderDetail;
                    $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($po_id);
                }
            }
        }

        return true;
    }

    public function calculateVatToSystemCurrency($po_id, $amount)
    {
        $data = array();
        $order = PurchaseOrder::find($po_id);
        if($order->exchange_rate == null)
        {
            $supplier_conv_rate = $order->PoSupplier != null ? ($order->PoSupplier != null ? $order->PoSupplier->getCurrency->conversion_rate : 1) : 1;
        }
        else
        {
            $supplier_conv_rate = $order->exchange_rate;
        }

        $data['converted_amount'] = ($amount / $supplier_conv_rate);
        return $data;
    }

    public function calculateVat($unit_price, $vat)
    {
        $data = array();
        $vat_amount = $unit_price * ( $vat / 100 );
        $vat_amount = number_format($vat_amount,4,'.','');
        $pod_unit_price_with_vat = ($unit_price + $vat_amount);

        $data['vat_amount'] = $vat_amount;
        $data['pod_unit_price_with_vat'] = $pod_unit_price_with_vat;
        return $data;
    }

    public function calculateUnitPriceAfterDiscount($unit_price, $discount)
    {
        $data = array();
        $discount_amount = $unit_price * ( $discount / 100 );
        $discount_amount = number_format($discount_amount,4,'.','');
        $pod_unit_price_after_discount = ($unit_price - $discount_amount);

        $data['discount_amount'] = $discount_amount;
        $data['pod_unit_price_after_discount'] = $pod_unit_price_after_discount;
        return $data;
    }

    /*grand calculation on each event for Draft PO/TD*/
    public function grandCalculationForPurchaseOrder($po_id)
    {
        $data = array();
        $total_gross_weight            = 0;
        $sub_total                     = 0;
        $total_item_product_quantities = 0;
        $total_import_tax_book         = 0;
        $total_import_tax_book_price   = 0;
        $total_vat_actual              = 0;
        $total_vat_actual_price        = 0;
        $pod_total_vat_amount          = 0;
        $pod_total_unit_price_with_vat = 0;
        $total_vat_actual_price_in_thb = 0;

        $query     = PurchaseOrderDetail::where('po_id',$po_id)->get();
        foreach ($query as  $value)
        {
            /*Calculation of THB values Start*/
            $pod_unit_price  = $value->pod_unit_price;
            $unit_price_wd   = $value->quantity * $pod_unit_price - (($value->quantity * $pod_unit_price) * (@$value->discount / 100));
            $value->pod_total_unit_price = $unit_price_wd;

            $cal_pod_unit_price = $value->pod_unit_price;
            $to_cal_pod_unit_price_after_discount = $this->calculateUnitPriceAfterDiscount($cal_pod_unit_price, $value->discount);
            $value->pod_unit_price_after_discount = round($to_cal_pod_unit_price_after_discount['pod_unit_price_after_discount'], 2);

            $cal_pod_unit_price = $value->pod_unit_price;
            $to_cal_pod_unit_price_with_vat = $this->calculateVat($cal_pod_unit_price, $value->pod_vat_actual);
            $value->pod_unit_price_with_vat = $to_cal_pod_unit_price_with_vat['pod_unit_price_with_vat'];
            $value->save();
            if($value->pod_unit_price_with_vat != null)
            {
                $pod_unit_price_with_vat = $value->pod_unit_price_with_vat;
            }
            else
            {
                $value->pod_unit_price_with_vat = $value->pod_unit_price + ($value->pod_unit_price * ($value->pod_vat_actual/100));
                $value->save();
                $pod_unit_price_with_vat = $value->pod_unit_price_with_vat;
            }
            $total_unit_price_wv_wd  = $value->quantity * $pod_unit_price_with_vat - (($value->quantity * $pod_unit_price_with_vat) * (@$value->discount / 100));
            $value->pod_total_unit_price_with_vat = $total_unit_price_wv_wd;

            $to_cal_unit_price_in_thb = $this->calculateVatToSystemCurrency($po_id, $cal_pod_unit_price);
            $cal_unit_price_in_thb    = $to_cal_unit_price_in_thb['converted_amount'];
            $value->unit_price_in_thb = $cal_unit_price_in_thb;

            $to_cal_unit_price_with_vat_in_thb = $this->calculateVatToSystemCurrency($po_id, $value->pod_unit_price_with_vat);
            $cal_unit_price_with_vat_in_thb    = $to_cal_unit_price_with_vat_in_thb['converted_amount'];
            $value->unit_price_with_vat_in_thb = $cal_unit_price_with_vat_in_thb;

            $to_cal_total_unit_price_in_thb = $this->calculateVatToSystemCurrency($po_id, $value->pod_total_unit_price);
            $cal_total_unit_price_in_thb    = $to_cal_total_unit_price_in_thb['converted_amount'];
            $value->total_unit_price_in_thb = ($cal_total_unit_price_in_thb);

            $to_cal_total_unit_price_with_vat_in_thb = $this->calculateVatToSystemCurrency($po_id, $value->pod_total_unit_price_with_vat);
            $cal_total_unit_price_with_vat_in_thb    = $to_cal_total_unit_price_with_vat_in_thb['converted_amount'];
            $value->total_unit_price_with_vat_in_thb = ($cal_total_unit_price_with_vat_in_thb);

            $to_cal_pod_vat_actual_price_in_thb = $this->calculateVatToSystemCurrency($po_id, $value->pod_vat_actual_price);
            $cal_pod_vat_actual_price_in_thb    = $to_cal_pod_vat_actual_price_in_thb['converted_amount'];
            $value->pod_vat_actual_price_in_thb = $cal_pod_vat_actual_price_in_thb;

            $cal_pod_vat_actual_price          = $value->pod_vat_actual_price;
            $vat_actual_price_wd               = $value->quantity * $cal_pod_vat_actual_price - (($value->quantity * $cal_pod_vat_actual_price) * (@$value->discount / 100));
            $value->pod_vat_actual_total_price = ($vat_actual_price_wd);

            $to_cal_pod_vat_actual_total_price_in_thb = $this->calculateVatToSystemCurrency($po_id, $value->pod_vat_actual_total_price);
            $cal_pod_vat_actual_total_price_in_thb    = $to_cal_pod_vat_actual_total_price_in_thb['converted_amount'];
            $value->pod_vat_actual_total_price_in_thb = ($cal_pod_vat_actual_total_price_in_thb);

            $value->save();
            /*Calculation of THB values End*/

            $sub_total                     += $value->pod_total_unit_price;
            $pod_total_unit_price_with_vat += $value->pod_total_unit_price_with_vat;
            $total_gross_weight            = ($value->quantity * $value->pod_gross_weight) + $total_gross_weight;
            $total_item_product_quantities = $total_item_product_quantities + $value->quantity;
            $total_import_tax_book         = $total_import_tax_book + $value->pod_import_tax_book;
            $total_import_tax_book_price   = $total_import_tax_book_price + $value->pod_import_tax_book_price;
            $total_vat_actual              = $total_vat_actual + $value->pod_vat_actual;
            $total_vat_actual_price        = $total_vat_actual_price + $value->pod_vat_actual_price;
            $pod_total_vat_amount          = $pod_total_vat_amount + $value->pod_vat_actual_total_price;
            $total_vat_actual_price_in_thb = $total_vat_actual_price_in_thb + $value->pod_vat_actual_total_price_in_thb;
        }

        $po_totals_update                                = PurchaseOrder::find($po_id);
        $po_totals_update->total_gross_weight            = $total_gross_weight;
        $po_totals_update->total_quantity                = $total_item_product_quantities;
        $po_totals_update->total_import_tax_book         = $total_import_tax_book;
        $po_totals_update->total_import_tax_book_price   = $total_import_tax_book_price;
        $po_totals_update->total_vat_actual              = $total_vat_actual;
        $po_totals_update->total_vat_actual_price        = $total_vat_actual_price;
        $po_totals_update->total_vat_actual_price_in_thb = $total_vat_actual_price_in_thb;
        $po_totals_update->total                         = number_format($sub_total, 3, '.', '');

        /**/
        $to_cal_po_total_in_thb = $this->calculateVatToSystemCurrency($po_id, $sub_total);
        $po_totals_update->total_in_thb = number_format($to_cal_po_total_in_thb['converted_amount'], 3, '.', '');
        /**/

        $po_totals_update->vat_amount_total              = number_format($pod_total_vat_amount, 3, '.', '');
        $po_totals_update->total_with_vat                = number_format($pod_total_unit_price_with_vat, 3, '.', '');

        /**/
        $to_cal_po_total_with_vat_in_thb = $this->calculateVatToSystemCurrency($po_id, $pod_total_unit_price_with_vat);
        $po_totals_update->total_with_vat_in_thb = number_format($to_cal_po_total_with_vat_in_thb['converted_amount'], 3, '.', '');
        /**/

        $po_totals_update->save();

        $data['sub_total'] = $sub_total;
        $data['total_qty'] = $total_item_product_quantities;
        $data['vat_amout'] = $pod_total_vat_amount;
        $data['total_w_v'] = $pod_total_unit_price_with_vat;
        $data['total_gross_weight'] = $total_gross_weight;
        return $data;
    }
    public function supplier_product()
    {
        return $this->hasOne('App\Models\Common\SupplierProducts','product_id','product_id');
    }
    public function order_product_note()
    {
        return $this->hasOne('App\Models\Common\Order\OrderProductNote','order_product_id','order_product_id');
    }
    public function stock_management_out()
    {
        return $this->hasMany('App\Models\Common\StockManagementOut','p_o_d_id','id')->whereNotNull('quantity_out');
    }

    public function stock_management_in()
    {
        return $this->hasMany('App\Models\Common\StockManagementOut','p_o_d_id','id')->whereNotNull('quantity_in');
    }

    // calculate columns
    public function calColumns($updateRow)
    {
        $decimals = $updateRow->product != null ? ($updateRow->product->units != null ? $updateRow->product->units->decimal_places : 3) : 3;

        $data = array();
        //item level calculations
        $total_amount_wo_vat = number_format((float)$updateRow->pod_total_unit_price,3,'.','');
        $total_amount_w_vat = number_format((float)$updateRow->pod_total_unit_price_with_vat,3,'.','');

        $unit_price = $updateRow->pod_unit_price != null ?  number_format($updateRow->pod_unit_price,3,'.','') : '--';
        $unit_price_w_vat = $updateRow->pod_unit_price_with_vat != null ?  number_format($updateRow->pod_unit_price_with_vat,3,'.','') : '--';

        $unit_gross_weight = $updateRow->pod_gross_weight != null ?  number_format($updateRow->pod_gross_weight,3,'.','') : '--';
        $total_gross_weight = $updateRow->pod_total_gross_weight != null ?  number_format($updateRow->pod_total_gross_weight,3,'.','') : '--';

        $desired_qty = ($updateRow->desired_qty != null ? number_format($updateRow->desired_qty,$decimals,'.','') : "--" );
        $quantity = ($updateRow->quantity != null ? number_format($updateRow->quantity,$decimals,'.','') : "--" );

        $data['id'] = $updateRow->id;
        $data['unit_price'] = $unit_price;
        $data['unit_price_w_vat'] = $unit_price_w_vat;
        $data['total_amount_wo_vat'] = $total_amount_wo_vat;
        $data['total_amount_w_vat'] = $total_amount_w_vat;
        $data['unit_gross_weight'] = $unit_gross_weight;
        $data['total_gross_weight'] = $total_gross_weight;
        $data['desired_qty'] = $desired_qty;
        $data['quantity'] = $quantity;

        return $data;
    }

    public function getOnWater($ids)
    {
        $on_water = Product::join('purchase_order_details','products.id','=','purchase_order_details.product_id')
        ->join('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
        ->join('po_groups', 'po_groups.id', '=', 'purchase_orders.po_group_id')
        ->join('couriers', 'couriers.id', '=', 'po_groups.courier')
        // ->join('courier_types', 'courier_types.id', '=', 'couriers.courier_type_id')
        ->whereIn('purchase_order_details.product_id',$ids)
        ->where('purchase_orders.status', 14)
        // ->where('courier_types.type', 'Ship')
        ->sum('purchase_order_details.quantity');

        return $on_water;
    }

    public static function getOnSupplier($ids)
    {
        $on_supplier = Product::join('purchase_order_details','products.id','=','purchase_order_details.product_id')
        ->join('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
        ->whereIn('purchase_order_details.product_id',$ids)
        ->where('purchase_orders.status', 13)
        ->sum('purchase_order_details.quantity');

        return $on_supplier;
    }

    public function getOnAirplane($ids)
    {
        $on_airplane = Product::join('purchase_order_details','products.id','=','purchase_order_details.product_id')
        ->join('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
        ->join('po_groups', 'po_groups.id', '=', 'purchase_orders.po_group_id')
        ->join('couriers', 'couriers.id', '=', 'po_groups.courier')
        ->join('courier_types', 'courier_types.id', '=', 'couriers.courier_type_id')
        ->whereIn('purchase_order_details.product_id',$ids)
        ->where('purchase_orders.status', 14)
        ->where('courier_types.type', 'Airplane')
        ->sum('purchase_order_details.quantity');

        return $on_airplane;
    }

    public function getOnDomestic($ids)
    {
        $on_domestic = Product::join('purchase_order_details','products.id','=','purchase_order_details.product_id')
        ->join('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
        ->join('po_groups', 'po_groups.id', '=', 'purchase_orders.po_group_id')
        ->join('couriers', 'couriers.id', '=', 'po_groups.courier')
        ->join('courier_types', 'courier_types.id', '=', 'couriers.courier_type_id')
        ->whereIn('purchase_order_details.product_id',$ids)
        ->where('purchase_orders.status', 14)
        ->where('courier_types.type', 'Domestic Vehicle')
        ->sum('purchase_order_details.quantity');

        return $on_domestic;
    }

    public static function doSort($request, $query) {
        $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';

    if($request['column_name'] == "reference_no")
    {
        $query->select('purchase_order_details.*')->leftJoin('products','products.id', '=', 'purchase_order_details.product_id')->orderBy('products.refrence_code', $sort_order);
    } elseif($request['column_name'] == "description") {
        $query->select('purchase_order_details.*')->leftJoin('products','products.id', '=', 'purchase_order_details.product_id')->orderBy('products.short_desc', $sort_order);
    } elseif($request['column_name'] == "location") {
        $query->select('purchase_order_details.*')->leftJoin('purchase_orders','purchase_orders.id', '=', 'purchase_order_details.po_id')->leftJoin('warehouses','warehouses.id', '=', 'purchase_orders.from_warehouse_id')->orderBy('warehouses.location_code', $sort_order);
    } elseif($request['column_name'] == "unit_price") {
        $query->orderBy(\DB::Raw('pod_unit_price+0'), $sort_order);
    }  elseif($request['column_name'] == "quantity") {
        $query->orderBy(\DB::Raw('quantity+0'), $sort_order);
    }  elseif($request['column_name'] == "quantity_shipped") {
        $query->orderBy(\DB::Raw('trasnfer_qty_shipped+0'), $sort_order);
    }


      return $query->get();

    }

    public static function doSortby($request, $query)
    {
        if($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 1)
        {
            $sort_variable  = 'reference_name';
            $sort_order     = 'DESC';
        }
        elseif($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 2)
        {
            $sort_variable  = 'reference_name';
            $sort_order     = 'ASC';
        }
        if($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 1)
        {
            $sort_variable  = 'refrence_code';
            $sort_order     = 'DESC';
        }
        elseif($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 2)
        {
            $sort_variable  = 'refrence_code';
            $sort_order     = 'ASC';
        }
        if($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 1)
        {
            $sort_variable  = 'short_desc';
            $sort_order     = 'DESC';
        }
        elseif($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 2)
        {
            $sort_variable  = 'short_desc';
            $sort_order     = 'ASC';
        }
        if($request['sortbyparam'] == 1)
        {
            $query->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')->orderBy('suppliers.reference_name', $sort_order);
        }
        elseif($request['sortbyparam'] == 3)
        {
            $query->join('products','products.id','=','purchase_order_details.product_id')->orderBy('products.refrence_code',$sort_order);
        }
        elseif($request['sortbyparam'] == 4)
        {
            $query->join('products','products.id','=','purchase_order_details.product_id')->orderBy('products.short_desc',$sort_order);
        }
        elseif ($request['sortbyparam'] == 'confirm_date') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->select(\DB::raw('purchase_order_details.*'))
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->orderBy('purchase_orders.confirm_date', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'po_no') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('purchase_orders as po', 'po.id', '=', 'purchase_order_details.po_id')->orderBy('po.ref_id', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'type') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->join('types as t', 't.id', '=', 'p.type_id')->orderBy('t.title', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'type_2') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->join('product_secondary_types as t', 't.id', '=', 'p.type_id_2')->orderBy('t.title', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'type_3') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->join('product_type_tertiaries as t', 't.id', '=', 'p.type_id_3')->orderBy('t.title', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'billing_unit') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->join('units as u', 'u.id', '=', 'p.buying_unit')->orderBy('u.title', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'selling_unit') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->join('units as u', 'u.id', '=', 'p.selling_unit')->orderBy('u.title', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'minimum_stock') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->orderBy('p.min_stock', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'sum_of_qty') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->orderBy(\DB::raw('purchase_order_details.quantity+0'), $sort_order);
        }
        elseif ($request['sortbyparam'] == 'cost_price') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->orderBy('p.total_buy_unit_cost_price', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'product_cost') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->orderBy(\DB::raw('purchase_order_details.pod_unit_price+0'), $sort_order);
        }
        elseif ($request['sortbyparam'] == 'sum_pro_cost') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->orderBy(\DB::raw('purchase_order_details.pod_total_unit_price+0'), $sort_order);
        }
        elseif ($request['sortbyparam'] == 'vat') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->orderBy('p.vat', $sort_order);
        }
        elseif ($request['sortbyparam'] == 'custom_invoice_number') {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('purchase_orders as po', 'po.id', '=', 'purchase_order_details.po_id')->join('po_groups as pog', 'pog.id', '=', 'po.po_group_id')->orderBy('pog.custom_invoice_number', $sort_order);
        }
        elseif($request['sortbyparam'] == 'country')
        {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->join('countries', 'countries.id', '=', 'suppliers.country')
            ->orderBy('countries.name', $sort_order);
        }
        elseif($request['sortbyparam'] == 'category')
        {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')
            ->join('product_categories as pc', 'pc.id', '=', 'p.primary_category')
            ->orderBy('pc.title', $sort_order);
        }
        elseif($request['sortbyparam'] == 'avg_weight')
        {
            $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
            $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')
            ->orderBy('p.weight', $sort_order);
        }
        else
        {
            $query->select(\DB::raw('purchase_order_details.*'))
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->orderBy('purchase_orders.confirm_date', 'desc');
        }
        return $query;
    }

    public static function PurchasingReportGroupedSorting($request, $query)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        // if($request->sortbyparam == 1)
        // {
        //     $query->leftJoin('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
        //     ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')->orderBy('suppliers.reference_name', $sort_order);
        // }
        if($request['sortbyparam'] == 3)
        {
            $query->leftJoin('products','products.id','=','purchase_order_details.product_id')->orderBy('products.refrence_code',$sort_order);
        }
        elseif($request['sortbyparam'] == 4)
        {
            $query->leftJoin('products','products.id','=','purchase_order_details.product_id')->orderBy('products.short_desc',$sort_order);
        }
        elseif($request['sortbyparam'] == 'billing_unit')
        {
            $query->leftJoin('products as p','p.id','=','purchase_order_details.product_id')->leftJoin('units as u','u.id','=','p.buying_unit')->orderBy('u.title',$sort_order);
        }
        elseif($request['sortbyparam'] == 'selling_unit')
        {
            $query->leftJoin('products as p','p.id','=','purchase_order_details.product_id')->leftJoin('units as u','u.id','=','p.selling_unit')->orderBy('u.title',$sort_order);
        }
        elseif($request['sortbyparam'] == 'sum_qty')
        {
            $query->orderBy('TotalQuantity',$sort_order);
        }
        elseif($request['sortbyparam'] == 'product_cost')
        {
            $query->orderBy(\DB::raw('pod_unit_price+0'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'sum_pro_cost')
        {
            $query->orderBy('GrandTotalUnitPrice',$sort_order);
        }
        else
        {
            $query->orderBy('po.confirm_date', 'DESC');
        }
        return $query;
    }

    public static function PurchaseOrderDetailSorting($request, $query)
    {
        $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';

        if ($request['column_name'] == 1)
        {
            $query->leftJoin('products', 'products.id', '=', 'purchase_order_details.product_id')->orderBy('products.refrence_code', $sort_order);
        }
        elseif ($request['column_name'] == 'customer')
        {
            $query->leftJoin('customers', 'customers.id', '=', 'purchase_order_details.customer_id')->orderBy('customers.reference_name', $sort_order);
        }
        elseif ($request['column_name'] == 'brand')
        {
            $query->leftJoin('products', 'products.id', '=', 'purchase_order_details.product_id')->orderBy('products.brand', $sort_order);
        }
        elseif ($request['column_name'] == 'type')
        {
            $query->leftJoin('products', 'products.id', '=', 'purchase_order_details.product_id')->leftJoin('types', 'types.id', '=', 'products.type_id')->orderBy('types.title', $sort_order);
        }
        elseif ($request['column_name'] == 'selling_unit')
        {
            $query->leftJoin('products', 'products.id', '=', 'purchase_order_details.product_id')->leftJoin('units', 'units.id', '=', 'products.selling_unit')->orderBy('units.title', $sort_order);
        }
        elseif ($request['column_name'] == 'gross_weight')
        {
            $query->orderBy(\DB::raw('purchase_order_details.pod_gross_weight+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'order_qty')
        {
            $query->orderBy(\DB::raw('purchase_order_details.desired_qty+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'qty')
        {
            $query->leftJoin('order_products as op', 'op.id', '=', 'purchase_order_details.order_product_id')->orderBy(\DB::Raw('op.quantity+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'qty_inv')
        {
            $query->orderBy(\DB::raw('purchase_order_details.quantity+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'unit_price')
        {
            $query->orderBy(\DB::raw('purchase_order_details.pod_unit_price+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'purchasing_vat')
        {
            $query->orderBy(\DB::raw('purchase_order_details.pod_vat_actual+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'unit_price_w_vat')
        {
            $query->orderBy(\DB::raw('purchase_order_details.pod_unit_price_with_vat+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'price_date')
        {
            $query->orderBy('purchase_order_details.last_updated_price_on', $sort_order);
        }
        elseif ($request['column_name'] == 'discount')
        {
            $query->orderBy(\DB::Raw('purchase_order_details.discount+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'total_gross_weight')
        {
            $query->orderBy(\DB::Raw('purchase_order_details.pod_total_gross_weight+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'weight')
        {
            $query->leftJoin('products', 'products.id', '=', 'purchase_order_details.product_id')->orderBy(\DB::Raw('products.weight+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'order_no')
        {
            $query->leftJoin('orders', 'orders.id', '=', 'purchase_order_details.order_id')->orderBy('orders.full_inv_no', $sort_order);
        }
        else
        {
            $query->orderBy('id','ASC');
        }

        return $query;
    }

    public static function returnAddColumn($column, $item, $config) {
        switch ($column) {
            case 'weight':
                if($item->product_id != null)
                {
                    $html_string = '
                    <span class="m-l-15 " id="weight"  data-fieldvalue="'.@$item->product->weight.'">';
                    $html_string .= $item->product->weight != NULL ? $item->product->weight : "--";
                    $html_string .= '</span>';
                    return $html_string;
                }
                else
                {
                    return "N.A";
                }
                break;

            case 'unit_gross_weight':
                if($item->product_id != null)
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity pod_gross_weight unit_gross_weight_'.$item->id.'" data-id id="pod_gross_weight"  data-fieldvalue="'.@$item->pod_gross_weight.'">';
                    $html_string .= $item->pod_gross_weight != NULL ? number_format(@$item->pod_gross_weight, 3, '.', ',') : '--';
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="pod_gross_weight" class="PodGrossWeightFieldFocus d-none unit_gross_weight_field_'.$item->id.'" value="'.@$item->pod_gross_weight.'">';
                    return $html_string;
                }
                else
                {
                    return "N.A";
                }
                break;

            case 'discount':
                if($item->product_id != null)
                {
                    if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 13 || $item->PurchaseOrder->status == 14 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                    {
                        $html = '<span class="inputDoubleClick font-weight-bold discount_span_'.$item->id.'" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none discount_field_'.$item->id.'" style="width:85%"  maxlength="5" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">';
                        return $html.' %';
                    }
                    else
                    {
                        return $item->discount !== null ? $item->discount." %" : "--";
                    }
                }
                else
                {
                    return "N.A";
                }
                break;

            case 'remarks':
                if($item->order_product_id != null)
                {
                    $notes = $item->order_product->get_order_product_notes->count();
                    $product_note = ($notes > 0) ? $item->order_product->get_order_product_notes->first()->note : '';
                    $product_note = mb_substr($product_note, 0, 30, 'UTF-8');
                    if(Auth::user()->role_id != 7)
                    {
                    $html_string = '<div class="d-flex justify-content-center text-center">';
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->order_product_id.'" data-pod_id="" class="font-weight-bold note_'.$item->id.' show-notes mr-2 '.($notes > 0 ? "" : "d-none").'" title="View Notes">'.$product_note.' ...</a>';
                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->order_product_id.'" data-pod_id="" class="add-notes fa fa-plus mt-1" title="Add Note"></a>
                              </div>';
                    }
                    else
                    {
                        $html_string = "--";
                    }
                    return $html_string;
                }
                else
                {
                    $notes = $item->pod_notes->count();
                    $product_note = ($notes > 0) ? $item->pod_notes->first()->note : '';
                    $product_note = mb_substr($product_note, 0, 30, 'UTF-8');
                    if(Auth::user()->role_id != 7)
                    {
                        $html_string = '<div class="d-flex justify-content-center text-center">';
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="" data-pod_id="'.$item->id.'" class="font-weight-bold show-notes note_'.$item->id.' mr-2 '.($notes > 0 ? "" : "d-none").'" title="View Notes">'.$product_note.' ...</a>';
                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="" data-pod_id="'.$item->id.'"  class="add-notes fa fa-plus mt-1" title="Add Note"></a>
                              </div>';
                    }
                    else
                    {
                        $html_string = "--";
                    }
                    return $html_string;
                }
                break;

            case 'supplier_packaging':
                return $item->supplier_packaging !== null ? $item->supplier_packaging : '--';
                break;

            case 'order_no':
                if($item->order_id != null)
                {
                    if($item->getOrder->in_status_prefix !== null && $item->getOrder->in_ref_prefix !== null && $item->getOrder->in_ref_id !== null )
                    {
                        $ref_no = @$item->getOrder->in_status_prefix.'-'.$item->getOrder->in_ref_prefix.$item->getOrder->in_ref_id;
                    }
                    elseif($item->getOrder->status_prefix !== null && $item->getOrder->ref_prefix !== null && $item->getOrder->ref_id !== null )
                    {
                        $ref_no = @$item->getOrder->status_prefix.'-'.$item->getOrder->ref_prefix.$item->getOrder->ref_id;
                    }
                    else
                    {
                        $ref_no = @$item->getOrder->customer->primary_sale_person->get_warehouse->order_short_code.@$item->getOrder->customer->CustomerCategory->short_code.@$item->getOrder->ref_id;
                    }

                    if(@$item->getOrder->primary_status == 3)
                    {
                        $link = 'get-completed-invoices-details';
                    }
                    elseif(@$item->getOrder->primary_status == 17)
                    {
                        $link = 'get-cancelled-order-detail';
                    }
                    else
                    {
                        $link = 'get-completed-draft-invoices';
                    }
                        return $title = '<a target="_blank" href="'.route($link, ['id' => $item->getOrder->id]).'" title="View Detail" class=""><b>'.$ref_no .'</b></a>';
                }
                else
                {
                    return '--';
                }
                break;

            case 'amount_with_vat':
                $amount = $item->pod_unit_price_with_vat * $item->quantity;
                $amount = $amount - ($amount * (@$item->discount / 100));
                return $amount !== null ? '<span class="amount_with_vat_'.$item->id.'">'.number_format((float)$amount,3,'.',',').'</span>' : "--";
                break;

            case 'amount':
                $amount = $item->pod_unit_price * $item->quantity;
                $amount = $amount - ($amount * (@$item->discount / 100));
                return $amount !== null ? '<span class="amount_'.$item->id.'">'.number_format((float)$amount,3,'.',',').'</span>' : "--";
                break;

            case 'last_updated_price_on':
                if($item->last_updated_price_on!=null)
                {
                  return Carbon::parse($item->last_updated_price_on)->format('d/m/Y');
                }
                else
                {
                  return '--';
                }
                break;

            case 'unit_price_with_vat':
                $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';
                $supp_curren = $item->PurchaseOrder->supplier_id !== null ? @$item->PurchaseOrder->PoSupplier->getCurrency->currency_code : '';

                if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 13 || $item->PurchaseOrder->status == 14)
                {
                    $history = $item->pod_histories->where('column_name','Unit Price')->where('old_value','!=','')->where('old_value','!=','--')->first();
                    if($history !== null)
                    {
                        $style = 'color:red';
                    }
                    else
                    {
                        $style = '';
                    }

                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity pod_unit_price_with_vat unit_price_with_vat_span_'.$item->id.' mr-2" style="'.$style.'" data-id id="pod_unit_price_with_vat"  data-fieldvalue="'.number_format(@$item->pod_unit_price_with_vat, 3, '.', '').'">';
                    $html_string .= $item->pod_unit_price_with_vat !== null ? number_format(@$item->pod_unit_price_with_vat, 3, '.', ',') : "--" ;
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="pod_unit_price_with_vat" class="unitfieldFocus d-none form-control input-height unit_price_with_vat_field_'.$item->id.'" min="0" value="'.number_format(@$item->pod_unit_price_with_vat, 3, '.', '').'">';
                    $html_string .= $supp_curren.' / '.$billed_unit;
                    return $html_string;
                }
                else
                {
                    return $item->pod_unit_price_with_vat !== null ? number_format($item->pod_unit_price_with_vat,3,'.','').'<span class="ml-2">'.$billed_unit.'</span>' : "--".'<span class="ml-2">'.$supp_curren.' / '.$billed_unit.'</span>';
                }
                break;

            case 'unit_price':
                $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';
                $supp_curren = $item->PurchaseOrder->supplier_id !== null ? @$item->PurchaseOrder->PoSupplier->getCurrency->currency_code : '';

                if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 13 || $item->PurchaseOrder->status == 14 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                {
                    $history = $item->pod_histories->where('column_name','Unit Price')->where('old_value','!=','')->where('old_value','!=','--')->first();
                    // dd($history);
                    if($history !== null)
                    {
                        $style = 'color:red';
                    }
                    else
                    {
                        $style = '';
                    }

                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity unit_price unit_price_span_'.$item->id.' mr-2" style="'.$style.'" data-id id="unit_price"  data-fieldvalue="'.number_format(@$item->pod_unit_price, 3, '.', '').'">';
                    $html_string .= $item->pod_unit_price !== null ? number_format(@$item->pod_unit_price, 3, '.', ',') : "--" ;
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="unit_price" class="unitfieldFocus d-none form-control input-height unit_price_field_'.$item->id.'" min="0" value="'.number_format(@$item->pod_unit_price, 3, '.', '').'">';
                    $html_string .= $supp_curren.' / '.$billed_unit;
                    return $html_string;
                }
                else
                {
                    return $item->pod_unit_price !== null ? number_format($item->pod_unit_price,3,'.','').'<span class="ml-2">'.$billed_unit.'</span>' : "--".'<span class="ml-2">'.$supp_curren.' / '.$billed_unit.'</span>';
                }
                break;
            case 'unit_price_after_discount':
                $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';
                $supp_curren = $item->PurchaseOrder->supplier_id !== null ? @$item->PurchaseOrder->PoSupplier->getCurrency->currency_code : '';

                if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 13 || $item->PurchaseOrder->status == 14 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                {
                    $history = $item->pod_histories->where('column_name','Unit Price After Discount')->where('old_value','!=','')->where('old_value','!=','--')->first();
                    // dd($history);
                    if($history !== null)
                    {
                        $style = 'color:red';
                    }
                    else
                    {
                        $style = '';
                    }

                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity unit_price_after_discount unit_price_after_discount_span_'.$item->id.' mr-2" style="'.$style.'" data-id id="unit_price_after_discount"  data-fieldvalue="'.number_format(@$item->pod_unit_price_after_discount, 3, '.', '').'">';
                    $html_string .= $item->pod_unit_price_after_discount !== null ? number_format(@$item->pod_unit_price_after_discount, 3, '.', ',') : "--" ;
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="unit_price_after_discount" class="unitfieldFocus d-none form-control input-height unit_price_after_discount_field_'.$item->id.'" min="0" value="'.number_format(@$item->pod_unit_price_after_discount, 3, '.', '').'">';
                    $html_string .= $supp_curren.' / '.$billed_unit;
                    return $html_string;
                }
                else
                {
                    return $item->pod_unit_price_after_discount !== null ? number_format($item->pod_unit_price_after_discount,3,'.','').'<span class="ml-2">'.$billed_unit.'</span>' : "--".'<span class="ml-2">'.$supp_curren.' / '.$billed_unit.'</span>';
                }
                break;

            case 'billed_unit_per_package':
                if($item->product_id != null)
                {
                    if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                    {
                        $html_string = '
                        <span class="m-l-15 inputDoubleClickQuantity billed_unit_per_package" data-id id="billed_unit_per_package"  data-fieldvalue="'.($item->billed_unit_per_package !== null ? number_format((float)$item->billed_unit_per_package, 3, '.', ',') : "").'">';
                        $html_string .= $item->billed_unit_per_package !== null ? number_format((float)$item->billed_unit_per_package, 3, '.', ',') : "--" ;
                        $html_string .= '</span>';
                        $html_string .= '<input type="number" style="width:100%;" name="billed_unit_per_package" class="unitfieldFocus d-none" min="0" value="'.($item->billed_unit_per_package !== null ? number_format((float)$item->billed_unit_per_package, 3, '.', ',') : "").'">';
                        return $html_string;
                    }
                    else
                    {
                        return $item->billed_unit_per_package !== null ? $item->billed_unit_per_package : "--";
                    }
                }
                else
                {
                    return "N.A";
                }
                break;

            case 'purchasing_vat':
                if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 13 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity quantity" data-id id="pod_vat_actual"  data-fieldvalue="'.number_format($item->pod_vat_actual,3,'.','').'">';
                    $html_string .= ($item->pod_vat_actual != null ? number_format($item->pod_vat_actual,3,'.','') : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;border-radius: 0px;padding:0px;" name="pod_vat_actual" class="fieldFocusQuantity d-none form-control input-height" min="0" value="'.number_format($item->pod_vat_actual,3,'.','').'">';
                }
                else
                {
                    $html_string = '<span>'.($item->pod_vat_actual != null ? number_format($item->pod_vat_actual,3,'.','') : "--" ).'</span>';
                }
                    return $html_string;
                break;

            case 'desired_qty':
                $supplier_packaging = $item->supplier_packaging !== null ? $item->supplier_packaging : 'N.A';
                $decimals = $item->product != null ? ($item->product->units != null ? $item->product->units->decimal_places : 0) : 0;
                if($item->product_id != null )
                {
                    if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                    {
                        $html_string = '
                        <span class="m-l-15 inputDoubleClickQuantity desired_qty desired_qty_span_'.$item->id.' mr-2" data-id id="desired_qty"  data-fieldvalue="'.number_format(@$item->desired_qty, $decimals, '.', ',').'">';
                        $html_string .= $item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', ',') : "--" ;
                        $html_string .= '</span>';
                        $html_string .= '<input type="number" style="width:100%;" name="desired_qty" class="unitfieldFocus d-none form-control input-height desired_qty_field_'.$item->id.'" min="0" value="'.number_format(@$item->desired_qty, $decimals, '.', ',').'">';
                        $html_string .= $supplier_packaging;
                        return $html_string;

                    }
                    else
                    {
                        return $item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', ',').'<span class="ml-2">'.$supplier_packaging.'</span>' : "--".'<span class="ml-2">'.$supplier_packaging.'</span>';
                    }
                }
                else
                {
                    return "N.A";
                }
                break;

            case 'gross_weight':
                return $item->pod_total_gross_weight != NULL ? '<span class="total_gross_weight_'.$item->id.'">'.number_format((float)$item->pod_total_gross_weight,3,'.','').'</span>' : '<span class="total_gross_weight_'.$item->id.'"> N.A </span>';
                break;

            case 'customer_pcs':
                if($item->order_product_id != null)
                {
                    $html_string = '<span class="m-l-15 customer_pcs">';
                    $html_string .= ($item->order_product_id != null ? ($item->order_product->number_of_pieces != null ? $item->order_product->number_of_pieces : "--") : "--");
                    $html_string .= '</span>';
                    return $html_string;
                }
                else
                {
                    return "Stock";
                }
                break;

            case 'customer_qty':
                if($item->order_product_id != null)
                {
                    $selling_unit = ($item->order_product_id != null ? $item->order_product->product->sellingUnits->title : "N.A");
                    $html_string = '<span class="m-l-15 customer_qty">';
                    $html_string .= ($item->order_product_id != null ? ($item->order_product->quantity != null ? $item->order_product->quantity : "--").' '.$selling_unit : "--");
                    $html_string .= '</span>';
                    return $html_string;
                }
                else if(@$item->pod_quantity_history != null && @$config->server == 'lucilla'){
                    $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';
                    return @$item->pod_quantity_history->new_value.' '.@$billed_unit;
                }
                else
                {
                    return "Stock";
                }
                break;

            case 'quantity':
                $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';
                $decimals = $item->product != null ? ($item->product->units != null ? $item->product->units->decimal_places : 0) : 0;
                if($item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 14 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                {
                    $history = $item->pod_histories->where('column_name','Quantity')->first();
                    if($history !== null)
                    {
                        $style = 'color:red';
                    }
                    else
                    {
                        $style = '';
                    }

                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity quantity quantity_span_'.$item->id.'" style="'.$style.'" data-id id="quantity"  data-fieldvalue="'.number_format($item->quantity,3,'.','').'">';
                    $html_string .= ($item->quantity != null ? number_format($item->quantity,$decimals,'.','') : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;border-radius: 0px;padding:0px;" name="quantity" class="fieldFocusQuantity d-none form-control input-height quantity_field_'.$item->id.'" min="0" value="'.number_format($item->quantity,$decimals,'.','').'">';
                    $html_string .= ' '.$billed_unit;
                    return $html_string;
                }
                else
                {
                    return $item->quantity !== null ? number_format($item->quantity,3,'.','').'<span class="ml-2">'.$billed_unit.'</span>' : "--".'<span class="ml-2">'.$billed_unit.'</span>';
                }
                break;

            case 'warehouse':
                return $item->warehouse_id !== null ? @$item->getWarehouse->warehouse_title : '--';
                break;

            case 'buying_unit':
                return $item->product_id !== null ? @$item->product->units->title : 'N.A';
                break;

            case 'type':
                return $item->product_id != null ? @$item->product->productType->title : '--';
                break;

            case 'leading_time':
                if($item->PurchaseOrder->supplier_id != null)
                {
                    if($item->product_id != null)
                    {
                        $gettingProdSuppData = $item->product->supplier_products->where('supplier_id',$item->PurchaseOrder->supplier_id)->first();
                        $leading_time = @$gettingProdSuppData->leading_time !== null ? $gettingProdSuppData->leading_time : "--";
                        return  $leading_time;
                    }
                    else
                    {
                        return  $html_string1 = 'N.A';
                    }
                } else {
                    return '--';
                }
                break;

            case 'brand':
                if($item->product_id != null)
                {
                    return ($item->product->brand !== null ? $item->product->brand : '--');
                }
                else
                {
                    return '--';
                }
                break;

            case 'product_description':
                if($item->product_id != null)
                {
                    return  $item->product->short_desc;
                }
                else
                {
                    $style = $item->billed_desc == null ? "color:red;" : "";
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity billed_desc" style="'.$style.'" data-id id="billed_desc"  data-fieldvalue="'.@$item->billed_desc.'">';
                    $html_string .= ($item->billed_desc != null ? $item->billed_desc : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="billed_desc" class="fieldFocusQuantity d-none" value="'.$item->billed_desc .'">';
                    return $html_string;
                }
                break;

            case 'customer':
                if(@$item->customer_id !== null)
                {
                    return  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.$item->customer->id).'"  ><b>'.@$item->customer->reference_name.'</b></a>';
                }
                else
                {
                    return $html_string = 'Stock';
                }
                break;

            case 'item_ref':
                if($item->product_id != null)
                {
                    $ref_no = $item->product->refrence_code;
                    return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$ref_no.'</b></a>';
                }
                else
                {
                    return  $html_string = '--';
                }
                break;

            case 'supplier_id':
                if($item->PurchaseOrder->supplier_id != null)
                {
                    if($item->product_id != null)
                    {
                        $gettingProdSuppData = $item->product->supplier_products->where('supplier_id',$item->PurchaseOrder->supplier_id)->first();
                        if($gettingProdSuppData)
                        {
                            $ref_no1 = $gettingProdSuppData->product_supplier_reference_no !== null ? $gettingProdSuppData->product_supplier_reference_no : "--";
                            if($ref_no1 != '--')
                            {
                                return  $html_string1 = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$ref_no1.'</b></a>';
                            }
                            else
                            {
                                return  $ref_no1;
                            }
                        }
                        else
                        {
                            return 'N.A';
                        }
                    }
                    else
                    {
                        return  $html_string1 = 'N.A';
                    }
                } else {
                    return '--';
                }
                break;

            case 'action':
                if($item->order_id != NULL && $item->PurchaseOrder->status == 12 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                {
                    $html_string = '
                    <a href="javascript:void(0);" class="actionicon editIcon delete-product-from-list" data-order_id="' . $item->order_id . '" data-order_product_id="'. $item->order_product_id .'" data-po_id ="'. $item->po_id .'" data-id="'.$item->id.'" title="Revert To Purchase List"><i class="fa fa-undo"></i></a>';
                }
                elseif($item->order_id == NULL && $item->PurchaseOrder->status == 12 && Auth::user()->role_id != 7 || $item->PurchaseOrder->status == 26 || $item->PurchaseOrder->status == 27 || $item->PurchaseOrder->status == 29 || $item->PurchaseOrder->status == 30)
                {
                    $html_string = '
                    <a href="javascript:void(0);" class="actionicon deleteIcon delete-item-from-list" data-po_id ="'. $item->po_id .'" data-id="'.$item->id.'" title="Delete Item From List"><i class="fa fa-trash"></i></a>';
                }
                else
                {
                    $html_string = 'N.A';
                }
                return $html_string;
                break;
            case 'current_stock_qty':
                if(@$config->server == 'lucilla'){
                        return $item->getProductStock != null ? round(@$item->getProductStock->sum('current_quantity'),3) : '--';
                    }else{
                        return $item->getProductStock != null ? round(@$item->getProductStock->where('warehouse_id', @$item->PurchaseOrder->to_warehouse_id)->first()->current_quantity,3) : '--';
                    }
                break;

                
        }
    }

    public static function returnEditColumn($column, $item) {
        switch ($column) {
            case 'short_desc':
                if($item->product_id != null)
                {
                    if($item->PurchaseOrder->supplier_id != null)
                    {
                       $supplier_id = $item->PurchaseOrder->supplier_id;
                       $getDescription = $item->product->supplier_products->where('supplier_id',$supplier_id)->first();
                        return @$getDescription->supplier_description != null ? $getDescription->supplier_description : ($item->product->short_desc != null ? $item->product->short_desc : "--") ;
                    }
                    else
                    {
                        $supplier_id = $item->product->supplier_id;
                        return $item->product->short_desc != null ? $item->product->short_desc : "--" ;
                    }
                } else {
                    $style = $item->billed_desc == null ? "color:red;" : "";
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity billed_desc" style="'.$style.'" data-id id="billed_desc"  data-fieldvalue="'.@$item->billed_desc.'">';
                    $html_string .= ($item->billed_desc != null ? $item->billed_desc : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="billed_desc" class="fieldFocusQuantity d-none" value="'.$item->billed_desc .'">';
                    return $html_string;
                }
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'supplier_id':
                $item = $item->whereIn('product_id', SupplierProducts::select('product_id')->where('product_supplier_reference_no','LIKE',"%$keyword%")->pluck('product_id'));
                break;

            case 'item_ref':
                $item = $item->whereIn('product_id', Product::select('id')->where('status',1)->where('refrence_code','LIKE',"%$keyword%")->pluck('id'));
                break;

            case 'customer':
                $item = $item->whereIn('customer_id', Customer::select('id')->where('reference_name','LIKE',"%$keyword%")->pluck('id'));
                break;

            case 'product_description':
                $item = $item->whereIn('product_id', Product::select('id')->where('status',1)->where('short_desc','LIKE',"%$keyword%")->pluck('id'));
                break;

            case 'short_desc':
                $item = $item->whereIn('product_id', SupplierProducts::select('id')->where('supplier_description','LIKE',"%$keyword%")->pluck('id'));
                break;
        }
    }


    public static function returnAddColumnPurchasingReportDetail($column, $item) {
        $po_group_product_detail = PoGroupProductDetail::where('status', 1)->where('po_group_id', @$item->PurchaseOrder->po_group_id)->where('supplier_id', @$item->PurchaseOrder->supplier_id)->where('product_id', $item->product_id)->first();
        switch ($column) {
            case 'custom_line_number':
                $po_group = $item->PurchaseOrder->po_group_id !== null ? $item->PurchaseOrder->po_group : null;
                if($po_group != null)
                {
                    return $po_group->po_group_product_details->where('product_id',$item->product_id)->pluck('custom_line_number')->first() !== null ? $po_group->po_group_product_details->where('product_id',$item->product_id)->pluck('custom_line_number')->first() : '--';
                }
                else
                {
                    return '--';
                }
                break;

            case 'custom_invoice_number':
                $po_group = $item->PurchaseOrder->po_group_id !== null ? $item->PurchaseOrder->po_group : null;
                if($po_group != null)
                {
                    return $item->custom_invoice_number != null ? $po_group->custom_invoice_number : '--';
                }
                else
                {
                    return '--';
                }
                break;

            case 'seller_price':
                return $item->product_id !== null ? ($item->product->total_buy_unit_cost_price !== null ? number_format((float) $item->product->total_buy_unit_cost_price, 3, '.', ',') : '--') : 'N.A';
                break;

            case 'import_tax_actual':
                if($po_group_product_detail){
                    return round(@$po_group_product_detail->actual_tax_percent,4) ?? 0;
                }
                return '--';
                break;

            case 'landing':
                if($po_group_product_detail){
                    return round(@$po_group_product_detail->landing,4) ?? 0;
                }
                return '--';
                break;

            case 'freight':
            // dd($po_group_product_detail);
                if($po_group_product_detail){
                    return round(@$po_group_product_detail->freight,4) ?? 0;
                }
                return '--';
                break;
            case 'vat':
                // return $item->product->vat !== null ? $item->product->vat.' %' : '0 %';
                return $item->pod_vat_actual !== null ? $item->pod_vat_actual.' %' : '--';
                break;

            case 'sum_cost_amount':
                $sum_cost_amt = ($item->unit_price_in_thb * $item->quantity);
                return number_format($sum_cost_amt,3,'.',',');
                break;

            case 'cost_unit_thb':
                $cost_unit_thb = $item->unit_price_in_thb + ($item->pod_freight + $item->pod_landing + $item->pod_total_extra_cost);
                return number_format($cost_unit_thb,3,'.',',');
                break;

            case 'total_cost':
                return $item->pod_total_unit_price !== null ? number_format($item->pod_total_unit_price,3,'.',',') : 'N.A';
                break;

            case 'cost_unit':
                return $item->pod_unit_price !== null ? number_format($item->pod_unit_price,3,'.',',') : 'N.A';
                break;

            case 'sum_qty':
                return $item->quantity !== null ? $item->quantity : 'N.A';
                break;

            case 'unit':
                return $item->product_id !== null ? $item->product->sellingUnits->title : 'N.A';
                break;

            case 'buying_unit':
                return $item->product_id !== null ? $item->product->units->title : 'N.A';
                break;

            case 'short_desc':
                return $item->product_id !== null ? $item->product->short_desc : 'N.A';
                break;
            case 'category':
                return @$item->product->productCategory !== null ? @$item->product->productCategory->title : 'N.A';
                break;
            case 'avg_weight':
                return @$item->product !== null ? @$item->product->weight?? '--' : 'N.A';
                break;

            case 'product_type_2':
                return @$item->product->productType2 !== null ? @$item->product->productType2->title : 'N.A';
                break;

            case 'product_type_3':
                return @$item->product->productType3 !== null ? @$item->product->productType3->title : 'N.A';
                break;

            case 'product_type':
                return @$item->product->productType !== null ? @$item->product->productType->title : 'N.A';
                break;

            case 'refrence_code':
                $refrence_code = $item->product_id != null ? $item->product->refrence_code : "N.A";
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'" ><b>'.$refrence_code.'</b></a>';
                break;

            case 'confirm_date':
                return $item->PurchaseOrder->confirm_date !== null ? Carbon::parse($item->PurchaseOrder->confirm_date)->format('d/m/Y') : 'N.A';
                break;

            case 'supplier':
                return $item->po_id !== null && $item->PurchaseOrder->PoSupplier !== null ? $item->PurchaseOrder->PoSupplier->reference_name : 'N.A';
                break;
            case 'country':
                return @$item->PurchaseOrder->PoSupplier->getcountry !== null ? $item->PurchaseOrder->PoSupplier->getcountry->name : 'N.A';
                break;

            case 'ref_id':
                $refrence_code = $item->po_id !== null ? $item->PurchaseOrder->ref_id : "N.A";
                return  $html_string = '<a target="_blank" href="'.url('get-purchase-order-detail/'.$item->po_id).'" ><b>'.$refrence_code.'</b></a>';
                break;
            case 'minimum_stock':
                $minimum_stock = $item->product->min_stock !== null ? $item->product->min_stock : "--";
                return $minimum_stock;
                break;
            case 'supplier_invoice':
                return $item->PurchaseOrder->invoice_number !== null ? $item->PurchaseOrder->invoice_number : "--";
                break;
            case 'supplier_invoice_date':
                return $item->PurchaseOrder->invoice_date !== null ? Carbon::parse($item->PurchaseOrder->invoice_date)->format('d/m/Y') : "--";
                break;
            case 'vat_amount_euro':
                return $item->pod_vat_actual_price !== null ? number_format($item->pod_vat_actual_price,3,'.',',') : "--";
                break;
            case 'vat_amount_thb':
                return $item->pod_vat_actual_price_in_thb !== null ? number_format($item->pod_vat_actual_price_in_thb,3,'.',',') : "--";
                break;
            case 'unit_price_before_vat_euro':
                return $item->pod_unit_price !== null ? number_format($item->pod_unit_price,3,'.',',') : "--";
                break;
            case 'unit_price_before_vat_thb':
                return $item->unit_price_in_thb !== null ? number_format($item->unit_price_in_thb,3,'.',',') : "--";
                break;
            case 'unit_price_after_vat_euro':
                return $item->pod_unit_price_with_vat !== null ? number_format($item->pod_unit_price_with_vat,3,'.',',') : "--";
                break;
            case 'unit_price_after_vat_thb':
                return $item->unit_price_with_vat_in_thb !== null ? number_format($item->unit_price_with_vat_in_thb,3,'.',',') : "--";
                break;
            case 'discount_percent':
                return $item->discount !== null ? number_format($item->discount,3,'.',',') : "--";
                break;
            case 'sub_total_euro':
                return $item->pod_total_unit_price !== null ? number_format($item->pod_total_unit_price,3,'.',',') : "--";
                break;
            case 'sub_total_thb':
                return $item->total_unit_price_in_thb !== null ? number_format($item->total_unit_price_in_thb,3,'.',',') : "--";
                break;
            case 'total_amount_sfter_vat_euro':
                return $item->pod_total_unit_price_with_vat !== null ? number_format($item->pod_total_unit_price_with_vat,3,'.',',') : "--";
                break;
            case 'total_amount_sfter_vat_thb':
                return $item->total_unit_price_with_vat_in_thb !== null ? number_format($item->total_unit_price_with_vat_in_thb,3,'.',',') : "--";
                break;
            case 'conversion_rate':
                return $item->product->unit_conversion_rate != null ? number_format($item->product->unit_conversion_rate,3,'.',',') : "--";
                break;
            case 'qty_into_stock':
                $u_c_r = $item->product->unit_conversion_rate == 0 ? 1 : $item->product->unit_conversion_rate;
				return number_format($item->quantity/$u_c_r,3,'.',',');
                break;
        }
    }

    public static function returnFilterColumnPurchasingReportDetail($column, $item, $keyword) {
        switch ($column) {
            case 'refrence_code':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.refrence_code','LIKE', "%$keyword%");
                });
                break;

            case 'ref_id':
                $item->whereHas('PurchaseOrder', function($q) use($keyword){
                    $q->where('purchase_orders.ref_id','LIKE', "%$keyword%");
                });
                break;

            case 'short_desc':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.short_desc','LIKE', "%$keyword%");
                });
                break;
            case 'minimum_stock':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.min_stock','LIKE', "%$keyword%");
                });
                break;

        }
    }

    public static function returnAddColumnPurchasingReportGrouped($column, $item, $category_id, $supplier_id, $filter_value, $from_date, $to_date) {
        switch ($column) {
            case 'total_cost':
                return $item->GrandTotalUnitPrice !== null ? number_format($item->GrandTotalUnitPrice,3,'.',',') : 'N.A';
                break;

            case 'cost_unit':
                return $item->pod_unit_price !== null ? number_format($item->pod_unit_price,3,'.',',') : 'N.A';
                break;

            case 'sum_qty':
                return $item->TotalQuantity !== null ? $item->TotalQuantity : 'N.A';
                break;

            case 'unit':
                return $item->product_id !== null ? $item->product->sellingUnits->title : 'N.A';
                break;

            case 'buying_unit':
                return $item->product_id !== null ? $item->product->units->title : 'N.A';
                break;

            case 'short_desc':
                return $item->product_id !== null ? $item->product->short_desc : 'N.A';
                break;

            case 'refrence_code':
                $refrence_code = $item->product_id != null ? $item->product->refrence_code : "N.A";
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'" ><b>'.$refrence_code.'</b></a</a>';
                break;

            case 'action':
                $category_id  == '' ? $category_id  = 'NA' : '';
                $supplier_id  == '' ? $supplier_id  = 'NA' : '';
                $filter_value == '' ? $filter_value = 'NA' : '';
                $from_date    == '' ? $from_date    = 'NoDate' : '';
                $to_date      == '' ? $to_date      = 'NoDate' : '';

                $request_array = '';
                $request_array .= $category_id;
                $request_array .= ','.$supplier_id;
                $request_array .= ','.$filter_value;
                $request_array .= ','.$from_date;
                $request_array .= ','.$to_date;
                $request_array .= ','.$item->product_id;

                $type = 'group';

                $html_string = '<a target="_blank" href="'.url('purchasing-report/'.$request_array.'/'.$type).'" class="actionicon" style="cursor:pointer" title="View history" data-id='.$item->product_id.'><i class="fa fa-history"></i></a>';
                return $html_string;
                break;
        }
    }

    public static function returnFilterColumnPurchasingReportGrouped($column, $item, $keyword) {
        switch ($column) {
            case 'refrence_code':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.refrence_code','LIKE', "%$keyword%");
                });
                break;

            case 'short_desc':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.short_desc','LIKE', "%$keyword%");
                });
                break;
        }
    }

    public static function reserveQtyForTD($quantity_out, $stock_m_out, $pod){
        //Checking if stock against that pod_id already exists then retrun null
        $transferReserveQtyCheck = TransferDocumentReservedQuantity::where('pod_id', $pod->id)->where('stock_id', $stock_m_out->id)->first();
        if ($transferReserveQtyCheck) {
            return null;
        }
        //if can be filled completely
        if($stock_m_out->available_stock >= $quantity_out){
            $transferReserveQty = new TransferDocumentReservedQuantity();
            $transferReserveQty->stock_id = $stock_m_out->id;
            $transferReserveQty->po_id = $pod->po_id;
            $transferReserveQty->pod_id = $pod->id;
            $transferReserveQty->reserved_quantity = $quantity_out;
            $transferReserveQty->save();
            $stock_m_out->available_stock -= $quantity_out;
            $stock_m_out->save();
            return 0;
        }
        //if can't be filled completely
        else if($stock_m_out->available_stock < $quantity_out){
            $remaining = $quantity_out - $stock_m_out->available_stock;
            $transferReserveQty = new TransferDocumentReservedQuantity();
            $transferReserveQty->stock_id = $stock_m_out->id;
            $transferReserveQty->po_id = $pod->po_id;
            $transferReserveQty->pod_id = $pod->id;
            $transferReserveQty->reserved_quantity = $stock_m_out->available_stock;
            $transferReserveQty->save();
            $stock_m_out->available_stock = 0;
            $stock_m_out->save();
            return $remaining;
        }
    }

    public static function reserveQtyForTDWithoutStock($quantity, $pod){
        $transferReserveQty = new TransferDocumentReservedQuantity();
        $transferReserveQty->stock_id = null;
        $transferReserveQty->po_id = $pod->po_id;
        $transferReserveQty->pod_id = $pod->id;
        $transferReserveQty->reserved_quantity = $quantity;
        $transferReserveQty->save();
        return 0;
    }


}
