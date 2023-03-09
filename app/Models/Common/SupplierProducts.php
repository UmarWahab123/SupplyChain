<?php

namespace App\Models\Common;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SupplierProducts extends Model
{
    protected $fillable = ['supplier_id','product_id','product_supplier_reference_no','buying_price','selling_price','import_tax','leading_time','freight','landing','m_o_q','extra_cost','supplier_packaging','billed_unit','supplier_description','buying_price_in_thb','is_deleted','delete_date'];

    // public function user(){
    // 	return $this->belongsTo('App\User', 'user_id', 'id');
    // }

    public function product(){
        return $this->belongsTo('App\Models\Common\Product', 'supplier_id', 'supplier_id');
    }

    public function supplier_products(){
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function supplier(){
        return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }

    public function price_calculate($product_id,$supplier_id){
        $getProductDefaultSupplier = SupplierProducts::where('product_id',@$product_id)->where('supplier_id',@$supplier_id)->first();
        // dd($getProductDefaultSupplier);
        return $getProductDefaultSupplier->buying_price_in_thb;

        $importTax = $getProductDefaultSupplier->import_tax_actual != null ? $getProductDefaultSupplier->import_tax_actual : $getProductDefaultSupplier->supplier_products->import_tax_book;
        $newTotalBuyingPrice = ((($importTax)/100) * $getProductDefaultSupplier->buying_price_in_thb) +$getProductDefaultSupplier->buying_price_in_thb;

        $total_buying_price = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->buying_price_in_thb);

        //dd($total_buying_price);
        return $total_buying_price;
    }

    public function gross_weight_calculate($product_id,$supplier_id)
    {
        $getProductDefaultSupplier = SupplierProducts::where('product_id',@$product_id)->where('supplier_id',@$supplier_id)->first();
        return $getProductDefaultSupplier->gross_weight;
    }

    public function getGrossWeightPerProductSupplier($product_id,$supplier_id)
    {
        $getProductDefaultSupplier = SupplierProducts::where('product_id',@$product_id)->where('supplier_id',@$supplier_id)->first();
        return $getProductDefaultSupplier->gross_weight;
    }

    public function defaultSupplierProductPriceCalculation($product_id,$supplier_id,$buying_price,$freight,$landing,$extra_cost,$importTax,$extra_tax)
    {

        $product = Product::find($product_id);

        $supp_product = SupplierProducts::where('supplier_id',$supplier_id)->where('product_id',$product_id)->first();

        if ($supp_product->currency_conversion_rate != null) {
            $supplier_conv_rate_thb = 1/$supp_product->currency_conversion_rate;
        }
        else{
            $supplier_conv_rate_thb = $supp_product->supplier->getCurrency->conversion_rate;
        }

        $buying_price_in_thb = $supp_product->buying_price_in_thb;
        $supp_product->import_tax_actual = $importTax;
        $supp_product->unit_import_tax = ($importTax/100) * $buying_price_in_thb;
        $supp_product->save();

        $total_buying_price = $supp_product->unit_import_tax + $buying_price_in_thb;

        // dd($buying_price_in_thb,$freight,$landing,$extra_cost,$extra_tax);
        $total_buying_price = ($freight)+($landing)+($extra_cost)+($extra_tax)+($total_buying_price);

        $product->total_buy_unit_cost_price = $total_buying_price;

        // this is supplier buying unit cost price
        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

        // this is selling price
        $total_selling_price = $total_buying_price * $product->unit_conversion_rate;

        $product->selling_price = $total_selling_price;

        $product->last_price_updated_date = Carbon::now();

        $product->save();

    }

    public function defaultSupplierProductPriceUpdate($product_id,$supplier_id,$buying_price,$freight,$landing,$extra_cost,$importTax,$extra_tax)
    {

        $product = Product::find($product_id);

        $supp_product = SupplierProducts::where('supplier_id',$supplier_id)->where('product_id',$product_id)->first();

        $supplier_conv_rate_thb = $supp_product->supplier->getCurrency->conversion_rate;

        $buying_price_in_thb = $supp_product->buying_price_in_thb;

        $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

      // dd($total_buying_price,$freight,$landing,$extra_cost);
        $total_buying_price = ($freight)+($landing)+($extra_cost)+($extra_tax)+($total_buying_price);

        $product->total_buy_unit_cost_price = $total_buying_price;

        // this is supplier buying unit cost price
        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

        // this is selling price
        $total_selling_price = $total_buying_price * $product->unit_conversion_rate;

        $product->selling_price = $total_selling_price;
        $product->is_currency_updated = 1;

        // $product->last_price_updated_date = Carbon::now();

        $product->save();
    }

    public function updateSingleProdctPrice($product_id,$supplier_id,$buying_price,$freight,$landing,$extra_cost,$importTax,$extra_tax)
    {
        $product = Product::find($product_id);

        $supp_product = SupplierProducts::where('supplier_id',$supplier_id)->where('product_id',$product_id)->first();

        $supplier_conv_rate_thb = $supp_product->supplier->getCurrency->conversion_rate;

        $buying_price_in_thb = $supp_product->buying_price_in_thb;

        $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

        // dd($total_buying_price,$freight,$landing,$extra_cost);
        $total_buying_price = ($freight)+($landing)+($extra_cost)+($extra_tax)+($total_buying_price);

        $product->total_buy_unit_cost_price = $total_buying_price;

        // this is supplier buying unit cost price
        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

        // this is selling price
        $total_selling_price = $total_buying_price * $product->unit_conversion_rate;

        $product->selling_price = $total_selling_price;

        $product->last_price_updated_date = Carbon::now();

        $product->save();
    }
}
