<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderTransaction extends Model
{
    public function order(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder','po_id','id');
    }

    public function get_payment_type(){
    	return $this->belongsTo('App\Models\Common\PaymentType','payment_method_id','id');
    }

    public function get_payment_ref(){
    	return $this->belongsTo('App\PoPaymentRef','payment_reference_no','id');
    }

    public static function returnAddColumnAccountTransaction($column, $item) {
        switch ($column) {
            case 'action':
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon delete_po_transaction" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';
                return $html_string;
                break;

            case 'payment_reference_no':
                return $item->payment_reference_no != null ? $item->get_payment_ref->payment_reference_no : '--';
                break;

            case 'payment_type':
                return $item->payment_method_type != null ? $item->get_payment_type->title : 'N.A';
                break;

            case 'received_date':
                return $item->received_date != null ? Carbon::parse(@$item->received_date)->format('d/m/Y') : 'N.A';
                break;

            case 'total_paid':
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="total_received" data-fieldvalue="'.@round($item->total_received,2).'">';
                $html_string .= round($item->total_received,2);
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="total_received" style="width:100%;" class="fieldFocusTran d-none" value="'.round($item->total_received,2).'">';

                return $html_string;
                break;

            case 'invoice_total':
                return $item->order !== null && $item->order->total_in_thb != null ? number_format($item->order->total_in_thb,2,'.',',') : 'N.A';
                break;

            case 'difference':
                if ($item->order) {
                    $diff = $item->order->total_in_thb-$item->total_received;
                    return number_format($diff,2,'.',',') ;
                }
                return '--';
                break;

            case 'supplier_company':
                if ($item->order) {
                    $html_string = '<a href="'.url('get-supplier-detail/'.$item->order->PoSupplier->id).'" title="View Detail"><b>'.$item->order->PoSupplier->reference_name.'</b></a>';
                    return $html_string;
                }
                return '--';
                break;
            case 'ref_no':
                if ($item->order) {
                    $ref_id = $item->order->primary_status == 25 ? $item->order->p_o_statuses->parent->prefix.$item->order->ref_id : $item->order->ref_id;
                    $url = $item->order->primary_status == 25 ? url('get-supplier-credit-note-detail/'.$item->order->id) : url('get-purchase-order-detail/'.$item->order->id);
                    $html_string = '<a href="'.$url.'" title="View Detail"><b>'.$ref_id.'</b></a>';
                    return $html_string;
                }
                return '--';
                break;
        }
    }
}
