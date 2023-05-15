<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use DB;

class StockOutHistory extends Model
{
    public function get_order(){
        return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }
    public function stock_out(){
        return $this->belongsTo('App\Models\Common\StockManagementOut', 'stock_out_from_id', 'id');
    }
    public function supplier(){
        return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }
    public function purchase_order_detail(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail','id','order_product_id');
    }
    public function get_order_product(){
        return $this->belongsTo('App\Models\Common\Order\OrderProduct', 'order_product_id', 'id');
    }
    public function setHistory($out,$stock_out,$order_product,$qty)
    {
        try{
        $new_stock_out_history = new StockOutHistory;
        $new_stock_out_history->stock_out_from_id = $out->id;
        $new_stock_out_history->stock_out_id = $stock_out->id;
        $new_stock_out_history->quantity = $qty != null ? $qty : ($order_product->qty_shipped != null ? abs($order_product->qty_shipped) : 0);
        $new_stock_out_history->order_id = $order_product->order_id;
        $new_stock_out_history->order_product_id = $order_product->id;
        $new_stock_out_history->supplier_id = $out->supplier_id;
        $new_stock_out_history->po_id = $out->po_id;
        $new_stock_out_history->pod_id = $out->p_o_d_id;
        $new_stock_out_history->save();

        $new_stock_out_history->sales = $new_stock_out_history->quantity * $order_product->unit_price;
        $new_stock_out_history->total_cost = $new_stock_out_history->quantity * $out->cost;
        $new_stock_out_history->vat_out = $new_stock_out_history->quantity * ($order_product->unit_price_with_vat - $order_product->unit_price);

        if($out->p_o_d_id != null)
        {
            $new_stock_out_history->vat_in = @$out->stock_out_purchase_order_detail != null ? (($out->stock_out_purchase_order_detail->pod_vat_actual_price_in_thb * @$order_product->product->unit_conversion_rate) * $new_stock_out_history->quantity) : null;
        }
        $new_stock_out_history->save();
        $order_product->total_vat_in += $new_stock_out_history->vat_in;
        $order_product->save();
        return true;
        }
        catch(\Excepion $e)
        {
            return $e->getMessage();
        }
    }
    public function setHistoryForManualAdjustments($out,$stock_out,$qty_out)
    {
        $new_stock_out_history = new StockOutHistory;
        $new_stock_out_history->stock_out_from_id = $out->id;
        $new_stock_out_history->stock_out_id = $stock_out->id;
        $new_stock_out_history->quantity = $qty_out;
        $new_stock_out_history->supplier_id = $out->supplier_id;
        $new_stock_out_history->po_id = $out->po_id;
        $new_stock_out_history->pod_id = $out->p_o_d_id;
        $new_stock_out_history->save();
        $new_stock_out_history->total_cost = $new_stock_out_history->quantity * floatval(trim($out->cost));
        $new_stock_out_history->save();
        return true;
    }
    public function setHistoryForPO($out,$stock_out,$order_product,$qty)
    {
        try{
        $new_stock_out_history = new StockOutHistory;
        $new_stock_out_history->stock_out_from_id = $out->id;
        $new_stock_out_history->stock_out_id = $stock_out->id;
        $new_stock_out_history->quantity = $qty != null ? $qty : 0;
        $new_stock_out_history->order_id = $stock_out->order_id;
        $new_stock_out_history->order_product_id = $stock_out->order_product_id;
        $new_stock_out_history->supplier_id = $out->supplier_id;
        $new_stock_out_history->po_id = $out->po_id;
        $new_stock_out_history->pod_id = $out->p_o_d_id;
        $new_stock_out_history->save();

        //to find unit price
        $unit_price = $stock_out->order_product != null ? ($stock_out->order_product->unit_price_with_discount != null ? $stock_out->order_product->unit_price_with_discount : ($stock_out->order_product->quantity > 0 ? $stock_out->order_product->total_price/$stock_out->order_product->quantity : 0)) : 0; 

        $new_stock_out_history->sales = $new_stock_out_history->quantity * $unit_price;
        $new_stock_out_history->total_cost = $new_stock_out_history->quantity * $out->cost;
        $new_stock_out_history->vat_out = $new_stock_out_history->quantity * (@$stock_out->order_product->unit_price_with_vat - @$stock_out->order_product->unit_price);

        if($out->p_o_d_id != null)
        {
            $new_stock_out_history->vat_in = @$out->stock_out_purchase_order_detail != null ? (($out->stock_out_purchase_order_detail->pod_vat_actual_price_in_thb * @$stock_out->order_product->product->unit_conversion_rate) * $new_stock_out_history->quantity) : null;
        }
        $new_stock_out_history->save();
        if($stock_out->order_product != null)
        {
            $stock_out->order_product->total_vat_in += $new_stock_out_history->vat_in;
            $stock_out->order_product->save();
        }
        return true;
        }
        catch(\Excepion $e)
        {
            return $e->getMessage();
        }
    }
    public function updateCostForOrders($stock_out)
    {
        foreach ($stock_out as $out) {
            $record = StockOutHistory::where('stock_out_from_id',$out->id)->get();
            foreach ($record as $red) {
                $red->total_cost = $red->quantity * $out->cost;
                $red->save();
            }
        }
        return true;
    }
    public static function doSortby($request, $query)
    {
      if($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'customer_id';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'customer_id';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 2 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'from_warehouse_id';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 2 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'from_warehouse_id';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'product_id';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'product_id';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'order_products.short_desc';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'order_products.short_desc';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 6 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'order_products.brand';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 6 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'order_products.brand';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 7 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'total_price';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 7 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'total_price';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 8 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'total_price_with_vat';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 8 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'total_price_with_vat';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 1)
      {
        $query->leftJoin('orders','orders.id', '=', 'stock_out_histories.order_id')->leftJoin('customers','customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_name', $sort_order);
      }
      elseif($request['sortbyparam'] == 2)
      {
        $query->leftJoin('orders','orders.id', '=', 'stock_out_histories.order_id')->leftJoin('users','users.id', '=', 'orders.user_id')->leftJoin('warehouses','warehouses.id', '=', 'users.warehouse_id')->orderBy('warehouses.warehouse_title', $sort_order);
      }
      elseif($request['sortbyparam'] == 3)
      {
        $query->leftJoin('order_products','stock_out_histories.order_product_id', '=', 'order_products.id')->leftJoin('products','products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $sort_order);
      }
      elseif($request['sortbyparam'] == 4)
      {
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy($sort_variable, $sort_order);
      }
      elseif($request['sortbyparam'] == 6)
      {
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy($sort_variable, $sort_order);
      }
      elseif($request['sortbyparam'] == 7)
      {
        $query->orderBy(DB::raw('(sales-vat_out)+0'), $sort_order);
      }
      elseif($request['sortbyparam'] == 8)
      {
        $query->orderBy(DB::raw('sales+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'status') 
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders', 'orders.id' , '=', 'stock_out_histories.order_id')->leftJoin('statuses', 'statuses.id' , '=', 'orders.status')->orderBy('statuses.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'ref_po_no')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'stock_out_histories.order_id')->orderBy('o.memo', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'po_no')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('purchase_order_details as pod', 'pod.order_product_id' , '=', 'stock_out_histories.order_product_id')->leftJoin('purchase_orders as po', 'po.id' , '=', 'pod.po_id')->orderBy('po.ref_id', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'sale_person')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'stock_out_histories.order_id')->leftJoin('users as u', 'u.id' , '=', 'o.user_id')->orderBy('u.name', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'delivery_date')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'stock_out_histories.order_id')->orderBy('o.delivery_request_date', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'created_date')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'stock_out_histories.order_id')->orderBy('o.converted_to_invoice_on', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'category')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('product_categories as pc', 'pc.id' , '=', 'p.primary_category')->orderBy('pc.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'type')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('types as t', 't.id' , '=', 'p.type_id')->orderBy('t.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'type_2')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('product_secondary_types as t', 't.id' , '=', 'p.type_id_2')->orderBy('t.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'selling_unit')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('units as u', 'u.id' , '=', 'p.selling_unit')->orderBy('u.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'selling_unit')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('units as u', 'u.id' , '=', 'p.selling_unit')->orderBy('u.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'unit_price')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy(\DB::raw('order_products.unit_price+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'discount')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy(\DB::raw('order_products.discount+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'net_price')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy(\DB::raw('order_products.actual_cost+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'vat_thb')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy(\DB::raw('order_products.vat_amount_total+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'vat_percent')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('order_products', 'order_products.id' , '=', 'stock_out_histories.order_product_id')->orderBy(\DB::raw('order_products.vat+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'note_two')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.vat+0'), $sort_order);
      }
      else
      {
        $query->orderBy('stock_out_histories.id','desc');
      }
      return $query;
    }
}
