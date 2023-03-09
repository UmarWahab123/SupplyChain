<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class Status extends Model
{
    use HasEvents;
    public function parent()
    {
        return $this->belongsTo('App\Models\Common\Status', 'parent_id', 'id');
    }
}

