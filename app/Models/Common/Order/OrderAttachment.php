<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class OrderAttachment extends Model
{
	protected $fillable = ['order_id','file_title','file'];

    public function get_order(){
    	return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }
}
