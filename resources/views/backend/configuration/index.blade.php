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
          <li class="breadcrumb-item active">Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}


{{-- <div class="col-lg-12 col-md-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1 col-md-1">
    @if(@$configuration->logo != Null )
    <div class="logo-container">
    <img src="{{asset('public/uploads/logo/'.$configuration->logo)}}" style="border-radius:60%;height:78px;" alt="logo" class="img-fluid lg-logo">
    
    </div>
      @else
      <div class="logo-container">
      <img src="{{asset('public/uploads/logo/temp-logo.png')}}" alt="Avatar" class="image">
      
    </div>
    @endif
  </div>
  <div class="col-lg-8 col-md-7 p-0">
    <h4 class="mb-0">Configuration</h4>
  </div>
  <div class="col-lg-3 col-md-4 p-0">
    <div class="mb-0">
        <a href="javascript:void(0);" data-id="{{@$configuration->id}}"  class="btn button-st btn-wd edit-icon">Edit Cournfiguration</a> 
      </div>
  </div>
</div>

</div> --}}

<div class="row align-items-center mb-3">
  <div class="col-lg-5 col-md-6">
    <div class="row">
      <div class="col-md-6 title-col">
        <h4 class="maintitle">Configuration</h4>
      </div>    
      <div class="col-md-6 text-right">
        <a href="javascript:void(0);" data-id="{{@$configuration->id}}"  class="btn button-st edit-icon btn-wd">Edit Configuration</a> 
      </div>
    </div>
  </div>
  <div class="col-lg-7 col-md-6">
    <div class="row">
      <div class="col-md-8 title-col">
        <h4 class="maintitle">Deployments</h4>
      </div>    
      <div class="col-md-4 text-right pull-right">
        <a href="javascript:void(0);" class="btn button-st btn-wd" id="add_new_deployment" data-toggle="modal" data-target="#deployment-Modal">Add Deployement</a> 
      </div>
    </div>
  </div>

  <div class="col-lg-5 col-md-6 d-none">
    <div class="row">
      <div class="col-md-8 title-col">
        @if(Auth::user()->role_id == '10' || Auth::user()->role_id == 10)
        <h4 class="maintitle">Maximum Accounts Configuration</h4>
        @endif
      </div>    
    </div>
  </div>

</div>



<div class="row mb-3">
<!-- left Side Start -->




<div class="col-lg-5 col-md-6">
  <div class="bg-white pt-3 pl-2 h-100">
    <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
      <tbody>
        <tr>
          <td class="fontbold">System Name <b style="color: red;">*</b></td>
          <td> 
            {{@$configuration->company_name}}
          </td>
        </tr>
        <tr>
          <td class="fontbold">System Big Logo</td>
          <td>
            @if(@$configuration->logo != Null )
              @php $image_path = public_path('uploads/logo/'.$configuration->logo); @endphp
              @if(file_exists($image_path))
                <img src="{{asset('public/uploads/logo/'.$configuration->logo)}}" width="" height="40px" alt="System logo">
              @else
                <img src="{{asset('public/site/assets/backend/img/upload.jpg')}}" width="" height="40px" alt="Not Found">
              @endif
            @else
              <img src="{{asset('public/site/assets/backend/img/upload.jpg')}}" width="" height="40px" alt="Not Found">
            @endif
          </td>
        </tr>
        <tr>
          <td class="fontbold">System Small Logo</td>
          <td>
            @if(@$configuration->small_logo != Null )
              @php $image_path = public_path('uploads/logo/'.$configuration->small_logo); @endphp
              @if(file_exists($image_path))
                <img src="{{asset('public/uploads/logo/'.$configuration->small_logo)}}" width="" height="40px" alt="System logo">
              @else
                <img src="{{asset('public/site/assets/backend/img/upload.jpg')}}" width="" height="40px" alt="Not Found">
              @endif
            @else
              <img src="{{asset('public/site/assets/backend/img/upload.jpg')}}" width="" height="40px" alt="Not Found">
            @endif
          </td>
        </tr>
        <tr>
          <td class="fontbold">System Favicon</td>
          <td>
            @if(@$configuration->favicon != Null )
              @php $image_path = public_path('uploads/logo/'.$configuration->favicon); @endphp
              @if(file_exists($image_path))
                <img src="{{asset('public/uploads/logo/'.$configuration->favicon)}}" width="" height="40px" alt="System logo">
              @else
                <img src="{{asset('public/site/assets/backend/img/upload.jpg')}}" width="" height="40px" alt="Not Found">
              @endif
            @else
              <img src="{{asset('public/site/assets/backend/img/upload.jpg')}}" width="" height="40px" alt="Not Found">
            @endif
          </td>
        </tr>
        <tr>
          <td class="fontbold">Login Page Background</td>
          <td>
            @if(@$configuration->login_background != Null )
              @php $image_path = public_path('uploads/logo/'.$configuration->login_background); @endphp
              @if(file_exists($image_path))
                <img src="{{asset('public/uploads/logo/'.$configuration->login_background)}}" width="" height="40px" alt="Login Background Image">
              @else
                <img src="https://thumbs.dreamstime.com/b/asian-food-background-various-cooking-ingredients-rustic-background-top-view-banner-concept-chinese-thai-66582124.jpg" width="" height="40px" alt="Login Background Image">
              @endif
            @else
              <img src="https://thumbs.dreamstime.com/b/asian-food-background-various-cooking-ingredients-rustic-background-top-view-banner-concept-chinese-thai-66582124.jpg" width="" height="40px" alt="Not Found">
            @endif
          </td>
        </tr>
        <tr>
          <td class="fontbold">Email Notification</td>
          <td class="text-nowrap">
            @if(@$configuration->email_notification == 1) 
              <span>Enabled</span>
            @elseif(@$configuration->email_notification == 0)
              <span>Disabled</span>
            @endif
          </td>
        </tr>

        <tr>
          <td class="fontbold">Currency Code<b style="color: red;">*</b></td>
          <td class="text-nowrap">
            {{@$configuration->Currency->currency_code}}
          </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold text-nowrap">Quotation Prefix<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
              {{@$configuration->quotation_prefix}}

          </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold text-nowrap">Draft Invoice Prefix<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
              {{@$configuration->draft_invoice_prefix}}

          </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold text-nowrap">Invoice Prefix<b style="color: red;">*</b></td>
          <td class="text-nowrap"> 
              {{@$configuration->invoice_prefix}}

          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">System Email<b style="color: red;">*</b></td>
          <td class="text-nowrap">
            {{@$configuration->system_email}}
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Purchasing Email<b style="color: red;">*</b></td>
          <td class="text-nowrap">
            {{@$configuration->purchasing_email != null ? @$configuration->purchasing_email : '--'}}
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Billing Email<b style="color: red;">*</b></td>
          <td class="text-nowrap">
            {{@$configuration->billing_email != null ? @$configuration->billing_email : '--'}}
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">System Color</td>
          
          <td class="text-nowrap">
            <input type="color" id="system_color" disabled="" name="system_color" value="{{@$configuration->system_color}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">System Button Text Color</td>
          
          <td class="text-nowrap">
            <input type="color" id="system_color" disabled="" name="system_color" value="{{@$configuration->bg_txt_color}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Button Hover Color</td>
          
          <td class="text-nowrap">
            <input type="color" id="system_color" disabled="" name="system_color" value="{{@$configuration->btn_hover_color}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Button Hover Text Color</td>
          
          <td class="text-nowrap">
            <input type="color" id="system_color" disabled="" name="system_color" value="{{@$configuration->btn_hover_txt_color}}">
          </td>
        </tr>

      </tbody>
    </table>
  </div>
  
</div>

<div class="col-lg-7 col-md-6">
  <div class="bg-white pt-3 pl-2 pr-2 pb-2">
    <table class="table entriestable table-bordered deployments-table text-center w-100">
      <thead>
        <tr>
          <th>Action</th>
          <th>Type</th>
          <th>URL</th>
          <th>Token</th>
          <th>Price</th>
          <th>Warehouse</th>
          <th>Created By</th>
          <th>Status</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<div class="col-lg-3 col-md-6 d-none">
  @if(Auth::user()->role_id == '10' || Auth::user()->role_id == 10)
  <div class="bg-white pt-3 pl-2 h-100">
    <span class="d-block text-danger mx-auto w-100 pl-4"><i>Maximum Number of Accounts For Each Role</i></span>
     <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font p-4" style="width: 100%;">
      <tbody>
        <tr>
          <td>1.</td>
          <td class="font-weight-bold">Admin</td>
          <td>
            <input type="number" name="maximum_admin_accounts" data-id="{{$configuration->id}}" class="fieldFocus" value="{{$configuration->maximum_admin_accounts}}" data-fieldvalue="{{$configuration->maximum_admin_accounts}}" placeholder="Max. Accounts e.g. 5">
          </td>
        </tr>

         <tr>
          <td>2.</td>
          <td class="font-weight-bold">Other Staff</td>
          <td>
            <input type="number" name="maximum_staff_accounts" data-id="{{$configuration->id}}" class="fieldFocus" value="{{$configuration->maximum_staff_accounts}}" data-fieldvalue="{{$configuration->maximum_staff_accounts}}" placeholder="Max. Accounts e.g. 5">
          </td>
        </tr>

      </tbody>
    </table>
  </div>
  @endif
</div>



</div>



<!--  Configurations Edit Modal Start Here -->

<div class="modal" id="editCustomerModal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center" id="editCustomerModalForm">
      </div>
    </div>
  </div>
</div>

<!-- Configurations Edit Modal End Here -->

<!--  Deployment Modal Start Here -->
<div class="modal" id="deployment-Modal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h2 id="deployment-Modal-title">Add New Deployment</h2>
        <form id="deployment-Form">
          @csrf
          <div class="row">
            <div class="form-group col-md-6">
              <label for="type" class="pull-left">Enter Type</label>
              <input type="text" name="type" class="form-control" id="type" placeholder="Enter Type" required>
            </div>
            <div class="form-group col-md-6">
              <label for="url" class="pull-left">Enter URL</label>
              <input type="text" name="url" class="form-control" id="url" placeholder="Enter URL" required>
            </div>
            <div class="form-group col-md-6">
              <label for="price" class="pull-left">Choose Price</label>
              <select class="form-control select2" name="price" id="price" required>
                  <option value="0">Choose Price</option>
                @foreach($customer_cats as $cat)
                  <option value="{{$cat->id}}">{{$cat->title}}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="warehouse" class="pull-left">Choose Warehouse</label>
              <select class="form-control select2" name="warehouse" id="warehouse">
                  <option value="0">Choose Warehouse</option>
                @foreach($warehouses as $warehouse)
                  <option value="{{$warehouse->id}}">{{$warehouse->warehouse_title}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <input type="hidden" name="deployment_id" id="deployment_id">
        </form>
        <button type="button" class="btn pull-right btn-save">Save</button>
        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" style="background: crimson !important">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Configurations Edit Modal End Here -->

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


</script>
@include('backend.configuration.deployment-table-js')
@stop

