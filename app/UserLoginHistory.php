<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLoginHistory extends Model
{
    public function user_detail(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
