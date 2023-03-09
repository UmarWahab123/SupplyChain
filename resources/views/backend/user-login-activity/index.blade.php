@extends('backend.layouts.layout')

@section('title','Payment Terms | Supply Chain')

@section('content')
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
          <li class="breadcrumb-item active">Users Login History</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}

<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h4 class="maintitle">Users Login History</h4>
  </div>
  <div class="col-md-4 text-right title-right-col">
    <div class="pull-right">
      <span class="export-btn vertical-icons" title="Create New Export">
        <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
  </div>
</div>

{{--Filters start here--}}
<div class="col-lg-12 col-md-12 pl-0 pr-0 d-flex align-items-center mb-3 filters_div">

  <div class="col">
    <label class="pull-left font-weight-bold">From Date (Last Login):</label>
    <input type="text" placeholder="From Date" name="from_login_date" class="form-control font-weight-bold fld" id="from_date" autocomplete="off" readonly="">
  </div>

  <div class="col">
    <label class="pull-left font-weight-bold">To Date (Last Login):</label>
    <input type="text" placeholder="To Date" name="to_login_date" class="form-control font-weight-bold tld" id="to_date" autocomplete="off" readonly="">
  </div>

  <div class="col" style="margin-top: 30px;">
    <div class="pull-left">
      <span class="vertical-icons reset-btn" id="reset-btn" title="Reset">
        <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
    </div>
  </div>
</div>
{{--Filters ends here--}}


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

      <div class="alert alert-primary export-alert d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is being prepared! Please wait.. </b>
      </div>
      <div class="alert alert-success export-alert-success d-none"  role="alert">
        <i class=" fa fa-check "></i>
        <b>Export file is ready to download.
          <a class="exp_download" href="{{ url('get-download-xslx','users-login-history-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>
      <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is already being prepared by another user! Please wait.. </b>
      </div>

      <div class="table-responsive">
        <table class="table entriestable table-bordered table-login-details text-center">
          <thead>
            <tr>
              <th>Username</th>
              <th>Number of logins</th>
              <th>First login date</th>
              <th>Last login date</th>
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


<form id="export_user_login_history_form">
  <input type="hidden" name="from_login_date_exp" id="from_login_date_exp">
  <input type="hidden" name="to_login_date_exp" id="to_login_date_exp">
</form>

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $("#from_date, #to_date").datepicker({
      format: "dd/mm/yyyy",
      autoHide: true
    });

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
        timepicker:false,
        format:'Y-m-d'
      });
    });

    var table2 = $('.table-login-details').DataTable({
      "sPaginationType": "listbox",
      processing: false,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      pageLength: {{100}},
      lengthMenu: [ 100, 200, 300, 400],
      ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url:"{!! route('get-user-login-details') !!}",
        data: function(data) {
          data.from_login = $('#from_date').val(),
          data.to_login   = $('#to_date').val()
        },
      },
      columns: [
        { data: 'username', name: 'username' },
        { data: 'number_of_logins', name: 'number_of_logins' },
        { data: 'first_login_date', name: 'first_login_date' },
        { data: 'last_login_date', name: 'last_login_date' }
      ],
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
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

    $('#from_date').change(function() {
      if($('#from_date').val() != '')
      {
        $('#from_login_date_exp').val($('#from_date').val());
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
        $('.table-login-details').DataTable().ajax.reload();
      }
    });

    $('#to_date').change(function() {
      if($('#to_date').val() != '')
      {
        $("#to_login_date_exp").val($('#to_date').val());
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
        $('.table-login-details').DataTable().ajax.reload();
      }
    });

    $('.reset-btn').on('click',function(){
      $('#from_date').val('');
      $('#to_date').val('');

      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');

      $('.table-login-details').DataTable().ajax.reload();
    });

    $(document).on('click','.export-btn',function(){

      var form = $('#export_user_login_history_form');
      var form_data = form.serialize();
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"post",
        url:"{{route('export-users-login-history-list')}}",
        data:form_data,
        beforeSend:function(){
          $('.export-btn').prop('disabled',true);
        },
        success:function(data){
          if(data.status==1)
          {
            $('.export-alert-success').addClass('d-none');
            $('.export-alert').removeClass('d-none');
            $('.export-btn').prop('disabled',true);
            console.log("Calling Function from first part");
            checkStatusForPurchaseList();
          }
          else if(data.status==2)
          {
            $('.export-alert-another-user').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-btn').prop('disabled',true);
            checkStatusForPurchaseList();
          }
        },
        error:function(){
          $('.export-btn').prop('disabled',false);
        }
      });
    });

    $(document).ready(function(){
      $.ajax({
        method:"get",
        url:"{{route('check-status-for-first-time-user-login-list')}}",
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
            $('.export-btn').prop('disabled',true);
            checkStatusForPurchaseList();
          }
        }
      });
    });

    function checkStatusForPurchaseList()
    {
      $.ajax({
        method:"get",
        url:"{{route('recursive-export-status-user-login-list')}}",
        success:function(data){
          if(data.status==1)
          {
            console.log("Status " +data.status);
            setTimeout(
              function(){
                console.log("Calling Function Again");
                checkStatusForPurchaseList();
              }, 5000);
          }
          else if(data.status==0)
          {
            $('.export-alert-success').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-alert-another-user').addClass('d-none');
            $('.export-btn').prop('disabled',false);

          }
          else if(data.status==2)
          {
            $('.export-alert-success').addClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-alert-another-user').addClass('d-none');
            $('.export-btn').prop('disabled',false);
            toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
            console.log(data.exception);
          }
        }
      });
    }

  });
</script>
@stop

