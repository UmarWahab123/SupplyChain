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
use DB;
use App\User;
use App\Models\Common\Order\OrderNote;


class PickInstructionDatatable {

    public static function returnAddColumn($column, $item) {
        switch ($column) {
            case 'total_amount1':
                return number_format($item->total_amount, 3, '.', ',');
                break;

            case 'comment_to_customer':
                $warehouse_note = $item->order_customer_note;
                if($item->ecommerce_order == 1){
                    return $item->delivery_note != null ? $item->delivery_note : '--';
                }else{
                    return $warehouse_note != null ? $warehouse_note->note : '--';
                }
                break;

            case 'comment_to_warehouse':
                $warehouse_note = $item->order_warehouse_note;
                return $warehouse_note != null ? $warehouse_note->note  : '--';
                break;

            case 'invoice_date1':
                return $item->updated_at != null ? Carbon::parse($item->updated_at)->format('d/m/Y') : "N.A";
                break;

            case 'ref_id1':
                if ($item->status_prefix !== null || $item->in_status_prefix !== null) {
                    if ($item->primary_status == 3) {
                        $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                    } else {
                        $ref_no = @$item->status_prefix . '-' . $item->ref_prefix . $item->ref_id;
                    }
                } else {
                    $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id;
                }

                return $html_string = '<a href="' . route('pick-instruction', ['id' => $item->id]) . '"><b>' . $ref_no . '</b></a>';
                break;

            case 'status1':
                $html = '<span class="sentverification">' . $item->statuses->title . '</span>';
                return $html;
                break;

            case 'delivery_request_date1':
                return $item->delivery_request_date != null ? Carbon::parse(@$item->delivery_request_date)->format('d/m/Y') : '--';
                break;

            case 'customer_name':
                return ($item->customer->company !== null ? @$item->customer->company : '--');
                break;

            case 'customer_ref_no1':
                return $item->customer->reference_number;
                break;

            case 'user_id1':
                return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
                break;

            case 'customer_reference_name1':
                if ($item->customer_id != null) {
                    if($item->customer->ecommerce_customer == 1){
                        $ref_no = $item->customer !== null ? $item->customer->first_name.' '.$item->customer->last_name : "--";

                    }else{
                        $ref_no = $item->customer !== null ? $item->customer->reference_name : "--";
                    }
                    return $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail', $item->customer->id) . '"><b>' . $ref_no . '</b></a>';
                } else {
                    $html_string = 'N.A';
                }

                return $html_string;
                break;
            case 'printed':
                return @$item->draft_invoice_pick_instruction_printed != null ? 'Yes' : 'No';
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'customer_reference_name1':
                $item->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('reference_name', 'LIKE', "%$keyword%");
                });
                break;

            case 'customer_ref_no1':
                $item->orWhereHas('customer', function ($q) use ($keyword) {
                    $q->where('reference_number', 'LIKE', "%$keyword%");
                });
                break;

            case 'comment_to_customer':
                $item->orWhereHas('order_notes', function ($q) use ($keyword) {
                    $q->where('note', 'LIKE', "%$keyword%");
                });
                // $item = $item->orWhereIn('id', OrderNote::where('type', 'customer')->where('note', 'LIKE', "%$keyword%")->pluck('order_id'));
                // break;

            case 'ref_id1':
                $result = $keyword;
                if (strstr($result, '-')) {
                    $item->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $keyword . "%");
                    $item->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_id`)"), 'LIKE', "%" . $keyword . "%");
                    $item->orWhere("in_ref_id", 'LIKE', "%" . $keyword . "%");
                    $item->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $keyword . "%");
                    $item->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_id`)"), 'LIKE', "%" . $keyword . "%");
                } else {
                    $resultt = preg_replace("/[^0-9]/", "", $result);
                    if ($resultt != '') {
                        $item->where('ref_id', 'LIKE', "%$resultt%")->orWhere('in_ref_id', 'LIKE', "%$resultt%");
                    }
                }
                break;

            case 'user_id1':
                // $item = $item->orWhereIn('user_id', User::where('name', 'LIKE', "%$keyword%")->pluck('id'));
                $item->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%$keyword%");
                });
                break;
        }
     }

}
