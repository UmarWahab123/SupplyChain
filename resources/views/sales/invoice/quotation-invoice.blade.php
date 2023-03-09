<!DOCTYPE html>
<html>
<head>
	<title>Quotation</title>
	<!-- <link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"> -->
	<!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/> -->
  <!-- <link rel="stylesheet" type="text/css" href="{{asset('public/css/thai.css')}}"> -->
  <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
  <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css">
</head>
<style type="text/css">
	
	#table_container .first_table {
    /*border: 1px solid black;*/
    float: left;
  }
  #table_container .second_table {
    /*border: 1px solid black;*/
    float: left;
  }
  #table_container .first_table{
  	width: 60%;
  }

  #table_container .second_table{
  	width: 35%;
  }
  
  #table_container {
    width: 100%;
    margin: 0 auto;
  }
  .products_table {
    border:1px solid #aaa;
    border-collapse: collapse;
    min-height: 200px;

}
.products_table tbody{
	  font-family: 'TH Sarabun New';
    font-weight: bold;
    font-style: normal;
    font-size: 16px;
    line-height: 0.7;
}
.custom_font{
	  font-family: 'TH Sarabun New';
    font-weight: bold;
    font-style: normal;
    font-size: 16px;
    line-height: 0.7;
}
.products_table th {
	border-right: 1px solid #aaa;
}
.products_table td {	
    border-left: 1px solid #aaa;
    border-right: 1px solid #aaa;
}

.products_table td:first-child {
    border-left: none;
}

.products_table td:last-child {
    border-right: none;
}
/*from here*/

.m-0{
  margin: 0;
}

</style>
<?php
use Carbon\Carbon;
?>
@php
 $first_total = 0;
     $vat_total = 0;
     $arr = explode("\r\n", @$order->user->getCompany->bank_detail);

     @endphp
<body>
 <img src="{{public_path('uploads/logo/'.@$company_info->logo)}}" height="80" style="margin-bottom: 0;">
 <div class="">
 	<table style="width: 100%;margin-top: -25px;" class="custom_font">
 		<tbody>
 			<tr>
 				<td width="67%"></td>
 				<td align="center"class="text-danger h6"><b style="font-family: sans-serif;color: red;">Quotation</b></td>
 			</tr>
 			<tr>
 				<td></td>
 				<td>
 					<p class="m-0">
 					  <b style="font-family: sans-serif;font-size: 12px;line-height: 1;"> {{Auth::user()->getCompany->company_name}}</b>
 				  </p>
          @if($config->is_show_in_prints == 0)
 				  <p class="m-0" style="line-height: 0.5;font-size: 14px;">                    
            บริษัท พรีเมี่ยม ฟู้ด (ประเทศไทย) จำกัด <br>
            ที่อยู่: 8/3 ซอยสุขุมวิท 62 แยก 8-5 แขวงพระโขนงใต้เขตพระโขนงกรุงเทพมหานคร {{@Auth::user()->getCompany->billing_zip}}<br>
            โทรศัพท์: {{ Auth::user()->getCompany->billing_phone }}, แฟกซ์: {{ Auth::user()->getCompany->billing_fax }}
          </p>
          @endif
 				</td>
 			</tr>
 		</tbody>
 	</table>
 </div>
 <div>
 	<table style="width: 100%;margin: 0;padding: 0;">
 		<tr>
 			<td width="60%" style="border: 1px solid #aaa;padding: 0px 3px 0px 25px;background-color: #ccc;border-bottom: 3px solid #ccc;border-top: 3px solid #ccc;">
        <div style="width: 100%">
 				<table style="width: 100%;border-collapse: collapse;border: 0px solid #aaa;margin:0;padding:0;margin-top: 1px;background-color: white;" class="custom_font">
 					<tr>
 						<td width="30%" style="border-bottom: 0px solid #aaa;padding: 10px 5px;">ชื่อลูกค้า<br>Customer Name</td>
 						<td width="70%" style="border-bottom: 0px solid #aaa;position: relative;">{{@$order->customer->company}} <br>
              <!--  <span style="">{{@$order->customer_billing_address->billing_address}}, {{@$order->customer_billing_address->billing_city}}, {{@$order->customer_billing_address->getstate->name}}, {{@$order->customer_billing_address->getcountry->name}}, {{@$order->customer_billing_address->billing_zip}}
                </span> -->
            </td>
 					</tr>
          
          <tr>
            <td style="padding-left: 5px;">Address :</td>
            <td>
              <span style="font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 14px;">
   @if(@$order->customer_billing_address->title !== 'Default Address' && @$order->customer_billing_address->show_title != 0)
   {{@$order->customer_billing_address->title}}<br>
   @endif
   {{@$order->customer_billing_address->billing_address != null ? @$order->customer_billing_address->billing_address.',' : ''}} {{@$order->customer_billing_address->billing_city != null ? @$order->customer_billing_address->billing_city.',' :''}} {{@$order->customer->language == 'en' ? (@$order->customer_billing_address->getstate->name != null ? @$order->customer_billing_address->getstate->name.',' : '') : (@$order->customer_billing_address->getstate->thai_name != null ? (@$order->customer_billing_address->getstate->thai_name != null ? @$order->customer_billing_address->getstate->thai_name.',' : '') : (@$order->customer_billing_address->getstate->name != null ? @$order->customer_billing_address->getstate->name.',' : ''))}} {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name.',' : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' : ''))}} {{@$order->customer_billing_address->billing_zip}}</span>
            </td>
          </tr>
         
 					<tr>
 						<td width="30%" style="padding: 2px 5px 15px;">Contact Number:</td>
 						<td width="70%" valign="top">{{@$order->customer_billing_address->billing_phone != null ? @$order->customer_billing_address->billing_phone : '--'}}</td>
 					</tr>
 					<tr>
            <td></td>
            <td></td>
          </tr>
           <tr>
            <td></td>
            <td></td>
          </tr>
 				</table>
        </div>
 			</td>
 			<!-- <td width="5%"></td> -->
 			<td width="40%" style="background-color: #ccc;padding-left: 25px;">
 				<table style="width: 99%;border-collapse: collapse;background-color: white;margin: 0" class="custom_font">
          <tr>
            <td width="45%">Quotation No.</td>
            <td>
              @if(@$order->status_prefix !== null)
              {{@$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id}}
              @else
              {{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{$order->ref_id }}
              @endif
            </td>
          </tr>
       
 					<tr>
 						<td width="30%" valign="top" style="padding: 20px 5px;">Issued by:<br> <span>Contact:</span></td>
 						<td width="70%" style="">{{@$order->customer->primary_sale_person->name}}<br>{{@$order->customer->user->phone_number != null ? @$order->customer->user->phone_number : '--'}}</td>
 					</tr>
 					<tr>
 						<td width="30%" style="padding: 5px 5px 15px;">วันที่ <br>Date:</td>
 						<td width="70%" valign="middle">{{@$order->delivery_request_date != null ? Carbon::parse(@$order->delivery_request_date)->format('d/m/Y') : '--'}}</td>
 					</tr>
 					
 					
 				</table>
 			</td>
 		</tr>
 	</table>
 	<div style="margin-top: -2px;border-top: 2px solid white;">
 		<table style="width: 100%;" class="products_table">
 			<thead style="font-weight: bold;background-color: #ccc;text-align: center;font-size: 12px;">
 				<tr>
 					<th style="padding: 5px 0px;" width="10%">Item</th>
 					<th width="50%">Description</th>
 					
 					<th>QTY</th>
 					<th>Unit</th>
          @if(@$with_vat == null)
 					<th>Unit <br> Price</th>
          @else
          <th>Unit Price<br>(Inc VAT)</th>
          @endif
          <th>Discount</th>
 					<th>Amount</th>
 				</tr>
 			</thead>
 			<tbody>
        @php
        if(@$with_vat == null)
         @$heightt = 200; 
         else
         @$heightt = 180;
         @endphp
 				@foreach($order_products as $result)
        @if($result->is_retail == 'qty' && $result->quantity != 0 || $result->is_retail == 'pieces' && $result->number_of_pieces != 0)
                <tr>
                  <td style="height: 15pt" align="center" valign="top">{{ @$result->product->refrence_code != null ? @$result->product->refrence_code : '--' }}</td>
                  <td align="left" valign="top" style="padding-left: 5px;">{{ @$result->short_desc != null ? @$result->short_desc : '--' }} @if(@$result->discount != null) Discount {{@$result->discount}} % @endif </td>
                 
                  <td align="center" valign="top">
                     @if(@$result->is_retail == 'qty')
                    {{ @$result->quantity != null ? @$result->quantity : 0 }}
                    @else 
                    {{ @$result->number_of_pieces != null ? @$result->number_of_pieces : 0 }}
                    @endif
                  </td>
                  <td align="center" valign="top">
                     @if(@$result->is_retail == 'qty')
                    {{$result->product && $result->product->sellingUnits ? $result->product->sellingUnits->title : "N.A"}}
                    @else 
                      pc
                    @endif
                  </td>
                   @php $vat = round(@$result->total_price_with_vat-@$result->total_price,2); 
                        $first_total = $first_total + round(@$result->total_price,2);
                        $vat_total = ($vat_total + $vat);
                        $heightt = $heightt - 15;
                  @endphp
                  <td align="center" valign="top">
                    @if(@$with_vat == null)
                    {{number_format(@$result->unit_price, 2, '.', ',')}}
                    @else
                    @php
                      $unit_price = $result->unit_price;
                      $vat = $result->vat;
                        $vat_amount = @$unit_price * ( @$vat / 100 );

                        $unit_price_with_vat = number_format(floor((@$unit_price+@$vat_amount)*100)/100,2,'.',',');
                        $total = @$result->total_price;
                        $vat_amount_w_v = @$total * ( @$vat / 100 );
                        $vat_amount_with_vat = number_format(floor((@$total+@$vat_amount_w_v)*100)/100,2,'.',',');
                    @endphp
                    {{@$unit_price_with_vat}}
                    @endif
                  </td>
            		<td align="center" valign="top">
                 {{@$result->discount != null ? @$result->discount.' %': '--'}}
                </td>
                <td>
                  @if(@$with_vat == null)
                  {{number_format(@$result->total_price,2,'.',',')}}
                  @else
                     {{@$vat_amount_with_vat}}
                  @endif
                </td>
                </tr>
                @endif
                @endforeach
                <tr>
                  <td style="height: {{@$heightt}}pt"></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
                  <tr>
                  <td style="padding-top: 40px;"></td>
                  <td style="padding: 5px 20px;">
                    <span style="font-size: 18px;">
                      @if($inv_note !== null)
                      @if($inv_note->note !== null)
                      <span>{{$global_terminologies['comment_to_customer'] }}: {{ $inv_note->note }}</span><br>
                      @endif
                      @endif
                      <span style="">Account Name: {{@$bank->title}}<br>Account No.: {{@$bank->account_no}}<br>Bank: {{@$bank->description}}<br>Branch: {{@$bank->branch}}</span>
                    </span>
                  </td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
 			</tbody>
 		</table>
 	</div>
 	
 	

<!--  <div class="d-inline-block mt-0" style="width: 75%;font-size: 12px;">
 		<p class="mb-0" style="width: 60%;"><span class="h6 mb-0">
       <span style="display: flex;width: 80px;visibility: hidden;">Bank Detail: </span><span style="display: inline-block;">{{@$arr[0]}}<br>{{@$arr[1]}}<br>{{@$arr[2]}}</span>
    </p>
 		
 	</div> -->
  <div style="display: block;">
  <div style="display: inline-block;width: 50%;float: left;" class="custom_font">
    <span style="margin-top: 10px;">Ref.Name &nbsp;&nbsp;&nbsp;&nbsp;{{@$order->customer->reference_name}}</span>
  </div>
 	<div class="d-inline-block" style="float: right;margin-top: 0px;width: 20%;font-size: 12px;">

 		<table width="100%">
      @if(@$with_vat == null)
 			<tr>
 				<td align="center" class="pt-2"><span class="custom_font">รวมเงิน</span><br>Sub Total : </td>
 				<td align="right">{{number_format(@$first_total,'2','.',',')}}</td>
 			</tr>

 			<tr>
 				<td align="center" class="p-2"><span class="custom_font">ภาษี<br></span><span>Vat :</span> </td>
 				<td align="right">{{number_format(@$vat_total,'2','.',',')}}</td>
 			</tr>
      @endif
     
 			<tr>
 				<td align="center" class="pt-2" style="background-color: #ccc;"><b>Total <br>Amount</b></td>
 				<td align="right">{{number_format((@$first_total + @$vat_total) - @$order->discount,'2','.',',')}}</td>
 			</tr>
 		</table>
 	</div>
  </div>
 
<div style="clear: both;"></div>
 <div style="text-align: center;font-size: 10px;page-break-inside: avoid;">
 	<p class="mb-0">
 		Company Name Here
 	</p>
 	<p class="mt-0 mb-0 mr-2">Address: address here</p>
 	<p class="mt-0">Telephone: {{ Auth::user()->getCompany->billing_phone }}, Fax: {{ Auth::user()->getCompany->billing_fax }}</p>
 </div>
 </div>
</body>
</html>