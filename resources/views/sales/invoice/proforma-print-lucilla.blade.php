<!DOCTYPE html>
<html>
<head>
  <title>Invoice</title>


  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/> -->
    <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/dzhitl" type="text/css"/> -->
    <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
    <!-- <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css"> -->


  <style type="text/css">
    *{
      padding: 0;
      margin: 0;
    }
    @page { size: 23cm 28cm }

    body {
   font-family: 'TH Sarabun New';
   font-weight: bold;
   font-style: normal;
   font-size: 16px;
   line-height: 1;
}

.boon
{
  font-family: BoonRegular;
  font-weight: normal;
  font-style: normal;
}


 body {
  color: black;
 }
 /*Italic characters*/
       /* body {
             font-family: 'DizhitlRegular';
    font-weight: bold;
    font-style: italic;
        }*/
       /* body{
           font-family: 'THSarabunNewBold';
    font-weight: bold;
    font-style: normal;
        }*/

  </style>

  @php

    use Carbon\Carbon;
    use App\Models\Common\Order\Order;
  @endphp
</head>
@foreach($orders_array as $id)
@php
      $data = Order::getDataForProforma($id,$default_sort,'lucilla');
      $order = $data['order'];
      $address = $data['address'];
      $customerAddress = $data['customerAddress'];
      $customerShippingAddress = $data['customerShippingAddress'];
      $query = $data['query'];
      $vat_count = $data['vat_count'];
      $vat_count_notes = $data['vat_count_notes'];
      $arr = $data['arr'];
      $query_count = $data['query_count'];
      $query2 = $data['query2'];
      $non_vat_count = $data['non_vat_count'];
      $query2_notes = $data['query2_notes'];
      $query2_discounts = $data['query2_discounts'];
      $inv_note = $data['inv_note'];
      $query_count2 = $data['query_count2'];
      $query_count = $data['query_count'];
      $all_orders_count = $data['all_orders_count'];
      $do_pages_count = $data['do_pages_count'];
      $first_total = 0;
      $vat_total = 0;
      $non_vat_total = 0;
      $vat_items_total = 0;
      $all_product_total = 0;
      $vat = 0;
      $sr_no = 1;

      $item_level_discount = 0;
      $sub_total_without_discount = 0;
      $non_vat_total_after_discount = 0;
      $vat_items_total_after_discount = 0;
@endphp

@foreach($query as $result)
    @php
        if ($result->discount != 0) {
            if ($result->discount == 100) {
                if ($result->is_retail == 'pieces') {
                    $discount_full =  $result->unit_price_with_vat * $result->pcs_shipped;
                    $sub_total_without_discount += $discount_full;
                } else {
                    $discount_full =  $result->unit_price_with_vat * $result->qty_shipped;
                    $sub_total_without_discount += $discount_full;
                }
                $item_level_discount += $discount_full;
            } else {
                $sub_total_without_discount += $result->total_price / ((100 - $result->discount) / 100);
                $item_level_discount += ($result->total_price / ((100 - $result->discount) / 100)) - $result->total_price;
            }
        }
        $qty_to_multiply = $result->is_retail == 'qty' ? $result->qty_shipped : $result->pcs_shipped;

        $non_vat_total += $result->vat == null ? ($result->discount == null ? $result->total_price : $result->unit_price * $qty_to_multiply) : 0;
        $non_vat_total_after_discount += $result->vat == null ? $result->total_price : 0;

        $vat_items_total += $result->vat != null ? ($result->discount == null ? $result->total_price : $result->unit_price * $qty_to_multiply) : 0;
        $vat_items_total_after_discount += $result->vat != null ? $result->total_price : 0;

        $vat = @$result->total_price_with_vat-@$result->total_price;
        $first_total = $first_total + @$result->total_price;
        $vat_total = $vat_total + ($result->vat_amount_total != null ? round($result->vat_amount_total,4) : $vat);
    @endphp
@endforeach
@for($z = 1 ; $z <= $do_pages_count ; $z++)
<body>
  <div style="width: 20.1cm">
  <!-- header -->
  <div style="border: 1px solid white;height: 5.1cm;">
    <span style="float: right;margin-top: 68.031496063px;margin-right: -10px;">{{@$order->full_inv_no}}</span>
  </div>
  <!-- customer section -->
  <div>
    <table width="100%" style="margin: 0;border-collapse: collapse;overflow-y: hidden;">
      <tr>
        <td width="336.61417323px" height="132.28346457px" style="border: 1px solid white;padding: 0;padding-left: 50px;margin: 0;max-height:132.28346457px;height: 132.28346457px; " align="left" valign="top">
          <div style="max-height:132.28346457px;height: 132.28346457px;padding-left: 40px;line-height: 0.8;">
            <div class="d-inline">
                <span style="padding-left: 60px;">{{@$order->customer->company}}
                </span>
                <span style="" align="right">{{@$customerAddress->title}}</span>

            </div>
<!--            @if(@$customerAddress->title !== 'Default Address' && @$customerAddress->show_title != 0)
           {{@$customerAddress->title}}<br>
           @endif -->
           <br>
           {{@$customerAddress->billing_address != null ? @$customerAddress->billing_address : ''}},&nbsp;
           {{@$customerAddress->billing_city != null ? @$customerAddress->billing_city : ''}},&nbsp;
           {{@$order->customer->language == 'en' ? (@$customerAddress->billing_state != null ? @$customerAddress->getstate->name : '') : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name : @$customerAddress->getstate->name)}},
            &nbsp;

            <!-- {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name.',' : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' : ''))}}  -->
            {{@$customerAddress->billing_zip}} <br>
           TAX ID : {{@$customerAddress->tax_id}}
           </div>
        </td>
        <td width="18.677165354px" style="border: 1px solid white;padding: 0;max-height:132.28346457px;height: 132.28346457px;color:white;">abc</td>
        <td width="334.17322835px" style="border: 1px solid white;padding: 0;max-height:132.28346457px;height: 132.28346457px;">
          <div style="max-height:132.28346457px;height: 132.28346457px;padding-left: 100px;line-height: 0.8;">
            <div style="height: 80px;">
                <div class="d-inline">
                    <span style="padding-left: 10px;">{{@$order->customer->reference_name}}</span>
                    <span style="white-space: nowrap;" align="right">{{@$customerShippingAddress->title}}</span>
                </div>
                <br>
           <!-- @if(@$customerShippingAddress->title !== 'Default Address' && @$customerShippingAddress->show_title != 0)
            {{@$customerShippingAddress->title}}<br>
            @endif -->
            {{@$customerShippingAddress->billing_address != null ? @$customerShippingAddress->billing_address.',' : ''}},&nbsp;
            <br>

            {{@$customerShippingAddress->billing_city != null ? @$customerShippingAddress->billing_city : ''}},&nbsp;

            {{@$order->customer->language == 'en' ? (@$customerShippingAddress->billing_state != null ? @$customerShippingAddress->getstate->name : '') : (@$customerShippingAddress->getstate->thai_name !== null ? @$customerShippingAddress->getstate->thai_name : @$customerShippingAddress->getstate->name)}},
            &nbsp;
            {{@$customerShippingAddress->billing_zip}}



            <!-- {{@$order->customer->language == 'en' ? (@$customerShippingAddress->getcountry->name !== 'Thailand' ? @$customerShippingAddress->getcountry->name.',' :'') : (@$customerShippingAddress->getcountry->thai_name !== null ? (@$customerShippingAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerShippingAddress->getcountry->thai_name.',' : '') : (@$customerShippingAddress->getcountry->name !== 'Thailand' ? @$customerShippingAddress->getcountry->name.',' : ''))}} -->
            </div>
            <div>
              <span style="padding-left: 75px">{{@$order->customer->primary_contact->name}} {{@$order->customer->primary_contact->sur_name}}</span><br>
              <span style="padding-left: 75px">{{@$order->customer->primary_contact->telehone_number}}</span>
            </div>
           </div>
        </td>
      </tr>
    </table>
  </div>
  <!-- due date ref po # section -->
  <div style="margin-top: 32px;padding-left: 0.5cm;">
    <table style="width: 100%;border-collapse: collapse;">
      <tr>
        <td width="3.3cm" align="center" style="border: 1px solid white;">
          <span style="padding-left: 0.8cm;">
          {{@$order->customer->reference_number}}
          </span>
        </td>
        <td width="3.3cm" align="" style="border: 1px solid white;position: relative;">
          <span style="position: absolute;width: 150%;left: 1.4cm;">{{@$order->memo}}</span>

        </td>
        <td width="3.3cm" align="right" style="border: 1px solid white;">7.0</td>
        <td width="3.3cm" align="center" style="border: 1px solid white;">
          <span style="padding-left: 1.3cm;">
          {{ Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') }}
          </span>
        </td>
        <td width="3.3cm" align="center" style="border: 1px solid white;">
          <span style="padding-left: 1.1cm;">
          {{ @$order->payment_due_date != null ? Carbon::parse(@$order->payment_due_date)->format('d/m/Y') : '--' }}
          </span>
        </td>
        <td width="3.3cm" align="center" style="border: 1px solid white;color:white;">f</td>
      </tr>
    </table>
  </div>
  <!-- table body -->
  <div style="margin-top: 38px;height: 321.25984252px;max-height: 321.25984252px;overflow: hidden;border: 1px solid white;width: 22cm">
    <table style="width: 100%;border-collapse: collapse;">
      @php
        $product_count1 = 0;
        $result_ids[] = [];
      @endphp

      @foreach($query as $result)

        @if(($result->is_retail == 'qty' && ($result->qty_shipped != 0 || $result->get_order_product_notes->count() > 0) || $result->is_retail == 'pieces' && ($result->pcs_shipped != 0 || $result->get_order_product_notes->count() > 0)) && !in_array($result->id, $result_ids))
      @php $product_count1++;
        if($product_count1 > 12)
        {
          break;
        }
      @endphp
      <tr>
        <td style="border: 1px solid white;padding-left: 1cm;" width="1cm" align="center">
          {{-- {{@$loop->iteration}} --}}
          {{ $sr_no++ }}
        </td>
        <td style="border: 1px solid white;position: relative;" width="2.6cm" align="left">
          <span style="padding-left: 0.1cm;position: absolute;left: 0;width: 150%">
          {{ @$result->product->refrence_code }}
          </span>
        </td>
        <td style="border: 1px solid white;" width="7.8cm" align="left">
          <span style="padding-left: 0.5cm;">{{ @$result->short_desc }}</span>
        </td>
        <td style="border: 1px solid white;" width="2.6cm" align="center">
          <span style="padding-left: 0.4cm;">
          @if(@$result->is_retail == 'qty')
          {{ @$result->qty_shipped != null ? @$result->qty_shipped : 0 }}
           @else
          {{ @$result->pcs_shipped != null ? @$result->pcs_shipped : 0 }}
          @endif
          @if(@$result->is_retail == 'qty')
            {{@$result->product && @$result->product->sellingUnits ? @$result->product->sellingUnits->title : "N.A"}}
          @else
            pc
          @endif
          </span>
        </td>
        <td style="border: 1px solid white;" width="2.1cm" align="right">
          <span style="padding-left: 0.5cm;">
          {{number_format(@$result->unit_price, 2, '.', ',')}}
          </span>
        </td>
        <td style="border: 1px solid white;" width="1cm" align="center">
          <span style="padding-left: 0.1cm;white-space: nowrap;">
          {{$result->discount != null && $result->discount != 0 ? $result->discount .'%' : ''}}
          </span>
        </td>

        <td style="border: 1px solid white;position: relative;z-index: 1;" width="4.8cm" align="right">
          <span style="margin-right: 100px;z-index: 1000;">
          {{number_format(@$result->total_price,2,'.',',')}}
          </span>
        </td>
      </tr>
      @if($result->get_order_product_notes->where('show_on_invoice',1)->count() > 0)
      @php $product_count1++;$index = 1; @endphp
      <tr>
        <td></td>
        <td style="border: 1px solid white;height: 15pt;position: relative;" width="2.6cm" align="left">

        </td>
        <td colspan="5">
        <span style="padding-left: 0.5cm;">
          Note
        </span>
        <span style="padding-left: 0cm;">
        @foreach(@$result->get_order_product_notes->where('show_on_invoice',1) as $note)
        {{@$note->note.' '}}
        </span>
        @endforeach
        </td>
      </tr>
      @endif
      @php
        array_push($result_ids, $result->id);
      @endphp
      @endif
      @endforeach
    </table>
  </div>

  <!-- show footer only on last page -->
  @if($z == $do_pages_count)
  <!-- Remark section -->
  <div style="overflow: hidden;width: 22cm;margin-top: 0px;">
    <table style="width: 100%;border-collapse: collapse;">
      <tr>
        <td valign="middle" align="left" width="430.866141736px" height="90.708661417px" style="border: 1px solid white;padding-left: 1.4cm;">
          <span>{{@$inv_note->note}}</span>
        </td>
        <td valign="bottom" align="right" width="215.433070866px" style="border: 1px solid white;">
          <span style="padding-right: 40px;">
          {{@$vat_total != 0 ? '7.0' : ''}}
          </span>
        </td>
        <td valign="top" align="right" style="border: 1px solid white;position: relative;" width="131px">
          <span style="position: absolute;padding-left: 20px;display: block;">
          {{number_format(floor($item_level_discount*100)/100, 2, '.', ',')}}<br>
          {{number_format($non_vat_total_after_discount, 2, '.',',')}}<br>
          {{number_format($vat_items_total_after_discount, 2, '.',',')}}<br>
          {{number_format($vat_total, 2, '.',',')}}
          </span>
        </td>
      </tr>
    </table>
  </div>
  <!-- total amount section -->
  <div style="overflow: hidden;width: 21cm">
    <table style="width: 100%;border-collapse: collapse;">
      <tr>
        @php
            $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            $total_final = round($non_vat_total_after_discount,2) + round($vat_items_total_after_discount,2) + round($vat_total,2);
            $total = number_format($total_final,'2','.','');

            $total_split = explode('.',$total);
            $word1 = $f->format($total_split[0]);
            $word2 = $f->format($total_split[1]);

            $word = $word1.' and '.$word2.' Satang';
        @endphp
        <td valign="top" align="center" width="460.866141736px" height="37.795275591px" style="border: 1px solid white;text-transform: uppercase;">
          <span style="padding-left: 45px;margin-top: 12px;">
            {{$word}}
          </span>
        </td>
        <td valign="top" width="215.433070866px" style="border: 1px solid white;color:white">abc</td>
        <td valign="middle" align="right" style="border: 1px solid white;" width="143px">
          <span style="margin-right: 53px;margin-top: 10px;">
          {{number_format($total, 2, '.',',')}}
          </span>
        </td>
      </tr>
    </table>
  </div>
  @endif

  @if($z !== intval(@$do_pages_count))
          </div>
        </body>
        @endif
            @endfor
  {{-- </div>
</body> --}}
@endforeach
</html>
