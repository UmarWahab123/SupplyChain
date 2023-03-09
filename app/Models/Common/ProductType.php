<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class ProductType extends Model
{
    use HasEvents;
	protected $table = 'types';

    public function supplier(){
        return $this->hasMany('App\Models\Common\Supplier', 'product_type_id', 'id');
    }

    public function product()
    {
    	return $this->belongsTo('App\Models\Common\Product', 'product_type_id', 'id');
    }
    
    public function get_manual_adjustments($request, $cat_id)
    {
        $stock = StockManagementOut::select('stock_management_outs.product_id','stock_management_outs.order_id','stock_management_outs.supplier_id','stock_management_outs.warehouse_id as from_warehouse_id','stock_management_outs.id','stock_management_outs.note as vat','stock_management_outs.title as total_price','stock_management_outs.title as unit_price','stock_management_outs.quantity_out as qty_shipped','stock_management_outs.quantity_out as pcs_shipped','stock_management_outs.quantity_out as number_of_pieces','stock_management_outs.cost as total_price_with_vat','stock_management_outs.quantity_out as quantity','stock_management_outs.created_at','stock_management_outs.cost as actual_cost','stock_management_outs.note as vat_amount_total','stock_management_outs.title as type_id','stock_management_outs.note as discount','stock_management_outs.warehouse_id as user_warehouse_id')
        ->whereNull('order_id')->whereNull('order_product_id')->whereNull('po_id')->whereNull('p_o_d_id')->whereNull('po_group_id')->whereNull('supplier_id')->whereNull('quantity_in')->whereNotNull('cost');
        $stock->whereHas('get_product',function($cat) use ($cat_id){
            $cat->where('type_id',$cat_id);
        });

        if($request->from_date != null)
          {
              $date = str_replace("/","-",$request->from_date);
              $date =  date('Y-m-d',strtotime($date));
              $stock->whereDate('created_at','>=',$date);
          }
          if($request->to_date != null)
          {
              $date = str_replace("/","-",$request->to_date);
              $date =  date('Y-m-d',strtotime($date));
              $stock->whereDate('created_at','<=',$date);
          }

        return $stock;
    }

     public function get_manual_adjustments_for_export($request, $cat_id)
    {
        $stock = StockManagementOut::select('stock_management_outs.product_id','stock_management_outs.order_id','stock_management_outs.supplier_id','stock_management_outs.warehouse_id as from_warehouse_id','stock_management_outs.id','stock_management_outs.note as vat','stock_management_outs.title as total_price','stock_management_outs.title as unit_price','stock_management_outs.quantity_out as qty_shipped','stock_management_outs.quantity_out as pcs_shipped','stock_management_outs.quantity_out as number_of_pieces','stock_management_outs.cost as total_price_with_vat','stock_management_outs.quantity_out as quantity','stock_management_outs.created_at','stock_management_outs.cost as actual_cost','stock_management_outs.note as vat_amount_total','stock_management_outs.title as type_id','stock_management_outs.note as discount','stock_management_outs.warehouse_id as user_warehouse_id')
        ->whereNull('order_id')->whereNull('order_product_id')->whereNull('po_id')->whereNull('p_o_d_id')->whereNull('po_group_id')->whereNull('supplier_id')->whereNull('quantity_in')->whereNotNull('cost');
        $stock->whereHas('get_product',function($cat) use ($cat_id){
            $cat->where('type_id',$cat_id);
        });

        if($request['from_date_exp'] != null)
          {
              $date = str_replace("/","-",$request['from_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              $stock->whereDate('created_at','>=',$date);
          }
          if($request['to_date_exp'] != null)
          {
              $date = str_replace("/","-",$request['to_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              $stock->whereDate('created_at','<=',$date);
          }

        return $stock;
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
        elseif($request['sortbyparam'] == 'product_type')
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
