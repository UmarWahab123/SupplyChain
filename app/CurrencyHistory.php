<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyHistory extends Model
{
     public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

     public function currency(){
    	return $this->belongsTo('App\Models\Common\Currency', 'currency_id', 'id');
    }
}
