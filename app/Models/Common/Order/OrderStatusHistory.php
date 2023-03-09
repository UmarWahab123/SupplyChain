<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $fillable = ['order_id', 'user_id','status','new_status'];
    
    public function get_order(){
    	return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }

    public function get_user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
