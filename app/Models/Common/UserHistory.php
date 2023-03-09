<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    public function roles(){
        return $this->belongsTo('App\Models\Common\Role', 'new_value', 'id');
    }

    public function roles_second(){
        return $this->belongsTo('App\Models\Common\Role', 'old_value', 'id');
    }
}
