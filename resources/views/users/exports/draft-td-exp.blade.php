<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
              {{-- <th>{{$global_terminologies['suppliers_product_reference_no']}}</th> --}}
              <th>{{$global_terminologies['our_reference_number']}}</th>
              <th>{{$global_terminologies['brand']}}</th>
              <th>{{$global_terminologies['product_description']}}</th>
              <th>{{$global_terminologies['type']}}</th>
              <th>Selling Unit</th>
              {{-- <th>Supplier Packaging</th>
              <th>Billed Unit <br> Per Package</th> --}}
              <th>{{$global_terminologies['qty']}}</th>
              <!-- @if(@$show_custom_line_number == 1 && @$to_warehouse_id == 1)
              <th>Custom's Line#</th>
              @endif
              @if(@$allow_custom_invoice_number == 1 && @$to_warehouse_id == 1)
              <th>Custom's Inv#</th>
              @endif
              @if(@$show_supplier_invoice_number == 1 && @$to_warehouse_id == 1)
              <th>Supplier Inv#</th>
              @endif -->
            </tr>
        </thead>
            <tbody>
              @if($query->count() > 0)
                @foreach($query as $item)
                @if($item->quantity != NULL)
                <tr>
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
                  <td>
                    {{$item->product_id !== null ? $item->getProduct->brand : "--"}}    
                  </td> 
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
                  <td>
                    {{$item->product_id !== null ? $item->getProduct->productType->title : "--"}}    
                  </td>
                  <td>
                    {{$item->product_id !== null ? $item->getProduct->sellingUnits->title : "--" }} 
                  </td> 
                  <!-- <td>
                    <?php
                       $billed_unit = $item->supplier_packaging !== null ? $item->supplier_packaging: "N.A" ;
                        if($item->product_id != null )
                        {
                            ?>
                            {{$item->desired_qty != null ? number_format($item->desired_qty,3,'.','').' '.$billed_unit : "--" }}
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
                  <td>
                      <?php
                        $billed_unit = $item->billed_unit_per_package !== null ? $item->billed_unit_per_package: "N.A" ;
                        if($item->product_id != null )
                        {
                            ?>
                            {{$item->billed_unit_per_package != null ? number_format($item->billed_unit_per_package,3,'.','') : "--" }}
                        <?php
                        }
                        else
                        {
                            ?>
                            {{"N.A"}}
                            <?php
                        }
                        ?>
                      
                  </td> -->
                  <td>
                    <?php
                        $billed_unit = $item->product_id !== null ? @$item->getProduct->units->title : 'N.A';
                    ?> 
                    {{$item->quantity != NULL ? number_format(@$item->quantity, 3, '.', '')." ".$billed_unit : "--"}}
                  </td>
                  <!-- @if(@$show_custom_line_number == 1 && @$to_warehouse_id == 1)
                  <td>
                    <?php
                      $html_string = '';
                      $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->whereIn('purchase_orders.status',[14,15])->where('purchase_order_details.product_id',$item->product_id);

                      $groups_id = $find_group_of_prod->pluck('po_group_details.po_group_id')->toArray();
                      $pos = App\Models\Common\PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm','po_group_product_details.custom_line_number')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_id)->get();
                      // dd($find_groups->count());

                      if($pos->count() > 0 && $pos->count() == 1)
                      {
                        $values = '';
                          foreach ($pos as $value) {
                              $values .= $value->custom_line_number != null ? $value->custom_line_number : '--';
                          }
                          ?>
                          {{$values}}
                          <?php
                      }
                      else if($pos->count() > 1)
                      {
                        $numbers = '';
                        foreach($pos as $inv)
                        {
                          if($inv->custom_invoice_number != null)
                          {
                              $numbers .= $inv->custom_line_number.', ';
                          }
                        }
                        if($numbers != ''){
                          ?>
                        {{$numbers}}
                        <?php
                        }
                        else{
                          ?>
                        {{'--'}}
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
                  @if(@$allow_custom_invoice_number == 1 && @$to_warehouse_id == 1)
                  <td>
                    <?php
                      $html_string = '';
                      $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->whereIn('purchase_orders.status',[14,15])->where('purchase_order_details.product_id',$item->product_id);

                      $groups_id = $find_group_of_prod->pluck('po_group_details.po_group_id')->toArray();
                      $pos = App\Models\Common\PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_id)->get();
                      // dd($find_groups->count());

                      if($pos->count() > 0 && $pos->count() == 1)
                          {
                            $group_val = '';
                              foreach ($pos as $value) {
                                  
                                  $group_val .= $value->custom_invoice_number != null ? $value->custom_invoice_number : '--';
                              }

                            ?>
                            {{$group_val}}
                            <?php
                          }
                          else if($pos->count() > 1)
                          {
                        
                            foreach($pos as $inv)
                            {
                            
                              $html_val = '';
                              if($inv->custom_invoice_number != null)
                              {
                                  $html_val .= $inv->custom_invoice_number.', ';
                              }
                            }
                            if($html_val != ''){
                              ?>
                              {{$html_val}}
                              <?php
                            }else{
                              ?>
                              {{'--'}}
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
                  @if(@$show_supplier_invoice_number == 1 && @$to_warehouse_id == 1)
                  <td>
                    <?php
                      $html_string = '';
                    $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->where('purchase_order_details.product_id',$item->product_id);

                    $pos_id = $find_group_of_prod->pluck('purchase_order_details.po_id')->toArray();
                    $pos = App\Models\Common\PurchaseOrders\PurchaseOrder::select('id','invoice_number','ref_id')->whereIn('id',$pos_id)->whereIn('status',[14,15])->get();
                    if($pos->count() > 0 && $pos->count() == 1)
                    {
                        $vals = '';
                        foreach ($pos as $value) {
                            $vals .= $value->invoice_number != null ? $value->invoice_number : '--';
                        }
                        ?>
                         {{$vals}}
                        <?php
                    }
                    else if($pos->count() > 1)
                    {
                      foreach($pos as $inv)
                      {
                        $numbers = '';
                        if($inv->invoice_number != null)
                        {
                            $numbers .= $inv->invoice_number.', ';
                        }
                      }
                      if($numbers != ''){
                          ?>
                          {{$numbers}}
                          <?php
                      }else{
                        ?>
                          {{'--'}}
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
                  @endif -->
                </tr>
                @endif
                @endforeach
              @endif
            </tbody> 
    
    </table>

    </body>
</html>
