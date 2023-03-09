<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class PosIntegration extends Model
{
    protected $fillable = ['device_name', 'warehouse_id', 'warehouse_name', 'token', 'status'];
}
