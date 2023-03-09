  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>QUOTATION</title>
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
      border: 1px solid skyblue;
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
    @font-face {
    font-family: 'THSarabunNew Bold';
    src: url("{{ storage_path('fonts\THSarabunNew Bold.ttf') }}") format("truetype");
   
}
    </style>

  @php
    use Carbon\Carbon;
    $customerAddress = App\Models\Common\Order\CustomerBillingDetail::where('customer_id',$order->customer->id)->where('id',$order->billing_address_id)->first();
     $customerAddressShip = App\Models\Common\Order\CustomerBillingDetail::where('customer_id',$order->customer->id)->where('id',$order->shipping_address_id)->first();
  @endphp
  </head>

  <body style="font-family: THSarabunNew Bold">
    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;">
      <tbody>
        <tr>
          <td width="30%">
            <table class="table" style="width: 100%">
              <tbody>
                <tr>
                  <td colspan="2">
                    <img src="{{asset('public/uploads/logo/'.@Auth::user()->getCompany->logo)}}" width="150" style="margin-bottom: 20px;">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;"><strong>
                      {{Auth::user()->getCompany->company_name}}
                    </strong></h5>
                  </td>
                </tr>
                <tr>
                  <td style="width: 50%;">
                    <p style="margin: 0px 0px 6px;">
                      {{ Auth::user()->getCompany->billing_address}},<br> {{Auth::user()->getCompany->getcountry->name.', '.Auth::user()->getCompany->getstate->name.', '.Auth::user()->getCompany->billing_zip }}
                    </p>
                    <p style="margin: 0px 0px 6px;">
                      Phone: {{ Auth::user()->getCompany->billing_phone }} Fax: {{ Auth::user()->getCompany->billing_fax }}
                    </p>
              
                  </td>

                
                </tr>
                <tr style="height: 40px;">
                  <td height="15px;"></td>
                  <td></td>
                </tr>
                 <tr>
                  <td style="width: 60%;">
                   <div style="padding:3px 3px 3px 25px;background-color: skyblue;">
                    <table class="table" style="width: 100%;background-color: white;height: 105px;">
                      <tbody>
                        <tr>
                          <td width="30%" style="padding-left: 5px;line-height: 3;"><strong>Customer Name</strong><br><strong>Address</strong></td>
                          <td width="70%" style=""> <p style="">{{$order->customer->company}}</p><p style="margin-top: 10px;">{{$customerAddress->billing_address}},<br>{{$customerAddress->getcountry->name}}, {{@$customerAddress->getstate->name}}, {{@$customerAddress->billing_city}}, {{@$customerAddress->billing_zip}}</p></td>
                        </tr>
                        <tr >
                          <td width="40%" style="padding-left: 5px;line-height: 2;"> <strong>Tax ID: </strong></td>
                          <td width="60%" style="line-height: 1;">{{$customerAddress->tax_id}}</td>
                        </tr>

                      </tbody>
                    </table>
                   </div>
                  </td>
                  <td style="width: 40%;">
                   <div style="padding:3px 3px 3px 25px;background-color: skyblue;">
                      <table class="table" style="width: 100%;background-color: white;height: 105px;">
                      <tbody>
                        <tr style="height: 50px;">
                          <td width="40%" style="padding-left: 5px;line-height: 2;"><strong>Document No.</strong></td>
                          <td width="60%" style="line-height: 1;"> 13056478</td>
                        </tr>
                        <tr>
                          <td></td>
                          <td></td>
                        </tr>
                        <tr >
                          <td width="40%" style="padding-left: 5px;line-height: 4;"> <strong>Date: </strong></td>
                          <td width="60%" style="line-height: 3;">{{@$order->created_at->format('d/m/Y')}}</td>
                        </tr>

                      </tbody>
                    </table>
                   </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
      
</tr>
        <tr>
          <td align="center" style="padding-top: 0px;">
           
            <table class="table invoicetable" style="width: 100%;border-color: skyblue;">
              <thead align="center" style="background-color: skyblue;">
                <tr>
                  <th>Item</th>
                  <th width="35%">Description</th>
              
                  <th>QTY</th>
                  <th>Unit</th>
                  
                  <th>Unit Price</th>
                 
                 
                  <th>Amount</th>
                </tr>
              </thead>
             
              <tbody>
                @foreach($query as $result)
                <tr>
                  <td align="center" style="white-space: nowrap;"> {{ @$result->product->refrence_code }}</td>
                  <td align="left">{{ @$result->product->short_desc }} <br> VAT {{@$result->product->vat}} %</td>
                  <td align="center">{{ @$result->quantity }}</td>
                  <td align="center">{{@$result->product->units->title}}</td>
                  <td align="right">{{number_format(@$result->unit_price, 2, '.', ',')}}</td>
                  @php $vat = (@$result->product->vat/100)*@$result->total_price @endphp
                  <td align="right">{{number_format(@$result->total_price, 2, '.', ',')}} <br> {{number_format(@$vat, 2, '.', ',')}}</td>
            
              
                </tr>
                @endforeach
              </tbody>
            </table>

          </td>
          
        </tr>
       
        
  
      </tbody>
    </table>
      <div style="margin-left: 15px;width: 50%;">
        <p style="font-size: 12px;font-weight: 100;">Ship To:<br>
        Address : {{$customerAddressShip->billing_address}},<br>{{$customerAddressShip->getcountry->name}}, {{@$customerAddressShip->getstate->name}}, {{@$customerAddressShip->billing_city}}, {{@$customerAddressShip->billing_zip}}</p>
      </div>
  </body>
</html>