<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class Warehouse extends Model
{
    use HasEvents;
    public function getCompany()
    {
        return $this->belongsTo('App\Models\Common\Company', 'company_id', 'id');
    }

    public function get_warehouse_products()
    {
        return $this->hasMany('App\Models\Common\WarehouseProduct', 'warehouse_id', 'id');
    }

    public function manual_adjustment(){
        return $this->hasMany('App\Models\Common\StockManagementOut', 'warehouse_id', 'id')->whereNull('order_id')->whereNull('order_product_id')->whereNull('po_id')->whereNull('p_o_d_id')->whereNull('po_group_id')->whereNull('supplier_id')->whereNull('quantity_in')->whereNotNull('cost');
    }

    public static function doSortby($request, $products, $total_items_sale, $total_items_gp)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if($request['sortbyparam'] == 1)
        {
            $sort_variable  = 'sales';
        }
        elseif($request['sortbyparam'] == 2)
        {
            $sort_variable  = 'products_total_cost';
        }
        elseif($request['sortbyparam'] == 3)
        {
            $sort_variable  = 'marg';
        }
        elseif($request['sortbyparam'] == 'office')
        {
            $sort_variable  = 'warehouse_title';
        }
        elseif($request['sortbyparam'] == 'vat_out')
        {
            $sort_variable  = 'vat_amount_total';
        }
        elseif($request['sortbyparam'] == 'sales')
        {
            $sort_variable  = 'sales';
        }
        elseif($request['sortbyparam'] == 'vat_in')
        {
            $sort_variable  = 'import_vat_amount';
        }
        elseif($request['sortbyparam'] == 'percent_sales')
        {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) /'.$total_items_sale.' END )*100'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'gp')
        {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'percent_gp')
        {
            $products->orderBy(\DB::raw('((CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))) /'.$total_items_gp.'*100'), $sort_order);
        }

        if($sort_variable != null)
        {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }
}
