<?php

namespace App\Models\Common\order;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderAddressDetail extends Model
{
    //
    public function getstate(){
        return $this->belongsTo('App\Models\Common\State', 'state', 'id');
    }
}
