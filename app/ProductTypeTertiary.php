<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductTypeTertiary extends Model
{
    protected $fillable = ['title'];

    public function supplier(){
        return $this->hasMany('App\Models\Common\Supplier', 'product_type_id', 'id');
    }

    public static function  doSortby($request, $products, $total_items_sale, $total_items_gp)
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
        elseif($request['sortbyparam'] == 'product_type_3')
        {
            $sort_variable  = 'title';
        }
        elseif($request['sortbyparam'] == 'vat_out')
        {
            $sort_variable  = 'vat_amount_total';
        }
        elseif($request['sortbyparam'] == 'percent_sales')
        {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END /'.$total_items_sale.')*100'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'vat_in')
        {
            $sort_variable  = 'import_vat_amount';
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
