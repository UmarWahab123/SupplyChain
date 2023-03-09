@extends('users.layouts.layout')

@section('title','Purchasing Report Grouped | Supply Chain')

@section('content')

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

table.dataTable thead .sorting { background: url('public/sort/sort_both.png') no-repeat center right !important; }
table.dataTable thead .sorting_asc { background: url('public/sort/sort_asc.png') no-repeat center right !important; }
table.dataTable thead .sorting_desc { background: url('public/sort/sort_desc.png') no-repeat center right !important; }
/*tbody tr td:nth-last-child(1) {
  text-align:right;
}
tbody tr td:nth-last-child(2) {
  text-align:right;
}
tbody tr td:nth-last-child(3) {
  text-align:right;
}
tbody tr td:nth-last-child(4) {
  text-align:right;
}
tbody tr td:nth-last-child(5) {
  text-align:right;
}*/
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
          <li class="breadcrumb-item active">Purchasing Report Grouped</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h5 class="maintitle text-uppercase fontbold">Purchasing Report Grouped</h5>
  </div>
  <div class="col-md-4 col-6">
    <div class="pull-right">
      <span class="export_btn common-icons" title="Create New Export" style="padding: 13px 15px;">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
  </div>
</div>

{{--Filters start here--}}
<div class="col-md-12 pl-0 pr-0 d-flex row align-items-center mb-3 filters_div">

  <div class="col-md-2 col-6 incomplete-filter">
    <label>Choose Category</label>
    <select class="form-control-lg form-control product_category_id state-tags" name="category_id" >
     <option value="" selected="">Choose Category</option>
     <hr>
      @foreach($parentCat as $ppc)
      <option value="{{'pri-'.$ppc->id}}" title="{{$ppc->title}}">{{$ppc->title}}{!! $extra_space !!}{{$ppc->get_Child != null ? $ppc->get_Child->pluck('title') : ''}}</option>
      @foreach($ppc->get_Child as $sc)
        <option value="{{'sub-'.$sc->id}}" title="{{$sc->title}}"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$sc->title}}
          {!! $extra_space !!} {{$ppc->title}} </option>
      @endforeach
      @endforeach
    </select>
  </div>

<!-- <div class="col-lg-4 col-md-4 mb-2 search-div">
      <label><b>Choose Product</b></label>
         <div class="border rounded position-relative custom-input-group autosearch">
          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_product_search" tabindex="0" name="prod_name" placeholder="Choose Product / Primary Category / Sub Category" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
        </div>
        <span id="loader__custom_search" class="position-absolute d-none" style="right:27px;top:43%;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows custom__search_arrows2" aria-hidden="true"></i>
        <p id="myIddd" class="m-0"></p>
      </div> -->

  <div class="col-md-2 col-6">
    <label class="pull-left">Stock</label>
    <select class="form-control-lg form-control js-states state-tags filter-dropdown" name="filter">
      <option value="" selected="">Select a Filter</option>
      <option value="stock">In Stock</option>
      <option value="reorder">Reorder Items</option>
    </select>
  </div>

  <div class="col-md-2 col-6">
    <label class="pull-left">Suppliers</label>
    <select class="form-control-lg form-control js-states state-tags supplier_id supplier-dropdown" name="supplier_filter">
      <option value="" selected="">Select Supplier</option>
      @foreach ($suppliers as $supplier)
        <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-2 col-6">
    <label>Choose Product</label>
    <select class="form-control-lg form-control product_id state-tags" name="product_id" >
      <option value="" selected="">Choose Product</option>
      @foreach($products as $s)
        <option value="{{$s->id}}">{{$s->refrence_code}} -  {{$s->short_desc}}</option>
      @endforeach
    </select>
  </div>

 <div class="col-md-2 col-6">
  <label class="d-block"><b style="visibility: hidden;">Reset</b></label>
    <!-- <input type="button" value="Reset" class="btn recived-button reset-btn"> -->
    <span class="vertical-icons reset-btn" id="reset-btn" title="Reset" style="padding: 13px 15px;">
      <img src="{{asset('public/icons/reset.png')}}" width="27px">
    </span>

  </div>
  <div class="col-lg-2 col-md-2">
    <label><b style="visibility: hidden;">Reset</b></label>
    <!-- <input type="button" value="Export" class="btn recived-button export_btn"> -->
  </div>

</div>
<div class="col-md-12 pl-0 pr-0 d-flex row align-items-center mb-3 filters_div">
  <div class="col-md-2 col-6">
    <div class="form-group">
      <label class="pull-left">From Date:</label>
      <input type="text" placeholder="From Date" name="from_date" class="form-control" id="from_date" autocomplete="off">
    </div>
  </div>

  <div class="col-md-2 col-6">
    <div class="form-group">
      <label class="pull-left">To Date:</label>
      <input type="text" placeholder="To Date" name="to_date" class="form-control" id="to_date" autocomplete="off">
    </div>
  </div>
  <div class="col-md-1 p-0" style="">
      <div class="form-group mb-0">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3 pb-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Filters</button>   -->
         <span class="apply_date common-icons mr-4" title="Apply Filters" id="apply_filter" >
            <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
          </span>
      </div>
      </div>
  </div>
  <div class="col-lg-3">
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
        <!-- <a download href="{{'storage/app/purchasing-report.xlsx'}}"><u>Click Here</u></a> -->
        <a class="exp_download" href="{{ url('get-download-xslx','purchasing-report.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>
      <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is already being prepared by another user! Please wait.. </b>
      </div>
      <table id="po_tabale" class="table entriestable table entriestable table-bordered text-center purchasing-report">
        <thead>
          <tr>
            <!-- <th>Confirm Date</th>
            <th>Supplier
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>PO#</th> -->
            <th>Action</th>
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="4">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="4">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Billing Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="billing_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="billing_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['selling_unit']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Sum of <br> {{$global_terminologies['qty']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sum_qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sum_qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <!-- <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
            <th>{{$global_terminologies['landing_per_billed_unit']}}</th>
            <th>{{$global_terminologies['import_tax_actual']}}</th> -->
            <!-- <th>{{$global_terminologies['cost_price']}}</th> -->

            <th>{{$global_terminologies['product_cost']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="product_cost">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="product_cost">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['sum_pro_cost']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sum_pro_cost">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sum_pro_cost">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <!-- <th>{{$global_terminologies['cost_unit_thb']}}</th> -->
            <!-- <th>{{$global_terminologies['sum_cost_amnt']}}</th> -->
            <!-- <th>Vat</th> -->
          </tr>
        </thead>
        <tbody></tbody>
        @if($units_total->count() > 1)
          @php $showFooter = 'd-none'; @endphp
        @else
          @php $showFooter = ''; @endphp
        @endif
        <tfoot align="right" class="{{$showFooter}}">
          <tr>
            <!-- <th id="total_head"></th> -->
            <!-- <th></th>
            <th></th>
            <th></th> -->
            <!-- <th></th>
            <th></th>
            <th></th>
            <th id="qty_sum"></th> -->
            <!-- <th id="freight_p_b_unit"></th>
            <th id="landing_p_b_unit"></th>
            <th id="total_allocation"></th> -->
            <!-- <th id="total_unit_cost"></th>
            <th id="unit_euro"></th>
            <th id="total_amount_euro"></th> -->
            <!-- <th id="unit_cost_thb"></th>
            <th id="total_amount_thb"></th> -->
            <!-- <th></th> -->
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<!--  Content End Here -->
<!-- Loader Modal -->
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

<form id="export_purchasing_report_form" method="post"  action="{{route('export-purchasing-report') }}">
  @csrf
  <input type="hidden" name="product_category_exp" id="product_category_exp">
  <input type="hidden" name="filter_dropdown_exp" id="filter_dropdown_exp">
  <input type="hidden" name="supplier_filter_exp" id="supplier_filter_exp">
  <input type="hidden" name="product_id_filter_exp" id="product_id_filter_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="product_id_select" id="product_id_select">
  <input type="hidden" name="className" id="className">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
</form>

@endsection

@section('javascript')
<script type="text/javascript">

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );

  $('#from_date').datepicker('setDate',last_month);
  $('#to_date').datepicker('setDate', 'today');

  $(function(e){
    // start point
    var order = 1;
    var column_name = '';

    $('.sorting_filter_table').on('click',function(){
      $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
      $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

      order = $(this).data('order');
      column_name = $(this).data('column_name');
      $('#sortbyparam').val(column_name);
      $('#sortbyvalue').val(order);

      $('.purchasing-report').DataTable().ajax.reload();

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
  // ending point
    $(".state-tags").select2();
      var table2 =  $('.purchasing-report').DataTable({
        // "pagingType": "input",
        "sPaginationType": "listbox",
      processing: false,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      pageLength: {{100}},
      serverSide: true,
      "lengthMenu": [100,150,200,250],
      "columnDefs": [
        // { className: "dt-body-left", "targets": [ 0,1,3,4,5] },
        // { className: "dt-body-right", "targets": [2,6,7,8,9,10,11,12,13,14,15 ] },
      ],
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      ajax:
      {
        beforeSend:function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url:"{!! route('get-purchase-orders-data-for-grouped-report') !!}",
        // data: function(data) {
        //   data.from_date     = $('#from_date').val(),
        //   data.to_date       = $('#to_date').val(),
        //   data.prod_category = $('.product_category_id option:selected').val(),
        //   data.filter        = $('.filter-dropdown option:selected').val(),
        //   data.supplier      = $('.supplier-dropdown option:selected').val(),
        //   data.product_id    = $('.product_id option:selected').val(),
        //   data.sortbyparam   = column_name,
        //   data.sortbyvalue   = order },
        data: function(data) {
          data.from_date     = $('#from_date').val(),
          data.to_date       = $('#to_date').val(),
          // data.prod_category = $('#product_id_select').val(),
          data.filter        = $('.filter-dropdown option:selected').val(),
          data.supplier      = $('.supplier-dropdown option:selected').val(),
          data.product_id    = $('.product_id option:selected').val(),
          data.sortbyparam   = column_name,
          data.sortbyvalue   = order,
          data.className = className,
          data.prod_category = $('.product_category_id option:selected').val()
          },
      },
      columns: [
        // { data: 'confirm_date', name: 'confirm_date' },
        // { data: 'supplier', name: 'supplier' },
        // { data: 'ref_id', name: 'ref_id'},
        { data: 'action', name: 'action'},
        { data: 'refrence_code', name: 'refrence_code', class: 'text-start' },
        { data: 'short_desc', name: 'short_desc',   class: 'text-start'},
        { data: 'buying_unit', name: 'buying_unit',  class: 'text-start'},
        { data: 'unit', name: 'unit',  class: 'text-start'},
        { data: 'sum_qty', name: 'sum_qty', class: 'text-start' },
        /*new columns added*/
        // { data: 'freight', name: 'freight' },
        // { data: 'landing', name: 'landing' },
        // { data: 'import_tax_actual', name: 'import_tax_actual' },
        // { data: 'seller_price', name: 'seller_price' },
        /*new columns added*/
        { data: 'cost_unit', name: 'cost_unit',class: 'text-right' },
        { data: 'total_cost', name: 'total_cost',class: 'text-right' },
        // { data: 'cost_unit_thb', name: 'cost_unit_thb' },
        // { data: 'sum_cost_amount', name: 'sum_cost_amount' },
        // { data: 'vat', name: 'vat' },
      ],
      initComplete: function () {
        // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');

        // Sync THEAD scrolling with TBODY
        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
        $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
      },
      // footerCallback: function ( row, data, start, end, display ) {
      //   var api = this.api();
      //   $.ajaxSetup({
      //     headers: {
      //         'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      //     }
      //   });
      //   $.ajax({
      //     method:"get",
      //     dataType:"json",
      //     url:"{{ route('get-purchase-orders-data-for-report-footer-values') }}",
      //     data:{
      //       from_date     : $('#from_date').val(),
      //       to_date       : $('#to_date').val(),
      //       prod_category : $('.product_category_id option:selected').val(),
      //       filter        : $('.filter-dropdown option:selected').val(),
      //       supplier      : $('.supplier-dropdown option:selected').val(),
      //       product_id    : $('.product_id option:selected').val(),
      //     },
      //     beforeSend:function(){
      //       $( api.column( 0 ).footer() ).html('Loading...');
      //       $( api.column( 7 ).footer() ).html('Loading...');
      //       $( api.column( 8 ).footer() ).html('Loading...');
      //       $( api.column( 9 ).footer() ).html('Loading...');
      //       $( api.column( 10 ).footer() ).html('Loading...');
      //       $( api.column( 11 ).footer() ).html('Loading...');
      //       $( api.column( 12 ).footer() ).html('Loading...');
      //       $( api.column( 13 ).footer() ).html('Loading...');
      //       $( api.column( 14 ).footer() ).html('Loading...');
      //       $( api.column( 15 ).footer() ).html('Loading...');
      //       $($.fn.dataTable.tables(true)).DataTable()
      //     .columns.adjust();
      //       // $('#total_head').html('Totals');
      //       // $('#qty_sum').html("Loading...");
      //       // $('#freight_p_b_unit').html("Loading...");
      //       // $('#landing_p_b_unit').html("Loading...");
      //       // $('#total_allocation').html("Loading...");
      //       // $('#total_unit_cost').html("Loading...");
      //       // $('#unit_euro').html("Loading...");
      //       // $('#total_amount_euro').html("Loading...");
      //       // $('#unit_cost_thb').html("Loading...");
      //       // $('#total_amount_thb').html("Loading...");
      //     },
      //     success:function(result){
      //       $( api.column( 0 ).footer() ).html('Totals');
      //       $( api.column( 7 ).footer() ).html(result.qty_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 8 ).footer() ).html(result.freight_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 9 ).footer() ).html(result.landing_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 10 ).footer() ).html(result.total_allocation.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 11 ).footer() ).html(result.total_unit_cost.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 12 ).footer() ).html(result.unit_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 13 ).footer() ).html(result.total_amount_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 14 ).footer() ).html(result.unit_cost_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $( api.column( 15 ).footer() ).html(result.total_amount_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       $($.fn.dataTable.tables(true)).DataTable()
      //     .columns.adjust();
      //       // $('#qty_sum').html(result.qty_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#freight_p_b_unit').html(result.freight_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#landing_p_b_unit').html(result.landing_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#total_allocation').html(result.total_allocation.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#total_unit_cost').html(result.total_unit_cost.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#unit_euro').html(result.unit_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#total_amount_euro').html(result.total_amount_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#unit_cost_thb').html(result.unit_cost_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //       // $('#total_amount_thb').html(result.total_amount_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
      //     },
      //     error: function(){

      //     }
      //   });
      // },
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

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });

  $(document).on('change','.product_category_id',function(){
    $("#apply_filter_btn").val("1");
    $('#product_category_exp').val($('.product_category_id option:selected').val());
    var selected = $(this).val();
  });

  $(document).on('change','.filter-dropdown',function(){
    $("#apply_filter_btn").val("1");
    $('#filter_dropdown_exp').val($('.filter-dropdown option:selected').val());
  });

  $(document).on('change','.supplier-dropdown',function(){
    $("#apply_filter_btn").val("1");
    $('#supplier_filter_exp').val($('.supplier-dropdown option:selected').val());
  });

  $(document).on('change','.product_id',function(){
    $("#apply_filter_btn").val("1");
    $('#product_id_filter_exp').val($('.product_id option:selected').val());
  });

  $('#from_date').change(function() {
    $("#apply_filter_btn").val("1");
    if($('#from_date').val() != '')
    {
      $('#from_date_exp').val($('#from_date').val());
    }
  });

  $('#to_date').change(function() {
    $("#apply_filter_btn").val("1");
    if($('#to_date').val() != '')
    {
      $('#to_date_exp').val($('#to_date').val());
    }
  });




  $(document).on('click','.apply_date',function(){
    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.purchasing-report').DataTable().ajax.reload();
  });

  $('.reset-btn').on('click',function(){
    $('#myIddd').hide();

    $('#product_id_select').val(null);
    $('#header_product_search').val('');
    $("#apply_filter_btn").val("0");
    $('#from_date').val('');
    $('#to_date').val('');
    $('.product_category_id').val('');
    $('.filter-dropdown').val('');
    $('.supplier-dropdown').val('');
    $('.product_id').val('');

    $('#product_category_exp').val('');
    $('#filter_dropdown_exp').val('');
    $('#from_date_exp').val('');
    $('#to_date_exp').val('');
    $('#supplier_filter_exp').val('');
    $('#product_id_filter_exp').val('');
    $(".state-tags").select2("", "");


    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.purchasing-report').DataTable().ajax.reload();
  });

  $('.export_btn').on('click',function(){

    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    $("#to_date_exp").val($('#to_date').val());
    $("#from_date_exp").val($('#from_date').val());
    $("#product_id_filter_exp").val($('.product_id option:selected').val());
    $("#supplier_filter_exp").val($('.supplier-dropdown option:selected').val());
    $("#filter_dropdown_exp").val($('.filter-dropdown option:selected').val());
    $("#product_category_exp").val($('.product_category_id option:selected').val());

    var form = $('#export_purchasing_report_form');
    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method:"get",
      url:"{{route('export-purchasing-report-grouped')}}",
      data:form_data,
      beforeSend:function(){
        $('.export_btn').attr('title','Please Wait...');
        $('.export_btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export_btn').attr('title','EXPORT is being Prepared');
          $('.export_btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForProducts();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export_btn').prop('disabled',true);
          $('.export_btn').attr('title','EXPORT is being Prepared');
          checkStatusForProducts();
        }
      },
      error:function(){
         $('.export_btn').attr('title','Create New Export');
        $('.export_btn').prop('disabled',false);
      }
    });
  });

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-purchasing-report-grouped')}}",
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
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').attr('title','Create New Export');
          $('.export_btn').prop('disabled',false);
          $('.download-btn').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').attr('title','Create New Export');
          $('.export_btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $( document ).ready(function() {

    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-purchasing-report-grouped')}}",
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
          $('.export_btn').attr('title','EXPORT is being Prepared');
          $('.export_btn').prop('disabled',true);
          checkStatusForProducts();
        }
      }
    });

    if($('#from_date').val() != '')
    {
      $('#from_date_exp').val($('#from_date').val());
    }
    if($('#to_date').val() != '')
    {
      $('#to_date_exp').val($('#to_date').val());
    }
  });
$('#header_product_search').on('click',function(){
  if($('.custom__search_arrows').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetProductCategorySearch($(this).val(),_token);
  }
  else
  {
    $("#product-ul-div").empty();
  }
});
$('.custom__search_arrows').on('click',function(){
  if($(this).hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetProductCategorySearch($('#header_product_search').val(),_token);
  }
  else
  {
    $("#product-ul-div").empty();
  }
});
  $('#header_product_search').keyup(function(event){
      // $('#header_product_search').unbind("focus");
      keyindex = -1;
      alinks = '';
      var query = $(this).val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
         var _token = $('input[name="_token"]').val();
         GetProductCategorySearch(query,_token);
         $('#myIddd').show();
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
  function GetProductCategorySearch(query=null,_token=null){
    $.ajax({
      url:"{{ route('fetch-product-with-category') }}",
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
  var parent_li = true;
  $(document).on("click",".list-data-category",function() {
    if (parent_li) {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       console.log(li_id);
       console.log(li_text);
       $('.search_customer').val(li_text);
      $("#product_id_select").val(li_id);
      $("#customer_id_exp").val(li_id);
      $(".select_customer_id").hide();
      $('#header_product_search').val(li_text);
      className = $(this).hasClass('parent') ? 'parent' : 'child';
      $('#className').val(className);
    }
    else{
      parent_li = true;
    }
});
   $(document).on("click",".child-list-data",function() {
      parent_li = false;
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       console.log(li_id);
       console.log(li_text);
       $('.search_customer').val(li_text);
      $("#product_id_select").val(li_id);
      $("#customer_id_exp").val(li_id);
      $(".select_customer_id").hide();
      $('#header_product_search').val(li_text);
      className = 'child_product';
      $('#className').val(className);
});
   $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   })
</script>
@stop
