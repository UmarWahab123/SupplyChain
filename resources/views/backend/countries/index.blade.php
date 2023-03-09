@extends('backend.layouts.layout')

@section('title','Countries | Supply Chain')

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
          <li class="breadcrumb-item active">Countries</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
<div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">Countries</h4>
        <div class="mb-0">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addcountryModal">ADD Country</a>
        </div>
    </div>
  </div>
</div>

{{-- <div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">Configuration</h4>
  </div>
      <div class="col-md-4 text-right">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addcountryModal">ADD Country</a>
  </div>
</div> --}}


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-countries text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Abbrevation</th>
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

<!--  country Modal Start Here -->
<div class="modal fade" id="addcountryModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add country</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-country-form']) !!}
            <div class="form-group">
              {!! Form::text('abbrevation', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Abbrevation']) !!}
            </div>

            <div class="form-group">
              {!! Form::text('name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Name']) !!}
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

<!-- add country Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editcountryModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Country</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-country')}}" class="edit-country-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="abbrevation" class="font-weight-bold form-control-lg form-control c-abbrevation" placeholder="Enter country" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="name" class="font-weight-bold form-control-lg form-control c-name" placeholder="Enter country" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="thai_name" class="font-weight-bold form-control-lg form-control c-thai-name" placeholder="Enter country in thai">
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


@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
    var table2 = $('.table-countries').DataTable({
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
          $("#loader_modal").modal('show');
        },
            url:"{!! route('get-country') !!}"},
        columns: [
            { data: 'action', name: 'action' },
            { data: 'abbrevation', name: 'abbrevation' },
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

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });



    $(document).on('submit', '.add-country-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-country') }}",
          method: 'post',
          data: $('.add-country-form').serialize(),
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
              toastr.success('Success!', 'country added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-country-form')[0].reset();
              setTimeout(function(){
                window.location.reload();
              }, 2000);

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

    $(document).on('submit', '.edit-country-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-country') }}",
          method: 'post',
          data: $('.edit-country-form').serialize(),
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
              toastr.success('Success!', 'country Updated successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-country-form')[0].reset();
              $('.table-countries').DataTable().ajax.reload();
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
      var oldAbbrevation = $(this).parents('td').next().text();
      var oldName = $(this).parents('td').next().next().text();
      var oldNamee = $(this).parents('td').next().next().next().text();
      $('.c-abbrevation').val(oldAbbrevation);
      $('.c-name').val(oldName);
      $('.c-thai-name').val(oldNamee);
      $('input[name=editid]').val(sId);
      $('#editcountryModal').modal('show');
    });

    $(document).on('click', '.delete-country', function(){
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
                url:"{{ route('delete-country') }}",
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
                      $('.table-countries').DataTable().ajax.reload();
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

