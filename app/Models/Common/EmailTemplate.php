<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public function users(){
    	return $this->belongsTo('App\User', 'updated_by', 'id');
    }
}
