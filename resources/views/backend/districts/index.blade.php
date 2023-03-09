@extends('backend.layouts.layout')

@section('title','Districts | Supply Chain')

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
          <li class="breadcrumb-item active">Cities</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
<div class="row mb-0">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">Cities</h4>
  </div>
    <div class="col-md-4 text-right title-right-col">
      <select class="font-weight-bold form-control-lg form-control select_country" name="select_country" >
      <option value="" disabled>Choose Country</option>
      @foreach($countries as $key => $country)
      <option value="{{ $country->id }}" {{ $country->name == 'Thailand' ? 'selected="selected"' : '' }}>{{ $country->name }}</option>
      @endforeach
    </select>
    <div class="col">
    <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#adddistrictModal">ADD City</a>
    </div>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-districts text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Name</th>
                      <th>Name(Thai)</th>
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

<!--  district Modal Start Here -->
<div class="modal fade" id="adddistrictModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add City</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-district-form']) !!}

            <div class="form-group">
              <select class="form-control country" name="country" disabled>
                <option value="">Select Country</option>
                @foreach($countries as $country)
                  <option value="{{$country->id}}" {{ $country->name == 'Thailand' ? 'selected="selected"' : '' }} > {{$country->name}} </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              {!! Form::text('name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Name']) !!}
            </div>


            <div class="form-submit">
              <input type="hidden" name="country_id" class="country_id">
              <input type="submit" value="add" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div>
        </div>
      </div>
    </div>
  </div>

<!-- add district Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editdistrictModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit City</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-district')}}" class="edit-district-form">
            {{ csrf_field() }}

            <div class="form-group mb-4 pb-1">
              <input type="text" name="name" class="font-weight-bold form-control-lg form-control e-name" placeholder="Enter district" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="thai_name" class="font-weight-bold form-control-lg form-control thai-name" placeholder="Enter district Name In Thai">
            </div>

            <div class="form-group">
              <select class="form-control e-country" name="e_country" disabled>
                <option value="">Select Country</option>
                @foreach($countries as $country)
                  <option value="{{$country->id}}"> {{$country->name}} </option>
                @endforeach
              </select>
            </div>

            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="hidden" name="e_country_id" class="e_country_id">
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
            format:'Y-m-d'});
    });
     var table2 = $('.table-districts').DataTable({
      "sPaginationType": "listbox",
        ajax:
        {
            beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
          url: "{!! route('get-district') !!}",
          data: function(data) { data.select_country = $('.select_country option:selected').val() },

        },
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


        columns: [
            { data: 'action', name: 'action' },
            { data: 'name', name: 'name' },
            { data: 'thai_name', name: 'thai_name' },
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

     $(document).on('change','.select_country',function(){
        var selected = $(this).val();
        if($('.select_country option:selected').val() != '')
        {
          $(".country option[value="+selected+"]").attr("selected", true);
          $(".country_id").val(selected);
          // $('.table-districts').DataTable().destroy();
          $('.table-districts').DataTable().ajax.reload();
        }
      });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });



    $(document).on('submit', '.add-district-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-district') }}",
          method: 'post',
          data: $('.add-district-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'City added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-district-form')[0].reset();
              setTimeout(function(){
                window.location.reload();
              }, 2000);

            }
            else{
              $('#loader_modal').modal('hide');
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

    $(document).on('submit', '.edit-district-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-district') }}",
          method: 'post',
          data: $('.edit-district-form').serialize(),
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
              toastr.success('Success!', 'City Updated successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-district-form')[0].reset();
              $('.table-districts').DataTable().ajax.reload();
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

      $.ajax({
                method:"get",
                dataType:"json",
                data:{id:sId},
                url:"{{ route('edit-district') }}",
                beforeSend:function(){
                   $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                      });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                  console.log(data.district);
                  $('.e-name').val(data.district['name']);
                  $('.thai-name').val(data.district['thai_name']);
                  $(".e-country option[value="+data.district['country_id']+"]").attr("selected", true);
                  $('.e_country_id').val(data.district['country_id']);

                }
             });

      $('input[name=editid]').val(sId);
      $('#editdistrictModal').modal('show');
    });

    $(document).on('click', '.delete-state', function(){
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
                url:"{{ route('delete-district') }}",
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
                      $('.table-districts').DataTable().ajax.reload();
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

