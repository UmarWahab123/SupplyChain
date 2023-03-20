<html>
<head>
    <!-- <link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"> -->
      {{-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> --}}
       {{-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/dzhitl" type="text/css"/> --}}
       <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css">
  <style>
    @page { margin: 400px 50px 25px 50px; }
    #header { position: fixed; left: 0px; top: -400px; right: 0px; height: 150px;}
    #footer { position: fixed; left: 0px; bottom: -400px; right: 0px; height: 150px; background-color: lightblue; }
    #footer .page:after { content: counter(page, upper-roman); }

      body{
    font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;
   font-size: 14px;
   /*line-height: 0.5;*/
  }

  .header{
    width: 100%;

    /*border: 1px solid red;*/
  }

  .title{
    font-family: 'DizhitlBold';
    font-weight: bold;
    font-style: normal;font-size: 12px;
  }

   .main_table tr td, .main_table tr th {
      /*border: 1px solid skyblue;*/
      border-left: 1px solid #ccc;
      padding: 0px 7px;
    }

 /* .main_table th{
    border:1px solid #ccc;
  }*/

  .main_table th{
    text-align: center;
  }

  </style>
  <?php
use Carbon\Carbon;
$grand_total = 0;


$arr = explode("\r\n", @$all_orders[0]->user->getCompany->bank_detail);

?>
<body>
  <div id="header">
   <table class="header" style="line-height: 0.5;">
                <tr>
                  <td colspan="1">
                    <img src="{{asset('public/uploads/logo/'.@Auth::user()->getCompany->logo)}}" width="150" height="80" style="margin-bottom: 0px;">

                  </td>
                  <td align="right">
                    <h2 style=""><span style="font-family: 'DizhitlBold';
                    font-weight: bold;
                    font-style: normal;font-size: 16px;">
                    <span style="text-transform: uppercase;line-height: 0.7;">Date <span style="text-decoration: underline;font-weight: 100;">{{@$billing_ref_no->created_at->format('d-m-Y')}}</span></span></h2>
                                      <h2 style=""><span style="font-family: 'DizhitlBold';
                    font-weight: bold;
                    font-style: normal;font-size: 16px;">
                    <span style="text-transform: uppercase;line-height: 0.7;">ใบวางบิล<br>Billing Note</span></h2>
                    <h2 style=""><span style="font-family: 'DizhitlBold';
                    font-weight: bold;
                    font-style: normal;font-size: 16px;">
                    <span style="text-transform: uppercase;line-height: 0.7;">No. <span style="text-decoration: underline;font-weight: 100;">{{@$billing_ref_no->prefix}}{{@$billing_ref_no->ref_no}}</span></span></h2>
                  </td>
                </tr>

                <tr>
                @if(Auth::user()->getCompany->prefix == 'P')
                  <td style="width: 50%;">
                    <p style="">
                       <h4 style="margin: 0;font-family: 'DizhitlBold';
                        font-weight: bold;
                        font-style: normal;font-size: 12px;"><strong>
                      {{Auth::user()->getCompany->company_name}}
                    </strong></h4>
                    <span style="line-height: 0.7;">
                      {{ @Auth::user()->getCompany->billing_address}},<br> {{@Auth::user()->getCompany->getcountry->name.', '.@Auth::user()->getCompany->getstate->name.', '.@Auth::user()->getCompany->billing_zip }} <br>
                        Branch 00001
                      </span>
                    </p>
                    <p style="margin: 0px 0px 6px;">
                      Phone: {{ Auth::user()->getCompany->billing_phone }}
                    </p>
                    <p>Tax ID: {{Auth::user()->getCompany->tax_id}}</p>

                  </td>
                @else

                <td style="width: 50%;">
                    <p style="">
                       <h4 style="margin: 0;font-family: 'DizhitlBold';
                        font-weight: bold;
                        font-style: normal;font-size: 12px;"><strong>
                      {{Auth::user()->getCompany->company_name}}
                    </strong></h4>
                    <span style="line-height: 0.7;">
                      {{ @Auth::user()->getCompany->billing_address}},<br> {{@Auth::user()->getCompany->getcountry->name.', '.@Auth::user()->getCompany->getstate->name.', '.@Auth::user()->getCompany->billing_zip }}
                      </span>
                    </p>
                    <p style="margin: 0px 0px 6px;">
                      Phone: {{ Auth::user()->getCompany->billing_phone }}
                    </p>
                    <p>Tax ID: {{Auth::user()->getCompany->tax_id}}</p>

                  </td>
                  @endif







                  @if(Auth::user()->getCompany->prefix == 'P')
                  <td style="width: 50%;" align="right">
                    <p style="">
                        <p style="margin: 0;font-size: 18px;">
                        <!-- บริษัท พรีเมี่ยม ฟู้ด (ประเทศไทย) จำกัด -->
                        {{Auth::user()->getCompany->thai_billing_name}}
                    </p>
                     <span style="line-height: 0.7;">
                        {{Auth::user()->getCompany->thai_billing_address}},<br>
                        อำเภอเมืองภูเก็ต จังหวัดภูเก็ต, {{@Auth::user()->getCompany->billing_zip}}<br>
                        สาขา 00001 <br>
                        โทรศัพท์: {{ Auth::user()->getCompany->billing_phone }}
                        </span>
                    </p>

              <p>เลขประจำตัวผู้เสียภาษี: 0105561152253</p>
                  </td>
                  @else

                  <td style="width: 50%;" align="right">
                    <p style="">
                        <p style="margin: 0;font-size: 18px;">
                      <!-- บริษัท พรีเมี่ยม ฟู้ด (ประเทศไทย) จำกัด -->
                      {{Auth::user()->getCompany->thai_billing_name}}
                    </p>
                     <span style="line-height: 0.7;">
                        <!-- สำนักงานใหญ่: 8/3, ซอยสุขุมวิท 62 แยก 8-5, พระโขนงใต้, -->
                        {{Auth::user()->getCompany->thai_billing_address}},
                        <br>
                        ประเทศไทย, กรุงเทพ, {{@Auth::user()->getCompany->billing_zip}}<br>
                        โทรศัพท์: {{ Auth::user()->getCompany->billing_phone }}
                        </span>
                    </p>

              <p>เลขประจำตัวผู้เสียภาษี: {{Auth::user()->getCompany->tax_id}}</p>
                  </td>
                  @endif




                </tr>
    </table>
    <div style="margin-top: 0px;border-top: 1px solid #ccc;">
      <table style="width: 100%">
        <tr>
          <td width="15%"><span class="title">รหัสลูกค้า/ No.</span></td>
          <td width="40%">{{@$all_orders[0]->customer->reference_number}}</td>
          <td width="15%"><span>Ref Name</span> :</td>
          <td align="left">{{@$all_orders[0]->customer->reference_name}}</td>
        </tr>
      </table>
         </div>
      <div style=""><span class="title">ชื่อลูกค้า/ Name : &nbsp;&nbsp;&nbsp;&nbsp;</span> {{@$all_orders[0]->customer->company}}</div>
     <div style="line-height: 0.5;"><span class="title">ที่อยู่/ Address : </span>{{@$customerAddress->billing_address}}, {{@$customerAddress->billing_city}}, {{@$all_orders[0]->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}}, {{@$all_orders[0]->customer->language == 'en' ? @$customerAddress->getcountry->name : (@$customerAddress->getcountry->thai_name !== null ? @$customerAddress->getcountry->thai_name : @$customerAddress->getcountry->name)}}, {{@$customerAddress->billing_zip}}</div>
     <div style="line-height: 0.5;margin-top: 10px;"><span>เลขที่ประจำตัวผู้เสียภาษี/Tax ID : {{@$customerAddress->tax_id !== null ? @$customerAddress->tax_id : '--'}}</span></div>
  </div>
  <div id="content">
   <div style="@if( strlen(@$all_orders[0]->customer->reference_name) < 31) margin-top: 6px @else margin-top: 45px; @endif">
       <table style="width: 100%;" class="main_table">
        <thead class="title" style="line-height: 0.6;background-color: #aaa;">
         <tr>
          <th>S.No #</th>
          <th align="center" width="20%" style="padding-top: 6px;"><span>วันที่</span><br>Date</th>
          <th align="center" width="60%"><span>ใบกำกับภาษีเลขที่</span><br>Invoice No.</th>
          <th align="center" width="20%"><span>ยอดเงินสุทธิ</span><br>Amount</th>
         </tr>
         </thead>
          @php $j = 0; $heightt = 280;$count = 0; @endphp

          <body style="line-height: 0.7;">
            @if(@$all_orders->count() > 0)
          @foreach(@$all_orders as $order)
          @php
            if(@$order->in_status_prefix !== null)
              {
                 $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
              }
            elseif(@$order->status_prefix !== null && @$order->primary_status == 25)
              {
                $ref_no = $order->status_prefix.$order->ref_id;
              }
            elseif($order->status_prefix !== null && $order->ref_prefix !== null && $order->ref_id !== null )
              {
                $ref_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              }
            else
              {
                 $ref_no = @$order->ref_id != null ? @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id : '';
              }
              $check_vat = $order->order_products()->where('vat','>',0)->count();

              $vat_total = @$order->order_products != null ? @$order->getOrderTotalVatAccounting($order->id,0) : 0;

              $vat_amount = @$order->order_products != null ? @$order->getOrderTotalVatAccounting($order->id,1) : 0;
              $vat_total = $vat_total - @$order->vat_total_paid;

              $check_non_vat = $order->order_products()->where(function($q){
                  $q->whereNull('vat')->orWhere('vat',0);

              })->count();

          $vat_totalll = @$order->order_products != null ? @$order->getOrderTotalVatAccounting($order->id,2) : 0;

          $non_vat_total = floatval(preg_replace('/[^\d.]/', '', $vat_totalll)) - @$order->non_vat_total_paid;
          $order_total = @$order->order_products->sum('total_price')+@$order->order_products->sum('vat_amount_total');

          //if($order->in_ref_id <= 23011593){
                //$nums = @$order->getorderTotal($order->id);
                //$vat_total = $nums['vat_items_total_after_discount'] + $nums['vat_total'];

                //$non_vat_total = $nums['non_vat_total_after_discount'];

                //$order_total = $vat_total + $non_vat_total;
              //}

          


          @endphp
          {{-- @if($check_vat > 0 && $vat_total > 0) --}}
          @php $count++; @endphp
          <tr>
            <td>{{$count}}</td>
            <td style="">
              @if(@$order->primary_status !== 25)
              {{-- {{ $order->delivery_request_date !== null ? Carbon::parse(@$order->delivery_request_date)->format('d/m/Y') : '--'}} --}}
              {{ $order->converted_to_invoice_on !== null ? Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') : '--'}}
              @else
              {{ $order->credit_note_date !== null ? Carbon::parse(@$order->credit_note_date)->format('d/m/Y') : '--'}}
              @endif
            </td>
            <td style="text-align: left;">{{@$ref_no}}</td>
            <td align="" style="text-align: right;border-right: 1px solid #ccc;">{{@$order->primary_status == 25 ? '-' : ''}}{{number_format($order_total,2,'.',',')}}</td>
          </tr>
          {{-- @endif --}}


          @php
          if(@$order->primary_status == 25)
          {
            $grand_total -= round($order_total, 2);
          }
          else
          {
            $grand_total += round($order_total, 2);
          }

          $heightt = $heightt - 40; @endphp
          @endforeach
         @endif
         </body>
       </table>
     </div>
      <div>
       <table style="width: 100%;border-top: 2px solid black;">
         <tr>
           <td width="53%" valign="top" colspan="2"></td>
           <td align="left" width="15%" valign="top" style="position: relative;"><span class="title" style="position: absolute;top: 7px;left: -50px;">รวมเงินสุทธิ/Grand Total</span></td>
           <td align="right" valign="top" style="position: relative;"><span style="position: absolute;right: 0;">{{number_format(@$grand_total,2,'.',',')}}</span></td>
         </tr>
         <tr>
           <td colspan="4" height="20"></td>
         </tr>

       </table>
     </div>
     <div style="margin-top: 5px;page-break-inside: avoid">
       <table style="width: 100%;">
        <tr>
          <td colspan="3">
            <span style="font-size: 14px;font-family: sans-serif;"><span style=""></span><span style="">{{@$arr[0]}}<br>{{@$arr[1]}}<br>{{@$arr[2]}}</span></span>
          </td>
        </tr>
        <tr>
           @php
            $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);

            $total = number_format(@$grand_total,2,'.','');
            $total_split = explode('.',$total);
            $word1 = $f->format($total_split[0]);
            $word2 = $f->format($total_split[1]);

            $word = $word1.' and '.$word2.' Satang';


           @endphp
           <td style="padding-bottom: 10px;"><span class="title" style="font-size: 14px;line-height: 0.5;">จำนวนเงิน/Amount</span></td>
           <td style="text-transform: uppercase;line-height: 0.7;" width="40%">{{@$word}}</td>
           <td></td>
         </tr>
         <tr style="">
           <td align="center" class="title" style="padding-top: 7px;border-top: 2px solid black;"><span style="line-height: 0.7;">ผู้วางบิล<br>Billing By</span></td>
           <td align="center" class="title" style="border-top: 2px solid black;"><span style="line-height: 0.7;padding-top: 10px;">ผู้รับวางบิล<br>Received By</span></td>
           <td align="center" class="title" style="border-top: 2px solid black;"><span style="line-height: 0.7;padding-top: 10px;">วันที่นัดชำระ<br>Payment Date</span></td>
         </tr>
         <tr>
           <td colspan="3" height="30"></td>
         </tr>
        <!--  <tr>
           <td align="center" valign="bottom">............................................</td>
           <td align="center" valign="bottom">............................................</td>
           <td align="center" valign="bottom">............................................</td>

         </tr> -->
         <tr>
           <td align="center" ><span>Date...................................</span></td>
           <td align="center" ><span>Date...................................</span></td>
           <td align="center" ><span>Date...................................</span></td>
         </tr>
       </table>
     </div>
  </div>
  <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 35;
        $pdf->page_text('30', $y, $text, $font, $size);
    }
</script>
</body>
</html>
