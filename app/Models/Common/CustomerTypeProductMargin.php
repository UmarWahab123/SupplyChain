<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class CustomerTypeProductMargin extends Model
{
    use HasEvents;
    protected $fillable = ['product_id','customer_type_id','default_margin','default_value'];

    public function products()
    {
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function margins()
    {
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'customer_type_id', 'id');
    }
}
