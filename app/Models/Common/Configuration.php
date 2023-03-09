<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class Configuration extends Model
{
    use HasEvents;
    public function Currency(){
        return $this->belongsTo('App\Models\Common\Currency','currency_id','id');
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Common\Warehouse','woocom_warehouse_id','id');
    }
}
