<!DOCTYPE html>
<html lang="en-US">
<head>
  @if($order->primary_status == 25)
    <title>Credit Note</title>
  @else
    <title>Debit Note</title>
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
    $first_total = 0;
    $vat_total = 0;
    $order_total_at_end = 0;
    $order_total_with_vat = 0;
    $orderTotal = 0;
    $subTotal = 0;
  @endphp
</head>

@php 
  $per_page_id1 = 0; 
  $indexes = 1;
@endphp

@for($z = 1 ; $z <= $do_pages_count ; $z++)
<body>

<div class="container" style="width: 100%; margin: 0 auto;">

<header>
  <h1 style="text-align: center; font-size: 30px; margin: 0; padding: 0; line-height: .5;">บริษัท เท็กซีก้า จำกัด (สาขา 00003)</h1>
  <h1 style="text-align: center; font-size: 30px; margin: 0; padding: 0; line-height: .5;">{{@Auth::user()->getCompany->company_name}}</h1>
  <p style="text-align: center; margin: 0; padding: 0; line-height: .7;">46/8 ซอย แยกซอยสันติสุข แขวงพระโขนง เขตคลองเตย กรุงเทพมหานคร 10110<br>46/8 Soi Yaek Santisuk, Phrakhanong, Klongtoey, Bangkok 10110 ,THAILAND</p>
  <p style="text-align: center; margin: 0; padding: 0; line-height: .7;">Tel. {{ Auth::user()->getCompany->billing_phone }}   Fax. {{ Auth::user()->getCompany->billing_fax }}</p>
  <p style="text-align: center; margin: 0; padding: 0; line-height: .7;">Tax ID No.{{Auth::user()->getCompany->tax_id}}</p>
  <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6;"><strong>ใบลดหนี้</strong></h2>
  @if($order->primary_status == 25)
    <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6;">CREDIT NOTE</h2>
  @else
    <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6;">DEBIT NOTE</h2>
  @endif
</header>


<section>
  <table cellspacing="0" cellpadding="0" border="1" style="width: 100%; font-size: 20px; ">
    <tr>
      <td width="70%">
        <table class="code_header">
          <tr>
            <td width="20%" style="line-height: 12px; padding-top: 4px;">Code</td>
            <td style="line-height: 12px; padding-top: 4px;">
              {{@$order->customer->reference_number}}
            </td>
          </tr>
          <tr>
            <td style="line-height: 12px;">Name</td>
            <td style="line-height: 12px;">
              {{@$order->customer->reference_name !== null ? @$order->customer->reference_name : '--'}}<br>
              {{@$order->customer->company}}<br>
              {{@$customerAddress->billing_address}} {{@$customerAddress->billing_city}} {{@$order->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}} {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name : ''))}} {{@$customerAddress->billing_zip}}
            </td>
          </tr>
          <tr>
            <td style="line-height: 12px;">Tax ID :</td>
            <td style="line-height: 12px;">{{@$customerAddress->tax_id}}</td>
          </tr>
        </table>
      </td>
      <td width="30%">
        <table width="100%" class="code_header">
          <tr>
            <td style="line-height: 12px; padding-top: 4px;">Date</td>
            <td style="line-height: 12px; padding-top: 4px;">{{@$order->credit_note_date != null ? Carbon::parse(@$order->credit_note_date)->format('d/m/Y') : '--'}}</td>
          </tr>
          <tr>
            <td style="line-height: 12px;">CN No.</td>
            <td style="line-height: 12px;">
              @if(@$order->status_prefix !== null)
                {{@$order->status_prefix.$order->ref_id}}
              @else
                {{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id}}
              @endif
            </td>
          </tr>
          <tr>
            <td style="line-height: 12px;">Ref. IV No.</td>
            <td style="line-height: 12px;">
              {{$order->memo != NULL ? $order->memo : "--"}}
            </td>
          </tr>
          <tr>
            <td style="line-height: 12px;">Sale</td>
            <td style="line-height: 12px;">{{$order->customer != NULL ? $order->customer->primary_sale_person->name : "--"}}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <table class="custom-styles" cellspacing="0" cellpadding="0" border="1" style="width: 100%; margin-top: 0px; border-top: 0;">
  <tr>
    <td style="padding: 0 2px; width: 10%; text-align: center; line-height: 0.6;">Product<br> Code</td>
    <td style="padding: 0 2px; width: 42%; text-align: center; line-height: 0.6;">Description of goods</td>
    <td style="padding: 0 2px; width: 8%; text-align: center; line-height: 0.6;">Vol.%<br>(L/Btl.)</td>
    <td style="padding: 0 2px; width: 8%; text-align: center; line-height: 0.6;">Alc.<br>(%)</td>
    <td style="padding: 0 2px; width: 8%; text-align: center; line-height: 0.6;">Quantity<br> (Bottles)</td>
    <td style="padding: 0 2px; width: 12%; text-align: center; line-height: 0.6;">Unit Price<br> (THB/Bottle)</td>
    <td style="padding: 0 2px; width: 12%; text-align: center; line-height: 0.6;">Total<br> (THB/Bottle)</td>    
  </tr>

  @if(@$order->order_products->count() > 0)
  @php
    $product_count1 = 0;
  @endphp

  @foreach($order->order_products as $result)
  @if($result->id > $per_page_id1)

  @php 
    $product_count1++; 
    $counting = $do_pages_count - $z;
    if($counting == 1)
      {
        if($z != 1 && $all_orders_count > 9)
        {
          if($product_count1 > 9)
          {
            break;
          }  
        }
        elseif($z == 1 && $all_orders_count > 9 && $all_orders_count < 17)
        {
          if($product_count1 > 9)
          {
            break;
          }
        }
        else
        {
          if($product_count1 > 10)
          {
            break;
          }
        }
        
      }
      else
      {
        if($counting == 0)
        {
          if($product_count1 > 9)
          {
            break;
          }
        }
        else
        {
          if($product_count1 > 10)
          {
            break;
          }
        }
      }
  @endphp

    <tr>
      <td style="padding: 0 2px; width: 10%; text-align: center; line-height: 0.5;">{{ @$result->product->refrence_code }}</td>
      <td style="padding: 0 2px; width: 42%; text-align: left; line-height: 0.5;" height="30px">{{ @$result->short_desc }}</td>
      <td style="padding: 0 2px; width: 8%; text-align: center; line-height: 0.5;">{{ @$result->product->product_notes != NULL ? $result->product->product_notes : "--" }}</td>
      <td style="padding: 0 2px; width: 8%; text-align: center; line-height: 0.5;">{{ @$result->product->product_temprature_c != NULL ? $result->product->product_temprature_c." %" : "--" }}</td>
      <td style="padding: 0 2px; width: 8%; text-align: center; line-height: 0.5;">
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
      <td style="padding: 0 2px; width: 12%; text-align: right; line-height: 0.5;">
        @php
          $unit_price = $result->unit_price;
        @endphp
        {{number_format(preg_replace('/\.(\d{2}).*/', '.$1', (((float)@$unit_price))), 2, '.', ',')}}
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

      @php
        $subTotal += $result->total_price;
        $orderTotal +=  $result->total_price_with_vat;
        $vat = (@$result->vat/100)*@$result->total_price; 
        $first_total = $first_total + @$result->total_price;
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
      <td style="padding: 0 2px; width: 12%; text-align: right; line-height: 0.5;">
        {{number_format(preg_replace('/\.(\d{2}).*/', '.$1', (((float)@$result->total_price))), 2, '.', ',')}}
      </td>
    </tr>

  @php 
    @$per_page_id1 = $result->id;
  @endphp
  
  @endif
  @endforeach
  @endif
  </table>

  @if($z == $do_pages_count)
  <table cellspacing="0" cellpadding="0" border="0" width="100%" >
    <tr>
      <td style="padding: 0 5px; width: 60%;"> </td>
      <td style="padding: 0 5px;">รวม</td>
      <td style="border: 1px solid #000; padding: 0 2px; width: 12%; text-align: right;">{{number_format(@$first_total,'2','.',',')}}</td>
    </tr>
    
    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">หักส่วนลด</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">--</td>
    </tr>
    
    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">จำนวนเงินหลังหักส่วนลด</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">{{number_format(@$first_total,'2','.',',')}}</td>
    </tr>

    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">รวมมูลค่าตามใบกำกับภาษีเดิม  (1)</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;"> -- </td>
    </tr>

    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">มูลค่าที่ถูกต้อง  (2)</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;"> -- </td>
    </tr>
    
    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">ผลต่าง (1) - (2)  (3)</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;"> -- </td>
    </tr>

    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">ภาษีมูลค่าเพิ่ม 7%  (4)</td>
      @php
        $totalVat = $orderTotal - $subTotal;
      @endphp
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">
        {{number_format(preg_replace('/\.(\d{2}).*/', '.$1', (@$totalVat)),'2','.',',')}}
      </td>
    </tr>
    
    <tr>
      <td style="padding: 0 5px; width: 60%;"></td>
      <td style="padding: 0 5px;">รวม (3) + (4)</td>
      <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">{{number_format((@$first_total + @$vat_total) - @$order->discount,'2','.',',')}}</td>
    </tr> 
  </table>
  @endif
</section>


<footer style="padding-top: 10px; width: 100%; float: left; position: absolute; bottom: 3;">
  
  <table cellspacing="0" cellpadding="0" border="0" width="100%;" style="display: none;" >
    <tr>  
      <td><strong>เหตุผลในการลดหนี้</strong></td>
    </tr> 

    <tr>  
      <td><strong>ลดหนี้เนื่องจากลูกค้าคืนไวน์</strong></td>
    </tr>
  </table>

  <table cellspacing="0" cellpadding="0" border="0" width="100%;" style="margin-top: 50px;" >
    <tr>
      <td style="text-align: center;"><p style="border-bottom: 1px solid #000; text-align: center;margin-bottom: 0;"></p><br> ผู้รับเอกสาร </td>
      <td style="width: 20%;">&nbsp;</td>
      <td style="text-align: center;"><p style="border-bottom: 1px solid #000; text-align: center;margin-bottom: 0;"></p><br>ผู้รับมอบอำนาจ</td>
    </tr>
  </table>

  <span style="float: right;">
    {{$z}}/{{$do_pages_count}}
  </span>
</footer>

</div>

</body>
@endfor
</html>