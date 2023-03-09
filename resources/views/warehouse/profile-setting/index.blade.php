@extends('warehouse.layouts.layout')
@section('title','Profile Settings')


@section('content')


<div class="row mb-3">
  <div class="col-md-8 title-col">
    {{-- <h3 class="maintitle text-uppercase fontbold">Profile Settings</h3> --}}
  </div>
  
</div>


<div class="row mb-3 justify-content-center ">
      <div class="col-lg-9 col-md-11 col-12 signform-col">
       
        <div class="row">
          <div class="col-md-12">
            <div class="bg-white pr-4 pl-4 pt-4 pb-5">
          
                <div class="bulk-add-gemstone-col">
                  <div class="bulk-add-gemstone text-center w-75">
                    <h2 class="fontbold text-uppercase mb-3 mb-lg-5">Profile Settings</h2>

                   
                    <form id="profileSettingForm" enctype="multipart/form-data" method="POST">
                      {{csrf_field()}}
                     
                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Name <span class="font-weight-bold text-danger">*</span></label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="name" value="{{Auth::user()->name}}">
                      </div>
                    
                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Company <span class="font-weight-bold text-danger">*</span></label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="company" value="{{@$user_detail->company_name}}">
                      </div>

                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Address <span class="font-weight-bold text-danger">*</span></label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="address" value="{{@$user_detail->address}}">
                      </div>

                    
                      <div class="custom-file mb-5">
                      
                      <label class="d-block text-left">Country <span class="font-weight-bold text-danger">*</span></label>
                       <div id="errorHighLightCountry">   
                       <select  name="country"  class="form-control selectpicker" title="Choose Country" data-live-search="true" id="countryProfileSetting" data-select_type="country">
                          
                         @foreach($countries  as $result)
                          @php
                            if(@$user_detail->country_id == $result->id){
                              $country_selected="selected";
                            }
                            else{
                              $country_selected="";
                            }
                          @endphp
                          <option value="{{$result['id']}}" {{@$country_selected}}>{{$result['name']}}</option>
                         @endforeach
                         
                       </select>
                      </div>
                      </div>
                

                      <div class="custom-file mb-5" id="fill_states_div">
                    

                        <label class="d-block text-left">State <span class="font-weight-bold text-danger">*</span></label>
                        <div id="errorHighLightState">
                        <select id="stateProfileSetting" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" >
                          @foreach($user_states  as $result)
                           @php
                             if($user_detail->state_id == $result->id){
                               $state_selected="selected";
                             }
                             else{
                               $state_selected="";
                             }
                           @endphp
                           <option value="{{$result['id']}}" {{@$state_selected}}>{{$result['name']}}</option>
                          @endforeach
                        </select>
                        </div>
                     
                      </div>

                      <div class="custom-file mb-5">
                        <label class="d-block text-left">City<span class="font-weight-bold text-danger">*</span></label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="city" value="{{@$user_detail->city_name}}">
                      </div>

                     {{--  <div class="custom-file mb-5">
                        <label class="d-block text-left">Return Address</label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="return_address" value="{{@$user_detail->return_address}}">
                      </div> --}}


                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Zip Code<span class="font-weight-bold text-danger">*</span></label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="zip_code" value="{{@$user_detail->zip_code}}">
                      </div>

                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Phone Number<span class="font-weight-bold text-danger">*</span></label>
                        <input type="text" class="font-weight-bold form-control-lg form-control"  name="phone_number" value="{{@$user_detail->phone_no}}">
                      </div>

                      @if(@$user_detail->image != null)
                      <div class="custom-file mb-5">
                       <label class="d-block text-left">
                      <a href="{{url('public/uploads/warehouse/images/'.@$user_detail->image)}}"><img src="{{url('public/uploads/warehouse/images/'.@$user_detail->image)}}"  width="100" class="img-rounded" align="center"></a>
                      </label>
                      </div>                      
                      @endif

                      <br>
                      <div class="custom-file mb-5">
                        <label class="d-block text-left">Logo (  @if(@$user_detail->image != null)<span >If you want to upload a new logo, please choose file. This will delete the previous logo. </span>@endif<span style="color:blue;">1MB is maximum file size </span>)</label>
                        
                       <input type="file" class="form-control form-control-lg" name="image">
                      </div>
                     
                       
                       <input type="submit" class="btn m-5" id="save-btn" value="Submit">

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
$(document).ready(function(){

  $(document).keydown(function(event){
     if( (event.keyCode == 13)) {
       event.preventDefault();
       return false;
     }
   });

$(document).on('change',"#countryProfileSetting",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){

            var html_string='<div class="custom-file mb-5" id="fill_states_div">  <label class="d-block text-left">State <span class="font-weight-bold text-danger">*</span></label>';
            html_string+='<div id="errorHighLightState">';
            html_string+='  <select id="stateProfileSetting" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true">';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div></div>";
            $("#fill_states_div").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(){
            alert('Error');
        }

    });


});


$(document).on('submit', '#profileSettingForm', function(e){
    e.preventDefault();
  
  // if(e.keyCode == 13){
  //   alert('s');

  //   return false;
  //   alert('f');
  // }
  var error = false;
  var formData = new FormData($(this)[0]);

  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });

  if($("#countryProfileSetting").val() == ""){
    error =true;
    $("#errorHighLightCountry").css('border','1px solid red');
  }
  else{
    $("#errorHighLightCountry").css('border',''); 
  } 

  if($("#stateProfileSetting").val() == ""){
    error =true;
    $("#errorHighLightState").css('border','1px solid red');
  }
  else{
    $("#errorHighLightState").css('border',''); 
  }

  $('input[name="name"],input[name="company"],input[name="address"],input[name="city"],input[name="zip_code"],input[name="phone_number"]').each(function () {
       
      if ($(this).val() == "")
      {
          error =true;
          $(this).addClass('is-invalid');
      } 
      else
      {
          $(this).removeClass('is-invalid');
      }
  });

  if(error == false){
   $.ajax({
      url: "{{ route('warehouse-update-profile-setting') }}",
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
      success: function(data){
        $('.save-btn').val('add');
        $('.save-btn').attr('disabled', true);
        $('.save-btn').removeAttr('disabled');
        if(data.error == false){
          toastr.success('Success!', 'Profile updated successfully.' ,{"positionClass": "toast-bottom-right"});
          window.location.href="{{url('/warehouse')}}";
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
                 
              //error handling
            });

           
            
        }
   });
  }
});

});
</script>
@endsection