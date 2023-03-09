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
          <li class="breadcrumb-item active">Payment Terms</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">PAYMENT TERMS</h4> 
        <div class="mb-0">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addPaymentTermModal">ADD PAYMENT TERM</a>
        </div>
    </div>
  </div>
</div> --}}

<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">PAYMENT TERMS</h4>
  </div>    
      <div class="col-md-4 text-right title-right-col">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addPaymentTermModal">ADD PAYMENT TERM</a>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-payment-terms text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>Title</th>    
            <th>Description</th>
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
<div class="modal fade" id="addPaymentTermModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Payment Term</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-payment-term-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
            </div>

            <div class="form-group">
              {!! Form::text('description', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Description']) !!}
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

<!-- add Payment Term Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editpaymentTypeModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Payment Term</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-payment-term')}}" class="edit-payment-term-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control payment_name" placeholder="Enter Payment Term" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="description" class="font-weight-bold form-control-lg form-control payment_desc" placeholder="Enter Payment Term Description" required="true">
            </div>

            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="submit" value="Update" class="btn btn-bg w-25 update-btn">
            </div>

          </form>
         </div> 
        </div>
      </div>
    </div>
  </div>
<!-- Edit modal End -->

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


@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     var table2 = $('.table-payment-terms').DataTable({
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
          url:"{!! route('get-payment-term') !!}",
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'created_at', name: 'created_at' }
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

    

    $(document).on('submit', '.add-payment-term-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-payment-term') }}",
          method: 'post',
          data: $('.add-payment-term-form').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
            $('.save-btn').val('Wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Payment Term added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-payment-term-form')[0].reset();
              setTimeout(function(){
                $('.table-payment-terms').DataTable().ajax.reload();
              }, 500);
              
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

    $(document).on('submit', '.edit-payment-term-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-payment-term') }}",
          method: 'post',
          data: $('.edit-payment-term-form').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
            $('.update-btn').val('Wait..');
            $('.update-btn').addClass('disabled');
            $('.update-btn').attr('disabled', true);
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            $('.update-btn').val('add');
            $('.update-btn').attr('disabled', false);
            $('.update-btn').removeClass('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Payment Term Updated successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-payment-term-form')[0].reset();
              $('.table-payment-terms').DataTable().ajax.reload();
              
            }
            
            
          },
          error: function (request, status, error) {
                $('.update-btn').val('add');
                $('.update-btn').removeClass('disabled');
                $('.update-btn').removeAttr('disabled');
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
      var sId = $(this).parents('tr').attr('id');
      var oldTitle = $(this).parents('td').next().text();
      var oldDesc = $(this).parents('td').next().next().text();
      $('.payment_name').val(oldTitle);
      $('.payment_desc').val(oldDesc);
      $('input[name=editid]').val(sId);
      $('#editpaymentTypeModal').modal('show');
    });

    $(document).on('click', '.delete-icon', function(){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to delete this?",
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
                method:"get",
                dataType:"json",
                data:{id:id},
                url:"{{ route('delete-payment-term') }}",
                beforeSend:function(){
                   $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                      });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                    if(data.error == false){
                      toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                      setTimeout(function(){
                      $('.table-payment-terms').DataTable().ajax.reload();
                      }, 1000);
                    }else if(data.error == true){
                      toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    }
                }
             });
          } 
          else{
              swal("Cancelled", "", "error");
          }
     });  
    });

  });


</script>
@stop

