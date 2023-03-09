<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
              <tr>                
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th>{{$global_terminologies['category']}}</th>
                <th>{{$global_terminologies['subcategory']}}</th>
                <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>                  
                <th width="10%">{{$global_terminologies['product_description']}}</th>
                <th>Default/Last <br> Supplier</th>                  
                <th>{{$global_terminologies['purchasing_price']}}</th>
                <th>{{$global_terminologies['expected_lead_time_in_days']}}</th>
              </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                @php
                $product_supplier = \App\Models\Common\SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->def_or_last_supplier->id)->first();
                @endphp
                <tr>
                    <td>{{$product->refrence_code}}</td>
                    <td>{{$product->productCategory->title}}</td>
                    <td>{{$product->productSubCategory->title}}</td>
                    <td>{{$product_supplier->product_supplier_reference_no}}</td>
                    <td>
                    @if($product_supplier->supplier_description)
                        {{ $product_supplier->supplier_description }}
                    @else
                        {{ $product->short_desc }}
                    @endif
                    </td>
                    <td>{{$product->def_or_last_supplier->company}}</td>
                    <td>
                        {{$product_supplier->buying_price}}
                    </td>
                    <td>{{$product_supplier->leading_time}}</td>
                </tr>
                @endforeach
            </tbody> 
    
    </table>

    </body>
</html>
