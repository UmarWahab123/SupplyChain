<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class RevertedPurchaseOrder extends Model
{
    public function PurchaseOrder()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }
    public function po_group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'group_id', 'id');
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Common\Product','product_id','id');
    }
    public function supplier()
    {
        return $this->belongsTo('App\Models\Common\Supplier','supplier_id','id');
    }
}
