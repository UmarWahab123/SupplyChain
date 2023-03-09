@php
use Carbon\Carbon;
@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
          <tr>
            @if (!in_array("1", $not_in_arr))<th>PO #</th>@endif

            @if (!in_array("2", $not_in_arr))<th>Order #</th>@endif

            @if (!in_array("3", $not_in_arr))<th>{{$global_terminologies['suppliers_product_reference_no']}}</th>@endif

            @if (!in_array("4", $not_in_arr))<th>{{$global_terminologies['our_reference_number']}}</th>@endif
           
            @if (!in_array("5", $not_in_arr))<th>Warehouse</th>@endif

            @if (!in_array("6", $not_in_arr))<th>Supplier <br> Reference<br> Name</th>@endif

            @if (!in_array("7", $not_in_arr))<th>{{$global_terminologies['supplier_description']}}</th>@endif

            @if (!in_array("8", $not_in_arr))<th>Customer</th>@endif
           
            @if (!in_array("9", $not_in_arr))<th>{{$global_terminologies['qty']}} <br>Ordered</th>@endif

            @if (!in_array("10", $not_in_arr))<th>{{$global_terminologies['qty']}} <br>Inv</th>@endif

            @if(Auth::user()->role_id == 2)
              <th>Unit Price</th>
              <th>Discount (%)</th>
              <th>Amount</th>

              <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
              <th>{{$global_terminologies['landing_per_billed_unit']}}</th>
              <th>Import Tax (Actual)</th>
            @endif

            <th>{{$global_terminologies['avg_units_for-sales'] }}</th>

            @if (!in_array("11", $not_in_arr))<th>Billed<br> Unit</th>@endif

            @if (!in_array("12", $not_in_arr))<th>{{$global_terminologies['qty']}} <br>Rcvd 1</th>@endif

            @if (!in_array("13", $not_in_arr))<th>Expiration <br>Date 1</th>@endif
           
            @if (!in_array("14", $not_in_arr))<th>{{$global_terminologies['qty']}} <br>Rcvd 2</th>@endif

            @if (!in_array("15", $not_in_arr))<th>Expiration <br>Date 2</th>@endif

            @if (!in_array("16", $not_in_arr))<th>Goods <br>Condition </th>@endif

            @if (!in_array("17", $not_in_arr))<th>Results </th>@endif

            @if (!in_array("18", $not_in_arr))<th>Goods <br>Type </th>@endif

            @if (!in_array("19", $not_in_arr))<th>Goods <br>Temp C</th>@endif

            @if (!in_array("20", $not_in_arr))<th>Checker </th>@endif

            @if (!in_array("21", $not_in_arr))<th>Problem <br>Found </th>@endif

            @if (!in_array("22", $not_in_arr))<th>Solution </th>@endif

            @if (!in_array("23", $not_in_arr))<th>Authorized <br>Changes</th>@endif

          </tr>
        </thead>
        <tbody>
            @foreach($query as $item)
            @if($item->occurrence == 1)
            <tr>
            @if (!in_array("1", $not_in_arr))
              <td>
                @php
                $occurrence = $item->occurrence;
                if($occurrence == 1)
                {
                  $purchase_orders_ids =  App\Models\Common\PurchaseOrders\PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
                  $pod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
                  if($pod[0]->PurchaseOrder->ref_id !== null)
                  {
                    $html_string = $pod[0]->PurchaseOrder->ref_id;
                  }
                  else
                  {
                    $html_string = "--";
                  }
                }
                else
                {
                  $html_string = '--';
                }
                @endphp
              {{$html_string}}
              </td>
            @endif

            @if (!in_array("2", $not_in_arr))
              <td>
                @php
                $occurrence = $item->occurrence;
                if($occurrence == 1)
                {
                  $purchase_orders_ids =  App\Models\Common\PurchaseOrders\PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
                  $pod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_id','order_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
                  $order = App\Models\Common\Order\Order::find($pod[0]->order_id);
                  if($order !== null)
                  {

                    if($order->primary_status == 3)
                    {
                      if($order->in_status_prefix !== null || $order->in_ref_prefix !== null)
                      {
                        $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
                      }
                      else
                      {
                        $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;
                      }
                    }
                    else
                    {
                      if($order->status_prefix !== null || $order->ref_prefix !== null)
                      {
                        $ref_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
                        }else{
                        $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;                          
                      }
                    }
                    $html_string = $ref_no;
                  }
                  else
                  {
                    $html_string = 'N.A';
                  }
                }
                else
                {
                  $html_string = '--';
                }
                @endphp
              {{$html_string}}
            </td>
            @endif

            @if (!in_array("3", $not_in_arr))
              <td>
              @php
              if($item->supplier_id !== NULL)
              {
                $sup_name = App\Models\Common\SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
                $sup_ref = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
              }
              else
              {
                $sup_ref = "N.A";
              }
              @endphp
              {{$sup_ref}}
              </td>
            @endif
            
            @if (!in_array("4", $not_in_arr))  
              <td>
              @php
              $product_ref = $item->product->refrence_code;          
              @endphp
              {{$product_ref}}
              </td>
            @endif

            @if (!in_array("5", $not_in_arr))
              <td>
                @php
                $occurrence = $item->occurrence;
                if($occurrence == 1)
                {
                  $purchase_orders_ids =  App\Models\Common\PurchaseOrders\PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
                  $pod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_id','order_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
                  $order = App\Models\Common\Order\Order::find($pod[0]->order_id);
                  if($order !== null){
                  $html_string = $order->user->get_warehouse->warehouse_title ;
                }else{
                  $html_string = 'N.A';
                }
                }
                else
                {
                  $html_string = '--';
                }
                @endphp
              {{$html_string}}
            </td>
            @endif

            @if (!in_array("6", $not_in_arr))
              <td>
              @php

              if($item->supplier_id !== NULL)
              { 
                $sup_name = $item->get_supplier->reference_name;
              }
              else
              {
                $sup_name = $item->get_warehouse->warehouse_title;
              }          
              @endphp 
              {{$sup_name}} 
              </td>
            @endif

            @if (!in_array("7", $not_in_arr))
              <td>
              @php
              $product_ref = $item->product->short_desc;          
              @endphp
              {{$product_ref}}
              </td>
            @endif

            @if (!in_array("8", $not_in_arr))
              <td>
                @php
                $occurrence = $item->occurrence;
                if($occurrence == 1)
                {
                  $purchase_orders_ids =  App\Models\Common\PurchaseOrders\PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
                  $pod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_id','order_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
                  $order = App\Models\Common\Order\Order::find($pod[0]->order_id);
                  if($order !== null){
                    $html_string = $order->customer->reference_name;
                  }else{
                    $html_string = 'N.A';
                  }
                }
                else
                {
                  $html_string = '--';
                }
                @endphp
              {{$html_string}}
            </td>
            @endif

            @if (!in_array("9", $not_in_arr))
              <td>{{round($item->quantity_ordered,3).' '.@$item->product->sellingUnits->title}}</td>
            @endif

            @if (!in_array("10", $not_in_arr))
              <td>{{round($item->quantity_inv,3)}}</td>
            @endif

            @if(Auth::user()->role_id == 2)
              <td>{{round($item->unit_price,3)}}</td>
              <td>{{round($item->discount,3)}}</td>
              <td>{{round($item->total_unit_price,3)}}</td>

              @php
                $getProductDefaultSupplier = $item->product->supplier_products->where('supplier_id',$item->supplier_id)->first();
              @endphp
              <td>
                @php
                  if($getProductDefaultSupplier)
                  {
                    $html_string = $getProductDefaultSupplier->freight != NULL ? $getProductDefaultSupplier->freight : "--";
                  }
                  else
                  {
                    $html_string = "--";
                  }
                @endphp
                {{$html_string}}
              </td>
              <td>
                @php
                  if($getProductDefaultSupplier)
                  {
                    $html_string = $getProductDefaultSupplier->landing != NULL ? $getProductDefaultSupplier->landing : "--";
                  }
                  else
                  {
                    $html_string = "--";
                  }
                @endphp
                {{$html_string}}
              </td>
              <td>
                @php
                  if($getProductDefaultSupplier)
                  {
                    $html_string = $getProductDefaultSupplier->import_tax_actual != NULL ? $getProductDefaultSupplier->import_tax_actual : "--";
                  }
                  else
                  {
                    $html_string = "--";
                  }
                @endphp
                {{$html_string}}
              </td>
            @endif

            <td>{{$item->product->weight != null ? $item->product->weight : "--"}}</td>
            
            @if (!in_array("11", $not_in_arr))
              <td>{{$item->product->units->title != null ? $item->product->units->title : ''}}</td>
            @endif

            @if (!in_array("12", $not_in_arr))
              <td>{{$item->quantity_received_1 != null ? $item->quantity_received_1 : '' }}</td>
            @endif

            @if (!in_array("13", $not_in_arr))
              <td>{{$item->expiration_date_1 !== null ? Carbon::parse($item->expiration_date_1)->format('d/m/Y') : ''}}</td>
            @endif

            @if (!in_array("14", $not_in_arr))
              <td>{{$item->quantity_received_2 != null ? $item->quantity_received_2 : ''}}</td>
            @endif

            @if (!in_array("15", $not_in_arr))
              <td>{{$item->expiration_date_2 !== null ? Carbon::parse($item->expiration_date_2)->format('d/m/Y') : ''}}</td>
            @endif

            @if (!in_array("16", $not_in_arr))
              <td>{{$item->good_condition}}</td>
            @endif

            @if (!in_array("17", $not_in_arr))
              <td>{{$item->result}}</td>
            @endif

            @if (!in_array("18", $not_in_arr))
              <td>
              @php
              $goods_type = App\Models\Common\ProductType::find($item->good_type);
              @endphp
              {{@$goods_type->title}}
              </td>
            @endif

            @if (!in_array("19", $not_in_arr))
              <td>{{$item->temperature_c}}</td>
            @endif

            @if (!in_array("20", $not_in_arr))
              <td>{{$item->checker}}</td>
            @endif

            @if (!in_array("21", $not_in_arr))
              <td>{{$item->problem_found}}</td>
            @endif

            @if (!in_array("22", $not_in_arr))
              <td>{{$item->solution}}</td>
            @endif

            @if (!in_array("23", $not_in_arr))
              <td>{{$item->authorized_changes}}</td>
            @endif

            </tr>
            @endif
            @if($item->occurrence > 1)
            @php
            $all_record = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($item){
              $q->where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id);
            })->get();
            @endphp
            @foreach($all_record as $record)
              <tr>
                @if (!in_array("1", $not_in_arr))
                <td>
                  @php
                  $html_string = $record->PurchaseOrder->ref_id;
                  @endphp
                  {{$html_string}}
                </td>
                @endif

                @if (!in_array("2", $not_in_arr))
                <td>
                  @php
                  $order = App\Models\Common\Order\Order::find(@$record->order_id);
                  if($order !== null){
                  if($order->primary_status == 3){
                    if($order->in_status_prefix !== null || $order->in_ref_prefix !== null){
                      $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
                      }else{
                      $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;
                      
                      }
                  }else{
                    if($order->status_prefix !== null || $order->ref_prefix !== null){
                      $ref_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
                      }else{
                      $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;
                      
                      }
                  }
                  $html_string = $ref_no;
                  
                }else{
                  $html_string = "--";
                }
                @endphp
                {{$html_string}}
  
                </td>
                @endif

                @if (!in_array("3", $not_in_arr))
                <td>
                @php

                if($record->PurchaseOrder->supplier_id !== NULL)
                {
                  $sup_name = App\Models\Common\SupplierProducts::where('supplier_id',$record->PurchaseOrder->supplier_id)->where('product_id',$record->product_id)->first();
                  $sup_ref = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
                }
                else
                {
                  $sup_ref = "N.A";
                }
                @endphp
                {{$sup_ref}}
                </td>
                @endif

                @if (!in_array("4", $not_in_arr))
                <td>
                @php
                $product_ref = $record->product->refrence_code;          
                @endphp
                {{$product_ref}}
                </td>
                @endif

                @if (!in_array("5", $not_in_arr))
                <td>
                  @php
                  $order = App\Models\Common\Order\Order::find(@$record->order_id);
                  $html_string = $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
                  @endphp
                  {{$html_string}}
                </td>
                @endif

                @if (!in_array("6", $not_in_arr))
                <td>
                  @php

                  if($record->PurchaseOrder->supplier_id !== NULL)
                  { 
                    $sup_name = $record->PurchaseOrder->PoSupplier->reference_name;
                  }
                  else
                  {
                    $sup_name = $record->PurchaseOrder->PoWarehouse->warehouse_title;
                  }          
                  @endphp 
                  {{$sup_name}} 
                </td>
                @endif

                @if (!in_array("7", $not_in_arr))
                <td>
                @php
                $product_ref = $record->product->short_desc;          
                @endphp
                {{$product_ref}}
                </td>
                @endif

                @if (!in_array("8", $not_in_arr))
                <td>
                  @php
                  $order = App\Models\Common\Order\Order::find(@$record->order_id);
                  if($order !== null)
                  {
                    $html_string = $order->customer->reference_name;
                  }
                  else
                  {
                    $html_string = "--";
                  }
                @endphp
                {{$html_string}}
                </td>
                @endif
          
                @if (!in_array("9", $not_in_arr))
                <td>
                  @if($record->order_product_id != null)
                  {{ round($record->order_product->quantity,3).' '.@$record->product->sellingUnits->title}}
                  @else
                  {{'--'}}
                  @endif
                </td>                        
                @endif

                @if (!in_array("10", $not_in_arr))
                <td>{{round($record->quantity,3)}}</td>
                @endif

                @if(Auth::user()->role_id == 2)
                  <td>{{round($record->pod_unit_price,3)}}</td>
                  <td>{{round($record->discount,3)}}</td>
                  <td>{{round($record->pod_total_unit_price,3)}}</td>

                  @php
                    $getProductDefaultSupplier = $record->product->supplier_products->where('supplier_id',$record->PurchaseOrder->supplier_id)->first();
                  @endphp
                  <td>
                    @php
                      if($getProductDefaultSupplier)
                      {
                        $html_string = $getProductDefaultSupplier->freight != NULL ? $getProductDefaultSupplier->freight : "--";
                      }
                      else
                      {
                        $html_string = "--";
                      }
                    @endphp
                    {{$html_string}}
                  </td>
                  <td>
                    @php
                      if($getProductDefaultSupplier)
                      {
                        $html_string = $getProductDefaultSupplier->landing != NULL ? $getProductDefaultSupplier->landing : "--";
                      }
                      else
                      {
                        $html_string = "--";
                      }
                    @endphp
                    {{$html_string}}
                  </td>
                  <td>
                    @php
                      if($getProductDefaultSupplier)
                      {
                        $html_string = $getProductDefaultSupplier->import_tax_actual != NULL ? $getProductDefaultSupplier->import_tax_actual : "--";
                      }
                      else
                      {
                        $html_string = "--";
                      }
                    @endphp
                    {{$html_string}}
                  </td>
                @endif

                <td>{{$record->product->weight != null ? $record->product->weight : "--"}}</td>

                @if (!in_array("11", $not_in_arr))
                <td>{{$record->product->units->title != null ? $record->product->units->title : ''}}</td>
                @endif

              </tr>
            @endforeach
            @endif
                
            @endforeach

        </tbody> 
    
    </table>

    </body>
</html>
