<?php

namespace App\Models\Common\Order;

use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Common\Order\Order;
use App\Models\Common\Product;
use App\Models\Common\Unit;
use App\Models\Common\SupplierProducts;
use App\Models\Common\ProductType;
use App\Models\Common\CustomerTypeProductMargin;
use Auth;
use App\RoleMenu;
use Carbon\Carbon;
use App\Models\Common\ProductCategory;
use Illuminate\Support\Facades\DB;

class OrderProduct extends Model
{

    protected $fillable = ['order_id', 'product_id','supplier_id','from_warehouse_id','number_of_pieces','quantity','exp_unit_cost','margin','unit_price','total_price','total_price_with_vat','actual_cost','warehouse_id','status','is_mkt','short_desc','name','is_billed','created_by','category_id','vat','brand','selling_unit','discount','is_retail','qty_shipped','unit_price_with_vat','vat_amount_total','type_id','last_updated_price_on','locked_actual_cost','is_cogs_updated','manual_cogs_shipment','default_supplier','user_warehouse_id','is_warehouse','import_vat_amount','unit_price_with_discount','remarks', 'return_to_stock', 'pcs_shipped'];

    public function get_order(){
    	return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    }

    public function get_order_specific(){
        return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id')->whereIn('primary_status',[2,3]);
    }

    public function get_order_product_notes(){
        return $this->hasMany('App\Models\Common\Order\OrderProductNote', 'order_product_id', 'id');
    }

    public function single_note(){
        return $this->hasOne('App\Models\Common\Order\OrderProductNote', 'order_product_id', 'id')->orderBy('id','desc');
    }

    public function product(){
    	return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function from_warehouse(){
    	return $this->belongsTo('App\Models\Common\Warehouse','from_warehouse_id','id');
    }

    public function user_warehouse(){
        return $this->belongsTo('App\Models\Common\Warehouse','user_warehouse_id','id');
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Common\Warehouse','warehouse_id','id');
    }

    public function from_supplier(){
        return $this->belongsTo('App\Models\Common\Supplier','supplier_id','id');
    }

    public function added_by(){
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function productSubCategory(){
        return $this->belongsTo('App\Models\Common\ProductCategory', 'category_id', 'id');
    }
    public function unit(){
        return $this->belongsTo('App\Models\Common\Unit','selling_unit','id');
    }

    public function purchase_order_detail(){
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderDetail','id','order_product_id');
    }

    public function productType(){
        return $this->belongsTo('App\Models\Common\ProductType', 'type_id', 'id');
    }

    public function default_supplier_rel(){
        return $this->belongsTo('App\Models\Common\Supplier','default_supplier','id');
    }
    public function warehouse_products(){
        return $this->hasMany('App\Models\Common\WarehouseProduct', 'product_id', 'product_id');
    }

    public function warehouse_products_existing(){
        return $this->hasMany('App\Models\Common\WarehouseProduct', 'product_id', 'product_id');
    }

    public function po_group_product_detail(){
        return $this->hasMany('App\Models\Common\PoGroupProductDetail', 'product_id', 'product_id')->whereHas('po_group',function($t){
          $t->where('is_review',1);
        });
    }

    public function addProductToOrder(Request $request)
    {
        //To add new product
    $order = Order::find($request->id['id']);
      $refrence_number = $request->refrence_number;
      $product = Product::where('refrence_code',$refrence_number)->where('status',1)->first();
      if($product)
      {
        $vat_amount_import = NULL;
        $getSpData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();
        if($getSpData)
        {
          $vat_amount_import = $getSpData->vat_actual;
        }
        $order = Order::find($request->id['id']);
        $price_calculate_return = $product->price_calculate($product,$order);
        $unit_price = $price_calculate_return[0];
        $price_type = $price_calculate_return[1];
        $price_date = $price_calculate_return[2];
        $user_warehouse = @$order->customer->primary_sale_person->get_warehouse->id;
        $total_product_status = 0;
        $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$product->id)->where('customer_type_id',$order->customer->category_id)->first();
        if($CustomerTypeProductMargin != null )
        {
          $margin = $CustomerTypeProductMargin->default_value;
          $margin = (($margin/100)*$product->selling_price);
          $product_ref_price  = $margin+($product->selling_price);
          $exp_unit_cost = $product_ref_price;
        }

        //if this product is already in quotation then increment the quantity
        $order_products = OrderProduct::where('order_id',$order->id)->where('product_id',$product->id)->first();
        if($order_products)
        {
          $total_price_with_vat = (($product->vat/100)*$unit_price)+$unit_price;
          $supplier_id = $product->supplier_id;
          $salesWarehouse_id = Auth::user()->get_warehouse->id;

          $new_draft_quotation_products   = new OrderProduct;
          $new_draft_quotation_products->order_id                 = $order->id;
          $new_draft_quotation_products->product_id               = $product->id;
          $new_draft_quotation_products->category_id              = $product->category_id;
          $new_draft_quotation_products->hs_code                  = $product->hs_code;
          $new_draft_quotation_products->product_temprature_c     = $product->product_temprature_c;
          // $new_draft_quotation_products->supplier_id         = $supplier_id;
          $new_draft_quotation_products->short_desc               = $product->short_desc;
          $new_draft_quotation_products->type_id                  = $product->type_id;
          $new_draft_quotation_products->brand                    = $product->brand;
          $new_draft_quotation_products->exp_unit_cost            = $exp_unit_cost;
          $new_draft_quotation_products->margin                   = $price_type;
          $new_draft_quotation_products->last_updated_price_on    = $price_date;
          $new_draft_quotation_products->unit_price               = number_format($unit_price,2,'.','');
          $new_draft_quotation_products->unit_price_with_discount = number_format($unit_price,2,'.','');
          $new_draft_quotation_products->import_vat_amount        = $vat_amount_import;
          if($order->is_vat == 0)
          {
            $new_draft_quotation_products->vat               = $product->vat;
            if(@$product->vat !== null)
            {
              $unit_p = number_format($unit_price,2,'.','');
              $vat_amount = $unit_p * (@$product->vat/100);
              $final_price_with_vat = $unit_p + $vat_amount;

              $new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat,2,'.','');
            }
            else
            {
              $new_draft_quotation_products->unit_price_with_vat = number_format($unit_price,2,'.','');
            }
          }
          else
          {
            $new_draft_quotation_products->vat                  = 0;
            $new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price,2,'.','');
          }

          $new_draft_quotation_products->actual_cost         = $product->selling_price;
          $new_draft_quotation_products->locked_actual_cost  = $product->selling_price;
          $new_draft_quotation_products->warehouse_id        = $salesWarehouse_id;
          if(@$product->min_stock > 0)
          {
            $new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
            $new_draft_quotation_products->is_warehouse = 1;
          }
          $new_draft_quotation_products->user_warehouse_id = $order->from_warehouse_id;
          // dd($new_draft_quotation_products);
          if($order->primary_status == 1)
          {
            $new_draft_quotation_products->status              = 6;
          }
          elseif($order->primary_status == 2)
          {
            if($new_draft_quotation_products->user_warehouse_id == $new_draft_quotation_products->from_warehouse_id)
            {
              // dd('here');
              $new_draft_quotation_products->status = 10;
            }
            else
            {
              $total_product_status = 1;
              $new_draft_quotation_products->status = 7;
            }
          }
          else if($order->status == 11)
          {
            $new_draft_quotation_products->status   = 11;
          }
          elseif($order->primary_status == 25)
          {
            $new_draft_quotation_products->status   = 26;
          }
          elseif($order->primary_status == 28)
          {
            $new_draft_quotation_products->status   = 29;
          }

          $new_draft_quotation_products->save();
        }
        else
        {
          $total_price_with_vat = (($product->vat/100)*$unit_price)+$unit_price;
          $supplier_id = $product->supplier_id;
          $salesWarehouse_id = Auth::user()->get_warehouse->id;

          $new_draft_quotation_products   = new OrderProduct;
          $new_draft_quotation_products->order_id                   = $order->id;
          $new_draft_quotation_products->product_id                 = $product->id;
          $new_draft_quotation_products->category_id                = $product->category_id;
          $new_draft_quotation_products->hs_code                    = $product->hs_code;
          $new_draft_quotation_products->product_temprature_c       = $product->product_temprature_c;
          // $new_draft_quotation_products->supplier_id         = $supplier_id;
          $new_draft_quotation_products->short_desc                 = $product->short_desc;
          $new_draft_quotation_products->type_id                    = $product->type_id;
          $new_draft_quotation_products->brand                      = $product->brand;
          $new_draft_quotation_products->exp_unit_cost              = $exp_unit_cost;
          $new_draft_quotation_products->margin                     = $price_type;
          $new_draft_quotation_products->last_updated_price_on      = $price_date;
          $new_draft_quotation_products->unit_price                 = number_format($unit_price,2,'.','');
          $new_draft_quotation_products->unit_price_with_discount   = number_format($unit_price,2,'.','');
          $new_draft_quotation_products->import_vat_amount          = $vat_amount_import;
          if($order->is_vat == 0)
          {
            $new_draft_quotation_products->vat               = $product->vat;
            if(@$product->vat !== null)
            {
              $unit_p = number_format($unit_price,2,'.','');
              $vat_amount = $unit_p * (@$product->vat/100);
              $final_price_with_vat = $unit_p + $vat_amount;

              $new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat,2,'.','');
            }
            else
            {
              $new_draft_quotation_products->unit_price_with_vat = number_format($unit_price,2,'.','');
            }
          }
          else
          {
            $new_draft_quotation_products->vat                  = 0;
            $new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price,2,'.','');
          }

          $new_draft_quotation_products->actual_cost         = $product->selling_price;
          $new_draft_quotation_products->locked_actual_cost  = $product->selling_price;
          $new_draft_quotation_products->warehouse_id        = $salesWarehouse_id;
          if($product->min_stock > 0)
          {
            $new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
            $new_draft_quotation_products->is_warehouse = 1;
          }
          $new_draft_quotation_products->user_warehouse_id = $order->from_warehouse_id;
          if($order->primary_status == 1)
          {
            $new_draft_quotation_products->status  = 6;
          }
          elseif($order->primary_status == 2)
          {
            if($user_warehouse == $new_draft_quotation_products->from_warehouse_id)
            {
              $new_draft_quotation_products->status = 10;
            }
            else
            {
              $total_product_status = 1;
              $new_draft_quotation_products->status = 7;
            }
          }
          else if($order->status == 11)
          {
            $new_draft_quotation_products->status              = 11;
          }
          elseif($order->primary_status == 25)
          {
            $new_draft_quotation_products->status              = 26;
          }
          elseif($order->primary_status == 28)
          {
            $new_draft_quotation_products->status              = 29;
          }

          $new_draft_quotation_products->save();
        }
          $new_draft_quotation_products->save();

        if(@$total_product_status == 1)
        {
          $order->status = 7;
        }
        else
        {
          $order_status = $order->order_products->where('is_billed','=','Product')->min('status');
          $order->status = $order_status;
        }
        $order->save();

        $sub_total     = 0 ;
        $total_vat     = 0 ;
        $grand_total   = 0 ;
        $query         = OrderProduct::where('order_id',$order->id)->get();
        foreach ($query as  $value) {
          if($value->is_retail == 'qty')
          {
            $sub_total += $value->total_price;
          }
          else if($value->is_retail == 'pieces')
          {
            $sub_total += $value->total_price;
          }
          $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);
        }
        $grand_total = ($sub_total)-($order->discount)+($order->shipping)+($total_vat);
        return $order->order_products;
        return response()->json(['success' => true,'status'=>@$order->statuses->title,'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'successmsg' => 'Product successfully Added', 'total_products' => $order->order_products->count('id')]);
      }
      else
      {
        return response()->json(['success' => false, 'successmsg' => 'Product Not Found in Catalog']);
      }
    }
    public function po_detail_product()
    {
        return $this->hasMany(PurchaseOrderDetail::class,'product_id','id');
    }

    public function order_product_note()
    {
        return $this->hasOne('App\Models\Common\Order\OrderProductNote','order_product_id','id');
    }

    public function getColumns($item)
    {
      $product_type = ProductType::select('id','title')->get();
      $units = Unit::orderBy('title')->get();
      $purchasing_role_menu=RoleMenu::where('role_id',2)->where('menu_id',40)->first();

      $data = array();
      //action
      $action = '';
      if(Auth::user()->role_id == 2)
      {
        $disable = "";
      }
      else
      {
        $disable = "";
      }
      if($item->product_id == null && $item->is_billed != "Inquiry" && Auth::user()->role_id != 7)
      {
        if(@$item->get_order->primary_status !== 2 && @$item->get_order->primary_status !== 3)
        {
          $action = '
              <a href="javascript:void(0);" class="actionicon viewIcon add-as-product" data-id="' . $item->id . '" title="Add as New Product "><i class="fa fa-envelope"></i></a>';
          $action .= '<button type="button" class="actionicon d-none inquiry_modal" data-toggle="modal" data-target="#inquiryModal">
                        Add as Inquiry Product
                      </button>';
        }
      }
      if($item->status < 8 && Auth::user()->role_id != 7)
      {

        $action .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
      }
      else if(@$item->get_order->primary_status == 25)
      {
        $action .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
      }
      else if(@$item->get_order->primary_status == 28)
      {
        $action .= '<a href="javascript:void(0);" class="actionicon deleteIcon removeProduct '.$disable.'" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
      }
      else
      {
        $action .= '--';
      }
      $data['action'] = $action;

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
      if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
      {
        $class = "";
      }
      else if($item->get_order->status == 24)
      {
        $class = "";
      }
      else
      {
        $class = "inputDoubleClick";
      }
      if($item->short_desc == null)
      {
          $style = "color:red;";
      }
      else
      {
          $style = "";
      }
      $html = '<span class="'.$class.'" data-fieldvalue="'.$item->short_desc.'" style="'.@$style.'">'.($item->short_desc != null ? $item->short_desc : "--" ).'</span><input type="text" name="short_desc" value="'.$item->short_desc.'"  class="short_desc form-control input-height d-none" id="input_description" style="width:100%">';
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
      $html_string=null;
      if($item->is_billed == 'Product')
      {
      if($item->category_id != null)
      {
        $html_string = '<span class="m-l-15 " id="category_id" data-fieldvalue="'.@$item->category_id.'" data-id="cat '.@$item->category_id.' '.@$item->id.'"> ';
        $html_string .= ($item->category_id != null) ? $item->product->productSubCategory->get_Parent->title.' / '.$item->product->productSubCategory->title: "--";
      }
      else
      {
        $html_string = '<span class="m-l-15 " id="category_id" data-fieldvalue="'.@$item->category_id.'" data-id="cat '.@$item->category_id.' '.@$item->id.'"> ';
        $html_string .= ($item->product_id!= null) ? $item->product->productSubCategory->get_Parent->title.' / '.$item->product->productSubCategory->title: "--";
      }
      $html_string .= '</span>';

      $html_string .= '<div class="incomplete-filter d-none inc-fil-cat">
      <select class="font-weight-bold form-control-lg form-control js-states state-tags select-common category_id categories_select'.@$item->id.'" name="category_id" required="true">';
      $html_string .= '</select></div>';
      }
      else
      {
        $html_string = '--';
      }
      $data['category_id'] = $html_string;

      //type_id
      $html_string = null;
      if($item->get_order->status == 24)
      {
        $class = "";
      }
      else
      {
        $class = 'inputDoubleClick';
      }
        if($item->type_id == null)
        {
          $html_string = '
          <span class="m-l-15 '.$class.'" id="product_type" data-fieldvalue="'.$item->type_id.'">';
          $html_string .= 'Select';
          $html_string .= '</span>';

          $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
          <option value="" selected="" disabled="">Choose Type</option>';
          foreach ($product_type as $type) {
            $html_string .='<option value="'.@$type->id.'" >'.$type->title.'</option>';
          }
          $html_string .= '</select>';

        }
        else
        {
          $html_string = '
          <span class="m-l-15 '.$class.'" id="product_type"  data-fieldvalue="'.$item->type_id.'">';
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
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
        }
        else
        {
          $class = "inputDoubleClick";
        }
          $html = '<span class="'.$class.'" data-fieldvalue="'.$item->brand.'">'.($item->brand != null ? $item->brand : "--" ).'</span><input type="text" name="brand" value="'.$item->brand.'" min="0" class="brand form-control input-height d-none" id="input_brand" style="width:100%">';
        $data['brand'] = $html;

        //temperature
        if($item->product_id !== NULL)
        $temp = $item->unit ? $item->product->product_temprature_c : "N.A";
        else
        $temp = '--';
        $data['temperature'] = $temp;

        //supply from
        if(Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
        }
        else
        {
          $class = "inputDoubleClick";
        }

        if($item->product_id == null)
        {
          $html = "N.A";
        }
        else
        {
          $label = $item->is_warehouse == 1 ? 'Warehouse' : (@$item->from_supplier->reference_name != null ? @$item->from_supplier->reference_name : "--");
          $html =  '<span class="'.$class.' supply_from_'.$item->id.'">'.@$label.'</span>';
          $html .= '<select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" id="select_supply_from" name="from_warehouse_id" >';
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
          if($purchasing_role_menu!=null)
          {
            $html .= '<optgroup label="Suppliers">';
                $getSuppliers = $item->product->supplier_products;
                  foreach ($getSuppliers as $sup)
                  {
                    $getSupplier = $sup->supplier;
                    $value = $item->supplier_id == @$getSupplier->id ? 'selected' : "";
                    $html .= '<option '.$value.' value="s-'.@$getSupplier->id.'">'.@$getSupplier->reference_name.'</option>';
                  }
            $html .= ' </optgroup>';
          }
          $html .= ' </select>';
        }
        $data['supply_from'] = $html;

        //available qty
        $warehouse_id = $item->user_warehouse_id != null ? $item->user_warehouse_id : ($item->from_warehouse_id != null ? $item->from_warehouse_id :Auth::user()->warehouse_id);
        $stock = $item->product != null ? number_format($item->product->warehouse_products->where('warehouse_id',$warehouse_id)->first()->available_quantity,3,'.',',') : 'N.A';
        $data['available_qty'] = $stock;

        //po quantity
        if($item->status > 7){
          $po_quantity = @$item->purchase_order_detail != null ?  $item->purchase_order_detail->quantity.' '.$item->product->units->title : '--';
        }else{
          $po_quantity = '--';
        }
        $data['po_quantity'] = $po_quantity;

        //po_number
        if($item->status > 7){
          $po_number = @$item->purchase_order_detail != null ?  $item->purchase_order_detail->PurchaseOrder->ref_id: '--';
        }else{
          $po_number = '--';
        }
        $data['po_number'] = $po_number;

        //last_price
        $order = Order::with('order_products')->whereHas('order_products',function($q) use($item) {
        $q->where('is_billed','Product');
        $q->where('product_id',$item->product_id);
        })->where('customer_id',$item->get_order->customer_id)->where('primary_status',3)->orderBy('converted_to_invoice_on','desc')->first();
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
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
          $radio = "disabled";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
          $radio = "disabled";
        }
        else
        {
          $class = "inputDoubleClick";
          $radio = "";
        }

        if($item->is_retail == "qty")
        {
          $radio = "disabled";
        }
        else
        {
          $radio = "";
        }
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
          // }
          $sale_unit = $html;
        }
        $html = '';
        // sale unit code ends
        if(@$item->get_order->primary_status == 3){
          $html .= '<span class="" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span> ';
        }else{
          $html .= '<span class="inputDoubleClick quantity_span_'.$item->id.'" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span>';
          $html .= '<input type="number" name="quantity"  value="'.$item->quantity.'" class="quantity form-control input-height d-none" id="input_quantity_'.$item->id.'" style="width:100%; border-radius:0px;"> ';
        }
        $html .= @$sale_unit;


        if(@$item->get_order->primary_status !== 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){


        $html .= '
        <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
        $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' id="checkbox_quantity">';

        $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
        }
        $data['quantity'] = $html;

        //quantity shipped
        if($item->is_retail == "qty")
        {
          $checked = "disabled";
        }
        else
        {
          $checked = "";
        }
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
          $radio = "disabled";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
          $radio = "disabled";
        }
        else
        {
          $class = "inputDoubleClick";
          $radio = "";
        }

        if($item->is_retail == "qty")
        {
          $radio = "disabled";
        }
        else
        {
          $radio = "";
        }
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
        $data['quantity_ship'] = $html;

        //number of pieces
        $sale_unit = '';
        if(Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
        {
          $class = "";
          $radio = "disabled";
        }
        else if(Auth::user()->role_id == 2)
        {
          $radio = "disabled";
          $class = 'inputDoubleClick';
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
          $radio = "disabled";
        }
        else
        {
          $class = "inputDoubleClick";
          $radio = "";
        }

        if($item->is_retail == "pieces")
        {
          $radio = "disabled";
        }
        else
        {
          $radio = "";
        }

        if($item->get_order->ecommerce_order == 1 && $item->is_retail == 'pieces')
          {
            $sale_unit = $item->product && $item->product->ecomSellingUnits ? $item->product->ecomSellingUnits->title : "N.A";
          }
          else
          {
            $sale_unit = '';
          }
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
          $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' id="checkbox_number_of_pieces">';

          $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
          }else if(Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
            $html .= '
              <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
              $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' id="checkbox_number_of_pieces1">';

              $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
          }
        }
        else
        {
          $html = 'N.A';
        }
        $data['number_of_pieces'] = $html.' '.$sale_unit;

        //pcs shipped
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
        {
          $class = "";
          $radio = "disabled";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
          $radio = "disabled";
        }
        else
        {
          $class = "inputDoubleClick";
          $radio = "";
        }

        if($item->is_retail == "pieces")
        {
          $radio = "disabled";
        }
        else
        {
          $radio = "";
        }
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
        $data['pcs_shipped'] = $html;

        //exp unit cost
        if($item->exp_unit_cost == null)
        {
          $html_string = "N.A";
        }
        else
        {
          $redHighlighted = '';
          $tooltip = '';
          if($item->product_id != null)
          {
            $checkItemPo = $item->po_group_product_detail->count();
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
          }

          $html_string ='<span title="'.$tooltip.'" class="unit-price-'.$item->id.'" '.$redHighlighted.'>'.number_format(floor($item->exp_unit_cost*100)/100, 2).'</span>';
        }
        $data['exp_unit_cost'] = $html_string;

        //margin
        if($item->is_billed != "Product")
        {
          $margin = "N.A";
        }
        else{
          if($item->margin == null)
          {
            $margin = "Fixed Price";
          }
          else
          {
            if(is_numeric($item->margin))
            {
              $margin = $item->margin.'%';
            }
            else
            {
              $margin = $item->margin;
            }
          }
        }
        $data['margin'] = $margin;

        //unit_price
        if(Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
        }
        else
        {
          $class = "inputDoubleClick";
        }
        $star = '';
        if(is_numeric($item->margin)){
          if($item->product)
          {
            $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
            if($product_margin){
                $star = '*';
            }
          }
        }
        $unit_price = number_format($item->unit_price, 2, '.', '');
        $html = '<span class="'.$class.' unit_price_'.$item->id.'" data-fieldvalue="'.number_format(@$item->unit_price, 2,'.','').'">'.$star.number_format(@$item->unit_price, 2).'</span><input type="number" name="unit_price" step="0.01"  value="'.number_format(@$item->unit_price,2,'.','').'" class="unit_price form-control input-height d-none unit_price_field_'.$item->id.'" id="input_unit_price" style="width:100%;  border-radius:0px;">';
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
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
        }
        else if($item->get_order->status == 24)
        {
          $class = "";
        }
        else
        {
          $class = "inputDoubleClick";
        }
        $html = '<span class="'.$class.'" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none" id="input_discount" style="width:100%">';
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
          if($item->unit_price != null && $item->get_order->is_vat == 0)
          {
            if($item->get_order->status == 24)
            {
              $clickable = "";
            }
            else
            {
              $clickable = "inputDoubleClick";
            }

          }
          else
          {
            if($item->get_order->status == 24)
            {
              $clickable = "";
            }else{
              $clickable = "inputDoubleClick";
            }
          }
          $vat = '<span class="'.$clickable.'" data-fieldvalue="'.$item->vat.'">'.($item->vat != null ? $item->vat : '--').'</span><input type="number" name="vat" value="'.@$item->vat.'"  class="vat form-control input-height d-none" id="input_vat" style="width:90%"> %';
        }
        $data['vat'] = $vat;

        //unit_price_with_vat
        if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
        {
          $class = "";
        }
        else if($item->get_order->status == 24)
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

           $html = '<span class="'.$class.' unit_price_w_vat_'.$item->id.'" data-fieldvalue="'.@$unit_price_with_vat.'">'.@$unit_price_with_vat2.'</span><input type="tel" name="unit_price_with_vat" step="0.01"  value="'.$unit_price_with_vat.'" class="unit_price_with_vat form-control input-height d-none unit_price_w_vat_field'.$item->id.'" id="input_unit_price_with_vat" style="width:100%;  border-radius:0px;">';
        $data['unit_price_with_vat'] = $html;

        //total amount
        $total_amount = ($item->total_price_with_vat !== null ? '<span class="total_amount_w_vat_'.$item->id.'">'.number_format(preg_replace('/(\.\d\d).*/', '$1', $item->total_price_with_vat),2,'.','').'</span>' : '<span class="total_amount_w_vat_'.$item->id.'">--</span>');
        $data['total_amount'] = $total_amount;

        //restaurant_price
        $prodFixPrice = $item->product != null ? ($item->product->product_fixed_price->where('customer_type_id', 1)->first()) : 'N.A';
        if($prodFixPrice!="N.A" && $prodFixPrice != null)
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


    public static function doSortPickInstructionDetialPagePrint($column_name, $default_sort, $ordersProducts) {
      $default_sort == '1' ? $default_sort='DESC' : $default_sort='ASC';

      if($column_name == "reference_no")
      {
        $ordersProducts->select('order_products.*')->leftJoin('products','products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort);
      }
      elseif($column_name == "description")
      {
        $ordersProducts->orderBy('short_desc', $default_sort);
      }
      elseif($column_name == "location") {
        $ordersProducts->select('order_products.*')->leftJoin('warehouses','warehouses.id', '=', 'order_products.warehouse_id')->orderBy('warehouses.location_code', $default_sort);
      }
      elseif($column_name == "unit_price") {
        $ordersProducts->orderBy(\DB::Raw('unit_price+0'), $default_sort);
      }
      elseif($column_name == "quantity") {
        $ordersProducts->orderBy(\DB::Raw('quantity+0'), $default_sort);

      }
      elseif($column_name == "pieces") {
        $ordersProducts->orderBy('number_of_pieces', $default_sort);
      }
      elseif($column_name == "current_quantity") {
        $warehouse_id = Auth::user()->warehouse_id;
        $ordersProducts->select('order_products.*')->leftJoin('warehouse_products','warehouse_products.product_id', '=', 'order_products.product_id')->where('warehouse_products.warehouse_id','=',$warehouse_id)->orderBy(\DB::Raw('warehouse_products.current_quantity+0'), $default_sort);

      }
      elseif($column_name == "reserved_quantity") {
        $warehouse_id = Auth::user()->warehouse_id;
        $ordersProducts->select('order_products.*')->leftJoin('warehouse_products','warehouse_products.product_id', '=', 'order_products.product_id')->where('warehouse_products.warehouse_id','=',$warehouse_id)->orderBy(\DB::Raw('warehouse_products.reserved_quantity+0'), $default_sort);
      }
      elseif($column_name == "reserved_quantity") {
        $ordersProducts->orderBy('order_products.quantity', $default_sort);
      }
      // dd($ordersProducts->get());
      return $ordersProducts->get();
    }

    public static function doSortPickInstructionDetialPage($request, $query) {
      $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';

      if($request['column_name'] == "reference_no")
      {
        $query->select('order_products.*')->leftJoin('products','products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $sort_order);
      }
      elseif($request['column_name'] == "description")
      {
        $query->orderBy('short_desc', $sort_order);
      }
      elseif($request['column_name'] == "location") {
        $query->select('order_products.*')->leftJoin('warehouses','warehouses.id', '=', 'order_products.warehouse_id')->orderBy('warehouses.location_code', $sort_order);
      }
      elseif($request['column_name'] == "unit_price") {
        $query->orderBy(\DB::Raw('unit_price+0'), $sort_order);
      }
      elseif($request['column_name'] == "quantity") {
        $query->orderBy(\DB::Raw('quantity+0'), $sort_order);

      }
      elseif($request['column_name'] == "pieces") {
        $query->orderBy('number_of_pieces', $sort_order);
      }
      elseif($request['column_name'] == "current_quantity") {
        $warehouse_id = Auth::user()->warehouse_id;
        $query->select('order_products.*')->leftJoin('warehouse_products','warehouse_products.product_id', '=', 'order_products.product_id')->where('warehouse_products.warehouse_id','=',$warehouse_id)->orderBy(\DB::Raw('warehouse_products.current_quantity+0'), $sort_order);

      }
      elseif($request['column_name'] == "reserved_quantity") {
        $warehouse_id = Auth::user()->warehouse_id;
        $query->select('order_products.*')->leftJoin('warehouse_products','warehouse_products.product_id', '=', 'order_products.product_id')->where('warehouse_products.warehouse_id','=',$warehouse_id)->orderBy(\DB::Raw('warehouse_products.reserved_quantity+0'), $sort_order);
      }
      elseif($request['column_name'] == "reserved_quantity") {
        $query->orderBy('quantity', $sort_order);
      }
      // dd($query->get());
      return $query->get();

    }

    public static function doSortPrint($column_name, $sortorder, $query) {
      $sortorder == '1' ? $sortorder='DESC' : $sortorder='ASC';

      if($column_name == "reference_no")
      {
        $query->select('order_products.*')->leftJoin('products','products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $sortorder);
      }
      elseif($column_name == "description")
      {
        $query->orderBy('short_desc', $sortorder);
      }
      elseif($column_name == "brand")
      {
        $query->orderBy('brand', $sortorder);
      }
      elseif($column_name == "quantity")
      {
        $query->orderBy(\DB::Raw('quantity+0'), $sortorder);
      }
      // elseif($column_name == "pieces")
      // {
      //   $query->orderBy('pcs_shipped', $sortorder);
      // }
      elseif($column_name == "reference_price")
      {
        $query->orderBy('exp_unit_cost', $sortorder);
      }
      elseif($column_name == "default_price")
      {
        $query->orderBy(\DB::Raw('unit_price+0'), $sortorder);
      }
      elseif($column_name == "discount")
      {
        $query->orderBy(\DB::Raw('discount+0'), $sortorder);
      }
      elseif($column_name == "vat")
      {
        $query->orderBy(\DB::Raw('vat+0'), $sortorder);
      }
      elseif($column_name == "unit_price")
      {
        $query->orderBy(\DB::Raw('unit_price_with_vat+0'), $sortorder);
      }
      elseif($column_name == "total_amount")
      {
        $query->orderBy(\DB::Raw('unit_price_with_vat+0'), $sortorder);
      }
      elseif($column_name == "supply_from")
      {
        $query->select('order_products.*')->leftJoin('suppliers','suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $sortorder);
      } else {
        $query->orderBy('id', 'ASC');
      }

      // dd($query->get());

      return $query->get();
    }

    public static function doSort($request, $query) {
      $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';

      if($request['column_name'] == "reference_no")
      {
        $query->select('order_products.*')->leftJoin('products','products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $sort_order);
      }
      elseif($request['column_name'] == "description")
      {
        $query->orderBy('short_desc', $sort_order);
      }
      elseif($request['column_name'] == "brand")
      {
        $query->orderBy('brand', $sort_order);
      }
      elseif($request['column_name'] == "quantity")
      {
        $query->orderBy(\DB::Raw('quantity+0'), $sort_order);
      }
      // elseif($request['column_name'] == "pieces")
      // {
      //   $query->orderBy('pcs_shipped', $sort_order);
      // }
      elseif($request['column_name'] == "reference_price")
      {
        $query->orderBy('exp_unit_cost', $sort_order);
      }
      elseif($request['column_name'] == "default_price")
      {
        $query->orderBy(\DB::Raw('unit_price+0'), $sort_order);
      }
      elseif($request['column_name'] == "discount")
      {
        $query->orderBy(\DB::Raw('discount+0'), $sort_order);
      }
      elseif($request['column_name'] == "vat")
      {
        $query->orderBy(\DB::Raw('vat+0'), $sort_order);
      }
      elseif($request['column_name'] == "unit_price")
      {
        $query->orderBy(\DB::Raw('unit_price_with_vat+0'), $sort_order);
      }
      elseif($request['column_name'] == "total_amount")
      {
        $query->orderBy(\DB::Raw('unit_price_with_vat+0'), $sort_order);
      }
      elseif($request['column_name'] == "supply_from")
      {
        $query->select('order_products.*')->leftJoin('suppliers','suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $sort_order);
      } else {
        $query->orderBy('id', 'ASC');
      }

    }

    public static function doSortby($request, $query)
    {
      if($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'customer_id';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 1 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'customer_id';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 2 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'from_warehouse_id';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 2 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'from_warehouse_id';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'product_id';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 3 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'product_id';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'short_desc';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 4 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'short_desc';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 6 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'brand';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 6 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'brand';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 7 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'total_price';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 7 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'total_price';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 8 && $request['sortbyvalue'] == 1)
      {
        $sort_variable  = 'total_price_with_vat';
        $sort_order     = 'DESC';
      }
      elseif($request['sortbyparam'] == 8 && $request['sortbyvalue'] == 2)
      {
        $sort_variable  = 'total_price_with_vat';
        $sort_order     = 'ASC';
      }

      if($request['sortbyparam'] == 1)
      {
        $query->leftJoin('orders','orders.id', '=', 'order_products.order_id')->leftJoin('customers','customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_name', $sort_order);
      }
      elseif($request['sortbyparam'] == 2)
      {
        $query->leftJoin('orders','orders.id', '=', 'order_products.order_id')->leftJoin('users','users.id', '=', 'orders.user_id')->leftJoin('warehouses','warehouses.id', '=', 'users.warehouse_id')->orderBy('warehouses.warehouse_title', $sort_order);
      }
      elseif($request['sortbyparam'] == 3)
      {
        $query->leftJoin('products','products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $sort_order);
      }
      elseif($request['sortbyparam'] == 4)
      {
        $query->orderBy($sort_variable, $sort_order);
      }
      elseif($request['sortbyparam'] == 6)
      {
        $query->orderBy($sort_variable, $sort_order);
      }
      elseif($request['sortbyparam'] == 7)
      {
        $query->orderBy(DB::raw('total_price+0'), $sort_order);
      }
      elseif($request['sortbyparam'] == 8)
      {
        $query->orderBy(DB::raw('total_price_with_vat+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'status')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'order_products.order_id')->leftJoin('statuses as s', 's.id' , '=', 'o.status')->orderBy('s.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'ref_po_no')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'order_products.order_id')->orderBy('o.memo', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'po_no')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('purchase_order_details as pod', 'pod.order_product_id' , '=', 'order_products.id')->leftJoin('purchase_orders as po', 'po.id' , '=', 'pod.po_id')->orderBy('po.ref_id', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'sale_person')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'order_products.order_id')->leftJoin('users as u', 'u.id' , '=', 'o.user_id')->orderBy('u.name', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'delivery_date')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'order_products.order_id')->orderBy('o.delivery_request_date', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'created_date')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'order_products.order_id')->orderBy('o.converted_to_invoice_on', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'target_ship_date')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('orders as o', 'o.id' , '=', 'order_products.order_id')->orderBy('o.target_ship_date', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'category')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('product_categories as pc', 'pc.id' , '=', 'p.primary_category')->orderBy('pc.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'type')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('types as t', 't.id' , '=', 'p.type_id')->orderBy('t.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'type_2')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('product_secondary_types as t', 't.id' , '=', 'p.type_id_2')->orderBy('t.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'selling_unit')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('units as u', 'u.id' , '=', 'p.selling_unit')->orderBy('u.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'selling_unit')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->leftJoin('products as p', 'p.id' , '=', 'order_products.product_id')->leftJoin('units as u', 'u.id' , '=', 'p.selling_unit')->orderBy('u.title', $sort_order);
      }
      elseif ($request['sortbyparam'] == 'unit_price')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.unit_price+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'discount')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.discount+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'net_price')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.actual_cost+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'vat_thb')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.vat_amount_total+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'vat_percent')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.vat+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'note_two')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('order_products.vat+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'total_margin')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('(order_products.total_price - (order_products.actual_cost * order_products.qty_shipped))+0'), $sort_order);
      }
      elseif ($request['sortbyparam'] == 'margin_percent')
      {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $query->orderBy(\DB::raw('(CASE when order_products.total_price != 0 then (((order_products.total_price - (order_products.actual_cost * order_products.qty_shipped)) / order_products.total_price) * 100) else 0 END) +0'), $sort_order);
      }
      else
      {
        $query->orderBy('order_products.id','desc');
      }
      return $query;
    }

    public static function PurchaseListSorting($request, $query, $getWarehouses)
    {
      $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
      if ($request['column_name'] == 'sale')
      {
        $query->leftJoin('orders as o', 'o.id', '=', 'order_products.order_id')->leftJoin('users as u', 'u.id', '=', 'o.user_id')->orderBy('u.name', $sort_order);
      }
      elseif ($request['column_name'] == 'reference_name')
      {
        $query->leftJoin('orders as o', 'o.id', '=', 'order_products.order_id')->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')->orderBy('c.reference_name', $sort_order);
      }
      elseif ($request['column_name'] == 'our_reference_number')
      {
        $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->orderBy('p.refrence_code', $sort_order);
      }
      elseif ($request['column_name'] == 'product_description')
      {
        $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->orderBy('p.short_desc', $sort_order);
      }
      elseif ($request['column_name'] == 'primary_category')
      {
        $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->leftJoin('product_categories as pc', 'pc.id', '=', 'p.primary_category')->orderBy('pc.title', $sort_order);
      }
      elseif ($request['column_name'] == 'subcategory')
      {
        $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->leftJoin('product_categories as pc', 'pc.id', '=', 'p.category_id')->orderBy('pc.title', $sort_order);
      }
      elseif ($request['column_name'] == 'order_confirm')
      {
        $query->leftJoin('orders as o', 'o.id', '=', 'order_products.order_id')->orderBy('o.converted_to_invoice_on', $sort_order);
      }
      elseif ($request['column_name'] == 'target_ship_date')
      {
        $query->leftJoin('orders as o', 'o.id', '=', 'order_products.order_id')->orderBy('o.target_ship_date', $sort_order);
      }
      elseif ($request['column_name'] == 'delivery_request_date')
      {
        $query->leftJoin('orders as o', 'o.id', '=', 'order_products.order_id')->orderBy('o.delivery_request_date', $sort_order);
      }
      elseif ($request['column_name'] == 'pieces')
      {
        $query->orderBy(\DB::Raw('number_of_pieces+0'), $sort_order);
      }
      elseif ($request['column_name'] == 'qty')
      {
        $query->orderBy(\DB::Raw('quantity+0'), $sort_order);
      }
      elseif ($request['column_name'] == 'billed_unit')
      {
        $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->leftJoin('units as u', 'u.id', '=', 'p.buying_unit')->orderBy('u.title', $sort_order);
      }
      elseif ($request['column_name'] == 'billed_unit')
      {
        $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->leftJoin('units as u', 'u.id', '=', 'p.buying_unit')->orderBy('u.title', $sort_order);
      }
      elseif ($getWarehouses->count() > 0)
      {
        foreach ($getWarehouses as $warehouse)
        {
          if ($request['column_name'] == $warehouse->warehouse_title.'_available_qty')
          {
            $query->leftJoin('products as p', 'p.id', '=', 'order_products.product_id')->leftJoin('warehouse_products as wp', 'wp.product_id', '=', 'p.id')->where('wp.warehouse_id',$warehouse->id)->orderBy(\DB::Raw('wp.available_quantity+0'), $sort_order);
          }
        }
      }
      else
      {
        $query->orderBy('order_id', 'ASC');
      }
      return $query;
    }


    public static function returnAddColumn($column, $item, $product_type, $units, $purchasing_role_menu) {
        switch ($column) {
            case 'size':
                if($item->product_id != null && $item->product != null)
                {
                  if($item->product->product_notes!=null)
                    return $item->product->product_notes;
                  else
                    return '--';
                }
                else
                {
                  return '--';
                }
                break;

            case 'restaurant_price':
                $prodFixPrice = $item->product != null ? ($item->product->product_fixed_price->where('customer_type_id', 1)->first()) : 'N.A';
                if($prodFixPrice!="N.A" && $prodFixPrice != null)
                {
                    $formated_value = number_format($prodFixPrice->fixed_price,3,'.',',');
                    return (@$formated_value !== null) ? $formated_value : '--';
                }
                else
                {
                    return 'N.A';
                }
                break;

            case 'available_qty':
                $warehouse_id = $item->user_warehouse_id != null ? $item->user_warehouse_id : ($item->from_warehouse_id != null ? $item->from_warehouse_id :Auth::user()->warehouse_id);
                $stock = $item->product != null ? number_format($item->product->warehouse_products->where('warehouse_id',$warehouse_id)->first()->available_quantity,3,'.',',') : 'N.A';
                return $stock;
                break;

            case 'last_price':
                $order = Order::with('order_products')->whereHas('order_products',function($q) use($item) {
                    $q->where('is_billed','Product');
                    $q->where('product_id',$item->product_id);
                  })->where('customer_id',$item->get_order->customer_id)->where('primary_status',3)->orderBy('converted_to_invoice_on','desc')->first();
                  if($order)
                  {
                    $cust_last_price = number_format($order->order_products->where('product_id',$item->product_id)->first()->unit_price, 2, '.', ',');
                  }
                  else
                  {
                    $cust_last_price = "N.A";
                  }

                  return $cust_last_price;
                break;

            case 'notes':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                return "--";
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

                return $html_string;
              }
                break;

            case 'supply_from':
                if(Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  $class = "";
                }
                else if($item->get_order->status == 24)
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
                else if($item->product != null)
                {
                  $label = $item->is_warehouse == 1 ? 'Warehouse' : (@$item->from_supplier->reference_name != null ? @$item->from_supplier->reference_name : "--");
                //   $label = @$item->from_supplier != null && @$item->from_supplier->reference_name != null ? @$item->from_supplier->reference_name : 'Warehouse';
                  $html =  '<span class="'.$class.' supply_from_'.$item->id.'">'.@$label.'</span>';
                  $html .= '<select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" id="select_supply_from" name="from_warehouse_id" >';
                  $html .= '<option value="" selected disabled>Choose Supply From</option>';
                  $html .= '<optgroup label="Select Warehouse">';
                    if($item->is_warehouse == 1)
                    {
                    //   $html = $html.'<option selected value="w-1">Warehouse</option>';
                      $html = $html.'<option selected value="w-'.$item->get_order->from_warehouse_id.'">Warehouse</option>';
                    }
                    else
                    {
                    //   $html = $html.'<option value="w-1">Warehouse</option>';
                      $html = $html.'<option value="w-'.$item->get_order->from_warehouse_id.'">Warehouse</option>';
                    }
                  $html = $html.'</optgroup>';
                  if($purchasing_role_menu!=null)
                  {
                    $html .= '<optgroup label="Suppliers">';
                        $getSuppliers = $item->product->supplier_products;
                          foreach ($getSuppliers as $sup)
                          {
                            if ($sup->is_deleted == 0) {
                                $getSupplier = $sup->supplier;
                                $value = $item->supplier_id == @$getSupplier->id ? 'selected' : "";
                                $html .= '<option value="s-'.@$getSupplier->id.'">'.@$getSupplier->reference_name.'</option>';
                            }
                          }

                    $html .= ' </optgroup>';
                  }
                  else
                 {
                  //Do Nothing
                 }

                    $html .= ' </select>';
                  return $html;
                }
                else{
                    return "N.A";
                }
                break;

            case 'vat':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  return $item->vat != null ? $item->vat : '--';
                }
                else
                {
                  if($item->unit_price != null && $item->get_order->is_vat == 0)
                  {
                    $clickable = $item->get_order->status == 24 ? "" : "inputDoubleClick";
                  } else {
                    $clickable = $item->get_order->status == 24 ? "" : "inputDoubleClick";
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
                $html_string ='<span class="total-price total-price-'.$item->id.' total_amount_wo_vat_'.$item->id.'">'.number_format($total_price, 2, '.', ',').'</span>';
                return $html_string;
                break;

            case 'unit_price_discount':
                return $item->unit_price_with_discount != null ? '<span class="unit_price_after_discount_'.$item->id.'">'.number_format($item->unit_price_with_discount, 2, '.', ',').'</span>' : '--';
                break;

            case 'unit_price_with_vat':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else if($item->get_order->status == 24)
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

                 $html = '<span class="'.$class.' unit_price_w_vat_'.$item->id.'" data-fieldvalue="'.@$unit_price_with_vat.'">'.@$unit_price_with_vat2.'</span><input type="tel" name="unit_price_with_vat" step="0.01"  value="'.$unit_price_with_vat.'" class="unit_price_with_vat form-control input-height d-none unit_price_w_vat_field'.$item->id.'" id="input_unit_price_with_vat" style="width:100%;  border-radius:0px;">';
                 return $html;
                break;

            case 'last_updated_price_on':
                if($item->last_updated_price_on != null)
                {
                  return Carbon::parse($item->last_updated_price_on)->format('d/m/Y');
                }
                else
                {
                  return '--';
                }
                break;

            case 'unit_price':
                if(Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else if($item->get_order->status == 24)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }
              $star = '';
              if ($item->product != null) {
                if(is_numeric($item->margin)){
                  if($item->product)
                  {
                    $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
                    if($product_margin){
                        $star = '*';
                    }
                  }
                }
                $unit_price = number_format($item->unit_price, 2, '.', '');
                $html = '<span class="'.$class.' unit_price_'.$item->id.'" data-fieldvalue="'.number_format(@$item->unit_price, 2,'.','').'">'.$star.number_format(@$item->unit_price, 2).'</span><input type="number" name="unit_price" step="0.01"  value="'.number_format(@$item->unit_price,2,'.','').'" class="unit_price form-control input-height d-none unit_price_field_'.$item->id.'" id="input_unit_price" style="width:100%;  border-radius:0px;">';
                return $html;
              }
              return '--';
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
                if(is_numeric($item->margin))
                {
                  return $item->margin.'%';
                }
                else
                {
                  return $item->margin;
                }
              }
                break;

            case 'exp_unit_cost':
                if($item->exp_unit_cost == null)
              {
                return "N.A";
              }
              else
              {
                $redHighlighted = '';
                $tooltip = '';
                if($item->product_id != null && $item->product != null)
                {
                  $checkItemPo = $item->po_group_product_detail->count();
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
                }

                $html_string ='<span title="'.$tooltip.'" class="unit-price-'.$item->id.'" '.$redHighlighted.'>'.number_format(floor($item->exp_unit_cost*100)/100, 2).'</span>';
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
                return ($item->total_price_with_vat !== null ? '<span class="total_amount_w_vat_'.$item->id.'">'.number_format($item->total_price_with_vat,2,'.','').'</span>' : "--");
                break;

            case 'buying_unit':
                return ($item->product && $item->product->units !== null ? $item->product->units->title : "N.A");
                break;

            case 'sell_unit':
                if($item->product_id !== NULL && $item->product != null)
              {
                return $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
              }
              else
              {

               $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
               $html =  '<span class="inputDoubleClick">'.@$unit.'</span>';
                $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" id="select_sell_unit" name="selling_unit" >';
                $html .= '<optgroup label="Select Sale Unit">';
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
                if($item->is_retail == "qty")
              {
                $checked = "disabled";
              }
              else
              {
                $checked = "";
              }
              if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
                $radio = "disabled";
              }
              else if($item->get_order->status == 24)
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
                if($item->product_id !== NULL && $item->product != null)
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
                  }
                    $sale_unit = $html;
                }
              }
                 $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->qty_shipped.'" id="span_qty_shiped_'.@$item->id.'">'.($item->qty_shipped != null ? $item->qty_shipped : "--" ).'</span><input type="number" name="qty_shipped"  value="'.$item->qty_shipped.'" class="input_qty_shiped_'.@$item->id.' qty_shipped form-control input-height d-none" id="input_qty_shipped" style="width:100%; border-radius:0px;"> ';
                  $html .= @$sale_unit;
                  if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                   $html .= '
                    <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                    $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="qy_shipped_is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->qty_shipped.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' id="checkbox_qty_shipped">';

                    $html .='<label class="custom-control-label" for="qy_shipped_is_retail'.@$item->id.'"></label></div>';
                  }
              return $html;
                break;

            case 'pcs_shipped':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
              {
                $class = "";
                $radio = "disabled";
              }
              else if($item->get_order->status == 24)
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
                  $html = '<span class="'.$class.'" data-fieldvalue="'.@$item->pcs_shipped.'" id="span_pcs_shiped_'.@$item->id.'">'.($item->pcs_shipped != null ? $item->pcs_shipped : "--" ).'</span><input type="number" name="pcs_shipped"  value="'.$item->pcs_shipped.'" class="input_pcs_shiped_'.@$item->id.' pcs_shipped form-control input-height d-none" id="input_pcs_shipped" style="width:100%; border-radius:0px;"> ';
                      if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7){
                      $html .= '
                  <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                  $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces_shipped'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->pcs_shipped.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). ' id="checkbox_pcs_shipped">';

                  $html .='<label class="custom-control-label" for="pieces_shipped'.@$item->id.'"></label></div>';
                }
              }
              else
              {
                $html = 'N.A';
              }

              return $html;
                break;

            case 'number_of_pieces':
                $sale_unit = '';
              if(Auth::user()->role_id == 5 || Auth::user()->role_id == 6|| Auth::user()->role_id == 7)
              {
                $class = "";
                $radio = "disabled";
              }
              else if(Auth::user()->role_id == 2)
              {
                $radio = "disabled";
                $class = 'inputDoubleClick';
              }
              else if($item->get_order->status == 24)
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

              if($item->get_order->ecommerce_order == 1 && $item->is_retail == 'pieces')
                {
                  $sale_unit = $item->product && $item->product->ecomSellingUnits ? $item->product->ecomSellingUnits->title : "N.A";
                }
                else
                {
                  $sale_unit = '';
                }
              if(@$item->get_order->primary_status == 25 || @$item->get_order->primary_status == 28)
              {
                $class = 'inputDoubleClick';
              }
               if(@$item->get_order->primary_status == 3){
                if(@$item->is_billed !== 'Billed')
                {
                  $html = '<span class="pcs_span_'.$item->id.'" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span> ';
                }
                else
                {
                  $html = 'N.A';
                }
              }else if(@$item->is_billed !== 'Billed'){

                $html = '<span class="'.$class.' pcs_span_'.$item->id.'" data-fieldvalue="'.@$item->number_of_pieces.'">'.($item->number_of_pieces != null ? $item->number_of_pieces : "--" ).'</span><input type="number" name="number_of_pieces"  value="'.$item->number_of_pieces.'" class="number_of_pieces form-control input-height d-none" id="input_number_of_pieces_'.$item->id.'" style="width:100%; border-radius:0px;">';
                if(@$item->get_order->primary_status != 3 && @$item->is_billed !== 'Billed' && $item->get_order->primary_status !== 25 && $item->get_order->primary_status !== 28){

                $html .= '
                <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). '>';

                $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
                }else if(Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                  $html .= '
                    <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                    $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="pieces'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->number_of_pieces.'" value="pieces" ' .($item->is_retail == "pieces" ? "checked" : ""). '>';

                    $html .='<label class="custom-control-label" for="pieces'.@$item->id.'"></label></div>';
                }
                }
                else
                {
                    $html = 'N.A';
                }
                return $html.' '.$sale_unit;
                break;

            case 'quantity':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  $class = "";
                  $radio = "disabled";
                }
                else if($item->get_order->status == 24)
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
                if($item->product_id !== NULL && $item->product != null)
                {
                    $sale_unit = $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
                }
                else
                {

                 $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
                 $html = '<span class="'.$class.'">'.@$unit.'</span>';
                  $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" id="select_quantity" name="selling_unit" >';
                  $html .= '<optgroup label="Select Sale Unit">';
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
                  $html .= '<span class="quantity_span_'.$item->id.'" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span> ';
                }else{
                  $html .= '<span class="inputDoubleClick quantity_span_'.$item->id.'" data-fieldvalue="'.@$item->quantity.'">'.($item->quantity != null ? $item->quantity : "--" ).'</span>';
                  $html .= '<input type="number" name="quantity"  value="'.$item->quantity.'" class="quantity form-control input-height d-none" id="input_quantity_'.$item->id.'" style="width:100%; border-radius:0px;"> ';
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
                if($item->get_order->status == 24)
                {
                    $class = "";
                }
                else
                {
                    $class = 'inputDoubleClick';
                }
                if($item->type_id == null)
                {
                  $html_string = '
                  <span class="m-l-15 '.$class.' product_type_'.$item->id.'" id="product_type" data-fieldvalue="'.$item->type_id.'">';
                  $html_string .= 'Select';
                  $html_string .= '</span>';

                  $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                  <option value="" selected="" disabled="">Choose Type</option>';
                  foreach ($product_type as $type) {
                    $html_string .='<option value="'.@$type->id.'" >'.$type->title.'</option>';
                  }
                  $html_string .= '</select>';

                }
                else
                {
                  $html_string = '
                  <span class="m-l-15 '.$class.' product_type_'.$item->id.'" id="product_type"  data-fieldvalue="'.$item->type_id.'">';
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
                if($item->product_id !== NULL && $item->product != null)
                return  $item->unit ? $item->product->product_temprature_c : "N.A";
                break;

            case 'selling_unit':
                if($item->product_id !== NULL && $item->product != null)
                return   $item->unit ? $item->unit->title : "N.A";
                break;

            case 'brand':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  $class = "";
                }
                else if($item->get_order->status == 24)
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

            case 'category_id':
                $html_string=null;
                if($item->is_billed == 'Product' && $item->product != null)
                {
                if($item->category_id != null)
                {
                  $html_string = '<span class="m-l-15 " id="category_id" data-fieldvalue="'.@$item->category_id.'" data-id="cat '.@$item->category_id.' '.@$item->id.'"> ';
                  $html_string .= ($item->category_id != null) ? $item->product->productSubCategory->get_Parent->title.' / '.$item->product->productSubCategory->title: "--";
                }
                else
                {
                  $html_string = '<span class="m-l-15 " id="category_id" data-fieldvalue="'.@$item->category_id.'" data-id="cat '.@$item->category_id.' '.@$item->id.'"> ';
                  $html_string .= ($item->product_id!= null) ? $item->product->productSubCategory->get_Parent->title.' / '.$item->product->productSubCategory->title: "--";
                }
                $html_string .= '</span>';

                $html_string .= '<div class="incomplete-filter d-none inc-fil-cat">
                <select class="font-weight-bold form-control-lg form-control js-states state-tags select-common category_id categories_select'.@$item->id.'" name="category_id" required="true">';
                $html_string .= '</select></div>';
                return $html_string;
                }
                else
                {
                    return '--';
                }
                break;

            case 'description':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
              {
                $class = "";
              }
              else if($item->get_order->status == 24)
              {
                $class = "";
              }
              else
              {
                $class = "inputDoubleClick";
              }
               if($item->short_desc == null)
                {
                    $style = "color:red;";
                }
                else
                {
                    $style = "";
                }
                $html = '<span class="'.$class.' description_'.$item->id.'" data-fieldvalue="'.$item->short_desc.'" style="'.@$style.'">'.($item->short_desc != null ? $item->short_desc : "--" ).'</span><input type="text" name="short_desc" value="'. htmlspecialchars($item->short_desc).'"  class="short_desc form-control input-height d-none" id="input_description" style="width:100%">';
                return $html;
                break;

            case 'discount':
                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                {
                  $class = "";
                }
                else if($item->get_order->status == 24)
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
                if($item->product_id!=null && $item->product != null)
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
                  $reference_code = $item->product != null ? $item->product->refrence_code : 'N.A';
                  return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.@$item->product->id).'"  ><b>'.$reference_code.'<b></a>';
                }
                break;

            case 'action':
                $html_string = '';
                if(Auth::user()->role_id == 2)
                {
                  $disable = "";
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
                  $html_string .= '<button type="button" class="actionicon d-none inquiry_modal" data-toggle="modal" data-target="#inquiryModal">
                                  Add as Inquiry Product
                                </button>';
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

    public static function returnAddColumnInquiryProduct($column, $item) {
        switch ($column) {
            case 'quotation_no':
                return ($item->order_id != null ? @$item->get_order->ref_id : "N.A");
                break;

            case 'added_by':
                return ($item->created_by != null ? $item->added_by->name : "N.A");
                break;

            case 'category_id':
                $parentCat = ProductCategory::where('parent_id',0)->orderBy('title','ASC')->get();
                if($item->category_id == null)
                {
                    $color = 'red';
                }
                else
                {
                    $color = '';
                }
                $html_string = '<span style="color:'.$color.';" class="m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="'.@$item->category_id.'">';
                $html_string .= $item->category_id != null ? $item->productSubCategory->title : 'Select Category';
                $html_string .= '</span>';

                $html_string .= '<div class="d-none">';
                $html_string .= '<select name="category_id" class="selectFocus incomp-select2 state-tags form-control prod-category">
                                <option>Choose Category</option>';
                if($parentCat)
                {
                    foreach($parentCat as $category)
                    {
                        $html_string .= '<optgroup label="'.$category->title.'">';
                            $subCat = ProductCategory::where('parent_id',$category->id)->get();
                          foreach($subCat as $scat)
                          {
                            $condition = $item->category_id == $scat->id ? 'selected' : '';
                            $html_string .= '<option '.$condition.' value="'.$scat->id.'">'.$scat->title.'</option>';
                          }
                        $html_string .= '</optgroup>';
                    }
                }
                $html_string .= '</select>';
                $html_string .= '</div>';
                return $html_string;
                break;

            case 'default_price':
                return ($item->unit_price != null ? number_format((float)@$item->unit_price, 3, '.', '') : "N.A");
                break;

            case 'qty':
                return ($item->quantity != null ? $item->quantity : "N.A");
                break;

            case 'supplier':
                return ($item->default_supplier_rel != null ? $item->default_supplier_rel->reference_name : "N.A");
                break;

            case 'pieces':
                return ($item->number_of_pieces != null ? $item->number_of_pieces : "N.A");
                break;

            case 'reference_no':
                if($item->product == null )
                {
                    return "N.A";
                }
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                </div>';
                return $html_string;
                break;
        }
    }

    public static function returnEditColumnInquiryProduct($column, $item) {
        switch ($column) {
            case 'short_desc':
                return ($item->short_desc != null ? $item->short_desc : "N.A");
                break;
        }
    }

    public static function returnFilterColumnInquiryProduct($column, $item, $keyword) {
        switch ($column) {
            case 'added_by':
                $item->whereHas('added_by', function($q) use($keyword){
                    $q->where('users.name','LIKE', "%$keyword%");
                });
                break;

            case 'quotation_no':
                $item = $item->whereIn('order_id', Order::select('id')->where('ref_id','LIKE',"%$keyword%")->pluck('id'));
                break;

        }
    }

}
