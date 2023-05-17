<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class WarehouseProduct extends Model
{
    use HasEvents;
    public function getWarehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function getProduct()
    {
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }
}
