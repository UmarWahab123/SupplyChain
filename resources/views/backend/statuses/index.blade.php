@extends('backend.layouts.layout')

@section('title','Statuses | Supply Chain')

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
          <li class="breadcrumb-item active">Statuses</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">Statuses</h3>
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">


    <div class="d-sm-flex justify-content-between">
      <h4>Statuses</h4>
      <div class="mb-3">
        <a class="btn button-st" href data-toggle="modal" data-target="#addStatusModal">
          ADD STATUSES
        </a>
      </div>
    </div>

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-statuses text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Title</th>
                      <th>Created At</th>
                      <th>Updated At</th>
                  </tr>
              </thead>

          </table>
    </div>

    </div>

  </div>
</div>

<!--  Content End Here -->

<!--  Product-cat Modal Start Here -->
<div class="modal fade" id="addStatusModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Status</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-status-form']) !!}


            <div class="form-group mb-4 pb-1">
              <select class="font-weight-bold form-control-lg form-control" title="Choose a Parent" data-live-search="true" name="parent_id" >
                <option value="0">Select a parent</option>
                @foreach($statuses as $status)
                  <option value="{{$status->id}}">{{$status->title}}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Status']) !!}
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

<!-- add Product-cat Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editStatusModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Status</h3>
          <div class="mt-5">
          <form method="POST" class="edit-status-form">
            {{ csrf_field() }}
            <div class="form-group d-none">
                    <select class="font-weight-bold form-control-lg form-control" name="parent_id">
                      <option value="0">Select a category</option>
                      @foreach($statuses as $status)
                        <option value="{{$status->id}}">{{$status->title}}</option>
                      @endforeach
                    </select>
            </div>
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control status-title" placeholder="Enter Status" required="true" autocomplete="off">
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
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-statuses').DataTable({
      "sPaginationType": "listbox",
         processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        ajax:{
            beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
            url: "{!! route('get-status') !!}"},
        columns: [
            { data: 'action', name: 'action' },
            { data: 'title', name: 'title' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
        ],
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },

    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });



    $(document).on('submit', '.add-status-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-status') }}",
          method: 'post',
          data: $('.add-status-form').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('#loader_modal').modal('hide');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Status added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-status-form')[0].reset();
              setTimeout(function(){
                $('.table-statuses').DataTable().ajax.reload();
              }, 500);

            }


          },
          error: function (request, status, error) {
                $('#loader_modal').modal('hide');
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


    $(document).on('submit', '.edit-status-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-status') }}",
          method: 'post',
          data: $('.edit-status-form').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('#loader_modal').modal('hide');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Status updated successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-status-form')[0].reset();
              setTimeout(function(){
                $('.table-statuses').DataTable().ajax.reload();
              }, 500);

            }


          },
          error: function (request, status, error) {
                $('#loader_modal').modal('hide');
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
      var nametrim = $.trim(oldName);
      $('.status-title').val(nametrim);
      $('input[name=editid]').val(sId);
      $('#editStatusModal').modal('show');
    });

  });
</script>
@stop

