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


class AccountingDashboardDatatable {

    public static function returnAddColumn($column, $item) {
        switch ($column) {
            case 'total_amount':
                return number_format($item->total_amount,2,'.',',');
                break;

            case 'ref_id':
                $ref_no = @$item->status_prefix.$item->ref_id;
                $html_string = '<a href="'.route('get-credit-note-detail', ['id' => $item->id]).'" title="View Order"><b>'.$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'sales_person':
                return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
                break;

            case 'status':
                $html = '<span class="sentverification">'.$item->statuses->title.'</span>';
                return $html;
                break;

            case 'memo':
                return @$item->memo != null ? @$item->memo : '--';
                break;

            case 'delivery_date':
                return @$item->credit_note_date != null ?  Carbon::parse($item->credit_note_date)->format('d/m/Y'): '--';
                break;

            case 'customer_ref_no':
                $ref_no = @$item->customer !== null ? @$item->customer->reference_number : '--';
                $html_string ='<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.@$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'customer':
                if($item->customer_id != null)
                {
                  if($item->customer['reference_name'] != null)
                  {
                    $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'.$item->customer['reference_name'].'</a>';
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

            case 'action':
                if($item->status == 31)
                {
                  $html_string = '--';
                }
                else
                {
                   $html_string = '
                   <a href="javascript:void(0);" class="actionicon deleteIcon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }

                  return $html_string;
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword) {
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


    public static function returnSupplierCreditAddColumn($column, $item) {
        switch ($column) {
            case 'total_amount':
                return number_format($item->total_with_vat,2,'.',',');
                break;

            case 'ref_id':
                $ref_no = $item->p_o_statuses->parent->prefix . $item->ref_id;
                $html_string = '<a href="'.route('get-supplier-credit-note-detail', ['id' => $item->id]).'" title="View Order"><b>'.$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'status':
                $html = '<span class="sentverification">'.$item->p_o_statuses->title.'</span>';
                return $html;
                break;

            case 'memo':
                return @$item->memo != null ? @$item->memo : '--';
                break;

            case 'credit_note_date':
                return @$item->invoice_date != null ?  Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                break;

            case 'supplier_ref_no':
                $ref_no = @$item->PoSupplier !== null ? @$item->PoSupplier->reference_number : '--';
                $html_string ='<a target="_blank" href="'.url('get-supplier-detail/'.@$item->supplier).'"><b>'.@$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'supplier':
                if($item->supplier_id != null)
                {
                  if($item->PoSupplier['reference_name'] != null)
                  {
                    $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.@$item->supplier_id).'">'.$item->PoSupplier['reference_name'].'</a>';
                  }
                  else
                  {
                    $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.@$item->supplier_id).'">'. $item->PoSupplier['first_name'].' '.$item->PoSupplier['last_name'].'</a>';
                  }
                }
                else{
                  $html_string = 'N.A';
                }

                return $html_string;
                break;

            case 'action':
                if($item->status == 31)
                {
                  $html_string = '--';
                }
                else
                {
                   $html_string = '
                   <a href="javascript:void(0);" class="delete_supplier_icon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }

                  return $html_string;
                break;
        }
    }
    public static function returnSupplierCreditFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'ref_id':
                $result = $keyword;
                $item = $item->orWhere(DB::raw("CONCAT(`".$item->p_o_statuses->prefix."`,`ref_id`)"), 'LIKE', "%".$result."%");
                break;;

            case 'supplier_ref_no':
                $item->whereHas('PoSupplier', function($q) use($keyword){
                    $q->where('reference_number','LIKE', "%$keyword%");
                });
                break;

            case 'supplier':
                $item->whereHas('PoSupplier', function($q) use($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
                break;
        }
    }

    public static function returnSupplierDebitAddColumn($column, $item) {
        switch ($column) {
            case 'total_amount':
                return number_format($item->total_with_vat,2,'.',',');
                break;

            case 'ref_id':
                $ref_no = $item->p_o_statuses->parent->prefix . $item->ref_id;
                $html_string = '<a href="'.route('get-supplier-debit-note-detail', ['id' => $item->id]).'" title="View Order"><b>'.$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'status':
                $html = '<span class="sentverification">'.$item->p_o_statuses->title.'</span>';
                return $html;
                break;

            case 'memo':
                return @$item->memo != null ? @$item->memo : '--';
                break;

            case 'credit_note_date':
                return @$item->invoice_date != null ?  Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                break;

            case 'supplier_ref_no':
                $ref_no = @$item->PoSupplier !== null ? @$item->PoSupplier->reference_number : '--';
                $html_string ='<a target="_blank" href="'.url('get-supplier-detail/'.@$item->supplier).'"><b>'.@$ref_no.'</b></a>';
                return $html_string;
                break;

            case 'supplier':
                if($item->supplier_id != null)
                {
                  if($item->PoSupplier['reference_name'] != null)
                  {
                    $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.@$item->supplier_id).'">'.$item->PoSupplier['reference_name'].'</a>';
                  }
                  else
                  {
                    $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.@$item->supplier_id).'">'. $item->PoSupplier['first_name'].' '.$item->PoSupplier['last_name'].'</a>';
                  }
                }
                else{
                  $html_string = 'N.A';
                }

                return $html_string;
                break;

            case 'action':
                $html_string = '
                   <a href="javascript:void(0);" class="delete_supplier_debit_icon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                  return $html_string;
                break;
        }
    }
    public static function returnSupplierDebitFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'ref_id':
                $result = $keyword;
                $item = $item->orWhere(DB::raw("CONCAT(`".$item->p_o_statuses->prefix."`,`ref_id`)"), 'LIKE', "%".$result."%");
                break;;

            case 'supplier_ref_no':
                $item->whereHas('PoSupplier', function($q) use($keyword){
                    $q->where('reference_number','LIKE', "%$keyword%");
                });
                break;

            case 'supplier':
                $item->whereHas('PoSupplier', function($q) use($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
                break;
        }
    }

}
