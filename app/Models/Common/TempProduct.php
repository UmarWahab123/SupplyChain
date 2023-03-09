<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class TempProduct extends Model
{
    protected $fillable = [
        'refrence_code','hs_code',
        'short_desc','weight',
        'primary_category','category_id',
        'type_id', 'type_2_id','brand_id','brand',
        'product_temprature_c','buying_unit',
        'selling_unit','stock_unit',
        'min_stock','m_o_q','supplier_packaging',
        'billed_unit','unit_conversion_rate',
        'import_tax_book','vat','selling_price',
        'supplier_id','p_s_r','supplier_description',
        'buying_price','extra_cost','extra_tax','freight',
        'landing','gross_weight','leading_time',
        'restaurant_fixed_price','hotel_fixed_price',
        'retail_fixed_price','private_fixed_price',
        'catering_fixed_price',
        'hasError','status','created_by','fixed_prices_array','product_notes','import_tax_actual','system_code', 'order_qty_per_piece', 'type_3_id'];

    public function tempProductType()
    {
        return $this->belongsTo('App\Models\Common\ProductType', 'type_id', 'id');
    }

    public function tempProductType2()
    {
        return $this->belongsTo('App\Models\Common\ProductSecondaryType', 'type_2_id', 'id');
    }
    public function tempProductType3()
    {
        return $this->belongsTo('App\ProductTypeTertiary', 'type_3_id', 'id');
    }

    public function tempProductCategory()
    {
        return $this->belongsTo('App\Models\Common\ProductCategory', 'primary_category', 'id');
    }

    public function tempProductSubCategory()
    {
        return $this->belongsTo('App\Models\Common\ProductCategory', 'category_id', 'id');
    }

    public function tempUnits()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'buying_unit', 'id');
    }

    public function tempSellingUnits()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'selling_unit', 'id');
    }

    public function tempStockUnits()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'stock_unit', 'id');
    }

    public function tempSupplier()
    {
        return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }

    public function tempProductTableSubCategory()
    {
        return $this->hasMany('App\Models\Common\Product', 'category_id', 'category_id');
    }

    public function tempProductSystemCode()
    {
        return $this->belongsTo('App\Models\Common\Product', 'system_code', 'system_code');
    }

    public function tempProductShortDesc()
    {
        return $this->belongsTo('App\Models\Common\Product', 'short_desc', 'short_desc');
    }


}
