@extends('backend.layouts.layout')

@section('title','Company Detail | Supply Chain')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
.dropdowncls button{
  background-color: #eee !important;
  border-color: #eee !important;
  color: black !important;
}
.imgstylings{
  width: 100%;
  min-height: 500px;
}
.h-camera{
  color: #b3afaf !important;
  font-size: 35px !important;
  top: 45px !important;
}
.select2-search__field{
  width: 10.75em !important;
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
          <li class="breadcrumb-item"><a href="{{route('show-company')}}">Campanies</a></li>
          <li class="breadcrumb-item active">Company Detail</li>
      </ol>
  </div>
</div>

<!-- cust-cat = customer-category -->
{{-- Content Start from here --}}
  <div class="row align-items-center">
  </div>

  <div class="row mb-3">

  <div class="col-lg-3 col-md-6">
    <h4 class="headings-color d-md-block">Comapny Logo</h4>
    <div class="bg-white pt-3 pl-2 h-100">

      <a class="my_logo_click align-items-center d-flex" href="javascript:void(0)" title="Click Here To Update Logo">
      @if($addCompany->logo != null && file_exists( public_path() . '/uploads/logo/' . $addCompany->logo))
        <img src="{{asset('public/uploads/logo/'.$addCompany->logo)}}" alt="Company Logo" style="margin: 150px; max-width: 150px; max-height: 150px; min-height: 150px; min-width: 150px;">
        <i class="fa fa-camera h-camera"></i>
      @else
        <img src="{{asset('public/uploads/logo-here.png')}}" alt="Company Logo" style="margin: 150px; max-width: 150px; max-height: 150px; min-height: 150px; min-width: 150px;">
        <i class="fa fa-camera h-camera"></i>
      @endif
      </a>

      <div style="display: none;">
        <form enctype="multipart/form-data" method="post" id="logo_upload_form">
          <input type="hidden" name="comapny_id" id="comapny_id" value="{{$addCompany->id}}">
          <input type="file" name="company_logo" id="profile_logo" accept="image/x-png,image/gif,image/jpeg">
          <button id="logoUpdate" type="submit">Upload</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <h4 class="headings-color d-md-block">Comapny Information</h4>
    <div class="bg-white pt-3 pl-2 h-100">
      <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
        <tbody>

          <tr>
            <td class="fontbold">{{$global_terminologies['company_name']}}<b style="color: red;">*</b></td>
            <td>
              <span class="m-l-15 inputDoubleClick" id="company_name"  data-fieldvalue="{{$addCompany->company_name}}">
                {{($addCompany->company_name != null) ? $addCompany->company_name:'--'}}
              </span>
              <input type="text" name="company_name" class="d-none fieldFocus" id="company_name" value="{{($addCompany->company_name!=null)?$addCompany->company_name:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">{{(array_key_exists('thai_billing_name', $global_terminologies)) ? $global_terminologies['thai_billing_name'] : 'Billing Name (THAI)'}}<b style="color: red;">*</b></td>
            <td>
              <span class="m-l-15 inputDoubleClick" id="thai_billing_name"  data-fieldvalue="{{$addCompany->thai_billing_name}}">
                {{($addCompany->thai_billing_name != null) ? $addCompany->thai_billing_name:'--'}}
              </span>
              <input type="text" name="thai_billing_name" class="d-none fieldFocus" id="thai_billing_name" value="{{($addCompany->thai_billing_name!=null)?$addCompany->thai_billing_name:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Email</td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="billing_email"  data-fieldvalue="{{@$addCompany->billing_email}}">
              {{(@$addCompany->billing_email!=null)?@$addCompany->billing_email:'--'}}
            </span>

            <input type="text" name="billing_email" class="d-none fieldFocus" id="billing_email" value="{{($addCompany->billing_email!=null)?$addCompany->billing_email:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Tax ID<b style="color: red;">*</b></td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="tax_id"  data-fieldvalue="{{@$addCompany->tax_id}}">
              {{(@$addCompany->tax_id!=null)?@$addCompany->tax_id:'--'}}
            </span>

            <input type="text" name="tax_id" class="d-none fieldFocus" id="tax_id" value="{{($addCompany->tax_id!=null)?$addCompany->tax_id:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Fax</td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="billing_fax"  data-fieldvalue="{{@$addCompany->billing_fax}}">
              {{(@$addCompany->billing_fax!=null)?@$addCompany->billing_fax:'--'}}
            </span>

            <input type="text" name="billing_fax" class="d-none fieldFocus" id="billing_fax" value="{{($addCompany->billing_fax!=null)?$addCompany->billing_fax:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Address</td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="billing_address"  data-fieldvalue="{{@$addCompany->billing_address}}">
              {{(@$addCompany->billing_address!=null)?@$addCompany->billing_address:'--'}}
            </span>

            <input type="text" name="billing_address" class="d-none fieldFocus" id="billing_address" value="{{($addCompany->billing_address!=null)?$addCompany->billing_address:''}}" autocomplete="nope">
            </td>
          </tr>

           <tr>
            <td class="fontbold">Address (THAI)</td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="thai_billing_address"  data-fieldvalue="{{@$addCompany->thai_billing_address}}">
              {{(@$addCompany->thai_billing_address!=null)?@$addCompany->thai_billing_address:'--'}}
            </span>

            <input type="text" name="thai_billing_address" class="d-none fieldFocus" id="thai_billing_address" value="{{($addCompany->thai_billing_address!=null)?$addCompany->thai_billing_address:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Zip</td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="billing_zip"  data-fieldvalue="{{@$addCompany->billing_zip}}">
              {{(@$addCompany->billing_zip!=null)?@$addCompany->billing_zip:'--'}}
            </span>

            <input type="text" name="billing_zip" class="d-none fieldFocus" id="billing_zip" value="{{($addCompany->billing_zip!=null)?$addCompany->billing_zip:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold text-nowrap">Country<b style="color: red;">*</b></td>
            
            <td class="text-nowrap">
            <span class="m-l-15 inputDoubleClick" id="billing_country" data-fieldvalue="{{@$addCompany->billing_country}}"> 
              {{(@$addCompany->billing_country != null)?@$addCompany->getcountry->name:'Select Country'}}
            </span>

            <select name="billing_country" class="selectFocus form-control d-none">
              <option value="" disabled="" selected="">Choose Country</option>
              @foreach($countries as $type)
              <option value="{{$type->id}}" {{ ($addCompany->billing_country == $type->id ? "selected" : "") }} >{{$type->name}}</option>
              @endforeach
            </select>
            </td>
          </tr>

          <tr>
            <td class="fontbold">State<b style="color: red;">*</b></td>
            <td>
              <span class="m-l-15 inputDoubleClick" id="billing_state"  data-fieldvalue="{{@$addCompany->billing_state}}">
                {{(@$addCompany->billing_state != null)?@$addCompany->getstate->name:'Select State'}}
              </span>
              <div class="d-none state-div">
                <select name="billing_state" class="selectFocus form-control select-two country-states" id="state-select">
                  <option>Choose State</option>
                  @if($states->count() > 0)
                    @if($addCompany->billing_state != null)
                      @foreach($states as $state)
                      <option {{ ($state->id == @$addCompany->billing_state ? 'selected' : '' ) }} value="{{ $state->id }}">{{ $state->name }}</option>
                      @endforeach
                    @else
                      @foreach($states as $state)
                      <option value="{{ $state->id }}">{{ $state->name }}</option>
                      @endforeach
                    @endif
                  @endif
                </select>
              </div>
            </td>
          </tr>

          <tr>
            <td class="fontbold">City<b style="color: red;">*</b></td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="billing_city"  data-fieldvalue="{{@$addCompany->billing_city}}">
              {{(@$addCompany->billing_city!=null)?@$addCompany->billing_city:'--'}}
            </span>

            <input type="text" name="billing_city" class="d-none fieldFocus" id="billing_city" value="{{($addCompany->billing_city!=null)?$addCompany->billing_city:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Phone</td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="billing_phone"  data-fieldvalue="{{@$addCompany->billing_phone}}">
              {{(@$addCompany->billing_phone!=null)?@$addCompany->billing_phone:'--'}}
            </span>

            <input type="text" name="billing_phone" class="d-none fieldFocus" id="billing_phone" value="{{($addCompany->billing_phone!=null)?$addCompany->billing_phone:''}}" autocomplete="nope">
            </td>
          </tr>

          <tr>
            <td class="fontbold">Prefix<b style="color: red;">*</b></td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="prefix"  data-fieldvalue="{{@$addCompany->prefix}}">
              {{(@$addCompany->prefix!=null)?@$addCompany->prefix:'--'}}
            </span>

            <input type="text" name="prefix" class="d-none fieldFocus" id="prefix" value="{{($addCompany->prefix!=null)?$addCompany->prefix:''}}" autocomplete="nope">
            </td>
          </tr>

         </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-6 col-md-6">
    <h4 class="headings-color d-md-block">Bank Information</h4>
    <div class="bg-white pt-3 pl-2 h-100">
      <form id="company_banks" class="company-banks">
      <input type="hidden" name="comp_id" id="comp_id" value="{{$addCompany->id}}">
      <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
        <tbody>
          
          <tr>
            <p style="color: red;">Note*. Must select one bank for each Group</p>  
          </tr>

          @if($customer_categories->count() > 0)
          @php $i = 0; @endphp
          @foreach($customer_categories as $cat)
          <tr class="parent-tr">
            <td class="text-nowrap">
              <input type="hidden" name="category_id[]" id="category_id" value="{{$cat->id}}">
              <h5 value="{{$cat->id}}">{{$cat->title}}</h5>
            </td>
            
            <td class="text-nowrap">
              @if($banks->count() > 0)
              <select name="banks[{{$i}}][]" id="banks_{{$cat->id}}" class="form-control banks font-weight-bold form-control-lg form-control js-states state-tags" multiple="multiple" required="">
              
              <option></option>
              @foreach($banks as $bank)
                @php
                  $check = $cat->categoryBanks->where('company_id',$addCompany->id)->where('bank_id',$bank->id)->first();
                @endphp
                <option value="{{$bank->id}}" {{ ($check != null ? 'selected' : '' )}} >{{$bank->title}}</option>
              @endforeach
              
              </select>
              @else
                <p>Please Add <a href="{{route('banks-list')}}" title="Click Here" style="color: red;"><b>Banks</b></a> First</p>
              @endif
            </td>
          </tr>
          @php $i++; @endphp
          @endforeach
          @endif
          
        </tbody>
      </table>
      @if($banks->count() > 0)
        <input type="submit" class="recived-button m-3" value="Save" style="width: 15%; float: right; font-size: 15px;">
      @endif
      </form>
    </div>
  </div>

  </div>

</div>
<!--  Content End Here -->

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

  $(".state-tags").select2();
  $('.state-tags').select2({
    placeholder: "Select Banks Here",
    allowClear: true
  });

  $('.my_logo_click').on('click',function(){
    $("#profile_logo").click();
  });

  // $('.add-more').on('click',function(){
  //   var html_string = '';
  // });
    
  $('#profile_logo').on('change',function(){
    $('#logoUpdate').trigger('click');
  });

  $("#logo_upload_form").on('submit',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('update-comapny-logo') }}",
      method: 'post',
      data: new FormData(this), 
      contentType: false,       
      cache: false,             
      processData:false,
      beforeSend: function(){
        $('#loader_modal').modal('show');
      },
      success: function(result){
        if(result.error === false)
        {  
          toastr.success('Success!', 'Logo Updated Successfully',{"positionClass": "toast-bottom-right"});
          $('#logo_upload_form')[0].reset();
          setTimeout(function(){
            $('#loader_modal').modal('hide');
            window.location.reload();
          }, 500);
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

  $(document).on('keyup', '.form-control', function(){
    $(this).removeClass('is-invalid');
    $(this).next().remove();
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

  $(document).on("change",".selectFocus",function() {

    if($(this).attr('name') == 'billing_country')
    {
      var comapny_id = "{{$addCompany->id}}";
      var country_name = $("option:selected", this).html();
      var country_id   = $("option:selected", this).val();
      var thisPointer  = $(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(country_name);
      saveCompanyData(thisPointer,thisPointer.attr('name'), thisPointer.val());
      $.ajax({
        method: "get",
        url: "{{ route('getting-country-states-backend') }}",
        dataType: 'json',
        context: this,
        data: {country_id:country_id, comapny_id:comapny_id},
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
          if(data.html_string)
          {
            $('.country-div').addClass('d-none');
            $(".inputDoubleClick").removeClass("d-none");
            $("#billing_country").html(country_name);
            $(".country-states").removeClass('d-none');
            $('.country-states').empty();
            $(".country-states").append(data.html_string);
            $(".state-div").removeClass('d-none');
            $("#billing_state").addClass('d-none');
          }
        },
      });
    }
    else if($(this).attr('name') == 'billing_state')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      $(".state-div").addClass('d-none');
      $(".country-states").addClass('d-none');
      $(".inputDoubleClick").removeClass('d-none');
      $("#billing_state").html(new_value);
      saveCompanyData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    }

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

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active'))
    {
      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
      }
      if($(this).attr('name') == 'billing_email')
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
              saveCompanyData(thisPointer,thisPointer.attr('name'), thisPointer.val());
            }
          }
          else
          {
            swal({ html:true, title:'Alert !!!', text:'<b>Please Enter Valid Email, Try Again!!!</b>'});
            return false;
          }
        }
      }
      else
      {
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
          
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');

          saveCompanyData(thisPointer,thisPointer.attr('name'), thisPointer.val());
        }
        else
        {
          return false;
        }
      }
    }
  });

  function saveCompanyData(thisPointer,field_name,field_value){
    var comapny_id = "{{$addCompany->id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ route('save-comapny-data-from-detail') }}",
      dataType: 'json',
      data: 'comapny_id='+comapny_id+'&'+field_name+'='+encodeURIComponent(field_value),
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
        if(data.success == false)
        {
          if(field_name == "company_name")
          {
            toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
            return false;
          }
        }
        if(data.completed == 1)
        {
          toastr.success('Success!', 'Comapny Marked As Completed Successfully.',{"positionClass": "toast-bottom-right"});
        }
        else
        {
          toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
        }
        if(data.reload == 1)
        {
          window.location.reload();
        }
      },
    });
  }
  // getting state with respect to selected country
  $(document).on('change',".country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({
      url:"{{url('common/filter-state')}}",
      method:"get",
      dataType:"json",
      data:{country_id:country_id},
      success:function(data){
        var html_string='';
        for(var i=0;i<data.length;i++){
            html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
        }
        $("#state").html(html_string);
        $("#state2").html(html_string);
        $('.selectpicker').selectpicker('refresh');
      },
      error:function(){
        alert('Error');
      }
    });
  });

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27)
    {
      if($('.inputDoubleClick').hasClass('d-none'))
      {
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none'); 
      }
    }
  });

  $('.company-banks').on('submit', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-company-banks') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this), 
      contentType: false,       
      cache: false,             
      processData:false,   
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      success: function(result){
        // $('#loader_modal').modal('hide');
        if(result.success == true)
        {
          toastr.success('Success!', 'Bank\'s Assigned Successfully' ,{"positionClass": "toast-bottom-right"});
          setTimeout(function(){
            window.location.reload();
          }, 500);
        }
      },
      error: function (request, status, error) {
        $('#loader_modal').modal('hide');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'[]"]').addClass('is-invalid');
        });
      }
    });
  });

  });
</script>
@stop

