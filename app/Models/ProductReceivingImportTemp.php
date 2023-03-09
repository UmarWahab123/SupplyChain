<?php

namespace App\Models;

use App\Models\ProductReceivingImportTemp;
use Illuminate\Database\Eloquent\Model;

class ProductReceivingImportTemp extends Model
{
	public function po_group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'group_id', 'id');
    }

    public function purchase_order(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }

    public static function create($group_id, $po_id, $pod_id, $id, $prod_ref_no, $user_id){
    	$obj = new ProductReceivingImportTemp;
    	$obj->user_id = $user_id;
    	$obj->group_id = $group_id;
    	$obj->po_id = $po_id;
    	$obj->pod_id = $pod_id;
    	$obj->p_c_id = $id;
    	$obj->prod_ref_no = $prod_ref_no;
    	$obj->save();

    	return $obj;
    }
}
