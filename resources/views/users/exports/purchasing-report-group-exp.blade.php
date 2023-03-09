<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                <th><b>{{$global_terminologies['our_reference_number']}}</b></th>
                <th><b>{{$global_terminologies['product_description']}} </b></th>
                <th><b>Billing Unit</b></th>
                <th><b>{{$global_terminologies['selling_unit']}}</b></th>
                <th><b>Sum of {{$global_terminologies['qty']}} </b></th>
                <th><b>{{$global_terminologies['product_cost']}}</b></th>
                <th><b>{{$global_terminologies['sum_pro_cost']}}</b></th>
              </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                    <td>
                        {{$item['pf_no']}}
                    </td>
                    <td>
                        {{$item['description']}}
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
                        {{$item['unit_eur']}}
                    </td>
                    <td>
                        {{$item['total_amount_eur']}}
                    </td>
                </tr>
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>
