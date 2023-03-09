<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class SupplierNote extends Model
{
    protected $fillable = ['supplier_id','note_title','note_description'];

    public function getuser()
    {
    	return $this->belongsTo('App\User','user_id','id');
    }
}
