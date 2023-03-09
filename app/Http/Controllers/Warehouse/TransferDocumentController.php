<?php

namespace App\Http\Controllers\Warehouse;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Common\PoGroup;
use App\Models\Common\Product;
use App\Models\Common\Spoilage;
use Illuminate\Support\Facades\DB;
use App\Helpers\PODetailCRUDHelper;
use App\Http\Controllers\Controller;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\TransferDocumentReservedQuantity;
use App\Helpers\DraftPOInsertUpdateHelper;
use App\Http\Controllers\Warehouse\HomeController;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Http\Controllers\Warehouse\PurchaseOrderGroupsController;
use App\Models\Common\PoGroupProductDetail;

class TransferDocumentController extends Controller
{
    public function getStockDataForTD(Request $request)
    {
        $required_qty = 0;
        $shipped_total_qty = 0;
        $inbound_po_ids = PurchaseOrder::where('status', 14)->where('to_warehouse_id', $request->warehouse_id)->pluck('id')->toArray();
        $inbound_po_details = PurchaseOrderDetail::with('PurchaseOrder.PoSupplier', 'product.sellingUnits')->where('product_id', $request->product_id)->where('is_billed', 'Product')->whereIn('po_id', $inbound_po_ids)->get();

        if ($request->has('is_draft')) {
            $total_qty_label = 'Total Reserved Qty';
        }
        if ($request->has('pi_side')) {
            $total_qty_label = 'Total Shipped Qty';
        }

        $custom_table_data = '<div class="col-md-12 mr-3 mt-2">
            <span class="pull-right">'.$total_qty_label.' : <span class="font-weight-bold" id="total_qty_span">0</span></span>
        </div>';
        $wh = WarehouseProduct::where('warehouse_id', $request->warehouse_id)->where('product_id', $request->product_id)->first();
        $stock_card = StockManagementIn::where('product_id', $request->product_id)->where('warehouse_id', $request->warehouse_id)->orderBy('expiration_date', 'DESC')->get();
        $product = Product::find($request->product_id);
        $decimal_places = $product->sellingUnits != null ? $product->sellingUnits->decimal_places : 3;


        $stock_ids = TransferDocumentReservedQuantity::where('po_id', $request->po_id)->where('pod_id', $request->pod_id)->whereNotNull('stock_id')->pluck('stock_id')->toArray();

        $group_conifrm = null;
        if ($request->is_draft == 'false') {
            $po_group = PoGroupDetail::where('purchase_order_id', $request->po_id)->orderBy('id', 'DESC')->first();
            $group_conifrm = $po_group != null ? $po_group->po_group->is_confirm : null;
        }


        $custom_table_data .= '<form id="save_qty_Form">
        <div class="bg-white table-responsive h-100" style="min-height: 235px;">
                <div class="">
                  <div class="">';
        $custom_table_data .= '</div>
                </div>';
        $custom_table_data .= '<table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
            <tbody>';
        if ($stock_card->count() > 0) {
            foreach ($stock_card as $card) {
                if ($wh->getWarehouse->id == $card->warehouse_id) {
                    $stock_out_in = DB::table('stock_management_outs')->where('smi_id', $card->id)->sum('quantity_in');
                    $stock_out_out = DB::table('stock_management_outs')->where('smi_id', $card->id)->sum('quantity_out');
                    // if (round(($stock_out_in + $stock_out_out), $decimal_places) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
                    if (round(($stock_out_in + $stock_out_out), $decimal_places) > 0) {
                        if ($request->is_draft == 'true'){
                            $stock_out = \App\Models\Common\StockManagementOut::with('supplier', 'stock_out_order', 'get_po_group', 'stock_out_po', 'stock_out_purchase_order_detail.PurchaseOrder')->where('smi_id', $card->id)->where('available_stock', '>', 0)->whereNotNull('quantity_in')->orderBy('id', 'DESC')->get();
                        }
                        else{
                            $stock_out = \App\Models\Common\StockManagementOut::with('supplier', 'stock_out_order', 'get_po_group', 'stock_out_po', 'stock_out_purchase_order_detail.PurchaseOrder', 'get_stock_in')->where('smi_id', $card->id)->where('available_stock', '>', 0)->whereNotNull('quantity_in')->orWhereIn('id', $stock_ids)->orderBy('id', 'DESC')->get();
                        }
                        if ($stock_out->count() > 0) {
                            $custom_table_data .= '<tr class="header">
                                    <th style="width: 25%;border: 1px solid #eee;">EXP:
                                    <span class="m-l-15 inputDoubleClickFirst" id="expiration_date"  data-fieldvalue="' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '') . '">' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '---') . '</span>
                                    </th>
                                    </tr>
                                    <tr class="ddddd" id="ddddd' . $card->id . '">
                                        <td colspan="6" style="padding: 0">
                                        <div style="max-height: 40vh;" class="tableFix  ml-2 mr-2">
                                        <table width="100%" class="dataTable stock_table table entriestable table-theme-header table-bordered" id="stock-detail-table' . $card->id . '" >
                                        <thead>
                                            <tr>
                                            <th width="30%">Supplier</th>';
                            $custom_table_data .= '<th width="15%">Current QTY</th>';
                            $custom_table_data .= '<th width="15%">Available QTY</th>';
                            $custom_table_data .= '<th width="15%">Reserved QTY</th>';
                            if ($request->has('pi_side')) {
                                $custom_table_data .= '<th width="15%">Shipped QTY</th>';
                                $custom_table_data .= '<th width="15%">Received QTY</th>';
                                $custom_table_data .= '<th width="20%">Spoilage</th>';
                                $custom_table_data .= '<th width="20%">Type</th>';
                            }
                            $custom_table_data .= '
                                            </tr>
                                        </thead>
                                        <tbody>';

                            foreach ($stock_out as $out) {
                                if ($out->get_stock_in->expiration_date == $card->expiration_date) {
                                    if ($request->is_draft == 'true') {
                                        $transfer_reserved = TransferDocumentReservedQuantity::where('draft_po_id', $request->po_id)->where('draft_pod_id', $request->pod_id)->where('stock_id', $out->id)->where('type', 'stock')->first();
                                    } else {
                                        $transfer_reserved = TransferDocumentReservedQuantity::where('po_id', $request->po_id)->where('pod_id', $request->pod_id)->where('stock_id', $out->id)->where('type', 'stock')->first();
                                    }
                                    $transfer_reserved_qty = $transfer_reserved != null ? $transfer_reserved->reserved_quantity : '';


                                    if ($transfer_reserved_qty != null) {
                                        $required_qty += $transfer_reserved_qty;
                                    }

                                    $custom_table_data .= '<tr>';

                                    $custom_table_data .= '<td width="30%">' . @$out->supplier->reference_name . ' (';
                                    if ($out->order_id !== null && $out->title == null && $out->stock_out_order) {
                                        $ret = $out->stock_out_order->get_order_number_and_link($out->stock_out_order);
                                        $ref_no = $ret[0];
                                        $link = $ret[1];
                                        $custom_table_data .= '<a target="_blank" href="' . route($link, ['id' => $out->stock_out_order->id]) . '" title="View Detail" class="">ORDER: ' . $ref_no . '</a>';
                                    } elseif ($out->po_group_id != null) {
                                        $custom_table_data .= '<a target="_blank" href="' . url('warehouse/warehouse-completed-receiving-queue-detail', $out->po_group_id) . '" class="" title="View Detail">SHIPMENT: ' . $out->get_po_group->ref_id . '</a>';
                                    } elseif ($out->p_o_d_id != null && $out->stock_out_po != null && $out->stock_out_po->status != 40) {
                                        $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : 'PO') . ' : ' . ($out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                    } elseif ($out->p_o_d_id != null && $out->stock_out_po != null && $out->stock_out_po->status != 40) {
                                        $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : 'PO') . ' : ' . ($out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                    } elseif ($out->p_o_d_id != null && $out->title == 'TD') {
                                        $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', @$out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : 'PO') . ' : ' . (@$out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                    } elseif ($out->p_o_d_id != null) {
                                        $custom_table_data .= '<b><span style="color:black;"><a target="_blank" href="' . url('get-purchase-order-detail', $out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : (@$out->stock_out_purchase_order_detail->PurchaseOrder->supplier_id == null ? 'TD' : 'PO')) . ':' . $out->stock_out_purchase_order_detail->PurchaseOrder->ref_id . '</span></a></b>';
                                        // $title = "PO:".$out->stock_out_purchase_order_detail->PurchaseOrder->ref_id  ;
                                    } elseif ($out->title != null) {
                                        $custom_table_data .= '<span class="m-l-15" id="title" data-fieldvalue="' . @$out->title . '">
                                            ' . (@$out->title != null ? $out->title : 'Select') . '
                                          </span>';
                                        if ($out->order_id != null) {
                                            if (@$out->stock_out_order->primary_status == 37) {
                                                $custom_table_data .= '<a target="_blank" href="' . route('get-completed-draft-invoices', ['id' => $out->stock_out_order->id]) . '" title="View Detail" class="font-weight-bold ml-3">ORDER# ' . @$out->stock_out_order->full_inv_no . '</a>';
                                            }
                                        }
                                        if ($out->po_id != null) {
                                            if (@$out->stock_out_po->status == 40) {
                                                $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $out->po_id) . '" title="View Detail" class="font-weight-bold ml-3">PO# ' . @$out->stock_out_po->ref_id . '</a>';
                                            }
                                        }
                                    } else {
                                        $custom_table_data .= 'Adjustment';
                                    }

                                    $custom_table_data .= ')</td>';

                                    $current_stock = $request->is_draft == 'false' ? $out->available_stock + ($transfer_reserved_qty != '' ? (float)$transfer_reserved_qty : 0) : $out->available_stock;
                                    $custom_table_data .= '<td width="15%">' . number_format($current_stock, $decimal_places, '.', '') . '</td>';

                                    $custom_table_data .= '<td width="15%">' . number_format($out->available_stock, $decimal_places, '.', '') . '</td>';

                                    $transfer_reserved_qty = $transfer_reserved_qty != null ? number_format((float)$transfer_reserved_qty, $decimal_places, '.', '') : '';

                                    $readonly = '';
                                    if ($group_conifrm != null && $group_conifrm == 1) {
                                        $readonly = 'readonly';
                                    }
                                    $custom_table_data .= '<td width="15%"><input type="number" name="qty[]" style="width:70%;" data-id="' . $out->id . '" value="' . $transfer_reserved_qty . '" class="input_required_qty" step="any" '.$readonly.'>
                                    </td>';

                                    if ($request->has('pi_side')) {
                                        $qty_shipped = $transfer_reserved != null &&$transfer_reserved->qty_received != null ? $transfer_reserved->qty_received : ($transfer_reserved != null ? $transfer_reserved->qty_shipped : '');

                                        $qty_received = $transfer_reserved != null ? $transfer_reserved->qty_received : '';

                                        $spoilage = $transfer_reserved != null ? $transfer_reserved->spoilage : '';

                                        $spoilage_type = $transfer_reserved != null && $transfer_reserved->spoilage_table != null ? $transfer_reserved->spoilage_table->title : '';

                                        $custom_table_data .= '<td width="15%"><input type="number" name="qty_shipped[]" style="width:70%;" data-id="' . $out->id . '" value="' . $qty_shipped . '" class="input_shipped_qty" step="any" '.$readonly.'>
                                        </td>';
                                        $custom_table_data .= '<td width="15%">' . $qty_received . '
                                        </td>';
                                        $custom_table_data .= '<td width="20%">' . $spoilage . '
                                        </td>';
                                        $custom_table_data .= '<td width="20%">' . $spoilage_type . '
                                        </td>';

                                        if ($qty_shipped != null) {
                                            $shipped_total_qty += $qty_shipped;
                                        }
                                    }

                                    $custom_table_data .= '<input type="hidden" name="stock_id[]" value ="' . $out->id . '">';
                                    $custom_table_data .= '</tr>';
                                }
                            }
                            $custom_table_data .= '</tbody>
                                </table>
                                </div>';
                            $custom_table_data .= '</td>
                                </tr>';
                        }
                    }
                }
            }
            $custom_table_data .= '<tr class="header"></tr>';
        }
        if ($inbound_po_details->count() > 0) {
            $custom_table_data .= '<tr class="header">
                                    <th style="width: 25%;border: 1px solid #eee;">InBound POs
                                    </th>
                                    </tr>';
            $custom_table_data .= '<tr class="ddddd">
                        <td colspan="6" style="padding: 0">
                        <div style="max-height: 40vh;" class="tableFix  ml-2 mr-2">
                        <table width="100%" class="dataTable stock_table table entriestable table-theme-header table-bordered">
                        <thead>
                            <tr>';
            if ($request->has('pi_side')) {
                $custom_table_data .= '<th width="34%">Supplier</th>';
            }
            else{
                $custom_table_data .= '<th width="30%">Supplier</th>';
            }
            $custom_table_data .= '<th width="15%">Current QTY</th>';
            $custom_table_data .= '<th width="15%">Available QTY</th>';
            $custom_table_data .= '<th width="15%">Reserved QTY</th>';
            if ($request->has('pi_side')) {
                $custom_table_data .= '<th width="15%">Shipped QTY</th>';
                $custom_table_data .= '<th width="15%">Received QTY</th>';
                $custom_table_data .= '<th width="20%">Spoilage</th>';
                $custom_table_data .= '<th width="20%">Type</th>';
            }
            $custom_table_data .= '
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($inbound_po_details as $detail) {
                if ($request->is_draft == 'true') {
                    $transfer_reserved = TransferDocumentReservedQuantity::where('draft_po_id', $request->po_id)->where('draft_pod_id', $request->pod_id)->where('inbound_pod_id', $detail->id)->where('type', 'inbound')->first();
                } else {
                    $transfer_reserved = TransferDocumentReservedQuantity::where('po_id', $request->po_id)->where('inbound_pod_id', $detail->id)->where('pod_id', $request->pod_id)->where('type', 'inbound')->first();
                }
                $custom_table_data .= '<tr>';
                if ($request->has('pi_side')){
                    $custom_table_data .= '<td width="34%">'.$detail->PurchaseOrder->PoSupplier->reference_name;
                }
                else{
                    $custom_table_data .= '<td width="30%">'.$detail->PurchaseOrder->PoSupplier->reference_name;
                }
                $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $detail->PurchaseOrder->id) . '" class="" title="View Detail"> (PO: ' . $detail->PurchaseOrder->ref_id . ')</a>';
                $custom_table_data .='</td>';

                $transfer_reserved_qty = $transfer_reserved != null ? $transfer_reserved->reserved_quantity : null;
                if ($transfer_reserved_qty != null) {
                    $required_qty += $transfer_reserved_qty;
                }
                $transfer_reserved_qty = $transfer_reserved_qty != null ? number_format((float)$transfer_reserved_qty, 3, '.', '') : '';

                $detail_reserved_qty = $detail->reserved_qty != null ? $detail->reserved_qty : 0;
                $unit_conversion_rate = $detail->product->unit_conversion_rate;
                $unit_conversion_rate = $unit_conversion_rate != 0 ? $unit_conversion_rate : 1;
                $decimal_places = $detail->product->sellingUnits->decimal_places;

                $custom_table_data .= '<td width="15%">'. round($detail->quantity / $unit_conversion_rate, $decimal_places) .'</td>';
                $custom_table_data .= '<td width="15%">'. round(($detail->quantity - $detail_reserved_qty) / $unit_conversion_rate, $decimal_places) .'</td>';

                $readonly = '';
                if ($group_conifrm != null && $group_conifrm == 1) {
                    $readonly = 'readonly';
                }

                $custom_table_data .= '<td width="15%"><input type="number" name="qty[]" style="width:70%;" value="' . $transfer_reserved_qty . '" class="input_required_qty" step="any" '.$readonly.'>
                </td>';

                if ($request->has('pi_side')) {
                    $qty_shipped = $transfer_reserved != null &&$transfer_reserved->qty_received != null ? $transfer_reserved->qty_received : ($transfer_reserved != null ? $transfer_reserved->qty_shipped : '');

                    $qty_received = $transfer_reserved != null ? $transfer_reserved->qty_received : '';

                    $spoilage = $transfer_reserved != null ? $transfer_reserved->spoilage : '';

                    $spoilage_type = $transfer_reserved != null && $transfer_reserved->spoilage_table != null ? $transfer_reserved->spoilage_table->title : '';

                    $custom_table_data .= '<td width="15%"><input type="number" name="qty_shipped[]" style="width:70%;" value="' . $qty_shipped . '" class="input_shipped_qty" step="any" '.$readonly.'>
                    </td>';
                    $custom_table_data .= '<td width="15%">' . $qty_received . '
                    </td>';
                    $custom_table_data .= '<td width="20%">' . $spoilage . '
                    </td>';
                    $custom_table_data .= '<td width="20%">' . $spoilage_type . '
                    </td>';

                    if ($qty_shipped != null) {
                        $shipped_total_qty += $qty_shipped;
                    }
                }

                $custom_table_data .= '<input type="hidden" name="inbound_pod_id[]" style="width:70%;" value="' . $detail->id . '"';
                $custom_table_data .= '</tr>';
            }
        }
        // else if ($stock_card->count() < 0 && $inbound_po_details->count() < 0){
        if ($stock_card->count() < 0){
            $custom_table_data .= '<tr>
                  <td align="center">No Data Found!!!</td>
                </tr>';
        }
        $custom_table_data .= '</tbody>
            </table>';
        if ($stock_card->count() > 0 || $inbound_po_details->count() > 0) {
            $custom_table_data .= '<div class="pull-right mr-3 mt-2 mb-2">
                    <button type="submit" class="btn btn-primary" id="save_qty_in_reserved_table">Save Quantity</button>
                </div>';
        }
        $custom_table_data .= '</div>
        <input type="hidden" name="pod_id" value="' . $request->pod_id . '">
        </form>';

        return response()->json(['success' => true, 'html' => $custom_table_data, 'total_qty' => $required_qty, 'shipped_total_qty' => $shipped_total_qty]);
    }

    public function getReservedStockDataForTD(Request $request)
    {
        $required_qty = 0;
        $custom_table_data = '<div class="col-md-12 mr-3 mt-2">
            <span class="pull-right">Total Qty : <span class="font-weight-bold" id="total_qty_span">0</span></span>
         </div>';
        $custom_table_data = '<div class="col-md-12 mr-3 mt-2">
        </div>';
        $td = PurchaseOrder::find($request->po_id);
        $wh = WarehouseProduct::where('warehouse_id', $request->warehouse_id)->where('product_id', $request->product_id)->first();

        $po_group_id = PoGroupDetail::where('purchase_order_id', $request->po_id)->first()->po_group_id;

        $po_group_confirm = PoGroup::find($po_group_id)->is_confirm;

        $reserved = TransferDocumentReservedQuantity::where('po_id', $request->po_id)->where('pod_id', $request->pod_id)->get();


        foreach ($reserved as $reserve) {
            if ($reserve->reserved_quantity != null) {
                $stock_m_out = StockManagementOut::with('supplier', 'stock_out_order', 'get_po_group', 'stock_out_po', 'stock_out_purchase_order_detail.PurchaseOrder')->find($reserve->stock_id);
                if ($stock_m_out) {
                    $card = StockManagementIn::find($stock_m_out->smi_id);
                    $custom_table_data .= '<form id="save_qty_Form" class="mb-2">
                    <div class="bg-white table-responsive" style="min-height: 150px;">
                            <div class="">
                            <div class="">';
                    $custom_table_data .= '</div>
                            </div>';
                    $custom_table_data .= '<table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
                        <tbody>';
                    if ($card) {
                        if ($wh->getWarehouse->id == $card->warehouse_id) {
                            $custom_table_data .= '<tr class="header">
                                        <th style="width: 25%;border: 1px solid #eee;">EXP:
                                        <span class="m-l-15 inputDoubleClickFirst" id="expiration_date"  data-fieldvalue="' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '') . '">' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '---') . '</span>
                                        </th>
                                        </tr>
                                        <tr class="ddddd" id="ddddd' . $card->id . '">
                                            <td colspan="6" style="padding: 0">
                                            <div style="max-height: 40vh;" class="tableFix  ml-2 mr-2">
                                            <table width="100%" class="dataTable stock_table table entriestable table-theme-header table-bordered" id="stock-detail-table' . $card->id . '" >
                                            <thead>
                                                <tr>
                                                <th width="30%">Supplier</th>';
                            if ($request->has('receiving_side')){
                                $custom_table_data .= '<th width="20%">QTY Ordered</th>';
                            }
                            if ($request->has('pi_side')) {
                                $custom_table_data .= '<th width="20%">QTY Ordered</th>';
                            }
                            $custom_table_data .= '<th width="20%">QTY Shipped</th>';
                            // if ($request->has('receiving_side') ) {
                                $custom_table_data .= '<th width="20%">QTY Received</th>';
                                $custom_table_data .= '<th width="20%">QTY in Spoilage</th>';
                                $custom_table_data .= '<th width="20%">Spoilage Type</th>';
                            // }
                            $custom_table_data .= '
                                                </tr>
                                            </thead>
                                            <tbody>';

                                $transfer_reserved_qty = $reserve != null ? $reserve->reserved_quantity : '';

                                $transfer_shipped_qty = $reserve != null ? $reserve->qty_shipped : '';

                                $transfer_received_qty = $reserve != null ? $reserve->qty_received : '';

                                $transfer_spoilage_qty = $reserve != null ? $reserve->spoilage : '';

                                $transfer_spoilage_type = $reserve != null ? $reserve->spoilage_type : '';

                                if ($transfer_shipped_qty != null) {
                                    $required_qty += $transfer_shipped_qty;
                                }
                                if ($transfer_received_qty != null && $transfer_spoilage_qty != null) {
                                    $required_qty += $transfer_received_qty + $transfer_shipped_qty;
                                }

                                $custom_table_data .= '<tr>';

                                $supplier = $stock_m_out->supplier != null ? $stock_m_out->supplier->reference_name : '';
                                $custom_table_data .= '<td width="30%">' . $supplier . ' (';
                                if ($stock_m_out->po_group_id != null) {
                                    $custom_table_data .= '<a target="_blank" href="' . url('warehouse/warehouse-completed-receiving-queue-detail', $stock_m_out->po_group_id) . '" class="" title="View Detail">SHIPMENT: ' . $stock_m_out->get_po_group->ref_id . '</a>';
                                } elseif ($stock_m_out->p_o_d_id != null && $stock_m_out->stock_out_po != null && $stock_m_out->stock_out_po->status != 40) {
                                    $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($stock_m_out->title != null ? $stock_m_out->title : 'PO') . ' : ' . ($stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                } elseif ($stock_m_out->p_o_d_id != null && $stock_m_out->stock_out_po != null && $stock_m_out->stock_out_po->status != 40) {
                                    $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($stock_m_out->title != null ? $stock_m_out->title : 'PO') . ' : ' . ($stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                } elseif ($stock_m_out->p_o_d_id != null && $stock_m_out->title == 'TD') {
                                    $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', @$stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($stock_m_out->title != null ? $stock_m_out->title : 'PO') . ' : ' . (@$stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                } elseif ($stock_m_out->p_o_d_id != null) {
                                    $custom_table_data .= '<b><span style="color:black;"><a target="_blank" href="' . url('get-purchase-order-detail', $stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($stock_m_out->title != null ? $stock_m_out->title : (@$stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->supplier_id == null ? 'TD' : 'PO')) . ':' . $stock_m_out->stock_out_purchase_order_detail->PurchaseOrder->ref_id . '</span></a></b>';
                                } elseif ($stock_m_out->title != null) {
                                    $custom_table_data .= '<span class="m-l-15" id="title" data-fieldvalue="' . @$stock_m_out->title . '">
                                            ' . (@$stock_m_out->title != null ? $stock_m_out->title : 'Select') . '
                                            </span>';
                                    if ($stock_m_out->order_id != null) {
                                        if (@$stock_m_out->stock_out_order->primary_status == 37) {
                                            $custom_table_data .= '<a target="_blank" href="' . route('get-completed-draft-invoices', ['id' => $stock_m_out->stock_out_order->id]) . '" title="View Detail" class="font-weight-bold ml-3">ORDER# ' . @$stock_m_out->stock_out_order->full_inv_no . '</a>';
                                        }
                                    }
                                    if ($stock_m_out->po_id != null) {
                                        if (@$stock_m_out->stock_out_po->status == 40) {
                                            $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $stock_m_out->po_id) . '" title="View Detail" class="font-weight-bold ml-3">PO# ' . @$stock_m_out->stock_out_po->ref_id . '</a>';
                                        }
                                    }
                                } else {
                                    $custom_table_data .= 'Adjustment';
                                }

                                $custom_table_data .= ')</td>';

                                if ($request->has('receiving_side')){
                                    $custom_table_data .= '<th width="20%">'.$reserve->reserved_quantity.'</th>';
                                }
                                $transfer_reserved_qty = $transfer_reserved_qty != null ? number_format((float)$transfer_reserved_qty, 3, '.', '') : '';

                                $transfer_shipped_qty = $transfer_shipped_qty != null ? number_format((float)$transfer_shipped_qty, 3, '.', '') : '';

                                $transfer_received_qty = $transfer_received_qty != null ? number_format((float)$transfer_received_qty, 3, '.', '') : '';

                                $transfer_spoilage_qty = $transfer_spoilage_qty != null ? number_format((float)$transfer_spoilage_qty, 3, '.', '') : '';

                                if ($request->has('pi_side')) {
                                    $custom_table_data .= '<td width="20%"><input type="number" name="qty[]" style="width:70%;" data-id="' . $stock_m_out->id . '" value="' . $transfer_reserved_qty . '" class="input_required_qty" readonly>
                                    </td>';
                                }

                                $readOnly = '';
                                if ($request->has('receiving_side'))
                                {
                                    $readOnly = 'readonly disabled';
                                }
                                $custom_table_data .= '<td width="20%"><input type="number" name="qty_shipped[]" style="width:70%;" data-id="' . $stock_m_out->id . '" value="' . $transfer_shipped_qty . '" class="input_shipped_qty" '.$readOnly.' step="any">
                                </td>';

                                if ($request->has('pi_side')) {
                                    if ($po_group_confirm == 1) {
                                        $spoilage_type = $transfer_spoilage_type != null ? $reserve->spoilage_table->title : '--';
                                        $custom_table_data .= '<td width="20%">'.$transfer_received_qty.'</td>';
                                        $custom_table_data .= '<td width="20%">'.$transfer_spoilage_qty.'</td>';
                                        $custom_table_data .= '<td width="20%">'.$spoilage_type.'</td>';
                                    }
                                }

                                if ($request->has('receiving_side')) {
                                    $disbaled = $po_group_confirm == 1 ? 'disabled' : '';

                                    $custom_table_data .= '<td width="20%"><input type="number" name="qty_received[]" style="width:70%;" data-id="' . $stock_m_out->id . '" value="' . $transfer_received_qty . '" class="input_received_qty" '.$disbaled.' step="any">
                                    </td>';
                                    $custom_table_data .= '<td width="20%"><input type="number" name="qty_spoilage[]" style="width:70%;" data-id="' . $stock_m_out->id . '" value="' . $transfer_spoilage_qty . '" class="input_spoilage_qty" '.$disbaled.' step="any">
                                    </td>';

                                    $spoilages = Spoilage::all();

                                    $custom_table_data .= '<td width="20%">
                                    <select name="spoilage_type[]" data-id="' . $stock_m_out->id . '" '.$disbaled.'>';
                                    $custom_table_data .= '<option value="">Choose Spoilage Type</option>';

                                    foreach ($spoilages as $spoilage){
                                        $selected = $spoilage->id == $transfer_spoilage_type ? 'selected' : '';
                                        $custom_table_data .= '<option value="'.$spoilage->id.'" '.$selected.'>'.$spoilage->title.'</option>';
                                    }
                                    $custom_table_data .= '</select>
                                    </td>';
                                }

                                $custom_table_data .= '<input type="hidden" name="stock_id[]" value ="' . $stock_m_out->id . '">';
                                $custom_table_data .= '</tr>';
                                $custom_table_data .= '</tbody>
                                        </table>
                                        </div>';
                                $custom_table_data .= '</td>
                                        </tr>';
                        }
                        $custom_table_data .= '<tr class="header"></tr>';
                    }
                }
                $detail = PurchaseOrderDetail::with('PurchaseOrder.PoSupplier')->find($reserve->inbound_pod_id);
                if ($detail) {
                    $custom_table_data .= '<form id="save_qty_Form">
                    <div class="bg-white table-responsive h-100" style="min-height: 235px;">
                            <div class="">
                              <div class="">';
                    $custom_table_data .= '</div>
                            </div>';
                    $custom_table_data .= '<table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
                        <tbody>';
                    $custom_table_data .= '<tr class="header">
                                            <th style="width: 25%;border: 1px solid #eee;">InBound POs
                                            </th>
                                            </tr>';
                    $custom_table_data .= '<tr class="ddddd">
                                <td colspan="6" style="padding: 0">
                                <div style="max-height: 40vh;" class="tableFix  ml-2 mr-2">
                                <table width="100%" class="dataTable stock_table table entriestable table-theme-header table-bordered">
                                <thead>
                                    <tr>
                                    <th width="30%">Supplier</th>';
                    if ($request->has('receiving_side')){
                        $custom_table_data .= '<th width="20%">QTY Ordered</th>';
                    }
                    if ($request->has('pi_side')) {
                        $custom_table_data .= '<th width="20%">QTY Ordered</th>';
                    }
                    $custom_table_data .= '<th width="20%">QTY Shipped</th>';
                    // if ($request->has('receiving_side')) {
                        $custom_table_data .= '<th width="20%">QTY Received</th>';
                        $custom_table_data .= '<th width="20%">QTY in Spoilage</th>';
                        $custom_table_data .= '<th width="20%">Spoilage Type</th>';
                    // }
                    $custom_table_data .= '
                                    </tr>
                                </thead>
                                <tbody>';

                    if ($request->is_draft == 'true') {
                        $transfer_reserved = TransferDocumentReservedQuantity::where('draft_po_id', $request->po_id)->where('draft_pod_id', $request->pod_id)->where('type', 'inbound')->first();
                    } else {
                        $transfer_reserved = TransferDocumentReservedQuantity::where('po_id', $request->po_id)->where('pod_id', $request->pod_id)->where('type', 'inbound')->where('inbound_pod_id', $reserve->inbound_pod_id)->first();
                    }
                    $custom_table_data .= '<tr>';
                    $custom_table_data .= '<td width="20%">'.$detail->PurchaseOrder->PoSupplier->reference_name;
                    $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $detail->PurchaseOrder->id) . '" class="" title="View Detail"> (PO: ' . $detail->PurchaseOrder->ref_id . ')</a>';
                    $custom_table_data .='</td>';

                    if ($request->has('receiving_side')){
                        $custom_table_data .= '<th width="20%">'.$transfer_reserved->reserved_quantity.'</th>';
                    }

                    $transfer_reserved_qty = $transfer_reserved != null ? $transfer_reserved->reserved_quantity : '';

                    $transfer_shipped_qty = $transfer_reserved != null ? $transfer_reserved->qty_shipped : '';

                    $transfer_received_qty = $transfer_reserved != null ? $transfer_reserved->qty_received : '';

                    $transfer_spoilage_qty = $transfer_reserved != null ? $transfer_reserved->spoilage : '';

                    $transfer_spoilage_type = $transfer_reserved != null ? $transfer_reserved->spoilage_type : '';


                    $transfer_shipped_qty = $transfer_shipped_qty != null ? number_format((float)$transfer_shipped_qty, 3, '.', '') : '';

                    $transfer_received_qty = $transfer_received_qty != null ? number_format((float)$transfer_received_qty, 3, '.', '') : '';

                    $transfer_reserved_qty = $transfer_reserved_qty != null ? number_format((float)$transfer_reserved_qty, 3, '.', '') : '';

                    if ($request->has('pi_side')) {
                        $custom_table_data .= '<td width="20%"><input type="number" name="qty[]" style="width:70%;" value="' . $transfer_reserved_qty . '" class="input_required_qty" readonly>
                        </td>';
                    }


                    $readOnly = '';
                    if ($request->has('receiving_side'))
                    {
                        $readOnly = 'readonly disabled';
                    }
                    $custom_table_data .= '<td width="20%"><input type="number" name="qty_shipped[]" style="width:70%;" value="' . $transfer_shipped_qty . '" class="input_shipped_qty" '.$readOnly.'>
                    </td>';

                    if ($request->has('pi_side')) {
                        if ($po_group_confirm == 1) {
                            $spoilage_type = $transfer_spoilage_type != null ? $transfer_reserved->spoilage_table->title : '--';
                            $custom_table_data .= '<td width="20%">'.$transfer_received_qty.'</td>';
                            $custom_table_data .= '<td width="20%">'.$transfer_spoilage_qty.'</td>';
                            $custom_table_data .= '<td width="20%">'.$spoilage_type.'</td>';
                        }
                    }

                    if ($request->has('receiving_side')) {
                        $disbaled = $po_group_confirm == 1 ? 'disabled' : '';

                        $custom_table_data .= '<td width="20%"><input type="number" name="qty_received[]" style="width:70%;" value="' . $transfer_received_qty . '" class="input_received_qty" '.$disbaled.'>
                        </td>';

                        $custom_table_data .= '<td width="20%"><input type="number" name="qty_spoilage[]" style="width:70%;" value="' . $transfer_spoilage_qty . '" class="input_spoilage_qty" '.$disbaled.'>
                                            </td>';

                        $spoilages = Spoilage::all();

                        $custom_table_data .= '<td width="20%">
                        <select name="spoilage_type[]" '.$disbaled.'>';
                        $custom_table_data .= '<option value="">Choose Spoilage Type</option>';

                        foreach ($spoilages as $spoilage){
                            $selected = $spoilage->id == $transfer_spoilage_type ? 'selected' : '';
                            $custom_table_data .= '<option value="'.$spoilage->id.'" '.$selected.'>'.$spoilage->title.'</option>';
                        }
                        $custom_table_data .= '</select>
                        </td>';
                    }
                    $custom_table_data .= '<input type="hidden" name="inbound_pod_id[]" style="width:70%;" value="' . $detail->id . '"';
                    $custom_table_data .= '</tr>';
                }
                else if ($stock_m_out != null && $detail != null){
                    $custom_table_data .= '<tr>
                            <td align="center">No Data Found!!!</td>
                            </tr>';
                }
                $custom_table_data .= '</tbody>
                        </table>';
            }
        }
        // if ($reserved && (($po_group_confirm == 0 && ($td->status == 22 || $td->status == 21)) || ($po_group_confirm == 1 && $td->status != 22)))
        if ($reserved && $po_group_confirm == 0)
        {
            $custom_table_data .= '<div class="pull-right mr-3 mt-2 mb-2">
                        <button type="submit" class="btn btn-primary" id="save_qty_in_reserved_table">Save Quantity</button>
                    </div>';
        }
        $custom_table_data .= '</div>
            <input type="hidden" name="pod_id" value="' . $request->pod_id . '">
            <input type="hidden" name="po_id" value="' . $request->po_id . '">
            </form>';
        return response()->json(['success' => true, 'html' => $custom_table_data, 'total_qty' => $required_qty]);
    }

    public function saveAvailableStockOfProductInTd(Request $request)
    {
        $i = 0;
        $qty_ordered = 0;
        $qty_shipped = 0;
        $qty_received = 0;

        if ($request->has('stock_id')) {
            foreach ($request->stock_id as $stock_id) {
                if ($request->is_draft == 'true') {
                    $reserved = TransferDocumentReservedQuantity::where('stock_id', $stock_id)->where('draft_po_id', $request->po_id)->where('draft_pod_id', $request->pod_id)->first();
                } else {
                    $reserved = TransferDocumentReservedQuantity::where('stock_id', $stock_id)->where('po_id', $request->po_id)->where('pod_id', $request->pod_id)->first();
                }

                if ($request->has('is_draft')){
                    $stock_out = StockManagementOut::find($stock_id);
                    if ( ($reserved && $request->qty[$i] == $reserved->reserved_quantity) || ($stock_out && $stock_out->available_stock >= $request->qty[$i]) )
                    {
                        if ($request->qty[$i] != null && (float)$request->qty[$i] > 0) {
                            if ($reserved == null) {
                                $reserved = new TransferDocumentReservedQuantity();
                                if ($request->is_draft == 'true') {
                                    $reserved->draft_po_id = $request->po_id;
                                    $reserved->draft_pod_id = $request->pod_id;
                                } else {
                                    $reserved->po_id = $request->po_id;
                                    $reserved->pod_id = $request->pod_id;
                                }
                                $reserved->stock_id = $stock_id;
                                $reserved->type = 'stock';
                            }
                            $reserved->old_qty = $reserved->reserved_quantity;
                            $reserved->reserved_quantity = $request->qty[$i];
                            $reserved->save();
                            $qty_ordered += $reserved->reserved_quantity;
                        }
                        else {
                            if ($reserved != null && $request->is_draft == 'false' || ($reserved != null && $request->qty_shipped[$i] == null && $request->qty[$i] == null)) {
                                $stock_m_out = StockManagementOut::find($stock_id);
                                $stock_m_out->available_stock += $reserved->reserved_quantity;
                                $stock_m_out->save();

                                $reserved->old_qty = $reserved->reserved_quantity;
                                $reserved->reserved_quantity = $request->qty[$i];
                                $reserved->save();
                                // $reserved->delete();
                            }
                            elseif ($request->qty_shipped[$i] != null && $request->qty[$i] == null) {
                                return response()->json(['success' => false, 'message' => 'Reserved Qty must be filled if Shipped QTY is filled']);
                            }
                        }
                    }
                    else{
                        return response()->json(['success' => false, 'message' => 'Reserved Qty must be less then or equal to Available Qty']);
                    }
                }
                if ($request->has('pi_side') && $reserved != null) {
                    if ($reserved->qty_received != null) {
                        $total = $reserved->qty_received + $reserved->spoilage;
                        if ($request->qty_shipped[$i] == (float)$total) {
                            $reserved->qty_shipped = $reserved->qty_received;
                            $reserved->save();
                            $qty_shipped += (Float)$reserved->qty_shipped;
                        }
                        else{
                            return response()->json(['success' => false, 'message' => 'Qty Shipped must be equal to Qty Received + Qty in Spoilage']);
                        }
                    }
                    else if ($request->qty_shipped[$i] != null && (float)$request->qty_shipped[$i] > 0) {
                        $reserved->old_qty_shipped = $reserved->qty_shipped;
                        $reserved->qty_shipped = $request->qty_shipped[$i];
                        $reserved->save();
                        $qty_shipped += (Float)$reserved->qty_shipped;
                    }
                    else{
                        if ($request->is_draft == 'false' && $reserved->qty_shipped == null && $reserved->old_qty_shipped != null) {
                            $stock_m_out = StockManagementOut::find($stock_id);
                            $stock_m_out->available_stock += $reserved->reserved_quantity;
                            $stock_m_out->save();
                            $reserved->delete();
                        }
                    }
                }
                if ($request->has('receiving_side') && $reserved != null) {
                    if ($request->qty_received[$i] != null && (float)$request->qty_received[$i] > 0) {
                        $total = (Float)$request->qty_received[$i] + (float)$request->qty_spoilage[$i];
                        if ($reserved->qty_shipped != null) {
                            if ($total == (float)$reserved->qty_shipped) {
                                $reserved->qty_received = (float)$request->qty_received[$i];
                                $reserved->spoilage = (float)$request->qty_spoilage[$i];
                                $reserved->spoilage_type = $request->spoilage_type[$i];
                                $reserved->save();
                                // $qty_received += (Float)$request->qty_received[$i] + (float)$request->qty_spoilage[$i];
                                $qty_received += (Float)$request->qty_received[$i];
                            }else{
                                return response()->json(['success' => false, 'message' => 'Qty Received + Qty in Spoilage must be equal to Qty Shipped']);
                            }
                        }
                        else{
                            if ($total == (float)$reserved->reserved_quantity) {
                                $reserved->qty_received = (float)$request->qty_received[$i];
                                $reserved->spoilage = (float)$request->qty_spoilage[$i];
                                $reserved->spoilage_type = $request->spoilage_type[$i];
                                $reserved->save();
                                // $qty_received += (Float)$request->qty_received[$i] + (float)$request->qty_spoilage[$i];
                                $qty_received += (Float)$request->qty_received[$i];
                            }else{
                                return response()->json(['success' => false, 'message' => 'Qty Received + Qty in Spoilage must be equal to Order Qty']);
                            }
                        }
                    }
                    else {
                        return response()->json(['success' => false, 'message' => 'Qty Received must not be empty']);
                    }
                }
                $i++;
            }
        }

        if ($request->has('inbound_pod_id')) {
            foreach ($request->inbound_pod_id as $inbound_pod_id) {
                if ($request->is_draft == 'true') {
                    $reserved = TransferDocumentReservedQuantity::where('inbound_pod_id', $inbound_pod_id)->where('draft_po_id', $request->po_id)->where('draft_pod_id', $request->pod_id)->where('type', 'inbound')->first();
                } else {
                    $reserved = TransferDocumentReservedQuantity::where('inbound_pod_id', $inbound_pod_id)->where('po_id', $request->po_id)->where('pod_id', $request->pod_id)->where('type', 'inbound')->first();
                }

                if ($request->has('is_draft')){
                    $po_detail = PurchaseOrderDetail::find($inbound_pod_id);
                    $unit_conversion_rate = $po_detail->product->unit_conversion_rate;
                    $unit_conversion_rate = $unit_conversion_rate != 0 ? $unit_conversion_rate : 1;

                    $pod_reseved_qty = ($po_detail->quantity - $po_detail->reserved_qty) / $unit_conversion_rate;
                    if (($reserved && $reserved->qty_received != null) || ($po_detail && $pod_reseved_qty >= $request->qty[$i])) {
                        if ($request->qty[$i] != null && (float)$request->qty[$i] > 0) {
                            if ($reserved == null) {
                                $reserved = new TransferDocumentReservedQuantity();
                                if ($request->is_draft == 'true') {
                                    $reserved->draft_po_id = $request->po_id;
                                    $reserved->draft_pod_id = $request->pod_id;
                                } else {
                                    $reserved->po_id = $request->po_id;
                                    $reserved->pod_id = $request->pod_id;
                                }
                                $reserved->inbound_pod_id = $inbound_pod_id;
                                $reserved->type = 'inbound';
                            }
                            $reserved->old_qty = $reserved->reserved_quantity;
                            $reserved->reserved_quantity = $request->qty[$i];
                            $reserved->save();
                            $qty_ordered += $reserved->reserved_quantity;
                        }
                        else {
                            if ($reserved != null && $request->is_draft == 'false' || ($reserved != null && $request->qty_shipped[$i] == null && $request->qty[$i] == null)) {
                                $po_detail->reserved_qty -= ($reserved->reserved_quantity / $unit_conversion_rate);
                                $po_detail->save();

                                $reserved->old_qty = $reserved->reserved_quantity;
                                $reserved->reserved_quantity = $request->qty[$i];
                                $reserved->save();
                                // $reserved->delete();
                            }
                            elseif ($request->qty_shipped[$i] != null && $request->qty[$i] == null) {
                                return response()->json(['success' => false, 'message' => 'Reserved Qty must be filled if Shipped QTY is filled']);
                            }
                        }
                    }
                    else{
                        return response()->json(['success' => false, 'message' => 'Reserved Qty must be less then or equal to Available Qty']);
                    }
                }
                if ($request->has('pi_side') && $reserved != null) {
                    if ($reserved->qty_received != null) {
                        $total = $reserved->qty_received + $reserved->spoilage;
                        if ($request->qty_shipped[$i] == (float)$total) {
                            $reserved->qty_shipped = $reserved->qty_received;
                            $reserved->save();
                            $qty_shipped += (Float)$reserved->qty_shipped;
                        }
                        else{
                            return response()->json(['success' => false, 'message' => 'Qty Shipped must be equal to Qty Received + Qty in Spoilage']);
                        }
                    }
                    else if ($request->qty_shipped[$i] != null && (float)$request->qty_shipped[$i] > 0) {
                        $reserved->old_qty_shipped = $reserved->qty_shipped;
                        $reserved->qty_shipped = $request->qty_shipped[$i];
                        $reserved->save();
                        $qty_shipped += (Float)$reserved->qty_shipped;
                    }
                    else{
                        if ($request->is_draft == 'false' && $reserved->qty_shipped == null && $reserved->old_qty_shipped != null) {
                            $po_detail->reserved_qty -= ($reserved->reserved_quantity / $unit_conversion_rate);
                            $po_detail->save();
                            $reserved->delete();
                        }
                    }
                }
                if ($request->has('receiving_side') && $reserved != null) {
                    if ($request->qty_received[$i] != null && (float)$request->qty_received[$i] > 0) {
                        $total = (Float)$request->qty_received[$i] + (float)$request->qty_spoilage[$i];
                        if ($reserved->qty_shipped != null) {
                            if ($total == (float)$reserved->qty_shipped) {
                                $reserved->qty_received = (float)$request->qty_received[$i];
                                $reserved->spoilage = (float)$request->qty_spoilage[$i];
                                $reserved->spoilage_type = $request->spoilage_type[$i];
                                $reserved->save();
                                $qty_received += (Float)$request->qty_received[$i];
                            }else{
                                return response()->json(['success' => false, 'message' => 'Qty Received + Qty in Spoilage must be equal to Qty Shipped']);
                            }
                        }
                        else{
                            if ($total == (float)$reserved->reserved_quantity) {
                                $reserved->qty_received = (float)$request->qty_received[$i];
                                $reserved->spoilage = (float)$request->qty_spoilage[$i];
                                $reserved->spoilage_type = $request->spoilage_type[$i];
                                $reserved->save();
                                $qty_received += (Float)$request->qty_received[$i];
                            }else{
                                return response()->json(['success' => false, 'message' => 'Qty Received + Qty in Spoilage must be equal to Order Qty']);
                            }
                        }
                    }
                    else {
                        return response()->json(['success' => false, 'message' => 'Qty Received must not be empty']);
                    }
                }
                $i++;
            }
        }

        $responce = null;
        // Calling Respective functions of transfer document statuses
        if ($request->has('is_draft')) {
            if ($request->is_draft == 'true') {
                $new_request = new Request();
                $new_request->replace([
                    "rowId" => $request->pod_id,
                    "draft_po_id" => $request->po_id,
                    "quantity" => $qty_ordered
                ]);
                $responce = DraftPOInsertUpdateHelper::SaveDraftPoProductQuantity($new_request); // Draft TD
            } else {
                $new_request = new Request();
                $new_request->replace([
                    "rowId" => $request->pod_id,
                    "po_id" => $request->po_id,
                    "quantity" => $qty_ordered,
                ]);
                $responce = PODetailCRUDHelper::SavePoProductQuantity($new_request); // Waiting Confirmation and Trabsfer
            }
        }
        if ($request->has('pi_side')) {
            $new_request = new Request();
            $new_request->replace([
                'pod_id' => $request->pod_id,
                'trasnfer_qty_shipped' => $qty_shipped
            ]);
            $responce = (new HomeController)->editTransferPickInstruction($new_request); // PI side
        }
        if ($request->has('receiving_side')) {
            $new_request = new Request();
            $new_request->replace([
                'pod_id' => $request->pod_id,
                'quantity_received' => $qty_received,
                'po_group_id' => $request->po_group_id
            ]);
            $responce = (new PurchaseOrderGroupsController)->savePoGroupDetailChanges($new_request); // Transfer Receiving Queue side
        }

        return $responce;
    }
}
