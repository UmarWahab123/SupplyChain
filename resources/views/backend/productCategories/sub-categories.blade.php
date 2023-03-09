@extends('backend.layouts.layout')

@section('title','Product sub Categories | Supply Chain')

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
          <li class="breadcrumb-item"><a href="{{route('product-categories-list')}}">Category</a></li>
          <li class="breadcrumb-item active">Sub Category</li>
      </ol>
  </div>
</div>



<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
<div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <p class="text-uppercase fontbold mt-1" style="font-size:20px;">{{$parentCategory}}</p>
      <span style="margin-right:auto;margin-left:2px; margin-top:10px;">- Sub Categories</span>
      <div class="mb-0">
        <a class="btn button-st" href="javascript:void(0);" id="add-sub-category-btn">
          Add {{$global_terminologies['subcategory']}}
        </a>
      </div>
      <a onclick="backFunctionality()" style="width: inherit;" class="btn mb-auto button-st ml-3 d-none" data-toggle="tooltip" data-original-title="Back">Back</a>
    </div>
  </div>

  <div class="col-md-12 mt-2">
    <div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
      <b> Margins Updating on products level! Please wait.. </b>
    </div>
    <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
      <b> Margins is already being updated by another user! Please wait.. </b>
    </div>
    <div class="alert alert-success export-alert-success d-none"  role="alert">
      <i class=" fa fa-check "></i>
      <b> Margins updated successfully on products level.</b>
    </div>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
  <div class="entriesbg bg-white custompadding customborder">

    <table class="table entriestable table-bordered table-prod-cat text-center table-responsive">
      <thead>
        <tr>
          <th>Action</th>
          <th>{{$global_terminologies['subcategory']}}</th>
          <th>HS Code</th>
          <th>Prefix</th>
          <th>Import <br> Tax Book</th>
          <th>Vat</th>
          @foreach($customerCategory as $customerCat)
          <th>{{$customerCat->title}} Default <br> Markup Value %</th>
          @endforeach
          {{--<th>Default Product <br> Expiration (Days)</th>--}}
          <th>Created At</th>
        </tr>
      </thead>
    </table>

  </div>
  </div>
</div>


<div class="row entriestable-row mt-4">
    <div class="col-6 bg-white pt-3 pl-2 pb-3 pr-2 ml-3">

        <div class="col-lg-10 col-md-9 pl-3 pr-0">
            <h4>SubCategory History</h4>
          </div>

        <div class="product-update-history">

            <table class="table-sub-category-history entriestable table table-bordered text-center" style="width: 100%;font-size: 12px;">
              <thead>
                <tr>
                  <th>User  </th>
                  <th>Date/time </th>
                  <th>SubCategory</th>
                  <th>Column</th>
                  <th>Old Value</th>
                  <th>New Value</th>
                </tr>
              </thead>
            </table>
          </div>

    </div>
</div>

</div>
<!--  Content End Here -->



<!--  Product-sub-cat Modal Start Here -->
<div class="modal fade subCatMargins" id="subCatMargins">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">×</button>
    </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">@if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif Markups</h3>
        <div class="mt-5 detail" id="detail">

       </div>
      </div>
    </div>
  </div>
</div>

<!--  Product-sub-cat Modal Start Here -->
<div class="modal fade" id="addProdSubCatModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</h3>
          <div class="mt-1">
          {!! Form::open(['method' => 'POST', 'class' => 'add-prod-sub-cat-form']) !!}

            <div class="form-group">
              <label class="pull-left">HS Code</label>
              {!! Form::text('hs_code', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off','placeholder'=>'HS Code Here']) !!}
            </div>

            <div class="form-group">
              <label class="pull-left">@if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</label>
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Sub Category Name', 'autocomplete' => 'off', 'required' => 'required']) !!}
            </div>

            {{--<div class="form-group">
              <label class="pull-left">Default Product Expiration (Days)</label>
              {!! Form::number('expiry', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off', 'required' => 'required','placeholder'=>'Default Product Expiration (Days)']) !!}
            </div>--}}

            <div class="form-group">
              <label class="pull-left">Import Tax Book(%)</label>
              {!! Form::number('import_tax_book', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off','placeholder'=>'Import Tax Book Here']) !!}
            </div>

            <div class="form-group">
              <label class="pull-left">Vat(%)</label>
              {!! Form::number('vat', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'autocomplete' => 'off','placeholder'=>'Vat Here']) !!}
            </div>

            @if($ecommerceconfig_status == 1)
            <div class="form-group">
              <img  id="new_sub_cat_uploaded_image" name="new_sub_cat_img_to_be_uploaded" height="140px" src="{{asset('public/uploads/logo/file-upload.jpg')}}">
            </div>
            <div class="form-group">
               <input type="file" id="new__sub_cat_add_image" name="new_sub_cat_img_to_be_uploaded" class="font-weight-bold form-control-lg form-control">
            </div>
            @endif

            <div style="margin-top: 15px; margin-bottom: 15px;">
            <h4 align="center">@if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif Margins</h4>
            </div>

            <div class="table-responsive d">
              <table class="table table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>@if(!array_key_exists('customer_type', $global_terminologies))Customer Type @else {{$global_terminologies['customer_type']}} @endif</th>
                    <th>Default Markup Value %</th>
                  </tr>
                </thead>
                <input type="hidden" name="product_cat_id" value="{{$id}}">
                <tbody id="dynamic_customer_categories">

                </tbody>
              </table>
            </div>

            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-prod-cat-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div>
        </div>
      </div>
    </div>
  </div>

<!-- Edit modal -->
<div class="modal fade" id="editProdCatModal">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">×</button>
    </div>
    <div class="modal-body text-center">
      <h3 class="text-capitalize fontmed">Edit @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</h3>
      <div class="mt-3">
      <form method="POST" class="edit-sub-cat-form" enctype="multipart/form-data">
        <div id="edit_html_string">

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
        <div id="msg"></div>
      </div>
    </div>
  </div>
</div>

<div id="progressModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
      <!-- Modal content-->
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Processing</h4>
    </div>
    <div class="modal-body">
      Please Wait while the process is running.
      <ul>
        <li>Do Not perform any actions while the process is running.</li>
        <li>Do Not close this tab/window while the process is running.</li>
        <li>Do Not Refresh / Reload the page while the process is running.</li>
      </ul>
      <div class="progress">
        <div class="progress-p" id="update-p" style="left: 49%; position: absolute; color: black; font-weight: bold;">2 %</div>
        <div class="progress-bar progress-bar-striped progress-bar-animated" id="update-b" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 2%"></div>
      </div>
    </div>
  </div>
  </div>
</div>



@endsection

@section('javascript')
<script type="text/javascript">

$('.table-sub-category-history').DataTable({
    "sPaginationType": "listbox",
  processing: false,
  ordering: false,
  searching:false,
  "lengthChange": false,
  serverSide: true,
  "scrollX": true,
  "bPaginate": false,
  "bInfo":false,
  lengthMenu: [ 100, 200, 300, 400],
  "columnDefs": [
    { className: "dt-body-left", "targets": [] },
    { className: "dt-body-right", "targets": [] },
  ],
         ajax: {
          beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            url:"{!! route('get-sub-category-history') !!}",
            },
        columns: [
            { data: 'user_id', name: 'user_id' },
            { data: 'created_at', name: 'created_at' },
            { data: 'sub_category_id', name: 'sub_category_id' },
            { data: 'column_name', name: 'column_name' },
            { data: 'old_value', name: 'old_value' },
            { data: 'new_value', name: 'new_value' },

              ],
              initComplete: function () {
              // Enable THEAD scroll bars
              $('.dataTables_scrollHead').css('overflow', 'auto');

              // Sync THEAD scrolling with TBODY
              $('.dataTables_scrollHead').on('scroll', function () {
                  $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
              });
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });


  var prefix_unique = null;
  var title_msg = 0;
  var prefix_msg = 0;
  $(function(e){

  $(document).on("focus", ".datepicker", function(){
    $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'
    });
  });

  var id = '<?php echo $id; ?>';
  $('.table-prod-cat').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
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
      url:"{!! url('admin/get-product-sub-categories') !!}"+'/'+id,
    },
    columns: [
      { data: 'action', name: 'action' },
      { data: 'title', name: 'title' },
      { data: 'hs_code', name: 'hs_code' },
      { data: 'prefix', name: 'prefix' },
      { data: 'import_tax_book', name: 'import_tax_book' },
      { data: 'vat', name: 'vat' },

      @foreach($customerCategory as $cust)
        { data: '{{$cust->title}}', name: '{{$cust->title}}' },
      @endforeach
      // { data: 'expiry', name: 'expiry' },
      { data: 'created_at', name: 'created_at' }
    ],
       drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
  });

  $(document).on('keyup', '.form-control', function(){
    $(this).removeClass('is-invalid');
    $(this).next().remove();
  });

  // get dynamic customer categories field for add sub category form
  $(document).on('click', '#add-sub-category-btn',function(e){
    $('.add-prod-sub-cat-form').trigger("reset");
    $.ajax({
      url:"{{ route('get-dynamic-category-fields') }}",
      method:"get",
      dataType:"json",
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success:function(data){
        $('#loader_modal').modal('hide');
        if(data.success == true)
        {
          $("#dynamic_customer_categories").html(data.html_string);
          $('#addProdSubCatModal').modal('show');
        }
      },
      error:function(){
        toastr.error('Error!', "Something went wrong, please contact support team." ,{"positionClass": "toast-bottom-right"});
      }
    });
  });

  // edit code here
  $(document).on('click', '.edit-icon',function(e){
    var cat_id = $(this).data('id');
    $.ajax({
      url:"{{ route('get-sub-cat-detail-for-edit') }}",
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
        $('#loader_modal').modal('hide');
        if(data.success == true)
        {
          $("#edit_html_string").html(data.html_string);
          $('#editProdCatModal').modal('show');
        }
      },
      error:function(){
        toastr.error('Error!', "Something went wrong, please contact support team." ,{"positionClass": "toast-bottom-right"});
      }
    });
  });

  $(document).on('submit', '.add-prod-sub-cat-form', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
     $.ajax({
      url: "{{ route('add-product-sub-category') }}",
      method: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      beforeSend: function(){
        $('.save-prod-cat-btn').val('Please wait...');
        $('.save-prod-cat-btn').addClass('disabled');
        $('.save-prod-cat-btn').attr('disabled', true);
      },
      success: function(result){
        $('.save-prod-cat-btn').val('ADD');
        $('.save-prod-cat-btn').attr('disabled', false);
        $('.save-prod-cat-btn').removeAttr('disabled');
        $('.save-prod-cat-btn').removeClass('disabled');
        if(result.success === true)
        {
          $('.modal').modal('hide');
          toastr.success('Success!', 'Sub Category Added Successfully',{"positionClass": "toast-bottom-right"});
          $('.add-prod-sub-cat-form')[0].reset();
          $('.table-prod-cat').DataTable().ajax.reload();
        }
        else if(result.success === false)
        {
          toastr.info('info!', "Sub Category already exists" ,{"positionClass": "toast-bottom-right"});
          return false;
        }
      },
      error: function (request, status, error) {
        $('.save-prod-cat-btn').val('ADD');
        $('.save-prod-cat-btn').attr('disabled', false);
        $('.save-prod-cat-btn').removeAttr('disabled');
        $('.save-prod-cat-btn').removeClass('disabled');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'"]').addClass('is-invalid');
        });
      }
    });
  });

  // detail of sub category
  $(document).on('click', '.view_detail_sub_cat', function(e){
    $('.subCatMargins').modal('show');
    var cat_id = $(this).data('id');
    $.ajax({
      url:"{{ route('get-sub-cat-margin-detail') }}",
      method:"get",
      dataType:"json",
      data:{cat_id:cat_id},
      success:function(data){
        if(data.success == true)
        {
          $("#detail").html(data.html_string);
        }
      },
      error:function(){
        toastr.error('Error!', "Something went wrong, please contact support team." ,{"positionClass": "toast-bottom-right"});
      }
    });
  });

  $(document).on('submit', '.edit-sub-cat-form', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{route('edit-product-category')}}",
      method: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      // data: $('.edit-sub-cat-form').serialize(),
      beforeSend: function(){
        $('.save-edit-cat-btn').val('Loading..');
        $('.save-edit-cat-btn').addClass('disabled');
        $('.save-edit-cat-btn').attr('disabled', true);
      },
      success: function(result){
        $('.save-edit-cat-btn').val('update');
        $('.save-edit-cat-btn').attr('disabled', false);
        $('.save-edit-cat-btn').removeClass('disabled');
        $('.save-edit-cat-btn').removeAttr('disabled');
        if(result.success === true)
        {
          $('.modal').modal('hide');
          toastr.success('Success!', 'Sub Category Details Updated Successfully',{"positionClass": "toast-bottom-right"});
          $('.edit-sub-cat-form')[0].reset();
          $('.table-prod-cat').DataTable().ajax.reload();
          $('.table-sub-category-history').DataTable().ajax.reload();
          prefix_unique = null;
        }
        else if(result.success === false)
        {
          $('.table-prod-cat').DataTable().ajax.reload();
          if(result.sub_title == 1)
          {
            $('#tit').removeClass('d-none');
          }
          else
          {
            $('#tit').addClass('d-none');
          }
          if(result.sub_prefix == 1)
          {
            $('#pre').removeClass('d-none');
          }
          else
          {
            $('#pre').addClass('d-none');
          }
        }
      },
      error: function (request, status, error) {
        $('.save-edit-cat-btn').val('update');
        $('.save-edit-cat-btn').attr('disabled', false);
        $('.save-edit-cat-btn').removeClass('disabled');
        $('.save-edit-cat-btn').removeAttr('disabled');
        $('.form-control').removeClass('is-invalid');
        // $('.form-control').next().remove();
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
      text: "Are you sure you want to delete this Sub-Category?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm)
      {
        $.ajax({
          method:"get",
          dataType:"json",
          data:{id:id},
          url:"{{ route('delete-sub-category') }}",
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
              setTimeout(function(){
              $('.table-prod-cat').DataTable().ajax.reload();
              }, 1000);
            }
            else if(data.error == true)
            {
              toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
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

  // $(document).on('click', '.update-prod-by-cat', function(){
  //   var id = $(this).data('id');
  //   $.ajaxSetup({
  //     headers: {
  //       'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  //     }
  //   });
  //   swal({
  //     title: "Alert!",
  //     text: "Are you sure you want to update the products that are bind with this category?",
  //     type: "info",
  //     showCancelButton: true,
  //     confirmButtonClass: "btn-danger",
  //     confirmButtonText: "Yes!",
  //     cancelButtonText: "No!",
  //     closeOnConfirm: true,
  //     closeOnCancel: false
  //     },
  //   function(isConfirm) {
  //     if (isConfirm)
  //     {
  //       $('#updateModal').modal('toggle');
  //       $('#progressModal').modal({backdrop: 'static', keyboard: false});
  //       $('#progressModal').modal('toggle');
  //       update_currency(id);
  //       var timeout;
  //     }
  //     else
  //     {
  //       swal("Cancelled", "", "error");
  //     }
  //   });
  // });

  // function update_currency(id){
  //   var my_id = id;
  //   $.ajaxSetup({
  //     headers: {
  //       'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  //     }
  //   });
  //   var timeout;
  //   $.ajax({
  //     method:"post",
  //     dataType:"json",
  //     data:{id:my_id},
  //     url:"{{ route('update-products-margins-by-cat') }}",
  //     beforeSend:function(){

  //     },
  //     success:function(result){
  //       if(result.percent)
  //       {
  //         $('#update-b').css('width',result.percent+'%');
  //         $('#update-p').html(result.percent+' %');

  //         setTimeout(function(e){
  //           update_currency(my_id);
  //         }, 10000);
  //       }

  //       if(result.success == true)
  //       {
  //         $('#update-b').css('width','100%');
  //         $('#update-p').html('100 %');
  //         setTimeout(function(e){
  //           $('.modal').modal('hide');
  //           toastr.success('Success!', 'Products Margin Updated Successfully',{"positionClass": "toast-bottom-right"});
  //           $('#update-b').css('width','2%');
  //           $('#update-p').html('2 %');
  //         }, 1000);
  //       }
  //     },
  //     error: function (request, status, error) {
  //       $("#loader_modal").modal('hide');
  //       toastr.error('Error!', 'Something Went Wrong!!!' ,{"positionClass": "toast-bottom-right"});
  //     }
  //  });
  // }

  /*New code for update rates by a JOB*/
  $(document).on('click','.update-prod-by-cat',function(){

    var id = $(this).data('id');

    swal({
      title: "Alert!",
      text: "Are you sure you want to update the products that are bind with this category?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm)
      {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"get",
          url:"{{route('margins-update-job-status')}}",
          data:{id:id},
          beforeSend:function(){

          },
          success:function(data){
            if(data.status == 1)
            {
              $('.export-alert-success').addClass('d-none');
              $('.export-alert').removeClass('d-none');
              $('.update-prod-by-cat').css('pointer-events', 'none');
              checkStatusForMarginsUpdate();
            }
            else if(data.status == 2)
            {
              $('.export-alert-another-user').removeClass('d-none');
              $('.export-alert').addClass('d-none');
              $('.update-prod-by-cat').css('pointer-events', 'none');
              checkStatusForMarginsUpdate();
            }
          },
          error: function(request, status, error){
            toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-margins-update')}}",
      success:function(data)
      {
        if(data.status == 0 || data.status == 2)
        {
          // Do Nothing For Now
        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.update-prod-by-cat').css('pointer-events', 'none');
          checkStatusForMarginsUpdate();
        }
      }
    });
  });

  function checkStatusForMarginsUpdate()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-job-status-margins-update')}}",
      success:function(data){
        if(data.status==1)
        {
          setTimeout(
            function(){
              checkStatusForMarginsUpdate();
            }, 5000);
        }
        else if(data.status == 0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.update-prod-by-cat').css('pointer-events', '');
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status == 2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.update-prod-by-cat').css('pointer-events', '');
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
        }
      }
    });
  }

  });

  function readURL(input,id_mod)
  {
    if (input.files && input.files[0])
    {
      var reader = new FileReader();
      reader.onload = function (e) {
        $(id_mod).attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  }

  $(document).ready(function(e){
    $(document).on('change','#upload_sub_cat_image_file_field', function(){
      var id_mod='#uploaded_sub_cat_image';
      readURL(this,id_mod);
    });
    $(document).on('change','#new__sub_cat_add_image', function(){
      var id_mod ='#new_sub_cat_uploaded_image';
      readURL(this,id_mod);
    });
  });

</script>
<script>
	function backFunctionality() {
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
		if (history.length > 1) {
			return history.go(-1);
		} else {
			var url = "{{ url('/admin/product-categories-list') }}";
			document.location.href = url;
		}
	}
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

