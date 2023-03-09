<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['title'];

    public function products_unit(){
        return $this->hasMany('App\Models\Common\Product', 'buying_unit', 'id');
    }

    public function sellingUnits(){
        return $this->belongsTo('App\Models\Common\Product', 'selling_unit', 'id');
    }
}
