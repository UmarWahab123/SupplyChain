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
p
{
font-size: small;
font-style: italic;
color: red;
}
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
}
@php
use Carbon\Carbon;
@endphp
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
          <li class="breadcrumb-item"><a href="{{route('all-users-list')}}">All Users</a></li>
          <li class="breadcrumb-item active">User Detail</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}



<div class="col-lg-12 col-md-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1 col-md-1">
    <div class="logo-container">
       <!-- <?php $product_id = $id; ?> -->
        @if(!$userdetail)
        <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>
        @elseif(@Auth::user()->user_details->user_id==$id && @Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/admin/images/' . @Auth::user()->user_details->image))

        <img src="{{asset('public/uploads/admin/images/'.@Auth::user()->user_details->image)}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>

        <form action="{{ route('remove-profile-image') }}" method="POST">
          @csrf
          <input name="id" type="hidden" value="{{@Auth::user()->id}}">
          <input value="Remove Image" type="submit" class="add-btn mt-2" title="Remove Image"></a>
        </form>

        @elseif($userdetail->user_id==$id && $userdetail->image != null && file_exists( public_path() . '/uploads/admin/images/' . $userdetail->image))

        <img src="{{asset('public/uploads/admin/images/'.$userdetail->image)}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>


        @elseif($userdetail->user_id==$id && $userdetail->image != null && file_exists( public_path() . '/uploads/accounting/images/' . $userdetail->image))

        <img src="{{asset('public/uploads/accounting/images/'.$userdetail->image)}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>
        @elseif($userdetail->user_id==$id && $userdetail->image != null && file_exists( public_path() . '/uploads/sales/images/' . $userdetail->image))

        <img src="{{asset('public/uploads/sales/images/'.$userdetail->image)}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>
        @elseif($userdetail->user_id==$id && $userdetail->image != null && file_exists( public_path() . '/uploads/warehouse/images/' . $userdetail->image))

        <img src="{{asset('public/uploads/warehouse/images/'.$userdetail->image)}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>

        @elseif($userdetail->user_id==$id && $userdetail->image != null && file_exists( public_path() . '/uploads/purchase/' . $userdetail->image))

        <img src="{{asset('public/uploads/purchase/'.$userdetail->image)}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" alt="user image" class="image" style="cursor: pointer; height:72px; width:73px;"><i class="fa fa-camera h-camera" style="display: none;"></i>
        @endif



      <div style="display: none;">
        <form enctype="multipart/form-data" method="post" id="upload_user_form">
      <input type="hidden" id="custId" name="userid" value="{{$id}}">
      <input type="file" name="profileimg" onchange="validateImage()" id="profile_user" style="">
       <button id="submit_button" type="submit">Upload</button>
      </form>
      </div>
      <!-- <img src="{{asset('public/img/profileImg.jpg')}}" alt="Avatar" class="image"> -->
    </div>
  </div>

  <div class="col-lg-4 col-md-4 p-0">
    <h4 class="mb-0 mt-lg-4">User Detail</h4>
  </div>
  <div class="col-lg-5 col-md-5 offset-1">
    <h4 class="mb-0 mt-lg-4">Change User Password</h4>

    <!-- <div class="mb-0">
      @if($user_role->role_id == 6)
        @if($defaultUser->count())
          @if($user_role->is_default)
            <a href="javascript:void(0);" data-id="{{@$user_role->id}}"  class="btn button-st  ResetdefaultIcon">Reset default Warehouse</a>
          @endif
        @else
            <a href="javascript:void(0);" data-id="{{@$user_role->id}}"  class="btn button-st MakedefaultIcon">Make default Warehouse</a>
        @endif
      @endif
    </div> -->
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
          <td class="fontbold">Name <b style="color: red;">*</b></td>
          <td>
            <span class="m-l-15 inputDoubleClick" id="name" data-fieldvalue="{{@$user_role->name}}">
            {{ (@$user_role->name!=null) ? @$user_role->name : '--' }}
            </span>

            <input type="text" name="name" class="fieldFocus d-none" value="{{ (@$user_role->name!=null) ? $user_role->name :''}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">UserName <b style="color: red;">*</b></td>
          <td>
            <span class="m-l-15 inputDoubleClick" id="user_name" data-fieldvalue="{{@$user_role->user_name}}">
            {{ (@$user_role->user_name!=null) ? @$user_role->user_name : '--' }}
            </span>

            <input type="text" name="user_name" class="fieldFocus d-none user_name" value="{{ (@$user_role->user_name!=null) ? $user_role->user_name :''}}">
            <div id="errors"></div>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Email </td>
          <td class="text-nowrap">
            <span class="m-l-15 inputDoubleClick" id="email" data-fieldvalue="{{@$user_role->email}}">
            {{ (@$user_role->email!=null) ? @$user_role->email : '--' }}
            </span>

            <input type="email" name="email" class="fieldFocus d-none email" value="{{ (@$user_role->email!=null) ? $user_role->email :''}}">
            <div id="email_errors"></div>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Phone no</td>
          <td class="text-nowrap">
            <span class="m-l-15 inputDoubleClick" id="phone_number" data-fieldvalue="{{@$user_role->phone_number}}">
            {{ (@$user_role->phone_number!=null) ? @$user_role->phone_number : '--' }}
            </span>

            <input type="phone_number" name="phone_number" class="fieldFocus d-none phone_number" value="{{ (@$user_role->phone_number!=null) ? $user_role->phone_number :''}}">
            <div id="phone_number_errors"></div>
          </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies["company_name"]}}</td>
          <td class="text-nowrap">
            <span class="m-l-15 inputDoubleClick" id="company_name" data-fieldvalue="{{@$user_role->getCompany->company_name}}">
            {{ (@$user_role->getCompany->company_name!=null) ? @$user_role->getCompany->company_name : '--' }}
            </span>

            <!-- <input type="text" name="company_name" class="fieldFocus d-none company_name" value="{{ (@$user_role->getCompany->company_name!=null) ? $user_role->getCompany->company_name :''}}"> -->

            <select name="company_id" id="company_id" class="select-common form-control company_id d-none">
              <option disabled value="">Select {{$global_terminologies["company_name"]}}</option>
              @foreach($user_companies as $usr_company)
                @php
                  if(@$user_role->getCompany->id == $usr_company->id)
                  {
                    $var_select = "Selected";
                  }
                  else
                  {
                    $var_select = "";

                  }
                @endphp
                <option  value="{{$usr_company->id}}" {{$var_select}} >{{$usr_company->company_name}}</option>
              @endforeach
            </select>
            <div id="email_errors"></div>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Role<b style="color: red;">*</b></td>
          <td class="text-nowrap">
              {{@$user_role->roles->name}}
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Status<b style="color: red;">*</b></td>

          <td class="text-nowrap">
              @if(@$user_role->status == 1)
                Active
              @elseif(@$user_role->status == 2)
                Suspended
              @else
                InActive
              @endif
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Created At</td>

          <td class="text-nowrap">
              {{$user_role->created_at != null ? Carbon::parse($user_role->created_at)->format('d/m/Y') : "--"}}
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Updated At</td>
          <td class="text-nowrap">
            {{$user_role->updated_at != null ? Carbon::parse($user_role->updated_at)->format('d/m/Y') : "--"}}
          </td>
        </tr>


      </tbody>
    </table>
  </div>

</div>

<div class="col-lg-5 col-md-6">
  <div class="bg-white p-4 h-100">
  <form id="change-password-form">
    {{csrf_field()}}
    <input type="hidden" name="user_id" value="{{@$user_role->id}}">
   <span><i style="color: red;"><b>Note: </b>Password must contain (UpperCase, LowerCase, Number/SpecialChar and min 8 Chars)</i></span><br><br>
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-default"><i class="fa fa-lock"></i></span>
      </div>
      <input type="password" class="form-control" name="new_password" id="new_password" aria-label="Default" aria-describedby="inputGroup-sizing-default" placeholder="New Password" pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text" id="inputGroup-sizing-default"><i class="fa fa-lock"></i></span>
      </div>
      <input type="password" class="form-control" name="confirm_new_password" id="confirm_new_password" aria-label="Default" aria-describedby="inputGroup-sizing-default" placeholder="Confirm New Password">
    </div>
    <span class="text-left d-none" id="not_matched_error" role="alert" style="color:red;margin-top:-15px;"><strong>Password and confirm password not matched.</strong></span><br>

    <input type="submit" class="save-btn btn add-btn btn-color" id="save-btn" value="Change Password">
  </form>
  </div>
</div>

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

<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
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


    $('.image').on('click',function(){
    $("#profile_user").click();
    });

      $('#profile_user').on('change',function(){
         var test = validateImage();  // this will grab you the return value from validateImage();
          if(test){
              $('#submit_button').trigger('click');
          }else{
              return false;
          }

    });




    function validateImage() {
  console.log("validateImage called");
  var formData = new FormData();

  var file = document.getElementById("profile_user").files[0];

  formData.append("Filedata", file);
  var t = file.type.split('/').pop().toLowerCase();
   if (t != "jpeg" && t != "jpg" && t != "png") {
    toastr.error('Uploaded file image must be jpeg, jpg or png.','Error!' , {
                                "positionClass": "toast-bottom-right"
                            });
    document.getElementById("profile_user").value = '';
    return false;
  }
  if (file.size > 2048000) {
    toastr.error('Image size must be less than 2MB.','Error!' , {
                                "positionClass": "toast-bottom-right"
                            });
    document.getElementById("profile_user").value = '';
    return false;
  }
  return true;
}

$("#upload_user_form").on('submit',function(e){
      e.preventDefault();
       $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ route('update-profile-img-user') }}",
        method: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('#loader_modal').modal('show');
        },
        success: function(result){

           if (result.error) {
                            toastr.error((result.error),"Must be Upload image and maximum image size 2MB", {
                                "positionClass": "toast-bottom-right"
                            });
                            $('#loader_modal').modal('hide');
                        }

                         if (result.success === 1) {

                          toastr.success('Success!', 'Profile image updated successfully', {
                                "positionClass": "toast-bottom-right"
                            });
                            $('#upload_form')[0].reset();
                            setTimeout(function() {
                                $('#loader_modal').modal('hide');
                                window.location.reload();
                            }, 2000);
                        }
        },

        });
      });

  $(function(e){

  $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

  $(document).on('click', '.ResetdefaultIcon', function(){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to Reset Default of  this Warehouse?",
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
                data:{id:id,type:'warehouse'},
                url:"{{ route('reset-default') }}",
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
                        window.location.reload();
                      }, 2000);
                    }
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
    });

  $(document).on('click', '.MakedefaultIcon', function(){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to Make Default this Warehouse?",
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
                data:{id:id,type:'warehouse'},
                url:"{{ route('set-default') }}",
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
                        window.location.reload();
                      }, 2000);
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

  //change password
  $(document).on('submit', '#change-password-form', function(e){
       e.preventDefault();
       $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
           }
       });

       var new_password= $("#new_password").val();
       var confirm_new_password= $("#confirm_new_password").val();

       if(new_password != confirm_new_password)
       {
        $("#confirm_new_password").addClass('is-invalid');
        $("#not_matched_error").removeClass('d-none');
        return false;
       }
       else
       {
        $("#confirm_new_password").removeClass('is-invalid');
        $("#not_matched_error").addClass('d-none');
       }

        $.ajax({
           url: "{{ route('change-user-password-by-admin') }}",
           method: 'post',
           data: $('#change-password-form').serialize(),
           beforeSend: function(){
             $('#save-btn').val('Please wait...');
             $('#save-btn').addClass('disabled');
             $('#save-btn').attr('disabled', true);
           },
           success: function(result){
             $('#save-btn').val('Change Password');
             $('#save-btn').attr('disabled', true);
             $('#save-btn').removeAttr('disabled');
             if(result.success === true){

              $('#change-password-form').trigger('reset');
              toastr.success('Success!', 'Password changed successfully',{"positionClass": "toast-bottom-right"});
              // window.location.href="{{url('sales/')}}";
             }


           },
           error: function (request, status, error) {
                 $('#save-btn').val('add');
                 $('#save-btn').removeClass('disabled');
                 $('#save-btn').removeAttr('disabled');
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

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $x = $(this);
      $(this).addClass('d-none');
      $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');

    setTimeout(function(){

      $('.spinner').remove();
      $x.next().removeClass('d-none');
      $x.next().addClass('active');
      $x.next().focus();
      var num = $x.next().val();
      $x.next().focus().val('').val(num);


     }, 300);
  });

  $(document).on('keypress keyup focusout', '.fieldFocus', function(e){

  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

    if($(this).attr('name') == 'email')
    {
      if($(this).val().length < 1)
      {
        return false;
      }
      else
      {
        var new_value = $(this).val();
        var testEmail = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;
        if (testEmail.test(new_value))
        {
          var fieldvalue = $(this).prev().data('fieldvalue');
          var new_value = $(this).val();
          if(fieldvalue == new_value)
          {
            var thisPointer = $(this);
            thisPointer.addClass('d-none');
            thisPointer.removeClass('active');
            thisPointer.prev().removeClass('d-none');
            $(this).prev().html(fieldvalue);
          }
          else
          {
            var thisPointer = $(this);
            thisPointer.addClass('d-none');
            thisPointer.removeClass('active');
            thisPointer.prev().removeClass('d-none');
            if(new_value != '')
            {
              $(this).prev().removeData('fieldvalue');
              $(this).prev().data('fieldvalue', new_value);
              $(this).attr('value', new_value);
              $(this).prev().html(new_value);
            }
            saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val());
          }
        }
        else
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Please Enter Valid Email, Try Again!!!</b>'});
          $(this).focus();
          return false;
        }
      }
    }
    if($(this).val().length < 1)
    {
      return false;
    }
    else if(fieldvalue == new_value)
    {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }
    else
    {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
        saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    }

    }
  });

  $(document).on('change focusout keyup', 'select.select-common', function(e){
    if($("option:selected", this).val() != '' )
    {
    var fieldvalue = $(this).prev().data('fieldvalue');
    // var new_value = $(this).val();
    var new_value = $("option:selected", this).html();

    var thisPointer = $(this);
        thisPointer.addClass('d-none');
    if(fieldvalue == new_value)
    {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
    }
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
    saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val());
  }

  });

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc

      if($('.inputDoubleClick').hasClass('d-none')){
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

  function saveProdData(thisPointer,field_name,field_value){
    console.log(thisPointer+' '+' '+field_name+' '+field_value);
    var user_id = "{{$id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('admin/save-user-data-user-detail-page') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,user_id:user_id},
      data: 'user_id='+user_id+'&'+field_name+'='+encodeURIComponent(field_value),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(data)
      {
        $("#loader_modal").modal('hide');

        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }

        if(field_name == "user_name" && data.success == false)
        {
          $('#errors').html('<p style="color:red;"><b>'+data.error+'</b></p>');
          setTimeout(function() {
            $('#errors').html('');
          }, 7000);
          $('#user_name').data('fieldvalue', data.old_name);
          $('#user_name').html(data.old_name);
          $('input[name=user_name]').val(data.old_name);
        }

        if(field_name == "email" && data.success == false)
        {
          $('#email_errors').html('<p style="color:red;"><b>'+data.error+'</b></p>');
          setTimeout(function() {
            $('#email_errors').html('');
          }, 7000);
          $('#email').data('fieldvalue', data.old_email);
          $('#email').html(data.old_email);
          $('input[name=email]').val(data.old_email);
        }
      }

    });
  }
</script>
@stop

