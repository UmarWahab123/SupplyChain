<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTransaction extends Model
{
    use SoftDeletes;

    public function order(){
        return $this->belongsTo('App\Models\Common\Order\Order','order_id','id');
    }

    public function get_payment_type(){
    	return $this->belongsTo('App\Models\Common\PaymentType','payment_method_id','id');
    }

    public function get_payment_ref(){
    	return $this->belongsTo('App\OrdersPaymentRef','payment_reference_no','id');
    }
}
