@extends('backend.layouts.layout')

@section('title','Product Categories | Supply Chain')

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
          <li class="breadcrumb-item active">Category</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 mb-2 title-col">
    <div class="align-items-center d-sm-flex justify-content-between">
      <h4 class="maintitle">@if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</h4>
        <div class="d-flex mb-0">
          <a class="btn update-products-btn button-st d-none" href="javascript:void(0);">Update Product Level</a>
          <a class="btn upload-excel-btn button-st" href="javascript:void(0);">Bulk Upload</a>
          <a class="btn button-st" id="add_category" href="javascript:void(0);" data-toggle="modal" data-target="#addProdCatModal">ADD @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</a>
        </div>
    </div>
  </div>
</div> --}}

<div class="row align-items-center mb-3">
  <div class="col-md-10 title-col">
      <h4 class="maintitle">@if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</h4>
  </div>
  <div class="col-md-2 text-right title-right-col">
    <div class="d-flex mb-0 float-right">
      <a class="btn update-products-btn button-st d-none" href="javascript:void(0);">Update Product Level</a>
      <a class="upload-excel-btn" href="javascript:void(0);" style="margin-top: 8px;">
      <!-- Bulk Upload -->
      <span class="common-icons" title="Bulk Upload" style="padding: 13px 15px;">
        <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
      </span>
      </a>
      <a class="btn button-st" id="add_category" href="javascript:void(0);" data-toggle="modal" data-target="#addProdCatModal">ADD @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</a>
    </div>
  </div>
</div>
<div class="row entriestable-row mt-0">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
      <table class="table entriestable table-bordered table-prod-cat text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>Title</th>
            <th>@if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif </th>
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

<!--  Product-cat Modal Start Here -->
<div class="modal fade" id="addProdCatModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-prod-cat-form', 'enctype'=>"multipart/form-data"]) !!}


            {{--<div class="form-group mb-4 pb-1">
              <select class="font-weight-bold form-control-lg form-control" title="Choose a Parent" data-live-search="true" name="parent_id" >
                <option value="">Select a parent</option>
                @foreach($categories as $category)
                  <option value="{{$category->id}}">{{$category->title}}</option>
                @endforeach
              </select>
            </div>--}}

            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
            </div>

            <input type="hidden" name="ecom_enable_status" value="{{$ecommerceconfig_status}}">

            @if($ecommerceconfig_status == 1)
            <div class="form-group">
              <img  id="new_cat_uploaded_image" height="140px" src="{{asset('public/uploads/logo/file-upload.jpg')}}">
            </div>
            <div class="form-group">
              <input type="file" id="new_cat_add_image" name="category_image" class="font-weight-bold form-control-lg form-control">
            </div>
            @endif

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg edit-prod-btn">
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
  <div class="modal fade" id="editProdCatModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Edit @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</h3>
          <div class="mt-5">
          <form method="POST" class="edit-prod-cat-form" enctype="multipart/form-data">
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

<form id="excelExportFormData" method="get" action="{{route('export-categories-data')}}" class="excelExportFormData" enctype="multipart/form-data">

</form>

{{-- Upload excel file --}}
<div class="modal fade" id="uploadExcel">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Upload Excel</h3>
        <div class="mt-3">
          <form method="post" action="{{route('upload-bulk-categories')}}" class="upload-excel-form" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group">
              <a href="javascript:void(0);" class="export_excel_btn"><span class="btn btn-success" id="examplefilebtn">Download Example File</span></a>
            </div>

            <div class="form-group">
              <input type="file" name="excel" class="font-weight-bold form-control-lg form-control" required="">
            </div>

            <div class="form-submit">
              <input type="submit" value="upload" id="upload_bulk_cat_btn" class="btn btn-bg save-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
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

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on('click','.upload-excel-btn',function(){
      $('#uploadExcel').modal('show');
    });

    $(document).on('click','.export_excel_btn',function(){
      $('#excelExportFormData').submit();
    });

    $(document).on('click','#upload_bulk_cat_btn',function(){
      $('#uploadExcel').modal('hide');
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
    });

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
        timepicker:false,
        format:'Y-m-d'
      });
    });

    var table2 = $('.table-prod-cat').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
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
        url: "{!! route('get-product-categories') !!}",
      },
      columns: [
          { data: 'action', name: 'action' },
          { data: 'title', name: 'title' },
          { data: 'sub_categories', name: 'sub_categories' },
          { data: 'created_at', name: 'created_at' }
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

    $(document).on('submit', '.add-prod-cat-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
     $.ajax({
        url: "{{ route('add-product-category') }}",
        method: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('.edit-prod-btn').val('Please wait...');
          $('.edit-prod-btn').addClass('disabled');
          $('.edit-prod-btn').attr('disabled', true);
        },
        success: function(result){
          $('.edit-prod-btn').val('ADD');
          $('.edit-prod-btn').attr('disabled', false);
          $('.edit-prod-btn').removeAttr('disabled');
          $('.edit-prod-btn').removeClass('disabled');
          if(result.success === true){
            $('.modal').modal('hide');
            toastr.success('Success!', 'Product Category added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-prod-cat-form')[0].reset();
            $('.table-prod-cat').DataTable().ajax.reload();
            /*setTimeout(function(){
              window.location.reload();
            }, 2000);*/
          }
          else{
            toastr.error('Error!', 'Category Title Already Taken',{"positionClass": "toast-bottom-right"});
          }
        },
        error: function (request, status, error) {
          $('.edit-prod-btn').val('ADD');
          $('.edit-prod-btn').attr('disabled', false);
          $('.edit-prod-btn').removeClass('disabled');
          $('.edit-prod-btn').removeAttr('disabled');
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

    // edit code here
    $(document).on('click', '.edit-icon',function(e){
      var cat_id = $(this).data('id');
      $.ajax({
        url:"{{ route('get-prod-cat-name-for-edit') }}",
        method:"get",
        dataType:"json",
        data:{cat_id:cat_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success:function(data){
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            $("#edit_html_string").html(data.html_string);
            $('#editProdCatModal').modal('show');
          }
        },
        error:function(request, status, error){
          $("#loader_modal").modal('hide');
          toastr.error('Error!', 'Something went wrong',{"positionClass": "toast-bottom-right"});
        }
      });
    });

    // edit form submit
    $(document).on('submit', '.edit-prod-cat-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{route('edit-product-parent-category')}}",
          method: 'post',
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
            $('.save-btn').val('UPDATE');
            $('.save-btn').attr('disabled', false);
            $('.save-btn').removeClass('disabled');
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Category Updated Successfully',{"positionClass": "toast-bottom-right"});
              $('.edit-prod-cat-form')[0].reset();
              $('.table-prod-cat').DataTable().ajax.reload();
            }
            else{
              toastr.error('Error!', 'Category Title Already Taken',{"positionClass": "toast-bottom-right"});
            }
          },
          error: function (request, status, error) {
                $('.save-btn').val('UPDATE');
                $('.save-btn').attr('disabled', false);
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

    $(document).on('click', '.delete-icon', function(){
      var id = $(this).data('id');
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
                method:"get",
                dataType:"json",
                data:{id:id},
                url:"{{ route('delete-category') }}",
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
                      $('.table-prod-cat').DataTable().ajax.reload();
                      }, 1000);
                    }else if(data.error == true){
                      toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    }
                },
                error: function(request, status, error){
                  $("#loader_modal").modal('hide');
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
    });

    $(document).on('click', '.update-products-btn', function(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      swal({
          title: "Alert!",
          text: "Are you sure you want to update the products against Restaurant, Hotel, Retail, Private & Catering margins?",
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
            var timeout;
             $.ajax({
                method:"post",
                dataType:"json",
                url:"{{ route('update-products-margins') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                    timeout = setTimeout(function(){
                    var alertMsg = "<p style='color:red;''>Please be paitent this process will take some time .....</p>";
                    $('#msg').html(alertMsg);
                  }, 5000);
                },
                success:function(data){
                  clearTimeout(timeout);
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                  }
                  else
                  {
                    toastr.error('Error!', 'Something Went Wrong!!!' ,{"positionClass": "toast-bottom-right"});
                  }
                },
                error: function(request, status, error){
                  $("#loader_modal").modal('hide');
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
    });



  });

      function readURL(input,id_mod){

        if (input.files && input.files[0]) {
          var reader = new FileReader();

        reader.onload = function (e) {
          $(id_mod).attr('src', e.target.result);
        }

          reader.readAsDataURL(input.files[0]);
        }
      }

    $(document).ready(function(e){
        $(document).on('change', '#upload_image_file_field', function(){
        var id_mod='#uploaded_image';
          readURL(this,id_mod);

          });

    $(document).on('change', '#new_cat_add_image', function(){
    var id_mod ='#new_cat_uploaded_image';
          readURL(this,id_mod);

          });


  });



@if(Session::has('message'))
  toastr.success('Success!', "{{ Session::get('message') }}",{"positionClass": "toast-bottom-right"});
@endif

@if(Session::has('errorMsg'))
  toastr.error('Error!', "{{ Session::get('errorMsg') }}",{"positionClass": "toast-bottom-right"});
@endif

$(document).on('click', '.snyc_with_ecom', function(){
    let id = $(this).data('id');
    swal({
      title: "Are you sure?",
      text: "You want to Enable this category to Ecom?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, Update it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
      },
      function (isConfirm) {
        if(isConfirm)
        {
          $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });
           $.ajax({
              url: "{{ route('sync-product-category') }}",
              method: 'post',
              dataType: 'json',
              data: 'id='+id,
              beforeSend: function(){

              },
              success: function(result){
                if(result.success == true)
                {
                  toastr.success('Success!', 'Operation Succeeded !!!',{"positionClass": "toast-bottom-right"});
                }
                else
                {
                  toastr.error('Sorry!', 'Something Went Wrong !!!',{"positionClass": "toast-bottom-right"});
                }
              },
              error: function (request, status, error) {
              }
            });
        }
      }
    );

});

</script>

@stop

