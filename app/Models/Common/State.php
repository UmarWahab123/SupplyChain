<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'states';

    protected $fillable = ['abbrevation','name','country_id','status'];

    public function country(){
    	return $this->belongsTo('App\Models\Common\Country');
    }

    public function customer(){
    	return $this->hasMany('App\Models\Sales\Customer', 'state', 'id');
    }

    public function getSupplierState(){
    	return $this->belongsTo('App\Models\Sales\Supplier', 'state', 'id');
    }
}
