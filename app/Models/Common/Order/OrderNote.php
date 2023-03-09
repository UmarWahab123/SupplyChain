<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class OrderNote extends Model
{
    protected $fillable = ['note','type'];
}
