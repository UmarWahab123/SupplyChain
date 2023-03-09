@extends('backend.layouts.layout')

@section('title','Configuration Management | Supply Chain')

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
          <li class="breadcrumb-item active">Barcode Configuration</li>
      </ol>
  </div>
</div>

<div class="row align-items-center mb-3">
  <div class="col-lg-10 col-md-10">
    <div class="row">
      <div class="col-md-4 col-lg-4 title-col">
        <h4 class="maintitle">Barcode Configuration</h4>
      </div>
    </div>
  </div>

</div>

{{--  --}}

<div class="row mb-3">
     <!-- left Side Start -->

     <div class="col-lg-4 col-md-4">
       <div class="bg-white pt-3 pl-2 h-100">
         <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
           <form id="addBarcodeForm">
                @csrf
               <tbody>
                    <tr>
                      <td class="fontbold">PF</td>
                      <td>
                          <div class="form-check">
                              <input class="form-check-input" type="checkbox" checked name="barcode_columns[]" value="refrence_code" id="refrence_code" >
                              <label class="form-check-label" for="refrence_code">

                              </label>
                          </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="fontbold">HS Code</td>
                      <td>
                           {{-- <img src="http://localhost/supplychain/public/uploads/logo/ice-3_1653978038.jpg" width="" height="40px" alt="System logo"> --}}
                           <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="barcode_columns[]" value="hs_code" id="hs_code" >
                                <label class="form-check-label" for="hs_code">

                                </label>
                           </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="fontbold">Description</td>
                      <td>
                           <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="barcode_columns[]" value="short_desc" id="short_desc" >
                                <label class="form-check-label" for="short_desc">

                                </label>
                           </div>
                      {{-- <img src="http://localhost/supplychain/public/uploads/logo/ice-3_1653978038.jpg" width="" height="40px" alt="System logo"> --}}
                      </td>
                    </tr>
                    <tr>
                      <td class="fontbold">Product Note</td>
                      <td>
                           <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="barcode_columns[]" value="product_notes" id="product_notes" >
                                <label class="form-check-label" for="product_notes">

                                </label>
                           </div>
                           {{-- <img src="http://localhost/supplychain/public/uploads/logo/ice2_1653978038.jpg" width="" height="40px" alt="System logo"> --}}
                      </td>
                    </tr>
                    <tr>
                      <td class="fontbold">Category</td>
                      <td>
                           <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="barcode_columns[]" value="category_id" id="category_id" >
                                <label class="form-check-label" for="category_id">

                                </label>
                           </div>
                           {{-- <img src="http://localhost/supplychain/public/uploads/logo/ice2_1653978038.jpg" width="" height="40px" alt="Login Background Image"> --}}
                      </td>
                    </tr>
                    <tr>
                      <td class="fontbold">Selling Unit</td>
                      <td class="text-nowrap">
                           <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="barcode_columns[]" value="selling_unit" id="selling_unit" >
                                <label class="form-check-label" for="selling_unit">
                                </label>
                           </div>
                      </td>
                    </tr>
                    <tr>
                         <td>
                              <div class="row ml-2 mt-5 col-md-12">
                                   <div><h5><b>Default Printing Size</b></h5></div>
                                   <div class="row ">
                                        <div class="form-check form-check-inline">
                                             <input class="form-check-input" type="radio" name="height_width" id="2.5 x 2.5" value="2.5 x 2.5" checked>
                                             <label class="form-check-label" for="2.5 x 2.5">2.5 x 2.5 cm</label>
                                           </div>
                                           <div class="form-check form-check-inline">
                                             <input class="form-check-input" type="radio" name="height_width" id="3 x 3" value="3 x 3">
                                             <label class="form-check-label" for="3 x 3">3 x 3 cm</label>
                                           </div>
                                           <div class="form-check form-check-inline">
                                             <input class="form-check-input" type="radio" name="height_width" id="4 x 4" value="4 x 4">
                                             <label class="form-check-label" for="4 x 4">4 x 4 cm</label>
                                           </div>
                                           <div class="form-check form-check-inline">
                                             <input class="form-check-input" type="radio" name="height_width" id="c_s" value="custom_size">
                                             <label class="form-check-label" for="c_s">Custom Size</label>
                                           </div>
                                   </div>
                              </div>
                         </td>
                    </tr>
                    <tr>
                      <td >
                        <div class="d-none row col-md-12" id="input_field">
                          <div class="col-md-6">
                            <input type="text" class="form-control height_width_input" name="height_width_input" placeholder="W x L">
                          </div>
                        </div>
                      </td>
                    </tr>
                         <tr>
                              <td>
                                   <button type="submit" class="btn btn-sm" id="barcode_save">Submit</button>
                              </td>
                         </tr>
                  </tbody>
           </form>
         </table>

       </div>

     </div>

</div>

@endsection

@section('javascript')


<script type="text/javascript">

function loadBigImg(event) {
            $('#big_logo-error').html( "" );
            var fileInput =
                document.getElementById('blogo');

            var filePath = fileInput.value;

            var FileSize = document.getElementById("blogo").files[0].size / 1024 / 1024;
            var allowedExtensions =
                    /(\.jpg|\.jpeg|\.png|\.svg)$/i;

             if (FileSize > 2) {
            $('#big_logo-error').html('Image size must be less than 2MB.');
            fileInput.value = '';
            return false;
        }
            if (!allowedExtensions.exec(filePath)) {
                $('#big_logo-error').html('Uploaded file image should be jpeg, jpg, png or svg.');
                fileInput.value = '';
                return false;
            }
            else
            {
            return $('#big_img').attr('src', URL.createObjectURL(event.target.files[0]));
            }
        }
        function loadLoginImg(event) {
            $('#login_bg_img-error').html( "" );
            var fileInput =
                document.getElementById('blogin');

            var filePath = fileInput.value;

            // var FileSize = document.getElementById("blogin").files[0].size / 1024 / 1024;
            var allowedExtensions =
                    /(\.jpg|\.jpeg|\.png|\.svg)$/i;

             if (FileSize > 2) {
            $('#login_bg_img-error').html('Image size must be less than 2MB.');
            fileInput.value = '';
            return false;
        }
            if (!allowedExtensions.exec(filePath)) {
                $('#login_bg_img-error').html('Uploaded file image should be jpeg, jpg, png or svg.');
                fileInput.value = '';
                return false;
            }
            else
            {
            return $('#login_bg_img').attr('src', URL.createObjectURL(event.target.files[0]));
            }
        }

function loadSmallImg(event) {
    $('#small_logo-error').html( "" );
    var fileInput =
        document.getElementById('slogo');

    var filePath = fileInput.value;

    var FileSize = document.getElementById("slogo").files[0].size / 1024 / 1024;
    var allowedExtensions =
            /(\.jpg|\.jpeg|\.png|\.svg)$/i;

     if (FileSize > 2) {
    $('#small_logo-error').html('Image size must be less than 2MB.');
    fileInput.value = '';
    return false;
}
    if (!allowedExtensions.exec(filePath)) {
        $('#small_logo-error').html('Uploaded file image should be jpeg, jpg, png or svg.');
        fileInput.value = '';
        return false;
    }
    else
    {
    return $('#small_img').attr('src', URL.createObjectURL(event.target.files[0]));
    }
}

function loadFaviconImg(event) {
    $( '#favicon-error' ).html( "" );
    var fileInput =
        document.getElementById('flogo');

    var filePath = fileInput.value;

    var FileSize = document.getElementById("flogo").files[0].size / 1024 / 1024;
    var allowedExtensions =
            /(\.jpg|\.jpeg|\.png|\.svg)$/i;

     if (FileSize > 2) {
    $('#favicon-error').html('Image size must be less than 2MB.');
    fileInput.value = '';
    return false;
}
    if (!allowedExtensions.exec(filePath)) {
        $('#favicon-error').html('Uploaded file image should be jpeg, jpg, png or svg.');
        fileInput.value = '';
        return false;
    }
    else
    {
    return $('#favicon_img').attr('src', URL.createObjectURL(event.target.files[0]));
    }
}

    var checkSysColor = function(){
      var sys_col = $('#system_bg_color').val();
      var sys_txt_col = $('#system_bg_txt_color').val();
      if(sys_col === sys_txt_col)
      {
        $('#sys_color_div').attr('style','display:true');
        $('.save-btn').prop('disabled', true);
      }
      else{
        $('#sys_color_div').attr('style','display:none');
        $('.save-btn').prop('disabled', false);
      }
    };
    var checkHoverColor = function(){
      var hover_col = $('#btn_hover_color').val();
      var hover_txt_col = $('#btn_hover_txt_color').val();
      if(hover_col === hover_txt_col)
      {
        $('#hover_color_div').attr('style','display:true');
        $('.save-btn').prop('disabled', true);
      }
      else{
        $('#hover_color_div').attr('style','display:none');
        $('.save-btn').prop('disabled', false);
      }
    };
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
    $('.table-configuration').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        ajax: "{!! route('get-configuration') !!}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'company_name', name: 'name' },
            { data: 'image', name: 'logo' },
            { data: 'email_notification', name: 'email_notification' },
            { data: 'currency_code', name: 'currency_code' },
            { data: 'currency_symbol', name: 'currency_symbol' },
            { data: 'quotation_prefix', name: 'quotation_prefix' },
            { data: 'draft_invoice_prefix', name: 'draft_invoice_prefix' },
            { data: 'invoice_prefix', name: 'invoice_prefix' },
            { data: 'system_email', name: 'system_email' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_at', name: 'updated_at' }
        ]
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });



    $(document).on('click', '.edit-icon', function() {
      // console.log('hello');
      var id = $(this).data('id');
      $.ajax({
        method: "get",
          data:{id:id},
          url:"{{ route('edit-configuration') }}",
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(data)
          {
            $('#editCustomerModalForm').html(data);
            $('#editCustomerModal').modal();
            $('#loader_modal').modal('hide');
          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
      });
    });


    $(document).on('submit', '.edit_con_form', function(e){
      e.preventDefault();
      var check = document.getElementById("email_notification").checked;
      if(check)
      {
        var e_notification = 1;
      }
      else
      {
        var e_notification = 0;
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $( '#big_logo-error' ).html( "" );
      $( '#login_bg_img-error' ).html( "" );
      $( '#small_logo-error' ).html( "" );
      $( '#favicon-error' ).html( "" );

      $.ajax({
        url: "{{ route('update-configuration') }}",
        method: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
            /*$('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');*/
        },
        success: function(result){
          var html = '';
          $('.save-btn').val('update');
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
          // if(result.errors)
          // {
          //   if(result.errors.big_logo)
          //   {
          //     $( '#big_logo-error' ).html( result.errors.big_logo[0] );
          //     $('#blogo').val('');
          //   }
          //   if(result.errors.small_logo)
          //   {
          //     $( '#small_logo-error' ).html( result.errors.small_logo[0] );
          //     $('#slogo').val('');
          //   }
          //   if(result.errors.favicon)
          //   {
          //     $( '#favicon-error' ).html( result.errors.favicon[0] );
          //     $('#flogo').val('');
          //   }
          // }
          if(result.success === true)
          {
            $('.modal').modal('hide');
            toastr.success('Success!', 'Configurations Updated successfully',{"positionClass": "toast-bottom-right"});
            $('.edit_con_form')[0].reset();
            setTimeout(function(){
              window.location.reload();
            }, 2000);
          }
          else
          {
            /*$('#loader_modal').modal('hide');*/
          }
        },
        error: function (request, status, error) {
          /*$('#loader_modal').modal('hide');*/
          $('.save-btn').val('update');

          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
          $('.form-control').removeClass('is-invalid');
          // $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('select[name="'+key+'"]').addClass('is-invalid');
            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'"]').addClass('is-invalid');
          });
        }
      });
    });

  });

$(document).on('keyup focusout','.fieldFocus',function(e){
  var id = $(this).data('id');
  var val = $(this).val();
  var name = $(this).attr('name');
  if(e.which === 0 || e.keyCode === 13)
  {
    var fieldvalue = $(this).data('fieldvalue');
    if ($(this).val() == fieldvalue) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      return false;
    }

    $.ajax({
        method: "get",
          data:{id:id,val:val,name:name},
          url:"{{ route('edit-roles-configuration') }}",
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(data)
          {
            if(data.success == true)
            {
              toastr.success('Success!', 'Data Updated successfully',{"positionClass": "toast-bottom-right"});
              $('#loader_modal').modal('hide');
            }
            else
            {
              toastr.error('Sorry!', 'Something went wrong !!!',{"positionClass": "toast-bottom-right"});
            }
          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
      });
  }
})

$(document).ready(function () {
     $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
$(document).on('submit','#addBarcodeForm',function(e){
    e.preventDefault();
    let data = $(this).serialize();
    console.log(data);
    $.ajax({
        type: "POST",
        url: "{{ route('barcodeConfig-save') }}",
        data: data,
        dataType: "json",
        success: function (response) {
            if(response.status == true)
            {
                toastr.success('Success!', 'Data Added Successfully',{"positionClass": "toast-bottom-right"});
            }
        },
        error:function(response) {
            if(response.status==false){
                toastr.success('Sorry!', 'Something went wrong !',{"positionClass": "toast-bottom-right"});
            }
        }
    });
});

$('input:radio[name="height_width"]').change(function()
{
  if ($(this).val() == 'custom_size')
  {
    $('#input_field').removeClass('d-none');
  }
  else {
    $('#input_field').addClass('d-none');
    $('.height_width_input').val('');
  }
});

</script>
@stop

