@extends('backend.layouts.layout')

@section('title','Product Type | Supply Chain')

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
          <li class="breadcrumb-item active">Product Types</li>
      </ol>
  </div>
</div>


<div class="col-lg-12 col-md-12 pb-5">
    <div class="bg-white pr-4 pl-4 pt-4 pb-5 mb-2">

 <ul class="nav nav-tabs">
    <li class="nav-item ">
      <a class="nav-link cut-tab active" data-toggle="tab" href="#type1">Product Types</a>
    </li>
    <li class="nav-item">
      <a class="nav-link cut-tab" data-toggle="tab" href="#type2">Product Types 2</a>
    </li>
    <li class="nav-item">
        <a class="nav-link cut-tab" data-toggle="tab" href="#type3">Product Types 3</a>
      </li>
  </ul>

  <div class="tab-content mt-3">
    <div class="tab-pane active" id="type1">
        @include('backend.productType.includes.type1_table')
    </div>

    <div class="tab-pane" id="type2">
        @include('backend.productType.includes.type2_table')
    </div>

    <div class="tab-pane" id="type3">
        @include('backend.productType.includes.type3_table')
    </div>

  </div>
    </div>
</div>


</div>
<!--  Content End Here -->

<!--  cust-cat Modal Start Here -->
@include('backend.productType.includes.type1_add_modal')
<!-- add cust-cat Modal End Here -->

<!--  cust-cat Modal Start Here -->
@include('backend.productType.includes.type2_add_modal')

<!-- add cust-cat Modal End Here -->

<!--  cust-cat Modal Start Here -->
@include('backend.productType.includes.type3_add_modal')
<!-- add cust-cat Modal End Here -->

<!-- Edit modal -->
  <div class="modal fade" id="editProdTypeModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit Product Type</h3>
          <div class="mt-5">
          <form method="POST" class="edit-prod-type-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control e-prod-type" placeholder="Enter Customer Category" required="true">
            </div>

            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="hidden" name="check_secondary" value="false">
              <input type="hidden" name="check_tertiary" value="false">
              <input type="submit" value="Update" class="btn btn-bg">
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

  var table2 = $('.table-product-type').DataTable({
    "sPaginationType": "listbox",
         processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
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
          url:"{!! route('get-product-types') !!}",
        },
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


  var table3 = $('.table-product-secondary-type').DataTable({
    "sPaginationType": "listbox",
         processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
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
          url:"{!! route('get-product-secondary-types') !!}",
        },
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



    var table4 = $('.table-product-type-3').DataTable({
     "sPaginationType": "listbox",
         processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
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
          url:"{!! route('get-product-types-3') !!}",
        },
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




//   $('.dataTables_filter input').unbind();
//   $('.dataTables_filter input').bind('keyup', function(e) {
//   if(e.keyCode == 13) {
//   // alert();
//         table2.search($(this).val()).draw();
//   }
//   });
    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });



    $(document).on('submit', '.add-prod-type-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-product-type') }}",
          method: 'post',
          data: $('.add-prod-type-form').serialize(),
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
              toastr.success('Success!', 'Product Type added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-prod-type-form')[0].reset();
              setTimeout(function(){
                window.location.reload();
              }, 2000);

            } else{
              $('#loader_modal').modal('hide');
              $('.modal').modal('hide');
            }

          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
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
    $(document).on('submit', '.add-prod-secondary-type-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-prod-secondary-type') }}",
          method: 'post',
          data: $('.add-prod-secondary-type-form').serialize(),
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
              toastr.success('Success!', 'Product Type added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-prod-secondary-type-form')[0].reset();
              $('.table-product-secondary-type').DataTable().ajax.reload();

            } else{
              $('#loader_modal').modal('hide');
              $('.modal').modal('hide');
            }

          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
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

    $(document).on('submit', '.add-prod-type-3-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-prod-type-3') }}",
          method: 'post',
          data: $('.add-prod-type-3-form').serialize(),
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
              toastr.success('Success!', 'Product Type added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-prod-type-3-form')[0].reset();
              $('.table-product-type-3').DataTable().ajax.reload();

            } else{
              $('#loader_modal').modal('hide');
              $('.modal').modal('hide');
            }

          },
          error: function (request, status, error) {
                $('.save-btn').val('add');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
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

    $(document).on('submit', '.edit-prod-type-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('edit-product-type') }}",
          method: 'post',
          data: $('.edit-prod-type-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('edit');
            $('.save-btn').attr('disabled', false);
            $('.save-btn').removeClass('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product Type edited successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-prod-type-form')[0].reset();
              if(result.secondary == true)
              {
                $('.table-product-secondary-type').DataTable().ajax.reload();
              } else if(result.tertiary == true) {
                $('.table-product-type-3').DataTable().ajax.reload();
              }
              else
              {
                $('.table-product-type').Datatable().ajax.reload();
              }

            }

          },
          error: function (request, status, error) {
                $('.save-btn').val('edit');
                $('.save-btn').attr('disabled', false);
                $('.save-btn').removeClass('disabled');
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
      $('.e-prod-type').val(oldName);
      $('input[name=editid]').val(sId);
      $('#editProdTypeModal').modal('show');
    });

    $(document).on('click', '.edit-icon-secondary',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldName = $(this).parents('td').next().text();
      $('.e-prod-type').val(oldName);
      $('input[name=editid]').val(sId);
      $('input[name=check_secondary]').val("true");
      $('#editProdTypeModal').modal('show');
    });

    $(document).on('click', '.edit-icon-tertiary',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldName = $(this).parents('td').next().text();
      $('.e-prod-type').val(oldName);
      $('input[name=editid]').val(sId);
      $('input[name=check_tertiary]').val("true");
      $('#editProdTypeModal').modal('show');
    });

  });
</script>
@stop

