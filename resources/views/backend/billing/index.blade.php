@extends('backend.layouts.layout')

@section('title','Billing Configuration | Supply Chain')
@section('content')
<style type="text/css">
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
  font-weight: bold;
}

/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
-webkit-appearance: none;
margin: 0;
}

/* Firefox */
input[type=number] {
-moz-appearance:textfield;
}
.select2-container {
  width: 100% !important;
}
.annual-check
{
	width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    border: 4px solid blue;
}

.monthly-check
{
	width: 12px;
    height: 12px;
    background: white;
    border-radius: 50%;
    border: 4px solid blue;
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
          <li class="breadcrumb-item active">Billing Configuration</li>
      </ol>
  </div>
</div>

@php
use Carbon\Carbon;
@endphp
<h3>Billing Configuration</h3>

<div class="row mb-3">
	<div class="col-md-12">
		<div class="bg-white pt-3 pl-2 h-100">
			<ul class="nav nav-tabs" id="myTab" role="tablist">
			  <li class="nav-item">
			    <a class="nav-link @if($annual == null || @$annual->status == 1) active @endif" id="annual-tab" data-toggle="tab" href="#annual" role="tab" aria-controls="annual" aria-selected="true">
			    	@if($annual == null || $annual->status == 1)
			    	<i class="fa mr-2 annual-check"></i>
			    	@else
			    	<i class="fa mr-2 annual-check d-none"></i>
			    	@endif
			    Annual</a>
			  </li>
			  <li class="nav-item">
			    <a class="nav-link @if(@$monthly->status == 1) active @endif" id="monthly-tab" data-toggle="tab" href="#monthly" role="tab" aria-controls="monthly" aria-selected="false">
		    	@if($monthly->status == 1)
		    	<i class="fa mr-2 monthly-check"></i>
		    	@else
		    	<i class="fa mr-2 monthly-check d-none"></i>
		    	@endif
			    Monthly
			</a>
			  </li>
			</ul>
			<div class="tab-content" id="myTabContent">
			  <div class="tab-pane fade @if($annual == null || @$annual->status == 1) show active @endif"  id="annual" role="tabpanel" aria-labelledby="annual-tab">
			  	<table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font mt-2" style="width: 100%;">
			        <tbody>

			          <tr>
			            <td class="fontbold">Official Launch Date: <b style="color: red;">*</b></td>
			            <td>
			              <span class="pl-4 inputDoubleClick" id="annaul_official_launch_date_span" data-fieldvalue="{{@$annual->official_launch_date}}">
					      @if($annual != null && $annual->official_launch_date != null)
					      {{Carbon::parse($annual->official_launch_date)->format('d/m/Y')}}
					      @else
					      Official Launch Date Here
					      @endif
					    </span>
					    <input type="text" class="ml-4 mt-2 d-none date" name="annaul_official_launch_date" id="annaul_official_launch_date" value="{{@$annual->official_launch_date}}">
			            </td>
			          </tr>

			          <tr>
			            <td class="fontbold">Total Users Allowed: <b style="color: red;">*</b></td>
			            <td class="text-nowrap">
			                 <span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$annual->total_users_allowed}}">
						      @if($annual != null && $annual->total_users_allowed != null)
						      {{$annual->total_users_allowed}}
						      @else
						      Total users allowed here
						      @endif
						    </span>
						    <input type="number" class="ml-4 mt-2 d-none fieldFocus" name="total_users_allowed" id="total_users_allowed" value="{{@$annual->total_users_allowed}}">
			            </td>
			          </tr>

			          <tr>
			            <td class="fontbold">Billing Currency: <b style="color: red;">*</b></td>
			            <td class="text-nowrap">
			            	<span class="pl-4 selectDoubleClick" id="annual_billing_currency">
						      @if($annual != null && $annual->currency_id != null)
						      {{@$annual->currency->currency_name}}
						      @else
						      Select Billing Currency here
						      @endif
						    </span>
						    <select class="form-control d-none mb-2 select-common fieldFocus" name="annual_billing_currency" id="annual_billing_currency" style="width: 100%; margin-left: 25px; height: 40px;">
						      <option selected disabled value="">Select Billing Curruncy</option>';
						     @foreach($currencies as $currency)
						     	<option value="{{$currency->id}}">{{$currency->currency_name}}</option>
						     @endforeach
						    </select>
			            </td>
			          </tr>
			          <tr>
			            <td class="fontbold text-nowrap">Current Annual Fee: <b style="color: red;">*</b></td>
			            <td class="text-nowrap">
			            	<span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$annual->current_annual_fee}}" id="annual_fee">
						      @if($annual != null && $annual->current_annual_fee != null)
						      {{$annual->current_annual_fee}} {{@$annual->currency->currency_symbol}}
						      @else
						      Current Annual fee here
						      @endif
						    </span>
						    <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="current_annual_fee" id="current_annual_fee" value="{{@$annual->current_annual_fee}}">
			            </td>
			          </tr>
			        </tbody>
			      </table>
			  </div>
			  <div class="tab-pane fade @if(@$monthly->status == 1) show active @endif" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
			  	<table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font mt-2" style="width: 100%;">
			        <tbody>

			          <tr>
			            <td class="fontbold">Official Launch Date: <b style="color: red;">*</b></td>
			            <td>
			              <span class="pl-4 inputDoubleClick" id="monthly_official_launch_date_span" data-fieldvalue="{{@$monthl->official_launch_date}}">
					      @if($monthly != null && $monthly->official_launch_date != null)
					      {{Carbon::parse($monthly->official_launch_date)->format('d/m/Y')}}
					      @else
					      Official Launch Date Here
					      @endif
					    </span>
					    <input type="text" class="ml-4 mt-2 d-none date" name="monthly_official_launch_date" id="monthly_official_launch_date" value="{{@$monthly->official_launch_date}}" readonly="true">
			            </td>
			            </td>
			          </tr>
			          <tr>
			            <td class="fontbold text-nowrap">Number of Free Users: <b style="color: red;">*</b></td>
			            <td class="text-nowrap">
			            	<span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$monthly->total_users_allowed}}">
						      @if($monthly != null && $monthly->no_of_free_users != null)
						      {{$monthly->no_of_free_users}}
						      @else
						      No. of Free Users here
						      @endif
						    </span>
						    <input type="number" class="ml-4 mt-2 d-none fieldFocus" name="no_of_free_users" id="no_of_free_users" value="{{@$monthly->no_of_free_users}}">
			            </td>
			          </tr>
			          <tr>
			            <td class="fontbold">Billing Currency: <b style="color: red;">*</b></td>
			            <td class="text-nowrap">
			            	<span class="pl-4 selectDoubleClick" id="month_billing_currency">
						      @if($monthly != null && $monthly->currency_id != null)
						      {{@$monthly->currency->currency_name}}
						      @else
						      Select Billing Currency here
						      @endif
						    </span>
						    <select class="form-control d-none mb-2 select-common fieldFocus" name="monthly_billing_currency" id="monthly_billing_currency" style="width: 100%; margin-left: 25px; height: 40px;">
						      <option selected disabled value="">Select Billing Curruncy</option>';
						     @foreach($currencies as $currency)
						     	<option value="{{$currency->id}}">{{$currency->currency_name}}</option>
						     @endforeach
						    </select>
			            </td>
			          </tr>

			          <tr>
			            <td class="fontbold">Monthly Price per User: <b style="color: red;">*</b></td>
			            <td class="text-nowrap">
			                 <span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$monthly->monthly_price_per_user}}" id="monthly_price">
						      @if($monthly != null && $monthly->monthly_price_per_user != null)
						      {{$monthly->monthly_price_per_user}} {{@$monthly->currency->currency_symbol}}
						      @else
						      Monthly Price per Unit here
						      @endif
						    </span>
						    <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="monthly_price_per_user" id="monthly_price_per_user" value="{{@$monthly->monthly_price_per_user}}">
			            </td>
			          </tr>
			        </tbody>
			      </table>
			  </div>
			</div>
		</div>
	</div>

</div>

@endsection

@section('javascript')
<script type="text/javascript">
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
});
$("#annaul_official_launch_date, #monthly_official_launch_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });




$('#annual-tab').click(function () {
	$('.annual-check').removeClass('d-none');
	$('.monthly-check').addClass('d-none');
	saveRadioData('annual');
})

$('#monthly-tab').click(function () {
	$('.annual-check').addClass('d-none');
	$('.monthly-check').removeClass('d-none');
	saveRadioData('monthly');
})

function saveRadioData(type) {
	$.ajax({
      method: "post",
      url: "{{ route('save-config-type') }}",
      dataType: 'json',
      data: 'type='+type,
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
      	if (data.success) {
      		toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
      	}
      }
    });
}
	// double click select
$(document).on("dblclick",".selectDoubleClick",function(){
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

$(document).on(' change ','#annaul_official_launch_date, #monthly_official_launch_date, #annual_billing_currency, #monthly_billing_currency', function(e){

	getValues(e, $(this));

});

$(document).on(' keyup focusout ','.fieldFocus', function(e){

	getValues(e, $(this));

});

function getValues(e, thisPointer) {

    var attr_name = thisPointer.attr('name');
    // if(fieldvalue == thisPointer.val())
    // {
    //   thisPointer.addClass('d-none');
    //   thisPointer.removeClass('active');
    //   thisPointer.prev().removeClass('d-none');
    //   return false;
    // }
	if(thisPointer.val() == '' || thisPointer.val() == null)
    {
      if(attr_name == 'annaul_official_launch_date' || attr_name == 'monthly_official_launch_date')
      {
        thisPointer.prev().html("Official Launch Date Here");
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
      else if(attr_name == 'total_users_allowed')
      {
        thisPointer.prev().html("Total Users Allowed Here");
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
      else if(attr_name == 'annual_billing_currency' || attr_name == 'monthly_billing_currency')
      {
        thisPointer.prev().html("Select Billing Currency Here");
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
      else if(attr_name == 'current_annual_fee')
      {
        thisPointer.prev().html("Current Annual Fee Here");
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
      else if(attr_name == 'monthly_price_per_user')
      {
        thisPointer.prev().html("Monthly Price per User Here");
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
      else if(attr_name == 'no_of_free_users')
      {
        thisPointer.prev().html("No. of Free User Here");
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
  	}
  	var old_value = thisPointer.prev().data('fieldvalue');
  	if (e.keyCode === 27 && thisPointer.hasClass('active')) {
    	var fieldvalue = thisPointer.prev().data('fieldvalue');
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    var fieldvalue = thisPointer.prev().data('fieldvalue');
    var old_value = thisPointer.prev().data('fieldvalue');
    var new_value = thisPointer.val();
    if((e.type === 'keyup' || e.type === 'focusout') && (e.keyCode === 13 || e.which === 0) && thisPointer.hasClass('active')){
        saveData(thisPointer,thisPointer.attr('name'), thisPointer.val(),old_value);
    }
    else if(e.type === 'change' & thisPointer.hasClass('active')){
    	saveData(thisPointer,thisPointer.attr('name'), thisPointer.val(),old_value);
    }
}
    //rarec
function saveData(thisPointer,field_name,field_value,old_value){
	var type = '';
	if (field_name === 'annaul_official_launch_date' || field_name === 'total_users_allowed' || field_name === 'annual_billing_currency' || field_name === 'current_annual_fee') {
		type = 'annual';
	}
	else{
		type = 'monthly';
	}

    $.ajax({
      method: "post",
      url: "{{ route('save-billing-configuration') }}",
      dataType: 'json',
      data: 'field_name='+field_name+'&'+'field_value='+encodeURIComponent(field_value)+'&'+'old_value'+'='+encodeURIComponent(old_value)+'&type='+type,
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
		thisPointer.addClass('d-none');
		if (field_name === 'annual_billing_currency') {
			thisPointer.prev().html(data.currency_name);
			if (data.current_annual_fee != null) {
				$('#annual_fee').html(data.current_annual_fee + ' ' + data.symbol);
			}
		}
		else if (field_name === 'monthly_billing_currency') {
			thisPointer.prev().html(data.currency_name);
			if (data.monthly_price_per_user != null) {
				$('#monthly_price').html(data.monthly_price_per_user + ' ' + data.symbol);
			}
		}
		else if (field_name === 'current_annual_fee' || field_name === 'monthly_price_per_user') {
			thisPointer.prev().html(field_value + ' ' + data.symbol);
		}
		else{
			thisPointer.prev().html(field_value);
		}
		thisPointer.prev().removeClass('d-none');
		thisPointer.removeClass('active');
		thisPointer.attr('value', field_value);
		thisPointer.val(field_value);
		thisPointer.prev().removeData('fieldvalue');
		thisPointer.prev().data('fieldvalue', field_value);
        toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});


      },
      error: function(request, status, error){

        $("#loader_modal").modal('hide');
      }
    });
};

</script>
@endsection
