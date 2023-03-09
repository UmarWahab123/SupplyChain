<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class ProductFixedPrice extends Model
{
    use HasEvents;
    public function custType()
    {
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'customer_type_id', 'id');
    }
}
