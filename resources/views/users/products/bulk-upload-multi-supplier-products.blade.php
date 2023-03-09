@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')

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
          <li class="breadcrumb-item active">Products Bulk Upload</li>
      </ol>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle">Products Bulk Upload</h3>
  </div>
</div>

<div class="row errormsgDivBulk d-none">
    <div class="container" style="max-width: 50% !important; min-width: 50% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <div id="msgs_alerts"></div>
      </div>
    </div>
  </div>

<div class="row mb-3 justify-content-center ">
  <div class="col-lg-12 col-md-12 col-12 signform-col">
    <div class="row add-gemstone">
      <div class="col-md-12">
        <div class="bg-white pr-4 pl-4 pt-4 pb-5">

          <ul class="nav nav-tabs">
            <li class="nav-item ">
              <a class="nav-link cut-tab active" data-toggle="tab" href="#tab1">Products Bulk Upload</a>
            </li>
          </ul>

          <div class="tab-content mt-3">
            <div class="tab-pane active" id="tab1">
              <form id="filteredProducts">
                {{csrf_field()}}
              <div class="form-group row filters_div">

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</label>
                    <select class="form-control selecting-suppliers js-states state-tags" name="suppliers">
                        <option value="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{$supplier->id}}">{{@$supplier->reference_name}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose Primary @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</label>
                    <select class="form-control selecting-primary-cat js-states state-tags" name="primary_category">
                        <option value="">Choose Primary @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
                        @foreach($primary_category as $p_cat)
                        <option value="{{$p_cat->id}}">{{@$p_cat->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">@if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</label>
                    <select class="form-control fill_sub_cat_div js-states state-tags" name="sub_category">
                        <option value="">Choose @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</option>
                    </select>
                  </div>
                </div>

                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</label>
                    <select class="form-control product-types js-states state-tags" name="types">
                        <option value="">Choose @if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</option>
                        @foreach($types as $type)
                        <option value="{{$type->id}}">{{$type->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>

                @if (in_array('product_type_2', $product_detail_section))
                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</label>
                    <select class="form-control product-types_2 js-states state-tags" name="types_2">
                        <option value="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
                        @foreach($types_2 as $type)
                        <option value="{{$type->id}}">{{$type->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
                @endif

                @if (in_array('product_type_3', $product_detail_section))
                <div class="col">
                  <div class="form-group">
                    <label class="text-nowrap stock-lable">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</label>
                    <select class="form-control product-types_3 js-states state-tags" name="types_3">
                        <option value="">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
                        @foreach($types_3 as $type)
                        <option value="{{$type->id}}">{{$type->title}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
                @endif

                <div class="col">
                  <!-- <button type="button" class="btn btn-success reset_btn pull-right mt-4">Reset</button> -->
                  <label><b style="visibility: hidden;">Reset</b></label>
                  <div class="input-group-append ml-3">
                    <span class="reset_btn common-icons" title="Reset">
                      <img src="{{asset('public/icons/reset.png')}}" width="27px">
                    </span>
                  </div>
                </div>

                <div class="col pull-right">
                <button class="btn  pull-right mt-4" id="filteredProductsbtn" >Download Filtered Products</button>
                </div>

              </div>
              </form>

              <div class="form-group d-inline">
                <form action="{{route('get-all-prod-qty-excel')}}" method="POST" id="allProducts">
                  <input type="hidden" name="warehouses" id="warehouse_for_all">
                  {{csrf_field()}}
                </form>
                <button class="btn btn-info pull-right" id="alreadybtn" >Already Have File</button>
                <button class="btn btn-success pull-right d-none" id="allProductsbtn" >Download All Products</button>
                <a href="javascript:void(0);" class="export_excel_btn"><span class="btn btn-success pull-right mr-1 " id="examplefilebtn">Download Example File</span></a>
                <a class="btn btn-info pull-right mr-2" href="{{url('bulk-products-upload-form', null)}}">Go to Temporary Products</a>
              </div>

              <br>
              <div class="upload-div" style="display: none;">
                <h3>Upload File</h3>
                <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-dangers">Also Don't Upload Empty File.</span></label>
                <form class="u-b-product-form" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <label for="bulk_import_file">Choose Excel File</label>
                  <input type="file" class="form-control" name="excel" id="excel" accept=".xls,.xlsx" required=""><br>
                  <button class="btn btn-info products-upload-btn" type="button">Upload</button>
                  <input type="hidden" name="supplier">
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    <!-- Loader Modal -->
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

<!-- Product Upload Loader Modal -->
<div class="modal" id="product_upload_loader" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-body">
          <h3 style="text-align:center;">Please wait</h3>
          <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>

          <div class="alert alert-primary export-alert-bulk d-none"  role="alert">
            <i class="fa fa-spinner fa-spin"></i>
            <b> Data is importing... </b>
          </div>

          <div class="alert alert-success export-alert-success-bulk d-none"  role="alert">
            <i class=" fa fa-check "></i>
            <b>Data Imported Successfully !!!</b>
          </div>

          <div class="alert alert-primary export-alert-another-user-bulk d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> Data is importing by another user! Please wait... </b>
          </div>

        </div>
      </div>
    </div>
    </div>
    <!-- New Design Ends Here -->

{{-- Content End Here  --}}

@endsection

@section('javascript')

<script type="text/javascript">
$(function(e){

  $(".state-tags").select2();
  $(document).on('click','.quantity-upload-btn',function(){
    if($('.quantity-upload-btn').val() !== '') {
      $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    }

  });

  $('#allProductsbtn').on('click',function (e) {
      $('.upload-div').show(300);
      e.preventDefault();
      var w_id = $('select[name=warehouses]').val();
      $('#warehouse_for_all').val(w_id);
      $('#allProducts').submit();
    });

  $('#alreadybtn').on('click',function(){
    $('.upload-div').show(300);
  });

  $(document).on('click','.reset_btn', function(){
    $('.selecting-suppliers').val("");
    $('.selecting-primary-cat').val("");
    $('.fill_sub_cat_div').val("");
    $('.product-types').val('');
    $('.product-types_2').val('');
    $('.product-types_3').val('');
     $(".state-tags").select2("", "");
  });

  $(document).on('change',".selecting-primary-cat",function(){
      var category_id=$(this).val();
      // var store_sb_cat =$(this);
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
              var html_string = '';
                html_string+="<option value=''>Select a Sub Category</option>";
              for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
              }
              // $("#state_div").remove();
              // store_sb_cat.after($("<div></div>").text(html_string));
              $(".fill_sub_cat_div").empty();
              $(".fill_sub_cat_div").append(html_string);

          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
  });

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').hide();
    $('.error_div').addClass('d-none');
  });

  @if(Session::has('successmsg'))
      swal( "{{ Session::get('successmsg') }}");
      @php
       Session()->forget('successmsg');
      @endphp
  @endif
  @if(Session::has('msg'))
      swal( "{{ Session::get('msg') }}");
      @php
       Session()->forget('msg');
      @endphp
  @endif
  @if(Session::has('errormsg'))
      $('.error_div').removeClass('d-none');
      var msg = "{{Session::get('errormsg') }}";
       var data = msg.split("&amp;");
      var html = '<ol>';
      for(var i =0; i< data.length; i++){
        if(i != data.length-1){
          html += '<li>'+data[i]+'</li>';
        }
      }
      html += '</ol>';
      $('.error_msg_div').append(html);
      swal("Some of them have Error");
      @php
       Session()->forget('errormsg');
      @endphp
  @endif

  if($('.selecting-primary-cat').val() != '')
  {
      var category_id=$('.selecting-primary-cat').val();
      // var store_sb_cat =$(this);
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
              var html_string = '';
                html_string+="<option value=''>Select a Sub Category</option>";
              for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
              }
              // $("#state_div").remove();
              // store_sb_cat.after($("<div></div>").text(html_string));
              $(".fill_sub_cat_div").empty();
              $(".fill_sub_cat_div").append(html_string);
              var getsession = window.sessionStorage.getItem('form-controlfill_sub_cat_divjs-statesstate-tags');
              if(getsession != null)
              {
                $('.fill_sub_cat_div').val(getsession);
              }


          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
  }

});

$('.products-upload-btn').on('click',function(e){
    var fileInput = $.trim($("#excel").val());
    if (!fileInput && fileInput == '')
    {
      toastr.error('Error!', "Choose File First For Upload", {"positionClass": "toast-bottom-right"});
      return false;
    }
    swal({
      title: "Alert!",
      text: "Are you sure you want to upload the BULK PRODUCTS file ?",
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
        $('.u-b-product-form').submit();
      }
      else
      {
        swal("Cancelled", "", "error");
        $('.u-b-product-form')[0].reset();
      }
   });
  });

  $('.u-b-product-form').on('submit',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-bulk-product') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function(){

        $('.export-alert-success-bulk').addClass('d-none');
        $('.export-alert-bulk').addClass('d-none');
        $('.export-alert-another-user-bulk').addClass('d-none');

        $('#product_upload_loader').modal({
          backdrop: 'static',
          keyboard: false
        });
        // $("#product_upload_loader").data('bs.modal')._config.backdrop = 'static';
        $('#product_upload_loader').modal('show');
        $(".products-upload-btn").attr("disabled", true);
      },
      success: function(data){
        // $('#loader_modal').modal('hide');

        $(".products-upload-btn").attr("disabled", false);
        if(data.success == true)
        {
          toastr.success('Success!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDivBulk').removeClass('d-none');
          }
          $('.u-b-product-form')[0].reset();
          $('.link-of-temp-products').removeClass('d-none');
        }
        if(data.success == "withissues")
        {
          toastr.warning('Warning!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDivBulk').removeClass('d-none');
          }
          $('.u-b-product-form')[0].reset();
          $('.link-of-temp-products').removeClass('d-none');
        }
        if(data.success == false)
        {
          toastr.error('Error!', data.msg, {"positionClass": "toast-bottom-right"});
          $('.u-b-product-form')[0].reset();
        }

      },
      error: function (request, status, error) {
        $('#product_upload_loader').modal('hide');
        $(".products-upload-btn").attr("disabled", false);
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'[]"]').addClass('is-invalid');
        });
      }
    });
  });

  $(document).on('submit','.u-b-product-form',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-supplier-bulk-product-job-status') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').removeClass('d-none');
          checkStatusForSupplierProductsImport();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $("#product_upload_loader").modal('hide');
          $(".products-upload-btn").attr("disabled", false);
          checkStatusForSupplierProductsImport();
        }

      },
      error: function(request, status, error){
        $("#product_upload_loader").modal('hide');
      }
    });
  });

  function checkStatusForSupplierProductsImport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-supplier-bulk-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
          function(){
            console.log("Calling Function Again");
            checkStatusForSupplierProductsImport();
          }, 5000);
        }
        else if(data.status == 0)
        {
          $('.export-alert-success-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.u-b-product-form')[0].reset();
          $('.link-of-temp-products').removeClass('d-none');
          $("#product_upload_loader").modal('hide');
          $(".products-upload-btn").attr("disabled", false);

          if(data.error_msgs != null && data.error_msgs != '')
          {
            toastr.info('Information!', data.error_msgs, {"positionClass": "toast-bottom-right"});
            if (data.exception == null && data.exception == '') {
              setTimeout(function(){
                // window.location.href = "{{ url('bulk-products-upload-form') }}"+"/"+supplier_id;
              }, 500);
            }
          }
          if(data.exception != null && data.exception != '')
          {
            $('#errormsg').html(data.exception);
            $('.errormsgDiv').removeClass('d-none');
          }
          // alert('here');
          window.location.href = "{{url('bulk-products-upload-form')}}"+"/";
          return;
        }
        else if(data.status == 2)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.u-b-product-form')[0].reset();

          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          $('.u-b-product-form')[0].reset();
          $("#product_upload_loader").modal('hide');
        }
      }
    });
  }

  $("#filteredProducts").submit(function(e) {
    e.preventDefault();
    var primary_category = $('.supplier-product-category').val();
    var fill_sub_cat_div = $('.supplier-product-sub-category').val();

    // if(primary_category != '' || fill_sub_cat_div != '')
    // {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"post",
        url:"{{route('download-supplier-all-products')}}",
        data:new FormData(this),
        processData:false,
        contentType:false,
        cache:false,
        beforeSend:function()
        {
            $('#filteredProductsbtn').html('Please Wait');
            $('#filteredProductsbtn').prop('disabled',true);
        },
        success:function(data){
          if(data.status==1)
          {
            $('.pro-export-alert-success').addClass('d-none');

            $('#filteredProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> Downloading! Please Wait...');
            $('#filteredProductsbtn').prop('disabled',true);
            console.log("Calling Function from first part");
            checkStatusForBulkProduct();
          }
          else if(data.status==2)
          {
            $$('#filteredProductsbtn').html('<i class="fa fa-spinner fa-spin"></i> '+data.msg);
            $('#filteredProductsbtn').prop('disabled',true);
            checkStatusForBulkProduct();
          }
        },
        error:function(){
            $('#filteredProductsbtn').html('Download Filtered Products');
            $('#filteredProductsbtn').prop('disabled',false);
        }
      });
  });

  function checkStatusForBulkProduct()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-bulk-product')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForBulkProduct();
            }, 5000);
        }
        else if(data.status==0)
        {
            $('#filteredProductsbtn').html('Download Filtered Products');
            $('#filteredProductsbtn').prop('disabled',false);

            window.location = 'storage/app/bulk_products_export.xlsx';

        }
        else if(data.status==2)
        {
            $('#filteredProductsbtn').html('Download Filtered Products');
            $('#filteredProductsbtn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).on('click', '.export_excel_btn', function(e){
    e.preventDefault();

    $.ajax({
      method: "get",
      url: "{{route('export-bulk-products-file-download')}}" ,
      beforeSend: function(){
        $('.examplefilebtn').html('Please Wait');
        $('.examplefilebtn').prop('disabled',true);
      },
      success: function(data)
      {
        if(data.success)
        {
            $('.examplefilebtn').html('Download Example File');
            $('.examplefilebtn').prop('disabled', false);
            window.location = 'storage/app/Bulk_Products.xlsx';
        }
      },
    });
})

</script>
@endsection
