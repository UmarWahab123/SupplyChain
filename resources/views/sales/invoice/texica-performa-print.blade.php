<!DOCTYPE html>
<html lang="en-US">
<head>
  @if(@$order->primary_status == 2)
  <title>DELIVERY ORDER</title>
  @else
   @if(@$proforma == 'yes')
  <title>DELIVERY ORDER</title>
   @else
  <title>Delivery ORDER</title>
  @endif
  @endif
  <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
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
      .code_header tr td
      {
        padding-left: 10px;
      }
      .custom-styles th, td{
        vertical-align: middle !important;
      }
    </style>
    @php
      use Carbon\Carbon;
      use App\Models\Common\Order\Order;
    @endphp
</head>
@foreach($orders_array as $id)
@php
  $data = Order::getDataForInvIncVat($id,$column_name,$default_sort);
  $order = $data['order'];
  $arr = $data['arr'];
  $customerAddress = $data['customerAddress'];
  $customerShippingAddress = $data['customerShippingAddress'];
  $order_products = $data['order_products'];
  $inv_note = $data['inv_note'];
  $total_products_count_qty = $data['total_products_count_qty'];
  $total_products_count_pieces = $data['total_products_count_pieces'];
  $all_orders_count = $data['all_orders_count'];
  $do_pages_count = $data['do_pages_count'];
  $final_pages = $data['final_pages'];
  $pages = $data['pages'];
  $first_total = 0;
  $vat_total = 0;
  $order_total_at_end = 0;
  $order_total_with_vat = 0;
  $per_page_id1 = 0; 
  $indexes = 1;
  $totalQty = 0;
@endphp
@for($z = 1 ; $z <= $do_pages_count ; $z++)
<body>

<div class="container" style="width: 100%; margin: 0 auto;">

<header>
  <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6;"><strong>ใบส่งของ</strong></h2>
  <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6;"><strong>DELIVERY ORDER</strong></h2>
</header>

<section>
  <!-- <h2 style="text-align: center;margin: 0;">TAX INVOICE/DELIVERY ORDER</h2> -->
  <table cellspacing="0" cellpadding="0" border="1" style="width: 100%;font-size: 20px;">
    <tr>
      <td width="60%">
        <table class="code_header">
          <tr>
            <td width="20%" style="font-weight: bold; line-height: 12px; padding-top: 4px;">Code</td>
            <td style="line-height: 12px; padding-top: 4px;">
              {{@$order->customer->reference_number}}
            </td>
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Name</td>
            <td style="line-height: 12px;">
              {{@$order->customer->reference_name !== null ? @$order->customer->reference_name : '--'}}<br>
              {{@$order->customer->company}}<br>
              {{@$customerAddress->billing_address}} {{@$customerAddress->billing_city}} {{@$order->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}} {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name : ''))}} {{@$customerAddress->billing_zip}} {{@$customerAddress->billing_phone}}
            </td>
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Tax ID :</td>
            <td style="line-height: 12px;">{{@$customerAddress->tax_id}}</td>
          </tr>
        </table>
      </td>
      <td width="40%">
        <table width="100%" class="code_header">
          <tr>
            <td style="font-weight: bold; width: 50%; line-height: 12px; padding-top: 4px;">Invoice Date</td>
            @if($invoiceEditAllow == 1)
              <td style="line-height: 12px; padding-top: 4px;">
                {{ $order->delivery_request_date != null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : "--" }}
              </td>
            @else
              <td style="line-height: 12px; padding-top: 4px;">
                {{ $order->converted_to_invoice_on != null ? Carbon::parse($order->converted_to_invoice_on)->format('d/m/Y') : "--" }}
              </td>
            @endif
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Invoice No.</td>
            <td style="line-height: 12px;">
              @if(@$order->in_status_prefix !== null)
                {{@$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id}}
              @else
                {{@$order->user->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id}}
              @endif
            </td>
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Order No.</td>
            <td style="line-height: 12px;">
              @if(@$order->status_prefix !== null)
                {{@$order->status_prefix.''.$order->ref_prefix.$order->ref_id}}
              @else
                {{@$order->user->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id}}
              @endif
            </td>
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Ref. Po No.</td>
            <td style="line-height: 12px;">
              @if($order->memo != null)
                {{$order->memo}}
              @else
                --
              @endif
            </td>
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Payment Term</td>
            <td style="line-height: 12px;">
              @if($order->payment_terms_id != null)
                {{$order->paymentTerm->title}}
              @else
                --
              @endif
            </td>
          </tr>
          <tr>
            <td style="font-weight: bold; line-height: 12px;">Sale Name</td>
            <td style="line-height: 12px;">{{$order->user != NULL ? $order->user->name : "--"}}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <table class="custom-styles" cellspacing="0" cellpadding="0" border="1" style="width: 100%; margin-top: 0px; border-top: 0;">
  <tbody>
  <tr>
    <td style="padding: 0 2px; width: 10%; font-weight: bold; text-align: center; line-height: 0.6;">Product<br> Code</td>
    <td style="padding: 0 2px; width: 40%; font-weight: bold; text-align: center; line-height: 0.6;">{{$global_terminologies['product_description']}}</td>
    <td style="padding: 0 2px; width: 8%; font-weight: bold; text-align: center; line-height: 0.6;">Vol.%<br>(L/Btl.)</td>
    <td style="padding: 0 2px; width: 8%; font-weight: bold; text-align: center; line-height: 0.6;">Alc.<br>(%)</td>
    <td style="padding: 0 2px; width: 10%; font-weight: bold; text-align: center; line-height: 0.6;">Qty<br> (Bottles)</td>
    <td style="padding: 0 2px; width: 12%; font-weight: bold; text-align: center; line-height: 0.6;">Unit Price<br> (THB/Bottle)</td>
    <td style="padding: 0 2px; width: 12%; font-weight: bold; text-align: center; line-height: 0.6;">Unit Price<br> (With Discount)</td>
    <td style="padding: 0 2px; width: 12%; font-weight: bold; text-align: center; line-height: 0.6;">Total<br> (THB/Bottle)</td>
  </tr>

  @if(@$order->count() > 0)
  @php
    $product_count1 = 0;
  @endphp

  @foreach($order->order_products as $result)
  @if($result->id > $per_page_id1)

  @if((($order->primary_status == 2 && $result->is_retail == 'qty' && ($result->quantity != 0 || $result->get_order_product_notes->count() > 0) || $result->is_retail == 'pieces' && ($result->number_of_pieces != 0 || $result->get_order_product_notes->count() > 0)) || ($order->primary_status == 3 && $result->is_retail == 'qty' && ($result->qty_shipped != 0 || $result->get_order_product_notes->count() > 0) || $result->is_retail == 'pieces' && ($result->pcs_shipped != 0 || $result->get_order_product_notes->count() > 0))) && @$result->id > @$per_page_id1)

  @php 
    $product_count1++;
    $counting = $do_pages_count - $z;
    if($counting == 1)
    {
      if($z != 1 && $all_orders_count > 8)
      {
        if($product_count1 > 8)
        {
          break;
        }  
      }
      elseif($z == 1 && $all_orders_count > 8 && $all_orders_count < 17)
      {
        if($product_count1 > 8)
        {
          break;
        }
      }
      else
      {
        if($product_count1 > 9)
        {
          break;
        }
      }
      
    }
    else
    {
      if($counting == 0)
      {
        if($product_count1 > 8)
        {
          break;
        }
      }
      else
      {
        if($product_count1 > 9)
        {
          break;
        }
      }
    }
  @endphp

  <tr>
    <td style="padding: 5px 2px; width: 10%; text-align: center; line-height: 0.5;">{{ @$result->product->refrence_code }}</td>
    <td style="padding: 5px 2px; width: 40%; line-height: 0.5;" height="40px">
      @php 
        if($result->product_id != null)
        {
          $total_str = substr($result->brand.' - '.$result->short_desc.' - '.$result->productType->title,0,95);
        }
        else
        {
          $total_str = substr($result->brand.' - '.$result->short_desc,0,95);
        }
      @endphp
      @if(strlen($total_str) >= 95)
        {{ $total_str }} ...
      @else
        {{ $total_str }}
      @endif
    </td>
    <td style="padding: 5px 2px; width: 8%; text-align: center; line-height: 0.5;">{{ @$result->product->product_notes != NULL ? $result->product->product_notes : "--" }}</td>
    <td style="padding: 5px 2px; width: 8%; text-align: center; line-height: 0.5;">{{ @$result->product->product_temprature_c != NULL ? $result->product->product_temprature_c." %" : "--" }}</td>
    <td style="padding: 5px 2px; width: 10%; text-align: center; line-height: 0.5;">
      @if(@$order->primary_status == 3)
        @if(@$result->is_retail == 'qty')
          @php $num_to_multiply = @$result->qty_shipped; @endphp
          {{ @$result->qty_shipped != null ? @$result->qty_shipped : 0 }}
           @else 
           @php $num_to_multiply = @$result->pcs_shipped; @endphp
          {{ @$result->pcs_shipped != null ? @$result->pcs_shipped : 0 }}
          @endif
          @else
          @if(@$result->is_retail == 'qty')
          @php $num_to_multiply = @$result->quantity; @endphp
          {{ @$result->quantity != null ? @$result->quantity : 0 }}
          @else 
          @php $num_to_multiply = @$result->number_of_pieces; @endphp
          {{ @$result->number_of_pieces != null ? @$result->number_of_pieces : 0 }}
        @endif
      @endif
    </td>
    <td style="padding: 5px 2px; width: 12%; text-align: right; line-height: 0.5;">
      @php
        $unit_price = $result->unit_price;
      @endphp
      {{number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$unit_price))), 2, '.', ',')}}
    </td>

    @if(@$order->primary_status == 3)
    @if(@$result->is_retail == 'qty')
    @php $num_to_multiply = @$result->qty_shipped; @endphp
    @else 
    @php $num_to_multiply = @$result->pcs_shipped; @endphp
    @endif
    @else
    @if(@$result->is_retail == 'qty')
    @php $num_to_multiply = @$result->quantity; @endphp
    @else 
    @php $num_to_multiply = @$result->number_of_pieces; @endphp
    @endif
    @endif

    @php $vat = (@$result->vat/100)*@$result->total_price; 
      $first_total = $first_total + @$result->total_price_with_vat;
      $vat_total = $vat_total + $vat;

      $dis_value = 0;

      if(@$result->discount !== null)
      {
        $total = @$unit_price_with_vat*$num_to_multiply;
        $dis_value = ($total * ((@$result->discount)/100));
      }
    @endphp
    @php @$order_total_at_end = @$order_total_at_end + $result->total_price;
         @$order_total_with_vat = $order_total_with_vat + @$result->total_price_with_vat; 
    @endphp
    <td style="padding: 5px 2px; width: 12%; text-align: right; line-height: 0.5;">{{($result->unit_price_with_discount != null && $result->discount != 0) ? number_format($result->unit_price_with_discount, 2, '.', ','): "--" }}</td>
    <td style="padding: 5px 2px; width: 12%; text-align: right; line-height: 0.5;">{{number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$result->total_price))), 2, '.', ',')}}</td>
  </tr>

  @endif

  @php 
    @$per_page_id1 = $result->id;
  @endphp

  @php
    if($order->primary_status == 2)
    {
      $totalQty += $result->quantity;
    }
    else
    {
      $totalQty += $result->qty_shipped;
    }
  @endphp


  @endif
  @endforeach
  @endif
  </tbody>
  
  @if($z == $do_pages_count)
  <tfoot>
    <tr>
      <td colspan="4" style="padding: 0 5px; font-weight: bold;">Total</td>
      <td style="padding: 0 2px; width: 10%; text-align: center;">{{$totalQty}}</td>
      <td></td>
      <td></td>
      <td></td>
    </tr>
  </tfoot>
  </table>

  <table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
      <td style="padding: 0 5px; width: 20%;"></td>
      <td style="padding: 0 5px;"></td>
      <td style="    text-align: right;
      padding-right: 10px; padding: 0 5px;">รวมเป็นเงิน</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right; width: 12%;">
        {{number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$order_total_at_end))), 2, '.', ',')}}
      </td>
    </tr>

    <!-- <tr>
      <td style="padding: 5px; width: 20%;"></td>
      <td style="padding: 5px;"></td>
      <td style="    text-align: right;
      padding-right: 10px; padding: 5px;">Total</td>
      <td style="border: 1px solid #000; padding: 5px;"> {{preg_replace('/\.(\d{2}).*/', '.$1', ((@$order_total_at_end)))}} </td>
    </tr> -->

    <tr>
      <td style="padding: 0 5px; width: 20%;"></td>
      <td style="padding: 0 5px;"></td>
      <td style="    text-align: right;
      padding-right: 10px; padding: 0 5px;">ภาษีมูลค่าเพิ่ม 7%</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;"> @php $total_vat = @$order_total_with_vat - @$order_total_at_end @endphp
        {{number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$total_vat))), 2, '.', ',')}}
         </td>
        }
        }
    </tr>
    <tr>
      <td style="padding: 0 5px;"></td>
      <td  style="padding: 0 5px;"><!-- (สี่พันบาทถ้วน) --></td>
      <td  style="text-align: right; padding-right: 10px; padding: 0 5px;">จำนวนเงินรวมทั้งสิ้น </td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">{{number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$order_total_with_vat))), 2, '.', ',')}}</td>
    </tr>
  </table>

  @endif
</section>


<footer style="padding-top: 50px; width: 100%; position: absolute; bottom: 3;">
@if($z == $do_pages_count)  
<table cellspacing="0" cellpadding="0" border="0" width="100%;" style="margin-left: 50px;">
<tr style="width: 100%">
  <td style="width: 50%; line-height: .7;"><strong>Note :</strong>
    @if($inv_note)
      {{$inv_note->note != null ? $inv_note->note : ""}}
    @endif
  </td>
  <td style="width: 50%; line-height: .7;"><strong></strong></td>
</tr>

<tr>
<td style="line-height: .7;"><strong>1. Please transfer the payment to :</strong></td>
</tr> 

<tr> @ 
<td style="line-height: .7;"><strong>A/C Name :</strong> {{@$bank->title}}</td>
</tr>

<tr>  
<td style="line-height: .7;"><strong>A/C No. :</strong> {{@$bank->account_no}}</td>
</tr> 

<tr>  
<td style="line-height: .7;"><strong>Bank :</strong> {{@$bank->description}}</td>
</tr>

<tr>  
<td style="line-height: .7;"><strong>Branch :</strong> {{@$bank->branch}}</td>
</tr>

<tr>  
<td style="font-weight: bold; line-height: .7; width: 100%;">2. หากเกินกำหนดชำระ ทางบริษัทฯจะคิดดอกเบี้ยร้อยละ 1.5 ต่อเดือน<br>
    3. สินค้าตามรายการข้างต้น แม้จะส่งมอบแก่ผู้ซื้อแล้วยังคงเป็นทรัพย์สินของผู้ขาย
    จนกว่าผู้ซื้อจะได้ชำระเงินเต็มตามจำนวนเรียบร้อยแล้ว</td>
</tr>

<!-- <tr>  
<td>Please after transfer confirm the payment with our accounting dept.<br>
    by calling 02-713-6034 / 02-713-6035<br>
    You can also call or SMS at : 08 6986 25 25</td>
</tr> -->
</table>
<table cellspacing="0" cellpadding="0" border="0" width="100%;" style="margin-top: 50px;" >
<tr>
  <td style="text-align: center;"><p style="border-bottom: 1px solid #000; text-align: center;margin-bottom: 0;"></p><br>ผู้รับของ/Receiver</td>
  <td style="width: 15%;">&nbsp;</td>
  <td style="text-align: center;"><p style="border-bottom: 1px solid #000;text-align: center;margin-bottom: 0;"></p><br>ผู้ส่งสินค้า/Delivery</td>
  <td style="width: 15%;">&nbsp;</td>
  <td style="text-align: center;"><p style="border-bottom: 1px solid #000; text-align: center;margin-bottom: 0;"></p><br>ผู้รับมอบอำนาจ/Authorized Signature</td>
</tr>
</table>
@endif
<span style="float: right;">
  {{$z}}/{{$do_pages_count}}
</span>
</footer>

</div>

</body>
@endfor
@endforeach
</html>