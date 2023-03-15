  <!DOCTYPE html>
  <html>
  <head>
    @if(@$order->primary_status == 2)
    <title>DELIVERY NOTE</title>
    @else
     @if(@$proforma == 'yes')
    <title>DELIVERY NOTE</title>
     @else
    <title>Delivery Note</title>
    @endif
    @endif
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <!--  <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/>
    <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/dzhitl" type="text/css"/>
    <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
    <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css">

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
      /*border: 1px solid skyblue;*/
      border-left: 1px solid #ccc;
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

    .invoicetable td:last-child {
    border-right: 1px solid #ccc;
}


  /*  @font-face {
    font-family: 'THSarabunNew Bold';
    src: url("{{ storage_path('fonts\THSarabunNew Bold.ttf') }}") format("truetype");

}*/
body {
   font-family: 'TH Sarabun New';
   font-weight: bold;
   font-style: normal;
   font-size: 16px;
   line-height: 1;
}
.custom_font{
    font-family: 'TH Sarabun New';
    font-weight: bold;
    font-style: normal;
    font-size: 16px;
    line-height: 0.7;
}
.break_here{
  page-break-before: always;
}
    </style>

  @php

    use Carbon\Carbon;
       $first_total = 0;
     $vat_total = 0;
     $all_product_total = 0;

  @endphp
  </head>
@if($query->count() > 0)
  <body>
    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;">
      <tbody>
        <tr>
          <td width="30%">
            <table class="table" style="width: 100%">
              <tbody>
                <tr>
                  <td colspan="1">

                    <img src="{{asset('public/uploads/logo/'.@Auth::user()->getCompany->logo)}}" width="150" style="margin-bottom: 0px;" height="80">

                  </td>

                  <td colspan="1" align="right">
                   @if($order->primary_status == 25)
                    <h2 style=""><span style="font-size: 28px;">
ใบลดหนี้</span></h2>
     <h2 style=""><span style="font-family: 'DizhitlBold';
    font-weight: bold;
    font-style: normal;font-size: 16px;">
    <span style="text-transform: uppercase;">Credit Note</span></h2>
    @else
    <h2 style=""><span style="font-family: 'DizhitlBold';
    font-weight: bold;
    font-style: normal;font-size: 16px;">

เดบิตหมายเหตุ</span></h2>
     <h2 style=""><span style="font-family: 'DizhitlBold';
    font-weight: bold;
    font-style: normal;font-size: 16px;">
    <span style="text-transform: uppercase;">Debit Note</span></h2>
    @endif

                  </td>

                </tr>
                <tr>
                  <td style="width: 50%;">
                    <p style="">
                       <h2 style="margin: 0;font-family: 'DizhitlBold';
    font-weight: bold;
    font-style: normal;font-size: 16px;"><strong>
                      {{Auth::user()->getCompany->company_name}}
                    </strong></h2>
                      <span style="font-size: 18px">{{ @Auth::user()->getCompany->billing_address}},<br> {{@Auth::user()->getCompany->getcountry->name.', '.@Auth::user()->getCompany->getstate->name.', '.@Auth::user()->getCompany->billing_zip }}
                        </span>
                    </p>
                    <p style="margin: 0px 0px 6px;font-size: 18px">
                      Phone: {{ Auth::user()->getCompany->billing_phone }} Fax: {{ Auth::user()->getCompany->billing_fax }}
                    </p>
                    <p style="font-size: 18px">Tax ID: {{Auth::user()->getCompany->tax_id}}</p>

                  </td>

                  <td style="width: 65%;" align="right">
                    @if($config->is_show_in_prints == 0)
                    <p style="margin: 0px 0px 6px;">
                      <h2 style="text-transform: uppercase;; margin: 0;font-family: 'DizhitlBold'; font-weight: bold; font-style: normal;font-size: 16px;">
                      <strong>
                        {{-- บริษัท พรีเมี่ยม ฟู้ด (ประเทศไทย) จำกัด --}}
                        {{Auth::user()->getCompany->thai_billing_name}}
                      </strong></h2>
                      <span style="font-size: 18px">
                        {{-- สำนักงานใหญ่: 8/3, ซอยสุขุมวิท 62 แยก 8-5, พระโขนงใต้,<br>
                        ประเทศไทย, กรุงเทพ, 10260 --}}
                        {{ @Auth::user()->getCompany->thai_billing_address}},<br> {{@Auth::user()->getCompany->getcountry->thai_name.', '.@Auth::user()->getCompany->getstate->thai_name.', '.@Auth::user()->getCompany->billing_zip }}
                        <br>
                        {{-- โทรศัพท์: +66 (0) 2-012-6921 แฟกซ์: +66 (0) 2-012-6922 --}}
                        โทรศัพท์: {{ Auth::user()->getCompany->billing_phone }} แฟกซ์: {{ Auth::user()->getCompany->billing_fax }}
                      </span>
                    </p>
                    <p style="font-size: 18px">เลขประจำตัวผู้เสียภาษี: {{Auth::user()->getCompany->tax_id}}</p>
                    @endif
                  </td>


                </tr>
                <!-- <tr style="height: 15px;">
                  <td height="15px;"></td>
                  <td></td>
                </tr> -->
                 <tr>
                  <td style="width: 80%;">
                   <div style="padding:3px 3px 3px 25px;background-color: #ccc;min-height: 115px;overflow: hidden;">
                    <table class="table" style="width: 100%;background-color: white;min-height: 115px;font-size: 16px">
                      <tbody>
                        <tr>
                          <td width="40%" style="padding-left: 5px;line-height: 0.8;height: 65px;" valign="middle"><strong>
                            <span style="position: relative;"><span style="position: absolute;top: 10px;">ชื่อลูกค้าที่อยู่ <br>Customer Name, Address </span></span></strong></td>
                          <td width="60%" style="height: 65px;line-height: 0.5;font-size: 16px;"><p style="margin-top: 10px;"><span style="font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 14px;">{{@$order->customer->company}}<br>
   @if(@$customerAddress->title !== 'Default Address')
   {{@$customerAddress->title}}<br>
   @endif
   {{@$customerAddress->billing_address}}, {{@$customerAddress->billing_city}}, {{@$order->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}}, {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name : ''))}}, {{@$customerAddress->billing_zip}}<br>{{@$customerAddress->billing_phone}}</span></p></td>
                        </tr>
                        <tr >
                          <td width="40%" style="padding-left: 5px;min-height: 55px;line-height: 0.8"> <span>เลขประจำตัวผู้เสียภาษี <br>Tax ID:</span></td>
                          <td width="60%" style="line-height: 2">{{@$customerAddress->tax_id}}</td>
                        </tr>

                      </tbody>
                    </table>
                   </div>
                  </td>
                  <td style="width: 20%;">
                   <div style="padding:3px 3px 3px 25px;background-color: #ccc;min-height: 115px;">
                      <table class="table" style="width: 100%;background-color: white;min-height: 115px;font-size: 16px;">
                      <tbody>
                        <tr style="height: 50px;">
                          <td width="40%" style="padding-left: 5px;line-height: 0.8;min-height: 55px;"><strong><span style="position: relative;"><span style="position: absolute;top: 12px;">เลขที่<br>Document No.</span></span></strong></td>
                          <td width="60%" style="line-height: 0.8;padding-top: 15px;">
                            @if(@$order->primary_status == 3)
                                @if(@$order->in_status_prefix !== null)
                                {{'DNB'.'-'.$order->in_ref_prefix.$order->in_ref_id}}
                                @else
                                {{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id}}
                                @endif
                            @else
                              @if(@$order->status_prefix !== null)
                              {{@$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id}}
                              @else
                              {{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id}}
                              @endif
                            @endif <br>{{@$order->memo}}</td>
                        </tr>
                        <tr style="height: 0px;">
                          <td height="0px"></td>
                          <td></td>
                        </tr>
                        <tr >
                          <td width="40%" style="padding-left: 5px;line-height: 1;"> <strong><span><span>วันที่<br>Date:</span></span> </strong></td>
                          <td width="60%" style="line-height: 2;">{{@$order->credit_note_date != null ? Carbon::parse(@$order->credit_note_date)->format('d/m/Y') : '--'}}</td>
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

      </tbody>
    </table>

          <div style="position: relative;padding: 0 10px;">

            <table class="table invoicetable custom_font" style="width: 100%;border-color: skyblue;border: 1px solid #ccc;">
              <thead align="center" style="background-color: #ccc;font-size: 16px !important;line-height: 0.7;">
                <tr>
                  <th><span>รหัสสินค้า</span><br>Item</th>
                  <th width="45%"><span>รายละเอียด</span><br>Description</th>

                  <th><span>จำนวน</span><br> @if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif</th>
                  <th><span>หน่วย</span><br>Unit</th>

                  <th>Unit Price</th>


                  <th><span>จำนวนเงิน</span><br>Amount</th>
                </tr>
              </thead>

              <tbody style="">
                @php $j = 0; $heightt = 200; @endphp
                @foreach($query as $result)

                <tr>
                  <td align="center" style="white-space: nowrap;"> {{ @$result->product->refrence_code }}</td>
                  <td align="left">{{ @$result->short_desc }} @if(@$result->discount != null) Discount {{@$result->discount}} % @endif</td>

                  <td align="center">

                    @if(@$result->is_retail == 'qty')
                    {{ @$result->quantity != null ? @$result->quantity : 0 }}
                    @else
                    {{ @$result->number_of_pieces != null ? @$result->number_of_pieces : 0 }}
                    @endif

                  </td>

                  <td align="center">
                     @if(@$result->is_retail == 'qty')
                    {{$result->product && $result->product->sellingUnits ? $result->product->sellingUnits->title : "N.A"}}
                    @else
                      pc
                    @endif
                  </td>

                  <td align="right">
                     @php
                      $unit_price = $result->unit_price;
                      $vat = $result->vat;
                        $vat_amount = @$unit_price * ( @$vat / 100 );

                        $unit_price_with_vat = number_format(floor((@$unit_price+@$vat_amount)*100)/100,2,'.',',');

                        $total = @$result->total_price;
                        $vat_amount_w_v = @$total * ( @$vat / 100 );
                        $vat_amount_with_vat = number_format(floor((@$total+@$vat_amount_w_v)*100)/100,2,'.',',');
                    @endphp
                    {{number_format(@$result->unit_price,2,'.','')}}
                  </td>
                  @php $vat = @$result->vat_amount_total !== null ? round(@$result->vat_amount_total,4) : (@$result->vat/100)*@$result->total_price;
                        $first_total = $first_total + @$result->total_price;
                        $vat_total = $vat_total + $vat;
                        $heightt = $heightt - 40;
                        $all_product_total += round($result->total_price_with_vat,2);
                  @endphp
                  <td align="right">{{number_format(@$result->total_price,2,'.',',')}}</td>


                </tr>

                @if($result->get_order_product_notes->count() > 0)
                @php $heightt = $heightt - 15; @endphp
                <tr>
                  <td colspan="1" align="center" style="border: 1px solid #aaa;height: 15pt">Note</td>
                  <td colspan="5" style="border: 1px solid #aaa;">

                  @foreach(@$result->get_order_product_notes as $note)
                  {{@$note->note.' '}}
                  @endforeach
                  </td>
                    </tr>
                  @endif

                @endforeach
                <tr >
                  <td style="height: {{@$heightt}}pt"></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
                <!-- <tr>
                  <td style="padding-top: 40px;"></td>
                  <td><span style="font-size: 18px;"><span style="">{{@$arr[0]}}<br>{{@$arr[1]}}<br>{{@$arr[2]}}</span></span></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr> -->
              </tbody>
            </table>

<div style="margin: 0 auto;width: 528.6pt;height:124pt;max-height:124pt;font-size: 12px;">
<table style="max-height: 124pt;" class="custom_font">
  <tr>
    <td width="63.2pt">Delivered to :</td>
    <td width="170pt" align="" style="position: relative;padding-top: 5px;padding-left: -30px;"><span style="line-height: 0.5;font-size: 14px;">
      @if(@$customerShippingAddress->title !== 'Default Address')
      {{@$customerShippingAddress->title}}<br>
      @endif
      {{@$customerShippingAddress->billing_address}}, {{@$customerShippingAddress->billing_city}}, {{@$order->customer->language == 'en' ? @$customerShippingAddress->getstate->name : (@$customerShippingAddress->getstate->thai_name !== null ? @$customerShippingAddress->getstate->thai_name : @$customerShippingAddress->getstate->name)}}, {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name : ''))}}, {{@$customerShippingAddress->billing_zip}}</span></td>
    <td width="45pt">Due Date</td>
    <td width="20pt" style="position: relative;"><span style="position: absolute;left: -20pt;">{{@$order->payment_due_date != null ? Carbon::parse(@$order->payment_due_date)->format('d/m/Y') : '--'}}</span></td>
    <td width="60.4pt" align="right" style="line-height: 0.6;"><span>รวมเงิน</span><br>Sub Total</td>
    <td width="91.2pt" align="center" valign="center" style="">THB {{@$first_total != 0 ? number_format(floor(@$first_total*100)/100,2,'.',',') : 0}}</td>
  </tr>

   <tr >
    <td width="53.2pt" height="38pt"><span style="position: relative;"><span style="position: absolute;top: 9px;">Sales Contact :</span></span></td>
    <td width="170pt" align="center"><span style="position: relative;"><span style="position: absolute;top: 4px;left: -30px;font-family: 'TH Sarabun New';
    font-weight: bold;

    font-style: normal;font-size: 16px;line-height: 1;">{{@$order->customer->primary_sale_person->name}}</span></span></td>
    <td width="45pt"><span style="position: relative;"><span style="position: absolute;top: 9px;">Phone No. : </td>
    <td width="20pt" style="position: relative;"><span style="position: absolute;left: -20pt;top: 10px;">
      @if(@$order->customer->primary_sale_person->phone_number)
        {{@$order->customer->primary_sale_person->phone_number}}
      @else
        --
        <span style="position: absolute;left: -20pt;top: 10px;visibility: hidden;">1234567899</span>
      @endif
    </span></td>

    <td width="60.4pt" align="right" style="line-height: 0.6;padding-top: 10px;"><span>ภาษีมูลค่าเพิ่ม</span><br>Vat</td>
    <td width="61.2pt" align="center" valign="middle" style="padding-top: 7px;">THB {{@$vat_total != 0 ? @number_format(floor($vat_total*100)/100,2,'.',',') : 0}}</td>
  </tr>

   <tr style="border: 1px solid red;">
    <td colspan="3" width="350pt" style="text-transform: capitalize;background: #ccc;padding-top: 5px;" align="right" valign="middle">
       @php
            $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

             $total = number_format(@$all_product_total,'2','.','');
            $total_split = explode('.',$total);
            $word1 = $f->format($total_split[0]);
            $word2 = $f->format($total_split[1]);

            $word = $word1.' and '.$word2.' Satang';


           @endphp
          <span style="text-transform: uppercase;">{{@$word}} </span>
    </td>
    <td width=""></td>
    <td width="50.4pt" align="right" style="line-height: 0.6;background-color: #ccc;"><span>ยอดเงินสุทธิ</span><br>Total Amount</td>

    <td width="61.2pt" align="center" valign="middle">THB {{number_format((@$all_product_total) - @$order->discount,'2','.',',')}}</td>

  </tr>
@if(@$order->primary_status == 2)
   <tr>
    <td width="53.2pt" height="28.8pt" valign="middle">@if(!array_key_exists('remark', $global_terminologies)) Remark  @else {{$global_terminologies['remark']}} @endif  :</td>
    <!-- <td width="36"></td> -->
    <td colspan="5" valign="bottom" style="position: relative;"> <span style="position: absolute;top: 0pt;"><p style="font-size: 13px;margin: 0px;">Returns could be accepted at the time and day of delivery only.</p><p style="margin: 0;font-family: 'TH Sarabun New';
    font-weight: bold;
    font-style: normal;position: absolute;top:10pt;">การคืนสินค้าทางบริษัทจะยอมรับก็ต่อเมื่อมีการคืนในวันที่ทำการส่งสินค้าเท่านั้น</p></span>


</td>

  </tr>
  @endif

</table>
<div>
  <table style="width: 100%">
  <td width="25%"><span class="custom_font">{{$global_terminologies['comment_to_customer'] }}: </span>
    </td>
    <td valign="middle"><span>
    @php
      $skips = ["[","]","\""];
    @endphp
    <span class="custom_font">{{str_replace($skips,'',@$order->order_notes()->where('type','customer')->pluck('note'))}}</span>
  </span>
  </td>
  </table>
</div>

<table class="custom_font" style="margin-top: 50px;">
   <tr>
                  <td style="border: none !important;margin-top: 20px;" colspan="2" width="170pt"><b>ผู้รับของ / Receiver</b></td>
                  <td style="border: none !important;" colspan="2" width="170pt"><b>ผู้ส่งของ / Delivered By</b></td>
                  <td style="border: none !important;" colspan="2"><b>ผู้มีอำนาจลงนาม / Authorized Signature</b></td>

                </tr>
                </table>

</div>
</div>

  </body>
@endif

</html>
