<?php

namespace App\Http\Controllers\Observer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\WarehouseProduct;
use App\Helpers\MyHelper;

class WarehouseProductObsever extends Controller
{
    public function update_reserve_quantity_old(Request $request){
    	// dd('testt');
    	// $wh_pro = WarehouseProduct::where('id', $request->warehouse_products_id)->first();
     //    $my_helper =  new MyHelper;
     //    $res_wh_update = $my_helper->updateWarehouseProduct($wh_pro);
        
        $wh_pro = WarehouseProduct::where('id', $request->warehouse_products_id)->first();
    	// dd($wh_pro);
    	$wh_pro->ecommerce_reserved_quantity = $request->ecommerce_reserved_quantity;
    	$wh_pro->available_quantity          = $request->available_quantity;
    	$wh_pro->save();

        
    }
}
