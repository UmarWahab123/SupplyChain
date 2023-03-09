<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuantityHistory extends Model
{
    public function get_order(){
        return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }
    public function User(){
        return $this->belongsTo('App\User','user_id','id');
    }
    public function product(){
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }
}
