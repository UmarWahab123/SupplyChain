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
          <th>Order#</th>
            <th>Customer</th>
            <th>Delivery <br> Date</th>
            <th>Created <br> Date</th>
            <th> Supply <br> From </th>
            <th>Warehouse</th>
            <th>{{$global_terminologies['our_reference_number']}}</th>
            <th> {{$global_terminologies['product_description']}} </th>
            <th>Selling<br>Unit</th>
            <th>{{$global_terminologies['qty']}} </th>
            <th>Unit Price</th>
            <th>Total<br>Amount</th>
            <th>Vat</th>
          </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                  @php
                    $order = $item->get_order;
                  @endphp
                    <td>

                      @if ($item->order_id != null)

                          @if ($order->in_status_prefix !== null && $order->in_ref_prefix !== null && $order->in_ref_id !== null)
                              {{$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id}}
                          @elseif($order->status_prefix !== null && $order->ref_prefix !== null && $order->ref_id !== null)
                              {{$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id}}
                          @else
                              {{$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id}}
                          @endif
                      @else
                          {{"--"}}
                      @endif
                      
                    </td>

                    <td>
                      {{$order->customer !== null ? $order->customer->reference_name : 'N.A'}}
                    </td>

                    <td>
                      {{$order->delivery_request_date !== null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : 'N.A'}}
                    </td>

                    
                    <td>
                    
                    {{$item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A'}}
                    </td>

                    

                    <td>
                      @if($item->supplier_id != NULL && $item->from_warehouse_id == NULL)
                      
                         {{$item->from_supplier->reference_name}}
                      
                      @elseif($item->from_warehouse_id != NULL && $item->supplier_id == NULL)
                      
                         {{$item->from_warehouse->warehouse_title}}
                      
                      @else
                      
                         {{"N.A"}}
                      @endif

                     
                    </td>

                    <td>
                      {{$order->user->get_warehouse !== null ? $order->user->get_warehouse->warehouse_title : 'N.A'}}
                    </td>

                    <td>
                      {{$item->product->refrence_code}}
                    </td>

                    <td>
                      {{$item->product->short_desc }}
                    </td>

                    <td>
                      {{$item->product->sellingUnits->title }}
                    </td>

                    <td>
                      @if($order->primary_status == 2)
                      {{($item->quantity !== null ? round($item->quantity,2) : 'N.A')}}
                      @else
                      {{($item->qty_shipped !== null ? round($item->qty_shipped,2) : 'N.A')}}
                      @endif
                    </td>

                    <td>
                      {{$item->unit_price !== null ? round($item->unit_price,2) : 'N.A'}}
                    </td>

                    <td>
                      {{$item->total_price !== null ? round($item->total_price,2) : 'N.A'}}
                    </td>

                    <td>
                      {{$item->vat !== null ? $item->vat.' %' : 'N.A'}}
                    </td>

                </tr>
                    
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>
