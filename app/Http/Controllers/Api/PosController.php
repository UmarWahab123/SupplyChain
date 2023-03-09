<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Warehouse;
use App\Helpers\PosControllerHelper;
use App\Models\Pos\PosIntegration;
use App\Models\Common\ProductCategory;
use App\Models\Common\Product;


class PosController extends Controller
{
    public function get_warehouses() {
        $warehouses = Warehouse::all();
        return response()->json(['success' => true,'warehouses' => $warehouses]);
    }

    public function store(Request $request) {
        $data = PosControllerHelper::store($request);
        return $data;
    }

    public function get_categories(Request $request) {
        $token = $this->check_token($request);
        if($token == false) {
            return response()->json(['success' => false,'message' => 'Invalid Token !']);
        }
      $categories = ProductCategory::where('parent_id', '=', 0)->get();
      return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function get_products(Request $request) {
        $token = $this->check_token($request);
        if($token == null) {
            return response()->json(['success' => false,'message' => 'Invalid Token !']);
        }

        if($request->product_category_id) {
            $products = Product::where([['status',1],['primary_category',$request->product_category_id]])->orderBy('id', 'ASC');
        } else if($request->sub_category_id) {
            $products = Product::where([['status',1],['category_id',$request->sub_category_id]])->orderBy('id', 'ASC');
        } else if($request->product_id) {
            $products = Product::where([['status',1],['id',$request->product_id]])->orderBy('id', 'ASC');
        }
        else {
            $products = Product::where('status',1)->orderBy('id', 'ASC');
        }

        $products->with(['sellingUnits:id,title as unit','productCategory:id,title as category','stock_in' => function($st) use ($token){
            $st->where('warehouse_id',@$token->warehouse_id)->whereHas('stock_out',function($st){
                $st->where('available_stock','>',0);
            });
        },'stock_in.stock_out' => function($q){
            $q->select('available_stock','smi_id')->whereNotNull('available_stock')->where('available_stock','>',0);
        }])->select('id','name','long_desc','selling_unit','short_desc','long_desc as product_description','bar_code','barcode_type','primary_category','selling_price','discount','refrence_code');

        $products = $products->paginate(50);

        return response()->json(['success' => true, 'products' => $products]);
    }

    public function check_token(Request $request) {
        $token = $request->token;
        $check_token = PosIntegration::where('token','=',$token)->first();
        return $check_token;
    }
}
