@extends($layout.'layouts.layout')

@section('title','Customer Detail | Supply Chain')

@section('content')
<?php
use Carbon\Carbon;
?>
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}

/* p
{
  font-size: small;
  font-style: italic;
  color: red;
} */
.selectDoubleClick, .inputDoubleClick{
    font-style: italic;
}
</style>

<!-- New Design-->
<!-- Right Content Start Here -->
<div class="right-content pt-0">

<!-- upper section start -->
<div class="row mb-4">
<!-- left Side Start -->

<div class="col-lg-12 col-md-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1 col-md-1">
  @if($customer->logo != Null && file_exists( public_path() . '/uploads/sales/customer/logos/' . $customer->logo))
  <div class="logo-container">
  <img src="{{asset('public/uploads/sales/customer/logos/'.$customer->logo)}}" style="border-radius:50%;height:68px;" alt="logo" class="img-fluid lg-logo">
  <div class="overlay">
  <a href="#" class="icon" title="User Profile" data-toggle="modal" data-target="#uploadModal">
    <i class="fa fa-camera"></i>
  </a>
  </div>
</div>
  @else
  <div class="logo-container">
  <img src="{{asset('public/uploads/sales/customer/logos/profileImg.png')}}" alt="Avatar" class="image">
  <div class="overlay">
  <a href="#" class="icon" title="User Profile" data-toggle="modal" data-target="#uploadModal">
    <i class="fa fa-camera"></i>
  </a>
  </div>
</div>
  @endif
  </div>
  <div class="col-lg-9 col-md-9 p-0">
<h4 class="mb-0 mt-lg-4">Customer Detail Page</h4>
  </div>
  
</div>

</div>
<div class="col-lg-12 col-md-12">
  <div class="row headings-color">
  <div class="col-lg-6 col-md-6">
    <h3 class="">Company Information</h3>
  </div>

  <div class="col-lg-3 col-md-3 ">
    <h3 class="">Notes</h3>
  </div>
  <div class="col-lg-3 col-md-3">
  
  </div>
  </div>
</div>
<!-- Upload profile Image Modal -->
<!-- Modal -->
<div class="modal fade" id="uploadModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Upload Profile Image</h4>
        </div>
        <div class="modal-body">
              <form method="post" action="{{url('sales/update-profile-pic/'.$customer->id)}}" class="upload-excel-form" enctype="multipart/form-data">
                  {{csrf_field()}}
                  <div class="col-lg-12 col-md-6 col-sm-6 col-6 form-group">
                          <label>Choose File</label>
                          <input type="file" class="form-control" name="logo" >
                          <span style="color:blue;">(Only pdf,jpg,jpeg files allowed. Max file size is 2 MB.)</span>
                        </div>           
                  
                 
       
        <div class="modal-footer">
        <input type="submit" value="upload" class="btn btn-sm save-btn">

          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
        </form>
        </div>
      </div>
      
    </div>
  </div>
<!-- Modal Ends Here -->





<div class="col-lg-6 col-md-6">
  <div class="bg-white p-3">
    <table class="table-responsive table sales-customer-table const-font" style="width: 100%;">
      <tbody>

        <tr>
          <td class="fontbold">Reference Name<b style="color:red;">*</b></td>
          <td></td>
          <td class="text-nowrap"> 
          <span class="m-l-15 inputDoubleClick" id="reference_name"  data-fieldvalue="{{@$customer->reference_name}}">
          {{(@$customer->reference_name!=null)?@$customer->reference_name:'N/A'}}
          </span>

          <input type="text" autocomplete="nope" name="reference_name" class="fieldFocus d-none" value="{{(@$customer->reference_name!=null)?$customer->reference_name:''}}"><br>
          </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['company_name']}}<b style="color:red;">*</b></td>
          <td></td>
          <td class="text-nowrap"> 
          <span class="m-l-15 inputDoubleClick" id="company"  data-fieldvalue="{{@$customer->company}}">
          {{(@$customer->company!=null)?@$customer->company:'N/A'}}
          </span>

          <input type="text" autocomplete="nope" name="company" class="fieldFocus d-none" id="companyCheck" value="{{(@$customer->company!=null)?$customer->company:''}}"><br>
         <span id="company-exists" style="color:red;" class="d-none"> Company already Exist </span>
          </td>
        </tr>


         <tr>
          <td class="fontbold"> @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif <b style="color:red;">*</b></td>
          <td></td>

          <td class="text-nowrap">
            <span class="m-l-15 selectDoubleClick" id="category_id" data-fieldvalue="{{@$customer->category_id}}"> 
              {{(@$customer->category_id!=null)?@$customer->CustomerCategory->title:'Select'}}
            </span>

            <select name="category_id" class="selectFocus form-control prod-category d-none">
            <option>Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
            @if($categories->count() > 0)
            @foreach($categories as $categry)
            <option {{ (@$customer->category_id == $categry->id ? 'selected' : '' ) }} value="{{ $categry->id }}">{{ $categry->title }}</option>
            @endforeach
            @endif
            </select>
            <!-- <input type="text" name="category_id" class="fieldFocus d-none" value="{{(@$customer->category_id!=null)?@$customer->CustomerCategory->title:'Select'}}"> -->
          </td>
        </tr>

          <tr class="d-none">
          <td class="fontbold">Primary Contact</td>
          <td></td>
          <td>
          <span class="m-l-15 inputDoubleClick" id="phone"  data-fieldvalue="{{$customer->phone}}"> 
            {{$customer->phone}}
          </span>

          <input type="text" name="phone" class="fieldFocus d-none" value="{{(@$customer->phone!=null)?$customer->phone:''}}">

          </td>
        </tr>
        @if($customer->secondary_phone != NULL)
         <tr>
          <td class="fontbold text-nowrap">Additional Contacts </td>
          
          <td></td>
          <td> {{@$customerShipping->secondary_phone}}</td>
        </tr>
        @endif

        <tr class="d-none">
          <td class="fontbold">Address</td>          
          <td></td>
          <td class="text-nowrap"> 
            <span class="m-l-15 inputDoubleClick" id="address"  data-fieldvalue="{{$customer->address_line_1}}">
            {{$customer->address_line_1}}
          </span>
          <input type="text" name="address_line_1" class="fieldFocus d-none" value="{{(@$customer->address_line_1!=null)?$customer->address_line_1:''}}">

          <br>
            @if($customer->address_line_2 != NULL)
            <span class="m-l-15 inputDoubleClick" id="address_2"  data-fieldvalue="{{$customer->address_line_2}}">
            {{$customer->address_line_2}}
            </span>

            <input type="text" autocomplete="nope" name="address_line_2" class="fieldFocus d-none" value="{{(@$customer->address_line_2!=null)?$customer->address_line_2:''}}">

            @endif
          </td>
        </tr>



        <tr class="d-none">
          <td class="fontbold">City</td>
          
          <td></td>
          <td>
          <span class="m-l-15 inputDoubleClick" id="city"  data-fieldvalue="{{$customer->city}}">
            {{$customer->city}}
          </span>
          <input type="text" autocomplete="nope" name="city" class="fieldFocus d-none" value="{{(@$customer->city!=null)?$customer->city:''}}">

          </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold">Phone #s</td>
        
          <td></td>
          <td>{{$customer->phone}}</td>
        </tr>

         <tr>
          <td class="fontbold">Credit Terms<b style="color:red;">*</b> </td>
        
          <td></td>
          <td>
          <span class="m-l-15 inputDoubleClick" id="credit_term"  data-fieldvalue="{{$customer->credit_term}}">
          {{(@$customer->getpayment_term->title!=null)?$customer->getpayment_term->title:'Select'}}
          </span>
          <select name="credit_term" class="selectFocus form-control prod-category d-none">
          <option>Choose Credit Term</option>
          @if($paymentTerms->count() > 0)
          @foreach($paymentTerms as $paymentTerm)
          <option {{ (@$paymentTerm->title == @$customer->getpayment_term->title ? 'selected' : '' ) }} value="{{ $paymentTerm->id }}">{{ $paymentTerm->title }}</option>
          @endforeach
          @endif
          {{--<option value="new">Add New</option>--}}

          </select>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Payment Method<b style="color:red;">*</b></td>
          
          <td></td>
          <td>
           @foreach($paymentTypes as $paymentType)
           @php $find = true; @endphp
            @foreach($customer->customer_payment_types as $type)
          <span class="m-l-15">
            @if($type->get_payment_type->id == $paymentType->id)
            @php $find = false; @endphp
            <input type="checkbox" name="paymentType" checked class="pay-check" disabled="true" value={{@$type->get_payment_type->id}}> {{@$type->get_payment_type->title}}
            @endif
          </span>
          @endforeach
            @if($find)
            <input type="checkbox" name="paymentType" disabled="true" class="pay-check" value="{{@$paymentType->id}}"> {{@$paymentType->title}}
            @endif
            @endforeach
          </td>
        </tr>

        <tr>
          <td class="fontbold">Reference #</td>
          <td></td>
          <td class="text-nowrap"> 
          <span class="m-l-15" id="reference_number"  data-fieldvalue="{{@$customer->reference_number}}">
          {{(@$customer->reference_number!=null)?@$customer->reference_number:'N/A'}}
          </span>

          <input type="text" autocomplete="nope" name="reference_number" class="d-none" value="{{(@$customer->reference_number!=null)?$customer->reference_number:''}}">
          </td>
        </tr>



      </tbody>
    </table>
   
  </div>
  
  
</div>
 <div class="col-lg-4 d-none">
  <div class="bg-white h-100 p-3">
    <table class=" table sales-customer-table const-font" style="width: 100%;max-width:100%;">
      <tbody>
        <tr>
        <td class="fontbold text-nowrap">Shipping Information</td>
        
        <td class="pull-right">
          @if($getCustShipping)
          <select class="shippingSelectFocus" id="shipping-id" name="shipping-select">
          <option value="" disabled>Select Shipping</option>
            @foreach($getCustShipping as $shipping)
            <option value="{{$shipping->id}}">{{$shipping->title}}</option>
            @endforeach
          </select>
          @endif
          <a href="#" data-toggle="modal" data-target="#add_shipping_detail_modal">
          <span class="pluse "> +</span></a>     
        </td>
     
        </tr>

        <tr>
          <td class="fontbold">Title</td>
          <td> <span id="shipping-title" class="m-l-15 inputDoubleClick"> {{(@$customerShipping->title!=null)?$customerShipping->title:'N.A'}}</span>
          <input type="text" name="title" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->title!=null)?$customerShipping->title:''}}">
        
        </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['company_name']}}</td>
          <td> <span id="shipping-company" class="m-l-15 inputDoubleClick"> {{(@$customerShipping->company_name!=null)?$customerShipping->company_name:'N.A'}}</span>
          <input type="text" name="company_name" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->company_name!=null)?$customerShipping->company_name:''}}">
        
        </td>
        </tr>

        <tr>
          <td class="fontbold">Shipping Contact Name</td>
          <td> <span id="shipping-contact_name" class="m-l-15 inputDoubleClick"> {{(@$customerShipping->shipping_contact_name!=null)?$customerShipping->shipping_contact_name:'N.A'}}</span>
          <input type="text" name="shipping_contact_name" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->shipping_contact_name!=null)?$customerShipping->shipping_contact_name:''}}">
        
        </td>
        </tr>

        <tr>
          <td class="fontbold">Primary Contact</td>
          <td> <span id="primary-contact" class="m-l-15 inputDoubleClick"> {{@$customerShipping->shipping_phone}}</span>
          <input type="text" name="shipping_phone" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->shipping_phone!=null)?$customerShipping->shipping_phone:''}}">
        
        </td>
        </tr>

        @if(@$customerShipping->secondary_phone != NULL)
        <tr>
          <td class="fontbold">Additional Contacts </td>
          <td> <span id="secondary-contact" class="m-l-15 inputDoubleClick"> {{@$customerShipping->secondary_phone}} </span></td>
        </tr>
        @endif

        <tr>
          <td class="fontbold">Addresses</td>
          <td class=""> <span id="shipping-address" class="m-l-15 inputDoubleClick"> {{@$customerShipping->shipping_address}} </span>
          <input type="text" name="shipping_address" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->shipping_address!=null)?$customerShipping->shipping_address:''}}">
        </td>
        </tr>

        <tr>
          <td class="fontbold">Email</td>
          <td> <span id="shipping-email" class="m-l-15 inputDoubleClick"> {{(@$customerShipping->shipping_email!=null)?$customerShipping->shipping_email:'N.A'}}</span>
          <input type="email" name="shipping_email" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->shipping_email!=null)?$customerShipping->shipping_email:''}}">
        
        </td>
        </tr>

        <tr>
          <td class="fontbold">Fax</td>
          <td class=""> <span id="shipping-fax" class="m-l-15 inputDoubleClick"> {{(@$customerShipping->shipping_fax!=null)?$customerShipping->shipping_fax:'N.A'}} </span>
          <input type="number" name="shipping_fax" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->shipping_fax!=null)?$customerShipping->shipping_fax:''}}">
        </td>
        </tr>

        <tr>
          <td class="fontbold">City</td>
          <td><span id="shipping-city" class="m-l-15 inputDoubleClick">
            {{(@$customerShipping->shipping_city!=Null)?@$customerShipping->shipping_city:'N.A'}}
            <!-- {{(@$customer->reference_number!=null)?@$customer->reference_number:'N/A'}} -->

          </span>
          <input type="text" name="shipping_city" class="shipping-fieldFocus d-none" value="{{(@$customerShipping->shipping_city!=null)?$customerShipping->shipping_city:''}}">

        </td>
        </tr>
        

        <tr>
          <td class="fontbold">Phone #s</td>
        
          <td><span id="phone-s">{{@$customerShipping->shipping_phone}}</span></td>
        </tr>

  


      </tbody>
    </table>
  </div> 
</div>
<div class="col-lg-6 col-md-6">
  <div class="bg-white h-100 const-font pt-3">
      
      <div class="inner-div pl-3 pr-3 pb-5" id="myNotes">
     
        <div class="inner-div-detail p-3">
          @if($customerNotes != null)
          @if($customerNotes->count() > 0)
        @foreach($customerNotes as $note)
          <div class="para-detail1 bg-white p-3">
            <p>{{@$note->note_description}}</p>
          </div>

          <div class="d-flex justify-content-between pt-2 pb-2">
            <p>by {{@$note->getuser->name}} | {{Carbon::parse(@$note->created_at)->format('M d Y')}}</p>
           
           </div>
           @endforeach
           @else
            <div class="para-detail1 bg-white p-3">
            <p style="text-align: center;">No Notes Found</p>
          </div>
          @endif
          @endif
        </div>
      
      </div>



  </div>
  
</div>
<div class="col-lg-6 col-md-6 mt-4 h-100">
  <div class="col-lg-12 col-md-12 mt-1">
      <div class="row headings-color mb-2" >
        <div class="col-lg-6 col-md-6 p-0">
          <h3 class="mb-0">Contacts</h3>
        </div>
      
        
      </div>
    </div>
   
       <div class="entriesbg bg-white custompadding customborder" style="min-height: 45% !important;height: 270px;">
  <table class="table-contacts table entriestable table-bordered text-center">
       <thead style="width: 40%;">
                  <tr>
                      <th>Name</th>
                      <th>Surname</th>
                      <th>Email</th>
                      <th>Telephone</th>
                      <th>Position</th>
                  </tr>
              </thead>
     
    </table>
  </div>

  <div class="col-lg-12 col-md-12 p-0 mt-4">
  <div class="row">
    <div class="col-lg-8 col-md-10">
    <h3>@if(!array_key_exists('general_document', $global_terminologies)) General Documents @else {{$global_terminologies['general_document']}} @endif</h3>
    </div>
    <div class="col-lg-4 col-md-2">
     @if(@$customer->get_customer_documents->count() > 5)
    <a href="{{url('common/get-customer-documents-common/'.@$customer->id)}}" class="btn add-btn btn-color pull-right mr-1" id="viewAllDocuments">View All</a>
    @endif
    </div>
  </div>
</div>



  <div class="entriesbg bg-white custompadding customborder" style="min-height: 43% !important;height: 270px;">
    <div class="mb-2">
    <table class="table entriestable table-bordered table-general-documents text-center" style="border-bottom: none;width: 100%">
       <thead>
                  <tr>
                      <th>Description</th>
                      <th>File Name</th>
                      <th>Date</th>
              
                  </tr>
              </thead>
 
    </table>
    </div>
</div>

</div>
<div class="col-lg-6 col-md-6 mt-4">
   <div class="col-lg-12 col-md-12 mt-1">
      <div class="row headings-color mb-2" >
        <div class="col-lg-6 col-md-10 p-0">
          <h3 class="mb-0">Company Addresses</h3>
        </div>
        <div class="col-lg-6 col-md-2 p-0">
     
        </div>
        
      </div>
    </div>
  <div class="bg-white p-3" style="height: 94% !important;">
    <table class=" table sales-customer-table const-font" style="width: 100%;max-width:100%">
      <tbody>
        <tr>
        <td class="fontbold ">Company Addresses</td>
        
        <td class="" width="50%">
          @if($getCustBilling)
          <select class="billingSelectFocus" style="width: 132px;" id="billing-id" name="billing-select">
          <option value="{{@$customerBilling->id}}" disabled="true">Select Address</option>
            @foreach($getCustBilling as $billing)
            @if(@$billing->id == @$customerBilling->id)
            <option value="{{$billing->id}}" selected="true">{{$billing->title}}</option>
            @else
            <option value="{{$billing->id}}">{{$billing->title}}</option>
            @endif
            @endforeach
          </select>
          @endif
            
        </td>
     
        </tr>

        <tr>
          <td class="fontbold">Reference Name<b style="color:red;">*</b></td>
          <td> <span id="billing-title" class="m-l-15 inputDoubleClick"> {{(@$customerBilling->title!=null)?$customerBilling->title:'N.A'}}</span>
          <input type="text" autocomplete="nope" name="title" class="billing-fieldFocus d-none" value="{{(@$customerBilling->title!=null)?$customerBilling->title:''}}">
        
        </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold">{{$global_terminologies['company_name']}}</td>
          <td> <span id="billing-company" class="m-l-15 inputDoubleClick"> {{(@$customerBilling->company_name!=null)?$customerBilling->company_name:'N.A'}}</span>
          <input type="text" autocomplete="nope" name="company_name" class="billing-fieldFocus d-none" value="{{(@$customerBilling->company_name!=null)?$customerBilling->company_name:''}}">
        
        </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold">Billing Contact Name</td>
          <td> <span id="billing-contact_name" class="m-l-15 inputDoubleClick"> {{(@$customerBilling->billing_contact_name!=null)?$customerBilling->billing_contact_name:'N.A'}}</span>
          <input type="text" autocomplete="nope" name="billing_contact_name" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_contact_name!=null)?$customerBilling->billing_contact_name:''}}">
        
        </td>
        </tr>

        <tr>
          <td class="fontbold">Phone No.<b style="color:red;">*</b></td>
          <td> <span id="billing-primary-contact" class="m-l-15 inputDoubleClick"> {{@$customerBilling->billing_phone != NULL ? @$customerBilling->billing_phone : 'N.A'}}</span>
          <input type="text" autocomplete="nope" name="billing_phone" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_phone!=null)?$customerBilling->billing_phone:''}}">
        
        </td>
        </tr>

        <tr>
          <td class="fontbold">Moblie No.</td>
        
          <td><span id="cell_number" class="m-l-15 inputDoubleClick">{{@$customerBilling->cell_number != null ? $customerBilling->cell_number : 'N.A'}}</span>
             <input type="text" autocomplete="nope" name="cell_number" class="billing-fieldFocus d-none" value="{{(@$customerBilling->cell_number!=null)?$customerBilling->cell_number:''}}">
          </td>
        </tr>

        @if(@$customerBilling->secondary_phone != NULL)
        <tr>
          <td class="fontbold ">Additional Contacts </td>
          <td> <span id="billing-secondary-contact" class="m-l-15 inputDoubleClick"> {{@$customerBilling->secondary_phone}} </span></td>
        </tr>
        @endif

        <tr>
          <td class="fontbold">Address<b style="color:red;">*</b></td>
          <td class=""> <span id="billing-address" class="m-l-15 inputDoubleClick"> {{@$customerBilling->billing_address != NULL ? @$customerBilling->billing_address : 'N.A'}} </span>
          <input type="text" autocomplete="nope" name="billing_address" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_address!=null)?$customerBilling->billing_address:''}}">
        </td>
        </tr>

        <tr>
          <td class="fontbold">Tax ID<b style="color:red;">*</b></td>
        
          <td><span id="cell_number" class="m-l-15 inputDoubleClick">{{@$customerBilling->tax_id != null ? $customerBilling->tax_id : 'N.A'}}</span>
             <input type="text" autocomplete="nope" name="tax_id" class="billing-fieldFocus d-none" value="{{(@$customerBilling->tax_id!=null)?$customerBilling->tax_id:''}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">Email<b style="color:red;">*</b></td>
          <td> <span id="billing-email" class="m-l-15 inputDoubleClick"> {{(@$customerBilling->billing_email!=null)?$customerBilling->billing_email:'N.A'}}</span>
          <input type="email" autocomplete="nope" name="billing_email" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_email!=null)?$customerBilling->billing_email:''}}">
        
        </td>
        </tr>

        <tr class="">
          <td class="fontbold">Fax</td>
          <td class=""> <span id="billing-fax" class="m-l-15 inputDoubleClick"> {{(@$customerBilling->billing_fax!=null)?$customerBilling->billing_fax:'N.A'}} </span>
          <input type="number" autocomplete="nope" name="billing_fax" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_fax!=null)?$customerBilling->billing_fax:''}}">
        </td>
        </tr>
         <tr>
          <td class="fontbold">District<b style="color:red;">*</b></td>
          <td><span id="billing-statee" class="m-l-15 inputDoubleClick">
            {{(@$customerBilling->billing_state!=Null)?@$customerBilling->getstate->name:'N.A'}}
          </span>
         <div class="d-none state-div">
         <select class="form-control state-tags update_state" id="billing-state" name="billing_state">
                <option selected="selected">Select District</option>
                @foreach($states as $state)
                @if($state->id == @$customerBilling->getstate->id)
                <option value="{{$state->id}}" selected="true">{{$state->name}}</option>
                @else
                <option value="{{$state->id}}">{{$state->name}}</option>
                @endif
                @endforeach
              </select>
              </div>

        </td>
        </tr>

        <tr>
          <td class="fontbold">City<b style="color:red;">*</b></td>
          <td><span id="billing-city" class="m-l-15 inputDoubleClick">
            {{(@$customerBilling->billing_city!=Null)?@$customerBilling->billing_city:'N.A'}}
          
          </span>
          <input type="text" autocomplete="nope" name="billing_city" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_city!=null)?$customerBilling->billing_city:''}}">

        </td>
        </tr>

        <tr class="">
          <td class="fontbold">Zip<b style="color:red;">*</b></td>
          <td class=""> <span id="billing-zip" class="m-l-15 inputDoubleClick"> {{(@$customerBilling->billing_zip!=null)?$customerBilling->billing_zip:'N.A'}} </span>
          <input type="number" autocomplete="nope" name="billing_zip" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_zip!=null)?$customerBilling->billing_zip:''}}">
        </td>
        </tr>

        

        <tr class="d-none">
          <td class="fontbold">Phone #s</td>
        
          <td><span id="billing-phone-s">{{@$customerBilling->billing_phone}}</span></td>
        </tr>

        

        <tr>
          <td class="fontbold">Is Default</td>
          @if(@$customerBilling->is_default == 1)
          <td><span id="billing_default">Yes</span></td>
          @else
          <td><span id="">
          <input type="checkbox" id="is_default" class=" is_default " name="is_default">
              <input type="hidden" id="is_default_value" class="form-control" name="is_default_value" value='0'>
              <input type="hidden" id="billing_address_id" class="form-control" name="is_default_value" value='{{@$customerBilling->id}}'>

          </span></td>
          @endif
        </tr>

  


      </tbody>
    </table>
  </div> 
</div>

</div>

<div class="row mb-3 headings-color">
<!-- left Side Start -->
<div class="col-lg-6 col-md-6 mt-5 d-none">
  <h3>Orders</h3>
</div>
<div class="col-lg-6  col-md-6 mt-5">
  <div class="row">
    <div class="col-lg-9 col-md-11"> <h3>Product Fixed Prices</h3></div>
    <div class="col-lg-3 col-md-1">
   
 @if(@$customer->productCustomerFixedPrice->count() > 5)
    <a href="{{url('common/get-customer-product-fixed-prices-common/'.@$customer->id)}}" class="btn add-btn btn-color pull-right mr-1" id="viewAllFixedPrices">View All</a>
    @endif
    </div>
  </div>
 
</div>
<div class="col-lg-6 col-md-6 mt-5">

</div>

<div class="col-lg-6 col-md-6 d-none">

  <div class="bg-white">

  <table class="table headings-color const-font" style="width:100%">
        <thead class="sales-coordinator-thead">                                                                                                     
            <tr>
             
                 <th>Month</th>
                 <th>Total Order</th>
                 <th>Paid</th>
                 <th>Unpaid</th>
                                                                 
            </tr>
        </thead>
        <tbody class="dot-dash">
          <tr>
           <td>Jan19</td>
           <td>J$120.00</td>
           <td>$70.00</td>
           <td>$50.00</td>
          </tr><tr>
           <td>Jan19</td>
           <td>J$120.00</td>
           <td>$70.00</td>
           <td>$50.00</td>
          </tr>

        </tbody>

       
    </table>
</div>
</div>

<!-- Order fixed Price  -->
<div class="col-lg-6 col-md-8">
<div class="bg-white p-2">

  <table class="table headings-color const-font table-bordered text-center" style="width:100%">
    <thead class="sales-coordinator-thead">                                                     
      <tr>
        <th>Product #</th>
        <th>Default Price</th>
        <th>Fixed Price</th>
        <th>Expiration Date</th>                                                
      </tr>
    </thead>

    <tbody class="">
    @if($ProductCustomerFixedPrice->count() > 0)
    @foreach($ProductCustomerFixedPrice as $item)
      <tr id="cust-fixed-price-{{$item->id}}" style="border: 1px solid #eee;">
        <td>{{@$item->products->refrence_code}}</td>
        <td>{{@$item->products->selling_price}}</td>
        <td>
      
        <span class="m-l-15 selectDoubleClick" id="fixed-price"  data-fieldvalue="{{@$item->fixed_price}}"> 
              {{(@$item->fixed_price!=null)?@$item->fixed_price:'N.A'}}
        </span>
        <input type="number" name="fixed-price" class="productFixed d-none" data-id="{{@$item->products->id}}" value="{{(@$item->fixed_price!=null)?$item->fixed_price:''}}">
        </td>
        <td>
        <span class="m-l-15 selectDoubleClick" id="expiration-date"  data-fieldvalue="{{@$item->expiration_date}}"> 
              {{(@$item->expiration_date!=null)?@$item->expiration_date:'N.A'}}
        </span>
        <input type="date" name="expiration-date" class="productFixed d-none" data-id="{{@$item->products->id}}" value="{{(@$item->expiration_date!=null)?$item->expiration_date:''}}">
        </td>
      </tr>               
    @endforeach
    @else
      <tr style="border: 1px solid #eee;">
        <td colspan="5">No Fixed product Info Found</td>
      </tr>          
    @endif
    </tbody>
  </table>
</div>
</div>


</div>













 <!-- main content end here -->
</div><!-- main content end here -->

{{--Add Billing Info--}}
<!-- Add Billing detail Modal Started -->
  <div class="modal fade" id="add_billing_detail_modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New Address</h4>
        </div>
        <div class="modal-body">
          
          <form id="add-address-form" method="POST">
          
          {{csrf_field()}}

          <input type="hidden" name="customer_id" id="billing_customer_id" value="{{$customer->id}}">
         
          <div class="row">
            <div class="form-group col-md-6">
              <label for="title"> Title:</label>
              <input required="true" type="text" class="form-control billing" id="billing_title" name="billing_title">
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Billing Contact Name :</label>
              <input required="true" type="text" class="form-control" id="billing_contact_name" name="billing_contact_name1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:</label>
              <input required="true" type="email" class="form-control" id="billing_email" name="billing_email1">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Billing Name :</label>
              <input required="true" type="text" class="form-control" id="company_name" name="company_name1">
            </div>

            </div>
            
            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:</label>
              <input required="true" type="text" class="form-control" id="billing_phone" name="billing_phone">
            </div>
            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control" id="billing_fax" name="billing_fax1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:</label>
              <input required="true" type="text" class="form-control" id="billing_address" name="billing_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:</label>
              <input required="true" type="text" class="form-control" id="billing_zip" name="billing_zip">
            </div>

           
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_country">Country:</label>
              <select required="true" id="billing_country" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select Country" name="billing_country">
                <option value="217" selected disabled="true">Thailand</option>
              </select>
            </div>

            
            <div class="form-group col-md-6 customer-state">
              <div>
              <label for="business_state">District:</label><br>
             <!--  <select required="true" id="billing_state" name="billing_state" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select State">
                 <option>choose state</option>
              </select> -->
               <select class="form-control state-tags" name="state">
                <option selected="selected">Select District</option>
                @foreach($states as $state)
                <option value="{{$state->id}}">{{$state->name}}</option>
                @endforeach
              </select>
              </div>
            </div>
    
            
            <div class="form-group col-md-6">
              <label for="contact_city">City:</label>
              <input type="text" required="true" id="billing_city" class="form-control " name="billing_city">
                 
            </div>

            <div class="form-group col-md-6 mt-4">
            <div class="row">
            <div class="col-lg-7 mt-3">
            <label for="contact_city">Is Default&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" id="is_default" class=" is_default " name="is_default">
              <input type="hidden" id="is_default_value" class="form-control" name="is_default_value" value='0'>
            </div>
            <div class="col-lg-1 offsets-3">
           
            </div>
            </div>
             
                 
            </div>
            </div>      
                     
          
          <div class="form-group col-md-12">
           <button type="submit" class="btn btn-primary btn-success" id="add-address-form-btn">Submit</button>
          </div>         
          </form>

        </div>
        
      </div>
      
    </div>
  </div>

<!-- Add Billing Details Modal Ended -->


<!-- Add Shipping detail Modal Started -->
  <div class="modal fade" id="add_shipping_detail_modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New Shipping Detail</h4>
        </div>
        <div class="modal-body">
          
          <form action="{{url('sales/save-customer-shipping')}}" method="POST">
          
          {{csrf_field()}}

          <input type="hidden" name="customer_id" value="{{$customer->id}}">
         
          <div class="row">
            <div class="form-group col-md-6">
              <label for="title"> Title:</label>
              <input required="true" type="text" class="form-control" id="shipping_title" name="shipping_title">
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Shipping Contact Name :</label>
              <input required="true" type="text" class="form-control" id="shipping_contact_name" name="shipping_contact_name">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:</label>
              <input required="true" type="email" class="form-control" id="shipping_email" name="shipping_email">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Billing Name :</label>
              <input required="true" type="text" class="form-control" id="company_name" name="company_name">
            </div>

            </div>
            
            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:</label>
              <input required="true" type="text" class="form-control" id="shipping_phone" name="shipping_phone">
            </div>
            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control" id="shipping_fax" name="shipping_fax">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:</label>
              <input required="true" type="text" class="form-control" id="shipping_address" name="shipping_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:</label>
              <input required="true" type="text" class="form-control" id="shipping_zip" name="shipping_zip">
            </div>

           
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_country">Country:</label>
              <select required="true" id="country" class="form-control selectpicker" data-live-search="true" title="Select Country" name="shipping_country">
              @foreach($countries as $result)
                 <option value="{{$result->id}}">{{$result->name}}</option> 
              @endforeach
              </select>
            </div>

            
            <div class="form-group col-md-6">
              <label for="business_state">District:</label>
              <select required="true" id="state" name="state" class="form-control selectpicker" data-live-search="true" title="Select District">
                 <option>Choose District</option>
              </select>
            </div>
    
            
            <div class="form-group col-md-6">
              <label for="contact_city">City:</label>
              <input type="text" required="true" id="shipping_city" class="form-control " name="shipping_information_city">
                 
            </div>
            </div>      
                     
          
          <div class="form-group col-md-12">
           <button type="submit" class="btn btn-primary btn-success">Submit</button>
          </div>         
          </form>

        </div>
        
      </div>
      
    </div>
  </div>

<!-- Modal For Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Customer Notes</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-cust-note-form" method="post">
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                      <div class="row">
                          <div class="col-xs-12 col-md-12">
                              <div class="form-group"> 
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Note Title" name="note_title">
                              </div>
                              <div class="form-group"> 
                                <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                                <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                              </div>
                          </div>
                      </div>
              </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="customer_id" class="note-customer-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>


{{-- Add Customer fixed prices modal --}}
<div class="modal addCustFixedPriceModal" id="addCustFixedPriceModal">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">ADD CUSTOMER FIXED PRICE</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

      
  <form id="addCustFixedPriceForm" method="POST">

    <div class="modal-body">

      <input type="hidden" name="customer_id" id="customer_id" value="{{$customer->id}}">

      @php
      $products = App\Models\Common\Product::where('status',1)->get();
      @endphp

      <div class="form-group">
        <label class="pull-left">Products*</label>
        <select class="font-weight-bold form-control-lg form-control product" name="product">
          <option value="">Select Products</option>
          <!-- @if($products)
          @foreach($products as $product)
            <option value="{{$product->id}}">{{$product->name}}</option>
          @endforeach
          @endif -->

          @if($products)
          @foreach($products as $product)
          @php $find = true; @endphp
          @if($ProductCustomerFixedPrice->count() > 0)
          @foreach($ProductCustomerFixedPrice as $item)
             @if(@$item->products->refrence_code == $product->refrence_code)
             @php $find = false; @endphp 
             @endif          
          @endforeach
          @endif
          @if($find)
          <option value="{{$product->id}}">{{$product->name}}</option>
        
          @endif
          @endforeach
          @endif
        </select>
      </div>

      <div class="form-group">
        <label class="pull-left">Default Price</label>
        <input type="text" class="font-weight-bold form-control-lg form-control" id="default_price" name="default_price" value="" readonly>
      </div>

      <div class="form-group">
        <label class="pull-left">Fixed Price</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Define fixed price here" name="fixed_price" type="number">
      </div>

      <div class="form-group">
        <label class="pull-left">Expiration Date</label>
        <input class="font-weight-bold form-control-lg form-control" name="expiration_date"  type="date">
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary save-fixed-price" id="addCustFixedPriceBtn">Add</button>
    </div>
  </form>

    </div>
  </div>
</div>
<!-- Add Shipping Details Modal Ended -->

   <!-- Add customer contacts modal -->
   <div class="modal customerContactModal" id="customerContactModal">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">Add Customer Contact</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

      
  <form id="addCustomerContactForm" method="POST">

    <div class="modal-body">

      <input type="hidden" name="customer_id" id="customer_id" value="{{$customer->id}}">



      <div class="form-group">
        <label class="pull-left">Name</label>
        <input type="text" required="required" class="font-weight-bold form-control-lg form-control" id="name" name="name" value="" placeholder="Name" >
      </div>

      <div class="form-group">
        <label class="pull-left">Sur Name</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Sur Name" name="sur_name" type="text">
      </div>

      <div class="form-group">
        <label class="pull-left">Email</label>
        <input class="font-weight-bold form-control-lg form-control" name="email"  type="email" placeholder="Email">
      </div>

      <div class="form-group">
        <label class="pull-left">Telephone</label>
        <input class="font-weight-bold form-control-lg form-control" name="telehone_number"  type="text" placeholder="Telephone Number">
      </div>

      <div class="form-group">
        <label class="pull-left">Position</label>
        <input class="font-weight-bold form-control-lg form-control" name="postion"  type="text" placeholder="Position">
      </div>

    </div>

    <div class="modal-footer">
      <!-- <button type="submit" class="btn btn-primary save-fixed-price" id="addCustFixedPriceBtn">Add</button> -->
      <input type="submit" class="btn btn-primary " id="addCustomerContact" value="Add">
    </div>
  </form>

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

@endsection

@section('javascript')
<script type="text/javascript">
  var id = "{{$customer->id}}";
     $('.table-contacts').DataTable({
          processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:false,
  "lengthChange": false,
  serverSide: true,
   "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,4 ] },
    { className: "dt-body-right", "targets": [] }
  ],
  "scrollX": true,
          "bPaginate": false,
          "bInfo":false,
  lengthMenu: [ 100, 200, 300, 400],
         ajax: {
            url:"{!! route('get-customer-contact-common') !!}",
            data: function(data) { data.id = id } ,
            },
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'name', name: 'name' },
            { data: 'sur_name', name: 'sur_name' },
            { data: 'email', name: 'email' },
            // { data: 'name', name: 'name' },
            { data: 'telehone_number', name: 'telehone_number' },
            { data: 'postion', name: 'postion' },
            // { data: 'action', name: 'action' },
           
              ]
    });

     //General documents table
$(function(e){
  var id = "{{@$customer->id}}";
     $('.table-general-documents').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        "searching": false,
          "lengthChange": false,
          "autoWidth": false,
          "bInfo" : false,
          "scrollX": true,
          "bPaginate": false,
        ordering: false,
        serverSide: true,
        
        lengthMenu: false,
         columnDefs: [
            { width: '10%', targets: 0 },
            { width: '30%', targets: 1 },
            { width: '30%', targets: 2 },
            { className: "dt-body-left", "targets": [ 0,1,2] },
    { className: "dt-body-right", "targets": [] }
        ],
        fixedColumns: true,
         ajax: {
            url:"{!! route('get-customer-general-documents-common') !!}",
            data: function(data) { data.id = id } ,
            },
        columns: [
            { data: 'description', name: 'description' },
            { data: 'file_name', name: 'file_name' },
            { data: 'date', name: 'date' },
              ]
    });
});

$(document).on("change",".billingSelectFocus",function(){
  if($(this).attr('name') == 'billing-select')
      {
        var thisPointer=$(this);
        // alert(thisPointer.val());
        // var new_select_value = $("option:selected", this).html();
        // saveCustData(thisPointer, thisPointer.attr('name'), thisPointer.val() , new_select_value);
        showBillingRecord(thisPointer.val());
      }
});

function showBillingRecord(billing_id){
  var cust_detail_id= "{{$customer->id}}";

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
        method: "post",
        url: "{{ url('common/show-single-billing-record-common') }}",
        dataType: 'json',
        data: {billing_id:billing_id,cust_detail_id:cust_detail_id},
        beforeSend: function(){
          // shahsky here
          $('#loader_modal').modal('show');

        },
        success: function(data)
        {
          $('#loader_modal').modal('hide');

          console.log(data);
          if(data.error == false)
           {
              $("#billing-primary-contact").html(data.billingCustomer.billing_phone != null ? data.billingCustomer.billing_phone : '--');  
              $('input[name=billing_phone]').val(data.billingCustomer.billing_phone != null ? data.billingCustomer.billing_phone : '--');

              $("#billing-address").html(data.billingCustomer.billing_address != null ? data.billingCustomer.billing_address : '--'); 
              $('input[name=billing_address]').val(data.billingCustomer.billing_address != null ? data.billingCustomer.billing_address : '--');

              $("#billing-title").html(data.billingCustomer.title != null ? data.billingCustomer.title : '--'); 
              $('input[name=title]').val(data.billingCustomer.title != null ? data.billingCustomer.title : '--');

              $("#cell_number").html(data.billingCustomer.cell_number != null ? data.billingCustomer.cell_number : '--'); 
              $('input[name=cell_number]').val(data.billingCustomer.cell_number != null ? data.billingCustomer.cell_number : '--');

              if(data.billingCustomer.is_default == 1){
              $("#billing_default").html('Yes');
              }else{
              $("#billing_default").html('No');
               


              }



              $("#billing-contact_name").html(data.billingCustomer.billing_contact_name != null ? data.billingCustomer.billing_contact_name : '--'); 
              $('input[name=billing_contact_name]').val(data.billingCustomer.billing_contact_name != null ? data.billingCustomer.billing_contact_name : '--');

              $("#billing-company").html(data.billingCustomer.company_name != null ? data.billingCustomer.company_name : '--'); 
              $('input[name=company_name]').val(data.billingCustomer.company_name != null ? data.billingCustomer.company_name : '--');

              $("#billing-email").html(data.billingCustomer.billing_email != null ? data.billingCustomer.billing_email : '--'); 
              $('input[name=billing_email]').val(data.billingCustomer.billing_email != null ? data.billingCustomer.billing_email : '--');

              $("#billing-fax").html(data.billingCustomer.billing_fax != null ? data.billingCustomer.billing_fax : '--'); 
              $('input[name=billing_fax]').val(data.billingCustomer.billing_fax != null ? data.billingCustomer.billing_fax : '--');

              $("#billing-city").html(data.billingCustomer.billing_city != null ?data.billingCustomer.billing_city:'N.A');
              $('input[name=billing_city]').val(data.billingCustomer.billing_city);
              $("#billing-statee").html(data.userState?data.userState:'N.A');
              var option = '<option value='+data.billingCustomer.billing_state+' selected> '+data.userState+' </option>';

              $("#billing-state").append(option);
              $("#billing-phone-s").html(data.billingCustomer.billing_phone); 

               $("#billing-zip").html(data.billingCustomer.billing_zip != null ? data.billingCustomer.billing_zip : '--'); 
              $('input[name=billing_zip]').val(data.billingCustomer.billing_zip != null ? data.billingCustomer.billing_zip : '--');
              // $('input[name=]').val(data.shippingCustomer.shipping_city);

              // $('input[name=reference_number]').val(data.customer.reference_number);

              // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              // return false;
            } 
          
        },

      });

}

</script>
@stop