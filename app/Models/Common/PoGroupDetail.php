<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class PoGroupDetail extends Model
{
    public function purchase_order(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'purchase_order_id', 'id');
    }

    public function po_group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'po_group_id', 'id');
    }
}
