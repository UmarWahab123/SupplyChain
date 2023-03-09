@extends('warehouse.layouts.layout')
@section('title','Receiving Queue')
<?php
use Carbon\Carbon;
?>

@section('content')

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
          <li class="breadcrumb-item"><a href="{{route('warehouse-incompleted-transfer-groups')}}">Transfer Document Receiving Queue</a></li>
          <li class="breadcrumb-item active">Product Receiving Records</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="right-content pt-0">
  <div class="row headings-color">
   <input type="hidden" name="id" id="po_group_id" value="{{$po_group->id}}">
    <div class="col-lg-4 col-md-6 d-flex align-items-center">
      <h4>Group No {{$po_group->ref_id != null ? $po_group->ref_id : 'N.A' }}<br>Product Receiving Records</h4>
    </div>
    <div class="col-lg-6 col-md-4"></div>

    <!-- <div class="col-lg-2 col-md-2 text-right">
      <a onclick="backFunctionality()">
        <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button>
        <span class="vertical-icons" title="Back">
          <img src="{{asset('public/icons/back.png')}}" width="27px">
        </span>
      </a> -->
      {{-- <table class="table table-bordered mb-0">
        <tbody> --}}
          {{-- <tr>
            <th>AWB:B/L</th>
            <td>{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : 'N.A'}}</td>
          </tr>
          <tr>
            <th>Courier</th>
            <td>{{$po_group->po_courier != null ? $po_group->po_courier->title :" N.A"}}</td>
          </tr>
          <tr>
            <th>Note</th>
            <td>N.A</td>
          </tr> --}}
          {{-- @if(@$allow_custom_invoice_number == 1 && @$po_group->ToWarehouse->is_bonded == 1)
          <tr>
            <th>Custom's Inv.#</th>
            <td>
              <span>
                <input type="text"  name="custom_invoice_number" class="custom_invoice_number fieldFocus" data-id="{{$po_group->po_group_detail[0]->purchase_order->id}}" data-fieldvalue="{{@$po_group->po_group_detail[0]->purchase_order->invoice_number}}" value="{{@$po_group->po_group_detail[0]->purchase_order->invoice_number}}" readonly disabled style="width:100%">
              </span></td>
          </tr>
          @endif --}}
        {{-- </tbody>
        </table> --}}
    </div>
  </div>

  <div class="row headings-color">
    <div class="col-lg-4 col-md-4 d-flex align-items-center fontbold mb-3">
      <a href="javascript:void(0);" class="d-none">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf">print</button>
      </a>
@if(@$group_detail < 11)
       <a href="javascript:void(0);" class="ml-1 d-none">
        <button type="button" class="d-none btn-color btn text-uppercase purch-btn headings-color export-pdf2">print</button>
      </a>
      @endif
      <!-- code for print pdf through javascript -->
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export_new mr-3 d-none">print</button>
    </div>
  </div>
  <!-- export pdf form starts -->
  <form class="export-group-form" method="post" action="{{url('importing/export-group-to-pdf')}}">
    @csrf
    <input type="hidden" name="po_group_id" id="group_id_for_pdf" value="{{$po_group->id}}">
  </form>

  <!-- export pdf2 form starts -->
  <form class="export-group-form2" method="post" action="{{url('warehouse/export-group-to-pdf2')}}">
    @csrf
    <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
  </form>
<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 p-0">
        <div class="bg-white p-3">
          <table class="table headings-color entriestable text-center table-bordered product_table  table-responsive" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>TD No.
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="to_no">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="to_no">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               {{--<th>Order <br>Warehouse</th>
               <th>Order #</th>--}}
               <th>Supply <br> From</th>
               {{--<th>Sup's<br> Ref #</th>--}}
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
               <th>Selling <br> Unit
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Ordered
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_ordered">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_ordered">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Inv
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_inv">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_inv">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Rcvd 1
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_receive">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_receive">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Expiration <br>Date 1
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Rcvd 2
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity_received_2">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity_received_2">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Expiration <br>Date 2
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date_2">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date_2">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Goods <br>Condition </th>
               <th>Results </th>
               <th>Goods <br>Type </th>
               <th>{{$global_terminologies['temprature_c']}} </th>
               <th>Checker </th>
               <th>Problem <br>Found  </th>
               <th>Solution </th>
               <th>Authorized <br>Changes</th>
               <th>Custom's Inv#</th>
               <th>Custom's Line#</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-lg-8 col-md-8"></div>
      <div class="col-lg-2 col-md-2">
        <button type="button" data-id="{{$po_group->id}}" class="btn-color btn float-right full_qty_btn">Full Quantity</button>
      </div>
      @if($po_group->is_confirm == 0)
      <div class="col-lg-2 col-md-2">
        <a href="javascript:void(0);">
          <button type="button" data-id="{{$po_group->id}}" class="btn-color btn float-right confirm-po-group"><i class="fa fa-check"></i>Confirm to Stock</button>
        </a>
      </div>
      @endif
    </div>

    <div class="row mb-3">
      <div class="col-lg-6 p-0">
        <div class="table-responsive">
          <table class="table table-bordered bg-white tbl-history entriestable">
           <thead class="sales-coordinator-thead ">
             <tr>
              <th>User  </th>
              <th>Date/time </th>
              <th>Product </th>
              <th>Column </th>
              <th>Old Value</th>
              <th>New Value</th>
             </tr>
           </thead>
          </table>
          </div>
        </div>
      <div class="col-lg-6 mt-4">
        <div class="purchase-order-detail table-responsive">
          <table class="table table-bordered bg-white mt-3">
           <thead class="sales-coordinator-thead ">
             <tr>
              <th>User  </th>
              <th>Date/time </th>
              <th>Status </th>
              <th>New Status</th>
             </tr>
           </thead>
           <tbody>
              @if($status_history->count() > 0)
              @foreach($status_history as $history)
              <tr>
                <td>{{$history->get_user->name}}</td>
                <td>{{Carbon::parse(@$history->created_at)->format('d/m/Y, H:i:s')}}</td>
                <td>{{$history->status}}</td>
                <td>{{$history->new_status}}</td>
              </tr>
              @endforeach
              @else
              <tr>
                <td colspan="6"><center>No Data Available in Table</center></td>
              </tr>
              @endif
           </tbody>
          </table>
        </div>
      </div>
    </div>
    </div>
</div>
</div>

{{-- Greater Quantity Modal --}}
<div class="modal" id="greater_quantity">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order's </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-po">
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
<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
<div class="modal-dialog modal-sm">
  <div class="modal-content">
    <div class="modal-body">
      <h3 style="text-align:center;">Downloading...</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
    </div>
  </div>
</div>
</div>

{{-- Stock Qty Modal --}}
<div class="modal stock_Modal" id="stock_Modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Quantity Received</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="fetched-stock-details">
            <div class="d-flex justify-content-center">
              <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
        </div>
      </div>
    </div>
  </div>
  {{-- end modal code--}}

@endsection


@section('javascript')
<script type="text/javascript">

  // Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.product_table').DataTable().ajax.reload();

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
$(function(e){

  var show_custom_line_number_choice = "{{@$show_custom_line_number}}";
  var show_custom_invoice_number_choice = "{{@$allow_custom_invoice_number}}";
  var show_custom_line_number = '';
  var show_custom_invoice_number = '';
  if(show_custom_line_number_choice == 1 && "{{@$po_group->ToWarehouse->is_bonded}}" == 1)
  {
    show_custom_line_number = true;
  }
  else
  {
    show_custom_line_number = false;
  }

  if(show_custom_invoice_number_choice == 1 && "{{@$po_group->ToWarehouse->is_bonded}}" == 1)
  {
    show_custom_invoice_number = true;
  }
  else
  {
    show_custom_invoice_number = false;
  }

 $(document).on('click','.condition',function(){
  // alert('hi');
      var value = $(this).val();
      var id = $(this).data('id');

      savePoData(value,id);
  });

  function savePoData(value,id){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",

        url: "{{ url('warehouse/edit-po-goods') }}",
        dataType: 'json',
        data: 'value='+value+'&'+'id'+'='+id,
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $("#receive-table").DataTable().ajax.reload(null, false );
            return true;
          }
        },

      });
    }

  var id = $('#po_group_id').val();

  var table2 = $('.product_table').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    // "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    lengthMenu: [ 100, 200, 300, 400],
    ajax:{
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url:"{{ url('warehouse/get-details-of-transfer-doc')}}"+"/"+id,
        data: function(data) {
          data.column_name = column_name,
          data.sort_order = order
        }
      },
    columns: [
        { data: 'po_number', name: 'po_number' },
        // { data: 'order_warehouse', name: 'order_warehouse' },
        // { data: 'order_no', name: 'order_no' },
        { data: 'supplier', name: 'supplier' },
        // { data: 'reference_number', name: 'reference_number' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'desc', name: 'desc' },
        { data: 'selling_unit', name: 'selling_unit' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'qty_inv', name: 'qty_inv' },
        { data: 'qty_receive', name: 'qty_receive' },
        { data: 'expiration_date', name: 'expiration_date' },
        { data: 'quantity_received_2', name: 'quantity_received_2' },
        { data: 'expiration_date_2', name: 'expiration_date_2' },
        { data: 'goods_condition', name: 'goods_condition' },
        { data: 'results', name: 'results' },
        { data: 'goods_type', name: 'goods_type' },
        { data: 'goods_temp', name: 'goods_temp' },
        { data: 'checker', name: 'checker' },
        { data: 'problem_found', name: 'problem_found' },
        { data: 'solution', name: 'solution' },
        { data: 'changes', name: 'changes' },
        { data: 'custom_invoice_number', name: 'custom_invoice_number' , visible: show_custom_invoice_number },
        { data: 'custom_line_number', name: 'custom_line_number' , visible: show_custom_line_number },
    ],
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
    },
    fnDrawCallback:function(){ $('#expiration_date, #expiration_date_2').datepicker({
        format: "dd/mm/yyyy",
        autoHide: true,
    });
    $('#loader_modal').modal('hide');
  },
      // drawCallback: function(){
      //   $('#loader_modal').modal('hide');
      // },
  });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  $(document).on("dblclick",".inputDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on("dblclick",".expirations_dates",function(){
    $(this).removeAttr('disabled');
    $(this).focus();
  });
  $(document).on("click",".full_qty_btn",function(){
    var group_id = $(this).data('id');
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
      type: "post",
      data: {id: group_id},
      url: "{{ route('full-qty-add') }}",
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        toastr.success('Success!', 'All Qty Rcvd 1 Updated Successfully',{"positionClass": "toast-bottom-right"});
        $('.product_table').DataTable().ajax.reload();
      }
    });
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocus',function(e) {
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
      }
    if(e.keyCode == 13 || e.which == 0){

      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var pod_id= $(this).data('id');
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
    }
  });



  // confirm po button code here
  $(document).on('click','.confirm-po-group', function(e){
    var id = $(this).data('id');   //po_Group id
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });

    $.ajax({
      method:"post",
      data:'id='+id,
      url: "{{ route('check-transfer-document-status') }}",
      beforeSend:function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        if(response.success == true)
        {
          swal({
            title: "Are you sure!!!",
            text: ""+response.msg+ " Once you confirmed, no changes will be allowed.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, confirm it!",
            cancelButtonText: "Cancel",
            closeOnConfirm: true,
            closeOnCancel: false
            },
          function (isConfirm) {
            if (isConfirm) {
              $(".confirm-po-group").attr('disabled',true);
              $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
              });

              $.ajax({
                method:"post",
                data:'id='+id,
                url: "{{ route('confirm-transfer-warehouse-po-group') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                },
                success: function(response){
                  $("#loader_modal").modal('hide');
                  if(response.success == true)
                  {
                    toastr.success('Success!', 'Product Received Into Stock Successfully.',{"positionClass": "toast-bottom-right"});
                    window.location.href = "{{ route('warehouse-incompleted-transfer-groups')}}";
                  }
                  if(response.success == false)
                  {
                    toastr.warning('Warning!', 'Already Received Into Stock.',{"positionClass": "toast-bottom-right"});
                    setTimeout(function() {
                      window.location.href = "{{ route('warehouse-incompleted-transfer-groups')}}";
                    }, 200);
                  }
                }
              });
            }
            else
            {
              swal("Cancelled", "", "error");
            }
          });
        }
      }
    });
  });

    // export pdf code
  $(document).on('click', '.export-pdf', function(e){

    var po_group_id = $('#po_group_id').val();
    $('.export-group-form')[0].submit();
  });

});

  $(document).on('keyup', function(e) {
  if (e.keyCode === 27){ // esc
    $(".expirations_dates").datepicker('hide');

      $('.expirations_dates').attr('disabled',true);
      $('.expirations_dates').attr('readonly',true);
  }
  });

  $(document).on("change",".expirations_dates",function(e) {
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
      }

    if($(this).val() == '' || $(this).val() == fieldvalue)
    {
      return false;
    }
    else
    {
      var pod_id= $(this).data('id');
      var thisPointer = $(this);
      saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id);
      $(this).data('fieldvalue',thisPointer.val());
    }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
  });

  function saveSupData(thisPointer,field_name,field_value,pod_id){
    var po_group_id = $('#po_group_id').val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/edit-po-group-details') }}",
        dataType: 'json',
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&po_group_id='+po_group_id,
        success: function(data)
        {
          if(data.success == true)
          {
            //$(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          else if(data.success == false){
            var extra_quantity = data.extra_quantity;
            $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                  }
              });
            $.ajax({
              type: "post",
              url: "{{ route('get-incomplete-pos') }}",
              data: 'pod_id='+pod_id+'&extra_quantity='+extra_quantity,

              success: function(response){
                if(response.success == true){
                  $('#greater_quantity').modal('show');
                  $('.fetched-po').html(response.html_string);
                }
                else{
                  $(".product_table").DataTable().ajax.reload(null, false );
                  if(response.extra_quantity == true){
                  swal('The QTY You Entered Cannot be divided accordingly');
                  }
                  else{
                  swal('You Cannot Enter QTY Greater Than the Required QTY');
                  }
                }
              }
            });


          }
        },

      });
  }

     // export pdf2 code
  $(document).on('click', '.export-pdf2', function(e){

    var po_group_id = $('#po_group_id').val();
    $('.export-group-form2')[0].submit();
    /*
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });
    $.ajax({
      method:"post",
      data:'po_group_id='+po_group_id,
      url: "{{ route('export-group-to-pdf') }}",
      success: function(response){
          if(response.success === true){
          toastr.success('Success!', 'Product Received Into Stock Successfully.',{"positionClass": "toast-bottom-right"});
          window.location.href = "{{ url('/importing')}}";
        }
      }
    });*/

  });

  $(document).on('click','.export_new',function(){
  // alert('he');
  var id = "{{@$id}}";
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/export-group-to-pdf2') }}",
        dataType: 'json',
        data:{po_group_id:id},
        beforeSend:function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          if(data.success == true)
          {
            //$(".pick-instruction-table").DataTable().ajax.reload(null, false );
            // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            // return true;
                  var opt = {
            margin:       0.3,
            filename:     'Group NO-'+id+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
          };
        //         html2pdf()
        // .set({ html2canvas: { scale: 4 } })
        //   .from(data.view)
        //   .save();
          html2pdf().set(opt).from(data.view).save().then(function(){
          $("#loader_modal").modal('hide');
            // alert('done');
          });

          }
        },

      });
})
</script>
<script>
	function backFunctionality() {
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
		if (history.length > 1) {
			return history.go(-1);
		} else {
			var url = "{{ url('/') }}";
			document.location.href = url;
		}
	}

</script>
<script>
  var id = $('#po_group_id').val();
   $('.tbl-history').DataTable({
    "sPaginationType": "listbox",
    fixedHeader: true,
    searching: false,
    ordering: false,
    serverSide: true,
    // scrollX: true,
    // scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu: [ 25, 50, 100, 200],
    ajax: {
        url:"{!! route('get-details-of-transfer-doc-history') !!}",
        data: {id:id},
        method: "get"
      },
    columns: [
        { data: 'user', name: 'user' },
        { data: 'date', name: 'date' },
        { data: 'product', name: 'product' },
        { data: 'column', name: 'column' },
        { data: 'old_value', name: 'old_value' },
        { data: 'new_value', name: 'new_value' }
    ]
  });


    $(document).on('dblclick', '.td_qty_received', function() {
      $('#stock_Modal').modal({
                backdrop: 'static',
                keyboard: false
            });
        $('#stock_Modal').modal('show');

        let pod_id = $(this).data('id');
        let product_id = $(this).data('product_id');
        let warehouse_id = $(this).data('warehouse_id');
        let po_id = $(this).data('po_id');
        let receiving_side = true;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            url: "{{ route('get-reserved-stock-of-product') }}",
            data: {product_id:product_id, warehouse_id:warehouse_id, po_id:po_id, pod_id:pod_id,receiving_side:receiving_side, is_draft:false},
            beforeSend: function(){
                var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
                var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
                $('.fetched-stock-details').html(loader_html);
            },
            success: function(response){
                $('.fetched-stock-details').html(response.html);
                $('#total_qty_span').html(response.total_qty);

            },
            error: function(request, status, error){
                // $('#loader_modal').modal('hide');
            }
        });
    })


  $(document).on('keyup', '.input_received_qty, .input_spoilage_qty', function() {

        let total_qty = 0;
        $(".input_received_qty").each(function(){
            if ($(this).val() !== "") {
                total_qty += parseFloat($(this).val());
            }
        });
        $(".input_spoilage_qty").each(function(){
            if ($(this).val() !== "") {
                total_qty += parseFloat($(this).val());
            }
        });
        $('#total_qty_span').html(total_qty);
    });


    $(document).on('submit', '#save_qty_Form', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        formData.append('total_qty', $('#total_qty_span').text());
        formData.append('receiving_side', true);
        formData.append('po_group_id', '{{ $po_group->id }}');
        $.ajax({
            type: "post",
            url: "{{ route('save-available-stock-of-product_in_td') }}",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function(){
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
                $("#loader_modal").modal('show');
                $("#save_qty_in_reserved_table").prop('disabled', true);

            },
            success: function(response){
                if (response.success == true) {
                    $('#stock_Modal').modal('hide');
                    $(".product_table").DataTable().ajax.reload(null, false );
                }else{
                    $('#loader_modal').modal('hide');
                $("#save_qty_in_reserved_table").prop('disabled', false);

                    toastr.error('Error!', response.message, {"positionClass": "toast-bottom-right"});
                }
            },
            error: function(request, status, error){
                // $('#loader_modal').modal('hide');
            }
        });
    });
</script>
@stop

