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
                <th> {{$global_terminologies['subcategory']}}</th>
                <th width="10%">{{$global_terminologies['product_description']}}</th>
                <th>Default/Last <br> Supplier</th>                  
                <th>Default Price</th>
                <th>Fixed Price</th>                      
                <th>Expiration Date</th>                      
              </tr>
            </thead>
            <tbody>
                @if($products->count() > 0)
                @foreach($products as $product)
                <tr>
                    <td>{{$product->refrence_code}}</td>
                    <td>{{$product->productCategory->title}}</td>
                    <td>{{$product->productSubCategory->title}}</td>
                    <td>{{$product->short_desc}}</td>
                    <td>{{$product->def_or_last_supplier->company}}</td>
                    @php
                      $getCustomer = \App\Models\Sales\Customer::find($customer_id);
                      $ctpmargin = \App\Models\Common\CustomerTypeProductMargin::where('product_id',$product->id)->where('customer_type_id',$getCustomer->category_id)->first();
                      $salePrice = $product->selling_price+($product->selling_price*($ctpmargin->default_value/100));
                      $formated_value = number_format($salePrice ,2,'.','');
                    @endphp  
                    <td>{{$formated_value}}</td>
                    <td></td>
                        @php
                          $date = Carbon\Carbon::now()->addDays(7)->format('Y-m-d');
                        @endphp
                    <td>{{$date}}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td>No Data Found</td>
                </tr>
                @endif
            </tbody> 
    
    </table>

    </body>
</html>
