<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class TempCustomers extends Model
{
    protected $table = 'temp_customers';

    protected $fillable = [
        'reference_number',
        'reference_name','sales_person','secondary_sale',
        'company_name','classification',
        'credit_term','payment_method',
        'address_reference_name','phone_no','cell_no',
        'address','tax_id',
        'email','fax','state',
        'city','zip','contact_name',
        'contact_sur_name','contact_email','contact_tel','contact_position','status'
    ];

    public function primary_sales_person()
    {
        return $this->belongsTo('App\User', 'sales_person', 'name');
    }
    public function secondary_sales_person()
    {
        return $this->belongsTo('App\User', 'secondary_sale', 'name');
    }
    public function customer_category()
    {
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'classification', 'title')->where('is_deleted', 0);
    }
    public function payment_term()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm', 'credit_term', 'title');
    }
}
