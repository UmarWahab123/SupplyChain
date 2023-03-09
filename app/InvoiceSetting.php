<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $table = 'invoice_settings';
    protected $fillable = ['company_name','billing_email' , 'billing_phone' , 'billing_fax' , 'billing_address' , 'billing_country' , 'billing_state' , 'billing_city' , 'billing_zip' , 'created_by'];

    public function country(){
        return $this->belongsTo('App\Models\Common\Country','billing_country','id');
    }

    public function state(){
        return $this->belongsTo('App\Models\Common\State','billing_state','id');
    }

    public function user(){
        return $this->belongsTo('App\User','created_by','id');
    }
}
