<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductSaleReportDetailHistory extends Model
{
    public function user()
    {
    	return $this->belongsTo('App\User', 'updated_by', 'id');
    }
    public function order_product()
    {
    	return $this->belongsTo('App\Models\Common\Order\OrderProduct', 'order_product_id', 'id');
    }
}
