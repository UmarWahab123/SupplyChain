<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class TempSupplier extends Model
{
    protected $fillable = [
        'reference_name',
        'company','first_name',
        'last_name','email',
        'phone','address_line_1',
        'country','state','city',
        'postalcode','currency_id',
        'credit_term','tax_id','user_id',
        'c_name','c_sur_name','c_email',
        'c_telehone_number','c_position','status'
    ];

    public function getcountry(){
        return $this->belongsTo('App\Models\Common\Country', 'country', 'id');
    }

    public function getstate(){
        return $this->belongsTo('App\Models\Common\State', 'state', 'id');
    }
    
    public function getpayment_term(){
        return $this->belongsTo('App\Models\Common\PaymentTerm', 'credit_term', 'id');
    }

    public function getCurrency(){
        return $this->belongsTo('App\Models\Common\Currency', 'currency_id', 'id');
    }    
}
