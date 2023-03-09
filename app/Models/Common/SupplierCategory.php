<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class SupplierCategory extends Model
{
    protected $table = 'supplier_categories';
    protected $fillable = ['supplier_id', 'category_id'];

    public function supplierCategories(){
        return $this->belongsTo('App\Models\Common\ProductCategory', 'category_id', 'id');
    }
    public function categoryTitle(){
        return $this->belongsTo('App\Models\Common\ProductCategory', 'category_id', 'id');
    }

    public function catMultipleSupplier()
    {
        return $this->hasMany('App\Models\Common\Supplier', 'supplier_id', 'id');
    }
}
