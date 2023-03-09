<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDocument extends Model
{
    protected $fillable = ['po_id','file_name'];

    public function PurchaseOrder()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }
}
