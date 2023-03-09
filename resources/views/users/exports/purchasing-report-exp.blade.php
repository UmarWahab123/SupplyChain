<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                <th><b>Confirm Date</b></th>
                <th><b>Supplier</b></th>
                <th><b>PO#</b></th>
                <th><b>{{$global_terminologies['our_reference_number']}}</b></th>
                <th><b>{{$global_terminologies['product_description']}} </b></th>
                <th><b>{{$global_terminologies['type']}} </b></th>
                <th><b>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</b></th>
                <th><b>Billing Unit</b></th>
                <th><b>{{$global_terminologies['selling_unit']}}</b></th>
                <th><b>Sum of {{$global_terminologies['qty']}} </b></th>

                <th><b>{{$global_terminologies['freight_per_billed_unit']}}</b></th>
                <th><b>{{$global_terminologies['landing_per_billed_unit']}}</b></th>
                <th><b>{{$global_terminologies['import_tax_actual']}}</b></th>
                <th><b>{{$global_terminologies['cost_price']}}</b></th>

                <th><b>{{$global_terminologies['product_cost']}}</b></th>
                <th><b>{{$global_terminologies['sum_pro_cost']}}</b></th>
                <th><b>{{$global_terminologies['cost_unit_thb']}}</b></th>
                <th><b>{{$global_terminologies['sum_cost_amnt']}}</b></th>

                <th><b>Vat</b></th>

              </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                    <td>
                        {{$item['confirm_date']}}
                    </td>
                    <td>
                        {{$item['supplier']}}
                    </td>
                    <td>
                        {{$item['po_no']}}
                    </td>
                    <td>
                        {{$item['pf_no']}}
                    </td>
                    <td>
                        {{$item['description']}}
                    </td>
                    <td>
                        {{$item['product_type']}}
                    </td>
                    <td>
                        {{$item['product_type_2']}}
                    </td>
                    <td>
                        {{$item['billing_unit']}}
                    </td>
                    <td>
                        {{$item['selling_unit']}}
                    </td>
                    <td>
                        {{$item['sum_of_qty']}}
                    </td>
                    <td>
                        {{$item['freight']}}
                    </td>
                    <td>
                        {{$item['landing']}}
                    </td>
                    <td>
                        {{$item['tax_allocation']}}
                    </td>
                    <td>
                        {{$item['total_unit_cost']}}
                    </td>
                    <td>
                        {{$item['unit_eur']}}
                    </td>
                    <td>
                        {{$item['total_amount_eur']}}
                    </td>
                    <td>
                        {{$item['unit_cost']}}
                    </td>
                    <td>
                        {{$item['total_amount']}}
                    </td>
                    <td>
                        {{$item['vat']}}
                    </td>
                </tr>
            
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>
