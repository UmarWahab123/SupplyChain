@extends('importing.layouts.layout')
@section('title','Dashboard')


@section('content')
<style type="text/css">
  .inputDoubleClick{
  font-style: italic;
  font-weight: bold;
}
</style>
{{-- Content Start from here --}}

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
          <li class="breadcrumb-item active">Product Receiving Dashboard</li>
      </ol>
  </div>
</div>

<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class=" col-xl-4 col-lg-6 col-md-8">
        <h4 class="mb-0 fontbold mt-2">Product Receiving Dashboard</h4>
      </div>
    </div>
    <div class="row mb-3 headings-color filters_div">
      <div class="col-xl-2 col-lg-1 col-md-2 d-md-none">

      </div>
        <div class="col-xl-3 col-lg-3 col-md-3">
          <label><b>From Target Received Date</b></label>
          <div class="form-group">
            <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
          </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-3">
          <label><b>To Target Received Date</b></label>
          <div class="form-group">
            <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off">
          </div>
        </div>
        <div class="col-1 p-0" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
         <span class="apply_date common-icons mr-4" title="Apply Dates" id="apply_filter">
            <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
          </span>
      </div>
      </div>
    </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-md-3">
          <label><b>Group Status</b></label>
           <div class="form-group">
                <select class="form-control product_receiving">
                    <option value="0" selected="true">Open Product Receiving</option>
                    <option value="1">Closed Product Receiving</option>
                    <option value="3">From Bonded Warehouse Receiving</option>
                    <option value="2">Cancelled Product Receiving</option>
                </select>
            </div>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 p-0">
          <div class="input-group-append" style="margin-top: 26px;">
           <!--  <button class="btn recived-button reset-btn" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button> -->

           <span class="common-icons reset-btn" id="reset-btn" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
          </div>
        </div>
      </div>
    <div class="row mb-3 headings-color">

        <div class="col-lg-12 col-md-12">

            <div class="bg-white table-responsive">
            <div class="p-4">
              <table class="table headings-color entriestable text-center table-bordered product_receiving_table" style="width:100%">
                    <thead class="sales-coordinator-thead table-bordered">
                        <tr>
                            {{-- <th>Action</th> --}}
                            <th>Group#
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="group_no">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="group_no">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>POs#</th>
                            <!-- <th>B/L</th> -->
                            <th>B/L or AWB
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="bl_awb">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="bl_awb">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <!-- <th>AWB</th> -->
                            <th>Courier
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="courier">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="courier">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Supplier</th>
                            <!-- <th>Supplier Ref#</th> -->
                            <th>Purchase <br>{{$global_terminologies['qty']}}
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Net <br>Weight (KG)
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="weight">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="weight">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Issue <br> Date
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="issue_date">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="issue_date">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th> PO <br>Total(THB)
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_total">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_total">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Target <br> Received <br> Date
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_receive_date">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_receive_date">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Tax
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="tax">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="tax">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>{{$global_terminologies['freight_per_billed_unit']}}
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="freight">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="freight">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>{{$global_terminologies['landing_per_billed_unit']}}
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="landing">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="landing">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Warehouse
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="warehouse">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="warehouse">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>{{$global_terminologies['note_two']}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader Modal -->
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

{{-- PO's Model --}}
  <div class="modal fade" id="poNumberModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">PO Numbers</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="table_body_po_div" style="overflow: auto;">
                <table class="bordered" style="width:100%;">
                      <thead style="border:1px solid #eee;text-align:center;">
                          <tr><th>S.No</th><th>PO No.</th></tr>
                      </thead>
                      <tbody id='table_body_po'>
                      {{-- foreach ($po_group_detail as $p_g_d) {
                          $link = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $p_g_d->purchase_order->id]).'" title="View Detail"><b>'.$p_g_d->purchase_order->ref_id.'</b></a>';
              $html_string .= '<tr><td>'.$i.'</td><td>'.@ $link.'</td></tr>';
              $i++;
          }
            $html_string .= ' --}}
                      </tbody>
                </table>

            </div>
        </div>
      </div>
    </div>
  </div>

{{-- Supplier Name Model --}}
  <div class="modal fade" id="SupplierModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Supplier(s)</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div id="table_body_supplier_div" style="overflow: auto;">
                <table class="bordered" style="width:100%;">
                      <thead style="border:1px solid #eee;text-align:center;">
                          <tr><th>S.No</th><th>Supplier #</th><th>Supplier Reference Name</th></tr>
                      </thead>
                      <tbody id='table_body_supplier'>
                      {{-- foreach ($po_group_detail as $p_g_d)
                      {
                          if($p_g_d->purchase_order->supplier_id != null)
                          {
                              $ref_no = $p_g_d->purchase_order->PoSupplier->reference_number;
                              $name = $p_g_d->purchase_order->PoSupplier->reference_name;
                          }
                          else
                          {
                              $ref_no = $p_g_d->purchase_order->PoWarehouse->location_code;
                              $name = $p_g_d->purchase_order->PoWarehouse->warehouse_title;
                          }
                          $html_string .= '<tr><td>'.$i.'</td><td>'.@$ref_no.'</td><td>'.@$name.'</td></tr>';
                          $i++;
                      } --}}
                      </tbody>
                </table>
            </div>
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

  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.product_receiving_table').DataTable().ajax.reload();

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

  $("#from_date, #to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });
// onchange statuses code starts here
  $('.product_receiving').on('change', function(e){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#from_date').change(function() {
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $("#loader_modal").modal('show');
    // $('.product_receiving_table').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#to_date').change(function() {
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $("#loader_modal").modal('show');
    // $('.product_receiving_table').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('click','.apply_date',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.reset-btn').on('click',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('#from_date').val('');
    $('#to_date').val('');
    $('.product_receiving').val('0').change();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

$(function(e){
  var table2 = $('.product_receiving_table').DataTable({
    "sPaginationType": "listbox",
   processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  serverSide: true,
  scrollX: true,
  scrollY : '90vh',
    scrollCollapse: true,
  dom: 'Blfrtip',
  pageLength: {{50}},
  lengthMenu: [ 50, 100, 150, 200],
  colReorder: {
    realtime: false
  },
  columnDefs: [
    { targets: [{{$hidden_by_default}}], visible: false },
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,9,13,14 ] },
    { className: "dt-body-right", "targets": [8,10,11,12] }
  ],
  buttons: [
    {
      extend: 'colvis',
      columns: ':not(.noVis)',
    }
  ],
  ajax:{
     beforeSend: function(){
       $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
    url:"{!! route('get-importing-receiving-po-groups') !!}",
    data: function(data) { data.dosortby = $('.product_receiving option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(), data.sort_order = order, data.column_name= column_name } ,
  },

  columns: [
      // { data: 'action', name: 'action' },
      { data: 'id', name: 'id' },
      { data: 'po_number', name: 'po_number' },
      // { data: 'bill_of_lading', name: 'bill_of_lading' },
      { data: 'bill_of_landing_or_airway_bill', name: 'bill_of_landing_or_airway_bill' },
      // { data: 'airway_bill', name: 'airway_bill' },
      { data: 'courier', name: 'courier' },
      { data: 'supplier_ref_no', name: 'supplier_ref_no' },
      { data: 'quantity', name: 'quantity' },
      { data: 'net_weight', name: 'net_weight' },
      { data: 'issue_date', name: 'issue_date' },
      { data: 'po_total', name: 'po_total' },
      { data: 'target_receive_date', name: 'target_receive_date' },
      { data: 'tax', name: 'tax' },
      { data: 'freight', name: 'freight' },
      { data: 'landing', name: 'landing' },
      { data: 'warehouse', name: 'warehouse' },
      { data: 'note', name: 'note' },
  ],
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
    },

    drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
});

   $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
     $('#tableSearchField').val(searchField);
     count=searchField.length;
      if(e.keyCode == 13) {

         table2.search($(this).val()).draw();
        return;
      }else if(count>0){
        if(e.type == 'focusout'){
           table2.search(this.value).draw();
              return;
                   }
        }else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
        }
 });

  $(document).on("dblclick",".inputDoubleClick",function(){
    // alert(this);
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

    // dropdown double click editable code start here
  $(document).on(' keyup change', 'select.select-common', function(e){
    if (e.keyCode === 27 && $(this).hasClass('active')) {
    //alert('hi');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
    else{
      var new_value = $("option:selected", this).html();
      var group_id= $(this).data('id');
      var thisPointer = $(this);
      saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),group_id);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
    }
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('click', '.po_list_btn', function() {
    // console.log('hello');
     var id = $(this).data('id');
    $.ajax({
      method: "get",
        data:{id:id},
        url:"{{ route('view-po-numbers') }}",
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $('#table_body_po').html(data);
          if ($('#table_body_po > tr').length > 20) {
            $('#table_body_po_div').css('height','500px');
          }
          else{
            $('#table_body_po_div').css('height', '');
        }
          $('#poNumberModal').modal();
          $('#loader_modal').modal('hide');
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
    });
  });

  $(document).on('click', '.supplier_reference_name', function() {
    // console.log('hello');
     var id = $(this).data('id');
    $.ajax({
      method: "get",
        data:{id:id},
        url:"{{ route('view-supplier_name') }}",
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $('#table_body_supplier').html(data);
          if ($('#table_body_supplier > tr').length > 20) {
            $('#table_body_supplier_div').css('height','500px');
          }
          else{
            $('#table_body_supplier_div').css('height', '');
        }
          $('#SupplierModel').modal();
          $('#loader_modal').modal('hide');
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
    });
  });

  // to make that field on its orignal state
  $(document).on("keyup focusout",".fieldFocus",function(e) {
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
      if($(this).val().length < 1)
      {
        // swal("Must fill this filed accurately!");
        return false;
      }
     if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active'))
      {
        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var group_id= $(this).data('id');
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).prev().html(new_value);
          }
          saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),group_id);
        }
      }
  });

});

  function saveSupData(thisPointer,field_name,field_value,group_id){
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('importing/edit-po-group') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
      data: 'group_id='+group_id+'&'+field_name+'='+field_value,
      beforeSend: function(){
        $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal2").modal('show');
      },
      success: function(data)
      {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

          // if(field_name == "main_tags")
          // {
          //   location.reload();
          // }
          return true;
        }
      },
       error: function (request, status, error) {
        swal('Something Went Wrong! Contact Administrator!');
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
</script>
@stop

