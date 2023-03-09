<?php

namespace App\Http\Controllers\Ecom;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\TableHideColumn;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Supplier;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\ExportStatus;
use Auth;
use App\QuotationConfig;
use App\Models\Common\Product;
use App\Models\Common\Warehouse;
use Carbon\Carbon;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\SupplierProducts;
use Yajra\Datatables\Datatables;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Unit;

class EcomProductController extends Controller
{
    public function index(){
    	 // get all products completegetData
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'ecom_completed_products')->first();

        $suppliers = Supplier::where('status',1)->orderBy('reference_name')->get();

        $product_parent_categories = ProductCategory::where('parent_id',0)->orderBy('title')->get();
        $product_sub_categories = ProductCategory::where('parent_id','!=',0)->orderBy('title')->groupBy('title')->get();
        $product_types = ProductType::all();
        // dd($product_parent_category[0]->get_Child);
        $statusCheck=ExportStatus::where('type','complete_products')->first();
        $last_downloaded=null;
        if($statusCheck!=null)
        {
          $last_downloaded=$statusCheck->last_downloaded;
        }

        $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $default_warehouse_id =  $check_status['status'][5];

        $getWarehouses = Warehouse::where('id', $default_warehouse_id)->first();
        $getCategories = CustomerCategory::where('is_deleted',0)->where('show',1)->get();
        $getCategoriesSuggested = CustomerCategory::where('is_deleted',0)->where('suggested_price_show',1)->get();
        $total_system_units = Unit::whereNotNull('id')->count();
        $display_prods = ColumnDisplayPreference::where('type', 'ecom_completed_products')->where('user_id', Auth::user()->id)->first();
        // $getWarehouses = $getWarehouses->warehouse_title;
        return $this->render('ecom.home.eproducts',compact('last_downloaded','table_hide_columns','suppliers','product_sub_categories','product_parent_categories','product_types','getWarehouses', 'getCategories', 'getCategoriesSuggested', 'total_system_units', 'display_prods'));
    }
    public function EcomSoldProduct(){
    	return ('sold product report');
    }
    public function getEcomProductData(Request $request){
    	// dd($request->search['value']);
        $search_product = $request->search['value'];
        //$query = DB::table('products')->pluck('refrence_code', 'primary_category','short_desc','buying_unit','selling_unit','type_id','brand','product_temprature_c','supplier_id','id','total_buy_unit_cost_price','weight','unit_conversion_rate','selling_price','vat','import_tax_book','hs_code','category_id')->all();

         $query = Product::select('products.refrence_code','products.primary_category','products.short_desc','products.buying_unit','products.selling_unit','products.type_id','products.brand','products.product_temprature_c','products.supplier_id','products.id','products.total_buy_unit_cost_price','products.weight','products.unit_conversion_rate','products.selling_price','products.vat','products.import_tax_book','products.hs_code','products.hs_description','products.category_id','products.product_notes','products.status','products.min_stock','products.last_price_updated_date');
        $query->with('def_or_last_supplier', 'units','productType','productBrand','productSubCategory','supplier_products','supplier_products.supplier.getCurrency','productCategory')->where('products.ecommerce_enabled',1);

        // $query->orderBy('products.short_desc', 'ASC');
        if($request->default_supplier != '')
        {
          $supplier_query = $request->default_supplier;
          $query = $query->whereIn('products.id', SupplierProducts::select('product_id')->where('supplier_id',$supplier_query)->pluck('product_id'));
        }

        if($request->prod_type != '')
        {
          $query->where('products.type_id', $request->prod_type)->where('products.status',1);
        }

        if($request->prod_category != '')
        {
          // $sub_cat_ids = ProductCategory::select('id')->where('title',$request->prod_category)->where('parent_id','!=',0)->get();
          $query->whereIn('products.category_id', ProductCategory::select('id')->where('title',$request->prod_category)->where('parent_id','!=',0)->pluck('id'))->where('products.status',1);
        }

        if($request->prod_category_primary != '')
        {
          $query->where('products.primary_category', $request->prod_category_primary)->where('products.status',1);
        }

        if($request->filter != '')
        {
          if($request->filter == 'stock')
          {
            $query = $query->whereIn('products.id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
          }
          elseif($request->filter == 'reorder')
          {
            $query->where('products.min_stock','>',0);
          }
        }

        // if($request->order[0]['dir'] != 'asc' && $request->order[0]['dir'] != 'desc')
        // {
        //   $query->orderBy('refrence_no', 'DESC');
        // }

        if($request->sortbyparam == 2 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'refrence_code';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 2 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'refrence_code';
          $sort_order     = 'ASC';
        }

        if($request->sortbyparam == 5 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'short_desc';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 5 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'short_desc';
          $sort_order     = 'ASC';
        }


         if($request->sortbyparam == 6 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'product_notes';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 6 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'product_notes';
          $sort_order     = 'ASC';
        }

        if($request->sortbyparam == 14 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'reference_name';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 14 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'reference_name';
          $sort_order     = 'ASC';
        }
        if($request->sortbyparam == 9 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'title';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 9 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'title';
          $sort_order     = 'ASC';
        }
        if($request->sortbyparam == 10 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'brand';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 10 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'brand';
          $sort_order     = 'ASC';
        }
        if($request->sortbyparam == 15 && $request->sortbyvalue == 1)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'supplier_description';
          $sort_order     = 'DESC';
        }
        elseif($request->sortbyparam == 15 && $request->sortbyvalue == 2)
        {
          $query->getQuery()->orders = null;
          $sort_variable  = 'supplier_description';
          $sort_order     = 'ASC';
        }

        $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $default_warehouse_id =  $check_status['status'][5];

        $getWarehouses = Warehouse::where('status',1)->get();
        $getWarehouse = Warehouse::where('id', $default_warehouse_id)->first();

        if($request->sortbyparam >= 27)
        {
          if($getWarehouses->count() > 0)
          {
            $inc = 27;
            foreach ($getWarehouses as $wh)
            {
              if($request->sortbyparam == $inc && $request->sortbyvalue == 1)
              {
                $sort_variable  = 'current_quantity';
                $sort_order     = 'DESC';
              }
              elseif($request->sortbyparam == $inc && $request->sortbyvalue == 2)
              {
                $sort_variable  = 'current_quantity';
                $sort_order     = 'ASC';
              }

              if($request->sortbyparam == $inc)
              {
                $query->join('warehouse_products','warehouse_products.product_id', '=', 'products.id')->where('warehouse_id',$wh->id)->orderBy(\DB::raw('round(warehouse_products.current_quantity,3)'), $sort_order);
              }
              $inc = $inc+2;
            }
          }
        }
        elseif($request->sortbyparam == 2)
        {
          $query->join('types', 'types.id', '=', 'products.type_id')->orderBy($sort_variable, $sort_order)->orderBy('types.title', 'ASC');
        }
        elseif($request->sortbyparam == 5)
        {
          $query->join('types', 'types.id', '=', 'products.type_id')->orderBy($sort_variable, $sort_order)->orderBy('products.brand', $sort_order)->orderBy('types.title', 'ASC');
          // $query->orderBy($sort_variable, $sort_order);
        }
        elseif($request->sortbyparam == 6)
        {
          $query->orderBy($sort_variable, $sort_order);
          // $query->orderBy($sort_variable, $sort_order);
        }
        elseif($request->sortbyparam == 9)
        {
          $query->join('types', 'types.id', '=', 'products.type_id')->orderBy('types.title', $sort_order);
        }
        elseif($request->sortbyparam == 10)
        {
          $query->join('types', 'types.id', '=', 'products.type_id')->orderBy($sort_variable, $sort_order)->orderBy('products.short_desc', $sort_order)->orderBy('types.title', 'ASC');
        }
        elseif($request->sortbyparam == 14)
        {
          $query->join('suppliers', 'suppliers.id', '=', 'products.supplier_id')->join('types', 'types.id', '=', 'products.type_id')->orderBy('suppliers.reference_name', $sort_order)->orderBy('types.title', 'ASC');
        }
        elseif($request->sortbyparam == 15)
        {
          $query->leftJoin('supplier_products', function($join){
            $join->on('supplier_products.supplier_id','=','products.supplier_id');
            $join->on('supplier_products.product_id','=','products.id');
          })->orderBy('supplier_products.supplier_description', $sort_order);
          // $query->orderBy('products.short_desc', $sort_order);
        }
        else
        {
          $query->orderBy('products.short_desc', 'ASC');
          $query->orderBy('products.brand', 'ASC');
        }

        if($search_product != null)
        {
          $filteredRecords = Product::where('refrence_code','LIKE','%'.$search_product.'%')->orWhere('hs_code','LIKE','%'.$search_product.'%')->orWhere('hs_description','LIKE','%'.$search_product.'%')->orWhere('short_desc','LIKE','%'.$search_product.'%')->orWhere('product_notes','LIKE','%'.$search_product.'%')->orWhere('brand','LIKE','%'.$search_product.'%')
          ->orWhere(function($q) use($search_product){
            $q->whereHas('supplier_products',function($z) use($search_product){
              $z->where('product_supplier_reference_no','LIKE','%'.$search_product.'%')->orWhere('supplier_description','LIKE','%'.$search_product.'%');
            });
          });

          $products_ids_prods = $filteredRecords;

        }
        else
        {
          $products_ids_prods = $query;
        }

        $products_ids = $products_ids_prods->pluck('products.id')->toArray();
        $total_unit = WarehouseProduct::whereIn('product_id',$products_ids)->get()->sum('current_quantity');

        // dd($request->recordsFiltered);
        // return Datatables::of($query)
        $dt = Datatables::of($query);

            $dt->addColumn('checkbox', function ($item) {

                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                              </div>';
                return $html_string;
            });

            $dt->addColumn('action', function ($item) {
                $html_string = '
                 <a href="'.url('get-product-detail/'.$item->id).'" class="actionicon editIcon text-center" title="View Detail"><i class="fa fa-eye"></i></a>
                 ';
                // <a href="javascript:void(0);" class="actionicon deleteIcon deleteProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>

                return $html_string;
            });
            // ->editColumn('primary_category',function($item){
            //     return @$item->productCategory->title;
            // })
            // ->filterColumn('primary_category', function( $query, $keyword ) {
            //      $query->whereHas('productCategory', function($q) use($keyword){
            //         $q->where('title','LIKE', "%$keyword%");
            //     });
            // },true )
            $dt->editColumn('refrence_code', function ($item) {
                $refrence_code = $item->refrence_code != null ? $item->refrence_code: "--";
                //return $refrence_code;
                $html_string = '
                 <a target="_blank" href="'.url('get-product-detail/'.$item->id).'" title="View Detail"><b>'.$refrence_code.'</b></a>
                 ';
                return $html_string;
            });

            // ->orderColumn('refrence_code', function ($query, $order) {
            //     $query->orderBy('refrence_code', $order);
            // })

            // $dt->editColumn('hs_code',function($item){
            //     $hs_code = $item->hs_code != null ? $item->hs_code: "--";
            //     return $hs_code;
            // });

            $dt->editColumn('hs_description', function ($item) {
               $html_string = '<span class="m-l-15 inputDoubleClick" id="hs_description" data-fieldvalue="'.@$item->hs_description.'">';
                $html_string .= ($item->hs_description != null ? $item->hs_description : "--");
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="hs_description" style="width:100%;" class="fieldFocus d-none" value="'.$item->hs_description .'">';

                return $html_string;
            });

            $dt->addColumn('category_id', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClick" id="category_id" data-fieldvalue="'.@$item->category_id.'" data-id="cat '.@$item->category_id.' '.@$item->id.'"> ';
                $html_string .= ($item->primary_category != null) ? @$item->productCategory->title.' / '.@$item->productSubCategory->title: "--";
                $html_string .= '</span>';

                $html_string .= '<div class="incomplete-filter d-none inc-fil-cat">
                <select class="font-weight-bold form-control-lg form-control js-states state-tags select-common category_id categories_select'.@$item->id.'" name="category_id" required="true">';
                    // <option value="">Choose Category</option>';

                // $product_parent_category = ProductCategory::select('id','title')->where('parent_id',0)->orderBy('title')->get();

                // if($product_parent_category->count() > 0){
                // foreach($product_parent_category as $pcat){

                // $html_string .= '<optgroup label='.$pcat->title.'>';
                //         $subCat = ProductCategory::select('id','title')->where('parent_id',$pcat->id)->orderBy('title')->get();
                //       foreach($subCat as $scat){
                // $html_string .= '<option '.($scat->id == $item->category_id ? 'selected' : '' ).' value="'.$scat->id.'">'.$scat->title.'</option>';
                //       }
                // $html_string .= '</optgroup>';

                // }
                // }
                $html_string .= '</select></div>';
                return $html_string;
            });

            // ->filterColumn('category_id', function( $query, $keyword ) {
            //     $query = $query->whereIn('category_id', ProductCategory::select('id')->where('title','LIKE',"%$keyword%")->pluck('id'));
            // },true )

            $dt->editColumn('short_desc', function ($item) {
                if($item->short_desc == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="short_desc" style="'.$text_color.'" data-fieldvalue="'.@$item->short_desc.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="short_desc" data-fieldvalue="'.@$item->short_desc.'">';
                $html_string .= $item->short_desc;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="'.$item->short_desc .'">';
                }
                return $html_string;
            });

            $dt->editColumn('product_notes', function ($item) {
                if($item->product_notes == null)
                {
                  $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_notes" data-fieldvalue="'.@$item->product_notes.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="product_notes" style="width:100%;" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_notes" data-fieldvalue="'.@$item->product_notes.'">';
                $html_string .= $item->product_notes;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="product_notes" style="width:100%;" class="fieldFocus d-none" value="'.$item->product_notes .'">';
                }
                return $html_string;
            });

            $dt->addColumn('buying_unit',function($item){
                // $units = Unit::select('id','title')->get();

                if($item->buying_unit == null)
                {
                $text_color = 'color: red;';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit" style="'.$text_color.'"  data-fieldvalue="'.@$item->units->title.'" data-id="buying_unit '.@$item->buying_unit.' '.@$item->id.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';

                $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none buying_select'.@$item->id.'">';
                // <option>Choose Unit</option>';
                // if($units){
                // foreach($units as $unit){
                //     $html_string .= '<option  value="'.$unit->id.'"> '.$unit->title.'</option>';
                // }
                // }
                $html_string .= '</select>';

                $html_string .= '<input type="text"  name="buying_unit" class="fieldFocus d-none" value="'.@$item->units->title.'">';
                }
                else
                {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit"  data-fieldvalue="'.@$item->units->title.'" data-id="buying_unit '.@$item->buying_unit.' '.@$item->id.'">';
                $html_string .= @$item->units->title;
                $html_string .= '</span>';

                $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none buying_select'.@$item->id.'">';
                // <option>Choose Unit</option>';
                // if($units){
                // foreach($units as $unit){
                // $value = $unit->id == $item->buying_unit ? 'selected' : "";
                // $html_string .= '<option '.$value.' value="'.$unit->id.'"> '.$unit->title.'</option>';
                // }
                // }
                $html_string .= '</select>';

                $html_string .= '<input type="text" name="buying_unit" class="fieldFocus d-none" value="'.@$item->units->title.'">';

                }
                return $html_string;
            });

            $dt->addColumn('selling_unit',function($item){
                // $units = Unit::select('id','title')->get();
              //dd(@$item->sellingUnits->title);
                if($item->selling_unit == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit" style="'.$text_color.'"  data-fieldvalue="'.@$item->sellingUnits->title.'" data-id="selling_unit '.@$item->selling_unit.' '.@$item->id.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';

                $html_string .= '<select name="selling_unit" class="select-common form-control buying-unit selling_unit'.@$item->id.' d-none">';
                // <option>Choose Unit</option>';
                // if($units){
                // foreach($units as $unit){
                //     $html_string .= '<option  value="'.$unit->id.'"> '.$unit->title.'</option>';
                // }
                // }
                $html_string .= '</select>';

                $html_string .= '<input type="text"  name="selling_unit" class="fieldFocus d-none" value="'.@$item->sellingUnits->title.'">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit"  data-fieldvalue="'.@$item->sellingUnits->title.' '.@$item->id.'" data-id="selling_unit '.@$item->selling_unit.' '.@$item->id.'">';
                $html_string .= @$item->sellingUnits->title;
                $html_string .= '</span>';

                $html_string .= '<select name="selling_unit" class="select-common form-control selling_unit'.@$item->id.' buying-unit d-none">';
                // <option>Choose Unit</option>';
                // if($units){
                // foreach($units as $unit){
                // $value = $unit->id == $item->selling_unit ? 'selected' : "";
                // $html_string .= '<option '.$value.' value="'.$unit->id.'"> '.$unit->title.'</option>';
                // }
                // }
                $html_string .= '</select>';

                $html_string .= '<input type="text" name="selling_unit" class="fieldFocus d-none" value="'.@$item->sellingUnits->title.'">';

                }
                return $html_string;
            });

            $dt->addColumn('import_tax_book',function($item){
                // $import_tax_book = $item->import_tax_book != null ? $item->import_tax_book.' %': "--";
                // return $import_tax_book;
                if($item->import_tax_book === null)
                {
                  $html_string = '
                <span class="m-l-15 inputDoubleClick" id="import_tax_book" data-fieldvalue="'.@$item->import_tax_book.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="import_tax_book" style="width:100%;" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="import_tax_book" data-fieldvalue="'.@$item->import_tax_book.'">';
                $html_string .= $item->import_tax_book;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="import_tax_book" style="width:100%;" class="fieldFocus d-none" value="'.$item->import_tax_book .'">';
                }
                return $html_string;
            });
            $dt->addColumn('vat',function($item){
                $vat = $item->vat != null ? $item->vat.' %': "--";
                return $vat;
            });
            $dt->addColumn('image', function ($item) {
                // check already uploaded images //
                // $product_images = ProductImage::select('id')->where('product_id', $item->id)->count('id');
                $product_images = $item->prouctImages->count();
                // dd($product_images);

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($product_images > 0)
                {
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-prod-image mr-2" title="View Images"></a> ';
                }
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#productImagesModal" class="img-uploader fa fa-plus d-block" title="Add Images"></a><input type="hidden" id="images_count_'.$item->id.'" value="'.$product_images.'">
                          </div>';

                return $html_string;
            });
            $dt->addColumn('supplier_id',function($item){
                return (@$item->supplier_id != null) ? @$item->def_or_last_supplier->reference_name:'--';
            });


            $dt->addColumn('p_s_reference_number',function($item){
                if(@$item->supplier_products[0]->product_supplier_reference_no == null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_supplier_reference_no"  data-fieldvalue="'.@$item->supplier_products[0]->product_supplier_reference_no.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocus d-none"  value="'.@$item->supplier_products[0]->product_supplier_reference_no .'">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_supplier_reference_no"  data-fieldvalue="'.@$item->supplier_products[0]->product_supplier_reference_no.'">';
                $html_string .= @$item->supplier_products[0]->product_supplier_reference_no;
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocus d-none" value="'.@$item->supplier_products[0]->product_supplier_reference_no .'">';
                }
                return $html_string;
            });


            $dt->addColumn('supplier_description',function($item){
                if($item->supplier_id !== 0)
                {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',@$item->supplier_id)->first();
                    $html_string = '';
                    if($getProductDefaultSupplier)
                    {
                       $html_string = '<span class="m-l-15 inputDoubleClick" id="supplier_description"  data-fieldvalue="'.@$getProductDefaultSupplier->supplier_description.'">'.($getProductDefaultSupplier->supplier_description != NULL ? $getProductDefaultSupplier->supplier_description : "--").'</span>
                        <input type="text" style="width:100%;" name="supplier_description" class="fieldFocus d-none" value="'.$getProductDefaultSupplier->supplier_description.'">';
                    }
                    return $html_string;
                }
                else
                {
                    return "--";
                }
            });

            $dt->filterColumn('supplier_description', function( $query, $keyword ) {
                $query = $query->whereIn('products.id', SupplierProducts::select('product_id')->where('supplier_description','LIKE',"%$keyword%")->pluck('product_id'));
            },true );

            $dt->addColumn('freight',function($item){
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',$item->supplier_id)->first();
                    $html_string = '';
                    if($getProductDefaultSupplier)
                    {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="freight"  data-fieldvalue="'.@$getProductDefaultSupplier->freight.'">'.($getProductDefaultSupplier->freight != NULL ? $getProductDefaultSupplier->freight : "--").'</span>
                        <input type="text" style="width:100%;" name="freight" class="fieldFocus d-none" value="'.@$getProductDefaultSupplier->freight.'">';
                    }
                    return $html_string;
                }
                else
                {
                    return "--";
                }
            });

            $dt->addColumn('landing',function($item){
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',$item->supplier_id)->first();
                    $html_string = '';
                    if($getProductDefaultSupplier)
                    {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="landing"  data-fieldvalue="'.@$getProductDefaultSupplier->landing.'">'.($getProductDefaultSupplier->landing != NULL ? $getProductDefaultSupplier->landing : "--").'</span>
                        <input type="text" style="width:100%;" name="landing" class="fieldFocus d-none" value="'.@$getProductDefaultSupplier->landing.'">';
                    }
                    return $html_string;
                }
                else
                {
                    return "--";
                }
            });

            $dt->addColumn('vendor_price',function($item){
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',@$item->supplier_id)->first();
                    $html_string = '';
                    if($getProductDefaultSupplier)
                    {
                        if($getProductDefaultSupplier->buying_price !== null)
                        {
                            $supplier_currency_logo = @$getProductDefaultSupplier->supplier->getCurrency->currency_symbol;
                        }
                        else
                        {
                            $supplier_currency_logo = '';
                        }

                        $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '');

                        if($getProductDefaultSupplier->buying_price !== null)
                        {
                            $text_color = '';
                        }
                        else
                        {
                            $text_color = 'color: red;';
                        }

                        $html_string = '<span class="m-l-15 inputDoubleClick" style="'.$text_color.'" id="buying_price"  data-fieldvalue="'.@$getProductDefaultSupplier->buying_price.'">'.($getProductDefaultSupplier->buying_price !== NULL ?  ' <b>'.@$supplier_currency_logo.'</b> '.number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') : "--").'</span>
                        <input type="text" style="width:100%;" name="buying_price" class="fieldFocus d-none" value="'.number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '').'">';
                    }
                    return $html_string;
                }
                else
                {
                    return "--";
                }

            });

            $dt->addColumn('vendor_price_in_thb',function($item){
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',$item->supplier_id)->first();
                    $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price_in_thb, 3, '.', '');
                    return (@$getProductDefaultSupplier->buying_price_in_thb !=null) ? $formated_value:'--';
                }

            });

            $dt->addColumn('total_buy_unit_cost_price',function($item){
                return (@$item->total_buy_unit_cost_price != null) ? number_format((float)@$item->total_buy_unit_cost_price, 3, '.', ''):'--';
            });

            $dt->addColumn('last_price_history',function($item){
              if($item->last_price_updated_date == null)
              {
                  return '--';
              }
              else
              {
                return Carbon::parse($item->last_price_updated_date)->format('d/m/Y');
              }

             });

            $dt->addColumn('unit_conversion_rate',function($item){
                if($item->unit_conversion_rate == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate" style="'.$text_color.'" data-fieldvalue="'.number_format((float)@$item->unit_conversion_rate, 3, '.', '').'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="unit_conversion_rate" style="width: 80%;" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate"  data-fieldvalue="'.number_format((float)@$item->unit_conversion_rate, 3, '.', '').'">';
                $html_string .= number_format((float)@$item->unit_conversion_rate, 3, '.', '');
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="unit_conversion_rate" style="width: 80%;" class="fieldFocus d-none" value="'.number_format((float)@$item->unit_conversion_rate, 3, '.', '').'">';
                }
                return $html_string;
            });

            $dt->addColumn('selling_unit_cost_price',function($item){
                if($item->selling_price == null)
                {
                    $html_string = '
                <span class="m-l-15" id="selling_price"  data-fieldvalue="'.number_format((float)@$item->selling_price, 3, '.', '').'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="selling_price" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15" id="selling_price"  data-fieldvalue="'.@$item->selling_price.'">';
                $html_string .= number_format((float)@$item->selling_price, 3, '.', '');
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="selling_price" class="fieldFocus d-none" value="'.number_format((float)@$item->selling_price, 3, '.', '').'">';
                }
                return $html_string;
                return (@$item->selling_price != null) ? number_format((float)@$item->selling_price, 3, '.', ''):'--';
            });

            $current_qty=null;
            $pqty=null;

              if($getWarehouse)
              {
                $dt->addColumn($getWarehouse->warehouse_title.'current',function($item) use($getWarehouse,$current_qty){
                $warehouse_product = $item->warehouse_products->where('warehouse_id',$getWarehouse->id)->first();
                  $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
                  $this->curr_quantity = $qty;
                  return round($qty,3).' '.@$item->sellingUnits->title;
                });

                $dt->addColumn($getWarehouse->warehouse_title.'available', function($item) use ($getWarehouse){
                  $warehouse_product = $item->warehouse_products->where('warehouse_id',$getWarehouse->id)->first();
                  $available_qty = ($warehouse_product->available_quantity != null) ? $warehouse_product->available_quantity: 0;
                  return round($available_qty, 3);
                });

                $dt->addColumn($getWarehouse->warehouse_title.'ecommerce', function($item) use ($getWarehouse){
                  $warehouse_product = $item->warehouse_products->where('warehouse_id',$getWarehouse->id)->first();
                  $eom_reserve_qty = ($warehouse_product->ecommerce_reserved_quantity != null) ? $warehouse_product->ecommerce_reserved_quantity: 0;
                  return round($eom_reserve_qty, 3);
                });
              }

            // $implodeArray = ("'" . implode("', '", $arrayWarehouse) . "'");

            $dt->addColumn('weight',function($item){
                // return (@$item->weight != null) ? @$item->weight." Kg":'-';
                if($item->weight == null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="weight" data-fieldvalue="'.@$item->weight.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="weight" style="width: 100%;" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="weight"  data-fieldvalue="'.@$item->weight.'">';
                $html_string .= $item->weight;
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="weight" style="width: 100%;" class="fieldFocus d-none" value="'.$item->weight .'">';
                }
                return $html_string;
            });

            $dt->addColumn('lead_time',function($item){
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',$item->supplier_id)->first();
                    $html_string = '';
                    if($getProductDefaultSupplier)
                    {
                        if($getProductDefaultSupplier->leading_time !== null)
                        {
                            $text_color = '';
                        }
                        else
                        {
                            $text_color = 'color: red;';
                        }

                        $html_string = '<span class="m-l-15 inputDoubleClick" style="'.$text_color.'" id="leading_time"  data-fieldvalue="'.@$getProductDefaultSupplier->leading_time.'">'.($getProductDefaultSupplier->leading_time != NULL ? $getProductDefaultSupplier->leading_time : "--").'</span>
                        <input type="text" style="width:100%;" name="leading_time" class="fieldFocus d-none" value="'.@$getProductDefaultSupplier->leading_time.'">';
                    }
                    return $html_string;
                }
                else
                {
                    return "--";
                }
            });

            $dt->addColumn('product_type',function($item){
                // $product_type = ProductType::select('id','title')->get();

                if($item->type_id == null)
                {
                $text_color = 'color: red;';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type" style="'.$text_color.'" data-fieldvalue="'.@$item->type_id.'" data-id="type '.@$item->type_id.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';

                $html_string .= '<select name="type_id" class="select-common form-control product_type d-none type_select'.@$item->id.'">';
                // <option value="" selected="" disabled="">Choose Product Type</option>';
                // foreach ($product_type as $type) {
                // $html_string .='<option value="'.$type->id.'" >'.$type->title.'</option>';
                // }
                $html_string .= '</select>';

                }
                else
                {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type"  data-fieldvalue="'.@$item->type_id.'" data-id="type '.@$item->type_id.' '.@$item->id.'">';
                $html_string .= @$item->productType->title;
                $html_string .= '</span>';
                $html_string .= '<select name="type_id" class="select-common form-control product_type d-none type_select'.@$item->id.'">';
                // <option value="" disabled="">Choose Type</option>';
                // foreach ($product_type as $type) {
                // $html_string .= '<option value="'.$type->id.'" "' .($item->type_id == $type->id ? "selected" : ""). '" >'.$type->title.'</option>';
                // }


                $html_string .= '</select>';

                }
                return $html_string;
            });

            $dt->editColumn('brand',function($item){
                if($item->brand == null)
                {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="brand"  data-fieldvalue="'.@$item->brand.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="">';
                }
                else
                {
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="brand"  data-fieldvalue="'.@$item->brand.'">';
                $html_string .= $item->brand;
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="'.$item->brand .'">';
                }
                return $html_string;
            });

            $dt->addColumn('product_temprature_c',function($item){
                if($item->product_temprature_c == null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="'.@$item->product_temprature_c.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="'.@$item->product_temprature_c.'">';
                $html_string .= $item->product_temprature_c;
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="'.$item->product_temprature_c .'">';
                }
                return $html_string;
            });

            $dt->addColumn('restaruant_price',function($item){
                if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
                {
                    $getRecord = new Product;
                    $prodFixPrice   = $getRecord->getDataOfProductMargins($item->id, 1, "prodFixPrice");
                    $formated_value = number_format($prodFixPrice->fixed_price,3,'.',',');

                    $html_string = '
                    <span class="m-l-15 inputDoubleClick" style="font-style: italic;" id="product_fixed_price"  data-fieldvalue="'.@$formated_value.'">';
                    $html_string .= (@$formated_value !== null) ? $formated_value : '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;" name="product_fixed_price" class="fieldFocus d-none" value="'.$formated_value .'">';
                    return $html_string;
                }
                else
                {
                    $getRecord = new Product;
                    $prodFixPrice   = $getRecord->getDataOfProductMargins($item->id, 1, "prodFixPrice");
                    $formated_value = number_format($prodFixPrice->fixed_price,3,'.',',');
                    return (@$formated_value !== null) ? $formated_value : '--';
                }


            });

            $dt->setRowId(function ($item) {
                return @$item->id;
            });

            // $finalArray = array_merge($customRawCol,$arrayWarehouse);

            $dt->escapeColumns([]);
            $dt->rawColumns(['checkbox','action','name','category_id','supplier_id','image','import_tax_book','import_tax_actual','freight','landing','total_buy_unit_cost_price','unit_conversion_rate','selling_unit_cost_price','product_type','brand','product_temprature_c','weight','lead_time','last_price_history','refrence_code','vat','hs_code','hs_description','short_desc','buying_unit','selling_unit','supplier_description','vendor_price_in_thb','vendor_price','restaruant_price','product_notes']);
            $dt->with(['total_unit' => number_format(floatval($total_unit),2,'.',',')]);
            return $dt->make(true);
    }
}
