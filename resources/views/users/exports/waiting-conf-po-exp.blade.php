@php
use Carbon\Carbon;
@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <style type="text/css">
      .hideRow{
        display: none;
      }
    </style>
    <body>
    <table>
        <thead>
            <tr>
              <th>Get Purchase Order</th>
              @if(!in_array('2',$not_visible_arr))<th>{{$global_terminologies['suppliers_product_reference_no']}}</th>@endif
              @if(!in_array('3',$not_visible_arr))<th>PF#</th>@endif
              @if(!in_array('4',$not_visible_arr))<th>Customer Reference Name</th>@endif
              @if(!in_array('5',$not_visible_arr))<th>{{$global_terminologies['brand']}}</th>@endif
              @if(!in_array('6',$not_visible_arr))<th>{{$global_terminologies['product_description']}}</th>@endif
              @if(!in_array('7',$not_visible_arr))<th>{{$global_terminologies['type']}}</th>@endif
              @if(!in_array('8',$not_visible_arr))<th>{{$global_terminologies['supplier_description']}}</th>@endif
              @if(!in_array('9',$not_visible_arr))<th>{{$global_terminologies['expected_lead_time_in_days']}}</th>@endif
              @if(!in_array('10',$not_visible_arr))<th>Gross Weight</th>@endif
              @if(!in_array('11',$not_visible_arr))<th>Order {{$global_terminologies['qty']}}</th>@endif
              @if(!in_array('12',$not_visible_arr))<th>{{$global_terminologies['order_qty_unit']}}</th>@endif
              @if(!in_array('13',$not_visible_arr))<th>{{$global_terminologies['quantity']}}</th>@endif
              @if(!in_array('14',$not_visible_arr))<th>{{$global_terminologies['pcs']}}</th>@endif
              @if(!in_array('15',$not_visible_arr))<th>{{$global_terminologies['quantity_inv']}}</th>@endif
              @if(!in_array('16',$not_visible_arr))<th>Unit Price</th>@endif
              @if(!in_array('17',$not_visible_arr))<th>Purchasing VAT %</th>@endif
              @if(!in_array('18',$not_visible_arr) && !in_array('17',$not_visible_arr))<th>Unit Price (+VAT)</th>@endif
              @if(!in_array('19',$not_visible_arr))<th>Price Date</th>@endif
              @if(!in_array('20',$not_visible_arr))<th>Discount</th>@endif
              @if(!in_array('21',$not_visible_arr))<th>Total Amount <br> (EUR) </th>@endif
              @if(!in_array('22',$not_visible_arr) && !in_array('17',$not_visible_arr))<th>Total Amount <br> (EUR) (Inc VAT) </th>@endif
              @if(!in_array('23',$not_visible_arr))<th>Total {{$global_terminologies['gross_weight']}}</th>@endif
              @if(!in_array('24',$not_visible_arr))<th>Order #</th>@endif
              @if(!in_array('25',$not_visible_arr))<th>{{$global_terminologies['avg_units_for-sales'] }}</th>@endif
              <th class="hideRow">Row ID</th>
            </tr>
        </thead>
            <tbody>
              @if($query != null && $query->count() > 0)
                @foreach($query->get() as $item)
                @if($item->is_billed == "Product")
                <tr>
                <td>Get Purchase Order</td>
                  @if(!in_array('2',$not_visible_arr))
                  <td>
                    <?php
                      if($item->PurchaseOrder->supplier_id != null)
                      {
                        if($item->product_id != null)
                        {
                          $gettingProdSuppData = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$item->PurchaseOrder->supplier_id)->first();
                          ?>
                          {{@$gettingProdSuppData->product_supplier_reference_no !== null ? @$gettingProdSuppData->product_supplier_reference_no : "--"}}
                          <?php
                        }
                        else
                        {
                          ?>
                            {{'N.A'}}
                          <?php
                        }
                      }
                      else
                      {
                      ?>
                        {{'--'}}
                      <?php
                    }?>
                  </td>
                  @endif
                  @if(!in_array('3',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        $ref_no = $item->product->refrence_code;
                        ?>
                        {{$ref_no}}
                        <?php
                      }
                      else
                      {
                        ?>
                          {{"--"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('4',$not_visible_arr))
                  <td>
                    <?php
                      if(@$item->customer_id !== null)
                      {
                        ?>
                        {{@$item->customer->reference_name}}
                        <?php
                      }
                      else
                      {
                        ?>
                        {{'Stock'}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('5',$not_visible_arr))
                  <td>
                    {{$item->product_id != null ? $item->product->brand : '--'}}
                  </td>
                  @endif
                  @if(!in_array('6',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        if($item->PurchaseOrder->supplier_id != null)
                        {
                          $supplier_id = $item->PurchaseOrder->supplier_id;
                          $getDescription = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                          ?>
                          {{@$getDescription->supplier_description != null ? @$getDescription->supplier_description : ($item->product->short_desc != null ? $item->product->short_desc : "--")}}
                          <?php
                        }
                        else
                        {
                          $supplier_id = $item->product->supplier_id;
                          ?>
                          {{$item->product->short_desc != null ? $item->product->short_desc : "--" }}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                        {{$item->billed_desc != null ? $item->billed_desc : "--" }}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('7',$not_visible_arr))
                  <td>
                    {{$item->product_id != null ? $item->product->productType->title : '--'}}
                  </td>
                  @endif
                  @if(!in_array('8',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        if($item->PurchaseOrder->supplier_id != null)
                        {
                          $supplier_id = $item->PurchaseOrder->supplier_id;
                          $getDescription = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                          ?>
                          {{@$getDescription->supplier_description != null ? @$getDescription->supplier_description : ($item->product->short_desc != null ? $item->product->short_desc : "--")}}
                          <?php
                        }
                        else
                        {
                          $supplier_id = $item->product->supplier_id;
                          ?>
                          {{$item->product->short_desc != null ? $item->product->short_desc : "--"}}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                        {{$item->billed_desc != null ? $item->billed_desc : "--" }}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('9',$not_visible_arr))
                  <td>
                    <?php
                      if($item->PurchaseOrder->supplier_id != null)
                      {
                        if($item->product_id != null)
                        {
                          $gettingProdSuppData = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$item->PurchaseOrder->supplier_id)->first();

                          $leading_time = $gettingProdSuppData->leading_time !== null ? $gettingProdSuppData->leading_time : "--";
                          ?>
                          {{$leading_time}}
                          <?php
                        }
                        else
                        {
                          ?>
                          {{'N.A'}}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                        {{'--'}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('10',$not_visible_arr))
                  <td>
                    <?php
                      if($item->PurchaseOrder->supplier_id != null)
                      {
                        if($item->product_id != null)
                        {
                          ?>
                            {{$item->pod_gross_weight}}
                          <?php
                        }
                        else
                        {
                          ?>
                            {{'N.A'}}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                          {{'--'}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('11',$not_visible_arr))
                  <td>
                    <?php
                      $supplier_packaging = $item->supplier_packaging !== null ? $item->supplier_packaging : 'N.A';
                      $decimals = $item->product != null ? ($item->product->units != null ? $item->product->units->decimal_places : 0) : 0;
                      if($item->product_id != null )
                      {
                        if($item->PurchaseOrder->status == 12)
                        {
                          ?>
                          {{$item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', '')." ".$supplier_packaging : "--"}}
                          <?php
                        }
                        else
                        {
                          ?>
                          {{ $item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', '').' '.$supplier_packaging : "--" }}
                          <?php
                        }

                      }
                      else
                      {
                        ?>
                        {{"N.A"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('12',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        if($item->PurchaseOrder->status == 12)
                        {
                          ?>
                          {{$item->billed_unit_per_package !== null ? number_format((float)$item->billed_unit_per_package, 3, '.', '') : "--" }}
                          <?php
                        }
                        else
                        {
                          ?>
                          {{ $item->billed_unit_per_package !== null ? $item->billed_unit_per_package : "--"}}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                        {{"N.A"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('13',$not_visible_arr))
                  <td>
                    <?php
                      if($item->order_product_id != null)
                      {
                        $selling_unit = ($item->order_product_id != null ? $item->order_product->product->sellingUnits->title : "N.A");
                        ?>
                        {{$item->order_product_id != null ? ($item->order_product->quantity != null ? $item->order_product->quantity : "--").' '.$selling_unit : "--"}}
                        <?php
                      }
                      else
                      {
                        ?>
                        {{"Stock"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('14',$not_visible_arr))
                  <td>
                    <?php
                      if($item->order_product_id != null)
                      {
                        ?>
                        {{$item->order_product_id != null ? ($item->order_product->number_of_pieces != null ? $item->order_product->number_of_pieces : "--") : "--"}}
                        <?php
                      }
                      else
                      {
                        ?>
                        {{"Stock"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('15',$not_visible_arr))
                  <td>
                    <?php
                      $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';
                      if($item->PurchaseOrder->status == 12)

                      {
                        $history = $item->pod_histories->where('column_name','Quantity')->first();
                        ?>

                        {{$item->quantity != null ? number_format($item->quantity,3,'.','') : "--" }}
                        <?php
                      }
                      else
                      {
                        ?>
                        {{ $item->quantity !== null ? number_format($item->quantity,3,'.','') : "--" }}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('16',$not_visible_arr))
                  <td>
                    <?php
                      $billed_unit = $item->product_id !== null ? @$item->product->units->title : 'N.A';

                      if($item->PurchaseOrder->status == 12)
                      {
                        $history = $item->pod_histories->where('column_name','Unit Price')->first();
                         ?>
                        {{$item->pod_unit_price !== null ? number_format(@$item->pod_unit_price, 3, '.', '') : "--" }}
                        <?php
                      }
                      else
                      {
                        ?>
                        {{$item->pod_unit_price !== null ? number_format($item->pod_unit_price,3,'.','') : "--"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('17',$not_visible_arr))
                  <td>
                    {{$item->pod_vat_actual}}
                  </td>
                  @endif

                  @if(!in_array('18',$not_visible_arr) && !in_array('17',$not_visible_arr))
                  <td>
                    {{$item->pod_unit_price_with_vat}}
                  </td>
                  @endif

                  @if(!in_array('19',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                          ?>
                          {{$item->last_updated_price_on != NULL ? Carbon::parse($item->last_updated_price_on)->format("d/m/Y") : "N.A"}}
                          <?php
                      }
                      else
                      {
                        ?>
                        {{"N.A"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('20',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        if($item->PurchaseOrder->status == 12)
                        {
                          ?>
                          {{$item->discount != null ? $item->discount : "--" }}
                          <?php
                        }
                        else
                        {
                          ?>
                          {{$item->discount !== null ? $item->discount : "--"}}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                        {{"N.A"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('21',$not_visible_arr))
                  <td>
                    <?php
                      $amount = $item->pod_unit_price * $item->quantity;
                      $amount = $amount - ($amount * (@$item->discount / 100));
                    ?>
                    {{$amount !== null ? number_format((float)$amount,3,'.','') : "--"}}
                  </td>
                  @endif
                  @if(!in_array('22',$not_visible_arr) && !in_array('17',$not_visible_arr))
                  <td>
                    <?php
                      $amount = $item->pod_unit_price_with_vat * $item->quantity;
                      $amount = $amount - ($amount * (@$item->discount / 100));
                    ?>
                    {{$amount !== null ? number_format((float)$amount,3,'.','') : "--"}}
                  </td>
                  @endif
                  @if(!in_array('23',$not_visible_arr))
                  <td>
                    {{$item->pod_total_gross_weight != NULL ? number_format((float)$item->pod_total_gross_weight,3,'.','') : "N.A"}}
                  </td>
                  @endif
                  @if(!in_array('24',$not_visible_arr))
                  <td>
                    <?php
                    if($item->order_id != null)
                    {
                      if($item->getOrder->in_status_prefix !== null && $item->getOrder->in_ref_prefix !== null && $item->getOrder->in_ref_id !== null )
                      {
                        $ref_no = @$item->getOrder->in_status_prefix.'-'.$item->getOrder->in_ref_prefix.$item->getOrder->in_ref_id;
                      }
                      elseif($item->getOrder->status_prefix !== null && $item->getOrder->ref_prefix !== null && $item->getOrder->ref_id !== null )
                      {
                        $ref_no = @$item->getOrder->status_prefix.'-'.$item->getOrder->ref_prefix.$item->getOrder->ref_id;
                      }
                      else
                      {
                        $ref_no = @$item->getOrder->customer->primary_sale_person->get_warehouse->order_short_code.@$item->getOrder->customer->CustomerCategory->short_code.@$item->getOrder->ref_id;
                      }
                      ?>
                      {{$ref_no}}
                      <?php
                    }
                    else
                    {
                      ?>
                      {{ '--'}}
                      <?php
                    }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('25',$not_visible_arr))
                  <td>
                    {{$item->product->weight !== null ? $item->product->weight : "--"}}
                  </td>
                  @endif
                  <td class="hideRow">{{$item->id}}</td>
                </tr>
                @endif
                @endforeach
              @endif
            </tbody>

    </table>

    </body>
</html>
