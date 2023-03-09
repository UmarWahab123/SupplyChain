<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $table = 'customer_contacts';
    protected $fillable = ['customer_id','name','sur_name','email','telehone_number','postion'];

    
}
