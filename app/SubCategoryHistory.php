<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubCategoryHistory extends Model
{
    protected $fillable = [
        'user_id',
        'created_at',
        'sub_category_id',
        'column_name',
        'old_value',
        'new_value',
    ];


    public function productCategory(){
    	return $this->belongsTo('App\Models\Common\ProductCategory', 'sub_category_id', 'id');
    }

    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
