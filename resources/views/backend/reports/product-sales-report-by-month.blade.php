@extends('backend.layouts.layout')

@section('title','Users Management | Supply Chain')

@section('content')
<style type="text/css">
.select2-results__option
{
  display: block !important;
  overflow:  hidden !important;
  white-space: nowrap !important;
}
#select2-choose_product_select-results .select2-results__option
{
  white-space: normal !important;
}
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}

  .dataTables_scrollFootInner
  {
    width: 100% !important;
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
          <li class="breadcrumb-item active">Product Sales Report By Month</li>
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
      <h4 class="text-uppercase fontbold">Product Sale Report By Month</h4>
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
  <form id="export-product-sale-report-form" class="filters_div" method="post">
    <input type="hidden" name="year_filter" value="{{$year}}">
<div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between row">
        {{-- <div class="col"></div> --}}

        <div class="col-md-2 col-6">
          <select  class="font-weight-bold form-control-lg form-control supplier state-tags" name="supplier" id="supplier" >
              <option value="" selected>Choose Supplier</option>
              @foreach($suppliers as $supplier)
              <option value="{{ $supplier->id }}">{{ $supplier->reference_number.'-'.$supplier->reference_name}}</option>
              @endforeach
            </select>
        </div>

        <div class="col-md-2 col-6">
          <select class="font-weight-bold form-control-lg form-control selecting-customer-group state-tags" name="customer_categories" >
            <option value="">Choose Customer</option>
              @foreach($customer_categories as $cat)
              <option value="{{'pri-'.$cat->id }}">{{ $cat->title }}</option>
              @foreach($cat->customer as $customer)
                <option value="{{'sub-'.$customer->id }}" title="{{ $customer->reference_number.'-'.$customer->reference_name }}"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $customer->reference_number.'-'.$customer->reference_name }} {!! $extra_space !!} {{ $cat->title }}</option>
              @endforeach
              @endforeach
          </select>
        </div>

        <div class="col-md-2 col-6">
          <select  class="font-weight-bold form-control-lg form-control product state-tags" name="product" id="product" >
              <option value="" selected>Choose Product</option>
              @foreach($products as $product)
              <option value="{{ $product->id }}">{{ $product->refrence_code.'-'.$product->short_desc}}</option>
              @endforeach
            </select>
        </div>

        <div class="col-md-2 col-6">
          <select class="font-weight-bold form-control-lg form-control product_category state-tags" name="product_category" id="product_category">
            <option value="">Choose Product Category</option>
              @foreach($product_categories as $cat)
              <option value="{{'pri-'.$cat->id }}">{{ $cat->title }}</option>
              @foreach($cat->get_Child as $child)
                <option value="{{'sub-'.$child->id }}" title="{{ $child->title }}"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $child->title}} {!! $extra_space !!} {{ $cat->title }}</option>
              @endforeach
              @endforeach
          </select>
        </div>

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
          <select  class="font-weight-bold form-control-lg form-control sale_person_filter state-tags" name="sale_person_filter" >
              <option value="" selected>Choose a Sales Person</option>
              @foreach($sales_person_filter as $sale_person)
              <option value="{{ $sale_person->id }}">{{ $sale_person->name }}</option>
              @endforeach
            </select>
        </div>
        @endif

        @php
          if($file_name==null)
          $className='d-none';
          else
          $className='';
        @endphp

        <div class="col-md-2 col-6">
        @csrf
        <select class="font-weight-bold form-control-lg form-control sale_year state-tags" name="sale_year" >
          <option value="" disabled>Choose year</option>
          @foreach($sales_years as $key => $sale_year)
          <option value="{{ $sale_year }}" {{ $sale_year == $year ? 'selected="selected"' : '' }}>{{ $sale_year }}</option>
          @endforeach
        </select>
      </div>

    </div>
  </div>
</div>
<div class="row mb-1">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between row">
    <div class="col-md-2 col-6">
      <select class="font-weight-bold form-control-lg form-control order_status state-tags" name="sale_year" >
        <option value="" selected title="All">All (Draft Invoice & Invoice)</option>
        <option value="2" title="Draft Invoice">Draft Invoice</option>
        <option value="3" title="Invoice">Invoice</option>

      </select>
    </div>

      <div class="col-md-2 col-6">
        <div class="text-center">
          <!-- <button class="btn button-st export-btn" id="export_customer_sale_report" >Create New Export</button> -->
          <span class="vertical-icons float-right" id="btn_reset" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
           <span class="apply_filter vertical-icons float-right" title="Apply Filters" id="apply_filter">
            <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
          </span>

        </div>
      </div>
    </div>
  </div>
</div>
    <input type="hidden" name="months" value="{{$var}}">
    <input type="hidden" name="sortbyparam" id="sortbyparam">
    <input type="hidden" name="sortbyvalue" id="sortbyvalue">

    <input type="hidden" name="supplier" id="supplier_exp">
    <input type="hidden" name="product" id="product_exp">
    <input type="hidden" name="product_category" id="product_category_exp">
    <input type="hidden" name="order_status" id="order_status_exp">
    <input type="hidden" name="sales_person" id="sales_person_exp">
    <input type="hidden" name="sales_person_filter" id="sales_person_filter_exp">
    <input type="hidden" name="selecting-customer-group" id="selecting_customer_group_exp">

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
      <table class="table entriestable table-bordered product-sales-report-by-month text-center">
        <thead>
          <tr>
           <th>{{$global_terminologies['our_reference_number'] }}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>{{$global_terminologies['brand']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="2">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="2">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>Selling Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="4">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="4">
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
          </tr>
        </thead>

        <tfoot>
          <tr>
            <th>Total</th>
            <th></th>
            <th></th>
            <th></th>
            @foreach($months as $key => $value)
              <th id="month_{{$key}}"></th>
            @endforeach
          </tr>
        </tfoot>
      </table>
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
    var column_name;

    $('.sorting_filter_table').on('click',function(){
      $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
      $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

      order = $(this).data('order');
      column_name = $(this).data('column_name');
      $('#sortbyparam').val(column_name);
      $('#sortbyvalue').val(order);

      $('.product-sales-report-by-month').DataTable().ajax.reload();

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

    table2 = $('.product-sales-report-by-month').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      ordering: false,
      lengthMenu:[100,200,300,400,500],
      serverSide: true,
      scrollX:true,
      scrollY : '90vh',
      dom: 'Blfrtip',
      scrollCollapse: true,
      "columnDefs": [
       { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      { className: "dt-body-left", "targets": [0,1,2,3,4] }

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
          url: "{!! route('get-product-sale-report-by-month') !!}",
          data: function(data) { data.sale_year = $('.sale_year option:selected').val(),data.sale_person = $('.sale_person option:selected').val(),data.sale_person_filter = $('.sale_person_filter option:selected').val(),data.customer_categories = $('.selecting-customer-group option:selected').val(),
          data.supplier = $('.supplier option:selected').val(),
          data.product = $('.product option:selected').val(),
          data.product_category = $('.product_category option:selected').val(),
          data.order_status = $('.order_status option:selected').val(),
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.type = 'body'
      } ,
           method: "get",
        },


      columns: [
        // { data: 'checkbox', name: 'checkbox' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'selling_unit', name: 'selling_unit' },
        <?php foreach ($months as $mon): ?>
          {data: "{{$mon}}", name: "{{$mon}}", className: 'dt-body-right'},
        <?php endforeach ?>

      ],
     initComplete: function () {
      $('.dataTables_scrollHead').css('overflow-x', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      $('body').find('.dataTables_scrollHead').addClass("scrollbar");

      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
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
        // var yearTotal ='';
        // var overAllTotal='';
        // var count='';
        var api = this.api()
        var json = api.ajax.json();
        $('#loader_modal').modal('hide');
        $.ajax({
            method:"get",
            url: "{!! route('get-product-sale-report-by-month') !!}",
            data: {
              sale_year : $('.sale_year option:selected').val(),
              sale_person : $('.sale_person option:selected').val(),
              sale_person_filter : $('.sale_person_filter option:selected').val(),
              customer_categories : $('.selecting-customer-group option:selected').val(),
              supplier : $('.supplier option:selected').val(),
              product : $('.product option:selected').val(),
              product_category : $('.product_category option:selected').val(),
              order_status : $('.order_status option:selected').val(),
              sortbyparam : column_name,
              sortbyvalue : order,
              type : 'footer'
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

              $( api.column( 0 ).footer() ).html('Total');
              <?php foreach ($months as $key => $value): ?>
              if("{{$key}}" == 0)
              {
                $( api.column( 4 ).footer() ).html(janTotal);
              }

              if("{{$key}}" == 1)
              {
                $( api.column( 5 ).footer() ).html(febTotal);
              }

              if("{{$key}}" == 2)
              {
                $( api.column( 6 ).footer() ).html(marTotal);
              }

              if("{{$key}}" == 3)
              {
                $( api.column( 7 ).footer() ).html(aprTotal);
              }

              if("{{$key}}" == 4)
              {
                $( api.column( 8 ).footer() ).html(mayTotal);
              }

              if("{{$key}}" == 5)
              {
                $( api.column( 9 ).footer() ).html(junTotal);
              }

              if("{{$key}}" == 6)
              {
                $( api.column( 10 ).footer() ).html(julTotal);
              }

              if("{{$key}}" == 7)
              {
                $( api.column( 11 ).footer() ).html(augTotal);
              }

              if("{{$key}}" == 8)
              {
                $( api.column( 12 ).footer() ).html(sepTotal);
              }

              if("{{$key}}" == 9)
              {
                $( api.column( 13 ).footer() ).html(octTotal);
              }

              if("{{$key}}" == 10)
              {
                $( api.column( 14 ).footer() ).html(novTotal);
              }

              if("{{$key}}" == 11)
              {
                $( api.column( 15 ).footer() ).html(decTotal);
              }
              <?php endforeach ?>
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

            }

          });
      }
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
      data : {type:'product_sale_report_by_month',column_id:column,year:year,},
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


 $('#btn_reset').on('click',function(){
   $('input[type="search"]').val('');
    $("#apply_filter_btn").val("0");
      $('#sale_person').val('');
      $('.sale_person_filter').val('');
      $('.supplier').val('');
      $('.product').val('');
      $('.product_category').val('');
      $('.selecting-customer-group').val('');
      $('#export-product-sale-report-form').val();
      $("#loader_modal").modal('show');
      $(".state-tags").select2("", "");

      $('.product-sales-report-by-month').DataTable().ajax.reload();
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




    $(document).on('change','.sale_year',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      var selected = $(this).val();
      if($('.sale_year option:selected').val() != '')
      {
        window.location.href = "{{url('admin/product-sales-report-by-month/')}}"+"/"+selected;
      }
    });



  $(document).on('change','.sale_person',function(){
    $("#apply_filter_btn").val("1");
  });

  $(document).on('change','.sale_person_filter',function(){
    $("#apply_filter_btn").val("1");
  });

  $(document).on('change','.selecting-customer-group',function(){
    $("#apply_filter_btn").val("1");
  });

  $(document).on('click','.apply_filter',function(){
    $("#apply_filter_btn").val("0");
    $('#sortbyparam').val(column_name);
    $('#sortbyvalue').val(order);
    $('#supplier_exp').val($('.supplier').val());
    $('#product_exp').val($('.product').val());
    $('#product_category_exp').val($('.product_category').val());
    $('#order_status_exp').val($('.order_status').val());
    $('#sale_person_exp').val($('.sales_persons').val());
    $('#sales_person_filter_exp').val($('.sales_person_filter').val());
    $('#selecting_customer_group_exp').val($('.selecting-customer-group').val());
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.product-sales-report-by-month').DataTable().ajax.reload();
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
    var form_data=$('#export-product-sale-report-form').serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-product-sale-report-by-month')}}",
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
      url:"{{route('check-status-for-first-time-product-sales-report-by-month')}}",
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
            url:"{{route('recursive-export-status-product-sales-report-by-month')}}",
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
          $('.product-sales-report-by-month').DataTable().ajax.reload();
          // alert('clicked outside');
        }
      }
    });
});
</script>
@stop

