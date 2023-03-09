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
                @if(!in_array('2',$not_visible_arr))<th style="font-weight: bold;">
                    {{$global_terminologies['our_reference_number']}}</th>@endif
                @if($hide_hs_description==0)
                
                @if(!in_array('3',$not_visible_arr))<th  style="font-weight: bold;">
                Hs Description</th>@endif
                @endif
                @if(!in_array('4',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['suppliers_product_reference_no']}}</th>@endif
                @if(!in_array('5',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['category']}}/{{$global_terminologies['subcategory']}}</th>@endif
                @if(!in_array('6',$not_visible_arr))<th style="font-weight: bold;">
                    {{$global_terminologies['product_description']}}</th>@endif
                {{-- @if(!in_array('7',$not_visible_arr))<th style="font-weight: bold;">Picture</th>@endif --}}
                {{-- @if(!in_array('8',$not_visible_arr))<th style="font-weight: bold;">Note</th>@endif --}}
                @if(!in_array('9',$not_visible_arr)) <th style="font-weight: bold;">Billed Unit</th>@endif
                @if(!in_array('10',$not_visible_arr)) <th style="font-weight: bold;">Selling Unit</th>@endif
                @if(!in_array('11',$not_visible_arr)) <th style="font-weight: bold;">{{$global_terminologies['type']}}</th>@endif
                @if(!in_array('12',$not_visible_arr)) <th style="font-weight: bold;">@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>@endif
                @if(!in_array('13',$not_visible_arr))<th style="font-weight: bold;">{{$global_terminologies['brand']}}</th>@endif
                @if(!in_array('14',$not_visible_arr))<th style="font-weight: bold;">{{$global_terminologies['temprature_c']}}</th>
                @endif 
                @if(!in_array('15',$not_visible_arr)) <th style="font-weight: bold;">Import Tax (Book) %</th>@endif
                @if(!in_array('16',$not_visible_arr)) <th style="font-weight: bold;">VAT</th>@endif
                @if(!in_array('17',$not_visible_arr)) <th style="font-weight: bold;">Default/Last Supplier</th>@endif
                @if(!in_array('18',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['supplier_description']}}</th>@endif
                @if(!in_array('19',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['purchasing_price']}} (EUR)</th>@endif
                @if(!in_array('20',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['purchasing_price']}} (THB)</th>@endif
                @if(!in_array('21',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['freight_per_billed_unit']}}</th>@endif
                @if(!in_array('22',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['landing_per_billed_unit']}}</th>@endif
                @if(!in_array('23',$not_visible_arr)) <th style="font-weight: bold;">{{$global_terminologies['cost_price']}}</th>@endif
               
                @if(!in_array('24',$not_visible_arr))<th style="font-weight: bold;">
                    {{$global_terminologies['unit_conversion_rate']}}</th>@endif


                @if(!in_array('25',$not_visible_arr)) <th style="font-weight: bold;">{{$global_terminologies['net_price']}} /unit (THB)</th>@endif

                @if(!in_array('26',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['avg_units_for-sales']}}</th>@endif
                @if(!in_array('27',$not_visible_arr)) <th style="font-weight: bold;">
                    {{$global_terminologies['expected_lead_time_in_days']}}</th>@endif
                @if(!in_array('28',$not_visible_arr)) <th style="font-weight: bold;">Last Update Price</th>@endif
                @if(!in_array('29',$not_visible_arr)) <th style="font-weight: bold;">Total Visible Stock</th>@endif
                @if(!in_array('30',$not_visible_arr)) <th style="font-weight: bold;">On Water</th>@endif
                <!-- @if(!in_array('30',$not_visible_arr)) <th style="font-weight: bold;">On Airplane</th>@endif
                @if(!in_array('31',$not_visible_arr)) <th style="font-weight: bold;">On Delivery</th>@endif -->


                @if($getWarehouses->count() > 0)
                @php $inc = 31; @endphp
                @foreach($getWarehouses as $warehouse)

                @if(!in_array($inc,$not_visible_arr)) <th style="font-weight: bold;">{{$warehouse->warehouse_title}}<br>{{$global_terminologies['current_qty']}}</th>@endif
                @if(!in_array($inc+1,$not_visible_arr)) <th style="font-weight: bold;">{{$warehouse->warehouse_title}}<br> Available <br> Qty</th>@endif
                @if(!in_array($inc+2,$not_visible_arr)) <th style="font-weight: bold;">{{$warehouse->warehouse_title}}<br> Reserved <br> Qty</th>@endif
                
                @php $inc+=3; @endphp
                @endforeach
                @endif

                @if($getCategories->count() > 0)
                
                @foreach($getCategories as $cat)
                @if(!in_array($inc,$not_visible_arr)) <th style="font-weight: bold;">{{$cat->title}}<br>( Fixed Price )</th>@endif
                @php $inc+=1; @endphp
                @endforeach
                @endif

                @if($getCategoriesSuggestedPrices->count() > 0)
                @foreach($getCategoriesSuggestedPrices as $cat)
                @if(!in_array($inc,$not_visible_arr)) <th style="font-weight: bold;">{{$cat->title}}<br>( Suggested Price )</th>@endif
                @php $inc+=1; @endphp
                @endforeach
                @endif

                
            </tr>
        </thead>
        <tbody>
<?php

?>
            @foreach($records as $item)
            <tr>
                @if(!in_array('2',$not_visible_arr))
                <td>
                    {{$item->refrence_code != null ? $item->refrence_code: "--"}}
                </td>
                @endif
                @if($hide_hs_description==0)
                    @if(!in_array('3',$not_visible_arr))
                        <td>
                            {{$item->hs_description != null ? $item->hs_description: "--"}}
                        </td>
                    @endif
                @endif
                @if(!in_array('4',$not_visible_arr)) 
                <td>
                    {{$item->product_supplier_reference_no != null ? $item->product_supplier_reference_no:'--'}}
                </td>
                @endif
                @if(!in_array('5',$not_visible_arr)) 
                <td>
                    {{$item->primary_category_title != null ? $item->primary_category_title.' / '.$item->category_title: "--"}}
                </td>
                @endif
                @if(!in_array('6',$not_visible_arr)) 
                <td>
                    {{$item->short_desc != null ? $item->short_desc: "--"}}
                </td>
                @endif
                {{-- @if(!in_array('7',$not_visible_arr))<td>

                  </td> --}}
                   {{-- @if(!in_array('8',$not_visible_arr))<td>
                    Note
                  </td> --}}

                @if(!in_array('9',$not_visible_arr)) 
                <td>
                    {{$item->buying_unit_title != null ? $item->buying_unit_title : '--'}}
                </td>
                @endif
                @if(!in_array('10',$not_visible_arr)) 
                <td>
                    {{$item->selling_unit_title != null ? $item->selling_unit_title : '--'}}
                </td>
                @endif
                @if(!in_array('11',$not_visible_arr)) 
                <td>
                    {{$item->type_title != null ? $item->type_title : '--'}}
                </td>
                @endif
                @if(!in_array('12',$not_visible_arr)) 
                <td>
                    {{$item->type_id != null ? $item->type_id : '--'}}
                </td>
                @endif
                @if(!in_array('13',$not_visible_arr)) 
                <td>
                    {{$item->brand != null ? $item->brand : '--' }}
                </td>
                @endif
                @if(!in_array('14',$not_visible_arr)) 
                <td>
                    {{$item->product_temprature_c != null ? $item->product_temprature_c : '--' }}
                </td>
                @endif
                @if(!in_array('15',$not_visible_arr)) 
                <td>
                    {{$item->import_tax_book != null ? $item->import_tax_book.' %': "--"}}
                </td>
                @endif
                @if(!in_array('16',$not_visible_arr)) 
                <td>
                    {{$item->vat != null ? $item->vat.' %': "--"}}
                </td>
                @endif
                @if(!in_array('17',$not_visible_arr)) 
                <td>
                    {{$item->default_supplier != null ? $item->default_supplier:'--'}}
                </td>
                @endif
                @if(!in_array('18',$not_visible_arr)) 
                <td>
                    {{$item->supplier_description != null ? $item->supplier_description:'--'}}
                </td>
                @endif
                @if(!in_array('19',$not_visible_arr)) 
                <td>
                    {{$item->purchasing_price_eur != null ? $item->purchasing_price_eur:'--'}}/{{$item->currency_symbol != null ? $item->currency_symbol:'--'}}
                </td>
                @endif
                @if(!in_array('20',$not_visible_arr)) 
                <td>
                    {{$item->purchasing_price_thb != null ? $item->purchasing_price_thb:'--'}}
                </td>
                @endif
                @if(!in_array('21',$not_visible_arr)) 
                <td>
                    {{$item->freight != null ? $item->freight:'--'}}
                </td>
                @endif
                @if(!in_array('22',$not_visible_arr)) 
                <td>
                    {{$item->landing != null ? $item->landing:'--'}}
                </td>@endif

                @if(!in_array('23',$not_visible_arr)) 
                <td>
                    {{$item->total_buy_unit_cost_price != null ? number_format((float)$item->total_buy_unit_cost_price, 3, '.', ''):'--'}}
                </td>
                @endif
               
                @if(!in_array('24',$not_visible_arr)) 
                <td>
                    {{$item->unit_conversion_rate != null ? number_format((float)$item->unit_conversion_rate, 3, '.', '') : '--'}}
                </td>
                @endif
                @if(!in_array('25',$not_visible_arr)) 
                <td>
                    {{$item->selling_price != null ? number_format((float)$item->selling_price, 3, '.', ''):'--'}}
                </td>
                @endif
                @if(!in_array('26',$not_visible_arr)) 
                <td>
                    {{$item->weight != null ? $item->weight : '--'}}
                </td>
                @endif
                @if(!in_array('27',$not_visible_arr)) 
                <td>
                    {{$item->leading_time!= null ? $item->leading_time: '--'}}
                </td>
                @endif
                @if(!in_array('28',$not_visible_arr)) 
                <td>
                    {{$item->last_price_updated_date!= null ? Carbon::parse($item->last_price_updated_date)->format('d/m/Y') : '--'}}
                </td>
                @endif

                @if(!in_array('29',$not_visible_arr)) 
                <td>
                    {{$item->total_visible_stock != null ? number_format((float)$item->total_visible_stock, 3, '.', '') : '--'}}
                </td>
                @endif

                @if(!in_array('30',$not_visible_arr)) 
                <td>
                    {{$item->on_water !== null ? $item->on_water : '0'}}
                </td>
                @endif

                <!-- @if(!in_array('30',$not_visible_arr)) 
                <td>
                    {{$item->on_airplane !== null ? $item->on_airplane : '0'}}
                </td>
                @endif

                @if(!in_array('31',$not_visible_arr)) 
                <td>
                    {{$item->on_domestic !== null ? $item->on_domestic : '0'}}
                </td>
                @endif -->


                @if($getWarehouses->count() > 0)
                @php 
                    $increment = 31; 
                    $arr_index = 0; 
                    $warehosue_c_r_array = unserialize($item->warehosue_c_r_array);
                    $ids=[];
                @endphp

                @foreach($getWarehouses as $warehouse)

                @php
                    //$order_ids=Order::where('primary_status',2)->whereHas('user',function($qq) use($warehouse){
                    //$qq->where('warehouse_id',$warehouse->id);
                    //})->pluck('id')->toArray();
                    $current_qty  = substr($warehouse->warehouse_title, 0, 3).'_current_qty';
                    $available_qty = substr($warehouse->warehouse_title, 0, 3).'_available_qty';
                    $reserved_qty = substr($warehouse->warehouse_title, 0, 3).'_reserved_qty';
                @endphp
                @if(array_key_exists($arr_index,$warehosue_c_r_array))
                    
                    @if(!in_array($increment,$not_visible_arr)) 
                    <td>
                        {{$warehosue_c_r_array[$arr_index][$current_qty] != null ? number_format((float)$warehosue_c_r_array[$arr_index][$current_qty], 3, '.', ''):'0'}}    
                    </td>
                    @endif
                    @if(!in_array($increment+1,$not_visible_arr)) 
                    <td>
                        {{$warehosue_c_r_array[$arr_index][$available_qty]!= null ? number_format((float)$warehosue_c_r_array[$arr_index][$available_qty], 3, '.', ''):'0'}}
                   
                    </td>
                    @endif
                    @if(!in_array($increment+2,$not_visible_arr)) 
                    <td>
                        {{$warehosue_c_r_array[$arr_index][$reserved_qty]!= null ? number_format((float)$warehosue_c_r_array[$arr_index][$reserved_qty], 3, '.', ''):'0'}}
                        
                    </td>
                    @endif
                @endif
                
                @php 
                    $increment+=3; 
                    $arr_index++; 
                @endphp
                
                @endforeach
                @endif

                {{-- customer categories --}}
                @if($getCategories->count() > 0)
                @php 
                    $arr_index = 0; 
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
                        {{$customer_categories_array[$arr_index][$current_qty] != null ? number_format((float)$customer_categories_array[$arr_index][$current_qty], 3, '.', ''):'0'}}    
                    </td>
                    @endif
                @endif
                
                @php 
                    $increment+=1; 
                    $arr_index++; 
                @endphp
                
                @endforeach
                @endif

                {{-- customer categories suggested prices --}}
                @if($getCategoriesSuggestedPrices->count() > 0)
                @php 
                    $arr_index = 0; 
                    $customer_suggested_prices_array = unserialize($item->customer_suggested_prices_array);
                    $ids=[];
                @endphp

                @foreach($getCategoriesSuggestedPrices as $cat)

                @php
                    //$order_ids=Order::where('primary_status',2)->whereHas('user',function($qq) use($warehouse){
                    //$qq->where('warehouse_id',$warehouse->id);
                    //})->pluck('id')->toArray();
                    $current_qty  = substr($cat->title, 0, 3);
                @endphp
                @if(array_key_exists($arr_index,$customer_suggested_prices_array))
                    
                    @if(!in_array($increment,$not_visible_arr)) 
                    <td>
                        {{$customer_suggested_prices_array[$arr_index][$current_qty] != null ? number_format((float)$customer_suggested_prices_array[$arr_index][$current_qty], 3, '.', ''):'0'}}    
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

            @endforeach

        </tbody>

    </table>

</body>

</html>
