<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = ['abbrevation','name','status'];

    public function states(){
    	return $this->hasMany('App\Models\Common\State');
    }

    public function customer(){
    	return $this->hasMany('App\Models\Sales\Customer', 'country', 'id');
    }

    public function getSupplierCountry(){
        return $this->belongsTo('App\Models\Common\Supplier', 'country', 'id');
    }
}
