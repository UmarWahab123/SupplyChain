@extends('backend.layouts.layout')

@section('title','Payment Types | Supply Chain')

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
          <li class="breadcrumb-item active">Payment Types</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">PAYMENT TYPES</h4>
        <div class="mb-0">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addPaymentTypeModal">ADD PAYMENT TYPE</a>
        </div>
    </div>
  </div>
</div> --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">PAYMENT TYPES</h4>
  </div>    
      <div class="col-md-4 text-right title-right-col">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addPaymentTypeModal">ADD PAYMENT TYPE</a>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-payment-types text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>Title</th>
            <th>Description</th>
            <th>Visible in Customer</th>
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

<!--  Payment Type Modal Start Here -->
<div class="modal fade" id="addPaymentTypeModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Payment Type</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-payment-type-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
            </div>

            <div class="form-group">
              {!! Form::text('description', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Description']) !!}
            </div>

            <div class="form-group radio">
                <label for="email">Visible for Customer </label><br>
              Yes: {!! Form::radio('visible_in_customer', $value = 1, true) !!}
              No: {!! Form::radio('visible_in_customer', $value = 0, false) !!}
            </div>

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div>
        </div>
      </div>
    </div>
  </div>
<!-- add Payment Type Modal End Here -->

<!-- Edit modal -->
<div class="modal fade" id="editpaymentTypeModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Payment Type</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-payment-type')}}" class="edit-payment-type-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control payment_name" placeholder="Enter Payment Type" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="description" class="font-weight-bold form-control-lg form-control payment_desc" placeholder="Enter Payment Type Description" required="true">
            </div>

              {{--<div class="form-group mb-4 pb-1">
                  <label for="email">Visible for Customer </label><br>
                  Yes: <input id="yes" name="visible_in_customer" type="radio" value="1">
              </div>--}}
            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="submit" value="Update" class="btn btn-bg save-btn">
            </div>

          </form>
         </div>
        </div>
      </div>
    </div>
  </div>
<!-- Edit modal End -->

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
        timepicker:false,
        format:'Y-m-d'
      });
    });

    var table2 = $('.table-payment-types').DataTable({
      "sPaginationType": "listbox",
       processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
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
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
        url:"{!! route('get-payment-type') !!}"
      },
      columns: [
          { data: 'action', name: 'action' },
          { data: 'title', name: 'title' },
          { data: 'description', name: 'description' },
          { data: 'visible_in_customer', name: 'visible_in_customer' },
          { data: 'created_at', name: 'created_at' }
      ],
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

    $(document).on('submit', '.add-payment-type-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-payment-type') }}",
          method: 'post',
          data: $('.add-payment-type-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Payment Type added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-payment-type-form')[0].reset();
              $('.table-payment-types').DataTable().ajax.reload();

            }


          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
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

    $(document).on('submit', '.edit-payment-type-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-payment-type') }}",
          method: 'post',
          data: $('.edit-payment-type-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Payment Type added successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-payment-type-form')[0].reset();
              $('.table-payment-types').DataTable().ajax.reload();
            }
          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
                $('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');
                });
            }
        });
    })

    $(document).on('change', '.check_visible_in_customer', function(e){
      e.preventDefault();
        var payment_type_id = $(this).parents('tr').attr('id');
        var visible_in_customer = 0;
        if(this.checked) {
            visible_in_customer = 1;
        }

        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('payment-visible-in-customer') }}",
          method: 'post',
          data: {visible_in_customer: visible_in_customer, payment_type_id: payment_type_id},
          beforeSend: function(){

          },
          success: function(result){

            if(result.success === true){
              toastr.success('Success!', 'Visible in Customer Changed Successfully ',{"positionClass": "toast-bottom-right"});
              $('.table-payment-types').DataTable().ajax.reload();
            }
          },
          error: function (request, status, error) {

            }
        });
    });

    $(document).on('click', '.edit-icon',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldTitle = $(this).parents('td').next().text();
      var oldDesc = $(this).parents('td').next().next().text();
      $('.payment_name').val(oldTitle);
      $('.payment_desc').val(oldDesc);
      $('input[name=editid]').val(sId);

      $('#editpaymentTypeModal').modal('show');
    });

    $(document).on('click', '.deletePaymentType',function(e){
      var id = $(this).data('id');

      $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });
      $.ajax({
        type: "get",
        url: "{{ route('check-payment-type-of-customer') }}",
        dataType: 'json',
        data:'id='+id,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
          if(data.success == true)
          {
            swal({
              title: "Are you sure!!!",
              text: "You want to delete this payment type?",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Yes, remove it!",
              cancelButtonText: "Cancel",
              closeOnConfirm: true,
              closeOnCancel: false
              },
            function (isConfirm) {
              if (isConfirm) {
                $.ajax({
                  method:"get",
                  data:'id='+id,
                  url: "{{ route('delete-payment-type') }}",
                  success: function(data)
                  {
                    if(data.success === true)
                    {
                      toastr.success('Success!', 'Payment Type Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                      $('.table-payment-types').DataTable().ajax.reload();
                    }
                  }
                });
              }
              else {
                  swal("Cancelled", "", "error");
              }
            });
          }
          else
          {
            swal({ html:true, title:'Alert !!!', text:'<b>Payment type cannot be deleted because it is in the use of Customer !!!</b>'});
          }
        }
      });
      });

  });
</script>
@stop

