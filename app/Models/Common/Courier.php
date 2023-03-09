<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
     public function courier_type(){
        return $this->belongsTo('App\CourierType', 'courier_type_id', 'id');
    }
}
