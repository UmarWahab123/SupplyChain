@extends('warehouse.layouts.layout')
@section('title','Dashboard')
@section('content')

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
      </ol>
  </div>
</div>
<!-- Right Content Start Here -->
<div class="right-content">

  <div class="row mb-2">
    <div class="col-lg-12" style="font-size:0.9rem">
      <div class="row headings-color">
        @if(Auth::user()->role_id!=2)
        <div class="col-lg col-md-4 pb-3 ">
          <a class="my-pos" data-id="all" title="All Pick Instructions" style="cursor: pointer; ">
            <div class="bg-white box1 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$all}}</h6>
                  <span class="span-color">All Pick Instructions</span>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-lg col-md-4 pb-3 ">
          <a class="my-pos" data-id='UpComming' title="UpComing Pick Instructions" style="cursor: pointer; ">
            <div class="bg-white box2 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$upcomming}}</h6>
                  <span class="span-color">Upcoming Pick Instructions</span>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-lg col-md-4 pb-3 ">
          <a class="my-pos" data-id='11' title="Completed Pick Instructions" style="cursor: pointer; ">
            <div class="bg-white box3 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$completed}}</h6>
                  <span class="span-color">{{$page_status[2]}} Pick Instructions</span>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-lg col-md-4 pb-3">
          <a class="my-pos" data-id='10'title="Orders In Waiting To Pick" style="cursor: pointer;">
            <div class="bg-white box4 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$delivery}}</h6>
                  <span class="span-color">Orders In {{$page_status[1]}}</span>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-lg col-md-4 pb-3 ">
          <a class="my-pos" data-id="9" title="Orders In Importing" style="cursor: pointer;">
            <div class="bg-white box5 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$importing}}</h6>
                  <span class="span-color">Orders In {{$page_status[0]}}</span>
                </div>
              </div>
            </div>
          </a>
        </div>
        @endif
        <div class="col-lg col-md-4 pb-3 ">
          <a class="my-pos pick-purchasing" data-id="21" title="Waiting Transfer" style="cursor: pointer;">
            <div class="bg-white box5 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$waitingTransferDoc}}</h6>
                  <span class="span-color">{{$page_status_td[0]}}</span>
                </div>
              </div>
            </div>
          </a>
        </div>

        <div class="col-lg col-md-4 pb-3 ">
          <a class="my-pos" data-id="22" title="Complete Transfer" style="cursor: pointer;">
            <div class="bg-white box5 py-4 px-3 h-100 dashboard-boxes-shadow">
              <div class="d-flex align-items-center justify-content-center">
                <img src="{{asset('public/img/client.jpg')}}" class="img-fluid">
                <div class="title pl-lg-3 pl-2">
                  <h6 class="mb-0 number-size text-center">{{$completeTransferDoc}}</h6>
                  <span class="span-color">{{$page_status_td[1]}}</span>
                </div>
              </div>
            </div>
          </a>
        </div>

      </div>
    </div>
  </div>

<div class="row headings-color">
  <div class="col-lg-3 col-md-4">
    <h4>Pick Instruction</h4>
  </div>
</div>

<div class="row headings-color filters_div">

<div class="col-lg-2 col-md-2 status_div" >
  <select class="font-weight-bold  form-control orders-status" id="ord-statuses" name="order_status" >
    <option value="" disabled="" >Choose Status</option>
    <option value="UpComming" selected>UpComing</option>
    <option value="all">All</option>

    @foreach($page_status_dropdown as $status)
      <option value="{{$status->id}}">{{$status->title}}</option>
    @endforeach
    <!-- <option value="8">Purchasing</option>
    <option value="7">Selecting Vendors</option> -->
    <option value="18">Cancelled</option>
  </select>

  <select class="font-weight-bold  form-control orders-status new-status-class d-none" id="td-statuses" name="order_status" >
    <option value="" disabled="" >Choose Status</option>
    @foreach($page_status_td_dropdown as $status)
      <option value="{{$status->id}}">{{$status->title}}</option>
    @endforeach
    <!-- <option value="21" selected>Waiting Transfer</option>
    <option value="22">Complete Transfer</option> -->
    <option value="all-transfer">All</option>
  </select>
</div>
<div class="col-lg-2 col-md-2 customer-and-salesperson-divs">
      <div class="form-group">
        <select class="font-weight-bold  form-control form-control js-states state-tags selecting-customer-salesperson" name="salesperson" id="salesperson_id" required="true">
        <option value="" >Choose Sales Person </option>
    @foreach($sales_persons as $sales_person)
      <option value="{{$sales_person->id}}">{{$sales_person->name}}</option>
    @endforeach
        </select>
      </div>
</div>
<div class="col-lg-2 col-md-2 customer-and-salesperson-divs" >
      <div class="form-group">
        <select class="font-weight-bold  form-control form-control js-states state-tags selecting-customer-salesperson choose-customer-filer" name="customer" id="customer_id" required="true">
        <option value="" >Choose Customer </option>
    @foreach($referenceNameWithId as $nameId)
      <option value="{{$nameId['id']}}">{{$nameId['reference_name']}}</option>
    @endforeach
        </select>
      </div>
</div>



<div class="col-lg-2 col-md-2" id="from_date_div">
  <div class="form-group">
    <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
  </div>
</div>

<div class="col-lg-2 col-md-2" id="to_date_div">
  <div class="form-group">
    <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off">
  </div>
</div>
<div class="col-lg-2 col-md-2 p-0" style="">
      <div class="form-group">
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Dates">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>

        <span class="reset-btn common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>
<!-- <div class="col-lg-1 col-md-1">
  <div class="input-group-append ml-3">
    <button class="btn recived-button reset-btn" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>
  </div>
</div> -->
</div>

<div class="row entriestable-row pr-0 quotation" id="quotation">
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-quotation text-center">
        <thead>
          <tr>
            {{-- <th>Action</th> --}}
            <th>Order#
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Sales <br> Person
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sales_person">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sales_person">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
            <th>Customer #
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Customer<br> Name
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>
            @if(!array_key_exists('reference_name', $global_terminologies)) Reference<br> Name  @else {{$global_terminologies['reference_name']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_reference_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_reference_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th> @if(!array_key_exists('delivery_request_date', $global_terminologies)) Delivery <br>Request<br> Date  @else {{$global_terminologies['delivery_request_date']}} @endif
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Date <br>Purchase
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchase_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchase_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
            <th>Remark
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="remark">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="remark">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Comment To Warehouse
            <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="comment">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="comment">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Order <br>Total(THB)
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Status
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="status">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="status">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Printed
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="printed">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="printed">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<div class="row entriestable-row pr-0 transfer-doc" id="transfer-doc" style="width: 100%;">
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-transfer-doc text-center" style="width: 100%;">
        <thead>
          <tr>
            {{-- <th>Action</th> --}}
            <th>TD #
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="td">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="td">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            {{--<th>Sales Person</th>--}}
            <th>Customer #
            <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Supply To
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supply_to">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supply_to">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Date Confirm
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="confirm_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="confirm_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Transfer Date
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="transfer_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="transfer_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            {{--<th>Order Total(THB)</th>
            <th>Delivery Request Date</th>--}}
            <th>Status</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

</div>

<div class="modal" id="loader_modal" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
    <div class="modal-body">
      <h3 style="text-align:center;">Please wait</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
    </div>
    </div>
  </div>
</div>

<!-- main content end here -->
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

    $('.table-quotation').DataTable().ajax.reload();
    $('.table-transfer-doc').DataTable().ajax.reload();

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
  // by default this draft Purchase order table is d-none
  document.getElementById('transfer-doc').style.display = "none";

  var table2 = $('.table-quotation').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    colReorder: {
          realtime: false,
        },
    "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu:[100, 200, 300, 400],
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,10] },
      { className: "dt-body-right", "targets": [9] }
    ],
    scrollX: true,
    ajax:
    {
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      url: "{!! route('get-draft-invoices-warehouse') !!}",
      data: function(data) { data.orders_status = $('.orders-status option:selected').val() ,data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(), data.customer_id = $('#customer_id').val(),data.salesperson_id = $('#salesperson_id').val(),data.sortbyparam = column_name,
          data.sortbyvalue = order } ,
    },
    columns: [
      // { data: 'action1', name: 'action1' },
      { data: 'ref_id1', name: 'ref_id1' },
      { data: 'user_id1', name: 'user_id1' },
      { data: 'customer_ref_no1', name: 'customer_ref_no1' },
      { data: 'customer_name', name: 'customer_name' },
      { data: 'customer_reference_name1', name: 'customer_reference_name1' },
      { data: 'delivery_request_date1', name: 'delivery_request_date1' },
      { data: 'invoice_date1', name: 'invoice_date1' },
      { data: 'comment_to_customer', name: 'comment_to_customer' },
      { data: 'comment_to_warehouse', name: 'comment_to_warehouse' },
      { data: 'total_amount1', name: 'total_amount1' },
      { data: 'status1', name: 'status1' },
      { data: 'printed', name: 'printed' },
    ],
    initComplete: function () {
    // Enable THEAD scroll bars
        @if($display_my_quotation)
            table2.colReorder.order( [{{$display_my_quotation->display_order}}]);
        @endif
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

    table2.on( 'column-reorder', function ( e, settings, details ) {
      $.get({
        url : "{{ route('column-reorder') }}",
        dataType : "json",
        data : "type=pick_instruction_dashboard&order="+table2.colReorder.order(),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('hide');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
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

  var table3 = $('.table-transfer-doc').DataTable({
    "sPaginationType": "listbox",
    processing: true,
    colReorder: {
          realtime: false,
    },
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    searching:false,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu:[100, 200, 300, 400],
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 1,2,3,4,5] },
    ],
    scrollX: true,
    ajax:
    {
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      url: "{!! route('get-transfer-document') !!}",
      data: function(data) { data.orders_status = $('.orders-status option:selected').val() ,data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),
        data.sortbyparam = column_name,
          data.sortbyvalue = order
      } ,
    },
    columns: [
      // { data: 'action', name: 'action' },
      { data: 'ref_id', name: 'ref_id' },
      { data: 'customer_ref_no', name: 'customer_ref_no' },
      { data: 'customer', name: 'customer' },
      { data: 'invoice_date', name: 'invoice_date' },
      { data: 'transfer_date', name: 'transfer_date' },
      { data: 'status', name: 'status' },
    ],
    initComplete: function () {
      @if($display_my_transfer)
            table3.colReorder.order( [{{$display_my_transfer->display_order}}]);
      @endif
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

    table3.on( 'column-reorder', function ( e, settings, details ) {
      $.get({
        url : "{{ route('column-reorder') }}",
        dataType : "json",
        data : "type=pick_instruction_dashboard_transfer&order="+table3.colReorder.order(),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('hide');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
        },
        error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
            });
             //console.log(table.colReorder.order());
         table3.button(0).remove();
         table3.button().add(0,
         {
           extend: 'colvis',
           autoClose: false,
           fade: 0,
           columns: ':not(.noVis)',
           colVis: { showAll: "Show all" }
         });

         // table2.ajax.reload();
         var headerCell = $( table3.column( details.to ).header() );
         headerCell.addClass( 'reordered' );
      });

  $(document).on('change','.orders-status',function(){
    var selected = $(this).val();
    console.log(selected);
    if($('.orders-status option:selected').val() == 21 || $('.orders-status option:selected').val() == 22 || $('.orders-status option:selected').val() == "all-transfer")
    {
      $('#loader_modal').modal({
          backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('#customer_id').val('');
      $('#salesperson_id').val('');
      $('.customer-and-salesperson-divs').addClass('d-none');
      $('#from_date_div').removeClass('col-md-2 col-lg-2');
      $('#from_date_div').addClass('col-md-3 col-lg-3');
      $('#to_date_div').removeClass('col-md-2 col-lg-2');
      $('#to_date_div').addClass('col-md-3 col-lg-3');
      $('.status_div').removeClass('col-md-2 col-lg-2');
      $('.status_div').addClass('col-md-3 col-lg-3');

      $('.table-transfer-doc').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "none";
      document.getElementById('transfer-doc').style.display = "block";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
    else
    {
      // alert('here');
      $('#loader_modal').modal({
          backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.customer-and-salesperson-divs').removeClass('d-none');
      $('#to_date_div').removeClass('col-md-3 col-lg-3');
      $('#to_date_div').addClass('col-md-2 col-lg-2');
      $('#from_date_div').removeClass('col-md-3 col-lg-3');
      $('#from_date_div').addClass('col-md-2 col-lg-2');
      $('.status_div').removeClass('col-md-3 col-lg-3');
      $('.status_div').addClass('col-md-2 col-lg-2');

      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
      document.getElementById('transfer-doc').style.display = "none";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
  });

  $('#from_date').change(function() {
    // if($('.orders-status option:selected').val() == 21 || $('.orders-status option:selected').val() == 22 || $('.orders-status option:selected').val() == "all-transfer")
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //     keyboard: false
    //   });
    //   $("#loader_modal").modal('show');
    //   $('.table-transfer-doc').DataTable().ajax.reload();
    //   document.getElementById('quotation').style.display = "none";
    //   document.getElementById('transfer-doc').style.display = "block";
    //   $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    //   // $('#customer_select_box_div').addClass('d-none');
    //   // $('#to_date_div').removeClass('col-md-2 col-lg-2');
    //   // $('#to_date_div').addClass('col-md-3 col-lg-3');
    //   // $('#from_date_div').removeClass('col-md-2 col-lg-2');
    //   // $('#from_date_div').addClass('col-md-3 col-lg-3');

    // }
    // else
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //     keyboard: false
    //   });
    //   $("#loader_modal").modal('show');
    //   $('.table-quotation').DataTable().ajax.reload();
    //   document.getElementById('quotation').style.display = "block";
    //   document.getElementById('transfer-doc').style.display = "none";
    //   $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    //   $('#customer_select_box_div').removeClass('d-none');

    // }
  });

  $('#to_date').change(function() {
    // if($('.orders-status option:selected').val() == 21 || $('.orders-status option:selected').val() == 22 || $('.orders-status option:selected').val() == "all-transfer")
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //     keyboard: false
    //   });
    //   $("#loader_modal").modal('show');
    //   $('.table-transfer-doc').DataTable().ajax.reload();
    //   document.getElementById('quotation').style.display = "none";
    //   document.getElementById('transfer-doc').style.display = "block";
    //   $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    // }
    // else
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //     keyboard: false
    //   });
    //   $("#loader_modal").modal('show');
    //   $('.table-quotation').DataTable().ajax.reload();
    //   document.getElementById('quotation').style.display = "block";
    //   document.getElementById('transfer-doc').style.display = "none";
    //   $($.fn.dataTable.tables(true)).DataTable().columns.adjust();


    // }
  });

  $(document).on('click','.apply_date',function(){
   if($('.orders-status option:selected').val() == 21 || $('.orders-status option:selected').val() == 22 || $('.orders-status option:selected').val() == "all-transfer")
    {
      $('#loader_modal').modal({
          backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.table-transfer-doc').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "none";
      document.getElementById('transfer-doc').style.display = "block";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
    else
    {
      $('#loader_modal').modal({
          backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
      document.getElementById('transfer-doc').style.display = "none";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();


    }
  });

  //Get Orders Against Customer Reference Name

  $('#customer_id').on('change', function(e){
    //alert($('#customerReferecneName').val());
      $("#loader_modal").modal('show');
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
      document.getElementById('transfer-doc').style.display = "none";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });
  $('#salesperson_id').on('change', function(e){
    //alert($('#customerReferecneName').val());
      $("#loader_modal").modal('show');
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
      document.getElementById('transfer-doc').style.display = "none";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

    $(document).ready(function() {
       $('.selecting-customer-salesperson').select2();
    });

  $('.reset-btn').on('click',function(){
    $('#loader_modal').modal({
        backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('#from_date').val('');
    $('#to_date').val('');
    $('#ord-statuses').removeClass('d-none');
    $('#td-statuses').addClass('d-none');
    $('.orders-status').val('UpComming');
    $('#customer_id').val('');
    $('#salesperson_id').val('');
    $(".state-tags").select2("", "");
    $('.table-quotation').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });


  $('.my-pos').on('click',function(){
    var option = $(this).data('id');

    if(option == 21 || option == 22)
    {
      $('#loader_modal').modal({
          backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.orders-status').val(option);
      $('#td-statuses').removeClass('d-none');
      $('#ord-statuses').addClass('d-none');
      $('.customer-and-salesperson-divs').addClass('d-none');
      $('#from_date_div').removeClass('col-md-2 col-lg-2');
      $('#from_date_div').addClass('col-md-3 col-lg-3');
      $('#to_date_div').removeClass('col-md-2 col-lg-2');
      $('#to_date_div').addClass('col-md-3 col-lg-3');
      $('.status_div').removeClass('col-md-2 col-lg-2');
      $('.status_div').addClass('col-md-3 col-lg-3');

      $('.table-transfer-doc').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "none";
      document.getElementById('transfer-doc').style.display = "block";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
    else
    {
      $('#loader_modal').modal({
          backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.orders-status').val(option);
      $('#ord-statuses').removeClass('d-none');
      $('#td-statuses').addClass('d-none');

      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
      document.getElementById('transfer-doc').style.display = "none";
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
  });

  @if(session('td_status') == 22)
    $('.orders-status').val(22).change();
    $('#td-statuses').removeClass('d-none');
    $('#ord-statuses').addClass('d-none');
  @endif

  setTimeout(function(){ {{Session::forget('td_status')}} }, 50);

});

@if(Auth::user()->role_id==2)
$(document).ready(function(){
    $('.pick-purchasing').click();
})
@endif
</script>
@stop
