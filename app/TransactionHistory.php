<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function order_tran(){
        return $this->belongsTo('App\OrderTransaction','order_transaction_id','id');
    }

    public function order(){
        return $this->belongsTo('App\Models\Common\Order\Order','order_id','id');
    }
}
