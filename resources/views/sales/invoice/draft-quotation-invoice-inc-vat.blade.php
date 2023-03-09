<!DOCTYPE html>
<html>
<head>
  <title>Draft Quotation</title>
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
        <td align="center"class="text-danger h6"><b style="font-family: sans-serif;color: red;">Draft Quotation</b></td>
      </tr>
      <tr>
        <td></td>
        <td>
          <p class="m-0">
            <b style="font-family: sans-serif;font-size: 12px;line-height: 1;"> {{Auth::user()->getCompany->company_name}}</b>
          </p>
          @if($config->is_show_in_prints == 0)
          <p class="m-0" style="line-height: 0.5;font-size: 14px;">
            <!-- บริษัท พรีเมี่ยม ฟู้ด (ประเทศไทย) จำกัด <br>
            ที่อยู่: 8/3 ซอยสุขุมวิท 62 แยก 8-5 -->
            {{Auth::user()->getCompany->thai_billing_address}}
            แขวงพระโขนงใต้เขตพระโขนงกรุงเทพมหานคร {{@Auth::user()->getCompany->billing_zip}}<br>
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
              <!--  <span style="">{{@$order->billing_address->billing_address}}, {{@$order->billing_address->billing_city}}, {{@$order->billing_address->getstate->name}}, {{@$order->billing_address->getcountry->name}}, {{@$order->billing_address->billing_zip}}
                </span> -->
            </td>
          </tr>

          <tr>
            <td style="padding-left: 5px;">Address :</td>
            <td>
              <span style="font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 14px;">
   @if(@$order->billing_address->title !== 'Default Address' && @$order->billing_address->show_title != 0)
   {{@$order->billing_address->title}}<br>
   @endif
   {{@$order->billing_address->billing_address != null ? @$order->billing_address->billing_address.',' : ''}} {{@$order->billing_address->billing_city != null ? @$order->billing_address->billing_city.',' :''}} {{@$order->customer->language == 'en' ? (@$order->billing_address->getstate->name != null ? @$order->billing_address->getstate->name.',' : '') : (@$order->billing_address->getstate->thai_name != null ? (@$order->billing_address->getstate->thai_name != null ? @$order->billing_address->getstate->thai_name.',' : '') : (@$order->billing_address->getstate->name != null ? @$order->billing_address->getstate->name.',' : ''))}} {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name.',' : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' : ''))}} {{@$order->billing_address->billing_zip}}</span>
            </td>
          </tr>

          <tr>
            <td width="30%" style="padding: 2px 5px 15px;">Contact Number:</td>
            <td width="70%" valign="top">{{@$order->billing_address->billing_phone != null ? @$order->billing_address->billing_phone : '--'}}</td>
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
          <th>Unit Price<br>(Inc VAT)</th>
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
        @foreach($draft_quotation_products as $result)
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
                    {{$result->unit_price_with_vat}}
                  </td>
                <td align="center" valign="top">
                 {{@$result->discount != null ? @$result->discount.' %': '--'}}
                </td>
                <td>
                  {{$result->total_price_with_vat}}
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
      <tr>
        <td align="center" class="pt-2" style="background-color: #ccc;"><b>Total <br>Amount</b></td>
        <td align="right">{{number_format($draft_quotation_products->sum(total_price_with_vat),'2','.',',')}}</td>
      </tr>
    </table>
  </div>
  </div>

<div style="clear: both;"></div>
 <div style="text-align: center;font-size: 10px;page-break-inside: avoid;">
  <p class="mb-0">
    {{@Auth::user()->getCompany->company_name}}
  </p>
  <p class="mt-0 mb-0 mr-2">{{ @$order->user->getCompany->billing_address}},<br> {{@$order->user->getCompany->getcountry->name.', '.@$order->user->getCompany->getstate->name.', '.@$order->user->getCompany->billing_zip }}</p>
 	<p class="mt-0">Phone: {{ @$order->user->getCompany->billing_phone }} Fax: {{ @$order->user->getCompany->billing_fax }}</p>
 </div>
 </div>
</body>
</html>
