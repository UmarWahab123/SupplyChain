<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    public function getcountry(){
        return $this->belongsTo('App\Models\Common\Country', 'billing_country', 'id');
    }

    public function getstate(){
        return $this->belongsTo('App\Models\Common\State', 'billing_state', 'id');
    }
}
