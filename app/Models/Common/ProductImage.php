<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class ProductImage extends Model
{
    use HasEvents;
    protected $table = 'product_images';
    protected $fillable = ['product_id', 'image'];

    public function prouctImages()
    {
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }
    public function woocommerce_enabled()
    {
       return $this->belongsTo('App\Models\Common\ShareProduct','product_id','product_id')->where('store_type', 'woocommerce');
    }
}
