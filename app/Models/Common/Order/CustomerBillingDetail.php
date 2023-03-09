<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerBillingDetail extends Model
{
	use SoftDeletes;
    protected $table = 'customer_billing_details';
    protected $primaryKey = 'id';
    protected $fillable = ['customer_id','title','show_title','billing_contact_name','billing_email','company_name','billing_phone','cell_number','billing_fax','billing_address','billing_country','billing_state','billing_city','billing_zip','tax_id','is_default','is_default_shipping','status','created_by','created_at','updated_at','deleted_at'];
    //
    public function getcountry(){
        return $this->belongsTo('App\Models\Common\Country', 'billing_country', 'id');
    }

    public function getstate(){
        return $this->belongsTo('App\Models\Common\State', 'billing_state', 'id');
    }
}
