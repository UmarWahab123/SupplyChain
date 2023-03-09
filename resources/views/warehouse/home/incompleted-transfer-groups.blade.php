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
        <li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 10)
          <li class="breadcrumb-item"><a href="{{route('roles-list')}}">Home</a></li>
        @endif
          <li class="breadcrumb-item active">Transfer Document Receiving Queue</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}


<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-4 col-md-6">
        <h4 class="mb-0 fontbold mt-2">Transfer Document Receiving Queue</h4>
      </div>
    </div>

    <div class="row mb-3 headings-color filters_div">
      <div class="col-lg-2 col-md-2 d-md-none">

      </div>
        <div class="col-lg-3 col-md-3" >
          <div class="form-group">
            <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
          </div>
        </div>

        <div class="col-lg-3 col-md-3" >
          <div class="form-group">
            <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off">
          </div>
        </div>
        <div class="col-lg-1 p-0" style="">
      <div class="form-group">
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date common-icons" title="Apply Dates">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>
        <div class="col-lg-3 col-md-3" >
           <div class="form-group">
                <select class="form-control product_receiving">
                    <option value="0" selected="true">Open Product Receiving</option>
                    <option value="1">Closed Product Receiving</option>
                </select>
            </div>
        </div>
        <div class="col-lg-1 col-md-3 p-0">
          <div class="input-group-append">
            <!-- <button class="btn recived-button reset-btn" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>   -->
            <span class="reset-btn common-icons" title="Reset">
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
                            <th>TD's#</th>
                            <th>@if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif</th>
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
                            {{--<th> PO Total<br>(THB)</th>
                            <th>Target <br>Received<br> Date
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_receive_date">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_receive_date">
                                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                            </th>--}}
                            <th>To Warehouse
                              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="to_warehouse">
                                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                              </span>
                              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="to_warehouse">
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

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

$(function(e){

  var table2 = $('.product_receiving_table').DataTable({
    "sPaginationType": "listbox",
   processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  serverSide: true,
  pageLength: {{100}},
  scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
  "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6 ] },
    // { className: "dt-body-right", "targets": [7] }
  ],
  lengthMenu: [ 100, 200, 300, 400],
  ajax:{
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
          url:"{!! route('get-warehouse-incompleted-td-groups') !!}",
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
      // { data: 'po_total', name: 'po_total' },
      // { data: 'target_receive_date', name: 'target_receive_date' },
      { data: 'warehouse', name: 'warehouse' },
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

  $('.product_receiving').on('change', function(e){
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#from_date').change(function() {
    // $('.product_receiving_table').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#to_date').change(function() {
    // $('.product_receiving_table').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('click','.apply_date',function(){
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.reset-btn').on('click',function(){
    $('#from_date').val('').change();
    $('#to_date').val('').change();
    $('.product_receiving').val('0').change();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });



});
</script>
@stop

