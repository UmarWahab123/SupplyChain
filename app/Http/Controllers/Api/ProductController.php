<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Product;
use App\Models\Common\Warehouse;

class ProductController extends Controller
{
    public function index($warehouse_id = null)
    {
        // $products = Product::select('id','refrence_code','name','short_desc','ecommerce_price','discount_price','selling_unit','primary_category','category_id','long_desc','system_code','product_temprature_c','vat','type_id','ecom_selling_unit','weight','type_id_2','supplier_id','length','width','height','ecom_product_weight_per_unit','brand','product_notes','product_note_3')->with('prouctImages:id,product_id,image','productCategory:id,parent_id,title','productSubCategory:id,parent_id,title','ecom_warehouse:id,product_id,available_quantity','sellingUnits:id,title','productType','ecomSellingUnits','restaurant_fixed_price','hotel_fixed_price','productOrigin','supplier_products:id,supplier_description,supplier_id,product_id')->where('ecommerce_enabled',1)->paginate(50);

        if ($warehouse_id != null) {
            $warehouse = Warehouse::find($warehouse_id);
            if ($warehouse == null) {
                return response()->json(['success' => false, 'error' => 'No such warehouse exists']);
            }
        }
        $products = Product::select('id','refrence_code','name','short_desc','ecommerce_price','discount_price','selling_unit','primary_category','category_id','long_desc','system_code','product_temprature_c','vat','type_id','ecom_selling_unit','weight','type_id_2','supplier_id','length','width','height','ecom_product_weight_per_unit','brand','product_notes','product_note_3');


        if ($warehouse_id != null) {
            $products = $products->with(['prouctImages:id,product_id,image','productCategory:id,parent_id,title','productSubCategory:id,parent_id,title','sellingUnits:id,title','productType','ecomSellingUnits','restaurant_fixed_price','hotel_fixed_price','productOrigin','supplier_products:id,supplier_description,supplier_id,product_id', 'warehouse_products' => function ($q) use ($warehouse_id) {
                $q->where('warehouse_id',$warehouse_id)->select('id', 'product_id', 'available_quantity');
            }
            ]);
        }
        else{
            $products = $products->with('prouctImages:id,product_id,image','productCategory:id,parent_id,title','productSubCategory:id,parent_id,title','ecom_warehouse:id,product_id,available_quantity','sellingUnits:id,title','productType','ecomSellingUnits','restaurant_fixed_price','hotel_fixed_price','productOrigin','supplier_products:id,supplier_description,supplier_id,product_id');
        };
        $products = $products->where('ecommerce_enabled',1)->paginate(50);

        return response()->json(['success' => true, 'products' => $products]);
    }

    public function show($id, $warehouse_id = null)
    {
        // $product = Product::select('id','refrence_code','name','short_desc','brand','ecommerce_price','discount_price','selling_unit','primary_category','category_id','long_desc','system_code','product_temprature_c','vat','type_id','ecom_selling_unit','weight','type_id_2','supplier_id','length','width','height','ecom_product_weight_per_unit','product_notes','product_note_3')->with('prouctImages:id,product_id,image','productCategory:id,parent_id,title','productSubCategory:id,parent_id,title','ecom_warehouse:id,product_id,available_quantity','sellingUnits:id,title','productType','ecomSellingUnits','restaurant_fixed_price','hotel_fixed_price','productOrigin','supplier_products:id,supplier_description,supplier_id,product_id')->find($id);

        if ($warehouse_id != null) {
            $warehouse = Warehouse::find($warehouse_id);
            if ($warehouse == null) {
                return response()->json(['success' => false, 'error' => 'No such warehouse exists']);
            }
        }
        $product = Product::select('id','refrence_code','name','short_desc','brand','ecommerce_price','discount_price','selling_unit','primary_category','category_id','long_desc','system_code','product_temprature_c','vat','type_id','ecom_selling_unit','weight','type_id_2','supplier_id','length','width','height','ecom_product_weight_per_unit','product_notes','product_note_3');

        if ($warehouse_id != null) {
            $product = $product->with(['prouctImages:id,product_id,image','productCategory:id,parent_id,title','productSubCategory:id,parent_id,title','sellingUnits:id,title','productType','ecomSellingUnits','restaurant_fixed_price','hotel_fixed_price','productOrigin','supplier_products:id,supplier_description,supplier_id,product_id',
            'warehouse_products' => function ($q) use ($warehouse_id) {
                $q->where('warehouse_id',$warehouse_id)->select('id', 'product_id', 'available_quantity');
            }
        ]);
        }
        else{
            $product = $product->with('prouctImages:id,product_id,image','productCategory:id,parent_id,title','productSubCategory:id,parent_id,title','ecom_warehouse:id,product_id,available_quantity','sellingUnits:id,title','productType','ecomSellingUnits','restaurant_fixed_price','hotel_fixed_price','productOrigin','supplier_products:id,supplier_description,supplier_id,product_id');
        }

        $product = $product->find($id);

        if($product)
        {
            return response()->json(['success' => true, 'product' => $product]);
        }

        return response()->json(['success' => false, 'message' => 'Product '.$id.' not found.']);

    }
}
