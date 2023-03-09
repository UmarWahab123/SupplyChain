<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderStatusHistory extends Model
{
	protected $table = 'purchase_orders_status_history';

    protected $fillable = ['po_id', 'user_id','status','new_status'];

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
