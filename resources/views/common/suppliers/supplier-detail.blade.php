@extends($layout.'.layouts.layout')


@section('title','Supplier Detail | Supply Chain')

@section('content')
@php
use App\Models\Common\ProductCategory;
use App\Models\Common\SupplierCategory;
use Carbon\Carbon;
@endphp
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
.selectDoubleClick, .inputDoubleClick .inputDoubleClickContacts{
  font-style: italic;
}
</style>

{{-- Content Start from here --}}
<!-- New Design Starts Here -->
<!-- Right Content Start Here -->
<div class="right-content pt-0 pl-0 pr-0">

<div class="row mb-3">

<div class="col-lg-12 headings-color mb-2">

<div class="row">

<div class="col-lg-1">
@if(@$supplier->logo != Null && file_exists( public_path() . '/uploads/sales/customer/logos/' . @$supplier->logo))
<div class="logo-container">
<img src="{{asset('public/uploads/sales/customer/logos/'.$supplier->logo)}}" style="border-radius:50%;height:68px;" alt="logo" class="img-fluid lg-logo">
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
<a href="#" class="icon" title="Supplier Profile" data-toggle="modal" data-target="#uploadModal">
  <i class="fa fa-camera"></i>
</a>
</div>
</div>
@endif
</div>
<div class="col-lg-9 p-0">
<h5 class="fontbold headings-color mb-0 mt-4">Supplier Detail Page</h5>
</div>





{{--<div class="col-lg-1 offset-7 mr-0 mb-0 mt-4 ">
  <a href="{{ route('list-of-suppliers') }}" class="pull-right">
    <img src="{{asset('public/site/assets/backend/img/back_03.png')}}">
  </a>
</div>--}}
</div>

</div>




<div class="col-lg-12 headings-color">
<div class="row">

  <div class="col-lg-6">
  <div class="bg-white h1-00">
  <table id="example" class="table-responsive table sales-customer-table dataTable const-font" style="width: 100%;">
    <tbody>
      
    <input type="hidden" name="supplier_id_detail_page" id="supplier_id_detail_page" value="{{$id}}">

    <tr>
      <td class="fontbold"><div style="width: 150px;">Reference # <b style="color: red;">*</b></div></td>
      <td> 
        <span class="m-l-15" id="reference_number"  data-fieldvalue="{{@$supplier->reference_number}}">
        {{(@$supplier->reference_number!=null)?@$supplier->reference_number:'N/A'}}
        </span>

        <input type="text"  name="reference_number" class="d-none" value="{{(@$supplier->reference_number!=null)?$supplier->reference_number:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">{{$global_terminologies['company_name']}} <b style="color: red;">*</b></td>
      <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="company"  data-fieldvalue="{{@$supplier->company}}">
        {{(@$supplier->company!=null)?@$supplier->company:'N/A'}}
        </span>

        <input type="text" name="company" class="fieldFocus d-none" value="{{(@$supplier->company!=null)?$supplier->company:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Email <b style="color: red;">*</b></td>
      <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="email"  data-fieldvalue="{{@$supplier->email}}">
        {{(@$supplier->email!=null)?@$supplier->email:'N/A'}}
        </span>

        <input type="email" name="email" class="fieldFocus d-none" value="{{(@$supplier->email!=null)?$supplier->email:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Primary Contact <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="phone"  data-fieldvalue="{{@$supplier->phone}}">
        {{(@$supplier->phone!=null)?@$supplier->phone:'N/A'}}
        </span>

        <input type="text" name="phone" class="fieldFocus d-none" value="{{(@$supplier->phone!=null)?$supplier->phone:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Addresses <b style="color: red;">*</b></td>
      <td class="">
        <span class="m-l-15 inputDoubleClick" id="address_line_1"  data-fieldvalue="{{@$supplier->address_line_1}}">
          {{(@$supplier->address_line_1!=null)?@$supplier->address_line_1:'N/A'}}
        </span>
        <input type="text" name="address_line_1" class="fieldFocus d-none" value="{{(@$supplier->address_line_1!=null)?$supplier->address_line_1:''}}">

        <br>
        @if($supplier->address_line_2 != NULL)
        <span class="m-l-15 inputDoubleClick" id="address_line_2"  data-fieldvalue="{{@$supplier->address_line_2}}">
        {{$supplier->address_line_2}}
        </span>

        <input type="text" name="address_line_2" class="fieldFocus d-none" value="{{(@$supplier->address_line_2!=null)?$supplier->address_line_2:''}}">
        @endif
      </td>
    </tr>

    <tr>
      <td class="fontbold">Country <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="country"  data-fieldvalue="{{@$supplier->getcountry->id}}">
          {{$supplier->country != null ? @$supplier->getcountry->name : 'Select Country'}}
        </span>

        <select name="country" class="selectFocus form-control prod-category d-none">
        <option>Choose Country</option>
        @if($countries->count() > 0)
        @foreach($countries as $country)
        <option {{ (@$country->name == @$supplier->getcountry->name ? 'selected' : '' ) }} value="{{ $country->id }}">{{ $country->name }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}
        </select>
    </td>
    </tr>

    <tr>
      <td class="fontbold">State <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="state"  data-fieldvalue="{{@$supplier->getstate->id}}">
          {{$supplier->state != null ? @$supplier->getstate->name : 'Select State'}}
        </span>

        <select name="state" class="selectFocus form-control country-states d-none" id="state-select">
        <option>Choose State</option>
        @if($states->count() > 0)
        @if($supplier->state != null)
        @foreach($states as $state)
        <option {{ ($state->name == @$supplier->getstate->name ? 'selected' : '' ) }} value="{{ $state->id }}">{{ $state->name }}</option>
        @endforeach
        @else
        @foreach($states as $state)
        <option value="{{ $state->id }}">{{ $state->name }}</option>
        @endforeach
        @endif
        @endif
        {{--<option value="new">Add New</option>--}}
        </select>
      </td>
    </tr>

    <tr>
      <td class="fontbold">City <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="city"  data-fieldvalue="{{@$supplier->city}}">
          {{(@$supplier->city!=null)?@$supplier->city:'N/A'}}
        </span>
        <input type="text" name="city" class="fieldFocus d-none" value="{{(@$supplier->city!=null)?$supplier->city:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Zip Code <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="postalcode"  data-fieldvalue="{{@$supplier->postalcode}}">
          {{(@$supplier->postalcode!=null)?@$supplier->postalcode:'N/A'}}
        </span>
        <input type="text" name="postalcode" class="fieldFocus d-none" value="{{(@$supplier->postalcode!=null)?$supplier->postalcode:''}}">
      </td>
    </tr>

    @if($supplier->secondary_phone != NULL)
    <tr>
      <td class="fontbold text-nowrap">Additional Contacts </td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="additional-contact"  data-fieldvalue="{{@$supplier->secondary_phone}}">
        {{$supplier->secondary_phone}}
        </span>
        <input type="number" name="additional-contact" class="fieldFocus d-none" value="{{(@$supplier->secondary_phone!=null)?$supplier->secondary_phone:''}}">
      </td>
    </tr>
    @endif

    <tr>
      <td class="fontbold">Currency <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="currency_id"  data-fieldvalue="{{@$supplier->currency_id}}">
        {{$supplier->currency_id != null ? @$supplier->getCurrency->currency_name : 'Select Currency'}}
        </span>
        <select name="currency_id" class="selectFocus form-control currency_id d-none">
        <option>Choose Currency</option>
        @if($currencies->count() > 0)
        @foreach($currencies as $currencie)
        <option {{ (@$currencie->id == @$supplier->currency_id ? 'selected' : '' ) }} value="{{ $currencie->id }}">{{ $currencie->currency_name }}</option>
        @endforeach
        @endif
        </select>
      </td>
    </tr>

    <tr>
      <td class="fontbold">Credit Terms <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="credit_term"  data-fieldvalue="{{@$supplier->credit_term}}">
        {{$supplier->credit_term != null ? @$supplier->getpayment_term->title : 'Select Credit Term'}}
        </span>
        <select name="credit_term" class="selectFocus form-control credit-term d-none">
        <option>Choose Credit Term</option>
        @if($paymentTerms->count() > 0)
        @foreach($paymentTerms as $paymentTerm)
        <option {{ (@$paymentTerm->title == @$supplier->getpayment_term->title ? 'selected' : '' ) }} value="{{ $paymentTerm->id }}">{{ $paymentTerm->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}
        </select>
      </td>
    </tr>   

    <tr>
    <td class="fontbold">Categories</td>
    <td>
      <span class="m-l-15" id="category_id"">
      @if($SupplierCat_count > 0)
        <i class="fa fa-edit supplier-cats"></i>
        @foreach($categories as $pCat)
          @php
          $getChild = SupplierCategory::with('supplierCategories')
          ->whereIn('category_id',$pCat->get_Child->pluck('id')->toArray())
          ->where('supplier_id',$id)->get();
          @endphp
          
          @if($getChild->count() > 0)
            <b>{{$pCat->title}}</b>
            <ul>
                @foreach($getChild as $subCat)
                <li>{{$subCat->supplierCategories->title}}</li>
                @endforeach
            </ul>
          @endif
        @endforeach
      @else
        <p>No Categories Found</p>
      @endif
      </span>      
    </td>
    </tr>

    <tr class="d-none">
      <td class="fontbold">Tags</td>
        @php
        $multi_tags = null;
            if($supplier->main_tags != null)
            $multi_tags = explode(',', $supplier->main_tags);    
        @endphp
        <td width="100%">
          @if($multi_tags != null)
        @foreach($multi_tags as $tag)
        <span class="abc">{{@$tag}}</span>  
        @endforeach
        <i class="fa fa-edit supplier-tags"></i>
        @else
        <span class="" style="color:red;">No tags found</span>
        <i class="fa fa-edit supplier-tags"></i>
        @endif
        @php 
        $string='';
        @endphp
         @if($multi_tags != null)
       @foreach($multi_tags as $tag)
       @php $string .=  $tag.','; @endphp
        @endforeach 
        @else
        @php $string = ''; @endphp
        @endif
      <div style="position:relative;">     
      <div class="form-group text-left d-none update-tags">
        <input type="text" value="{{$string}}" id="tag-input" name="main_tags" data-role="tagsinput" class="fieldFocus tag-input form-control form-control-lg " placeholder="Enter Main Tags" style="width: 100%;" />      
      </div>
        <input type="button" class="recived-button d-none update-tag-btn" value="update">
      </div>    
      </td>
  </tr>

  

</tbody>
</table>
</div>
</div>

  <div class="col-lg-6">
    <div class="bg-white h-100 const-font">
      <div class="d-flex justify-content-between p-3">
      <h4>Notes</h4>
      <a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="{{$supplier->id}}"  class="add-notes fa fa-plus" title="Add Note"></a>
    </div>
    <div class="inner-div pl-3 pr-3 pb-5" id="myNotes">
   
      <div class="inner-div-detail p-3">
      @foreach($supplierNotes as $note)
        <div class="para-detail1 bg-white p-3">
          <p>{{@$note->note_description}}</p>
        </div>

        <div class="d-flex justify-content-between pt-2 pb-2">
          <p>by {{@$note->getuser->name}} | {{Carbon::parse(@$note->created_at)->format('M d Y')}}</p>
          <a href="javascript:void(0)" class="deleteNote" data-id="{{$note->id}}"><i class="fa fa-trash" ></i></a>
        </div>
      @endforeach
      </div>
    
    </div>
    </div>
  </div>

  <div class="col-lg-12 d-none">

<h4 class="pb-2 mt-2">Vendor Score Card</h4>

<div class="bg-white table-responsive">
  <table id="example" class=" table  headings-color mb-0 const-font" style="width: 100%;">
    <thead class="sales-coordinator-thead">
      <tr>
        <th>Event Date</th>
        <th>Event Type </th>
        <th>Description</th>
        <th>Points</th> 
      </tr>
    </thead>
    <tbody class="sale-form-page">
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      <tr>
        <td>23/09/2019</td>
        <td> 
          <div>
            <select>
              <option>xyz</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
            </select>
          </div>
        </td>
        <td>abc</td>
        <td>10</td>
      </tr>
      
      <tr>
        <td>    
          <a href="#" class="supplier-detail-plus">+</a>
        </td>
      </tr>
    
    </tbody>
  </table>

  
</div>
</div>

  <div class="col-lg-6">

  <div class="row headings-color mt-2">
    <div class="col-lg-6">
      <h3 class="mb-0">@if(!array_key_exists('supplier_contacts', $global_terminologies)) Supplier Contacts @else {{$global_terminologies['supplier_contacts']}} @endif</h3>
    </div>
    <div class="col-lg-6"></div>
  </div>
  <div class="entriesbg bg-white custompadding customborder">
  <table class="supplier-contact-table table entriestable table-bordered text-center">
    <thead>
      <tr>
        <th>Name</th>
        <th>Surname</th>
        <th>Email</th>
        <th>Telephone</th>
        <th>Position</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
  </div>
  </div>

  <div class="col-lg-6">

  <div class="row headings-color mt-2">
    <div class="col-lg-6">
      <h3 class="mb-0">@if(!array_key_exists('general_document', $global_terminologies)) General Documents @else {{$global_terminologies['general_document']}} @endif</h3>
    </div>
    <div class="col-lg-6"></div>
  </div>
  <div class="entriesbg bg-white custompadding customborder" style="overflow: scroll;">
  <table class="table-general-documents table entriestable table-bordered text-center">
    <thead>
      <tr>
        <th>File Name</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
  </div>
  </div>

</div>
</div>

<div class="col-lg-6 overflow-hidden table-responsive d-none">
<div class="bg-white h-80 p-2">
<!-- <div class="tab-content table-responsive" id="myTabContent">
<div class="tab-pane fade show active p-3" id="salesone" role="tabpanel" aria-labelledby="one-tab"> -->
<table id="example" class="table headings-color const-font" style="width:100%">
  <thead class="sales-coordinator-thead ">           
    <tr style="border-bottom: 2px dashed #eee;">
        <th>Sn # </th>
        <th>Month </th>
        <th>Paid POs</th>
        <th>Unpaid POs</th>
        <th>Due POs</th>                                
    </tr>
  </thead>
  <tbody class="dot-dash">
    <tr>
      <td>1</td>
      <td>January 2019 </td>
      <td>$200.00</td>
      <td>$200.00</td>
      <td>$200.00</td>
    </tr>
     <tr>
      <td>1</td>
      <td>January 2019 </td>
      <td>$200.00</td>
      <td>$200.00</td>
      <td>$200.00</td>
    </tr>
     <tr>
      <td>1</td>
      <td>January 2019 </td>
      <td>$200.00</td>
      <td>$200.00</td>
      <td>$200.00</td>
    </tr>
  </tbody>
</table>


</div>
</div>

</div>
</div> 


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
<!-- New Design Ends Here -->
<!--  Content End Here -->



@endsection

@section('javascript')
<script type="text/javascript">
// to make fields double click editable

var id = $("#supplier_id").val();

$('.supplier-contact-table').DataTable({
   processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:true,
  serverSide: true,
  lengthMenu: [ 100, 200, 300, 400],
  ajax: {
    url:"{!! route('get-common-supplier-contacts') !!}",
    data: function(data) { data.id = id } ,
      },
  columns: 
  [
    { data: 'name', name: 'name' },
    { data: 'sur_name', name: 'sur_name' },
    { data: 'email', name: 'email' },
    { data: 'telehone_number', name: 'telehone_number' },
    { data: 'postion', name: 'postion' },
    { data: 'action', name: 'action' },
  ]
});
// Delete supplier contact


$('.table-general-documents').DataTable({
   processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:true,
  serverSide: true,
  lengthMenu: [ 100, 200, 300, 400],
  ajax: {
    url:"{!! route('get-common-supplier-general-docs') !!}",
    data: function(data) { data.id = id } ,
    },
  columns: 
  [
    { data: 'file_name', name: 'file_name' },
    { data: 'description', name: 'description' },
    { data: 'action', name: 'action' },
  ]
});



</script>
@stop