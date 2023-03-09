<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                <th><b>{{$global_terminologies['our_reference_number']}}</b></th>
                <th><b>{{$global_terminologies['brand']}}</b></th>
                <th><b>{{$global_terminologies['product_description']}}</b></th>
                <th><b>Billing Unit</b></th>
                <th><b>Total <br>{{$global_terminologies['qty']}}</b></th>
                <th><b>Selling <br>Price</b></th>
                <th><b>Avg <br>Unit <br> Price</b></th>
                <th><b>Total<br>Amount</b></th>
              </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                    <tr>
                        <td>{{$item->product_id != null ? $item->refrence_code : "N.A"}}</td>
                        <td>{{$item->product_id != null ? $item->brand : "N.A"}}</td>
                        <td>{{$item->product_id != null ? $item->short_desc : "N.A"}}</td>
                        <td>{{$item->product_id != null ? $item->units->title : "N.A"}}</td>
                        <td>{{$item->QuantityText !== null ? $item->QuantityText : "N.A"}}</td>
                        <td>{{$item->total_buy_unit_cost_price !== null ? number_format((float) $item->total_buy_unit_cost_price, 3, '.', '') : 'N.A' }}</td>
                        <td>{{$item->avg_unit_price !== null ? number_format($item->avg_unit_price,2,'.','') : 'N.A'}}</td>
                        <td>{{$item->TotalAmount !== null ? number_format($item->TotalAmount,2,'.','') : 'N.A'}}</td>
                        
                    </tr>
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>

