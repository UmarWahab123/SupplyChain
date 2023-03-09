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
          <!-- Row to represent each column with name  -->
            <tr>
            @foreach($col_display_pref as $col_display)
              @if($col_display == "1")
                @if(!in_array('1',$not_visible_arr))<th d-id="1">po_no</th>@endif
              @endif

              @if($col_display == "2")
                @if(!in_array('2',$not_visible_arr))<th d-id="2">order_warehouse</th>@endif
              @endif

              @if($col_display == "3")
                @if(!in_array('3',$not_visible_arr))<th d-id="3">order_no</th>@endif
              @endif

              @if($col_display == "4")
                @if(!in_array('4',$not_visible_arr))<th d-id="4">sup_ref_no</th>@endif
              @endif

              @if($col_display == "5")
                @if(!in_array('5',$not_visible_arr))<th d-id="5">sup_name</th>@endif
              @endif

              @if($col_display == "6")
                @if(!in_array('6',$not_visible_arr))<th d-id="6">supplier_description</th>@endif
              @endif

              @if($col_display == "7")
                @if(!in_array('7',$not_visible_arr))<th d-id="7">prod_ref_no</th>@endif
              @endif

              @if($col_display == "8")
                @if(!in_array('8',$not_visible_arr))<th d-id="8">brand</th>@endif
              @endif

              @if($col_display == "9")
                @if(!in_array('9',$not_visible_arr))<th d-id="9">prod_desc</th>@endif
              @endif

              @if($col_display == "10")
                @if(!in_array('10',$not_visible_arr))<th d-id="10">prod_type</th>@endif
              @endif

              @if($col_display == "11")
                @if(!in_array('11',$not_visible_arr))<th d-id="11">cust_name</th>@endif
              @endif

              @if($col_display == "12")
                @if(!in_array('12',$not_visible_arr))<th d-id="12"> buying_unit</th>@endif
              @endif

              @if($col_display == "13")
                @if(!in_array('13',$not_visible_arr))<th d-id="13">qty_ordered</th>@endif
              @endif

              @if($col_display == "14")
                @if(!in_array('14',$not_visible_arr))<th d-id="14">qty</th>@endif
              @endif
              @if($col_display == "15")
                @if(!in_array('15',$not_visible_arr))<th d-id="15">qty_inv</th>@endif
              @endif

              @if($col_display == "16")
                @if(!in_array('16',$not_visible_arr))<th d-id="16">note_two</th>@endif
              @endif

              @if($col_display == "17")
                @if(!in_array('17',$not_visible_arr))<th d-id="17">gross_weight</th>@endif
              @endif

              @if($col_display == "18")
                @if(!in_array('18',$not_visible_arr))<th d-id="18">total_gross_weight</th>@endif
              @endif

              @if($col_display == "19")
                @if(!in_array('19',$not_visible_arr))<th d-id="19">extra_cost</th>@endif
              @endif

              @if($col_display == "20")
                @if(!in_array('20',$not_visible_arr))<th d-id="20">total_extra_cost</th>@endif
              @endif

              @if($col_display == "21")
                @if(!in_array('21',$not_visible_arr))<th d-id="21">extra_tax</th>@endif
              @endif

              @if($col_display == "22")
                @if(!in_array('22',$not_visible_arr))<th d-id="22">total_extra_tax</th>@endif
              @endif

              @if($col_display == "23")
                @if(!in_array('23',$not_visible_arr))<th d-id="23">purchasing_price_euro</th>@endif
              @endif

              @if($col_display == "24")
                @if(!in_array('24',$not_visible_arr))<th d-id="24">purchasing_price_euro_with_vat</th>@endif
              @endif

              @if($col_display == "25")
                @if(!in_array('25',$not_visible_arr))<th d-id="25">discount</th>@endif
              @endif

              @if($col_display == "26")
                @if(!in_array('26',$not_visible_arr))<th d-id="26">total_purchasing_price_euro</th>@endif
              @endif

               @if($col_display == "27")
                @if(!in_array('27',$not_visible_arr))<th d-id="27">total_purchasing_price_euro_with_vat</th>@endif
              @endif

              @if($col_display == "28")
                @if(!in_array('28',$not_visible_arr))<th d-id="28">currency_conversion_rate</th>@endif
              @endif

              @if($col_display == "29")
                @if(!in_array('29',$not_visible_arr))<th d-id="29">purchasing_price_thb</th>@endif
              @endif

               @if($col_display == "30")
                @if(!in_array('30',$not_visible_arr))<th d-id="30">purchasing_price_thb_with_vat</th>@endif
              @endif

              @if($col_display == "31")
                @if(!in_array('31',$not_visible_arr))<th d-id="31">total_purchasing_price_thb</th>@endif
              @endif

              @if($col_display == "32")
                @if(!in_array('32',$not_visible_arr))<th d-id="32">total_purchasing_price_thb_with_vat</th>@endif
              @endif

              @if($col_display == "33")
                @if(!in_array('33',$not_visible_arr))<th d-id="33">book_vat_percent</th>@endif
              @endif

              @if($col_display == "34")
                @if(!in_array('34',$not_visible_arr))<th d-id="34">import_tax_book_%</th>@endif
              @endif

              @if($col_display == "35")
                @if(!in_array('35',$not_visible_arr))<th d-id="35">freight_per_billed_unit</th>@endif
              @endif
              @if($col_display == "36")
                @if(!in_array('36',$not_visible_arr))<th d-id="36">total_freight</th>@endif
              @endif

              @if($col_display == "37")
                @if(!in_array('37',$not_visible_arr))<th d-id="37">landing_per_billed_unit</th>@endif
              @endif
              @if($col_display == "38")
                @if(!in_array('38',$not_visible_arr))<th d-id="38">total_landing</th>@endif
              @endif

              @if($col_display == "39")
                @if(!in_array('39',$not_visible_arr))<th d-id="39">book_vat_total_thb</th>@endif
              @endif

               @if($col_display == "40")
                @if(!in_array('40',$not_visible_arr))<th d-id="40">vat_weighted_percent</th>@endif
              @endif

              @if($col_display == "41")
                @if(!in_array('41',$not_visible_arr))<th d-id="41">unit_purchasing_vat_thb</th>@endif
              @endif

              @if($col_display == "42")
                @if(!in_array('42',$not_visible_arr))<th d-id="42">total_purchasing_vat_thb</th>@endif
              @endif

              @if($col_display == "43")
                @if(!in_array('43',$not_visible_arr))<th d-id="43">purchasing_vat_percent</th>@endif
              @endif

              @if($col_display == "44")
                @if(!in_array('44',$not_visible_arr))<th d-id="44">book_%_tax</th>@endif
              @endif

              @if($col_display == "45")
                @if(!in_array('45',$not_visible_arr))<th d-id="45">weighted_%</th>@endif
              @endif

              @if($col_display == "46")
                @if(!in_array('46',$not_visible_arr))<th d-id="46">actual_tax</th>@endif
              @endif

              @if($col_display == "47")
                @if(!in_array('47',$not_visible_arr))<th d-id="47">total_import_tax</th>@endif
              @endif

              @if($col_display == "48")
                @if(!in_array('48',$not_visible_arr))<th d-id="48">actual_tax_%</th>@endif
              @endif

              @if($col_display == "50")
                @if(!in_array('50',$not_visible_arr))<th d-id="50">cogs</th>@endif
              @endif

              @if($col_display == "51")
                @if(!in_array('51',$not_visible_arr))<th d-id="51">total_cogs</th>@endif
              @endif
            @endforeach

            <th>avg_weight</th>
            <th>needed_ids</th>
          </tr>
          <!-- Ends here -->

          <tr>
            @foreach($col_display_pref as $col_display)
              @if($col_display == "1")
                @if(!in_array('1',$not_visible_arr))<th d-id="1">PO #</th>@endif
              @endif

              @if($col_display == "2")
                @if(!in_array('2',$not_visible_arr))<th d-id="2">Order <br>Warehouse</th>@endif
              @endif

              @if($col_display == "3")
                @if(!in_array('3',$not_visible_arr))<th d-id="3">Order #</th>@endif
              @endif

              @if($col_display == "4")
                @if(!in_array('4',$not_visible_arr))<th d-id="4">{{$global_terminologies['suppliers_product_reference_no']}}</th>@endif
              @endif

              @if($col_display == "5")
                @if(!in_array('5',$not_visible_arr))<th d-id="5">Supplier</th>@endif
              @endif

              @if($col_display == "6")
                @if(!in_array('6',$not_visible_arr))<th d-id="6">{{$global_terminologies['supplier_description']}}</th>@endif
              @endif

              @if($col_display == "7")
                @if(!in_array('7',$not_visible_arr))<th d-id="7">{{$global_terminologies['our_reference_number']}}</th>@endif
              @endif

              @if($col_display == "8")
                @if(!in_array('8',$not_visible_arr))<th d-id="8">{{$global_terminologies['brand']}}</th>@endif
              @endif

              @if($col_display == "9")
                @if(!in_array('9',$not_visible_arr))<th d-id="9">{{$global_terminologies['product_description']}}</th>@endif
              @endif

              @if($col_display == "10")
                @if(!in_array('10',$not_visible_arr))<th d-id="10">{{$global_terminologies['type']}}</th>@endif
              @endif

              @if($col_display == "11")
                @if(!in_array('11',$not_visible_arr))<th d-id="11">Customer</th>@endif
              @endif

              @if($col_display == "12")
                @if(!in_array('12',$not_visible_arr))<th d-id="12"> Buying <br> Unit</th>@endif
              @endif

              @if($col_display == "13")
                @if(!in_array('13',$not_visible_arr))<th d-id="13">{{$global_terminologies['qty']}} <br>Ordered</th>@endif
              @endif

              @if($col_display == "14")
                @if(!in_array('14',$not_visible_arr))<th d-id="14">{{$global_terminologies['qty']}}</th>@endif
              @endif
              @if($col_display == "15")
                @if(!in_array('15',$not_visible_arr))<th d-id="15">{{$global_terminologies['qty']}} <br>Inv</th>@endif
              @endif

              @if($col_display == "16")
                @if(!in_array('16',$not_visible_arr))<th d-id="16">{{$global_terminologies['note_two']}}</th>@endif
              @endif

              @if($col_display == "17")
                @if(!in_array('17',$not_visible_arr))<th d-id="17">{{$global_terminologies['gross_weight']}}</th>@endif
              @endif

              @if($col_display == "18")
                @if(!in_array('18',$not_visible_arr))<th d-id="18">Total <br>{{$global_terminologies['gross_weight']}}</th>@endif
              @endif

              @if($col_display == "19")
                @if(!in_array('19',$not_visible_arr))<th d-id="19">{{$global_terminologies['extra_cost_per_billed_unit']}}</th>@endif
              @endif

              @if($col_display == "20")
                @if(!in_array('20',$not_visible_arr))<th d-id="20">Total <br>{{$global_terminologies['extra_cost_per_billed_unit']}}</th>@endif
              @endif

              @if($col_display == "21")
                @if(!in_array('21',$not_visible_arr))<th d-id="21">{{$global_terminologies['extra_tax_per_billed_unit']}}</th>@endif
              @endif

              @if($col_display == "22")
                @if(!in_array('22',$not_visible_arr))<th d-id="22">Total {{$global_terminologies['extra_tax_per_billed_unit']}}</th>@endif
              @endif

              @if($col_display == "23")
                @if(!in_array('23',$not_visible_arr))<th d-id="23">{{$global_terminologies['purchasing_price']}}<br>EUR (W/O Vat)</th>@endif
              @endif

              @if($col_display == "24")
                @if(!in_array('24',$not_visible_arr))<th d-id="24">{{$global_terminologies['purchasing_price']}}<br>EUR (+Vat)</th>@endif
              @endif

              @if($col_display == "25")
                @if(!in_array('25',$not_visible_arr))<th d-id="25">Discount</th>@endif
              @endif

              @if($col_display == "26")
                @if(!in_array('26',$not_visible_arr))<th d-id="26">Total <br>{{$global_terminologies['purchasing_price']}} (W/O Vat)</th>@endif
              @endif

              @if($col_display == "27")
                @if(!in_array('27',$not_visible_arr))<th d-id="27">Total <br>{{$global_terminologies['purchasing_price']}} (+Vat)</th>@endif
              @endif

              @if($col_display == "28")
                @if(!in_array('28',$not_visible_arr))<th d-id="28">Currency Conversion Rate</th>@endif
              @endif

              @if($col_display == "29")
                @if(!in_array('29',$not_visible_arr))<th d-id="29">{{$global_terminologies['purchasing_price']}}<br> THB (W/O Vat)</th>@endif
              @endif

              @if($col_display == "30")
                @if(!in_array('30',$not_visible_arr))<th d-id="30">{{$global_terminologies['purchasing_price']}}<br> THB (+Vat)</th>@endif
              @endif

              @if($col_display == "31")
                @if(!in_array('31',$not_visible_arr))<th d-id="31">Total <br>{{$global_terminologies['purchasing_price']}}<br> THB (W/O Vat)</th>@endif
              @endif

              @if($col_display == "32")
                @if(!in_array('32',$not_visible_arr))<th d-id="32">Total <br>{{$global_terminologies['purchasing_price']}}<br> THB (+Vat)</th>@endif
              @endif

              @if($col_display == "33")
                @if(!in_array('33',$not_visible_arr))<th d-id="33">Book Vat %</th>@endif
              @endif

              @if($col_display == "34")
                @if(!in_array('34',$not_visible_arr))<th d-id="34">Import <br>Tax <br>(Book)%</th>@endif
              @endif

              @if($col_display == "35")
                @if(!in_array('35',$not_visible_arr))<th d-id="35">{{$global_terminologies['freight_per_billed_unit']}}</th>@endif
              @endif

              @if($col_display == "36")
                @if(!in_array('36',$not_visible_arr))<th d-id="36">Total Freight</th>@endif
              @endif

              @if($col_display == "37")
                @if(!in_array('37',$not_visible_arr))<th d-id="37">{{$global_terminologies['landing_per_billed_unit']}}</th>@endif
              @endif

              @if($col_display == "38")
                @if(!in_array('38',$not_visible_arr))<th d-id="38">Total Landing</th>@endif
              @endif

              @if($col_display == "39")
                @if(!in_array('39',$not_visible_arr))<th d-id="39">Book VAT Total (THB)</th>@endif
              @endif

               @if($col_display == "40")
                @if(!in_array('40',$not_visible_arr))<th d-id="40">VAT Weighted %</th>@endif
              @endif

              @if($col_display == "41")
                @if(!in_array('41',$not_visible_arr))<th d-id="41">Unit Purchasing VAT (THB)</th>@endif
              @endif

              @if($col_display == "42")
                @if(!in_array('42',$not_visible_arr))<th d-id="42">Total Purchasing VAT (THB)</th>@endif
              @endif

              @if($col_display == "43")
                @if(!in_array('43',$not_visible_arr))<th d-id="43">Purchasing VAT %</th>@endif
              @endif

              @if($col_display == "44")
                @if(!in_array('44',$not_visible_arr))<th d-id="44">Book Import Tax Total</th>@endif
              @endif

              @if($col_display == "45")
                @if(!in_array('45',$not_visible_arr))<th d-id="45">Import Weighted %</th>@endif
              @endif

              @if($col_display == "46")
                @if(!in_array('46',$not_visible_arr))<th d-id="46">{{$global_terminologies['actual_tax']}}</th>@endif
              @endif

              @if($col_display == "47")
                @if(!in_array('47',$not_visible_arr))<th d-id="47">Total Import Tax (THB)</th>@endif
              @endif

              @if($col_display == "48")
                @if(!in_array('48',$not_visible_arr))<th d-id="49">{{$global_terminologies['import_tax_actual']}}</th>@endif
              @endif

              @if($col_display == "50")
                @if(!in_array('50',$not_visible_arr))<th d-id="50">COGS Per Unit</th>@endif
              @endif

              @if($col_display == "51")
                @if(!in_array('51',$not_visible_arr))<th d-id="51">Total COGS</th>@endif
              @endif
            @endforeach

            <th>Avg. Weight per piece or box per billed unit (Kg)</th>
            <th>Id's</th>
          </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                @if($item->po_no != '--')
                <tr>
                  @foreach($col_display_pref as $col_display)

                    @if($col_display == "1")
                      @if(!in_array('1',$not_visible_arr))<td> {{ $item->po_no }} </td>@endif
                    @endif

                    @if($col_display == "2")
                      @if(!in_array('2',$not_visible_arr))<td> {{ $item->order_warehouse }} </td>@endif
                    @endif

                    @if($col_display == "3")
                      @if(!in_array('3',$not_visible_arr))<td> {{ $item->order_no }} </td>@endif
                    @endif

                    @if($col_display == "4")
                      @if(!in_array('4',$not_visible_arr))<td> {{ $item->sup_ref_no }} </td>@endif
                    @endif

                    @if($col_display == "5")
                      @if(!in_array('5',$not_visible_arr))<td> {{ $item->supplier }} </td>@endif
                    @endif

                    @if($col_display == "6")
                      @if(!in_array('6',$not_visible_arr))<td> {{ $item->supplier_description }} </td>@endif
                    @endif

                    @if($col_display == "7")
                      @if(!in_array('7',$not_visible_arr))<td> {{ $item->pf_no }} </td>@endif
                    @endif

                    @if($col_display == "8")
                      @if(!in_array('8',$not_visible_arr))<td> {{ $item->brand }} </td>@endif
                    @endif

                    @if($col_display == "9")
                      @if(!in_array('9',$not_visible_arr))<td> {{ $item->description }} </td>@endif
                    @endif

                    @if($col_display == "10")
                      @if(!in_array('10',$not_visible_arr))<td> {{ $item->type }} </td>@endif
                    @endif

                    @if($col_display == "11")
                      @if(!in_array('11',$not_visible_arr))<td> {{ $item->customer }} </td>@endif
                    @endif

                    @if($col_display == "12")
                      @if(!in_array('12',$not_visible_arr))<td> {{ $item->buying_unit }} </td>@endif
                    @endif

                    @if($col_display == "13")
                      @if(!in_array('13',$not_visible_arr))<td> {{ $item->qty_ordered }} </td>@endif
                    @endif

                    @if($col_display == "14")
                      @if(!in_array('14',$not_visible_arr))<td> {{ $item->customer_qty }} </td>@endif
                    @endif

                    @if($col_display == "15")
                      @if(!in_array('15',$not_visible_arr))<td> {{ $item->qty_inv }} </td>@endif
                    @endif

                    @if($col_display == "16")
                      @if(!in_array('16',$not_visible_arr))<td> {{ $item->product_note }} </td>@endif
                    @endif

                    @if($col_display == "17")
                      @if(!in_array('17',$not_visible_arr))<td> {{ $item->gross_weight }} </td>@endif
                    @endif

                    @if($col_display == "18")
                      @if(!in_array('18',$not_visible_arr))<td> {{ $item->total_gross_weight }} </td>@endif
                    @endif

                    @if($col_display == "19")
                      @if(!in_array('19',$not_visible_arr))<td> {{ $item->extra_cost }} </td>@endif
                    @endif

                    @if($col_display == "20")
                      @if(!in_array('20',$not_visible_arr))<td> {{ $item->total_extra_cost_thb }} </td>@endif
                    @endif

                    @if($col_display == "21")
                      @if(!in_array('21',$not_visible_arr))<td> {{ $item->extra_tax }} </td>@endif
                    @endif

                    @if($col_display == "22")
                      @if(!in_array('22',$not_visible_arr))<td> {{ $item->total_extra_tax_thb }} </td>@endif
                    @endif

                    @if($col_display == "23")
                      @if(!in_array('23',$not_visible_arr))<td> {{ $item->purchasing_price_eur }} </td>@endif
                    @endif

                    @if($col_display == "24")
                      @if(!in_array('24',$not_visible_arr))<td> {{ $item->purchasing_price_eur_with_vat }} </td>@endif
                    @endif

                    @if($col_display == "25")
                      @if(!in_array('25',$not_visible_arr))<td> {{ $item->discount }} </td>@endif
                    @endif

                    @if($col_display == "26")
                      @if(!in_array('26',$not_visible_arr))<td> {{ $item->total_purchasing_price }} </td>@endif
                    @endif

                    @if($col_display == "27")
                      @if(!in_array('27',$not_visible_arr))<td> {{ $item->total_purchasing_price_with_vat }} </td>@endif
                    @endif

                    @if($col_display == "28")
                      @if(!in_array('28',$not_visible_arr))<td> {{ $item->currency_conversion_rate }} </td>@endif
                    @endif

                    @if($col_display == "29")
                      @if(!in_array('29',$not_visible_arr))<td> {{ $item->purchasing_price_thb }} </td>@endif
                    @endif

                    @if($col_display == "30")
                      @if(!in_array('30',$not_visible_arr))<td> {{ $item->purchasing_price_thb_with_vat }} </td>@endif
                    @endif

                    @if($col_display == "31")
                      @if(!in_array('31',$not_visible_arr))<td> {{ $item->total_purchasing_price_thb }} </td>@endif
                    @endif

                    @if($col_display == "32")
                      @if(!in_array('32',$not_visible_arr))<td> {{ $item->total_purchasing_price_thb_with_vat }} </td>@endif
                    @endif

                    @if($col_display == "33")
                      @if(!in_array('33',$not_visible_arr))<td> {{ $item->pogpd_vat_actual }} </td>@endif
                    @endif

                    @if($col_display == "34")
                      @if(!in_array('34',$not_visible_arr))<td> {{ $item->import_tax_book_percent }} </td>@endif
                    @endif

                    @if($col_display == "35")
                      @if(!in_array('35',$not_visible_arr))<td> {{ $item->freight_thb }} </td>@endif
                    @endif

                    @if($col_display == "36")
                      @if(!in_array('36',$not_visible_arr))<td> {{ $item->total_freight }} </td>@endif
                    @endif

                    @if($col_display == "37")
                      @if(!in_array('37',$not_visible_arr))<td> {{ $item->landing_thb }} </td>@endif
                    @endif

                    @if($col_display == "38")
                      @if(!in_array('38',$not_visible_arr))<td> {{ $item->total_landing }} </td>@endif
                    @endif

                    @if($col_display == "39")
                      @if(!in_array('39',$not_visible_arr))<td> {{ $item->book_vat_total }} </td>@endif
                    @endif

                    @if($col_display == "40")
                      @if(!in_array('40',$not_visible_arr))<td> {{ $item->vat_weighted_percent }} </td>@endif
                    @endif

                    @if($col_display == "41")
                      @if(!in_array('41',$not_visible_arr))<td> {{ $item->pogpd_vat_actual_price }} </td>@endif
                    @endif

                    @if($col_display == "42")
                      @if(!in_array('42',$not_visible_arr))<td> {{ $item->total_pogpd_vat_actual_price }} </td>@endif
                    @endif

                    @if($col_display == "43")
                      @if(!in_array('43',$not_visible_arr))<td> {{ $item->pogpd_vat_actual_percent_val }} </td>@endif
                    @endif

                    @if($col_display == "44")
                      @if(!in_array('44',$not_visible_arr))<td> {{ $item->book_percent_tax }} </td>@endif
                    @endif

                    @if($col_display == "45")
                      @if(!in_array('45',$not_visible_arr))<td> {{ $item->weighted_percent }} </td>@endif
                    @endif

                    @if($col_display == "46")
                      @if(!in_array('46',$not_visible_arr))<td> {{ $item->actual_tax }} </td>@endif
                    @endif

                    @if($col_display == "47")
                      @if(!in_array('47',$not_visible_arr))<td> {{ $item->actual_tax * $item->qty_inv }} </td>@endif
                    @endif

                    @if($col_display == "48")
                      @if(!in_array('48',$not_visible_arr))<td> {{ $item->actual_tax_percent }} </td>@endif
                    @endif

                    @if($col_display == "50")
                      @if(!in_array('50',$not_visible_arr))<td> {{ $item->cogs }} </td>@endif
                    @endif

                    @if($col_display == "51")
                      @if(!in_array('51',$not_visible_arr))<td> {{ $item->total_cogs }} </td>@endif
                    @endif

                  @endforeach

                  <td> {{ $item->avg_weight }} </td>
                  <td> {{ $item->pod_id }},{{ $item->pogpd_id }},{{ $item->po_id }}  </td>
                </tr>
                @endif
                @endforeach

            </tbody>

    </table>

    </body>
</html>
