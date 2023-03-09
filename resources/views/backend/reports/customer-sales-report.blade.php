@extends('backend.layouts.layout')

@section('title','Users Management | Supply Chain')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}
  /* .dt-buttons{
    float: right !important;

  }

  .dt-buttons button{
      background: #13436c;
    color: #fff;
    border-radius: 0px;
    font-size: 11px;
    max-height: 34px;
  }
  .dt-buttons button:hover:not(.disabled){
     background-color: #13436c !important;
    color: #fff;
    background-image: none !important;
  } */

  .dataTables_scrollFootInner
  {
    width: 100% !important;
    margin-top: 10px;
  }

  table.dataTable thead .sorting { background: url('../public/sort/sort_both.png') no-repeat center right;
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('../public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('../public/sort/sort_desc.png') no-repeat center right;
  background-size: 5vh;}

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
          <li class="breadcrumb-item active">Customers Sales Report</li>
      </ol>
  </div>
</div>

@php
 $var = implode(" ",$months);
@endphp

{{-- Content Start from here --}}

<div class="row mb-5">
  <div class="col-md-8 title-col col-6">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">Customers Sale Report</h4>
        {{-- <div class="col"></div> --}}
    </div>
  </div>
  <div class="col-md-4 col-6">
    <div class="pull-right">
      <span class="export-btn vertical-icons" title="Create New Export">
        <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
  </div>
</div>
  <form id="export-customer-sale-report-form" class="filters_div" action="{{ route('export-customer-sale-report') }}" method="post">
    <input type="hidden" name="year_filter" value="{{$year}}">
<div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex row justify-content-between">
        {{-- <div class="col"></div> --}}
        @if(Auth::user()->role_id !== 3)
        <div class="col-md-2 col-6">
          <select  class="font-weight-bold form-control-lg form-control sale_person state-tags" name="sale_person" id="sale_person" >
              <option value="" selected>Choose a Sales Person</option>
              @foreach($sales_persons as $sale_person)
              <option value="{{ $sale_person->id }}">{{ $sale_person->name }}</option>
              @endforeach
            </select>
        </div>
        @endif

         @if(Auth::user()->role_id == 3)
        <div class="col-md-2 col-6">
          <select  class="font-weight-bold form-control-lg form-control sale_person_filter state-tags" name="sale_person_filter" disabled="">
              <option value="" selected>Choose a Sales Person</option>
              @foreach($sales_person_filter as $sale_person)
              <option value="{{ $sale_person->id }}" {{auth()->user()->id == $sale_person->id ? 'selected' : ''}}>{{ $sale_person->name }}</option>
              @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-2 col-6">
          <select class="font-weight-bold form-control-lg form-control selecting-customer-group state-tags" name="customer_categories" >
            <option value="">@if(!array_key_exists('customer_group', $global_terminologies))Customer Group @else {{$global_terminologies['customer_group']}} @endif</option>
              @foreach($customer_categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->title }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-2 col-6">
            @csrf
            <select class="font-weight-bold form-control-lg form-control sale_year state-tags" name="sale_year" >
              <option value="" disabled>Choose year</option>
              @foreach($sales_years as $key => $sale_year)
              <option value="{{ $sale_year }}" {{ $sale_year == $year ? 'selected="selected"' : '' }}>{{ $sale_year }}</option>
              @endforeach
            </select>
        </div>



        @php
          if($file_name==null)
          $className='d-none';
          else
          $className='';
        @endphp
      <!--   <div class="col download-btn-div {{$className}}">

          <a download href="{{asset('storage/app/'.@$file_name->file_name)}}" class="download-btn vertical-icons" id="" title="Download">
            <span class="">
                <img src="{{asset('public/icons/download.png')}}" width="27px">
            </span>
          </a>
        </div> -->

        <div class="col-md-2 col-6 mt-2">
        <div class="text-center">
          <!-- <button class="btn button-st export-btn" id="export_customer_sale_report" >Create New Export</button> -->
           <span class="apply_filter vertical-icons mr-4" title="Apply Filters" id="apply_filter">
            <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
          </span>
          <span class="vertical-icons" id="btn_reset" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>

          <!--  <span class="col download-btn-div {{$className}}">

          <a download href="{{asset('storage/app/'.@$file_name->file_name)}}" class="download-btn vertical-icons" id="" title="Download">
            <span class="">
                <img src="{{asset('public/icons/download.png')}}" width="27px">
            </span>
          </a>
        </span> -->
        </div>
        </div>
        <div class="col-md-2"></div>
        <div class="col-md-2"></div>
    </div>
  </div>
</div>
    <input type="hidden" name="months" value="{{$var}}">
    <input type="hidden" name="sortbyparam" id="sortbyparam">
    <input type="hidden" name="sortbyvalue" id="sortbyvalue">

    <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  </form>
  <div class="alert alert-primary export-alert d-none"  role="alert">
    <i class="  fa fa-spinner fa-spin"></i>
    <b> Export file is being prepared! Please wait.. </b>
  </div>

  <div class="alert alert-success export-alert-success d-none"  role="alert">
  <i class=" fa fa-check "></i>
    <b>Export file is ready to download.
      <a id="export-download-btn" href="{{asset('storage/app/'.@$file_name->file_name)}}" target="_blank"><u>Click Here</u></a>
    </b>
  </div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-customer-sales-report text-center">
        <thead>
          <tr>
            <!-- <th>
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                  <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
              <label class="custom-control-label" for="check-all"></label>
              </div>
            </th> -->
           <th>Customers
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            @foreach($months as $mon)
           <th>
           {{$mon}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="{{@$mon}}">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="{{@$mon}}">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
         </th>
           @endforeach
            <th>Grand <br> Total
             <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="orders">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="orders">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>Location<br>Code
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="location_code">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="location_code">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
            </th>
            <th>Sales <br>Person<br> Code
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sales_person_code">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sales_person_code">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
            </th>
            <th>Payment<br> Terms<br> Code
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="payment_term_code">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="payment_term_code">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
            </th>
          </tr>
        </thead>

        <tfoot align="right">
          <tr>
            <th></th>
            @foreach($months as $key => $value)
              <th id="month_{{$key}}"></th>
            @endforeach
             <th id="overallTotal"></th>
             <th></th>
             <th></th>
             <th></th>
          </tr>
          @if (Auth::user()->role_id == 3)
            <tr>
              <th></th>
              @foreach($months as $key => $value)
                <th></th>
              @endforeach
              <th></th>
              <th></th>
              <th></th>
              <th></th>
            </tr>
          @endif
        </tfoot>
      </table>
      <div>
        <span><b>Company Total</b></span>
        <span id="overAllTotal"></span>

      </div>
    </div>
    </div>

  </div>
</div>

</div>
<!--  Content End Here -->

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

@endsection
@php
  $hidden_by_default = '';
@endphp
@section('javascript')
<script type="text/javascript">
  function copyToClipboard() {
    document.getElementById("login_url").select();
    document.execCommand('copy');
  }

  $(function(e){

    var order = 2;
    var column_name = 3;

    $('.sorting_filter_table').on('click',function(){
      $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
      $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

      order = $(this).data('order');
      column_name = $(this).data('column_name');
      $('#sortbyparam').val(column_name);
      $('#sortbyvalue').val(order);

      $('.table-customer-sales-report').DataTable().ajax.reload();

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

    $(".state-tags").select2();
    var full_path = $('#site_url').val()+'/';

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

    table2 = $('.table-customer-sales-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      lengthMenu:[100,200,300,400,500],
      serverSide: true,
      scrollX:true,
      scrollY : '90vh',
      dom: 'Blfrtip',
      scrollCollapse: true,
      "columnDefs": [
       { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      { className: "dt-body-left", "targets": [0] }
      // { className: "dt-body-right", "targets": [1,2,3,4,5,6,7,8,9,10,11,12,13] },

    ],
     buttons: [
      {
        extend: 'colvis',
      }
    ],
      ajax:
        {
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          url: "{!! route('get-customers-sale-report') !!}",
          data: function(data) { data.sale_year = $('.sale_year option:selected').val(),data.sale_person = $('.sale_person option:selected').val(),data.sale_person_filter = $('.sale_person_filter option:selected').val(),data.customer_categories = $('.selecting-customer-group option:selected').val(),
          data.sortbyparam = column_name,
          data.sortbyvalue = order
      } ,
           method: "get",
        },


      columns: [
        // { data: 'checkbox', name: 'checkbox' },
        { data: 'reference_name', name: 'reference_name' },
        <?php foreach ($months as $mon): ?>
          {data: "{{$mon}}", name: "{{$mon}}", className: 'dt-body-right'},
        <?php endforeach ?>
        { data: 'orders', name: 'orders', className: 'dt-body-right'},
        { data: 'location_code', name: 'location_code', className: 'dt-body-left' },
        { data: 'sale_person', name: 'sale_person', className: 'dt-body-left' },
        { data: 'payment_term', name: 'payment_term', className: 'dt-body-left' },

      ],
     //dom: 'Blfrtip',
       //buttons: [
           // {
                // extend: 'excelHtml5',
                // text: '<i class="fa fa-file-excel-o" style="font-size:22px;" title="Export Excel"></i>',
                // exportOptions: { orthogonal: 'export',columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16] },
              //  title: null,
              //  footer: true
            //},
       // ],
     initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
       $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      $('body').find('.dataTables_scrollHead').addClass("scrollbar");
        },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
      "rowCallback": function( row, data, index ) {
        if("{{Auth::user()->role_id}}" != 3){
          if (data["orders"] <= 0) {
            $(row).hide();
          }
        }
      },
      footerCallback: function ( row, data, start, end, display ) {
          var janTotal = '';
        var febTotal = '';
        var marTotal = '';
        var aprTotal = '';
        var mayTotal = '';
        var junTotal = '';
        var julTotal = '';
        var augTotal = '';
        var sepTotal = '';
        var octTotal = '';
        var novTotal = '';
        var decTotal = '';
        var yearTotal ='';
        var overAllTotal='';
        var count='';
        var api = this.api()
        var json = api.ajax.json();
        $('#loader_modal').modal('hide');



        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });
        $.ajax({
            method:"post",
            dataType:"json",
            url:"{{ route('get-customer-sale-report-general-footer-values') }}",
            debugger: true,
            data:{
                sale_year : $('.sale_year option:selected').val(),
                sale_person : $('.sale_person option:selected').val(),
                sale_person_filter : $('.sale_person_filter option:selected').val(),
                customer_categories : $('.selecting-customer-group option:selected').val()
             },
            success: function(result){

         janTotal = result.janTotal;
         febTotal = result.febTotal;
         marTotal = result.marTotal;
         aprTotal = result.aprTotal;
         mayTotal = result.mayTotal;
         junTotal = result.junTotal;
         julTotal = result.julTotal;
         augTotal = result.augTotal;
         sepTotal = result.sepTotal;
         octTotal = result.octTotal;
         novTotal = result.novTotal;
         decTotal = result.decTotal;
         yearTotal =result.yearTotal;
            overAllTotal = result.overAllTotal;
         count = '';

    //    var total = total.toFixed(2);

        overAllTotal = parseFloat(overAllTotal).toFixed(2);
        overAllTotal = overAllTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        $('#overAllTotal').html(overAllTotal);

        janTotal = parseFloat(janTotal).toFixed(2);
        janTotal = janTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        febTotal = parseFloat(febTotal).toFixed(2);
        febTotal = febTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        marTotal = parseFloat(marTotal).toFixed(2);
        marTotal = marTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        aprTotal = parseFloat(aprTotal).toFixed(2);
        aprTotal = aprTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        mayTotal = parseFloat(mayTotal).toFixed(2);
        mayTotal = mayTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        junTotal = parseFloat(junTotal).toFixed(2);
        junTotal = junTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        julTotal = parseFloat(julTotal).toFixed(2);
        julTotal = julTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        augTotal = parseFloat(augTotal).toFixed(2);
        augTotal = augTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        sepTotal = parseFloat(sepTotal).toFixed(2);
        sepTotal = sepTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        octTotal = parseFloat(octTotal).toFixed(2);
        octTotal = octTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        novTotal = parseFloat(novTotal).toFixed(2);
        novTotal = novTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        decTotal = parseFloat(decTotal).toFixed(2);
        decTotal = decTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        yearTotal = parseFloat(yearTotal).toFixed(2);
        yearTotal = yearTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        if("{{Auth::user()->role_id}}" != 3){
            // $("#month_0").html(janTotal);
            // $( api.column( 0 ).footer() ).html('Total');
            // $( api.column( 1 ).footer() ).html(janTotal);
            // $( api.column( 2 ).footer() ).html(febTotal);
            // $( api.column( 3 ).footer() ).html(marTotal);
            // $( api.column( 4 ).footer() ).html(aprTotal);
            // $( api.column( 5 ).footer() ).html(mayTotal);
            // $( api.column( 6 ).footer() ).html(junTotal);
            // $( api.column( 7 ).footer() ).html(julTotal);
            // $( api.column( 8 ).footer() ).html(augTotal);
            // $( api.column( 9 ).footer() ).html(sepTotal);
            // $( api.column( 10 ).footer() ).html(octTotal);
            // $( api.column( 11 ).footer() ).html(novTotal);
            // $( api.column( 12 ).footer() ).html(decTotal);
            // $( api.column( +count ).footer() ).html(yearTotal);
            $( api.column( 0 ).footer() ).html('Total');
              <?php foreach ($months as $key => $value): ?>
              if("{{$key}}" == 0)
              {
                $( api.column( 1 ).footer() ).html(janTotal);
              }

              if("{{$key}}" == 1)
              {
                $( api.column( 2 ).footer() ).html(febTotal);
              }

              if("{{$key}}" == 2)
              {
                $( api.column( 3 ).footer() ).html(marTotal);
              }

              if("{{$key}}" == 3)
              {
                $( api.column( 4 ).footer() ).html(aprTotal);
              }

              if("{{$key}}" == 4)
              {
                $( api.column( 5 ).footer() ).html(mayTotal);
              }

              if("{{$key}}" == 5)
              {
                $( api.column( 6 ).footer() ).html(junTotal);
              }

              if("{{$key}}" == 6)
              {
                $( api.column( 7 ).footer() ).html(julTotal);
              }

              if("{{$key}}" == 7)
              {
                $( api.column( 8 ).footer() ).html(augTotal);
              }

              if("{{$key}}" == 8)
              {
                $( api.column( 9 ).footer() ).html(sepTotal);
              }

              if("{{$key}}" == 9)
              {
                $( api.column( 10 ).footer() ).html(octTotal);
              }

              if("{{$key}}" == 10)
              {
                $( api.column( 11 ).footer() ).html(novTotal);
              }

              if("{{$key}}" == 11)
              {
                $( api.column( 12 ).footer() ).html(decTotal);
              }

              count = "{{$key + 2}}";
              <?php endforeach ?>
                $( api.column( +count ).footer() ).html(yearTotal);


        }
        // $("row_0").html(janTotal);
        // $("row_1").html(febTotal);
        // $("row_2").html(marTotal);
        // $("row_3").html(aprTotal);
        // $("row_4").html(mayTotal);
        // $("row_").html(junTotal);
        // $("row_0").html(julTotal);
        // $("row_0").html(augTotal);
        // $("row_0").html(sepTotal);
        // $("row_0").html(octTotal);
        // $("row_0").html(novTotal);
        // $("row_0").html(decTotal);
            }

        });

        if("{{Auth::user()->role_id}}" == 3){
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });
          $.ajax({
            method:"post",
            dataType:"json",
            url:"{{ route('get-customer-sale-report-footer-values') }}",
            data:{
              sale_year : $('.sale_year option:selected').val(),
              sale_person : $('.sale_person option:selected').val(),
              sale_person_filter : $('.sale_person_filter option:selected').val(),
              customer_categories : $('.selecting-customer-group option:selected').val()
            },
            beforeSend:function(){
              // $( api.column( 0 ).footer() ).html('Total');
              // $( api.column( 15 ).footer() ).html('Loading...');
              // /*$( api.column( 16 ).footer() ).html('Loading...');
              // $( api.column( 19 ).footer() ).html('Loading...');*/
              // $( api.column( 20 ).footer() ).html('Loading...');
              // $( api.column( 21 ).footer() ).html('Loading...');
              // $( api.column( 22 ).footer() ).html('Loading...');
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            },
            success:function(result){
              janCTotal = parseFloat(result.jan_com_total).toFixed(2);
              janCTotal = janCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              febCTotal = parseFloat(result.fab_com_total).toFixed(2);
              febCTotal = febCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              marCTotal = parseFloat(result.mar_com_total).toFixed(2);
              marCTotal = marCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              aprCTotal = parseFloat(result.apr_com_total).toFixed(2);
              aprCTotal = aprCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              mayCTotal = parseFloat(result.may_com_total).toFixed(2);
              mayCTotal = mayCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              junCTotal = parseFloat(result.jun_com_total).toFixed(2);
              junCTotal = junCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              julCTotal = parseFloat(result.jul_com_total).toFixed(2);
              julCTotal = julCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              augCTotal = parseFloat(result.aug_com_total).toFixed(2);
              augCTotal = augCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              sepCTotal = parseFloat(result.sep_com_total).toFixed(2);
              sepCTotal = sepCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              octCTotal = parseFloat(result.oct_com_total).toFixed(2);
              octCTotal = octCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              novCTotal = parseFloat(result.nov_com_total).toFixed(2);
              novCTotal = novCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              decCTotal = parseFloat(result.dec_com_total).toFixed(2);
              decCTotal = decCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
              yearCTotal = parseFloat(result.year_com_total).toFixed(2);
              yearCTotal = yearCTotal.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

              $( api.column( 0 ).footer() ).html('Total');
              $('tr:eq(1) th:eq(0)', api.table().footer()).html('Company Total');
              <?php foreach ($months as $key => $value): ?>
              if("{{$key}}" == 0)
              {
                $( api.column( 1 ).footer() ).html(janTotal);
                $('tr:eq(1) th:eq(1)', api.table().footer()).html(janCTotal);
              }

              if("{{$key}}" == 1)
              {
                $( api.column( 2 ).footer() ).html(febTotal);
                $('tr:eq(1) th:eq(2)', api.table().footer()).html(febCTotal);
              }

              if("{{$key}}" == 2)
              {
                $( api.column( 3 ).footer() ).html(marTotal);
                $('tr:eq(1) th:eq(3)', api.table().footer()).html(marCTotal);
              }

              if("{{$key}}" == 3)
              {
                $( api.column( 4 ).footer() ).html(aprTotal);
                $('tr:eq(1) th:eq(4)', api.table().footer()).html(aprCTotal);
              }

              if("{{$key}}" == 4)
              {
                $( api.column( 5 ).footer() ).html(mayTotal);
                $('tr:eq(1) th:eq(5)', api.table().footer()).html(mayCTotal);
              }

              if("{{$key}}" == 5)
              {
                $( api.column( 6 ).footer() ).html(junTotal);
                $('tr:eq(1) th:eq(6)', api.table().footer()).html(junCTotal);
              }

              if("{{$key}}" == 6)
              {
                $( api.column( 7 ).footer() ).html(julTotal);
                $('tr:eq(1) th:eq(7)', api.table().footer()).html(julCTotal);
              }

              if("{{$key}}" == 7)
              {
                $( api.column( 8 ).footer() ).html(augTotal);
                $('tr:eq(1) th:eq(8)', api.table().footer()).html(augCTotal);
              }

              if("{{$key}}" == 8)
              {
                $( api.column( 9 ).footer() ).html(sepTotal);
                $('tr:eq(1) th:eq(9)', api.table().footer()).html(sepCTotal);
              }

              if("{{$key}}" == 9)
              {
                $( api.column( 10 ).footer() ).html(octTotal);
                $('tr:eq(1) th:eq(10)', api.table().footer()).html(octCTotal);
              }

              if("{{$key}}" == 10)
              {
                $( api.column( 11 ).footer() ).html(novTotal);
                $('tr:eq(1) th:eq(11)', api.table().footer()).html(novCTotal);
              }

              if("{{$key}}" == 11)
              {
                $( api.column( 12 ).footer() ).html(decTotal);
                $('tr:eq(1) th:eq(12)', api.table().footer()).html(decCTotal);
              }

              count = "{{$key + 2}}";
              <?php endforeach ?>
              // $( api.column( +count ).footer() ).html(yearTotal);
                $( api.column( +count ).footer() ).html(yearTotal);
                $('tr:eq(1) th:eq('+count+')', api.table().footer()).html(yearCTotal);
              // $( api.column( +count ).footer() ).html(yearTotal);
            },
            error: function(){

            }
          });
        }
        else{
          $( api.column( 0 ).footer() ).html('Total');
          <?php foreach ($months as $key => $value): ?>
          if("{{$key}}" == 0)
          {
            $( api.column( 1 ).footer() ).html(janTotal);
          }
          else if("{{$key}}" == 1)
          {
            // if(febTotal == 0)
            // {
            //   table2.column(2).visible(false);
            // }
            // else
            // {
            //   table2.column(2).visible(true);
            // }
            $( api.column( 2 ).footer() ).html(febTotal);
          }
          else if("{{$key}}" == 2)
          {
            // if(marTotal == 0)
            // {
            //   table2.column(3).visible(false);
            // }
            // else
            // {
            //   table2.column(3).visible(true);
            // }
            $( api.column( 3 ).footer() ).html(marTotal);
          }
          else if("{{$key}}" == 3)
          {
            // if(aprTotal == 0)
            // {
            //   table2.column(4).visible(false);
            // }
            // else
            // {
            //   table2.column(4).visible(true);
            // }
            $( api.column( 4 ).footer() ).html(aprTotal);
          }
          else if("{{$key}}" == 4)
          {
            // if(mayTotal == 0)
            // {
            //   table2.column(5).visible(false);
            // }
            // else
            // {
            //   table2.column(5).visible(true);
            // }
            $( api.column( 5 ).footer() ).html(mayTotal);
          }
          else if("{{$key}}" == 5)
          {
            // if(junTotal == 0)
            // {
            //   table2.column(6).visible(false);
            // }
            // else
            // {
            //   table2.column(6).visible(true);
            // }
            $( api.column( 6 ).footer() ).html(junTotal);
          }
          else if("{{$key}}" == 6)
          {
            // if(julTotal == 0)
            // {
            //   table2.column(7).visible(false);
            // }
            // else
            // {
            //   table2.column(7).visible(true);
            // }
            $( api.column( 7 ).footer() ).html(julTotal);
          }
          else if("{{$key}}" == 7)
          {
            // if(augTotal == 0)
            // {
            //   table2.column(8).visible(false);
            // }
            // else
            // {
            //   table2.column(8).visible(true);
            // }
            $( api.column( 8 ).footer() ).html(augTotal);
          }
          else if("{{$key}}" == 8)
          {
            // if(sepTotal == 0)
            // {
            //   table2.column(9).visible(false);
            // }
            // else
            // {
            //   table2.column(9).visible(true);
            // }
            $( api.column( 9 ).footer() ).html(sepTotal);
          }
          else if("{{$key}}" == 9)
          {
            // if(octTotal == 0)
            // {
            //   table2.column(10).visible(false);
            // }
            // else
            // {
            //   table2.column(10).visible(true);
            // }
            $( api.column( 10 ).footer() ).html(octTotal);
          }
          else if("{{$key}}" == 10)
          {
            // if(novTotal == 0)
            // {
            //   table2.column(11).visible(false);
            // }
            // else
            // {
            //   table2.column(11).visible(true);
            // }
            $( api.column( 11 ).footer() ).html(novTotal);
          }
          else if("{{$key}}" == 11)
          {
            // if(decTotal == 0)
            // {
            //   table2.column(12).visible(false);
            // }
            // else
            // {
            //   table2.column(12).visible(true);
            // }
            $( api.column( 12 ).footer() ).html(decTotal);
          }

          count = "{{$key + 2}}";
          <?php endforeach ?>
          $( api.column( +count ).footer() ).html(yearTotal);

        }
      },
    });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
     // alert(column);
     var year = "{{$year}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.post({
      url : "{{ route('toggle-column-display') }}",
      dataType : "json",
      data : {type:'customer_sale_report',column_id:column,year:year,},
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
      error: function(request, status, error)
      {
        $('#loader_modal').modal('hide');
      }
    });
  });




//  $(document).on('click','#btn_reset',function(){
//         // $('#loader_modal').modal({
//         //   backdrop: 'static',
//         //   keyboard: false
//         // });

//       $("#apply_filter_btn").val("0");
//       $('#sale_person').val('');
//       $('.sale_person_filter').val('');
//       $('.selecting-customer-group').val('');
//       $("#loader_modal").modal('show');
//       $(".state-tags").select2("", "");
//       $('.table-customer-sales-report').DataTable().ajax.reload();
//     });


 $('#btn_reset').on('click',function(){
   $('input[type="search"]').val('');
    $("#apply_filter_btn").val("0");
      $('#sale_person').val('');
      $('.sale_person_filter').val('');
      $('.selecting-customer-group').val('');
      $("#loader_modal").modal('show');
      $(".state-tags").select2("", "");
      $('.table-customer-sales-report').DataTable().ajax.reload();
    });
    // $('.dataTables_filter input').unbind();
    // $('.dataTables_filter input').bind('keyup', function(e) {
    //   if(e.keyCode == 13) {
    //     $('#loader_modal').modal({
    //       backdrop: 'static',
    //       keyboard: false
    //     });
    //     $('#loader_modal').modal('show');
    //     table2.search($(this).val()).draw();
    //   }
    // });


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




    $(document).on('change','.sale_year',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      var selected = $(this).val();
      if($('.sale_year option:selected').val() != '')
      {
        // $('.table-customer-sales-report').DataTable().ajax.reload();
        window.location.href = "{{url('admin/customer-sales-report/')}}"+"/"+selected;
      }
    });



  $(document).on('change','.sale_person',function(){
    $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-customer-sales-report').DataTable().ajax.reload();
  });

  $(document).on('change','.sale_person_filter',function(){
    $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-customer-sales-report').DataTable().ajax.reload();
  });

  $(document).on('change','.selecting-customer-group',function(){
    $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-customer-sales-report').DataTable().ajax.reload();
  });

  $(document).on('click','.apply_filter',function(){
    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.table-customer-sales-report').DataTable().ajax.reload();
  });

  $(document).on('click','.export-btn',function(e){
    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }
    e.preventDefault();
    var count=0;
    if($('.sale_year').val()!='')
    {
      count=1;
    }
    if(count==0)
    {
      toastr.info('Year!', 'Please select the filter' ,{"positionClass": "toast-bottom-right"});
      return;
    }
    var form_data=$('#export-customer-sale-report-form').serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-customer-sale-report')}}",
      data:form_data,
      beforeSend:function(){
        // $('.export-btn').html('Please Wait...');
        $('.export-btn').prop('disabled',true);

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          // $('.export-btn').html('EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          $('.download-btn-div ').addClass('d-none');

          console.log("Calling Function from first part");
          checkStatusForProducts();
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          // $('.export-btn').html(data.msg);
          $('.export-btn').prop('disabled',true);
          $('.download-btn-div ').addClass('d-none');
          checkStatusForProducts();
        }
      },
      error: function(request, status, error){
        {{-- $("#loader_modal").modal('hide'); --}}
        // $('.export-btn').html('Create New Export');
        $('.export-btn').prop('disabled',false);
      }
    });
  });

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-sales-customer-report')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          // $('.export-btn').html('EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          $('.download-btn-div ').addClass('d-none');
          checkStatusForProducts();
        }
      }
    });
  });
  function checkStatusForProducts()
  {
    $.ajax({
            method:"get",
            url:"{{route('recursive-export-status-customer-sales-report')}}",
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
                    // var href="{{asset('storage/app')}}"+"/"+data.file_name;
                    var href="{{ url('get-download-xslx')}}"+"/"+data.file_name;
                    $('.export-alert-success').removeClass('d-none');
                    $('.export-alert').addClass('d-none');
                    // $('.export-btn').html('Create New Export');
                    $('.export-btn').prop('disabled',false);
                    // $('.download-btn-div ').html('');
                    $('#export-download-btn').attr("href",href);
                    // $('.download-btn-div a').attr("href",href);
                    $('.download-btn-div ').removeClass('d-none');
                    $('.primary-btn').addClass('d-none');
                  }
                  else if(data.status==2)
                  {
                    $('.export-alert-success').addClass('d-none');
                    $('.export-alert').addClass('d-none');
                    // $('.export-btn').html('Create New Export');
                    $('.export-btn').prop('disabled',false);
                    $('.export-alert-another-user').addClass('d-none');
                    toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
                    console.log(data.exception);
                  }
              }
          });
  }

  var btn_click = false;
    $(document).on('click',function(e){
      if ($(e.target).closest(".dt-button-collection").length === 1) {
          btn_click = true;
      }

      if(btn_click)
      {
        if ($(e.target).closest(".dt-button-collection").length === 0) {
          btn_click = false;
          $('.table-customer-sales-report').DataTable().ajax.reload();
          // alert('clicked outside');
        }
      }
    });
});
</script>
@stop

