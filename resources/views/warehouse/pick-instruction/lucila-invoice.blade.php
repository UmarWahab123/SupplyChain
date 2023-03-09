  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>Pick Instruction</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/> -->
     <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
    <!-- Bootstrap CSS -->
<!-- <link href="{{asset('public/site/assets/backend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"> -->
<style type="text/css">

body {
 font-family: 'BoonRegular';
   font-weight: normal;
   font-style: normal;font-size: 14px;
   line-height: 1;
}
.break_here{
  page-break-before: always;
}
</style>
  @php
    use Carbon\Carbon;
    use App\Models\Common\Order\OrderProduct;
    use App\Models\Common\Order\Order;
    use App\Models\Common\Order\OrderNote;
    use App\Models\Sales\Customer;
  @endphp
  </head>
@foreach($orders_array as $id)
@php

      $order = Order::find($id);
      $comment = OrderNote::select('note')->where('order_id',$order->id)->where('type','warehouse')->first();

      $comment_to_customer = OrderNote::select('note')->where('order_id',$order->id)->where('type','customer')->first();
      $cust_id = $order->customer_id;
      $customer = Customer::select('reference_number','company','first_name','last_name','reference_name')->where('id',$cust_id)->first();

      $ordersProducts = OrderProduct::with('get_order')->where('order_id', $id)->whereNotNull('product_id');

      if ($column_name == 'reference_code' && $default_sort !== 'id_sort')
      {
        $ordersProducts = $ordersProducts->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
      }
      elseif($column_name == 'short_desc' && $default_sort != 'id_sort')
      {
        $ordersProducts = $ordersProducts->orderBy('short_desc', $default_sort)->get();
      }
      elseif($column_name == 'supply_from' && $default_sort !== 'id_sort')
      {
        $ordersProducts = $ordersProducts->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
      }
      elseif($column_name == 'type_id' && $default_sort !== 'id_sort')
      {
        $ordersProducts = $ordersProducts->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
      }
      elseif($column_name == 'brand' && $default_sort !== 'id_sort')
      {
        // dd('works');
        $ordersProducts = $ordersProducts->orderBy($column_name, $default_sort)->get();
      }
      else{
        $ordersProducts = $ordersProducts->orderBy('id', 'ASC')->get();
      }
@endphp
  <body style="">
    <!-- <page size="A4"> -->
      <div class="row" style="">
        <div class="col-lg-6">
          <h3 class="text-bold">Pick Instruction</h3>
          {{-- <p>{{Auth::user()->getCompany->company_name}}</p> --}}
        </div>
      </div>
      <table width="100%" style="font-size: 16px;margin-top: 10px;">
        <thead>
          <tr>
            <th>Order No.</th>
            <th>Customer No.</th>
            <th>Customer Name</th>
            <th>Request Delivery Date</th>
            <th>External Document No.</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td valign="top">
              @if(@$order->status_prefix !== null)
              {{@$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id}}
              @else{{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id != null ? @$order->ref_id : '--'}}@endif</td>
            <td valign="top">{{@$customer->reference_number != null ? $customer->reference_number : 'N.A'}}</td>
            <td width="25%" valign="top">
              {{@$customer->company != null ? @$customer->company : $customer->first_name.' '.$customer->last_name}}
            <br>
              <b>Name </b> {{@$customer->reference_name}} <br>
            </td>
            <td valign="top">{{@$order->delivery_request_date != null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : '--'}}</td>
            <td valign="top">{{@$order->memo !== null ? @$order->memo : '--'}}</td>
          </tr>
        </tbody>
      </table>

      <table width="100%" style="font-size: 16px;break-inside: all;" class="my_table">
        <thead style="border-bottom: 1px solid black;">
          <tr>
            <th width="15%">Item No.</th>
            <th width="35%">Description</th>
            <th>U.Q.M</th>
            <th width="10%">QTY</th>
            <th width="9%">Pcs.</th>
            <th>@if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif <br> Picked</th>
            <!-- <th>Quanity <br> Shipped</th> -->
            <th width="10%">Note</th>
          </tr>
        </thead>
      </table>
      @foreach($ordersProducts as $item)
      <table width="100%" style="font-size: 16px;break-inside: all;" class="my_table">
        <tbody>

          <tr>
            <td width="15%">{{@$item->product->refrence_code}}</td>
            <td width="35%">{{@$item->product != null ? $item->product->short_desc : 'N.A'}}</td>
            <td width="9%">{{@$item->product != null ? $item->product->sellingUnits->title : 'N.A'}}</td>
            <td width="7%" align="center">

                {{@$item->quantity != null ? @$item->quantity : 0}}

            </td>
            <td width="13%" align="center">
              {{@$item->number_of_pieces != null ? @$item->number_of_pieces : 0}}
            </td>
            <td>_________</td>
            <td id="id{{@$item->id}}" width="10%" style="left: -15px;">
            @if($item->get_order_product_notes->count() > 0)
                @foreach(@$item->get_order_product_notes as $note)
                  {{@$note->note.' '}}
                @endforeach
            @endif
              </td>
          </tr>


        </tbody>
      </table>
      @endforeach
      <div class="col-lg-6 col-md-6 col-sm-6 pr-4 mt-5 pl-0">
        <p><strong>Comment To Warehouse: </strong><span class="inv-note" style="font-weight: normal;">{{@$comment != null ? @$comment->note : ''}}</span></p>
        <p><strong>{{$global_terminologies['comment_to_customer'] }}: </strong><span class="inv-note" style="font-weight: normal;">{{@$comment_to_customer != null ? @$comment_to_customer->note : ''}}</span></p>
      </div>
 <!--    </page> -->
  </body>
@endforeach


</html>
