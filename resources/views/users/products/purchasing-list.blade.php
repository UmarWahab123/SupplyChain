@extends('users.layouts.layout')

@section('title','Purchasing List | Supply Chain')

@section('content')
@php
use App\Models\Common\Supplier;
use App\User;
use Carbon\Carbon;
@endphp
<style type="text/css">
.invalid-feedback {
  font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
}
.dt-buttons > .buttons-excel{
  float: right !important;
}
.dt-buttons > .buttons-excel{
  background: #13436c;
  color: #fff;
  border-radius: 0px;
  font-size: 11px;
  max-height: 34px;
}
.dt-buttons > .buttons-excel:hover:not(.disabled){
  background-color: #13436c !important;
  color: #fff;
  background-image: none !important;
}
</style>

<div class="row">
  <div class="col-md-12">
    <a href="{{ url()->previous() }}" class="float-left pt-3">
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>
    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11)
          <li class="breadcrumb-item"><a href="{{route('sales')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 2)
          <li class="breadcrumb-item"><a href="{{route('purchasing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 5)
          <li class="breadcrumb-item"><a href="{{route('importing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 6)
          <li class="breadcrumb-item"><a href="{{route('warehouse-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 7)
          <li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 10)
          <li class="breadcrumb-item"><a href="{{route('roles-list')}}">Home</a></li>
        @endif
          <li class="breadcrumb-item active">Purchasing List</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h5 class="maintitle text-uppercase fontbold">Purchase List</h5>
  </div>
  <div class="col-md-4">
    <div class="pull-right">
        <span class="export-btn vertical-icons" title="Create New Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
    </div>
  </div>
</div>

{{--Filters start here--}}
<div class="col-lg-12 col-md-12 pl-0 pr-0 d-flex align-items-center mb-3 filters_div">

  <div class="col-lg-2 col-md-2">
    <label class="pull-left font-weight-bold">Choose Supply From:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags supply_from_filter" id="supply_from_filter" name="supply_from_filter">
      <option value="" disabled="" selected="">@if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif</option>
      @if(@$warehouseFilter->count() > 0)
        <optgroup label="Warehouses">
        @foreach($warehouseFilter as $warehouse)
        <option value="w-{{ $warehouse->id }}"> {{$warehouse->warehouse_title}} </option>
        @endforeach
        </optgroup>
      @endif
      @if(@$suppliersFilter->count() > 0)
        <optgroup label="Suppliers">
        @foreach($suppliersFilter as $supplier)
        <option value="s-{{ $supplier->id }}"> {{$supplier->reference_name}} </option>
        @endforeach
        </optgroup>
      @endif
    </select>
  </div>

  <div class="col-lg-2 col-md-2">
    <label class="pull-left font-weight-bold">Choose Supply To:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags supply_to_filter" id="supply_to_filter" name="supply_to_filter">
      <option value="" disabled="" selected="">Supply To</option>
      @foreach($warehouseFilter as $wf)
        <option value="{{$wf->id}}">{{$wf->warehouse_title}}</option>
      @endforeach
    </select>
  </div>
    @if($targetShipDate['target_ship_date']==1)

    <div class="col-lg-2 col-md-2 mb-2">
    <label class="pull-left font-weight-bold">@if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_receiving_date']}} @endif (From):</label>
    <input type="text" placeholder="@if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_receiving_date']}} @endif (From)" name="date_delivery_filter_from" class="form-control font-weight-bold f_tsd" id="date_delivery_filter_from" autocomplete="off">
  </div>

  <div class="col-lg-2 col-md-2 mb-2">
    <label class="pull-left font-weight-bold">@if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_receiving_date']}} @endif (To):</label>
    <input type="text" placeholder="@if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_receiving_date']}} @endif (To)" name="date_delivery_filter_to" class="form-control font-weight-bold to_tsd" id="date_delivery_filter_to" autocomplete="off">
  </div>
@else
    <div class="col-lg-2 col-md-2">
    </div>

@endif
  <div class="col-lg-3 col-md-4" style="margin-top: 10px; margin-left: -160px;">
    <!-- <button type="button" value="Reset" class="btn recived-button reset-btn">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif </button> -->
    <div class="pull-right">
      <span class="vertical-icons reset-btn" id="reset-btn" title="Reset">
        <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
    </div>
  </div>

</div>
{{--Filters ends here--}}


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
        <div class="alert alert-primary export-alert d-none"  role="alert">
         <i class="  fa fa-spinner fa-spin"></i>
         <b> Export file is being prepared! Please wait.. </b>
         </div>
          <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>
          <b>Export file is ready to download.
          <!-- <a download href="{{ url('storage/app/purchase-list-export.xlsx')}}"><u>Click Here</u></a> -->
          <a class="exp_download" href="{{ url('get-download-xslx','purchase-list-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
          </b>
        </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is already being prepared by another user! Please wait.. </b>
      </div>
      <div class="col-lg-12 col-md-12 pl-0 pr-0 d-flex align-items-center mb-3">

      <div class="delete-selected-item col-lg-1 col-md-1">
        <a title="Combine to PO" href="javascript:void(0);" class="btn selected-item-btn btn-sm success-btn create_purchase_ord" id="create_purchase_ord">
          <img src="{{ asset('public\site\assets\purchasing\img\move.png') }}" alt="Combine to PO" style="width:30px; height:30px;">
        </a>
      </div>

      <div class="col-lg-3 col-md-3">
        <!-- <div class="add-items-to-po-div d-none"> -->
        <label class="pull-left font-weight-bold">Choose PO (to add items):</label>
        <a type="button" style="cursor: pointer; float: right; color: #c5a316;" class="reset-po-dropdown" title="Reset Dropdown"><i class="fa fa-refresh"></i></a>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags add-items-to-po" id="add-items-to-po" name="add-items-to-po">
          <option value="" disabled="" selected="">Choose PO</option>
          @if(@$purchaseOrdersW->count() > 0)
            <optgroup label="@if(!array_key_exists('waiting_confrimation', $global_terminologies)) Waiting Confirmation @else {{$global_terminologies['waiting_confrimation']}} @endif">
            @foreach($purchaseOrdersW as $pow)
            <option value="{{ $pow->id }}"> (PO# {{$pow->ref_id}}) {{ $pow->PoSupplier->reference_name }} </option>
            @endforeach
            </optgroup>
          @endif
          @if(@$purchaseOrdersD->count() > 0)
            <optgroup label="Waiting Shipping Info">
            @foreach($purchaseOrdersD as $pod)
            <option value="{{ $pod->id }}"> (PO# {{$pod->ref_id}}) {{ $pod->PoSupplier->reference_name }} </option>
            @endforeach
            </optgroup>
          @endif
        </select>
        <!-- </div> -->
      </div>

      <div class="col-lg-2 col-md-1">
        {{-- <button class="btn recived-button">Export</button> --}}
      </div>

      </div>

      <table id="po_tabale" class="table entriestable table-bordered text-center table-create-purchase-order">
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox" align="center">
                <input type="checkbox" class="custom-control-input all_check" id="all_check" name="all_check">
                <label class="custom-control-label" for="all_check"></label>
              </div>
            </th>
            <th>Sale
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sale">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sale">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Draft Invoice #
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="draft_inovice_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="draft_inovice_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Reference Name
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reference_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reference_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="our_reference_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="our_reference_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="product_description">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="product_description">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['category']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="primary_category">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="primary_category">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['subcategory']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="subcategory">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="subcategory">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Order Confirm
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_confirm">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_confirm">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <th>{{$global_terminologies['target_ship_date']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_ship_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_ship_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['delivery_request_date']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="delivery_request_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="delivery_request_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th> {{$global_terminologies['pieces']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="pieces">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="pieces">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['qty']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Billed Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="billed_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="billed_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            {{-- <th>Sell Unit</th> --}}
            <th style="width: 150px;">{{$global_terminologies['supply_from']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supply_from">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supply_from">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>{{$global_terminologies['suppliers_product_reference_no']}}
             <!--  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="suppliers_product_reference_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="suppliers_product_reference_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th style="width: 175px;">Supply To
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supply_to">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supply_to">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th style="width: 150px;">Notes</th>
            @if($getWarehouses->count() > 0)
            @foreach($getWarehouses as $warehouse)
              <th>{{$warehouse->warehouse_title}}<br> Available <br>QTY
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="{{$warehouse->warehouse_title}}_available_qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="{{$warehouse->warehouse_title}}_available_qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
              </th>
            @endforeach
            @endif
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<!--  Content End Here -->
<!-- Loader Modal -->
<div class="modal" id="loader_modal2" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">

    <div class="modal-body">
      <h3 style="text-align:center;">Please wait</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
    </div>

    </div>
  </div>
</div>

<!-- Modal For Add Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Purchase List Notes</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-purchase-list-note-form" method="post">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="form-group">
                  <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                  <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="purchase_list_id" class="note-purchase-list-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger close-btn" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>
<input type="hidden" value="{{ $targetShipDate['target_ship_date'] }}" id="target_ship_date_status">
<input type="hidden" value="{{ $targetShipDate['target_ship_date_required'] }}" id="target_ship_date_required">
<!-- Modal for Showing Notes  -->
<div class="modal" id="notes-modal" style="width:600px; margin-left: 400px;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Purchase List Product Notes</h4>
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

<form id="export_purchase_list_form">
  <input type="hidden" name="supply_from_filter_exp" id="supply_from_filter_exp">
  <input type="hidden" name="supply_to_filter_exp" id="supply_to_filter_exp">
  <input type="hidden" name="date_delivery_filter_exp1" id="date_delivery_filter_exp1">
  <input type="hidden" name="date_delivery_filter_exp2" id="date_delivery_filter_exp2">
  <input type="hidden" name="tsd_exp" id="tsd_exp">
  <input type="hidden" name="sort_order" id="sort_order">
  <input type="hidden" name="column_name" id="column_name">
  <input type="hidden" id="search_value" name="search_value" value="search_value">
</form>

@endsection
<?php
  $hidden_by_default = '';
  $tsd=null;
  if($targetShipDate['target_ship_date']==1)
  {
    $tsd='visible:true';
  }
  else
  {
    $tsd='visible:false';
  }
?>

@section('javascript')
<script type="text/javascript">

  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('#sort_order').val(order);
    $('#column_name').val(column_name);

    $('.table-create-purchase-order').DataTable().ajax.reload();

    if($(this).data('order') ==  '2')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == '1')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });

  $( document ).ready(function() {
    var tsd = "{{ $tsd }}";
    $("#tsd_exp").val(tsd);
    setTimeout(() => {
        $('.supply_from_filter').trigger('change');
    }, 3000);
  });

  $("#date_delivery_filter_from, #date_delivery_filter_to").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

   // new function for to date which is not  before from

$('#date_delivery_filter_to').change(function(){
  var from = $('#date_delivery_filter_from').datepicker('getDate');
  var to = $(this).datepicker('getDate');
   if(from !='' && $('#date_delivery_filter_from').val() != '')
   {
      if(Date.parse(to)<Date.parse(from))
        {
          toastr.error('Error!', '"End Date" Must be Greater then start Date' ,{"positionClass": "toast-bottom-right"});
           $('#date_delivery_filter_to').val('');
        }else{
           $("#apply_filter_btn").val("1");
        }
  }


});

$('#date_delivery_filter_from').change(function(){
  var from = $(this).datepicker('getDate');
  var to = $('#date_delivery_filter_to').datepicker('getDate');
    if(to !='' && $('#date_delivery_filter_to').val() != '')
    {
      if(Date.parse(to)<Date.parse(from))
       {
        toastr.error('Error!', '"Start Date" Must be less then End Date' ,{"positionClass": "toast-bottom-right"});
         $('#date_delivery_filter_from').val('');
        }else{
           $("#apply_filter_btn").val("1");
        }
    }

  });

  $(function(e){

    $(".state-tags").select2();
    var buttonCommon = {
      exportOptions: {
        format: {
          body: function ( data, row, column, node ) {
            // Strip $ from salary column to make it numeric
            if(column === 15)
            {
              console.log(data);
            }
          }
        }
      }
    };

    var table2 = $('.table-create-purchase-order').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      searching:true,
      oLanguage:
      {
        sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
      },
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      bSort: false,
      info: true,
      colReorder: {
          realtime: false,
          fixedColumnsLeft: 1,
      },
        // bAutoWidth: true,
        retrieve: true,
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        columnDefs: [
          {
            targets: [{{ ($setting_table_hide_columns != null) ? $setting_table_hide_columns->hide_columns : $hidden_by_default }}],
            visible: false,
          },
          { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,10,11 ] },

        ],

      fixedHeader: true,
      dom: 'Blfrtip',
      pageLength: {{100}},
      lengthMenu: [ 100, 200, 300, 400],
      buttons: [
        {
          extend: 'colvis',
          columns: ':not(.noVis)',
        }
      ],
      ajax: {
        beforeSend: function(){
          $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal2").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal2").modal('show');
        },
        url:"{!! route('get-all-purchase-list-data') !!}",
        data: function(data) { data.supply_from_filter = $('.supply_from_filter option:selected').val(), data.supply_to_filter = $('.supply_to_filter option:selected').val(), data.date_delivery_filter_from = $('#date_delivery_filter_from').val(), data.date_delivery_filter_to = $('#date_delivery_filter_to').val(), data.sort_order=order, data.column_name=column_name },
        // method: "get",
      },
      // ajax: "{{ route('get-all-purchase-list-data') }}",
      columns: [
        { data: 'checkbox', name: 'checkbox'},
        { data: 'sale', name: 'sale' },
        { data: 'ref_id', name: 'ref_id' },
        { data: 'reference_name', name: 'reference_name' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'primary_category', name: 'primary_category' },
        { data: 'category_id', name: 'category_id' },
        { data: 'purchase_date', name: 'purchase_date' },
        { data: 'target_ship_date', name: 'target_ship_date', {{ $tsd }} },
        { data: 'delivery_date', name: 'delivery_date' },
        { data: 'pieces', name: 'pieces' },
        { data: 'quantity', name: 'quantity' },
        { data: 'bill_unit', name: 'bill_unit' },
        {{-- { data: 'selling_unit', name: 'selling_unit' }, --}}
        { data: 'supply_from', name: 'supply_from' },
        { data: 'supplier_product_ref', name: 'supplier_product_ref' },
        { data: 'supply_to', name: 'supply_to' },
        { data: 'remarks', name: 'remarks' },
        // Dynamic columns start
        @if($getWarehouses->count() > 0)
        @foreach($getWarehouses as $warehouse)
        { data: '{{$warehouse->warehouse_title}}{{"available"}}', name: '{{$warehouse->warehouse_title}}{{"available"}}'},
        @endforeach
        @endif
      ],
      initComplete: function () {

      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
      @if(@$display_purchase_list)
        table2.colReorder.order( [{{$display_purchase_list->display_order}}]);
      @endif
      },
      drawCallback: function(){
        $('#loader_modal2').modal('hide');
      },
   });


    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13)
      {
        table2.search($(this).val()).draw();
      }
    });


    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      var arr = table2.colReorder.order();
      // console.log(arr[0]);
      // var all = arr.split(',');
      var all = arr;
      if(all == ''){
        var col = column;
      }else{
        var col = all[column]
      }
    $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
    });
     $.post({
      url : "{{ route('toggle-column-display') }}",
      dataType : "json",
      data : {type:'purchase_list',column_id:col},
      beforeSend: function(){
        $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal2").modal('show');
      },
      success: function(data){
        $("#loader_modal2").modal('hide');
        if(data.success == true)
        {
          // table2.ajax.reload();
        }
      },
      error: function(request, status, error){
        $('#loader_modal2').modal('hide');
      }
    });
    });

    table2.on( 'column-reorder', function ( e, settings, details ) {
    $.get({
      url : "{{ route('column-reorder') }}",
      dataType : "json",
      data : "type=purchase_list&order="+table2.colReorder.order(),
      beforeSend: function(){
        $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal2").modal('show');
      },
      success: function(data){
        $("#loader_modal2").modal('hide');
      },
      error: function(request, status, error){
        $('#loader_modal2').modal('hide');
      }
    });
    table2.button(0).remove();
      table2.button().add(0,
      {
          extend: 'colvis',
          autoClose: false,
          fade: 0,
          columns: ':not(.noVis)',
          colVis: { showAll: "Show all" }
      });

    // table2.ajax.reload();

    var headerCell = $( table2.column( details.to ).header() );
    headerCell.addClass( 'reordered' );

    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });

  $(document).on('change','.supply_from_filter',function(){
    $("#supply_from_filter_exp").val($('.supply_from_filter option:selected').val());
    var selected = $(this).val();

    $.ajax({
        method:"get",
        url:"{{route('get-pos-of-seleced-supplier')}}",
        data: {supplier_id:selected},
        beforeSend: function(){
        $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
            });
            $("#loader_modal2").modal('show');
        },
        success:function(data){
            $('.add-items-to-po').html(data.html);
        }
    });
    $('.table-create-purchase-order').DataTable().ajax.reload();
  });

  $(document).on('change','.supply_to_filter',function(){
    $("#supply_to_filter_exp").val($('.supply_to_filter option:selected').val());
    var selected = $(this).val();
    if($('.supply_to_filter option:selected').val() != '')
    {
      $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal2").modal('show');
      $('.table-create-purchase-order').DataTable().ajax.reload();
    }
  });

  $('#date_delivery_filter_from').change(function() {
    if($('#date_delivery_filter_from').val() != '')
    {
      $('#date_delivery_filter_exp1').val($('#date_delivery_filter_from').val());
      $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal2").modal('show');
      $('.table-create-purchase-order').DataTable().ajax.reload();
    }
  });

  $('#date_delivery_filter_to').change(function() {
    if($('#date_delivery_filter_to').val() != '')
    {
      $("#date_delivery_filter_exp2").val($('#date_delivery_filter_to').val());
      $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal2").modal('show');
      $('.table-create-purchase-order').DataTable().ajax.reload();
    }
  });

  $('.reset-po-dropdown').on('click',function(){
    $('.add-items-to-po').val('').change();
  });

  $('.reset-btn').on('click',function(){
    $('.supply_from_filter').val('').trigger('change');
    $('.supply_to_filter').val('');
    $('#date_delivery_filter_from').val('');
    $('#date_delivery_filter_to').val('');
    $('.prod_type').val('');

    $('#supply_from_filter_exp').val('');
    $('#supply_to_filter_exp').val('');
    $('#date_delivery_filter_exp1').val('');
    $('#date_delivery_filter_exp2').val('');
    $(".state-tags").select2("", "");


    $('#loader_modal2').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal2").modal('show');
    $('.table-create-purchase-order').DataTable().ajax.reload();

  });

  $(document).on('click', '.all_check', function () {
  if(this.checked == true)
  {
    $('.individual_check').prop('checked', true);
    $('.individual_check').parents('tr').addClass('selected');
    var cb_length = $( ".individual_check:checked" ).length;
  }
  else
  {
    $('.individual_check').prop('checked', false);
    $('.individual_check').parents('tr').removeClass('selected');
    // $('.delete-selected-item').addClass('d-none');
  }
  });

  $(document).on('click', '.individual_check', function () {
    if(this.checked == true)
    {
      $(this).parents('tr').addClass('selected');
      var cb_length = $( ".individual_check:checked" ).length;
    }
    else
    {
      $(this).parents('tr').removeClass('selected');
    }
  });

  $(document).on("change",'.add-items-to-po',function(){

    var po_id = $(this).val();

    if(po_id > 0)
    {
      var selected_ids = [];
      $("input.individual_check:checked").each(function(){
        selected_ids.push($(this).val());
      });

      if(selected_ids == '')
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Select Items First To Add In PO !!!</b>'});
        return false;
      }

      $.ajaxSetup({
        headers:
        {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      swal({
        title: "Alert!",
        text: "Are you sure you want to add this item(s) into selected PO ?",
        type: "info",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Yes!",
        cancelButtonText: "No!",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function(isConfirm) {
    if (isConfirm){
      $.ajax({
        method: "get",
        url: "{{ url('check-existing-pos') }}",
        // async: false,
        dataType: 'json',
        context: this,
        data: {po_id:po_id, selected_ids:selected_ids,target_ship_date_status:$('#target_ship_date_status').val(),target_ship_date_required:$('#target_ship_date_required').val()},
        beforeSend: function(){
          $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal2").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal2").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal2").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', data.errorMsg ,{"positionClass": "toast-bottom-right"});
            $('.reset-po-dropdown').click();
            $('.table-create-purchase-order').DataTable().ajax.reload();
          }
          else if(data.success == false)
          {
            toastr.error('Error!', data.errorMsg ,{"positionClass": "toast-bottom-right"});
            $('.reset-po-dropdown').click();
          }
        },
        error: function(request, status, error){
          $('#loader_modal2').modal('hide');
        }
      });
    }
    else
    {
      swal("Cancelled", "", "error");
    }
    });
    }
  });

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $x = $(this);
      $(this).addClass('d-none');
      $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');

    setTimeout(function(){

      $('.spinner').remove();
      $x.next().removeClass('d-none');
      $x.next().addClass('active');
      $x.next().focus();

    }, 300);
  });

  $(document).on('keypress', 'input[type=text]', function(e){
    if(e.keyCode === 13 && $(this).hasClass('active')){

    var op_id = $(this).parent().parent().find('input.individual_check').val();;
    if($(this).attr('name') == 'remarks')
    {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();

      if($(this).val().length < 1)
      {
        return false;
      }
      else if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
      }
      else
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
        savePurchaseOrderRemarks(op_id, $(this).attr('name'), $(this).val());
      }

    }
   }
  });

  function savePurchaseOrderRemarks(op_id,field_name,field_value){
    console.log(field_name+'-'+field_value+'-'+op_id);
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('save-remarks-to-order-Products') }}",
      dataType: 'json',
      data: 'op_id='+op_id+'&'+field_name+'='+field_value,
      beforeSend: function(){
        $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal2").modal('show');
      },
      success: function(data)
      {
        $("#loader_modal2").modal('hide');
        if(data.success == true)
        {
          toastr.success('Success!', 'Remarks added successfully.',{"positionClass": "toast-bottom-right"});
        }
      },
      error: function(request, status, error){
        $('#loader_modal2').modal('hide');
      }
    });
  }

  // creating purchase order
  // $(document).on("click",'.create_purchase_ord',function(){
  $(".create_purchase_ord").click(function(){
    var selected_ids = [];
    // var vendors_ids = [];
    // var warehouse_ids = [];
    // var stop = 0;

    $("input.individual_check:checked").each(function(){
      selected_ids.push($(this).val());
    });

    if(selected_ids == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Select Items First To Combine To PO !!!</b>'});
      return false;
    }

    $('#create_purchase_ord').hide();

    // $("input.individual_check:checked").each(function(){
    //   if($(this).closest('tr').find('select').val() != null)
    //   {
    //     vendors_ids.push($(this).closest('tr').find('select').val());
    //   }
    //   else
    //   {
    //     stop = 1;
    //   }
    // });

    // if(stop == 1)
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Select Supply From First To Combine To PO !!!</b>'});
    //   return false;
    // }


    // $("input.individual_check:checked").each(function(){
    //   warehouse_ids.push($(this).closest('tr').find('.warehouses').val());
    // });

    // var check = vendors_ids.every( v => v === vendors_ids[0] );
    // var warehouse_check = warehouse_ids.every( v => v === warehouse_ids[0] );
    // if(warehouse_check == false)
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Please Select Same Supply To For Selected Items !!!</b>'});
    //   return false;
    // }

    // if(check == true)
    // {
      $.ajaxSetup({
        headers:
        {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
          method: "post",
          url: "{{ url('create_purchase_order') }}",
          dataType: 'json',
          context: this,
          data: {selected_ids:selected_ids,target_ship_date_status:$('#target_ship_date_status').val(),target_ship_date_required:$('#target_ship_date_required').val()},
          beforeSend: function(){
            $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal2").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal2").modal('show');
          },
          success: function(data)
          {
            $("#loader_modal2").modal('hide');
            if(data.success == true && data.action == "Refresh")
            {

              $('.table-create-purchase-order').DataTable().ajax.reload();
              $('#create_purchase_ord').show();
              toastr.success('Success!', 'Status Set To Delivery Of Selected Items Successfully.' ,{"positionClass": "toast-bottom-right"});
            }
            if(data.success == true && data.action == "LoadTD")
            {
              $('.table-create-purchase-order').DataTable().ajax.reload();
              toastr.success('Success!', 'Transfer Document Created Successfully.' ,{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('get-purchase-order-detail') }}"+"/"+data.po_id;
            }
            if(data.success == true && data.action == "LoadPO")
            {
              $('.table-create-purchase-order').DataTable().ajax.reload();
              toastr.success('Success!', 'Purchase Order Created Successfully.' ,{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('get-purchase-order-detail') }}"+"/"+data.po_id;
            }
            if(data.success == false)
            {
              $('#create_purchase_ord').show();
              toastr.error('Error!', data.errorMsg ,{"positionClass": "toast-bottom-right"});
              if(data.action == "Refresh")
              {
                $('.table-create-purchase-order').DataTable().ajax.reload();
              }
              if(data.page_reload == true)
              {
                setTimeout(function(){
                  window.location.reload();
                }, 300);
              }
            }
          },
          error: function (request, status, error)
          {
            $('#create_purchase_ord').show();
            $('#loader_modal2').modal('hide');
            toastr.error('Error!', 'Something went wrong!!!',{"positionClass": "toast-bottom-right"});
          }

        });
    // }
    // else
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Please Select Same Supply From For Selected Items !!!</b>'});
    // }

  });

  $(document).on("change",'.suppliers',function(){

    var supplier_id = $(this).val();
    var order_product_id = $(this).parent().parent().find('input.individual_check').val();

    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
        method: "post",
        url: "{{ url('order-product-supplier-save') }}",
        dataType: 'json',
        context: this,
        data: {supplier_id:supplier_id, order_product_id:order_product_id},
        beforeSend: function(){
          $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal2").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal2").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', data.msg ,{"positionClass": "toast-bottom-right"});
            // $('.table-create-purchase-order').DataTable().ajax.reload();
          }
          else if(data.success == false)
          {
            swal({ html:true, title:'Alert !!!', text:'<b>'+data.msg+'</b>'});
            $(this).val('');
            // $('.table-create-purchase-order').DataTable().ajax.reload();
          }

          if(data.waiting_to_pick == true)
          {
            $(this).closest('tr').remove();
          }
        },
        error: function(request, status, error){
          $('#loader_modal2').modal('hide');
        }
      });
  });

  $(document).on("change",'.warehouses',function(){

    var warehouses_id = $(this).val();
    var order_product_id = $(this).parent().parent().find('input.individual_check').val();

    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
        method: "post",
        url: "{{ url('order-product-warehouse-save') }}",
        dataType: 'json',
        context: this,
        data: {warehouses_id:warehouses_id, order_product_id:order_product_id},
        beforeSend: function(){
          $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal2").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal2").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Warehouse Assigned Successfully.' ,{"positionClass": "toast-bottom-right"});
            // $('.table-create-purchase-order').DataTable().ajax.reload();
          }
        },
        error: function(request, status, error){
          $('#loader_modal2').modal('hide');
        }
      });
  });

  $(document).on('click', '.add-notes', function(e){
    var id = $(this).data('id');
    $('.note-purchase-list-id').val(id);
  });

  $('.add-purchase-list-note-form').on('submit', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-purchase-list-prod-note') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      beforeSend: function(){
        $('.save-btn').addClass('disabled');
        $('.save-btn').attr('disabled', true);
        $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal2').modal('show');
      },
      success: function(result){
        $('.save-btn').attr('disabled', true);
        $('.save-btn').removeAttr('disabled');
        $('#loader_modal2').modal('hide');
        if(result.success == true){
          toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
          $('.table-create-purchase-order').DataTable().ajax.reload();

          $('.add-purchase-list-note-form')[0].reset();
          $('#add_notes_modal').modal('hide');

        }else{
          toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
        }

      },
        error: function (request, status, error)
        {
          $('#loader_modal2').modal('hide');
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            if ($('.invalid-feedback').html() == undefined) {
             $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
             $('textarea[name="'+key+'"]').addClass('is-invalid');
             $('textarea[name="'+key+'"]').css('border-color', '#dc3545');
            }
          });
        }
    });
  });

  $(document).on('click', '.close-btn', function(e){
    $('.add-purchase-list-note-form')[0].reset();
    $('.invalid-feedback').remove();
    $('textarea').css('border-color', 'black');

  });

  $(document).on('click', '.show-notes', function(e){
    let id = $(this).data('id');
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-purchase-list-prod-note') }}",
      data: 'id='+id,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response)
      {
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){
        //$('#loader_modal').modal('hide');
      }
    });

  });

  $(document).on('click', '#delete-compl-note', function(e){
    let note_id = $(this).data('id');
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    swal({
      title: "Alert!",
      text: "Are you sure you want to remove this note?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
      },
    function(isConfirm) {
      if (isConfirm){
      $.ajax({
      type: "get",
      url: "{{ route('delete-purchase-list-prod-note') }}",
      data: 'note_id='+note_id,
      beforeSend: function(){
        $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal2").modal('show');
      },
      success: function(response)
      {
        $("#loader_modal2").modal('hide');
        if(response.success == true)
        {
          $('#notes-modal').modal('hide');
          toastr.success('Success!', 'Note Deleted successfully',{"positionClass": "toast-bottom-right"});
          $('.table-create-purchase-order').DataTable().ajax.reload();
        }
      },
      error: function(request, status, error){
        $('#loader_modal2').modal('hide');
      }
      });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

  $(document).on('click','.export-btn',function(){
    $("#search_value").val($('.table-create-purchase-order').DataTable().search());

    $("#supply_from_filter_exp").val($('.supply_from_filter option:selected').val());
    $("#supply_to_filter_exp").val($('.supply_to_filter option:selected').val());
    $("#date_delivery_filter_exp1").val($('#date_delivery_filter_from').val());
    $("#date_delivery_filter_exp2").val($('#date_delivery_filter_to').val());
    $("#sort_order").val(order);
    $("#column_name").val(column_name);
     var form=$('#export_purchase_list_form');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"post",
      url:"{{route('export-purchase-list')}}",
      data:form_data,
      beforeSend:function(){
        $('.export-btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForPurchaseList();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForPurchaseList();
        }
      },
      error:function(){
        $('.export-btn').prop('disabled',false);
      }
    });
});

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-purchase-list')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-alert-success').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForPurchaseList();
        }
      }
    });
  });

  function checkStatusForPurchaseList()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-purchase-list')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForPurchaseList();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);

        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }


</script>
@stop
