<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrdersHistory extends Model
{
    protected $table = 'purchase_orders_histories';
    protected $fillable = ['user_id','old_value','new_value','column_name','reference_number','order_id','order_product_id', 'po_id'];

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getOrder(){
        return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }

     public function units(){
        return $this->belongsTo('App\Models\Common\Unit', 'new_value', 'id');
    }

    public function from_warehouse(){
    	return $this->belongsTo('App\Models\Common\Warehouse','new_value','id');
    }
    
    public function product(){
        return $this->belongsTo('App\Models\Common\Product','reference_number','refrence_code'); 
    }
}
