<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerSecondaryUser extends Model
{
    protected $fillable=['user_id','customer_id','status'];
    public function customers()
    {
        return $this->belongsTo('App\Models\Sales\Customer','customer_id','id');
    }

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function secondarySalesPersons()
    {
        return $this->belongsTo('App\User','user_id','id');
    }
}
