<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftQuatationProductHistory extends Model
{
    protected $table = 'draft_quatation_product_histories';
    protected $fillable = ['user_id','old_value','new_value','column_name','reference_number'];

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function type_old_value(){
    	return $this->belongsTo('App\Models\Common\ProductType', 'old_value', 'id');
    }
    public function type_new_value(){
    	return $this->belongsTo('App\Models\Common\ProductType', 'new_value', 'id');
    }

    public function newCustomer(){
        return $this->belongsTo('App\Models\Sales\Customer', 'new_value', 'id');
    }

    public function oldCustomer(){
        return $this->belongsTo('App\Models\Sales\Customer', 'old_value', 'id');
    }
}
