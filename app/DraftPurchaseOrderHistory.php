<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftPurchaseOrderHistory extends Model
{
    protected $fillable = ['po_id', 'reference_number', 'column_name', 'old_value', 'new_value', 'user_id'];

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function product(){
        return $this->belongsTo('App\Models\Common\Product','reference_number','id'); 
}
}
