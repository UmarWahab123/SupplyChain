<?php

namespace App\Models\Common\Order;

use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Common\Order\Order;
use App\Models\Common\Product;
use App\Models\Common\Unit;
use App\Models\Common\ProductType;
use App\Models\Common\CustomerTypeProductMargin;
use App\RoleMenu;
use Carbon\Carbon;

class DraftQuotationProduct extends Model
{
	protected $fillable = ['draft_quotation_id','product_id','name','short_desc','number_of_pieces','quantity','exp_unit_cost','margin','unit_price','total_price','total_price_with_vat','from_warehouse_id','warehouse_id','is_mkt','is_billed','created_by','vat','brand','type_id','last_updated_price_on','actual_unit_cost','import_vat_amount'];

    public function get_draft_quotation(){
    	return $this->belongsTo('App\Models\Common\Order\DraftQuotation', 'draft_quotation_id', 'id');
    }

    public function product(){
    	return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function from_warehouse(){
    	return $this->belongsTo('App\Models\Common\Warehouse','from_warehouse_id','id');
    }

    public function from_supplier(){
        return $this->belongsTo('App\Models\Common\Supplier','supplier_id','id');
    }

     public function unit(){
        return $this->belongsTo('App\Models\Common\Unit','selling_unit','id');
    }

    public function productType(){
        return $this->belongsTo('App\Models\Common\ProductType', 'type_id', 'id');
    }

    public function get_order_product_notes(){
        return $this->hasMany('App\Models\Common\Order\DraftQuotationProductNote', 'draft_quotation_product_id', 'id');
    }

    public function single_note(){
        return $this->hasOne('App\Models\Common\Order\DraftQuotationProductNote', 'draft_quotation_product_id', 'id')->orderBy('id','desc');
    }

    public function getColumns($item)
    {
      $product_type = ProductType::select('id','title')->get();
      $units = Unit::orderBy('title')->get();
      $purchasing_role_menu=RoleMenu::where('role_id',2)->where('menu_id',40)->first();

      $data = array();
      //action
      $html_string = '';
        if($item->product == null && $item->is_billed != "Inquiry")
        {
          $html_string = '
              <a href="javascript:void(0);" class="actionicon viewIcon add-as-product" data-id="' . $item->id . '" title="Add as New Inquiry Product "><i class="fa fa-envelope"></i></a>';
           $html_string .= '<button type="button" class="actionicon d-none inquiry_modal" data-toggle="modal" data-target="#inquiryModal">
                          Add as Inquiry Product
                        </button>';
        }
        if($item->is_billed == "Inquiry")
        {
          $html_string = '
              <a href="javascript:void(0);" class="actionicon viewIcon" title="This inquiry item will be visible once the quotation is saved"><i class="fa fa-info"></i></a>';
        }
        $html_string .= '
         <a href="javascript:void(0);" class="actionicon deleteIcon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
      $data['action'] = $html_string;

      //refrence code
      if($item->product == null )
      {
        $refrence_code = "N.A";
      }
      else
      {
        $reference_code = $item->product != null ? $item->product->refrence_code : 'N.A';
        $refrence_code = '<a target="_blank" href="'.url('get-product-detail/'.@$item->product->id).'"  ><b>'.$reference_code.'<b></a>';
      }
      $data['refrence_code'] = $refrence_code;

      //hs_code
      if($item->product_id!=null)
      $hs_code =  $item->product->hs_code;
      else
      $hs_code = 'N.A';
      $data['hs_code'] = $hs_code;

      //description
      $html = '<span class="inputDoubleClick" data-fieldvalue="'.$item->short_desc.'">'.($item->short_desc != null ? $item->short_desc : "--" ).'</span><input type="text" name="short_desc" value="'.$item->short_desc.'"  class="short_desc form-control input-height d-none" style="width:100%">';
      $data['description'] = $html;

      //notes
      if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
      {
        $html_string = "--";
      }
      else
      {
        $notes = $item->get_order_product_notes->count();
        $note = $item->single_note;

        $html_string = '<div class="d-flex justify-content-center text-center">';
        if($notes > 0){
        $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
        if($note->show_on_invoice == 1)
        {
          $html_string .=  '<input class="ml-2" type="checkbox" data-id="'.@$note->id.'" data-compl_quot_id = "'.$item->id.'" name="show_note_checkbox" id="show_note_checkbox" style="vertical-align: middle;" checked /></div>';
                              }else{
          $html_string .=  '<input class="ml-2" type="checkbox" data-id="'.$note->id.'" data-compl_quot_id = "'.$item->id.'" name="show_note_checkbox" id="show_note_checkbox" style="vertical-align: middle;" /></div>';
        }
        }
        if ($notes == 0) {
            if (@$item->status != 18 && $item->status != 24) {
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
                  </div>';
            } else {
                $html_string .= '--';
            }
        }
      }
      $data['notes'] = $html_string;

      //category_id
      if($item->product_id!=null)
      $html_string = $item->product->productSubCategory->title;
      else
      $html_string = 'N.A';
      $data['category_id'] = $html_string;

      //type_id
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
        $data['type_id'] = $html_string;

        //brand
        $html = '<span class="inputDoubleClick" data-fieldvalue="'.$item->brand.'">'.($item->brand != null ? $item->brand : "--" ).'</span><input type="text" name="brand" value="'.$item->brand.'" min="0" class="brand form-control input-height d-none" style="width:100%">';
        $data['brand'] = $html;

        //temperature
        if($item->product_id !== NULL)
        $temp = $item->unit ? $item->product->product_temprature_c : "N.A";
        else
        $temp = '--';
        $data['temperature'] = $temp;

        //supply from
        if($item->product_id == null)
          {
            $html = "N.A";
          }
          else
          {
            if($item->is_warehouse == 0 && $item->supplier_id == null)
            {
              $label = 'Select Supply From';
            }
            else
            {
              $label = $item->is_warehouse == 1 ? 'Warehouse' : @$item->from_supplier->company;
            }

            $html =  '<span class="inputDoubleClick supply_from_'.$item->id.'" data-fieldvalue="'.@$label. '">'.@$label.'</span>';
            $html .= '<select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" name="from_warehouse_id" >';
            $html .= '<option value="" selected disabled>Choose Supply From</option>';
            $html .= '<optgroup label="Select Warehouse">';
              if($item->is_warehouse == 1)
              {
                $html = $html.'<option selected value="w-1">Warehouse</option>';
              }
              else
              {
                $html = $html.'<option value="w-1">Warehouse</option>';
              }
            $html = $html.'</optgroup>';

              if($purchasing_role_menu!=null )
              {
                $html .= '<optgroup label="Suppliers">';
                $getSuppliersByCat = $item->product != null ? $item->product->supplier_products : null;
                if($getSuppliersByCat != null)
                {
                      foreach ($getSuppliersByCat as $getSupplier)
                      {
                        $value = $item->supplier_id == @$getSupplier->supplier->id ? 'selected' : "";
                        $html .= '<option '.$value.' value="s-'.@$getSupplier->supplier->id.'">'.$getSupplier->supplier->reference_name.'</option>';
                      }
                }
                $html .= ' </optgroup>';
              }
            $html.='</select>';
          }
        $data['supply_from'] = $html;

        //available qty
        $warehouse_id = $item->from_warehouse_id != null ? $item->from_warehouse_id : Auth::user()->warehouse_id;
          $stock = $item->product != null ? ($item->product->warehouse_products != null ? $item->product->warehouse_products->where('warehouse_id',$warehouse_id)->first() : null) : null;
          if($stock != null)
          {
            $stock = number_format($stock->available_quantity,3,'.','');
          }
        $data['available_qty'] = $stock;

        //po quantity
        $po_quantity = '--';
        $data['po_quantity'] = $po_quantity;

        //po_number
        $po_number = '--';
        $data['po_number'] = $po_number;

        //last_price
        $order = Order::with('order_products')->whereHas('order_products',function($q) use($item) {
                $q->where('is_billed','Product');
                $q->where('product_id',$item->product_id);
              })->where('customer_id',$item->get_draft_quotation->customer_id)->where('primary_status',3)->orderBy('converted_to_invoice_on','desc')->first();

          if($order)
          {
            $cust_last_price = number_format($order->order_products->where('product_id',$item->product_id)->first()->unit_price, 2, '.', ',');
          }
          else
          {
            $cust_last_price = "N.A";
          }
        $data['last_price'] = $cust_last_price;

        //quantity
        if($item->product_id !== null){
                $sale_unit = $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
              }
              else
              {
                $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
                $html =  '<span class="inputDoubleClick">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
                // $units = Unit::orderBy('title')->get();
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
          if($item->quantity == null)
          {
            $style = "color:red;";
          }
          else
          {
            $style = "";
          }
          if($item->is_retail == "qty")
          {
            $radio = "disabled";
          }
          else
          {
            $radio = "";
          }
          $html = '<span class="inputDoubleClick quantity_span_'.$item->id.'" id="draft_quotation_qty_span_'.$item->product_id.'" data-fieldvalue="'.$item->quantity.'" style="'.$style.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span><input type="number" name="quantity" value="'.$item->quantity.'" id="draft_quotation_qty_'.$item->product_id.'" class="quantity form-control input-height d-none" style="width:100%">';
          $html .= ' '.@$sale_unit;
          if($item->is_billed == 'Product')
          {
            $html .= '
            <div class="custom-control custom-radio custom-control-inline pull-right">';
            $html .= '<input type="checkbox" class="condition custom-control-input qty_'.$item->id.'" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' '.$radio.'>';

            $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
          }
        $data['quantity'] = $html;

        //quantity shipped
        $data['quantity_ship'] = '--';

        //number of pieces
        if($item->is_retail == "pieces")
          {
            $radio = "disabled";
          }
          else
          {
            $radio = "";
          }
           if($item->is_billed == 'Product')
           {
            $html = '<span class="inputDoubleClick pcs_span_'.$item->id.'" data-fieldvalue="'.$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span><input type="number" name="number_of_pieces" id="draft_quotation_pieces_'.$item->id.'" value="'.$item->number_of_pieces.'" class="number_of_pieces form-control input-height d-none" style="width:100%">';

            $html .= '
            <div class="custom-control custom-radio custom-control-inline pull-right">';
            $html .= '<input type="checkbox" class="condition custom-control-input pieces_'.$item->id.'" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' '.$radio.'>';

            $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
          }
          else
          {
            $html = 'N.A';
          }
        $data['number_of_pieces'] = $html;

        //pcs shipped
        $data['pcs_shipped'] = '--';

        //exp unit cost
        if($item->product == null)
        {
          $html_string = "N.A";
        }
        else
        {
          $checkItemPo = new Product;
          $checkItemPo = $checkItemPo->checkItemImportExistance($item->product_id);
          if($checkItemPo == 0)
          {
            $redHighlighted = 'style=color:red';
            $tooltip = "This item has never been imported before in our system, so the suggested price may be incorrect";
          }
          else
          {
            $redHighlighted = '';
            $tooltip = '';
          }

          $html_string ='<span title="'.$tooltip.'" class="unit-price-'.$item->id.'" '.$redHighlighted.'>'.number_format(floor($item->exp_unit_cost*100)/100,2).'</span>';
        }
        $data['exp_unit_cost'] = $html_string;

        //margin
        if($item->product == null){
            $margin = "N.A";
        }
        else{
           if( is_numeric($item->margin)){
               $margin = $item->margin.'%';
            }
            else{
                $margin = $item->margin;
            }
        }
        $data['margin'] = $margin;

        //unit_price
        $star = '';
        if($item->product == null)
        {
          $html = '<span class="inputDoubleClick unit_price_'.$item->id.'" data-fieldvalue="'.number_format(@$item->unit_price,2,'.','').'">'.($item->unit_price !== null ? number_format(@$item->unit_price, 2,'.','') : "--").'</span><input type="number" name="unit_price" step="0.01" value="'. number_format(@$item->unit_price, 2,'.','').'" class="unit_price form-control input-height d-none unit_price_field_'.$item->id.'" style="width:100%">';
        }
        else
        {
          if(is_numeric($item->margin))
          {
            // $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_draft_quotation->customer->category_id)->where('is_mkt',1)->first();
            $product_margin = $item->product->customer_type_product_margins->where('product_id',@$item->product->id)->where('customer_type_id',$item->get_draft_quotation->customer->category_id)->where('is_mkt',1)->first();
            if($product_margin)
            {
              $star = '*';
            }
          }

        $html = '<span class="inputDoubleClick unit_price_'.$item->id.'" data-fieldvalue="'.number_format(@$item->unit_price, 2,'.','').'">'.$star.number_format(@$item->unit_price, 2,'.','').'</span><input type="number" name="unit_price" step="0.01" value="'. number_format(@$item->unit_price, 2,'.','').'" class="unit_price form-control input-height d-none unit_price_field_'.$item->id.'" style="width:100%">';
        }

        $data['unit_price'] = $html;

        //last update price on
        if($item->last_updated_price_on != null)
        {
          $last_updated_price_on = Carbon::parse($item->last_updated_price_on)->format('d/m/Y');
        }
        else
        {
          $last_updated_price_on = '--';
        }
        $data['last_updated_price_on'] = $last_updated_price_on;

        //discount
        $html = '<span class="inputDoubleClick" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none" style="width:100%">';
        $data['discount'] = $html.' %';

        //unit price discount
        $unit_price_discount = $item->unit_price_with_discount != null ? '<span class="unit_price_after_discount_'.$item->id.'">'.number_format($item->unit_price_with_discount, 2, '.', ',').'</span>' : '<span class="unit_price_after_discount_'.$item->id.'">--</span>';
        $data['unit_price_discount'] = $unit_price_discount;

        //vat
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $vat = $item->vat != null ? $item->vat : '--';
        }
        else
        {
          if($item->unit_price != null && $item->get_draft_quotation->is_vat == 0)
          {
              $clickable = "inputDoubleClick";
          }
          else
          {
              $clickable = "inputDoubleClick";
          }
          $vat = '<span class="'.$clickable.'" data-fieldvalue="'.$item->vat.'">'.($item->vat != null ? $item->vat : '--').'</span><input type="number" name="vat" value="'.@$item->vat.'" class="vat form-control input-height d-none" style="width:90%"> %';
        }
        $data['vat'] = $vat;

        //unit_price_with_vat
        $unit_price = round($item->unit_price,2);
          $vat = $item->vat;
          $vat_amount = @$unit_price * ( @$vat / 100 );
        if($item->unit_price_with_vat !== null)
        {
          // $unit_price_with_vat = bcdiv(@$item->unit_price_with_vat,1,2);
          $unit_price_with_vat = preg_replace('/(\.\d\d).*/', '$1',@$item->unit_price_with_vat);
        }
        else
        {
          $unit_price_with_vat = number_format(@$unit_price+@$vat_amount,2,'.',',');
        }

         $html = '<span class="inputDoubleClick unit_price_w_vat_'.$item->id.'" data-fieldvalue="'.number_format(floor(@$item->unit_price_with_vat*10000)/10000, 4,'.','').'">'.number_format(floor(@$item->unit_price_with_vat*100)/100, 2).'</span><input type="number" name="unit_price_with_vat" step="0.01"  value="'.number_format(floor(@$item->unit_price_with_vat*10000)/10000, 4,'.','').'" class="unit_price_with_vat form-control input-height d-none unit_price_w_vat_field'.$item->id.'" style="width:100%;  border-radius:0px;">';
        $data['unit_price_with_vat'] = $html;

        //total amount
        $total_price = $item->total_price_with_vat;
        $html_string ='<span class="total-price total-price-'.$item->id.' total_amount_w_vat_'.$item->id.'">'.number_format(floor(@$total_price*100)/100, 2).'</span>';
        $data['total_amount'] = $html_string;

        //restaurant_price
        $getRecord = new Product;
          $prodFixPrice   = $getRecord->getDataOfProductMargins($item->product_id, 1, "prodFixPrice");
          if($prodFixPrice!="N.A")
          {
            $formated_value = number_format($prodFixPrice->fixed_price,3,'.',',');
            $restaurant_price = (@$formated_value !== null) ? $formated_value : '--';
          }
          else
          {
            $restaurant_price = 'N.A';
          }
        $data['restaurant_price'] = $restaurant_price;

        //size
        if($item->product_id != null)
        {
          if($item->product->product_notes!=null)
            $size = $item->product->product_notes;
          else
            $size = '--';
        }
        else
        {
          $size = '--';
        }
        $data['size'] = $size;

        //total price
        if($item->total_price == null)
        {
          $total_price = "N.A"; 
        }
        else{
          $total_price = $item->total_price;
        }
        $html_string ='<span class="total-price total-price-'.$item->id.' total_amount_wo_vat_'.$item->id.'">'.number_format((float)$total_price, 2, '.', ',').'</span>';
        $data['total_price'] = $html_string;

        $data['id'] = $item->id;

      return $data;
    }


}
