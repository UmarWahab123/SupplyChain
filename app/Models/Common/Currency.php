<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class Currency extends Model
{
	use HasEvents;
    public function Configuration()
    {
        return $this->hasMany('App\Models\Common\Configuration','currency_id','id');
    }
    public function user(){
        return $this->belongsTo('App\User', 'last_update_by', 'id');
    }

    function free_curr_convert($code, $base = DEFAULT_CURRENCY){
    	// dd($code);
	    $code = 'THB_'.$code;
	    $page = file('https://free.currconv.com/api/v7/convert?q='.$code.'&compact=ultra&apiKey=4f9fe413b10e9925c9b3');
	    $exRates = json_decode($page[0]);
	    
	    if (!empty($exRates->$code)) {
	      return $exRates->$code;
	   } 
	   else {
	     return false;
	   }
    }
}
