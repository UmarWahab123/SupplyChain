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
use App\Models\Common\ProductType;
use DB;
use App\User;
use App\Models\Common\ProductSecondaryType;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\ProductCategory;
use App\Models\Common\Product;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\ProductImage;
use App\Models\Common\Supplier;
use App\Models\Common\Unit;


class EcomDashboardDatatable {

    public static function returnAddColumnEcom($column, $item) {
        switch ($column) {
            case 'action':
                $html_string = '';
                if($item->primary_status == 1 &&  Auth::user()->role_id != 7)
                {
                    $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                return $html_string;
                break;

            case 'order_note':
                return @$item->delivery_note != null ? wordwrap(@$item->delivery_note,50,"<br>\n") : '--';
                break;

            case 'order_type':
                if($item->order_note_type !== null){
                     $note_type = $item->order_note_type == 1 ? 'Self Pick' : 'Delivery';
                   }else {
                     $note_type = '--';
                   }
                return $note_type;
                break;

            case 'total_amount':
                return number_format(floor($item->total_amount*100)/100,2,'.',',');
                break;

            case 'discount':
                return $item->all_discount !== null ? number_format(floor($item->all_discount*100)/100,2,'.',',') : '0.00';
                break;

            case 'due_date':
                return @$item->payment_due_date != null ? Carbon::parse(@$item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'invoice_date':
                return Carbon::parse(@$item->converted_to_invoice_on)->format('d/m/Y');
                break;

            case 'sub_total_2':
                return $item->not_vat_total_amount !== null ? number_format($item->not_vat_total_amount,2,'.',',') : '0.00';
                break;

            case 'reference_id_vat_2':
                if($item->is_vat == 1)
                {
                  if($item->manual_ref_no !== null)
                  {
                    if($item->in_ref_id == $item->manual_ref_no)
                    {
                      $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
                    }
                    else
                    {
                      $ref_no = $item->in_ref_id.'-2';
                    }
                  }
                  else
                  {
                    $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
                  }
                }
                else
                {
                  $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
                }
                return $ref_no;
                break;

            case 'vat_1':
                return $item->vat_amount_price !== null ? number_format($item->vat_amount_price,2,'.',',') : '0.00';
                break;

            case 'sub_total_1':
                return $item->vat_total_amount !== null ? number_format($item->vat_total_amount,2,'.',',') : '0.00';
                break;

            case 'reference_id_vat':
                if($item->is_vat == 1)
                {
                    if($item->manual_ref_no !== null)
                    {
                    if($item->in_ref_id == $item->manual_ref_no)
                    {
                        $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
                    }
                    else
                    {
                        $ref_no = $item->in_ref_id.'-1';
                    }
                    }
                    else
                    {
                    $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
                    }
                }
                else
                {
                    $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
                }
                return $ref_no;
                break;

            case 'ref_id':
                if($item->status_prefix !== null || $item->ref_prefix !== null)
                {
                  $ref_no = @$item->status_prefix.'-'.$item->ref_prefix.$item->ref_id;
                }
                else
                {
                  $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
                }

                $html_string = '';
                if($item->primary_status == 2  )
                {
                  $html_string .= '<a href="'.route('get-completed-draft-invoices', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
                }
                elseif($item->primary_status == 3)
                {
                  if($item->ref_id == null){
                    $ref_no = '-';
                  }
                  $html_string .= $ref_no;
                }
                elseif($item->primary_status == 1)
                {
                  $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
                }
                return $html_string;
                break;

            case 'received_date':
                if(!$item->get_order_transactions->isEmpty())
                {
                $count = count($item->get_order_transactions);
                $html=Carbon::parse(@$item->get_order_transactions[$count - 1]->received_date)->format('d/m/Y');
                return $html;
                }
                else
                {
                return '--';
                }
                break;

            case 'payment_reference_no':
                if(!$item->get_order_transactions->isEmpty())
                {
                    $html='';
                    foreach($item->get_order_transactions as $key => $ot)
                    {
                    if($key==0)
                    {
                        $html.=$ot->get_payment_ref->payment_reference_no;
                    }
                    else
                    {
                        $html.=','.$ot->get_payment_ref->payment_reference_no;
                    }
                    }
                    return $html;
                }
                else
                {
                    return '--';
                }
                break;

            case 'sales_person':
                $html_string = '<div class="d-flex justify-content-center text-center">';
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="'.$item->id.'" class="fa fa-camera d-block payment_img mr-2" title="Payment Image"></a> </div>';
                return $html_string;
                break;

            case 'number_of_products':
                $html_string = $item->order_products->count();
                return $html_string;
                break;

            case 'created_at':
                return @$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y'): '--';
                break;

            case 'status':
                $html = '<span class="sentverification">'.@$item->statuses->title.'</span>';
                return $html;
                break;

            case 'target_ship_date':
                return @$item->target_ship_date != null ?  Carbon::parse($item->target_ship_date)->format('d/m/Y'): '--';
                break;

            case 'customer_ref_no':
                $html_string ='<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.@$item->customer->reference_number.'</b></a>';
                return $html_string;
                break;

            case 'customer':
                if($item->customer_id != null)
                {
                    if($item->customer['reference_name'] != null)
                    {
                    $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.$item->customer['reference_name'].'</b></a>';
                    }
                    else
                    {
                    $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
                    }
                }
                else{
                    $html_string = 'N.A';
                }

                return $html_string;
                break;

            case 'inv_no':
                if($item->in_status_prefix !== null || $item->in_ref_prefix !== null || $item->in_ref_id !== null)
                 {
                if($item->is_vat == 1)
                {
                  if($item->manual_ref_no !== null)
                  {
                    if($item->in_ref_id == $item->manual_ref_no)
                    {
                      $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id;
                    }
                    else
                    {
                      $ref_no = $item->in_ref_id;
                    }
                  }
                  else
                  {
                    $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id;
                  }
                }
                else
                {
                  $ref_no = @$item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
                }
                }
                else
                {
                    $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->in_ref_id;
                }
                $html_string = '<a href="'.route('get-completed-invoices-details', ['id' => $item->id]).'" title="View Detail"><b>'.$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="quot_'.$item->id.'">
                                    <label class="custom-control-label" for="quot_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                break;

        }
    }

    public static function returnFilterColumnEcom($column, $item, $keyword) {
        switch ($column) {
            case 'ref_id':
                $result = $keyword;
                if (strstr($result,'-'))
                {
                    $item = $item->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
                }
                else
                {
                    $resultt = preg_replace("/[^0-9]/", "", $result );
                    $item = $item->orWhere('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
                }
                break;

            case 'sales_person':
                $item->whereHas('customer', function($q) use($keyword){
                    $q->whereHas('primary_sale_person', function($q) use($keyword){
                        $q->where('name','LIKE', "%$keyword%");
                    });
                  });
                break;

            case 'customer_ref_no':
                $item->whereHas('customer', function($q) use($keyword){
                    $q->where('reference_number','LIKE', "%$keyword%");
                });
                break;

            case 'customer':
                $item->whereHas('customer', function($q) use($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
                break;

        }
    }

}
