<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SalesWarehouse extends Model
{
    public function getwarehouse(){
    	return $this->belongsTo('App\User', 'warehouse_id', 'id');
    }

    // For Yajra
    public function users(){
    	return $this->belongsTo('App\User', 'warehouse_id', 'id');
    }
}
