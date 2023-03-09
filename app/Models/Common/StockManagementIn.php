<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class StockManagementIn extends Model
{
    protected $fillable = ['title','po_id','p_o_d_id','product_id','quantity_in','created_by','warehouse_id','expiration_date'];

    public function stock_out(){
    	return $this->hasMany('App\Models\Common\StockManagementOut', 'smi_id', 'id');
    }
    public function stock_out_available(){
    	return $this->hasMany('App\Models\Common\StockManagementOut', 'smi_id', 'id')->where('available_stock','>',0);
    }

    public function getWarehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'warehouse_id', 'id');
    }

    public function product() {
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }
}
