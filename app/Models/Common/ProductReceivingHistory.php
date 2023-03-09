<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class ProductReceivingHistory extends Model
{
    public function get_pod(){
    	return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'pod_id', 'id');
    }

    public function get_po_group_product_detail(){
    	return $this->belongsTo('App\Models\Common\PoGroupProductDetail', 'p_g_p_d_id', 'id');
    }

    public function get_user(){
    	return $this->belongsTo('App\User', 'updated_by', 'id');
    }
}
