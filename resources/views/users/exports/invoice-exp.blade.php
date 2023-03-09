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
            @if(!in_array('1',$not_visible_arr))<th>Order#</th>@endif
            @if(!in_array('2',$not_visible_arr))<th>Sales Person</th>@endif
            @if(!in_array('3',$not_visible_arr))<th>Customer #</th>@endif
            @if(!in_array('4',$not_visible_arr))<th>Reference Name</th>@endif
            @if(!in_array('5',$not_visible_arr))<th>Company Name</th>@endif
            @if(!in_array('6',$not_visible_arr))<th>Draft#</th>@endif
            @if(!in_array('7',$not_visible_arr))<th>Inv.#</th>@endif
            @if(!in_array('8',$not_visible_arr))<th>VAT Inv (-1)</th>@endif
            @if(!in_array('9',$not_visible_arr))<th>VAT</th>@endif
            @if(!in_array('10',$not_visible_arr))<th>Inv.#</th>@endif
            @if(!in_array('11',$not_visible_arr))<th>Non VAT <br>Inv (-2)</th>@endif
            @if(!in_array('12',$not_visible_arr))<th>Discount</th>@endif
            @if(!in_array('13',$not_visible_arr))<th>Sub Total</th>@endif
            @if(!in_array('14',$not_visible_arr))<th>Order Total</th>@endif
            @if(!in_array('15',$not_visible_arr))<th>Payment Reference</th>@endif
            @if(!in_array('16',$not_visible_arr))<th>Received Date</th>@endif
            @if(!in_array('17',$not_visible_arr))<th>Delivery Date</th>@endif
            @if(!in_array('18',$not_visible_arr))<th>Invoice Date</th>@endif
            @if(!in_array('19',$not_visible_arr))<th>Due Date</th>@endif
            @if(!in_array('20',$not_visible_arr))<th>Remark</th>@endif
            @if(!in_array('21',$not_visible_arr))<th>Comment to Warehouse</th>@endif
            @if(!in_array('22',$not_visible_arr))<th>Ref. PO#</th>@endif
            @if(!in_array('23',$not_visible_arr))<th>Status</th>@endif
            
          </tr>
        </thead>
            <tbody>
                @foreach($query->chunk(500) as $orders)
                @foreach($orders as $item)
                <tr>
                  @if(!in_array('1',$not_visible_arr))<td>
                      @if($item->in_status_prefix !== null || $item->in_ref_prefix !== null)
                      @php
                          $ref_no = @$item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
                      @endphp
                        @else
                        @php
                          $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->in_ref_id;
                        @endphp
                        @endif
                        {{$ref_no}}
                    </td>@endif

                    @if(!in_array('2',$not_visible_arr))<td>
                      <!-- {{$item->customer !== null ? @$item->customer->primary_sale_person->name : '--'}} -->
                      {{$item->user !== null ? @$item->user->name : '--'}}
                    </td>@endif

                    @if(!in_array('3',$not_visible_arr))<td>
                      {{$item->customer->reference_number}}
                    </td>@endif


                    @if(!in_array('4',$not_visible_arr))<td>
                      @if($item->customer_id != null)
                      
                        @if($item->customer['reference_name'] != null)
                        
                          {{$item->customer['reference_name']}}
                        
                        @else
                        
                          {{$item->customer['first_name'].' '.$item->customer['last_name']}}
                        @endif                     
                      
                      @else
                        N.A
                      @endif


                    </td>@endif

                    @if(!in_array('5',$not_visible_arr))<td>
                      {{@$item->customer->company}}
                    </td>@endif

                    @if(!in_array('6',$not_visible_arr))<td>
                    @php
                    if($item->status_prefix !== null || $item->ref_prefix !== null){
                      $ref_no = @$item->status_prefix.'-'.$item->ref_prefix.$item->ref_id;
                    }else{
                      $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
                    }

                    $html_string = '';
                    if($item->primary_status == 2  )
                    {
                      $html_string .= $ref_no;
                    }
                    elseif($item->primary_status == 3)
                    {
                      if($item->ref_id == null){
                        $ref_no = '-';
                      }
                      $html_string .= $ref_no;
                    }
                    elseif($item->primary_status == 1)
                    {
                      $html_string = $ref_no;
                    }


                    @endphp

                    {{$html_string}}

                    </td>@endif

                    @if(!in_array('7',$not_visible_arr))<td>
                      {{$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1'}}
                    </td>@endif

                    @if(!in_array('8',$not_visible_arr))<td>
                      <!-- {{$item->vat_total_amount !== null ? number_format($item->vat_total_amount,2,'.',',') : '0.00'}} -->
                      {{@$item->order_products != null ? @$item->getOrderTotalVat($item->id,0) : '--'}}
                      
                    </td>@endif
                    
                    @if(!in_array('9',$not_visible_arr))<td>
                      <!-- {{$item->vat_amount_price !== null ? number_format($item->vat_amount_price,2,'.',',') : '0.00'}} -->
                      {{@$item->order_products != null ? @$item->getOrderTotalVat($item->id,1) : '--'}}
                      
                    </td>@endif

                    @if(!in_array('10',$not_visible_arr))<td>
                      {{@$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2'}}
                    </td>@endif

                    @if(!in_array('11',$not_visible_arr))<td>
                     <!--  {{$item->not_vat_total_amount !== null ? number_format($item->not_vat_total_amount,2,'.',',') : '0.00'}} -->
                      {{@$item->order_products != null ? @$item->getOrderTotalVat($item->id,2) : '--'}}
                    </td>@endif

                    @if(!in_array('12',$not_visible_arr))<td>
                      {{$item->all_discount !== null ? number_format(floor($item->all_discount*100)/100,2,'.','') : '0.00'}}
                     <!--  @php
                                      
                          $item_level_dicount = 0;
                          $values = App\Models\Common\Order\OrderProduct::where('order_id',$item->id)->get();
                          foreach ($values as  $value) {
                            if($value->discount != 0)
                            {
                                if($value->discount == 100)
                              {
                                if($value->is_retail == 'pieces')
                                {
                                  if($item->primary_status == 3){
                                    $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                                  }else{
                                    $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                                  }
                                }
                                else
                                {
                                  if($item->primary_status == 3)
                                  {
                                    $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                                  }
                                  else{
                                    $discount_full =  $value->unit_price_with_vat * $value->quantity;
                                  }
                                }
                                  $item_level_dicount += $discount_full;
                              }
                              else
                              {
                                $item_level_dicount += ($value->total_price / ((100 - $value->discount)/100)) - $value->total_price;
                              }
                            }
                            
                          }
                                      
                      @endphp
                      {{number_format(floor($item_level_dicount*100)/100, 2, '.', ',')}} -->
                    </td>@endif

                    @if(!in_array('13',$not_visible_arr))<td>
                      {{round($item->sub_total_price,2)}}
                    </td>@endif
                    @if(!in_array('14',$not_visible_arr))<td>
                      {{round($item->total_amount,2)}}
                    </td>@endif
                      @if(!in_array('15',$not_visible_arr))<td>
                      <?php
                       if(!$item->get_order_transactions->isEmpty())
                      {
                        $html='';
                        foreach($item->get_order_transactions as $key=>$ot)
                        {
                          if($key==0)
                            {$html.=$ot->get_payment_ref->payment_reference_no;}
                          else
                          {
                            $html.=','.$ot->get_payment_ref->payment_reference_no;
                          }
                        }
                        echo $html;
                      }
                      else
                      {
                        echo '--';
                      }
                      ?>
                    </td>@endif
                      @if(!in_array('16',$not_visible_arr))<td>
                      <?php
                    if(!$item->get_order_transactions->isEmpty())
                    {
                      $count = count($item->get_order_transactions);
                      $html=Carbon::parse(@$item->get_order_transactions[$count - 1]->received_date)->format('d/m/Y');
                      // $html='';
                      // foreach($item->get_order_transactions as $key => $ot)
                      // {
                      //   if($key==0)
                      //   {
                      //     $html.=Carbon::parse(@$ot->received_date)->format('d/m/Y');
                      //   }
                      //   else
                      //   {
                      //     $html.=','.Carbon::parse(@$ot->received_date)->format('d/m/Y');
                      //   }
                      // }
                      echo $html;
                    }
                      else
                      {
                        echo '--';
                      }
                      ?>
                    </td>@endif

                    @if(!in_array('17',$not_visible_arr))<td>
                      {{$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y'): '--'}}
                    </td>@endif

                    @if(!in_array('18',$not_visible_arr))<td>
                      {{$item->converted_to_invoice_on != null ?  Carbon::parse($item->converted_to_invoice_on)->format('d/m/Y'): '--'}}
                    </td>@endif

                    @if(!in_array('19',$not_visible_arr))<td>
                      {{$item->payment_due_date != null ?  Carbon::parse($item->payment_due_date)->format('d/m/Y'): '--'}}
                    </td>@endif

                    @if(!in_array('20',$not_visible_arr))<th>
                      @php
                          $warehouse_note = $item->order_notes->where('type','customer')->first();
                      @endphp
                      {{@$warehouse_note != null ? @$warehouse_note->note : '--'}}
                    </th>@endif

                    @if(!in_array('21',$not_visible_arr))<th>
                      @php
                          $warehouse_note = $item->order_notes->where('type','warehouse')->first();
                      @endphp
                      {{@$warehouse_note != null ? @$warehouse_note->note : '--'}}
                    </th>@endif

                    @if(!in_array('22',$not_visible_arr))<td>
                      {{@$item->memo != null ? @$item->memo : '--'}}
                    </td>@endif

                    @if(!in_array('23',$not_visible_arr))<td>
                      {{@$item->statuses->title}}
                    </td>@endif
                    

                </tr>
                @endforeach
                @endforeach

            </tbody> 
    
    </table>

    </body>
</html>
