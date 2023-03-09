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
              <th>{{$global_terminologies['our_reference_number']}}</th>
              @if(@$allow_custom_code_edit == 1)
              <th>System Code</th>
              @else
              <th>{{$global_terminologies['our_reference_number']}}</th>
              @endif
              <th style="background-color: yellow;">Supplier</th>
              <th>{{$global_terminologies['supplier_description']}}</th>
              <th>Odering <br> Unit</th>
              <th>{{$global_terminologies['order_qty_unit']}}</th>
              <th>Supplier MOQ <br> (Minimum number <br> of Billed Unit)</th>
              <th style="background-color: yellow;">Supplier <br> Billed <br> Unit</th>
              <th style="background-color: yellow;">{{$global_terminologies['purchasing_price']}} <br>(EUR)</th>
              <th>{{$global_terminologies['gross_weight']}}</th>
              <th>Freight Per <br> Billed Unit</th>
              <th>Landing Per <br> Billed Unit</th>
              <th>Import Tax <br> Actual</th>
              <th>{{$global_terminologies['extra_cost_per_billed_unit']}}</th>
              <th>Extra Tax <br> (THB)</th>
              <th style="background-color: yellow;">Selling <br> Unit</th>
              <th style="background-color: yellow;">{{$global_terminologies['unit_conversion_rate']}}</th>
              <th style="background-color: yellow;">{{$global_terminologies['expected_lead_time_in_days']}}</th>
              <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>
              <th>{{$global_terminologies['brand']}}</th>
              <th style="background-color: yellow;">{{$global_terminologies['product_description']}}</th>
              <th>{{$global_terminologies['avg_units_for-sales']}}</th>
              <th>Stock <br> Unit</th>
              <th>MINIMUM <br> STOCK</th>
              <th style="background-color: yellow;">Primary <br>{{$global_terminologies['category']}}</th>
              <th style="background-color: yellow;">{{$global_terminologies['subcategory']}}</th>
              <th style="background-color: yellow;">Good <br>{{$global_terminologies['type']}}</th>
              <th style="background-color: yellow;">Good <br>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>
              <th style="background-color: yellow;">Good <br>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</th>
              <th>Good <br>{{$global_terminologies['temprature_c']}}</th>
              <!-- new column added -->
              <th>{{$global_terminologies['note_two']}}</th>
              <th>{{ $global_terminologies['order_qty_per_piece'] }}</th>
              <!-- new column added -->
              <?php foreach($customerCategory as $customerCat){ ?>
                <th><?php echo $customerCat->title ?> <br> Fixed <br> Price</th>
              <?php } ?>
              <th>Vat</th>
          </tr>
        </thead>
        <tbody>

        </tbody>

    </table>

    </body>
</html>
