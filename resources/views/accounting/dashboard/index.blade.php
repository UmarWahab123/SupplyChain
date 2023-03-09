@extends('accounting.layouts.layout')
@section('title','Dashboard')


@section('content')
<style type="text/css">
  .select2-results__option
{
  display: block !important;
  overflow:  hidden !important;
  white-space: nowrap !important;
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
          <li class="breadcrumb-item active">Accounting Dashboard</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<!-- Right Content Start Here -->
<div class="right-contentIn">
	<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

<div class="col-lg-12">
<!-- 1st four box row start -->
<div class="row mb-3">

@include('accounting.layouts.dashboard-boxes')

</div>
<!-- first four box row end-->

</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>

<div class="row mb-3 headings-color">

<div class="col-lg-9 headings-color">
  <h4>Credit Notes</h4>
</div>

<div class="col-lg-12 headings-color">
  <form id="form_id" class="filters_div">
  <div class="row">

    <div class="col" id="quotation-1">
      <div class="form-group">
        <select class="form-control selecting-tables sort-by-value">
            <option value="25" selected="true">-- Credit Notes --</option>
           <!--  <option value="6">@if(!array_key_exists('waiting_confrimation', $global_terminologies)) Waiting Confirmation @else {{$global_terminologies['waiting_confrimation']}} @endif</option>
            <option value="5">Complete</option> -->
             @foreach(@$statuses as $status)
           <option value="{{@$status->id}}">{{@$status->title}}</option>
           @endforeach
        </select>
        <select class="form-control selecting-tables supplier_credit_statuses_Select d-none">
            <option value="25" selected="true">-- Credit Notes --</option>
           <!--  <option value="6">@if(!array_key_exists('waiting_confrimation', $global_terminologies)) Waiting Confirmation @else {{$global_terminologies['waiting_confrimation']}} @endif</option>
            <option value="5">Complete</option> -->
             @foreach(@$statuses as $status)
           <option value="{{@$status->id}}">{{@$status->title}}</option>
           @endforeach
        </select>
      </div>
    </div>

     <!-- <div class="col-lg col-md-6" id="Choose-customer">
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer" name="customer" required="true">
            <option value="">Choose Customer</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{@$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div> -->

    <div class="col-2" id="credit_person">
        <div class="form-group">
          <select class="font-weight-bold form-control-lg form-control js-states state-tags credit_person_select" name="credit_person" required="true">
              <option value="">Choose Credit Person</option>
              <option value="1" @if (app('request') == null || app('request')->input('type') != 'supplier') selected="selected" @endif >Customer</option>
              <option value="2" @if (app('request')->input('type') == 'supplier') selected="selected" @endif>Supplier</option>

          </select>
        </div>
      </div>

    @if (app('request') == null || app('request')->input('type') != 'supplier')
    <div class="col-2" id="customer-group">
        <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
            <option value="">Choose Customer / Group</option>
            @foreach($customer_categories as $cat)
                <option value="{{'cat-'.@$cat->id}}" class="parent" title="{{@$cat->title}}">{{@$cat->title}} {!! $extra_space !!}{{$cat->customer != null ? $cat->customer->pluck('reference_name') : ''}}</option>
                @foreach($cat->customer as $customer)
                    <option value="{{'cus-'.$customer->id}}" class="child-{{@$cat->id}}" title="{{@$customer->reference_name}}" >&nbsp;&nbsp;&nbsp;{{@$customer->reference_name}}{!! $extra_space !!}{{$cat->title}}</option>
                @endforeach
            @endforeach
        </select>
        </div>
    </div>

    @endif
    @if (app('request')->input('type') == 'supplier')
     <div class="col-2" id="supplier_select_div">
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags supplier_select" name="supplier" required="true">
            <option value="">Choose Supplier</option>
            @foreach($suppliers as $supplier)
              <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div>
    @endif

    <!-- <div class="col-lg col-md-8">
         <div class="border rounded position-relative custom-input-group autosearch">
          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_customer_search" tabindex="0" name="prod_name" placeholder="Choose Customer / Customer Group" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
          <span id="loader__custom_search" class="position-absolute d-none" style="right:0;top:0;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows" aria-hidden="true"></i>
        </div>
        <p id="myIddd" class="m-0"></p>
      </div> -->
      @if (app('request') == null || app('request')->input('type') != 'supplier')
    <div class="col" id="sale-person">
      <div class="form-group">
        <select class="form-control selecting-sale">
            <option value="">-- Sale person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div>
    @endif

    <div class="col">
      <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col">
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off">
      </div>
    </div>

    <div class="col" style="">
      <div class="form-group">

      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Dates" id="apply_filter">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>
        <span class="reset common-icons" title="Reset">
        <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
      </div>
      </div>
    </div>

    <div class="col" id="reset-button">
      <div class="input-group-append ml-3">
      </div>
    </div>

  </div>
  </form>
</div>

<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
    @if (app('request') == null || app('request')->input('type') != 'supplier')
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder table-quotation_div">
          <table class="table entriestable table-bordered table-quotation text-center">
              <thead>
                  <tr>
                     <!--  <th>
                          <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                            <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                            <label class="custom-control-label" for="check-all"></label>
                          </div>
                      </th> -->
                      <th>Action</th>
                      <th>Order#
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>Sales Person
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sales_person">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sales_person">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>Customer #
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_no">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_no">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_reference_name">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_reference_name">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>

                      <th>Order Total
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_total">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_total">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>Date
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="delivery_date">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="delivery_date">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>

                      <th>Memo
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="memo">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="memo">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>Status</th>
                  </tr>
              </thead>
                <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;"></th>

                <th colspan="4" style="text-align: left;"></th>

            </tr>
        </tfoot>
          </table>
    </div>
  </div>
  @else
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder table-supplier-credit-note_div">
          <!-- Supplier Credit Note Table-->
          <table class="table entriestable table-bordered table-supplier-credit-note text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Order#
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference #  @else {{$global_terminologies['reference_name']}} @endif
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_reference_no">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_reference_no">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_reference_name">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_reference_name">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>

                      <th>Order Total
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_total">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_total">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>Date
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_date">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_date">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>

                      <th>Memo
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="memo">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="memo">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      </th>
                      <th>Status</th>
                  </tr>
              </thead>
                <tfoot>
            <tr>
                <th style="text-align: right;">Order Total For All Entries</th>
                <th style="text-align: left;"></th>

            </tr>
        </tfoot>
          </table>
        </div>
  </div>
  @endif
</div>

</div>

<!-- main content dsfa end here -->

  <input type="hidden" name="customer_id_select" id="customer_id_select" >
</div>

@endsection



@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){

    var order = 1;
  var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    // $value = $('#sortbyvalue').val(order);
    // $value2 = $('#sortbyparam').val(column_name);

    $('.table-quotation').DataTable().ajax.reload();
    $('.table-supplier-credit-note').DataTable().ajax.reload();

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


	$('.reset').on('click',function(){


      $('#customer_id_select').val(null);
      $('#header_customer_search').val('');
      $('#form_id').trigger("reset");
      $('.sort-by-value').val(25).change();
      $('.selecting-customer-group').val("").change();
      $('.table-quotation_div').removeClass('d-none');
        $('.table-supplier-credit-note_div').addClass('d-none');
        $('#customer-group').removeClass('d-none');
        $('#sale-person').removeClass('d-none');
        $('#supplier_select_div').addClass('d-none');
        $('.credit_person_select').val('').trigger('change');
    });

	$("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $('#from_date').change(function() {
      var date = $('#from_date').val();

      // $('.table-quotation').DataTable().ajax.reload();
      // $($.fn.dataTable.tables(true)).DataTable()
      //   .columns.adjust();

    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();

      // $('.table-quotation').DataTable().ajax.reload();
      // $($.fn.dataTable.tables(true)).DataTable()
      //   .columns.adjust();

    });

    $(document).on('click','.apply_date',function(){

    $('.table-quotation').DataTable().ajax.reload();
    $('.table-supplier-credit-note').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
  });


	 $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

	 $('.sort-by-value').on('change', function(e){


        $('.table-quotation').DataTable().ajax.reload();
        $('.table-supplier-credit-note').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();

    });
	$('.selecting-customer').on('change', function(e){
      // alert($('.selecting-customer option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();

    });

    $('.selecting-customer-group').on('change', function(e){

      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();

    });

    $('.selecting-sale').on('change', function(e){

      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();

    });

	var table2 = $('.table-quotation').DataTable({
        "sPaginationType": "listbox",
         processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        searching:true,
        serverSide: true,
        "lengthMenu": [100,200,300,400],
        // dom: 'ftipr',
         "columnDefs": [
    { className: "dt-body-left", "targets": [ 2,3,4,5,7,8 ] },
    { className: "dt-body-right", "targets": [6] }
  ],
        scrollX: true,
        scrollY : '90vh',
    scrollCollapse: true,
        ajax:{
          beforeSend: function(){
            $('#loader_modal').modal('show');
          },
          url:"{!! route('get-credit-notes') !!}",
          // data: function(data) {data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val()} ,
           data: function(data) {
             data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val(), data.className = className,
             data.sortbyparam = column_name,
            data.sortbyvalue = order
             } ,
        },
        columns: [
            // { data: 'checkbox', name: 'checkbox'},
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'sales_person', name: 'sales_person' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'customer', name: 'customer' },
            // { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'delivery_date', name: 'delivery_date' },
            // { data: 'target_ship_date', name: 'target_ship_date' },
            { data: 'memo', name: 'memo' },
            { data: 'status', name: 'status' },
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
        // drawCallback: function(){
        // },
        "fnDrawCallback": function() {
          $('#loader_modal').modal('hide');

        var api = this.api()
        var json = api.ajax.json();
        var total = json.post;
         // total = total.toFixed(2);
         total = parseFloat(total).toFixed(2);
          total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
          $( api.column( 0 ).footer() ).html('Order Total For All Entries');
            $( api.column( 7 ).footer() ).html(total);
        // alert(total);
    }
   });

   $('.table-supplier-credit-note').DataTable({
            "sPaginationType": "listbox",
            processing: false,
            ordering: false,
            searching:false,
            serverSide: true,
            "lengthMenu": [100,200,300,400],
            // dom: 'ftipr',
            "columnDefs": [
        { className: "dt-body-left", "targets": [ 2,3,5,6 ] },
        { className: "dt-body-right", "targets": [4] }
    ],
            scrollX: true,
            scrollY : '90vh',
        scrollCollapse: true,
            ajax:{
            beforeSend: function(){
                $('#loader_modal').modal('show');
            },
            url:"{!! route('get-supplier-credit-notes') !!}",
            data: function(data) {
                data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.supplier = $('.supplier_select option:selected').val(),
                data.sortbyparam = column_name,
                data.sortbyvalue = order
                } ,
            },
            columns: [
                { data: 'action', name: 'action' },
                { data: 'ref_id', name: 'ref_id' },
                { data: 'supplier_ref_no', name: 'supplier_ref_no' },
                { data: 'supplier', name: 'supplier' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'credit_note_date', name: 'credit_note_date' },
                { data: 'memo', name: 'memo' },
                { data: 'status', name: 'status' },
            ],
            initComplete: function () {
            // Enable THEAD scroll bars
            $('.dataTables_scrollHead').css('overflow', 'auto');

            // Sync THEAD scrolling with TBODY
            $('.dataTables_scrollHead').on('scroll', function () {
                $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
            });
            },
            // drawCallback: function(){
            // },
            "fnDrawCallback": function() {
            $('#loader_modal').modal('hide');

            var api = this.api()
            var json = api.ajax.json();
            var total = json.post;
            // total = total.toFixed(2);
            total = parseFloat(total).toFixed(2);
            total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            // $( api.column( 0 ).footer() ).html('Order Total For All Entries');
                $( api.column( 1 ).footer() ).html(total);
            // alert(total);
        }
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


  $(document).on('click', '.deleteIcon, .delete_supplier_icon', function(){
    // var selected_quots = [];
    // $("input.check:checked").each(function() {
    //   selected_quots.push($(this).val());
    // });
    // // console.log(selected_quots)
    // length = selected_quots.length;
     var type = $('.credit_person_select').val() == '1' ? 'customer' : 'supplier';
    var id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to delete this credit note ?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: true
      },
      function(isConfirm) {
      if (isConfirm){
        $.ajax({

          method:"get",
          dataType:"json",
          data: {id : id, type:type},
          url:"{{ route('delete-credit-note') }}",

          success:function(result){

            if(result.success == true){

              toastr.success('Success!', 'Credit Note deleted Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.table-supplier-credit-note').DataTable().ajax.reload();
            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });
    });
$('#header_customer_search').on('click',function(){
  if($('.custom__search_arrows').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($(this).val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('.custom__search_arrows').on('click',function(){
  if($(this).hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($('#header_customer_search').val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('#header_customer_search').keyup(function(event){
      // $('#header_customer_search').unbind("focus");
      keyindex = -1;
      alinks = '';
      var query = $(this).val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
         var _token = $('input[name="_token"]').val();
         GetCathegoryCustomers(query,_token);
        }
        else if(query.length == 0)
        {
          $('#header_prod_searchh').val('');
          $('#header_prod_searchh').data('prod_id','');
        }
        else
        {
          $('#myIddd').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });
  function GetCathegoryCustomers(query=null,_token=null){
    $.ajax({
      url:"{{ route('accounting-fetch-customer') }}",
      method:"POST",
      data:{query:query, _token:_token},
      beforeSend:function(){
        $('#loader__custom_search').removeClass('d-none');
      },
      success:function(data){
        $('#myIddd').html(data);
        $('#loader__custom_search').addClass('d-none');
        $('.custom__search_arrows').removeClass('fa-caret-down');
        $('.custom__search_arrows').addClass('fa-caret-up');
       },
       error: function(){

       }
    });
  }
  var className = '';
  $(document).on("click",".list-data",function() {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       $('.search_customer').val(li_text);
      $("#customer_id_select").val(li_id);
      $("#customer_id_exp").val(li_id);
      $(".select_customer_id").hide();
      $('#header_customer_search').val(li_text);
      className = $(this).hasClass('parent') ? 'parent' : 'child';
      $('#className').val(className);

    $('.table-quotation').DataTable().ajax.reload();

    // $(".customer_id").val(li_id);


});
  $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   })

   $(document).on('change', '.credit_person_select', function() {

    var url = location.href;
    url = url.substring(0, url.indexOf("?"));
    if ($(this).val() == '1') {
        location.href = url + '?type=customer';
        // $('.table-quotation_div').removeClass('d-none');
        // $('.table-supplier-credit-note_div').addClass('d-none');
        // $('#customer-group').removeClass('d-none');
        // $('#sale-person').removeClass('d-none');
        // $('#supplier_select_div').addClass('d-none');
    }
    else{
        location.href = url + '?type=supplier';
        // $('.table-quotation_div').addClass('d-none');
        // $('.table-supplier-credit-note_div').removeClass('d-none');
        // $('#customer-group').addClass('d-none');
        // $('#sale-person').addClass('d-none');
        // $('#supplier_select_div').removeClass('d-none');
    }
   })
   $(document).on('change', '.supplier_select', function() {
    $('.table-supplier-credit-note').DataTable().ajax.reload();
   })

</script>
@stop

