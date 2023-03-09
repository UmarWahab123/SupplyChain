<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                <th>our_reference_number</th>
                @if(@$allow_custom_code_edit == 1)
                <th>system_code</th>
                @else
                <th>system_code</th>
                @endif
                <th>supplier</th>
                <th>supplier_description</th>
                <th>ordering_unit</th>
                <th>order_qty_unit</th>
                <th>m_o_q</th>
                <th>supplier_billed_unit</th>

                <th>purchasing_price_euro</th>
                <th>gross_weight</th>
                <th>freight</th>
                <th>landing</th>
                <th>import_tax_actual</th>
                <th>extra_cost_per_billed_unit</th>
                <th>extra_tax_thb</th>
                <th>selling_unit</th>
                <th>unit_conversion_rate</th>
                <th>expected_lead_time_in_days</th>
                <th>suppliers_product_reference_no</th>
                <th>brand</th>
                <th width="10%">product_description</th>
                <th>avg_units_for_sales</th>
                <th>stock_unit</th>
                <th>minimum_stock</th>
                <th>primary_category</th>
                <th>subcategory</th>
                <th>goods_type</th>
                <th>goods_type_2</th>
                <th>goods_type_3</th>
                <th>temprature_c</th>
                <th>note_two</th>
                <th>order_qty_per_piece</th>
                @if($customerCategory->count() > 0)
                    @foreach($customerCategory as $cat)
                    <th>{{$cat->title}}_fixed_prices</th>
                    @endforeach
                @endif
                <th>vat</th>
            </tr>

            <tr>
                <th>{{$global_terminologies['our_reference_number']}} </th>
                @if(@$allow_custom_code_edit == 1)
                <th>System Code</th>
                @else
                <th>{{$global_terminologies['our_reference_number']}}</th>
                @endif
                <th>Supplier</th>
                <th>{{$global_terminologies['supplier_description']}}</th>
                <th>Ordering <br>Unit</th>
                <th>{{$global_terminologies['order_qty_unit']}}</th>
                <th>Supplier MOQ <br> (Minimum number <br> of Billed Unit)</th>
                <th>Supplier <br> Billed Unit</th>

                <th>{{$global_terminologies['purchasing_price']}} <br>(EUR)</th>
                <th>{{$global_terminologies['gross_weight']}}</th>
                <th> Freight <br> Per<br> Billed Unit </th>
                <th> Landing <br> Per<br> Billed Unit </th>
                <th>Import <br> Tax <br> Actual</th>
                <th>{{$global_terminologies['extra_cost_per_billed_unit']}}</th>
                <th>Extra Tax (THB)</th>
                <th>Selling<br> Unit</th>
                <th>{{$global_terminologies['unit_conversion_rate']}}</th>
                <th>{{$global_terminologies['expected_lead_time_in_days']}}</th>
                <th>{{$global_terminologies['suppliers_product_reference_no']}} </th>
                <th>{{$global_terminologies['brand']}}</th>
                <th width="10%">{{$global_terminologies['product_description']}} </th>
                <th>{{$global_terminologies['avg_units_for-sales']}}</th>
                <th>Stock<br> Unit</th>
                <th> MINIMUM <br>STOCK </th>
                <th>Primary<br> {{$global_terminologies['category']}} </th>
                <th>{{$global_terminologies['subcategory']}}</th>
                <th>Good <br>{{$global_terminologies['type']}}</th>
                <th>Good <br>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>
                <th>Good <br>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</th>
                <th>Good <br>{{$global_terminologies['temprature_c']}} </th>
                <th>{{$global_terminologies['note_two']}}</th>
                <th>{{ $global_terminologies['order_qty_per_piece'] }}</th>
                @if($customerCategory->count() > 0)
                    @foreach($customerCategory as $cat)
                    <th>{{$cat->title}} <br> Fixed <br> Prices</th>
                    @endforeach
                @endif
                <th>Vat</th>
            </tr>
            </thead>

            <tbody>
            @foreach($products as $product)
            @if ($sup_id != '' && $sup_id != null)

            <tr>
                <td>{{$product->refrence_code}}</td>
                <td>{{$product->system_code}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->supplier->reference_name}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->supplier_description}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->supplier_packaging}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->billed_unit}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->m_o_q}}</td>
                <td>{{$product->units->title}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->buying_price}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->gross_weight}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->freight}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->landing}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->import_tax_actual}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->extra_cost}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->extra_tax}}</td>
                <td>{{$product->sellingUnits->title}}</td>
                <td>{{$product->unit_conversion_rate}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->leading_time}}</td>
                <td>{{$product->supplier_products->where('supplier_id',$sup_id)->first()->product_supplier_reference_no}}</td>
                <td>{{$product->brand}}</td>
                <td>{{$product->short_desc}}</td>
                <td>{{$product->weight}}</td>
                <td>{{$product->stockUnit != null ? $product->stockUnit->title:''}}</td>
                <td>{{$product->min_stock}}</td>
                <td>{{$product->productCategory->title}}</td>
                <td>{{$product->productSubCategory->title}}</td>
                <td>{{$product->productType->title}}</td>
                <td>{{$product->productType2 != null ? $product->productType2->title : ''}}</td>
                <td>{{$product->productType3 != null ? $product->productType3->title : ''}}</td>
                <td>{{$product->product_temprature_c}}</td>
                <td>{{$product->product_notes}}</td>
                <td>{{$product->order_qty_per_piece}}</td>
                @foreach($customerCategory as $cust)
                    <td>{{$product->product_fixed_price != null ? ($product->product_fixed_price->where('customer_type_id',$cust->id)->first() != null ? $product->product_fixed_price->where('customer_type_id',$cust->id)->first()->fixed_price : 'N.A') : 'N.A'}}</td>
                @endforeach
                <td>{{$product->vat}}</td>
            </tr>

            @else

            <tr>
                <td>{{$product->refrence_code}}</td>
                <td>{{$product->system_code}}</td>
                <td>{{$product->supplier_products->first()->supplier->reference_name}}</td>
                <td>{{$product->supplier_products->first()->supplier_description}}</td>
                <td>{{$product->supplier_products->first()->supplier_packaging}}</td>
                <td>{{$product->supplier_products->first()->billed_unit}}</td>
                <td>{{$product->supplier_products->first()->m_o_q}}</td>
                <td>{{$product->units->title}}</td>
                <td>{{$product->supplier_products->first()->buying_price}}</td>
                <td>{{$product->supplier_products->first()->gross_weight}}</td>
                <td>{{$product->supplier_products->first()->freight}}</td>
                <td>{{$product->supplier_products->first()->landing}}</td>
                <td>{{$product->supplier_products->first()->import_tax_actual}}</td>
                <td>{{$product->supplier_products->first()->extra_cost}}</td>
                <td>{{$product->supplier_products->first()->extra_tax}}</td>
                <td>{{$product->sellingUnits->title}}</td>
                <td>{{$product->unit_conversion_rate}}</td>
                <td>{{$product->supplier_products->first()->leading_time}}</td>
                <td>{{$product->supplier_products->first()->product_supplier_reference_no}}</td>
                <td>{{$product->brand}}</td>
                <td>{{$product->short_desc}}</td>
                <td>{{$product->weight}}</td>
                <td>{{$product->stockUnit != null ? $product->stockUnit->title:''}}</td>
                <td>{{$product->min_stock}}</td>
                <td>{{$product->productCategory->title}}</td>
                <td>{{$product->productSubCategory->title}}</td>
                <td>{{$product->productType->title}}</td>
                <td>{{$product->productType2 != null ? $product->productType2->title : ''}}</td>
                <td>{{$product->productType3 != null ? $product->productType3->title : ''}}</td>
                <td>{{$product->product_temprature_c}}</td>
                <td>{{$product->product_notes}}</td>
                <td>{{$product->order_qty_per_piece}}</td>
                @foreach($customerCategory as $cust)
                    <td>{{$product->product_fixed_price != null ? ($product->product_fixed_price->where('customer_type_id',$cust->id)->first() != null ? $product->product_fixed_price->where('customer_type_id',$cust->id)->first()->fixed_price : 'N.A') : 'N.A'}}</td>
                @endforeach
                <td>{{$product->vat}}</td>
            </tr>

            @endif

            @endforeach
            </tbody>

    </table>

    </body>
</html>
