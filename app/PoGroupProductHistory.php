<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class PoGroupProductHistory extends Model
{
   public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
      public function product_info(){
    	return $this->belongsTo('App\Models\Common\Product', 'order_product_id', 'id');
    }
    public function po_group(){
    	return $this->belongsTo('App\Models\Common\PoGroup','po_group_id','id');
    }
}
