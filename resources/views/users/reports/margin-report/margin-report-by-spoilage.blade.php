@extends('backend.layouts.layout')

@section('title','Product Sales Report | Supply Chain')

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
          <li class="breadcrumb-item active">Margin Report by Spoilage</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h5 class="maintitle text-uppercase fontbold">Admin Management & Sales Report</h5>
  </div>
  <div class="col-md-4 col-6">
  <div class="pull-right">
    <span class="export-btn vertical-icons mr-4" title="Create New Export">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span>
  </div>
</div>
</div>

@include('users.reports.margin-report.dropdown-boxes')
<div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is being prepared! Please wait.. </b>
</div>
<div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>
<div class="alert alert-success export-alert-success d-none"  role="alert">
<i class=" fa fa-check "></i>

  <b>Export file is ready to download.
  <!-- <a download href="{{'storage/app/Completed-Product-Report.xlsx'}}"><u>Click Here</u></a> -->
    <a class="exp_download" href="{{asset('storage/app/'.@$file_name->file_name)}}" target="_blank" id="export-download-btn"><u>Click Here</u></a>
  </b>
</div>
<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered text-center product-spoilage-report">
        <thead>
          <tr>
            <th class="nowrap">PF#</th>
            <th class="nowrap">Supplier</th>
            <th class="nowrap">Customer Ref #</th>
            <th class="nowrap">Quantity</th>
            <th class="nowrap">Cogs / Unit</th>
            <th class="nowrap">Cogs Total</th>
          </tr>
        </thead>
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
<form id="Form-export-margin-report-by-spoilage">
  @csrf
  <input type="hidden" name="primary_filter_exp" id="primary_filter_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
</form>


@endsection

@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
    $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var date = new Date();
  date.setDate( date.getDate() - 30 );

  $("#from_date").datepicker('setDate',date);
  $("#to_date").datepicker('setDate','today');

  // $("#from_date").datepicker('');
  var date = $('#from_date').val();
    $("#from_date_exp").val(date);
  var date = $('#to_date').val();
    $("#to_date_exp").val(date);

    var table2 = $('.product-spoilage-report').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    searching: true,
    serverSide: true,
    "aaSorting": [[3]],
    bSort: false,
    info: true,
    retrieve: true,
    scrollX: true,
    scrollY: '90vh',
    scrollCollapse: true,
    fixedHeader: true,
    dom: 'ftri',
    lengthMenu: [100, 200, 400, 500, 1000],
    ajax: {
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      url: "{!! route('get-margin-report-13') !!}",
      data: function(data) {
        data.from_date = $('#from_date').val();
        data.to_date = $('#to_date').val();
      }, // Add closing brace here
      method: "get",
    },
    columns: [
      { data: 'refrence_code', name: 'refrence_code'},
      { data: 'default_supplier', name: 'default_supplier'},
      { data: 'customer', name: 'customer'},
      { data: 'quantity', name: 'quantity' },
      { data: 'unit_cogs', name: 'unit_cogs' },
      { data: 'cogs_total', name: 'cogs_total'},
    ],
    initComplete: function () {
      // Enable THEAD scroll bars
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
  });
  });
  $(document).on('click','.apply_date',function(){
    var date_from = $('#from_date').val();
    $("#from_date_exp").val(date_from);
    var date_to = $('#to_date').val();
    $("#to_date_exp").val(date_to);

    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-spoilage-report').DataTable().ajax.reload();
  });
  $('.reset-btn').on('click',function(){
    $('#from_date').val('');
    $('#to_date').val('');

    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $(".state-tags").select2("", "");
    $('.product-spoilage-report').DataTable().ajax.reload();
  });
   //export work start
   $(document).on('click','.export-btn',function(){
      let from_date=$("#from_date").val();
      let to_date=$("#to_date").val();
      let filter = 'spoilage';
      if (from_date == '') {
        toastr.error('Error!', 'Please Select From date first !!!' ,{"positionClass": "toast-bottom-right"});
        return;
      }
      if (to_date == '') {
        toastr.error('Error!', 'Please Select To date first !!!' ,{"positionClass": "toast-bottom-right"});
        return;
      }
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"get",
        url:"{{route('export-status-margin-report-by-spoilage')}}",
        data:{from_date_exp:from_date, to_date_exp:to_date, filter:filter},
        beforeSend:function(){

        },
        success:function(data){
          if(data.status==1)
          {
            $('.export-alert-success').addClass('d-none');
            $('.export-alert').removeClass('d-none');
            $('.export-btn').attr('title','EXPORT is being Prepared');
            $('.export-btn').prop('disabled',true);
          }
          else if(data.status==2)
          {
            $('.export-alert-another-user').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-btn').prop('disabled',true);
            $('.export-btn').attr('title','EXPORT is being Prepared');
          }

          checkStatusForMarginExport();
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
   });
    let type = 'margin_report_by_spoilage';
    function checkStatusForMarginExport()
    {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-margin-reports')}}",
      data: {type:type},
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForMarginExport();
            }, 5000);
        }
        else if(data.status==0)
        {
          var href="{{ url('get-downloads-xslx')}}"+"/"+data.file_name;
          $('#export-download-btn').attr("href",href);
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
    }
    $(document).ready(function(){
      $.ajax({
        method:"get",
        url:"{{route('recursive-export-status-margin-reports')}}",
        data: {type:type},
        success:function(data)
        {
          if(data.status==0 || data.status==2)
          {

          }
          else
          {
            $('.export-alert').removeClass('d-none');
            $('.export-btn').attr('title','EXPORT is being Prepared');
            $('.export-btn').prop('disabled',true);
            checkStatusForMarginExport();
          }
        }
      });
      }); 
</script>
<script src="{{asset("public\site\assets\backend\js\margin-report.js")}}"></script>
@stop
