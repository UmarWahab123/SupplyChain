@php
use Carbon\Carbon;
use App\OrderTransaction;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\ProductType;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Product;
@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th>Quotation File</th>
                    @if(!in_array('1',$not_visible_arr))<th>reference_no</th>@endif
                    @if(!in_array('2',$not_visible_arr))<th>HS Code</th>@endif
                    @if(!in_array('3',$not_visible_arr))<th>{{$global_terminologies['product_description']}} </th>@endif
                    @if(!in_array('4',$not_visible_arr))<th>Note</th>@endif
                    @if(!in_array('5',$not_visible_arr))<th class="inv-head">{{$global_terminologies['category']}} / {{$global_terminologies['subcategory']}}</th>@endif
                    @if(!in_array('6',$not_visible_arr))<th>{{$global_terminologies['type']}}</th>@endif
                    @if(!in_array('7',$not_visible_arr))<th>{{$global_terminologies['brand']}}</th>@endif
                    @if(!in_array('8',$not_visible_arr))<th>{{$global_terminologies['temprature_c']}}</th>@endif
                    @if(!in_array('9',$not_visible_arr))<th>{{$global_terminologies['supply_from']}}</th>@endif
                    <!-- <th>Sale Unit</th> -->
                    @if(!in_array('10',$not_visible_arr))<th>Available <br>{{$global_terminologies['qty']}}</th>@endif
                    @if(!in_array('11',$not_visible_arr))<th>PO QTY</th>@endif                      
                    @if(!in_array('12',$not_visible_arr))<th>PO No</th>@endif          
                    @if(!in_array('13',$not_visible_arr))<th>Customer <br> Last <br> Price</th>@endif
                    @if(!in_array('14',$not_visible_arr))<th>quantity_ordered</th>@endif
                    @if(!in_array('15',$not_visible_arr))<th>quantity_shipped</th>@endif
                    @if(!in_array('16',$not_visible_arr))<th>pieces_ordered</th>@endif
                    @if(!in_array('17',$not_visible_arr))<th>pieces_shipped</th>@endif
                    @if(!in_array('18',$not_visible_arr))<th>{{$global_terminologies['reference_price']}} </th>@endif
                    @if(!in_array('19',$not_visible_arr))<th>*{{$global_terminologies['default_price_type']}}</th>@endif
                    @if(!in_array('20',$not_visible_arr))<th>unit_price</th>@endif
                    @if(!in_array('21',$not_visible_arr))<th>Price Date</th>@endif
                    @if(!in_array('22',$not_visible_arr))<th>discount</th>@endif
                    @if(!in_array('23',$not_visible_arr))<th>Unit Price (with Discount)</th>@endif
                    @if(!in_array('24',$not_visible_arr))<th>vat</th>@endif
                    @if(!in_array('25',$not_visible_arr))<th>unit_price_vat</th>@endif
                    @if(!in_array('29',$not_visible_arr))<th>{{$global_terminologies['total_price_after_discount_without_vat']}}</th>@endif                      
                    @if(!in_array('26',$not_visible_arr))<th>{{$global_terminologies['total_amount_inc_vat']}}</th>@endif
                   
                    @if(!in_array('27',$not_visible_arr))<th>Restaurant Price</th>@endif   
                    @if(!in_array('28',$not_visible_arr))<th>{{$global_terminologies['note_two']}}</th>@endif 
                    <th>order_product_id</th>
                         
                </tr>
                <tr>
                    <th>Quotation File</th>
                    @if(!in_array('1',$not_visible_arr))<th>{{$global_terminologies['our_reference_number']}}</th>@endif
                    @if(!in_array('2',$not_visible_arr))<th>HS Code</th>@endif
                    @if(!in_array('3',$not_visible_arr))<th>{{$global_terminologies['product_description']}} </th>@endif
                    @if(!in_array('4',$not_visible_arr))<th>Note</th>@endif
                    @if(!in_array('5',$not_visible_arr))<th class="inv-head">{{$global_terminologies['category']}} / {{$global_terminologies['subcategory']}}</th>@endif
                    @if(!in_array('6',$not_visible_arr))<th>{{$global_terminologies['type']}}</th>@endif
                    @if(!in_array('7',$not_visible_arr))<th>{{$global_terminologies['brand']}}</th>@endif
                    @if(!in_array('8',$not_visible_arr))<th>{{$global_terminologies['temprature_c']}}</th>@endif
                    @if(!in_array('9',$not_visible_arr))<th>{{$global_terminologies['supply_from']}}</th>@endif
                    <!-- <th>Sale Unit</th> -->
                    @if(!in_array('10',$not_visible_arr))<th>Available <br>{{$global_terminologies['qty']}}</th>@endif
                    @if(!in_array('11',$not_visible_arr))<th>PO QTY</th>@endif                      
                    @if(!in_array('12',$not_visible_arr))<th>PO No</th>@endif          
                    @if(!in_array('13',$not_visible_arr))<th>Customer <br> Last <br> Price</th>@endif
                    @if(!in_array('14',$not_visible_arr))<th># {{$global_terminologies['qty']}}<br>Ordered</th>@endif
                    @if(!in_array('15',$not_visible_arr))<th># {{$global_terminologies['qty']}} Sent</th>@endif
                    @if(!in_array('16',$not_visible_arr))<th># {{$global_terminologies['pieces']}}<br>Ordered</th>@endif
                    @if(!in_array('17',$not_visible_arr))<th># {{$global_terminologies['pieces']}} Sent</th>@endif
                    @if(!in_array('18',$not_visible_arr))<th>{{$global_terminologies['reference_price']}} </th>@endif
                    @if(!in_array('19',$not_visible_arr))<th>*{{$global_terminologies['default_price_type']}}</th>@endif
                    @if(!in_array('20',$not_visible_arr))<th>{{$global_terminologies['default_price_type_wo_vat']}}</th>@endif
                    @if(!in_array('21',$not_visible_arr))<th>Price Date</th>@endif
                    @if(!in_array('22',$not_visible_arr))<th>Discount %</th>@endif
                    @if(!in_array('23',$not_visible_arr))<th>Unit Price (with Discount)</th>@endif
                    @if(!in_array('24',$not_visible_arr))<th>VAT %</th>@endif
                    @if(!in_array('25',$not_visible_arr))<th>{{$global_terminologies['unit_price_vat']}}</th>@endif
                    @if(!in_array('29',$not_visible_arr))<th>{{$global_terminologies['total_price_after_discount_without_vat']}}</th>@endif                      
                    @if(!in_array('26',$not_visible_arr))<th>{{$global_terminologies['total_amount_inc_vat']}}</th>@endif
                   
                    @if(!in_array('27',$not_visible_arr))<th>Restaurant Price</th>@endif   
                    @if(!in_array('28',$not_visible_arr))<th>{{$global_terminologies['note_two']}}</th>@endif 
                    <th>Order Product ID</th>
                         
                </tr>
            </thead>
            <tbody>
                @if($query != null)
                @foreach($query as $item)
                    <tr>
                        <td>Quotation File</td>
                        @if(!in_array('1',$not_visible_arr))
                        <td>
                            <?php
                                if($item->product == null )
                                {
                                ?>
                                {{"N.A"}}
                                <?php
                                }
                                else
                                {              
                                $item->product->refrence_code ? $reference_code = $item->product->refrence_code : $reference_code = "N.A";
                                ?>
                                    {{$reference_code}}
                                    <?php
                                } 
                            ?>    
                        </td>
                        @endif
                        @if(!in_array('2',$not_visible_arr))
                        <td>
                            <?php
                                if($item->product_id!=null)
                                {
                                ?>
                                {{$item->product->hs_code}}
                                <?php
                                }
                                else
                                {
                            ?>
                                {{'N.A'}}   
                                <?php
                                }
                                ?>
                        </td>
                        @endif
                        @if(!in_array('3',$not_visible_arr))<td>{{$item->short_desc != null ? $item->short_desc : "--"}}</td>@endif
                        @if(!in_array('4',$not_visible_arr))<td>
                            <?php
                                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                                {
                                ?>
                                    {{"--"}}
                                    <?php
                                }
                                else
                                {
                                    // check already uploaded images //
                                    $notes = OrderProductNote::where('order_product_id', $item->id)->get();
                                    $html_string = '';
                                    if($notes->count() > 0){  
                                        foreach($notes as $note){
                                            $html_string .= $note->note.' ,';
                                        }
                                    }else{
                                        $html_string .= '';
                                    }
                                    ?>
                                    {{$html_string}}
                                    <?php
                                }
                            ?>    
                        </td>@endif
                        @if(!in_array('5',$not_visible_arr))<td>
                            <?php
                                 $html_string=null;
                                if($item->category_id != null)
                                {
                                $html_string = $item->category_id != null ? $item->product->productSubCategory->get_Parent->title.' / '.$item->product->productSubCategory->title: "--";
                                }
                                else
                                {
                                $html_string = $item->product_id!= null ? $item->product->productSubCategory->get_Parent->title.' / '.$item->product->productSubCategory->title: "--";
                                }
                                ?>
                                {{$html_string}}   
                        </td>@endif
                        @if(!in_array('6',$not_visible_arr))<td>
                            <?php
                                $product_type = ProductType::select('id','title')->get();
                                if($item->type_id == null)
                                {
                                $html_string = '';
                                }
                                else
                                {
                                $html_string = $item->productType->title;
                                }
                                ?>
                                {{ $html_string }}   
                        </td>@endif
                        @if(!in_array('7',$not_visible_arr))<td>{{$item->brand != null ? $item->brand : "--"}}</td>@endif
                        @if(!in_array('8',$not_visible_arr))<td>
                            @if ($item->product_id !== NULL)
                                {{$item->unit ? $item->product->product_temprature_c : "N.A"}}
                            @endif    
                        </td>@endif
                        @if(!in_array('9',$not_visible_arr))<td>
                            <?php
                                if($item->product_id == null)
                                {
                                    ?>
                                    {{"N.A"}}
                                    <?php
                                }
                                else
                                {
                                    
                                    $label = $item->from_warehouse_id != null ? @$item->from_warehouse->warehouse_title : (@$item->from_supplier->reference_name != null ? $item->from_supplier->reference_name : "--");
                                    $html =  $label;
                                    ?>
                                    {{$html}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        @if(!in_array('10',$not_visible_arr))<td>
                            <?php
                                $warehouse_id = $item->from_warehouse_id != null ? $item->from_warehouse_id : Auth::user()->warehouse_id;
                                $stock = $item->product != null ? $item->product->get_stock($item->product->id, $warehouse_id) : 'N.A';
                               
                            ?>  
                            {{-- $pids = PurchaseOrder::where('status',21)->whereHas('PoWarehouse',function($qq) use($warehouse_id){
                                    $qq->where('from_warehouse_id',$warehouse_id);
                                })->pluck('id')->toArray();
                                $pqty =  PurchaseOrderDetail::whereIn('po_id',$pids)->where('product_id',$item->id)->sum('quantity');

                                $warehouse_product = WarehouseProduct::where('product_id',$item->product_id)->where('warehouse_id',$warehouse_id)->first();
                                $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:' 0';

                                $ids =  Order::where('primary_status',2)->where('id','!=',$item->order_id)->whereHas('order_products',function($qq) use($warehouse_id){
                                    $qq->where('from_warehouse_id',$warehouse_id);
                                })->pluck('id')->toArray();
                                
                                $ordered_qty =  OrderProduct::whereIn('order_id',$ids)->where('product_id',$item->product_id)->sum('quantity');
                                $order_products=$stock_qty-($ordered_qty+$pqty); --}}
                            {{round($stock,3)}}  
                        </td>@endif
                        @if(!in_array('11',$not_visible_arr))<td>
                            <?php
                                if($item->status > 7){
                                    ?>
                                    {{$item->purchase_order_detail != null ?  $item->purchase_order_detail->quantity.' '.$item->product->units->title : ''}}
                                    <?php
                                }else{
                                    ?>
                                    {{''}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        @if(!in_array('12',$not_visible_arr))<td>
                            <?php
                                if($item->status > 7){
                                    ?>
                                    {{$item->purchase_order_detail != null ?  $item->purchase_order_detail->PurchaseOrder->ref_id: ''}}
                                    <?php
                                }else{
                                    ?>
                                    {{''}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        @if(!in_array('13',$not_visible_arr))<td>
                            <?php
                                $order = Order::with('order_products')->whereHas('order_products',function($q) use($item) {
                                    $q->where('is_billed','Product');
                                    $q->where('product_id',$item->product_id);
                                })->where('customer_id',$item->get_order->customer_id)->where('primary_status',3)->orderBy('converted_to_invoice_on','desc')->first();

                                if($order)
                                {
                                    $cust_last_price = number_format($order->order_products->where('product_id',$item->product_id)->first()->unit_price, 2, '.', '');
                                }
                                else
                                {
                                    $cust_last_price = "N.A"; 
                                }
                            ?>
                            {{$cust_last_price}}
                        </td>@endif
                        @if(!in_array('14',$not_visible_arr))<td>
                            <?php
                                if($item->product_id !== NULL)
                                {
                                    $sale_unit = $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
                                }
                                else
                                {

                                $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '');
                                $html = $unit;
                                $sale_unit = $html;
                                }
                                $html = '';
                                if(@$item->get_order->primary_status == 3){
                                    $html .= $item->quantity != null ? $item->quantity : "--" ;
                                }else{
                                $html .= $item->quantity != null ? $item->quantity : "--";
                                }
                                // $html .= @$sale_unit;

                                // if(@$item->get_order->primary_status !== 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                                    

                                //     $html .= '
                                //     <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                                //     $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' id="checkbox_quantity">';
                                    
                                //     $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
                                    
                                //     // }else if(@$item->quantity != null && @$item->number_of_pieces != null){
                                //     //     $html .= '
                                //     //   <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                                //     //   $html .= '<input type="checkbox" class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->quantity.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' disabled>';
                                    
                                //     //   $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
                                //     //   }
                                //     }
                                //     return $html;
                                ?>
                                {{$html}}    
                        </td>@endif
                        @if(!in_array('15',$not_visible_arr))<td>
                            <?php
                                if(@$item->is_billed !== 'Billed')
                                {
                                    if($item->product_id !== NULL)
                                    {
                                    $sale_unit = $item->product && $item->product->sellingUnits ? @$item->product->sellingUnits->title : "N.A";
                                    }
                                    else
                                    {
                                        $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '');
                                        if(@$item->product_id === NULL){
                                            $html = $unit;
                                        }else{
                                            $html = $unit;
                                        }
                                        $sale_unit = $html;
                                    }
                                } 
                                $html = $item->qty_shipped != null ? $item->qty_shipped : "--";
                                // $html .= @$sale_unit;
                                //     if(@$item->get_order->primary_status == 3 && Auth::user()->role_id != 7 && @$item->is_billed !== 'Billed'){
                                //     $html .= '
                                // <div class="custom-control custom-radio custom-control-inline pull-right mr-0">';
                                // $html .= '<input type="checkbox" '.$radio.' class="condition custom-control-input" id="is_retail'.@$item->id.'" name="is_retail" data-id="'.$item->id.' '.@$item->qty_shipped.'" value="qty" ' .($item->is_retail == "qty" ? "checked" : ""). ' id="checkbox_qty_shipped">';
                                
                                // $html .='<label class="custom-control-label" for="is_retail'.@$item->id.'"></label></div>';
                                // }
                                ?>
                                {{$html}}    
                        </td>@endif
                        @if(!in_array('16',$not_visible_arr))<td>
                            <?php
                                if(@$item->get_order->primary_status == 3){
                                    if(@$item->is_billed !== 'Billed')
                                    {
                                    $html = $item->number_of_pieces != null ? $item->number_of_pieces : "--" ;
                                    }
                                    else
                                    {
                                    $html = 'N.A';
                                    }
                                }else if(@$item->is_billed !== 'Billed'){
                                    $html = $item->number_of_pieces != null ? $item->number_of_pieces : "--";
                                }
                                else
                                {
                                    $html = 'N.A';
                                }
                            ?> 
                            {{$html}} 
                        </td>@endif
                        @if(!in_array('17',$not_visible_arr))<td> 
                            <?php
                                if($item->is_billed !== 'Billed'){
                                    $html = $item->pcs_shipped != null ? $item->pcs_shipped : "--";
                                }
                                else
                                {
                                    $html = 'N.A';
                                }
                            ?>
                            {{$html}} 
                        </td>@endif
                        @if(!in_array('18',$not_visible_arr))<td>
                            <?php
                                if($item->exp_unit_cost == null)
                                {
                                    ?>
                                    {{"N.A"}}
                                    <?php
                                }
                                else
                                { 
                                    $html_string = number_format(floor($item->exp_unit_cost*100)/100, 2,'.','');
                                } 
                            ?>  
                            {{$html_string}}  
                        </td>@endif
                        @if(!in_array('19',$not_visible_arr))<td>
                            <?php
                                if($item->is_billed != "Product")
                                {
                                    ?>
                                    {{"N.A"}}
                                    <?php
                                }
                                if($item->margin == null)
                                {
                                    ?>
                                    {{"Fixed Price"}}
                                    <?php
                                }
                                else
                                {
                                    if(is_numeric($item->margin))
                                    {
                                    ?>
                                    {{$item->margin.'%'}}
                                    <?php
                                    }
                                    else
                                    {
                                    ?>
                                    {{$item->margin}}
                                    <?php
                                    }
                                }
                            ?>   
                        </td>@endif
                        @if(!in_array('20',$not_visible_arr))<td>
                            <?php
                                if(is_numeric($item->margin)){
                                    $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
                                }
                                $unit_price = number_format($item->unit_price, 2, '.', '');
                                $html = number_format(@$item->unit_price, 2,'.','');
                            ?>
                            {{$html}}    
                        </td>@endif
                        @if(!in_array('21',$not_visible_arr))<td>
                            <?php
                                if($item->last_updated_price_on!=null)
                                {
                                    ?>
                                    {{Carbon::parse($item->last_updated_price_on)->format('d/m/Y')}}
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    {{''}}
                                    <?php
                                }
                            ?>    
                        </td>@endif
                        @if(!in_array('22',$not_visible_arr))<td>{{$item->discount != null ? $item->discount : "" }}</td>@endif
                        @if(!in_array('23',$not_visible_arr))<td>{{$item->unit_price_with_discount != null ? number_format($item->unit_price_with_discount, 2, '.', ''): "--" }}</td>@endif
                        @if(!in_array('24',$not_visible_arr))<td>
                            <?php
                                if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6)
                                {
                                    ?>
                                {{$item->vat != null ? $item->vat : ''}}
                                <?php
                                }
                                else
                                {
                                ?>
                                    {{$item->vat != null ? $item->vat : ''}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        @if(!in_array('25',$not_visible_arr))<td>
                            <?php
                                $unit_price = round($item->unit_price,2);
                                $vat = $item->vat;
                                    $vat_amount = @$unit_price * ( @$vat / 100 );
                                    if($item->unit_price_with_vat !== null)
                                    {
                                        $unit_price_with_vat = number_format(@$item->unit_price_with_vat,4,'.','');
                                        $unit_price_with_vat2 =  preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);
                                    }
                                    else
                                    {
                                        $unit_price_with_vat = number_format((@$unit_price+@$vat_amount),4,'.','');
                                        $unit_price_with_vat2 = number_format(floor((@$unit_price+@$vat_amount)*100)/100,2,'.','');
                                    }

                                    $html = $unit_price_with_vat2;
                            ?>
                            {{$html}}
                        </td>@endif
                        @if(!in_array('29',$not_visible_arr))<td>
                            <?php
                                if($item->total_price!="")
                                {
                                    $formated_value = number_format($item->total_price,3,'.','');
                                    ?>
                                    {{$formated_value !== null ? $formated_value : ''}}
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    {{'N.A'}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        @if(!in_array('26',$not_visible_arr))<td>
                            <?php
                                $unit_price_with_vat2 =  preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);
                            ?>
                            {{$item->total_price_with_vat !== null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $item->total_price_with_vat),2,'.','') : "--"}}
                        </td>@endif                       
                        @if(!in_array('27',$not_visible_arr))<td>
                            <?php
                                $getRecord = new Product;
                                $prodFixPrice   = $getRecord->getDataOfProductMargins($item->product_id, 1, "prodFixPrice");
                                if($prodFixPrice!="N.A")
                                {
                                    $formated_value = number_format($prodFixPrice->fixed_price,3,'.','');
                                    ?>
                                    {{$formated_value !== null ? $formated_value : ''}}
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    {{'N.A'}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        @if(!in_array('28',$not_visible_arr))<td>
                            <?php
                                 if($item->product_id != null)
                                {
                                    if($item->product->product_notes!=null)
                                    {
                                        ?>
                                    {{$item->product->product_notes}}
                                    <?php
                                    }
                                    else
                                    {
                                        ?>
                                    {{''}}
                                    <?php
                                    }
                                }
                                else
                                {
                                    ?>
                                    {{''}}
                                    <?php
                                }
                            ?>
                        </td>@endif
                        
                        <td>{{$item->id}}</td>
                    </tr>
                @endforeach
                @endif

            </tbody> 
    
        </table>

    </body>
</html>