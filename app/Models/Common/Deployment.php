<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    public function customerCategory()
    {
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'price', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
