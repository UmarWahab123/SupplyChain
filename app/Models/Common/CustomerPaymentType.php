<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class CustomerPaymentType extends Model
{
    protected $table = 'customer_payment_type';

    public function get_payment_type(){
    	return $this->belongsTo('App\Models\Common\PaymentType','payment_type_id','id');
    }
}
