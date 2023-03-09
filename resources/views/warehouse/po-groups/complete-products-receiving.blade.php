@extends('warehouse.layouts.layout')
@section('title','Receiving Queue')
<?php
use Carbon\Carbon;
?>
<style type="text/css">
  .supplier_invoice_number_table tr td
  {
    border: 1px solid #aaa;
    padding: 3px 5px;
  }

  .supplier_invoice_number_table tr th
  {
    padding: 3px 5px;
    border: 1px solid #aaa;
  }
</style>
@section('content')
{{-- Content Start from here --}}

<div class="right-content pt-0">
  <div class="row headings-color">
    <div class="col-12 col-lg-12 col-md-12 col-sm-12 mb-3">
        <div class="pull-right">
          <a onclick="history.go(-1)">
            <!-- <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color btn_back">back</button> -->
            <span class="vertical-icons" title="Back">
              <img src="{{asset('public/icons/back.png')}}" width="27px">
            </span>
          </a>

          <span class="vertical-icons mr-4" id="export_r_q_d" title="Create New Export">
            <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
          </span>
        </div>
    </div>
   <input type="hidden" name="id" id="po_group_id" value="{{$po_group->id}}">
    <div class="col-lg-3 align-items-center">
      <h4>Group No {{$po_group->ref_id}}<br>Product Receiving Records</h4>
      <div class="form-group">
        <label class="mb-1 font-weight-bold">Group Status:</label>
        @if($po_group->is_confirm == 0)
        <span>OPEN</span>
        @else
        <span>CLOSED</span>
        @endif
      </div>
    </div>
    <div class="col-lg-6">

    </div>
    <div class="col-lg-3 d-flex align-items-center bg-white p-2">
      <table class="table table-bordered mb-0">
        <tbody>
          <tr>
            <th>AWB:B/L</th>
            <td>{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : "N.A" }}</td>
          </tr>
          <tr>
            <th>Courier</th>
            <td>{{$po_group->po_courier != null ? $po_group->po_courier->title :" N.A"}}</td>
          </tr>
          <tr>
            <th>Note</th>
            <td>{{$po_group->note != null ? $po_group->note :" N.A"}}</td>
          </tr>
          <tr>
            <th>Supplier Inv.#</th>
            <td>
              @if($pos_supplier_invoice_no->count() > 0)
              @if($pos_supplier_invoice_no->count() == 1)
                @foreach($pos_supplier_invoice_no as $inv)
                  <span><a href="{{route('get-purchase-order-detail',['id'=> $inv->id])}}" target="_blank">{{$inv->invoice_number != null ? $inv->invoice_number : '--'}}</a></span>
                @endforeach
              @else
                <!-- Button to Open the Modal -->
                <button type="button" class="btn p-0 pl-1 pr-1" data-toggle="modal" data-target="#myModal">
                  <i class="fa fa-eye"></i>
                </button>
              @endif
              @else
                <span>--</span>
              @endif
            </td>
          </tr>
          @if(@$allow_custom_invoice_number == 1 && @$po_group->ToWarehouse->is_bonded == 1)
          <tr>
            <th>Custom's Inv.#</th>
            <td>
              <span>
                <input type="text"  name="custom_invoice_number" class="custom_invoice_number fieldFocus" data-id="{{@$po_group->id}}" data-fieldvalue="{{@$po_group->custom_invoice_number}}" value="{{@$po_group->custom_invoice_number}}" style="width:100%">
              </span></td>
          </tr>
          @endif
        </tbody>
        </table>
    </div>
  </div>

  <div class="row mb-2">
  <div class="col-lg-12 col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">

      <div class="col-3"></div>

     <div class="col-3"></div>

      <div class="col"></div>

      <div class="col-2"></div>


      <div class="col-2">
        <label></label>
        <div class="input-group-append mt-2">
          <!-- <button id="export_r_q_d" class="btn recived-button rounded" >Export</button>  -->
<!--
          <span class="vertical-icons mr-4" id="export_r_q_d" title="Create New Export">
                  <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
              </span>
 -->        </div>
      </div>


    </div>
  </div>

</div>

  <div class="row headings-color">
    <div class="col-lg-4 d-flex align-items-center fontbold mb-3">
      <a href="javascript:void(0);" class="">
        <!-- <button type="button" class=" btn-color btn text-uppercase purch-btn headings-color export-pdf">print</button> -->

        <span class="vertical-icons export-pdf" title="Print">
          <img src="{{asset('public/icons/print.png')}}" width="27px">
        </span>
      </a>
       <!-- <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export_new mr-3">print</button> -->
    </div>
    <div class="col-lg-8 fontbold mb-3">
      <div class="pull-right">

      </div>
    </div>
  </div>
    <div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> PDF file is being prepared! Please wait.. </b>
</div>
<div class="alert alert-success export-alert-success d-none"  role="alert">
<i class=" fa fa-check "></i>

  <b>PDF file is ready to download.
  <a download href="{{asset('public/uploads/system_pdfs/Group-No-'.$po_group->ref_id.'.pdf')}}"><u>Click Here</u></a>
  </b>
</div>

<!-- Excel file alert divs -->
<div class="alert alert-primary excel-alert d-none"  role="alert">
    <i class="  fa fa-spinner fa-spin"></i>
      <b> Excel file is being prepared! Please wait.. </b>
</div>
<div class="alert alert-success excel-alert-success d-none"  role="alert">
  <i class=" fa fa-check "></i>

  <b>Excel file is ready to download.
  <a class="exp_download" href="{{ url('get-download-xslx','Group No '.$po_group->ref_id.'.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
  </b>
</div>
<!-- ends here -->

  <!-- export pdf form starts -->
  <form class="export-group-form" method="post" action="{{url('warehouse/completed-export-group-to-pdf')}}">
    @csrf
    <input type="hidden" name="po_group_id" id="group_id_for_pdf" value="{{$po_group->id}}">
    <input type="hidden" name="po_group_supplier_id" id="supplier_group_id_for_pdf" value="">
    <input type="hidden" name="po_group_product_id" id="product_group_id_for_pdf" value="">
    <input type="hidden" name="group_ref_id" id="group_ref_id" value="{{ $po_group->ref_id }}">
    <input type="hidden" name="sort_order" id="sort_order_pdf">
    <input type="hidden" name="column_name" id="column_name_pdf">
    <input type="hidden" name="blade" id="blade" value="receiving_queue_details">
  </form>

<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 p-0">
        <div class="bg-white table-responsive p-3">
          <table class="table headings-color entriestable text-center table-bordered product_table first_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>Details</th>
               <th>PO #
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_no">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_no">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Order #</th>
               <th>{{$global_terminologies['suppliers_product_reference_no']}} </th>
               <th>{{$global_terminologies['our_reference_number']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="our_reference_number">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="our_reference_number">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Warehouse
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="warehouse">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="warehouse">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Supplier <br> Reference<br> Name</th>
               <th>{{$global_terminologies['supplier_description']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_description">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_description">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Customer
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Ordered
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_ordered">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_ordered">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Inv
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_inv">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_inv">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Billed<br> Unit
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="billed_unit">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="billed_unit">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Rcvd 1
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_receive">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_receive">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Expiration<br> Date 1
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['qty']}} <br>Rcvd 2
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity_received_2">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity_received_2">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Expiration<br> Date 2
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="expiration_date_2">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="expiration_date_2">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Goods <br>Condition
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="goods_condition">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="goods_condition">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Results
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="results">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="results">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Goods <br>Type
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="goods_type">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="goods_type">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>{{$global_terminologies['temprature_c']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="temprature_c">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="temprature_c">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Checker
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="checker">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="checker">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Problem<br> Found
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="problem_found">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="problem_found">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Solution
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="solution">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="solution">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Authorized<br> Changes
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="authorized_changes">
                  <img src="http://localhost/supplychain/public/svg/up.svg" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="authorized_changes">
                  <img src="http://localhost/supplychain/public/svg/down.svg" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
               </th>
               <th>Custom's Line#</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-lg-3 col-md-3 offset-9">
        <a href="javascript:void(0);">
          <button type="button" class="btn float-right btn-success save-and-close-btn" onclick="window.history.back();">Save & Close</button>
        </a>
      </div>
    </div>

    <div class="row mb-3 headings-color">
      <div class="col-lg-3 align-items-center">
        <h4>Group No {{$po_group->ref_id}} Reverted Purchase Orders</h4>
      </div>
      <div class="col-lg-12 p-0">
        <div class="bg-white table-responsive p-3">
          <table class="table headings-color entriestable text-center table-bordered reverted_pos_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>Po#</th>
               <th>Group</th>
               <th>PF#</th>
               <th>Supplier Reference Name</th>
               <th>Quantity Inv</th>
               <th>Total Received</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>


    <div class="row mb-3">
      <div class="col-lg-6 p-0">
        <div class="bg-white p-3">
          <div>
          <table class="my-tablee table table-bordered entriestable tbl-history w-100">
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
      </div>
      <div class="col-lg-6">
        <div class="purchase-order-detail table-responsive">
          <div class="bg-white p-3">
            <table class="my-tablee table-bordered">
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

<!-- The Modal For Supplier Invoice number -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">PO's Supplier Inv.#</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <table width="100%" class="supplier_invoice_number_table">
          <thead>
            <tr>
              <th>S.No</th>
              <th>Supplier Inv.#</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pos_supplier_invoice_no as $inv)
              <tr>
                <td>{{$loop->iteration}}</td>
                <td> <a href="{{route('get-purchase-order-detail',['id'=> $inv->id])}}" target="_blank">{{$inv->invoice_number}}</a> </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Modal footer -->
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

<form id="export_receiving_queue_detail">
  @csrf
  <input type="hidden" name="id" value="{{$po_group->id}}">
  <input type="hidden" name="sort_order" id="sort_order">
  <input type="hidden" name="column_name" id="column_name">
</form>

@endsection


@section('javascript')
<script type="text/javascript">
  var order = 1;
  var column = 'none';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column = $(this).data('column_name');
    $('#sort_order').val(order);
    $('#column_name').val(column);

    $('#sort_order_pdf').val(order);
    $('#column_name_pdf').val(column);

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
  var show_custom_line_number = '';
  if(show_custom_line_number_choice == 1 && "{{@$po_group->ToWarehouse->is_bonded}}" == 1)
  {
    show_custom_line_number = true;
  }
  else
  {
    show_custom_line_number = false;
  }
    // export pdf code
  $(document).on('click', '.export-pdf', function(e){
    var po_group_id = $('#po_group_id').val();
    {{-- $('.export-group-form')[0].submit(); --}}
    e.preventDefault();
    $.ajax({
      method:"post",
      data:$('.export-group-form').serialize(),
      url:"{{url('warehouse/completed-export-group-to-pdf')}}",
      beforeSend:function(){
        $('.export-pdf').attr('title','Please Wait...');
        $('.export-pdf').prop('disabled',true);
        $('.download-btn').addClass('d-none');
      },
      success: function(response){
          if(response.success === true){
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-pdf').attr('title','Please Wait...');
          $('.export-pdf').prop('disabled',true);
          getPdfStatus();
        }
      },
      error:function()
      {
          $('.export-alert').addClass('d-none');
          $('.export-pdf').attr('title','Print');
          $('.export-pdf').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong.',{"positionClass": "toast-bottom-right"});

      }
    });
  });
  function getPdfStatus()
  {

        var po_group_id=$('#po_group_id').val();
        console.log(1+' '+po_group_id);

          $.ajax({
              method:"get",
              url:"{{route('get-pdf-status')}}",
              data:{group_id:po_group_id},
              success:function(data){
                    if(data.status==1)
                    {
                      console.log("Status " +data.status);
                      setTimeout(
                        function(){
                          console.log("Calling Function Again");
                          getPdfStatus();
                        }, 5000);
                    }
                    else if(data.status==0)
                    {
                        $('.export-alert').addClass('d-none');
                        $('.export-pdf').attr('title','Print');
                        $('.export-pdf').prop('disabled',false);
                        $('.export-alert-success').removeClass('d-none');
                    }
                     else if(data.status==2)
                      {
                        $('.export-alert').addClass('d-none');
                        $('.export-pdf').attr('title','Print');
                        $('.export-pdf').prop('disabled',false);
                        $('.export-alert-success').addClass('d-none');
                        toastr.error('Error!', 'Something went wrong.',{"positionClass": "toast-bottom-right"});
                      }
              }
            });
  }
  $(document).on('click', '.save-and-close-btn', function(e){
    $('.save-and-close-btn').prop('disabled',true);
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    setTimeout(function(){
      window.location.href = "{{ url('warehouse/warehouse-receiving-queue')}}";
    }, 500);
  });

  $(document).on('click','.btn_back', function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
  });

  var table2 = $('.product_table').DataTable({
    "sPaginationType": "listbox",
     processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    dom: 'Blfrtip',

    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
    "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22 ] },
    { className: "dt-body-right", "targets": [] }
  ],

    ajax:{
       beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
      },
      url: "{{ url('warehouse/get-completed-po-group-product-details')}}"+"/"+id,
      data: function(data){
        data.sort_order=order,
        data.column_name=column
      },
      method: 'get',
    },
    columns: [
        {
          "className":      'details-control',
          "orderable":      false,
          "searchable":      false,
          "defaultContent": ''
        },
        { data: 'po_number', name: 'po_number' },
        { data: 'order_no', name: 'order_no' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'supplier', name: 'supplier' },
        { data: 'desc', name: 'desc' },
        { data: 'customer', name: 'customer' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'qty_inv', name: 'qty_inv' },
        { data: 'unit', name: 'unit' },
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
        { data: 'custom_line_number', name: 'custom_line_number', visible: show_custom_line_number },
    ],createdRow: function (row, data, index) {
      // console.log(data.status);
          if (data.occurrence < 2 || data.status == 0) {
              var td = $(row).find("td:first");
              td.removeClass('details-control');
          }
      },
    initComplete: function () {
        // Enabled THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

      },
      fnDrawCallback:function(){
        $('#expiration_date_1, #expiration_date_2').datepicker({
        format: "dd/mm/yyyy",
        autoHide: true,
       });

        $('#loader_modal').modal('hide');

    }
  });

  function template( d ) {
  return '<table class="second_table table entriestable table-bordered text-center" id="products-'+d.id+'">'+
  '<thead style="display:none;">'+
  '<tr>'+
    '<th></th>'+
    '<th>PO #</th>'+
    '<th>Order#</th>'+
    '<th>{{$global_terminologies["suppliers_product_reference_no"]}}</th>'+
    '<th>{{$global_terminologies["our_reference_number"]}}</th>'+
    '<th>Order<br>Warehouse</th>'+
    '<th>Supplier <br>Reference<br> Name</th>'+
    '<th>{{$global_terminologies["product_description"]}}</th>'+
    '<th></th>'+

    '<th>{{$global_terminologies["qty"]}} <br>Ordered</th>'+
    '<th>{{$global_terminologies["qty"]}} <br>Inv</th>'+
    '<th><th>Billed<br> Unit</th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
    '<th></th>'+
  '</tr>'+
  '</thead>'+
'</table>';
}

  $('.product_table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table2.row(tr);
        var tableId = 'products-' + row.data().id;

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
            tr.removeClass('details');
            tr.removeClass('greyRow');
        } else {
            // Open this row
            row.child(template(row.data())).show();
            initTable(tableId, row.data());
            tr.addClass('shown');
            tr.addClass('details');
            tr.addClass('greyRow');
            tr.next().find('td').addClass('no-padding bg-gray');
        }
    });

  function initTable(tableId, data) {
    var product_id = data.product_id;
    var group_id   = data.po_group_id;
    var supplier_id = data.supplier_id;
    $('#'+tableId).DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        paging: false,
        "bInfo" : false,
        "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ajax:
        {
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
          },
          url: "{!! route('get-po-group-every-product-details')  !!}",
          data: function(data) { data.product_id = product_id,data.group_id = group_id,data.supplier_id = supplier_id } ,
        },
        columns: [
            {
              "className":      '',
              "orderable":      false,
              "searchable":      false,
              "defaultContent": ''
            },
            { data: 'po_no', name: 'po_no' },
            { data: 'order_no', name: 'order_no' },
            { data: 'supplier_ref_no', name: 'supplier_ref_no' },
            { data: 'product_ref_no', name: 'product_ref_no' },
            { data: 'order_warehouse', name: 'order_warehouse' },
            { data: 'supplier_ref_name', name: 'supplier_ref_name' },
            { data: 'short_desc', name: 'short_desc' },
            { data: 'customer', name: 'customer' },
            { data: 'quantity_ordered', name: 'quantity_ordered' },
            { data: 'quantity_inv', name: 'quantity_inv' },
            { data: 'buying_unit', name: 'buying_unit' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' },
            { data: 'empty_col', name: 'empty_col' , visible: show_custom_line_number},
        ],
        columnDefs: [
        { className: "dt-body-left", "targets": [ 3,4,5,6,7,9,10,11] },
        { className: "dt-body-right", "targets": [] },

        ],
        fixedColumns: true,
        drawCallback: function ( settings ) {
          $('#loader_modal').modal('hide');
          for (var i = 1; i <= 24; i++) {
          var headerHeight = $('.first_table tbody tr td:nth-child('+i+')').innerWidth();
          headerHeight -=10;
          $('.second_table tbody tr td:nth-child('+i+')').css('width', headerHeight);
          // $('.first_table tbody tr td:nth-child('+i+')').css('width', headerHeight);
        }

        // var headerHeight = $('.second_table tbody tr td:nth-child(2)').outerWidth();
          // $('.first_table tbody tr td:nth-child(2)').css('width', headerHeight);

        }
    })
  }

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
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

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocus',function(e) {
    console.log(e.keyCode+' '+e.which);
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      // thisPointer.attr('disabled','true');
      // thisPointer.attr('readonly','true');
      }
    if(e.keyCode == 13 || e.which == 0){

      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var p_g_p_d_id= $(this).data('id');
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),p_g_p_d_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      // $(this).attr('disabled','true');
      // $(this).attr('readonly','true');
    }
  });

  $(document).on("change",".expirations_dates",function(e) {
    if (e.keyCode === 27) {
      var fieldvalue = $(this).data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      // thisPointer.attr('disabled',true);
      // thisPointer.attr('readonly',true);
    }
    if($(this).val() != '')
    {
      var fieldvalue = $(this).data('fieldvalue');
      // $(this).attr('disabled','true');
      // $(this).attr('readonly','true');
      if($(this).val().length < 1 || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var p_g_p_d_id= $(this).data('id');
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),p_g_p_d_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
    }
  });

  function saveSupData(thisPointer,field_name,field_value,p_g_p_d_id){
    var po_group_id = $('#po_group_id').val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/edit-po-group-product-details') }}",
        dataType: 'json',
        data: 'p_g_p_d_id='+p_g_p_d_id+'&'+field_name+'='+field_value+'&po_group_id='+po_group_id,
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $('#loader_modal').modal('hide');
          if(data.custom_invoice_number == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $("#loader_modal").modal('hide');
            return true;
          }


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
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  }

});

  $(document).on('keyup', function(e) {
  if (e.keyCode === 27){ // esc
    $(".expirations_dates").datepicker('hide');

      $('.expirations_dates').attr('disabled',true);
      $('.expirations_dates').attr('readonly',true);
  }
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
        url: "{{ url('warehouse/completed-export-group-to-pdf') }}",
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
          $('#loader_modal').modal('hide');
          if(data.success == true)
          {
            //$(".pick-instruction-table").DataTable().ajax.reload(null, false );
            // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            // return true;
                  var opt = {
            margin:       0.1,
            filename:     'Group NO-'+id+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' },
            // pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
          };

          html2pdf().set(opt).from(data.view).save().then(function(){
          $("#loader_modal").modal('hide');
            // alert('done');
          });


          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
})

 $('#export_r_q_d').on('click',function(){
    $("#export_receiving_queue_detail").submit();
  });

  $(document).on('submit', '#export_receiving_queue_detail', function (e) {
        e.preventDefault();
        $.ajax({
            method: "post",
            url: "{{route('export-product-receiving-record')}}",
            data: $(this).serialize(),
            beforeSend: function() {
                $('.excel-alert').removeClass('d-none');
                $('.excel-alert-success').addClass('d-none');
            },
            success: function(data) {
                if (data.success == true) {
                    $('.excel-alert').addClass('d-none');
                    $('.excel-alert-success').removeClass('d-none');
                }
            },
            error: function(request, status, error) {
                $("#loader_modal").modal('hide');
            }
        });
    })

</script>
<script>
  var id = $('#po_group_id').val();
   $('.tbl-history').DataTable({
    "sPaginationType": "listbox",
    fixedHeader: true,
    searching: false,
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu: [ 25, 50, 100, 200],
    ajax: {
        url:"{!! route('warehouse-products-receiving-history') !!}",
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

   $('.reverted_pos_table').DataTable({
    fixedHeader: true,
    searching: false,
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu: [ 25, 50, 100, 200],
    ajax: {
        url:"{!! route('warehouse-reverted-pos') !!}",
        data: {id:id},
        method: "get"
      },
    columns: [
        { data: 'po', name: 'po' },
        { data: 'group', name: 'group' },
        { data: 'product', name: 'product' },
        { data: 'supplier', name: 'supplier' },
        { data: 'quantity', name: 'quantity' },
        { data: 'total_quantity', name: 'total_quantity' }
    ]
  });
</script>
@stop

