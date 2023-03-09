  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>PO Group</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <style type="text/css">
      @page {size: auto;   /* auto is the initial value */
        margin: 5mm;}
    	  table { font-size: 10px; }
    table tr td 
    {
      vertical-align: top; padding-bottom: 3px;
    }
    table 
    {
      border-collapse: collapse;
      font-size:12px;
      border-spacing: 0px;}
      .invoicetable tr td, .invoicetable tr th {
      border: 1px solid #000;
      padding: 4px 7px;
    }
    .main-table > tbody > tr > td  { padding-right: 10px; padding-left: 10px;  }

    .invoicetable tr.inv-total-tr td {
        border: none;
        padding: 10px 2px 5px;
    }
    .inv-total-td span {
        font-weight: bold;
        border-bottom: 2px solid #000;
        display: inline-block;
        padding: 0px 5px 2px;
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

  <body style="font-family: sans-serif;">
    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;">
      <tbody>
        <tr>
          <td width="30%">
            <table class="table" style="width: 100%">
              <tbody>
                <tr>
                  <td colspan="2">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;"><strong>Group No {{$group_detail[0]->id}}<br>Product Receiving Records</strong></h5>
                  </td>
                </tr>
                <tr>
                  <td style="width: 50%;">                  	
                    <p style="margin: 0px 0px 6px;">PO Date: {{Carbon::parse(@$group_detail[0]->created_at)->format('d/m/Y')}}</p>
                    <p style="margin: 0px 0px 6px;">AWB or B/L:{{$group_detail[0]->bill_of_landing_or_airway_bill}}</p>                   
                    <p style="margin: 0px 0px 6px;">
                      @php
                        $courier = Courier::find($group_detail[0]->courier);
                      @endphp
                      courier: {{$courier->title}}
                    </p>                   
                  </td>
                </tr>
              </tbody>
            </table>
          </td>     
        </tr>          	
        
        <tr>
          <td align="center" style="padding-top: 0px;">
            <h1 style="font-style: italic; margin-top: 0px; margin-bottom: 10px;">PO GROUP</h1>
            <div style="border:1px solid;" class="border-top border-left border-right border-bottom">
              <table class="table invoicetable" style="width: 100%;">
              <thead align="center">
                <tr>
                  <th>Po No.</th>
                  <th>Supplier #</th>
                  <th>Ref #</th>
                  <th>Product<br>Ref #</th>
                  <th>Description</th>
                  <th>Unit</th>
                  <th>QTY</th>
                  <th>Qty<br>Rcvd</th>
                  <th>Goods<br>Condition</th>
                  <th>Results</th>
                  <th>@if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</th>
                  <th>@if(!array_key_exists('temprature_c', $global_terminologies)) Temprature <br> C    @else {{$global_terminologies['temprature_c']}} @endif</th>
                  <th>Checker</th>
                  <th>Problem<br>Found</th>
                  <th>Solution</th>
                  <th>Authorized<br>Changes</th>
                </tr>
              </thead>
             
              <tbody class="table-bordered">
                @foreach($group_detail as $result)
                <tr>
                  <td align="center" style="white-space: nowrap;"> {{ @$result->po_id }}</td>
                  <td align="center">
                    @php 
                      $sup_name = Supplier::where('id',$result->supplier_id)->first();
                    @endphp
                    {{ @$sup_name->company }}
                  </td>

                  <td align="center">
                    @php 
                      $sup_name = SupplierProducts::where('supplier_id',$result->supplier_id)->where('product_id',$result->product_id)->first();
                    @endphp
                    {{ @$sup_name->product_supplier_reference_no }}
                  </td>

                  <td align="center">
                    @php 
                      $product = Product::where('id',$result->product_id)->first();
                    @endphp
                    {{ @$product->refrence_code }}
                  </td>

                  <td align="center">
                    @php 
                      $product = Product::where('id',$result->product_id)->first();
                    @endphp
                    {{ @$product->short_desc }}
                  </td>

                  <td align="center">
                    @php 
                      $product = Product::where('id',$result->product_id)->first();
                    @endphp
                    {{ @$product->units->title }}
                  </td>

                  <td align="center"> {{ @$result->quantity }}</td>

                  <td align="center">  </td> <!-- Quantity Received -->

                  <td align="center">                   
                    <div class="d-flex">
                      <div class="custom-control custom-radio custom-control-inline">
                       <input type="radio" class="condition custom-control-input" value="normal">                    
                       <label class="custom-control-label">Normal</label>                    
                      </div>
                      <div class="custom-control custom-radio custom-control-inline">
                       <input type="radio" class="condition custom-control-input" value="problem">
                       <label class="custom-control-label">Problem</label>
                     </div>
                    </div>
                  </td>

                  <td align="center">                   
                    <div class="d-flex">
                      <div class="custom-control custom-radio custom-control-inline">
                       <input type="radio" class="condition custom-control-input" value="normal">                    
                       <label class="custom-control-label">Pass</label>                    
                      </div>
                      <div class="custom-control custom-radio custom-control-inline">
                       <input type="radio" class="condition custom-control-input" value="problem">
                       <label class="custom-control-label">Fail</label>
                     </div>
                    </div>
                  </td>

                  <td align="center" class="nowrap" style="white-space: nowrap;">
                    @php 
                      $goods_types = ProductType::all();
                    @endphp
                    <div class="d-flex">
                    @foreach ($goods_types as $type)                
                    <div class="custom-control custom-radio custom-control-inline">
                     <input type="radio" class="condition custom-control-input" >
                     <label class="custom-control-label" >{{$type->title}}</label>
                     </div>
                    @endforeach
                    </div>
                  </td>

                  <td align="center"> {{ @ $result->temperature_c }}</td>

                  <td align="center">  </td> <!-- checker -->

                  <td align="center">  </td> <!-- Problem Found -->

                  <td align="center">  </td> <!-- Solution -->

                  <td align="center">  </td> <!-- Authorized Changes -->

                </tr>
                @endforeach
              </tbody>
            </table>
            </div>
          </td>
        </tr>        
      </tbody>
    </table>
  </body>
</html>