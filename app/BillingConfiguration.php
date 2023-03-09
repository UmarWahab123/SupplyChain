<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingConfiguration extends Model
{
    protected $fillable = [
    	'type'
    ];

     public function currency(){
    	return $this->belongsTo('App\Models\Common\Currency', 'currency_id', 'id');
    }
}
