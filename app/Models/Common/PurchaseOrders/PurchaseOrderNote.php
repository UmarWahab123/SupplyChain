<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderNote extends Model
{
    protected $fillable = ['po_id','note','created_by'];
}
