<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;

class PoGroupStatusHistory extends Model
{
    public function get_po_group(){
    	return $this->belongsTo('App\Models\Common\PoGroup', 'po_group_id', 'id');
    }

    public function get_user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
