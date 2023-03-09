<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class SupplierContacts extends Model
{
    protected $table = 'supplier_contacts';
    protected $fillable = ['supplier_id','name','sur_name','email','telehone_number','postion'];
}
