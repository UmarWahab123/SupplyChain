<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
          <tr>
            @if(!in_array('1',$not_visible_arr))<th style="font-weight: bold;">Sale</th>@endif
            @if(!in_array('2',$not_visible_arr))<th style="font-weight: bold;" >Draft Invoice #</th>@endif
            @if(!in_array('3',$not_visible_arr))<th style="font-weight: bold;" >Reference Name </th>@endif
            @if(!in_array('5',$not_visible_arr))<th style="font-weight: bold;" >Description</th>@endif
            @if(!in_array('4',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['our_reference_number']}}</th>@endif
            @if(!in_array('6',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['category']}} </th>@endif
            @if(!in_array('7',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['subcategory']}}</th>@endif
            @if(!in_array('8',$not_visible_arr))<th style="font-weight: bold;" >Order Confirm</th>@endif
           <!--  @if($tsd) -->
            @if(!in_array('9',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['target_ship_date'], $tsd}}</th>@endif
          <!--   @endif -->
            @if(!in_array('10',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['delivery_request_date']}} </th>@endif
            @if(!in_array('11',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['pieces']}}</th>@endif
            @if(!in_array('12',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['qty']}}</th>@endif
            @if(!in_array('13',$not_visible_arr))<th style="font-weight: bold;" >Billed Unit</th>@endif
     <!--        @if(!in_array('14',$not_visible_arr))<th style="font-weight: bold;" >Sall Unit</th>@endif -->
            @if(!in_array('14',$not_visible_arr))<th style="font-weight: bold;" >Supplier From</th>@endif
            @if(!in_array('15',$not_visible_arr))<th style="font-weight: bold;" >{{$global_terminologies['suppliers_product_reference_no']}}</th>@endif
            @if(!in_array('16',$not_visible_arr))<th style="font-weight: bold;" >Supplier To</th>@endif
            @if(!in_array('17',$not_visible_arr))<th style="font-weight: bold;" >Notes</th>@endif
            @if($getWarehouses->count() > 0)
            @php $inc = 18; @endphp
            @foreach($getWarehouses as $warehouse)
            @if(!in_array($inc++,$not_visible_arr)) <th style="font-weight: bold;">{{$warehouse->warehouse_title}}<br> Available <br> Qty</th>@endif
            @endforeach
            @endif
          </tr>
        </thead>
            <tbody>
                
                @foreach($query as $item)
                <tr>
                    @if(!in_array('1',$not_visible_arr))
                    <td >
                        {{$item->get_order->user->name}}
                    </td>
                    @endif

                    @if(!in_array('2',$not_visible_arr))
                    <td>
                        <?php
                        if($item->get_order->status_prefix !== null || $item->get_order->ref_prefix !== null){
                        ?>
                        {{$item->get_order->status_prefix.'-'.$item->get_order->ref_prefix.$item->get_order->ref_id}}
                        <?php
                        }else{
                        ?>
                        {{$item->get_order->customer->primary_sale_person->get_warehouse->order_short_code.@$item->get_order->customer->CustomerCategory->short_code.@$item->get_order->ref_id}}
                        <?php
                        }
                        ?>
                    </td>
                    @endif

                    @if(!in_array('3',$not_visible_arr))
                    <td>
                        {{$item->get_order->customer->reference_name != null ? $item->get_order->customer->reference_name: 'N/A'}}  
                    </td>
                    @endif

                    @if(!in_array('5',$not_visible_arr))
                    <td >
                        {{$item->product->short_desc}}        
                    </td>
                    @endif

                    @if(!in_array('4',$not_visible_arr))
                    <td >
                        {{$item->product_id != null ? $item->product->refrence_code : "N.A"}}
                    </td>
                    @endif

                    @if(!in_array('6',$not_visible_arr))
                    <td>
                        {{$item->product->productCategory->title}}
                    </td>
                    @endif

                    @if(!in_array('7',$not_visible_arr))
                    <td>
                        {{$item->product->productSubCategory->title}}
                    </td>
                    @endif
                    
                    @if(!in_array('8',$not_visible_arr))
                    <td>
                        {{$item->get_order->converted_to_invoice_on != null ? Carbon\Carbon::parse(@$item->get_order->converted_to_invoice_on)->format('d/m/Y H:m A') : "N.A"}}
                    </td>
                    @endif

                    @if(!in_array('9',$not_visible_arr))
                    <td>
                        {{$item->get_order->target_ship_date != null ? Carbon\Carbon::parse($item->get_order->target_ship_date)->format('d/m/Y') : "N.A"}}  
                    </td>
                    @endif

                    @if(!in_array('10',$not_visible_arr))
                    <td>
                        {{$item->get_order->delivery_request_date != null ?Carbon\Carbon::parse($item->get_order->delivery_request_date)->format('d/m/Y') : "N.A"}}
                    </td>
                    @endif

                    @if(!in_array('11',$not_visible_arr))
                    <td> 
                        {{$item->number_of_pieces != null ? $item->number_of_pieces : "--"}}
                    </td>
                    @endif

                    @if(!in_array('12',$not_visible_arr))
                    <td>
                        {{$item->quantity != null ? $item->quantity : "--"}}
                    </td>
                    @endif

                    @if(!in_array('13',$not_visible_arr))
                    <td>  
                        {{$item->product->units->title}}
                    </td>
                    @endif

           <!--          @if(!in_array('14',$not_visible_arr))
                    <td>
                        {{$item->product->sellingUnits->title}}
                    </td>
                    @endif -->

                    @if(!in_array('14',$not_visible_arr))
                    <td>  
                        <?php
                            if($item->supplier_id != null && $item->from_warehouse_id == null)
                            {
                        ?>
                        {{$item->from_supplier->reference_name}}
                        <?php 
                            }
                            elseif($item->from_warehouse_id != null && $item->supplier_id == null)
                            {
                        ?>
                        {{$item->from_warehouse->warehouse_title}}
                        <?php
                            }
                            else
                            {
                        ?>
                        {{"N.A"}}
                        <?php
                            }
                        ?>
                    </td>
                    @endif

                    @if(!in_array('15',$not_visible_arr))
                    <td>
                        <?php 
                            if($item->supplier_id != null)
                            {
                                $supplier_id = $item->supplier_id;
                            }
                            else
                            {
                                $supplier_id = $item->product->supplier_id;
                            }
                            $getProductDefaultSupplier = $item->product->supplier_products->where('supplier_id',$supplier_id)->first();
                            if($getProductDefaultSupplier)
                            {
                                if($getProductDefaultSupplier->product_supplier_reference_no != null)
                                {
                        ?>
                        {{$getProductDefaultSupplier->product_supplier_reference_no}}
                        <?php 
                        }
                        else
                        {
                        ?>
                        {{'N.A'}}
                        <?php
                             }
                                }
                                else
                                {
                        ?>
                        {{'N.A'}}
                        <?php
                        }
                        ?>
                    </td>
                    @endif

                    @if(!in_array('16',$not_visible_arr))
                    <td>
                        <?php 
                            
                            if($item->warehouse_id != null)
                            {
                        ?>
                        {{$item->warehouse->warehouse_title}}
                        <?php
                            }
                        ?>
                    </td>
                    @endif

                    @if(!in_array('17',$not_visible_arr))
                    <td>
                        <?php
                            $notes = App\Models\Common\Order\OrderProductNote::select('note')->where('order_product_id', $item->id)->get();
                            if(count($notes)>0){
                                foreach ($notes as $note) {
                        ?>
                                 {{$note->note.','}}
                        <?php
                                }
                            }
                            else{
                        ?>
                                {{'--'}}
                        <?php
                            }
                        ?>
                    </td>
                    @endif
                    @if($getWarehouses->count() > 0)
                    @php 
                        $increment = 18; 
                    @endphp

                    @foreach($getWarehouses as $warehouse)
                        @if(!in_array($increment++,$not_visible_arr))
                            @php 
                                    $warehouse_product = $item->product->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                                    $available_qty = ($warehouse_product->available_quantity != null) ? $warehouse_product->available_quantity: 0;
                                    $a_qty = round($available_qty, 3);
                            @endphp
                            <td>{{$a_qty}}</td>
                        @endif
                    @endforeach
                    @endif

                </tr>
                    
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>