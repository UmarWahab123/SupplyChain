<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th>Customer Reference <br> Name </th>
                <th>{{$global_terminologies['brand']}}</th>
                <th>{{$global_terminologies['product_description']}}</th>
                <th>{{$global_terminologies['type']}}</th>
                <th>Selling Unit</th>
                <th>{{$global_terminologies['qty']}} Ordered</th>
                {{-- <th>{{$global_terminologies['qty']}} Sent</th>
                <th>{{$global_terminologies['qty']}} Received</th> --}}
                <th>Order #s</th>
                @if ($show_supplier_invoice_number == 1 && $is_bonded == 1)
                  <th>Supplier Inv# </th>
                @endif
                @if ($allow_custom_invoice_number == 1 && $is_bonded == 1)
                  <th>Custom's Inv# </th>
                @endif
                @if ($show_custom_line_number == 1 && $is_bonded == 1)
                  <th>Custom's Line# </th>
                @endif
                
            </tr>
        </thead>
            <tbody>
              @if($query->count() > 0)
                @foreach($query as $item)
                @if($item->quantity != NULL)
                <tr>
                    <td>
                        {{$item->product_id != null ? $item->product->refrence_code : '--' }}
                    </td> 
                  <td>
                    {{$item->customer_id !== null ? @$item->customer->reference_name : 'N.A'}}    
                  </td> 
                  <td>
                      {{$item->product->brand != null ?  $item->product->brand : '--'}} 
                  </td>
                  <td>
                    <?php
                        if($item->product_id != null)
                        {
                            if($item->PurchaseOrder->supplier_id != null)
                            {
                            $supplier_id = $item->PurchaseOrder->supplier_id;

                                $getDescription = App\Models\Common\SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                                ?>
                                {{$getDescription->supplier_description != null ? $getDescription->supplier_description : ($item->product->short_desc != null ? $item->product->short_desc : "--")}}
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
                  <td>
                    {{$item->product_id != null ?  $item->product->productType->title : '--'}} 
                  </td> 
                  <td>
                    {{$item->product_id !== null ? @$item->product->sellingUnits->title : 'N.A'}}
                  </td> 
                  <td>
                    <?php
                    if($item->PurchaseOrder->status == 20 || $item->PurchaseOrder->status == 21)
                    {
                        ?>
                        {{$item->quantity != null ? number_format($item->quantity,3,'.','') : "--" }}
                        <?php
                    }
                    else
                    {
                        ?>
                        {{$item->quantity !== null ? number_format($item->quantity,3,'.','') : "--"}}
                        <?php
                    } 
                    ?>
                  </td>  
                  {{-- <td>
                    {{$item->trasnfer_qty_shipped != NULL ? number_format($item->trasnfer_qty_shipped,3,'.','') : "N.A"}}
                  </td>  
                  <td>
                    {{$item->quantity_received != NULL ? number_format($item->quantity_received,3,'.','') : "N.A"}}  
                  </td> --}}
                  <td>
                  <?php
                    $ref_no = $item->order_id !== null ? $item->getOrder->ref_id : 'N.A';
                    if($item->order_id == null)
                    {
                        ?>
                        {{@$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.$ref_no}}
                        <?php
                    }
                    else
                    {
                        ?>
                        {{@$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.$ref_no}}
                        <?php
                    }
                  ?>  
                  </td>

                  @if ($show_supplier_invoice_number == 1 && $is_bonded == 1)
                    <td>
                      <?php
                        $html_string = '';
                        if($item->PurchaseOrder->status == 22)
                        {
                          $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id','purchase_order_details.id','stock_management_outs.p_o_d_id','stock_management_outs.po_group_id','purchase_order_details.product_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('stock_management_outs.p_o_d_id',$item->id)->whereNotNull('stock_management_outs.po_group_id')->where('purchase_orders.status',22)->where('purchase_order_details.product_id',$item->product_id);

                          $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('purchase_order_details.id','stock_management_outs.smi_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('purchase_order_details.id',$item->id)->pluck('smi_id')->toArray();

                          $groups_id = App\Models\Common\StockManagementOut::select('po_group_id','smi_id')->whereIn('smi_id',$find_group_of_prod)->whereNotNull('po_group_id')->groupBy('po_group_id')->pluck('po_group_id')->toArray();
                          // dd($groups_id);
                          $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->where('purchase_order_details.product_id',$item->product_id)->whereIn('po_group_details.po_group_id',$groups_id);

                          // dd('here');
                          $pos_id = $find_group_of_prod->pluck('purchase_order_details.po_id')->toArray();
                          $pos = App\Models\Common\PurchaseOrders\PurchaseOrder::select('id','invoice_number','ref_id','status')->whereIn('id',$pos_id)->whereIn('status',[14,15])->get();
                        }
                        else
                        {
                            $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->where('purchase_order_details.product_id',$item->product_id);

                            $pos_id = $find_group_of_prod->pluck('purchase_order_details.po_id')->toArray();
                            $pos = App\Models\Common\PurchaseOrders\PurchaseOrder::select('id','invoice_number','ref_id','status')->whereIn('id',$pos_id)->whereIn('status',[14,15])->get();
                        }
                        if($pos->count() > 0 && $pos->count() == 1)
                        {
                            foreach ($pos as $value) {
                                $html_string .= $value->invoice_number != null ? $value->invoice_number : '--';
                            }
                          ?>
                            {{$html_string}}
                          <?php
                        }
                        else if($pos->count() > 1)
                        {
                          $invoices = '';
                          foreach($pos as $inv)
                          {
                            if($inv->invoice_number != null)
                            {
                                $invoices .= $inv->invoice_number.', ';
                            }
                          }
                          if($invoices != ''){
                            ?>
                            {{$invoices}}
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
                  @if ($allow_custom_invoice_number == 1 && $is_bonded == 1)
                    <td>
                      <?php
                        $html_string = '';
                        if($item->PurchaseOrder->status == 22)
                        {
                            $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id','purchase_order_details.id','stock_management_outs.p_o_d_id','stock_management_outs.po_group_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('stock_management_outs.p_o_d_id',$item->id)->whereNotNull('stock_management_outs.po_group_id')->where('purchase_orders.status',22)->where('purchase_order_details.product_id',$item->product_id);
                            $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('purchase_order_details.id','stock_management_outs.smi_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('purchase_order_details.id',$item->id)->pluck('smi_id')->toArray();

                            $groups_id = App\Models\Common\StockManagementOut::select('po_group_id','smi_id')->whereIn('smi_id',$find_group_of_prod)->whereNotNull('po_group_id')->groupBy('po_group_id')->pluck('po_group_id')->toArray();
                            // dd($stock_entries);
                            // $groups_id = $find_group_of_prod->pluck('stock_management_outs.po_group_id')->toArray();
                        }
                        else
                        {
                            $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->whereIn('purchase_orders.status',[14,15])->where('purchase_order_details.product_id',$item->product_id);
                            $groups_id = $find_group_of_prod->pluck('po_group_details.po_group_id')->toArray();
                        }
                        $pos = App\Models\Common\PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_id)->get();
                        // dd($find_groups->count());

                        if($pos->count() > 0 && $pos->count() == 1)
                        {
                          $val = '';
                          foreach ($pos as $value) {
                              $val .= $value->custom_invoice_number != null ? $value->custom_invoice_number : '--';
                          }
                          ?>
                            {{$val}}
                          <?php
                        }
                        else if($pos->count() > 1)
                        {
                          $inv_numbers = '';
                          foreach($pos as $inv)
                          {
                            if($inv->custom_invoice_number != null)
                            {
                                $inv_numbers .= $inv->custom_invoice_number.', ';
                            }
                          }
                          if($inv_numbers != '')
                          {
                            ?>
                              {{$inv_numbers}}
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
                  @if ($show_custom_line_number == 1 && $is_bonded == 1)
                    <td>
                      <?php
                        $html_string = '';
                          if($item->PurchaseOrder->status == 22)
                          {
                              $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id','purchase_order_details.id','stock_management_outs.p_o_d_id','stock_management_outs.po_group_id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('stock_management_outs.p_o_d_id',$item->id)->whereNotNull('stock_management_outs.po_group_id')->where('purchase_orders.status',22)->where('purchase_order_details.product_id',$item->product_id);


                              $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('purchase_order_details.id','stock_management_outs.smi_id')->join('stock_management_outs','stock_management_outs.p_o_d_id','=','purchase_order_details.id')->where('purchase_order_details.id',$item->id)->pluck('smi_id')->toArray();

                              $groups_id = App\Models\Common\StockManagementOut::select('po_group_id','smi_id')->whereIn('smi_id',$find_group_of_prod)->whereNotNull('po_group_id')->groupBy('po_group_id')->pluck('po_group_id')->toArray();
                          }
                          else
                          {
                              $find_group_of_prod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_group_details.po_group_id','purchase_order_details.po_id','po_group_details.purchase_order_id','purchase_orders.status','purchase_orders.id')->join('po_group_details','purchase_order_details.po_id','=','po_group_details.purchase_order_id')->join('purchase_orders','purchase_orders.id','purchase_order_details.po_id')->whereIn('purchase_orders.status',[14,15])->where('purchase_order_details.product_id',$item->product_id);

                              $groups_id = $find_group_of_prod->pluck('po_group_details.po_group_id')->toArray();
                          }
                          $pos = App\Models\Common\PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm','po_group_product_details.custom_line_number')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_id)->get();
                          

                          if($pos->count() > 0 && $pos->count() == 1)
                              {
                                $val = '';
                                foreach ($pos as $value) {
                                    $val .= $value->custom_line_number != null ? $value->custom_line_number : '--';
                                }
                                ?>
                                {{$val}}
                                <?php
                              }
                              else if($pos->count() > 1)
                              {
                                $items = '';
                                foreach($pos as $inv)
                                {
                                  if($inv->custom_invoice_number != null)
                                  {
                                      $items .= $inv->custom_line_number.', ';
                                  }
                                }
                                if($items != ''){
                                  ?>
                                  {{$items}}
                              <?php
                                }else{
                                  ?>
                                  {{'--'}}
                                  <?php
                                }
                              }
                              else
                              {?>
                                  {{'--'}}
                                  <?php
                              } 
                      ?>
                    </td>
                  @endif
                    
                </tr>
                @endif
                @endforeach
              @endif
            </tbody> 
    
    </table>

    </body>
</html>
