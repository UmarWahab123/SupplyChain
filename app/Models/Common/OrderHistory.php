<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $table = 'order_histories';
    protected $fillable = ['user_id','old_value','new_value','column_name','reference_number'];

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

     public function units(){
        return $this->belongsTo('App\Models\Common\Unit', 'new_value', 'id');
    }

    public function from_warehouse(){
    	return $this->belongsTo('App\Models\Common\Warehouse','new_value','id');
    }

     public function supplier(){
        return $this->belongsTo('App\Models\Common\Supplier', 'new_value', 'id');
    }

    public function product(){
        return $this->belongsTo('App\Models\Common\Product','reference_number','refrence_code');
    }

    public function productType(){
        return $this->belongsTo('App\Models\Common\ProductType', 'new_value', 'id');
    }

    public function newCustomer(){
        return $this->belongsTo('App\Models\Sales\Customer', 'new_value', 'id');
    }

    public function oldCustomer(){
        return $this->belongsTo('App\Models\Sales\Customer', 'old_value', 'id');
    }
    
}
