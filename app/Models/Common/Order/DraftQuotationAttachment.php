<?php

namespace App\Models\Common\Order;

use Illuminate\Database\Eloquent\Model;

class DraftQuotationAttachment extends Model
{
    protected $fillable = ['draft_quotation_id','file_name','file'];

   	public function get_draft_quotation(){
    	return $this->belongsTo('App\Models\Common\Order\DraftQuotation', 'draft_quotation_id', 'id');
    }
}
