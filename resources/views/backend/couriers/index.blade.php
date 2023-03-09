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
          <li class="breadcrumb-item active">Couriers</li>
      </ol>
  </div>
</div>

<!-- cust-cat = customer-category -->
{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">COURIERS</h4>
        <div class="mb-0">
        <a href="#" class="btn button-st" data-toggle="modal" data-target="#addCourier">ADD</a>
        </div>
    </div>
  </div>
</div> --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">COURIERS</h4>
  </div>
      <div class="col-md-4 text-right">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addCourier">ADD</a>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered courier-table text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Title</th>
                      <th>Courier Type</th>
                      <th>Created At</th>
                      <th>Updated At</th>
                  </tr>
              </thead>

          </table>
        </div>
        </div>

  </div>
</div>


<div class="row align-items-center mb-3 mt-4">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">COURIER TYPES</h4>
  </div>
      <div class="col-md-4 text-right">
        <a href="#" class="btn button-st btn-wd btnAdd @if(Auth::user()->role_id != 8) d-none @endif" data-toggle="modal" data-target="#addCourierType">ADD</a>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered courier-type-table text-center">
              <thead>
                  <tr>
                      @if(Auth::user()->role_id == 8) <th>Action</th> @endif
                      <th>Type</th>
                      <th>Created At</th>
                      <th>Updated At</th>
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
<div class="modal fade" id="addCourier">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Courier</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-courier']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
            </div>
            <div class="form-group">
              <select class="courier-type-select form-control" name="courier_type_select" id="courier-type-select">
                <option value="">Select Courier Type</option>
                @foreach($courier_types as $type)
                  <option value="{{$type->id}}">{{$type->type}}</option>
                @endforeach
              </select>
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

<!-- add cust-cat Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="edit-courier">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Courier</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-courier')}}" class="edit-courier">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control e-cust-cat" placeholder="Enter Customer Category" required="true">
            </div>
            <div class="form-group">
              <select class="courier-type-select form-control" name="courier_type_select" id="courier-type-select">
                <option value="">Select Courier Type</option>
                @foreach($courier_types as $type)
                  <option value="{{$type->id}}">{{$type->type}}</option>
                @endforeach
              </select>
            </div>
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


<!--  Courier Type Modal Start Here -->
<div class="modal fade" id="addCourierType">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
    <div class="modal-body text-center">
      <h3 class="text-capitalize fontmed model_title">Add Courier Type</h3>
      <div class="mt-5">
      {!! Form::open(['method' => 'POST', 'class' => 'add-courier-type']) !!}
      <div class="form-group">
        {!! Form::text('type', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Type', 'id' => 'txtType']) !!}
      </div>

      <div class="form-submit">
        <input type="submit" value="add" class="btn btn-bg btn-Save">
        <input type="reset" value="close" class="btn btn-danger btn-Close">
      </div>
      <input type="hidden" name="id" id="courier-type-id">
      {!! Form::close() !!}
      </div>
    </div>
    </div>
  </div>
</div>

<!-- Courier Type Modal End Here -->



@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     var table2 = $('.courier-table').DataTable({
      "sPaginationType": "listbox",
         processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
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
          url:"{!! route('get-couriers') !!}",
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'title', name: 'title' },
            { data: 'type', name: 'type' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
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



    $(document).on('submit', '.add-courier', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-courier') }}",
          method: 'post',
          data: $('.add-courier').serialize(),
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
              toastr.success('Success!', 'Courier added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-courier')[0].reset();
              $('.courier-table').DataTable().ajax.reload();
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

    $(document).on('submit', '.edit-courier', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-courier') }}",
          method: 'post',
          data: $('.edit-courier').serialize(),
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
              toastr.success('Success!', 'Courier added successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-courier')[0].reset();
              $('.courier-table').DataTable().ajax.reload();
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

    $(document).on('click', '.edit-icon',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldName = $(this).parents('td').next().text();
      var type = $(this).parents('td').next().next().text();
      $('.e-cust-cat').val(oldName);
      $('input[name=editid]').val(sId);
      $("#courier-type-select option:contains(" + type + ")").attr('selected', 'selected');
      
      $('#edit-courier').modal('show');
    });

    $(document).on('click','.delete-icon',function(e){
        var id = $(this).attr("data-id");
          swal({
          title: "Alert!",
          text: "Are you sure you want to suspend this Courier?",
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
                data:{id:id,type:'courier'},
                url:"{{ route('delete-courier') }}",
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
                      $('.courier-table').DataTable().ajax.reload();
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

var table_Type = $('.courier-type-table').DataTable({
        "sPaginationType": "listbox",
         processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
        scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
        lengthMenu: [ 100, 200, 300, 400],
        ajax: {
          url:"{!! route('get-courier-types') !!}",
        },
        columns: [
          @if(Auth::user()->role_id == 8)
            { data: 'action', name: 'action' },
          @endif
            { data: 'type', name: 'type' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
        ],

    });

        $('.dataTables_filter input').unbind();
$('.dataTables_filter input').bind('keyup', function(e) {
if(e.keyCode == 13) {
  // alert();
        table_Type.search($(this).val()).draw();
}
});

 $(document).on('submit', '.add-courier-type', function(e){
      e.preventDefault();
      let btnText = $('.btn-Save').val();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-courier-type') }}",
          method: 'post',
          data: $(this).serialize(),
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
              if (btnText == 'add') {
                $('select').append('<option value="' + result.type['id'] + '">' + result.type['type'] + '</option>');
              }
              else
              {
                $('select option:contains("'+ oldName +'")').text(result.type['type']);
              }
              $('.add-courier-type').trigger('reset');
              toastr.success('Success!', result.msg, {"positionClass": "toast-bottom-right"});
              $('.edit-courier')[0].reset();
              $('.courier-type-table').DataTable().ajax.reload();
              $('.courier-table').DataTable().ajax.reload();
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
 var oldName = '';
$(document).on('click', '.btn-Edit',function(e){
    var sId = $(this).parents('tr').attr('id');
    oldName = $(this).parents('td').next().text();
    $('#txtType').val(oldName);
    $('#courier-type-id').val(sId);

    $('.model_title').html('Update Courier Type');
    $('.btn-Save').val('update');
    $('#add-courier-type').modal('show');
});

$(document).on('click', '.btn-Close',function(e){
    $('.modal').modal('hide');
});
$(document).on('click', '.btnAdd',function(e){
    $('.model_title').html('Add Courier Type');
    $('.btn-Save').val('add');
    $('.add-courier-type').trigger('reset');
    $('#courier-type-id').val(null);
});

$(document).on('click','.btn-Delete',function(e){
  var id = $(this).attr("data-id");
  swal({
    title: "Alert!",
    text: "Are you sure you want to Delete this Courier Type?",
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
          url:"{{ route('courier-type.delete') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $(".modal").modal('hide');
            if (data.success) {
              toastr.success('Success!', data.msg ,{"positionClass": "toast-bottom-right"});
              $('.courier-type-table').DataTable().ajax.reload();
              $('.courier-table').DataTable().ajax.reload();
              $("select option[value='"+ data.type['id'] +"']").remove();
            }
            else{
              toastr.error('Errror!', data.msg ,{"positionClass": "toast-bottom-right"});
            }
          }
      });
    }
    else{
        swal("Cancelled", "", "error");
    }
  });
});

</script>
@stop

