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
      $data = Order::getDataForProforma($id,$default_sort);
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
      $all_product_total = 0;
@endphp
@if($query->count() > 0)
  @php $per_page_id1 = 0; @endphp
  @for($i = 0; $i < @$query_count ; $i++)
<body>
<div style="border: none; height: 94.666667px;max-height: 94.666667px;margin: 0 auto;margin-top: 149.6pt;width: 518pt;font-size: 12px">
 <table>
   <tr>
     <td style="width: 338.4pt; border:none;height: 94.666667px;">
       <table style="font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;font-size: 12px;line-height: 0.6;">
         <tr>
           <td style="width: 113pt;height: 60px;visibility: hidden;" valign="top">>Customer Name, Address</td>
           <td style="width: 245.4pt;position: relative;" valign="top"> <span style="position: absolute;top: -5px;left: -18pt;"><span style="font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 90%;">{{@$order->customer->company}}<br>
   @if(@$customerAddress->title !== 'Default Address' && @$customerAddress->show_title != 0)
   {{@$customerAddress->title}}<br>
   @endif
   {{@$customerAddress->billing_address != null ? @$customerAddress->billing_address.',' : ''}} {{@$customerAddress->billing_city != null ? @$customerAddress->billing_city.',' : ''}} {{@$order->customer->language == 'en' ? (@$customerAddress->billing_state != null ? @$customerAddress->getstate->name.',' : '') : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name.',' : @$customerAddress->getstate->name.',')}} {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name.',' : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' : ''))}} {{@$customerAddress->billing_zip}}<br>{{@$customerAddress->billing_phone}}</span></span></td>
         </tr>
          <tr style="padding-bottom: 8px;">
           <td style="width: 113pt;height: 26.666667px;visibility: hidden;" valign="bottom">Tax ID</td>
           <td style="width: 225.4pt;border: none;position: relative;"><span style="position: absolute;top: 32px;left: -18pt;">{{$customerAddress->tax_id}}</span></td>
         </tr>
       </table>
     </td>
     <td style="width: 179.6pt; border: none">
       <table>
         <tr>
           <td style="width: 95pt;height: 43px;visibility: hidden;" valign="middle">Document No.</td>
           @php
            if(@$order->primary_status == 3)
            {
              if(@$order->in_status_prefix !== null)
              {
                $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
              }
              else
              {
                $ref_no = @$order->ref_id != null ? @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id : '';
              }
            }
            else  
            {
              if(@$order->status_prefix !== null)
              {
                $ref_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              }
              else
              {
                $ref_no = @$order->ref_id != null ? @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id : '';
              }
            }
          @endphp
           @if(@$order->memo != null)
           <td style="width: 65pt;border: none;position: relative;" valign="top" align="right"><span style="position: absolute;top: 4pt;font-size: 94vh;width: 100px !important;">
             {{@$ref_no}}-1<br>{{@$order->memo}}</span></td>
           @else
           <td style="width: 65pt;border: none;position: relative;" valign="middle" align="right"><span style="position: absolute;top: 6pt;font-size: 18px">
             {{@$ref_no}}-1<br>{{@$order->memo}}</span></td>
           @endif
         </tr>
          <tr style="padding-bottom: 8px;">
           <td style="width: 95pt;height: 43px;visibility: hidden;" valign="middle" >Date</td>
           <td style="width: 65pt;border:none;" valign="bottom" align="right"><span style="position: relative;"><span style="position: absolute;top: 34px;font-size: 18px;">{{@$order->delivery_request_date != null ? Carbon::parse(@$order->delivery_request_date)->format('d/m/Y') : '--'}}</span></span></td>
         </tr>
       </table>
     </td>
     <td></td>
   </tr>
 </table>
</div>
<div style="border: none;margin: 0 auto;margin-top: 48.8pt;width: 528.6pt;height:338.4pt;font-size: 12px;position: relative;">
  <table style="font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;font-size: 12px;line-height: 0.8;">
    @php 
    $product_count1 = 0; 
    $result_ids[] = [];
    @endphp
    @foreach($query as $result)
     @if(($result->is_retail == 'qty' && ($result->qty_shipped != 0 || $result->get_order_product_notes->count() > 0) || $result->is_retail == 'pieces' && ($result->pcs_shipped != 0 || $result->get_order_product_notes->count() > 0)) && !in_array($result->id, $result_ids))
      @php $product_count1++; 
          if($product_count1 > 10)
          {
            break;
          }
     @endphp
                <tr>
                  <td align="left" width="53.2pt" style="position: relative;"> <span style="position: absolute;left: 2px;top: 1pt;">{{ @$result->product->refrence_code }}</span></td>
                  <td align="left" valign="top" width="252pt">{{ @$result->short_desc }} @if(@$result->discount != null && @$result->discount > 0) <br>Discount {{@$result->discount}} % @endif</td>
                  <td align="right" width="36pt" style="position: relative;"><span style="position: absolute;top: 0pt;left: 45px;">
                     @if(@$result->is_retail == 'qty')
                    {{ @$result->qty_shipped != null ? @$result->qty_shipped : 0 }}
                     @else 
                    {{ @$result->pcs_shipped != null ? @$result->pcs_shipped : 0 }}
                    @endif
                  </span></td>
                  <td align="right" width="46pt" valign="top" style="position: relative;"><span style="position: absolute;right: -9pt;">
                    @if(@$result->is_retail == 'qty')
                    {{@$result->product && @$result->product->sellingUnits ? @$result->product->sellingUnits->title : "N.A"}}
                    @else
                    pc
                    @endif
                  </span></td>
                  <td align="right" width="63.4pt" valign="top">
                  {{number_format(@$result->unit_price, 2, '.', ',')}}
                  @if($result->discount != null && $result->discount > 0)
                  <br>
                    -{{number_format(@$result->unit_price * ($result->discount / 100), 2, '.', ',')}}
                  @endif
                  </td>
                  @php $vat = round(@$result->total_price_with_vat-@$result->total_price,2);
                        $first_total = $first_total + @$result->total_price;
                        $vat_total = $vat_total + ($result->vat_amount_total != null ? round($result->vat_amount_total,4) : $vat);
                        $all_product_total += round($result->total_price_with_vat,2); 
                   @endphp
                  <td align="right" width="71.2pt">{{number_format(@$result->total_price,2,'.',',')}} 
                  </td>
            
              
                </tr>
                 @if($result->get_order_product_notes->where('show_on_invoice',1)->count() > 0)
                @php $product_count1++; @endphp
                <tr>
                  <td colspan="1" align="center" style="height: 15pt;position: relative;"><span style="position: absolute;left: -10px;top: 1pt;">Note</span></td>
                  <td colspan="5">
              
                  @foreach(@$result->get_order_product_notes->where('show_on_invoice',1) as $note)
                  {{@$note->note.' '}}
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

<p style="position: absolute;left: 20pt;bottom: 135pt;width: 250pt"> 
  <span style="display: flex;width: 80px;visibility: hidden;"> {{$global_terminologies['comment_to_customer'] }} : </span><span style="font-size: 16px;width:190pt;display: inline-block;line-height: 0.5;padding-bottom: 5px;">{{$global_terminologies['comment_to_customer'] }} : {{@$order->order_notes()->where('type','customer')->pluck('note')->first()}}</span>
  <span style="display: flex;width: 80px;visibility: hidden;">Bank Detail: </span><span style="display: inline-block;font-size: 16px;line-height: 0.6;">Account Name: {{@$bank->title}}<br>Account No.: {{@$bank->account_no}}<br>Bank: {{@$bank->description}}<br>Branch: {{@$bank->branch}}</span>
</p>
</div>

<div style="margin: 0 auto;margin-top:29.6pt;width: 508.6pt;height:124pt;max-height:124pt;font-size: 12px">
<table style="max-height: 124pt;">
  <tr>
    <td width="33.2pt" style="position: relative;font-size: 10px;" class="boon"><span style="position: absolute;left: -27px;">Delivered to :</span></td>
    <td width="220pt" align="" style="position: relative;"><span style="position: absolute;left: 0px;top:5px;font-family: 'BoonRegular';
    font-weight: normal;
    font-style: normal;line-height: 0.6;height: 25pt">
    <span style="font-size: 10px">
     @if(@$customerShippingAddress->title !== 'Default Address' && @$customerShippingAddress->show_title != 0)
    {{@$customerShippingAddress->title}}<br>
    @endif
    {{@$customerShippingAddress->billing_address != null ? @$customerShippingAddress->billing_address.',' : ''}} {{@$customerShippingAddress->billing_city != null ? @$customerShippingAddress->billing_city.',' : ''}} {{@$order->customer->language == 'en' ? (@$customerShippingAddress->billing_state != null ? @$customerShippingAddress->getstate->name.',' : '') : (@$customerShippingAddress->getstate->thai_name !== null ? @$customerShippingAddress->getstate->thai_name.',' : @$customerShippingAddress->getstate->name.',')}} {{@$order->customer->language == 'en' ? (@$customerShippingAddress->getcountry->name !== 'Thailand' ? @$customerShippingAddress->getcountry->name.',' :'') : (@$customerShippingAddress->getcountry->thai_name !== null ? (@$customerShippingAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerShippingAddress->getcountry->thai_name.',' : '') : (@$customerShippingAddress->getcountry->name !== 'Thailand' ? @$customerShippingAddress->getcountry->name.',' : ''))}} {{@$customerShippingAddress->billing_zip}}</span></span></td>
    <td width="35pt" style="font-size: 10px;" class="boon">Due Date</td>
    <td width="77pt" style="font-size: 10px" class="boon">{{@$order->payment_due_date != null ? Carbon::parse(@$order->payment_due_date)->format('d/m/Y') : '--'}}</td>
    <td width="30.4pt" align="right" style="visibility: hidden;" >Sub Total</td>
    @if($i == intval($query_count-1))
    <td width="91.2pt" align="right" valign="center" style="font-size: 12px;font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;">THB {{@$query->sum('total_price') != 0 ? number_format(@$query->sum('total_price'),2,'.',',') : 0}}</td>
    @endif
  </tr>

   <tr >
    <td width="33.2pt" height="38pt" class="boon" style="font-size: 10px;"><span style="position: relative;"><span style="position: absolute;top: 10px;left: -27px;">Sales Contact :</span></span></td>
    <td width="220pt" align="center"><span style="position: relative;"><span style="position: absolute;top: 10px;left: 0px;font-family: 'BoonRegular';
    font-weight: normal;
    font-style: normal;font-size: 10px;line-height: 1;">{{@$order->user->name}}</span></span></td>
    <td width="45pt"><span style="position: relative;font-size: 10px" class="boon"><span style="position: absolute;top: 10px;">Phone No. : </td>
    <td width="77pt" style="position: relative;font-size: 10px" class="boon"><span style="position: absolute;top: 10px;">{{@$order->user->phone_number}}</span></td>
    <td width="30.4pt" align="right" style="visibility: hidden;">Vat</td>
    @if($i == intval($query_count-1))
    <td width="61.2pt" align="right" valign="middle" style="font-size: 12px;font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;">THB {{ $vat_total != 0 ? number_format($vat_total,2,'.',',') : 0}}</td>
    @endif
  </tr>

   <tr style="border: 1px solid red;">
    <td colspan="4" width="" height="28.8pt" style="text-transform: capitalize;">
       @php 
            $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            $total_final = round($query->sum('total_price'),2) + round($vat_total,2);
            $total = number_format($total_final - @$order->discount,'2','.','');
          
            $total_split = explode('.',$total);
            $word1 = $f->format($total_split[0]);
            $word2 = $f->format($total_split[1]);

            $word = $word1.' and '.$word2.' Satang';
           @endphp
          <span style="position: relative;"><span style="position: absolute;top: 11px;text-transform: uppercase;font-size: 12px;"> {{@$word}} </span></span>
    </td>
    <!-- <td width="36"></td> -->
    <td width="30.4pt" align="right" style="visibility: hidden;">Total Amount</td>
    @if($i == intval($query_count-1))
    <td width="61.2pt" align="right" valign="middle" style="font-size: 12px;font-family: 'DizhitlRegular';
    font-weight: normal;

    font-style: normal;">THB {{number_format($total_final - @$order->discount,'2','.',',')}}</td>

    @endif

  </tr>

   <tr>
    <td width="53.2pt" height="28.8pt" valign="middle" colspan="2" class="boon" style="position: relative;"><span style="position: absolute;left: -25px;top: 7px">Ref.Name: &nbsp;&nbsp;&nbsp;{{@$order->customer->reference_name}}&nbsp;&nbsp;&nbsp;<span style="position:absolute;right: 80px;">Return Policy :</span></span></td>
    <!-- <td width="36"></td> -->
    <td colspan="4" valign="bottom" style="position: relative;"> <span style="position: absolute;top: 5pt;left: -80px"><p style="font-size: 13px;margin: 0px;">Returns could be accepcted with in 24 hours of delivery only.</p><p style="margin: 0;font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;position: absolute;top:10pt;">การคืนสินค้าทางบริษัทจะยอมรับก็ต่อเมื่อมีการคืนในวันที่ทำการส่งสินค้าเท่านั้น</p></span>

              
</td>
   
  </tr>
</table>
<footer style="">
  @php 
    if($query2->count() > 0)
    {
      $count1 = $query_count2;
    }
    else
    {
      $count1 = 0;
    }
  @endphp
  <span style="float: right;">Page {{$i+1}} / {{@$query_count + @$count1}}</span>
</footer>
</div>
</body>
@endfor
@endif
  @if(@$query2->count() > 0)
  @php $per_page_id = 0;
      $all_product_total = 0;
   @endphp
  @for($i = 0; $i < @$query_count2 ; $i++)
  <style type="text/css">
    body {
      color:black;
    }
  </style>
  @php 
    $first_total = 0;
     $vat_total = 0;
  @endphp
  <body>
<div style="border: none; height: 94.666667px;max-height: 94.666667px;margin: 0 auto;margin-top: 149.6pt;width: 518pt;font-size: 12px">
 <table>
   <tr>
     <td style="width: 338.4pt; border:none;height: 94.666667px;">
       <table style="font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;font-size: 12px;line-height: 0.6;">
         <tr>
           <td style="width: 113pt;height: 60px;visibility: hidden;" valign="top">>Customer Name, Address</td>
           <td style="width: 245.4pt;position: relative;" valign="top"><span style="position: absolute;top: -5px;left: -18pt;"><span style="font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 90%;">{{$order->customer->company}}<br>

    @if(@$customerAddress->title !== 'Default Address' && @$customerAddress->show_title != 0)

   {{@$customerAddress->title}}<br>
   @endif
   {{@$customerAddress->billing_address != null ? @$customerAddress->billing_address.',' : ''}} {{@$customerAddress->billing_city != null ? @$customerAddress->billing_city.',' : ''}} {{@$order->customer->language == 'en' ? (@$customerAddress->billing_state != null ? @$customerAddress->getstate->name.',' : '') : (@$customerAddress->getstate->thai_name !== null ? @$customerAddress->getstate->thai_name.',' : @$customerAddress->getstate->name.',')}} {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name.',' : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name.',' : ''))}} {{@$customerAddress->billing_zip}}<br>{{@$customerAddress->billing_phone}}</span></span></td>
         </tr>
          <tr style="padding-bottom: 8px;">
           <td style="width: 113pt;height: 26.666667px;visibility: hidden;" valign="bottom">Tax ID</td>
           <td style="width: 225.4pt;border: none;position: relative;"><span style="position: absolute;top: 32px;left: -18pt;">{{$customerAddress->tax_id}}</span></td>
         </tr>
       </table>
     </td>
     <td style="width: 179.6pt; border: none">
       <table>
         <tr>
          <td style="width: 95pt;height: 43px;visibility: hidden;" valign="middle">Document No.</td>
          @php
            if(@$order->primary_status == 3)
            {
              if(@$order->in_status_prefix !== null)
              {
                $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
              }
              else
              {
                $ref_no = @$order->ref_id != null ? @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id : '';
              }
            }
            else  
            {
              if(@$order->status_prefix !== null)
              {
                $ref_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              }
              else
              {
                $ref_no = @$order->ref_id != null ? @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id : '';
              }
            }
          @endphp
          @if(@$order->memo != null)
           <td style="width: 65pt;border: none;position: relative;" valign="top" align="right"><span style="position: absolute;top: 4pt;font-size: 94vh;width: 100px !important;">
            {{$ref_no}}-2<br>{{@$order->memo}}</span></td>
          @else
           <td style="width: 65pt;border: none;position: relative;" valign="middle" align="right"><span style="position: absolute;top: 6pt;font-size: 18px;">
            {{$ref_no}}-2<br>{{@$order->memo}}</span></td>
          @endif
         </tr>
          <tr style="padding-bottom: 8px;">
           <td style="width: 95pt;height: 43px;visibility: hidden;" valign="middle" >Date</td>
           <td style="width: 65pt;border:none;" valign="bottom" align="right"><span style="position: relative;"><span style="position: absolute;top: 34px;font-size: 18px">{{@$order->delivery_request_date != null ? Carbon::parse(@$order->delivery_request_date)->format('d/m/Y') : '--'}}</span></span></td>
         </tr>
       </table>
     </td>
     <td></td>
   </tr>
 </table>
</div>
<div style="border: none;margin: 0 auto;margin-top: 48.8pt;width: 528.6pt;height:338.4pt;font-size: 12px;position: relative;">
  <table style="font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;font-size: 12px;line-height: 0.8;">
   @php $product_count = 0;
    $per_page_id1 = 0;
    $result_ids[] = [];
    @endphp
    @foreach($query2 as $result)

     @if((($result->is_retail == 'qty' && ($result->qty_shipped != 0 || $result->get_order_product_notes->count() > 0)) || ($result->is_retail == 'pieces' && ($result->pcs_shipped != 0 || $result->get_order_product_notes->count() > 0))) && !in_array($result->id, $result_ids))
     @php $product_count++; 
          if($product_count > 16)
          {
            break;
          }
     @endphp
                <tr>

                  <td align="left" width="53.2pt" style="position: relative;"> <span style="position: absolute;left: 2px;top: 1pt;">{{ @$result->product->refrence_code }}</span></td>
                  <td align="left" valign="top" width="252pt">{{ @$result->short_desc }} @if(@$result->discount != null && @$result->discount > 0) <br>Discount {{@$result->discount}} % @endif @if(@$result->vat != null) <br> VAT {{@$result->vat}} % @endif</td>
                  <td align="right" width="36pt" style="position: relative;"><span style="position: absolute;top: 0pt;left: 45px;">
                      @if(@$result->is_retail == 'qty')
                    {{ @$result->qty_shipped != null ? @$result->qty_shipped : 0 }}
                     @else 
                    {{ @$result->pcs_shipped != null ? @$result->pcs_shipped : 0 }}
                    @endif
                  </span></td>
                  <td align="right" width="46pt" valign="top" style="position: relative;"><span style="position: absolute;right: -9pt;">
                    @if(@$result->is_retail == 'qty')
                    {{@$result->product && @$result->product->sellingUnits ? @$result->product->sellingUnits->title : "N.A"}}
                    @else
                    pc
                    @endif
                  </span></td>
                  <td align="right" width="63.4pt" valign="top">
                  {{number_format(@$result->unit_price, 2, '.', ',')}}
                   @if($result->discount != null && $result->discount > 0)
                   <br>
                    -{{number_format(@$result->unit_price * ($result->discount/100), 2, '.', ',')}}
                    @php $product_count++; @endphp
                  @endif
                  </td>
                  @php $vat = round(@$result->total_price_with_vat-@$result->total_price,2);
                        $first_total = $first_total + @$result->total_price;
                        $vat_total = $vat_total + $vat;
                        $all_product_total += round($result->total_price_with_vat,2); 
                   @endphp
                  <td align="right" valign="top" width="71.2pt">{{number_format(@$result->total_price,2,'.',',')}} @if(@$result->vat != null) <br> {{number_format(@$vat, 2, '.', ',')}} @endif</td>
            
              
                </tr>
                 @if($result->get_order_product_notes->where('show_on_invoice',1)->count() > 0)
                @php $product_count++; @endphp
                <tr>
                  <td colspan="1" align="center" style="height: 15pt;position: relative;"><span style="position: absolute;left: -10px;top: 1pt;">Note</span></td>
                  <td colspan="5">
              
                  @foreach(@$result->get_order_product_notes->where('show_on_invoice',1) as $note)
                  {{@$note->note.' '}}
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
<!-- <p style="position: absolute;left: 20pt;bottom: 130pt;width: 250pt">
  <span style="display: flex;width: 80px;visibility: hidden;">Bank Detail: </span><span style="display: inline-block;font-size: 16px;">Account Name: {{@$bank->title}}<br>Account No.: {{@$bank->account_no}}<br>Bank: {{@$bank->description}}<br>Branch: {{@$bank->branch}}</span>
</p> -->

<p style="position: absolute;left: 20pt;bottom: 135pt;width: 250pt">
  <span style="display: flex;width: 80px;visibility: hidden;">Return Policy : </span><span style="font-size: 16px;width:190pt;display: inline-block;line-height: 0.5;padding-bottom: 5px;">{{$global_terminologies['comment_to_customer'] }} : {{@$order->order_notes()->where('type','customer')->pluck('note')->first()}}</span>
  <span style="display: flex;width: 80px;visibility: hidden;">Bank Detail: </span><span style="display: inline-block;font-size: 16px;line-height: 0.6;">Account Name: {{@$bank->title}}<br>Account No.: {{@$bank->account_no}}<br>Bank: {{@$bank->description}}<br>Branch: {{@$bank->branch}}</span>
</p>
</div>

<div style="border:none;margin: 0 auto;margin-top:29.6pt;width: 508.6pt;height:124pt;max-height:124pt;font-size: 12px">
<table style="max-height: 124pt;">
   <tr>
    <td width="33.2pt" style="position: relative;font-size: 10px;" class="boon"><span style="position: absolute;left: -27px;">Delivered to :</span></td>
    <td width="220pt" align="" style="position: relative;"><span style="position: absolute;left: 0px;top:5px;font-family: 'BoonRegular';
    font-weight: normal;
    font-style: normal;line-height: 0.6;height: 25pt;">
    <span style="font-size: 10px;">
    @if(@$customerShippingAddress->title !== 'Default Address' && @$customerShippingAddress->show_title != 0)
    {{@$customerShippingAddress->title}}<br>
    @endif
    {{@$customerShippingAddress->billing_address != null ? @$customerShippingAddress->billing_address.',' : ''}} {{@$customerShippingAddress->billing_city != null ? @$customerShippingAddress->billing_city.',' : ''}} {{@$order->customer->language == 'en' ? (@$customerShippingAddress->billing_state != null ? @$customerShippingAddress->getstate->name.',' : '') : (@$customerShippingAddress->getstate->thai_name !== null ? @$customerShippingAddress->getstate->thai_name.',' : @$customerShippingAddress->getstate->name.',')}} {{@$order->customer->language == 'en' ? (@$customerShippingAddress->getcountry->name !== 'Thailand' ? @$customerShippingAddress->getcountry->name.',' :'') : (@$customerShippingAddress->getcountry->thai_name !== null ? (@$customerShippingAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerShippingAddress->getcountry->thai_name.',' : '') : (@$customerShippingAddress->getcountry->name !== 'Thailand' ? @$customerShippingAddress->getcountry->name.',' : ''))}} {{@$customerShippingAddress->billing_zip}}</span></span></td>
    <td width="35pt" style="font-size: 10px" class="boon">Due Date</td>
    <td width="77pt" style="font-size: 10px" class="boon">{{@$order->payment_due_date != null ? Carbon::parse(@$order->payment_due_date)->format('d/m/Y') : '--'}}</td>
    <td width="30.4pt" align="right" style="visibility: hidden;">Sub Total</td>
    @if($i == intval($query_count2 - 1))
    <td width="91.2pt" align="right" valign="center"  style="font-size: 12px;font-family: 'DizhitlRegular';
    font-weight: normal;

    font-style: normal;">THB {{@$query2->sum('total_price') != 0 ? number_format(@$query2->sum('total_price'),2,'.',',') : '0'}}</td>

    @endif

  </tr>

   <tr >
    <td width="33.2pt" height="38pt" class="boon" style="font-size: 10px;"><span style="position: relative;"><span style="position: absolute;top: 9px;left: -27px;">Sales Contact :</span></span></td>
    <td width="220pt" align="center"><span style="position: relative;"><span style="position: absolute;top: 10px;left: 0px;font-family: 'BoonRegular';
    font-weight: normal;
    font-style: normal;font-size: 10px;line-height: 1;">{{@$order->user->name}}</span></span></td>
    <td width="45pt"><span style="position: relative;font-size: 10px" class="boon"><span style="position: absolute;top: 10px;">Phone No. : </td>
    <td width="77pt" style="position: relative;font-size: 10px;" class="boon"><span style="position: absolute;top: 10px;">{{@$order->user->phone_number}}</span></td>
    <td width="30.4pt" align="right" style="visibility: hidden;">Vat</td>
    @if($i == intval($query_count2 - 1))
    <td width="61.2pt" align="right" valign="middle" style="font-size: 12px;font-family: 'DizhitlRegular';
    font-weight: normal;

    font-style: normal;">THB {{@$vat_total != 0 ? @number_format($vat_total,2,'.',',') : 0}}</td>

    @endif

  </tr>

   <tr style="border: 1px solid red;">
    <td colspan="4" width="331.2pt" height="28.8pt" style="text-transform: capitalize;">
       @php 
            $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
           $total = number_format(@$all_product_total - @$order->discount,'2','.','');
            $total_split = explode('.',$total);
            $word1 = $f->format($total_split[0]);
            $word2 = $f->format($total_split[1]);

            $word = $word1.' and '.$word2.' Satang';
           
           @endphp
          <span style="position: relative;"><span style="position: absolute;top: 11px;text-transform: uppercase;font-size: 12px"> {{@$word}} </span></span>
    </td>
    <!-- <td width="36"></td> -->
    <td width="30.4pt" align="right" style="visibility: hidden;">Total Amount</td>
    @if($i == intval($query_count2 - 1))
    <td width="61.2pt" align="right" valign="middle" style="font-size: 12px;font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;">THB {{number_format(@$all_product_total - @$order->discount,'2','.',',')}}</td>


    @endif
  </tr>

    <tr>
    <td width="53.2pt" height="28.8pt" valign="middle" colspan="2" class="boon" style="position: relative;"><span style="position: absolute;left: -25px;top: 7px">Ref.Name: &nbsp;&nbsp;&nbsp;{{@$order->customer->reference_name}}&nbsp;&nbsp;&nbsp;<span style="position:absolute;right: 80px;">Return Policy :</span></span></td>
    <!-- <td width="36"></td> -->
    <td colspan="4" valign="bottom" style="position: relative;"> <span style="position: absolute;top: 5pt;left: -80px"><p style="font-size: 13px;margin: 0px;">Returns could be accepcted with in 24 hours of delivery only.</p><p style="margin: 0;font-family: 'DizhitlRegular';
    font-weight: normal;
    font-style: normal;position: absolute;top:10pt;">การคืนสินค้าทางบริษัทจะยอมรับก็ต่อเมื่อมีการคืนในวันที่ทำการส่งสินค้าเท่านั้น</p></span>

              
</td>
   
  </tr>
</table>
<footer style="">
  @php 
    if($query->count() > 0)
    {
      $count = $query_count;
    }
    else
    {
      $count = 0;
    }
  @endphp
  <span style="float: right;">Page {{$i+1+@$count}} / {{$query_count2 + @$count}}</span>
</footer>
</div>

</body>
@endfor
  @endif
@endforeach
</html>