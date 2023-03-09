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
              <th>Create Direct PO</th>
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
              @if(!in_array('17',$not_visible_arr))<th>Purchasing VAT</th>@endif
              @if(!in_array('18',$not_visible_arr) && !in_array('17',$not_visible_arr))<th>Unit Price (+VAT)</th>@endif
              @if(!in_array('19',$not_visible_arr))<th>Price Date</th>@endif
              @if(!in_array('20',$not_visible_arr))<th>Discount</th>@endif
              @if(!in_array('21',$not_visible_arr))<th>Amount</th>@endif
              @if(!in_array('22',$not_visible_arr) && !in_array('17',$not_visible_arr))<th>Amount (Inc VAT)</th>@endif
              @if(!in_array('24',$not_visible_arr))<th>Total {{$global_terminologies['gross_weight']}}</th>@endif
              @if(!in_array('23',$not_visible_arr))<th>Order #</th>@endif
              @if(!in_array('25',$not_visible_arr))<th>{{$global_terminologies['avg_units_for-sales'] }}</th>@endif
              <th>Row ID</th>
            </tr>
        </thead>
            <tbody>
              @if($query != null && $query->count() > 0)
                @foreach($query->get() as $item)
                @if($item->is_billed == "Product")
                <tr>
                  <td>Create Direct PO</td>
                  @if(!in_array('2',$not_visible_arr))
                  <td>
                    <?php
                        if($item->product_id != null)
                        {
                            if($item->draftPo->supplier_id == NULL && $item->draftPo->from_warehouse_id != NULL)
                            {
                                $supplier_id = $item->getProduct->supplier_id;
                            }
                            else
                            {
                                $supplier_id = $item->draftPo->getSupplier->id;
                            }

                            $gettingProdSuppData = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                            $ref_no = $gettingProdSuppData->product_supplier_reference_no != null ? $gettingProdSuppData->product_supplier_reference_no : "--";
                            ?>
                            {{$ref_no}}
                            <?php
                        }
                        else
                        {
                            ?>
                            {{"N.A"}}
                        <?php } ?>

                  </td>
                  @endif
                  @if(!in_array('3',$not_visible_arr))
                  <td>
                    <?php
                        if($item->product_id != null)
                        {
                            $ref_no = $item->product_id !== null ? $item->getProduct->refrence_code : "--" ;
                            ?>
                            {{$ref_no}}
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
                  @if(!in_array('4',$not_visible_arr))
                  <td>
                    {{'--'}}
                  </td>
                  @endif
                  @if(!in_array('5',$not_visible_arr))
                  <td>
                    {{$item->product_id !== null ? $item->getProduct->brand : "--"}}
                  </td>
                  @endif
                  @if(!in_array('6',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        if($item->draftPo->supplier_id != NULL)
                        {
                          $supplier_id = $item->draftPo->getSupplier->id;

                          $getDescription = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                          ?>
                            {{$getDescription->supplier_description != null ? $getDescription->supplier_description : ($item->getProduct->short_desc != null ? $item->getProduct->short_desc : "--")}}
                         <?php
                        }
                        else
                        {
                          ?>
                            {{$item->getProduct->short_desc != null ? $item->getProduct->short_desc : "--" }}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                          {{$item->billed_desc != NULL ? $item->billed_desc : "--"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('7',$not_visible_arr))
                  <td>
                    {{$item->product_id !== null ? $item->getProduct->productType->title : "--"}}
                  </td>
                  @endif
                  @if(!in_array('8',$not_visible_arr))
                  <td>
                    <?php
                      if($item->product_id != null)
                      {
                        if($item->draftPo->supplier_id != NULL)
                        {
                          $supplier_id = $item->draftPo->getSupplier->id;

                          $getDescription = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                          ?>
                          {{$getDescription->supplier_description != null ? $getDescription->supplier_description : ($item->getProduct->short_desc != null ? $item->getProduct->short_desc : "--")}}
                          <?php
                        }
                        else
                        {
                          ?>
                            {{$item->getProduct->short_desc != null ? $item->getProduct->short_desc : "--"}}
                          <?php
                        }
                      }
                      else
                      {
                        ?>
                          {{$item->billed_desc != NULL ? $item->billed_desc : "--"}}
                        <?php
                      }
                    ?>
                  </td>
                  @endif
                  @if(!in_array('9',$not_visible_arr))
                  <td>
                    <?php
                      if($item->draftPo->supplier_id != null)
                      {
                        if($item->product_id != null)
                        {
                          $gettingProdSuppData = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$item->draftPo->supplier_id)->first();

                          $leading_time = $gettingProdSuppData !== null ? $gettingProdSuppData->leading_time : "--";
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
                      if($item->draftPo->supplier_id != null)
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
                      $billed_unit = $item->supplier_packaging !== null ? $item->supplier_packaging: "N.A" ;
                      if($item->product_id != null )
                      {
                        ?>
                          {{$item->desired_qty != null ? number_format($item->desired_qty,3,'.','')." ".$billed_unit : "--" }}
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
                  @if(!in_array('12',$not_visible_arr))
                  <td>
                    <?php
                      $billed_unit = $item->billed_unit_per_package !== null ? $item->billed_unit_per_package: "N.A" ;
                      if($item->product_id != null )
                      {
                        ?>
                          {{$item->billed_unit_per_package != null ? number_format($item->billed_unit_per_package,3,'.',''): "--" }}
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
                  @if(!in_array('13',$not_visible_arr))
                  <td>
                    {{ '--' }}
                  </td>
                  @endif
                  @if(!in_array('14',$not_visible_arr))
                  <td>
                    {{ '--' }}
                  </td>
                  @endif
                  @if(!in_array('15',$not_visible_arr))
                  <td>
                    <?php
                      $billed_unit = $item->product_id !== null ? @$item->getProduct->units->title : 'N.A';
                    ?>
                    {{$item->quantity != NULL ? number_format($item->quantity,3,'.','') : "--" }}
                  </td>
                  @endif
                  @if(!in_array('16',$not_visible_arr))
                  <td>
                    <?php
                      $billed_unit = $item->product_id !== null ? @$item->getProduct->units->title : 'N.A';
                    ?>
                      {{$item->pod_unit_price !== null ? number_format(@$item->pod_unit_price, 3, '.', '') : "--"}}
                  </td>
                  @endif
                  @if(!in_array('17',$not_visible_arr))
                  <td>
                      {{$item->pod_vat_actual !== null ? number_format(@$item->pod_vat_actual, 3, '.', '') : "--"}}
                  </td>
                  @endif
                  @if(!in_array('18',$not_visible_arr) && !in_array('17',$not_visible_arr))
                  <td>
                      {{$item->pod_unit_price_with_vat !== null ? number_format(@$item->pod_unit_price_with_vat, 3, '.', '') : "--"}}
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
                        ?>
                          {{$item->discount != null ? $item->discount : "--" }}
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
                  @if(!in_array('21',$not_visible_arr))
                  <td>
                    <?php
                      $amount = $item->pod_unit_price * $item->quantity;
                      $amount = $amount - ($amount * (@$item->discount / 100));
                    ?>
                    {{$amount !== null ? number_format($amount,3,'.','') : "--"}}
                  </td>
                  @endif

                  @if(!in_array('22',$not_visible_arr) && !in_array('17',$not_visible_arr))
                  <td>
                    <?php
                      $amount = $item->pod_unit_price_with_vat * $item->quantity;
                      $amount = $amount - ($amount * (@$item->discount / 100));
                    ?>
                    {{$amount !== null ? number_format($amount,3,'.','') : "--"}}
                  </td>
                  @endif

                  @if(!in_array('24',$not_visible_arr))
                  <td>
                    {{$item->pod_total_gross_weight !== null ? number_format($item->pod_total_gross_weight, 3, '.', '') : 'N.A'}}
                  </td>
                  @endif
                  @if(!in_array('23',$not_visible_arr))
                  <td>
                    {{'--'}}
                  </td>
                  @endif
                   @if(!in_array('25',$not_visible_arr))
                  <td>
                    {{$item->getProduct->weight !== null ? $item->getProduct->weight : "--"}}
                  </td>
                  @endif
                  <td>{{$item->id}}</td>
                </tr>
                @endif
                @endforeach
              @endif
            </tbody>
    </table>
    </body>
</html>
