@extends('backend.layouts.layout')

@section('title','Units | Supply Chain')

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
          <li class="breadcrumb-item active">Units</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">UNITS</h4>
        <div class="mb-0">
        <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addUnitModal">ADD UNIT</a>
        </div>
    </div>
  </div>
</div> --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">UNITS</h4>
  </div>
      <div class="col-md-4 text-right">
         <a href="#" class="btn button-st btn-wd" data-toggle="modal" data-target="#addUnitModal">ADD UNIT</a>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
          <table class="table entriestable table-bordered table-prod-cat text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Title</th>
                      <th>Decimals</th>
                      <th>Created At</th>
                      <th>Updated At</th>
                  </tr>
              </thead>

          </table>
        </div>
        </div>

  </div>
</div>

  <!-- units history -->
  <div class="pt-5 pb-3 pr-3">
    <div class="row">

      <div class="col-lg-12">
        <div class="purchase-order-detail col-lg-6 col-md-5 pt-2 pb-3 pr-3">
          <table class="table-units-history headings-color entriestable table table-bordered text-center">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>User </th>
                <th>Date/Time</th>
                <!-- <th>Order #</th> -->
                <th>Title</th>
                <th>Column</th>
                <th>Old Value</th>
                <th>New Value</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
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
          <h3 class="text-capitalize fontmed">Add Unit</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-unit-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
            </div>

            <div class="form-group">
              {!! Form::number('decimal_places', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Decimals']) !!}
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

<!-- add Unit Modal End Here -->

<!-- Edit modal -->
<!--   <div class="modal fade" id="editUnitModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Unit</h3>
          <div class="mt-5">
          <form method="POST" action="{{route('edit-unit')}}" class="edit-unit-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control e-prod-cat" placeholder="Enter Unit" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="number" name="decimal_places" class="font-weight-bold form-control-lg form-control decimal_places" placeholder="Enter Decimals" required="true">
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
  </div> -->
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
     var table2 = $('.table-prod-cat').DataTable({
      "sPaginationType": "listbox",
         processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
        scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
        lengthMenu: [ 100, 200, 300, 400],
        ajax:
        {
          beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      url:"{!! route('get-unit') !!}",
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'title', name: 'title' },
            { data: 'decimal_places', name: 'decimal_places' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
        ],
         drawCallback: function(){
      $('#loader_modal').modal('hide');
    },

    });



  $('.table-units-history').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
    },
    ordering: false,
    searching: false,
    "lengthChange": false,
    serverSide: true,
    "scrollX": true,
    "scrollY": '50vh',
    scrollCollapse: true,
    "bPaginate": false,
    "bInfo": false,
    lengthMenu: [100, 200, 300, 400],

    ajax: {
      url: "{!! route('unit-history') !!}",
      data: function(data) {

      },
    },
    columns: [
      // { data: 'checkbox', name: 'checkbox' },
      {
        data: 'user_name',
        name: 'user_name'
      },
      {
        data: 'created_at',
        name: 'created_at'
      },
      // {
      //   data: 'order_no',
      //   name: 'order_no'
      // },
      {
        data: 'item',
        name: 'item'
      },
      // { data: 'name', name: 'name' },
      {
        data: 'column_name',
        name: 'column_name'
      },
      {
        data: 'old_value',
        name: 'old_value'
      },
      {
        data: 'new_value',
        name: 'new_value'
      },

    ],

  });

    $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();


  });

    $(document).on("keyup focusout","input[type=text]",function(e) {
    var id = $(this).data('id');
      var attr_name = $(this).attr('name');
      var fieldvalue = $(this).prev().data('fieldvalue');
      if (e.keyCode === 27 && $(this).hasClass('active')) {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        // thisPointer.val(fieldvalue);
        thisPointer.prev().removeClass('d-none');
        thisPointer.removeClass('active');
      }

      var new_value = $(this).val();

      if ((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')) {
      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        // $(this).prev().html(fieldvalue);
        return false;
      }

      var attr_name = $(this).attr('name');
      var rowId = $(this).parents('tr').attr('id');
      if($(this).attr('name') == 'title'|| 'decimal_places')
      {
        if($(this).val() == null)
        {
          return false;
        }
        else if($(this).val() !== '' && $(this).hasClass('active'))
        {
          var old_value = $(this).prev().html();

          $(this).prev().html($(this).val());
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');

        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });

          $.ajax({
            type: "post",
            url: "{{ route('edit-unit') }}",
            dataType: 'json',
            data: 'id='+id+'&'+'&'+attr_name+'='+encodeURIComponent($(this).val())+'&'+'old_value='+old_value,
            beforeSend: function(){
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(data){
              $("#loader_modal").modal('hide');
                if(data.success == true)
                 {
                 toastr.success('Success!', 'Unit Updated successfully',{"positionClass": "toast-bottom-right"});
                 $('.table-prod-cat').DataTable().ajax.reload();
                 $('.table-units-history').DataTable().ajax.reload();
                  }


           },
            error: function (request, status, error) {

               json = $.parseJSON(request.responseText);
               $("#loader_modal").modal('hide');
               toastr.error('Error!', json.errors.title ,{"positionClass": "toast-bottom-right"});
               $('.table-prod-cat').DataTable().ajax.reload();
            }
          });
       }
      }
    }
  });



/*
        $('.dataTables_filter input').unbind();
$('.dataTables_filter input').bind('keyup', function(e) {
if(e.keyCode == 13) {
  // alert();
        table2.search($(this).val()).draw();
}
});*/

    // $(document).on('keyup', '.form-control', function(){
    //   $(this).removeClass('is-invalid');
    //   $(this).next().remove();
    // });



    $(document).on('submit', '.add-unit-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-unit') }}",
          method: 'post',
          data: $('.add-unit-form').serialize(),
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
              toastr.success('Success!', 'Unit added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-unit-form')[0].reset();
              $('.table-prod-cat').DataTable().ajax.reload();

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

    // $(document).on('submit', '.edit-unit-form', function(e){
    //   e.preventDefault();
    //   $.ajaxSetup({
    //       headers: {
    //           'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    //       }
    //   });
    //    $.ajax({
    //       url: "{{ route('edit-unit') }}",
    //       method: 'post',
    //       data: $('.edit-unit-form').serialize(),
    //       beforeSend: function(){
    //         $('.save-btn').val('Please wait...');
    //         $('.save-btn').addClass('disabled');
    //         $('.save-btn').attr('disabled', true);
    //       },
    //       success: function(result){
    //         $('.save-btn').val('update');
    //         $('.save-btn').attr('disabled', true);
    //         $('.save-btn').removeAttr('disabled');
    //         $('.save-btn').removeClass('disabled');
    //         if(result.success === true){
    //           $('.modal').modal('hide');
    //           toastr.success('Success!', 'Unit Updated successfully',{"positionClass": "toast-bottom-right"});
    //           $('.edit-unit-form')[0].reset();
    //           $('.table-prod-cat').DataTable().ajax.reload();
    //         }


    //       },
    //       error: function (request, status, error) {
    //             $('.save-btn').val('add');
    //             $('.save-btn').removeClass('disabled');
    //             $('.save-btn').removeAttr('disabled');
    //             $('.form-control').removeClass('is-invalid');
    //             $('.form-control').next().remove();
    //             json = $.parseJSON(request.responseText);
    //             $.each(json.errors, function(key, value){
    //                 $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
    //                  $('input[name="'+key+'"]').addClass('is-invalid');
    //             });
    //         }
    //     });
    // });

    $(document).on('click', '.edit-icon',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldName = $(this).parents('td').next().text();
      var oldDecimal = $(this).parents('td').next().next().text();
      $('.e-prod-cat').val(oldName);
      $('.decimal_places').val(oldDecimal);
      $('input[name=editid]').val(sId);
      $('#editUnitModal').modal('show');
    });

    $(document).on('click', '.delete-unit', function(){
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
                url:"{{ route('delete-unit') }}",
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
                    $('.table-prod-cat').DataTable().ajax.reload();
                    $('.table-units-history').DataTable().ajax.reload();
                  }
                  else
                  {
                    toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    $('.table-prod-cat').DataTable().ajax.reload();
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

