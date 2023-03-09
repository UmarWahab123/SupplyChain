<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class CustomerHistory extends Model
{
     protected $fillable = ['user_id','customer_id','column_name','old_value','new_value'];


     public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function old_value_category(){
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'old_value', 'id');
    }
     public function new_value_category(){
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'new_value', 'id');
    }

     public function old_primary_sale(){
    	return $this->belongsTo('App\User', 'old_value', 'id');
    }

      public function new_primary_sale(){
      return $this->belongsTo('App\User', 'new_value', 'id');
    }

      public function old_secondary_sale(){
      return $this->belongsTo('App\User', 'old_value', 'id');
    }

      public function new_secondary_sale(){
      return $this->belongsTo('App\User', 'new_value', 'id');
    }

      public function old_value_payment_term(){
    	return $this->belongsTo('App\Models\Common\PaymentTerm', 'old_value', 'id');
    }
    public function new_value_payment_term(){
    	return $this->belongsTo('App\Models\Common\PaymentTerm', 'new_value', 'id');
    }
    public function customers(){
    	return $this->belongsTo('App\Models\Sales\Customer', 'customer_id', 'id');
    }
    
}


