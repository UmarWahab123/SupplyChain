  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>DRAFT INVOICE</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
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
    use App\Models\Common\Order\OrderProduct;
    use App\Models\Common\SupplierProducts;
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
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;"><strong>{{ @$order->ref_id}}</strong></h5>
                  </td>
                </tr>

                <tr>

                  <td style="width: 50%;">
                  	@if($order)
                    <p style="margin: 0px 0px 6px;">PO Date: {{Carbon::parse(@$order->created_at)->format('d/m/Y')}}</p>
                    <p style="margin: 0px 0px 6px;">Customer Name: {{ $order->customer->company}}</p>
                    <p style="margin: 0px 0px 6px;">
                      @if($order->customer->address_line_1 !== null) {{ $order->customer->address_line_1.' '.$order->customer->address_line_2 }}, @endif  
                      @if($order->customer->country !== null) {{ $order->customer->getcountry->name }}, @endif @if($order->customer->state !== null) {{ $order->customer->getstate->name }}, @endif @if($order->customer->city !== null) {{ $order->customer->city }}, @endif @if($order->customer->postalcode !== null) {{ $order->customer->postalcode }} @endif</p>
                    @endif
                  </td>

                  <td style="width: 50%;" class="d-none">
                      
                  </td>

                </tr>

              </tbody>
            </table>
          </td>
      

          	</tr>
          	
          </td>
        </tr>

        <tr>
          <td align="center" style="padding-top: 0px;">
            <h1 style="font-style: italic; margin-top: 0px; margin-bottom: 10px;">Draft Invoice</h1>
            <table class="table invoicetable" style="width: 100%;">
              <thead align="center">
                <tr>
                  <th>{{$global_terminologies['our_reference_number']}}</th>
                  <th>HS Code</th>
                  <th>Name</th>
                  <th>{{$global_terminologies['category']}}</th>
                  <th>Default Supplier</th>
                  <th>{{$global_terminologies['qty']}}</th>
                  <th>Unit Price</th>
                  <th>Total Price</th>
                </tr>
              </thead>
             
              <tbody>
                @foreach($getOrderDetail as $result)
                <tr>
                  <td align="center" style="white-space: nowrap;"> {{ @$result->product->refrence_code }}</td>
                  <td align="center">{{ @$result->product->hs_code }}</td>
                  <td align="center">{{ @$result->product->name }}</td>
                  <td align="center">{{ @$result->product->productSubCategory->title }}</td>
                  <td align="center">{{ @$result->product->def_or_last_supplier->first_name.' '.$result->product->def_or_last_supplier->last_name }}</td>
                  <td align="center">{{ @$result->quantity }}</td>
                  <td align="center">{{ number_format($result->product->selling_price,2,'.',',') }}</td>
                  <td align="center">
                    @php
                      $total_price = $result->product->selling_price * $result->quantity;
                    @endphp
                    {{ number_format($total_price,2,'.',',') }}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </td>
        </tr>
        
        
  
      </tbody>
    </table>
    <table>
      <tbody>
       
      </tbody>
    </table>
  </body>
</html>