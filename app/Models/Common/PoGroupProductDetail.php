<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\ProductHistory;
use DB;
use Auth;
class PoGroupProductDetail extends Model
{
    public function product()
    {
        return $this->belongsTo('App\Models\Common\Product','product_id','id');
    }

    public function get_supplier()
    {
        return $this->belongsTo('App\Models\Common\Supplier','supplier_id','id');
    }

    public function get_warehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse','from_warehouse_id','id');
    }

    public function po_group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'po_group_id', 'id');
    }

    public function purchase_order(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }

    public function order(){
        return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }
    public function purchase_order_detail(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'pod_id', 'id');
    }

    public function get_good_type()
    {
        return $this->belongsTo('App\Models\Common\ProductType','good_type','id');
    }

    public function averageCurrency($ids,$product_id,$field)
    {
        if($field == 'currency_conversion_rate')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum(DB::raw('1/currency_conversion_rate'));

            return $find_entries;
        }

        if($field == 'buying_price_in_thb')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('unit_price_with_vat_in_thb');
            // $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('unit_price_in_thb');

            return $find_entries;
        }

        if($field == 'buying_price_in_thb_wo_vat')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('unit_price_in_thb');

            return $find_entries;
        }

        if($field == 'total_buying_price_in_thb')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('total_unit_price_with_vat_in_thb');
            // $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('total_unit_price_in_thb');

            return $find_entries;
        }

        if($field == 'total_buying_price_in_thb_wo_vat')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('total_unit_price_in_thb');

            return $find_entries;
        }

        if($field == 'buying_price')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('pod_unit_price_with_vat');
            // $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('unit_price_in_thb');

            return $find_entries;
        }

        if($field == 'buying_price_wo_vat')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('pod_unit_price');

            return $find_entries;
        }

        if($field == 'total_buying_price')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('pod_total_unit_price_with_vat');
            // $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('total_unit_price_in_thb');

            return $find_entries;
        }

        if($field == 'total_buying_price_wo_vat')
        {
            $find_entries = PurchaseOrderDetail::whereIn('po_id',$ids)->where('product_id',$product_id)->sum('pod_total_unit_price');

            return $find_entries;
        }
    }

     public function saveProductHistory($product_id, $po_group_id, $column, $old_value, $new_value)
    {
        if($new_value !== $old_value){
            $product_history              = new ProductHistory;
            $product_history->user_id     = Auth::user()->id;
            $product_history->product_id  = $product_id;
            $product_history->group_id    = $po_group_id;
            $product_history->column_name = $column;
            $product_history->old_value   = $old_value;
            $product_history->new_value   = $new_value;
            $product_history->save();
        }
        return true;
    }
}
