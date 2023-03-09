<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'group_id', 'id');
    }

    public function def_or_last_supplier(){
        return $this->belongsTo('App\Models\Common\Supplier', 'new_value', 'id');
    }

    public function old_def_or_last_supplier(){
        return $this->belongsTo('App\Models\Common\Supplier', 'old_value', 'id');
    }

    public function new_productSubCategory(){
        return $this->belongsTo('App\Models\Common\ProductCategory', 'new_value', 'id');
    }

    public function old_productSubCategory(){
        return $this->belongsTo('App\Models\Common\ProductCategory', 'old_value', 'id');
    }

    public function newProductType(){
        return $this->belongsTo('App\Models\Common\ProductType', 'new_value', 'id');
    }

    public function oldProductType(){
        return $this->belongsTo('App\Models\Common\ProductType', 'old_value', 'id');
    }

    public function oldUnits(){
        return $this->belongsTo('App\Models\Common\Unit', 'old_value', 'id');
    }

    public function newUnits(){
        return $this->belongsTo('App\Models\Common\Unit', 'new_value', 'id');
    }

   
}
