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


class PurchaseListDatatable {

    public static function returnAddColumn($column, $item, $getWarehouses) {
        switch ($column) {
            case 'remarks':
                $notes = $item->order_product_note !=null ? $item->order_product_note:'';
                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($notes){
                    $note = mb_substr($notes->note, 0, 30, 'UTF-8');
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="font-weight-bold d-block show-notes mr-2" title="View Notes">'.$note.' ...</a>';
                }

                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
                        </div>';
                return $html_string;
                break;

            case 'supply_to':
                $html_string = '<select class="form-control warehouses" name="warehouses[]" id="warehouses_'.$item->id.'">
                <option value="" selected disabled>Choose Warehouses</option>';
                if($getWarehouses)
                {
                foreach ($getWarehouses as $value)
                {
                    $condition = @$item->warehouse_id == $value->id ? 'selected' : "";
                    $html_string .= '<option '.$condition.' value="'.$value->id.'">'.$value->warehouse_title.'</option>';
                }
                }
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'supply_from':
                $html_string = '<select class="form-control suppliers" name="suppliers[]" id="suppliers_'.$item->id.'">';
                $html_string .= '<option value="" selected disabled>Choose Supply From</option>';
                $html_string .= '<optgroup label="Suppliers">';
                $getSuppliersByCat=@$item->product->supplier_products;
                if($getSuppliersByCat && $getSuppliersByCat->count() > 0)
                {
                    foreach($getSuppliersByCat as $supplierCat)
                    {
                      $getSuppliers=$supplierCat->supplier;

                        $value = $item->supplier_id == $getSuppliers['id'] ? 'selected' : "";
                        $html_string .= '<option '.$value.' value="s-'.$getSuppliers['id'].'">'.$getSuppliers['reference_name'].'</option>';

                    }
                }
                $html_string .= ' </optgroup>';
                $html_string .= '<optgroup label="Warehouses">';
                if($getWarehouses->count() > 0)
                {
                  foreach ($getWarehouses as $value)
                  {
                    $selected = $item->from_warehouse_id == $value->id ? 'selected' : "";
                    $html_string .= '<option '.$selected.' value="w-'.$value->id.'">'.$value->warehouse_title.'</option>';
                  }
                }
                $html_string .= '</optgroup>';
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'bill_unit':
                $html_string = @$item->product->units->title;
                return $html_string;
                break;

            case 'quantity':
                $html_string = $item->quantity != null ? $item->quantity : "--";
                $html_string.= ' '. @$item->product->sellingUnits->title;
                return $html_string;
                break;

            case 'pieces':
                $html_string = $item->number_of_pieces != null ? $item->number_of_pieces : "--";
                return $html_string;
                break;

            case 'delivery_date':
                $html_string = $item->get_order->delivery_request_date != null ? Carbon::parse($item->get_order->delivery_request_date)->format('d/m/Y') : "N.A";
                return $html_string;
                break;

            case 'target_ship_date':
                $html_string = $item->get_order->target_ship_date != null ? Carbon::parse($item->get_order->target_ship_date)->format('d/m/Y') : "N.A";
                return $html_string;
                break;

            case 'purchase_date':
                $html_string = $item->get_order->converted_to_invoice_on != null ? Carbon::parse(@$item->get_order->converted_to_invoice_on)->format('d/m/Y H:m A') : "N.A";
                return $html_string;
                break;

            case 'refrence_code':
                $refrence_code = $item->product_id != null && $item->product != null ? $item->product->refrence_code : "N.A";
                if ($refrence_code != 'N.A') {
                    return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"><b>'.$refrence_code.'</b></a>';
                }
                return '--';
                break;

            case 'supplier_product_ref':
                if($item->supplier_id != null)
                {
                    $supplier_id = $item->supplier_id;
                }
                else
                {
                    $supplier_id = $item->product->supplier_id;
                }
                // $getProductDefaultSupplier = @$item->product->supplier_products->where('supplier_id',$supplier_id)->first();
                $getProductDefaultSupplier = @$item->product->supplier_products;
                if ($getProductDefaultSupplier != null)
                {
                    $getProductDefaultSupplier = $getProductDefaultSupplier->where('supplier_id',$supplier_id)->first();
                }
                if($getProductDefaultSupplier)
                {
                    if($getProductDefaultSupplier->product_supplier_reference_no != null)
                    {
                        return $getProductDefaultSupplier->product_supplier_reference_no;
                    }
                    else
                    {
                        return "N.A";
                    }
                }
                else
                {
                    return "N.A";
                }
                break;

            case 'reference_name':
                if($item->get_order->customer->reference_name != null)
                {
                    $html_string = @$item->get_order->customer->reference_name;
                }
                else
                {
                    $html_string = 'N/A';
                }
                return $html_string;
                break;

            case 'category_id':
                $html_string = @$item->product->productSubCategory->title;
                return $html_string;
                break;

            case 'primary_category':
                $html_string = @$item->product->productCategory->title;
                return $html_string;
                break;

            case 'short_desc':
                $html_string = @$item->product->short_desc;
                return $html_string;
                break;

            case 'ref_id':
                if ($item->get_order != null) {
                    if($item->get_order->status_prefix !== null || $item->get_order->ref_prefix !== null){
                        return '<a target="_blank" href="'.route('get-completed-draft-invoices',$item->get_order->id).'"><b>'.@$item->get_order->status_prefix.'-'.$item->get_order->ref_prefix.$item->get_order->ref_id.'</b></a>';
                      }else{
                        return '<a target="_blank" href="'.route('get-completed-draft-invoices',$item->get_order->id).'"><b>'.@$item->get_order->customer->primary_sale_person->get_warehouse->order_short_code.@$item->get_order->customer->CustomerCategory->short_code.@$item->get_order->ref_id.'</b></a>';
                      }
                }
                return '--';
                break;

            case 'sale':
                $html_string = $item->get_order->user->name;
                return $html_string;
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox" style="margin-left:10px;" align="center">
                <input type="checkbox" class="custom-control-input individual_check" id="individual_check'.$item->id.'" value="'.$item->id.'" name="individual_check">
                <label class="custom-control-label" for="individual_check'.$item->id.'"></label>
                    </div>';
                return $html_string;
                break;
        }
    }


    public static function returnFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'refrence_code':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.refrence_code','LIKE', "%$keyword%");
                });
                break;

            case 'reference_name':
                $item->whereHas('get_order', function($q) use($keyword){
                    $q->whereHas('customer', function($q) use($keyword){
                        $q->where('customers.reference_name','LIKE', "%$keyword%");
                    });
                });
                break;

            case 'short_desc':
                $item->whereHas('product', function($q) use($keyword){
                    $q->where('products.short_desc','LIKE', "%$keyword%");
                });
                break;

            case 'ref_id':
                $item->whereHas('get_order', function($q) use($keyword){
                    $d = substr($keyword, 1);
                    $q->where('orders.ref_id','LIKE', "%$d%");
                });
                break;

            case 'sale':
                $item->whereHas('get_order', function($q) use($keyword){
                    $q->whereHas('user', function($q) use($keyword){
                        $q->where('users.name','LIKE', "%$keyword%");
                    });
                });
                break;

            case 'primary_category':
                $item->whereHas('product', function($q) use($keyword) {
                    $q->whereHas('productCategory', function($qq) use($keyword) {
                        $qq->where('title', 'LIKE',"%$keyword%");
                    });
                });
                break;

            case 'category_id':
                $item->whereHas('product', function($q) use($keyword) {
                    $q->whereHas('productSubCategory', function($qq) use($keyword) {
                        $qq->where('title', 'LIKE',"%$keyword%");
                    });
                });
                break;

            case 'bill_unit':
                $item->whereHas('product', function($q) use($keyword) {
                    $q->whereHas('units', function($qq) use($keyword) {
                        $qq->where('title', 'LIKE',"%$keyword%");
                    });
                });
                break;

            case 'remarks':
                $item->whereHas('order_product_note', function($q) use($keyword) {
                    $q->where('note', 'LIKE', "%$keyword%");
                });
                break;


        }
    }

}
