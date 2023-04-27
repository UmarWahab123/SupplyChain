<?php
namespace App\Helpers;
use DB;
use Auth;
use App\User;
use DateTime;
use Carbon\Carbon;
use App\ExportStatus;
use App\ProductHistory;
use App\Models\Common\Status;
use App\Models\Common\PoGroup;
use App\Models\Common\Product;
use App\PoGroupProductHistory;
use App\DraftPurchaseOrderHistory;
use App\Models\Common\Order\Order;
use App\Models\Common\PaymentTerm;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\Jobs\ProductsReceivingImportJob;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\TransferDocumentReservedQuantity;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;


/**
 *
 */
class PODetailCRUDHelper
{
	public static function addProdByRefrenceNumberInPoDetail($request)
	{
		$order = PurchaseOrder::find($request->po_id['po_id']);
        $unit_price = 0;

        $product = Product::where('refrence_code',$request->refrence_number)->where('status',1)->first();
        $add_products_to_po_detail = null;
        if($product)
        {
            if($order->supplier_id != NULL && $order->from_warehouse_id == NULL)
            {
                $supplier_products = SupplierProducts::where('product_id',$product->id)->where('is_deleted',0)->where('supplier_id',$order->supplier_id)->count();
                if($supplier_products == 0)
                {
                    return response()->json(['success' => false, 'successmsg' => $order->PoSupplier->reference_name.' do not provide us '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                }

                $add_products_to_po_detail = new PurchaseOrderDetail;

                $add_products_to_po_detail->pod_import_tax_book  = $product->import_tax_book;
                $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $product->vat : null;

                $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$order->supplier_id)->first();

                $add_products_to_po_detail->pod_unit_price          = $gettingProdSuppData->buying_price;
                $add_products_to_po_detail->last_updated_price_on   = $product->last_price_updated_date;
                $add_products_to_po_detail->pod_gross_weight        = $gettingProdSuppData->gross_weight;
                $add_products_to_po_detail->good_type               = $product->type_id;
                $add_products_to_po_detail->temperature_c           = $product->product_temprature_c;
                $add_products_to_po_detail->po_id                   = $request->po_id['po_id'];
                $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;
                $add_products_to_po_detail->product_id              = $product->id;

                /*vat calculations*/
                $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price, $request->purchasing_vat == null ? $product->vat : null);
                $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                /*convert val to thb's*/
                $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->po_id['po_id'], $vat_calculations['vat_amount']);
                $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
                if ($order->status == 14) {
                    $add_products_to_po_detail->new_item_status = 1;
                }
                $add_products_to_po_detail->save();

                (new PODetailCRUDHelper)->MakeHistory($request->po_id['po_id'], null, '', null, $product->refrence_code, '', 'New Item');

            }
            if($order->from_warehouse_id != NULL && $order->supplier_id == NULL)
            {
                $warehouse_products = WarehouseProduct::where('product_id',$product->id)->where('warehouse_id',$order->from_warehouse_id)->count();

                if($warehouse_products == 0)
                {
                    return response()->json(['success' => false, 'successmsg' => $order->PoWarehouse->warehouse_title.' dosent have '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                }

                $add_products_to_po_detail = new PurchaseOrderDetail;

                $add_products_to_po_detail->pod_import_tax_book  = $product->import_tax_book;
                $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $product->vat : null;

                $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();

                $add_products_to_po_detail->pod_unit_price          = $gettingProdSuppData->buying_price;
                $add_products_to_po_detail->pod_gross_weight        = $gettingProdSuppData->gross_weight;
                $add_products_to_po_detail->po_id                   = $request->po_id['po_id'];
                $add_products_to_po_detail->product_id              = $product->id;
                $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;
                $add_products_to_po_detail->warehouse_id            = Auth::user()->get_warehouse->id;

                /*vat calculations*/
                $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price, $request->purchasing_vat == null ? $product->vat : null);
                $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                /*convert val to thb's*/
                $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->po_id['po_id'], $vat_calculations['vat_amount']);
                $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
                $add_products_to_po_detail->save();

                (new PODetailCRUDHelper)->MakeHistory($request->po_id['po_id'], null, '', null, $product->refrence_code, '', 'New Item');
            }

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id['po_id']);

            if ($order->status == 14) {
                PODetailCRUDHelper::updatePOGroupInShipment($order, $product->id, $add_products_to_po_detail);
            }

            return response()->json([
                'success'    => true,
                'successmsg' => 'Product Added Successfully',
                'sub_total'  => $grandCalculations['sub_total'],
                'vat_amout'  => $grandCalculations['vat_amout'],
                'total_w_v'  => $grandCalculations['total_w_v'],
            ]);
        }
        else
        {
            return response()->json(['success' => false, 'successmsg' => 'Product Not Found in System']);
        }
	}

    public static function updatePOGroupInShipment($purchase_order, $product_id, $p_o_d)
    {
        DB::beginTransaction();
        $total_import_tax_book_price = null;
        $total_vat_actual_price = null;

        $total_quantity                     = null;
        $total_price                        = null;
        $total_import_tax_book_price        = null;
        $total_vat_actual_price             = null;
        $total_buying_price_in_thb          = null;
        $total_buying_price_with_vat_in_thb = null;
        $total_gross_weight                 = null;
        $po_group_level_vat_actual          = null;

        $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
        if($purchase_order->exchange_rate == null)
        {
            $exch_rate = $purchase_order->PoSupplier->getCurrency->conversion_rate;
        }
        else
        {
            $exch_rate = $purchase_order->exchange_rate;
        }

        $total_import_tax_book_price        = $purchase_order->total_import_tax_book_price;
        $total_vat_actual_price             = $purchase_order->total_vat_actual_price_in_thb;
        // $total_vat_actual_price       += $purchase_order->total_vat_actual_price;
        $total_gross_weight                 = $purchase_order->total_gross_weight;
        $total_buying_price_with_vat_in_thb = $purchase_order->total_with_vat_in_thb;
        $total_buying_price_in_thb          = $purchase_order->total_in_thb;
        // $po_group_level_vat_actual          = ($purchase_order->vat_amount_total / $exch_rate);

        $po_group = $purchase_order->p_o_group->po_group;

        $po_group->total_quantity                     = $total_quantity;
        $po_group->po_group_import_tax_book           = floor($total_import_tax_book_price * 100) / 100;
        $po_group->po_group_vat_actual                = floor($total_vat_actual_price * 100) / 100;
        $po_group->total_buying_price_in_thb          = $total_buying_price_in_thb;
        $po_group->total_buying_price_in_thb_with_vat = $total_buying_price_with_vat_in_thb; //(new col)
        $po_group->po_group_total_gross_weight        = $total_gross_weight;
        $po_group->vat_actual_tax                     = $total_vat_actual_price;
        $po_group->save();

        $occurrence = null;
        $total_import_tax_book_percent = null;
        $po_group_vat_actual_percent = null;

        $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$purchase_order->supplier_id)->first();
        if($po_group_product != null)
        {
            $po_group_product->quantity_ordered          += @$p_o_d->desired_qty;
            $po_group_product->quantity_inv              += $p_o_d->quantity;
            $po_group_product->import_tax_book_price     += $p_o_d->pod_import_tax_book_price;
            // $po_group_product->pogpd_vat_actual_percent  += $p_o_d->pod_vat_actual_price;

            // prices in euro
            $po_group_product->unit_price                += $p_o_d->pod_unit_price;
            $po_group_product->unit_price_with_vat       += $p_o_d->pod_unit_price_with_vat;
            // to calculate total unit price
            $amount = $p_o_d->pod_unit_price * $p_o_d->quantity;
            if($p_o_d->discount !== null)
            {
                $amount = $amount - ($amount * ($p_o_d->discount / 100));
            }
            $po_group_product->total_unit_price  = floatval($po_group_product->total_unit_price) + ($amount !== null ? number_format((float)$amount,3,'.','') : 0);

            // to calculate total unit price with vat
            $amount_with_vat = $p_o_d->pod_unit_price_with_vat * $p_o_d->quantity;
            if($p_o_d->discount !== null)
            {
                $amount_with_vat = $amount_with_vat - ($amount_with_vat * ($p_o_d->discount / 100));
            }
            $po_group_product->total_unit_price_with_vat  = floatval($po_group_product->total_unit_price_with_vat) + ($amount_with_vat !== null ? number_format((float)$amount_with_vat,3,'.','') : 0);

            $po_group_product->pogpd_vat_actual_percent  += $p_o_d->pod_vat_actual_price_in_thb;
            $po_group_product->total_gross_weight        += $p_o_d->pod_total_gross_weight;

            // prices in thb
            $po_group_product->unit_price_in_thb         += $p_o_d->unit_price_in_thb;
            $po_group_product->total_unit_price_in_thb   += $p_o_d->total_unit_price_in_thb;

            $po_group_product->unit_price_in_thb_with_vat += $p_o_d->unit_price_with_vat_in_thb;
            $po_group_product->total_unit_price_in_thb_with_vat   += $p_o_d->total_unit_price_with_vat_in_thb; //(new col)
            $po_group_product->unit_gross_weight         += $p_o_d->pod_gross_weight;
            $po_group_product->occurrence                += 1;


            if($p_o_d->pod_vat_actual_total_price !== 0 && $p_o_d->pod_vat_actual_total_price != 0)
            {
                $po_group_product->vat_weighted_percent  += ($p_o_d->pod_vat_actual_total_price / $purchase_order->vat_amount_total) * 100;
            }
            else
            {
                $po_group_product->vat_weighted_percent  += 0;
            }

            $po_group_product->pogpd_vat_actual_price    += ($p_o_d->pod_vat_actual/100 * $p_o_d->unit_price_in_thb);

            $find_item_tax_value = (($p_o_d->pod_vat_actual/100) * $p_o_d->total_unit_price_in_thb);
            if($p_o_d->total_unit_price_in_thb != 0)
            {
                $p_vat_percent = ($find_item_tax_value / $p_o_d->total_unit_price_in_thb) * 100;
            }
            else
            {
                $p_vat_percent = 0;
            }

            $po_group_product->pogpd_vat_actual_percent_val += $p_vat_percent;


            $po_group_product->save();
            $po_group_product->unit_gross_weight         = ($po_group_product->unit_gross_weight / 2);
            $po_group_product->currency_conversion_rate = $exch_rate;
            $po_group_product->save();
        }
        else
        {
            $find_product = $p_o_d->product;
            $po_group_product = new PoGroupProductDetail;
            $po_group_product->po_group_id               = $po_group->id;
            $po_group_product->po_id               = $p_o_d->po_id;
            $po_group_product->pod_id               = $p_o_d->id;
            $po_group_product->order_id               = $p_o_d->order_id;

            if($purchase_order->supplier_id != null)
            {
                $po_group_product->supplier_id               = $purchase_order->supplier_id;
            }
            else
            {
                $po_group_product->from_warehouse_id         = $purchase_order->from_warehouse_id;
            }
            $po_group_product->to_warehouse_id           = $purchase_order->to_warehouse_id;
            $po_group_product->product_id                = $p_o_d->product_id;
            $po_group_product->quantity_ordered          = @$p_o_d->desired_qty;
            $po_group_product->quantity_inv              = $p_o_d->quantity;
            $po_group_product->import_tax_book           = $p_o_d->pod_import_tax_book;
            $po_group_product->import_tax_book_price     = $p_o_d->pod_import_tax_book_price;

            $po_group_product->pogpd_vat_actual          = $find_product->vat;
            $po_group_product->pogpd_vat_actual_percent  = $p_o_d->pod_vat_actual_price_in_thb;
            // $po_group_product->pogpd_vat_actual_percent  = $p_o_d->pod_vat_actual_price;

            if($p_o_d->pod_vat_actual_price !== 0 && $p_o_d->pod_vat_actual_price != 0)
            {
                if($purchase_order->vat_amount_total != 0 && $purchase_order->vat_amount_total !== 0)
                {
                    $po_group_product->vat_weighted_percent  = ($p_o_d->pod_vat_actual_total_price / $purchase_order->vat_amount_total) * 100;
                }
                else
                {
                    $po_group_product->vat_weighted_percent = 0;
                }
            }
            else
            {
                $po_group_product->vat_weighted_percent  = 0;
            }

            $po_group_product->pogpd_vat_actual_price    = ($p_o_d->pod_vat_actual/100 * $p_o_d->unit_price_in_thb);


            $find_item_tax_value = (($p_o_d->pod_vat_actual/100) * $p_o_d->total_unit_price_in_thb);
            if($p_o_d->total_unit_price_in_thb != 0)
            {
                $p_vat_percent = ($find_item_tax_value / $p_o_d->total_unit_price_in_thb) * 100;
            }
            else
            {
                $p_vat_percent = 0;
            }

            $po_group_product->pogpd_vat_actual_percent_val    = $p_vat_percent;

            $po_group_product->total_gross_weight        = $p_o_d->pod_total_gross_weight;
            $po_group_product->unit_gross_weight         = $p_o_d->pod_gross_weight;
            $po_group_product->unit_price                = $p_o_d->pod_unit_price;
            $po_group_product->unit_price_with_vat       = $p_o_d->pod_unit_price_with_vat;

            // to calculate total unit price
            $amount = $p_o_d->pod_unit_price * $p_o_d->quantity;
            $amount = $amount - ($amount * (@$p_o_d->discount / 100));
            $po_group_product->total_unit_price  = $amount !== null ? number_format((float)$amount,3,'.','') : "--";

            // to calculate total unit price with vat
            $amount_with_vat = $p_o_d->pod_unit_price_with_vat * $p_o_d->quantity;
            $amount_with_vat = $amount_with_vat - ($amount_with_vat * (@$p_o_d->discount / 100));
            $po_group_product->total_unit_price_with_vat  = $amount_with_vat !== null ? number_format((float)$amount_with_vat,3,'.','') : "--";


            $po_group_product->discount                  = $p_o_d->discount;
            $po_group_product->currency_conversion_rate  = $p_o_d->currency_conversion_rate;
            $po_group_product->unit_price_in_thb         = $p_o_d->unit_price_in_thb;
            $po_group_product->total_unit_price_in_thb   = $p_o_d->total_unit_price_in_thb;

            /*vat cols*/
            $po_group_product->unit_price_in_thb_with_vat         = $p_o_d->unit_price_with_vat_in_thb;
            $po_group_product->total_unit_price_in_thb_with_vat   = $p_o_d->total_unit_price_with_vat_in_thb;
            /*vat cols*/

            $po_group_product->good_condition            = $p_o_d->good_condition;
            $po_group_product->result                    = $p_o_d->result;
            $po_group_product->good_type                 = $p_o_d->good_type;
            $po_group_product->temperature_c             = $p_o_d->temperature_c;
            $po_group_product->occurrence                = 1;
            if($find_product != null)
            {
                $supplier_id = $purchase_order->supplier_id != null ? $purchase_order->supplier_id : $find_product->supplier_id;
                $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id',$find_product->id)->where('supplier_id',$supplier_id)->first();
                // dd($purchase_order);
                if($default_or_last_supplier)
                {
                    $po_group_product->total_extra_tax  = ($default_or_last_supplier->extra_tax * $po_group_product->quantity_inv);
                    $po_group_product->total_extra_cost = ($default_or_last_supplier->extra_cost * $po_group_product->quantity_inv);

                    $po_group_product->unit_extra_tax   = $default_or_last_supplier->extra_tax;
                    $po_group_product->unit_extra_cost  = $default_or_last_supplier->extra_cost;

                    $p_o_d->total_extra_tax = ($default_or_last_supplier->extra_tax * $p_o_d->quantity);
                    $p_o_d->total_extra_cost = ($default_or_last_supplier->extra_cost * $p_o_d->quantity);

                    $p_o_d->unit_extra_tax = $default_or_last_supplier->extra_tax;
                    $p_o_d->unit_extra_cost = $default_or_last_supplier->extra_cost;
                    $p_o_d->save();
                }
            }
            $po_group_product->currency_conversion_rate = $exch_rate;
            $po_group_product->save();
            $total_import_tax_book_percent += $p_o_d->pod_import_tax_book;
            $po_group_vat_actual_percent += $p_o_d->pod_vat_actual;
        }
        $p_o_d->currency_conversion_rate  = $exch_rate;
        $p_o_d->save();
        $po_group = PoGroup::where('id',$po_group->id)->first();

        $total_import_tax_book_price = 0;
        $total_vat_actual_price = 0;

        $total_import_tax_book_price_with_vat = 0;
        $total_vat_actual_price_with_vat = 0;

        $po_group_details = $po_group->po_group_product_details;

        foreach ($po_group_details as $po_group_detail) {
            $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
            $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);

            if($po_group_detail->occurrence > 1)
            {
                $po_group_detail->pogpd_vat_actual_price    = $po_group_detail->pogpd_vat_actual_price / $po_group_detail->occurrence;
                $po_group_detail->pogpd_vat_actual_percent_val    = $po_group_detail->pogpd_vat_actual_percent_val / $po_group_detail->occurrence;

                $po_group_detail->unit_price_with_vat    = $po_group_detail->unit_price_with_vat / $po_group_detail->occurrence;
                $po_group_detail->unit_price    = $po_group_detail->unit_price / $po_group_detail->occurrence;
                $po_group_detail->unit_price_in_thb_with_vat    = $po_group_detail->unit_price_in_thb_with_vat / $po_group_detail->occurrence;
                $po_group_detail->unit_price_in_thb    = $po_group_detail->unit_price_in_thb / $po_group_detail->occurrence;
                // $po_group_detail->vat_weighted_percent = $po_group_detail->vat_weighted_percent / $po_group_detail->occurrence;
                $po_group_detail->save();

                $all_ids = PurchaseOrder::where('po_group_id',$po_group_detail->po_group_id)->where('supplier_id',$po_group_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_group_detail->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                if($all_record->count() > 0)
                {
                    //to update total extra tax column in po group product detail
                    $po_group_detail->total_extra_cost = $all_record->sum('total_extra_cost');
                    $po_group_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                    $po_group_detail->total_extra_tax = $all_record->sum('total_extra_tax');
                    $po_group_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                    $po_group_detail->save();
                }
            }
        }

        if($total_import_tax_book_price == 0)
        {
            foreach ($po_group_details as $po_group_detail) {
                $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                $total_import_tax_book_price += $book_tax;

                $book_tax_with_vat = (1/$count)* $po_group_detail->total_unit_price_in_thb_with_vat;
                $total_import_tax_book_price_with_vat += $book_tax_with_vat;
            }
        }

        if($total_vat_actual_price == 0)
        {
            foreach ($po_group_details as $po_group_detail) {
                $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                $total_vat_actual_price += $book_tax;

                $book_tax_with_vat = (1/$count)* $po_group_detail->total_unit_price_in_thb_with_vat;
                $total_vat_actual_price_with_vat += $book_tax_with_vat;
            }
        }

        $po_group->total_import_tax_book_percent = $total_import_tax_book_percent;
        $po_group->po_group_vat_actual_percent = $po_group_vat_actual_percent;
        $po_group->po_group_import_tax_book = floor($total_import_tax_book_price * 100) / 100;
        $po_group->po_group_vat_actual = floor($total_vat_actual_price * 100) / 100;

        $po_group->po_group_import_tax_book_with_vat = floor($total_import_tax_book_price_with_vat * 100) / 100;
        $po_group->po_group_vat_actual_with_vat = floor($total_vat_actual_price_with_vat * 100) / 100;

        $po_group->save();
        DB::commit();
    }

	private function MakeHistory($po_id, $order_id, $column_name, $pod_id, $reference_number, $old_value, $new_value)
	{
		$order_history = new PurchaseOrdersHistory;
        $order_history->user_id = Auth::user()->id;
        $order_history->order_id = $order_id;
        $order_history->reference_number = $reference_number;
        $order_history->column_name = $column_name;
        $order_history->old_value = $old_value;
        $order_history->new_value = $new_value;
        $order_history->po_id = $po_id;
        $order_history->pod_id  = $pod_id;
        $order_history->save();
	}

	public static function updateUnitGrossWeight($request)
	{
        DB::beginTransaction();
        try {
            $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
            if($checkSameProduct->is_billed == "Product")
            {
                $purchase_order_detail                         = PurchaseOrderDetail::find($request->rowId);
                $purchase_order_detail->pod_gross_weight       = $request->pod_gross_weight;
                $purchase_order_detail->pod_total_gross_weight = ($request->pod_gross_weight * $purchase_order_detail->quantity);
                $purchase_order_detail->save();
            }

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            $po_totoal_change = PurchaseOrder::find($request->po_id);
            $po_totoal_change->total_gross_weight = $grandCalculations['total_gross_weight'];
            $po_totoal_change->save();

            (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, @$checkSameProduct->order_id, "Gross weight", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$request->old_value, @$request->pod_gross_weight);

            $updateRow = PurchaseOrderDetail::find($request->rowId);
            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, 0, null, $calColumn);
        } catch (\Exception $th) {
            DB::rollBack();
        }

	}

	public static function UpdateDesireQty($request)
	{
        DB::beginTransaction();
        try {
            $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
            foreach($request->except('rowId','po_id','old_value') as $key => $value)
            {
                if($key == 'desired_qty')
                {
                    if($key == 'desired_qty' && $po->product != null)
                    {
                        $decimal_places = $po->product->units->decimal_places;
                        $value = round($value,$decimal_places);
                        $request->desired_qty = round($request->desired_qty,$decimal_places);
                    }
                    $po->$key = $value;
                    $reference_number = $po->is_billed == "Billed" ? "Billed Item" :  @$po->product->refrence_code;
                    // if($po->is_billed == "Billed")
                    // {
                    //     $reference_number = "Billed Item";
                    // }
                    // else
                    // {
                    //     $reference_number = @$po->product->refrence_code;
                    // }
                    (new PODetailCRUDHelper)->MakeHistory(@$po->po_id, $po->order_id, "Desired Qty", @$po->id, $reference_number, @$request->old_value, @$value);
                }
            }

            if($po->billed_unit_per_package == null)
            {
                $getProductDefaultSupplier = $po->product->supplier_products->where('supplier_id',$po->PurchaseOrder->supplier_id)->first();
                $billed_unit_per_package = 1;
                if($getProductDefaultSupplier->billed_unit == null || $getProductDefaultSupplier->billed_unit == 0)
                {
                    $getProductDefaultSupplier->billed_unit = 1;
                    $getProductDefaultSupplier->save();
                }
            }
            else
            {
                $billed_unit_per_package = $po->billed_unit_per_package;
            }

            $new_qty = $request->desired_qty * $billed_unit_per_package;
            $po->quantity = $new_qty;
            // updating total unit price and total gross weight of this item
            $po->pod_total_unit_price   = ($po->pod_unit_price * $new_qty);
            $po->pod_vat_actual_total_price        = ($po->pod_vat_actual_price * $new_qty);
            $po->pod_vat_actual_total_price_in_thb = ($po->pod_vat_actual_price_in_thb * $new_qty);
            $po->pod_total_unit_price_with_vat     = ($po->pod_unit_price_with_vat * $new_qty);
            $po->pod_total_gross_weight = ($po->pod_gross_weight * $new_qty);

            $calculations = $po->total_unit_price_in_thb * ($po->pod_import_tax_book / 100);
            $po->pod_import_tax_book_price = $calculations;
            $po->save();

            // getting product total amount as quantity
            $amount = 0;
            $updateRow = PurchaseOrderDetail::find($po->id);

            $amount = $updateRow->quantity * $updateRow->pod_unit_price;
            $amount = number_format($amount, 3, '.', ',');

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, $amount, $grandCalculations, $calColumn);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
	}

	public function GetResponse($updateRow, $amount, $grandCalculations, $calColumn)
	{
		return response()->json([
            'success'   => true,
            'updateRow' => $updateRow,
            'amount'    => $amount,
            'sub_total' => $grandCalculations != null ? $grandCalculations['sub_total'] : 0,
            'total_qty' => $grandCalculations != null ? $grandCalculations['total_qty'] : 0,
            'vat_amout' => $grandCalculations != null ? $grandCalculations['vat_amout'] : 0,
            'total_w_v' => $grandCalculations != null ? $grandCalculations['total_w_v'] : 0,
            'id'        => $updateRow->id,
            'unit_price' => $calColumn != null ? $calColumn['unit_price'] : 0,
            'unit_price_after_discount' => $updateRow != null ? @$updateRow->pod_unit_price_after_discount : 0,
            'unit_price_w_vat' => $calColumn != null ? $calColumn['unit_price_w_vat'] : 0,
            'total_amount_wo_vat' => $calColumn != null ? $calColumn['total_amount_wo_vat'] : 0,
            'total_amount_w_vat' => $calColumn != null ? $calColumn['total_amount_w_vat'] : 0,
            'unit_gross_weight' => $calColumn != null ? $calColumn['unit_gross_weight'] : 0,
            'total_gross_weight' => $calColumn != null ? $calColumn['total_gross_weight'] : 0,
            'desired_qty' => $calColumn != null ? $calColumn['desired_qty'] : 0,
            'quantity' => $calColumn != null ? $calColumn['quantity'] : 0,
            'discount' => $updateRow != null ? $updateRow->discount : 0,
        ]);
	}

	public static function UpdateBilledUnitPerPackage($request)
	{
        DB::beginTransaction();
        try {
            $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
            if($checkSameProduct->is_billed == "Product")
            {
                $checkSameProduct->billed_unit_per_package = $request->billed_unit_per_package;

                if($checkSameProduct->desired_qty !== null)
                {
                    $new_qty = $checkSameProduct->desired_qty * $request->billed_unit_per_package;
                    $checkSameProduct->quantity = $new_qty;

                    // updating total unit price and total gross weight of this item
                    $checkSameProduct->pod_total_unit_price              = ($checkSameProduct->pod_unit_price * $new_qty);
                    $checkSameProduct->pod_vat_actual_total_price        = ($checkSameProduct->pod_vat_actual_price * $new_qty);
                    $checkSameProduct->pod_vat_actual_total_price_in_thb = ($checkSameProduct->pod_vat_actual_price_in_thb * $new_qty);
                    $checkSameProduct->pod_total_unit_price_with_vat     = ($checkSameProduct->pod_unit_price_with_vat * $new_qty);
                    $checkSameProduct->pod_total_gross_weight            = ($checkSameProduct->pod_gross_weight * $new_qty);
                }
                $checkSameProduct->save();

                (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, @$checkSameProduct->order_id, "MOQ (Minimum Order Quantity)", null, @$checkSameProduct->product->refrence_code, @$request->old_value, @$request->billed_unit_per_package);
            }

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            $updateRow = PurchaseOrderDetail::find($request->rowId);
            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, 0, $grandCalculations, $calColumn);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
	}

	public static function SavePoProductQuantity($request)
    {
        $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        if ($po->PurchaseOrder->status == 14) {
            $collection = collect([]);
            $collection->offsetSet('0', []);
            $collection->offsetSet('1', []);
            $collection->offsetSet('2', [
                'qty_inv' => $request->quantity,
                'discount' => $po->discount,
                'purchasing_price_euro' => $po->pod_unit_price,
                'gross_weight' => $po->pod_gross_weight,
                'extra_cost' => $po->unit_extra_cost,
                'extra_tax' => $po->unit_extra_tax,
                'needed_ids' => $po->id . ',' . $request->pogpd__id . ',' . $po->po_id
            ]);
            $rows = $collection;
            // dd($rows->count);
            $status = ExportStatus::where('type', 'products_receiving_importings_bulk_job')->where('user_id',auth()->user()->id)->first();

            if ($status == null) {
                $new = new ExportStatus();
                $new->type = 'products_receiving_importings_bulk_job';
                $new->user_id = Auth::user()->id;
                $new->status = 1;
                $new->save();
                ProductsReceivingImportJob::dispatch($rows,Auth::user()->id,$po->PurchaseOrder->p_o_group->po_group_id);
                return response()->json(['status' => 1, 'recursive' => true]);
            } elseif ($status->status == 1) {
                return response()->json(['status' => 2, 'recursive' => false]);
            } elseif ($status->status == 0 || $status->status == 2) {
                ProductsReceivingImportJob::dispatch($rows,Auth::user()->id,$po->PurchaseOrder->p_o_group->po_group_id);
                ExportStatus::where('type', 'products_receiving_importings_bulk_job')->update(['status' => 1, 'user_id' => Auth::user()->id, 'exception' => null]);

                return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
            }
        }

        DB::beginTransaction();
        try {
            $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();

            $field = null;
            $user = User::find($request->user_id);
            foreach($request->except('rowId','po_id','from_bulk','old_value','pogpd__id') as $key => $value)
            {
                $field = $key;
                $old_value = $po->quantity;
                if($key == 'quantity')
                {
                    if($key == 'quantity' && $po->product != null)
                    {
                        $decimal_places = $po->product->units->decimal_places;
                        $value = round($value,$decimal_places);
                    }
                    if($po->PurchaseOrder->status == 21)
                    {
                        $stock_q = $po->update_stock_card($po,$value);
                    }

                    $po->$key = $value;
                    if($old_value != $value)
                    {
                        $reference_number = $po->is_billed == "Billed" ? "Billed Item" : @$po->product->refrence_code;
                        (new PODetailCRUDHelper)->MakeHistory(@$po->po_id, $po->order_id, "Quantity", @$po->id, $reference_number, @$request->old_value, @$value);
                    }

                    if($po->get_td_reserved->count() > 0)
                    {
                        foreach ($po->get_td_reserved as $res) {
                            $stock_out = StockManagementOut::find($res->stock_id);
                            if($stock_out)
                            {
                                $stock_out->available_stock += $res->reserved_quantity;
                                $stock_out->save();
                            }

                            $res->delete();
                        }
                    }
                    if($po->PurchaseOrder->status == 21)
                    {
                        //Transfer Documnet Reserve Quantity
                        $stock_m_outs = StockManagementOut::where('warehouse_id', @$po->PurchaseOrder->from_warehouse_id)->where('product_id', $po->product_id)->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
                        $res = null;
                        // dd($stock_m_outs);
                        foreach ($stock_m_outs as $stock_m_out){
                            $quantity_out = $value;
                            $res = PurchaseOrderDetail::reserveQtyForTD($res != null ? $res : $quantity_out, $stock_m_out, $po);
                            if($res == 0){
                                break;
                            }
                        }
                    }
                }
            }

            if($po->product_id != null && $po->billed_unit_per_package > 0)
            {
                $po->desired_qty = ($po->quantity / $po->billed_unit_per_package);
            }
            // updating total unit price and total gross weight of this item
            $po->pod_total_unit_price              = ($po->pod_unit_price * $po->quantity);
            $po->pod_vat_actual_total_price        = ($po->pod_vat_actual_price * $po->quantity);
            $po->pod_vat_actual_total_price_in_thb = ($po->pod_vat_actual_price_in_thb * $po->quantity);
            $po->pod_total_unit_price_with_vat     = ($po->pod_unit_price_with_vat * $po->quantity);
            $po->pod_total_gross_weight            = ($po->pod_gross_weight * $po->quantity);

            $calculations     = $po->total_unit_price_in_thb * ($po->pod_import_tax_book / 100);
            $po->pod_import_tax_book_price = $calculations;
            // $vat_calculations = $po->total_unit_price_in_thb * ($po->pod_vat_actual / 100);
            // $po->pod_vat_actual_price      = $vat_calculations;
            $po->save();

            // getting product total amount as quantity
            $amount = 0;
            $updateRow = PurchaseOrderDetail::find($po->id);

            $amount = $updateRow->quantity * $updateRow->pod_unit_price;
            $amount = number_format($amount, 3, '.', ',');
            // dd($field);

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);
            if($field == 'quantity')
            {
                $po_totoal_change = PurchaseOrder::find($request->po_id);
                if($po_totoal_change->status >= 14)
                {
                    if($request->pogpd__id != null)
                    {
                        $po_group_p_detail = PoGroupProductDetail::where('status',1)->find($request->pogpd__id);
                        if($po_group_p_detail->occurrence == 1)
                        {
                            $po_group_p_detail->quantity_inv = $updateRow->quantity;
                            $po_group_p_detail->save();
                        }

                        if($po_group_p_detail->occurrence > 1)
                        {
                            $po_group_p_detail->quantity_inv -= $request->old_value;
                            $po_group_p_detail->save();
                            $po_group_p_detail->quantity_inv += $updateRow->quantity;
                            $po_group_p_detail->save();
                        }
                    }
                    if($request->from_bulk == null)
                    {
                        (new PurchaseOrderController)->updateGroupViaPo($updateRow->po_id);
                    }
                }

            }
            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, $amount, $grandCalculations, $calColumn);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
    }

	public static function UpdateUnitPrice($request)
	{
        DB::beginTransaction();
        try {
            $user = User::find($request->user_id);
            $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
            $po = PurchaseOrder::with('PoSupplier.getCurrency')->find($request->po_id);

            $pod_unit_price = $request->unit_price;
            $pod_vat_value  = $checkSameProduct->pod_vat_actual;
            $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
            $vat_amount     = number_format($vat_amount,4,'.','');

            if($checkSameProduct->is_billed == "Product")
            {
                if($po->exchange_rate == null)
                {
                    $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
                }
                else
                {
                    $supplier_conv_rate_thb = $po->exchange_rate;
                }

                $checkSameProduct->pod_unit_price        = $request->unit_price;
                $checkSameProduct->last_updated_price_on = date('Y-m-d');
                $checkSameProduct->pod_total_unit_price  = ($request->unit_price * $checkSameProduct->quantity);

                $checkSameProduct->pod_unit_price_with_vat       = number_format($request->unit_price + $vat_amount,3,'.','');
                $checkSameProduct->pod_total_unit_price_with_vat = number_format($checkSameProduct->pod_unit_price_with_vat * $checkSameProduct->quantity,3,'.','');
                $checkSameProduct->pod_vat_actual_price          = $vat_amount;
                $checkSameProduct->pod_vat_actual_total_price    = number_format($vat_amount * $checkSameProduct->quantity,3,'.','');

                $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
                $checkSameProduct->pod_import_tax_book_price = $calculations;

                $checkSameProduct->save();

                $checkSameProduct->pod_import_tax_book_price = ($checkSameProduct->pod_import_tax_book/100)*$checkSameProduct->total_unit_price_in_thb;
                $checkSameProduct->save();

                if($po->status == 13 || $po->status == 14)
                {
                    $checkSameProduct = PurchaseOrderDetail::with('PurchaseOrder')->find($request->rowId);
                    if($checkSameProduct->product_id != null)
                    {
                        if($checkSameProduct->PurchaseOrder->supplier_id != null && $checkSameProduct->PurchaseOrder->from_warehouse_id == null)
                        {
                            $supplier_id = $checkSameProduct->PurchaseOrder->supplier_id;
                        }
                        else
                        {
                            $supplier_id = $checkSameProduct->product->supplier_id;
                        }

                        if($checkSameProduct->PurchaseOrder->exchange_rate != NULL)
                        {
                            $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->exchange_rate;
                        }
                        else
                        {
                            $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
                        }

                        if($checkSameProduct->discount !== null)
                        {
                            $discount_price = $checkSameProduct->quantity * $request->unit_price - (($checkSameProduct->quantity * $request->unit_price) * ($checkSameProduct->discount / 100));
                            if($checkSameProduct->quantity != 0 && $checkSameProduct->quantity != null)
                            {
                                $after_discount_price = ($discount_price / $checkSameProduct->quantity);
                            }
                            else
                            {
                                $after_discount_price = $discount_price;
                            }
                            $unit_price = $after_discount_price;
                        }
                        else
                        {
                            $unit_price = $request->unit_price;
                        }

                        if($checkSameProduct->discount < 100 || $checkSameProduct->discount == null)
                        {
                            $getProductSupplier = SupplierProducts::where('product_id',$checkSameProduct->product_id)->where('supplier_id',$supplier_id)->first();
                            $old_price_value = $getProductSupplier->buying_price;

                            $getProductSupplier->buying_price = $unit_price;
                            $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);
                            $getProductSupplier->currency_conversion_rate = 1/$supplier_conv_rate_thb;
                            $getProductSupplier->save();

                            $product_detail = Product::find($checkSameProduct->product_id);
                            if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
                            {$supplier_conv_rate_thb;
                                $buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);

                                $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

                                $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($total_buying_price);

                                $product_detail->total_buy_unit_cost_price = $total_buying_price;

                                // this is supplier buying unit cost price
                                $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                                // this is selling price
                                $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                                $product_detail->selling_price = $total_selling_price;
                                $product_detail->last_price_updated_date = Carbon::now();
                                $product_detail->save();

                                $product_history              = new ProductHistory;
                                $product_history->user_id     = $user != null ? $user->id : Auth::user()->id;
                                $product_history->product_id  = $checkSameProduct->product_id;
                                $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct->id;
                                $product_history->old_value   = $old_price_value;
                                $product_history->new_value   = $unit_price;
                                $product_history->save();
                            }
                        }
                    }
                }
                (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Unit Price", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$request->old_value, @$request->unit_price);
            }
            else
            {
                $checkSameProduct->pod_unit_price = $request->unit_price;
                $checkSameProduct->pod_total_unit_price = ($request->unit_price * $checkSameProduct->quantity);

                $checkSameProduct->pod_unit_price_with_vat       = number_format($request->unit_price + $vat_amount,3,'.','');
                $checkSameProduct->pod_total_unit_price_with_vat = number_format($checkSameProduct->pod_unit_price_with_vat * $checkSameProduct->quantity,3,'.','');
                $checkSameProduct->pod_vat_actual_price          = $vat_amount;
                $checkSameProduct->pod_vat_actual_total_price    = number_format($vat_amount * $checkSameProduct->quantity,3,'.','');

                $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
                $checkSameProduct->pod_import_tax_book_price = $calculations;

                // $vat_calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_vat_actual / 100);
                // $checkSameProduct->pod_vat_actual_price = $vat_calculations;
                $checkSameProduct->save();

                (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Unit Price", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$request->old_value, @$request->unit_price);
            }

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            if($po->status == 14 && $request->from_bulk == null)
            {
                (new PurchaseOrderController)->updateGroupViaPo($request->po_id);
            }

            $updateRow = PurchaseOrderDetail::find($request->rowId);
            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, 0, $grandCalculations, $calColumn);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }

	}

    public static function UpdateUnitPriceAfterDiscount($request){
        DB::beginTransaction();
        try {
            $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
            $po = PurchaseOrder::with('PoSupplier.getCurrency')->find($request->po_id);
            $unit_price = $checkSameProduct->pod_unit_price;
            $discount = $checkSameProduct->discount;
            $unit_price_after_discount = $request->unit_price_after_discount;

            if(($unit_price == 0 || $unit_price == null) && ($discount == null || $discount == 0)){
                $old_value = $checkSameProduct->pod_unit_price;
                $checkSameProduct->pod_unit_price_after_discount = $unit_price_after_discount;
                $checkSameProduct->save();

                (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Unit Price After Discount", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$request->old_value, @$request->unit_price_after_discount);

                DB::commit();
                $update_unit_price = new \Illuminate\Http\Request();
                $update_unit_price->replace(['rowId' => $request->rowId, 'po_id' => $request->po_id,'unit_price' => $unit_price_after_discount, 'old_value' => $old_value]);

                return self::UpdateUnitPrice($update_unit_price);
            }

            if((@$unit_price < $unit_price_after_discount) && $unit_price != null && $unit_price != 0){
                return response()->json(['success' => false, 'msg' => 'Unit price after discount must be less than Unit Price']);
            }

            if ($unit_price != 0) {
                $discount_percentage = (($unit_price - $unit_price_after_discount) / $unit_price) * 100;
            } else {
                $discount_percentage = 0;
            }

            $old_value = $checkSameProduct->pod_unit_price;
            $old_discount = $checkSameProduct->discount;
            $checkSameProduct->pod_unit_price_after_discount = $unit_price_after_discount;
            $checkSameProduct->discount = $discount_percentage;
            $checkSameProduct->save();

            (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Unit Price After Discount", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$request->old_value, @$request->unit_price_after_discount);

            (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Discount", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$old_discount, @$checkSameProduct->discount);

            DB::commit();
            $update_discount_req = new \Illuminate\Http\Request();
            $update_discount_req->replace(['rowId' => $request->rowId, 'po_id' => $request->po_id,'discount' => $checkSameProduct->discount, 'old_value' => $old_discount]);

            return self::SavePoProductDiscount($update_discount_req);
            // return response()->json(['success' => true, 'msg' => 'Unit price after discount updated successfully !!!']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

	public static function SavePoProductVatActual($request)
	{
        DB::beginTransaction();
        try {
            $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
            foreach($request->except('rowId','po_id','old_value') as $key => $value)
            {
                $po->$key = $value;
                $po->save();

                (new PODetailCRUDHelper)->MakeHistory(@$po->po_id, $po->order_id, "Purchasing Vat", @$po->id, @$po->product->refrence_code, @$request->old_value, @$value);
            }

            /*vat calculations*/
            $vat_calculations = $po->calculateVat($po->pod_unit_price, $po->pod_vat_actual);
            $po->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
            $po->pod_vat_actual_price    = $vat_calculations['vat_amount'];

            /*convert val to thb's*/
            $converted_vals = $po->calculateVatToSystemCurrency($request->po_id, $vat_calculations['vat_amount']);
            $po->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
            $po->save();

            $request_for_quantity = new \Illuminate\Http\Request();
            $request_for_quantity->replace(['rowId' => $request->rowId, 'po_id' => $request->po_id,'quantity' => $po->quantity, 'old_value' => $po->quantity]);
            (new PurchaseOrderController)->SavePoProductQuantity($request_for_quantity);

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            $updateRow = PurchaseOrderDetail::find($po->id);

            $amount = $updateRow->quantity * $updateRow->pod_unit_price;
            $amount = number_format($amount, 3, '.', ',');

            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, $amount, $grandCalculations, $calColumn);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }

	}

	public static function UpdateUnitPriceWithVat($request)
	{
		DB::beginTransaction();
        try {
            $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
            $po = PurchaseOrder::find($request->po_id);

            $pod_unit_price = $request->pod_unit_price_with_vat;
            $pod_vat_value  = $checkSameProduct->pod_vat_actual;
            $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
            $vat_amount     = number_format($vat_amount,4,'.','');

            $pod_unit_price_with_vat = $request->pod_unit_price_with_vat;
            $pod_vat_value  = $checkSameProduct->pod_vat_actual;

            $pod_unit_price = ($pod_unit_price_with_vat * 100) / ( 100 + $pod_vat_value );
            $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
            $vat_amount     = number_format($vat_amount,4,'.','');

            if($checkSameProduct->is_billed == "Product")
            {
                if($po->exchange_rate == null)
                {
                    $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
                }
                else
                {
                    $supplier_conv_rate_thb = $po->exchange_rate;
                }

                $checkSameProduct->pod_unit_price        = $pod_unit_price;
                $checkSameProduct->last_updated_price_on = date('Y-m-d');
                $checkSameProduct->pod_total_unit_price  = ($pod_unit_price * $checkSameProduct->quantity);

                $checkSameProduct->pod_unit_price_with_vat       = number_format($request->pod_unit_price_with_vat,3,'.','');
                $checkSameProduct->pod_total_unit_price_with_vat = number_format($request->pod_unit_price_with_vat * $checkSameProduct->quantity,3,'.','');
                $checkSameProduct->pod_vat_actual_price          = $vat_amount;
                $checkSameProduct->pod_vat_actual_total_price    = number_format($vat_amount * $checkSameProduct->quantity,3,'.','');

                $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
                $checkSameProduct->pod_import_tax_book_price = $calculations;

                $checkSameProduct->save();

                $checkSameProduct->pod_import_tax_book_price = ($checkSameProduct->pod_import_tax_book/100)*$checkSameProduct->total_unit_price_in_thb;
                $checkSameProduct->save();

                if($po->status == 13 || $po->status == 14)
                {
                    $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
                    if($checkSameProduct->product_id != null)
                    {
                        if($checkSameProduct->PurchaseOrder->supplier_id != null && $checkSameProduct->PurchaseOrder->from_warehouse_id == null)
                        {
                            $supplier_id = $checkSameProduct->PurchaseOrder->supplier_id;
                        }
                        else
                        {
                            $supplier_id = $checkSameProduct->product->supplier_id;
                        }

                        if($checkSameProduct->PurchaseOrder->exchange_rate != NULL)
                        {
                            $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->exchange_rate;
                        }
                        else
                        {
                            $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
                        }

                        if($checkSameProduct->discount !== null)
                        {
                            $discount_price = $checkSameProduct->quantity * $pod_unit_price - (($checkSameProduct->quantity * $pod_unit_price) * ($checkSameProduct->discount / 100));
                            if($checkSameProduct->quantity != 0 && $checkSameProduct->quantity != null)
                            {
                                $after_discount_price = ($discount_price / $checkSameProduct->quantity);
                            }
                            else
                            {
                                $after_discount_price = $discount_price;
                            }
                            $unit_price = $after_discount_price;
                        }
                        else
                        {
                            $unit_price = $pod_unit_price;
                        }

                        if($checkSameProduct->discount < 100 || $checkSameProduct->discount == null)
                        {
                            $getProductSupplier = SupplierProducts::where('product_id',$checkSameProduct->product_id)->where('supplier_id',$supplier_id)->first();
                            $old_price_value = $getProductSupplier->buying_price;

                            $getProductSupplier->buying_price = $unit_price;
                            $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);

                            $getProductSupplier->currency_conversion_rate = 1/$supplier_conv_rate_thb;
                            $getProductSupplier->save();

                            $product_detail = Product::find($checkSameProduct->product_id);
                            if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
                            {
                                $buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);

                                $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

                                $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($total_buying_price);

                                $product_detail->total_buy_unit_cost_price = $total_buying_price;

                                // this is supplier buying unit cost price
                                $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                                // this is selling price
                                $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                                $product_detail->selling_price = $total_selling_price;
                                $product_detail->last_price_updated_date = Carbon::now();
                                $product_detail->save();

                                $product_history              = new ProductHistory;
                                $product_history->user_id     = Auth::user()->id;
                                $product_history->product_id  = $checkSameProduct->product_id;
                                $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct->id;
                                $product_history->old_value   = $old_price_value;
                                $product_history->new_value   = $unit_price;
                                $product_history->save();
                            }
                        }
                    }
                }
                (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Unit Price (+Vat)", @$checkSameProduct->id, @$checkSameProduct->product->refrence_code, @$request->old_value, $request->pod_unit_price_with_vat);
            }
            else
            {
                $checkSameProduct->pod_unit_price = $pod_unit_price;
                $checkSameProduct->pod_total_unit_price = ($pod_unit_price * $checkSameProduct->quantity);

                $checkSameProduct->pod_unit_price_with_vat       = number_format($request->pod_unit_price_with_vat,3,'.','');
                $checkSameProduct->pod_total_unit_price_with_vat = number_format($request->pod_unit_price_with_vat * $checkSameProduct->quantity,3,'.','');
                $checkSameProduct->pod_vat_actual_price          = $vat_amount;
                $checkSameProduct->pod_vat_actual_total_price    = number_format($vat_amount * $checkSameProduct->quantity,3,'.','');

                $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
                $checkSameProduct->pod_import_tax_book_price = $calculations;

                // $vat_calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_vat_actual / 100);
                // $checkSameProduct->pod_vat_actual_price = $vat_calculations;
                $checkSameProduct->save();

                (new PODetailCRUDHelper)->MakeHistory(@$checkSameProduct->po_id, $checkSameProduct->order_id, "Unit Price (+Vat)", @$checkSameProduct->id, "Billed Item", @$request->old_value, $request->pod_unit_price_with_vat);
            }

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            if($po->status == 14)
            {
                (new PurchaseOrderController)->updateGroupViaPo($request->po_id);
            }

            $updateRow = PurchaseOrderDetail::find($request->rowId);
            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, 0, $grandCalculations, $calColumn);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
	}

	public static function SavePoProductDiscount($request)
	{
        DB::beginTransaction();
        try {
            $user = User::find($request->user_id);
            $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
            $checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();
            foreach($request->except('rowId','po_id','from_bulk','old_value') as $key => $value)
            {
                if($key == 'discount')
                {
                    $po->$key = $value;
                    $po->save();


                    $order_history = new PurchaseOrdersHistory;
                    $order_history->user_id = $user != null ? $user->id : Auth::user()->id;
                    $order_history->order_id = @$po->order_id;
                    if($po->is_billed == "Billed")
                    {
                        $order_history->reference_number = "Billed Item";
                    }
                    else
                    {
                        $order_history->reference_number = @$po->product->refrence_code;
                    }
                    $order_history->old_value = @$request->old_value;

                    $order_history->column_name = "Discount";

                    $order_history->new_value = @$value;
                    $order_history->po_id = @$po->po_id;
                    $order_history->save();
                    $pf = $po->is_billed == "Billed" ? 'Billed Item' : @$po->product->refrence_code;
                    (new PODetailCRUDHelper)->MakeHistory(@$po->po_id, $po->order_id, "Discount", @$po->id, @$pf, @$request->old_value, $value);
                }
            }

            if($po->PurchaseOrder->status == 13 || $po->PurchaseOrder->status == 14 || $po->PurchaseOrder->status > 14)
            {
                $checkSameProduct2 = PurchaseOrderDetail::find($request->rowId);
                if ($checkSameProduct2)
                {
                    $total = ($checkSameProduct2->pod_unit_price * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
                    $total_thb = ($checkSameProduct2->unit_price_in_thb * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
                    $po->pod_total_unit_price = $total;
                    $po->total_unit_price_in_thb = $total_thb;
                    $po->save();

                    if($checkSameProduct2->product_id != null)
                    {
                        if($checkSameProduct2->PurchaseOrder->supplier_id == NULL && $checkSameProduct2->PurchaseOrder->from_warehouse_id != NULL)
                        {
                            $supplier_id = $checkSameProduct2->product->supplier_id;
                        }
                        else
                        {
                            $supplier_id = $checkSameProduct2->PurchaseOrder->supplier_id;
                        }

                        // this is the price of after conversion for THB
                        if($checkSameProduct2->PurchaseOrder->exchange_rate != NULL)
                        {
                            $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->exchange_rate;
                        }
                        else
                        {
                            $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
                        }

                        if($checkSameProduct2->pod_unit_price !== NULL)
                        {
                            $discount_price = $checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price - (($checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price) * ($checkSameProduct2->discount / 100));

                            if($checkSameProduct2->quantity !== NULL && $checkSameProduct2->quantity !== 0 && $checkSameProduct2->quantity != 0 )
                            {
                                $after_discount_price = ($discount_price / $checkSameProduct2->quantity);
                            }
                            else
                            {
                                $after_discount_price = ($discount_price);
                            }
                            $unit_price = $after_discount_price;
                        }
                        else
                        {
                            $unit_price = $checkSameProduct2->pod_unit_price;
                        }

                        if($checkSameProduct2->discount < 100 || $checkSameProduct2->discount == null)
                        {
                            $getProductSupplier = SupplierProducts::where('product_id',@$checkSameProduct2->product_id)->where('supplier_id',@$supplier_id)->first();
                            $old_price_value    = $getProductSupplier->buying_price;

                            $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);
                            $getProductSupplier->buying_price = $unit_price;
                            $getProductSupplier->save();

                            $product_detail = Product::find($checkSameProduct2->product_id);

                            if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
                            {
                                $buying_price_in_thb = ($getProductSupplier->buying_price / $supplier_conv_rate_thb);

                                $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

                                $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($getProductSupplier->extra_tax)+($total_buying_price);

                                $product_detail->total_buy_unit_cost_price = $total_buying_price;

                                // this is supplier buying unit cost price
                                $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                                // this is selling price
                                $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                                $product_detail->selling_price = $total_selling_price;
                                $product_detail->save();

                                $product_history = new ProductHistory;
                                $product_history->user_id = $user != null ? $user->id : Auth::user()->id;
                                $product_history->product_id = $product_detail->id;
                                $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct2->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct2->id;
                                $product_history->old_value = $old_price_value;
                                $product_history->new_value = $unit_price;
                                $product_history->save();
                            }
                        }
                    }
                }
            }

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

            $po_modifications = PurchaseOrder::find($request->po_id);
            $po_modifications->total = $grandCalculations['sub_total'];
            $po_modifications->save();

            if($po_modifications->status > 13)
            {
                $p_o_p_d = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_modifications->po_group_id)->where('product_id',$po->product_id)->first();
                $updated_pod = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
                if($p_o_p_d->occurrence == 1)
                {
                    $p_o_p_d->discount = $updated_pod->discount;
                    $p_o_p_d->total_unit_price = $updated_pod->pod_total_unit_price;
                    $p_o_p_d->total_unit_price_in_thb = $updated_pod->total_unit_price_in_thb;
                    $p_o_p_d->save();
                }
                if($request->from_bulk == null)
                {
                    (new PurchaseOrderController)->updateGroupViaPo($request->po_id);
                }
            }

            $updateRow = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
            $calColumn = $objectCreated->calColumns($updateRow);
            DB::commit();
            return (new PODetailCRUDHelper)->GetResponse($updateRow, 0, $grandCalculations, $calColumn);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
	}

	public static function deleteProdFromPoDetail($request)
	{
		$getToDelete = PurchaseOrderDetail::where('po_id',$request->po_id)->where('id',$request->id)->first();

        if($getToDelete->is_billed == "Product")
        {
        	(new PODetailCRUDHelper)->MakeHistory(@$request->po_id, null, null, null, $getToDelete->product->refrence_code." ( Ref ID#. ".$getToDelete->id." )", "", "Deleted");
        }

        $delProdFromList = PurchaseOrderDetail::where('po_id',$request->po_id)->where('id',$request->id)->delete();
        $redirect = "no";

        $checkPoDetailProduct = PurchaseOrderDetail::where('po_id',$request->po_id)->get();
        $itemsCount = PurchaseOrderDetail::where('is_billed','Product')->where('po_id',$request->po_id)->sum('quantity');

        $reserved = TransferDocumentReservedQuantity::where('pod_id', $request->id)->get();

        foreach ($reserved as $item) {
            if ($item->stock_id != null) {
                $stock_m_out = StockManagementOut::find($item->stock_id);
                $stock_m_out->available_stock += $item->reserved_quantity;
                $stock_m_out->save();
            }
            $item->delete();
        }
        /*calulation through a function*/
        $objectCreated = new PurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);
        if($checkPoDetailProduct->count() == 0)
        {
            $deletePurchaseOrder = PurchaseOrder::where('id',$request->po_id)->delete();
            $redirect = "yes";
        }

        return response()->json([
            'success'   => true,
            'sub_total' => @$grandCalculations['sub_total'],
            'total_qty' => @$grandCalculations['total_qty'],
            'vat_amout' => @$grandCalculations['vat_amout'],
            'total_w_v' => @$grandCalculations['total_w_v'],
            'redirect'  => $redirect,
            'total_qty' => $itemsCount
        ]);
	}

	public static function SavePoProductWarehouse($request)
	{
		$po = PurchaseOrder::find($request->selected_ids);
		if($po->status > 13)
		{
			return response()->json(['cannot_change_warehouse' => true]);
		}
         if($request->pos_count > 1)
        {
            $var = (new PurchaseOrderController)->removeFromExistingGroup($request);
        }
        $pod = PurchaseOrderDetail::where('po_id',$request->selected_ids)->get();
        foreach ($pod as $value)
        {
            $value->warehouse_id = $request->warehouse_id;
            $value->save();
        }


        $po->to_warehouse_id = $request->warehouse_id;
        $po->save();

        if($request->pos_count == 1)
        {
            $po_group = $po->po_group;
            $po_group->warehouse_id = $request->warehouse_id;
            $po_group->save();
        }


        return response()->json(['success' => true]);
	}

	public static function SavePoNote($request)
	{
        // dd($request->all());
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::find($request->po_id);
            $field = null;
            foreach($request->except('po_id','from_bulk') as $key => $value)
            {
                $field = $key;
                if($key == 'note')
                {
                    $po->po_notes()->updateOrCreate(['po_id'  => $po->id],['note'=>$value],['created_by'=>@Auth::user()->id]);
                    DB::commit();
                    return response()->json(['success'=>true]);
                }
                if($key == 'payment_due_date')
                {
                    $date = str_replace("/","-",$value);
                    $date =  date('Y-m-d',strtotime($date));
                    $po->$key = $date;
                }
                if($key == 'transfer_date')
                {
                    $date = str_replace("/","-",$value);
                    $date =  date('Y-m-d',strtotime($date));
                    $po->$key = $date;
                }
                if($key == 'invoice_date')
                {
                    $value = str_replace("/","-",$value);
                    $value =  date('Y-m-d',strtotime($value));
                    $po->$key = $value;
                    if($po->payment_terms_id !== null)
                    {
                        $getCreditTerm = PaymentTerm::find($po->payment_terms_id);
                        $creditTerm = $getCreditTerm->title;
                        $int = intval(preg_replace('/[^0-9]+/', '', $creditTerm), 10);

                        if($creditTerm == "COD") // today data if COD
                        {
                            $payment_due_date = $value;
                        }
                        $needle = "EOM";
                        if(strpos($creditTerm,$needle) !== false)
                        {
                            $trdate = $value;
                            $getDayOnly = date('d', strtotime($trdate));
                            $extractMY = new DateTime($trdate);

                            $daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)$extractMY->format('m'), $extractMY->format('Y') );
                            $subtractDays = $daysOfMonth - $getDayOnly;

                            $days = $int + $subtractDays;
                            $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                            $newdate = date("Y-m-d",$newdate);
                            $payment_due_date = $newdate;
                        }
                        else
                        {
                            $days = $int;
                            $trdate = $value;
                            $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                            $newdate = date("Y-m-d",$newdate);
                            $payment_due_date = $newdate;
                        }

                        $po->payment_due_date = $payment_due_date;
                    }
                }
                if($key == 'target_receive_date')
                {
                    $value = str_replace("/","-",$value);
                    $value =  date('Y-m-d',strtotime($value));
                    $po->$key = $value;
                }
                if($key == 'memo')
                {
                    $po->$key = $value;
                }
                if($key == 'invoice_number')
                {
                    $po->$key = $value;
                }
                if($key == 'exchange_rate')
                {
                    $old_value = 1/$po->exchange_rate;
                    PurchaseOrdersHistory::create([
                        'po_id' => $po->id,
                        'reference_number' => '--',
                        'column_name' => 'Invoice Exchange Rate',
                        'old_value' => $old_value,
                        'new_value' => $request->exchange_rate,
                        'user_id'=> @Auth::user()->id,

                    ]);


                    $total_import_tax_book_price = null;
                    $exchange_rate = (1 / $value);
                    $po->$key = $exchange_rate;
                    $po->save();

                    $po_detail = PurchaseOrderDetail::where('po_id',$po->id)->get();
                    if($po_detail->count() > 0)
                    {
                        foreach ($po_detail as $pod)
                        {
                            $pod->currency_conversion_rate = $exchange_rate;
                            $pod->save();
                        }
                    }

                    #To update thb values
                    if($po->supplier_id != null)
                    {
                        if($po->exchange_rate == null)
                        {
                            $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
                        }
                        else
                        {
                            $supplier_conv_rate_thb = $po->exchange_rate;
                        }

                        foreach ($po->PurchaseOrderDetail as $p_o_d)
                        {
                            $p_o_d->currency_conversion_rate  = $supplier_conv_rate_thb;
                            $p_o_d->unit_price_in_thb         = $p_o_d->pod_unit_price/$supplier_conv_rate_thb;
                            $p_o_d->total_unit_price_in_thb   = $p_o_d->pod_total_unit_price/$supplier_conv_rate_thb;
                            $p_o_d->pod_import_tax_book_price = ($p_o_d->pod_import_tax_book/100)*$p_o_d->total_unit_price_in_thb;
                            $total_import_tax_book_price      += $p_o_d->pod_import_tax_book_price;

                            $p_o_d->save();
                        }
                        $po->total_import_tax_book_price  = $total_import_tax_book_price;
                        $po->total_in_thb = $po->total/$supplier_conv_rate_thb;
                        $po->save();
                    }
                    #End Here
                }
            }
            $po->save();

            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($po->id);

            if($field == 'exchange_rate' && $request->from_bulk == null)
            {
                $po_totoal_change = PurchaseOrder::find($po->id);
                if($po_totoal_change->status == 14)
                {
                    (new PurchaseOrderController)->updateGroupViaPo($po->id);
                }
            }

            $updateRow = PurchaseOrderNote::where('po_id',$po->id)->first();

            DB::commit();

            return response()->json(['success' => true, 'updateRow' => $updateRow, 'po' => $po]);
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
	}

	public static function paymentTermSaveInPo($request)
	{
		$po = PurchaseOrder::find($request->po_id);
        $po->payment_terms_id = $request->payment_terms_id;

        $getCreditTerm = PaymentTerm::find($request->payment_terms_id);
        $creditTerm = $getCreditTerm->title;
        $int = intval(preg_replace('/[^0-9]+/', '', $creditTerm), 10);

        if($creditTerm == "COD") // today data if COD
        {
            $payment_due_date = $po->invoice_date;
        }
        $needle = "EOM";
        if(strpos($creditTerm,$needle) !== false)
        {               // getting remaining current month days
            $trdate = $po->invoice_date;
            $getDayOnly = date('d', strtotime($trdate));
            $extractMY = new DateTime($trdate);

            $daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)$extractMY->format('m'), $extractMY->format('Y') );
            $subtractDays = $daysOfMonth - $getDayOnly;

            $days = $int + $subtractDays;
            $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
            $newdate = date("Y-m-d",$newdate);
            $payment_due_date = $newdate;
        }
        else
        {
            $days = $int;
            $trdate = $po->invoice_date;
            $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
            $newdate = date("Y-m-d",$newdate);
            $payment_due_date = $newdate;
        }

        // dd($payment_due_date);

        $po->payment_due_date = $payment_due_date;
        $po->save();

        return response()->json([
            'success' => true,
            'payment_due_date' => $po->payment_due_date
        ]);
	}
}

