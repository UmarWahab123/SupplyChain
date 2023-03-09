@php
use Carbon\Carbon;
use App\OrderTransaction;

@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
          <tr>
                <th>Invoice Date</th>                                                    
                <th>Delivery Date</th>                                                    
                <th>Invoice # </th>                                                    
                <th>Sales Person</th>                                                    
                <th>Company Name</th>
                <th>Reference Name</th>
                <th>Inv.#</th>
                <th>VAT Inv (-1)</th>
                <th>VAT</th>
                <th>Inv.#</th>
                <th>Non VAT  Inv (-2)</th>
                <th>Sub Total</th>
                <th>Order Total</th>
                <th>Total Amount Paid</th>
                <th>Total Amount Due</th>
                <th>Payment Due Date</th>
                                                                 
            </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                  <td>
                    {{$item->converted_to_invoice_on != NULL ? Carbon::parse($item->converted_to_invoice_on)->format("d/m/Y") : "N.A"}}
                  </td>
                  
                  <td>
                    {{$item->delivery_request_date != NULL ? Carbon::parse($item->delivery_request_date)->format("d/m/Y") : "N.A"}}  
                  </td>
                  
                  <td>
                    @php
                    if($item->primary_status == 3)
                    {
                      if($item->in_status_prefix !== null || $item->in_ref_prefix !== null){
                        $ref_no = @$item->in_status_prefix."-".$item->in_ref_prefix.$item->in_ref_id;
                      }
                      else{
                        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
                      }
                    }
                    else
                    {
                      if($item->status_prefix !== null || $item->ref_prefix !== null){
                        $ref_no = @$item->in_status_prefix."-".$item->in_ref_prefix.$item->in_ref_id;
                      }
                      else{
                        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
                      }
                    }
                    @endphp
                    {{@$ref_no}}
                  </td>
                  
                  <td>
                    {{$item->customer->primary_sale_person != null ? @$item->customer->primary_sale_person->name : "N.A"}}
                  </td>
                  
                  <td>
                    {{$item->customer !== null ? $item->customer->company : "N.A"}}
                  </td>
                  
                  <td>
                    {{$item->customer !== null ? $item->customer->reference_name : "N.A"}}
                  </td>
                  
                  <td>
                    @php
                    if(@$item->in_status_prefix !== null)
                    {
                      $ref_id_vat = $item->in_status_prefix."-".@$item->in_ref_prefix.$item->in_ref_id."-1";
                    }
                    else
                    {
                      $ref_id_vat = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id."-1";
                    }
                    @endphp

                    {{$ref_id_vat}}
                  </td>

                  <td>
                    {{@$item->order_products != null ? round((@$item->getOrderTotalVatAccounting($item->id,0) - @$item->getOrderTotalVatAccounting($item->id,1)),2) : '--'}}
                  </td>

                  <td>
                    {{@$item->order_products != null ? @$item->getOrderTotalVatAccounting($item->id,1) : '--'}}
                  </td>
                  
                  <td>
                    @php
                    if(@$item->in_status_prefix !== null)
                    {
                      $reference_id_vat_2 = $item->in_status_prefix."-".@$item->in_ref_prefix.$item->in_ref_id."-2";
                    }
                    else
                    {
                      $reference_id_vat_2 = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id."-2";
                    }
                    @endphp
                    {{@$reference_id_vat_2}}
                  </td>
                  
                  <td>
                    {{@$item->order_products != null ? @$item->getOrderTotalVatAccounting($item->id,2) : '--'}}
                  </td>

                  <td>
                    {{$item->order_products != null ? round($item->order_products->sum('total_price'),2) : "N.A"}}
                  </td>
                  
                  <td>
                    {{$item->total_amount != null ? round($item->total_amount,2) : "N.A"}}
                  </td>

                  <td>
                    @php
                    $amount_paid = OrderTransaction::where("order_id" , $item->id)->sum("total_received");
                    @endphp
                    {{round(@$amount_paid,2)}}
                  </td>

                  <td>
                    @php
                    $amount_paid = OrderTransaction::where("order_id" , $item->id)->sum("total_received");
                    $amount_due = $item->total_amount-$amount_paid;
                    @endphp
                    {{round(@$amount_due,2)}}
                  </td>

                  <td>
                    {{$item->payment_due_date != NULL ? Carbon::parse($item->payment_due_date)->format("d/m/Y") : "N.A"}}
                  </td>

                </tr>
                    
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>
