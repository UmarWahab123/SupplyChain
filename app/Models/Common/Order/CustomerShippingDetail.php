<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class CustomerShippingDetail extends Model
{
    public function getcountry(){
        return $this->belongsTo('App\Models\Common\Country', 'shipping_country', 'id');
    }

    public function getstate(){
        return $this->belongsTo('App\Models\Common\State', 'shipping_state', 'id');
    }
}
