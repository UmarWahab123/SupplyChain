<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class DraftQuotationNote extends Model
{
    protected $fillable = ['draft_quotation_id','note'];
}
