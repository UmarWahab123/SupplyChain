<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;

class DraftPurchaseOrderDetailNote extends Model
{
    protected $table = 'draft_purchase_order_detail_notes';

    protected $fillable = ['draft_po_id', 'note'];
}
