<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdersPaymentRef extends Model
{
    use SoftDeletes;
    public function getTransactions(){
        return $this->hasMany('App\OrderTransaction','payment_reference_no','id');
    }

    public function customer(){
        return $this->belongsTo('App\Models\Sales\Customer', 'customer_id', 'id');
    }


    public function get_payment_type(){
    	return $this->belongsTo('App\Models\Common\PaymentType','payment_method','id');
    }
}
