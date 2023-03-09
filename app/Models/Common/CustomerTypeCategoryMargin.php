<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasEvents;

class CustomerTypeCategoryMargin extends Model
{
    use HasEvents;
    protected $fillable = ['category_id','customer_type_id','default_margin','default_value'];

    public function categoryMargins(){
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'customer_type_id', 'id');
    }
}
