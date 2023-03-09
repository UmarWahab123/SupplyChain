<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourierType extends Model
{
    public function couriers(){
        return $this->hasMany('App\Models\Common\Courier');
    }
}
