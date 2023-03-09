<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnitHistory extends Model
{
    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function unit_detail(){
        return $this->belongsTo('App\Models\Common\Unit', 'unit_id', 'id');
    }
}
