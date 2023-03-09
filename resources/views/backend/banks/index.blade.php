@extends('backend.layouts.layout')

@section('title','Banks | Supply Chain')

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
          <li class="breadcrumb-item active">Banks</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}

<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h4 class="maintitle">BANKS</h4>
  </div>    
  <div class="col-md-4 text-right">
    <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addUnitModal">ADD BANK</a>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
        <table class="table entriestable table-bordered table-banks text-center">
          <thead>
            <tr>
              <th>Action</th>
              <th>Account Name</th>                    
              <th>Account No.</th>                 
              <th>Bank Description</th>                 
              <th>Branch</th> 
              <th>QR Image</th>                
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

<!--  Unit Modal Start Here -->
  <div class="modal fade" id="addUnitModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Bank</h3>
          <div class="mt-5">
     <!--      {!! Form::open(['method' => 'POST', 'class' => 'add-bank-form', 'files' => true, 'enctype'=>'multipart/form-data']) !!} -->
          <form method="POST" class="add-bank-form" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group">
              <label style="float:left; font-weight: bold;">Account Name <b style="color: red">*</b></label>
              <input type="text" name="title" id="title" autocomplete="off" class="font-weight-bold form-control-lg form-control">
            </div>

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Account No <b style="color: red">*</b></label>
              <input type="text" name="account_no" id="account_no" class="font-weight-bold form-control-lg form-control">
            </div>

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Bank Description <b style="color: red">*</b></label>
              <input type="text" name="description" id="description" class="font-weight-bold form-control-lg form-control">
            </div>

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Branch <b style="color: red">*</b></label>
              <input type="text" name="branch" id="branch" class="font-weight-bold form-control-lg form-control">
            </div>

             <div class="form-group text-left">
             <input title="Select QR Image" type="file" name="image" id="add_picture" />
           </div>

            <div class="form-submit">
              <input type="submit" id="add_img" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          </form>
      <!--     {!! Form::close() !!} -->
         </div> 
        </div>
      </div>
    </div>
  </div>
<!-- add Unit Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editBankModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Bank</h3>
          <div class="mt-5">
          <form method="POST" class="edit-bank-form">
            {{ csrf_field() }}

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Account Name <b style="color: red">*</b></label>
              <input type="text" name="title_e" id="title" autocomplete="off" class="title font-weight-bold form-control-lg form-control">
            </div>

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Account No <b style="color: red">*</b></label>
              <input type="text" name="account_no_e" id="account_no" autocomplete="off" class="account_no font-weight-bold form-control-lg form-control">
            </div>

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Bank <b style="color: red">*</b></label>
              <input type="text" name="description_e" id="description" autocomplete="off" class="description font-weight-bold form-control-lg form-control">
            </div>

            <div class="form-group">
              <label style="float:left; font-weight: bold;">Branch <b style="color: red">*</b></label>
              <input type="text" name="branch_e" id="branch" autocomplete="off" class="branch font-weight-bold form-control-lg form-control">
            </div>

             {{--<div class="form-group">
              <span class="image"></span>
              <label style="float:left; font-weight: bold;">Select QR Image : <b style="color: red">*</b></label>
             <div><input type="file" name="image_e" id="image" /></div>
             <div class="image"></div>
           </div>--}}

            <div class="form-group text-left d-flex">
             <div><input id="edit_picture"  title="Select QR Image" type="file" name="image_e" /></div><div class="image text-right"></div>
           </div>

            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="submit" id="edit_update" value="Update" class="btn btn-bg update-btn">
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


  $('#edit_picture').bind('change', function() {

      var ext = $('#edit_picture').val().split('.').pop().toLowerCase();
      if ($.inArray(ext, ['png','jpg','jpeg']) == -1){
       toastr.error('Uploaded file image must be jpeg, jpg or png.','Error!' , {
                                "positionClass": "toast-bottom-right"
                            });
       $('#edit_update').prop("disabled", true);
       }else{
        $('#edit_update').prop("disabled", false);

       }
});
    $('#add_picture').bind('change', function() {

      var ext = $('#add_picture').val().split('.').pop().toLowerCase();
      if ($.inArray(ext, ['png','jpg','jpeg']) == -1){
       toastr.error('Uploaded file image must be jpeg, jpg or png.','Error!' , {
                                "positionClass": "toast-bottom-right"
                            });
       $('#add_img').prop("disabled", true);
       }else{
        $('#add_img').prop("disabled", false);

       }
});


  $(function(e){

    var table2 = $('.table-banks').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      // pageLength: {{100}},
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
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
        url:"{!! route('get-bank') !!}",
      },
      columns: [
        { data: 'action', name: 'action' },
        { data: 'title', name: 'title' },
        { data: 'account_no', name: 'account_no' },
        { data: 'description', name: 'description' },
        { data: 'branch', name: 'branch' },
        { data: 'image', name: 'image',
          render: function(data, type, full, meta){

          if(data != '--'){
            return "<img src=\"{{ URL::to('/') }}/public/uploads/" + data + "\"width=\"90\" height=\"45\"/>";
          }else{
            return "<img src=\"{{ URL::to('/') }}/public/uploads/placeholder-image.png \"width=\"90\" height=\"45\"/>";
               }
     
           },
   
         },
        { data: 'created_at', name: 'created_at' }
      ],
      drawCallback: function(){
      $('#loader_modal').modal('hide');
      }    
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

    $(document).on('submit', '.add-bank-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('add-bank') }}",
           dataType: 'json',
          type: 'post',
          data: new FormData(this),
          contentType: false,
          cache: false,
          processData:false,
        beforeSend: function(){
          $('.save-btn').val('Please wait...');
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
        },
        success: function(result){
          $('.save-btn').val('add');
          // $('.save-btn').attr('disabled', true);
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
          if(result.success === true)
          {
            $('.modal').modal('hide');
            toastr.success('Success!', 'Bank added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-bank-form')[0].reset();
            $('.table-banks').DataTable().ajax.reload();
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

    $(document).on('submit', '.edit-bank-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('edit-bank') }}",
        // method: 'post',
        // data: $('.edit-bank-form').serialize(),
           dataType: 'json',
          type: 'post',
          data: new FormData(this),
          contentType: false,
          cache: false,
          processData:false,
        beforeSend: function(){
          $('.update-btn').val('Please wait...');
          $('.update-btn').addClass('disabled');
          $('.update-btn').attr('disabled', true);
        },
        success: function(result){
          $('.update-btn').val('update');
          $('.update-btn').attr('disabled', true);
          $('.update-btn').removeAttr('disabled');
          $('.update-btn').removeClass('disabled');
          if(result.success === true)
          {
            $('.modal').modal('hide');
            toastr.success('Success!', 'Bank Updated successfully',{"positionClass": "toast-bottom-right"});
            $('.edit-bank-form')[0].reset();
            $('.table-banks').DataTable().ajax.reload();              
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

            $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('textarea[name="'+key+'"]').addClass('is-invalid');
          });
        }
      });
    });

    $(document).on('click', '.edit-icon',function(e){
      $('.edit-bank-form')[0].reset();
      var id = $(this).data('id');
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('get-bank-data') }}",
        method: 'get',
        data:{id:id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        success: function(result){
          console.log(result.bank);
          if(result.success == true)
          {
            $("#loader_modal").modal('hide');
            $('.title').val(result.bank.title);
            $('.description').val(result.bank.description);
            $('.account_no').val(result.bank.account_no);
            $('.branch').val(result.bank.branch);
            if(result.bank.qr_image != null){
               $('.image').html("<img src={{ URL::to('/') }}/public/uploads/" + result.bank.qr_image + " width='90' height='45' id='store_image' />");
             }else{
               $('.image').html("<img src={{ URL::to('/') }}/public/uploads/placeholder-image.png width='70' />");
             }
           
            $('input[name=editid]').val(result.bank.id);
            $('#editBankModal').modal('show');
          } 
        },
      });
    });

    $(document).on('click', '.delete-bank', function(){
      var id = $(this).data('id');
      swal({
        title: "Alert!",
        text: "Are you sure you want to delete this Bank?",
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
            url:"{{ route('delete-bank') }}",
            beforeSend:function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            success:function(data){
              $("#loader_modal").modal('hide');
              if(data.error == false)
              {
                toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                $('.table-banks').DataTable().ajax.reload();
              }
              if(data.error == true)
              {
                toastr.error('Error!', data.errorMsg ,{"positionClass": "toast-bottom-right"});
                $('.table-banks').DataTable().ajax.reload();
              }
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

