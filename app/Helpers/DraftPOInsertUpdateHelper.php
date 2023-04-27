<?php
namespace App\Helpers;

use Auth;
use DateTime;
use Carbon\Carbon;
use App\QuotationConfig;
use App\Models\Common\Status;
use App\Models\Common\Product;
use App\DraftPurchaseOrderHistory;
use App\Models\Common\Order\Order;
use App\Models\Common\PaymentTerm;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\TransferDocumentReservedQuantity;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\DraftPurchaseOrderNote;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\DraftPurchaseOrderDocument;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetailNote;

/**
 *
 */
class DraftPOInsertUpdateHelper
{
	public static function addProdByRefrenceNumber($request)
	{
		$order = DraftPurchaseOrder::find($request->draft_po_id['draft_po_id']);
        $unit_price = 0;
        if($order->supplier_id == null && $order->from_warehouse_id == null)
        {
            return response()->json(['success' => false, 'successmsg' => 'Please Select Supply From First']);
        }

        $product = Product::where('refrence_code',$request->refrence_number)->where('status',1)->first();

        if($product)
        {
            if($order->supplier_id != NULL && $order->from_warehouse_id == NULL)
            {
                $supplier_products = SupplierProducts::where('product_id',$product->id)->where('is_deleted',0)->where('supplier_id',$order->supplier_id)->count();

                if($supplier_products == 0)
                {
                    return response()->json(['success' => false, 'successmsg' => $order->getSupplier->reference_name.' do not provide us '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                }

                /*Adding item into a Draft PO detail*/
                $add_products_to_po_detail = new DraftPurchaseOrderDetail;

                $getImportTaxBook = Product::find($product->id);
                $add_products_to_po_detail->pod_import_tax_book  = $getImportTaxBook->import_tax_book;
                $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $getImportTaxBook->vat : null;

                $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$order->supplier_id)->first();

                $add_products_to_po_detail->pod_unit_price          = @$gettingProdSuppData->buying_price;
                $add_products_to_po_detail->last_updated_price_on   = $product->last_price_updated_date;
                $add_products_to_po_detail->pod_gross_weight        = @$gettingProdSuppData->gross_weight;
                $add_products_to_po_detail->po_id                   = $request->draft_po_id['draft_po_id'];
                $add_products_to_po_detail->product_id              = $product->id;
                $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;
                $add_products_to_po_detail->warehouse_id            = Auth::user()->get_warehouse->id;

                /*vat calculations*/
                $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price,$request->purchasing_vat == null ? $getImportTaxBook->vat : null);

                $add_products_to_po_detail->pod_unit_price_with_vat     = $vat_calculations['pod_unit_price_with_vat'];
                $add_products_to_po_detail->pod_vat_actual_price        = $vat_calculations['vat_amount'];

                /*convert val to thb's*/
                $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->draft_po_id['draft_po_id'], $vat_calculations['vat_amount']);
                $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                $add_products_to_po_detail->save();
            }
            if($order->from_warehouse_id != NULL && $order->supplier_id == NULL)
            {
                $warehouse_products = WarehouseProduct::where('product_id',$product->id)->where('warehouse_id',$order->from_warehouse_id)->count();

                if($warehouse_products == 0)
                {
                    return response()->json(['success' => false, 'successmsg' => $order->getFromWarehoue->warehouse_title.' dosent have '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                }

                $checkProduct = DraftPurchaseOrderDetail::where('po_id',$request->draft_po_id)->where('product_id',$product->id)->first();
                if($checkProduct)
                {
                    return response()->json(['success' => false, 'successmsg' => 'This product is already exist in this PO, please increase the quantity of that product']);
                }
                else
                {
                    $add_products_to_po_detail = new DraftPurchaseOrderDetail;

                    $getImportTaxBook = Product::find($product->id);
                    $add_products_to_po_detail->pod_import_tax_book  = $getImportTaxBook->import_tax_book;
                    $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $getImportTaxBook->vat : null;

                    $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();

                    $add_products_to_po_detail->pod_unit_price          = $gettingProdSuppData->buying_price;
                    $add_products_to_po_detail->pod_gross_weight        = $gettingProdSuppData->gross_weight;
                    $add_products_to_po_detail->po_id                   = $request->draft_po_id['draft_po_id'];
                    $add_products_to_po_detail->product_id              = $product->id;
                    $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                    $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;
                    $add_products_to_po_detail->warehouse_id            = Auth::user()->get_warehouse->id;

                    /*vat calculations*/
                    $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price,$request->purchasing_vat == null ? $getImportTaxBook->vat : null);

                    $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                    $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                    /*convert val to thb's*/
                    $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->draft_po_id['draft_po_id'], $vat_calculations['vat_amount']);
                    $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
                    $add_products_to_po_detail->save();
                }
            }

            // create history
            (new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id['draft_po_id'], $product->id, "Product", "New Added", "--");

            /*calulation through a function*/
            $objectCreated = new DraftPurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id['draft_po_id']);

            return response()->json([
                'success'    => true,
                'successmsg' => 'Product Added In PO Successfully',
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

	private function MakeHistory($draft_po_id, $product_id, $column_name, $new_value, $old_value, $pod_id = null)
	{
		$order_history = new DraftPurchaseOrderHistory;
        $order_history->user_id = Auth::user()->id;
        $order_history->reference_number =  $product_id;
        $order_history->old_value = $old_value;
        $order_history->column_name = $column_name;
        $order_history->po_id = $draft_po_id;
        $order_history->pod_id = $pod_id;
        $order_history->new_value = $new_value;
        $order_history->save();
	}

	public static function SaveDraftPoProductQuantity($request)
    {
        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        foreach($request->except('rowId','draft_po_id') as $key => $value)
        {
            if($key == 'quantity')
            {
                if($key == 'quantity' && $po->getProduct != null)
                {
                    $decimal_places = $po->getProduct->units->decimal_places;
                    $value = round($value,$decimal_places);
                }
                if ($po->draftPo->status == 23) {
                    $current_stock = WarehouseProduct::where('product_id', $po->product_id)->where('warehouse_id', $po->draftPo->from_warehouse_id)->first();
                    if ($current_stock) {
                        $pi_config = [];
                        $error_msg =  '';
                        $pi_config = QuotationConfig::where('section', 'pick_instruction')->first();
                        if ($pi_config != null)
                        {
                            $pi_config = unserialize($pi_config->print_prefrences);
                        }
                        if ($pi_config['pi_confirming_condition'] == 2){
                            $current_stock = $current_stock->current_quantity;
                            $error_msg =  'Current Qty is less then the ordered qty for ';
                        }
                        else if ($pi_config['pi_confirming_condition'] == 3){
                            $current_stock = $current_stock->available_quantity;
                            $error_msg =  'Available Qty is less then the ordered qty for ';
                        }
                        if ($pi_config['pi_confirming_condition'] == 2 || $pi_config['pi_confirming_condition'] == 3){
                            if ($value > $current_stock) {
                                return response()->json(['success' => false, 'errorMsg' => $error_msg . $po->getProduct->refrence_code]);
                            }
                        }
                    }
                }
                $po->$key = $value;
                $po->save();
            }

            if($po->get_td_reserved->count() > 0)
            {
                foreach ($po->get_td_reserved as $res)
                {
                    $stock_out = StockManagementOut::find($res->stock_id);
                    if($stock_out)
                    {
                        $stock_out->available_stock += $res->reserved_quantity;
                        $stock_out->save();
                    }
                    $res->delete();
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
        $po->save();

        // getting product total amount as quantity
        $amount = 0;
        $updateRow = DraftPurchaseOrderDetail::find($po->id);

        $amount = $updateRow->quantity * $updateRow->pod_unit_price;
        $amount = number_format($amount, 3, '.', ',');

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
            if($key == 'quantity' && $value != $request->old_value)
            {
                // create history
                (new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $po->product_id, "Quantity", $value, $request->old_value, $po->id);
            }
        }

        $calColumn = $objectCreated->calColumns($updateRow);

        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($updateRow, $amount, $grandCalculations, $calColumn);
    }

	private function GetJsonResponce($updateRow, $amount, $grandCalculations, $calColumn)
	{
		return response()->json([
            'success'   => true,
            'updateRow' => $updateRow,
            'amount'    => $amount,
            'sub_total' => $grandCalculations != null ? $grandCalculations['sub_total'] : '',
            'total_qty' => $grandCalculations != null ? $grandCalculations['total_qty'] : '',
            'vat_amout' => $grandCalculations != null ? $grandCalculations['vat_amout'] : '',
            'total_w_v' => $grandCalculations != null ? $grandCalculations['total_w_v'] : '',
            'id'        => $updateRow->id,
            'unit_price' => $calColumn != null ? $calColumn['unit_price'] : '',
            'unit_price_w_vat' => $calColumn != null ? $calColumn['unit_price_w_vat'] : '',
            'total_amount_wo_vat' => $calColumn != null ? $calColumn['total_amount_wo_vat'] : '',
            'total_amount_w_vat' => $calColumn != null ? $calColumn['total_amount_w_vat'] : '',
            'unit_gross_weight' => $calColumn != null ? $calColumn['unit_gross_weight'] : '',
            'total_gross_weight' => $calColumn != null ? $calColumn['total_gross_weight'] : '',
            'desired_qty' => $calColumn != null ? $calColumn['desired_qty'] : '',
            'quantity' => $calColumn != null ? $calColumn['quantity'] : '',
        ]);
	}

	public static function updateDraftPoDesiredQuantity($request)
	{
		$po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();

        foreach($request->except('rowId','draft_po_id','old_value') as $key => $value)
        {
            if($key == 'desired_qty')
            {
                if($key == 'desired_qty' && $po->getProduct != null)
                {
                    $decimal_places = $po->getProduct->units->decimal_places;
                    $value = round($value,$decimal_places);
                    $request->desired_qty = round($request->desired_qty,$decimal_places);
                }
                $po->$key = $value;
                $po->save();
            }
        }

        if($po->billed_unit_per_package == null)
        {
            $billed_unit_per_package = null;
            $getProductDefaultSupplier = $po->getProduct->supplier_products->where('supplier_id',$po->draftPo->supplier_id)->first();
            if($getProductDefaultSupplier->billed_unit == null || $getProductDefaultSupplier->billed_unit == 0)
            {
                $billed_unit_per_package = 1;
                $getProductDefaultSupplier->billed_unit = 1;
                $getProductDefaultSupplier->save();
            }
        }
        else
        {
            $billed_unit_per_package = $po->billed_unit_per_package;
        }

        $new_qty      = $request->desired_qty * $billed_unit_per_package;
        $po->quantity = $new_qty;

        $po->pod_total_unit_price              = ($po->pod_unit_price * $new_qty);
        $po->pod_vat_actual_total_price        = ($po->pod_vat_actual_price * $new_qty);
        $po->pod_vat_actual_total_price_in_thb = ($po->pod_vat_actual_price_in_thb * $new_qty);
        $po->pod_total_unit_price_with_vat     = ($po->pod_unit_price_with_vat * $new_qty);
        $po->pod_total_gross_weight            = ($po->pod_gross_weight * $new_qty);
        $po->save();

        // getting product total amount as quantity
        $amount = 0;
        $updateRow = DraftPurchaseOrderDetail::find($po->id);

        $amount = $updateRow->quantity * $updateRow->pod_unit_price;
        $amount = number_format($amount, 3, '.', ',');

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        // DraftPurchaseOrderHistory
        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
            if($key == 'desired_qty')
            {
            	// create history
            	(new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $po->product_id, 'Quantity', $value, $request->old_value);
            }
        }
        $calColumn = $objectCreated->calColumns($updateRow);
        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($updateRow, $amount, $grandCalculations, $calColumn);
	}

	public static function UpdateDraftPoUnitGrossWeight($request)
	{
		$purchase_order_detail = DraftPurchaseOrderDetail::find($request->rowId);
        $purchase_order_detail->pod_gross_weight       = $request->pod_gross_weight;
        $purchase_order_detail->pod_total_gross_weight = ($request->pod_gross_weight * $purchase_order_detail->quantity);
        $purchase_order_detail->save();

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
            if($key == 'pod_gross_weight')
            {
            	(new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $po->product_id, "Gross weight", $value, $request->old_value);
            }
        }

        $updateRow = DraftPurchaseOrderDetail::find($request->rowId);
        $calColumn = $objectCreated->calColumns($updateRow);
        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($updateRow, 0, null, $calColumn);
	}

	public static function updateDraftPoBilledUnitPerPackage($request)
	{
		$checkSameProduct = DraftPurchaseOrderDetail::find($request->rowId);
        if($checkSameProduct->is_billed == "Product")
        {
            $checkSameProduct->billed_unit_per_package = $request->billed_unit_per_package;

            if($checkSameProduct->desired_qty !== null)
            {
                $new_qty                                        = $checkSameProduct->desired_qty * $request->billed_unit_per_package;
                $checkSameProduct->quantity                      = $new_qty;
                $checkSameProduct->pod_total_unit_price          = ($checkSameProduct->pod_unit_price * $new_qty);
                $checkSameProduct->pod_vat_actual_total_price    = ($checkSameProduct->pod_vat_actual_price * $new_qty);
                $checkSameProduct->pod_vat_actual_total_price_in_thb = ($checkSameProduct->pod_vat_actual_price_in_thb * $new_qty);
                $checkSameProduct->pod_total_unit_price_with_vat = ($checkSameProduct->pod_unit_price_with_vat * $new_qty);
                $checkSameProduct->pod_total_gross_weight        = ($checkSameProduct->pod_gross_weight * $new_qty);
            }
            $checkSameProduct->save();
        }
        else
        {
            // Do nothing
        }

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
            if($key == 'billed_unit_per_package')
            {
            	(new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $po->product_id, "Order Qty Unit", $value, $request->old_value);
            }
        }
        $calColumn = $objectCreated->calColumns($po);

        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($po, 0, $grandCalculations, $calColumn);
	}

	public static function UpdateDraftPoUnitPrice($request)
	{
		$purchase_order_detail = DraftPurchaseOrderDetail::find($request->rowId);

        $pod_unit_price = $request->unit_price;
        $pod_vat_value  = $purchase_order_detail->pod_vat_actual;
        $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
        $vat_amount     = number_format($vat_amount,4,'.','');

        $purchase_order_detail->pod_unit_price                = number_format($request->unit_price,3,'.','');
        $purchase_order_detail->pod_total_unit_price          = number_format($request->unit_price * $purchase_order_detail->quantity,3,'.','');
        $purchase_order_detail->pod_unit_price_with_vat       = number_format($request->unit_price + $vat_amount,3,'.','');
        $purchase_order_detail->pod_total_unit_price_with_vat = number_format($purchase_order_detail->pod_unit_price_with_vat * $purchase_order_detail->quantity,3,'.','');
        $purchase_order_detail->pod_vat_actual_price          = $vat_amount;
        $purchase_order_detail->pod_vat_actual_total_price    = number_format($vat_amount * $purchase_order_detail->quantity,3,'.','');
        $purchase_order_detail->last_updated_price_on         = carbon::now();
        $purchase_order_detail->save();

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
            if($key == 'unit_price')
            {
            	(new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $purchase_order_detail->product_id, "unit price", $value, $request->old_value);
            }
        }

        $updateRow = DraftPurchaseOrderDetail::find($request->rowId);

        $calColumn = $objectCreated->calColumns($updateRow);
        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($updateRow, 0, $grandCalculations, $calColumn);
	}

	public static function SaveDraftPoVatActual($request)
	{
		$po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        foreach($request->except('rowId','draft_po_id') as $key => $value)
        {
            if($key == 'pod_vat_actual')
            {
                $po->$key = $value;
                $po->save();

                (new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $po->product_id, "Purchasing Vat", $value, $request->old_value);
            }
        }

        /*vat calculations*/
        $vat_calculations = $po->calculateVat($po->pod_unit_price, $po->pod_vat_actual);

        $po->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
        $po->pod_vat_actual_price    = $vat_calculations['vat_amount'];

        /*convert val to thb's*/
        $converted_vals = $po->calculateVatToSystemCurrency($request->draft_po_id, $vat_calculations['vat_amount']);
        $po->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
        $po->save();

        $request_for_quantity = new \Illuminate\Http\Request();
        $request_for_quantity->replace(['rowId' => $request->rowId, 'draft_po_id' => $request->draft_po_id,'quantity' => $po->quantity, 'old_value' => $po->quantity]);
        (new PurchaseOrderController)->SaveDraftPoProductQuantity($request_for_quantity);

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        $updateRow = DraftPurchaseOrderDetail::find($po->id);
        $amount = $updateRow->quantity * $updateRow->pod_unit_price;
        $amount = number_format($amount, 3, '.', ',');

        $calColumn = $objectCreated->calColumns($updateRow);

        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($updateRow, $amount, $grandCalculations, $calColumn);
	}

	public static function UpdateDraftPoUnitPriceVat($request)
	{
		$purchase_order_detail = DraftPurchaseOrderDetail::find($request->rowId);

        $pod_unit_price_with_vat = $request->pod_unit_price_with_vat;
        $pod_vat_value  = $purchase_order_detail->pod_vat_actual;

        $pod_unit_price = ($pod_unit_price_with_vat * 100) / ( 100 + $pod_vat_value );
        $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
        $vat_amount     = number_format($vat_amount,4,'.','');

        $purchase_order_detail->pod_unit_price                = number_format($pod_unit_price,3,'.','');
        $purchase_order_detail->pod_total_unit_price          = number_format($pod_unit_price * $purchase_order_detail->quantity,3,'.','');
        $purchase_order_detail->pod_unit_price_with_vat       = number_format($request->pod_unit_price_with_vat,3,'.','');
        $purchase_order_detail->pod_total_unit_price_with_vat = number_format($request->pod_unit_price_with_vat * $purchase_order_detail->quantity,3,'.','');
        $purchase_order_detail->pod_vat_actual_price          = $vat_amount;
        $purchase_order_detail->pod_vat_actual_total_price    = number_format($vat_amount * $purchase_order_detail->quantity,3,'.','');
        $purchase_order_detail->last_updated_price_on         = carbon::now();
        $purchase_order_detail->save();

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
            if($key == 'pod_unit_price_with_vat')
            {
            	(new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $purchase_order_detail->product_id, "Unit Price (+Vat)", $value, $request->old_value);
            }
        }
        $updateRow = DraftPurchaseOrderDetail::find($request->rowId);
        $calColumn = $objectCreated->calColumns($updateRow);
        return (new DraftPOInsertUpdateHelper)->GetJsonResponce($updateRow, 0, $grandCalculations, $calColumn);
	}

	public static function removeProductFromDraftPo($request)
	{
		$draft_po_detail = DraftPurchaseOrderDetail::find($request->id);
        foreach($request->except('id') as $key => $value)
        {
            if($key == 'draft_po_id')
            {
            	(new DraftPOInsertUpdateHelper)->MakeHistory($request->id, $draft_po_detail->product_id, "Product", 'Deleted', '--');
            }
        }
        $draft_po_detail->delete();

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        $itemsCount = DraftPurchaseOrderDetail::where('is_billed','Product')->where('po_id',$request->draft_po_id)->sum('quantity');

        return response()->json([
            'success'        => true,
            'successmsg'     => 'Product successfully removed',
            'total_item_qty' => $itemsCount,
            'sub_total'      => $grandCalculations['sub_total'],
            'vat_amout'      => $grandCalculations['vat_amout'],
            'total_w_v'      => $grandCalculations['total_w_v'],
        ]);
	}

	private function DiscardDraftPO($request)
	{
        $draft_po = DraftPurchaseOrder::find($request->draft_po_id);
        $draft_po->draftPoDetail()->delete();
        $draft_po->delete();
        $errorMsg =  'Discarded Successfully!';
        return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
	}

	private function SaveDraftPO($request)
	{
        $draft_po = DraftPurchaseOrder::find($request->draft_po_id);

        if($draft_po->draftPoDetail()->count() == 0)
        {
            $errorMsg =  'Please add some products in the Purchase Order';
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }

        $draft_po_detail = DraftPurchaseOrderDetail::where('po_id',$request->draft_po_id)->get();

        if($draft_po_detail->count() > 0)
        {
            foreach ($draft_po_detail as $value)
            {
                if($value->quantity == 0 || $value->quantity == null)
                {
                  $errorMsg =  'Quantity cannot be 0 or Null, please enter quantity of the added items';
                  return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                if($value->is_billed == "Billed")
                {
                    if($value->billed_desc == null)
                    {
                      $errorMsg =  'Billed Item Description Cannot Be Empty.';
                      return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }

                    if($value->pod_unit_price == null)
                    {
                      $errorMsg =  'Billed Item Unit Price Cannot Be Empty.';
                      return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }
                }
            }
        }

        $po_status = Status::where('id',4)->first();
        $counter_formula = $po_status->counter_formula;
        $counter_formula = explode('-',$counter_formula);
        $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $year = substr($year, -2);
        $month = sprintf("%02d", $month);
        $date = $year.$month;

        $c_p_ref = PurchaseOrder::where('ref_id','LIKE',"$date%")->orderby('id','DESC')->first();
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if($str == NULL)
        {
            $onlyIncrementGet = 0;
        }
        $system_gen_no = $date.str_pad(@$onlyIncrementGet + 1, $counter_length,0, STR_PAD_LEFT);
        $date = date('y-m-d');

        $purchaseOrder = PurchaseOrder::create([
            'ref_id'                        => $system_gen_no,
            'status'                        => 12,
            'total'                         => $draft_po->total,
            'total_with_vat'                => $draft_po->total_with_vat,
            'vat_amount_total'              => $draft_po->vat_amount_total,
            'total_quantity'                => $draft_po->total_quantity,
            'total_gross_weight'            => $draft_po->total_gross_weight,
            'total_import_tax_book'         => $draft_po->total_import_tax_book,
            'total_import_tax_book_price'   => $draft_po->total_import_tax_book_price,
            'total_vat_actual'              => $draft_po->total_vat_actual,
            'total_vat_actual_price'        => $draft_po->total_vat_actual_price,
            'total_vat_actual_price_in_thb' => $draft_po->total_vat_actual_price_in_thb,
            'supplier_id'                   => $draft_po->supplier_id,
            'from_warehouse_id'             => $draft_po->from_warehouse_id,
            'created_by'                    => Auth::user()->id,
            'memo'                          => $draft_po->memo,
            'payment_terms_id'              => $draft_po->payment_terms_id,
            'payment_due_date'              => $draft_po->payment_due_date,
            'target_receive_date'           => $draft_po->target_receive_date,
            'confirm_date'                  => $date,
            'to_warehouse_id'               => $draft_po->to_warehouse_id,
            'invoice_date'                  => $draft_po->invoice_date,
            'exchange_rate'                 => $draft_po->exchange_rate,
        ]);

        // PO status history maintaining
        $page_status = Status::select('title')->whereIn('id',[12])->pluck('title')->toArray();
        $poStatusHistory = new PurchaseOrderStatusHistory;
        $poStatusHistory->user_id    = Auth::user()->id;
        $poStatusHistory->po_id      = $purchaseOrder->id;
        $poStatusHistory->status     = 'Created';
        $poStatusHistory->new_status = $page_status[0];
        $poStatusHistory->save();

        $draft_po_detail = DraftPurchaseOrderDetail::where('po_id',$draft_po->id)->get();

        foreach($draft_po_detail as $dpo_detail)
        {
            if($dpo_detail->product_id != null)
            {
                $product = Product::where('id',$dpo_detail->product_id)->first();
                $purchaseOrderDetail = PurchaseOrderDetail::create([
                    'po_id'                             => $purchaseOrder->id,
                    'order_id'                          => NULL,
                    'customer_id'                       => NULL,
                    'order_product_id'                  => NULL,
                    'product_id'                        => $dpo_detail->product_id,
                    'billed_desc'                       => $dpo_detail->billed_desc,
                    'is_billed'                         => $dpo_detail->is_billed,
                    'created_by'                        => $dpo_detail->created_by,
                    'pod_import_tax_book'               => $dpo_detail->pod_import_tax_book,
                    'pod_vat_actual'                    => $dpo_detail->pod_vat_actual,
                    'pod_unit_price'                    => $dpo_detail->pod_unit_price,
                    'pod_unit_price_with_vat'           => $dpo_detail->pod_unit_price_with_vat,
                    'last_updated_price_on'             => $dpo_detail->last_updated_price_on,
                    'pod_gross_weight'                  => $dpo_detail->pod_gross_weight,
                    'quantity'                          => $dpo_detail->quantity,
                    'pod_total_gross_weight'            => $dpo_detail->pod_total_gross_weight,
                    'pod_total_unit_price'              => $dpo_detail->pod_total_unit_price,
                    'pod_total_unit_price_with_vat'     => $dpo_detail->pod_total_unit_price_with_vat,
                    'discount'                          => $dpo_detail->discount,
                    'pod_import_tax_book_price'         => $dpo_detail->pod_import_tax_book_price,
                    'pod_vat_actual_price'              => $dpo_detail->pod_vat_actual_price,
                    'pod_vat_actual_price_in_thb'       => $dpo_detail->pod_vat_actual_price_in_thb,
                    'pod_vat_actual_total_price'        => $dpo_detail->pod_vat_actual_total_price,
                    'pod_vat_actual_total_price_in_thb' => $dpo_detail->pod_vat_actual_total_price_in_thb,
                    'warehouse_id'                      => $draft_po->to_warehouse_id,
                    'temperature_c'                     => $product->product_temprature_c,
                    'good_type'                         => $product->type_id,
                    'supplier_packaging'                => $dpo_detail->supplier_packaging,
                    'billed_unit_per_package'           => $dpo_detail->billed_unit_per_package,
                    'desired_qty'                       => $dpo_detail->desired_qty,
                    'currency_conversion_rate'          => $draft_po->exchange_rate,
                ]);
            }
            else
            {
                $purchaseOrderDetail = PurchaseOrderDetail::create([
                    'po_id'                             => $purchaseOrder->id,
                    'order_id'                          => NULL,
                    'customer_id'                       => NULL,
                    'order_product_id'                  => NULL,
                    'product_id'                        => $dpo_detail->product_id,
                    'billed_desc'                       => $dpo_detail->billed_desc,
                    'is_billed'                         => $dpo_detail->is_billed,
                    'created_by'                        => $dpo_detail->created_by,
                    'pod_import_tax_book'               => $dpo_detail->pod_import_tax_book,
                    'pod_vat_actual'                    => $dpo_detail->pod_vat_actual,
                    'pod_unit_price'                    => $dpo_detail->pod_unit_price,
                    'pod_unit_price_with_vat'           => $dpo_detail->pod_unit_price_with_vat,
                    'last_updated_price_on'             => $dpo_detail->last_updated_price_on,
                    'pod_gross_weight'                  => $dpo_detail->pod_gross_weight,
                    'quantity'                          => $dpo_detail->quantity,
                    'pod_total_gross_weight'            => $dpo_detail->pod_total_gross_weight,
                    'pod_total_unit_price'              => $dpo_detail->pod_total_unit_price,
                    'pod_total_unit_price_with_vat'     => $dpo_detail->pod_total_unit_price_with_vat,
                    'discount'                          => $dpo_detail->discount,
                    'pod_import_tax_book_price'         => $dpo_detail->pod_import_tax_book_price,
                    'pod_vat_actual_price'              => $dpo_detail->pod_vat_actual_price,
                    'pod_vat_actual_price_in_thb'       => $dpo_detail->pod_vat_actual_price_in_thb,
                    'pod_vat_actual_total_price'        => $dpo_detail->pod_vat_actual_total_price,
                    'pod_vat_actual_total_price_in_thb' => $dpo_detail->pod_vat_actual_total_price_in_thb,
                    'warehouse_id'                      => $draft_po->to_warehouse_id,
                    'supplier_packaging'                => $dpo_detail->supplier_packaging,
                    'billed_unit_per_package'           => $dpo_detail->billed_unit_per_package,
                    'desired_qty'                       => $dpo_detail->desired_qty,
                    'currency_conversion_rate'          => $draft_po->exchange_rate,
                ]);
            }

            $draft_po_detail_note = DraftPurchaseOrderDetailNote::where('draft_po_id',$dpo_detail->id)->get();
            if($draft_po_detail_note->count() > 0)
            {
                foreach ($draft_po_detail_note as $value)
                {
                    $newNote = new OrderProductNote;
                    $newNote->pod_id           = $purchaseOrderDetail->id;
                    $newNote->note             = $value->note;
                    $newNote->save();
                    if($request->copy_and_update == 'yes')
                    {
                        // do nothing
                    }
                    else
                    {
                        $value->delete();
                    }
                }
            }

            $draftPurchaseOrderHistory = DraftPurchaseOrderHistory::with('product')->where('pod_id',$dpo_detail->id)->orderBy('id', 'asc')->get();
            foreach ($draftPurchaseOrderHistory as $value)
            {
                $PurchaseHistory = new PurchaseOrdersHistory;
                $PurchaseHistory->user_id           = $value->user_id;
                $PurchaseHistory->type               = 'PO';
                $PurchaseHistory->reference_number  = $value->product != null ? $value->product->refrence_code : 'Billed Item';
                $PurchaseHistory->column_name       = $value->column_name == 'QTY Inv' ? 'Quantity' : $value->column_name;
                $PurchaseHistory->old_value         = $value->old_value;
                $PurchaseHistory->new_value         = $value->new_value;
                $PurchaseHistory->po_id             = $purchaseOrder->id;
                $PurchaseHistory->pod_id             = $purchaseOrderDetail->id;
                $PurchaseHistory->save();

                if(!$request->copy_and_update == 'yes'){
                    $value->delete();
                }
            }
        }

        // getting documents of draft_Po
        $draft_po_docs = DraftPurchaseOrderDocument::where('po_id',$request->draft_po_id)->get();
        foreach ($draft_po_docs as $docs)
        {
            PurchaseOrderDocument::create([
                'po_id'     => $purchaseOrder->id,
                'file_name' => $docs->file_name
            ]);
        }

        $draft_notes = DraftPurchaseOrderNote::where('po_id',$request->draft_po_id)->get();
        if($draft_notes->count() > 0)
        {
            foreach ($draft_notes as $note)
            {
                $order_note = new PurchaseOrderNote;
                $order_note->po_id      = $purchaseOrder->id;
                $order_note->note       = $note->note;
                $order_note->created_by = @$note->created_by;
                $order_note->save();
            }
        }

        $delete_draft_po = DraftPurchaseOrder::find($request->draft_po_id);
        if($request->copy_and_update == 'yes')
        {
            $checkPoHistory = PurchaseOrdersHistory::where('type','DRAFT')->where('po_id',$delete_draft_po->id)->get();
            if($checkPoHistory->count() > 0)
            {

                foreach ($checkPoHistory as $value)
                {
                    $makeNew                   = new PurchaseOrdersHistory;
                    $makeNew->user_id          = $value->user_id;
                    $makeNew->type             = "PO";
                    $makeNew->reference_number = $value->reference_number;
                    $makeNew->new_value        = "Added Through Bulk Import";
                    $makeNew->po_id            = $purchaseOrder->id;
                    $makeNew->save();
                }
            }
        }
        else
        {
            $checkPoHistory = PurchaseOrdersHistory::where('type','DRAFT')->where('po_id',$delete_draft_po->id)->get();
            if($checkPoHistory->count() > 0)
            {
                foreach ($checkPoHistory as $value)
                {
                    $value->po_id = $purchaseOrder->id;
                    $value->type  = "PO";
                    $value->save();
                }
            }

            $delete_draft_po->draftPoDetail()->delete();
            $delete_draft_po->draft_po_notes()->delete();
            $delete_draft_po->delete();

            $delete_draft_po_docs = DraftPurchaseOrderDocument::where('po_id', $request->draft_po_id)->get();
            foreach ($delete_draft_po_docs as $del)
            {
                $del->delete();
            }
        }
        // --------------------------transfer history from Draftpurchase to PurchaserHistory---------
        $draftPurchaseOrderHistory = DraftPurchaseOrderHistory::with('product')->whereNull('pod_id')->where('po_id',$request->draft_po_id)->orderBy('id', 'asc')->get();
        foreach ($draftPurchaseOrderHistory as $value)
        {
            $PurchaseHistory = new PurchaseOrdersHistory;
            $PurchaseHistory->user_id           = $value->user_id;
            $PurchaseHistory->type               = 'PO';
            $PurchaseHistory->reference_number  = $value->product != null ? $value->product->refrence_code : 'Billed Item';
            $PurchaseHistory->column_name       = $value->column_name;
            $PurchaseHistory->old_value         = $value->old_value;
            $PurchaseHistory->new_value         = $value->new_value;
            $PurchaseHistory->po_id             = $purchaseOrder->id;
            $PurchaseHistory->save();

           if(!$request->copy_and_update == 'yes')
           {
                $value->delete();
           }
        }

        /*re-calulation through a function*/
        $objectCreated = new PurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($purchaseOrder->id);

        $errorMsg =  'Purchase Order Created';
        return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
	}

	public static function doActionDraftPo($request)
	{
        $action = $request->action;

        if($action == 'discard')
        {
        	return (new DraftPOInsertUpdateHelper)->DiscardDraftPO($request);
        }
        elseif($action == 'save')
        {
        	return (new DraftPOInsertUpdateHelper)->SaveDraftPO($request);
        }
	}

    public static function SaveDraftPoDates($request)
    {
        $draft_po = DraftPurchaseOrder::find($request->draft_po_id);
        $id = Auth::user()->id;

        foreach($request->except('draft_po_id') as $key => $value)
        {
            if($key == 'payment_due_date')
            {
                $date = str_replace("/","-",$value);
                $date =  date('Y-m-d',strtotime($date));
                $draft_po->$key = $date;
            }
            if($key == 'transfer_date')
            {
                $date = str_replace("/","-",$value);
                $date =  date('Y-m-d',strtotime($date));
                $draft_po->$key = $date;
            }
            if($key == 'invoice_date')
            {
                $value = str_replace("/","-",$value);
                $value =  date('Y-m-d',strtotime($value));
                $draft_po->$key = $value;
                if($draft_po->payment_terms_id !== null)
                {
                    $getCreditTerm = PaymentTerm::find($draft_po->payment_terms_id);
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

                    $draft_po->payment_due_date = $payment_due_date;
                }

            }
            if($key == 'note')
            {
                $draft_po->draft_po_notes()->updateOrCreate(['po_id'  => $draft_po->id],['note'=>$value],['created_by'=>$id]);
                return response()->json(['draft_note'=>true]);
            }
            if($key == 'memo')
            {
                $draft_po->$key = $value;
            }
            if($key == 'exchange_rate')
            {
                $old_value = 1/$draft_po->exchange_rate;
                DraftPurchaseOrderHistory::create([
                    'po_id' => $draft_po->id,
                    'reference_number' => '--',
                    'column_name' => 'Invoice Exchange Rate',
                    'old_value' => $old_value,
                    'new_value' => $request->exchange_rate,
                    'user_id'=> $id,

                ]);

                $exchange = (1 / $value);
                $draft_po->$key = $exchange;
            }
            if($key == 'target_receive_date')
            {
                $date = str_replace("/","-",$value);
                $date =  date('Y-m-d',strtotime($date));
                $draft_po->$key = $date;
            }
        }
        $draft_po->save();

        return response()->json(['success' => true, 'draft_note' => false, 'draft_po' => $draft_po]);
    }

    public static function addProdToPo($request)
    {
        // dd($request->all());
        $order = DraftPurchaseOrder::find($request->draft_po_id);
        $product_arr = explode(',', $request->selected_products);
        if($product_arr[0] == "") {
            return response()->json(['success' => false, 'message' => 'Select Product !!']);
        }
        foreach($product_arr as $product)
        {
            $product = Product::find($product);
            if($order->supplier_id != NULL && $order->from_warehouse_id == NULL)
            {
                $supplier_products = SupplierProducts::where('product_id',$product->id)->where('is_deleted',0)->where('supplier_id',$order->supplier_id)->count();
                if($supplier_products == 0)
                {
                    return response()->json(['success' => false, 'successmsg' => $order->getSupplier->reference_name.' do not provide us '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                }

                $add_products_to_po_detail = new DraftPurchaseOrderDetail;
                $add_products_to_po_detail->po_id                = $request->draft_po_id;
                $add_products_to_po_detail->pod_import_tax_book  = $product->import_tax_book;
                $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $product->vat : null;

                $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$order->supplier_id)->first();

                $add_products_to_po_detail->pod_unit_price          = @$gettingProdSuppData->buying_price;
                $add_products_to_po_detail->last_updated_price_on   = $product->last_price_updated_date;
                $add_products_to_po_detail->pod_gross_weight        = @$gettingProdSuppData->gross_weight;
                $add_products_to_po_detail->product_id              = $product->id;
                $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;

                /*vat calculations*/
                $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price,$request->purchasing_vat == null ? $product->vat : null);

                $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                /*convert val to thb's*/
                $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->draft_po_id, $vat_calculations['vat_amount']);
                $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                $add_products_to_po_detail->save();
            }
            if($order->from_warehouse_id != NULL && $order->supplier_id == NULL)
            {
                $warehouse_products = WarehouseProduct::where('product_id',$product->id)->where('warehouse_id',$order->from_warehouse_id)->count();

                if($warehouse_products == 0)
                {
                    return response()->json(['success' => false, 'successmsg' => $order->getFromWarehoue->warehouse_title.' dosent have '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                }

                $checkProduct = DraftPurchaseOrderDetail::where('po_id',$request->draft_po_id)->where('product_id',$product->id)->first();
                if($checkProduct)
                {
                    return response()->json(['success' => false, 'successmsg' => 'This product is already exist in this PO, please increase the quantity of that product']);
                }
                else
                {
                    $add_products_to_po_detail = new DraftPurchaseOrderDetail;
                    $add_products_to_po_detail->po_id = $request->draft_po_id;

                    $getImportTaxBook = Product::find($product->id);
                    $add_products_to_po_detail->pod_import_tax_book     = $getImportTaxBook->import_tax_book;
                    $add_products_to_po_detail->pod_vat_actual          = $request->purchasing_vat == null ? $getImportTaxBook->vat : null;
                    $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();

                    $add_products_to_po_detail->pod_unit_price          = $gettingProdSuppData->buying_price;
                    $add_products_to_po_detail->pod_gross_weight        = $gettingProdSuppData->gross_weight;
                    $add_products_to_po_detail->product_id              = $product->id;
                    $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                    $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;

                    /*vat calculations*/
                    $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price, $request->purchasing_vat == null ? $getImportTaxBook->vat : null);

                    $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                    $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                    /*convert val to thb's*/
                    $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->draft_po_id, $vat_calculations['vat_amount']);
                    $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                    $add_products_to_po_detail->save();
                }
            }
        }

        /*calulation through a function*/
        $objectCreated = new DraftPurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($request->draft_po_id);

        // Add history of Added Product
        foreach($request->except('draft_po_id') as $key => $value)
        {
            if($key == 'selected_products')
            {
                (new DraftPOInsertUpdateHelper)->MakeHistory($request->draft_po_id, $value, "Product", "New Added", '--');
            }
        }

        return response()->json([
            'success'    => true,
            'successmsg' => 'Product Added In PO Successfully ',
            'sub_total'  => $grandCalculations['sub_total'],
            'vat_amout'  => $grandCalculations['vat_amout'],
            'total_w_v'  => $grandCalculations['total_w_v'],
        ]);
    }
}
