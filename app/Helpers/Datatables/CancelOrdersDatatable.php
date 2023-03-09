<?php

namespace App\Helpers\Datatables;

use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\ProductType;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Unit;
use App\Models\Common\Warehouse;
use Illuminate\Support\Facades\Auth;

class CancelOrdersDatatable {

    public static function returnAddColumn($column, $item) {
        switch ($column) {
            case 'notes':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                return "--";
              }
              else
              {
                // check already uploaded images //
                $notes = OrderProductNote::where('order_product_id', $item->id)->count();

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($notes > 0){
                $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                }
                if(@$item->status != 18){
                   $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
                          </div>';
                        }else{
                          $html_string .= '--';
                        }

                return $html_string;
                }
                break;

            case 'supply_from':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }

              if($item->product_id == null)
              {
                return "N.A";
              }
              else
              {

                $label = $item->from_warehouse_id != null ? @$item->from_warehouse->warehouse_title : (@$item->from_supplier->reference_name != null ? $item->from_supplier->reference_name : "--");
                $html =  '<span class="'.$class.'">'.@$label.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" id="select_supply_from" name="from_warehouse_id" >';
                $html .= '<option value="" selected disabled>Choose Supply From</option>';
                $html .= '<optgroup label="Select Warehouse">';
                $warehouses = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
                foreach ($warehouses as $w)
                {
                  if($item->from_warehouse_id == $w->id)
                  {
                    $html = $html.'<option selected value="w-'.$w->id.'">'.$w->warehouse_title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="w-'.$w->id.'">'.$w->warehouse_title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Suppliers">';
                $getSuppliersByCat = SupplierProducts::where('product_id',$item->product->id)->pluck('supplier_id')->toArray();
                if(!empty($getSuppliersByCat))
                {
                      $getSuppliers = Supplier::whereIn('id',$getSuppliersByCat)->orderBy('reference_name')->get();
                      foreach ($getSuppliers as $getSupplier)
                      {
                        $value = $item->supplier_id == $getSupplier->id ? 'selected' : "";
                        $html .= '<option '.$value.' value="s-'.$getSupplier->id.'">'.$getSupplier->reference_name.'</option>';
                      }
                }
                $html .= ' </optgroup></select>';
                return $html;
              }
                break;

            case 'vat':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  return $item->vat != null ? $item->vat : '--';
                }
                else
                {
                  if($item->unit_price != null)
                  {
                    $clickable = "inputDoubleClick";
                  }
                  else
                  {
                    $clickable = "";
                  }
                  $html = '<span class="'.$clickable.'" data-fieldvalue="'.$item->vat.'">'.($item->vat != null ? $item->vat : '--').'</span><input type="number" name="vat" value="'.@$item->vat.'"  class="vat form-control input-height d-none" id="input_vat" style="width:90%"> %';
                  return $html;
                }
                break;

            case 'total_price':
                if($item->total_price == null){ return $total_price = "N.A"; }
                else{
                  $total_price = $item->total_price;
                }
                $html_string ='<span class="total-price total-price-'.$item->id.'"">'.number_format($total_price, 2, '.', ',').'</span>';
                return $html_string;
                break;

            case 'unit_price_with_vat':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }
              $unit_price = round($item->unit_price,2);
              $vat = $item->vat;
                $vat_amount = @$unit_price * ( @$vat / 100 );
                if($item->unit_price_with_vat !== null)
                {
                  $unit_price_with_vat = number_format(@$item->unit_price_with_vat,4,'.','');
                  $unit_price_with_vat2 =  preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);

                }
                else
                {
                  $unit_price_with_vat = number_format((@$unit_price+@$vat_amount),4,'.','');
                  $unit_price_with_vat2 = number_format(floor((@$unit_price+@$vat_amount)*100)/100,2);
                }

                 $html = '<span class="'.$class.'" data-fieldvalue="'.@$unit_price_with_vat.'">'.@$unit_price_with_vat2.'</span><input type="tel" name="unit_price_with_vat" step="0.01"  value="'.$unit_price_with_vat.'" class="unit_price_with_vat form-control input-height d-none" id="input_unit_price_with_vat" style="width:100%;  border-radius:0px;">';


                 return $html;
                break;

            case 'unit_price':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                    $class = "";
                }
                else
                {
                    $class = "inputDoubleClick";
                }
                $star = '';
                if(is_numeric($item->margin)){
                    $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
                    if($product_margin){
                        $star = '*';
                    }
                }
                $unit_price = number_format($item->unit_price, 2, '.', '');
                $html = '<span class="'.$class.'" data-fieldvalue="'.number_format(@$item->unit_price, 2,'.','').'">'.$star.number_format(@$item->unit_price, 2).'</span><input type="number" name="unit_price" step="0.01"  value="'.number_format(@$item->unit_price,2,'.','').'" class="unit_price form-control input-height d-none" id="input_unit_price" style="width:100%;  border-radius:0px;">';
                return $html;
                break;

            case 'margin':
                if($item->is_billed != "Product")
                {
                    return "N.A";
                }
                if($item->margin == null)
                {
                    return "Fixed Price";
                }
                else
                {
                    return is_numeric($item->margin) ? $item->margin.'%' : $item->margin;
                }
                break;

            case 'exp_unit_cost':
                if($item->exp_unit_cost == null)
                {
                  return "N.A";
                }
                else
                {
                 $html_string ='<span class="unit-price-'.$item->id.'"">'.number_format(floor($item->exp_unit_cost*100)/100, 2).'</span>';
                }
                return $html_string;
                break;

            case 'po_number':
                if($item->status > 7){
                    return @$item->purchase_order_detail != null ?  $item->purchase_order_detail->PurchaseOrder->ref_id: '--';
                  }else{
                    return '--';
                  }
                break;

            case 'po_quantity':
                if($item->status > 7){
                    return @$item->purchase_order_detail != null ?  $item->purchase_order_detail->quantity.' '.$item->product->units->title : '--';
                  }else{
                    return '--';
                  }
                break;

            case 'total_amount':
                $unit_price_with_vat2 =  preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);
                return ($item->total_price_with_vat !== null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $item->total_price_with_vat),2,'.','') : "--");
                break;

            case 'buying_unit':
                return ($item->product && $item->product->units !== null ? $item->product->units->title : "N.A");
                break;

            case 'sell_unit':
                if($item->product_id !== NULL)
              {
                return $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               $html =  '<span class="inputDoubleClick">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" id="select_sell_unit" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
                $units = Unit::orderBy('title')->get();
                foreach ($units as $w)
                {
                  if($item->selling_unit == $w->id)
                  {
                    $html = $html.'<option selected value="'.$w->id.'">'.$w->title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="'.$w->id.'">'.$w->title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Sale Unit">';

                $html .= ' </optgroup></select>';
                return $html;
              }
                break;

            case 'quantity_ship':
              $checked = $item->is_retail == "qty" ? "disabled" : "";
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
                $radio = "disabled";
              }
              else
              {
                $class = "inputDoubleClick";
                $radio = "";
              }
              $radio = $item->is_retail == "qty" ? "disabled" : "";
              if(@$item->is_billed !== 'Billed')
              {
              // Sales unit code shift here
               if($item->product_id !== NULL)
              {
                $sale_unit = $item->product && $item->product->sellingUnits ? @$item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               if(@$item->product_id === NULL){
                $html =  '<span class="">'.@$unit.'</span>';
               }else{
               $html =  '<span class="'.$class.'">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" id="select_qty_shipped" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
                $units = Unit::orderBy('title')->get();
                foreach ($units as $w)
                {
                  if($item->selling_unit == $w->id)
                  {
                    $html = $html.'<option selected value="'.$w->id.'">'.$w->title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="'.$w->id.'">'.$w->title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Sale Unit">';

                $html .= ' </optgroup></select>';
              }
                $sale_unit = $html;
              }
                }

                 $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->qty_shipped.'">'.($item->qty_shipped != null ? $item->qty_shipped : "--" ).'</span><input type="number" name="qty_shipped"  value="'.$item->qty_shipped.'" class="qty_shipped form-control input-height d-none" id="input_qty_shipped" style="width:100%; border-radius:0px;"> ';
                  $html .= @$sale_unit;
                  if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                   $html .= '
              <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->qty_shipped.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' id="checkbox_qty_shipped">';

              $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
                }
              return $html;
                break;

            case 'pcs_shipped':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
                {
                  $class = "";
                  $radio = "disabled";
                }
                else
                {
                  $class = "inputDoubleClick";
                  $radio = "";
                }
                $radio = $item->is_retail == "pieces" ? "disabled" : "";
                if($item->is_billed !== 'Billed'){
                 $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->pcs_shipped.'">'.($item->pcs_shipped != null ? $item->pcs_shipped : "--" ).'</span><input type="number" name="pcs_shipped"  value="'.$item->pcs_shipped.'" class="pcs_shipped form-control input-height d-none" id="input_pcs_shipped" style="width:100%; border-radius:0px;"> ';
                    if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7){
                     $html .= '
                <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->pcs_shipped.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' id="checkbox_pcs_shipped">';

                $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
                  }
                  }
                  else
                  {
                      $html = 'N.A';
                  }

                  return $html;
                break;

            case 'number_of_pieces':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
                {
                  $class = "";
                  $radio = "disabled";
                }
                else
                {
                  $class = "inputDoubleClick";
                  $radio = "";
                }

                $radio = $item->is_retail == "pieces" ? "disabled" : "";
                if(@$item->get_order->primary_status == 25 || @$item->get_order->primary_status == 28)
                {
                  $class = 'inputDoubleClick';
                }
                 if(@$item->get_order->primary_status == 3){
                  if(@$item->is_billed !== 'Billed')
                  {
                    $html = '<span class="" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span> ';
                  }
                  else
                  {
                    $html = 'N.A';
                  }
                  }else if(@$item->is_billed !== 'Billed'){
                  $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span><input type="number" name="number_of_pieces"  value="'.$item->number_of_pieces.'" class="number_of_pieces form-control input-height d-none" id="input_number_of_pieces" style="width:100%; border-radius:0px;">';
                  if(@$item->get_order->primary_status != 3 && @$item->is_billed !== 'Billed' && $item->get_order->primary_status !== 25 && $item->get_order->primary_status !== 28){

                  $html .= '
                  <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                  $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' id="checkbox_number_of_pieces" disabled>';

                  $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';


                  }else if(Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                 $html .= '
                  <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                  $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' id="checkbox_number_of_pieces1" disabled>';

                  $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
                  }
                  }
                  else
                  {
                      $html = 'N.A';
                  }
                  return $html;
                break;

            case 'quantity':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
                $radio = "disabled";
              }
              else
              {
                $class = "inputDoubleClick";
                $radio = "";
              }

              $radio = $item->is_retail == "qty" ? "disabled" : "";
              // Sales unit code shift here
               if($item->product_id !== NULL)
              {
                $sale_unit = $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               $html = '<span class="'.$class.'">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" id="select_quantity" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
                $units = Unit::orderBy('title')->get();
                foreach ($units as $w)
                {
                  if($item->selling_unit == $w->id)
                  {
                    $html = $html.'<option selected value="'.$w->id.'">'.$w->title.'</option>';
                  }
                  else
                  {
                    $html = $html.'<option value="'.$w->id.'">'.$w->title.'</option>';
                  }
                }

                $html = $html.'</optgroup>';
                $html .= '<optgroup label="Sale Unit">';

                $html .= ' </optgroup></select>';
                $sale_unit = $html;
              }
                $html = '';
              // sale unit code ends
              if(@$item->get_order->primary_status == 3){
                $html .= '<span class="" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span> ';
              }else{
              $html .= '<span class="inputDoubleClick" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span>';
              $html .= '<input type="number" name="quantity"  value="'.$item->quantity.'" class="quantity form-control input-height d-none" id="input_quantity" style="width:100%; border-radius:0px;"> ';
                }
              $html .= @$sale_unit;
             if(@$item->get_order->primary_status !== 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
              $html .= '
              <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' id="checkbox_quantity">';
              $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
                }
              return $html;
                break;

            case 'type_id':
                $product_type = ProductType::select('id','title')->get();
                if($item->type_id == null)
                {
                  $html_string = '
                  <span class="m-l-15 inputDoubleClick" id="product_type" data-fieldvalue="'.$item->type_id.'">';
                  $html_string .= 'Select';
                  $html_string .= '</span>';

                  $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                  <option value="" selected="" disabled="">Choose Type</option>';
                  foreach ($product_type as $type) {
                    $html_string .='<option value="'.$type->id.'" >'.$type->title.'</option>';
                  }
                  $html_string .= '</select>';

                }
                else
                {
                  $html_string = '
                  <span class="m-l-15 inputDoubleClick" id="product_type"  data-fieldvalue="'.$item->type_id.'">';
                  $html_string .= $item->productType->title;
                  $html_string .= '</span>';
                  $html_string .= '<select name="type_id" class="select-common form-control product_type d-none type_select'.$item->id.'">
                  <option value="" disabled="">Choose Type</option>';
                  foreach ($product_type as $type) {
                    $value = $item->type_id == $type->id ? 'selected' : "";
                    $html_string .= '<option '.$value.' value="'.$type->id.'">'.$type->title.'</option>';
                  }
                  $html_string .= '</select>';
                }
                return $html_string;
                break;

            case 'temprature':
                if($item->product_id !== NULL)
                return  $item->unit ? $item->product->product_temprature_c : "N.A";
                break;

            case 'selling_unit':
                if($item->product_id !== NULL)
                return   $item->unit ? $item->unit->title : "N.A";
                break;

            case 'brand':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  $class = "";
                }
                else
                {
                  $class = "inputDoubleClick";
                }
                  $html = '<span class="'.$class.'" data-fieldvalue="'.$item->brand.'">'.($item->brand != null ? $item->brand : "--" ).'</span><input type="text" name="brand" value="'.$item->brand.'" min="0" class="brand form-control input-height d-none" id="input_brand" style="width:100%">';
                  return $html;
                break;

            case 'category':
                if($item->product_id!=null)
                return $item->product->productSubCategory->title;
                else
                return 'N.A';
                break;

            case 'description':
                $class = Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6 ? "" : "inputDoubleClick";
                $style = $item->short_desc == null ? "color:red;" : "";
                $html = '<span class="'.$class.'" data-fieldvalue="'.$item->short_desc.'" style="'.@$style.'">'.($item->short_desc != null ? $item->short_desc : "--" ).'</span><input type="text" name="short_desc" value="'.$item->short_desc.'"  class="short_desc form-control input-height d-none" id="input_description" style="width:100%">';
                return $html;
                break;

            case 'discount':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                    $class = "";
                }
                else
                {
                    $class = "inputDoubleClick";
                }
                $html = '<span class="'.$class.'" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none" id="input_discount" style="width:100%">';
                return $html.' %';
                break;

            case 'hs_code':
                if($item->product_id!=null)
                return $item->product->hs_code;
                else
                return 'N.A';
                break;

            case 'refrence_code':
                if($item->product == null )
                {
                  return "N.A";
                }
                else
                {
                  $item->product->refrence_code ? $reference_code = $item->product->refrence_code : $reference_code = "N.A";
                  return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"  ><b>'.$reference_code.'<b></a>';
                }
                break;

            case 'action':
                $html_string = '';
                if(Auth::user()->role_id == 2)
                {
                  $disable = "disabled";
                }
                else
                {
                  $disable = "";
                }
                if($item->product_id == null && $item->is_billed != "Inquiry" && Auth::user()->role_id != 7)
                {
                  if(@$item->get_order->primary_status !== 2 && @$item->get_order->primary_status !== 3){

                    // dd($item);
                  $html_string = '
                      <a href="javascript:void(0);" class="actionicon viewIcon add-as-product" data-id="' . $item->id . '" title="Add as New Product "><i class="fa fa-envelope"></i></a>';
                }
                }
                if($item->status < 8 && Auth::user()->role_id != 7)
                {

                  $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                else if(@$item->get_order->primary_status == 25)
                {
                  $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                else if(@$item->get_order->primary_status == 28)
                {
                  $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                else
                {
                  $html_string .= '--';
                }
                return $html_string;
                break;
        }
    }

}
