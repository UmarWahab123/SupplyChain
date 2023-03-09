<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoTransactionHistory extends Model
{
    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function po(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder','po_id','id');
    }
}
