<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class SupplierGeneralDocument extends Model
{
    protected $table = 'supplier_general_documents';
    protected $fillable = ['supplier_id', 'file_name', 'description'];
}
