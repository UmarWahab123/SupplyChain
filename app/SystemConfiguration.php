<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class SystemConfiguration extends Model
{
    protected $table = 'system_configurations';
    protected $fillable = ['type','subject','detail'];

    public function users(){
    	return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
