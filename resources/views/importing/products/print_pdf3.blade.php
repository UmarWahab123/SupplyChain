  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>PO Group</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- <script src="{{asset('public/js/html2pdf.min.js')}}"></script> -->
    <style type="text/css">
    	  table { font-size: 10px;
  border-collapse: collapse;
  text-align: center;
         }
         td, th {
  border: 1px solid #dddddd;
 
}
 

.verticalTableHeader {
    text-align:center;
    white-space:nowrap;
    transform-origin:50% 50%;
    -webkit-transform: rotate(270deg);
    -moz-transform: rotate(270deg);
    -ms-transform: rotate(270deg);
    -o-transform: rotate(270deg);
    transform: rotate(270deg);
    
}
.verticalTableHeader:before {
    content:'';
    padding-top:110%;/* takes width as reference, + 10% for faking some extra padding */
    display:inline-block;
    vertical-align:middle;
}

    table tr td 
    {
      vertical-align: top; padding-bottom: 3px;
    }
  
      .rotate_text
      {
         -moz-transform:rotate(-90deg); 
         -moz-transform-origin: top left;
         -webkit-transform: rotate(-90deg);
         -webkit-transform-origin: top left;
         -o-transform: rotate(-90deg);
         -o-transform-origin:  top left;
          position:relative;
         top:10px;
         left: 10px;
      }
      .rotated_cell
      {
         height:50px;
         width: 5px;
         vertical-align:bottom;
      }
   
    .inv-total-td span {
        font-weight: bold;
        border-bottom: 2px solid #000;
        display: inline-block;
        padding: 0px 5px 2px;
    }
     .before {
        page-break-before: always;
      }
      .before > td{
        height: 80px;
        border: 1px solid white;
      }
/*    .main-table tr td {
        page-break-inside: avoid;
    }*/
    </style>
    @php
    use Carbon\Carbon;
    use App\Models\Common\Product;    
    use App\Models\Common\ProductType;
    use App\Models\Common\SupplierProducts;
    use App\Models\Common\Supplier;
    use App\Models\Common\Courier;
  @endphp

  </head>

  <body style="font-family: sans-serif;">

    <div id="invoice">
    <div id="print_lables_3_3">
<table class="table" style="width: 30%;border: 0px;border: none;text-align: left;">
              <tbody>
                <tr>
                  <td colspan="2" style="border: 0px;">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;"><strong>Group No {{@$group_detail[0]->po_group->ref_id}}<br>Product Receiving Records</strong></h5>
                  </td>
                </tr>
                <tr>
                  <td style="width: 50%;border: none;">                    
                    <p style="margin: 0px 0px 6px;">PO Date: {{Carbon::parse(@$group_detail[0]->created_at)->format('d/m/Y')}}</p>
                    <p style="margin: 0px 0px 6px;">AWB or B/L:{{$group_detail[0]->bill_of_landing_or_airway_bill}}</p>                   
                    <p style="margin: 0px 0px 6px;">
                      @php
                        $courier = Courier::find($group_detail[0]->courier);
                      @endphp
                      courier: {{@$courier->title}}
                    </p>                   
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- <h1 style="font-style: italic; margin-top: 0px; margin-bottom: 10px;text-align: center;">PO GROUP</h1> -->

    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;">

  <thead>
  <col>
  <colgroup span="2"></colgroup>
  <colgroup span="2"></colgroup>
  <tr>
    <!-- <td rowspan="2"></td> -->
    <th rowspan="1" colspan="5" style="text-align: left;padding: 12px 2px;">{{Carbon::parse(@$group_detail[0]->datee)->format('M-d-Y')}}</th>
    <th rowspan="1" colspan="2" style="text-align: center;">truck 1:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">temp:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">truck 2:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">temp:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">truck 3:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">temp:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">truck 4:</th>
    <th rowspan="1" colspan="2" style="text-align: center;">temp:</th>

    
  </tr>
  <tr>
    <!-- <td rowspan="2"></td> -->
    <th rowspan="2" style="text-align: center;">Po<br>No</th>
    <th rowspan="2" style="text-align: center;">Ref#</th>

    <th rowspan="2" style="text-align: center;">Supplier</th>
    <th rowspan="2" style="text-align: center;">Product Reference #</th>

    <th rowspan="2" style="text-align: center;">Description</th>

    <th colspan="4" scope="colgroup" style="text-align: center;">@if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif of Goods</th>
    <th colspan="4" scope="colgroup" style="text-align: center;">Goods Condition</th>
    <th colspan="4" scope="colgroup" style="text-align: center;">Goods Type</th>
    <th rowspan="2" style="text-align: center;">@if(!array_key_exists('temprature_c',$global_terminologies)) Temprature<br> C @else {{$global_terminologies['temprature_c']}}  @endif</th>
    <th rowspan="2" style="text-align: center;">Checker</th>

    <th colspan="2" scope="colgroup" style="text-align: center;">Problems and solutions (if found)</th>
    <th rowspan="2" style="text-align: center;">Authorised<br>Changes</th>


  </tr>
  <tr>
    <th scope="col">Unit</th>
    <th scope="col">Qty</th>
    <th scope="col" class="">Qty<br>Sent</th>
    <th scope="col">Diff</th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Normal</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Problem</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Passed</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Failed</div></th>
<!--     <th scope="col" class="">Problem</th>
    <th scope="col" class="">Passed</th>
    <th scope="col" class="">Failed</th> -->
  
     <th scope="col" class="rotated_cell"><div class="rotate_text">Frozen</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Chilled</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Dried</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Fresh</div></th>
  <!-- 
  <th scope="col" class="">Frozen</th>
    <th scope="col" class="">Chilled</th>
    <th scope="col" class="">Dried</th> -->
        <th scope="col">Problem <br>Found</th>
    <th scope="col">Solution</th>
  </tr>
</thead>
<tbody>
  <!-- @php $i = 0; $first = true; $j = 0; @endphp -->
    @foreach($group_detail as $item)
                <tr>
                  <th scope="row" > 
                    @php
                    $occurrence = $item->occurrence;
                    if($occurrence == 1)
                    {
                      $purchase_orders_ids =  App\Models\Common\PurchaseOrders\PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
                      $pod = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::select('po_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
                      if($pod[0]->PurchaseOrder->ref_id !== null){
                      $html_string = $pod[0]->PurchaseOrder->ref_id;
                    }else{
                      $html_string = "--";
                    }
                          }
                    else
                    {
                      $html_string = '--';
                    }
                    @endphp
                  {{$html_string}}
                  </th>
                  
                  <td>
                    
                  @php

                  if($item->supplier_id !== NULL)
                  {
                    $sup_name = App\Models\Common\SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
                    $sup_ref = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
                  }
                  else
                  {
                    $sup_ref = "N.A";
                  }
                  @endphp
                  {{$sup_ref}}
                  
                  </td>
                  
                  <td>
                    
                  @php

                  if($item->supplier_id !== NULL)
                  { 
                    $sup_name = $item->get_supplier->reference_name;
                  }
                  else
                  {
                    $sup_name = $item->get_warehouse->warehouse_title;
                  }          
                  @endphp 
                  {{$sup_name}} 
                    
                  </td>

                  

                  <td>
                    
                  @php
                  $product_ref = $item->product->refrence_code;          
                  @endphp
                  {{$product_ref}}
                  
                  </td>

                  <td style="max-width: 100pt;white-space: normal;" height="55">
                  
                    <span >
                      
                  @php
                  $product_ref = $item->product->short_desc;          
                  @endphp
                  {{$product_ref}}
                  
                    </span>
                  

                  <td>
                    {{$item->product->units->title != null ? $item->product->units->title : ''}}
                  </td>

                  <td>{{round($item->quantity_inv,3)}}</td>

                  <td> {{@$pod[0]->quantity_received}} </td> <!-- Quantity Received -->

                  <td> {{number_format(($item->quantity_inv - @$pod[0]->quantity_received),2,'.',',')}} </td> <!-- Quantity Received -->

                  <td> 
                  @if(@$item->good_condition == 'normal')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif                   
                  </td>
                  <td> 
                   @if(@$item->good_condition == 'problem')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif                  
                  </td>
                  <td>
                     @if(@$item->result == 'pass')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->result == 'fail')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>

                  <td>
                    @if(@$item->good_type == '2')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif</td>
                  <td>
                     @if(@$item->good_type == '1')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->good_type == '3')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->good_type == '4')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>

                  <td> {{ @$item->temperature_c != null ? @$item->temperature_c : '--' }}</td>

                  <td> {{@$item->checker != null ? @$item->checker : '--'}} </td> <!-- checker -->

                  <td> {{@$item->problem_found != null ? @$item->problem_found : '--'}} </td> <!-- Problem Found -->

                  <td> {{@$item->solution != null ? @$item->solution : '--'}} </td> <!-- Solution -->

                  <td> {{@$item->authorized_changes != null ? @$item->authorized_changes : '--'}} </td> <!-- Authorized Changes -->

                </tr>

                 @if($item->occurrence > 1)
                  @php
                  $all_record = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($item){
                    $q->where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id);
                  })->get();
                  @endphp
                  @foreach($all_record as $record)
                  <tr style="background:#B0E7DC; ">
                          
                      <th scope="row" >
                        
                        @php
                        $html_string = $record->PurchaseOrder->ref_id;
                        @endphp
                        {{$html_string}}
                      
                      </th>
                      
                      <td>
                        
                      @php

                      if($record->PurchaseOrder->supplier_id !== NULL)
                      {
                        $sup_name = App\Models\Common\SupplierProducts::where('supplier_id',$record->PurchaseOrder->supplier_id)->where('product_id',$record->product_id)->first();
                        $sup_ref = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
                      }
                      else
                      {
                        $sup_ref = "N.A";
                      }
                      @endphp
                      {{$sup_ref}}
                      
                      </td>
                      
                      <td>
                        
                        @php

                        if($record->PurchaseOrder->supplier_id !== NULL)
                        { 
                          $sup_name = $record->PurchaseOrder->PoSupplier->reference_name;
                        }
                        else
                        {
                          $sup_name = $record->PurchaseOrder->PoWarehouse->warehouse_title;
                        }          
                        @endphp 
                        {{$sup_name}} 
                      
                      </td>

                      

                      <td>
                        
                      @php
                      $product_ref = $record->product->refrence_code;          
                      @endphp
                      {{$product_ref}}
                      
                      </td>

                      <td style="max-width: 100pt;white-space: normal;" height="55">
                      
                        <span >    
                      @php
                      $product_ref = $record->product->short_desc;          
                      @endphp
                      {{$product_ref}}
                                          </span>
                      </td>

                      <td>  {{$record->product->units->title != null ? $record->product->units->title : ''}}                    </td>

                      <td>{{round($record->quantity,3)}}</td>

                      <td></td> <!-- Quantity Received -->

                      <td></td> <!-- Quantity Received -->

                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>

                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>

                      <td></td>

                      <td></td> <!-- checker -->

                      <td></td> <!-- Problem Found -->

                      <td></td> <!-- Solution -->

                      <td></td> <!-- Authorized Changes -->

                    
                    </tr>
                  @endforeach
                  @endif


                <!-- @php $i++; $j++; @endphp
                @if($i == 7 && $first == true)
                <tr class="before">
                  <td colspan="22"></td>
                </tr>
                @php $first = false; $j = 0; @endphp
                @elseif($j == 11 && $first == false)
                 <tr class="before">
                  <td colspan="22"></td>

                </tr>
                  @php $j = 0; @endphp
                @endif -->
                @endforeach
              </tbody>

</table>
</div>
</div>
  </body>
</html>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.0.272/jspdf.debug.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
        // w=window.open();
        // w.document.write($('#print_lables_3_3').html());
        // w.print();
        // w.close();

         html2canvas(document.getElementById("print_lables_3_3"), {
            onrendered: function(canvas) {
alert('farooq');
               var imgData = canvas.toDataURL('image/png');

      /*
      Here are the numbers (paper width and height) that I found to work. 
      It still creates a little overlap part between the pages, but good enough for me.
      if you can find an official number from jsPDF, use them.
      */
      var imgWidth = 210; 
      var pageHeight = 295;  
      var imgHeight = canvas.height * imgWidth / canvas.width;
      var heightLeft = imgHeight;

      var doc = new jsPDF('p', 'mm');
      var position = 0;

      doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;

      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        doc.addPage();
        doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      }
                doc.save('sample.pdf');
            }
        });
    });
</script> -->

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
<script type="text/javascript">
  $(document).ready(function(){

    //  // Choose the element that our invoice is rendered in.
    //     const element = document.getElementById("invoice");
    //     // Choose the element and save the PDF for our user.
    //     html2pdf()
    //     .set({ html2canvas: { scale: 4 } })
    //       .from(element)
    //       .save();
  

    // var element = document.getElementById('invoice');
    //       var opt = {
    //         margin:       0.3,
    //         filename:     'myfile.pdf',
    //         image:        { type: 'jpeg', quality: 0.98 },
    //         html2canvas:  { scale: 2 },
    //         jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
    //       };
// var doc = html2pdf(element, {return: true});
          // New Promise-based usage:
          // html2pdf().set(opt).from(element,{return: true}).save();
          // await browser.close();
          // window.location.href = "{{ url('warehouse/warehouse-complete-products-receiving-queue')}}"+'/'+id;
        });
</script>