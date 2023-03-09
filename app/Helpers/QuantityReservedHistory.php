<?php
namespace App\Helpers;
use App\Models\ProductQuantityHistory;
use App\Models\Common\WarehouseProduct;
use Auth;
use App\Helpers\MyHelper;

class QuantityReservedHistory{

	public static function quantityReservedHistoryFun($order,$id,$quantity,$type,$order_type,$user_id,$warehouse_product){
		$data = [];

		$history = new ProductQuantityHistory;
		$history->product_id = $order->product_id;
		$history->order_product_id = $order->id;
		$history->order_id = $id;
		$history->quantity = $quantity;
		$history->type = $type;
		$history->order_type = $order_type;
		$history->user_id = $user_id;
		$history->warehouse = $warehouse_product->getWarehouse != null ? $warehouse_product->getWarehouse->warehouse_title : '--';
		$history->current_quantity = $warehouse_product->current_quantity;
		$history->reserved_quantity = $warehouse_product->reserved_quantity;
		$history->available_quantity = $warehouse_product->available_quantity;
		if($history->save())
		{
			$data['success'] = true;
			return $data;
		}
		else
		{
			$data['success'] = false;
			return $data;
		}
	}

	public static function updateReservedQuantity($order_prod,$status,$sign)
	{
    // if($order_prod->from_warehouse_id != null)
    // {
    //   $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
    // }
    // else
    // {
    //   $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
    // }
    // if($warehouse_product != null)
    // {
    //   $my_helper =  new MyHelper;
    //   $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_product);
    // }

		if($order_prod->from_warehouse_id != null)
        {
          $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
        }
        else
        {
          $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
        }
        if($warehouse_product != null)
        {
        	if($sign == 'subtract')
        	{
          		$warehouse_product->reserved_quantity -= round($order_prod->quantity,3);
        	}
        	else
        	{
          		$warehouse_product->reserved_quantity += round($order_prod->quantity,3);
        	}
          $warehouse_product->available_quantity = round($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity),3);
          $warehouse_product->save();

          $res = (new QuantityReservedHistory)->quantityReservedHistoryFun($order_prod,$order_prod->order_id,$order_prod->quantity,$status,'Order',Auth::user()->id,$warehouse_product);

          return $res;
        }
	}
	public static function updateEcomReservedQuantity($order_prod,$status,$sign)
	{
    // if($order_prod->from_warehouse_id != null)
    // {
    //   $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
    // }
    // else
    // {
    //   $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
    // }
    // if($warehouse_product != null)
    // {
    //   $my_helper =  new MyHelper;
    //   $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_product);
    // }
		if($order_prod->from_warehouse_id != null)
        {
          $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
        }
        else
        {
          $warehouse_product = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
        }
        if($warehouse_product != null)
        {
        	if($sign == 'subtract')
        	{
          		$warehouse_product->ecommerce_reserved_quantity -= round($order_prod->quantity,3);
        	}
        	else
        	{
          		$warehouse_product->ecommerce_reserved_quantity += round($order_prod->quantity,3);
        	}
          $warehouse_product->available_quantity = round($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity),3);
          $warehouse_product->save();

          $res = (new QuantityReservedHistory)->quantityReservedHistoryFun($order_prod,$order_prod->order_id,$order_prod->quantity,$status,'Order',Auth::user()->id,$warehouse_product);
          return $res;
        }
	}
	public static function updateCurrentReservedQuantity($order_prod,$status,$sign, $user_id = null)
	{
		// if($order_prod->from_warehouse_id != null)
  //   {
  //     $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
  //   }
  //   else
  //   {
  //     $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
  //   }
  //   $my_helper =  new MyHelper;
  //   $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

    if($order_prod->from_warehouse_id != null)
    {
      $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
    }
    else
    {
      $warehouse_id = $order_prod->user_warehouse_id != null ? $order_prod->user_warehouse_id : (@$order_prod->get_order->from_warehouse_id != null ? @$order_prod->get_order->from_warehouse_id : auth()->user()->id);
      $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$warehouse_id)->first();
    }
    $warehouse_products->current_quantity -= round($order_prod->qty_shipped, 3);
    if($order_prod->get_order->ecommerce_order == 1)
    {
        $warehouse_products->ecommerce_reserved_quantity -= round($order_prod->quantity,3);
    }
    else
    {
        $warehouse_products->reserved_quantity -= round($order_prod->quantity,3);
    }

    $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
    $warehouse_products->save();

    $user_id = $user_id != null ? $user_id : Auth::user()->id;
    $res = (new QuantityReservedHistory)->quantityReservedHistoryFun($order_prod,$order_prod->order_id,$order_prod->quantity,$status,'Order', $user_id, $warehouse_products);

         return $res;
	}

	public static function updateCurrentQuantity($order_prod,$quantity,$sign,$w_id)
	{
        // if($w_id != null)
        // {
        //   $warehouse_products = WarehouseProduct::where('warehouse_id', @$w_id)->where('product_id', $order_prod->product_id)->first();
        // }
        // else
        // {
        //     if($order_prod->from_warehouse_id != null)
        //     {
        //       $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
        //     }
        //     else
        //     {
        //       $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
        //     }
        // }
        // $my_helper =  new MyHelper;
        // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

        if($w_id != null)
        {
          $warehouse_products = WarehouseProduct::where('warehouse_id', @$w_id)->where('product_id', $order_prod->product_id)->first();
        }
        else
        {
            if($order_prod->from_warehouse_id != null)
            {
              $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->from_warehouse_id)->first();
            }
            else
            {
              $warehouse_products = WarehouseProduct::where('product_id',$order_prod->product->id)->where('warehouse_id',$order_prod->user_warehouse_id)->first();
            }
        }

        if($sign == 'subtract')
        {
        	$warehouse_products->current_quantity -= round($quantity,3);
        }
        else
        {
        	$warehouse_products->current_quantity += round($quantity,3);
        }
        $warehouse_products->available_quantity = number_format($warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity),3,'.','');
        $warehouse_products->save();

        return $warehouse_products;
	}

	//Transfer document quantity updation
	public static function updateTDReservedQuantity($purchase_order,$p_o_d,$quantity,$new_value,$status,$sign)
	{
    // if($purchase_order->from_warehouse_id != null)
    // {
    //   $warehouse_product = WarehouseProduct::where('product_id',$p_o_d->product_id)->where('warehouse_id',$purchase_order->from_warehouse_id)->first();
    // }
    // else
    // {
    //   $warehouse_product = WarehouseProduct::where('product_id',$p_o_d->product->id)->where('warehouse_id',Auth::user()->warehouse_id)->first();
    // }
    // if($warehouse_product != null)
    // {
    //   $my_helper =  new MyHelper;
    //   $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_product);
    // }

    if($purchase_order->from_warehouse_id != null)
    {
      $warehouse_product = WarehouseProduct::where('product_id',$p_o_d->product_id)->where('warehouse_id',$purchase_order->from_warehouse_id)->first();
    }
    else
    {
      $warehouse_product = WarehouseProduct::where('product_id',$p_o_d->product->id)->where('warehouse_id',Auth::user()->warehouse_id)->first();
    }
    if($warehouse_product != null)
    {
    	if($sign == 'subtract')
    	{
      	$warehouse_product->reserved_quantity -= round($quantity,3);
    	}
    	else
    	{
      	$warehouse_product->reserved_quantity += round($quantity,3);
    	}
      $warehouse_product->available_quantity = round($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity),3);
      $warehouse_product->save();
      $res = (new QuantityReservedHistory)->quantityReservedHistoryFun($p_o_d,$purchase_order->id,$new_value,$status,'TD',Auth::user()->id,$warehouse_product);

      return $res;
    }
	}
	public static function updateTDCurrentQuantity($order_prod,$p_o_d,$quantity,$sign)
	{
    // if($order_prod->to_warehouse_id != null)
    // {
    //   $warehouse_products = WarehouseProduct::where('product_id',$p_o_d->product_id)->where('warehouse_id',$order_prod->to_warehouse_id)->first();
    // }
    // $my_helper =  new MyHelper;
    // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

    if($order_prod->to_warehouse_id != null)
    {
      $warehouse_products = WarehouseProduct::where('product_id',$p_o_d->product_id)->where('warehouse_id',$order_prod->to_warehouse_id)->first();
    }
    if($sign == 'subtract')
    {
    	$warehouse_products->current_quantity -= round($quantity,3);
    }
    else
    {
    	$warehouse_products->current_quantity += round($quantity,3);
    }
    $warehouse_products->available_quantity = number_format($warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity),3,'.','');
    $warehouse_products->save();

    return $warehouse_products;
	}

	public static function updateTDCurrentReservedQuantity($po,$order_product,$quantity,$status,$sign)
	{
    // if($po->from_warehouse_id != null)
    // {
    //   $warehouse_products = WarehouseProduct::where('product_id',$order_product->product_id)->where('warehouse_id',$po->from_warehouse_id)->first();
    // }
    // $my_helper =  new MyHelper;
    // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

	if($po->from_warehouse_id != null)
    {
      $warehouse_products = WarehouseProduct::where('product_id',$order_product->product_id)->where('warehouse_id',$po->from_warehouse_id)->first();
    }
    $warehouse_products->current_quantity -= round($quantity, 3);
    $warehouse_products->reserved_quantity -= round($order_product->quantity,3);

    $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
    $warehouse_products->save();
    $res = (new QuantityReservedHistory)->quantityReservedHistoryFun($order_product,$po->id,$order_product->quantity,$status,'TD',Auth::user()->id,$warehouse_products);

     return $res;
	}
}
