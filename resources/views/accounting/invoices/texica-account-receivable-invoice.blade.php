<!DOCTYPE html>
<html lang="en-US">
<head>
<title>Billing Note</title>
  <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/>
  <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/dzhitl" type="text/css"/>
  <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
  <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css">

  <style type="text/css">
    @page {size: auto;   /* auto is the initial value */
      margin: 5mm;}
    table { font-size: 17px; }
    table tr td 
    {
      vertical-align: top; padding-bottom: 3px;
    }
    table 
    {
      border-collapse: collapse;
      font-size:17px;
      border-spacing: 0px;}
    body {
      font-family: 'TH Sarabun New';
      font-weight: bold;
      /*font-style: normal;*/
      font-size: 17px;
      line-height: 1;
    }
  </style>
  <?php
    use Carbon\Carbon;
    use App\OrderTransaction;
    $grand_total = 0;
    $arr = explode("\r\n", @$all_orders[0]->user->getCompany->bank_detail);
  ?>
</head>

@php 
  $per_page_id1 = []; 
  $indexes = 1;
@endphp
@for($z = 1 ; $z <= $pages ; $z++)
<body>
<div class="container" style="width: 100%; margin: 0 auto;">

<header>
<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border-bottom: 1px solid #000;">
  <tr>
    <td style="padding: 0; width: 12%;"><img src="{{public_path('uploads/logo/'.@Auth::user()->getCompany->logo)}}" style="height: 80px;"></td>
    <td style="padding: 0 10px 5px; line-height: .6;">
      <strong style="font-weight: bold; width: 65%; line-height: .6; display: block;">{{@Auth::user()->getCompany->company_name}}</strong>
      <font style="display: block; line-height: .6;">46/8 ซอย แยกซอยสันติสุข แขวงพระโขนง เขตคลองเตย กรุงเทพมหานคร 10110</font>
      <font style="display: block; line-height: .6;">46/8  Soi Yaek Santisuk, Phrakhanong, Klongtoey, Bangkok 10110 ,THAILAND </font>
      <font style="display: block; line-height: .7;">   Tel : 66-2-713-6034  Fax : 66-2-713-6036</font>                          
      <font style="display: block; line-height: .7;">Head Office   Tax ID :  0105549032227 </font>
    </td> 
    <td style="font-weight: bold; font-size: 24px; width: 20%; line-height: .7;">ใบวางบิล<br>Billing Note</td>
  </tr>
</table>
</header>


<section>
  
<table cellspacing="0" cellpadding="0" border="0" width="100%">
  <tr>
    <td style="line-height: .8;"><strong>รหัสลูกค้า / No.</strong> {{@$all_orders[0]->customer->reference_number}}</td>
  </tr>

  <tr>
    <td colspan="2" style="font-weight: bold; line-height: .8;">รายละเอียดลูกค้า/Customer Information</td>
  </tr>

  <tr>
    <td colspan="2" style="line-height: .8;">{{@$all_orders[0]->customer->reference_name}}</td>
  </tr>

  <tr>
    <td colspan="2" style="line-height: .8;">{{@$all_orders[0]->customer->company}}</td>
  </tr>

  <tr>
    <td colspan="2" style="line-height: .8;">{{@$customerAddress->billing_address}}, {{@$customerAddress->billing_city}}, {{@$order->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}}, {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name : ''))}}, {{@$customerAddress->billing_zip}}</td>
  </tr>

  <tr>
    <td colspan="2" style="line-height: .8;">
      @if($customerAddress->billing_phone != null)
        Tel: {{@$customerAddress->billing_phone != null ? $customerAddress->billing_phone : "--"}} 
      @endif
      @if($customerAddress->billing_fax != null)
        Fax: {{@$customerAddress->billing_fax != null ? $customerAddress->billing_fax : "--"}}
      @endif
    </td>
  </tr>

  <tr>
    <!-- <td style="width: 50%;">Billing Note #.  --</td> -->
    <td style="width: 50%; line-height: .8;">
      Tax ID. {{@$customerAddress->tax_id != null ? @$customerAddress->tax_id : "--"}}
    </td>
  </tr>

</table>

<table cellspacing="0" cellpadding="0" border="1" width="100%" style="margin-top: 8px;">
<tr>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;"><strong>เลขที่เอกสาร<br>Document No.</strong></td>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;"><strong>วันที่เอกสาร<br>Document Date</strong></td>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;"><strong>เงื่อนไขการชำระ <br>Payment Terms</strong></td>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;"><strong>ชำระโดย<br>Payment Method </strong></td>
</tr>

<tr>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;">{{@$billing_ref_no->prefix}}{{@$billing_ref_no->ref_no}}</td>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;">
    {{$receipt_date !== '' ? preg_replace('/\//', '-', $receipt_date)  : $billing_ref_no->created_at->format('d-m-Y')}}
  </td>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;">{{@$getPaymentTerm != null ? @$getPaymentTerm->title : '--'}}</td>
  <td style="padding-top: 0 5px;text-align: center; line-height: .8;">--</td>

</tr>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 15px; height: 350px !important;">
<tr>
  <td style="padding-top: 0 5px; text-align: center; border-bottom:1px solid #000; line-height: .8; "><strong>เลำดับ<br> No.</strong></td>
  <td style="padding-top: 0 5px;text-align: center;border-bottom:1px solid #000; line-height: .8;"><strong>เลขที่ใบกำกับ<br> Tax Invoice No. </strong></td>
  <td style="padding-top: 0 5px;text-align: center;border-bottom:1px solid #000; line-height: .8;"><strong>วันที่เอกสาร<br> Inv. Date</strong></td>
  <td style="padding-top: 0 5px;text-align: center;border-bottom:1px solid #000; line-height: .8;"><strong>เลขที่ใบสังซื้อ<br>P/O No.  </strong></td>
  <td style="padding-top: 0 5px;text-align: center;border-bottom:1px solid #000; line-height: .8;"><strong>วันครบกำหนด<br>Due Date</strong></td>
  <!-- <td style="padding-top: 15px;text-align: center;border-bottom:1px solid #000 ;"><strong>ยอดคงค้าง<br>Remaining Amt.</strong></td> -->
  <td style="padding-top: 0 5px;text-align: center;border-bottom:1px solid #000; line-height: .8;"><strong>จำนวนเงิน<br>Amount </strong></td>

</tr>
  @if(@$all_orders->count() > 0)
  @php
    $product_count1 = 0;
  @endphp

  @foreach(@$all_orders as $order)
  @if(!in_array($order->id, $per_page_id1))

  @php 
    $product_count1++; 
    if($product_count1 > 10)
    {
      break;
    }
  @endphp

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
    $vat_total = $vat_total;

    $check_non_vat = $order->order_products()->where(function($q){
      $q->whereNull('vat')->orWhere('vat',0);
    })->count();

    $amount_paid = OrderTransaction::where('order_id' , $order->id)->sum('total_received');
    $amount_due = $order->total_amount-$amount_paid;

    $vat_totalll = @$order->order_products != null ? @$order->getOrderTotalVatAccounting($order->id,2) : 0;
    $non_vat_total = floatval(preg_replace('/[^\d.]/', '', $vat_totalll));
  @endphp
  
  @if($check_vat > 0 && $vat_total > 0)
  <tr>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8; ">{{$indexes++}}</td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{@$ref_no}}</td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{ $order->converted_to_invoice_on !== null ? Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') : '--'}}</td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">
      {{ $order->memo !== null ? $order->memo : '--'}}
    </td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{ $order->payment_due_date !== null ? Carbon::parse(@$order->payment_due_date)->format('d/m/Y') : '--'}}</td>
    <!-- <td style="padding-top: 15px;text-align: center;">{{number_format($amount_due,2,'.',',')}}</td> -->
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{@$order->primary_status == 25 ? '-' : ''}}{{number_format(@$vat_total,2,'.',',')}}</td>
  </tr>
  @endif

  @if($check_non_vat > 0 && $non_vat_total > 0)
  <tr>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8; ">{{$indexes++}}</td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{@$ref_no}}</td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{ $order->converted_to_invoice_on !== null ? Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') : '--'}}</td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">
      {{ $order->memo !== null ? $order->memo : '--'}}
    </td>
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{ $order->payment_due_date !== null ? Carbon::parse(@$order->payment_due_date)->format('d/m/Y') : '--'}}</td>
    <!-- <td style="padding-top: 15px;text-align: center;">{{number_format($amount_due,2,'.',',')}}</td> -->
    <td style="padding-top: 0 5px; text-align: center; line-height: .8;">{{@$order->primary_status == 25 ? '-' : ''}}{{number_format(@$non_vat_total,2,'.',',')}}</td>
  </tr>
  @endif
  
  @php 
    array_push($per_page_id1, $order->id);
  @endphp

  @php
    if($order->primary_status == 25)
    {
      $grand_total -= ($non_vat_total + @$vat_total);
    } 
    else
    {
      $grand_total += $non_vat_total + @$vat_total;
    }
  @endphp

  @endif
  @endforeach
  @endif
</table>

@if($z == $pages)
<div style="border-top:2px solid #000; border-bottom:2px solid #000;">
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="">
  <td style="width: 60%; padding-top: 5px;"><strong>หมายเหตุ :  Remark</strong>  </td>
  <td style="width: 20%; padding-top: 5px; text-align: right;"><strong>รวมเงินสุทธิ/Grand Total :</strong></td>
  <td style="width: 20%; padding-top: 5px; text-align: center;"><strong>{{number_format(@$grand_total,2,'.',',')}}</strong></td>
</table>

@php 
  $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
  $total = number_format(@$grand_total,2,'.','');
  $total_split = explode('.',$total);
  $word1 = $f->format($total_split[0]);
  $word2 = $f->format($total_split[1]);
  $word = $word1.' and '.$word2.' Satang';                
@endphp

<p style="padding-bottom: 0px; margin-bottom: 0px;"><strong>จำนวนเงิน Amount :</strong> ({{$word}})</p>
</div>
@endif

<footer style="width: 100%; position: absolute; bottom: 3;">
@if($z == $pages)
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="">
  
<td style="/*padding-top: 50px;*/ width: 33%; text-align: center;"><strong>................................................</strong></td>
<td style="/*padding-top: 50px;*/ width: 33%; text-align: center;"><strong>................................................</strong></td>
<td style="/*padding-top: 50px;*/ width: 33%; text-align: center;"><strong>................................................</strong></td>

</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="">
  
<td style="/*padding-top: 15px;*/ width: 33%; text-align: center;"><strong>ผู้วางบิล Billing By </strong> </td>
<td style="/*padding-top: 15px;*/ width: 33%; text-align: center;"><strong>ผู้รับวางบิล Received By </strong></td>
<td style="/*padding-top: 15px;*/ width: 33%; text-align: center;"><strong>วันที่นัดชำระเงิน  Payment Date  </strong></td>

</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="">
  
<td style="/*padding-top: 15px;*/ width: 33%; text-align: center;"><strong>Date ……./………./……………….</strong></td>
<td style="/*padding-top: 15px;*/ width: 33%; text-align: center;"><strong>Date ……./………./……………….</strong></td>
<td style="/*padding-top: 15px;*/ width: 33%; text-align: center;"><strong>Date ……./………./……………….</strong></td>

</table>
@endif
<span style="float: right;">
  {{$z}}/{{$pages}}
</span>
</footer>

</section>
  
</body>
@endfor
</html>