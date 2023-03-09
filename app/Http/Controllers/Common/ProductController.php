<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Brand;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\ProductImage;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\ProductReceivingHistory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\Unit;
use App\Models\Sales\Customer;
use App\Models\Common\Warehouse;
use App\Models\Common\StockManagementIn;

use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\User;
use Auth;
use Illuminate\Support\Facades\DB;
use PDF;
use Yajra\Datatables\Datatables;

class ProductController extends Controller
{
    public function index()
    {
    	$user = Auth::user();
        $suppliers = Supplier::where('status',1)->get();
    	$layout = '';
    	if($user->role_id == 1)
    	{
    		$layout = 'backend';
    	}
    	elseif ($user->role_id == 2) 
    	{
    		$layout = 'users';
    	}
    	elseif ($user->role_id == 3) 
    	{
    		$layout = 'sales';
    	}
    	elseif ($user->role_id == 5) 
    	{
    		$layout = 'importing';
    	}
    	elseif ($user->role_id == 6) 
    	{
    		$layout = 'warehouse';
    	}

    	return view('common.products.index',compact('user','layout','suppliers'));
    }

    public function getData(Request $request)
    {
        $query = Product::query();
        $query->with('def_or_last_supplier', 'units','prouctImages','productType','productBrand','productSubCategory')->where('status',1);

        if($request->default_supplier != '')
        {
            $query->where('supplier_id', $request->default_supplier)->orderBy('id', 'DESC');
        }

        return Datatables::of($query)
            
            ->addColumn('action', function ($item) {
                $html_string = ' 
                 <a href="'.url('get-product-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>  
                 ';
                // <a href="javascript:void(0);" class="actionicon deleteIcon deleteProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>

                return $html_string;
            })
            ->addColumn('primary_category',function($item){
                return @$item->productCategory->title;
            })
            ->addColumn('category_id',function($item){
                return @$item->productSubCategory->title;
            })
            ->addColumn('buying_unit',function($item){
                return @$item->units->title;
            })
            ->addColumn('selling_unit',function($item){
                return @$item->sellingUnits->title;
            })
            ->addColumn('import_tax_book',function($item){
                return (@$item->import_tax_book != null) ? @$item->import_tax_book.'%':'-'; 
            })
            // ->addColumn('import_tax_actual',function($item){
            //     return (@$item->import_tax_actual != null) ? @$item->import_tax_actual.'%':'-';
            // })
            ->addColumn('vat',function($item){
                return (@$item->vat != null) ? @$item->vat.'%':'-';
            })
            ->addColumn('image', function ($item) { 
                // check already uploaded images //
                $product_images = ProductImage::where('product_id', $item->id)->count('id');

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($product_images > 0)
                {
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-prod-image mr-2" title="View Images"></a> ';
                }
                else{
                    $html_string .= '--';
                }
                $html_string .= '
                          </div>';

                return $html_string;         
            })
            ->addColumn('supplier_id',function($item){
                return (@$item->supplier_id != null) ? @$item->def_or_last_supplier->company:'-';
            })
            ->addColumn('freight',function($item){
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();

                return ($getProductDefaultSupplier != null) ? $getProductDefaultSupplier->freight:'--';
            })
            ->addColumn('landing',function($item){
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();

                return (@$getProductDefaultSupplier != null) ? @$getProductDefaultSupplier->landing :'--';
            })
            ->addColumn('vendor_price',function($item){
                 $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                
                return (@$getProductDefaultSupplier != null) ? number_format((float)@$getProductDefaultSupplier->buying_price, 2, '.', ''):'-';
            })
            ->addColumn('total_buy_unit_cost_price',function($item){

                 $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                if($getProductDefaultSupplier !== null)
                {
                    $importTax = $getProductDefaultSupplier->import_tax_actual ? $getProductDefaultSupplier->import_tax_actual : $item->import_tax_book;

                    $total_buying_price = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->buying_price);
                    $newTotalBuyingPrice = (($importTax)/100) * $total_buying_price;
                    $total_buying_price = $total_buying_price + $newTotalBuyingPrice; 
                    return (@$total_buying_price != null) ? number_format((float)@$total_buying_price, 2, '.', ''):'--';
                }
            })
            ->addColumn('unit_conversion_rate',function($item){
                return (@$item->unit_conversion_rate != null) ? number_format((float)@$item->unit_conversion_rate, 2, '.', ''):'-';
            })
            ->addColumn('selling_unit_cost_price',function($item){
                return (@$item->selling_price != null) ? number_format((float)@$item->selling_price, 2, '.', ''):'-';
            })
            ->addColumn('weight',function($item){
                return (@$item->weight != null) ? @$item->weight." Kg":'-';
            })
            ->addColumn('lead_time',function($item){
                $getProductLastSupplierName = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                return (@$getProductLastSupplierName->leading_time != null) ? @$getProductLastSupplierName->leading_time:'-';
            })
            ->addColumn('product_type',function($item){
                return (@$item->type_id != null) ? @$item->productType->title : '--';
            })
            ->addColumn('product_brand',function($item){
                return (@$item->brand != null) ? @$item->brand : '--';
            })
            ->addColumn('product_temprature_c',function($item){
                return (@$item->product_temprature_c != null) ? @$item->product_temprature_c : '--';
            })
            ->addColumn('bangkok_current_qty',function($item){
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',1)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:'0 ';
                return $qty.$item->sellingUnits->title;
            })
            ->addColumn('bangkok_reserved_qty',function($item){
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',1)->first();
                return (@$warehouse_product->reserved_quantity != null) ? @$warehouse_product->reserved_quantity:'0';
            })
            ->addColumn('phuket_current_qty',function($item){
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',2)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:'0 ';
                return $qty.$item->sellingUnits->title;
            })
            ->addColumn('phuket_reserved_qty',function($item){
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',2)->first();
                return (@$warehouse_product->reserved_quantity != null) ? @$warehouse_product->reserved_quantity:'0';
            })
            ->setRowId(function ($item) {
                    return @$item->id;
            })
            ->rawColumns(['action','name','primary_category','category_id','supplier_id','image','import_tax_book','import_tax_actual','freight','landing','total_buy_unit_cost_price','unit_conversion_rate','selling_unit_cost_price','product_type','product_brand','product_temprature_c','weight','lead_time','refrence_code','vat','hs_code','short_desc','buying_unit','selling_unit','bangkok_current_qty','bangkok_reserved_qty','phuket_current_qty','phuket_reserved_qty'])
            ->make(true);

    }

    public function getImages(Request $request)
    {
        $prod_images = ProductImage::where('product_id', $request->prod_id)->get();
        $html_string ='';
        if($prod_images->count() > 0):
        foreach($prod_images as $pimage):
        $html_string .= '<div class="col-6 col-sm-4 gemstoneImg mb-3" id="prod-image-'.$pimage->id.'">
            <figure>
           
            <a href="'.url('public/uploads/products/product_'.$request->prod_id.'/'.$pimage->image).'" target="_blank">   
                <img class="stone-img img-thumbnail" style="width: 150px;
    height: 150px;" src="'.url('public/uploads/products/product_'.$request->prod_id.'/'.$pimage->image).'">
            </a>
            </figure>       
          </div>';
        endforeach;
        else:
         $html_string .= '<div class="col-12 mb-3 text-center">No Record Found</div>';
        endif;

        return $html_string;

    }

    public function getProductDetail($id)
    {
        // dd('hi');
        $user = Auth::user();
        $layout = '';
        if($user->role_id == 1)
        {
            $layout = 'backend';
        }
        elseif ($user->role_id == 2) 
        {
            $layout = 'users';
        }
        elseif ($user->role_id == 3) 
        {
            $layout = 'sales';
        }
        elseif ($user->role_id == 5) 
        {
            $layout = 'importing';
        }
        elseif ($user->role_id == 6) 
        {
            $layout = 'warehouse';
        }
        $product_type = ProductType::all();
        $product_brand = Brand::all();
        $product = Product::with('def_or_last_supplier', 'units', 'productCategory','supplier_products','productSubCategory')->where('id',$id)->first();

        $CustomerTypeProductMargin = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->get();

        $hotelProductMargin = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->where('customer_type_id',1)->get();

        $resturantProductMargin = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->where('customer_type_id',2)->get();

        $CustomerTypeProductMarginCount = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->count();

        $ProductCustomerFixedPrices = ProductCustomerFixedPrice::with('customers','products')->where('product_id',$id)->orderBy('id', 'ASC')->get();

        $productImages = ProductImage::where('product_id',$id)->orderBy('id', 'ASC')->get();
        $productImagesCount = ProductImage::select('image','product_id')->where('product_id',$id)->count();
        
        $primaryCategory = ProductCategory::where('parent_id',0)->get();

        $product_supplier = Product::where('id',$id)->first();
        $last_or_def_supp_id = @$product_supplier->supplier_id;

        if($last_or_def_supp_id != 0)
        {
            $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id',$id)->where('supplier_id',$last_or_def_supp_id)->first();
            $supplier_name = Supplier::select('company')->where('id',$last_or_def_supp_id)->where('status',1)->first();
            $supplier_company = @$supplier_name->company;
        }

        $checkLastOrDefaultSupplier = Product::where('id',$id)->where('supplier_id','!=',0)->count();
        $product_fixed_price = ProductFixedPrice::where("product_id",$id)->get();
        // $warehouses = User::where('role_id', '=','6')->orderBy('id','ASC')->get();

         $warehouses = Warehouse::where('status',1)->orderBy('id','ASC')->get();
        $stock_card = StockManagementIn::where('product_id',$id)->get();
        $warehouse_products = WarehouseProduct::where('product_id',$id)->get();

        // $stock_card = PurchaseOrderDetail::where('product_id',$id)->get();
        $countOfProductSuppliers = SupplierProducts::where('product_id',$id)->count();

        $total_buy_unit_calculation = SupplierProducts::where('product_id',$id)->where('supplier_id',$last_or_def_supp_id)->pluck('import_tax_actual')->first();

        $getProductUnit = Unit::all();

        $getSuppliers = Supplier::where('status',1)->get();

        $customers = Customer::where('status',1)->get();

        if($total_buy_unit_calculation != NULL)
        {
            $IMPcalculation = 'Import Tax Actual + Buying Price + Frieght + Landing';
        }
        elseif($product->productSubCategory->import_tax_book != null)
        {
            $IMPcalculation = 'Import Tax Book + Buying Price + Frieght + Landing';
        }
        else
        {
            $IMPcalculation = 'Import Tax Book + Buying Price + Frieght + Landing';
        }

        // dd($IMPcalculation);

        return view('common.products.product-detail',compact('product_type','product','CustomerTypeProductMargin','ProductCustomerFixedPrices','default_or_last_supplier','supplier_company','productImages','productImagesCount','primaryCategory','checkLastOrDefaultSupplier','CustomerTypeProductMarginCount','hotelProductMargin','resturantProductMargin','product_fixed_price','id','product_brand','warehouses','stock_card','countOfProductSuppliers','IMPcalculation','getProductUnit','getSuppliers','customers','layout','warehouse_products'));
    }

    public function getProductSuppliersData($id)
    {
        $query = SupplierProducts::with('supplier','product')->where('product_id',$id)->get();
        
         return Datatables::of($query)
                        
            ->addColumn('company',function($item){
                return $item->supplier->company;
                // return  $html_string = '<a target="_blank" href="'.url('importing/get-supplier-detail/'.$item->supplier->id).'"  >'.$ref_no.'</a>';

            })
            ->addColumn('product_supplier_reference_no',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="product_supplier_reference_no"  data-fieldvalue="'.$item->product_supplier_reference_no.'">'.($item->product_supplier_reference_no != NULL ? $item->product_supplier_reference_no : "--").'</span>
                <input type="text" style="width:100%;" name="product_supplier_reference_no" class="prodSuppFieldFocus d-none" value="'.$item->product_supplier_reference_no.'">';
                return $html_string;
            })
            ->addColumn('import_tax_actual',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="import_tax_actual"  data-fieldvalue="'.$item->import_tax_actual.'">'.($item->import_tax_actual != NULL ? $item->import_tax_actual : "--").'</span>
                <input type="number" style="width:100%;" name="import_tax_actual" class="prodSuppFieldFocus d-none" value="'.$item->import_tax_actual.'">';
                return $html_string;
            })
            ->addColumn('gross_weight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="gross_weight"  data-fieldvalue="'.$item->gross_weight.'">'.($item->gross_weight != NULL ? $item->gross_weight : "--").'</span>
                <input type="number" style="width:100%;" name="gross_weight" class="prodSuppFieldFocus d-none" value="'.$item->gross_weight.'">';
                return $html_string;              
            })
            ->addColumn('freight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="freight"  data-fieldvalue="'.$item->freight.'">'.($item->freight != NULL ? $item->freight : "--").'</span>
                <input type="number" style="width:100%;" name="freight" class="prodSuppFieldFocus d-none" value="'.$item->freight.'">';
                return $html_string;              
            })
            ->addColumn('landing',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="landing"  data-fieldvalue="'.$item->landing.'">'.($item->landing != NULL ? $item->landing : "--").'</span>
                <input type="number" style="width:100%;" name="landing" class="prodSuppFieldFocus d-none" value="'.$item->landing.'">';
                 return $html_string;
            })
            ->addColumn('buying_price',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="buying_price"  data-fieldvalue="'.$item->buying_price.'">'.($item->buying_price != NULL ? $item->buying_price : "--").'</span>
                <input type="number" style="width:100%;" name="buying_price" class="prodSuppFieldFocus d-none" value="'.$item->buying_price.'">';
                return $html_string;   
            })
            ->addColumn('buying_price_in_thb',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= '';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="buying_price_in_thb"  data-fieldvalue="'.$item->buying_price_in_thb.'">'.($item->buying_price_in_thb != NULL ? $item->buying_price_in_thb : "--").'</span>
                <input type="number" style="width:100%;" name="buying_price_in_thb" class="prodSuppFieldFocus d-none" value="'.$item->buying_price_in_thb.'">';
                return $html_string;   
            })
            ->addColumn('extra_cost',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="extra_cost"  data-fieldvalue="'.$item->extra_cost.'">'.($item->extra_cost != NULL ? $item->extra_cost : "--").'</span>
                <input type="number" style="width:100%;" name="extra_cost" class="prodSuppFieldFocus d-none" value="'.$item->extra_cost.'">';
                return $html_string;
            })
            ->addColumn('leading_time',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="leading_time"  data-fieldvalue="'.$item->leading_time.'">'.($item->leading_time != NULL ? $item->leading_time : "--").'</span>
                <input type="number" style="width:100%;" name="leading_time" class="prodSuppFieldFocus d-none" value="'.$item->leading_time.'">';
                return $html_string;
            })
            ->addColumn('supplier_packaging',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="supplier_packaging"  data-fieldvalue="'.$item->supplier_packaging.'">'.($item->supplier_packaging != NULL ? $item->supplier_packaging : "--").'</span>
                <input type="number" style="width:100%;" name="supplier_packaging" class="prodSuppFieldFocus d-none" value="'.$item->supplier_packaging.'">';
                return $html_string;
            })
            ->addColumn('billed_unit',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="billed_unit"  data-fieldvalue="'.$item->billed_unit.'">'.($item->billed_unit != NULL ? $item->billed_unit : "--").'</span>
                <input type="number" style="width:100%;" name="billed_unit" class="prodSuppFieldFocus d-none" value="'.$item->billed_unit.'">';
                return $html_string;
            })
            ->addColumn('m_o_q',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="m_o_q"  data-fieldvalue="'.$item->m_o_q.'">'.($item->m_o_q != NULL ? $item->m_o_q : "--").'</span>
                <input type="number" style="width:100%;" name="m_o_q" class="prodSuppFieldFocus d-none" value="'.$item->m_o_q.'">';
                return $html_string;
            })
            ->setRowId(function ($item) {
                    return $item->id;
            })
             // greyRow is a custom style in style.css file
            ->setRowClass(function ($item) {
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                    return $item->supplier_id == $checkLastSupp->supplier_id ? 'greyRow' : '';
                }
            })
            ->rawColumns(['action','company','product_supplier_reference_no','import_tax_actual','buying_price','freight','landing','leading_time','gross_weight','supplier_packaging','billed_unit','m_o_q','buying_price_in_thb','extra_cost'])
            ->make(true);
    
    }

     public function getCustomerFixedPrices(Request $request)
    {
      // dd($request->id);
      $id = $request->id;
      $customer = Customer::where('id',$id)->first();
      $ProductCustomerFixedPrices = ProductCustomerFixedPrice::where('product_id',$id)->get();
      return view('common.products.customerfixedprices',compact('id','customer','ProductCustomerFixedPrices'));
    }

    public function purchaseFetchProduct(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if($request->get('query'))
        {            
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            $category_query = ProductCategory::query();

            foreach ($search_box_value as $result)
            {
                // $product_query = $product_query->orWhere('short_desc', 'LIKE',"%$result%");
                // $category_query = $category_query->orWhere('title', 'LIKE',"%$result%");

                $product_query = $product_query->Where('short_desc', 'LIKE', '%'.$result.'%')->orWhere('refrence_code', 'LIKE', $result.'%')->orWhere('brand', 'LIKE', $result.'%');

                $category_query = $category_query->orWhere('title', 'LIKE', '%'.$result.'%');

                $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', '%'.$result.'%');
            }

            $product_query  = $product_query->pluck('id')->toArray();
            $category_query = $category_query->pluck('id')->toArray();

            if(! empty($product_query) || ! empty($category_query) )
            {
                $product_detail = Product::orderBy('id','ASC');

                    $product_detail->orWhere(function ($q) use ($product_query,$category_query) {

                        if(! empty($product_query))
                        {
                        $q->orWhereIn('id', $product_query);
                        }
                        if(! empty($category_query))
                        {
                        $q->orWhereIn('primary_category', $category_query)->orWhereIn('category_id',$category_query);
                        }

                    });
                
                $product_detail->where('status',1);
                $detail = $product_detail->take(10)->get();
            }
           
            if(!empty($detail)){
                $i = 1;
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                    foreach($detail as $row)
                    {                       
                        $output .= '<li>';
                        $output .= '<a tabindex="'.$i.'" target="_blank" href="'.url('get-product-detail/'.$row->id).'" data-prod_id="'.$row->id.'" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.$row->short_desc.'</a></li>'; 
                        $i++;
                    }
                $output .= '</ul>';
                echo $output;
            }
            else{
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        }
        else{
            echo '';
        }

    }
}
