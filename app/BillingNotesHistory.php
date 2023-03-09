<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingNotesHistory extends Model
{
    public function customer()
    {
        return $this->belongsTo('App\Models\Sales\Customer', 'customer_id', 'id');
    }
}
