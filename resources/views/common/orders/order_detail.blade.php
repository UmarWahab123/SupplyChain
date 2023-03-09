@extends($layout.'.layouts.layout')

@section('title','Quotations Products | Sales')
<?php
use Carbon\Carbon;
?>
@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
</style>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h5 class="maintitle text-uppercase fontbold mb-0">Quotations # <span class="c-ref-id">{{$order->ref_id }}</span></h5>
  </div> 
  <div class="col-md-4 title-col">
    <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</span></h5>
  </div>
  <!-- New Design Starts  -->
  <div class="col-lg-12">
    <div class="row">
      <div class="col-lg-8">
        <div class="d-flex align-items-center mb-0">
      <div>
         @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . @$company_info->logo))
        <img src="{{asset('public/uploads/logo/'.@$company_info->logo)}}" class="img-fluid" style="width: 80px;height: 80px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 80px;height: 80px;" align="big-qummy">
        @endif
        <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p></div>
    </div>
        <p class="mb-2">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
        <ul class="list-inline list-unstyled pl-0">
          <li class="list-inline-item"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}</li>
          <li class="list-inline-item"><em class="fa fa-envelope"></em> {{@$company_info->billing_email}} </li>
        </ul>
        <br>
        <div class="form-group">
          <label class="mb-1 font-weight-bold">Quotation # </label> <span class="dblclk-edit" data-type="ref_id">{{ $order->ref_id }}</span>
        </div>
        <div class="form-group"> 
          <label class="mb-1 font-weight-bold">Quotation Date:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{ $order->created_at }}</span>
        </div> 
      </div>
      <div class="col-lg-4">
        <div class="d-flex align-items-center mb-1">
            @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
            <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">
            @else
            <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">
            @endif

          <div class="pl-2 comp-name"><p>{{$order->customer->company}}</p></div>
        </div>
        <div class="bill_body">
          @if($billing_address != null)
           <p class="mb-2">{{ @$billing_address->billing_address.', '.@$billing_address->getcountry->name .', '.@$billing_address->getstate->name.', '.@$billing_address->billing_city.', '.@$billing_address->billing_zip }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{@$billing_address->billing_phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{@$billing_address->billing_email}}</a></li>
        </ul>
        <ul class="d-flex list-unstyled">
      <li><b>Tax ID:</b> @if($billing_address->tax_id !== null) {{ $billing_address->tax_id }} @endif</li>
    </ul>
          @else
        <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ $order->customer->address_line_1.' '.$order->customer->address_line_2.', '.$order->customer->getcountry->name .', '.$order->customer->getstate->name.', '.$order->customer->city.', '.$order->customer->postalcode }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
        </ul>
        @endif
        </div>
        <ul class="d-flex list-unstyled">
          <li>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif:</li>
          <li class="pl-3"><span class="inputDoubleClick">{{$order->target_ship_date}}</span></li>
        </ul>
      </div> 

      <div class="col-lg-12 text-uppercase fontbold">
        <a href="{{  url('sales') }}"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color">back</button></a>
        <a href="javascript:void(0);">
        </a>
        <div class="pull-right">         
          
          <a href="javascript:void(0)" data-id="{{$order->id}}" data-toggle="modal" data-target="#file-modal" class="download-documents d-none"><button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs">View documents<i class="pl-1 fa fa-download"></i></button></a>
         
        </div>
      </div>
    </div>
  </div>
  <!-- New Design Ends -->
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-quotation-product text-center">
        <thead>
          <tr>
            <th class="inv-head">Reference  #</th>
            <th class="inv-head">@if(!array_key_exists('product_description', $global_terminologies)) Product  Description @else {{$global_terminologies['product_description']}} @endif </th>
            <th class="inv-head"># @if(!array_key_exists('pieces', $global_terminologies)) Pieces @else {{$global_terminologies['pieces']}} @endif </th>
            <th class="inv-head">Sales <br> Unit </th>
            <th class="inv-head"># @if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif </th>   
            <th class="inv-head">*Reference <br> Price</th>                       
            <th class="inv-head">*@if(!array_key_exists('default_price_type', $global_terminologies))  Price Type @else {{$global_terminologies['default_price_type']}} @endif</th>    
            <th class="inv-head">Default <br> Price </th>
            <th class="inv-head">Total <br> Price </th>
            <th class="inv-head">Vat </th>
            <th class="inv-head">Notes </th>

          </tr>
        </thead>  
      </table>

        <!-- New Design Starts Here  -->
      <div class="row ml-0 mt-4">
        <div class="col-9 pad">
            <div class="row">
              <div class="col-lg-6 d-none">
                <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
                  
                 <table class="my-tablee">
                   <thead class="sales-coordinator-thead ">
                    <tr>                
                      <th>User  </th>
                      <th>Date/time </th>
                      <th>Item#</th>
                      <th>Column</th>
                      <th>Old Value</th>
                      <th>New Value</th>
                    </tr>
                   </thead>
                   <tbody>
                    <tr>
                      <td>abc</td>            
                      <td>10/10/19</td>
                      <td>12</td>            
                      <td>Unit</td>
                      <td>456</td>
                      <td>457</td>
                    </tr>
                   </tbody>
                 </table>
                </div>
                
              </div>

              <div class="col-lg-6">
                <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3 h-100">
                  <table class="my-tablee dot-dash">
                   <thead class="sales-coordinator-thead ">
                     <tr>                
                      <th>User  </th>
                      <th>Date/time </th>
                      <th>Status </th>
                      <th>New Status</th>
                     </tr>
                   </thead>
                   <tbody>
                      @foreach($status_history as $history)
                      <tr>
                        <td>{{$history->get_user->name}}</td>
                        <td>{{Carbon::parse(@$history->created_at)->format('d/m/Y')}}</td>                 
                        <td>{{$history->status}}</td>
                        <td>{{$history->new_status}}</td>                 
                      </tr>                 
                      @endforeach   
                   </tbody>
                  </table>
                </div>
              </div>

              <div class="col-lg-6">               
                <p><strong>Comment: </strong><span>@if($inv_note != null) {!! $inv_note->note !!} @else {{ 'No Comment Found' }} @endif</span>
                </p>
              </div>

              <div class="col-lg-6 mt-4">
                <p class="mb-0">Ship To</p>
        
        <div class="ship_body">
           @if($shipping_address != null)
           <p class="mb-2">{{ @$shipping_address->billing_address.', '.@$shipping_address->getcountry->name .', '.@$shipping_address->getstate->name.', '.@$shipping_address->billing_city.', '.@$shipping_address->billing_zip }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{@$shipping_address->billing_phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{@$shipping_address->billing_email}}</a></li>
        </ul>
        @else
        <p class="mb-2"> {{ $order->customer->address_line_1.' '.$order->customer->address_line_2.', '.$order->customer->getcountry->name .', '.$order->customer->getstate->name.', '.$order->customer->city.', '.$order->customer->postalcode }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
        </ul>
        @endif
        </div>
      </div> 
        </div>
        </div>
        <div class="col-lg-3 pt-4 mt-4">
          <div class="side-table">
            <table class="headings-color purch-last-left-table side-table">
              <tbody>
                <tr>
                  <td class="text-right fontbold">Sub Total:</td>
                  <td class="sub-total text-start fontbold">&nbsp;&nbsp;{{number_format($sub_total, 2, '.', ',') }}</td>
                </tr> 
                <tr>
                  <td class="text-nowrap fontbold">Discount:</td>
                  <td class="fontbold text-start">&nbsp;&nbsp;
                    <span class="mr-2">{{ $order->discount == '' ? 0 : number_format($order->discount, 2, '.', ',') }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">Shipping:</td>
                  <td class="fontbold text-start">&nbsp;&nbsp;
                    <span class="mr-2">{{ $order->shipping == '' ? 0 :number_format($order->shipping, 2, '.', ',') }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">VAT:</td>
                  <td class="fontbold text-start total-vat">&nbsp;&nbsp;{{number_format($vat,2,'.',',')}}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">Total:</td>
                  <td class="fontbold text-start grand-total">&nbsp;&nbsp;{{number_format($grand_total,2,'.',',')}}</td>
                </tr>
                 
                 <tr>
                  <td class="text-nowrap fontbold d-none">Paid:</td>
                  <td class="fontbold text-start d-none">&nbsp;&nbsp;20/09/2019</td>
                </tr>
                 <tr>
                  <td class="text-nowrap fontbold d-none">Due:</td>
                  <td class="fontbold text-start d-none">&nbsp;&nbsp;20/09/2019</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
        <!-- New Design Ends Here  -->
    </div>
  </div>
</div>
<!--  Content End Here -->



<div class="modal" id="loader_modal_old" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>
    </div>
  </div>
</div>



<!-- Modal for Showing Notes  -->
<div class="modal" id="notes-modal" style="width:600px; margin-left: 400px;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Completed Quotation Product Notes</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-notes">
            <div class="adv_loading_spinner3 d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


@endsection

@section('javascript')
<script type="text/javascript">
  $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
  $(function(e){
    
    $('.table-quotation-product').DataTable({
      // "bAutoWidth": false,
       processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      searching: false,
      ordering: false,
      serverSide: true,
      bInfo: false,
      paging: false,
      dom: 'lrtip',
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      ajax: "{{ url('common/get-order-product-detail') }}"+"/"+{{ $order->id }},       
      columns: [
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'description', name: 'description' },
        { data: 'number_of_pieces', name: 'number_of_pieces' },
        { data: 'sell_unit', name: 'sell_unit' },
        { data: 'quantity', name: 'quantity' },
        { data: 'exp_unit_cost', name: 'exp_unit_cost' },
        { data: 'margin', name: 'margin' },
        { data: 'unit_price', name: 'unit_price' },
        { data: 'total_price', name: 'total_price' },
        { data: 'vat', name: 'vat' },
        { data: 'notes', name: 'notes' },
      ]
   });

    $(document).on('click', '.show-notes', function(e){
    let compl_quot_id = $(this).data('id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-order-prod-note') }}",
      data: 'compl_quot_id='+compl_quot_id,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        // console.log(response);
        $('.fetched-notes').html(response);
      }
    });

  });

@if(Session::has('successmsg'))
  toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});  
  @php 
   Session()->forget('successmsg');     
  @endphp  
@endif

@if(Session::has('errormsg'))
  toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});  
  @php 
   Session()->forget('errormsg');     
  @endphp  
@endif


  });
</script>
@stop

