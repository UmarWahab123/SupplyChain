@extends('warehouse.layouts.layout')
@section('title','Receiving Queue')


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
          <li class="breadcrumb-item active">Receiving Queue</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}


<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-2 col-md-6">
        <h4 class="mb-0 fontbold mt-2">Receiving Queue</h4>
      </div>
    </div>
    <div class="row mb-3 headings-color filters_div">

        <div class="col-lg-3 col-md-3" >
          <label><b>From Target Received Date</b></label>
          <div class="form-group">
            <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
          </div>
        </div>

        <div class="col-lg-3 col-md-3" >
          <label><b>To Target Received Date</b></label>
          <div class="form-group">
            <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off">
          </div>
        </div>
        <div class="col-lg-1 p-0" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Dates">
            <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
          </span>
      </div>
      </div>
    </div>
        <div class="col-lg-3 col-md-3" >
          <label><b>Group Status</b></label>
           <div class="form-group">
                <select class="form-control product_receiving">
                    <option value="0" selected="true">Open Product Receiving</option>
                    <option value="1">Closed Product Receiving</option>
                    <option value="2">Cancelled Product Receiving</option>
                </select>
            </div>
        </div>
        <div class="col-lg-1 col-md-3 p-0">
          <div class="input-group-append" style="margin-top: 26px;">
            <!-- <button class="btn recived-button reset-btn" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button> -->

             <span class="common-icons reset-btn" id="reset-btn" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
          </div>
        </div>
      </div>

    <div class="row mb-3 headings-color">
        <div class="col-lg-12 col-md-12">

            <div class="bg-white table-responsive ">
            <div class="p-4">
              <table class="table headings-color entriestable text-center table-bordered product_receiving_table " style="width:100%">
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
                            <th>POs #</th>
                            <th>Supplier <br> Reference Name</th>
                            <!-- <th>Supplier</th> -->
                            <th>@if(!array_key_exists('qty', $global_terminologies)) QTY  @else {{$global_terminologies['qty']}} @endif <br>Ordered
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Net <br>Weight<br> (KG)
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="net_weight">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="net_weight">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Issue <br>Date
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="issue_date">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="issue_date">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th> PO Total<br>(THB)
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_total">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_total">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
                            <th>Target <br>Received<br> Date
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_receive_date">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_receive_date">
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
                            <th>Courier
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="courier">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="courier">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>
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

{{-- PO Number Model --}}
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
                    <tbody id="table_body_po">
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

{{-- Supplier Names Model  --}}
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
                    <tbody id="table_body_supplier">
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
                  }
                  $html_string .= ' --}}
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

 // Customer Sorting Code Here
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
  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var d = new Date();
  var today_date = d.getDate() + "/" + (d.getMonth()+1) + "/" + d.getFullYear();
  $("#from_date").val(today_date);

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var tomorrow_dae = (d.getDate()+1) + "/" + (d.getMonth()+1) + "/" + d.getFullYear();
  $("#to_date").val(tomorrow_dae);

$(function(e){

  var table2 = $('.product_receiving_table').DataTable({
    "sPaginationType": "listbox",
   processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  colReorder: {
    realtime: false
  },
  ordering: false,
  searching: false,
  serverSide: true,
  pageLength: {{100}},
  scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    dom: 'Blfrtip',
  "columnDefs": [
    { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,8 ] },
    { className: "dt-body-right", "targets": [7] }
  ],
  lengthMenu: [ 100, 200, 300, 400],
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
          url:"{!! route('get-warehouse-receiving-po-groups') !!}",
          data: function(data) { data.dosortby = $('.product_receiving option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(), data.sort_order = order, data.column_name = column_name } ,
        },

  columns: [
      // { data: 'action', name: 'action' },
      { data: 'id', name: 'id' },
      { data: 'po_number', name: 'po_number' },
      { data: 'supplier_ref_no', name: 'supplier_ref_no' },
      // { data: 'supplier', name: 'supplier' },
      { data: 'quantity', name: 'quantity' },
      { data: 'net_weight', name: 'net_weight' },
      { data: 'issue_date', name: 'issue_date' },
      { data: 'po_total', name: 'po_total' },
      { data: 'target_receive_date', name: 'target_receive_date' },
      { data: 'warehouse', name: 'warehouse' },
      { data: 'courier', name: 'courier' },
  ],
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });

      @if(@$display_prods)
        table2.colReorder.order( [{{@$display_prods->display_order}}]);
      @endif
    },
     drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
});

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

    var arr = table2.colReorder.order();
    // var all = arr.split(',');
    var all = arr;
    if(all == ''){
      var col = column;
    }else{
      var col = all[column];
    }
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.post({
      url : "{{ route('toggle-column-display') }}",
      dataType : "json",
      data : {type:'receiving_queue',column_id:col},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        if(data.success == true){
          /*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
          // table2.ajax.reload();
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  table2.on( 'column-reorder', function ( e, settings, details ) {
       $.get({
         url : "{{ route('column-reorder') }}",
         dataType : "json",
         data : "type=receiving_queue&order="+table2.colReorder.order(),
         beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
         },
         success: function(data){
          $('#loader_modal').modal('show');
         },
         error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
       });
           //console.log(table.colReorder.order());
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

  $(document).on('click', '.po_list_btn', function() {
    // console.log('hello');
     var id = $(this).data('id');
    $.ajax({
      method: "get",
        data:{id:id},
        url:"{{ route('view-po-numbers-warehouse') }}",
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
        url:"{{ route('view-supplier_names_warehouse') }}",
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
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    // $("#loader_modal").modal('show');
    // $('.product_receiving_table').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#to_date').change(function() {
    // $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
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



});
</script>
@stop

