<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class CustomerCategory extends Model
{
    use HasEvents;
    protected $fillable=['ecommr_enabled'];
    public function customer(){
        return $this->hasMany('App\Models\Sales\Customer', 'category_id', 'id');
    }

    public function margins(){
        return $this->hasMany('App\Models\Common\CustomerTypeProductMargin', 'customer_type_id', 'id');
    }

    public function categoryMargins(){
        return $this->hasMany('App\Models\Common\CustomerTypeCategoryMargin', 'customer_type_id', 'id');
    }

    public function categoryBanks(){
        return $this->hasMany('App\Models\Common\CompanyBank', 'customer_category_id', 'id');
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
        elseif($request['sortbyparam'] == 'customer_type')
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
            $sort_variable  = 'vat_in';
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
