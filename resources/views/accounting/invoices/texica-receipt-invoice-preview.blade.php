<!DOCTYPE html>
<html lang="en-US">
<head>
<title>Recepit</title>
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
    .custom-styles th, td{
      vertical-align: middle !important;
    }
  </style>
  @php
    use Carbon\Carbon;
  @endphp
</head>
@php
  $per_page_id1 = 0;
  $current_page = 1;
@endphp

@foreach($orders as $order)
@php
$all_orders_count = $order->order_products->count();
if($all_orders_count <= 8)
{
  $do_pages_count = ceil($all_orders_count / 8);
  $final_pages = $all_orders_count % 8;
  if($final_pages == 0)
  {
    // $do_pages_count++;
  }
}
else
{
  $do_pages_count = ceil($all_orders_count / 8);
  $final_pages = $all_orders_count % 8;
  if($final_pages == 0)
  {
    $do_pages_count++;
  }
}

$per_page_id1 = 0;
$first_total = 0;
    $vat_total = 0;
    $order_total_at_end = 0;
    $order_total_with_vat = 0;
    $orderTotal = 0;
    $subTotal = 0;
@endphp
@for($z = 1 ; $z <= $do_pages_count ; $z++)
<body>

  <div class="container" style="width: 100%; margin: 0 auto;">

    <header>
      <h1 style="text-align: center; font-size: 32px; margin: 0 !important; padding: 0; line-height: .5;">บริษัท เท็กซีก้า จำกัด (สาขา 00003)</h1>
       <h1 style="text-align: center; font-size: 32px; margin: 0 !important; padding: 0; line-height: .5">{{@Auth::user()->getCompany->company_name}}</h1>
      <p style="text-align: center; margin: 0; padding: 0; line-height: .7;">46/8 ซอย แยกซอยสันติสุข แขวงพระโขนง เขตคลองเตย กรุงเทพมหานคร 10110 <br>
      46/8  Soi Yaek Santisuk, Phrakhanong, Klongtoey, Bangkok 10110 ,THAILAND</p>
      <p style="text-align: center; margin: 0; padding: 0; line-height: .7;">Tel. 66-2-713-6034   Fax. 66-2-713-6036</p>
      <p style="text-align: center; margin: 0; padding: 0; line-height: .7;">Tax ID No.0105549032227</p>
      <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6;"><strong>ใบเสร็จรับเงิน</strong></h2>
      <h2 style="text-align: center; margin: 0; padding: 0; line-height: .6; letter-spacing: 2px;"><strong>RECEIPT</strong></h2>
    </header>

    <section>
      <table cellspacing="0" cellpadding="0" border="1" style="width: 100%;">
        <tr>
          <td style="width: 70%;">
            <table cellspacing="0" cellpadding="0" border="0">
              <tr>
                <td style="padding: 0 5px; width: 20%; font-weight: bold; line-height: 15px; padding-top: 4px;">Code :</td>
                <td style="padding: 0 5px; width: 60%; line-height: 15px; padding-top: 4px;">{{@$order->customer->reference_number}}</td>
              </tr>
              <tr>
                <td style="padding: 0 5px; width: 20%; font-weight: bold; line-height: 15px;">Name:</td>
                <td style="padding: 0 5px; width: 60%; line-height: 15px;">
                  {{@$customer->reference_name !== null ? @$customer->reference_name : '--'}}<br>
                  {{@$customer->company}}<br>
                  {{@$customerAddress->billing_address}}, {{@$customerAddress->billing_city}}, {{@$order->customer->language == 'en' ? @$customerAddress->getstate->name : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}}, {{@$order->customer->language == 'en' ? @$customerAddress->getcountry->name : (@$customerAddress->getcountry->thai_name !== null ? @$customerAddress->getcountry->thai_name : @$customerAddress->getcountry->name)}}, {{@$customerAddress->billing_zip}}<br>{{@$address->billing_phone}}
                </td>
              </tr>
              <tr>
                <td style="padding: 0 5px; width: 20%; font-weight: bold; line-height: 15px;">Tax ID :</td>
                <td style="padding: 0 5px; width: 60%; line-height: 15px;">{{$customerAddress->tax_id != null ? $customerAddress->tax_id : '--'}}</td>
              </tr>
            </table>
          </td>
          <td style="width: 30%;">
            <table cellspacing="0" cellpadding="0" border="0" style="border-left:1px; ">
              <tr>
                <td style="padding: 0 5px; width: 20%; font-weight: bold; line-height: 15px; padding-top: 4px;">เลขที่ / No. :</td>
                <!-- <td style="padding: 0 10px; width: 60%;">{{$order->memo != null ? $order->memo : '--'}}</td> -->
                @if (@$payment->auto_payment_ref_no != null)
                <td style="padding: 0 5px; width: 60%; line-height: 15px; padding-top: 4px;">{{@$payment->auto_payment_ref_no}}</td>
                @elseif($prints_checking == "REAL")
                <td style="padding: 0 5px; width: 60%; line-height: 15px; padding-top: 4px;">{{@$payment->payment_reference_no}}</td>
                @else
                <td style="padding: 0 5px; width: 60%; line-height: 15px; padding-top: 4px;">{{@$billing_ref_no->prefix}}{{@$billing_ref_no->ref_no}}</td>
                @endif
              </tr>

              <tr>
                <td style="padding: 0 5px; width: 20%; font-weight: bold; line-height: 15px;">วันที่ / Date   :</td>
                <!-- <td style="padding: 0 10px; width: 60%;">{{$order->converted_to_invoice_on !== null ? Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') : '--'}}</td>       -->

                @if($prints_checking == "REAL")
                <td style="padding: 0 5px; width: 60%; line-height: 15px;">
                  {{@$payment->received_date != null ? Carbon::parse(@$payment->received_date)->format('d/m/Y') : '--'}}
                </td>
                @else
                <td style="padding: 0 5px; width: 60%; line-height: 15px;">{{@$receipt_date !== '' ? preg_replace('/\//', '-', @$receipt_date)  : @$billing_ref_no->created_at->format('d-m-Y')}}</td>
                @endif
              </tr>
              <tr>
                <td style="padding: 0 5px; width: 20%; font-weight: bold; line-height: 15px;">อ้างถึง Invoice No. :</td>
                @php
                  if(@$order->in_status_prefix !== null)
                    {
                       $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
                    }
                  else
                    {
                      $ref_no = @$order->ref_id != null ? @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id : '';
                    }
                @endphp
                <td style="padding: 0 5px; width: 60%; line-height: 15px;">{{$ref_no}}</td>
              </tr>
            </table>
          </td>
        </tr>

      </table>

      <table class="custom-styles" cellspacing="0" cellpadding="0" border="1" style="width: 100%; margin-top: 5px;">
        <tr>
          <td style="padding: 0 2px; width: 10%; font-weight: bold; text-align: center; line-height: 0.6;">Product Code</td>
          <td style="padding: 0 2px; width: 42%; font-weight: bold; text-align: center; line-height: 0.6;">Description of goods</td>
          <td style="padding: 0 2px; width: 8%; font-weight: bold; text-align: center; line-height: 0.6;">Size.</td>
          <td style="padding: 0 2px; width: 8%; font-weight: bold; text-align: center; line-height: 0.6;">Alc.%</td>
          <td style="padding: 0 2px; width: 8%; font-weight: bold; text-align: center; line-height: 0.6;">Qty</td>
          <td style="padding: 0 2px; width: 12%; font-weight: bold; text-align: center; line-height: 0.6;">Unit Price</td>
          <td style="padding: 0 2px; width: 12%; font-weight: bold; text-align: center; line-height: 0.6;">Total</td>
        </tr>

        @if(@$order->order_products->count() > 0)
        @php
          $product_count1 = 0;
        @endphp

        @foreach($order->order_products as $result)

        @if((($order->primary_status == 3 && $result->is_retail == 'qty' && ($result->qty_shipped != 0 || $result->get_order_product_notes->count() > 0) || $result->is_retail == 'pieces' && ($result->pcs_shipped != 0 || $result->get_order_product_notes->count() > 0))) && @$result->id > @$per_page_id1)

        @if($result->id > $per_page_id1)

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
          <td style="padding: 5px 2px; width: 42%; text-align: left; line-height: 0.5;" height="40px">
            @php
              $total_str = substr($result->brand.' - '.$result->short_desc.' - '.$result->productType->title,0,95);
            @endphp
            @if(strlen($total_str) >= 95)
              {{ $total_str }} ...
            @else
              {{ $total_str }}
            @endif
          </td>
          <td style="padding: 5px 2px; width: 8%; text-align: center; line-height: 0.5;">{{ @$result->product->product_notes != NULL ? $result->product->product_notes : "--" }}</td>
          <td style="padding: 5px 2px; width: 8%; text-align: center; line-height: 0.5;">{{ @$result->product->product_temprature_c != NULL ? $result->product->product_temprature_c." %" : "--" }}</td>
          <td style="padding: 5px 2px; width: 8%; text-align: center; line-height: 0.5;">
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
            {{$unit_price != 0 ? number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$unit_price))),2,'.',',') : 0}}
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
          <td style="padding: 5px 2px; width: 12%; text-align: right; line-height: 0.5;">{{@$result->total_price != 0 ? number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$result->total_price))),2,'.',',') : 0}}</td>
        </tr>

        @php
          @$per_page_id1 = $result->id;
        @endphp

        @endif
        @endif
        @endforeach
        @endif

      </table>

      @if($z == $do_pages_count)
      <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
          <td style="padding: 0 5px; width: 70%;"> </td>
          <td style="padding: 0 5px; font-weight: bold;">รวมเป็นเงิน</td>
          <td style="border: 1px solid #000; padding: 0 2px; text-align: right; width: 12%;" >{{$first_total != 0 ? number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$first_total))),2,'.',',') : 0}}</td>
        </tr>

        <!-- <tr>
          <td style="padding: 10px; width: 70%;"> </td>
          <td style="padding: 10px;"></td>
          <td style="border: 1px solid #000; padding: 10px;"> </td>
        </tr>

        <tr>
          <td style="padding: 10px; width: 70%;"> </td>
          <td style="padding: 10px; font-weight: bold;">Total</td>
          <td style="border: 1px solid #000; padding: 10px; text-align: right;">{{$first_total != 0 ? number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$first_total))),2,'.',',') : 0}}</td>
        </tr> -->

        <tr>
          <td style="padding: 0 5px; width: 70%;"> </td>
          <td style="padding: 0 5px; font-weight: bold; text-align:">ภาษีมูลค่าเพิ่ม 7%</td>
          @php
            $totalVat = $orderTotal - $subTotal;
          @endphp
          <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">{{$totalVat != 0 ? number_format(preg_replace('/\.(\d{2}).*/', '.$1', (($totalVat))),2,'.',',') : 0}}</td>

        </tr>

        <tr>
          <td style="padding: 0 5px; width: 70%;"> </td>
          <td style="padding: 0 5px; font-weight: bold;">จำนวนเงินรวมทั้งสิ้น</td>
          <td style="border: 1px solid #000; padding: 0 2px; text-align: right;">{{number_format(preg_replace('/\.(\d{2}).*/', '.$1', ((@$order_total_with_vat))),2,'.',',')}}</td>
        </tr>
      </table>
      @endif
    </section>

    <footer style="padding-top: 50px; width: 100%; float: left; position: absolute; bottom: 3;">
      @if($z == $do_pages_count)
      <table cellspacing="0" cellpadding="0" border="0" width="100%;" style="margin-top: 5rem;">
        <tr style="width: 100%;">
          <td style="width: 50%; font-weight: bold; text-align: center;"><strong>______________________________</strong></td>
          <td style="width: 50%; font-weight: bold; text-align: center;"><strong>______________________________<strong></td>
        </tr>
        <tr style="width: 100%;">
          <td style="width: 50%; font-weight: bold; text-align: center;">ผู้รับเงิน/Receiver</td>
          <td style="width: 50%; font-weight: bold; text-align: center;">ผู้รับมอบอำนาจ/Authorized Signature </td>
        </tr>
      </table>
      @endif
      <span style="float: right;">
        <!-- {{$z}}/{{$do_pages_count}} -->
        {{$current_page}}/{{$total_pages}}
        @php $current_page++; @endphp
      </span>
    </footer>
  </div>

</body>
@endfor
@endforeach
</html>
