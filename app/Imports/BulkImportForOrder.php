<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\Order;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\Models\Common\CustomerTypeProductMargin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Auth;

class BulkImportForOrder implements ToCollection ,WithStartRow, WithHeadingRow
{
    private $order_id;
    private $customer_id;
    public $result;
    public $errors = null;
    public $response;
    public $success = false;
    public $sub_total;
    public $total_vat;
    public $grand_total;
    public $status;
    public $discount_full;
    public $sub_total_without_discount;
    public $item_level_dicount;
    public $remaining_amount;

    /**
    * @param Collection $collection
    */

    public function __construct($order_id,$customer_id)
    {
        $this->order_id = $order_id;
        $this->customer_id = $customer_id;
    }

    public function collection(Collection $rows)
    {
      // dd($rows);

      if($rows[0]->has('quotation_file')) {
      if($rows->count() > 2){
        $order_id_to_update = $this->order_id;
        $row1 = $rows->toArray();
        $remove = array_shift($row1);
        $remove = array_shift($row1);
        $error = 0;
        $order = Order::find($order_id_to_update);
        foreach ($row1 as $row) {
          $id = null;
            if(array_key_exists('order_product_id', $row)){
              $id = doubleval($row['order_product_id']);
            }
            if($id != '' && $id != null)
            {
                $order_product = OrderProduct::find(doubleval($row['order_product_id']));
                if($order->primary_status != 3)
                {
                  //To update quantity
                  if($row['quantity_ordered'] != null)
                  {
                  // dd($row['quantity_ordered']);
                      if(!is_numeric($row['quantity_ordered']))
                      {
                          $error = 1;
                          if($row['quantity_ordered'] != '--')
                          {
                            $this->errors .= '<li>Enter Valid Quantity For Product <b>'.$row['reference_no'].'</b></li>';
                          }
                          $row['quantity_ordered'] = null;
                      }
                  }

                  $quantity = $row['quantity_ordered'];
                  $quantity = abs($quantity);
                  if($quantity != $order_product->quantity && $quantity !== '--' && $row['quantity_ordered'] != null)
                  {
                      $request = new \Illuminate\Http\Request();
                      $request->replace(['order_id' => $id, 'quantity' => $quantity, 'old_value' => $order_product->quantity]);
                      app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);


                  }

                  //To update number of pieces
                  if($row['pieces_ordered'] != null)
                  {
                      if(!is_numeric($row['pieces_ordered']))
                      {
                          $error = 1;
                          if($row['pieces_ordered'] !== '--' && $row['pieces_ordered'] !== 'N.A')
                          {
                            $this->errors .= '<li>Enter Valid Number of Pieces For Product <b>'.$row['reference_no'].'</b></li>';
                          }
                          $row['pieces_ordered'] = null;
                      }
                  }
                  $number_of_pieces = $row['pieces_ordered'];
                   $number_of_pieces = abs($number_of_pieces);
                  if($number_of_pieces != $order_product->number_of_pieces && $number_of_pieces !== '--' && $row['pieces_ordered'] != null)
                  {
                      $request = new \Illuminate\Http\Request();
                      $request->replace(['order_id' => $id, 'number_of_pieces' => $number_of_pieces, 'old_value' => $order_product->number_of_pieces]);
                      app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                  }
                }
                if($order->primary_status == 3)
                {
                  //To update quantity shipped
                  if($row['quantity_shipped'] != null)
                  {
                  // dd($row['quantity_shipped']);
                      if(!is_numeric($row['quantity_shipped']))
                      {
                          $error = 1;
                          if($row['quantity_shipped'] != '--')
                          {
                            $this->errors .= '<li>Enter Valid Quantity Shipped For Product <b>'.$row['reference_no'].'</b></li>';
                          }
                          $row['quantity_shipped'] = null;
                      }
                  }

                  $qty_shipped = $row['quantity_shipped'];
                   $qty_shipped = abs($qty_shipped);
                  if($qty_shipped != $order_product->qty_shipped && $qty_shipped !== '--' && $row['quantity_shipped'] != null)
                  {
                      $request = new \Illuminate\Http\Request();
                      $request->replace(['order_id' => $id, 'qty_shipped' => $qty_shipped, 'old_value' => $order_product->qty_shipped]);
                      app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                  }

                  //To update pieces shipped
                  if($row['pieces_shipped'] != null)
                  {
                      if(!is_numeric($row['pieces_shipped']))
                      {
                          $error = 1;
                          if($row['pieces_shipped'] !== '--')
                          {
                            $this->errors .= '<li>Enter Valid Pieces Sent For Product <b>'.$row['reference_no'].'</b></li>';
                          }
                          $row['pieces_shipped'] = null;
                      }
                  }
                  $pcs_shipped = $row['pieces_shipped'];
                   $pcs_shipped = abs($pcs_shipped);
                  if($pcs_shipped != $order_product->pcs_shipped && $pcs_shipped !== '--' && $row['pieces_shipped'] != null)
                  {
                      $request = new \Illuminate\Http\Request();
                      $request->replace(['order_id' => $id, 'pcs_shipped' => $pcs_shipped, 'old_value' => $order_product->pcs_shipped]);
                      app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                  }
                }

                 //To update description
                 if($row['description'] != null)
                 {
                     if($row['description'] == null ||$row['description'] == '--' )
                     {
                         $error = 1;
                           $this->errors .= '<li>Enter Description</li>';
                     }
                 }
                 $description = $row['description'];
                 if($row['description'] !== null ||$row['description'] !== '--' )
                 {
                     $request = new \Illuminate\Http\Request();
                     $request->replace(['order_id' => $id, 'short_desc' => $description]);
                     app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                 }

                //To update default price without vat
                if($row['unit_price'] != null)
                {
                    if(!is_numeric($row['unit_price']))
                    {
                        $error = 1;
                        if($row['unit_price'] != '--')
                        {
                          $this->errors .= '<li>Enter Valid Unit Price For Product <b>'.$row['reference_no'].'</b></li>';
                        }
                        $row['unit_price'] = null;
                    }
                }
                $unit_price = $row['unit_price'];
                 $unit_price = abs($unit_price);
                if($unit_price != $order_product->unit_price && $unit_price !== '--' && $row['unit_price'] != null)
                {
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['order_id' => $id, 'unit_price' => $unit_price, 'old_value' => $order_product->unit_price]);
                    app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                }

                //To update discount
                if($row['discount'] != null)
                {
                    if(!is_numeric($row['discount']))
                    {
                        $error = 1;
                        if($row['discount'] != '--')
                        {
                          $this->errors .= '<li>Enter Valid Discount For Product <b>'.$row['reference_no'].'</b></li>';
                        }
                        $row['discount'] = null;
                    }
                }
                $discount = $row['discount'];
                if($discount != $order_product->discount && $discount !== '--' && ($discount >= 0 && $discount <=100 ) && $row['discount'] != null)
                {
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['order_id' => $id, 'discount' => $discount, 'old_value' => $order_product->discount]);
                    app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                }

                //To update vat
                if($row['vat'] != null)
                {
                    if(!is_numeric($row['vat']))
                    {
                        $error = 1;
                        if($row['vat'] != '--')
                        {
                          $this->errors .= '<li>Enter Valid Vat For Product <b>'.$row['reference_no'].'</b></li>';
                        }
                        $row['vat'] = null;
                    }
                }
                $vat = $row['vat'];
                if($vat != $order_product->vat && $vat !== '--' && ($vat >= 0 && $vat <=100) && $row['vat'] != null)
                {
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['order_id' => $id, 'vat' => $vat, 'old_value' => $order_product->vat]);
                    app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                }

                //To update default price without vat
                if($row['unit_price_vat'] != null)
                {
                    if(!is_numeric($row['unit_price_vat']))
                    {
                        $error = 1;
                        if($row['unit_price_vat'] != '--')
                        {
                          $this->errors .= '<li>Enter Valid Unit Price(+Vat) For Product <b>'.$row['reference_no'].'</b></li>';
                        }
                        $row['unit_price_vat'] = null;
                    }
                }
                $unit_price_with_vat = $row['unit_price_vat'];
                if($unit_price_with_vat != $order_product->unit_price_with_vat && $unit_price_with_vat !== '--')
                {
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['order_id' => $id, 'unit_price_with_vat' => $unit_price_with_vat, 'old_value' => $order_product->unit_price_with_vat]);
                    app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                }
            }
            elseif((array_key_exists('reference_no', $row ) && $row['reference_no'] != '' && $row['reference_no'] != null) || (array_key_exists('pf', $row ) && $row['pf'] != '' && $row['pf'] != null))
            {
              // dd('here');
                $pf = (array_key_exists('reference_no', $row)) ? $row['reference_no'] : $row['pf'];
                $token = csrf_token();
                $request = new \Illuminate\Http\Request();
                $request->replace(['refrence_number' => $pf, 'id' => ['id' => $order_id_to_update]]);
                
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

                    $order_product = OrderProduct::find($new_draft_quotation_products->id);
                    $id = $order_product->id;

                    //To update quantity
                      $quantity = (array_key_exists('quantity_ordered', $row)) ? $row['quantity_ordered'] : $row['qtyordered'];
                    if((array_key_exists('quantity_ordered', $row) && $row['quantity_ordered'] != null) || (array_key_exists('qtyordered', $row) && $row['qtyordered'] != null))
                    {
                      $quantity = explode(' ', $quantity);
                      $quantity = $quantity[0];
                        if(!is_numeric($quantity))
                        {
                            $error = 1;
                            if($quantity != '--')
                            {
                              $this->errors .= '<li>Enter Valid Quantity For Product <b>'.$quantity.'</b></li>';
                            }
                            $quantity = null;
                        }
                    }

                    // $quantity = $row['quantity_ordered'];
                    if($quantity != $order_product->quantity && $quantity !== '--' && $quantity != null)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['order_id' => $id, 'quantity' => $quantity, 'old_value' => $order_product->quantity]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                    }

                    //To update number of pieces
                      $number_of_pieces = (array_key_exists('pieces_ordered', $row)) ? $row['pieces_ordered'] : $row['piecesordered'];
                    if((array_key_exists('pieces_ordered', $row) && $row['pieces_ordered'] != null) || (array_key_exists('piecesordered', $row) && $row['piecesordered'] != null))
                    {
                        if(!is_numeric($number_of_pieces))
                        {
                            $error = 1;
                            if($number_of_pieces != '--' && $number_of_pieces !== 'N.A')
                            {
                              $this->errors .= '<li>Enter Valid Number of Pieces For Product <b>'.$pf.'</b></li>';
                            }
                            $number_of_pieces = null;
                        }
                    }
                    // $number_of_pieces = $row['pieces_ordered'];
                    if($number_of_pieces != $order_product->number_of_pieces && $number_of_pieces !== '--' && $number_of_pieces != null)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['order_id' => $id, 'number_of_pieces' => $number_of_pieces, 'old_value' => $order_product->number_of_pieces]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                    }

                    //To update default price without vat
                      $unit_price = (array_key_exists('unit_price', $row)) ? $row['unit_price'] : $row['unit_price_with_discount'];
                    if((array_key_exists('unit_price', $row) && $row['unit_price'] != null) || (array_key_exists('unit_price_with_discount', $row) && $row['unit_price_with_discount'] != null))
                    {
                        if(!is_numeric($unit_price))
                        {
                            $error = 1;
                            if($unit_price != '--')
                            {
                              $this->errors .= '<li>Enter Valid Unit Price For Product <b>'.$pf.'</b></li>';
                            }
                            $unit_price = null;
                        }
                    }
                    // $unit_price = $row['unit_price'];
                    if($unit_price != $order_product->unit_price && $unit_price !== '--' && $unit_price != null)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['order_id' => $id, 'unit_price' => $unit_price, 'old_value' => $order_product->unit_price]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                    }

                    //To update discount
                    $discount = (array_key_exists('discount', $row)) ? $row['discount'] : 0;
                    if($discount != null)
                    {
                        if(!is_numeric($discount))
                        {
                            $error = 1;
                            if($discount != '--')
                            {
                              $this->errors .= '<li>Enter Valid Discount For Product <b>'.$pf.'</b></li>';
                            }
                            $discount = null;
                        }
                    }

                    if($discount != $order_product->discount && $discount !== '--' && ($discount >= 0 && $discount <=100 ) && $discount != null)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['order_id' => $id, 'discount' => $discount, 'old_value' => $order_product->discount]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                    }

                    //To update vat
                    if($row['vat'] != null)
                    {
                        if(!is_numeric($row['vat']))
                        {
                            $error = 1;
                            if($row['vat'] != '--')
                            {
                              $this->errors .= '<li>Enter Valid Vat For Product <b>'.$pf.'</b></li>';
                            }
                            $row['vat'] = null;
                        }
                    }
                    $vat = $row['vat'];
                    if($vat != $order_product->vat && $vat !== '--' && ($vat >= 0 && $vat <=100) && $row['vat'] != null)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['order_id' => $id, 'vat' => $vat, 'old_value' => $order_product->vat]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                    }

                    //To update default price without vat
                      $unit_price_with_vat = (array_key_exists('unit_price_vat', $row)) ? $row['unit_price_vat'] : $row['unit_pricevat'];
                    if((array_key_exists('unit_price_vat', $row) && $row['unit_price_vat'] != null) || (array_key_exists('unit_pricevat', $row) && $row['unit_pricevat'] != null))
                    {
                        if(!is_numeric($unit_price_with_vat))
                        {
                            $error = 1;
                            if($unit_price_with_vat != '--')
                            {
                              $this->errors .= '<li>Enter Valid Unit Price(+Vat) For Product <b>'.$pf.'</b></li>';
                            }
                            $unit_price_with_vat = null;
                        }
                    }
                    // $unit_price_with_vat = $row['unit_price_vat'];
                    if($unit_price_with_vat != $order_product->unit_price_with_vat && $unit_price_with_vat !== '--')
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['order_id' => $id, 'unit_price_with_vat' => $unit_price_with_vat, 'old_value' => $order_product->unit_price_with_vat]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
                    }
                  }
                  else
                  {
                    $error = 1;
                    $this->errors .= ' '.$pf.' product not found in catalog';            
                  }
            }  
        }
        $this->success = true;
        $query         = OrderProduct::where('order_id',$order_id_to_update)->get();
        $order = Order::find($order_id_to_update);
        foreach ($query as  $value) {
          if($value->is_retail == 'qty')
          {
            $this->sub_total += $value->total_price;
          }
          else if($value->is_retail == 'pieces')
          {
            $this->sub_total += $value->total_price;
          }              
          $this->total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);
          if($value->discount != 0)
          {
            if($value->discount == 100)
            {
              if($value->is_retail == 'pieces')
              {
                $this->discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                $this->sub_total_without_discount += $this->discount_full;
              }
              else
              {
                $this->discount_full =  $value->unit_price_with_vat * $value->quantity;
                $this->sub_total_without_discount += $this->discount_full;
              }
              $this->item_level_dicount += $this->discount_full;
            }
            else
            {
              $this->sub_total_without_discount += $value->total_price / ((100 - $value->discount)/100);
              $this->item_level_dicount += ($value->total_price / ((100 - $value->discount)/100)) - $value->total_price;
            }
          }
          else
          {
            $this->sub_total_without_discount += $value->total_price;
          }
        }
        $this->grand_total = ($this->sub_total)+($this->total_vat);
        $this->status = @$order->statuses != null ? $order->statuses->title : null;
        $this->remaining_amount = $this->grand_total - $order->total_paid;
        
        return response()->json(['msg'=>'File Saved']);
        $result = [];

        $result['sucess'] = true;
        $result['errors'] = $this->errors;
        $result['error'] = $error;

        return $result;
      }
      else{
        $this->errors =  'Please Dont Upload Empty File';
      }
    } else {
      throw new \ErrorException('Invalid File');
    }
  }

    public function startRow():int
    {
        return 1;
    }

    public function getErrors()
    {
      return $this->errors;
    }
}
