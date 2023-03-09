<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                @if(!in_array('1',$not_visible_arr))<th style="font-weight: bold;">{{$global_terminologies['our_reference_number']}}</th>@endif
                @if(!in_array('2',$not_visible_arr))<th style="font-weight: bold;">@if(!array_key_exists('type', $global_terminologies)) Product Type @else {{$global_terminologies['type']}} @endif</th>@endif
                @if(!in_array('3',$not_visible_arr))<th style="font-weight: bold;">@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>@endif
                @if(!in_array('4',$not_visible_arr))<th style="font-weight: bold;">{{$global_terminologies['brand']}}</th>@endif
                @if(!in_array('5',$not_visible_arr))<th style="font-weight: bold;">{{$global_terminologies['product_description']}}</th>@endif
                @if(!in_array('6',$not_visible_arr))<th style="font-weight: bold;">Selling<br>Unit</th>@endif
                @if(!in_array('7',$not_visible_arr))<th style="font-weight: bold;">Total <br>{{$global_terminologies['qty']}}</th>@endif
                @if(!in_array('8',$not_visible_arr))<th style="font-weight: bold;">Total <br>Pieces</th>@endif
                @if(!in_array('9',$not_visible_arr))<th style="font-weight: bold;">Sub Total</th>@endif
                @if(!in_array('10',$not_visible_arr))<th style="font-weight: bold;">Total<br>Amount</th>@endif
                @if(!in_array('11',$not_visible_arr))<th style="font-weight: bold;">Vat(THB)</th>@endif
                @if(!in_array('12',$not_visible_arr))<th style="font-weight: bold;">Total <br>Stock</th>@endif
                @if(!in_array('13',$not_visible_arr))<th style="font-weight: bold;">Total <br>Visible Stock</th>@endif
                @if($role_id == 1 || $role_id == 2 || $role_id == 7)
                @if(!in_array('14',$not_visible_arr))<th style="font-weight: bold;">
                {{$global_terminologies['net_price']}} <br> (THB)</th>@endif
                @endif


                @if($role_id == 1 || $role_id == 2 || $role_id == 7)
                @if(!in_array('15',$not_visible_arr))<th style="font-weight: bold;">
                Total {{$global_terminologies['net_price']}} <br> (THB)</th>
                @endif
                @endif
               

                @php $key=16; @endphp
                @if($getWarehouses->count() > 0)
                @foreach($getWarehouses as $warehouse)
                @if(!in_array($key,$not_visible_arr))<th style="font-weight: bold;">{{$warehouse->warehouse_title}}<br>{{$global_terminologies['current_qty']}}</th>@endif
                @php $key++; @endphp
                @endforeach
                @endif
                @if($getCategories->count() > 0)
                @foreach($getCategories as $cat)
                @if(!in_array($key,$not_visible_arr))<th style="font-weight: bold;">{{$cat->title}}<br>( Fixed Price )</th>@endif
                @php $key++; @endphp
                @endforeach
                @endif
            </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                    @if(!in_array('1',$not_visible_arr))<td>{{$item->refrence_code}}</td>@endif
                    @if(!in_array('2',$not_visible_arr))<td>{{$item->productType != null ? $item->productType->title : '--'}}</td>@endif
                    @if(!in_array('3',$not_visible_arr))<td>{{$item->productType2 != null ? $item->productType2->title : '--'}}</td>@endif
                    @if(!in_array('4',$not_visible_arr))<td>{{$item->brand != null ? $item->brand : '--'}}</td>@endif
                    @if(!in_array('5',$not_visible_arr))<td>{{$item->short_desc}}</td>@endif
                    @if(!in_array('6',$not_visible_arr))<td>{{@$item->sellingUnits->title}}</td>@endif
                    @if(!in_array('7',$not_visible_arr))<td>{{number_format($item->QuantityText,2,'.','')}}</td>@endif
                    @if(!in_array('8',$not_visible_arr))<td>{{number_format($item->PiecesText,2,'.','')}}</td>@endif
                    @if(!in_array('9',$not_visible_arr))<td>{{number_format($item->totalPriceSub,2,'.','')}}</td>@endif
                    @if(!in_array('10',$not_visible_arr))<td>{{number_format($item->TotalAmount,2,'.','')}}</td>@endif
                    @if(!in_array('11',$not_visible_arr))<td>{{$item->VatTotalAmount != null ? number_format($item->VatTotalAmount,2,'.','') : '--'}}</td>@endif
                    @if(!in_array('12',$not_visible_arr))<td>{{number_format($item->warehouse_products()->sum('current_quantity'),2,'.','')}}</td>@endif
                     @if(!in_array('13',$not_visible_arr))<td>
                        <?php 
                            $visible = 0;
                              $start = 14;
                              foreach ($getWarehouses as $warehouse) {
                                if(!in_array($start,$not_visible_arr))
                                {
                                   $warehouse_product = $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                                  $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
                                  $visible += $qty;
                                }

                                $start += 1;
                              } 
                        ?>
                        {{number_format($visible,2,'.','')}}</td>
                     @endif
                     @if($role_id == 1 || $role_id == 2 || $role_id == 7)
                     @if(!in_array('14',$not_visible_arr))
                     <td>
                        {{(@$item->selling_price!=null)?number_format((float)@$item->selling_price, 3, '.', ''):'N/A'}} / {{@$item->sellingUnits->title}}
                     </td>
                     @endif
                     @endif

                    
                    @if($role_id == 1 || $role_id == 2 || $role_id == 7)
                     @if(!in_array('15',$not_visible_arr))
                     <td>
                        {{(@$item->selling_price!=null)?number_format((float)(@$item->totalCogs), 2, '.', ''):'N/A'}} / {{@$item->sellingUnits->title}}
                     </td>
                     @endif
                     @endif

                       {{-- customer categories --}}
                       @php $key=16; @endphp

                    @if($getWarehouses->count() > 0)
                    @foreach($getWarehouses as $warehouse)
                    @php
                        $warehouse_product = $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                        $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
                    @endphp
                    @if(!in_array($key,$not_visible_arr))<td>{{number_format($qty,2,'.','')}}</td>@endif
                    @php $key++; @endphp
                    @endforeach
                    @endif

                    @if($getCategories->count() > 0)
                    @foreach($getCategories as $cat)
                    @php
                      $fixed_value = $item->product_fixed_price()->where('product_id',$item->id)->where('customer_type_id',$cat->id)->first();
                      $value = $fixed_value != null ? $fixed_value->fixed_price : 0; 
                      $va = number_format($value,3,'.','');
                    @endphp
                    @if(!in_array($key,$not_visible_arr))<td>{{$va}}</td>@endif
                    @php $key++; @endphp
                    @endforeach
                    @endif
                </tr>
                @endforeach
            </tbody> 
    
    </table>

    </body>
</html>