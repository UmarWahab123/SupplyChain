@extends('ecom.layouts.layout')

@section('title','Product Sales Report | Supply Chain')

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
.dt-buttons
{
  display: inline-block;
  float: left;
  margin-right: 5px;
}
.dataTables_scrollFootInner
{
  width: 100% !important;
}

/*table.dataTable thead .sorting { background: url('public/sort/sort_both.png') no-repeat center right; 
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('public/sort/sort_desc.png') no-repeat center right; 
  background-size: 5vh;}*/
  
</style>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h4 class="maintitle text-uppercase fontbold">Product Sales Report</h4>
  </div> 
</div>


{{--Filters start here--}}
<div class="row mb-2">
  <div class="col-lg-12 col-md-12 title-col mb-2">
    {{--<div class="d-sm-flex justify-content-between">

      <div class="col-3"> 
        <label>Choose Supplier</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags supplier_id" name="supplier_id" required="true">
          <option value="" selected="">Choose Supplier</option>
          @foreach($suppliers as $s)
          <option value="{{$s->id}}">{{$s->reference_name}}</option>
          @endforeach
        </select> 
      </div>

      <div class="col-3">
        <label>Select a Customer Group</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags customer_group" name="filter">
          <option value="" selected="">Select a Customer Group</option>
          @foreach($customer_categories as $cc)
          <option value="{{$cc->id}}">{{$cc->title}}</option>
          @endforeach
        </select>
      </div>
      
      @if(Auth::user()->role_id !== 3)
      <div class="col-3">
        <label>Select Sales Person</label>
        <select class="font-weight-bold form-control-lg form-control js-states sales_person state-tags" name="sales_person" >
          <option value="" selected="">Select Sales Person</option>
          @foreach($sales_persons as $sp)
          <option value="{{$sp->id}}">{{$sp->name}}</option>
          @endforeach
        </select>
      </div>
      @endif

       <div class="col-3">
        <label>Choose Customer</label>
        <select class="font-weight-bold form-control-lg form-control customer_id state-tags" name="customer_id" >
          <option value="" >Choose Customer</option>
          @foreach($customers as $s)
          @php $id = Session::get('customer_id');@endphp
          <option value="{{$s->id}}" {{ ($s->id == @$id )? "selected='true'":" " }}>{{$s->reference_name}}</option>
          @endforeach
        </select>
      </div>

      <!-- <div class="col"></div>
      <div class="col"></div>
      <div class="col"></div> -->

      <div class="col-2 d-none">
        <label></label>
        <div class="input-group-append">
          <button id="export_s_p_r" class="btn recived-button export_btn" >Export</button>  
        </div>
      </div>


    </div>--}}
  </div>
  <div class="col-lg-12 col-md-12 title-col mb-2">
    <div class="d-sm-flex justify-content-between">

     

      <div class="col-3">
        <label>Choose Product</label>
        <select class="font-weight-bold form-control-lg form-control product_id state-tags" name="product_id" >
         <option value="" selected="">Choose Product</option>
          @foreach($products as $s)          
          <option value="{{$s->id}}">{{$s->system_code}} - {{$s->short_desc}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-3">
        <label>Choose Category</label>
        <select class="font-weight-bold form-control-lg form-control category_id state-tags" name="category_id" >
         <option value="" selected="">Choose Category</option>
          @foreach($product_parent_categories as $ppc)          
          <option value="{{$ppc->id}}">{{$ppc->title}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-3">
        <label>Choose Sub Category</label>
        <select class="font-weight-bold form-control-lg form-control sub_category_id state-tags" name="sub_category_id" >
         <option value="" selected="">Choose Sub Category</option>
          @foreach($product_sub_categories as $psc)          
          <option value="{{$psc->id}}">{{$psc->title}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-3" >
        <label>From Date</label>
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>


    </div>
    
  </div>

  <div class="col-lg-12 col-md-12 title-col mb-2">
    <div class="d-sm-flex justify-content-between">
      <div class="col-3" >
        <label>To Date</label>
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
      <div class="col-3 d-flex mt-auto">
        <div class="form-check mr-3">     
          <input type="radio" class="form-check-input dates_changer"  name="date_radio" value='2' checked>
          <label class="form-check-label" for="exampleCheck1"><b>Created Date</b></label>
        </div> &nbsp; 
        <div class="form-check mr-4">     
          <input type="radio" class="form-check-input dates_changer"  name="date_radio" value='1'>
          <label class="form-check-label" for="exampleCheck1"><b>Delivery Date</b></label>
        </div>
      </div>
      <div class="col-2" style="">
      <div class="form-group mb-0">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-1">
        <button class="btn recived-button apply_date" type="button">Apply Dates</button>  
      </div>
      </div>
    </div>


      <div class="col-2">
        <label style="visibility: hidden;">reset</label>
        <div class="input-group-append ml-1">
          <button class="btn recived-button reset-btn rounded" type="reset">Reset</button>  
        </div>
      </div>
<!--       <div class="col-2">
        <label style="visibility: hidden;">Hidden</label>
        <div class="input-group-append ml-1">
          <button id="export_s_p_r" class="btn recived-button export_btn d-none" >Export</button>
        </div>
      </div> -->
    </div>
  </div>


</div>
{{--Filters ends here--}}
<div class="row">
  <div class="col-12 d-flex">
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="supplier"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="customer_group"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="sales_person"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="customer"></span>
    <span class="d-none font-weight-bold  alert alert-primary mb-0" id="product"></span>
  </div>
</div>

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
          <a download href="{{ url('storage/app/product-sale-export.xlsx')}}"><u>Click Here</u></a>
          </b> 
        </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is already being prepared by another user! Please wait.. </b>
  </div>
      <table class="table entriestable table-bordered text-center product-sales-report">
        
        <thead>
          <tr>
            <th class="noVis">View</th>
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>
              {{$global_terminologies['brand']}}
            </th>
            <th>{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Selling<br>Unit</th>
            <th>Total <br>{{$global_terminologies['qty']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="5">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="5">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            {{-- @if (Auth::user()->role_id === 1) --}}
            <th>Avg <br>Unit <br>Cost</th>
            {{-- @endif --}}
            <th>Avg <br>Unit <br> Price</th>
            <th>Sub Total</th>
            <th>Total<br>Amount
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="7">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="7">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Vat(THB)</th>
            <th>Total <br>Stock</th>
            @if($getCategories->count() > 0)
            
            @foreach($getCategories as $cat)
              <th>{{$cat->title}}<br>( Fixed Price )
              </th>
            @endforeach
            @endif
          </tr>
        </thead>
        <tfoot align="right">
          <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            {{-- @if (Auth::user()->role_id == 1) --}}
            <th></th>
            {{-- @endif --}}
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            @if($getCategories->count() > 0)
            @foreach($getCategories as $cat)
              <th>
              </th>
            @endforeach
            @endif
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

<!-- <form id="export_product_sale_report" method="post" action="{{route('export-product-sales-report')}}"> -->
  <form id="export_product_sale_report">
  @csrf
  <input type="hidden" name="supplier_id_exp" id="supplier_id_exp">
  <input type="hidden" name="customer_group_id_exp" id="customer_group_id_exp">
  <input type="hidden" name="sales_person_exp" id="sales_person_exp">
  <input type="hidden" name="customer_id_exp" id="customer_id_exp">
  <input type="hidden" name="product_id_exp" id="product_id_exp">
  <input type="hidden" name="category_id_exp" id="category_id_exp">
  <input type="hidden" name="sub_category_id_exp" id="sub_category_id_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="date_type_exp" id="date_type_exp" value='2'>
</form>

@endsection

@php
  $hidden_by_default = '';
@endphp

@section('javascript')
<script type="text/javascript">
    // Columns Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");
    
    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.product-sales-report').DataTable().ajax.reload();

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

  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker('setDate',last_month);
  $("#to_date").datepicker('setDate','today');
  
  $(document).ready(function(){
    @if(Session::has('find'))
      var id = "{{Session::get('customer_id')}}";
      var dat = "{{Session::get('month')}}";
      var full_date = dat.split('-');
      var year = full_date[0];
      var month = full_date[1];
      var datee = '01';
      var year1 = full_date[0];
      var month1 = full_date[1];
      var getDaysInMonth = function(month,year) {
        return new Date(year, month, 0).getDate();
      };
      var datee1 = getDaysInMonth(month1, year1);
      var from_date =  datee+ "/" + month + "/" + year;
      var to_date =  datee1+ "/" + month1 + "/" + year1;
      document.querySelector("#from_date").value = from_date;
      document.querySelector("#to_date").value = to_date;
    @endif
  });

  var date = $('#from_date').val();
    $("#from_date_exp").val(date);
  var date = $('#to_date').val();
    $("#to_date_exp").val(date);

    $('input[type=radio][name=date_radio]').change(function() {
    if (this.value == '1') {
      $('#date_type_exp').val(this.value);
      $('.product-sales-report').DataTable().ajax.reload();
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
    }
    else if (this.value == '2') {
      $('#date_type_exp').val(this.value);
      $('.product-sales-report').DataTable().ajax.reload();
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
     $("#loader_modal").modal('show');
    }
});

 var hidden_cols = "{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}";
  hidden_cols = hidden_cols.split(',');

  $(function(e){
    var show_total_cost = false;
    var role = "{{Auth::user()->role_id}}";
    if(role == 1)
    {
      show_total_cost = true;
       if( hidden_cols.includes("6") )
      {
        var show_total_cost = false;
      }
    }
    $(".state-tags").select2();
    var table2 = $('.product-sales-report').DataTable({
      processing: true,
      searching:false,
      oLanguage: 
      {
        sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
      },
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      // ordering: true,
      serverSide: true,
      dom: 'Blfrtip',
      "aaSorting": [[3]],
      bSort: false,
      info: true,
      "columnDefs": [
       { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 1,2,3 ] },
        { className: "dt-body-right", "targets": [ 4,5,6] },
        // { "orderable": false, "targets": [0,3,4,5,6] }
      ],
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      // dom: 'Blfrtip',
      // pageLength: {{500}},
      lengthMenu: [ 100,200,300,400,500],
      buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
      ajax: {
        url:"{!! route('get-ecom-sold-product-data-for-report') !!}",
        data: function(data) {data.from_date = $('#from_date').val(),
         data.to_date = $('#to_date').val(), data.warehouse_id = $('.warehouse_id option:selected').val(),
         data.customer_id = $('.customer_id option:selected').val(),data.product_id = $('.product_id option:selected').val(),
         data.sales_person = $('.sales_person option:selected').val(),data.supplier_id = $('.supplier_id option:selected').val(),
         data.customer_group = $('.customer_group option:selected').val(),
         data.category_id = $('.category_id option:selected').val(),
         data.sub_category_id = $('.sub_category_id option:selected').val(),
         data.sortbyparam = column_name, 
         data.sortbyvalue = order,  data.date_type = $("input[name='date_radio']:checked").val() },
        method: "get",
      },
      columns: [
        { data: 'view', name: 'view'},
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'selling_unit', name: 'selling_unit' },
        { data: 'total_quantity', name: 'total_quantity' },
        { data: 'total_cost', name: 'total_cost'},
        { data: 'avg_unit_price', name: 'avg_unit_price' },
        { data: 'sub_total', name: 'sub_total' },
        { data: 'total_amount', name: 'total_amount' },
        { data: 'vat_thb', name: 'vat_thb' },
        { data: 'total_stock', name: 'total_stock' },
         // Dynamic columns start
        @if($getCategories->count() > 0)
        @foreach($getCategories as $cat)
          { data: '{{$cat->title}}', name: '{{$cat->title}}'},
        @endforeach
        @endif
        // Dynamic columns end
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
      footerCallback: function ( row, data, start, end, display ) {
        var api            = this.api();
        var json           = api.ajax.json();
        var total_quantity = json.total_quantity;
        var total_cost     = json.total_cost;
        var total_amount   = json.total_amount;
        var avg_unit_price = json.avg_unit_price;

        total_quantity = parseFloat(total_quantity).toFixed(2);
        total_quantity = total_quantity.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        total_cost     = parseFloat(total_cost).toFixed(2);
        total_cost     = total_cost.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        total_amount   = parseFloat(total_amount).toFixed(2);
        total_amount   = total_amount.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        avg_unit_price = parseFloat(avg_unit_price).toFixed(2);
        avg_unit_price = avg_unit_price.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
         
        $( api.column( 0 ).footer() ).html('Total');
        $( api.column( 5 ).footer() ).html(total_quantity);
        if(show_total_cost == true){
          $( api.column( 6 ).footer() ).html(total_cost);
        }           
        $( api.column( 7 ).footer() ).html(avg_unit_price);
        $( api.column( 9 ).footer() ).html(total_amount);
          
      },
   });

      table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'product_sale_report',column_id:column},
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

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13) 
      {
        table2.search($(this).val()).draw();
      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });
 

  $('.warehouse_id').change(function() {
    $("#warehouse_id_exp").val($('.warehouse_id option:selected').val());

    if($('.warehouse_id').val() != '')
    {
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      $('.product-sales-report').DataTable().ajax.reload();
    }
  });

  $('.customer_id').change(function() {
    $("#customer_id_exp").val($('.customer_id option:selected').val());
    var selected = $('.customer_id option:selected').val();
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();

    if(selected == '')
    {
      $("#customer").addClass('d-none');
    }
    else
    {
      var name = $('.customer_id option:selected').text();
      $("#customer").removeClass('d-none');
      $("#customer").html('Customer Name: '+name);
    }
  });

  $('.category_id').change(function() {
    $("#category_id_exp").val($('.category_id option:selected').val());
    var selected = $('.category_id option:selected').val();
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
  });

  $('.sub_category_id').change(function() {
    $("#sub_category_id_exp").val($('.sub_category_id option:selected').val());
    var selected = $('.sub_category_id option:selected').val();
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
  });

  $('.product_id').change(function() {
    $("#product_id_exp").val($('.product_id option:selected').val());
    var selected = $('.product_id option:selected').val();
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();

    if(selected == '')
    {
      $("#product").addClass('d-none');
    }
    else
    {
      var name = $('.product_id option:selected').text();
      $("#product").removeClass('d-none');
      $("#product").html('Product Desc: '+name);
    }
  });

  $(document).on('change','.sales_person',function(){
    $("#sales_person_exp").val($('.sales_person option:selected').val());
    var selected = $('.sales_person option:selected').val();
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();    

    if(selected == '')
    {
      $("#sales_person").addClass('d-none');
    }
    else
    {
      var name = $('.sales_person option:selected').text();
      $("#sales_person").removeClass('d-none');
      $("#sales_person").html('Sales Person Name: '+name);
    }
  });

  $(document).on('change','.supplier_id',function(){
    $("#supplier_id_exp").val($('.supplier_id option:selected').val());

    var selected = $(this).val();
    if(selected == '')
    {
      $("#supplier").addClass('d-none');
    }
    else
    {
      var name = $('.supplier_id option:selected').text();
      $("#supplier").removeClass('d-none');
      $("#supplier").html('Supplier: '+name);
    }
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
  });

  $(document).on('change','.customer_group',function(){
    $("#customer_group_id_exp").val($('.customer_group option:selected').val());
    var selected = $('.customer_group option:selected').val();

    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-sales-report').DataTable().ajax.reload(); 

    if(selected == '')
    {
      $("#customer_group").addClass('d-none');
    }
    else
    {
      var name = $('.customer_group option:selected').text();
      $("#customer_group").removeClass('d-none');
      $("#customer_group").html('Customer Group: '+name);
    }

  });

  $('#from_date').change(function() {
    var date = $('#from_date').val();
    $('#from_date_exp').val(date);
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $('#to_date').change(function() {
    var date = $('#to_date').val();
    $('#to_date_exp').val(date);
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $(document).on('click','.apply_date',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
  });

  $('.reset-btn').on('click',function(){
    $('.customer_id').val('');
    $('.product_id').val('');
    $('.warehouse_id').val('');
    $('.sub_category_id').val('');
    $('.category_id').val('');
    $('.sales_person').val('');
    $('.supplier_id').val('');
    $('.customer_group').val('');
    $('#from_date').val('');
    $('#to_date').val('');
    $(".state-tags").select2("", "");
    // $('#date_type_exp').val('');
    $('#to_date_exp').val('');
    $('#from_date_exp').val('');
    $('#sub_category_id_exp').val('');
    $('#category_id_exp').val('');
    $('#product_id_exp').val('');
    $('#customer_id_exp').val('');
    $('#sales_person_exp').val('');
    $('#supplier_id_exp').val('');
    $('#customer_group_id_exp').val('');
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
    $("#supplier").addClass('d-none');
    $("#customer_group").addClass('d-none');
    $("#sales_person").addClass('d-none');
    $("#customer").addClass('d-none');
    $("#product").addClass('d-none');
  });

  // $('.export_btn').on('click',function(){
  //   $("#export_product_sale_report").submit(); 
  // });

  $(document).on('click','.export_btn',function(){

     var form=$('#export_product_sale_report');
     
    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"post",
      url:"{{route('export-product-sales-report')}}",
      data:form_data,          
      beforeSend:function(){
        $('.export_btn').prop('disabled',true);             
      },
      success:function(data){            
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export_btn').prop('disabled',true); 
          console.log("Calling Function from first part");
          checkStatusForProductReport();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export_btn').prop('disabled',true); 
          checkStatusForProductReport();
        }
      },
      error:function(){
        $('.export_btn').prop('disabled',false);          
      }
    }); 
});

    $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-product-report')}}",
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
          $('.export_btn').prop('disabled',true);
          checkStatusForProductReport();
        }
      }
    });
  });
  function checkStatusForProductReport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-product-report')}}",
      success:function(data){                           
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForProductReport();
            }, 5000);
        }    
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').prop('disabled',false); 
    
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').prop('disabled',false); 
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

</script>
@stop




