@extends('importing.layouts.layout')
@section('title','Products Recieving')
<?php
use Carbon\Carbon;
?>

@section('content')
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
{{-- Content Start from here --}}
<div class="right-content pt-0">
  <div class="row headings-color mb-3">
   <input type="hidden" name="id" id="po_group_id" value="{{$id}}">
    <div class="col-lg-3 align-items-center">
      <h4>Group No {{$po_group->ref_id}}<br>Product Received Records</h4>
      <div class="form-group">
        <label class="mb-1 font-weight-bold">Group Status:</label>
        @if($po_group->is_review == 0)
        <span>OPEN</span>
        @else
        <span>CLOSED</span>
        @endif
      </div>
    </div>
    <div class="col-lg-6"></div>

    <div class="col-lg-3">
      <div class="row">
        <div class="col-lg-12 text-right mb-3">
          <a onclick="backFunctionality()">
            <!-- <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button> -->
            <span class="vertical-icons" title="Back">
              <img src="{{asset('public/icons/back.png')}}" width="27px">
            </span>
          </a>
        </div>
        <div class="col-lg-12 d-flex align-items-center bg-white p-2">
      <table class="table table-bordered mb-0">
        <tbody>
          <tr>
            <th>AWB:B/L</th>
            <td>{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : 'N.A'}}</td>
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
          @if($allow_custom_invoice_number == 1 && $po_group->ToWarehouse->is_bonded == 1)
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
    </div>
  </div>
  <div class="row headings-color mt-4">
    <div class="col-lg-12 fontbold mb-3">
      <div class="pull-right">
      <a href="javascript:void(0);" class="ml-1 d-none">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf2">print</button>
      </a>

      <a href="javascript:void(0);" class="ml-1">
        <!-- <button id="export_i_r_q_d_s" class="btn recived-button rounded export-btn" >Create New Export</button>    -->

        <span class="export-btn vertical-icons" id="export_i_r_q_d_s" title="Create New Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
      </a>
        @php
        if($last_downloaded==null)
        $className='d-none';
        else
        $className='';
      @endphp
      <a download href="{{asset('storage/app/Importing-Product-Receiving-'.$po_group->id.'.xlsx')}}"  class="download-btn {{$className}}">
      <!-- Download -->

      <span class="vertical-icons">
            <img src="{{asset('public/icons/download.png')}}" width="27px">
        </span>
    </a>
       <b class="float-right download-btn-text ml-1 {{$className}} " ><i>Last created on:  @if($last_downloaded!=null){{Carbon::parse(@$last_downloaded)->format('d/m/Y H:i:s')}} @endif</i> </b>

    </div>
    </div>
  </div>

<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 p-0">
        <div class="bg-white table-responsive p-3">

          <div class="alert alert-primary export-alert d-none"  role="alert">
                <i class="  fa fa-spinner fa-spin"></i>
           <b> Export file is being prepared! Please wait.. </b>
          </div>
          <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>

            <b>Export file is ready to download.
              <!-- <a download href="{{asset('storage/app/Importing-Product-Receiving-'.$po_group->ref_id.'.xlsx')}}"><u>Click Here</u></a> -->
              <a class="exp_download" href="{{ url('get-download-xslx','Importing-Product-Receiving-'.$po_group->ref_id.'.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
            </b>
          </div>
            <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
                <i class="  fa fa-spinner fa-spin"></i>
           <b> Export file is being prepared by another user! Please wait.. </b>
          </div>

          {{-- updating currency conversion rate --}}
           <div class="col-lg-12 col-md-12 mt-4">
                <div class="alert alert-primary export-alert-recievable d-none"  role="alert">
                  <i class="  fa fa-spinner fa-spin"></i>
                  <b> Data is Updating Please wait! Please wait.. </b>
                </div>

                  <div class="alert alert-success export-alert-success-recievable d-none"  role="alert">
                  <i class=" fa fa-check "></i>
                    <b>Data Update Successfully !!!
                    </b>
                  </div>
          </div>
          {{-- ends here --}}

          <table class="table headings-color entriestable text-center table-bordered product_table first_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>Detail</th>
                <th>PO #</th>
                <th>Order <br>Warehouse</th>
                <th>Order #</th>
                <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>
                <th>Supplier</th>
                <th>{{$global_terminologies['supplier_description']}}</th>
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th> {{$global_terminologies['brand']}}</th>
                <th>{{$global_terminologies['product_description']}}</th>
                <th>{{$global_terminologies['type']}}</th>
                <th>Customer</th>
                <th> Buying <br> Unit</th>
                <th>{{$global_terminologies['qty']}} <br>Ordered</th>
                <th>{{$global_terminologies['qty']}}</th>
                <th>{{$global_terminologies['qty']}} <br>Inv</th>
                <th>{{$global_terminologies['note_two']}}</th>
                <th>{{$global_terminologies['gross_weight']}}</th>
                <th>Total <br>{{$global_terminologies['gross_weight']}}</th>
                <th>Extra Cost (THB) </th>
                <th>Total <br>{{$global_terminologies['extra_cost_per_billed_unit']}}</th>
                <th>{{$global_terminologies['extra_tax_per_billed_unit']}} </th>
                <th>Total {{$global_terminologies['extra_tax_per_billed_unit']}}</th>
                <!-- <th>QTY Rcvd</th> -->
                <th>{{$global_terminologies['purchasing_price']}}<br>EUR (W/O Vat)</th>
                <th>{{$global_terminologies['purchasing_price']}}<br>EUR (+Vat)</th>
                <th>Discount</th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>EUR (W/O Vat)</th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>EUR (+Vat)</th>
                <th>Currency Conversion Rate</th>
                <th>{{$global_terminologies['purchasing_price']}}<br>THB (W/O Vat)</th>
                <th>{{$global_terminologies['purchasing_price']}}<br>THB (+Vat)</th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>THB (W/O Vat)</th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>THB (+Vat)</th>
                <th>Book VAT %</th>
                {{-- <th>VAT %</th> --}}
                <th>Import <br>Tax <br>(Book)%</th>
                <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
                <th>Total Freight</th>
                <th>{{$global_terminologies['landing_per_billed_unit']}}</th>
                <th>Total Landing</th>
                <th>Book VAT Total <br>(THB)</th>
                {{-- <th>VAT % Tax</th> --}}
                <th>VAT Weighted %</th>
                <th>Unit Purchasing<br>VAT (THB)</th>
                <th>Total Purchasing<br>VAT (THB)</th>
                {{-- <th>VAT Actual<br>Tax</th> --}}
                <th>Purchasing<br>VAT %</th>
                {{-- <th>VAT Actual<br> Tax %</th> --}}
                <th>Book Import<br>Tax Total</th>
                {{-- <th>Book % Tax</th> --}}
                <th>Import<br>Weighted %</th>
                {{-- <th>Weighted %</th> --}}
                <th>{{$global_terminologies['actual_tax']}}</th>
                <th>Total Import Tax (THB)</th>
                <th>{{$global_terminologies['import_tax_actual']}}</th>
                <th>Custom's Line#</th>
                <th>COGS Per Unit</th>
                <th>Total COGS</th>
              </tr>
            </thead>
            <tfoot align="right">
              <tr>
                <th>Total</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="qty_ordered_f"></th>
                <th></th>
                <th class="qty_inv_f"></th>
                <th></th>
                <th></th>
                <th class="total_gross_weight_f"></th>
                <th></th>
                <th class="total_extra_cost_f"></th>
                <th></th>
                <th class="total_import_tax_thb_f"></th>
                <th class="purchasing_price_f_wo_vat"></th>
                <th class="purchasing_price_f"></th>
                <th></th>
                <th class="total_purchasing_price_f_wo_vat"></th>
                <th class="total_purchasing_price_f"></th>
                <th></th>
                <th class="purchasing_price_thb_f_wo_vat"></th>
                <th class="purchasing_price_thb_f"></th>
                <th class="total_purchasing_price_thb_f_wo_vat"></th>
                <th class="total_purchasing_price_thb_f"></th>
                <th></th>
                <th></th>
                <th class="freight_per_billed_unit_f"></th>
                <th class="freight_per_billed_unit_t"></th>
                <th class="landing_per_billed_unit_f"></th>
                <th class="landing_per_billed_unit_t"></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="book_tax_f"></th>
                <th class="weighted_f"></th>
                <th class="actual_tax_f"></th>
                <!-- <th ></th> -->
                <th class="actual_tax_t"></th>
                <th class="actual_tax_percent_f"></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

  <div class="row headings-color">
    <div class="col-lg-9 d-flex align-items-center fontbold">
    </div>
    <div class="col-lg-3 input-group">
      @php
        $latest = App\Models\Common\PoGroup::where('is_review',1)->orderBy('id','DESC')->first();
      @endphp
      @if($latest->id == $po_group->id)
        <table class="table table-bordered bg-white">
          <tbody>
            <tr class="d-none">
              <th>Total Import Tax (THB)</th>
              <td style="text-align: center;">
                <span class="total_import_tax_thb"></span>
              </td>
            </tr>
            <tr>
              <th>Import Tax</th>
              <td><input type="number" name="tax" placeholder="Actual Tax" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->tax}}" value="{{$po_group->tax}}"></td>
            </tr>
            <tr>
              <th>Purchasing VAT</th>
              <td><input type="number" name="vat_actual_tax" placeholder="VAT (Actual)" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->vat_actual_tax}}" value="{{$po_group->vat_actual_tax}}"></td>
            </tr>
            <tr>
              <th>Freight Cost</th>

              <td><input type="number" name="freight" placeholder="Freight Cost" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->freight}}" value="{{$po_group->freight}}" {{$check_bond == 1 ? 'disabled' : ''}}></td>
            </tr>
            <tr>
              <th>Landing Cost</th>
              <td><input type="number" name="landing" placeholder="Landing Cost" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->landing}}" value="{{$po_group->landing}}" {{$check_bond == 1 ? 'disabled' : ''}}></td>
            </tr>
          </tbody>
        </table>
      @else
      <table class="table table-bordered bg-white mb-0">
        {{--
        <tr>
          <th>Total Import Tax (THB)</th>
          <td style="text-align: center;">
            <span class="total_import_tax_thb"></span>
          </td>
        </tr> --}}
        <tr>
          <th>Import Tax</th>
          <td>{{$po_group->tax !== null ? number_format($po_group->tax,2,'.',',') :'N.A'}}</td>
        </tr>
        <tr>
          <th>Purchasing VAT</th>
          <td>{{$po_group->vat_actual_tax !== null ? number_format($po_group->vat_actual_tax,2,'.',',') :'N.A'}}</td>
        </tr>
        <tr>
          <th>Freight Cost</th>
          <td>{{$po_group->freight !== null ? number_format($po_group->freight,2,'.',',') :'N.A'}}</td>
        </tr>
        <tr>
          <th>Landing Cost</th>
          <td>{{$po_group->landing !== null ? number_format($po_group->landing,2,'.',',') :'N.A'}}</td>
        </tr>
      </table>
      @endif
    </div>
  </div>

  <!-- ---------------History------- -->
    <div class="row">
      <div class="col-lg-6">
        <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
          <table class="table-po-group-order-history headings-color entriestable table table-bordered text-center">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>User </th>
                <th>Date/Time</th>
                <th>Group #</th>
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th>Column</th>
                <th>Old Value</th>
                <th>New Value</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
</div>
</div>

<form id="export_receiving_queue_detail" method="post"  action="{{route('export-importing-product-receiving-record') }}">
  @csrf
  <input type="hidden" name="id" value="{{$po_group->id}}">
  <input type="hidden" name="status" value="CLOSE">
  <input type="hidden" name="sort_order" id="sort_order">
  <input type="hidden" name="column_name" id="column_name">
</form>

<!-- export pdf2 form starts -->
<form class="export-group-form2" method="post" action="{{url('importing/export-group-to-pdf2')}}">
  @csrf
  <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
</form>

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

@endsection
@php
  $hidden_by_default = '';
@endphp

@section('javascript')
<script type="text/javascript">

  var visibility_arr = [];
  var reorder_arr = [];
  var is_child_table = false;

$(function(e){

  var hidden_cols = "{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}";
  hidden_cols = hidden_cols.split(',');

  var show_custom_line_number_choice = "{{$show_custom_line_number}}";
  var show_custom_line_number = '';
  if(show_custom_line_number_choice == 1 && "{{@$po_group->ToWarehouse->is_bonded}}" == 1)
  {
    show_custom_line_number = true;
    if( hidden_cols.includes("39") )
    {
      show_custom_line_number = false;
    }
  }
  else
  {
    show_custom_line_number = false;
  }

  var id = $('#po_group_id').val();

  visibility_arr = [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}];

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  var table2 = $('.product_table').DataTable({
    "sPaginationType": "listbox",
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    fixedHeader: false,
    dom: 'Blfrtip',
    pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    colReorder: {
      realtime: false
    },
    columnDefs: [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9,10,11 ] },
      { className: "dt-body-right", "targets": [12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29] }
    ],
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
    ajax:{
      beforeSend: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      url : "{{ url('importing/get-po-group-product-details')}}"+"/"+id,
      method: "post",
    },
    columns: [
        {
          "className":      'details-control',
          "orderable":      false,
          "searchable":      false,
          "defaultContent": ''
        },
        { data: 'po_number', name: 'po_number' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'order_no', name: 'order_no' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'supplier', name: 'supplier' },
        { data: 'supplier_description', name: 'supplier_description' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'brand', name: 'brand' },
        { data: 'desc', name: 'desc' },
        { data: 'type', name: 'type' },
        { data: 'customer', name: 'customer' },
        { data: 'unit', name: 'unit' },
        // { data: 'buying_currency', name: 'buying_currency' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'original_qty', name: 'original_qty' },
        { data: 'qty', name: 'qty' },
        { data: 'product_notes', name: 'product_notes' },
        { data: 'pod_unit_gross_weight', name: 'pod_unit_gross_weight' },
        { data: 'pod_total_gross_weight', name: 'pod_total_gross_weight' },
        { data: 'pod_unit_extra_cost', name: 'pod_unit_extra_cost' },
        { data: 'pod_total_extra_cost', name: 'pod_total_extra_cost' },
        { data: 'pod_unit_extra_tax', name: 'pod_unit_extra_tax' },
        { data: 'pod_total_extra_tax', name: 'pod_total_extra_tax' },
        /*{ data: 'qty_receive', name: 'qty_receive' },*/
        { data: 'buying_price_wo_vat', name: 'buying_price_wo_vat' },
        { data: 'buying_price', name: 'buying_price' },
        { data: 'discount', name: 'discount' },
        { data: 'total_buying_price_wo_vat', name: 'total_buying_price_wo_vat' },
        { data: 'total_buying_price', name: 'total_buying_price' },
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
        { data: 'buying_price_in_thb_wo_vat', name: 'buying_price_in_thb_wo_vat' },
        { data: 'buying_price_in_thb', name: 'buying_price_in_thb' },
        { data: 'total_buying_price_in_thb_wo_vat', name: 'total_buying_price_in_thb_wo_vat' },
        { data: 'total_buying_price_in_thb', name: 'total_buying_price_in_thb' },
        { data: 'vat_act', name: 'vat_act' },
        { data: 'import_tax_book', name: 'import_tax_book' },
        { data: 'freight', name: 'freight' },
        { data: 'total_freight', name: 'total_freight' },
        { data: 'landing', name: 'landing' },
        { data: 'total_landing', name: 'total_landing' },

        { data: 'vat_percent_tax', name: 'vat_percent_tax' },
        { data: 'vat_weighted_percent', name: 'vat_weighted_percent' },
        { data: 'vat_act_tax', name: 'vat_act_tax' },
        { data: 'total_vat_act_tax', name: 'total_vat_act_tax' },
        { data: 'vat_act_tax_percent', name: 'vat_act_tax_percent' },

        { data: 'book_tax', name: 'book_tax' },
        { data: 'weighted', name: 'weighted' },
        { data: 'actual_tax', name: 'actual_tax' },
        { data: 'total_actual_tax', name: 'total_actual_tax' },
        { data: 'actual_tax_percent', name: 'actual_tax_percent' },
        { data: 'custom_line_number', name: 'custom_line_number', visible: show_custom_line_number },
        { data: 'product_cost', name: 'product_cost' },
        { data: 'total_product_cost', name: 'total_product_cost' },
    ],
    createdRow: function (row, data, index) {
      if (data.occurrence < 2 ) {
        var td = $(row).find("td:first");
        td.removeClass('details-control');
      }
    },
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });

      @if($display_prods)
        reorder_arr = [{{ $display_prods->display_order }}];
        table2.colReorder.order( [{{ $display_prods->display_order }}]);
      @endif

    },
    footerCallback: function ( row, data, start, end, display ) {
      var api = this.api();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"get",
        dataType:"json",
        url : "{{ url('importing/get-po-group-product-details-footer-values')}}"+"/"+id,
        beforeSend:function(){
          $( '.qty_ordered_f' ).html('Loading...');
          $( '.qty_inv_f' ).html('Loading...');
          $( '.total_gross_weight_f' ).html('Loading...');
          $( '.total_extra_cost_f' ).html('Loading...');
          $( '.total_import_tax_thb_f' ).html('Loading...');
          $( '.purchasing_price_f' ).html('Loading...');
          $( '.total_purchasing_price_f' ).html('Loading...');
          $( '.purchasing_price_thb_f' ).html('Loading...');
          $( '.total_purchasing_price_thb_f' ).html('Loading...');
          // $( '.freight_per_billed_unit_f' ).html('Loading...');
          $( '.freight_per_billed_unit_t' ).html('Loading...');
          // $( '.landing_per_billed_unit_f' ).html('Loading...');
          $( '.landing_per_billed_unit_t' ).html('Loading...');
          $( '.book_tax_f' ).html('Loading...');
          $( '.total_purchasing_price_thb_f_wo_vat' ).html('Loading...');
          $( '.weighted_f' ).html('Loading...');
          // $( '.actual_tax_f' ).html('Loading...');
          $( '.actual_tax_t' ).html('Loading...');
          // $( '.actual_tax_percent_f' ).html('Loading...');
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        },
        success:function(result){
          $( '.qty_ordered_f' ).html(result.qty_ordered_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.qty_inv_f' ).html(result.qty_inv_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_gross_weight_f' ).html(result.total_gross_weight.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_extra_cost_f' ).html(result.total_extra_cost.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_import_tax_thb_f' ).html(result.total_extra_tax.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $('.total_import_tax_thb').html(result.total_extra_tax.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.purchasing_price_f' ).html(result.buying_price.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_purchasing_price_f' ).html(result.total_buying_price.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.purchasing_price_thb_f' ).html(result.buying_price_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_purchasing_price_thb_f' ).html(result.t_buying_price_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $( '.freight_per_billed_unit_f' ).html(result.freight.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{2})+(?!\d))/g, ","));
          // $( '.landing_per_billed_unit_f' ).html(result.landing.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{2})+(?!\d))/g, ","));
          $( '.book_tax_f' ).html(result.book_per_tax);
          $( '.total_purchasing_price_thb_f_wo_vat' ).html(result.total_unit_price_in_thb);
          $( '.weighted_f' ).html(result.weighted_sum);
          // $( '.actual_tax_f' ).html(result.actual_tax_col_sum);
          $( '.actual_tax_t' ).html(result.total_import_tax_cal);
          // $( '.actual_tax_percent_f' ).html(result.actual_tax_per_sum);
          $( '.freight_per_billed_unit_t' ).html(result.total_freight);
          $( '.landing_per_billed_unit_t' ).html(result.total_landing);
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        },
        error: function(){

        }
      });
    },
    drawCallback: function ( settings ) {
      $('#loader_modal').modal('hide');
    }
  });

  table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

    $(".product_table > tbody > tr").each(function () {
      var tr = $(this);
      var row = table2.row(tr);
      var tableId = 'products-' + tr.attr('id');
      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        tr.removeClass('details');
        tr.removeClass('greyRow');
      }
    });

    var arr = table2.colReorder.order();
    var all = arr;
    if(all == '')
    {
      var col = column;
    }
    else
    {
      var col = all[column];
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    // $.post({
    //   url : "{{ route('toggle-column-display') }}",
    //   dataType : "json",
    //   data : {type:'importing_closed_product_receiving',column_id:col},
    //   beforeSend: function(){
    //     $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    //     $('#loader_modal').modal({
    //         backdrop: 'static',
    //         keyboard: false
    //       });
    //     $("#loader_modal").modal('show');
    //   },
    //   success: function(data){
    //     $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    //     $("#loader_modal").modal('hide');
    //     if(data.success == true)
    //     {
    //       visibility_arr = [];
    //       visibility_arr = data.cols_arr;
    //       // table2.ajax.reload();
    //     }
    //   },
    //   error: function(request, status, error)
    //   {
    //     $("#loader_modal").modal('hide');
    //   }
    // });
    $.post({
      url : "{{ route('toggle-column-display') }}",
      dataType : "json",
      data : {type:'importing_open_product_receiving',column_id:col},
      beforeSend: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(data){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $("#loader_modal").modal('hide');
        if(data.success == true)
        {
          visibility_arr = [];
          visibility_arr = data.cols_arr;
          // table2.ajax.reload();
        }
      },
      error: function(request, status, error)
      {
        $("#loader_modal").modal('hide');
      }
    });
  });

  table2.on( 'column-reorder', function ( e, settings, details ) {
    if(is_child_table == false){
    $(".product_table > tbody > tr").each(function () {
      var tr = $(this);
      var row = table2.row(tr);
      var tableId = 'products-' + tr.attr('id');
      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        tr.removeClass('details');
        tr.removeClass('greyRow');
      }
    });

    reorder_arr = [];
    reorder_arr = table2.colReorder.order();

    $.get({
      url : "{{ route('column-reorder') }}",
      dataType : "json",
      // data : "type=importing_closed_product_receiving&order="+table2.colReorder.order(),
      data : "type=importing_open_product_receiving&order="+table2.colReorder.order(),
      beforeSend: function(){

      },
      success: function(data){
        // $('#loader_modal').modal('show');
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      },
      error: function(request, status, error){
        // $("#loader_modal").modal('hide');
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
    var headerCell = $( table2.column( details.to ).header() );
    table2.columns.adjust();
    headerCell.addClass( 'reordered' );
    }
  });

  function template( d ) {
  return '<table class="table entriestable table-bordered text-center second_table" id="products-'+d.id+'">'+
  '<thead style="display:none";>'+
  '<tr>'+
    '<th></th>'+
    '<th>PO #</th>'+
    '<th>Order<br>Warehouse</th>'+
    '<th>Order#</th>'+
    '<th>{{$global_terminologies["suppliers_product_reference_no"]}}</th>'+
    '<th>Supplier</th>'+
    '<th>{{$global_terminologies["supplier_description"]}}</th>'+
    '<th>{{$global_terminologies["our_reference_number"]}}</th>'+
    '<th>{{$global_terminologies["brand"]}}</th>'+
    '<th>{{$global_terminologies["product_description"]}}</th>'+
    '<th>{{$global_terminologies["type"]}}</th>'+
    '<th>Customer</th>'+
    '<th>Buying <br>Unit</th>'+
    '<th>{{$global_terminologies["qty"]}} <br>Ordered</th>'+
    '<th>{{$global_terminologies["qty"]}}</th>'+
    '<th>{{$global_terminologies["qty"]}} <br>Inv</th>'+
    '<th>{{$global_terminologies["note_two"]}} <br>Inv</th>'+
    '<th>>Gross <br>Weight</th>'+
    '<th>Total <br>Gross <br>Weight</th>'+
    '<th>Unit <br>Extra <br>Cost</th>'+
    '<th>Total <br>Extra <br>Cost</th>'+
    '<th>Unit <br>Extra <br>Tax</th>'+
    '<th>Total <br>Extra <br>Tax</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}} <br>EUR (W/O Vat)</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}} <br>EUR (+Vat)</th>'+
    '<th>Total <br>{{$global_terminologies["purchasing_price"]}} <br>EUR (W/O Vat)</th>'+
    '<th>Total <br>{{$global_terminologies["purchasing_price"]}} <br>EUR (+Vat)</th>'+
    '<th>Currency <br>Conversion <br>Rate</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}}<br>(THB) (W/O Vat)</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}}<br>(THB) (+Vat)</th>'+
    '<th>Discount</th>'+
    '<th>Total <br>Buying <br>Price<br>(THB) (W/O Vat)</th>'+
    '<th>Total <br>Buying <br>Price<br>(THB) +Vat)</th>'+
    '<th>VAT %</th>'+
    '<th>Import <br>Tax <br>(Book)%</th>'+
    '<th>Freight</th>'+
    '<th>Total Freight</th>'+
    '<th>Landing</th>'+
    '<th>Total Landing</th>'+
    '<th>VAT % Tax</th>'+
    '<th>VAT Weighted %</th>'+
    '<th>VAT Actual<br> Tax</th>'+
    '<th>Total VAT Actual<br> Tax</th>'+
    '<th>VAT Actual<br> Tax %</th>'+
    '<th>Book % Tax</th>'+
    '<th>Weighted %</th>'+
    '<th>Actual<br> Tax</th>'+
    '<th>Total Actual<br> Tax</th>'+
    '<th>Actual<br> Tax %</th>'+
    '<th>Custom\'s Line#</th>'+
    '<th>COGS Per Unit</th>'+
    '<th>Total COGS</th>'+
  '</tr>'+
  '</thead>'+
  '</table>';
 }

  $('.product_table tbody').on('click', 'td.details-control', function () {
    is_child_table = false;
    var tr      = $(this).closest('tr');
    var row     = table2.row(tr);
    var tableId = 'products-' + row.data().id;

    if (row.child.isShown())
    {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
      tr.removeClass('details');
      tr.removeClass('greyRow');
      is_child_table = false;
    }
    else
    {
      // Open this row
      is_child_table = true;
      row.child(template(row.data())).show();
      initTable(tableId, row.data());
      tr.addClass('shown');
      tr.addClass('details');
      tr.addClass('greyRow');
      tr.next().find('td').addClass('no-padding bg-gray');
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
  });

  function initTable(tableId, data) {
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    var product_id = data.product_id;
    var group_id   = data.po_group_id;
    var supplier_id = data.supplier_id;
    var detail_table = $('#'+tableId).DataTable({
      serverSide: true,
      responsive: true,
      ordering: false,
      searching: false,
      paging: false,
      processing: true,
      bInfo : false,
      retrieve: true,
      colReorder: {
        realtime: false
      },
      scrollX: true,
      scrollY: true,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      columnDefs: [
        { targets: visibility_arr,  visible: false },
        { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9,10,11] },
        { className: "dt-body-right", "targets": [12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29] },
      ],
      ajax:
      {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url: "{!! route('get-po-group-every-product-details-importing')  !!}",
        data: function(data) { data.product_id = product_id,data.group_id = group_id,data.supplier_id = supplier_id } ,
        method: 'POST',
      },
      fixedColumns: true,
      columns: [
        {
          "className":      '',
          "orderable":      false,
          "searchable":      false,
          "defaultContent": ''
        },
        { data: 'po_no', name: 'po_no' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'order_no', name: 'order_no' },
        { data: 'supplier_ref_no', name: 'supplier_ref_no' },
        { data: 'supplier_ref_name', name: 'supplier_ref_name' },
        { data: 'supplier_description', name: 'supplier_description' },
        { data: 'product_ref_no', name: 'product_ref_no' },
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'type', name: 'type' },
        { data: 'customer', name: 'customer' },
        { data: 'buying_unit', name: 'buying_unit' },
        { data: 'quantity_ordered', name: 'quantity_ordered' },
        { data: 'original_qty', name: 'original_qty' },
        { data: 'quantity_inv', name: 'quantity_inv' },
        { data: 'product_notes', name: 'product_notes' },

        { data: 'pod_unit_gross_weight', name: 'pod_unit_gross_weight' },
        { data: 'pod_total_gross_weight', name: 'pod_total_gross_weight' },
        { data: 'unit_extra_cost', name: 'unit_extra_cost' },
        { data: 'total_extra_cost', name: 'total_extra_cost' },
        { data: 'unit_extra_tax', name: 'unit_extra_tax' },
        { data: 'total_extra_tax', name: 'total_extra_tax' },
        { data: 'buying_price_wo_vat', name: 'buying_price_wo_vat' },
        { data: 'buying_price', name: 'buying_price' },
        { data: 'discount', name: 'discount' },
        { data: 'total_buying_price_wo_vat', name: 'total_buying_price_wo_vat' },
        { data: 'total_buying_price_o', name: 'total_buying_price_o' },     //copied
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
        { data: 'buying_price_in_thb_wo_vat', name: 'buying_price_in_thb_wo_vat' },
        { data: 'unit_price_in_thb', name: 'unit_price_in_thb' },
        { data: 'total_buying_price_in_thb_wo_vat', name: 'total_buying_price_in_thb_wo_vat' },
        { data: 'total_buying_price', name: 'total_buying_price' },
        { data: 'vat_act', name: 'vat_act' },
        { data: 'import_tax_book', name: 'import_tax_book' },

        { data: 'freight', name: 'freight' },
        { data: 'total_freight', name: 'total_freight' },
        { data: 'landing', name: 'landing' },
        { data: 'total_landing', name: 'total_landing' },

        { data: 'vat_percent_tax', name: 'vat_percent_tax' },
        { data: 'vat_weighted_percent', name: 'vat_weighted_percent' },
        { data: 'vat_act_tax', name: 'vat_act_tax' },
        { data: 'total_vat_act_tax', name: 'total_vat_act_tax' },
        { data: 'vat_act_tax_percent', name: 'vat_act_tax_percent' },

        { data: 'book_tax', name: 'book_tax' },
        { data: 'weighted', name: 'weighted' },
        { data: 'actual_tax', name: 'actual_tax' },
        { data: 'total_actual_tax', name: 'total_actual_tax' },
        { data: 'actual_tax_percent', name: 'actual_tax_percent' },
        { data: 'empty_col', name: 'empty_col' , visible: show_custom_line_number},
        { data: 'product_cost', name: 'product_cost'},
        { data: 'total_product_cost', name: 'total_product_cost'},
      ],
      initComplete: function ( settings ) {
        $('#loader_modal').modal('hide');


        @if($display_prods)
          detail_table.colReorder.order(reorder_arr);
          var sort_arr = [{{ $display_prods->display_order }}];
          $.each(sort_arr, function( index, value ) {
            var headerHeight = $('.first_table tbody tr td:nth-child('+value+')').innerWidth();
            headerHeight -=10;
            $('.second_table tbody tr td:nth-child('+value+')').css('width', headerHeight);
            $('.second_table tbody tr td:nth-child('+value+')').css('max-width', headerHeight);
            $('.second_table tbody tr td:nth-child('+value+')').css('overflow', "hidden");
          });
        @else
          for (var i = 1; i <= 41; i++) {
            var headerHeight = $('.first_table tbody tr td:nth-child('+i+')').innerWidth();
            headerHeight -=10;
            $('.second_table tbody tr td:nth-child('+i+')').css('width', headerHeight);
            $('.second_table tbody tr td:nth-child('+i+')').css('max-width', headerHeight);
            $('.second_table tbody tr td:nth-child('+i+')').css('overflow', 'hidden');
            // $('.first_table tbody tr td:nth-child('+i+')').css('width', headerHeight);
          }
        @endif
        is_child_table = false;
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      },
    })
  }

  var order_id = "{{$id}}";

  $('.table-po-group-order-history').DataTable({
    processing: true,
    "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
    },
    ordering: false,
    searching: false,
    "lengthChange": false,
    serverSide: true,
    "scrollX": true,
    "scrollY": '50vh',
    scrollCollapse: true,
    // "bPaginate": false,
    // "bInfo": false,
    dom: 'Blfrtip',
    pageLength: {{50}},
    lengthMenu: [100, 200, 300, 400],
     buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],

    ajax: {
      url: "{!! route('get-pogroupproduct-history') !!}",
      data: function(data) {
        data.order_id = order_id
      },
    },
    columns: [
      // { data: 'checkbox', name: 'checkbox' },
      { data: 'user_name', name: 'user_name' },
      { data: 'created_at', name: 'created_at' },
      { data: 'order_no', name: 'order_no' },
      { data: 'item', name: 'item' },
      // { data: 'name', name: 'name' },
      { data: 'column_name', name: 'column_name' },
      { data: 'old_value', name: 'old_value' },
      { data: 'new_value', name: 'new_value' },
    ],
  });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
    if(e.keyCode == 13)
    {
      table2.search($(this).val()).draw();
    }
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    //$(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
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

        // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocusDetail',function(e) {
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27)
    {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
    }
    if(e.keyCode == 13 || e.which == 0)
    {
      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var pod_id= $(this).data('id');
        var thisPointer = $(this);
        var pogid = $(this).data('pogid');
        var field_name = thisPointer.attr('name');
        var field_value = thisPointer.val();
        // saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id,fieldvalue);
        $(this).data('fieldvalue',thisPointer.val());

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('importing/edit-po-group-product-details-each') }}",
        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&pogid='+pogid+'&old_value='+fieldvalue,
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          $("#loader_modal").modal('hide');
          if(field_name == 'unit_extra_cost' || field_name == 'total_extra_cost' || field_name == 'unit_extra_tax' || field_name == 'total_extra_tax')
          {
            $('.product_table').DataTable().ajax.reload();
            $('.table-po-group-order-history').DataTable().ajax.reload();
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              return true;
          }

            if(data.success == true)
            {
              table2.on('xhr.dt', function() {
                // var position = table2.scroller.page().start;
                table2.one('draw', function() {
                  // table2.row(position).scrollTo(false);
                });
              });
              return true;
            }
        },
        error: function (request, status, error) {
          swal("Something Went Wrong! Contact System Administrator!");
          $('.form-control').removeClass('is-invalid');
          $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'"]').addClass('is-invalid');
          });
        }

      });
      }
    }
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
      url: "{{ url('importing/edit-po-group-product-details') }}",
      dataType: 'json',
      data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&po_group_id='+po_group_id,
      beforeSend: function(){
        // shahsky here
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
      },
      success: function(data)
      {
        $("#loader_modal").modal('hide');
        if(field_name == 'unit_extra_cost' || field_name == 'total_extra_cost' || field_name == 'unit_extra_tax' || field_name == 'total_extra_tax')
        {
          $('.product_table').DataTable().ajax.reload();
        }
        if(field_name == 'currency_conversion_rate')
        {
          $("#loader_modal").modal('show');
          if(data.status==1)
          {
            $('.export-alert-success-recievable').addClass('d-none');
            $('.export-alert-recievable').removeClass('d-none');
            console.log("Calling Function from first part");
            checkStatusForReceivingExport();
          }
          else if(data.status==2)
          {
            $('.export-alert-success-recievable').addClass('d-none');
            $('.export-alert-recievable').removeClass('d-none');
            checkStatusForReceivingExport();
          }
        }
        else
        {
          if(data.gross_weight == true)
          {
            $('#po_group_total_gross_weight').val(data.po_group.po_group_total_gross_weight);
            $(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          if(data.import_tax == true)
          {
            $('#po_group_import_tax_book').val(data.po_group.po_group_import_tax_book);
            $(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          if(data.success == true)
          {
            $(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
        }
      },
      error: function (request, status, error) {
        swal("Something Went Wrong! Contact System Administrator!");
        $('.form-control').removeClass('is-invalid');
        $('.form-control').next().remove();
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'"]').addClass('is-invalid');
        });
      }
    });
  }

  function checkStatusForReceivingExport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-old-data-status')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForReceivingExport();
            }, 5000);
        }
        else if(data.status==0)
        {
          console.log(data);
          $('.export-alert-success-recievable').removeClass('d-none');
          $('.export-alert-recievable').addClass('d-none');
          $('.export-btn-recievable').html('Create New Export');
          $('.export-btn-recievable').prop('disabled',false);
          $(".product_table").DataTable().ajax.reload();
          $('.primary-btn').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success-recievable').addClass('d-none');
          $('.export-alert-recievable').addClass('d-none');
          $('.export-btn-recievable').html('Create New Export');
          $('.export-btn-recievable').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).on('keyup', '.po_group_data', function(e){
    $(this).addClass('active');
  });

  $(document).on('keyup focusout', '.po_group_data', function(e){
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27)
    {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      return false;
    }
    if((e.keyCode == 13 || e.which == 0) && $(this).hasClass('active'))
    {
      $(this).removeClass('active');
      /*The Below Code is necessary because we need to calculate freight and landing which is based on total gross weight*/
      var total_gross_weight = $('#po_group_total_gross_weight').val();
      if(total_gross_weight == 0 && ($(this).attr('name') == 'freight' || $(this).attr('name') == 'landing'))
      {
        swal('Please Enter Total Gross Weight First!');
        $(this).val(fieldvalue);
        return false;
      }

      /*The Below Code is necessary because we need to calculate Actual tax which is based on total Import tax book*/
      var total_gross_weight = $('#po_group_import_tax_book').val();
      if(total_gross_weight == 0 && ($(this).attr('name') == 'tax'))
      {
        swal('Please Enter Import Tax Book First!');
        $(this).val(fieldvalue);
        return false;
      }
      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var gId = "{{$po_group->id}}";
        var attr_name = $(this).attr('name');
        var new_value = $(this).val();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });

        $.ajax({
          method: 'post',
          url: '{{route("save-po-group-data")}}',
          dataType: 'json',
          data:'gId='+gId+'&'+attr_name+'='+new_value,
          success: function (data) {
            if(data.success == true){
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            location.reload();
            }
          }
        });
      }
    }
  });

  $(document).on('click','.export-btn',function(){
    var form=$('#export_receiving_queue_detail');
    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-importing-product-receiving-record')}}",
      data:form_data,
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').html('EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForProducts();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          $('.export-btn').html('EXPORT is being Prepared');
          checkStatusForProducts();
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-importing-receiving-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForProducts();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').html('Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          $('.download-btn').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').html('Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-btn').html('EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          checkStatusForProducts();
        }
      }
    });
  });

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

  $(document).on('click','.download-btn',function(){
    $('.export-alert-success').addClass('d-none');
  });

  var btn_click = false;
    $(document).on('click',function(e){
      if ($(e.target).closest(".dt-button-collection").length === 1) {
          btn_click = true;
      }

      if(btn_click)
      {
        if ($(e.target).closest(".dt-button-collection").length === 0) {
          btn_click = false;
          $('.product_table').DataTable().ajax.reload();
          // alert('clicked outside');
        }
      }
    });


});


</script>
<script>
  function backFunctionality(){
   $('#loader_modal').modal({
     backdrop: 'static',
     keyboard: false
   });
   $('#loader_modal').modal('show');
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('importing/importing-receiving-queue') }}";
       document.location.href = url;
     }
   }
</script>
@stop
