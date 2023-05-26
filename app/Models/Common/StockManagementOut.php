<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
// use App\Common\Models\StockManagementOut;
use App\Common\Models\ProductCategory;
use App\Models\Common\Product;
use App\Models\Common\StockOutHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\Order\Order;
use Auth;
use Carbon\Carbon;

class StockManagementOut extends Model
{
    public function stock_out_order(){
    	return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }
    public function stock_out_po(){
      return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }

     public function order_product(){
        return $this->belongsTo('App\Models\Common\Order\OrderProduct', 'order_product_id', 'id');
    }

    public function get_po_group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'po_group_id', 'id');
    }

    public function stock_out_purchase_order_detail(){
    	return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'p_o_d_id', 'id');
    }

    public function get_stock_in(){
    	return $this->belongsTo('App\Models\Common\StockManagementIn', 'smi_id', 'id');
    }

    public function get_product(){
    	return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function get_warehouse(){
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function supplier(){
        return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }

    public function po_detail(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'p_o_d_id', 'id');
    }

    public function get_warehouse_ex(){
        return $this->belongsTo('App\Models\Common\Warehouse', 'user_warehouse_id', 'id');
    }

    public function get_manual_adjustments($request)
    {
        $stock = StockManagementOut::select('stock_management_outs.product_id','stock_management_outs.order_id','stock_management_outs.supplier_id','stock_management_outs.warehouse_id as from_warehouse_id','stock_management_outs.id','stock_management_outs.note as vat','stock_management_outs.title as total_price','stock_management_outs.title as unit_price','stock_management_outs.quantity_out as qty_shipped','stock_management_outs.quantity_out as pcs_shipped','stock_management_outs.quantity_out as number_of_pieces','stock_management_outs.cost as total_price_with_vat','stock_management_outs.quantity_out as quantity','stock_management_outs.created_at','stock_management_outs.cost as actual_cost','stock_management_outs.note as vat_amount_total','stock_management_outs.title as type_id','stock_management_outs.note as discount','stock_management_outs.warehouse_id as user_warehouse_id')
        ->whereNull('order_id')->whereNull('order_product_id')->whereNull('po_id')->whereNull('p_o_d_id')->whereNull('po_group_id')->whereNull('supplier_id')->whereNull('quantity_in')->whereNotNull('cost')->with('get_product.productCategory','get_warehouse_ex','get_product.productSubCategory','get_product.productType');
          if($request->warehouse_id != null)
          {
            $stock->where('stock_management_outs.warehouse_id',$request->warehouse_id);
          }
          if($request->product_id != '')
          {
            $stock = $stock->where('stock_management_outs.product_id' , $request->product_id);
          }
          if($request->p_c_id != "null" && $request->p_c_id != null)
          {
            // do something here
            $p_cat_id = ProductCategory::select('id','parent_id')->where('parent_id',$request->p_c_id)->pluck('id')->toArray();
            $product_ids = Product::select('id','category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $stock = $stock->whereIn('stock_management_outs.product_id',$product_ids);
          }
          else
          {
            if($request->prod_category != null)
            {
              $cat_id_split = explode('-',$request->prod_category);
              // dd($cat_id_split);
              if($cat_id_split[0] == 'sub')
              {
                // $filter_sub_categories = ProductCategory::where('parent_id','!=',0)->where('title',$request->prod_sub_category)->pluck('id')->toArray();
                $product_ids = Product::select('id','category_id','status')->where('category_id', $cat_id_split[1])->where('status',1)->pluck('id');
                $stock = $stock->whereIn('stock_management_outs.product_id',$product_ids);
              }
              else
              {

                $p_cat_ids = Product::select('id','primary_category','status')->where('primary_category', $cat_id_split[1])->where('status',1)->pluck('id');
                $stock = $stock->whereIn('stock_management_outs.product_id',$p_cat_ids);
              }
            }
        }

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

          if($request->product_type != null)
          {

            $p_cat_ids = Product::select('id','type_id','status')->where('type_id', $request->product_type)->where('status',1)->pluck('id');
                $stock = $stock->whereIn('stock_management_outs.product_id',$p_cat_ids);
          }

          if($request->product_type_2 != null)
          {
            $p_cat_ids = Product::select('id','type_id_2','status')->where('type_id_2', $request->product_type_2)->where('status',1)->pluck('id');
                $stock = $stock->whereIn('stock_management_outs.product_id',$p_cat_ids);
          }

        return $stock;
    }

    public function get_manual_adjustments_for_export($request)
    {
        $stock = StockManagementOut::select('stock_management_outs.product_id','stock_management_outs.order_id','stock_management_outs.supplier_id','stock_management_outs.warehouse_id as from_warehouse_id','stock_management_outs.id','stock_management_outs.note as vat','stock_management_outs.title as total_price','stock_management_outs.title as unit_price','stock_management_outs.quantity_out as qty_shipped','stock_management_outs.quantity_out as pcs_shipped','stock_management_outs.quantity_out as number_of_pieces','stock_management_outs.cost as total_price_with_vat','stock_management_outs.quantity_out as quantity','stock_management_outs.created_at','stock_management_outs.cost as actual_cost','stock_management_outs.note as vat_amount_total','stock_management_outs.title as type_id','stock_management_outs.note as discount','stock_management_outs.warehouse_id as user_warehouse_id')
        ->whereNull('order_id')->whereNull('order_product_id')->whereNull('po_id')->whereNull('p_o_d_id')->whereNull('po_group_id')->whereNull('supplier_id')->whereNull('quantity_in')->whereNotNull('cost');
          if(@$request['warehouse_id'] != null)
          {
            $stock->where('stock_management_outs.warehouse_id',$request['warehouse_id']);
          }
          if(@$request['p_c_id'] != "null" && @$request['p_c_id'] != null)
          {
            // do something here
            $p_cat_id = ProductCategory::select('id','parent_id')->where('parent_id',$request['p_c_id'])->pluck('id')->toArray();
            $product_ids = Product::select('id','category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $stock = $stock->whereIn('stock_management_outs.product_id',$product_ids);
          }
          else
          {
            if (@$request['product_id_select'] != null) {
              $id_split = explode('-', $request['product_id_select']);
              $id_split = (int)$id_split[1];
              if(@$request['className'] == 'parent')
              {
                $p_cat_ids = Product::select('id','primary_category','status')->where('primary_category', $id_split)->where('status',1)->pluck('id');
                $stock = $stock->whereIn('stock_management_outs.product_id',$p_cat_ids);
              }
              else if(@$request['className'] == 'child')
              {
                $product_ids = Product::select('id','category_id','status')->where('category_id', $id_split)->where('status',1)->pluck('id');
                $stock = $stock->whereIn('stock_management_outs.product_id',$product_ids);
              }
              else
              {
                $stock = $stock->where('stock_management_outs.product_id' , $id_split);
              }
            }
        }

        if(@$request['from_date_exp'] != null)
          {
              $date = str_replace("/","-",$request['from_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              $stock->whereDate('created_at','>=',$date);
          }
          if(@$request['to_date_exp'] != null)
          {
              $date = str_replace("/","-",$request['to_date_exp']);
              $date =  date('Y-m-d',strtotime($date));
              $stock->whereDate('created_at','<=',$date);
          }
        return $stock;
    }

    public static function addManualAdjustment($stock, $order_product, $quantity_diff, $warehouse_id,$balance = null, $existing_order = false, $user_id = null)
    {
      $stock_out                   = new StockManagementOut;
      $stock_out->smi_id           = $stock->id;
      $stock_out->order_id         = $order_product->order_id;
      $stock_out->order_product_id = $order_product->id;
      $stock_out->product_id       = $order_product->product_id;
      $stock_out->original_order_id = @$order_product->order_id;
      $stock_out->title       = $existing_order ? 'Order confirmed' : 'Quantity Shipped Updated in '.@$order_product->get_order->full_inv_no.' Complete Invoice';
      if($quantity_diff < 0)
      {
          $stock_out->quantity_out = $balance !== null ? -$balance : $quantity_diff;
          $stock_out->available_stock = $balance !== null ? -$balance : $quantity_diff;
          // $stock_out->available_stock = null;
      }
      else
      {
          $stock_out->quantity_in  = $balance !== null ? $balance : $quantity_diff;
          // $stock_out->available_stock  = $balance !== null ? $balance : $quantity_diff;
          $stock_out->available_stock  = null;
      }
      $stock_out->created_by       = @$user_id != null ? $user_id : @Auth::user()->id;
      $stock_out->warehouse_id     = $warehouse_id;
      $stock_out->save();
      $stock_out->cost     = $stock_out->cost == null ? ($stock_out->get_product != null ? round($stock_out->get_product->selling_price,3) : null) : $stock_out->cost;
      $stock_out->save();
      if($quantity_diff < 0 && $existing_order == false)
      {
        $dummy_order = Order::createManualOrder($stock_out, @$order_product->order_id, 'Quantity shipped updated by user '.$user_id.' '. @Auth::user()->user_name . ' in '. @$order_product->get_order->full_inv_no .' Invoice on '. Carbon::now(), $user_id);
      }
      // else
      // {
      //   $dummy_order = PurchaseOrder::createManualPo($stock_out);
      // }

      $setVatIn = StockManagementOut::setVatIn($quantity_diff, $order_product);
      // $find_stock_from_which_order_deducted = $this->findStockFromWhicOrderIsDeducted($quantity_diff,$stock, $stock_out);

      return $stock_out;
    }

    public static function findStockFromWhicOrderIsDeducted($quantity_diff, $stock, $stock_out, $order_product = null, $from_which_stock_it_will_deduct = null)
    {
      if($quantity_diff < 0)
      {
        //To find from which stock the order will be deducted
              $find_stock = $from_which_stock_it_will_deduct != null ? $from_which_stock_it_will_deduct : $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
              if($find_stock->count() > 0)
              {
                  foreach ($find_stock as $out)
                  {

                      if(abs($stock_out->available_stock) > 0)
                      {
                              if($out->available_stock >= abs($stock_out->available_stock))
                              {
                                  $history_quantity = $stock_out->available_stock;
                                  $stock_out->parent_id_in .= $out->id.',';
                                  $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                  $stock_out->available_stock = 0;
                                  if($from_which_stock_it_will_deduct == null){
                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,abs($history_quantity));
                                  }
                              }
                              else
                              {
                                  $history_quantity = $out->available_stock;
                                  $stock_out->parent_id_in .= $out->id.',';
                                  $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                  $out->available_stock = 0;
                                  if($from_which_stock_it_will_deduct == null){
                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,round(abs($history_quantity),4));
                                  }
                                }
                              $out->save();
                              $stock_out->save();
                      }
                  }
              }
      }
      else
      {
        $find_stock = $from_which_stock_it_will_deduct != null ? $from_which_stock_it_will_deduct : StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
        if($find_stock->count() > 0)
        {
            foreach ($find_stock as $out) {

                if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
                {
                        if($stock_out->available_stock >= abs($out->available_stock))
                        {
                            $out->parent_id_in .= $stock_out->id.',';
                            $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                            $out->available_stock = 0;
                        }
                        else
                        {
                            $out->parent_id_in .= $out->id.',';
                            $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                            $stock_out->available_stock = 0;
                        }
                        $out->save();
                        $stock_out->save();
                }
            }
        }
      }

      return true;
    }

    public static function setVatIn($quantity_diff, $order_product)
    {
      if($quantity_diff > 0)
      {
        $stock_out_history = StockOutHistory::where('order_product_id',$order_product->id)->orderBy('id','desc')->get();
        if($stock_out_history->count() > 0)
        {
          foreach($stock_out_history as $his)
          {
            if($his->quantity > $quantity_diff)
            {
              $order_product->total_vat_in = $order_product->total_vat_in - $his->vat_in;
              $order_product->save();

              $his->quantity = $his->quantity - $quantity_diff;
              $his->save();
              $stock_out_from = StockManagementOut::find($his->stock_out_from_id);

              $his->sales = $his->quantity * $order_product->unit_price;
              $his->total_cost = $his->quantity * @$stock_out_from->cost;
              $his->vat_out = $his->quantity * ($order_product->unit_price_with_vat - $order_product->unit_price);
              if($stock_out_from->p_o_d_id != null)
              {
                  $his->vat_in = @$stock_out_from->stock_out_purchase_order_detail != null ? (($stock_out_from->stock_out_purchase_order_detail->pod_vat_actual_price_in_thb * @$order_product->product->unit_conversion_rate) * $his->quantity) : null;
              }
              $his->save();
              $order_product->total_vat_in += $his->vat_in;
              $order_product->save();

              if($stock_out_from)
              {
                $stock_out_from->available_stock = $stock_out_from->available_stock + $quantity_diff;
                $stock_out_from->save();
              }
              break;
            }
            else
            {
              $order_product->total_vat_in = $order_product->total_vat_in - $his->vat_in;
              $order_product->save();

              $or_qty = $his->quantity;

              $remaining = $quantity_diff - $or_qty;

              $his->quantity = 0;
              $his->save();
              $stock_out_from = StockManagementOut::find($his->stock_out_from_id);

              $his->sales = 0;
              $his->total_cost = 0;
              $his->vat_out = 0;
              if($stock_out_from->p_o_d_id != null)
              {
                  $his->vat_in = 0;
              }
              $his->save();

              if($stock_out_from)
              {
                $stock_out_from->available_stock = $stock_out_from->available_stock + $or_qty;
                $stock_out_from->save();
              }

              $quantity_diff = $remaining;
              if($quantity_diff == 0 || $quantity_diff < 0)
              {
                break;
              }
            }
          }
        }
      }

      return true;
    }

    public static function setVatInManualAdjustment($quantity_diff, $stock_out)
    {
      if($quantity_diff > 0)
      {
        $stock_out_history = StockOutHistory::where('stock_out_id',$stock_out->id)->orderBy('id','desc')->get();
        if($stock_out_history->count() > 0)
        {
          foreach($stock_out_history as $his)
          {
            if($his->quantity > $quantity_diff)
            {
              $his->quantity = $his->quantity - $quantity_diff;
              $his->save();
              $stock_out_from = StockManagementOut::find($his->stock_out_from_id);
              $his->total_cost = $his->quantity * @$stock_out_from->cost;

              $his->save();
              $stock_out->save();

              if($stock_out_from)
              {
                $stock_out_from->available_stock = $stock_out_from->available_stock + $quantity_diff;
                $stock_out_from->save();
              }
              break;
            }
            else
            {
              $or_qty = $his->quantity;
              $remaining = $quantity_diff - $or_qty;
              $his->quantity = 0;
              $his->save();
              $stock_out_from = StockManagementOut::find($his->stock_out_from_id);
              $his->total_cost = 0;
              $his->save();

              if($stock_out_from)
              {
                $stock_out_from->available_stock = $stock_out_from->available_stock + $or_qty;
                $stock_out_from->save();
              }

              $quantity_diff = $remaining;
              if($quantity_diff == 0 || $quantity_diff < 0)
              {
                break;
              }
            }
          }
        }
      }

      return true;
    }

    public static function doSortby($request, $products, $total_items_sale, $total_items_gp)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if($request['sortbyparam'] == 1)
        {
            $sort_variable  = 'sales_total';
        }
        elseif($request['sortbyparam'] == 2)
        {
            $sort_variable  = 'total_cost_c';
        }
        elseif($request['sortbyparam'] == 3)
        {
            $sort_variable  = 'marg';
        }
        elseif($request['sortbyparam'] == 'supplier')
        {
            $products->leftJoin('suppliers as s', 's.id' , '=', 'stock_out_histories.supplier_id')->orderBy('s.reference_name', $sort_order);
        }
        elseif($request['sortbyparam'] == 'vat_out')
        {
            // $sort_variable  = 'vat_out';
            $products->orderBy(\DB::raw('sum(stock_out_histories.vat_out)'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'percent_sales')
        {
            $products->orderBy(\DB::raw('(sum(stock_out_histories.sales) /'.$total_items_sale.')*100'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'vat_in')
        {
            // $sort_variable  = 'vat_in';
            $products->orderBy(\DB::raw('sum(stock_out_histories.vat_in)'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'gp')
        {
            $products->orderBy(\DB::raw('sum(stock_out_histories.sales) - sum(stock_out_histories.total_cost)'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'percent_gp')
        {
            $products->orderBy(\DB::raw('(sum(stock_out_histories.sales) - sum(stock_out_histories.total_cost)) /'.$total_items_gp.'*100'), $sort_order);
        }

        if($sort_variable != null)
        {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }
}
