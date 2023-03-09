<?php

namespace App\Helpers\Datatables;

use App\Models\Common\Courier;
use Carbon\Carbon;
use App\Models\Common\StockManagementOut;
use App\Models\Common\PoGroup;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\Order\OrderProductNote;
use Auth;
use App\Models\Common\SupplierProducts;
use App\Models\Common\ProductType;
use DB;
use App\User;
use App\Models\Common\ProductSecondaryType;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\ProductCategory;
use App\Models\Common\Product;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\ProductImage;
use App\Models\Common\Supplier;
use App\Models\Common\Unit;


class ProductDatatable {

    public static function returnAddColumnDeactivatedProducts($column, $item) {
        switch ($column) {
            case 'product_temprature_c':
                return (@$item->product_temprature_c != null) ? @$item->product_temprature_c : '--';
                break;

            case 'product_brand':
                return (@$item->brand != null) ? @$item->brand : '--';
                break;

            case 'product_type_2':
                return (@$item->type_id_2 != null) ? @$item->productType2->title : '--';
                break;

            case 'product_type':
                return (@$item->type_id != null) ? @$item->productType->title : '--';
                break;

            case 'lead_time':
                $getProductLastSupplierName = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                return (@$getProductLastSupplierName->leading_time != null) ? @$getProductLastSupplierName->leading_time:'-';
                break;

            case 'weight':
                return (@$item->weight != null) ? @$item->weight :'-';
                break;

            case 'phuket_reserved_qty':
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',2)->first();
                return (@$warehouse_product->reserved_quantity != null) ? @$warehouse_product->reserved_quantity:'0';
                break;

            case 'phuket_current_qty':
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',2)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:'0 ';
                return $qty.$item->sellingUnits->title;
                break;

            case 'bangkok_reserved_qty':
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',1)->first();
                return (@$warehouse_product->reserved_quantity != null) ? @$warehouse_product->reserved_quantity:'0';
                break;

            case 'bangkok_current_qty':
                $warehouse_product = WarehouseProduct::where('product_id',$item->id)->where('warehouse_id',1)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:'0 ';
                return $qty.$item->sellingUnits->title;
                break;

            case 'selling_unit_cost_price':
                return (@$item->selling_price != null) ? number_format((float)@$item->selling_price, 3, '.', ''):'-';
                break;

            case 'unit_conversion_rate':
                return (@$item->unit_conversion_rate != null) ? number_format((float)@$item->unit_conversion_rate, 3, '.', ''):'-';
                break;

            case 'total_buy_unit_cost_price':
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                if($getProductDefaultSupplier !== null)
                {
                    $importTax = $getProductDefaultSupplier->import_tax_actual ? $getProductDefaultSupplier->import_tax_actual : $item->import_tax_book;

                    $total_buying_price = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->extra_cost)+($getProductDefaultSupplier->extra_tax)+($getProductDefaultSupplier->buying_price);
                    $newTotalBuyingPrice = (($importTax)/100) * $total_buying_price;
                    $total_buying_price = $total_buying_price + $newTotalBuyingPrice;

                    return (@$total_buying_price != null) ? number_format((float)@$total_buying_price, 3, '.', ''):'--';
                }
                break;

            case 'vendor_price':
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                return (@$getProductDefaultSupplier != null) ? number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', ''):'--';
                break;

            case 'landing':
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();

                return (@$getProductDefaultSupplier != null) ? @$getProductDefaultSupplier->landing :'--';
                break;

            case 'freight':
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                return ($getProductDefaultSupplier != null) ? $getProductDefaultSupplier->freight:'--';
                break;

            case 'supplier_id':
                return (@$item->supplier_id != null) ? @$item->def_or_last_supplier->reference_name:'-';
                break;

            case 'vat':
                $vat = $item->vat !== null ? $item->vat.' %': "--";
                return $vat;
                break;

            case 'import_tax_book':
                $import_tax_book = $item->import_tax_book != null ? $item->import_tax_book.' %': "--";
                return $import_tax_book;
                break;

            case 'selling_unit':
                return @$item->sellingUnits->title;
                break;

            case 'buying_unit':
                return @$item->units->title;
                break;

            case 'category_id':
                return @$item->productSubCategory->title;
                break;

            case 'action':
                $html_string = '
                <a href="'.url('get-product-detail/'.$item->id).'" class="actionicon editIcon text-center" title="View Detail"><i class="fa fa-eye"></i></a>
                ';
               return $html_string;
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                              </div>';
                return $html_string;
                break;

        }
    }

    public static function returnEditColumnDeactivatedProducts($column, $item) {
        switch ($column) {
            case 'hs_code':
                $hs_code = $item->hs_code != null ? $item->hs_code: "--";
                return $hs_code;
                break;

            case 'primary_category':
                return @$item->productCategory->title;
                break;

            case 'refrence_code':
                $refrence_code = $item->refrence_code != null ? $item->refrence_code: "--";
                $html_string = '
                 <a href="'.url('get-product-detail/'.$item->id).'" title="View Detail"><b>'.$refrence_code.'</b></a>
                 ';
                return $html_string;
                break;

        }
    }

    public static function returnFilterColumnDeactivatedProducts($column, $item, $keyword) {
        switch ($column) {
            case 'category_id':
                $item = $item->whereHas('productCategory', function($q) use($keyword) {
                    $q->where('title','LIKE',"%$keyword%");
                });
                break;
        }
    }

    public static function returnAddColumnIcompleteProducts($column, $item) {
        switch ($column) {
            case 'restaruant_price':
                $getRecord = new Product;
                $prodFixPrice   = $getRecord->getDataOfProductMargins($item->id, 1, "prodFixPrice");
                $formated_value = $prodFixPrice != 'N.A' ? number_format($prodFixPrice->fixed_price,3,'.',',') : 0;
                return $formated_value;
                break;

            case 'expiry':
                if($item->expiry == null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="'.@$item->expiry.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="expiry" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="'.@$item->expiry.'">';
                $html_string .= $item->expiry." Kg";
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="expiry" class="fieldFocus d-none" value="'.$item->expiry .'">';
                }
                return $html_string;
                break;

            case 'lead_time':
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
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
                break;

            case 'weight':
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
                break;

            case 'selling_unit_cost_price':
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
                break;

            case 'unit_conversion_rate':
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
                break;

            case 'total_buy_unit_cost_price':
                $formated_value = number_format((float)@$item->total_buy_unit_cost_price, 3, '.', '');
                return (@$item->total_buy_unit_cost_price != null) ? $formated_value : '--';
                break;

            case 't_b_u_c_p_of_supplier':
                $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                if($getProductDefaultSupplier)
                {
                    $supplier_currency_logo = @$getProductDefaultSupplier->supplier->getCurrency->currency_symbol;
                    $formated_value = number_format((float)@$item->t_b_u_c_p_of_supplier, 3, '.', '');
                    return (@$item->t_b_u_c_p_of_supplier !== null) ? ' <b>'.@$supplier_currency_logo.'</b> '.$formated_value:'--';
                }
                else
                {
                    return "--";
                }
                break;

            case 'vendor_price_in_thb':
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = SupplierProducts::with('supplier')->where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                    $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price_in_thb, 3, '.', '');
                    return (@$getProductDefaultSupplier->buying_price_in_thb !== null) ? $formated_value:'--';
                }
                else
                {
                    return "--";
                }
                break;

            case 'vendor_price':
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
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

                break;

            case 'supplier_desc':
                if($item->supplier_id !== 0)
                {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                    $html_string = '';
                    if($getProductDefaultSupplier)
                    {
                       $html_string = '<span class="m-l-15 inputDoubleClick sup_desc_width" id="supplier_description"  data-fieldvalue="'.@$getProductDefaultSupplier->supplier_description.'">'.($getProductDefaultSupplier->supplier_description != NULL ? $getProductDefaultSupplier->supplier_description : "--").'</span>
                        <input type="text" style="width:100%;" name="supplier_description" class="fieldFocus d-none" value="'.$getProductDefaultSupplier->supplier_description.'">';
                    }
                    return $html_string;
                }
                else
                {
                    return "--";
                }
                break;

            case 'landing':
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
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
                break;

            case 'freight':
                if($item->supplier_id != 0)
                {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
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
                break;

            case 'image':
                $product_images = ProductImage::where('product_id', $item->id)->count('id');

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($product_images > 0)
                {
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-prod-image mr-2" title="View Images"></a> ';
                }
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#productImagesModal" class="img-uploader fa fa-plus d-block" title="Add Images"></a>
                          </div>';

                return $html_string;
                break;

            case 'vat':
                $vat = $item->vat !== null ? $item->vat.' %': "--";
                return $vat;
                break;

            case 'import_tax_book':
                $import_tax_book = $item->import_tax_book != null ? $item->import_tax_book.' %': "--";
                return $import_tax_book;
                break;

            case 'supplier_id':
                $getSuppliers = Supplier::where('status',1)->orderBy('reference_name')->get();
                if($item->supplier_id === 0)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                    <span class="m-l-15 inputDoubleClick sup-width" id="supplier_id" style="'.$text_color.'"  data-fieldvalue="'.@$item->supplier_id.'">';
                    $html_string .= 'Select Supplier';
                    $html_string .= '</span>';

                    $html_string .= '<div class="d-none incomplete-filter inc-fil-supp"><select class="font-weight-bold form-control-lg form-control select-common js-states state-tags supplier_id" name="supplier_id" required="true">
                         <option value="" >Choose Supplier</option>';
                    if($getSuppliers)
                    {
                        foreach($getSuppliers as $sp)
                        {
                            $html_string .= '<option  value="'.$sp->id.'"> '.$sp->reference_name.'</option>';
                        }
                    }
                    $html_string .= '</select></div>';
                }
                else
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClick sup-width" id="supplier_id"  data-fieldvalue="'.@$item->supplier_id.'">';
                    $html_string .= @$item->def_or_last_supplier->reference_name;
                    $html_string .= '</span>';

                     $html_string .= '<div class="d-none incomplete-filter inc-fil-supp"><select class="font-weight-bold form-control-lg form-control select-common js-states state-tags supplier_id" name="supplier_id" required="true">
                     <option value="" >Choose Supplier</option>';
                    if($getSuppliers)
                    {
                        foreach($getSuppliers as $sp)
                        {
                            $html_string .= '<option '.($item->supplier_id == $sp->id ? 'selected' : '').' value="'.$sp->id.'"> '.$sp->reference_name.'</option>';
                        }
                    }
                  $html_string .= '</select></div>';

                }
                return $html_string;
                break;

            case 'product_temprature_c':
                if($item->product_temprature_c == null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick temp-width" id="product_temprature_c"  data-fieldvalue="'.@$item->product_temprature_c.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick temp-width" id="product_temprature_c"  data-fieldvalue="'.@$item->product_temprature_c.'">';
                $html_string .= $item->product_temprature_c;
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="'.$item->product_temprature_c .'">';
                }
                return $html_string;
                break;

            case 'product_brand':
                if($item->brand == null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick brand-width" id="brand"  data-fieldvalue="'.@$item->brand.'">';
                $html_string .= '--';
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick brand-width" id="brand"  data-fieldvalue="'.@$item->brand.'">';
                $html_string .= $item->brand;
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="'.$item->brand .'">';
                }
                return $html_string;
                break;

            case 'product_type_2':
                $product_type = ProductSecondaryType::orderBy('title','asc')->get();
                if($item->type_id_2 == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_2" style="'.$text_color.'" data-fieldvalue="'.@$item->type_id_2.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';

                $html_string .= '<select name="type_id_2" class="select-common form-control product_type_2 d-none">
                <option value="" selected="" disabled="">Choose Product Type 2</option>';
                foreach ($product_type as $type) {
                $html_string .='<option value="'.$type->id.'" >'.$type->title.'</option>';
                }
                $html_string .= '</select>';

                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_2"  data-fieldvalue="'.@$item->type_id_2.'">';
                $html_string .= @$item->productType2->title;
                $html_string .= '</span>';

                $html_string .= '<select name="type_id_2" class="select-common form-control product_type d-none">
                <option value="" disabled="">Choose Product Type 2</option>';
                foreach ($product_type as $type) {
                $html_string .= '<option value="'.$type->id.'" "' .($item->type_id_2 == $type->id ? "selected" : ""). '" >'.$type->title.'</option>';
                }

                $html_string .= '</select>';

                }
                return $html_string;
                break;

            case 'product_type':
                $product_type = ProductType::all();
                if($item->type_id == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type" style="'.$text_color.'" data-fieldvalue="'.@$item->type_id.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';

                $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                <option value="" selected="" disabled="">Choose Product Type</option>';
                foreach ($product_type as $type) {
                $html_string .='<option value="'.$type->id.'" >'.$type->title.'</option>';
                }
                $html_string .= '</select>';

                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type"  data-fieldvalue="'.@$item->type_id.'">';
                $html_string .= @$item->productType->title;
                $html_string .= '</span>';

                $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                <option value="" disabled="">Choose Type</option>';
                foreach ($product_type as $type) {
                $html_string .= '<option value="'.$type->id.'" "' .($item->type_id == $type->id ? "selected" : ""). '" >'.$type->title.'</option>';
                }

                $html_string .= '</select>';

                }
                return $html_string;
                break;

            case 'selling_unit':
                $units = Unit::all();
                if($item->selling_unit == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit" style="'.$text_color.'"  data-fieldvalue="'.@$item->sellingUnits->title.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';
                $html_string .= '<select name="selling_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                if($units){
                foreach($units as $unit){
                    $html_string .= '<option  value="'.$unit->id.'"> '.$unit->title.'</option>';
                }
                }
                $html_string .= '</select>';
                $html_string .= '<input type="text"  name="selling_unit" class="fieldFocus d-none" value="'.@$item->sellingUnits->title.'">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit"  data-fieldvalue="'.@$item->sellingUnits->title.'">';
                $html_string .= @$item->sellingUnits->title;
                $html_string .= '</span>';
                $html_string .= '<select name="selling_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                if($units){
                foreach($units as $unit){
                $value = $unit->id == $item->selling_unit ? 'selected' : "";
                $html_string .= '<option '.$value.' value="'.$unit->id.'"> '.$unit->title.'</option>';
                }
                }
                $html_string .= '</select>';
                $html_string .= '<input type="text" name="selling_unit" class="fieldFocus d-none" value="'.@$item->sellingUnits->title.'">';

                }
                return $html_string;
                break;

            case 'buying_unit':
                $units = Unit::all();
                if($item->buying_unit == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit" style="'.$text_color.'"  data-fieldvalue="'.@$item->units->title.'">';
                $html_string .= 'Select';
                $html_string .= '</span>';
                $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                if($units){
                foreach($units as $unit){
                    $html_string .= '<option  value="'.$unit->id.'"> '.$unit->title.'</option>';
                }
                }
                $html_string .= '</select>';

                $html_string .= '<input type="text"  name="buying_unit" class="fieldFocus d-none" value="'.@$item->units->title.'">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit"  data-fieldvalue="'.@$item->units->title.'">';
                $html_string .= @$item->units->title;
                $html_string .= '</span>';

                $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                if($units){
                foreach($units as $unit){
                $value = $unit->id == $item->buying_unit ? 'selected' : "";
                $html_string .= '<option '.$value.' value="'.$unit->id.'"> '.$unit->title.'</option>';
                }
                }
                $html_string .= '</select>';

                $html_string .= '<input type="text" name="buying_unit" class="fieldFocus d-none" value="'.@$item->units->title.'">';
                }
                return $html_string;
                break;

            case 'category_id':
                $html_string = '<span class="m-l-15 inputDoubleClick" id="category_id" data-fieldvalue="'.@$item->category_id.'"> ';
                $html_string .= ($item->primary_category != null) ? $item->productCategory->title.' / '.$item->productSubCategory->title: "--";
                $html_string .= '</span>';
                $html_string .= '<div class="incomplete-filter d-none inc-fil-cat">
                <select class="font-weight-bold form-control-lg form-control js-states state-tags select-common category_id" name="category_id" required="true">
                    <option value="">Choose Category</option>';
                $product_parent_category = ProductCategory::where('parent_id',0)->orderBy('title')->get();
                if($product_parent_category->count() > 0){
                  foreach($product_parent_category as $pcat){
                  $html_string .= '<optgroup label='.$pcat->title.'>';
                          $subCat = ProductCategory::where('parent_id',$pcat->id)->orderBy('title')->get();
                        foreach($subCat as $scat){
                  $html_string .= '<option '.($scat->id == $item->category_id ? 'selected' : '' ).' value="'.$scat->id.'">'.$scat->title.'</option>';
                        }
                  $html_string .= '</optgroup>';
                  }
                }
                $html_string .= '</select></div>';
                return $html_string;
                break;

            case 'hs_code':
                $hs_code = $item->hs_code != null ? $item->hs_code : "--";
                return $hs_code;
                break;

            case 'p_s_reference_number':
                if($item->supplier_id !== 0)
                {
                  $getProductDefaultSupplier = SupplierProducts::where('product_id',@$item->id)->where('supplier_id',@$item->supplier_id)->first();
                  $html_string = '';
                  if($getProductDefaultSupplier)
                  {
                    $html_string = $getProductDefaultSupplier->product_supplier_reference_no != NULL ? $getProductDefaultSupplier->product_supplier_reference_no : "--";
                  }
                  return $html_string;
                }
                else
                {
                  return "--";
                }
                break;

            case 'action':
                $html_string = '
                <a href="javascript:void(0);" class="actionicon deleteIcon deleteProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                <a href="'.url('get-product-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>
                ';
                return $html_string;
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                </div>';
                return $html_string;
                break;
        }
    }

    public static function returnEditColumnIcompleteProducts($column, $item) {
        switch ($column) {
            case 'short_desc':
                if($item->short_desc == null)
                {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick desc-width" id="short_desc" style="'.$text_color.'" data-fieldvalue="'.@$item->short_desc.'">';
                $html_string .= '--';
                $html_string .= '</span>';
                $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick desc-width" id="short_desc" data-fieldvalue="'.@$item->short_desc.'">';
                $html_string .= $item->short_desc;
                $html_string .= '</span>';
                $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="'.$item->short_desc .'">';
                }
                return $html_string;
                break;

            case 'refrence_code':
                if($item->refrence_code == null)
                {
                    $html_string = '
                <span class="m-l-15" id="refrence_code"  data-fieldvalue="'.@$item->refrence_code.'">';
                $html_string .= '--';
                $html_string .= '</span>';
                $html_string .= '<input type="text" style="width:100%;" name="refrence_code" class="d-none" value="">';
                }
                else
                {
                    $html_string = '
                <span class="m-l-15" id="refrence_code"  data-fieldvalue="'.@$item->refrence_code.'">';
                $html_string .= $item->refrence_code;
                $html_string .= '</span>';
                $html_string .= '<input type="text" style="width:100%;" name="refrence_code" class="d-none" value="'.$item->refrence_code .'">';
                }
                return $html_string;
                break;
        }
    }


}
