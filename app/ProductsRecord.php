<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductsRecord extends Model
{

    protected $fillable=['product_id','refrence_code','short_desc','primary_category_title','category_title','buying_unit_title','selling_unit_title','type_title','brand','product_temprature_c','total_buy_unit_cost_price','weight','unit_conversion_rate','selling_price','vat','import_tax_book','hs_code','hs_description','supplier_description','product_supplier_reference_no','purchasing_price_eur','purchasing_price_thb','freight','landing','leading_time','default_supplier','currency_symbol','warehosue_c_r_array','customer_categories_array','customer_suggested_prices_array','total_visible_stock','type_id'];
    public function get_product_quantites()
    {
        return $this->belongsTo('App\ProductQuantity','id','product_id');
    }
}
