<html>
<head>
   <title>@if(!array_key_exists('receipt', $global_terminologies)) Receipt @else {{$global_terminologies['receipt']}} @endif</title>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- <link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"> -->
      <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
      <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/> -->
       <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/dzhitl" type="text/css"/> -->
       <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css">
  <style>
    @page { margin: 410px 10px 35px 10px; }
    #header { position: fixed; left: 0px; top: -400px; right: 0px; height: 150px;}
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

.page-break {
page-break-before: always;
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
.table_footer tr td
{
  padding: 0px;
  line-height: 0.7;
}

.table_footer tr td input
{
  transform: scale(1);
  position: absolute;
  top: 0;
}
  </style>
@php

    use Carbon\Carbon;

       $first_total = 0;
     $vat_total = 0;
     $grand_total = 0;
     $f_page = 1;
  @endphp
<body>
  <div id="header">
    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;">
      <tbody>
        <tr>
          <td width="30%">
            <table class="table" style="width: 100%">
              <tbody>
                <tr>
                  <td colspan="1">

                    <img src="{{asset('public/uploads/logo/'.@Auth::user()->getCompany->logo)}}" width="150" height="80" style="margin-bottom: 0px;">

                  </td>
                  <td align="right">
                      <h2 style=""><span style="font-family: 'DizhitlBold';
    font-weight: bold;
    font-style: normal;font-size: 16px;">
    <span style="text-transform: uppercase;">ใบเสร็จรับเงิน <br>Receipt</span></h2>
                  </td>
                </tr>
                <tr>
                  <td style="width: 50%;">
                    <p style="">
                       <h2 style="margin: 0;font-family: 'DizhitlBold';
                        font-weight: bold;
                        font-style: normal;font-size: 12px;"><strong>
                      {{Auth::user()->getCompany->company_name}}
                    </strong></h2>
                      {{ @Auth::user()->getCompany->billing_address}},<br> {{@Auth::user()->getCompany->getcountry->name.', '.@Auth::user()->getCompany->getstate->name.', '.@Auth::user()->getCompany->billing_zip }}
                    </p>
                    <p style="margin: 0px 0px 6px;">
                      Phone: {{ Auth::user()->getCompany->billing_phone }} Fax: {{ Auth::user()->getCompany->billing_fax }}
                    </p>
                    <p>Tax ID: {{Auth::user()->getCompany->tax_id}}</p>

                  </td>

                  <td style="width: 50%;" align="right">
                    <p style="margin: 0px 0px 6px;">
                       <h2 style="text-transform: uppercase;; margin: 0;"><strong>
                      <!-- บริษัท พรีเมี่ยม ฟู้ด (ประเทศไทย) จำกัด -->
                      {{Auth::user()->getCompany->thai_billing_name}}
                    </strong></h2>
                        <!-- สำนักงานใหญ่: 8/3, ซอยสุขุมวิท 62 แยก 8-5, พระโขนงใต้,<br> -->
                        {{Auth::user()->getCompany->thai_billing_address}}<br>
                        <!-- ประเทศไทย, กรุงเทพ, {{@Auth::user()->getCompany->billing_zip }}<br> -->
                        {{ @Auth::user()->getCompany->getcountry->thai_name }}
                        , {{@Auth::user()->getCompany->getstate->thai_name}}, {{@Auth::user()->getCompany->billing_zip }}
                        <br>
                        {{-- โทรศัพท์: +66 (0) 2-012-6921 แฟกซ์: +66 (0) 2-012-6922 --}}
                        โทรศัพท์: {{ Auth::user()->getCompany->billing_phone }} แฟกซ์: {{ Auth::user()->getCompany->billing_fax }}
                    </p>

              <p>เลขประจำตัวผู้เสียภาษี: {{Auth::user()->getCompany->tax_id}}</p>
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
                            <span style="position: relative;"><span style="position: absolute;top: 10px;">ชื่อลูกค้า / ที่อยู่ <br>Customer Name, Address </span></span></strong></td>
                          <td width="60%" style="height: 65px;line-height: 0.5;font-size: 16px;"><p style="margin-top: 10px;"><span style="font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 14px;">{{@$transactions[0]->order->customer->company}}<br>{{@$customerAddress->billing_address}}, {{@$customerAddress->billing_city}}, {{@$transactions[0]->order->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}}, {{@$transactions[0]->order->customer->language == 'en' ? @$customerAddress->getcountry->name : (@$customerAddress->getcountry->thai_name !== null ? @$customerAddress->getcountry->thai_name : @$customerAddress->getcountry->name)}}, {{@$customerAddress->billing_zip}}<br>{{@$address->billing_phone}}</span></p></td>
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
                          <td width="60%" style="line-height: 0.8;padding-top: 15px;">{{@$payment->auto_payment_ref_no != null ? $payment->auto_payment_ref_no : @$payment->payment_reference_no}}</td>
                        </tr>
                        <tr style="height: 10px;">
                          <td height="15px"></td>
                          <td></td>
                        </tr>
                        <tr >
                          <td width="40%" style="padding-left: 5px;line-height: 0.5;border-top: 1px solid #ccc;padding-top: 10px;padding-bottom: 10px;" valign="bottom"> <strong><span>วันที่ <br>Date:</span></strong></td>
                          <td width="60%" style="line-height: 1;border-top: 1px solid #ccc;padding-top: 10px;padding-bottom: 10px;">{{@$payment->received_date != null ? Carbon::parse(@$payment->received_date)->format('d/m/Y') : '--'}}</td>
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
  </div>
 <!--   <div id="footer">

    <p class="" style="margin-top: 0;">Page <span class="page"></span></p>
  </div> -->
  <div id="content">
    <div style="padding: 0 10px;">

            <table class="table invoicetable custom_font" style="width: 100%;border-color: skyblue;border: 1px solid #ccc;">
              <thead align="center" style="background-color: #ccc;font-size: 16px !important;line-height: 0.7;">
                <tr>
                  <th><span>เลขที่</span><br>No</th>
                  {{-- <th width="45%">รายละเอียด<span></span><br>Description</th> --}}

                  <th><span>ตามใบกำกับภาษีเลขที่</span><br>Tax Invoice No.</th>
                  <th><span>วันที่ตามใบกำกับภาษี</span><br>Date as Tax Invoice</th>
                  <th><span>วันที่กำหนดชำระ</span><br>Payment Due Date</th>
                  <th><span>ยอดคงค้าง</span><br>Difference Amount</th>
                  <th><span>ยอดเงินสุทธิ</span><br>Total Amount</th>
                </tr>
              </thead>
              <tbody>
                <tbody style="">
                 @if(@$transactions->count() > 0)
                 @php $j = 0; $heightt = 180; $iteration = 0; @endphp
                @foreach(@$transactions as $key => $tran)
                @php $count = $tran->order->order_products()->where('is_billed','product')->count();
                $iteration++;
                if(@$tran->order->in_status_prefix !== null)
                {
                  $ref_no = @$tran->order->in_status_prefix.'-'.$tran->order->in_ref_prefix.$tran->order->in_ref_id;
                }
                elseif(@$tran->order->status_prefix !== null && @$tran->order->primary_status == 25)
                {
                  $ref_no = $tran->order->status_prefix.$tran->order->ref_id;
                }
            elseif($tran->order->status_prefix !== null && $tran->order->ref_prefix !== null && $tran->order->ref_id !== null )
              {
                $ref_no = @$tran->order->status_prefix.'-'.$tran->order->ref_prefix.$tran->ref_id;
              }
            else
              {
                 $ref_no = @$tran->order->ref_id != null ? @$tran->order->customer->primary_sale_person->get_warehouse->order_short_code.@$tran->order->customer->CustomerCategory->short_code.@$tran->order->ref_id : '';
              }
                @$vat_total_check = @$tran->order->order_products != null ? @$tran->order->getOrderTotalVat($tran->order->id,0) : '0';
                        @$non_vat_total_check = @$tran->order->order_products != null ? @$tran->order->getOrderTotalVat($tran->order->id,2) : '0';
                        if(@$tran->vat_total_paid !== null)
                        {
                          $vat_amount_paid = @$tran->vat_total_paid;
                        }
                        else
                        {
                          $vat_amount_paid = @$tran->order->vat_total_paid;
                        }
                        if(@$tran->vat_total_paid !== null)
                        {
                          $non_vat_amount_paid = @$tran->non_vat_total_paid;
                        }
                        else
                        {
                          $non_vat_amount_paid = @$tran->order->non_vat_total_paid;
                        }
                 @endphp
                  {{-- @if(@$vat_amount_paid > 0 || @$tran->order->vat_total_paid == null) --}}
                  @php $j = $j + 1; @endphp
                  <tr>
                    <td height="18pt">{{$j}}</td>
                    {{-- <td>
                      @if($count > 0 || @$tran->order->primary_status == 25)
                      Goods
                      @else
                      Freight charge
                      @endif
                  </td> --}}

                  <td style="">
                   <span>
                    {{$ref_no}}
                    </span>
                  </td>
                    <td>
                      @if(@$tran->order->primary_status == 25)
                      {{$tran->order->credit_note_date !== null ? Carbon::parse(@$tran->order->credit_note_date)->format('d/m/Y') : '--'}}
                      @else
                      {{$tran->order->delivery_request_date !== null ? Carbon::parse(@$tran->order->delivery_request_date)->format('d/m/Y') : '--'}}
                      @endif
                    </td>

                    <td>
                        {{$tran->order->payment_due_date !== null ? Carbon::parse($tran->order->payment_due_date)->format('d/m/Y') : '--'}}
                    </td>

                    <td>
                        @php
                            $diff = 0;
                            $diff = $tran->order->total_amount - ($tran->order->vat_total_paid + $tran->order->non_vat_total_paid);
                        @endphp
                        {{ $diff > 2 ? $diff : 0 }}
                    </td>

                    <td align="">
                       @php
                        $vat_total = @$tran->order->order_products != null ? @$tran->order->getOrderTotalVat($tran->order->id,0) : 0;
                        $vat_amount = @$tran->order->order_products != null ? @$tran->order->getOrderTotalVat($tran->order->id,1) : 0;
                        $total_vat = (floatval(preg_replace('/[^\d.]/', '', $vat_total)) + floatval(preg_replace('/[^\d.]/', '', $vat_amount)));

                        $vat_total = @$tran->order->order_products != null ? @$tran->order->getOrderTotalVat($tran->order->id,2) : 0;

                        $total_non_vat = (floatval(preg_replace('/[^\d.]/', '', $vat_total)));

                      @endphp
                       @if(@$vat_amount_paid > 0 || $non_vat_amount_paid > 0)
                      {{number_format(@$vat_amount_paid + @$non_vat_amount_paid,2,'.',',')}}
                      @else
                      {{number_format(@$total_vat + @$total_non_vat,2,'.',',')}}
                      @endif
                    </td>
                  </tr>
                  {{-- @endif
                   @if(@$non_vat_amount_paid > 0 || @$tran->order->non_vat_total_paid == null)
                   @php $j = $j + 1; @endphp
                  <tr>
                    <td height="18pt">{{$j}}</td>
                    <td>
                      @if($count > 0 || @$tran->order->primary_status == 25)
                      Goods
                      @else
                      Freight charge
                      @endif
                  </td>

                  <td style="">
                   <span>
                    {{$ref_no}}-2
                    </span>
                  </td>

                    <td>
                      @if(@$tran->order->primary_status == 25)
                      {{$tran->order->credit_note_date !== null ? Carbon::parse(@$tran->order->credit_note_date)->format('d/m/Y') : '--'}}
                      @else
                      {{$tran->order->delivery_request_date !== null ? Carbon::parse(@$tran->order->delivery_request_date)->format('d/m/Y') : '--'}}
                      @endif
                    </td>

                    <td align="">
                     @php
                        $vat_total = @$tran->order->order_products != null ? @$tran->order->getOrderTotalVat($tran->order->id,2) : 0;

                        $total_non_vat = (floatval(preg_replace('/[^\d.]/', '', $vat_total)));
                      @endphp
                      @if(@$non_vat_amount_paid > 0)
                      {{@$tran->order->primary_status == 25 ? '-' : ''}}{{number_format(@$non_vat_amount_paid,2,'.',',')}}
                      @else
                      {{@$tran->order->primary_status == 25 ? '-' : ''}}{{number_format(@$total_non_vat,2,'.',',')}}
                      @endif
                    </td>
                  </tr>
                  @endif --}}
                  @php
                  if(@$non_vat_amount_paid > 0)
                  {
                    if(@$tran->order->primary_status == 25)
                    {
                      $grand_total -= @$non_vat_amount_paid;
                    }
                    else
                    {
                      $grand_total += @$non_vat_amount_paid;
                    }
                  }
                  if(@$vat_amount_paid > 0)
                  {
                    if(@$tran->order->primary_status == 25)
                    {
                      $grand_total -= @$vat_amount_paid;
                    }
                    else
                    {
                      $grand_total += @$vat_amount_paid;
                    }
                  }
                  if(@$non_vat_amount_paid == 0 && @$vat_amount_paid == null)
                  {
                    if(@$tran->order->primary_status == 25)
                    {
                      $grand_total -= @$tran->order->total_amount;
                    }
                    else
                    {
                      $grand_total += @$tran->order->total_amount;
                      if(@$tran->order->ecommerce_order == 1){
                      $grand_total += @$tran->order->shipping;
                    }

                    }
                  }

                  @endphp
                  @endforeach
                  @endif
                   <tr>
                  <td colspan="4" style="border-top: 1px solid #ccc;border-bottom: 1px solid white;border-left: 1px solid white;"></td>
                  <td align="center" style="border-top: 1px solid #ccc;font-size: 14px;border-bottom: 1px solid #ccc;"><span>ยอดเงินสุทธิ</span><br>Total Amount</td>
                  <td style="border-top: 1px solid #ccc;border-bottom: 1px solid #ccc;">{{number_format(@$grand_total,2,'.',',')}}</td>
                </tr>
              </tbody>
            </table>
</div>
<div style="page-break-inside: avoid;">
<div style="padding: 0 10px;line-height: 0.5;">
  <p style="margin: 0;">Delivered : {{$transactions[0]->order->customer->company}}</p>
  <p style="word-break: break-all;margin: 0;margin-bottom: 5px;">@if(!array_key_exists('remark', $global_terminologies)) Remark  @else {{$global_terminologies['remark']}} @endif :  กรณีรับชำระเงินด้วยเช็คหรือมีการส่งมอบใบเสร็จก่อนชำระหนี้จริง ใบเสร็จนี้จะถือว่าถูกต้องและสมบูรณ์ก็ต่อเมื่อบริษัทได้เรียกเก็บเงิน ตามเช็คเรียบร้อยแล้วหรือเงินได้เข้าบัญชีบริษัทเรียบร้อยแล้ว</p>
</div>

<div style="padding: 0 10px;">
  <table style="width: 100%;">
    <tr>
      <td width="50%" style="border: 1px solid #ccc;">
        <span class="custom_font" style="font-size: 18px;">ชำระโดย/ Paid By</span>
        <table style="font-size: 16px;width: 100%" class="table_footer">
         <!--  <tr>
            <td width="50%"><span style="display: block;float: left;"><input type="checkbox" name="" checked="true"></span><span style="display: inline-block;float: left;padding-left: 10px;">{{@$payment->get_payment_type !== null ? @$payment->get_payment_type->title : 'N.A'}}</span><span style="clear: both;"></span></td>
          </tr> -->
          <tr>

            <td width="50%"><span style="display: block;float: left;"><input type="checkbox" name="" {{ (@$payment->payment_method == '3' ? "checked" : '')}}></span><span style="display: inline-block;float: left;padding-left: 10px;">เงินสด/ Cash</span><span style="clear: both;"></span></td>
          </tr>
          <tr>
            <td><span style="display: block;float: left;"><input type="checkbox" name="" {{ ((@$payment->payment_method == '6' || @$payment->payment_method == '7') ? "checked" : '')}}></span><span style="display: inline-block;float: left;padding-left: 10px;" >เงินโอน/ Transfer</span><span style="clear: both;"></span></td>
          </tr>
            <tr>
            <td><span style="display: block;float: left;"><input type="checkbox" name="" {{ (@$payment->payment_method == '2' ? "checked" : '')}}></span><span style="display: inline-block;float: left;padding-left: 10px;">เช็ค/ Cheque</span><span style="clear: both;"></span></td>
            <td>ธนาคาร/ Bank....................................</td>
          </tr>
          <tr>
            <td><span style="display: block;float: left;"></span><span style="display: inline-block;float: left;padding-left: 10px;">เลขที่เช็ค/ CHQ No. </span><span style="clear: both;"></span></td>
            <td>........................................................</td>
          </tr>
           <tr>
            <td><span style="display: block;float: left;"></span><span style="display: inline-block;float: left;padding-left: 10px;">วันที่/ Date </span><span style="clear: both;"></span></td>
            <td>........................................................</td>
          </tr>
        </table>
      </td>
      <td width="50%">
        <table style="width: 100%;font-size: 16px;">
          <tr>
            <td width="50%" style="text-align: right;"></td>
            <td width="50%" align="center">ผู้รับเงิน</td>
          </tr>
          <tr>
            <td width="50%" style="text-align: right;"></td>
            <td width="50%" align="center">.............................................</td>
          </tr>

           <tr>
            <td width="50%" style="text-align: right;">วันที่<br>Date</td>
            <td width="50%" align="center" style="vertical-align: bottom;">.............................................</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
</div>
<script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $size = 12;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 35;
        $pdf->page_text('520', $y, $text, $font, $size);
    }
</script>
</div>
</body>

</html>
