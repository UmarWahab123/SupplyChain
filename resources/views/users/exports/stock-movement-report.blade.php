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
          <tr>
            @if(!in_array('0', $column_visiblity))
            <th>PF#</th>
            @endif

            @if(!in_array('1', $column_visiblity))
            <th>Description</th>
            @endif

            @if(!in_array('2', $column_visiblity))
            <th><b>{{$global_terminologies['brand']}}</b></th>
            @endif

            @if(!in_array('3', $column_visiblity))
            <th><b>{{$global_terminologies['type']}}</b></th>
            @endif

            @if(!in_array('4', $column_visiblity))
            <th><b>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</b></th>
            @endif

            @if(!in_array('5', $column_visiblity))
            <th>Minimum Stock</th>
            @endif

            @if(!in_array('6', $column_visiblity))
            <th>Unit</th>
            @endif

            @if(!in_array('7', $column_visiblity))
            <th>Start <br> Count</th>
            @endif

            @if(!in_array('8', $column_visiblity))
            <th>In(From <br> Purchase)</th>
            @endif

            @if(!in_array('9', $column_visiblity))
            <th>In(Manual <br> Adjustment)</th>
            @endif

            @if(!in_array('10', $column_visiblity))
            <th>In(Transfer <br> Document)</th>
            @endif

            @if(!in_array('11', $column_visiblity))
            <th>In(Order <br> Update)</th>
            @endif

            @if(!in_array('12', $column_visiblity))
            <th>IN(Total)</th>
            @endif

            @if(!in_array('13', $column_visiblity))
            <th>Out(Order)</th>
            @endif

            @if(!in_array('14', $column_visiblity))
            <th>Out(Manual <br> Adjustment)</th>
            @endif

            @if(!in_array('15', $column_visiblity))
            <th>Out(Transfer <br> Document)</th>
            @endif

            @if(!in_array('16', $column_visiblity))
            <th>OUT(Total)</th>
            @endif

            @if(!in_array('17', $column_visiblity))
            <th>Balance</th>
            @endif

            @if($role_id == 1 || $role_id == 2 || $role_id == 7 && !in_array('18', $column_visiblity))
            <th>COGS</th>
            @endif

          </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                  @if(!in_array('0', $column_visiblity))
                  <td>{{$item->reference_code}}</td>
                  @endif

                  @if(!in_array('1', $column_visiblity))
                  <td>{{$item->short_desc}}</td>
                  @endif

                  @if(!in_array('2', $column_visiblity))
                  <td>{{$item->brand}}</td>
                  @endif

                  @if(!in_array('3', $column_visiblity))
                  <td>{{$item->type}}</td>
                  @endif

                  @if(!in_array('4', $column_visiblity))
                  <td>{{$item->type_2}}</td>
                  @endif

                  @if(!in_array('5', $column_visiblity))
                  <td>{{$item->min_stock}}</td>
                  @endif

                  @if(!in_array('6', $column_visiblity))
                  <td>{{$item->selling_unit}}</td>
                  @endif

                  @if(!in_array('7', $column_visiblity))
                  <td>{{$item->start_count}}</td>
                  @endif
                  @if(!in_array('8', $column_visiblity))
                  <td>{{$item->in_from_purchase}}</td>
                  @endif

                  @if(!in_array('9', $column_visiblity))
                  <td>{{$item->in_manual_adjustment}}</td>
                  @endif

                  @if(!in_array('10', $column_visiblity))
                  <td>{{$item->in_transfer_document}}</td>
                  @endif

                  @if(!in_array('11', $column_visiblity))
                  <td>{{$item->in_order_update}}</td>
                  @endif

                  @if(!in_array('12', $column_visiblity))
                  <td>{{$item->stock_in}}</td>
                  @endif

                  @if(!in_array('13', $column_visiblity))
                  <td>{{$item->out_order}}</td>
                  @endif

                  @if(!in_array('14', $column_visiblity))
                  <td>{{$item->out_manual_adjustment}}</td>
                  @endif

                  @if(!in_array('15', $column_visiblity))
                  <td>{{$item->out_transfer_document}}</td>
                  @endif

                  @if(!in_array('16', $column_visiblity))
                  <td>{{$item->stock_out}}</td>
                  @endif

                  @if(!in_array('17', $column_visiblity))
                  <td>{{$item->stock_balance}}</td>
                  @endif

                  @if($role_id == 1 || $role_id == 2 || $role_id == 7 && !in_array('18', $column_visiblity))
                  <td>{{$item->cogs}}</td>
                  @endif
                </tr>

                @endforeach

            </tbody>

    </table>

    </body>
</html>
