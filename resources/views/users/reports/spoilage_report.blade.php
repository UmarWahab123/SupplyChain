@extends('users.layouts.layout')

@section('title','Users Management | Supply Chain')

@section('content')
<?php
use Carbon\Carbon;
?>
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
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
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4)
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
          <li class="breadcrumb-item active">Spoilage Report</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-8">
    <h5 class="maintitle text-uppercase fontbold">Spoilage Report</h5>
  </div>
  @if(Auth::user()->role_id != 9)
  <div class="col-2  ">
  </div>

  <div class="col-2">
    <div class="pull-right">
    <!-- <label style="visibility: hidden;">nothing</label> -->
    <div class="input-group-append">
      <span class="export-btn vertical-icons pull-right d-none" title="Create New Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
    </div>
  </div>
  @endif
</div>
<div class="row mb-0 filters_div">
  <div class="col-lg-12 col-md-12 title-col">
    <div class="row justify-content-between">
      <div class="col-md-2 col-6">
        <div class="form-group">
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="form-group">
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
        </div>
      </div>
      <div class="col-md-2 col-6" style="">
      <div class="form-group">
      <div class="input-group-append ml-1">
        <!-- <button class="btn recived-button apply_date" type="button">Apply Filters</button>   -->
         <span class="apply_date vertical-icons mr-4" title="Apply Filters">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
      </span>
      <span class="reset-btn vertical-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
      </div>
      </div>
    </div>
    <div class="col-md-2 col-6"></div>
    <div class="col-md-2 col-6"></div>
    <div class="col-md-2 col-6"></div>
    </div>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg custompadding">
       <div class="alert alert-primary export-alert d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is being prepared! Please wait.. </b>
      </div>
        <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>

          <b>Export file is ready to download.
          <a download href="{{'storage/app/Stock-Movement-Report.xlsx'}}"><u>Click Here</u></a>
          </b>
        </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-spoilage-report text-center">
        <thead>
          <tr>
            <th>Occurrence</th>
            <th>{{$global_terminologies['our_reference_number']}}
            </th>
            <th>Shippment/Inbound PO</th>
            <th>Quanity</th>
            <th>Spoilage Type</th>
          </tr>
        </thead>
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

<div>

</div>
<form id="export_sold_product_report_form" method="post">
  @csrf
  <input type="hidden" name="warehouse_id_exp" id="warehouse_id_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="supplier_id_exp" id="supplier_id_exp">
  <input type="hidden" name="unit_id_exp" id="unit_id_exp">
  <input type="hidden" name="all_movement_exp" id="all_movement_exp">
  <input type="hidden" name="product_type_exp" id="product_type_exp">
  <input type="hidden" name="product_type_2_exp" id="product_type_2_exp">
  <input type="hidden" name="product_type_3_exp" id="product_type_3_exp">
  {{-- <input type="hidden" name="tableSearchField" id="tableSearchField"> --}}
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="column_name" id="column_name">
  <input type="hidden" name="sort_order" id="sort_order">
</form>


@endsection

@section('javascript')
<script type="text/javascript">

  $("#from_date").datepicker({
     format: "dd/mm/yyyy",
    autoHide: true,


  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var currentTime = new Date();
  // First Date Of the month
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', startDateFrom);
  $('#to_date').datepicker('setDate', 'today');

var sort_order = 1;
var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    sort_order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#column_name').val(column_name);
    $('#sort_order').val(sort_order);

    $('.table-spoilage-report').DataTable().ajax.reload();

    if($(this).data('order') ==  'ASC')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == 'DESC')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });

  $(function(e){

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

    var table2 = $('.table-spoilage-report').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      ordering: false,
      lengthMenu:[50,100,150,200],
      serverSide: true,
      ajax:
      {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url: "{!! route('get-spoilage-report') !!}",
        data: function(data) {
          data.from_date = $('#from_date').val(),
          data.to_date = $('#to_date').val(),
          data.column_name = column_name,
          data.sort_order = sort_order
         },
      },
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      dom: 'Blfrtip',
      buttons: [
      ],

      columns: [
        { data: 'occurence', name: 'occurence' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'shippment_no', name: 'shippment_no' },
        { data: 'quantity', name: 'quantity' },
        { data: 'spoilage_type', name: 'spoilage_type' }
      ],
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
      }
    });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

      var arr = '';
      var all = arr;
      if(all == '')
      {
        var col = column;
      }
      else
      {
        var col = all[column];
      }
      var columns = table2.settings().init().columns;
      var name = columns[col].name;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'stock_movement_report',column_id:col},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
           if(name.toLowerCase().indexOf('current') >= 0)
           {
              table2.ajax.reload();
           }
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

  $(document).on('click','.apply_date',function(){

      $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.table-spoilage-report').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

  });

  $('.reset-btn').on('click',function(){
    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
      $('#loader_modal').modal('show');
      $('#from_date').datepicker('setDate', startDateFrom);
      $('input[type=search]').val('');
      $('#to_date').datepicker('setDate', 'today');
      sort_order = 1;
      column_name = '';
      $('.table-spoilage-report').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });


  });

  $(document).on('click','.export-btn',function(){

    $("#from_date_exp").val($('#from_date').val());
    $("#to_date_exp").val($('#to_date').val());


    var form=$('#export_sold_product_report_form');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-status-stock-movement-report')}}",
      data:form_data,
      beforeSend:function(){
        $('.export-btn').attr('title','Please Wait...');
        $('.export-btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').attr('title','EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForProducts();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          $('.export-btn').attr('title','EXPORT is being Prepared');
          checkStatusForProducts();
        }
      },
      error:function(){
         $('.export-btn').attr('title','Create New Export');
        $('.export-btn').prop('disabled',false);
      }
    });
  });

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-stock-movement-report')}}",
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
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.download-btn').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }
</script>
@stop

