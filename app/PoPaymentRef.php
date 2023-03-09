<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoPaymentRef extends Model
{
    public function getTransactions(){
        return $this->hasMany('App\PurchaseOrderTransaction','payment_reference_no','id');
    }

    public function PoSupplier()
    {
    	return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }
}
