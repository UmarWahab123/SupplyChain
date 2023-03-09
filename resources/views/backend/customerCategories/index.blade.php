@extends('backend.layouts.layout')

@section('title','Customer Categories | Supply Chain')

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
          <li class="breadcrumb-item active">Customer Categories</li>
      </ol>
  </div>
</div>

<!-- cust-cat = customer-category -->
{{-- Content Start from here --}}

<div class="row align-items-center mb-3">
  <div class="col-md-10 title-col">
    <h4 class="maintitle">CUSTOMER CATEGORIES</h4>
  </div>
  <div class="col-md-2 text-right">
    <a href="javascript:void(0)" class="btn button-st new-category" data-toggle="modal" data-target="#addCustCatModal">ADD CATEGORY</a>
</div>
</div>

<div class="alert alert-primary export-alert d-none" role="alert">
  <i class="fa fa-spinner fa-spin"></i>
  <b> Binding Category With Products ... </b>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-cust-cat text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>Title</th>
            @if ($config && $config->server == 'lucilla')
                <th>Prefix</th>
            @endif
            <!-- <th>Ecommerce Enabled</th> -->
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

<!--  cust-cat Modal Start Here -->
<div class="modal fade" id="addCustCatModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Customer @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</h3>
          <div class="mt-5">
            <form class = 'add-cust-cat-form'>
                @csrf
                <div class="form-group">
                  <input type="text" name="title" class="font-weight-bold form-control-lg form-control" placeholder = 'Enter Title' required>
                </div>

                @if ($config->server == 'lucilla')
                <div class="form-group">
                  <input type="text" name="short_code" class="font-weight-bold form-control-lg form-control" placeholder = 'Enter Prefix' required>
                </div>
                @endif

                <div class="form-submit">
                  <input type="submit" value="add" class="btn btn-bg save-btn">
                  <input type="reset" value="close" class="btn btn-danger close-btn">
                </div>
            </form>
          {{-- {!! Form::open(['method' => 'POST', 'class' => 'add-cust-cat-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title', 'required' => 'true' ]) !!}
              @if ($config->server == 'lucilla')
              {!! Form::text('short_code', $value = null, ['class' => 'font-weight-bold form-control-lg form-control mt-2', 'placeholder' => 'Enter Prefix']) !!}
              @endif
            </div>

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!} --}}
         </div>
        </div>
      </div>
    </div>
  </div>
<!-- add cust-cat Modal End Here -->

<!-- Edit modal -->
<div class="modal fade" id="editCustCatModal">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Edit Customer Category</h3>
        <div class="mt-5">
        <form method="POST" class="edit-cust-cat-form">
          {{ csrf_field() }}
          <div id="edit_html_string">

          </div>

          <div class="form-submit">
            <input type="submit" value="Update" class="btn btn-bg save-btn">
          </div>

        </form>
       </div>
      </div>
    </div>
  </div>
</div>
<!-- Edit modal End -->

<!-- Loader Modal -->
<div class="modal" id="loader_modal2" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
        <div id="msg"></div>
      </div>
    </div>
  </div>
</div>

<div id="progressModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Processing</h4>
      </div>
      <div class="modal-body">
        Please Wait while the process is running.
        <ul>
          <li>Do Not perform any actions while the process is running.</li>
          <li>Do Not close this tab/window while the process is running.</li>
          <li>Do Not Refresh / Reload the page while the process is running.</li>
        </ul>
        <div class="progress">
          <div class="progress-p" id="update-p" style="left: 49%; position: absolute; color: black; font-weight: bold;">2 %</div>
          <div class="progress-bar progress-bar-striped progress-bar-animated" id="update-b" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 2%"></div>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

  $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

  var table2 = $('.table-cust-cat').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    "language":{
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    ajax: {
       beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      url:"{!! route('get-customer-categories') !!}",
    },
    columns: [
        { data: 'action', name: 'action' },
        { data: 'title', name: 'title' },
        @if ($config && $config->server == 'lucilla')
        { data: 'short_code', name: 'short_code' },
        @endif
        // { data: 'ecommr_enabled', name: 'ecommr_enabled' },
        { data: 'created_at', name: 'created_at' },
    ],
      drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
  });

  $('.dataTables_filter input').unbind();

  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  $(document).on('keyup', '.form-control', function(){
    $(this).removeClass('is-invalid');
    $(this).next().remove();
  });

$(document).on('change','.ecommerce_enabled',function(e){
    id=$(this).data('id');
    var ecommerce_enabled = '';
            if($('.ecommerce_enabled'+id).is(':checked'))
            {
                ecommerce_enabled = 1;
            }
            else
            {
                 ecommerce_enabled = 0;
            }

        swal({
          title: "Alert!",
          text: "Are you sure you want Enable/Disable this product for Ecommerce?",
          type: "info",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "Yes!",
          cancelButtonText: "No!",
          closeOnConfirm: true,
          closeOnCancel: false
        },
        function(isConfirm)
         {
           if (isConfirm)
          {

            $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN':$('meta[name="_token"]').attr('content')
            }

            });
        $.ajax({
          url: "{{ route('enable-ecommerce-customer-category') }}",
          method: 'get',
          data:'customer_categ_id='+id+'&ecommerce_enabled='+ecommerce_enabled,
          success:function(){
          toastr.success('Success!','changes made succesfully' ,{"positionClass": "toast-bottom-right"});
          }

           });
          }
          else{
              swal("Cancelled", "", "error");
             if(ecommerce_enabled==0 )
                { $('.ecommerce_enabled'+id).prop( "checked",true ); }
              else
                 {$('.ecommerce_enabled'+id).prop( "checked",false );}

              }
          });
});

  $(document).on('submit', '.add-cust-cat-form', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    var timeout;
    $.ajax({
      url: "{{ route('add-customer-category') }}",
      method: 'post',
      data: $('.add-cust-cat-form').serialize(),
      beforeSend: function(){
        $('.modal').modal('hide');
        $('#loader_modal2').modal('show');
        $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
        // timeout = setTimeout(function(){
        //   var alertMsg = "<p style='color:red;''>Please be patient this process will take some time .....</p>";
        //   $('#msg').html(alertMsg);
        // }, 12000);

      },
      success: function(result){
        $('.add-cust-cat-form')[0].reset();
        $('#loader_modal2').modal('hide');
        $('.table-cust-cat').DataTable().ajax.reload();
        if(result.success == true)
        {
          toastr.success('Success!', 'Category Added successfully.' ,{"positionClass": "toast-bottom-right"});
        }
      },
      error: function (request, status, error) {
        $('.add-cust-cat-form')[0].reset();
        $('#loader_modal2').modal('hide');
        $('.form-control').removeClass('is-invalid');
        $('.form-control').next().remove();
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'"]').addClass('is-invalid');
        });
      }
    });
  });

  $(document).on('click', '.makeBinding',function(e){
    var cat_id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to bind this Category?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm){
        $.ajax({
          url:"{{ route('make-binding') }}",
          method:"get",
          data:{cat_id:cat_id},
          beforeSend: function(){
            $('.makeBinding').addClass('d-none');
          },
          success:function(result){
            if(result.status==1)
            {
              $('.export-alert').removeClass('d-none');
              $('.new-category').addClass('disabled');
              checkStatusCategory();
            }
            else if(data.status==2)
            {
              $('.export-alert').addClass('d-none');
              $('.new-category').addClass('disabled');
              checkStatusCategory();
            }
          },
          error:function(request, status, error){
            $('#loader_modal2').modal('hide');
            $('.table-cust-cat').DataTable().ajax.reload();
            toastr.error('Error!', "Something went wrong, contact support !!!" ,{"positionClass": "toast-bottom-right"});
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-category')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.new-category').addClass('disabled');
          checkStatusCategory();
        }
      }
    });
  });

  function checkStatusCategory()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-type-category-status')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
          function(){
            console.log("Calling Function Again");
            checkStatusCategory();
          }, 12000);
        }
        else if(data.status == 0)
        {
          // $('#loader_modal2').modal('hide');
          $('.export-alert').addClass('d-none');
          $('.new-category').removeClass('disabled');

          toastr.success('Success!', 'Category Binded Successfully.',{"positionClass": "toast-bottom-right"});
          $('.add-cust-cat-form')[0].reset();
          $('.table-cust-cat').DataTable().ajax.reload();

        }
        else if(data.status == 2)
        {
          // $('#loader_modal2').modal('hide');
          $('.export-alert').addClass('d-none');
          $('.new-category').removeClass('disabled');

          toastr.error('error!', 'Something went wrong.',{"positionClass": "toast-bottom-right"});
          $('.add-cust-cat-form')[0].reset();
          $('.table-cust-cat').DataTable().ajax.reload();
        }
      }
    });
  }

  $(document).on('submit', '.edit-cust-cat-form', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{route('edit-customer-category')}}",
        method: 'post',
        data: $('.edit-cust-cat-form').serialize(),
        beforeSend: function(){
          $('.modal').modal('hide');
          $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal2').modal('show');
          // $('.edit-prod-btn').val('Please wait...');
          // $('.edit-prod-btn').addClass('disabled');
          // $('.edit-prod-btn').attr('disabled', true);
        },
        success: function(result){
          $('#loader_modal2').modal('hide');
          // $('.edit-prod-btn').val('add');
          // $('.edit-prod-btn').attr('disabled', true);
          // $('.edit-prod-btn').removeAttr('disabled');
          if(result.success === true)
          {
            toastr.success('Success!', 'Customer Category Updated Successfully',{"positionClass": "toast-bottom-right"});
            $('.edit-cust-cat-form')[0].reset();
            $('.table-cust-cat').DataTable().ajax.reload();
          }


        },
        error: function (request, status, error) {
              $('#loader_modal2').modal('hide');
              // $('.edit-prod-btn').val('add');
              // $('.edit-prod-btn').removeClass('disabled');
              // $('.edit-prod-btn').removeAttr('disabled');
              $('.form-control').removeClass('is-invalid');
              $('.form-control').next().remove();
              json = $.parseJSON(request.responseText);
              $.each(json.errors, function(key, value){
                  $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                   $('input[name="'+key+'"]').addClass('is-invalid');
              });
          }
      });
  });

  $(document).on('click', '.edit-icon',function(e){

    $('#editCustCatModal').modal('show');
    var cat_id = $(this).data('id');
    $.ajax({

      url:"{{ route('get-cust-cat-name-for-edit') }}",
      method:"get",
      dataType:"json",
      data:{cat_id:cat_id},
      beforeSend: function(){
        $('#loader_modal2').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal2').modal('show');
        },
      success:function(data){
        $('#loader_modal2').modal('hide');
        if(data.success == true)
        {
          $("#edit_html_string").html(data.html_string);
        }
      },
      error:function(){
        $('#loader_modal2').modal('hide');
      }

  });
  });

  $(document).on('click', '.deleteIcon',function(e){
    var cat_id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to delete this Category?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm){
        $.ajax({
          url:"{{ route('delete-cust-category') }}",
          method:"get",
          data:{cat_id:cat_id},
          beforeSend: function(){
            $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal2').modal('show');
            },
          success:function(data){
            $('#loader_modal2').modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', "Category Deleted Successfully !!!" ,{"positionClass": "toast-bottom-right"});
              $('.table-cust-cat').DataTable().ajax.reload();
            }
            else
            {
              toastr.error('Error!', "You cannot delete this category, this category is bind with a Customer(s) !!!" ,{"positionClass": "toast-bottom-right"});
            }
          },
          error:function(request, status, error){
            $('#loader_modal2').modal('hide');
            $('.table-cust-cat').DataTable().ajax.reload();
            toastr.error('Error!', "Something went wrong, contact support !!!" ,{"positionClass": "toast-bottom-right"});
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

  });

</script>
@stop

