@extends('users.layouts.layout')
@section('title','Dashboard')


@section('content')


<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Complete your profile</h3>
  </div>
  
</div>

<div class="row mb-3 justify-content-center ">
      <div class="col-lg-9 col-md-11 col-12 signform-col">
        <!-- progressbar -->
        <ul id="progressbar" class="d-flex justify-content-center mb-3">
          <!--<li class="active">Basic Info</li>
          <li>Show/Hide Column Preference</li>
          <li>Change Password</li>
          <li>Upload Logo</li> -->
        </ul>
        <!-- // progressbar -->
      <h3 align="center">Baisc Information</h3>
      <!-- multistep form -->
    <form id="complete-profile-form"  class="sign-form" enctype="multipart/form-data">
    
      <!-- fieldsets -->
      <fieldset id="fieldset-1" class="bg-white customborder custompadding">
      <div class="row">
              <div class="col-sm-6 col-6 form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="{{Auth::user()->name}}" autocomplete="off">
              </div>

              <div class="col-sm-6 col-6 form-group">
                <label>Company</label>
                <input type="text" name="company" class="form-control" autocomplete="off">
              </div>
             
              <div class="col-12 form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="">
              </div>
             
              
              <div class="col-sm-6 col-12 form-group" id="country_div">
                <label>Country</label>
                <select id="country" name="country"  class="form-control selectpicker" title="Choose Country" data-live-search="true" data-select_type="country">
                  
                  @foreach($countries  as $result)
                   <option value="{{$result['id']}}">{{$result['name']}}</option>
                  @endforeach
                  
                </select>
              </div>
              
              
             
              <div class="col-sm-6 col-6 form-group"  id="fill_states_div">
                <span id="state_div">
                <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>
                 <select id="state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state">
                  
                 </select>
                </span>
              </div>
             
          
              <div class="col-sm-12 col-12 form-group">
                <label>City</label>
                <input type="text" name="city" class="form-control" value="">
              </div>

              <div class="col-sm-6 col-6 form-group">
                <label>Zip Code</label>
                <input type="text" name="zip_code" class="form-control" value="">
              </div>

              <div class="col-sm-6 col-6 form-group">
                <label>Phone Number</label>
                <input type="text" name="phone_number" id="phone" class="form-control" value="">
              </div>
            
            </div>
          <div class="form-group form-btn-group">
            <button type="submit" class="btn pull-right btn-bg save-btn">Submit</button>
          </div>
      </fieldset>
              
      </form>
     </div> 
</div>

{{-- Content End Here  --}}

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
//    $('#phone').mask('(000) 000-0000');

// //  Handling Form Wizard Starts 

//   $('fieldset:first-child').fadeIn('slow');
//   $('#progressbar li:first-child').addClass('active');
  

//   $('.sign-form input[type="text"],input[type="email"],input[type="tel"],select').on('focus', function () {
//       $(this).removeClass('input-error');
//       $("#country_div .dropdown-toggle").removeClass('input-error');
//       $("#state_div .dropdown-toggle").removeClass('input-error');

//   });

//   // next step
//   $('.sign-form .btn-next').on('click', function () {
//       var parent_fieldset = $(this).parents('fieldset');
//       var next_step = true;
//       parent_fieldset.find('input[name="name"],input[name="company"],input[name="address"],#country,#state,input[name="city"],input[name="zip_code"],input[name="phone_number"],input[name="old_password"],input[name="new_password"],input[name="confirm_new_password"]').each(function () {
           
//           //country select box 
//           if($(this).data("select_type") == "country"){
//             if($(this).val() == ""){
                
//                 $("#country_div .bs-placeholder").addClass('input-error');
//             }
//             else{
//                 $("#country_div .dropdown-toggle").removeClass('input-error');
//             }
//           }
         
//           //state select box 
//           if($(this).data("select_type") == "state"){
//             if($(this).val() == ""){
//                $("#state_div .bs-placeholder").addClass('input-error');
//             }
//             else{
//                $("#state_div .dropdown-toggle").removeClass('input-error');
//             }
//           }

         
        

//           if ($(this).val() == "") {
//               $(this).addClass('input-error');
//               next_step = false;
//           } else {
//               $(this).removeClass('input-error');
//           }
//       });

      
//       if (next_step) {
//           parent_fieldset.fadeOut(400, function () {
//           $(this).next().fadeIn();
//           $('#progressbar li.active').next().addClass('active');
//       });

//       }

//   });

//   // previous step
//   $('.sign-form .btn-previous').on('click', function () {
//       $(this).parents('fieldset').fadeOut(400, function () {
//           $(this).prev().fadeIn();
//           $('#progressbar li.active:last').removeClass('active');
//       });
  
//   });
// //  Handling Form Wizard Ended 



  
// $(document).on('keyup', '.form-control', function(){
//   $(this).removeClass('is-invalid');
//   $(this).next().remove();
// });

$(document).on('submit', '#complete-profile-form', function(e){
  e.preventDefault();

  var formData = new FormData($(this)[0]);
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ url('complete-profile') }}",
      method: 'post',
      data:formData,
      cache: false,
      contentType: false,
      processData: false,
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
           
          //redirecting towards vendor dashboard with session successmsg 
           @php
            Session()->put('successmsg', 'Profile completed successfully!');
           @endphp 
           window.location.href="{{url('/')}}";
          
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


// $("#old_password").on("focusout",function(){

//   var old_password = $(this).val();
//   $.ajaxSetup({
//       headers: {
//           'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
//       }
//   });
//    $.ajax({
//       url: "{{ url('check-old-password') }}",
//       method: 'post',
//       dataType:'json',
//       data:{old_password:old_password},
      
//       success: function(result){
      
//         if(result.error == true)
//         {
//           $("#old_password_error").removeClass('d-none');
//         }
//         else
//         {
//           $("#old_password_error").addClass('d-none');
//         }
        
        
//       },
//       error: function (request, status, error) {
//       }
//     });

// });

});
</script>

<script type="text/javascript">
  // getting state with respect to selected country
  $(document).on('change',"#country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){

            var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
            html_string+='  <select id="state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state">';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div>";
            // $("#state_div").remove();
            // store_state.after($("<div></div>").text(html_string));
            $("#fill_states_div").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(){
            alert('Error');
        }

    });
});
</script>
@endsection