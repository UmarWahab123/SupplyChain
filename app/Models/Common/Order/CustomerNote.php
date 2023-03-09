<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    protected $fillable = ['customer_id','note_title','note_description','user_id'];

    public function getuser(){
    	return $this->belongsTo('App\User','user_id','id');
    }
}
