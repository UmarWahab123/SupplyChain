<?php

namespace App\Http\Controllers\Sales;

use App\Events\ProductCreated;
use App\Http\Controllers\Controller;
use App\Imports\ProductBulkImport;
use App\Imports\ProductSuppliersBulkImport;
use App\Models\Common\Brand;
use App\Models\Common\Country;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\ProductImage;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\TempProduct;
use App\Models\Common\Unit;
use App\Models\Common\Order\Order;
use App\Notifications\AddProductNotification;
use App\User;
use Auth;
use Excel;
use File;
use Illuminate\Http\Request;
use Notification;
use Validate;
use Yajra\Datatables\Datatables;

class ProductController extends Controller
{
    public function index()
    {
    	return view('sales.product.index');
    }
    public function readMark(Request $request){
        if(Auth()->user()->notifications[$request->id]->read_at == null){
        Auth()->user()->notifications[$request->id]->markAsRead();
        return response()->json(['error'=>false,'status'=>'read']);
        }else{
            Auth()->user()->notifications[$request->id]->read_at = NULL;
        // dd(Auth()->user()->notifications[$request->id]->read_at);
            Auth()->user()->notifications[$request->id]->save();
        return response()->json(['error'=>false,'status'=>'unread']);
        }
    }

    public function getProductSuppliersRecord($id)
    {
        $query = SupplierProducts::with('supplier','product')->where('product_id',$id)->get();

         return Datatables::of($query)

            ->addColumn('action',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= 'd-none';
                }
                else
                {
                  $class= '';
                }
                $html_string = '<button type="button" style="cursor: pointer;" class="btn-xs btn-danger '.$class.'" data-prodisupid="'.$item->supplier->id.'" data-prodid="'.$item->product_id.'" name="delete_sup" id="delete_sup"><i class="fa fa-trash"></i></button>';
                return $html_string;
            })
            ->addColumn('company',function($item){
                $ref_no = $item->supplier->company;
                return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier->id).'"  >'.$ref_no.'</a>';

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
            ->rawColumns(['action','company','product_supplier_reference_no','import_tax_actual','buying_price','freight','landing','leading_time','gross_weight'])
            ->make(true);

    }

    public function getData()
    {

        $query = Product::with('def_or_last_supplier','units','prouctImages','productType','productBrand')->where('status',1)->orderBy('id', 'ASC')->get();
        // dd($query);
        return Datatables::of($query)

            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="'.url('sales/get-product-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>
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
                else
                {
                    $html_string .= '--';
                }

                $html_string .= '</div>';

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

                return (@$getProductDefaultSupplier != null) ? @$getProductDefaultSupplier->buying_price:'-';
            })
            ->addColumn('total_buy_unit_cost_price',function($item){

                 $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                if($getProductDefaultSupplier !== null)
                {
                    $importTax = $getProductDefaultSupplier->import_tax_actual ? $getProductDefaultSupplier->import_tax_actual : $item->import_tax_book;

                    $total_buying_price = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->buying_price);
                    $newTotalBuyingPrice = (($importTax)/100) * $total_buying_price;
                    $total_buying_price = $total_buying_price + $newTotalBuyingPrice;

                    return (@$total_buying_price != null) ? @$total_buying_price:'--';
                }
            })
            ->addColumn('unit_conversion_rate',function($item){
                return (@$item->unit_conversion_rate != null) ? number_format((float)@$item->unit_conversion_rate, 3, '.', ''):'-';
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
            ->setRowId(function ($item) {
                    return @$item->id;
            })
            ->rawColumns(['action','name','primary_category','category_id','supplier_id','image','import_tax_book','import_tax_actual','freight','landing','total_buy_unit_cost_price','unit_conversion_rate','selling_unit_cost_price','product_type','product_brand','product_temprature_c','weight','lead_time','refrence_code','vat','hs_code','short_desc','buying_unit','selling_unit'])
            ->make(true);
    }

    public function getProdImages(Request $request)
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
        $product_type = ProductType::all();
        $product_brand = Brand::all();
        $product = Product::with('def_or_last_supplier', 'units', 'productCategory','supplier_products')->where('id',$id)->first();
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
            $default_or_last_supplier = SupplierProducts::where('product_id',$id)->where('supplier_id',$last_or_def_supp_id)->first();
            $supplier_name = Supplier::select('company')->where('id',$last_or_def_supp_id)->first();
            $supplier_company = @$supplier_name->company;
        }

        $checkLastOrDefaultSupplier = Product::where('id',$id)->where('supplier_id','!=',0)->count();
        $product_fixed_price = ProductFixedPrice::where("product_id",$id)->get();
        $warehouses = User::where('role_id', '=','6')->whereNull('parent_id')->orderBy('id','ASC')->get();

        $stock_card = PurchaseOrderDetail::where('product_id',$id)->get();

        $countOfProductSuppliers = SupplierProducts::where('product_id',$id)->count();

        $getProductUnit = Unit::all();

        $getSuppliers = Supplier::where('status',1)->get();

        $total_buy_unit_calculation = SupplierProducts::where('product_id',$id)->where('supplier_id',$last_or_def_supp_id)->pluck('import_tax_actual')->first();
        if($total_buy_unit_calculation != NULL)
        {
            $IMPcalculation = 'Import Tax Actual + Buying Price + Frieght + Landing';
        }
        elseif($product->import_tax_book != null)
        {
            $IMPcalculation = 'Import Tax Book + Buying Price + Frieght + Landing';
        }
        else
        {
            $IMPcalculation = 'Import Tax Book + Buying Price + Frieght + Landing';
        }

        return view('sales.product.product-detail',compact('product_type','product','CustomerTypeProductMargin','ProductCustomerFixedPrices','default_or_last_supplier','supplier_company','productImages','productImagesCount','primaryCategory','checkLastOrDefaultSupplier','CustomerTypeProductMarginCount','hotelProductMargin','resturantProductMargin','product_fixed_price','id','product_brand','warehouses','stock_card','countOfProductSuppliers','IMPcalculation','getProductUnit','getSuppliers'));
    }

     public function indexForInquiry()
    {
        return view('sales.product.inquiry');
    }

    public function getDataForInquiry()
    {
        // $query = Product::with('def_or_last_supplier', 'productType','units')->where('products.status',2)->get();
        $query = OrderProduct::query();
        $query->where('order_products.is_billed','Inquiry')->get();
        // dd($query);
        return Datatables::of($query)

        ->addColumn('checkbox', function ($item) {

            $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
              </div>';
            return $html_string;
        })

        ->addColumn('reference_no',function($item){
            if($item->product == null )
            {
                return "N.A";
            }
        })

        ->addColumn('pieces',function($item){
            return ($item->number_of_pieces != null ? $item->number_of_pieces : "N.A");
        })

        ->addColumn('qty',function($item){
            return ($item->quantity != null ? $item->quantity : "N.A");
        })

        ->addColumn('default_price',function($item){
            return ($item->unit_price != null ? number_format((float)@$item->unit_price, 2, '.', '') : "N.A");
        })

        ->addColumn('short_desc',function($item){
            return ($item->short_desc != null ? $item->short_desc : "N.A");
        })

        ->addColumn('added_by',function($item){
            return ($item->created_by != null ? $item->added_by->name : "N.A");
        })

        ->addColumn('quotation_no',function($item){
            return ($item->order_id != null ? $item->get_order->ref_id : "N.A");
        })

        ->rawColumns(['checkbox','added_by','quotation_no','short_desc','default_price','qty','pieces','reference_no'])
        ->make(true);

    }



}
