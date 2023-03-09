<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class CompanyBank extends Model
{
    public function getBanks(){
        return $this->belongsTo('App\Models\Common\Bank', 'bank_id', 'id');
    }
}
