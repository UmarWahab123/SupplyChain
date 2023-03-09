<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
              <tr>                
                @if(!in_array('0',$not_visible_arr))<th>Customers</th>@endif
                @php $key=1; @endphp
                @foreach($months as $mon)
                @if($mon == 'Dec')
                @php $mon = 'Dece';
                @endphp
                @endif
                @if($customers->sum($mon) > 0)
                  @if(!in_array($key,$not_visible_arr)) <th>{{$mon}}</th>@endif
                  @endif

                 @php $key++; @endphp
                @endforeach
                @if(!in_array('13',$not_visible_arr))<th>Grand Total</th>@endif
                @if(!in_array('14',$not_visible_arr))<th>Location Code</th>@endif
                @if(!in_array('15',$not_visible_arr))<th>Sales Person Code</th>@endif
                @if(!in_array('16',$not_visible_arr))<th>Payment Terms Code</th>@endif
              </tr>
            </thead>
            <tbody>
              @if($customers->count() > 0)
              $customers->chunk(300,function($customers){
              @foreach($customers as $customer)
                @if(@$customer->getYearWiseSale(@$customer->id , @$selected_year) > 0)
                <tr>
                  @if(!in_array('0',$not_visible_arr))<td>{{@$customer->reference_name}}</td>@endif
                  @php $key=1; @endphp  
                  @foreach($months as $mon)
                  @if($mon == 'Dec')
                  @php $mon = 'Dece' @endphp
                  @endif
                  @if($customers->sum($mon) > 0)
                  @if(!in_array($key,$not_visible_arr))<td>{{@$customer->$mon !== null ? number_format($customer->$mon,2,'.','') : '0.00'}}</td>@endif
                  @endif

                 @php $key++; @endphp
                  @endforeach    
                  @if(!in_array('13',$not_visible_arr))<td>{{@$customer->customer_orders_total !== null ? number_format(@$customer->customer_orders_total,2,'.','') : '0.00'}}</td>@endif  
                  @if(!in_array('14',$not_visible_arr))<td>{{@$customer->customer_orders[0]->user->get_warehouse->location_code}}</td>@endif  
                  @if(!in_array('15',$not_visible_arr))<td>{{@$customer->primary_sale_person->name}}</td>@endif  
                  @if(!in_array('16',$not_visible_arr))<td>{{@$customer->getpayment_term->title}}</td>@endif  
                    
                </tr>
                @endif
                @endforeach
              });
                
              @endif
            </tbody> 
    
    </table>

    </body>
</html>
