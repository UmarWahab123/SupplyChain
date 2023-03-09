<?php
namespace App\Helpers;
use App\Models\Common\Configuration;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;

class MyHelper{
	
	public static function getApiCredentials(){


        $query =	Configuration::all();
        // dd($query[0]);

		$api_credentials= array(
			"email_notification"=>$query[0]->email_notification,
		);

		return $api_credentials;

	}
	public function updateWarehouseProduct($warehouseProduct)
	{
		//Code to update the values correctly if there is any issue in stock
    return response()->json(['success' => true]);
        $pids = PurchaseOrder::where('status',21)->whereHas('PoWarehouse',function($qq)use($warehouseProduct){
                $qq->where('from_warehouse_id',$warehouseProduct->warehouse_id);
              })->pluck('id')->toArray();
              $pqty =  PurchaseOrderDetail::whereIn('po_id',$pids)->where('product_id',$warehouseProduct->product_id)->sum('quantity');

              $stock_qty = (@$warehouseProduct->current_quantity != null) ? @$warehouseProduct->current_quantity:' 0';

              $stck_out = StockManagementOut::select('quantity_out,warehouse_id')->where('product_id',$warehouseProduct->product_id)->where('warehouse_id',$warehouseProduct->warehouse_id)->sum('quantity_out');
              $stck_in = StockManagementOut::select('warehouse_id,quantity_in')->where('product_id',$warehouseProduct->product_id)->where('warehouse_id',$warehouseProduct->warehouse_id)->sum('quantity_in');

              $current_stock_all = round($stck_in,3) - abs(round($stck_out,3));
              $warehouseProduct->current_quantity = round($current_stock_all,3);
              $warehouseProduct->save();
              
              $ids =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($warehouseProduct){
                $qq->where('product_id',$warehouseProduct->product_id);
                $qq->where('from_warehouse_id',$warehouseProduct->warehouse_id);
              })->whereNull('ecommerce_order')->pluck('id')->toArray();

              $ids1 =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($warehouseProduct){
                $qq->where('product_id',$warehouseProduct->product_id);
                $qq->whereNull('from_warehouse_id');
              })->whereHas('user_created',function($query) use($warehouseProduct){
                  $query->where('warehouse_id',$warehouseProduct->warehouse_id);
                })->whereNull('ecommerce_order')
              ->pluck('id')->toArray();

              $ordered_qty0 =  OrderProduct::whereIn('order_id',$ids)->where('product_id',$warehouseProduct->product_id)->sum('quantity');

              $ordered_qty1 =  OrderProduct::whereIn('order_id',$ids1)->where('product_id',$warehouseProduct->product_id)->sum('quantity');

              $ordered_qty = $ordered_qty0 + $ordered_qty1 + $pqty;

              //To Update ECOM orders
              $ecom_ids =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($warehouseProduct){
                $qq->where('product_id',$warehouseProduct->product_id);
                $qq->where('from_warehouse_id',$warehouseProduct->warehouse_id);
              })->where('ecommerce_order',1)->pluck('id')->toArray();

              $ecom_ids1 =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($warehouseProduct){
                $qq->where('product_id',$warehouseProduct->product_id);
                $qq->whereNull('from_warehouse_id');
              })->whereHas('user_created',function($query) use($warehouseProduct){
                  $query->where('warehouse_id',$warehouseProduct->warehouse_id);
                })->where('ecommerce_order',1)
              ->pluck('id')->toArray();
              
              $ecom_ordered_qty0 =  OrderProduct::whereIn('order_id',$ecom_ids)->where('product_id',$warehouseProduct->product_id)->sum('quantity');

              $ecom_ordered_qty1 =  OrderProduct::whereIn('order_id',$ecom_ids1)->where('product_id',$warehouseProduct->product_id)->sum('quantity');

              $ecom_ordered_qty = $ecom_ordered_qty0 + $ecom_ordered_qty1;
              $warehouseProduct->reserved_quantity = number_format($ordered_qty,3,'.','');
              $warehouseProduct->ecommerce_reserved_quantity = number_format($ecom_ordered_qty,3,'.','');
              $warehouseProduct->available_quantity = number_format($warehouseProduct->current_quantity - ($warehouseProduct->reserved_quantity + $warehouseProduct->ecommerce_reserved_quantity),3,'.','');
              $warehouseProduct->save();

              return $warehouseProduct;
	}
}