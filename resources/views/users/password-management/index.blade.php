@extends('users.layouts.layout')
@section('title','Change Password')


@section('content')


<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Change Password</h3>
  </div>
  
</div>


<div class="row mb-3 justify-content-center ">
      <div class="col-lg-9 col-md-11 col-12 signform-col">
       
        <div class="row add-gemstone">
          <div class="col-md-12">
            <div class="bg-white pr-4 pl-4 pt-4 pb-5">
          
                <div class="bulk-add-gemstone-col">
                  <div class="bulk-add-gemstone text-center w-75">
                    <h2 class="fontbold text-uppercase mb-3 mb-lg-5">Change Password</h2>

                   
                    <form id="change-password-form">
                      {{csrf_field()}}
                      
                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Old Password <strong>*</strong></label>
                        <input type="password" class="font-weight-bold form-control-lg form-control" id="old_password" name="old_password">
                        <span class="text-left d-none" id="old_password_error" role="alert" style="color:red;margin-top:-15px;"><strong>Old password not matched.</strong></span>
                      </div>

                      
                      <div class="custom-file mb-5">
                        <label class="d-block text-left">New Password <strong>*</strong></label>
                        <input type="password" class="font-weight-bold form-control-lg form-control" id="new_password" name="new_password">
                      </div>
                      
                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Confirm New Password <strong>*</strong></label>
                        <input type="password" class="font-weight-bold form-control-lg form-control" id="confirm_new_password" name="confirm_new_password">
                      </div>
                      <p class="text-left d-none" id="not_matched_error" style="color:red;margin-top:0px;">Password and confirm password not matched.</p>
                      <p class="text-left d-none" id="length_error" style="color:red;margin-top:0px;">Password must be atleast 6 characters long.</p>
                       
                       <input type="submit" class="btn m-1" id="save-btn" value="Submit">

                    </form>

                   
                  </div>
                </div>
            </div>
          </div>  
        </div>
    
   
     </div> 
</div>

{{-- Content End Here  --}}

@endsection

@section('javascript')

<script type="text/javascript">

  $("#old_password").on("focusout",function(){

    var old_password = $(this).val();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ url('check-old-password') }}",
        method: 'post',
        dataType:'json',
        data:{old_password:old_password},
        
        success: function(result){
        
          if(result.error == true)
          {
            // alert("true");

            $("#old_password_error").removeClass('d-none');
            $("#save-btn").attr("disabled",true);
          }
          else
          {
            // alert("false");

            $("#old_password_error").addClass('d-none');
            $("#save-btn").removeAttr("disabled",true);

          }
          
          
        },
        error: function (request, status, error) {
        }
      });


  });



  $(document).on('submit', '#change-password-form', function(e){
       e.preventDefault();
       
       $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
           }
       });

       var new_password= $("#new_password").val();
       var confirm_new_password= $("#confirm_new_password").val();
       var new_password_length = new_password.length;
       var confirm_new_password_length = new_password.length;

       if(new_password != confirm_new_password)
       {
        $("#confirm_new_password").addClass('is-invalid');
        $("#not_matched_error").removeClass('d-none');
        return false;
       }
       if(new_password_length < 6){
        $("#confirm_new_password").addClass('is-invalid');
        $("#length_error").removeClass('d-none');
        return false;
       }
       else
       {
        $("#confirm_new_password").removeClass('is-invalid');
        $("#not_matched_error").addClass('d-none');
       }


        $.ajax({
           url: "{{ url('change-password-process') }}",
           method: 'post',
           data: $('#change-password-form').serialize(),
           beforeSend: function(){
             $('#save-btn').val('Please wait...');
             $('#save-btn').addClass('disabled');
             $('#save-btn').attr('disabled', true);
           },
           success: function(result){
             $('#save-btn').val('add');
             $('#save-btn').attr('disabled', true);
             $('#save-btn').removeAttr('disabled');
             if(result.success === true){
              
              $('#change-password-form').trigger('reset');
              toastr.success('Success!', 'Password changed successfully',{"positionClass": "toast-bottom-right"});
              window.location.href="{{url('/')}}";              
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
</script>
@endsection