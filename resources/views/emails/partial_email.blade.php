<!DOCTYPE html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<style type="text/css">
  .border-class tr th, .border-class tr td{
    border: 2px solid black;
    border-collapse: collapse !important;
  }
</style>
@php
use Illuminate\Support\Carbon;
@endphp
<html lang="en">
  <body style="background: white;">
  	<div class="container" style="width:75%; margin: auto; background: #eee;">
  		<div style="padding:18px;" >
        <p>Subject: Order {{@$getOrder->status_prefix.'-'.@$getOrder->ref_prefix.@$getOrder->ref_id}}</p>
        <p>Date and time: {{Carbon::parse($getNow)->format('d/m/Y  h:i:s')}}</p>
        <h3>Dear {{$createBy}},</h3>

        @php
          if($getOrder->is_vat == 1)
          {
            $inv_no = @$getOrder->in_status_prefix.'-'.@$getOrder->in_ref_id;
          }
          else
          {
            $inv_no = @$getOrder->in_status_prefix.'-'.@$getOrder->in_ref_prefix.@$getOrder->in_ref_id;
          }
        @endphp
        <p>This e-mail is regarding: Order {{@$getOrder->status_prefix.'-'.@$getOrder->ref_prefix.@$getOrder->ref_id}} -> {{@$inv_no}}</p>
      </div>
  		<div class="row">
  			<div class="col-lg-12" style="padding:18px;" align="center">

          <p>This order shipped to the customer with some items unavailable from the warehouse.</p>
          <p>The Customer ordered:</p>
  	    	<table style="width:100%;" class="border-class">    
            <tr>
              <th>Ref #</th>
              <th>Ordered Qty</th>
              <th>Delivered Qty</th>
              <th>Remaining Qty</th>
            </tr>
            {{-- @php $order_products = $getOrder->order_products->where('is_billed','Product'); @endphp --}}
            @foreach($getOrderProducts as $item)
            @if($item->qty_shipped < $item->quantity)
            <tr >
              @php 
                $product = App\Models\Common\Product::find($item->product_id);
              @endphp
              <td align="center">{{($item->product != null ? ($item->product->refrence_code != null ? $item->product->refrence_code : '') : '--')}}</td>
              <td align="center">{{($item->quantity != null ? number_format($item->quantity,3,'.',',') : '')}}</td>
              <td align="center">{{($item->qty_shipped != null ? number_format($item->qty_shipped,3,'.',',') : '')}}</td>
              @php
                $difference = ($item->quantity - $item->qty_shipped);
              @endphp
              <td align="center">{{($difference != null ? number_format($difference,3,'.',',') : '')}}</td> 
            </tr>
            @endif
            @endforeach
          </table>
          <p>If you would like to automatically create a second order in the system for the Remaining Quantity please click the following link which will generate an order with the unshipped items so that the warehouse can deliver them later: <a href="{{url('partial-shipment-order-process/'.$order_id)}}" target="_blank" class="btn btn-info" title="Click here to generate partial shipment for this order" style="cursor: pointer;">URL</a>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>