<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class DraftPurchaseOrderNote extends Model
{
	protected $table = 'draft_purchase_orders_notes';
    protected $fillable = ['po_id','note','created_by'];

}
