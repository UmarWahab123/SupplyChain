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
          <li class="breadcrumb-item active">All Users</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">All Users</h4>
  </div>
    @if($addUser)
      <div class="col-md-4 text-right title-right-col">
        <a class="btn mr-4" href data-toggle="modal" data-target="#addPurchasingModal">
          Add User
        </a>
          <span class="vertical-icons mr-4" title="Download Sample File" id="export_excel_btn"><img src="{{asset('/public/icons/sample_export_icon.png')}}" width="27px"></span>
          <span class="vertical-icons mr-4" title="Bulk Import" data-target="#addExcelModal" data-toggle="modal"><img src="{{asset('/public/icons/bulk_import.png')}}" width="27px"></span>
      </div>
  @endif
</div>

<form id="excelExportFormData" method="get" action="{{route('export-bulk-users-file-download')}}" class="excelExportFormData" enctype="multipart/form-data">
  @csrf
</form>

{{-- <div class="upload-user-bulk" style="display: none;">
  <h3>Upload File</h3>
  <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
  <form class="upload-excel-form u-b-product-form" enctype="multipart/form-data">
    {{csrf_field()}}
    <label for="bulk_import_file">Choose Excel File</label>
    <input type="hidden" name="supplier" value="{{$user->id}}">
    <input type="file" class="form-control" name="excel" id="excel" accept=".xls,.xlsx" required=""><br>
    <button class="btn btn-info products-upload-btn" type="button">Upload</button>
  </form>
</div> --}}


<div class="row">
  <div class="col-lg-2 col-md-2">
    <label for=""><b>Status</b></label>
    <select class="form-control-lg form-control js-states state-tags prod_type status" name="status">
      <option value="" selected="" disabled="">Select Status</option>
      <option value="1"> Active </option>
      <option value="0"> Inactive </option>
      <option value="2"> Suspended </option>
    </select>
  </div>

  <div class="col-lg-2 col-md-2">
    <label for=""><b>Roles</b></label>
    <select class="form-control-lg form-control role_user" title="Choose user role" data-live-search="true">
      <option value="" selected="" disabled="">Select Role</option>
      @foreach($roles as $role)
        <option value="{{$role->id}}">{{$role->name}}</option>
      @endforeach
    </select>
  </div>

  <div class="col-lg-2 col-md-2">
    <label for=""><b>Company</b></label>
    <select class="form-control-lg form-control company">
      <option value="" selected="" disabled="">Select Company</option>
      @if($companies->count() > 0)
      @foreach($companies as $company)
        <option value="{{$company->id}}">{{$company->company_name}}</option>
      @endforeach
      @endif
    </select>
  </div>

</div>


<div class="row mb-4 mt-4">
<div class="col-md-12 col-lg-12">
      <div class="float-right">

      <span class="apply_filters vertical-icons mr-4" title="Apply Filters">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
      </span>

      <span class="reset-btn vertical-icons mr-4" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
</div>
</div>
</div>



<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive" id="sticky-anchor">
      <table class="table entriestable table-bordered table-users text-center">
        <thead id="sticky">
          <tr>
            <th>Action</th>
            <th>Name
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Company
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="company_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="company_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Location
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="location">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="location">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>User Name
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="user_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="user_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Email
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="email">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="email">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Phone No.
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="phone_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="phone_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th width="10%">Role
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="role_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="role_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th width="15%">Status</th>
            <th>Created At
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="created_at">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="created_at">
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

<div class="row align-items-center mb-3 mt-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">User Type Updated History</h4>
  </div>

</div>
<div class="row entriestable-row">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive" id="sticky-anchor">
      <table class="table entriestable table-bordered table-users-histories text-center">
        <thead id="sticky">
          <tr>
            <th>User</th>
            <th>Updated By</th>
            <th>Column</th>
            <th>Old Value</th>
            <th>New Value</th>
            <th>Created At</th>
          </tr>
        </thead>

      </table>
    </div>
    </div>

  </div>
</div>

</div>
<!--  Content End Here -->

<!--  Purchasing Modal Start Here -->
<div class="modal" id="addPurchasingModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>

        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">User Information</h3>
          <div class="mt-2">

          {!! Form::open(['method' => 'POST', 'class' => 'add-users-form']) !!}

            <div class="form-row">
              <div class="form-group col-4 input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">
                    <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                  </div>
                </div>
                  {!! Form::text('first_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control name', 'placeholder' => 'Name']) !!}
              </div>
              <div class="form-group col-4 input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">
                    <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                  </div>
                </div>
                  {!! Form::text('user_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control username', 'placeholder' => 'UserName']) !!}
              </div>
              <div class="form-group col-4 input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">
                    <!-- <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i> -->
                  </div>
                </div>
                  {!! Form::text('phone_number', $value = null, ['class' => 'font-weight-bold form-control-lg form-control phone_number', 'placeholder' => 'Phone Number']) !!}
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-6 input-group">
                  {!! Form::email('email', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Email']) !!}
              </div>
              <div class="form-group col-6 input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">
                    <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                  </div>
                </div>
                  <select id="selectRole" class="font-weight-bold form-control-lg form-control" title="Choose user role" data-live-search="true" name="user_role" >
                    <option value="" selected="" disabled="">Select user role</option>
                    @foreach($roles as $role)
                      <option value="{{$role->id}}">{{$role->name}}</option>
                    @endforeach
                  </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-6 input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">
                    <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                  </div>
                </div>
                <select id="selectCompany" class="font-weight-bold form-control-lg form-control" name="user_company" >
                  <option value="" selected="" disabled="">Select Company</option>
                  @if($companies->count() > 0)
                  @foreach($companies as $company)
                    <option value="{{$company->id}}">{{$company->company_name}}</option>
                  @endforeach
                  @endif
                </select>
              </div>
              <div class="form-group col-6 input-group d-none" id="sales_warehouse">
                <select class="font-weight-bold form-control-lg form-control" title="Choose warehouse" data-live-search="true" name="warehouse_id" >
                  <option value="">Select a Warehouse</option>
                  @foreach($warehouse as $result)
                    <option value="{{$result->id}}">{{$result->warehouse_title}}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group col-6 input-group d-none" id="default_warehouse">
                {!! Form::select('is_default', ['' => 'Select as Default','1'=>'YES','0'=>'NO'], null, ['class' => 'font-weight-bold form-control-lg form-control', 'id' => 'is_default']) !!}
              </div>
            </div>

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-bg close-btn">
            </div>
          {!! Form::close() !!}
         </div>
        </div>
      </div>
    </div>
  </div>

<!-- Purchasing Modal End Here -->

{{-- User Modal Start --}}
<div class="modal" id="addExcelModal" >
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Excel File</h3>
        <div class="mt-2">
          <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
        <form method="POST" accept-charset="UTF-8" enctype="multipart/form-data" class="users-bulk-form">
          @csrf
          <div class="form-row">
            <div class="form-group col-12 input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                </div>
              </div>
                <input type="file" class="form-control" name="excel" id="excel" accept=".xls,.xlsx" required="">
            </div>
          </div>

          <div class="form-submit">
            <input type="submit" value="add" class="btn btn-bg save-bulk-btn">
            <input type="reset" value="close" class="btn btn-bg close-btn">
          </div>
        </form>
       </div>
      </div>
    </div>
  </div>
</div>
{{-- USer Modal End --}}

<!--  Copy Link Modal Start Here -->
<div class="modal" id="copyLinkModal">
    <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>

        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Copy Link</h3>
          <div class="mt-2">

            <div class="form-row">
              <div class="form-group col-12 input-group">
                <p>Please click on the copy button to copy link and paste it in a new incognito window otherwise admin will be logged out.</p>

              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-10 input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text">
                    <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                  </div>
                </div>

                <input type="text" name="" class="form-control" id="login_url">

                <!-- <button value="copy" onclick="copyToClipboard()">Copy!</button> -->
              </div>
              <div class="form-group col-2 input-group">
                <button class="btn button-st" value="copy" onclick="copyToClipboard()">Copy!</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Copy Link Modal End Here -->


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

@section('javascript')
<script type="text/javascript">

var order = 1;
var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.table-users').DataTable().ajax.reload();

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


  $(document).on('click','.reset-btn',function(){
      $(".status").val('');
      $(".role_user").val('');
      $(".company").val('');
      $('.table-users').DataTable().ajax.reload();
    });



  $(document).on('click','.apply_filters',function(){
    $('.table-users').DataTable().ajax.reload();
    });


$('#addPurchasingModal').on('hidden.bs.modal', function () {
  $('.name').removeClass("is-invalid");
  $('.username').removeClass("is-invalid");
  $('.phone_number').removeClass("is-invalid");
  $('#selectRole').removeClass("is-invalid");
  $('#selectCompany').removeClass("is-invalid");
  $('.invalid-feedback').hide();
  $('#sales_warehouse').addClass('d-none');
});



  function copyToClipboard() {
        document.getElementById("login_url").select();
        document.execCommand('copy');
    }

  $(function(e){

    var full_path = $('#site_url').val()+'/';

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

   var table2 = $('.table-users').DataTable({
    "sPaginationType": "listbox",
       processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      lengthMenu:[100,200,300,400],
      serverSide: true,
      ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        url: "{!! route('get-users') !!}",
        data: function(data) {
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.status=$(".status option:selected").val(),
          data.role_user=$(".role_user option:selected").val(),
          data.company=$(".company option:selected").val()
          },
          method: "get",
      },
      scrollX:true,
      scrollY : '90vh',
    scrollCollapse: true,

      columns: [
        { data: 'action', name: 'action' },
        { data: 'name', name: 'name' },
        { data: 'company', name: 'company' },
        { data: 'location', name: 'location' },
        { data: 'user_name', name: 'user_name' },
        { data: 'email', name: 'email' },
        { data: 'phone_number', name: 'phone_number' },
        { data: 'roles', name: 'roles' },
        { data: 'status', name: 'status' },
        { data: 'created_at', name: 'created_at' },
      ],
     initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      /*this.api().columns([1,2]).every(function () {
          var column = this;
          var input = document.createElement("input");
          $(input).addClass('form-control');
          $(input).attr('type', 'text');
          $(input).appendTo($(column.header()))
          .on('change', function () {
              column.search($(this).val()).draw();
          });
      });

      this.api().columns([7]).every(function () {
        var column = this;
        var select = document.createElement("select");
        $(select).append('<option value="">All</option><option value="InActive">InActive</option><option value="Active">Active</option><option value="Suspended">Suspended</option>');
        $(select).addClass('form-control');
        $(select).appendTo($(column.header()))
        .on('change', function () {
            column.search($(this).val()).draw();
        });
      });*/
        },

drawCallback: function(){
          $('#loader_modal').modal('hide');
        },
    });

   $('.dataTables_filter input').unbind();
$('.dataTables_filter input').bind('keyup', function(e) {
if(e.keyCode == 13) {
  // alert();
        table2.search($(this).val()).draw();
}
});

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    $(document).on('change','#selectCompany',function(){
      var val = $(this).children("option:selected").val();

      if(val == 1)  //company id 1 in DB
      {
        $('#sales_warehouse').removeClass('d-none');
      }
      else
      {
        $('#sales_warehouse').removeClass('d-none');
        // $('#sales_warehouse').addClass('d-none');
      }

    });

    $(document).on('click', '.save-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-users') }}",
          method: 'post',
          data: $('.add-users-form').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
            $('.save-btn').val('Please wait...');
            // $('.save-btn').addClass('disabled');
            // $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            $('.save-btn').val('add');
            // $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'User added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-users-form')[0].reset();
              setTimeout(function(){
                $('.table-users').DataTable().ajax.reload();
              }, 300);

            }
            // else if(result.admin_limit_reach == true)
            // {
            //   toastr.info('Sorry!','Cannot Create User , You Have Reached Maximum Number of Admin Accounts !!!' ,{"positionClass": "toast-bottom-right"});
            // }
            // else if(result.staff_limit_reach == true)
            // {
            //   toastr.info('Sorry!','Cannot Create User , You Have Reached Maximum Number of Staff Accounts !!!' ,{"positionClass": "toast-bottom-right"});
            // }
            else if (result.success === false) {
              toastr.success('Success!', result.message ,{"positionClass": "toast-bottom-right"});

            }
            // else if (result.max_accounts) {
            //    toastr.info('Sorry!', result.message ,{"positionClass": "toast-bottom-right"});
            // }


          },
          error: function (request, status, error) {
                $("#loader_modal").modal('hide');
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
                $('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');

                     $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('select[name="'+key+'"]').addClass('is-invalid');
                });

            }
        });
    });

    $(document).on("click",".login-as-user",function(){
      // alert('hi');
      var user_id=$(this).data('userid');

      $.ajax({

        method:"get",
        url:"create-token-of-user-for-admin-login",
        dataType:"json",
        data:{user_id:user_id},
        beforeSend:function(){
           $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });

            $("#loader_modal").modal('show');
        },
        success:function(data){
            $("#loader_modal").modal('hide');

            if(data.success == false){
                swal("the selected user is either suspended or inactive");
                swal("User not active", "the selected user is either suspended or inactive", "info")
            }else{
                // alert(data.token_for_admin_login+' User id='+data.user_id);
                var url_for_login=full_path+"user-login-from-admin/"+data.token_for_admin_login+'/'+data.user_id;
                $("#login_url").val(url_for_login);
                $("#copyLinkModal").modal("show");
            }

        },error: function(request, status, error) {
          $("#loader_modal").modal('hide');
          console.log(response);
        }

      });


    });
  });

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $x = $(this);
      $(this).addClass('d-none');
      $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');

    setTimeout(function(){

      $('.spinner').remove();
      $x.next().removeClass('d-none');
      $x.next().addClass('active');
      $x.next().focus();
      var num = $x.next().val();
      $x.next().focus().val('').val(num);


     }, 300);
  });

  var id = 0;

   $(document).on('keypress keyup focusout', '.fieldFocus', function(e){
    id = $(this).data('id');
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

    if($(this).val().length < 1)
    {
      return false;
    }
    else if(fieldvalue == new_value)
    {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }
    else
    {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
        saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    }

    }
  });

   function saveProdData(thisPointer,field_name,field_value){
    // console.log(thisPointer+' '+' '+field_name+' '+field_value);
    var user_id = id;
    // alert(user_id);
    // return;
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('admin/save-user-data-user-detail-page') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,user_id:user_id},
      data: 'user_id='+user_id+'&'+field_name+'='+field_value,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(data)
      {
        $("#loader_modal").modal('hide');

        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }
        else if(data.admin_limit_reach == true)
        {
          toastr.info('Sorry!','Cannot Create User , You Have Reached Maximum Number of Admin Accounts !!!' ,{"positionClass": "toast-bottom-right"});
        }
        else if(data.staff_limit_reach == true)
        {
          toastr.info('Sorry!','Cannot Create User , You Have Reached Maximum Number of Staff Accounts !!!' ,{"positionClass": "toast-bottom-right"});
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }

    });
  }

  $(document).on('change', 'select.select-common', function(){

if($(this).val() !== '')
{
  if($(this).attr('name') == 'primary_salesperson_id')
  {
    var attributeName='primary_salesperson_id';
    var old_value = $(this).parent().prev().data('fieldvalue');
    var rId = $(this).data('row_id');
    var new_value = $("option:selected", this).val();
    console.log(old_value,rId,new_value);
    swal({
      title: "Are you sure?",
      text: "You want to change this user type!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, Update it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
      },
      function (isConfirm) {
        if(isConfirm)
        {
          // thisPionter.addClass('d-none');
          // thisPionter.prev().removeClass('d-none');
          // thisPionter.prev().html(new_value);
          // thisPionter.removeClass('active');
          saveProdData(rId, attributeName, new_value, old_value);
        }
        else
        {
          //$('.table-product').DataTable().ajax.reload();
        }
      }

    );
  }
}
});
function saveProdData(rId,field_name,field_value,old_value){
      console.log(field_name+' '+field_value+''+rId+' '+old_value);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
         url: "{{ url('admin/save-user-data-user-detail-page') }}",
        dataType: 'json',
        data: 'user_id='+rId+'&field_name='+field_name+'&new_value='+field_value+'&'+'old_value'+'='+old_value,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'User type updated successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-users').DataTable().ajax.reload();
            $('.table-users-histories').DataTable().ajax.reload();
            // if(field_name == 'primary_salesperson_id')
            // {
            //   $('.primary_select_'+rId).addClass('d-none');
            //   $('.primary_span_'+rId).removeClass('d-none');
            //   $('.primary_span_'+rId).html(data.user.name);
            //   $('.primary_span_'+rId).attr('data-id','salesperson '+field_value+' '+rId).data('id','salesperson '+field_value+' '+rId);
            //   $('.primary_span_'+rId).attr('data-fieldvalue',field_value).data('fieldvalue',field_value);
            // }
            // else if(field_name == 'secondary_salesperson_id')
            // {
            //   $('.secondary_select_'+rId).addClass('d-none');
            //   $('.secondary_span_'+rId).removeClass('d-none');
            //   $('.secondary_span_'+rId).html(data.user.name);
            //   $('.secondary_span_'+rId).attr('data-id','salesperson '+field_value+' '+rId).data('id','salesperson '+field_value+' '+rId);
            //   $('.secondary_span_'+rId).attr('data-fieldvalue',field_value).data('fieldvalue',field_value);
            // }
            // {

            // }

          }
          else if(data.admin_limit_reach == true)
          {
            toastr.info('Sorry!','Cannot Create User , You Have Reached Maximum Number of Admin Accounts !!!' ,{"positionClass": "toast-bottom-right"});
            $('.table-users').DataTable().ajax.reload();

          }
          else if(data.staff_limit_reach == true)
          {
            toastr.info('Sorry!','Cannot Create User , You Have Reached Maximum Number of Staff Accounts !!!' ,{"positionClass": "toast-bottom-right"});
            $('.table-users').DataTable().ajax.reload();

          }
          else
          {
            toastr.success('Error!', 'Something Went Wrong.',{"positionClass": "toast-bottom-right"});
            // $('.table-customers').DataTable().ajax.reload();

          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
      });
    }

var table2 = $('.table-users-histories').DataTable({
  "sPaginationType": "listbox",
    processing: false,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    lengthMenu:[100,200,300,400],
    serverSide: true,
    ajax: "{!! route('get-users-histories') !!}",
    scrollX:true,
    searching: false,
    scrollY : '90vh',
    scrollCollapse: true,

    columns: [
      { data: 'user', name: 'user' },
      { data: 'updated_by', name: 'updated_by' },
      { data: 'column_name', name: 'column_name' },
      { data: 'old_value', name: 'old_value' },
      { data: 'new_value', name: 'new_value' },
      { data: 'created_at', name: 'created_at' },
    ],
    initComplete: function () {
    $('.dataTables_scrollHead').css('overflow', 'auto');

    $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
      }
    });

    $(document).on('click','#export_excel_btn',function(){
      $('#excelExportFormData').submit();
    });


    $(document).on('submit', '.users-bulk-form', function(e){
      e.preventDefault();

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
       $.ajax({
          url: "{{ route('add-bulk-users') }}",
          method: 'POST',
          data: new FormData(this),
          contentType: false,
          processData: false,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
            $('.save-bulk-btn').val('Please wait...');
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            $('.save-bulk-btn').val('add');
            $('.save-bulk-btn').removeAttr('disabled');
            toastr.success('Success!', 'User added successfully',{"positionClass": "toast-bottom-right"});
            $('.users-bulk-form')[0].reset();
            $('#addExcelModal').modal('hide');
            setTimeout(function(){
              $('.table-users').DataTable().ajax.reload();
            }, 300);

            if(result.success=== true){
              // alert('hide');
              // $('.modal').modal('hide');
              // toastr.success('Success!', 'User added successfully',{"positionClass": "toast-bottom-right"});
              // $('.users-bulk-form')[0].reset();
              // setTimeout(function(){
              //   // $('.table-users').DataTable().ajax.reload();
              //   window.location.reload();
              // }, 300);

            }
            else if (result.success === false) {
              toastr.success('Success!', result.message ,{"positionClass": "toast-bottom-right"});
            }
          },
          error: function (request, status, error) {
                $("#loader_modal").modal('hide');
                $('.save-bulk-btn').val('add');
                $('.save-bulk-btn').removeClass('disabled');
                $('.save-bulk-btn').removeAttr('disabled');
                $('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');

                     $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('select[name="'+key+'"]').addClass('is-invalid');
                });

            }
        });
    });

</script>

@stop

