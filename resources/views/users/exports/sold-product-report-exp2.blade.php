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
            @if(!in_array('0',$not_visible_arr))<th><b>Order#</b></th>@endif
            @if(!in_array('1',$not_visible_arr))<th><b>Status</b></th>@endif
            @if(!in_array('2',$not_visible_arr))<th><b>Ref. Po#</b></th>@endif
            @if(!in_array('3',$not_visible_arr))<th><b>PO #</b></th>@endif
            @if(!in_array('4',$not_visible_arr))<th><b>Customer</b></th>@endif
            @if(!in_array('5',$not_visible_arr))<th><b>Sale Person</b></th>@endif
            @if(!in_array('6',$not_visible_arr))<th><b>Delivery Date</b></th>@endif
            @if(!in_array('7',$not_visible_arr))<th><b>Created Date</b></th>@endif
            @if(!in_array('8',$not_visible_arr))<th><b>{{$global_terminologies['supply_from']}}</b></th>@endif
            @if(!in_array('9',$not_visible_arr))<th><b>{{$global_terminologies['our_reference_number']}}</b></th>@endif
            @if(!in_array('10',$not_visible_arr))<th><b>{{$global_terminologies['category']}} / {{$global_terminologies['subcategory']}}</b></th>@endif
            @if(!in_array('11',$not_visible_arr))<th><b>Product Type</b></th>@endif
            @if(!in_array('12',$not_visible_arr))<th><b>{{$global_terminologies['brand']}}</b></th>@endif
            @if(!in_array('13',$not_visible_arr))<th><b>{{$global_terminologies['product_description']}}</b></th>@endif
            @if(!in_array('14',$not_visible_arr))<th><b>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</b></th>@endif
            @if(!in_array('15',$not_visible_arr))<th><b>{{$available_stock}}</b></th>@endif
            @if(!in_array('16',$not_visible_arr))<th><b>Selling Unit</b></th>@endif
            @if(!in_array('17',$not_visible_arr))<th><b>{{$global_terminologies['qty']}}</b></th>@endif
            @if(!in_array('18',$not_visible_arr))<th><b>Pieces</b></th>@endif
            @if(!in_array('19',$not_visible_arr))<th><b>Unit Price</b></th>@endif
            @if(!in_array('20',$not_visible_arr))<th><b>Discount %</b></th>@endif
            @if($role_id != 3 && $role_id != 4 && $role_id != 6)
            @if(!in_array('21',$not_visible_arr))<th><b>{{$global_terminologies['net_price']}} (THB)</b></th>@endif
            @if(!in_array('22',$not_visible_arr))<th><b>Total {{$global_terminologies['net_price']}} / unit (THB)</b></th>@endif
            @endif
            @if(!in_array('23',$not_visible_arr))<th><b>Sub Total</b></th>@endif
            @if(!in_array('24',$not_visible_arr))<th><b>Total Amount</b></th>@endif
            @if(!in_array('25',$not_visible_arr))<th><b>Vat(THB)</b></th>@endif
            @if(!in_array('26',$not_visible_arr))<th><b>Vat %</b></th>@endif
            @if(!in_array('27',$not_visible_arr))<th><b>{{$global_terminologies['note_two']}}</b></th>@endif

            @if($getCategories->count() > 0)
                @php $inc = 28; @endphp
                @foreach($getCategories as $cat)
                @if(!in_array($inc,$not_visible_arr)) <th style="font-weight: bold;">{{$cat->title}}<br>( Fixed Price )</th>@endif
                @php $inc+=1; @endphp
                @endforeach
                @endif
          </tr>
        </thead>
            <tbody>
                 @foreach($query->chunk(3000) as $all)
                @foreach($all as $item)
                  @if ($item->qty != 0 && $item->qty > 0)
                  <tr>
                    @if(!in_array('0',$not_visible_arr))<td>{{$item->order_no}}</td>@endif
                    @if(!in_array('1',$not_visible_arr))<td>{{$item->status}}</td>@endif
                    @if(!in_array('2',$not_visible_arr))<td>{{$item->ref_po_no}}</td>@endif
                    @if(!in_array('3',$not_visible_arr))<td>{{$item->po_no}}</td>@endif
                    @if(!in_array('4',$not_visible_arr))<td>{{$item->customer}}</td>@endif
                    @if(!in_array('5',$not_visible_arr))<td>{{$item->sale_person}}</td>@endif
                    @if(!in_array('6',$not_visible_arr))<td>{{$item->delivery_date}}</td>@endif
                    @if(!in_array('7',$not_visible_arr))<td>{{$item->created_date}}</td>@endif
                    @if(!in_array('8',$not_visible_arr))<td>{{$item->supply_from}}</td>@endif
                    @if(!in_array('9',$not_visible_arr))<td>{{$item->p_ref}}</td>@endif
                    @if(!in_array('10',$not_visible_arr))<td>{{$item->category}}</td>@endif
                    @if(!in_array('11',$not_visible_arr))<td>{{$item->product_type}}</td>@endif
                    @if(!in_array('12',$not_visible_arr))<td>{{$item->brand}}</td>@endif
                    @if(!in_array('13',$not_visible_arr))<td>{{$item->item_description}}</td>@endif
                    @if(!in_array('14',$not_visible_arr))<td>{{$item->vintage}}</td>@endif
                    @if(!in_array('15',$not_visible_arr))<td>{{$item->available_stock}}</td>@endif
                    @if(!in_array('16',$not_visible_arr))<td>{{$item->selling_unit}}</td>@endif
                    @if(!in_array('17',$not_visible_arr))<td>{{$item->qty}}</td>@endif
                    @if(!in_array('18',$not_visible_arr))<td>{{$item->piece}}</td>@endif
                    @if(!in_array('19',$not_visible_arr))<td>{{$item->unit_price}}</td>@endif
                    @if(!in_array('20',$not_visible_arr))<td>{{$item->discount}}</td>@endif
                    
                    @if($role_id != 3 && $role_id != 4 && $role_id != 6)
                    @if(!in_array('21',$not_visible_arr))<td>{{$item->cogs}}</td>@endif
                    @if(!in_array('22',$not_visible_arr))<td>{{$item->total_cogs}}</td>@endif
                    @endif
  
                    @if(!in_array('23',$not_visible_arr))<td>{{$item->total_price_without_vat}}</td>@endif
                    @if(!in_array('24',$not_visible_arr))<td>{{$item->total_amount}}</td>@endif
                    @if(!in_array('25',$not_visible_arr))<td>{{$item->vat_thb}}</td>@endif
                    @if(!in_array('26',$not_visible_arr))<td>{{$item->vat}}</td>@endif
                    @if(!in_array('27',$not_visible_arr))<td>{{$item->notes}}</td>@endif

                    {{-- customer categories --}}
                @if($getCategories->count() > 0)
                @php 
                    $arr_index = 0; 
                    $increment = 28; 
                    $customer_categories_array = unserialize($item->customer_categories_array);
                    $ids=[];
                @endphp

                @foreach($getCategories as $cat)

                @php
                    //$order_ids=Order::where('primary_status',2)->whereHas('user',function($qq) use($warehouse){
                    //$qq->where('warehouse_id',$warehouse->id);
                    //})->pluck('id')->toArray();
                    $current_qty  = substr($cat->title, 0, 3);
                @endphp
                @if(array_key_exists($arr_index,$customer_categories_array))
                    
                    @if(!in_array($increment,$not_visible_arr)) 
                    <td>
                        {{$customer_categories_array[$arr_index] != null ? number_format((float)$customer_categories_array[$arr_index], 3, '.', ''):'0'}}    
                    </td>
                    @endif
                @endif
                
                @php 
                    $increment+=1; 
                    $arr_index++; 
                @endphp
                
                @endforeach
                @endif
                  </tr>
                  @endif    
                @endforeach
                @endforeach
            </tbody> 
    
    </table>

    </body>
</html>
