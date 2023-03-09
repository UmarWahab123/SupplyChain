<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransferDocumentReservedQuantity extends Model
{
    public function stock_m_out()
    {
        return $this->belongsTo('App\Models\Common\StockManagementOut', 'stock_id', 'id');
    }
    public function spoilage_table()
    {
        return $this->belongsTo('App\Models\Common\Spoilage', 'spoilage_type', 'id');
    }
    public function po()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrder', 'po_id', 'id');
    }
    public function po_detail()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'pod_id', 'id');
    }
    public function inbound_pod()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'inbound_pod_id', 'id');
    }
}
