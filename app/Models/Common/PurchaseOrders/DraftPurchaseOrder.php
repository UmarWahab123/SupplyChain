<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;

class DraftPurchaseOrder extends Model
{
    protected $fillable = ['status','total','created_by','supplier_id','from_warehouse_id','to_warehouse_id','payment_terms_id','transfer_date'];

    public function getSupplier()
    {
        return $this->belongsTo('App\Models\Common\Supplier','supplier_id','id');
    }

    public function getFromWarehoue()
    {
        return $this->belongsTo('App\Models\Common\Warehouse','from_warehouse_id','id');
    }

    public function getWarehoue()
    {
        return $this->belongsTo('App\Models\Common\Warehouse','to_warehouse_id','id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm','payment_terms_id','id');
    }

    public function createdBy()
    {
        return $this->belongsTo('App\User','created_by','id');
    }

    public function draftPoDetail()
    {
    	return $this->hasMany('App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail','po_id','id');
    }

     public function draft_po_notes(){
        return $this->hasMany('App\Models\Common\DraftPurchaseOrderNote', 'po_id', 'id');
    }
}
