<?php

namespace App\Helpers\Datatables;

use App\Models\Common\Courier;
use Carbon\Carbon;
use App\Models\Common\StockManagementOut;
use App\Models\Common\PoGroup;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\Order\OrderProductNote;
use Auth;
use App\Models\Common\SupplierProducts;


class TransferDocumentDatatable {

    public static function returnAddColumnTransferDoc($column, $item) {
        switch ($column) {
            case 'to_warehouse':
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : 'N.A';
                break;

            case 'customer':
                $getCust = $item->po_detail->groupBy('customer_id');

                $html_string = '';

                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . '<br>';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class="font-weight-bold d-block show-po-cust mr-2" title="View Customers">'.$customers.' ...</a> ';
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string = "---";
                }

                return $html_string;
                break;

            case 'note':
                if($item->po_notes->count() > 0)
                {
                    $note = $item->po_notes->first()->note;
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="'.$item->id.'" class="d-block show-po-note mr-2 font-weight-bold" title="View Notes">'.mb_substr($note, 0, 30).' ...</a> ';
                }
                else
                {
                    $html_string = '---';
                }
                return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('m/d/Y') : '--';
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,3,'.',',') : '--';
                break;

            case 'received_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'transfer_date':
                return $item->transfer_date !== null ? Carbon::parse($item->transfer_date)->format('d/m/Y') : '--';
                break;

            case 'confirm_date':
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
                break;

            case 'supplier_ref':
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
                break;

            case 'supplier':
                return $item->from_warehouse_id !== null ? $item->PoWarehouse->warehouse_title : "N.A";
                break;

            case 'action':
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
                return $html_string;
                break;

            case 'checkbox':
                if($item->status == 20)
                {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                              </div>';
                }
                else{
                    $html_string = 'N.A';
                }
                return $html_string;
                break;
        }
    }

    public static function returnEditColumnTransferDoc($column, $item) {
        switch ($column) {
            case 'ref_id':
                $item_id = $item->ref_id !== null ? $item->ref_id : '--';
                $html_string = '
                <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';

               return $html_string;
                break;
        }
    }

    public static function returnFilterColumnTransferDoc($column, $item, $keyword) {
        switch ($column) {
            case 'supplier':
                $item->whereHas('PoWarehouse', function($q) use($keyword){
                    $q->where('warehouses.warehouse_title','LIKE', "%$keyword%");
                });
                break;

            case 'to_warehouse':
                $item->whereHas('ToWarehouse', function($q) use($keyword){
                    $q->where('warehouses.warehouse_title','LIKE', "%$keyword%");
                });
                break;

            case 'note':
                $item->whereHas('po_notes', function($q) use($keyword) {
                    $q->where('note', 'LIKE', "%$keyword%");
                });
                break;

            case 'customer':
                $item->whereHas('po_detail', function($q) use($keyword) {
                    $q->whereHas('customer', function($qq) use($keyword) {
                        $qq->where('reference_name', 'LIKE',"%$keyword%");
                    });
                });
                break;
        }
    }

    public static function returnAddColumnTransferDocDetailPage($column, $item, $is_transfer) {
        switch ($column) {
            case 'custom_invoice_number':
                $html_string = '';
                if($item->PurchaseOrder->status == 22)
                {
                    $result = StockManagementOut::where('p_o_d_id',$item->id)->whereNotNull('quantity_out')->where('warehouse_id',$item->PurchaseOrder->from_warehouse_id)->pluck('parent_id_in')->first();
                    $ids_array = explode(',', $result);
                    $find_records = StockManagementOut::whereIn('id',$ids_array)->get();
                    if($find_records->count() > 0)
                    {
                        $html_string = '<button type="button" class="btn p-0 pl-1 pr-1" data-toggle="modal" data-target="#groupsModalInvTd'.$item->id.'">
                                            <i class="fa fa-eye"></i>
                                            </button>';

                         $html_string .= '
                                            <div class="modal" id="groupsModalInvTd'.$item->id.'" data-backdrop="false" style="top:50px;">
                                              <div class="modal-dialog">
                                                <div class="modal-content">

                                                  <div class="modal-header">
                                                    <h4 class="modal-title">Custom\'s Inv#</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                  </div>

                                                  <div class="modal-body">
                                                    <table width="100%" class="supplier_invoice_number_table">
                                                      <thead>
                                                        <tr>
                                                          <th>From</th>
                                                          <th>Custom\'s Inv#</th>
                                                        </tr>
                                                      </thead>
                                                      <tbody>';
                        foreach ($find_records as $record) {

                                    $html_string .= '<tr>';
                                    if($record->order_id != null)
                                    {
                                        $ret = $record->stock_out_order->get_order_number_and_link($record->stock_out_order);
                                        $ref_no = $ret[0];
                                        $link = $ret[1];

                                        $title = '<a target="_blank" href="'.route($link, ['id' => $record->order_id]).'" title="View Detail" class="">ORDER: '.$ref_no .'</a>';
                                    }
                                    elseif($record->p_o_d_id != null)
                                    {
                                        $title = '<a target="_blank" href="'.url('get-purchase-order-detail',$record->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($record->title != null ? $record->title : 'PO' ) .':'. $record->stock_out_purchase_order_detail->PurchaseOrder->ref_id .'</a>';
                                    }
                                    elseif($record->po_group_id  != null)
                                    {
                                        $title = '<a target="_blank" href="'.route('importing-completed-receiving-queue-detail', ['id' => $record->po_group_id]).'" title="View Detail" class="">SHIPMENT: '.$record->get_po_group->ref_id .'</a>';
                                    }
                                    elseif($record->title)
                                    {
                                        $title = $record->title;
                                    }
                                    else
                                    {
                                        $title = 'Adjustmet';
                                    }
                                    $html_string .= '<td>'.$title.'</td>';

                                if($record->po_group_id  != null)
                                {
                                    $val = $record->get_po_group->custom_invoice_number != null ? $record->get_po_group->custom_invoice_number.', ' : '--'.', ';
                                    $html_string .= '<td>'.$val.'</td>';
                                }
                                else
                                {
                                    $val = $record->custom_invoice_number != null ? $record->custom_invoice_number.', ' : '--'.', ';
                                    $html_string .= '<td>'.$val.'</td>';
                                }
                                $html_string .= '</tr>';

                        }
                        $html_string .= '</tbody>
                                                    </table>
                                                  </div>

                                                  <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                  </div>

                                                </div>
                                              </div>
                                            </div>
                            ';

                        return $html_string;
                    }
                    else
                    {
                        return '--';
                    }
                }
                else
                {
                $warehouse_id = $item->PurchaseOrder != null ? $item->PurchaseOrder->from_warehouse_id : '';

                $groups_id = StockManagementOut::where('product_id',$item->product_id)->whereNotNull('quantity_in')->where(function($q)use($item){
                    $q->where('available_stock','>',0)->orWhereIn('id',$item->get_td_reserved()->pluck('stock_id')->toArray());
                })->whereNotNull('po_group_id')->where('warehouse_id',$warehouse_id)->get();

                $existing_groups = $item->get_td_reserved()->pluck('stock_id')->toArray();

                $groups_ids = StockManagementOut::whereIn('id',$existing_groups)->pluck('po_group_id')->toArray();

                $groups_c_i_n = PoGroup::select('custom_invoice_number','id')->whereIn('id',$groups_ids)->get();

                if(true)
                {
                    if($groups_c_i_n->count() > 0)
                    {
                        $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModalInv'.$item->id.'">';
                        foreach ($groups_c_i_n as $group) {
                            $html_string .= ($group->custom_invoice_number != null ? $group->custom_invoice_number : '--').'<br>';
                        }
                        $html_string .= '</a>';
                    }
                    else
                    {
                        $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModalInv'.$item->id.'">--';
                        $html_string .= '</a>';
                    }
                    $html_string .= '
                                    <div class="modal" id="groupsModalInv'.$item->id.'" aria-hidden="true" data-backdrop="false" style="top:50px;" tabindex="-1">
                                      <div class="modal-dialog modal-lg">
                                        <div class="modal-content">

                                          <div class="modal-header">
                                            <h4 class="modal-title">Groups Custom\'s Inv.#</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                          </div>

                                          <div class="modal-body">
                                            <table width="100%" class="supplier_invoice_number_table">
                                              <thead>
                                                <tr>
                                                  <th>Reserved From</th>
                                                  <th>Group No.</th>
                                                  <th>Custom\'s Inv.#</th>
                                                  <th>Available Stock </th>
                                                  <th>Reserved For <br>This Item </th>
                                                  <th>Remaining Stock</th>
                                                </tr>
                                              </thead>
                                              <tbody>';
                                    foreach($groups_id as $inv)
                                    {
                                    if($item->get_td_reserved()->where('stock_id',$inv->id)->where('pod_id',$item->id)->first())
                                    {
                                        $check_class = 'checked';
                                    }
                                    else
                                    {
                                        $check_class = '';
                                    }
                                    if($inv->get_po_group->is_confirm == 1)
                                    {
                                        $url = 'importing-completed-receiving-queue-detail';
                                    }
                                    else
                                    {
                                        $url = 'importing-receiving-queue-detail';
                                    }
                                    $html_string .= '<tr>
                                        <td>
                                            <input type="checkbox" name="reservedFrom" class="pay-check" value="'.$inv->available_stock.'" data-id="'.$item->id.'" data-stockid="'.$inv->id.'" data-poid="'.$item->po_id.'" '.$check_class.'>
                                        </td>
                                        <td><a class="font-weight-bold" href="'.route($url,['id'=> $inv->po_group_id]).'" target="_blank">'.@$inv->get_po_group->ref_id.'</a></td>';
                                        if($item->get_td_reserved->count() > 0)
                                        {
                                            $all_rsv = $item->get_td_reserved()->where('stock_id',$inv->id)->sum('reserved_quantity');
                                            if($all_rsv)
                                            {
                                                $rsv_q = $all_rsv;
                                            }
                                            else
                                            {
                                                $rsv_q = 0;
                                            }
                                        }
                                        else
                                        {
                                            $rsv_q = 0;
                                        }

                                        if($inv->get_po_group->custom_invoice_number != null)
                                        {
                                            $html_string .= '<td>'.$inv->get_po_group->custom_invoice_number.'</td>';
                                            $html_string .= '<td>'.($inv->available_stock + $rsv_q).'</td>';
                                            $html_string .= '<td>'.$rsv_q.'</td>';
                                            $html_string .= '<td>'.$inv->available_stock.'</td>';
                                        }
                                        else
                                        {
                                            $html_string .= '<td>--</td>';
                                            $html_string .= '<td>'.($inv->available_stock + $rsv_q).'</td>';
                                            $html_string .= '<td>'.$rsv_q.'</td>';
                                            $html_string .= '<td>'.$inv->available_stock.'</td>';
                                        }

                                      $html_string .= '</tr>';
                                    }


                                            $html_string .= '</tbody>
                                            </table>
                                          </div>

                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>

                                        </div>
                                      </div>
                                    </div>
                    ';

                    return $html_string;
                }
                else
                {
                    return '--';
                }
                }
                break;

            case 'custom_line_number':
                $html_string = '';
                if($item->PurchaseOrder->status == 22)
                {
                    $result = StockManagementOut::where('p_o_d_id',$item->id)->whereNotNull('quantity_out')->where('warehouse_id',$item->PurchaseOrder->from_warehouse_id)->pluck('parent_id_in')->first();
                    $ids_array = explode(',', $result);

                     $find_records = StockManagementOut::whereIn('id',$ids_array)->get();
                     if($find_records->count() > 0)
                     {
                        $html_string = '<button type="button" class="btn p-0 pl-1 pr-1" data-toggle="modal" data-target="#groupsModalLineTd_'.$item->id.'">
                                            <i class="fa fa-eye"></i>
                                            </button>';

                         $html_string .= '
                                            <div class="modal" id="groupsModalLineTd_'.$item->id.'" data-backdrop="false"  style="top:50px;">
                                              <div class="modal-dialog">
                                                <div class="modal-content">

                                                  <div class="modal-header">
                                                    <h4 class="modal-title">Custom\'s Line#</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                  </div>

                                                  <div class="modal-body">
                                                    <table width="100%" class="supplier_invoice_number_table">
                                                      <thead>
                                                        <tr>
                                                          <th>From</th>
                                                          <th>Custom\'s Inv#</th>
                                                        </tr>
                                                      </thead>
                                                      <tbody>';
                        foreach ($find_records as $record) {
                                    $html_string .= '<tr>';
                                    if($record->order_id != null)
                                    {
                                        $ret = $record->stock_out_order->get_order_number_and_link($record->stock_out_order);
                                        $ref_no = $ret[0];
                                        $link = $ret[1];

                                        $title = '<a target="_blank" href="'.route($link, ['id' => $record->order_id]).'" title="View Detail" class="">ORDER: '.$ref_no .'</a>';
                                    }
                                    elseif($record->p_o_d_id != null)
                                    {
                                        $title = '<a target="_blank" href="'.url('get-purchase-order-detail',$record->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($record->title != null ? $record->title : 'PO' ) .':'. $record->stock_out_purchase_order_detail->PurchaseOrder->ref_id .'</a>';
                                    }
                                    elseif($record->po_group_id  != null)
                                    {
                                        $title = '<a target="_blank" href="'.route('importing-completed-receiving-queue-detail', ['id' => $record->po_group_id]).'" title="View Detail" class="">SHIPMENT: '.$record->get_po_group->ref_id .'</a>';
                                    }
                                    elseif($record->title)
                                    {
                                        $title = $record->title;
                                    }
                                    else
                                    {
                                        $title = 'Adjustmet';
                                    }
                                    $html_string .= '<td>'.$title.'</td>';
                                    if($record->po_group_id  != null)
                                    {
                                        $val = $record->get_po_group->custom_line_number != null ? $record->get_po_group->custom_line_number.', ' : '--'.', ';
                                        $val = $record->get_po_group->po_group_product_details()->where('product_id',$record->product_id)->pluck('custom_line_number')->first() !== null ? $record->get_po_group->po_group_product_details()->where('product_id',$record->product_id)->pluck('custom_line_number')->first().', ' : '--'.', ';
                                        $html_string .= '<td>'.$val.'</td>';

                                    }
                                    else
                                    {
                                        $val = $record->custom_line_number != null ? $record->custom_line_number.', ' : '--'.', ';
                                        $html_string .= '<td>'.$val.'</td>';
                                    }

                                $html_string .= '</tr>';
                        }
                        $html_string .= '</tbody>
                                                    </table>
                                                  </div>

                                                  <div class="modal-footer">
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                  </div>

                                                </div>
                                              </div>
                                            </div>
                            ';

                        return $html_string;
                    }
                    else
                    {
                        return '--';
                    }
                }
                else
                {
                     $warehouse_id = $item->PurchaseOrder != null ? $item->PurchaseOrder->from_warehouse_id : '';
                $groups_id = StockManagementOut::where('product_id',$item->product_id)->whereNotNull('quantity_in')->where(function($q)use($item){
                    $q->where('available_stock','>',0)->orWhereIn('id',$item->get_td_reserved()->pluck('stock_id')->toArray());
                })->whereNotNull('po_group_id')->where('warehouse_id',$warehouse_id)->pluck('po_group_id')->toArray();

                $existing_groups = $item->get_td_reserved()->pluck('stock_id')->toArray();
                $groups_ids = StockManagementOut::whereIn('id',$existing_groups)->pluck('po_group_id')->toArray();
                $pos = PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm','po_group_product_details.custom_line_number')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_ids)->get();
                $pos_line = PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm','po_group_product_details.custom_line_number')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_id)->get();
                    if(true)
                    {
                        if($pos->count() > 0)
                        {
                            $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupModalCusLine'.$item->id.'">';
                            foreach ($pos as $group) {
                                $html_string .= ($group->custom_line_number != null ? $group->custom_line_number : '--').'<br>';
                            }
                            $html_string .= '</a>';
                        }
                        else
                        {
                            $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupModalCusLine'.$item->id.'">--';
                            $html_string .= '</a>';
                        }

                        $html_string .= '
                                        <div class="modal" id="groupModalCusLine'.$item->id.'" aria-hidden="true" data-backdrop="false" style="top:50px;" tabindex="-1">
                                          <div class="modal-dialog">
                                            <div class="modal-content">

                                              <div class="modal-header">
                                                <h4 class="modal-title">Groups Custom\'s Line#</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                              </div>

                                              <div class="modal-body">
                                                <table width="100%" class="supplier_invoice_number_table">
                                                  <thead>
                                                    <tr>
                                                      <th>Group No.</th>
                                                      <th>Custom\'s Line#</th>
                                                    </tr>
                                                  </thead>
                                                  <tbody>';
                                        foreach($pos_line as $inv)
                                        {
                                        if($inv->is_confirm == 1)
                                        {
                                            $url = 'importing-completed-receiving-queue-detail';
                                        }
                                        else
                                        {
                                            $url = 'importing-receiving-queue-detail';
                                        }
                                        $html_string .= '<tr>
                                            <td><a class="font-weight-bold" href="'.route($url,['id'=> $inv->id]).'" target="_blank">'.@$inv->ref_id.'</a></td>';
                                            if($inv->custom_line_number != null)
                                            {
                                                $html_string .= '<td>'.$inv->custom_line_number.'</td>';
                                            }
                                            else
                                            {
                                                $html_string .= '<td>--</td>';
                                            }

                                          $html_string .= '</tr>';
                                        }


                                                $html_string .= '</tbody>
                                                </table>
                                              </div>

                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                              </div>

                                            </div>
                                          </div>
                                        </div>
                        ';

                        return $html_string;
                    }
                    else
                    {
                        return '--';
                    }
                }
                break;

            case 'supplier_invoice_number':
                $html_string = '';
                if($item->PurchaseOrder->status == 22)
                {
                    $find_group_of_prod = PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id','purchase_order_details.id','stock_management_outs.p_o_d_id','stock_management_outs.po_group_id','purchase_order_details.product_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('stock_management_outs.p_o_d_id',$item->id)->whereNotNull('stock_management_outs.po_group_id')->where('purchase_orders.status',22)->where('purchase_order_details.product_id',$item->product_id);

                    $find_group_of_prod = PurchaseOrderDetail::select('purchase_order_details.id','stock_management_outs.smi_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('purchase_order_details.id',$item->id)->pluck('smi_id')->toArray();

                    $groups_id = StockManagementOut::select('po_group_id','smi_id')->whereIn('smi_id',$find_group_of_prod)->whereNotNull('po_group_id')->groupBy('po_group_id')->pluck('po_group_id')->toArray();

                     $find_group_of_prod = PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->where('purchase_order_details.product_id',$item->product_id)->whereIn('po_group_details.po_group_id',$groups_id);

                    $pos_id = $find_group_of_prod->pluck('purchase_order_details.po_id')->toArray();
                    $pos = PurchaseOrder::select('id','invoice_number','ref_id','status')->whereIn('id',$pos_id)->whereIn('status',[14,15])->get();

                                        if($pos->count() > 0 && $pos->count() == 1)
                    {
                        foreach ($pos as $value) {
                            $html_string .= $value->invoice_number != null ? ('<a class="font-weight-bold" href="'.route('get-purchase-order-detail',['id'=> $value->id]).'" target="_blank">'.$value->invoice_number.'</a>') : '--';
                        }

                        return $html_string;
                    }
                    else if($pos->count() > 1)
                    {
                        $html_string .= '<button type="button" class="btn p-0 pl-1 pr-1" data-toggle="modal" data-target="#myModalTdSupInv'.$item->id.'">
                                        <i class="fa fa-eye"></i>
                                        </button>';
                        $html_string .= '
                                        <div class="modal" id="myModalTdSupInv'.$item->id.'" data-backdrop="false"  style="top:50px;">
                                          <div class="modal-dialog">
                                            <div class="modal-content">

                                              <div class="modal-header">
                                                <h4 class="modal-title">PO\'s Supplier Inv.#</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                              </div>

                                              <div class="modal-body">
                                                <table width="100%" class="supplier_invoice_number_table">
                                                  <thead>
                                                    <tr>
                                                      <th>PO No.</th>
                                                      <th>Supplier Inv.#</th>
                                                    </tr>
                                                  </thead>
                                                  <tbody>';
                                        foreach($pos as $inv)
                                        {
                                                    $html_string .= '<tr>
                                                        <td><a class="font-weight-bold" href="'.route('get-purchase-order-detail',['id'=> $inv->id]).'" target="_blank">'.@$inv->ref_id.'</a></td>';
                                                        if($inv->invoice_number != null)
                                                        {
                                                            $html_string .= '<td> '.$inv->invoice_number.' </td>';
                                                        }
                                                        else
                                                        {
                                                            $html_string .= '<td>--</td>';
                                                        }

                                                      $html_string .= '</tr>';
                                        }

                                                $html_string .= '</tbody>
                                                </table>
                                              </div>

                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                              </div>

                                            </div>
                                          </div>
                                        </div>
                        ';

                        return $html_string;
                    }
                    else
                    {
                        return '--';
                    }
                }
                else
                {
                    $warehouse_id = $item->PurchaseOrder != null ? $item->PurchaseOrder->from_warehouse_id : '';
                    $groups_id = StockManagementOut::where('product_id',$item->product_id)->whereNotNull('quantity_in')->where(function($q)use($item){
                    $q->where('available_stock','>',0)->orWhereIn('id',$item->get_td_reserved()->pluck('stock_id')->toArray());
                    })->whereNotNull('po_group_id')->where('warehouse_id',$warehouse_id)->pluck('po_group_id')->toArray();

                    $existing_groups = $item->get_td_reserved()->pluck('stock_id')->toArray();

                    $groups_ids = StockManagementOut::whereIn('id',$existing_groups)->pluck('po_group_id')->toArray();

                    $pos = PurchaseOrder::select('id','invoice_number','ref_id')->whereHas('PurchaseOrderDetail',function($p)use($item){
                        $p->where('product_id',$item->product_id);
                    })->whereIn('po_group_id',$groups_ids)->get();

                    $pos_sup_inv = PurchaseOrder::select('id','invoice_number','ref_id')->whereHas('PurchaseOrderDetail',function($p)use($item){
                        $p->where('product_id',$item->product_id);
                    })->whereIn('po_group_id',$groups_id)->get();
                    if(true)
                    {
                        if($pos->count() > 0)
                        {
                            $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupModalSupInv'.$item->id.'">';
                            foreach ($pos as $group) {
                                $html_string .= ($group->invoice_number != null ? $group->invoice_number : '--').'<br>';
                            }
                            $html_string .= '</a>';
                        }
                        else
                        {
                            $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupModalSupInv'.$item->id.'">--';
                            $html_string .= '</a>';
                        }
                        $html_string .= '
                                        <div class="modal" id="groupModalSupInv'.$item->id.'" aria-hidden="true" data-backdrop="false" style="top:50px;" tabindex="-1">
                                          <div class="modal-dialog">
                                            <div class="modal-content">

                                              <div class="modal-header">
                                                <h4 class="modal-title">PO\'s Supplier Inv.#</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                              </div>

                                              <div class="modal-body">
                                                <table width="100%" class="supplier_invoice_number_table">
                                                  <thead>
                                                    <tr>
                                                      <th>PO No.</th>
                                                      <th>Supplier Inv.#</th>
                                                    </tr>
                                                  </thead>
                                                  <tbody>';
                                        foreach($pos_sup_inv as $inv)
                                        {
                                                    $html_string .= '<tr>
                                                        <td><a class="font-weight-bold" href="'.route('get-purchase-order-detail',['id'=> $inv->id]).'" target="_blank">'.@$inv->ref_id.'</a></td>';
                                                        if($inv->invoice_number != null)
                                                        {
                                                            $html_string .= '<td> '.$inv->invoice_number.' </td>';
                                                        }
                                                        else
                                                        {
                                                            $html_string .= '<td>--</td>';
                                                        }

                                                      $html_string .= '</tr>';
                                        }

                                                $html_string .= '</tbody>
                                                </table>
                                              </div>

                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                              </div>

                                            </div>
                                          </div>
                                        </div>
                        ';

                        return $html_string;
                    }
                    else
                    {
                        return '--';
                    }
                }
                break;

            case 'discount':
                if($item->PurchaseOrder->status == 12)
                {
                    $html = '<span class="inputDoubleClick font-weight-bold" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none" style="width:85%"  maxlength="5" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">';
                    return $html.' %';
                }
                else
                {
                    return $item->discount !== null ? $item->discount." %" : "--";
                }
                break;

            case 'remarks':
                if($item->order_product_id != null)
                {
                    $notes = OrderProductNote::where('order_product_id', $item->order_product_id)->count();

                    if(Auth::user()->role_id != 7)
                    {
                        $html_string = '<div class="d-flex justify-content-center text-center">';
                    if($notes > 0){
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->order_product_id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                    }

                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->order_product_id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
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
                    return "--";
                }
                break;

            case 'billed_unit_per_package':
                return $item->billed_unit_per_package !== null ? $item->billed_unit_per_package : '--';
                break;

            case 'supplier_packaging':
                return $item->supplier_packaging !== null ? $item->supplier_packaging : '--';
                break;

            case 'order_no':
                $ref_no = $item->order_id !== null ? $item->getOrder->ref_id : 'N.A';
                if($item->order_id == null)
                {
                    // dd('here');
                    return @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.$ref_no;
                }
                else
                {
                   return  $html_string = '<a target="_blank" href="'.route('get-completed-draft-invoices',$item->getOrder->id).'"  >'.@$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.$ref_no.'</a>';
                }
                break;

            case 'amount':
                $amount = $item->pod_unit_price * $item->quantity;
                $amount = $amount - ($amount * (@$item->discount / 100));
                return $amount !== null ? number_format((float)$amount,3,'.',',') : "--";
                break;

            case 'unit_price':
                if($item->PurchaseOrder->status == 12)
                {
                if($item->pod_unit_price == null)
                {
                    $style = "color:red;";
                }
                else
                {
                    $style = "";
                }
                $html_string = '
                <span class="m-l-15 inputDoubleClickQuantity unit_price" style="'.$style.'" data-id id="unit_price"  data-fieldvalue="'.number_format(@$item->pod_unit_price, 3, '.', ',').'">';
                $html_string .= $item->pod_unit_price !== null ? number_format(@$item->pod_unit_price, 3, '.', ',') : "--" ;
                $html_string .= '</span>';
                $html_string .= '<input type="number" style="width:100%;" name="unit_price" class="unitfieldFocus d-none" min="0" value="'.number_format(@$item->pod_unit_price, 3, '.', ',').'">';
                return $html_string;
                }
                else
                {
                    return $item->pod_unit_price !== null ? number_format(@$item->pod_unit_price, 3, '.', ',') : "--" ;
                }
                break;

            case 'quantity_received':
                $html_string = '--';
                if ($item->quantity_received != null) {
                    $html_string = '
                    <span class="m-l-15 td_qty_received font-weight-bold" data-id="'.$item->id.'" data-product_id="'.$item->product_id.'" data-po_id="'.$item->po_id.'" data-warehouse_id="'.$item->PurchaseOrder->from_warehouse_id.'">';
                    $html_string .= number_format($item->quantity_received,3,'.','');
                    $html_string .= '</span>';
                }
                return $html_string;
                // return $item->quantity_received != NULL ? number_format($item->quantity_received,3,'.','') : "N.A";
                break;

            case 'qty_sent':
                return $item->trasnfer_qty_shipped != NULL ? number_format($item->trasnfer_qty_shipped,3,'.','') : "N.A";
                break;

            case 'gross_weight':
                return $item->pod_total_gross_weight != NULL ? number_format((float)$item->pod_total_gross_weight,3,'.','') : "N.A";
                break;

            case 'customer_qty':
                $selling_unit = ($item->order_product_id != null ? @$item->order_product->unit->title : "N.A");
                $html_string = '<span class="m-l-15 customer_qty">';
                $html_string .= ($item->order_product_id != null ? @$item->order_product->quantity.' '.$selling_unit : "N.A");
                $html_string .= '</span>';
                return $html_string;
                break;

            case 'quantity':
                $decimals = $item->product != null ? ($item->product->units != null ? $item->product->units->decimal_places : 0) : 0;
                if($item->PurchaseOrder->status == 20 || $item->PurchaseOrder->status == 21)
                {
                    if($item->quantity == null || $item->quantity == 0)
                    {
                        $style = "color:red;";
                    }
                    else
                    {
                        $style = "";
                    }
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity quantity" style="'.$style.'" data-id id="quantity"  data-fieldvalue="'.$item->quantity.'">';
                    $html_string .= ($item->quantity != null ? number_format($item->quantity,$decimals,'.','') : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;" name="quantity" class="fieldFocusQuantity d-none" min="0" value="'.$item->quantity.'">';
                    return $html_string;
                }
                else
                {
                    return $item->quantity !== null ? number_format($item->quantity,$decimals,'.','') : "--";
                }
                break;

            case 'warehouse':
                return $item->warehouse_id !== null ? @$item->getWarehouse->warehouse_title : '--';
                break;

            case 'selling_unit':
                return $item->product_id !== null ? @$item->product->sellingUnits->title : 'N.A';
                break;

            case 'buying_unit':
                return $item->product_id !== null ? @$item->product->units->title : 'N.A';
                break;

            case 'short_desc':
                if($item->product_id != null)
                {
                    if($item->PurchaseOrder->supplier_id != null)
                    {
                       $supplier_id = $item->PurchaseOrder->supplier_id;

                        $getDescription = SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                        if($getDescription)
                        {
                            return @$getDescription->supplier_description != null ? @$getDescription->supplier_description : ($item->product->short_desc != null ? $item->product->short_desc : "--") ;
                        }
                        else
                        {
                            return 'N.A';
                        }
                    }
                    else
                    {
                        $supplier_id = $item->product->supplier_id;

                        return $item->product->short_desc != null ? $item->product->short_desc : "--" ;
                    }
                }
                else
                {
                    if($item->billed_desc == null)
                    {
                        $style = "color:red;";
                    }
                    else
                    {
                        $style = "";
                    }
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity billed_desc" style="'.$style.'" data-id id="billed_desc"  data-fieldvalue="'.@$item->billed_desc.'">';
                    $html_string .= ($item->billed_desc != null ? $item->billed_desc : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="billed_desc" class="fieldFocusQuantity d-none" value="'.$item->billed_desc .'">';
                    return $html_string;
                }
                break;

            case 'customer':
                return $item->customer_id !== null ? @$item->customer->reference_name : 'N.A';
                break;

            case 'type':
                return $item->product_id != null ?  $item->product->productType->title : '--';
                break;

            case 'brand':
                return $item->product_id != null ?  $item->product->brand : '--';
                break;

            case 'item_ref':
                if($item->product_id != null)
                {
                    $ref_no = $item->product->refrence_code;
                    return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$ref_no.'</b></a>';
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
                        $gettingProdSuppData = SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$item->PurchaseOrder->PoSupplier->id)->first();

                        $ref_no1 = $gettingProdSuppData->product_supplier_reference_no !== null ? $gettingProdSuppData->product_supplier_reference_no : "--";

                        return  $html_string1 = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$ref_no1.'</b></a>';
                    }
                    else
                    {
                        return  $html_string1 = 'N.A';
                    }
                }
                else{
                        return '--';
                }
                break;

            case 'action':
                if($item->order_id != NULL && $item->PurchaseOrder->status == 20)
                {
                    $html_string = '
                    <a href="javascript:void(0);" class="actionicon editIcon delete-product-from-list" data-order_id="' . $item->order_id . '" data-order_product_id="'. $item->order_product_id .'" data-po_id ="'. $item->po_id .'" data-id="'.$item->id.'" title="Revert To Purchase List"><i class="fa fa-undo"></i></a>';
                }
                elseif($item->order_id == NULL && $item->PurchaseOrder->status == 20 && Auth::user()->role_id != 7)
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
        }
    }



}
