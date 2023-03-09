<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;
class DraftQuotation extends Model
{
    protected $with = ["draft_quotation_products","customer","draft_quotation_notes"];
	protected $fillable = ['customer_id','created_by','is_vat','manual_ref_no','from_warehouse_id'];

     public function draft_quotation_products(){
    	return $this->hasMany('App\Models\Common\Order\DraftQuotationProduct', 'draft_quotation_id', 'id');
    }

    public function draft_quotation_notes(){
        return $this->hasMany('App\Models\Common\Order\DraftQuotationNote', 'draft_quotation_id', 'id');
    }

    public function draft_quotation_attachments(){
        return $this->hasMany('App\Models\Common\Order\DraftQuotationAttachment', 'draft_quotation_id', 'id');
    }

    public function customer(){
        return $this->belongsTo('App\Models\Sales\Customer', 'customer_id', 'id');
    }

    public function billing_address(){
        return $this->belongsTo('App\Models\Common\Order\CustomerBillingDetail', 'billing_address_id', 'id');
    }

    public function shipping_address(){
        return $this->belongsTo('App\Models\Common\Order\CustomerBillingDetail', 'shipping_address_id', 'id');
    }

    public function user(){
        return $this->belongsTo('App\User', 'created_by', 'id');
    }
    public function o_user(){
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm','payment_terms_id','id');
    }

    public function from_warehouse(){
        return $this->belongsTo('App\Models\Common\Warehouse','from_warehouse_id','id');
    }
}
