<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
              <tr>
                <th>Stock Adjustment</th>                
                <th>Warehouse</th>                      
                <th>Default/Last <br> Supplier</th>                  
                <th>{{$global_terminologies['category']}}</th>
                <th>{{$global_terminologies['subcategory']}}</th>
                <th>{{$global_terminologies['type']}}</th>
                <th>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>

                <th width="10%">{{$global_terminologies['product_description']}}</th>
                <th>{{$global_terminologies['brand']}}</th>
                <th>Current Stock</th>
                <th>Unit</th>
                <th>Reorder {{$global_terminologies['qty']}}</th>
                <th>Reserved Qty</th>
                <th>Current Qty 1</th>
                <th>Adjust 1</th>   
                <th>Exp 1  <br> (dd/m/YYYY)</th>                      
                <th>Current Qty 2</th>
                <th>Adjust 2</th>
                <th>Exp 2  <br> (dd/m/YYYY)</th>                      
                <th>Current Qty 3</th>
                <th>Adjust 3</th>                      
                <th>Exp 3  <br> (dd/m/YYYY)</th>                      
              </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                <td>Stock Adjustment</td>  
                    <td>{{$warehouse->warehouse_title}}</td>
                    <td>{{@$product->def_or_last_supplier->reference_name}}</td>
                    <td>{{$product->productCategory->title}}</td>
                    <td>{{$product->productSubCategory->title}}</td>
                    <td>{{$product->productType->title}}</td>
                    <td>{{$product->productType2 != null ? $product->productType2->title : '--'}}</td>
                    <td>{{$product->refrence_code}}</td>
                    <td>{{$product->supplier_products[0]->product_supplier_reference_no}}</td>
                    <td>{{$product->short_desc}}</td>                    
                    <td>{{$product->brand != null ? $product->brand:''}}</td>                    
                    <td>{{$product->current_quantity != null ? round($product->current_quantity,3):0 }}</td>
                    <td>{{@$product->sellingUnits->title}}</td> 
                    <td>{{@$product->min_stock}}</td> 

                    @php
                        $reserved = $product->reserved_quantity != null ? round($product->reserved_quantity,3):0;
                        $ecom_reserved = $product->ecommerce_reserved_quantity != null ? round($product->ecommerce_reserved_quantity,3):0;
                    @endphp

                    <td>{{ $reserved + $ecom_reserved }}</td>
                    
                    @php
                        
                        $stock = $product->stock_in->where('warehouse_id',$warehouse->id);
                    @endphp
                    @foreach($stock as $s)
                    @php
                     
                      $stock_out_in = $s->stock_out->sum('quantity_in');
                      
                      $stock_out_out = $s->stock_out->sum('quantity_out');
                    @endphp
                    @if(round(($stock_out_in),3)+round(($stock_out_out),3) != 0)
                    <td>{{round(($stock_out_in+$stock_out_out),3)}}</td>
                    <td></td>
                    <td>{{$s->expiration_date != null ? Carbon\Carbon::parse($s->expiration_date)->format('d/m/Y') : $s->expiration_date}}</td>
                    @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody> 
    
    </table>

    </body>
</html>
