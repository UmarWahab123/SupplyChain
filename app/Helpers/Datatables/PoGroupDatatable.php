<?php

namespace App\Helpers\Datatables;

use App\Models\Common\Courier;
use Carbon\Carbon;


class PoGroupDatatable {

    public static function returnAddColumn($column, $item, $couriers) {
        switch ($column) {
            case 'warehouse':
                return $item->ToWarehouse !== null ? $item->ToWarehouse->warehouse_title: "--" ;
                break;

            case 'note':
                $created_at = Carbon::parse($item->created_at)->format('d/m/Y');
                return $item->note != null ? $item->note : '--';
                break;

            case 'target_receive_date':
                if($item->is_review == 0)
                {
                    $target_receive_date = $item->target_receive_date != null ? $item->target_receive_date : '';
                    $target_receive_date_sp = $item->target_receive_date != null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                    $html_string = '<span class="m-l-15 inputDoubleClick" id="target_receive_date" data-fieldvalue="'.$target_receive_date.'">
                    '.$target_receive_date_sp.'
                    </span>';

                    $today = Carbon::now();
                    $today = date('Y-m-d');
                    if($target_receive_date == 'N.A')
                    {
                        $target_receive_date = '';
                    }
                    $html_string .= '<input type="date"  name="target_receive_date" min="'.$today.'" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$target_receive_date.'" style="width:100%">';
                    return $html_string;
                }
                else
                {
                    return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : "--" ;
                }
                break;

            case 'po_total':
                $total = @$item->po_group_product_details != null ? $item->po_group_product_details->sum('total_unit_price_in_thb_with_vat') : 0;
		        return number_format($total,3,'.',',');
                break;

            case 'issue_date':
                $created_at = Carbon::parse($item->created_at)->format('d/m/Y');
                if($item->is_review == 0)
                {
                    $created_at = $item->created_at != null ? $item->created_at : '';
                    $created_at_sp = $item->created_at != null ? Carbon::parse($item->created_at)->format('d/m/Y') : '--';
                    $html_string = '<span class="m-l-15 inputDoubleClick" id="created_at" data-fieldvalue="'.$created_at.'">
                    '.$created_at_sp.'
                    </span>';

                    $today = Carbon::now();
                    $today = date('Y-m-d');
                    if($created_at == 'N.A')
                    {
                        $created_at = '';
                    }
                    $html_string .= '<input type="date"  name="created_at" min="'.$today.'" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$created_at.'" style="width:100%">';
                    return $html_string;
                }
                else
                {
                    return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : "--" ;
                }
                break;

            case 'net_weight':
                $weight = $item->po_group_product_details != null ? $item->po_group_product_details->sum('total_gross_weight') : 0;
                return round($weight,2);
                break;

            case 'landing':
                $landing = $item->landing !== null ? $item->landing : 'N.A';
                $html_string = '<span class="m-l-15" id="landing" data-fieldvalue="'.$landing.'" >
                '.$landing.'
                </span>';

                $html_string .= '<input type="number"  name="landing" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$landing.'" style="width:70%">';
                return $html_string;
                break;

            case 'freight':
                $freight = $item->freight !== null ? $item->freight : 'N.A';
                $html_string = '<span class="m-l-15" id="freight" data-fieldvalue="'.$freight.'">
                '.$freight.'
                </span>';

                $html_string .= '<input type="number"  name="freight" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$freight.'" style="width:70%">';
                return $html_string;
                break;

            case 'tax':
                $tax = $item->tax !== null ? $item->tax : 'N.A';
                $html_string = '<span class="m-l-15" id="tax" data-fieldvalue="'.$tax.'">
                '.$tax.'
                </span>';

                $html_string .= '<input type="number"  name="tax" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$tax.'" style="width:70%">';
                return $html_string;
                break;

            case 'quantity':
                $total_quantity = $item->po_group_product_details != null ? $item->po_group_product_details->sum('quantity_inv') : 0;
                return round($total_quantity,4);
                break;

            case 'supplier_ref_no':
                if($item->po_group_detail_count > 1 )
                {
                    $string = '';
                    $i = 0;
                    $po_group_details = $item->po_group_detail;
                    foreach ($po_group_details as $po_group) {
                        if ($i < 3) {
                            $string .= $po_group->purchase_order->PoSupplier->reference_name . '<br>';
                        }
                        $i++;
                    }
                $html_string = '
                    <a href="javascript:void(0)" data-id="'.$item->id.'" class="supplier_reference_name font-weight-bold">
                    '.$string.' ...
                    </a>
                ';

                return $html_string;
                }
                elseif($item->po_group_detail_count == 1)
                {
                    return $item->po_group_detail[0]->purchase_order->PoSupplier->reference_name;
                } else {
                    return '--';
                }
                break;

            case 'courier':
                if($item->is_review == 1)
                {
                    return $item->po_courier !== null ? $item->po_courier->title: "N.A" ;;
                }
                $title = $item->po_courier !== null ? $item->po_courier->title: "N.A" ;
                $html_string = '<span class="m-l-15 inputDoubleClick" data-fieldvalue="'.@$item->po_courier->title.'">';
                    $html_string .= $title;
                    $html_string .= '</span>';
                $html_string .= '<select name="courier" class="select-common form-control d-none" data-id="'.$item->id.'">
                <option>Choose Courier</option>';
                if($couriers){
                    foreach($couriers as $courier)
                    {
                        $html_string .= '<option value="'.$courier->id.'"> '.$courier->title.'</option>';
                    }
                }
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'po_number':
                $po_id = [];
                if($item->po_group_detail_count > 1)
                {
                    $string = '';
                    $i = 0;
                    $po_group_details = $item->po_group_detail;
                    foreach ($po_group_details as $po_group) {
                        if ($i < 3) {
                            $string .= @$po_group->purchase_order->ref_id . '<br>';
                        }
                        $i++;
                    }
                    $html_string = '
                        <a href="javascript:void(0)" data-id="'.$item->id.'" class="po_list_btn font-weight-bold">
                        '.$string.' ...
                        </a>
                    ';
                    return $html_string;
                }
                else if($item->po_group_detail_count ==1)
                {
                    $po = $item->po_group_product_details_one != null && $item->po_group_product_details_one->purchase_order != null ? $item->po_group_product_details_one->purchase_order : null;
                    if($po != null)
                    {
                    $link = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $po->id]).'" title="View Detail"><b>'.$po->ref_id.'</b></a>';
                    }
                    else
                    {
                        $link = '--';
                    }
                    return $link;
                }
                else
                {
                    return '--';
                }
                break;

            case 'id':
                if($item->is_review == 0){
                    $html_string = '<a href="'.url('importing/importing-receiving-queue-detail', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id.'</b></a>';
                }
                else if($item->is_review == 1){
                    $html_string = '<a href="'.url('importing/importing-completed-receiving-queue-detail', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id.'</b></a>';
                }
                return $html_string;
                break;
        }
    }

    public static function returnEditColumn($column, $item) {
        switch ($column) {
            case 'bill_of_landing_or_airway_bill':
                if($item->is_review == 0)
                {
                    $bill_of_landing_or_airway_bill = $item->bill_of_landing_or_airway_bill != null ? $item->bill_of_landing_or_airway_bill : 'N.A';
                    $html_string = '<span class="m-l-15 inputDoubleClick" id="bill_of_landing_or_airway_bill" data-fieldvalue="'.$bill_of_landing_or_airway_bill.'">
                    '.$bill_of_landing_or_airway_bill.'
                    </span>';

                    $html_string .= '<input type="text"  name="bill_of_landing_or_airway_bill" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$bill_of_landing_or_airway_bill.'" style="width:100%">';
                    return $html_string;
                }
                else
                {
                    return $item->bill_of_landing_or_airway_bill !== null ? $item->bill_of_landing_or_airway_bill: "N.A" ;
                }
                break;
            }
    }

    public static function returnFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'id':
                $item = $item->where('ref_id','like',"%$keyword%");
                break;

            case 'po_number':
                $item = $item->whereHas('purchase_orders',function($q) use ($keyword){
                    $q->where('purchase_orders.ref_id','like',"%$keyword%");
                })->get();
                break;

            case 'courier':
                $item = $item->whereIn('courier', Courier::select('id')->where('title','LIKE',"%$keyword%")->pluck('id'));
                break;
        }
    }

    public static function returnAddColumnReceivingQueue($column, $item) {
        switch ($column) {
            case 'courier':
                if($item->courier !== '' || $item->courier !== 0 || $item->courier !== NULL)
                {
                    return $item->po_courier !== null ? $item->po_courier->title : "--";
                }
                else
                {
                    return "--";
                }
                break;

            case 'warehouse':
                return $item->ToWarehouse !== null ? $item->ToWarehouse->warehouse_title: "--" ;
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'po_total':
                $total = @$item->po_group_product_details != null ? $item->po_group_product_details->sum('total_unit_price_in_thb_with_vat') : 0;
                return number_format($total,3,'.',',');
                break;

            case 'net_weight':
                return $item->po_group_total_gross_weight != null ? $item->po_group_total_gross_weight : '--';
                break;

            case 'quantity':
                $po_group_product_details = $item->po_group_product_details;
                $total_quantity = null;
                foreach ($po_group_product_details as $p_g_d) {
                    $total_quantity += $p_g_d->quantity_inv;
                }
                return $total_quantity !== null ? $total_quantity : '--' ;
                break;

            case 'supplier_ref_no':
                $po_group_detail = $item->po_group_detail;
		    	if($po_group_detail->count() > 1)
		    	{
                    $string = '';
                    $i = 0;
                    foreach ($po_group_detail as $detail) {
                        if ($i < 3) {
                            $string .= $detail->purchase_order->PoSupplier->reference_name . '<br>';
                        }
                        $i++;
                    }
					$html_string = '
						<a href="javascript:void(0)" data-id="'.$item->id.'" class="supplier_reference_name font-weight-bold">
						'.$string.' ...
						</a>
					';

			        return $html_string;
			    }
			    else
			    {
			    	$po = @$item->po_group_detail[0]->purchase_order;
			    	if($po !== null)
			    	{
			    	if(@$po->supplier_id != null)
	      			{
	      				return @$po->PoSupplier->reference_name;
	      			}
	      			else
	      			{
	      				return @$po->PoWarehouse->warehouse_title;
	      			}
	      			}
	      			else
	      			{
	      				return '--';
	      			}
			    }
                break;

            case 'po_number':
                $po_group_detail = $item->po_group_detail;
                if($po_group_detail->count() > 0)
                {
                    if($po_group_detail->count() > 1)
                    {
                        $i = 0;
                        $string = '';
                        foreach ($po_group_detail as $detail) {
                            if ($i < 3) {
                                $string .= @$detail->purchase_order->ref_id . '<br>';
                            }
                            $i++;
                        }
                        $html_string = '
                            <a href="javascript:void(0)" data-id="'.$item->id.'" class="po_list_btn font-weight-bold">
                            '.$string.' ...
                            </a>
                        ';

                        return $html_string;
                    }
                    else
                    {
                        $po = [];
                        foreach ($po_group_detail as $p_g_d) {
                            if(!in_array(@$p_g_d->purchase_order->ref_id, $po, true)){
                            array_push($po, $p_g_d->purchase_order);
                            }
                        }
                        $po = $po[0];
                        // dd($po);
                        $link = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $po->id]).'" title="View Detail"><b>'.$po->ref_id.'</b></a>';
                        return $link;
                    }
                }
                else
                {
                    return '--';
	            }
                break;

            case 'id':
                if($item->is_confirm == 0)
                {
                    $html_string = '<a href="'.url('warehouse/warehouse-receiving-queue-detail',$item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id.'</b></a>';
                    return $html_string;
                }
                else
                {
                    $html_string = '<a href="'.url('warehouse/warehouse-completed-receiving-queue-detail', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id.'</b></a>';
                    return $html_string;
                }
                break;

            case 'issue_date':
                $created_at = Carbon::parse($item->created_at)->format('d/m/Y');
	    	    return $created_at;
                break;
        }
    }


}
