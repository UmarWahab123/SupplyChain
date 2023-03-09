<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sales\Customer;

class PoGroup extends Model
{
    // protected $with = ["purchase_orders"];

    public function po_group_history($ref_id, $column_name, $new_value) {
        return $this->hasMany('App\PoGroupProductHistory')->where([['ref_id', $ref_id], ['column_name', $column_name], ['new_value',$new_value]])->orderBy('id', 'desc');
    }

    public function po_group_detail(){
        return $this->hasMany('App\Models\Common\PoGroupDetail', 'po_group_id', 'id')->where('status',1);
    }

    public function po_group_detail_single(){
        return $this->hasOne('App\Models\Common\PoGroupDetail', 'po_group_id', 'id')->where('status',1);
    }

    public function po_courier(){
        return $this->belongsTo('App\Models\Common\Courier', 'courier', 'id');
    }

    public function ToWarehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function FromWarehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'from_warehouse_id', 'id');
    }

    public function po_group_product_details(){
        return $this->hasMany('App\Models\Common\PoGroupProductDetail', 'po_group_id', 'id')->where('status',1);
    }

    public function po_group_product_details_one(){
        return $this->hasOne('App\Models\Common\PoGroupProductDetail', 'po_group_id', 'id')->where('status',1);
    }

    public function purchase_orders(){
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_group_id', 'id');
    }

    public function find_customers($groups, $id)
    {
      $group_pos = $groups->purchase_orders;
      $customer_ids = [];
      if($group_pos->count() > 0)
      {
        foreach ($group_pos as $po)
        {
            $po_detail = $po->PurchaseOrderDetail()->where('product_id',$id)->get();
            foreach ($po_detail as $pod) {
                array_push($customer_ids, $pod->customer_id);
            }
        }
      }

      $customers = Customer::select('id','reference_name')->whereIn('id',$customer_ids)->get();
      return $customers;
    }

    public function courier(){
        return $this->belongsTo('App\Models\Common\Courier', 'courier', 'id');
    }

    public static function POGroupSorting($request, $query)
    {
        $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
        if ($request['column_name'] == 'group_no')
        {
            $query->orderBy('ref_id', $sort_order);
        }
        elseif ($request['column_name'] == 'bl_awb')
        {
            $query->orderBy('bill_of_landing_or_airway_bill', $sort_order);
        }
        elseif ($request['column_name'] == 'courier')
        {
            $query->leftJoin('couriers as c', 'c.id', 'po_groups.courier')->orderBy('c.title', $sort_order);
        }
        elseif ($request['column_name'] == 'quantity')
        {
            $query->leftJoin('po_group_product_details as p', 'p.po_group_id', 'po_groups.id')->orderBy(\DB::Raw('sum(p.quantity_inv)'), $sort_order)->groupBy('po_groups.id');
        }
        elseif ($request['column_name'] == 'weight')
        {
            $query->leftJoin('po_group_product_details as p', 'p.po_group_id', 'po_groups.id')->orderBy(\DB::Raw('sum(p.total_gross_weight)'), $sort_order)->groupBy('po_groups.id');
        }
        elseif ($request['column_name'] == 'issue_date')
        {
            $query->orderBy('created_at', $sort_order);
        }
        elseif ($request['column_name'] == 'po_total')
        {
            $query->orderBy(\DB::Raw('case when total_buying_price_in_thb_with_vat !=null then total_buying_price_in_thb_with_vat+0 else total_buying_price_in_thb+0 end'), $sort_order);
        }
        elseif ($request['column_name'] == 'target_receive_date')
        {
            $query->orderBy('target_receive_date', $sort_order);
        }
        elseif ($request['column_name'] == 'tax')
        {
            $query->orderBy('tax', $sort_order);
        }
        elseif ($request['column_name'] == 'freight')
        {
            $query->orderBy('freight', $sort_order);
        }
        elseif ($request['column_name'] == 'landing')
        {
            $query->orderBy('landing', $sort_order);
        }
        elseif ($request['column_name'] == 'warehouse')
        {
            $query->leftJoin('warehouses as w', 'w.id', 'po_groups.warehouse_id')->orderBy('w.warehouse_title', $sort_order);
        }
        else
        {
            $query->orderBy('id', 'DESC');
        }
        return $query;
    }
}
