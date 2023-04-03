@extends('sales.layouts.layout')

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
.supplier_ref {
    width: 15%;
    word-break: break-all;
}

.pf {
    width: 10%;
}

.supplier {
    width: 18%;
}

.description {
    width: 50%;
}

.rsv {
    width: 8%;
}

.pStock {
    width: 8%;
}

.sIcon {
    width: 20px;
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
#select2-billing-state-container{
  height: 25px;
  line-height: 2 !important;
}
.customer-secondary-user-table thead tr th{
    border: 1px solid #eee;
    text-align: center;
}

.customer-secondary-user-table tbody tr td{
    border: 1px solid #eee;
    text-align: center;
}
.secondary-user-delete:hover
{
    color: red;
    transition: 0.5s all;
    cursor: pointer;
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
          <li class="breadcrumb-item"><a href="{{route('list-customer')}}">Customer List</a></li>
          <li class="breadcrumb-item active">Customer Details</li>
      </ol>
  </div>
</div>
<!-- New Design-->
<!-- Right Content Start Here -->
<div class="right-content pt-0">
<input type="hidden" id="cus_id" value="{{$customer->id}}">
<!-- upper section start -->
<div class="row mb-4">
<!-- left Side Start -->

<div class="col-lg-12 col-md-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1 col-md-1 col-4">
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
  <img src="{{asset('public/uploads/sales/customer/logos/profileImg.png')}}" alt="Avatar" class="image cust-image">
  <div class="overlay">
  <a href="#" class="icon" title="User Profile" data-toggle="modal" data-target="#uploadModal">
    <i class="fa fa-camera"></i>
  </a>
  </div>
</div>
  @endif
  </div>
  <div class="col-lg-7 col-md-6 p-0 cust-detail-pg">
<h4 class="mb-0 mt-lg-4 cust-detail-text">Customer Detail Page </h4>
  </div>
  @if(@$customer->status == 0)
   <div class="col-lg-4 col-md-5">
    <button class="btn btn-sm pl-3 pr-3 btn-danger pull-right mt-4 delete_customer ml-2">Discard And Close</button>
     <a href="{{route('save-incom-customer')}}"><button class="btn btn-sm pl-3 pr-3 btn-success mt-4 pull-right save_close_btn">Save And Close</button></a>
  </div>
  @endif

  @if(@$customer->status == 1)
  <div class="col-lg-4 col-md-5">
  <a style="cursor: pointer;" class="pull-right ml-3" href="JavaScript:void(0);" id="upload-div">
  <img src="{{asset('public/icons/upload_icon.png')}}" style="width: 37px; height: auto;margin-top: 11px;" title="Bulk Upload" class="image fldr-img">
  </a>
  <a onclick="backFunctionality()"><button class="btn btn-sm pl-3 pr-3 btn-success mt-3 pull-right save_close_btn save-close-btn">Save And Close</button></a>
</div>
  @endif
</div>
</div>

<div class="col-lg-12 col-md-12 pb-5 upload-div" style="display: none;">
  <div class="bg-white pr-4 pl-4 pt-4 pb-5">

    <ul class="nav nav-tabs">
      <li class="nav-item ">
        <a class="nav-link cut-tab active" data-toggle="tab" href="#tab2">Customer Fixed Prices</a>
      </li>
    </ul>

    <div class="tab-content mt-3">
      <div class="tab-pane active" id="tab2">
        <!-- Can be used for export on common products -->

        <button class="btn btn-info pull-right" id="alreadybtn" >Already Have File</button>
        <a href="{{asset('public/site/assets/sales/quotation/Product_Customer_Fixed_Prices.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtn">Download Example File</span></a>

        <br>
        <div class="upload-price-div" style="display: none;">
          <h3>Upload File</h3>
          <label><strong>Note : </strong>Please use the downloaded file for upload only.</label>
          <form action="{{ route('upload-fix-prices-bulk')}}" class="upload-excel-form" method="post" enctype="multipart/form-data">
            {{csrf_field()}}
            <label for="bulk_import_file">Choose Excel File</label>
            <input type="hidden" name="customer_id" value="{{$customer->id}}">
            <input type="file" class="form-control" name="excel" id="price_excel" accept=".xls,.xlsx" required=""><br>
            <button class="btn btn-info price-upload-btn" id="uploadBtn" type="submit">Upload</button>
          </form>
        </div>

      </div>
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
            <input type="file" class="form-control" name="logo" accept="image/x-png,image/gif,image/jpeg" required="true">
            <span style="color:blue;">(Only png,jpg,jpeg files allowed. Max file size is 2 MB.)</span>
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

<div class="col-lg-6 col-md-6 d-flex flex-column">
  <div class="align-items-center d-flex justify-content-between mb-3">
    <div class="">
      <h3 class="mb-0">Company Information</h3>
    </div>
  </div>

  <div class="bg-white p-3 h-100">
    <table class="table-responsive table sales-customer-table const-font" style="width: 100%;">
      <tbody>
        <tr>
          <td class="fontbold">Reference Name <b style="color:red;">*</b></td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span class="m-l-15 inputDoubleClick" id="reference_name"  data-fieldvalue="{{@$customer->reference_name}}">
          {{(@$customer->reference_name!=null)?@$customer->reference_name:'N/A'}}
          </span>

          <input type="text" autocomplete="nope" name="reference_name" class="fieldFocus d-none" value="{{(@$customer->reference_name!=null)?$customer->reference_name:''}}"><br>
          </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['company_name']}}<b style="color:red;">*</b></td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span class="m-l-15 inputDoubleClick" id="company"  data-fieldvalue="{{@$customer->company}}">
          {{(@$customer->company!=null)?@$customer->company:'N/A'}}
          </span>

          <input type="text" autocomplete="nope" name="company" class="fieldFocus d-none" id="companyCheck" value="{{(@$customer->company!=null)?$customer->company:''}}"><br>
         <span id="company-exists" style="color:red;" class="d-none"> Company already exist please change it </span>
          </td>
        </tr>
        @if($customer->ecommerce_customer == 1)
        <tr>
          <td class="fontbold">First Name <b style="color:red;">*</b></td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span>{{(@$customer->first_name!=null)?@$customer->first_name:'N/A'}}</span>
          </td>
        </tr>
        <tr>
          <td class="fontbold">Last Name <b style="color:red;">*</b></td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span>{{(@$customer->last_name!=null)?@$customer->last_name:'N/A'}}</span>
          </td>
        </tr>
         <!-- <tr>
          <td class="fontbold">Phone # <b style="color:red;">*</b></td>
          <td></td>
          <td class="text-wrap">
          <span>{{(@$customer->phone!=null)?@$customer->phone:'N/A'}}</span>
          </td>
        </tr> -->
        @endif

         <tr>
          <td class="fontbold"> {{$global_terminologies['category']}}<b style="color:red;">*</b></td>
          <td class="empty-td"></td>

          <td class="text-wrap">
            <span class="m-l-15 selectDoubleClick" id="category_id" data-fieldvalue="{{@$customer->category_id}}">
              {{(@$customer->category_id!=null)?@$customer->CustomerCategory->title:'Select'}}
            </span>

            <select name="category_id" class="selectFocus form-control prod-category d-none">

            <option value="">Choose  {{$global_terminologies['category']}}</option>

            @if($categories->count() > 0)
            @foreach($categories as $categry)
            <option {{ (@$customer->category_id == $categry->id ? 'selected' : '' ) }} value="{{ $categry->id }}">{{ $categry->title }}</option>
            @endforeach
            @endif
            </select>
            <!-- <input type="text" name="category_id" class="fieldFocus d-none" value="{{(@$customer->category_id!=null)?@$customer->CustomerCategory->title:'Select'}}"> -->
          </td>
        </tr>

          <tr class="">
          <td class="fontbold">Primary Contact</td>
          <td class="empty-td"></td>
          <td>
          <span class="m-l-15 inputDoubleClick" id="phone"  data-fieldvalue="{{$customer->phone}}">
            {{$customer->phone != null ? $customer->phone : '--'}}
          </span>

          <input type="text" name="phone" id="phone_input" class="fieldFocus d-none" value="{{(@$customer->phone!=null)?$customer->phone:''}}">

          </td>
        </tr>
        @if($customer->secondary_phone != NULL)
         <tr>
          <td class="fontbold text-wrap">Additional Contacts </td>

          <td class="empty-td"></td>
          <td> {{@$customerShipping->secondary_phone}}</td>
        </tr>
        @endif

        <tr class="d-none">
          <td class="fontbold">Address</td>
          <td class="empty-td"></td>
          <td class="text-wrap">
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

          <td class="empty-td"></td>
          <td>
          <span class="m-l-15 inputDoubleClick" id="city"  data-fieldvalue="{{$customer->city}}">
            {{$customer->city}}
          </span>
          <input type="text" autocomplete="nope" name="city" class="fieldFocus d-none" value="{{(@$customer->city!=null)?$customer->city:''}}">

          </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold">Phone #s</td>

          <td class="empty-td"></td>
          <td>{{$customer->phone}}</td>
        </tr>

         <tr>

          <td class="fontbold">Payment Terms <!-- <b style="color:red;">*</b> --> </td>

          <td class="empty-td"></td>
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

          <td class="fontbold">Payment Method <!-- <b style="color:red;">*</b> --> </td>

          <td class="empty-td"></td>
          <td>
           @foreach($paymentTypes as $paymentType)
           @php $find = true; @endphp
            @foreach($customer->customer_payment_types as $type)
          <span class="m-l-15">
            @if($type->get_payment_type->id == $paymentType->id)
            @php $find = false; @endphp
            <input type="checkbox" name="paymentType" checked class="pay-check" value={{@$type->get_payment_type->id}}> {{@$type->get_payment_type->title}}
            @endif
          </span>
          @endforeach
            @if($find)
            <input type="checkbox" name="paymentType" class="pay-check" value="{{@$paymentType->id}}"> {{@$paymentType->title}}
            @endif
            @endforeach
          </td>
        </tr>

        <tr>
          <td class="fontbold">Reference #</td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span class="m-l-15 {{$customer_detail_section == 'customer_reference_code' ? 'inputDoubleClick' : ''}} " id="reference_number"  data-fieldvalue="{{@$customer->reference_number}}">
          {{(@$customer->reference_number!=null)?@$customer->reference_number:'N/A'}}
          </span>

          <input type="text" autocomplete="nope" name="reference_number" class="d-none fieldFocus" value="{{(@$customer->reference_number!=null)?$customer->reference_number:''}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">Created By</td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span class="m-l-15" id="created_by" >
          {{(@$customer->user !=null)?@$customer->user->name:'N/A'}}
          </span>
          </td>
        </tr>

        @php
        Auth::user()->role_id == 1 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11 ? $edit = 'selectDoubleClick' : $edit = '';
        @endphp

        <tr>
          <td class="fontbold">Primary Sales Person<b style="color:red;">*</b></td>
          <td class="empty-td"></td>

          <td class="text-wrap">
            <span class="m-l-15 {{$edit}}" id="primary_sale_id" data-fieldvalue="{{@$customer->primary_sale_id}}">
              {{(@@$customer->primary_sale_person !=null)?@@$customer->primary_sale_person->name:'Select'}}
            </span>

            <select name="primary_sale_id" class="selectFocus form-control prod-category d-none">
            <option>Choose Primary Sales Person</option>
            @if($sales_or_coordinators->count() > 0)
            @foreach($sales_or_coordinators as $user)
            <option {{ (@$customer->primary_sale_id == $user->id ? 'selected' : '' ) }} value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
            @endif
            </select>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Secondary Sales Person</td>
          <td class="empty-td"></td>

          <td class="text-wrap">
            <span class="m-l-15 {{$edit}}" id="secondary_sale_id" data-fieldvalue="{{@$customer->secondary_sale_id}}">
              {{count($customer->CustomerSecondaryUser)>=1  ? 'Add More':'Add New'}}
            </span>

            <select name="secondary_sale_id" class="selectFocus form-control prod-category d-none">
            <option value="">Choose Secondary Sales Person</option>
            @if($sales_or_coordinators->count() > 0)
            @foreach($sales_or_coordinators as $user)
            <option {{ (@$customer->secondary_sale_id == $user->id ? 'selected' : '' ) }} value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
            @endif
            </select>
          </td>
          <td><button class="btn add-btn btn-color pull-left add-cust-fp-btn" type="button" title="view Secondary Sale Persons" id="Show-Secondary-Suppliers"><i class="fa fa-eye"></i></button>
          </td>
        </tr>

          <tr>
          <td class="fontbold">Language</td>
          <td class="empty-td"></td>

          <td class="text-wrap">
            <span class="m-l-15 inputDoubleClick" id="language" data-fieldvalue="{{@$customer->language}}">
              {{(@$customer->language == 'en') ? 'English' : 'Thai'}}
            </span>

            <select name="language" class="selectFocus form-control prod-category d-none">
            <option>Choose Language</option>
              <option value="en">English</option>
              <option value="thai">Thai</option>
            </select>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Customer Credit Limit</td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span class="m-l-15 inputDoubleClick" id="customer_credit_limit"  data-fieldvalue="{{@$customer->customer_credit_limit}}">
          {{(@$customer->customer_credit_limit!=null)?number_format(@$customer->customer_credit_limit,2,'.',','):'N/A'}}
          </span>

          <input type="number" autocomplete="nope" name="customer_credit_limit" class="fieldFocus d-none" value="{{(@$customer->customer_credit_limit!=null)?$customer->customer_credit_limit:''}}"><br>
          </td>
        </tr>

        <tr>
          <td class="fontbold">Discount</td>
          <td class="empty-td"></td>
          <td class="text-wrap">
          <span class="m-l-15 inputDoubleClick" id="discount"  data-fieldvalue="{{@$customer->discount}}">
          {{(@$customer->discount!=null)?number_format(@$customer->discount,2,'.',','):'N/A'}}
          </span>

          <input type="number" autocomplete="nope" name="discount" class="fieldFocus d-none" value="{{(@$customer->discount!=null)?$customer->discount:''}}"><br>
          </td>
        </tr>
      </tbody>
    </table>

  </div>


</div>


<div class="col-lg-6 col-md-6 d-flex flex-column">

  <div class="align-items-center d-flex justify-content-between mb-3 cust-fixed-prices-div">
      <div class=""><h3 class="mb-0">Customer Fixed Prices</h3></div>
      <div class="">
      {{--<button class="btn add-btn btn-color pull-right" data-toggle="modal" data-target="#addCustFixedPriceModal"><i class="fa fa-plus"></i> Add</button>--}}
      <button class="btn add-btn btn-color pull-right add-cust-fp-btn " type="button" title="Add Customer Fixed Prices" id="add-cust-fp-btn"><i class="fa fa-plus"></i> Add</button>
   @if(@$customer->productCustomerFixedPrice->count() > 5)
      <a href="{{url('sales/get-customer-product-fixed-prices/'.@$customer->id)}}" class="btn add-btn btn-color pull-right mr-1" id="viewAllFixedPrices">View All</a>
      @endif
      </div>
    </div>

<div class="bg-white p-2 table-responsive ">

  <table class="table headings-color const-font table-bordered text-center cust-fixed-prices-tbl" style="width:100%">
    <thead class="sales-coordinator-thead">
      <tr>
        <th>{{$global_terminologies['our_reference_number'] }}</th>
        <th>{{$global_terminologies['product_description']}}</th>
        <th>Price</th>
        <th>Customer Price</th>
        <th>Discount %</th>
        <th>Price After Discount</th>
        <th>Expiration Date</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody class="">
    @if($ProductCustomerFixedPrice->count() > 0)
    @foreach($ProductCustomerFixedPrice as $item)
      <tr id="cust-fixed-price-{{$item->id}}" style="border:1px solid #eee;">
        <td align="left">{{@$item->products->refrence_code}}</td>
        <td align="left">{{@$item->products->short_desc}}</td>

          @php
          $getCustomer = $customer;
          if($getCustomer->category_id != null) {
            $ctpmargin = \App\Models\Common\CustomerTypeProductMargin::where('product_id',@$item->products->id)->where('customer_type_id',$getCustomer->category_id)->first();
          } else {
            $ctpmargin = \App\Models\Common\CustomerTypeProductMargin::where('product_id',@$item->products->id)->first();
          }

          $salePrice = @$item->products->selling_price+(@$item->products->selling_price*(@$ctpmargin->default_value/100));
          $formated_value = number_format($salePrice ,2,'.',',');
        @endphp
        <td align="right">{{@$formated_value}}</td>
        <td align="right">
        <span class="m-l-15 selectDoubleClick" id="fixed-price"  data-fieldvalue="{{@$item->fixed_price}}">{{(@$item->fixed_price!=null)?number_format(@$item->fixed_price,2,'.',','):'N.A'}}</span>
        <input type="number" name="fixed-price" class="productFixed d-none" data-id="{{@$item->products->id}}" value="{{(@$item->fixed_price!=null)?number_format($item->fixed_price,2,'.',''):''}}">
        </td>
        <td align="right">
        <span class="m-l-15 selectDoubleClick" id="discount"  data-fieldvalue="{{@$item->discount}}">{{(@$item->discount!=null)?number_format(@$item->discount,2,'.',','):'N.A'}}</span>
        <input type="number" name="discount" class="discount d-none" data-id="{{@$item->products->id}}" value="{{(@$item->discount!=null)?number_format($item->discount,2,'.',''):''}}">
        </td>
        <td align="right">{{(@$item->price_after_discount!=null)?number_format(@$item->price_after_discount,2,'.',','):'N.A'}}</td>
          <td align="left">
        <span class="m-l-15 selectDoubleClick" id="expiration-date"  data-fieldvalue="{{@$item->expiration_date}}">
              {{(@$item->expiration_date!=null)?@$item->expiration_date:'N.A'}}
        </span>
        <input type="date" name="expiration-date" class="productFixed d-none" data-id="{{@$item->products->id}}" value="{{(@$item->expiration_date!=null)?$item->expiration_date:''}}">
        </td>

        <td>
          <a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteCustomerFixedPrice" data-id="{{$item->id}}" title="Void"><i class="fa fa-trash"></i></a>
        </td>
      </tr>
    @endforeach
    @else
      <tr style="border:1px solid #eee;">
        <td colspan="6">No Fixed product Info Found</td>
      </tr>
    @endif
    </tbody>
  </table>
</div>
</div>

{{-- ================== --}}

<div class="col-lg-6 col-md-6 mt-4 h-100">

  <div class="align-items-center d-flex justify-content-between mb-3">
    <div class="">
      <h3 class="mb-0">Contacts</h3>
    </div>
    <div class="">
      <!-- <button class="btn add-btn btn-color pull-right mb-0" id="add-customer-contacts"><i class="fa fa-plus"></i> Add</button> -->
    <button class="btn add-btn btn-color pull-right mb-0 " id="add-customer-contacts"><i class="fa fa-plus"></i> Add</button></div>
  </div>
  {{-- style="min-height: 45% !important;height: 270px;" --}}
  <div class="entriesbg bg-white custompadding customborder" >
    <table class="table-contacts table entriestable table-bordered text-center">
      <thead style="width: 40%;">
        <tr>
          <th>Action</th>
          <th>Name</th>
          <th>Surname</th>
          <th>Email</th>
          <th>Telephone</th>
          <th>Position</th>
          <th>Default</th>
        </tr>
      </thead>
    </table>
  </div>


  {{-- <div class="align-items-center d-flex justify-content-between mb-3">
    <div class="">
      <h3 class="mb-0">Notes</h3>
    </div>
    <div class="">

    </div>
  </div> --}}


  <div class="align-items-center d-flex justify-content-between mb-3 mt-4">
    <div class="">
      <h3 class="mb-0">@if(!array_key_exists('general_document', $global_terminologies)) General Documents @else {{$global_terminologies['general_document']}} @endif</h3>
    </div>
    <div class="">
      <button class="btn add-btn btn-color pull-right " data-toggle="modal" data-target="#addDocumentModal"><i class="fa fa-plus"></i> Add</button>
      @if(@$customer->get_customer_documents->count() > 5)
    <a href="{{url('sales/get-customer-documents/'.@$customer->id)}}" class="btn add-btn btn-color pull-right mr-1" id="viewAllDocuments">View All</a>
    @endif
    </div>
  </div>

  {{-- <div class="col-lg-12 col-md-12 p-0 mt-4">
  <div class="row">
    <div class="col-lg-8 col-md-10">
    <h3>@if(!array_key_exists('general_document', $global_terminologies)) General Documents @else {{$global_terminologies['general_document']}} @endif</h3>
    </div>
    <div class="col-lg-4 col-md-2">
      <button class="btn add-btn btn-color pull-right" data-toggle="modal" data-target="#addDocumentModal"><i class="fa fa-plus"></i> Add</button>
      @if(@$customer->get_customer_documents->count() > 5)
    <a href="{{url('sales/get-customer-documents/'.@$customer->id)}}" class="btn add-btn btn-color pull-right mr-1" id="viewAllDocuments">View All</a>
    @endif
    </div>
  </div>
  </div> --}}

  <div class="entriesbg bg-white custompadding customborder" style="">
    <div class="mb-2">
    <table class="table entriestable table-bordered table-general-documents text-center" style="border-bottom: none;width: 100%">
      <thead>
        <tr>
          <th>Description</th>
          <th>File Name</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>
    </table>
    </div>
  </div>
</div>
<div class="col-lg-6 col-md-6 mt-4">



    <div class="align-items-center d-flex justify-content-between mb-3">
      <div class="">
        <h3 class="mb-0">Company Addresses</h3>
      </div>
      <div class="">
        <a href="javascript:void(0)" data-toggle="modal" data-target="#add_billing_detail_modal" class="btn add-btn btn-color pull-right mb-0 " title="Add Address"><i class="fa fa-plus"></i> Add</a>
      </div>
    </div>



  <div class="bg-white p-3 h-100" style="height: 94% !important;">
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
          <td class="fontbold">Reference Name <b style="color:red;">*</b></td>
          <td> <span id="billing-title" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->title}}"> {{(@$customerBilling->title!=null)?@$customerBilling->title:'N.A'}}</span>
          <input type="text" autocomplete="nope" name="title" class="billing-fieldFocus d-none" value="{{(@$customerBilling->title!=null)?@$customerBilling->title:''}}">
          <input type="checkbox" name="show_title" class="ml-4 show_title" title="Show on print outs" {{@$customerBilling->show_title == 1 ? 'checked' : ''}} data-id="{{@$customerBilling->id}}">
        </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold">{{$global_terminologies['company_name']}}</td>
          <td> <span id="billing-company" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->company_name}}"> {{(@$customerBilling->company_name!=null)?@$customerBilling->company_name:'N.A'}}</span>
          <input type="text" autocomplete="nope" name="company_name" class="billing-fieldFocus d-none" value="{{(@$customerBilling->company_name!=null)?@$customerBilling->company_name:''}}">

        </td>
        </tr>

        <tr class="d-none">
          <td class="fontbold">Billing Contact Name</td>
          <td> <span id="billing-contact_name" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_contact_name}}"> {{(@$customerBilling->billing_contact_name!=null)?@$customerBilling->billing_contact_name:'N.A'}}</span>
          <input type="text" autocomplete="nope" name="billing_contact_name" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_contact_name!=null)?@$customerBilling->billing_contact_name:''}}">

        </td>
        </tr>

        <tr>
          <td class="fontbold">Phone No.<b style="color:red;">*</b></td>
          <td> <span id="billing-primary-contact" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_phone}}"> {{@$customerBilling->billing_phone != NULL ? @$customerBilling->billing_phone : 'N.A'}}</span>
          <input type="text" autocomplete="nope" name="billing_phone" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_phone!=null)?@$customerBilling->billing_phone:''}}">

        </td>
        </tr>

        <tr>
          <td class="fontbold">Moblie No.</td>

          <td><span id="cell_number" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->cell_number}}">{{@$customerBilling->cell_number != null ? @$customerBilling->cell_number : 'N.A'}}</span>
             <input type="text" autocomplete="nope" name="cell_number" class="billing-fieldFocus d-none" value="{{(@$customerBilling->cell_number!=null)?@$customerBilling->cell_number:''}}">
          </td>
        </tr>

        @if(@$customerBilling->secondary_phone != NULL)
        <tr>
          <td class="fontbold ">Additional Contacts </td>
          <td> <span id="billing-secondary-contact" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->secondary_phone}}"> {{@$customerBilling->secondary_phone}} </span></td>
        </tr>
        @endif

        <tr>
          <td class="fontbold">Tax ID<b style="color:red;">*</b></td>

          <td><span id="tax_id" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->tax_id}}">{{@$customerBilling->tax_id != null ? @$customerBilling->tax_id : 'N.A'}}</span>
             <input type="text" autocomplete="nope" name="tax_id" class="billing-fieldFocus d-none" value="{{(@$customerBilling->tax_id!=null)?@$customerBilling->tax_id:''}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">Email<b style="color:red;">*</b></td>
          <td> <span id="billing-email" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_email}}"> {{(@$customerBilling->billing_email!=null)?@$customerBilling->billing_email:'N.A'}}</span>
          <input type="email" autocomplete="nope" name="billing_email" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_email!=null)?@$customerBilling->billing_email:''}}">

        </td>
        </tr>

        <tr class="">
          <td class="fontbold">Fax</td>
          <td class=""> <span id="billing-fax" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_fax}}"> {{(@$customerBilling->billing_fax!=null)?@$customerBilling->billing_fax:'N.A'}} </span>
          <input type="number" autocomplete="nope" name="billing_fax" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_fax!=null)?@$customerBilling->billing_fax:''}}">
        </td>
        </tr>

        <tr>
          <td class="fontbold">Address<b style="color:red;">*</b></td>
          <td class=""> <span id="billing-address" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_address}}"> {{@$customerBilling->billing_address != NULL ? @$customerBilling->billing_address : 'N.A'}} </span>
          <input type="text" autocomplete="nope" name="billing_address" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_address!=null)?@$customerBilling->billing_address:''}}">
        </td>
        </tr>

         <tr>
          <td class="fontbold">District<b style="color:red;">*</b></td>
          <td><span id="billing-city" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_city}}">
            {{(@$customerBilling->billing_city!=Null)?@$customerBilling->billing_city:'N.A'}}

          </span>
          <input type="text" autocomplete="nope" name="billing_city" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_city!=null)?@$customerBilling->billing_city:''}}">

        </td>
        </tr>

         <tr>
          <td class="fontbold">City<b style="color:red;">*</b></td>
          <td><span id="billing-statee" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_state}}">
             @if(@$customer->language == 'en')
            {{(@$customerBilling->billing_state!=Null)?@$customerBilling->getstate->name:'N.A'}}
            @else
             {{@$customerBilling->getstate->thai_name != null ? @$customerBilling->getstate->thai_name : (@$customerBilling->getstate->name !== null ? @$customerBilling->getstate->name : '--')}}
            @endif
          </span>
         <div class="d-none state-div">
         <select class="form-control state-tags update_state" id="billing-state" name="billing_state">
                <option selected="selected">Select City</option>
                @foreach($states as $state)
                @if(@$customer->language == 'en')
                @if($state->id == @$customerBilling->getstate->id)
                <option value="{{$state->id}}" selected="true">{{$state->name}}</option>
                @else
                <option value="{{$state->id}}">{{$state->name}}</option>
                @endif
                @else
                   @if($state->id == @$customerBilling->getstate->id)
                    <option value="{{$state->id}}" selected="true">{{$state->thai_name !== null ? @$state->thai_name : @$state->name}}</option>
                  @else
                  <option value="{{$state->id}}">{{$state->thai_name !== null ? @$state->thai_name : @$state->name}}</option>
                  @endif
                @endif
                @endforeach
              </select>
              </div>

        </td>
        </tr>

        <tr>
        <td class="fontbold ">Country</td>

        <td class="" width="50%">
         <span id="billing-country" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_country}}">
          @if(@$customer->language == 'en')
          {{(@$customerBilling->billing_country!=null)?@$customerBilling->getcountry->name:'N.A'}}
          @else
          {{@$customerBilling->getcountry->thai_name != null ? @$customerBilling->getcountry->thai_name : @$customerBilling->getcountry->name}}
          @endif </span>
          <select class="billingSelectFocus form-control d-none" style="width: 100%;min-height: 25px;height: 25px;" id="country_id" name="billing_country">
          <option value="{{@$customerBilling->id}}" disabled="true">Select Country</option>
          @if(@$customer->language == 'en')
            <option value="217" id="217" {{ (@$customerBilling->billing_country == '217')? "selected='true'":" " }}>Thailand</option>
            <option value="119" id="119" {{ (@$customerBilling->billing_country == '119')? "selected='true'":" " }}>Laos</option>
            <option value="150" id="150" {{ (@$customerBilling->billing_country == '150')? "selected='true'":" " }}>Myanmar</option>
             <option value="98" id="98" {{ (@$customerBilling->billing_country == '98')? "selected='true'":" " }}>Hong Kong</option>
             <option value="102" id="102" {{ (@$customerBilling->billing_country == '102')? "selected='true'":" " }}>Indonesia</option>
             @else
               <option value="217" id="217" {{ (@$customerBilling->billing_country == '217')? "selected='true'":" " }}>ประเทศไทย</option>
            <option value="119" id="119" {{ (@$customerBilling->billing_country == '119')? "selected='true'":" " }}>
            ลาว</option>
            <option value="150" id="150" {{ (@$customerBilling->billing_country == '150')? "selected='true'":" " }}>พม่า</option>
             <option value="98" id="98" {{ (@$customerBilling->billing_country == '98')? "selected='true'":" " }}>
              ฮ่องกง</option>
              <option value="102" id="102" {{ (@$customerBilling->billing_country == '102')? "selected='true'":" " }}>
              อินโดนีเซีย</option>
             @endif
          </select>
        </td>

        </tr>




        <tr class="">
          <td class="fontbold">Zip Code<b style="color:red;">*</b></td>
          <td class=""> <span id="billing-zip" class="m-l-15 inputDoubleClick" data-fieldvalue="{{@$customerBilling->billing_zip}}"> {{(@$customerBilling->billing_zip!=null)?@$customerBilling->billing_zip:'N.A'}} </span>
          <input type="number" autocomplete="nope" name="billing_zip" class="billing-fieldFocus d-none" value="{{(@$customerBilling->billing_zip!=null)?@$customerBilling->billing_zip:''}}">
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
        <tr>
          <td class="fontbold">Is Default Shipping Address</td>
          <td>
            <input type="checkbox" id="is_default_shipping" class="is_default_shipping" name="is_default_shipping" {{@$customerBilling->is_default_shipping == 1 ? 'checked' : ''}}>
          </td>
        </tr>
        <tr class="@if(@$search_array_config['status'][0] == 0) d-none @endif">
          <td class="fontbold">Create New Pin<b style="color:red;">*</b></td>
          <td class="">
          <form id="pin_create_form" class="d-flex align-items-center">
          <input type="text" name="pincode" id="pincode" maxlength="4" pattern="^[0-9]{4}$" required/>
          <input type="hidden" name="ecom_customer_id" id="ecom_customer_id" value="{{$customer->ecommerce_customer_id}}" />

          <!-- <input type="button" id="submit_pin" class="ml-1" style="padding: 2px 8px;font-size: 12px;cursor: pointer;"/> -->

          <a id="submit_pin" class="btn add-btn btn-color pull-right mb-0" title="Submit"><i class="fa fa-plus"></i></a>

        </form>
        </td>
        </tr>

        @if(@$customerBilling->is_default == 1)
          @php $del_add = "d-none"; @endphp
        @else
          @php $del_add = ""; @endphp

        @endif
        <tr class="delete-add_row {{$del_add}}">
          <td class="fontbold">Delete</td>

          <td><span id="">
          <a href="javascript:void(0)" data-id="{{@$customerBilling->id}}" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn delete-company-address" title="Delete Address"><i class="fa fa-trash"></i></a>
          </span></td>

        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- left Side Start -->
<div class="col-lg-6 col-md-6 mt-lg-4 order-div-mrgin">
  {{-- <h4>Orders</h4> --}}

  <div class="align-items-center d-flex justify-content-between mb-3">
    <div class="">
      <h3 class="mb-0">Orders</h3>
    </div>
  </div>

  <div class="bg-white p-2">
    <div>
      <h5 class="pt-1 pb-1">Total Outstanding : {{number_format($outstandings,2,'.',',')}}</h5>
    </div>
  <table class="table entriestable  headings-color const-font table-bordered text-center" style="width:100%">
    <thead class="sales-coordinator-thead">
      <tr>
        <th>Month</th>
        <th>Total Order</th>
        <th>Paid</th>
        <th>Unpaid</th>
      </tr>
    </thead>
    <tbody class="">
      @if($orders_data->count() > 0)
      @foreach($orders_data as $order)
      <tr style="border: 1px solid #eee;">
        @php
            $date = strtotime($order[0]->converted_to_invoice_on);
            $new_date = date('Y-m-d',$date);
        @endphp
        <td align="left">{{date('M, Y',strtotime($new_date))}}</td>
        <td align="right"><a target="_blank" href="{{url('sales/get_customer_report/'.@$order[0]->customer_id.'/'.@$new_date)}}">{{number_format($order->sum('total_amount'),2,'.',',')}}</a></td>
        <td align="right"><a target="_blank" href="{{url('sales/get_customer_invoices/'.@$order[0]->customer_id.'/'.@$new_date.'/24')}}">{{number_format($order->where('status',24)->sum('total_paid'),2,'.',',')}}</a></td>
        <td align="right"><a target="_blank" href="{{url('sales/get_customer_invoices/'.@$order[0]->customer_id.'/'.@$new_date.'/11')}}">{{number_format($order->sum('total_amount') - $order->where('status',24)->sum('total_paid'),2,'.',',')}}</a></td>
      </tr>
      @endforeach
      @else
      <tr style="border: 1px solid #eee;">
        <td colspan="4" align="center">No Orders Found</td>
      </tr>
      @endif
    </tbody>
  </table>
  </div>

</div>

<!-- Product fixed Price  -->
<div class="col-lg-6 col-md-6 d-flex flex-column mt-4 notes-div-mrgin">
  <div class="align-items-center d-flex justify-content-between mb-3">
    <div class="">
      <h3 class="mb-0">Notes</h3>
    </div>
    <div class="">
      <!-- <button class="btn add-btn btn-color pull-right mb-0" id="add-customer-contacts"><i class="fa fa-plus"></i> Add</button> -->
       <a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="{{$customer->id}}"  class="add-notes btn add-btn btn-color pull-right mb-0 " title="Add Note"><i class="fa fa-plus"></i> Add</a>
    </div>
  </div>

    <div class="bg-white const-font pt-3">

        <div class="inner-div pl-3 pr-3 pb-5" id="myNotes">

          <div class="inner-div-detail p-3">
            @if($customerNotes != null)
            @if($customerNotes->count() > 0)
          @foreach($customerNotes as $note)
            <div class="para-detail1 bg-white p-3">
              <p>{{@$note->note_description}}</p>
            </div>

            <div class="d-flex justify-content-between pt-2 pb-2">
              <p class="mb-0">by {{@$note->getuser->name}} | {{Carbon::parse(@$note->created_at)->format('M d Y')}}</p>
              <a href="javascript:void(0)" class="deleteNote" data-id="{{$note->id}}"><i class="fa fa-trash" ></i></a>
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

<div class="col-lg-6 col-md-6 d-flex flex-column mt-4 order-doc-div-mrgin">
  <div class="mb-3">
  <h3 class="mb-0">Orders Documents</h3>
</div>
<div class="bg-white p-2 table-responsive">

  <table class="table entriestable  headings-color const-font text-center table-bordered bg-white" style="width:100%">
    <thead class="sales-coordinator-thead">
      <tr>
        <th>Order Number</th>
        <th>Order Date</th>
        <th>Order Completed Data</th>
        <th>Documents</th>
      </tr>
    </thead>

    <tbody class="">
    @if($customer_order_docs->count() > 0)
    @foreach($customer_order_docs as $item)
      <tr style="border:1px solid #eee;">
       <!--  <td align="left">{{@$item->get_order->customer->primary_sale_person->get_warehouse->order_short_code
}}{{@$item->get_order->customer->CustomerCategory->short_code}}{{$item->get_order->ref_id}}</td> -->
        <td align="left">
          @if($item->get_order->status_prefix !== null || $item->get_order->ref_prefix !== null)
                {{@$item->get_order->status_prefix.'-'.$item->get_order->ref_prefix.$item->get_order->ref_id}}
          @else
                {{@$item->get_order->customer->primary_sale_person->get_warehouse->order_short_code.@$item->get_order->customer->CustomerCategory->short_code.@$item->get_order->ref_id}}
          @endif
        </td>
        <td align="left">{{Carbon::parse(@$item->get_order->created_at)->format('d/m/Y')}}</td>
        <td align="left">N.A</td>
        <td class="" align="left">
          <a href="{{ asset('public/uploads/documents/quotations/'.$item->file_title) }}" title="{{$item->file_title}}" download="" class="text-center actionicon download" style="cursor: pointer;"><i class="fa fa-download"></i></a>
        </td>
      </tr>
    @endforeach
    @else
      <tr style="border:1px solid #eee;">
        <td colspan="4" align="center">No Order Documents Found</td>
      </tr>
    @endif
    </tbody>
  </table>
</div>
</div>

<div class="col-lg-12 col-md-6 d-flex flex-column mt-4 order-doc-div-mrgin">
  <div class="mb-3">
    <h3 class="mb-0">Customer History</h3>
  </div>
  <div class="bg-white p-2 table-responsive">
    <table class="table entriestable history-table headings-color const-font text-center table-bordered bg-white" style="width:100%">
      <thead class="sales-coordinator-thead">
        <tr>
          <th>User</th>
          <th>Customer</th>
          <th>Date/Time</th>
          <th>Updated From</th>
          <th>Column</th>
          <th>Old Value</th>
          <th>New Value</th>
        </tr>
      </thead>

      <tbody>

        <tr>
          @if ($customer_history->count() != 0)
          @foreach ($customer_history as $history)
            <tr>
              <td>{{@$history->user->name }}</td>
              <td>{{@$cust_histry->customers->reference_name != null ? @$cust_histry->customers->reference_name : '--' }}</td>
              <td>{{$history->created_at->format('d/m/Y')}}</td>
              <td>{{$history->updated_at->format('d/m/Y')}}</td>
              <td>{{$history->column_name}}</td>
              <td>{{$history->old_value}}</td>
              <td>{{$history->new_value}}</td>
            </tr>
          @endforeach
          @else
            <td colspan="7" align="center">No Data Found</td>
          @endif
        </tr>

      </tbody>
    </table>
  </div>
</div>

</div>

{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="addDocumentModal">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">ADD DOCUMENTS</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>


  <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">

    <div class="modal-body">
      <input type="hidden" name="customer_id" value="{{@$customer->id}}">
      <div class="form-group">
        <label for="description">Description:<b style="color:red;">*</b></label>
        <input type="text" class="form-control" id="description" name="description" required="true">
      </div>
      <div class="form-group">
        <label class="pull-left">Select Documents To Upload<b style="color:red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" id="po_docs" name="po_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
        <h6 style="color: red;">(Note*: Max file size is 2 MB)</h6>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary save-doc-btn" id="addDocBtn">Add</button>
    </div>

  </form>

    </div>
  </div>
</div>
{{-- end modal code--}}

<div class="row mb-3 headings-color d-none">
<!-- left Side Start -->
  <div class="col-lg-12 col-md-12 mt-2 mb-1">
    <h3>Company Addresses</h3>
  </div>
<div class="col-lg-12 col-md-12">
  <div class="entriesbg bg-white custompadding customborder">
    <table class="table entriestable table-bordered text-center table-company-addresses table-responsive" style="border-bottom: none;width: 100%">
       <thead>
        <tr>
          <th>Reference Name </th>
          <th>Phone No.</th>
          <th>Moblie No.</th>
          <th>Address</th>
          <th>Tax ID</th>
          <th>Email</th>
          <th>Fax</th>
          <th>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</th>
          <th>City</th>
          <th>Zip</th>
          <th>Is Default</th>
        </tr>
      </thead>
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
              <label for="title"> {{$global_terminologies['company_name']}}:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control billing" id="billing_title" name="billing_title" >
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Billing Contact Name :</label>
              <input required="true" type="text" class="form-control" id="billing_contact_name" name="billing_contact_name1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:<b style="color:red;">*</b></label>
              <input required="true" type="email" class="form-control" id="billing_email" name="billing_email1">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Tax ID:<b style="color: red;">*</b></label>
              <input required="true" type="text" class="form-control" id="tax_id" name="tax_id">
            </div>

            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" id="billing_phone" name="billing_phone">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Moblie No.</label>
              <input required="true" type="text" class="form-control" id="cell_number" name="cell_number">
            </div>

            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" id="billing_address" name="billing_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" id="billing_zip" name="billing_zip">
            </div>


            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_country">Country:</label>
              <select required="true" id="billing_country" class="font-weight-bold form-control-lg form-control selectpicker adding_country" data-live-search="true" title="Select Country" name="billing_country">
                @if(@$customer->language == 'en')
                <option value="217">Thailand</option>
                <option value="119">Laos</option>
                <option value="150">Myanmar</option>
                <option value="98">Hong Kong</option>
                <option value="102">Indonesia</option>
                @else
                  <option value="217" >ประเทศไทย</option>
            <option value="119">ลาว</option>
            <option value="150" >พม่า</option>
             <option value="98"> ฮ่องกง</option>
             <option value="102"> อินโดนีเซีย</option>
                @endif
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="contact_city">District:<b style="color:red;">*</b></label>
              <input type="text" required="true" id="billing_city" class="form-control " name="billing_city">

            </div>

            <div class="form-group col-md-6 customer-state">
              <div>
              <label for="business_state">City:<b style="color:red;">*</b></label><br>
             <!--  <select required="true" id="billing_state" name="billing_state" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select State">
                 <option>choose state</option>
              </select> -->
               <select id="billing_state" class="form-control state-tags state_select" name="state">
                <option selected="selected" value="nostate">Select State</option>
                @foreach($states as $state)
                @if(@$customer->language == 'en')
                <option value="{{$state->id}}">{{$state->name}}</option>
                @else
                <option value="{{$state->id}}">{{$state->thai_name !== null ? @$state->thai_name : @$state->name}}</option>
                @endif
                @endforeach
              </select>
              </div>
            </div>
            <!-- <div class="form-group col-md-6">
              <label for="contact_city">City:<b style="color:red;">*</b></label>
              <input type="text" required="true" id="billing_city" class="form-control " name="billing_city">

            </div> -->

            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control" id="billing_fax" name="billing_fax1">
            </div>

            <div class="form-group col-md-6 mt-4">
            <div class="row">
            <div class="col-lg-7 mt-3">
           <!--  <label for="contact_city">Is Default&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" id="is_default" class=" is_default " name="is_default">
              <input type="hidden" id="is_default_value" class="form-control" name="is_default_value" value='0'> -->
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
                              <div class="form-group d-none">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Note Title" name="note_title">
                              </div>
                              <div class="form-group">
                                <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                                <textarea id="note_description" class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
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

      <div class="form-group" style="margin-top: 10px; margin-bottom: 10px;position: relative;">
          <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
          <input autocomplete="off" type="text" name="prod_name" value="" id="prod_name" class="form-control form-group" placeholder="Search by PF# -Supplier -Product Description(Press Enter)" style="padding-left:30px;">
          <div id="product_name_div_complete">
          </div>
        </div>
      <input type="hidden" name="product" id="product">
      <div class="form-group">
        <label class="pull-left">Price</label>
        <input type="text" class="font-weight-bold form-control-lg form-control" id="default_price" name="default_price" value="" readonly>
      </div>

      <div class="form-group">
        <label class="pull-left">Customer Price <b style="color: red;">*</b></label>
        <input id="customer_price" class="font-weight-bold form-control-lg form-control" placeholder="Define Customer Price Here" name="fixed_price" type="number">
      </div>

      <!-- Sup-846 Discount and Price after discount Field added -->
      <div class="form-group">
        <label class="pull-left">Discount %</label>
        <input id="discount_percent" class="font-weight-bold form-control-lg form-control" placeholder="Define Discount Here" name="discount" type="number">
      </div>
      <div class="form-group">
        <label class="pull-left">Price After Discount</label>
        <input id="price_after_discount" class="font-weight-bold form-control-lg form-control" name="price_after_discount" type="text" readonly>
      </div>
      <!-- Ends here -->

      <div class="form-group">
        <label class="pull-left">Expiration Date</label>
        <input id="expiration_date" class="font-weight-bold form-control-lg form-control" name="expiration_date"  type="date">
      </div>

    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-primary save-fixed-price" id="addCustFixedPriceBtn">Add</button>
    </div>
  </form>

    </div>
  </div>
</div>
{{-- Show Secondary Suppliers --}}
<div class="modal ShowSecondarySalesPerson" id="ShowSecondarySalesPerson">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">Secondary Sale Persons</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>
    <div class="modal-body">
        <table class="table customer-secondary-user-table" width="100%" >
           <thead>
            <tr>
                <th>Sr.No</th>
                <th>Secondary Sales Person</th>
                <th>Remove</th>
            </tr>
        </thead>
        <tbody id="secondarySalesPersonTable">

        </tbody>
        </table>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-sm btn-primary save-fixed-price" data-dismiss="modal">Close</button>
    </div>


    </div>
  </div>
</div>
<!-- Add Shipping Details Modal Ended -->
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

<input type="hidden" name="com_add_id" class="com_add_id">

@endsection

@section('javascript')
@if(Auth::user()->role_id == 6 )
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.inputDoubleClick').removeClass('inputDoubleClick');
      $('.selectDoubleClick').removeClass('selectDoubleClick');
      $('.prodSuppInputDoubleClick').removeClass('prodSuppInputDoubleClick');
      $('.inputDoubleClickFirst').removeClass('inputDoubleClickFirst');
      $('.inputDoubleClickFixedPrice').removeClass('inputDoubleClickFixedPrice');
      $('.selectDoubleClickPM').removeClass('selectDoubleClickPM');
      $('.inputDoubleClickPM').removeClass('inputDoubleClickPM');
      $('.inputDoubleClickContacts').removeClass('inputDoubleClickContacts');
      $('.market_price_check').attr('disabled',true);
      $('.pay-check').attr('disabled',true);
      $('#add-product-image-btn').hide();
      $('#add-cust-fp-btn').hide();
      $('.btn').addClass('d-none');
      $('.default_supplier').addClass('d-none');
      $('#upload-div').addClass('d-none');
    });
  </script>
@endif
<script type="text/javascript">

$(document).on('keyup', '#discount_percent, #customer_price', function (e) {
    // let default_price = $('#product_default_price').val();
    let customer_price = $('#customer_price').val();
    let discount = $('#discount_percent').val();
    // default_price = default_price.replace(/\,/g,'');
    if (customer_price !== '') {
        $('#price_after_discount').val(parseFloat(customer_price - (customer_price * discount)/100).toFixed(2));
    }
    // else{
    //     $('#price_after_discount').val(parseFloat(default_price - (default_price * discount)/100).toFixed(2));
    // }
   });

$('#addDocumentModal').on('hidden.bs.modal', function () {
  $(this).find('form')[0].reset();
});

$('#addCustFixedPriceModal').on('hidden.bs.modal', function () {
  $(this).find('form')[0].reset();
});




$('#add_notes_modal').on('hidden.bs.modal', function () {
  $('#note_description').removeClass("is-invalid");
  $('.invalid-feedback').hide();
});


$('#addCustFixedPriceModal').on('hidden.bs.modal', function () {
  $('#expiration_date').removeClass("is-invalid");
  $('#customer_price').removeClass("is-invalid");
  $('.invalid-feedback').hide();
});



   var role = "{{Auth::user()->role_id}}";
    var show = false;
    if(role == 6){
      show = false;
    }
  $(document).ready(function(){

  $(".state-tags").select2({
    tags: true
  });

  // show customers onclick function
  $(document).on("click","#add-cust-fp-btn", function(){
  var cust_detail_id = "{{$customer->id}}";

  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('get-customer-products-data') }}",
      method: 'post',
      dataType: 'json',
      data: {cust_detail_id:cust_detail_id},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(result){
        $("#loader_modal").modal('hide');
        if(result.success == true)
        {
          $('.categories-dd').html(result.html_string2);
          $("#addCustFixedPriceModal").modal("show");
        }

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }

    });
  });
// shows customer related secondary supplier
$(document).on("click","#Show-Secondary-Suppliers", function(){
  var cust_detail_id = "{{$customer->id}}";
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('CustomerSecondarySalesPersons') }}",
      method: 'post',
      dataType: 'json',
      data: {cust_detail_id:cust_detail_id},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(result){
        $("#loader_modal").modal('hide');
        if(result.success==true)
        {
            $('#secondarySalesPersonTable').empty();
            let counter=0;
            for(let i=0;i<result.customerSecondarySalesPersons.length;i++){
                counter++;
                var row = $('<tr id="rowGenerated"><td>' +counter+ '</td><td>' + result.customerSecondarySalesPersons[i].secondary_sales_persons.name + '</td><td><i class="fa fa-trash secondary-user-delete secondarySalepersonDelete" id='+result.customerSecondarySalesPersons[i].id+' title="Delete"></i></td></tr>');
                $('#secondarySalesPersonTable').append(row);
            }
            // secondarySalesPerson
            // console.log(result.customerSecondarySalesPersons[0]);
        //   $('.categories-dd').html(result.html_string2);
          $("#ShowSecondarySalesPerson").modal("show");
        }

      },
      error: function(request, status, error){
        $("#ShowSecondarySalesPerson").modal('hide');
      }

    });
  });
// delete existing sales person of customer
$(document).on("click",".secondarySalepersonDelete", function(){

    let salesPersonRecordId=this.id;
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
     });
     $.ajax({
      url: "{{ route('deleteSalesPersonRecord') }}",
      method: 'post',
    //   dataType: 'json',
      data: {'salesPersonRecordId':salesPersonRecordId},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(result){
        $("#loader_modal").modal('hide');
        if(result.success==true){
        $("#ShowSecondarySalesPerson").modal('hide');
            toastr.success('Success!', 'Sales Person Deleted',{"positionClass": "toast-bottom-right"});
        }else{
        $("#ShowSecondarySalesPerson").modal('hide');
            toastr.warning('Warning!', 'Sales Person Can Not Be Deleted',{"positionClass": "toast-bottom-right"});
        }
      },
      error: function(request, status, error){

        $("#ShowSecondarySalesPerson").modal('hide');
      }

    });
});









  $(document).on("change",".categories-dd", function(){
    var cust_detail_id = "{{$customer->id}}";
    var cat_id = $(this).val();
    $('#default_price').val('');

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        url: "{{ route('get-product-against-cat') }}",
        method: 'post',
        dataType: 'json',
        data: {cust_detail_id:cust_detail_id, cat_id:cat_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
          $('.prod-fixed-cust').prepend($('<option></option>').html('Loading...'));
        },
        success: function(result){
          $("#loader_modal").modal('hide');
          if(result.success == true)
          {
            $('.prod-fixed-cust').html(result.html_string);
          }
          else
          {
            toastr.warning('Warning!', 'No products found in this Category.',{"positionClass": "toast-bottom-right"});
            $('.prod-fixed-cust').html(('<option value="">Select Category</option>'));
          }

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });

  });

  $(".update_state").select2().on("select2:close", function(e) {
      var id = document.getElementById('billing-id').value;
      var thisPointer=$(this);
      $('.state-div').addClass('d-none');
      $('#billing-statee').removeClass('d-none');
      saveBillingData(thisPointer,thisPointer.attr('name'), thisPointer.val(),id);
  });

  $(document).ready(function(){
    $(document).on('change',"#country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success:function(data){
           $("#loader_modal").modal('hide');
            var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
            html_string+='  <select id="state" name="shipping_state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div>";

            $("#state").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
        }

    });
  });
  });

  $(document).on('click','.is_default', function(){

    var check = document.getElementsByClassName('is_default');

    var customer_id = $("#customer_id").val();
    var billing_address = $('#billing_address_id').val();

    if(check[0].checked == true || check[1].checked == true){

      // document.getElementById('is_default_value').value = 1;
      $("#is_default_value").val('1');

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
        method: "get",
        url: "{{ url('sales/check_default_address') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data:{customer_id:customer_id,billing_address:billing_address},


        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.error == false)
           {
            swal({
          title: "Already Have Default Address, Are You Sure?",
          text: "You want to Replace it!!!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, do it!",
          cancelButtonText: "Cancel",
          closeOnConfirm: true,
          closeOnCancel: false
          },
          function (isConfirm) {
              if (isConfirm) {
                $.ajax({
                  method:"get",
                  data:{customer_id:customer_id,billing_address:billing_address},
                  url: "{{ route('replace_default_address') }}",
                  beforeSend: function(){
                    $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                    $('#loader_modal').modal('show');
                  },
                  success: function(response){
                    if(response.error === false){
                      toastr.success('Success!', 'Default Overwrite Successfully.',{"positionClass": "toast-bottom-right"});
                      $('#loader_modal').modal('hide');
                      // window.location.reload();

                    }
                    if(response.update === true){
                      toastr.success('Success!', 'Default Overwrite Successfully.',{"positionClass": "toast-bottom-right"});
                      // window.location.reload();
                      saveBillingData('no_pointer','no_name', 'no_value',billing_address);

                      location.reload();

                    }
                  }
                });
              }
              else {
                  swal("Cancelled", "", "error");
                  check[0].checked = false;
                  if(check[1]){
                  check[1].checked = false;
                  }
                  document.getElementById('is_default_value').value = 0;

              }
          });
            }

          if(data.set_default == true){
            toastr.success('Success!', 'Default Address Set Successfully.',{"positionClass": "toast-bottom-right"});
            saveBillingData('no_pointer','no_name', 'no_value',billing_address);

            // location.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }
  });

  @if (session('alert'))
    swal("{{ session('alert') }}");
  @endif

  $(document).on('click','.deleteNote', function(){
    var id = $(this).data('id');
    var cust_detail_id= "{{$customer->id}}";
    // alert('here');
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
        method: "get",
        url: "{{ url('sales/delete-customer-note') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data: {id:id,cust_detail_id:cust_detail_id},

        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          if(data.error == false)
           {
              toastr.success('Success!', 'Customer Note deleted successfully.',{"positionClass": "toast-bottom-right"});
              location.reload();
              return false;
            }
            else{
              $("#loader_modal").modal('hide');
            }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

  $(document).ready(function(){
    $('.pay-check').click(function(){
      var thisPointer = $(this);
      if($(this).prop("checked") == true)
      {
        var paymentThere = 1;
      }
      else if($(this).prop("checked") == false)
      {
        var paymentThere = 0;
      }
      saveCustData(thisPointer,thisPointer.attr('name'), thisPointer.val(),paymentThere);
    });

    $('.show_title').click(function(){
      var thisPointer = $(this);
      var id = null;
      if($('.com_add_id').val() != '')
      {
        id = $('.com_add_id').val();
      }
      else
      {
        id = $(this).data('id');
      }
      // alert($(this).prop('checked'));
      if($(this).prop("checked") == true)
      {
        var show = 1;
      }
      else if($(this).prop("checked") == false)
      {
        var show = 0;
      }
      // alert(show);
      saveCustData(thisPointer,thisPointer.attr('name'), id,show);
    });
  });

  $(document).on('click', '#submit_pin', function(e){
        e.preventDefault();
         var pincode = $("#pincode").val();
         var c_id =  "{{$customer->ecommerce_customer_id}}";
         // alert(c_id);``
         if(pincode == ''){
          toastr.error('Alert!', 'Pin code should not be empty.' ,{"positionClass": "toast-bottom-right"});
          return false;
         }
         var base_link  = "{{config('app.ecom_url')}}"+"/api/createnewpin";
// alert(base_link);
          $.ajax({
              type: "POST",
              // contentType: "application/json",
              url:  base_link,
              // dataType: 'jsonp',
              // cors: true ,
              data: {pincode:pincode, c_id: c_id, "_token":"{{ csrf_token() }}"},
              success: function(result) {
                if(result.success == true){
                  toastr.success('Success', result.message ,{"positionClass": "toast-bottom-right"});
                }else if(result.success == false){
                  toastr.error('Alert!', result.message ,{"positionClass": "toast-bottom-right"});
                }
              },
          });
      });




  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
  });

  $(document).on('keypress keyup focusout',".fieldFocus",function(e) {

    // alert('hi');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }

    var name = $(this).attr('name');
    // alert(name);

    if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active')){
      var str = $(this).val();
      if(($(this).val().length < 2 ||  !str.replace(/\s/g, '').length))
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Please Enter At Least 2 Characters (this string may only contain white spaces)!!!</b>'});
        return false;
      }
      else
      {
        $(this).removeClass('active');
        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        // alert(new_value);
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).prev().html(new_value);
          }
          $(this).prev().data('fieldvalue', new_value);
          saveCustData(thisPointer,thisPointer.attr('name'), thisPointer.val());
        }
      }
    }
    else if((e.keyCode === 13 || e.which === 0) && name == 'customer_credit_limit' && $(this).hasClass('active'))
    {
      $(this).removeClass('active');
        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        // alert(new_value);
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value == '')
          {
            $(this).prev().html('N/A');
          }
          $(this).prev().data('fieldvalue', new_value);
          saveCustData(thisPointer,thisPointer.attr('name'), thisPointer.val(), fieldvalue);
        }
    }

  });

  function saveCustData(thisPointer,field_name,field_value,new_select_value){
      var cust_detail_id= "{{$customer->id}}";

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",

        url: "{{ url('sales/save-cust-data-cust-detail-page') }}",
        dataType: 'json',
        data: 'cust_detail_id='+cust_detail_id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'new_select_value'+'='+encodeURIComponent(new_select_value),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.phone_already_exist == true)
          {
            var fieldvalue = $(this).prev().data('fieldvalue');
            var thisPointer = $(this);
            thisPointer.addClass('d-none');
            $('#phone_input').val(data.old_phone);
            thisPointer.removeClass('active');
            $('#phone').attr('data-fieldvalue',data.old_phone);
            $('#phone').html(data.old_phone);
            thisPointer.prev().removeClass('d-none');
            toastr.warning('Warning!', 'Phone Number Already Exists.',{"positionClass": "toast-bottom-right"});
            return false;

          }
          if(data.reference_name == true)
          {
            // var fieldvaluee = thisPointer.prev().data('fieldvalue');
            // alert(fieldvaluee);
            // $(this).prev().html(fieldvalue);
            // swal({ html:true, title:'Alert !!!', text:'<b>Reference Name Already Exists In '+data.status+' Customers!!!</b>'});
            // location.reload();
            //   return false;

              swal({
            title: "Alert!",
            html: true,
            text: "<b>Reference Name Already Exists In "+data.status+" Customers!!!</b>",
            type: "info",
            showCancelButton: false,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "OK!",
            cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: false
          },
            function(isConfirm) {
            if (isConfirm){
             window.location.reload();
            }
            else{
              swal("Cancelled", "", "error");
              window.location.reload();
            }
          });
          }
          if(data.success == true && data.completed == 1)
          {
            swal({
            title: "Success!",
            text: "Customer marked as Activated",
            type: "info",
            showCancelButton: false,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "OK!",
            cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: false
          },
            function(isConfirm) {
            if (isConfirm){
             window.location.reload();
            }
            else{
              swal("Cancelled", "", "error");
              window.location.reload();
            }
          });
          }
          if(data.success == true && data.completed == 0)
          {
            $('#company-exists').addClass('d-none');

            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            window.location.reload();

            // $('.history-table').DataTable().ajax.reload();

            if(field_name == "category_id")
            {
              // location.reload();
            }
            if(field_name == "reference_number")
            {
              location.reload();
            }
            if(field_name == "credit_term")
            {
              // location.reload();
            }
            if(field_name == "language")
            {
              location.reload();
            }
            return true;
          }
          if(data.success == false)
          {
            swal({
              title: "Alert!",
              html: true,
              text: "<b>Reference Name Already Exists In Completed/Suspended Customers!!!</b>",
              type: "info",
              showCancelButton: false,
              confirmButtonClass: "btn-danger",
              confirmButtonText: "OK!",
              cancelButtonText: "No!",
              closeOnConfirm: true,
              closeOnCancel: false
            },
            function(isConfirm) {
            if (isConfirm){
             window.location.reload();
            }
            else{
              swal("Cancelled", "", "error");
              window.location.reload();
            }
          });
          }

          if(data.company == true)
          {
            $('#company-exists').removeClass('d-none');
             // var thisPointer = $(this);
            // $('#company').addClass('d-none');
            $('#companyCheck').addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          }
        },

      });
    }


  // to make that field on its orignal state
  $(document).on("focusout",".shipping-fieldFocus",function() {
      var id = document.getElementById('shipping-id').value;

      if($(this).val().length < 1)
      {
        // swal("Must fill this filed accurately!");
        return false;
      }
      else
      {

        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).prev().html(new_value);
          }
          // saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
          saveShippingData(thisPointer,thisPointer.attr('name'), thisPointer.val(),id);

        }
      }

  });

  // to make that field on its orignal state
  $(document).on('keypress keyup focusout',".billing-fieldFocus",function(e) {
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
    if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active')){
      var id = document.getElementById('billing-id').value;
      if($(this).val().length < 1)
      {
        // swal("Must fill this filed accurately!");
        return false;
      }
      else
      {
        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).prev().html(new_value);
          }
          $(this).prev().data('fieldvalue', new_value);
          // saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
          saveBillingData(thisPointer,thisPointer.attr('name'), thisPointer.val(),id);
        }
      }
    }

  });

  // to make that field on its orignal state
  $(document).on("focusout keyup keypress",".productFixed, .discount",function(e) {
    if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
    thisPointer.addClass('d-none');
    thisPointer.removeClass('active');
    thisPointer.val(fieldvalue);
    thisPointer.prev().removeClass('d-none');
  }
  if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active'))
  {
    if($(this).val() != '')
    {
      if($(this).val() == $(this).prev().data('fieldvalue'))
      {
        return;
      }
      else
      {
        var id = $(this).data('id');
        var thisPointer = $(this);
        var new_value = thisPointer.val();
        thisPointer.removeClass('active');
        thisPointer.addClass('d-none');
        thisPointer.prev().html(new_value);
        thisPointer.prev().removeClass('d-none');
        saveProductFixedData(thisPointer,thisPointer.attr('name'), thisPointer.val(),id);
        thisPointer.prev().removeData('fieldvalue');
        thisPointer.prev().data('fieldvalue', new_value);
        console.log(thisPointer.prev());
      }
    }
  }
});

function saveProductFixedData(thisPointer,field_name,field_value,id){
    var cust_detail_id= "{{$customer->id}}";
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
        method: "post",
        url: "{{ url('sales/save-product-update-record') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data: {field_name:field_name,field_value:field_value,cust_detail_id:cust_detail_id,product_id:id},

        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          console.log(data);
         if(field_name == "fixed-price" || field_name == "discount")
          {
           if(data.error == false)
           {
              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              location.reload();
              $('.table-customer-history').DataTable().ajax.reload();
              return false;
            }

          }
         else if(field_name == "expiration-date")
          {
           if(data.error == false)
           {
              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              $('.table-customer-history').DataTable().ajax.reload();
              return false;
            }

          }

          else if(field_name == "city")
          {
           if(data.error == false)
           {
              $("#shipping-city").html(data.customerShipping.shipping_city);
              $('input[name=city]').val(data.customerShipping.shipping_city);

              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              return false;
            }
          }

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }

      });
  }

function saveShippingData(thisPointer,field_name,field_value,shipping_id){
      console.log(thisPointer+' '+' '+field_name+' '+field_value);
      var cust_detail_id= "{{$customer->id}}";

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        // url: "{{ url('save-supp-data-supp-detail-page') }}",
        url: "{{ url('sales/save-shipping-update-record') }}",

        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,cust_detail_id:cust_detail_id,shipping_id:shipping_id},

        data: 'cust_detail_id='+cust_detail_id+'&'+field_name+'='+field_value+'&'+'shipping_id'+'='+shipping_id,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

            if(field_name == "main_tags")
            {
              location.reload();
            }
            return true;
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

function saveBillingData(thisPointer,field_name,field_value,billing_id){
      console.log(thisPointer+' '+' '+field_name+' '+field_value);
      var cust_detail_id= "{{$customer->id}}";
      // alert(field_value);
      // return;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        // url: "{{ url('save-supp-data-supp-detail-page') }}",
        url: "{{ url('sales/save-billing-update-record') }}",

        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'cust_detail_id='+cust_detail_id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'billing_id'+'='+billing_id,
        // data: {cust_detail_id:cust_detail_id,field_name:field_value,billing_id:billing_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.completed == 1){
            swal({
            title: "Success!",
            text: "Customer marked as Activated",
            type: "info",
            showCancelButton: false,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "OK!",
            // cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: false
          },
            function(isConfirm) {
            if (isConfirm){
             window.location.reload();
            }
            else{
              swal("Cancelled", "", "error");
              window.location.reload();
            }
          });
          }
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

            if(field_name == "main_tags")
            {
              location.reload();
            }else if(field_name == 'billing_state'){
              // alert('here');
              $('.state-div').addClass('d-none');
              // alert(data.state);
        $('#billing-statee').removeClass('d-none');
              $('#billing-statee').html(data.state);
            }
            else if(field_name == 'billing_country'){
        //       // alert('here');
        //       $('.state-div').addClass('d-none');
        //       // alert(data.state);
        // $('#billing-statee').removeClass('d-none');
        $('#billing-statee').html('--');
              $('#billing-country').html(data.country);
              $('.update_state').empty();
              // $('.state-div').trigger('change.select2');
              $('.update_state').append(data.sta);
              // console.log(data.sta);
            }
            return true;
          }
          if(data.success == false)
          {
            swal({
              title: "Alert!",
              html: true,
              text: "<b>Reference Name Already Exists In Completed/Suspended Customers!!!</b>",
              type: "info",
              showCancelButton: false,
              confirmButtonClass: "btn-danger",
              confirmButtonText: "OK!",
              cancelButtonText: "No!",
              closeOnConfirm: true,
              closeOnCancel: false
            },
            function(isConfirm) {
            if (isConfirm){
             window.location.reload();
            }
            else{
              swal("Cancelled", "", "error");
              window.location.reload();
            }
          });
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

$(document).on("dblclick",".selectDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
  });

$(document).on("change",".shippingSelectFocus",function(){
  if($(this).attr('name') == 'shipping-select')
      {
        var thisPointer=$(this);
        // alert(thisPointer.val());
        // var new_select_value = $("option:selected", this).html();
        // saveCustData(thisPointer, thisPointer.attr('name'), thisPointer.val() , new_select_value);
        showShippingRecord(thisPointer.val());
      }
});

$(document).on("keypress keyup change",".billingSelectFocus",function(e){

  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
    thisPointer.addClass('d-none');
    thisPointer.val(fieldvalue);
    thisPointer.prev().removeClass('d-none');
    return;
  }

  if($(this).attr('name') == 'billing-select')
      {
        var thisPointer=$(this);
        // alert(thisPointer.val());
        // var new_select_value = $("option:selected", this).html();
        // saveCustData(thisPointer, thisPointer.attr('name'), thisPointer.val() , new_select_value);
        showBillingRecord(thisPointer.val());
      }

     if($(this).attr('name') == 'billing_country')
      {
         var id = document.getElementById('billing-id').value;
          var fieldvalue = $(this).prev().data('fieldvalue');
          var new_value = $(this).val();
            // if(fieldvalue == new_value)
            // {
            //   var thisPointer = $(this);
            //   thisPointer.addClass('d-none');
            //   thisPointer.prev().removeClass('d-none');
            //   $(this).prev().html(fieldvalue);
            // }
            // else
            // {
              var thisPointer = $(this);
              thisPointer.addClass('d-none');
              thisPointer.prev().removeClass('d-none');
              if(new_value != '')
              {
                // $(this).prev().html(new_value);
              }
              // $(this).prev().data('fieldvalue', new_value);
              // saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
              saveBillingData(thisPointer,thisPointer.attr('name'), thisPointer.val(),id);
            // }

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
        url: "{{ url('sales/show-single-billing-record') }}",
        dataType: 'json',
        data: {billing_id:billing_id,cust_detail_id:cust_detail_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $('#loader_modal').modal('hide');

          console.log(data);
          if(data.error == false)
           {
            $(".show_title").attr('data-id',data.billingCustomer.id);
            if(data.billingCustomer.show_title == 1)
            {
              $('.show_title').prop('checked',true);
              $('.com_add_id').val(data.billingCustomer.id);
            }
            else
            {
              $('.com_add_id').val(data.billingCustomer.id);
              $('.show_title').prop('checked',false);
            }
              $("#billing-primary-contact").html(data.billingCustomer.billing_phone != null ? data.billingCustomer.billing_phone : '--');
              $('input[name=billing_phone]').val(data.billingCustomer.billing_phone != null ? data.billingCustomer.billing_phone : '--');

              $("#billing-address").html(data.billingCustomer.billing_address != null ? data.billingCustomer.billing_address : '--');
              $('input[name=billing_address]').val(data.billingCustomer.billing_address != null ? data.billingCustomer.billing_address : '--');

              $("#billing-title").html(data.billingCustomer.title != null ? data.billingCustomer.title : '--');
              $('input[name=title]').val(data.billingCustomer.title != null ? data.billingCustomer.title : '--');

              $("#cell_number").html(data.billingCustomer.cell_number != null ? data.billingCustomer.cell_number : '--');
              $('input[name=cell_number]').val(data.billingCustomer.cell_number != null ? data.billingCustomer.cell_number : '--');

               $("#tax_id").html(data.billingCustomer.tax_id != null ? data.billingCustomer.tax_id : '--');
              $('input[name=tax_id]').val(data.billingCustomer.tax_id != null ? data.billingCustomer.tax_id : '--');

              $('.delete-company-address').data('id' , data.billingCustomer.id);
              if(data.billingCustomer.is_default == 1){
              $("#billing_default").html('Yes');
              $('.delete-add_row').addClass('d-none');
              }else{
                var checkbox = '<input type="checkbox" id="is_default" class=" is_default " name="is_default">';
                checkbox += ' <input type="hidden" id="is_default_value" class="form-control" name="is_default_value" value="0">';
                checkbox += '<input type="hidden" id="billing_address_id" class="form-control" name="is_default_value" value="'+data.billingCustomer.id+'">';
              $("#billing_default").html(checkbox);
              $('.delete-add_row').removeClass('d-none');



              }
              if(data.billingCustomer.is_default_shipping == 1)
              {
                $('.is_default_shipping').prop('checked',true);
              }
              else
              {
                $('.is_default_shipping').prop('checked',false);
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
               $('.update_state').empty();
              // $('.state-div').trigger('change.select2');
              $('.update_state').append(data.states);

              $("#billing-country").html(data.userCountry?data.userCountry:'N.A');
              // var option = '<option value='+data.billingCustomer.billing_country+' selected> '+data.userCountry+' </option>';
              document.getElementById(data.billingCustomer.billing_country).selected = true;
              // $("#country_id").append(option);

              $("#billing-phone-s").html(data.billingCustomer.billing_phone);

               $("#billing-zip").html(data.billingCustomer.billing_zip != null ? data.billingCustomer.billing_zip : '--');
              $('input[name=billing_zip]').val(data.billingCustomer.billing_zip != null ? data.billingCustomer.billing_zip : '--');
              // $('input[name=]').val(data.shippingCustomer.shipping_city);

              // $('input[name=reference_number]').val(data.customer.reference_number);

              // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              // return false;
            }

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });

}

function showShippingRecord(shipping_id){
  var cust_detail_id= "{{$customer->id}}";

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $.ajax({
        method: "post",
        url: "{{ url('sales/show-single-shipping-record') }}",
        dataType: 'json',
        data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          console.log(data);
          if(data.error == false)
           {
              $("#primary-contact").html(data.shippingCustomer.shipping_phone);
              $('input[name=primary-contact]').val(data.shippingCustomer.shipping_phone);

              $("#shipping-title").html(data.shippingCustomer.title);
              $('input[name=title]').val(data.shippingCustomer.title);

              $("#shipping-company").html(data.shippingCustomer.company_name);
              $('input[name=company]').val(data.shippingCustomer.company_name);


              $("#shipping-fax").html(data.shippingCustomer.shipping_fax);
              $('input[name=fax]').val(data.shippingCustomer.shipping_fax);

              $("#shipping-contact_name").html(data.shippingCustomer.shipping_contact_name);
              $('input[name=contact_name]').val(data.shippingCustomer.shipping_contact_name);

              $("#shipping-email").html(data.shippingCustomer.shipping_email);
              $('input[name=email]').val(data.shippingCustomer.shipping_email);

              $("#shipping-address").html(data.shippingCustomer.shipping_address);
              $('input[name=address]').val(data.shippingCustomer.shipping_address);

              $("#shipping-city").html(data.shippingCustomer.shipping_city?data.shippingCustomer.shipping_city:'N.A');
              $('input[name=city]').val(data.shippingCustomer.shipping_city);

              $("#phone-s").html(data.shippingCustomer.shipping_phone);
              // $('input[name=]').val(data.shippingCustomer.shipping_city);

              // $('input[name=reference_number]').val(data.customer.reference_number);

              // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              // return false;
            }

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });

}

// default supplier double click editable
$(document).on("keypress keyup change",".selectFocus",function(e) {
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
    thisPointer.addClass('d-none');
    thisPointer.val(fieldvalue);
    thisPointer.prev().removeClass('d-none');
    return;
  }

  if($(this).attr('name') == 'category_id')
  {
    // var new_value = $("option:selected", this).html();
    var thisPointer=$(this);
    var new_select_value =  thisPointer.prev().data('fieldvalue');
    var check_value = thisPointer.val();
    if(check_value == null || check_value == '')
    {
      toastr.warning('Warning!', 'Please Select Category !!!',{"positionClass": "toast-bottom-right"});
      return false;
    }
    thisPointer.addClass('d-none');
    thisPointer.prev().removeClass('d-none');
    var new_select_value = $("option:selected", this).html();
    $(this).prev().html(new_select_value);
    saveCustData(thisPointer, thisPointer.attr('name'), thisPointer.val() , new_select_value);
  }
  else
  {
    // alert('here');
    var thisPointer=$(this);
    thisPointer.addClass('d-none');
    thisPointer.prev().removeClass('d-none');
    var new_select_value = $("option:selected", this).html();
    $(this).prev().html('Add More');
    saveCustData(thisPointer, thisPointer.attr('name'), thisPointer.val() , new_select_value);
  }

});

// customer based product fixed prices
$(document).on('click', '#addCustFixedPriceBtn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-customer-product-fixed-price') }}",
          method: 'post',
          data: $('#addCustFixedPriceForm').serialize(),
          beforeSend: function(){
            $('.save-fixed-price').val('Please wait...');
            $('.save-fixed-price').addClass('disabled');
            $('.save-fixed-price').attr('disabled', true);
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('.save-fixed-price').val('add');
            $('.save-fixed-price').attr('disabled', true);
            $('.save-fixed-price').removeAttr('disabled');
            if(result.success === true){
              toastr.success('Success!', 'Customer Product Fixed Price added successfully',{"positionClass": "toast-bottom-right"});
              $('#addCustFixedPriceForm')[0].reset();
              $('.addCustFixedPriceModal').modal('hide');
              window.location.reload();
            }
            else{
              $('#loader_modal').modal('hide');
            }


          },
          error: function (request, status, error) {
                $('#loader_modal').modal('hide');
                $('.save-fixed-price').val('add');
                $('.save-fixed-price').removeClass('disabled');
                $('.save-fixed-price').removeAttr('disabled');
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

$(document).on('click', '#add-customer-contacts', function(e){
  var id = $('#cus_id').val();
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-customer-contact') }}",
          method: 'post',
          data:{id:id},
          // data: $('#addCustomerContactForm').serialize(),
          beforeSend: function(){
            $('.save-fixed-price').val('Please wait...');
            $('.save-fixed-price').addClass('disabled');
            $('.save-fixed-price').attr('disabled', true);
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('#loader_modal').modal('hide');
            if(result.contacts == true){
              toastr.warning('Alert!', 'Already Have Five Contacts!',{"positionClass": "toast-bottom-right"});

            }
            $('.save-fixed-price').val('add');
            $('.save-fixed-price').attr('disabled', true);
            $('.save-fixed-price').removeAttr('disabled');
            if(result.success === true){
              toastr.success('Success!', 'Customer Contact added successfully',{"positionClass": "toast-bottom-right"});
              // $('#addCustomerContactForm')[0].reset();
              // $('.customerContactModal').modal('hide');
            $('.table-contacts').DataTable().ajax.reload();

              // window.location.reload();
            }
          },
          error: function (request, status, error) {
            $('#loader_modal').modal('show');
            $('.save-fixed-price').val('add');
            $('.save-fixed-price').removeClass('disabled');
            $('.save-fixed-price').removeAttr('disabled');
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

    $(document).on('click','.save_close_btn', function(){
      $('.save_close_btn').prop('disabled', 'true');
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
    });
// Getting product selling price
$(document).on('change', '.product', function(e){
  var product_id = $(this).val();
  var customer_id = "{{$id}}";
      $.ajax({
        method: "get",
        data:{ product_id:product_id, customer_id:customer_id },
        url: "{{ url('sales/getting-product-selling-price') }}",
        dataType: 'json',
        context: this,
        // data: {customer_id:customer_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
          $("#default_price").val("Loading.....");
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            $("#default_price").val(data.price);
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

$(document).on('click', '.add_product_to', function(e){
  var product_id = $(this).data('prod_id');
  var customer_id = "{{$id}}";
      $.ajax({
        method: "get",
        data:{ product_id:product_id, customer_id:customer_id },
        url: "{{ url('sales/getting-product-selling-price') }}",
        dataType: 'json',
        context: this,
        // data: {customer_id:customer_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
          $("#default_price").val("Loading.....");
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            $('#prod_name').val(data.product.refrence_code);
            $('#product').val(data.product.id);
            $('#product_name_div_complete').hide();
            $("#default_price").val(data.price);
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

$(document).on('click', '.add-notes', function(e){
      var customer_id = $(this).data('id');
      $('.note-customer-id').val(customer_id);
      // alert(customer_id);

    });

$('.add-cust-note-form').on('submit', function(e){

      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-customer-note') }}",
          dataType: 'json',
          type: 'post',
          data: new FormData(this),
          contentType: false,
          cache: false,
          processData:false,
          beforeSend: function(){
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success == true){
              toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
              /*setTimeout(function(){
                window.location.reload();
              }, 2000);*/

              $('.add-cust-note-form')[0].reset();
              $('#add_notes_modal').modal('hide');
              window.location.reload();

            }else{
              toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
              $('#loader_modal').modal('hide');
            }

          },
          error: function (request, status, error) {
                /*$('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();*/
                $('#loader_modal').modal('hide');
                $('.save-btn').removeClass('disabled');
                $('.save-btn').removeAttr('disabled');
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                      $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');
                     $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('textarea[name="'+key+'"]').addClass('is-invalid');


                });
            }
        });
    });

//This one is for the Billing modal
$(document).on('change',"#billing_country",function(){
  // alert('hi');
      var country_id=$(this).val();
      var store_state =$(this);
      $.ajax({

          url:"{{url('common/filter-state')}}",
          method:"get",
          dataType:"json",
          data:{country_id:country_id},
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(data){
              $("#loader_modal").modal('hide');
              var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
              html_string+='  <select id="billing_state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
              for(var i=0;i<data.length;i++){
                  html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
              }
              html_string+=" </select></div>";

              $("#billing_state").html(html_string);
              $('.selectpicker').selectpicker('refresh');
      $('.state_select').empty();
      $('.state_select').append(data.states);

          },
          error:function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
  });

$(".billing").on("focusout",function(){

    var title = $(this).val();
    var customer_id = "{{$customer->id}}";
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ url('sales/check-duplicate-address') }}",
        method: 'post',
        dataType:'json',
        data:{customer_id:customer_id,title:title},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $("#loader_modal").modal('hide');
          if(data.success == false)
          {
            $('input[name="'+data.field+'"]').after('<span class="invalid-feedback" role="alert"><strong>The '+data.field+' is Already Been taken</strong>');
            $('input[name="'+data.field+'"]').addClass('is-invalid');
          }
          else if(data.success == true)
          {
            $('input[name="'+data.field+'"]').removeClass('is-invalid');
          }

        },
        error: function (request, status, error) {
          $("#loader_modal").modal('hide');
        }
      });


  });

$(document).on('click','#add-address-form-btn',function(e){
   e.preventDefault();
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });



    if(document.getElementById("billing_title").value == '') {
      toastr.error('Error!', 'The billing name is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    // if(document.getElementById("billing_contact_name").value == '') {
    //   toastr.error('Error!', 'The billing contact name is required',{"positionClass": "toast-bottom-right"});
    //   return false;
    // }

    if(document.getElementById("billing_email").value == '') {
      toastr.error('Error!', 'The Email field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    if(document.getElementById("tax_id").value == '') {
      toastr.error('Error!', 'The Tax ID field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    if(document.getElementById("billing_phone").value == '') {
      toastr.error('Error!', 'The Phone number field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    // if(document.getElementById("cell_number").value == '') {
    //   toastr.error('Error!', 'The Mobile number field is required',{"positionClass": "toast-bottom-right"});
    //   return false;
    // }

    if(document.getElementById("billing_address").value == '') {
      toastr.error('Error!', 'The Address field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    if(document.getElementById("billing_zip").value == '') {
      toastr.error('Error!', 'The Zip field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    if(document.getElementById("billing_country").value == '') {
      toastr.error('Error!', 'The Country field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    if(document.getElementById("billing_city").value == '') {
      toastr.error('Error!', 'The District field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    if(document.getElementById("billing_state").value == '') {
      toastr.error('Error!', 'The City field is required',{"positionClass": "toast-bottom-right"});
      return false;
    }

    // if(document.getElementById("billing_fax").value == '') {
    //   toastr.error('Error!', 'The Fax field is required',{"positionClass": "toast-bottom-right"});
    //   return false;
    // }


    $.ajax({
      url:"{{ url('sales/save-customer-billing')}}",
          method: 'post',
    data: $('#add-address-form').serialize(),
    beforeSend: function(){
      if(document.getElementById("add-address-form-btn").value !== '') {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      }

      },
      success:function(data){

        if(data.sucess == true){
        $('#add_billing_detail_modal').modal('hide');
        location.reload();
      }
      else if(data.sucess == false)
          {
            $("#loader_modal").modal('hide');
            $('input[name="'+data.field+'"]').after('<span class="invalid-feedback" role="alert"><strong>The '+data.field+' is Already Been taken</strong>');
            $('input[name="'+data.field+'"]').addClass('is-invalid');
          }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
  });
});

$(document).on('click','.delete_customer',function(){
      var customer_id = $("#customer_id").val();

      swal({
          title: "Are You Sure?",
          text: "You want to delete customer!!!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, do it!",
          cancelButtonText: "Cancel",
          closeOnConfirm: true,
          closeOnCancel: false
          },
          function (isConfirm) {
              if (isConfirm) {
                 $.ajax({
        method: "get",
        url: "{{ url('sales/delete-customer') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data: {id:customer_id},

        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
           {

              toastr.success('Success!', 'Customer deleted successfully.',{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('sales/customer') }}";
            }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
              }
              else {
                  swal("Cancelled", "", "error");

              }
          });
            });

$(document).on('click','.delete-company-address',function(){
      var address_id = $(this).data('id');

      swal({
          title: "Are You Sure?",
          text: "You want to delete address!!!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, do it!",
          cancelButtonText: "Cancel",
          closeOnConfirm: true,
          closeOnCancel: false
          },
          function (isConfirm) {
              if (isConfirm) {
                 $.ajax({
        method: "get",
        url: "{{ url('sales/delete-cust-company-address') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data: {id:address_id},

        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Address deleted successfully.',{"positionClass": "toast-bottom-right"});
            location.reload();
          }
          else
          {
            toastr.error('Error!', 'This address is used in previous orders, cannot delete this address.',{"positionClass": "toast-bottom-right"});
            return false;
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
              }
              else {
                  swal("Cancelled", "", "error");

              }
          });
            });

$(function(e){
  var id = $("#cus_id").val();
   $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-contacts').DataTable({
      "sPaginationType": "listbox",
          processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:false,
  "lengthChange": false,
  serverSide: true,
  "scrollX": true,
          "bPaginate": false,
          "bInfo":false,
  lengthMenu: [ 100, 200, 300, 400],
  "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5 ] },
    { className: "dt-body-right", "targets": [] },
    //  {
    //             "targets": [ 5 ],
    //             "visible": show,
    //             "searchable": false
    //         }
  ],
         ajax: {
            url:"{!! route('get-customer-contact') !!}",
            data: function(data) { data.id = id } ,
            },
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'action', name: 'action' },
            { data: 'name', name: 'name' },
            { data: 'sur_name', name: 'sur_name' },
            { data: 'email', name: 'email' },
            // { data: 'name', name: 'name' },
            { data: 'telehone_number', name: 'telehone_number' },
            { data: 'postion', name: 'postion' },
            { data: 'is_default', name: 'is_default' }

              ],
            initComplete: function () {
        if("{{Auth::user()->role_id}}" == 7)
        {
          $('.inputDoubleClickContacts').removeClass('inputDoubleClickContacts');
        }

      }
    });
});

//company address
$(function(e){
  var id = $("#cus_id").val();
   $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-company-addresses').DataTable({
      "sPaginationType": "listbox",
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        "searching": false,
          "lengthChange": false,
          "autoWidth": false,
          "bInfo" : false,
          "bPaginate": false,
        ordering: false,
        serverSide: true,
          lengthMenu: false,
         ajax: {
            url:"{!! route('get-customer-company-addresses') !!}",
            data: function(data) { data.id = id } ,
            },
        columns: [
            { data: 'reference_name', name: 'reference_name' },
            { data: 'phone_no', name: 'phone_no' },
            { data: 'cell_no', name: 'cell_no' },
            { data: 'address', name: 'address' },
            { data: 'tax_id', name: 'tax_id' },
            { data: 'email', name: 'email' },
            { data: 'fax', name: 'fax' },
            { data: 'state', name: 'state' },
            { data: 'city', name: 'city' },
            { data: 'zip', name: 'zip' },
            { data: 'is_default', name: 'is_default' },
              ]
    });

});

//General documents table
$(function(e){
  var show = true;
  var role = "{{Auth::user()->role_id}}";
  if(role == 6)
  {
    show = false;
  }
  // alert(role);
  var id = $("#cus_id").val();
   $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-general-documents').DataTable({
      "sPaginationType": "listbox",
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
            { width: '30%', targets: 3 },

    { className: "dt-body-left", "targets": [ 0,1,2] },
    { className: "dt-body-right", "targets": [] },
     {
                "targets": [ 3 ],
                "visible": show,
                "searchable": false
            }
        ],
        fixedColumns: true,
         ajax: {
            url:"{!! route('get-customer-general-documents') !!}",
            data: function(data) { data.id = id } ,
            },
        columns: [
            { data: 'description', name: 'description' },
            { data: 'file_name', name: 'file_name' },
            { data: 'date', name: 'date' },
            { data: 'action', name: 'action' },
              ]
    });
});

// Delete customer contact
$(document).on('click', '.deleteCustomerContact', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete the contact ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              method:"get",
              data:'id='+id,
              url: "{{ route('delete-customer-contact') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){
                $("#loader_modal").modal('hide');
                if(response.success === true){
                  toastr.success('Success!', 'Contact Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-contacts').DataTable().ajax.reload();
                }
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
            });
          }
          else {
            swal("Cancelled", "", "error");
          }
      });
    });

// Delete General Documents
$(document).on('click', '.deleteGeneralDocument', function(e){

    var id = $(this).data('id');
  var customer_id = $("#cus_id").val();

      swal({
        title: "Are you sure?",
        text: "You want to delete the document ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              method:"get",
              data:'id='+id+'&customer_id='+customer_id,
              url: "{{ route('delete-customer-general-document') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){

                if(response.success === true){
                  if(response.totalDocuments < 5){
                    $('#viewAllDocuments').addClass('d-none');
                    $("#loader_modal").modal('hide');
                  }
                  toastr.success('Success!', 'Document Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-general-documents').DataTable().ajax.reload();
                }
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
            });
          }
          else {
            swal("Cancelled", "", "error");
          }
      });
    });

$('#po_docs').bind('change', function() {
    var totalSize = 0;
    $("#po_docs").each(function() {
      for (var i = 0; i < this.files.length; i++) {
        totalSize += this.files[i].size;
      }
    });

    if((totalSize/1000) > 2000 )
    {
      swal({ html:true, title:'Alert !!!', text:'<b>File Size Must Be Less Then 2 MB!!!</b>'});
      $('#po_docs').val("")
      return false;
    }
});

// document upload
$('.addDocumentForm').on('submit', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ route('add-customer-general-document') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      beforeSend: function(){
        $('.save-doc-btn').html('Please wait...');
        $('.save-doc-btn').addClass('disabled');
        $('.save-doc-btn').attr('disabled', true);
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(result){
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').attr('disabled', true);
        $('.save-doc-btn').removeAttr('disabled');
        if(result.success == true)
        {
          toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
          $('.addDocumentForm')[0].reset();
          $('.addDocumentModal').modal('hide');
          $("#loader_modal").modal('hide');
          // $('.download-docs').removeClass('d-none');
          $('.table-general-documents').DataTable().ajax.reload();


        }
        if(result.con == true){
          location.reload();
        }
      },
      error: function (request, status, error) {
        $("#loader_modal").modal('hide');
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').removeClass('disabled');
        $('.save-doc-btn').removeAttr('disabled');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
              $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
             $('input[name="'+key+'[]"]').addClass('is-invalid');

        });
        }
    });
    });

//contacts edit
$(document).on("dblclick",".inputDoubleClickContacts",function(){
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
      $x.next().next('span').removeClass('d-none');
      $x.next().next('span').addClass('active');

     }, 300);
});

$(document).on('keypress keyup focusout', '.fieldFocusContact', function(e){

  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active')){

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
              var id = $(this).closest('tr').attr('id');
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
              saveCusContactData(id,thisPointer.attr('name'), thisPointer.val());
            }
          }
          else
          {
            swal({ html:true, title:'Alert !!!', text:'<b>Please Enter Valid Email, Try Again!!!</b>'});
            return false;
          }
        }
      }
      if($(this).val().length < 1)
      {
        return false;
      }
      else
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
          var id = $(this).closest('tr').attr('id');
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
          saveCusContactData(id,thisPointer.attr('name'), thisPointer.val());
        }
      }
    }

});

function saveCusContactData(id,field_name,field_value){
  var customer_id = $("#cus_id").val();
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  $.ajax({
    method: "post",
    url: "{{ url('sales/save-cus-contacts-data') }}",
    dataType: 'json',
    data: 'id='+id+'&'+'customer_id='+customer_id+'&'+field_name+'='+field_value,
    beforeSend: function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
    },
    success: function(data)
    {
      $("#loader_modal").modal('hide');
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
      if(data.success == true)
      {

        toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });
}

$(document).on('click','.deleteCustomerFixedPrice',function(){

          var id = $(this).data('id');


      swal({
          title: "Are You Sure?",
          text: "You want to delete!!!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, do it!",
          cancelButtonText: "Cancel",
          closeOnConfirm: true,
          closeOnCancel: false
          },
          function (isConfirm) {
              if (isConfirm) {
                 $.ajax({
        method: "get",
        url: "{{ url('sales/delete-customer-fixed-price') }}",
        dataType: 'json',
        // data: {shipping_id:shipping_id,cust_detail_id:cust_detail_id},
        data: {id:id},

        beforeSend:function()
        {
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          // $("#loader_modal").modal('hide');
          if(data.success === true)
          {
              toastr.success('Success!', 'Customer Fixed Prices Deleted Successfully' ,{"positionClass": "toast-bottom-right"});
          }
          location.reload();
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
              }
              else {
                  swal("Cancelled", "", "error");

              }
          });
            });

$('#upload-div').on('click',function(){
    $('.upload-div').toggle(300);
  });

$('#alreadybtn,#filteredProductsbtn').on('click',function(){
    $('.upload-price-div').toggle(300);
  });

$('#uploadBtn').on('click',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
      timeout = setTimeout(function(){
      var alertMsg = "<p style='color:red;''>Please be paitent this process will take some time .....</p>";
      $('#msg').html(alertMsg);
    }, 10000);
  });

$(document).on('change',".selecting-primary-cat",function(){
      var category_id=$(this).val();
      // var store_sb_cat =$(this);
      $.ajax({

          url:"{{route('filter-sub-category')}}",
          method:"get",
          dataType:"json",
          data:{category_id:category_id},
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
              var html_string = '';
                html_string+="<option value=''>Select a Sub Category</option>";
              for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
              }
              // $("#state_div").remove();
              // store_sb_cat.after($("<div></div>").text(html_string));
              $(".fill_sub_cat_div").empty();
              $(".fill_sub_cat_div").append(html_string);

          },
          error:function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
    });

@if(Session::has('successmsg'))
  toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
  @php
   Session()->forget('successmsg');
  @endphp
@elseif(Session::has('errormsg'))
toastr.error('Error !', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
@endif

$('#prod_name').keyup(function(e){
    var query = $(this).val();
    if(query == '' || e.keyCode == 8 || 'keyup' )
    {
      $('#product_name_div_complete').empty();
    }
    if(e.keyCode == 13)
    {
      if(query.length > 2)
      {
        var _token = $('input[name="_token"]').val();
        $.ajax({
          url:"{{ route('autocomplete-fetch-product-cdp') }}",
          method:"POST",
          data:{query:query, _token:_token},
          beforeSend: function(){
          $('#product_name_div_complete').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
          },
          success:function(data){
            $('#product_name_div_complete').fadeIn();
            $('#product_name_div_complete').html(data);
            $('#product_name_div_complete').show();
            $('#loader_modal').modal('hide');
          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
        });
      }
      else
      {
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
    });

$(document).on('click','.is_default_shipping',function(e){
  var address_id = $('.billingSelectFocus').val();
  var is_default_shipping = $(this).prop('checked');
  var customer_id = $("#customer_id").val();
  $.ajax({
          url:"{{route('setting-default-shipping-for-customer')}}",
          method:"get",
          dataType:"json",
          data:{address_id:address_id,is_default_shipping: is_default_shipping,customer_id: customer_id},
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Default Shipping Updated !!!',{"positionClass": "toast-bottom-right"});
              return true;
            }
            toastr.error('Sorry!', 'Something went wrong !!!',{"positionClass": "toast-bottom-right"});
          },
          error:function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
});


$(document).on('click','.default_customer',function(e){
  var is_default = $(this).prop('checked');
  var contact_id = $(this).data('id');
  $.ajax({
          url:"{{route('setting-default-customer-contact')}}",
          method:"get",
          dataType:"json",
          data:{is_default:is_default, contact_id:contact_id},
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Default Contact Updated !!!',{"positionClass": "toast-bottom-right"});
              $('.table-contacts').DataTable().ajax.reload();
              return true;
            }
            else
            {
                toastr.error('Error!', 'Cannot uncheck default contact !!!',{"positionClass": "toast-bottom-right"});
                $('.table-contacts').DataTable().ajax.reload();
                return true;
            }
          },
          error:function(request, status, error){
            $("#loader_modal").modal('hide');
            alert('Error');
          }

      });
});
});

</script>

<script>
  function backFunctionality(){
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('sales/customer') }}";
       document.location.href = url;
     }
   }
   $(document).ready(function () {
    if($(window).width()<767px ){
        $('.order-div-mrgin').insertBefore('.notes-div-mrgin');
    }
   });
</script>

@stop
