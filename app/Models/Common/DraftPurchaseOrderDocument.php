<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class DraftPurchaseOrderDocument extends Model
{
    protected $fillable = ['po_id','file_name'];
}
