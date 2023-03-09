<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SoldProductsReportRecord extends Model
{
     protected $fillable = ['order_no','customer','delivery_date','created_date','supply_from','warehouse','p_ref','item_description','selling_unit','qty','unit_price','total_amount','vat','vat_thb','available_stock','vintage','total_price_without_vat','status','customer_categories_array','notes','piece','product_type','category'];
}
