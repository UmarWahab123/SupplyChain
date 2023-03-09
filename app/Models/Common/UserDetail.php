<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'user_details';
    protected $fillable = ['user_id', 'company_name', 'sales_person', 'contact_name', 'classification', 'since', 'open_orders', 'total_orders', 'last_order_date', 'country_id', 'state_id','city_name','zip_code','primary_contact','phone_no','category_type','product_type','image','credit_terms','address'];

    public function country(){
    	return $this->belongsTo('App\Models\Common\Country', 'country_id', 'id');
    }

    public function state(){
    	return $this->belongsTo('App\Models\Common\State', 'state_id', 'id');
    }
}