<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class ProductCustomerFixedPrice extends Model
{
    protected $table = 'product_customer_fixed_prices';
    protected $fillable = ['product_id', 'customer_id', 'fixed_price', 'expiration_date', 'product_description'];

    public function products()
    {
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function customers()
    {
        return $this->belongsTo('App\Models\Sales\Customer', 'customer_id', 'id');
    }
}
