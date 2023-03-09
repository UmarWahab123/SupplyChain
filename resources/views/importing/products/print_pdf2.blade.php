  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>PO Group</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
    <meta name="description" content="">
    <meta name="author" content="">
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

    body{
      font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 14px;
    }
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

  <body>
<table class="table" style="width: 30%;border: 0px;border: none;text-align: left;">
              <tbody>
                <tr>
                  <td colspan="2" style="border: 0px;">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;"><strong>Group No {{$group_detail[0]->id}}<br>Product Receiving Records</strong></h5>
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
    <th rowspan="2" style="text-align: center;">Product Ref#</th>

    <th rowspan="2" style="text-align: center;">Description</th>

    <th colspan="4" scope="colgroup" style="text-align: center;">Quantity of Goods</th>
    <th colspan="4" scope="colgroup" style="text-align: center;">Goods Condition</th>
    <th colspan="3" scope="colgroup" style="text-align: center;">Goods Type</th>
    <th rowspan="2" style="text-align: center;"> @if(!array_key_exists('temprature_c', $global_terminologies)) Temprature <br> C    @else {{$global_terminologies['temprature_c']}} @endif </th>
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
  <!-- 
  <th scope="col" class="">Frozen</th>
    <th scope="col" class="">Chilled</th>
    <th scope="col" class="">Dried</th> -->
        <th scope="col">Problem <br>Found</th>
    <th scope="col">Solution</th>
  </tr>
</thead>
</tbody>
    @foreach($group_detail as $result)
                <tr>
                  <th scope="row" > {{ @$result->po_id }}</th>
                  <td>
                    @php 
                      $sup_name = SupplierProducts::where('supplier_id',$result->supplier_id)->where('product_id',$result->product_id)->first();
                    @endphp
                    {{ @$sup_name->product_supplier_reference_no }}
                  </td>
                  <td>
                    @php 
                      $sup_name = Supplier::where('id',$result->supplier_id)->first();
                    @endphp
                    {{ @$sup_name->company }}
                  </td>

                  

                  <td>
                    @php 
                      $product = Product::where('id',$result->product_id)->first();
                    @endphp
                    {{ @$product->refrence_code }}
                  </td>

                  <td style="">
                    @php 
                      $product = Product::where('id',$result->product_id)->first();
                    @endphp
                    {{ @$product->short_desc }}
                  </td>

                  <td>
                    @php 
                      $product = Product::where('id',$result->product_id)->first();
                    @endphp
                    {{ @$product->units->title }}
                  </td>

                  <td> {{ @$result->quantity }}</td>

                  <td>  </td> <!-- Quantity Received -->
                  <td>  </td> <!-- Quantity Received -->

                  <td>                   
                  </td>
                  <td>                   
                  </td>
                  <td></td>
                  <td></td>

                  <td></td>
                  <td></td>
                  <td></td>

                  <td> {{ @ $result->temperature_c }}</td>

                  <td>  </td> <!-- checker -->

                  <td>  </td> <!-- Problem Found -->

                  <td>  </td> <!-- Solution -->

                  <td>  </td> <!-- Authorized Changes -->

                </tr>
                @endforeach
</table>
</table>

  </body>
</html>