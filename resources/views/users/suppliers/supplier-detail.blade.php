@extends('users.layouts.layout')

@section('title','Supplier Detail | Supply Chain')

@section('content')
@php

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

.selectDoubleClick, .inputDoubleClick .inputDoubleClickContacts{
  font-style: italic;
}
.dataTables_scrollHeadInner, .table-general-documents, .supplier-contact-table, .supplier-account-table{
  width: 100% !important;
}

.select2-results__option
{
  display: block !important;
  overflow:  hidden !important;
  white-space: nowrap !important;
}
#select2-choose_product_select-results .select2-results__option
{
  white-space: normal !important;
}
.supplier-product-category-prices > .dropdown-menu
{
  min-width: auto !important;
  width: 100% !important;
  max-height: 350px !important;
}
.supplier-product-category > .dropdown-menu
{
  min-width: auto !important;
  width: 100% !important;
  max-height: 350px !important;
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
          <li class="breadcrumb-item"><a href="{{route('list-of-suppliers')}}">Supplier Center</a></li>
          <li class="breadcrumb-item active">Supplier Detail Page</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<!-- New Design Starts Here -->
<!-- Right Content Start Here -->

<div class="right-content pt-0 pl-0 pr-0">

  <div class="row errormsgDiv mt-2 d-none">
    <div class="container" style="max-width: 100% !important; min-width: 100% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <span id="errormsg"></span>
      </div>
    </div>
  </div>

  <div class="row errormsgDivBulk d-none">
    <div class="container" style="max-width: 50% !important; min-width: 50% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <div id="msgs_alerts"></div>
      </div>
    </div>
  </div>

<div class="row mb-3">

<div class="col-lg-12 col-md-12 headings-color mb-2">

<div class="row">

  <div class="col-lg-1 col-md-1">
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
  <div class="col-lg-6 col-md-5 p-0">
    <h5 class="fontbold headings-color mb-0 mt-lg-4">Supplier Detail Page</h5>
  </div>

  @if(@$supplier->status == 0)
    <div class="col-lg-5 col-md-4">
      <button class="btn btn-sm pl-3 pr-3 btn-danger pull-right mt-4 delete_supplier ml-2">Discard And Close</button>
      <button class="btn btn-sm pl-3 pr-3 btn-success mt-4 pull-right btn_save_close"><a href="{{url('save-incom-supplier')}}">Save And Close</a></button>
    </div>
  @endif

  @if(@$supplier->status == 1)
  <div class="col-lg-4 col-md-4">
    <button class="btn btn-sm btn-success mt-lg-3 custom-success-btn"><a href="{{url('supplier')}}">Save And Close</a></button>
    <button class="btn btn-sm btn-danger mt-lg-3 delete_supplier">Discard And Close</button>
    <a href="{{url('redirect-to-products/'.$id)}}" class="default_supplier pull-right"><img src="{{asset('public/img/View-Products.png')}}" style="width: 50px; height: 50px;" title="View Products" class="image d-inline img-icon"></a>
  </div>
  <div class="col-lg-1 col-md-1 mt-lg-2">
    <div class="row">
    <div class="col-lg-4 col-md-4 mt-md-1 mb-md-3">
    <a style="cursor: pointer;" class="pull-left" href="JavaScript:void(0);" id="quantity-upload-div">
    <img src="{{asset('public/img/Product-Bulk-Upload1.png')}}" style="width: 40px; height: 40px;" title="Bulk Upload" class="image img-icon">
    </a>
    </div>
    </div>
  </div>
  @endif

</div>
</div>

<?php if(!empty($errors) && count($errors)>0) : ?>
<div class="row errormsgDiv">
  <div class="container">
    <div class="alert alert-danger alert-dismissible">
      <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
      <?php foreach ($errors->all() as $error) : ?>
        <span><?php echo $error ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="col-lg-12 col-md-12 pb-5 quantity-upload-div" style="display: none;">
  <div class="bg-white pr-4 pl-4 pt-4 pb-5 mb-2">

    <ul class="nav nav-tabs">
      <li class="nav-item ">
        <a class="nav-link cut-tab active bulk_price" data-toggle="tab" href="#tab2">Add Bulk Prices</a>
      </li>
      <li class="nav-item">
        <a class="nav-link cut-tab bulk_product" data-toggle="tab" href="#tab3">Add Bulk Products</a>
      </li>
    </ul>

    <div class="tab-content mt-3">
      <div class="tab-pane active" id="tab2">
        <!-- <form id="filteredProducts"> -->
        <form id="supplierProductsPrices">
          {{csrf_field()}}
        <div class="form-group row">



          <div class="col-3">
            <div class="form-group">
              <input type="hidden" name="suppliers" value="{{$supplier->id}}">
              <label>Choose Category</label>
              <select class="form-control selecting-primary-cat supplier-product-category-prices selectpicker" name="primary_category[]" multiple data-live-search="true" title="Choose Category">
                  <!-- <option selected value="">Choose Category</option> -->
                  @foreach($primary_category as $p_cat)
                  <option class="font-weight-bold" value="{{'pri_'.$p_cat->id}}" title="{{@$p_cat->title}}">{{@$p_cat->title}}</option>
                  @foreach(@$p_cat->get_Child as $sub_cat)
                    <option value="{{'sub_'.$sub_cat->id}}" title="{{@$sub_cat->title}}"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$sub_cat->title}}
              {!! $extra_space !!} {{$p_cat->title}} </option>
                  @endforeach
                  @endforeach
              </select>
            </div>
          </div>

          <!-- <div class="col-3">
            <div class="form-group">
              <label>Choose Sub Category</label>
              <select class="form-control fill_sub_cat_div supplier-product-sub-category-prices" name="sub_category_prices">
                  <option value="">Choose Sub Category</option>
              </select>
            </div>
          </div> -->

          <div class="col pull-right">
            <button class="btn btn-success pull-right mt-4 export-btn" type="submit" id="filteredProductsbtn" >Download Products</button>
          </div>

        </div>
        </form>

        <button class="btn btn-info pull-right" id="alreadypricebtn" >Already Have File</button>

        <br>
        <div class="upload-price-div" style="display: none;">
          <h3>Upload File</h3>
          <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
          <form class="u-b-prices-form upload-excel-form" enctype="multipart/form-data">
            {{csrf_field()}}
            <input type="hidden" name="supplier" value="{{$supplier->id}}">
            <label for="bulk_import_file">Choose Excel File</label>
            <input type="file" class="form-control" name="excel" id="price_excel" accept=".xls,.xlsx" required=""><br>
            <button class="btn btn-info price-upload-btn" type="button">Upload</button>
          </form>
        </div>

      </div>
      <div class="tab-pane" id="tab3">
        <form id="supplierProducts">
          {{csrf_field()}}
        <div class="form-group row">

          <div class="col-3">
            <div class="form-group">
              <input type="hidden" name="suppliers" value="{{$supplier->id}}">
              <label>Choose Category</label>
              <select class="form-control selecting-primary-cat supplier-product-category selectpicker" name="primary_category[]" multiple data-live-search="true" title="Choose Category">
                  <!-- <option selected value="">Choose Category</option> -->
                  @foreach($primary_category as $p_cat)
                  <option class="font-weight-bold" value="{{'pri_'.$p_cat->id}}" title="{{@$p_cat->title}}">{{@$p_cat->title}}</option>
                  @foreach(@$p_cat->get_Child as $sub_cat)
                    <option value="{{'sub_'.$sub_cat->id}}" title="{{@$sub_cat->title}}"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$sub_cat->title}}
              {!! $extra_space !!} {{$p_cat->title}} </option>
                  @endforeach
                  @endforeach
              </select>
            </div>
          </div>

          <!-- <div class="col-3">
            <div class="form-group">
              <label>Choose Sub Category</label>
              <select class="form-control fill_sub_cat_div supplier-product-sub-category" name="sub_category">
                  <option value="">Choose Sub Category</option>
              </select>
            </div>
          </div> -->

          <div class="col pull-right">
            <button class="btn btn-success pull-right mt-4 export-btn" type="submit" id="supplierProductsbtn" >Download Products</button>
          </div>

        </div>
        </form>
        <div class="form-group">
          @if($temp_product_count > 0)
          <a class="btn btn-info pull-right" href="{{url('bulk-products-upload-form',$supplier->id)}}" >Go to Temporary Products</a>
          @endif
          <a class="btn btn-info pull-right d-none link-of-temp-products" href="{{url('bulk-products-upload-form',$supplier->id)}}" >Go to Temporary Products</a>
          <button class="btn btn-info pull-right mr-2" id="alreadyProductBtn" >Already Have File</button>
          {{--<a href="{{asset('public/site/assets/purchasing/product_excel/Bulk_Products.xlsx')}}" download><span class="btn btn-success pull-right  mr-1" id="examplefilebtn">Download Example File</span></a>--}}
          <a href="javascript:void(0);" class="export_excel_btn"><span class="btn btn-success pull-right mr-1 " id="examplefilebtn">Download Example File</span></a>
        </div>

        <br>
        <div class="upload-product-bulk" style="display: none;">
          <h3>Upload File</h3>
          <label><strong>Note : </strong>Please use the downloaded file for upload only.<span class="text-danger">Also Don't Upload Empty File.</span></label>
          <form class="upload-excel-form u-b-product-form" enctype="multipart/form-data">
            {{csrf_field()}}
            <label for="bulk_import_file">Choose Excel File</label>
            <input type="hidden" name="supplier" value="{{$supplier->id}}">
            <input type="file" class="form-control" name="excel" id="excel" accept=".xls,.xlsx" required=""><br>
            <button class="btn btn-info products-upload-btn" type="button">Upload</button>
          </form>
        </div>

      </div>
    </div>
  </div>

    <div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
     <b> Export file is being prepared! Please wait.. </b>
    </div>
    <div class="alert alert-success export-alert-success d-none"  role="alert">
      <i class=" fa fa-check "></i>
      <b>Export file is ready to download.
      <!-- <a download href="{{ url('storage/app/bulk_price_export.xlsx')}}"><u>Click Here</u></a> -->
      <a href="{{ url('get-download-xslx','bulk_price_export.xlsx')}}" target="_blank" title="Download" id=""><u>Click Here</u></a>
      </b>
    </div>
    <div class="alert alert-success example-export-alert-success d-none"  role="alert">
      <i class=" fa fa-check "></i>
      <b>Export file is ready to download.
      <!-- <a download href="{{ url('storage/app/bulk_price_export.xlsx')}}"><u>Click Here</u></a> -->
      <a href="{{ url('get-download-xslx','Bulk_Products.xlsx')}}" target="_blank" title="Download" id=""><u>Click Here</u></a>
      </b>
    </div>
    <div class="alert alert-success pro-export-alert-success d-none"  role="alert">
      <i class=" fa fa-check "></i>
      <b>Export file is ready to download.
      <!-- <a download href="{{ url('storage/app/bulk_products_export.xlsx')}}"><u>Click Here</u></a> -->
      <a href="{{ url('get-download-xslx','bulk_products_export.xlsx')}}" target="_blank" title="Download" id=""><u>Click Here</u></a>
      </b>
    </div>
    <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
      <b> Export file is already being prepared by another user! Please wait.. </b>
    </div>
</div>

<div class="col-lg-12 col-md-12">
  <div class="row headings-color">
  <div class="col-lg-6 col-md-6">
    <h3 class="">Supplier Information</h3>
  </div>
  <div class="col-lg-3 col-md-3">
    <h3 class="">Notes</h3>
  </div>
  <div class="col-lg-3 col-md-3">
    <a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="{{$supplier->id}}"  class="add-notes btn add-btn btn-color pull-right mb-0" title="Add Note"><i class="fa fa-plus"></i> Add</a>
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
  <form method="post" action="{{url('/update-supplier-profile-pic/'.$supplier->id)}}" class="upload-excel-form" enctype="multipart/form-data">
    {{csrf_field()}}
    <div class="col-lg-12 col-md-6 col-sm-6 col-6 form-group">
      <label>Choose File</label>
      <input required="true" type="file" class="form-control" name="logo" >
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

<!-- Supplier categories Modal -->
<div class="modal" id="update-cats" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Supplier Categories</h3>
        <form method="post" class="save-supplier-cats" id="save-supplier-cats">
        <input type="hidden" name="edit_supplier_id" id="edit_supplier_id" value="{{$id}}">

        <div class="form-row mt-5">
          <div class="form-group col-1"></div>
          <div class="form-group col-8">
            <select class="selectpicker selectFocus form-control show-tick category_id" data-live-search="true" title="Choose Category" name="category_id[]" required="true" multiple="">
              <option value="" disabled="" selected="">Choose Category</option>

              @foreach($categories as $pcat)
              <optgroup label="{{$pcat->title}}">
                @php
                  // $subCat = App\Models\Common\ProductCategory::where('parent_id',$pcat->id)->get();
                  $subCat = $pcat->get_Child;

                @endphp
                @foreach($subCat as $scat)

                <option {{in_array($scat->id, $supplierCats) ? 'selected':''}} value="{{$scat->id}}">{{$scat->title}}</option>

                @endforeach
              </optgroup>
              @endforeach

              {{--@endif--}}
            </select>
          </div>

          <div class="form-group col-2" style="float: right;">
            <button type="submit" id="sup-cat-save" class="btn-save btn btn-primary btn-md">Save</button>
          </div>
          <div class="form-group col-1"></div>
        </div>

        </form>
      </div>
    </div>
  </div>
</div>
<!-- Modal Ends Here -->

<div class="col-lg-12 col-md-12 headings-color">
<div class="row">

  <div class="col-lg-6 col-md-6">
  <div class="bg-white h1-00">
  <table id="example" class="table-responsive table sales-customer-table dataTable const-font" style="width: 100%;">
    <tbody>

    <input type="hidden" name="supplier_id_detail_page" id="supplier_id_detail_page" value="{{$id}}">

    <tr>
      <td class="fontbold">Reference Name <b style="color: red;">*</b></td>
      <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="reference_name"  data-fieldvalue="{{@$supplier->reference_name}}">
        {{(@$supplier->reference_name!=null)?@$supplier->reference_name:'--'}}
        </span>

        <input type="text" autocomplete="nope" name="reference_name" class="fieldFocus d-none" value="{{(@$supplier->reference_name!=null)?$supplier->reference_name:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold"> {{$global_terminologies['company_name']}} <b style="color: red;">*</b></td>
      <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="company"  data-fieldvalue="{{@$supplier->company}}">
        {{(@$supplier->company!=null)?@$supplier->company:'--'}}
        </span>

        <input type="text" autocomplete="nope" name="company" class="fieldFocus d-none" value="{{(@$supplier->company!=null)?$supplier->company:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Email</td>
      <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="email"  data-fieldvalue="{{@$supplier->email}}">
        {{(@$supplier->email!=null)?@$supplier->email:'--'}}
        </span>

        <input type="email" autocomplete="nope" name="email" class="fieldFocus d-none" value="{{(@$supplier->email!=null)?$supplier->email:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Primary Tel</td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="phone"  data-fieldvalue="{{@$supplier->phone}}">
        {{(@$supplier->phone!=null)?@$supplier->phone:'--'}}
        </span>

        <input type="text" autocomplete="nope" name="phone" class="fieldFocus d-none" value="{{(@$supplier->phone!=null)?$supplier->phone:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">Address <b style="color: red;">*</b></td>
      <td class="">
        <span class="m-l-15 inputDoubleClick" id="address_line_1"  data-fieldvalue="{{@$supplier->address_line_1}}">
          {{(@$supplier->address_line_1!=null)?@$supplier->address_line_1:'--'}}
        </span>
        <input type="text" name="address_line_1" class="fieldFocus d-none" value="{{(@$supplier->address_line_1!=null)?$supplier->address_line_1:''}}">

        <br>
        @if($supplier->address_line_2 != NULL)
        <span class="m-l-15 inputDoubleClick" id="address_line_2"  data-fieldvalue="{{@$supplier->address_line_2}}">
        {{$supplier->address_line_2}}
        </span>

        <input type="text" autocomplete="nope" name="address_line_2" class="fieldFocus d-none" value="{{(@$supplier->address_line_2!=null)?$supplier->address_line_2:''}}">
        @endif
      </td>
    </tr>

    <tr>
      <td class="fontbold">Country <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="country"  data-fieldvalue="{{@$supplier->getcountry->id}}">
          {{$supplier->country != null ? @$supplier->getcountry->name : 'Select Country'}}
        </span>
        <div class="d-none country-div">
          <select name="country" class="selectFocus form-control select-two prod-category ">
            <option value="" disabled="" selected="">Choose Country</option>
            @if($countries->count() > 0)
            @foreach($countries as $country)
            <option {{ (@$country->name == @$supplier->getcountry->name ? 'selected' : '' ) }} value="{{ $country->id }}">{{ $country->name }}</option>
            @endforeach
            @endif
          </select>
        </div>
    </td>
    </tr>

    <tr>
      <td class="fontbold"> District</td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="state"  data-fieldvalue="{{@$supplier->getstate->id}}">
          {{$supplier->state != null ? @$supplier->getstate->name : 'Select State'}}
        </span>
        <div class="d-none state-div">
          <select name="state" class="selectFocus form-control select-two country-states" id="state-select">
            <option value="" disabled="" selected="">Choose District</option>
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
          </select>
        </div>
      </td>
    </tr>

    <tr>
      <td class="fontbold">City <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="city"  data-fieldvalue="{{@$supplier->city}}">
          {{(@$supplier->city!=null)?@$supplier->city:'--'}}
        </span>
        <input type="text" autocomplete="nope" name="city" class="fieldFocus d-none" value="{{(@$supplier->city!=null)?$supplier->city:''}}">
      </td>
    </tr>

    <tr>
      <td class="fontbold">@if(!array_key_exists('zip_code', $global_terminologies)) Zip Code @else {{$global_terminologies['zip_code']}} @endif <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="postalcode"  data-fieldvalue="{{@$supplier->postalcode}}">
          {{(@$supplier->postalcode!=null)?@$supplier->postalcode:'--'}}
        </span>
        <input type="text" autocomplete="nope" name="postalcode" class="fieldFocus d-none" value="{{($supplier->postalcode!=null)?$supplier->postalcode:''}}">
      </td>
    </tr>

    @if($supplier->secondary_phone != NULL)
    <tr>
      <td class="fontbold text-nowrap">Additional Contacts </td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="additional-contact"  data-fieldvalue="{{@$supplier->secondary_phone}}">
        {{$supplier->secondary_phone}}
        </span>
        <input type="number" autocomplete="nope" name="additional-contact" class="fieldFocus d-none" value="{{(@$supplier->secondary_phone!=null)?$supplier->secondary_phone:''}}">
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
        <option value="" disabled="" selected="">Choose Currency</option>
        @if($currencies->count() > 0)
        @foreach($currencies as $currencie)
        <option {{ (@$currencie->id == @$supplier->currency_id ? 'selected' : '' ) }} value="{{ $currencie->id }}">{{ $currencie->currency_name }}</option>
        @endforeach
        @endif
        </select>
      </td>
    </tr>

    <tr>
      <td class="fontbold">Payment Terms</td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="credit_term"  data-fieldvalue="{{@$supplier->credit_term}}">
        {{$supplier->credit_term != null ? @$supplier->getpayment_term->title : 'Select Credit Term'}}
        </span>
        <select name="credit_term" class="selectFocus form-control credit-term d-none">
        <option value="" disabled="" selected="">Choose Credit Term</option>
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
      <td class="fontbold">Tax ID</td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="tax_id"  data-fieldvalue="{{@$supplier->tax_id}}">
          {{(@$supplier->tax_id!=null)?@$supplier->tax_id:'--'}}
        </span>
        <input type="text" autocomplete="nope" name="tax_id" class="fieldFocus d-none" value="{{(@$supplier->tax_id!=null)?$supplier->tax_id:''}}">
      </td>
    </tr>

    <tr>
    <td class="fontbold">Categories</td>
    <td>
      <span class="m-l-15" id="category_id">
      @if($supplier_categories_titles)
        <i class="fa fa-edit supplier-cats"></i>
        @foreach ($parent_categories1 as $cat)
            <ul style="font-weight:bold; margin-left:-20px;">{{$cat->title}}</ul>
            @foreach ($supplier_categories_titles as $sub)
                @if($sub->categoryTitle != null && $sub->categoryTitle->parent_category != null && $sub->categoryTitle->parent_category->id == $cat->id)
                <li style="margin-left:50px;">{{$sub->categoryTitle->title}}</li>
                @endif
            @endforeach
        @endforeach
      @else
        <p>No Categories Found</p>
        <i class="fa fa-edit supplier-cats"></i>
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
    <tr>
      <td class="fontbold"><div style="width: 150px;">Supplier #</div></td>
      <td>
        <span class="m-l-15 inputDoubleClick" id="reference_number"  data-fieldvalue="{{@$supplier->reference_number}}">
        {{(@$supplier->reference_number!=null)?@$supplier->reference_number:'--'}}
        </span>

        <input type="text" autocomplete="nope" name="reference_number" class="fieldFocus d-none" value="{{(@$supplier->reference_number!=null)?$supplier->reference_number:''}}">
      </td>
    </tr>

    </tbody>
  </table>
  </div>
  </div>

  <div class="col-lg-6 col-md-6">
    <div class="bg-white h-100 const-font">
      <div class="d-flex justify-content-between p-3">

    </div>
    <div class="inner-div pl-3 pr-3 pb-5" id="myNotes">

      <div class="inner-div-detail p-3">
      @foreach($supplierNotes as $note)
        <div class="para-detail1 bg-white p-3">
          <p>{{@$note->note_description}}</p>
        </div>

        <div class="d-flex justify-content-between pt-2 pb-2">
            <div class="col-7 pull-left">
                <p>by {{@$note->getuser->name}} | {{Carbon::parse(@$note->created_at)->format('M d Y')}} </p>
            </div>
            <div class="col-5 pull-left">
                <a href="javascript:void(0)" class="deleteNote pull-right" data-id="{{$note->id}}"><i class="fa fa-trash" ></i></a>
                <a href="javascript:void(0)" style="margin-right: 10px;" class="editNote pull-right" data-note="{{@$note->note_description}}" data-id="{{$note->id}}"><i class="fa fa-edit" ></i></a>
            </div>
        </div>
      @endforeach
      </div>

    </div>
    </div>
  </div>

  <div class="col-lg-6 col-md-6 mt-2">

  <div class="row headings-color mt-2 mb-2">
    <div class="col-lg-6 col-md-8">
      <h3 class="mb-0">@if(!array_key_exists('supplier_contacts', $global_terminologies)) Supplier Contacts @else {{$global_terminologies['supplier_contacts']}} @endif</h3>
    </div>
    <div class="col-lg-6 col-md-4">
      <button class="btn add-btn btn-color pull-right mb-0 supplier_contacts"><i class="fa fa-plus"></i> Add</button>
    </div>
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

  <div class="col-lg-6 col-md-6 mt-2">

  <div class="row headings-color mt-2 mb-2">
    <div class="col-lg-6 col-md-9">
      <h3 class="mb-0">@if(!array_key_exists('general_document', $global_terminologies)) General Documents @else {{$global_terminologies['general_document']}} @endif</h3>
    </div>
    <div class="col-lg-6 col-md-3">
      <button class="btn add-btn btn-color pull-right" data-toggle="modal" data-target="#addDocumentModal"><i class="fa fa-plus"></i> Add</button>
    </div>
  </div>
  <div class="entriesbg bg-white custompadding customborder">
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

  <div class="col-lg-6 col-md-6 mt-2">

  <div class="row headings-color mt-2 mb-2">
    <div class="col-lg-6 col-md-9">
      <h3 class="mb-0"> @if(!array_key_exists('supplier_accounts', $global_terminologies)) Supplier Accounts @else {{$global_terminologies['supplier_accounts']}} @endif</h3>
    </div>
    <div class="col-lg-6 col-md-3">
      <button class="btn add-btn btn-color pull-right mb-0 supplier_accounts"><i class="fa fa-plus"></i> Add</button>
    </div>
  </div>

  <div class="entriesbg bg-white custompadding customborder table-responsive">
  <table class="supplier-account-table table entriestable table-bordered text-center">
    <thead>
      <tr>
        <th>Bank Name</th>
        <th>Bank Address</th>
        <th>Account Name</th>
        <th>Account No</th>
        <th>Swift Code</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
  </div>
  </div>

  <div class="col-lg-6 col-md-6 mt-2">
  <div class="row headings-color mt-2 mb-2">
    <div class="col-lg-12">
      <h3 class="mb-0">@if(!array_key_exists('purchase_orders', $global_terminologies)) Purchase Orders @else {{$global_terminologies['purchase_orders']}} @endif</h3>
    </div>
  </div>

  <div class="bg-white h-80 p-2">
  <table id="example" class="table text-center headings-color const-font table-bordered" style="width:100%;">
    <thead class="sales-coordinator-thead table-bordered">
      <tr>
        <th>Month</th>
        <th>Total Order</th>
        <th>Paid</th>
        <th>Unpaid</th>
      </tr>
    </thead>
    <tbody>
      @if($porders_data->count() > 0)
      @foreach($porders_data as $order)
      <tr>
        <td align="left">{{date('M, Y',strtotime($order[0]->created_at))}}</td>
        <td align="left">{{number_format($order->sum('total'),2,'.',',')}} {{$order[0]->PoSupplier->getCurrency->currency_symbol}}</td>
        <td align="right">00.00</td>
        <td align="right">{{number_format($order->sum('total'),2,'.',',')}} {{$order[0]->PoSupplier->getCurrency->currency_symbol}}</td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan="4" align="center">No Purchase Orders Found</td>
      </tr>
      @endif
    </tbody>
  </table>


  </div>
  </div>

  <div class="col-lg-6 col-md-6 mt-2">
  <div class="row headings-color mt-2 mb-2">
    <div class="col-lg-12">
      <h3 class="mb-0">@if(!array_key_exists('purchase_order_documents', $global_terminologies)) Purchase Order Documents @else {{$global_terminologies['purchase_order_documents']}} @endif</h3>
    </div>
  </div>

  <div class="bg-white h-80 p-2 table-responsive">
  <table id="example" class="table text-center headings-color const-font table-bordered" style="width:100%;">
    <thead class="sales-coordinator-thead table-bordered">
      <tr>
        <th>PO Number</th>
        <th>PO Date</th>
        <th>PO Completed Data</th>
        <th>Documents</th>
      </tr>
    </thead>
    <tbody>
      @if($supplier_order_docs->count() > 0)
      @foreach($supplier_order_docs as $item)
      <tr>
        <td align="left">PO-{{$item->PurchaseOrder->id}}</td>
        <td align="left">{{Carbon::parse(@$item->PurchaseOrder->created_at)->format('d/m/Y')}}</td>
        <td align="left">{{Carbon::parse(@$item->PurchaseOrder->confirm_date)->format('d/m/Y')}}</td>
        <td align="left" class="">
          <a href="{{ asset('public/uploads/documents/'.$item->file_name) }}" title="{{$item->file_name}}" download="" class="text-center actionicon download" style="cursor: pointer;"><i class="fa fa-download"></i></a>
        </td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan="4" align="center">No Order Documents Found</td>
      </tr>
      @endif
    </tbody>
  </table>


  </div>
  </div>

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
      <h4 class="modal-title">Add Supplier Notes</h4>
      <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <form role="form" class="add-sup-note-form" enctype="multipart/form-data">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="row">
            <div class="col-xs-12 col-md-12">
              {{--<div class="form-group">
                <label>Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="Note Title" name="note_title">
              </div>--}}

              <div class="form-group">
                <label>Description <span class="text-danger">*</span> <small>(190 Characters Max)</small></label>
                <textarea id="note_description" class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="190"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal footer -->
    <div class="modal-footer">
      <input type="hidden" name="supplier_id_note" class="note-supplier-id">
      <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
    </div>
   </form>

  </div>
</div>
</div>

<!-- Modal For Edit Note -->
<div class="modal" id="edit_notes_modal">
<div class="modal-dialog">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Edit Supplier Note</h4>
      <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <form role="form" class="edit-sup-note-form" enctype="multipart/form-data">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="row">
            <div class="col-xs-12 col-md-12">

              <div class="form-group">
                <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                <textarea class="form-control note_description" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal footer -->
    <div class="modal-footer">
      <input type="hidden" name="supplier_note_id" class="supplier_note_id">
      <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
      <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
    </div>
   </form>

  </div>
</div>
</div>

<!-- Add Supplier contacts modal -->
<div class="modal supplierContactModal" id="supplierContactModal">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
  <h4 class="modal-title">Add @if(!array_key_exists('supplier_contacts', $global_terminologies)) Supplier Contact @else {{$global_terminologies['supplier_contacts']}} @endif</h4>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>


<form id="addSupplierContactForm" method="POST">

  <div class="modal-body">

    <input type="hidden" name="supplier_id" id="supplier_id" value="{{$supplier->id}}">

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
    <input type="submit" class="btn btn-primary " id="addSupplierContact" value="Add">
  </div>
</form>

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

<!-- Product Upload Loader Modal -->
<div class="modal" id="product_upload_loader" role="dialog">
<div class="modal-dialog modal-sm">
  <div class="modal-content">
    <div class="modal-body">
      <h3 style="text-align:center;">Please wait</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>

      <div class="alert alert-primary export-alert-bulk d-none"  role="alert">
        <i class="fa fa-spinner fa-spin"></i>
        <b> Data is importing... </b>
      </div>

      <div class="alert alert-success export-alert-success-bulk d-none"  role="alert">
        <i class=" fa fa-check "></i>
        <b>Data Imported Successfully !!!</b>
      </div>

      <div class="alert alert-primary export-alert-another-user-bulk d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Data is importing by another user! Please wait... </b>
      </div>

    </div>
  </div>
</div>
</div>
<!-- New Design Ends Here -->
<!--  Content End Here -->

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

      <input type="hidden" name="supplier_docs_id" value="{{@$id}}">

      <div class="form-group">
        <label class="pull-left">Select Documents To Upload</label>
        <input class="font-weight-bold form-control-lg form-control" id="fileee" name="supplier_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
        <span style="color: red;" id="invalid"></span>
      </div>

      <div class="form-group">
        <label for="description">Description:</label>
        <textarea class="form-control" rows="5" id="description" name="description" required="true"></textarea>
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

<form id="excelExportFormData" class="excelExportFormData" enctype="multipart/form-data">

</form>

@endsection

@section('javascript')
@if(Auth::user()->role_id == 3 || Auth::user()->role_id == 7)
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.inputDoubleClick').removeClass('inputDoubleClick');
      $('.inputDoubleClickContacts').removeClass('inputDoubleClickContacts');
      $('.selectDoubleClick').removeClass('selectDoubleClick');
      $('.prodSuppInputDoubleClick').removeClass('prodSuppInputDoubleClick');
      $('.inputDoubleClickFirst').removeClass('inputDoubleClickFirst');
      $('.inputDoubleClickFixedPrice').removeClass('inputDoubleClickFixedPrice');
      $('.selectDoubleClickPM').removeClass('selectDoubleClickPM');
      $('.inputDoubleClickPM').removeClass('inputDoubleClickPM');
      $('.market_price_check').attr('disabled',true);
      $('#add-product-image-btn').hide();
      $('#add-cust-fp-btn').hide();
      $('.btn').addClass('d-none');
      $('.default_supplier').addClass('d-none');
      $('#quantity-upload-div').addClass('d-none');
    });
  </script>
@endif
<script type="text/javascript">
$('.state-tags').select2();

$(document).on('click','.export_excel_btn',function(){
  $('#excelExportFormData').submit();
});

$(document).on('submit', '#excelExportFormData', function(e){
    e.preventDefault();

    $.ajax({
      method: "get",
      url: "{{route('export-bulk-products-file-download')}}" ,
      data: $(this).serialize(),
      beforeSend: function(){
        $('.export-alert').removeClass('d-none');
        $('.export-alert-success').addClass('d-none');
        $('.example-export-alert-success').addClass('d-none');
      },
      success: function(data)
      {
        if(data.success)
        {
          $(".example-export-alert-success").removeClass('d-none');
          $(".export-alert").addClass('d-none');
        }
      },
    });
})

$('#add_notes_modal').on('hidden.bs.modal', function () {
  $('#note_description').removeClass("is-invalid");
  $('.invalid-feedback').hide();
});

$('#uploadModal').on('hidden.bs.modal', function () {
  $(this).find('form')[0].reset();
});

/*$(document).on('click','.custom-success-btn', function(){
  $('.custom-success-btn').prop('disabled', true);
  $('#loader_modal').modal({
    backdrop: 'static',
    keyboard: false
  });
  $('#loader_modal').modal('show');
});*/

$( document ).ready(function() {
  $(".select-two").select2({
    tags: true
  });
  var role = "{{Auth::user()->role_id}}";
  var show = true;
  if(role == 3)
  {
    show = false;
  }
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
      $x.next().next('span').removeClass('d-none');
      $x.next().next('span').addClass('active');

     }, 300);
});

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

$(document).on('click','.supplier-cats', function(){
  $('#update-cats').modal('show');
});

$(document).on('click','.bulk_product', function(){
   $('.export-alert-success').addClass('d-none');
   // $('.pro-export-alert-success').removeClass('d-none');
});
$(document).on('click','.bulk_price', function(){
   $('.pro-export-alert-success').addClass('d-none');
   // $('.export-alert-success').removeClass('d-none');
});

$(document).on('click','.supplier-tags', function(){
  $('.update-tags').removeClass('d-none');
  $('.update-tag-btn').removeClass('d-none');
  $('.update-tags input').focus();
  $('.abc').addClass('d-none');
  $('.fa-edit').addClass('d-none');
});

$(document).on("click",".update-tag-btn",function(){
    var tags =  $('input[name=main_tags]').val();
    var thisPointer = 'tags Object';
    var field_name = 'main_tags';
    var field_value = tags;
    var id = 0;
    saveSupData(thisPointer,field_name,field_value,id);
  })

$(document).on("change",".selectFocus",function() {

  if($(this).attr('name') == 'country')
  {
    var country_name = $("option:selected", this).html();
    var country_id   = $("option:selected", this).val();
    var thisPointer  = $(this);
    thisPointer.addClass('d-none');
    thisPointer.prev().removeClass('d-none');
    $(this).prev().html(country_name);
    saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    $.ajax({
      method: "get",
      url: "{{ url('getting-country-states') }}",
      dataType: 'json',
      context: this,
      data: {country_id:country_id},
      beforeSend: function(){
        // shahsky here
      },
      success: function(data)
      {
        if(data.html_string)
        {
          // alert(data.html_string);
          $('.country-div').addClass('d-none');
          $(".inputDoubleClick").removeClass("d-none");
          $("#country").html(country_name);
          $('.country-states').empty();
          $(".country-states").append(data.html_string);
          $(".state-div").removeClass('d-none');
          $("#state").addClass('d-none');
        }
      },
    });
  }
  else if($(this).attr('name') == 'state')
  {
    var new_value = $("option:selected", this).html();
    var thisPointer=$(this);
    $(".state-div").addClass('d-none');
    $(".country-states").addClass('d-none');
    $(".inputDoubleClick").removeClass('d-none');
    $("#state").html(new_value);
    saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
  }

  else if($(this).attr('name') == 'credit_term')
  {
    var new_value = $("option:selected", this).html();
    var thisPointer=$(this);
    thisPointer.addClass('d-none');
    thisPointer.prev().removeClass('d-none');
    $(this).prev().html(new_value);
    saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
  }

  else if($(this).attr('name') == 'currency_id')
  {
    var new_value = $("option:selected", this).html();
    var thisPointer=$(this);
    thisPointer.addClass('d-none');
    thisPointer.prev().removeClass('d-none');
    $(this).prev().html(new_value);
    saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
  }

});

// to make that field on its orignal state
$(document).on('keypress keyup focusout', '.fieldFocus', function(e){

  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active')){

      // alert($(this).val());
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
              saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
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
        if(fieldvalue === new_value)
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
            // alert(new_value);
            $(this).prev().removeData('fieldvalue');
            $(this).prev().data('fieldvalue', new_value);
            $(this).attr('value', new_value);
            $(this).prev().html(new_value);
          }
          saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
        }
      }
    }

});

function saveSupData(thisPointer,field_name,field_value){
    console.log(thisPointer+' '+' '+field_name+' '+field_value);
    var supplier_id= $("#supplier_id_detail_page").val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('save-supp-data-supp-detail-page') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
      data: 'supplier_id='+supplier_id+'&'+field_name+'='+encodeURIComponent(field_value),
      beforeSend:function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(data)
      {
        $("#loader_modal").modal('hide');
        if(data.success == true && data.completed == 0)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

          if(field_name == "main_tags")
          {
            location.reload();
          }
          return true;
        }
        if(data.success == true && data.completed == 1)
        {
          toastr.success('Success!', 'Supplier Marked As Active Supplier.',{"positionClass": "toast-bottom-right"});
          setTimeout(function(){
            window.location.reload();
          }, 1000);
        }
        if(data.success == false && data.supplier_code_exists == true)
        {
          toastr.error('Error!', 'Supplier # already exists.',{"positionClass": "toast-bottom-right"});
        }

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }

// updating supplier cats
$('.save-supplier-cats').on('submit', function(e){
e.preventDefault();
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
});
$.ajax({
    url: "{{ route('add-supplier-cats') }}",
    /*dataType: 'json',*/
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
      $('#loader_modal').modal('show');
    },
    success: function(result){
      $('#loader_modal').modal('hide');
      if(result.success == true)
      {
        $('#update-cats').modal('hide');
        toastr.success('Success!', 'Categories Updated successfully',{"positionClass": "toast-bottom-right"});
        setTimeout(function(){
          window.location.reload();
        }, 1000);
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

$(document).on('click', '.add-notes', function(e){
  var supplier_id = $(this).data('id');
  $('.note-supplier-id').val(supplier_id);
});

$('.add-sup-note-form').on('submit', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('add-supplier-notes') }}",
       /*dataType: 'json',*/
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
        // $('#loader_modal').modal('hide');
        if(result.success == true){
          toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
          $('.add-sup-note-form')[0].reset();
          $('#add_notes_modal').modal('hide');
          setTimeout(function(){
              window.location.reload();
            }, 500);

        }else{
          // $('#loader_modal').modal('hide');
           $('#add_notes_modal').modal('hide');
           toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
          setTimeout(function(){
              window.location.reload();
            }, 300);
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

$('.edit-sup-note-form').on('submit', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('edit-supplier-notes') }}",
       /*dataType: 'json',*/
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      beforeSend: function(){
        $('.edit-sup-note-form .save-btn').addClass('disabled');
        $('.edit-sup-note-form .save-btn').attr('disabled', true);
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(result){
        $('.edit-sup-note-form .save-btn').attr('disabled', true);
        $('.edit-sup-note-form .save-btn').removeAttr('disabled');
        $('#loader_modal').modal('hide');
          toastr.success('Success!', 'Note Updated successfully',{"positionClass": "toast-bottom-right"});
          $('.edit-sup-note-form')[0].reset();
          $('#edit_notes_modal').modal('hide');
          setTimeout(function(){
              window.location.reload();
          }, 500);
      },
      error: function (request, status, error) {
            /*$('.form-control').removeClass('is-invalid');
            $('.form-control').next().remove();*/
            $('#loader_modal').modal('hide');
            $('.edit-sup-note-form .save-btn').removeClass('disabled');
            $('.edit-sup-note-form .save-btn').removeAttr('disabled');
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
                $('.edit-sup-note-form input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                 $('.edit-sup-note-form input[name="'+key+'"]').addClass('is-invalid');
                 $('.edit-sup-note-form textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                 $('.edit-sup-note-form textarea[name="'+key+'"]').addClass('is-invalid');


            });
        }
    });
  });

var id = $("#supplier_id").val();

$('.supplier-contact-table').DataTable({
  "sPaginationType": "listbox",
  processing: true,
  "language": {
  processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:true,
  "scrollX":true,
  serverSide: true,
  "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,4 ] },
    {
      "targets": [ 5 ],
      "visible": show,
      "searchable": false
    }
  ],
  lengthMenu: [ 100, 200, 300, 400],
  ajax: {
  url:"{!! route('get-supplier-contacts') !!}",
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
  ],
  initComplete: function () {
    if("{{Auth::user()->role_id}}" == 7)
    {
      $('.inputDoubleClickContacts').removeClass('inputDoubleClickContacts');
    }
  }
});
// Delete supplier Account
$(document).on('click', '.deleteSupplierAccount', function(e){

  var id = $(this).data('id');
    swal({
      title: "Are you sure?",
      text: "You want to delete the account ?",
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
        url: "{{ route('delete-supplier-account') }}",
        beforeSend:function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(response){
          $("#loader_modal").modal('hide');
          if(response.success === true){
            toastr.success('Success!', 'Account Deleted Successfully.',{"positionClass": "toast-bottom-right"});
            $('.supplier-account-table').DataTable().ajax.reload();
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
// Delete supplier contact
$(document).on('click', '.deleteSupplierContact', function(e){

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
        url: "{{ route('delete-supplier-contact') }}",
        beforeSend:function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(response){
          $("#loader_modal").modal('hide');
          if(response.success === true){
            toastr.success('Success!', 'Contact Deleted Successfully.',{"positionClass": "toast-bottom-right"});
            $('.supplier-contact-table').DataTable().ajax.reload();
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

var id = $("#supplier_id").val();
//supplier accounts
$('.supplier-account-table').DataTable({
  "sPaginationType": "listbox",
  processing: true,
  "language": {
  processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:true,
  "scrollX":true,
  serverSide: true,
   "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,4 ] },
     {
        "targets": [ 5 ],
        "visible": show,
        "searchable": false
    }
  ],
  lengthMenu: [ 100, 200, 300, 400],
  ajax: {
    url:"{!! route('get-supplier-accounts') !!}",
    data: function(data) { data.id = id } ,
      },
  columns:
  [
    { data: 'bank_name', name: 'bank_name' },
    { data: 'bank_address', name: 'bank_address' },
    { data: 'account_name', name: 'account_name' },
    { data: 'account_no', name: 'account_no' },
    { data: 'swift_code', name: 'swift_code' },
    { data: 'action', name: 'action' },
  ]
});
var id = $("#supplier_id").val();

$(document).on('click', '.supplier_accounts', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ route('add-supplier-accounts') }}",
      method: 'post',
      data: {id:id},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(result){
        $('#loader_modal').modal('hide');
        if(result.success === true){
          toastr.success('Success!', 'Supplier Account added successfully',{"positionClass": "toast-bottom-right"});
          // $('#addSupplierContactForm')[0].reset();
          // $('.supplierContactModal').modal('hide');
          $('.supplier-account-table').DataTable().ajax.reload();
        }
      },
      error: function (request, status, error) {
        $('#loader_modal').modal('hide');
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

$(document).on('click', '.supplier_contacts', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ route('add-supplier-contacts') }}",
      method: 'post',
      data: $('#addSupplierContactForm').serialize(),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(result){
        $('#loader_modal').modal('hide');
        if(result.success === true){
          toastr.success('Success!', 'Supplier Contact added successfully',{"positionClass": "toast-bottom-right"});
          // $('#addSupplierContactForm')[0].reset();
          // $('.supplierContactModal').modal('hide');
          $('.supplier-contact-table').DataTable().ajax.reload();
        }
      },
      error: function (request, status, error) {
        $('#loader_modal').modal('hide');
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

$('.table-general-documents').DataTable({
  "sPaginationType": "listbox",
   processing: true,
  "language": {
  processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:true,
  "scrollX":true,
  serverSide: true,
  "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1 ] },
     {
        "targets": [ 2 ],
        "visible": show,
        "searchable": false
     }
  ],
  lengthMenu: [ 100, 200, 300, 400],
  ajax: {
    url:"{!! route('get-supplier-general-docs') !!}",
    data: function(data) { data.id = id } ,
    },
  columns:
  [
    { data: 'file_name', name: 'file_name' },
    { data: 'description', name: 'description' },
    { data: 'action', name: 'action' },
  ]
});

$('.addDocumentForm').on('submit', function(e){
  e.preventDefault();

  $.ajaxSetup({
    headers:
    {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  $.ajax({
    url: "{{ route('add-supplier-general-document') }}",
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
      $('#loader_modal').modal('show');
    },
    success: function(result){

      if(result.success == true && result.invalid == 0)
      {
        toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
        $('.addDocumentForm')[0].reset();
        $('.addDocumentModal').modal('hide');
        $('#loader_modal').modal('hide');
        $('.table-general-documents').DataTable().ajax.reload();
      }
      else{
          $('#invalid').html(result.html);
      $('#loader_modal').modal('hide');
        $('.addDocumentForm')[0].reset();

        $('.table-general-documents').DataTable().ajax.reload();

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

$(document).on('click','.deleteGeneralDocument', function(){
  var id = $(this).data('id');
  var sId = $("#supplier_id").val();

  swal({
    title: "Are You Sure?",
    text: "You want to delete this Document!!!",
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
    url: "{{ url('delete-supplier-docs') }}",
    dataType: 'json',
    data: {id:id,sId:sId},

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
      if(data.success == true)
       {
          toastr.success('Success!', 'Supplier Document deleted successfully.',{"positionClass": "toast-bottom-right"});
          $('.table-general-documents').DataTable().ajax.reload();
        }
    },

    });
    }
    else
    {
      $('#loader_modal').modal('hide');
      swal("Cancelled", "", "error");
    }
  });
});

$(document).on('click','.deleteNote', function(){
  var id = $(this).data('id');
  var sId = $("#supplier_id").val();

  swal({
    title: "Are You Sure?",
    text: "You want to delete this Note!!!",
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
    url: "{{ url('delete-supplier-note') }}",
    dataType: 'json',
    data: {id:id,sId:sId},

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
      if(data.success == true)
       {
          toastr.success('Success!', 'Supplier Note deleted successfully.',{"positionClass": "toast-bottom-right"});
          window.location.reload();
        }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
    });
    }
    else
    {
      $('#loader_modal').modal('hide');
      swal("Cancelled", "", "error");
    }
  });

});

$(document).on('click','.editNote', function(){
  var id = $(this).data('id');
  var sId = $("#supplier_id").val();
  var note =  $(this).data("note");
  $("#edit_notes_modal .note_description").val(note);
  $("#edit_notes_modal .supplier_note_id").val(id);
  $("#edit_notes_modal").modal();

});

$(document).on('click','.delete_supplier',function(){
  var supplier_id = $("#supplier_id").val();

  swal({
    title: "Are You Sure?",
    text: "You want to delete this Supplier!!!",
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
    url: "{{ url('discard-supplier-from-detail') }}",
    dataType: 'json',
    data: {id:supplier_id},

    beforeSend: function(){
      $('.delete_supplier').prop('disabled',true);
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
          window.location = "{{ url('supplier') }}";
          window.history.go(-1);
          toastr.success('Success!', 'Supplier deleted successfully.',{"positionClass": "toast-bottom-right"});

        }
        if(data.success == false)
          {
            $('#loader_modal').modal('hide');
            $('.delete_supplier').prop('disabled',false);
            $('.errormsgDiv').show();
            $('#errormsg').html(data.errorMsg);
            toastr.warning('Warning!', data.errorMsg ,{"positionClass": "toast-bottom-right"});
            $('.table-suppliers').DataTable().ajax.reload();
          }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
    });
    }
    else
    {
      $('#loader_modal').modal('hide');
      swal("Cancelled", "", "error");
    }
});
});

$(document).on('click', '.closeErrorDiv', function (){
  $('.errormsgDiv').hide();
  $('.errormsgDivBulk').addClass('d-none');
});

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

$(document).on("dblclick",".inputDoubleClickAccounts",function(){
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
              saveSupContactData(id,thisPointer.attr('name'), thisPointer.val());
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
          saveSupContactData(id,thisPointer.attr('name'), thisPointer.val());
        }
      }
    }

});

$(document).on('click','.quantity-upload-btn',function(){
  $('#loader_modal').modal({
    backdrop: 'static',
    keyboard: false
  });
  $("#loader_modal").modal('show');
});

$('#alreadybtn').on('click',function(){
  $('.upload-div').show(300);
});

$('#quantity-upload-div').on('click',function(){
  $('.quantity-upload-div').toggle(300);
});

$('.quantity-upload-btn').on('click',function (e) {
  e.preventDefault();
  $('.upload-qty-excel-form').submit();
});

$('#alreadypricebtn').on('click',function(){
  $('.upload-price-div').show(300);
});

$('#alreadyProductBtn').on('click',function(){
  $('.upload-product-bulk').show(300);
});

$(document).on('keypress keyup focusout', '.fieldFocusAccount', function(e){

  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active')){


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
          saveSupAccountData(id,thisPointer.attr('name'), thisPointer.val());
        }
      }
    }

});

  $('.products-upload-btn').on('click',function(e){
    var fileInput = $.trim($("#excel").val());
    if (!fileInput && fileInput == '')
    {
      toastr.error('Error!', "Choose File First For Upload", {"positionClass": "toast-bottom-right"});
      return false;
    }
    swal({
      title: "Alert!",
      text: "Are you sure you want to upload the BULK PRODUCTS file ?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm)
      {
        $('.u-b-product-form').submit();
      }
      else
      {
        swal("Cancelled", "", "error");
        $('.u-b-product-form')[0].reset();
      }
   });
  });

  $('.u-b-product-form').on('submit',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-bulk-product') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function(){
        $('.export-alert-success-bulk').addClass('d-none');
        $('.export-alert-bulk').addClass('d-none');
        $('.export-alert-another-user-bulk').addClass('d-none');

        $('#product_upload_loader').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#product_upload_loader").data('bs.modal')._config.backdrop = 'static';
        $('#product_upload_loader').modal('show');
        $(".products-upload-btn").attr("disabled", true);
      },
      success: function(data){
        // $('#loader_modal').modal('hide');

        $(".products-upload-btn").attr("disabled", false);
        if(data.success == true)
        {
          toastr.success('Success!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDivBulk').removeClass('d-none');
          }
          $('.u-b-product-form')[0].reset();
          $('.link-of-temp-products').removeClass('d-none');
        }
        if(data.success == "withissues")
        {
          toastr.warning('Warning!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDivBulk').removeClass('d-none');
          }
          $('.u-b-product-form')[0].reset();
          $('.link-of-temp-products').removeClass('d-none');
        }
        if(data.success == false)
        {
          toastr.error('Error!', data.msg, {"positionClass": "toast-bottom-right"});
          $('.u-b-product-form')[0].reset();
        }

      },
      error: function (request, status, error) {
        $('#product_upload_loader').modal('hide');
        $(".products-upload-btn").attr("disabled", false);
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'[]"]').addClass('is-invalid');
        });
      }
    });
  });

  $(document).on('submit','.u-b-product-form',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-supplier-bulk-product-job-status') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').removeClass('d-none');
          checkStatusForSupplierProductsImport();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $("#product_upload_loader").modal('hide');
          $(".products-upload-btn").attr("disabled", false);
          checkStatusForSupplierProductsImport();
        }

      },
      error: function(request, status, error){
        $("#product_upload_loader").modal('hide');
      }
    });
  });

  var supplier_id = "{{$supplier->id}}";

  function checkStatusForSupplierProductsImport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-supplier-bulk-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
          function(){
            console.log("Calling Function Again");
            checkStatusForSupplierProductsImport();
          }, 5000);
        }
        else if(data.status == 0)
        {
          $('.export-alert-success-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.u-b-product-form')[0].reset();
          $('.link-of-temp-products').removeClass('d-none');
          $('.product_table').DataTable().ajax.reload();
          $("#product_upload_loader").modal('hide');
          $(".products-upload-btn").attr("disabled", false);

          if(data.error_msgs != null && data.error_msgs != '')
          {
            toastr.info('Information!', data.error_msgs, {"positionClass": "toast-bottom-right"});
            if (data.exception == null && data.exception == '') {
              setTimeout(function(){
                // window.location.href = "{{ url('bulk-products-upload-form') }}"+"/"+supplier_id;
              }, 500);
            }
          }
          if(data.exception != null && data.exception != '')
          {
            $('#errormsg').html(data.exception);
            $('.errormsgDiv').removeClass('d-none');
          }
          // alert('here');
          window.location.href = "{{url('bulk-products-upload-form')}}"+"/"+supplier_id;
          return;
        }
        else if(data.status == 2)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.u-b-product-form')[0].reset();

          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          $('.u-b-product-form')[0].reset();
          $('.product_table').DataTable().ajax.reload();
          $("#product_upload_loader").modal('hide');
        }
      }
    });
  }

  $('.price-upload-btn').on('click',function(e){

    var fileInput = $.trim($("#price_excel").val());
    if (!fileInput && fileInput == '')
    {
      toastr.error('Error!', "Choose File First For Upload", {"positionClass": "toast-bottom-right"});
      return false;
    }

    swal({
      title: "Alert!",
      text: "Are you sure you want to upload the BULK PRICES file ?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm)
      {
        $('.u-b-prices-form').submit();
      }
      else
      {
        swal("Cancelled", "", "error");
        $('.u-b-prices-form')[0].reset();
      }
   });
  });

  $('.u-b-prices-form').on('submit',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-prices-bulk-product') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function(){
        $('.export-alert-success-bulk').addClass('d-none');
        $('.export-alert-bulk').addClass('d-none');
        $('.export-alert-another-user-bulk').addClass('d-none');

        $('#product_upload_loader').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#product_upload_loader").data('bs.modal')._config.backdrop = 'static';
        $('#product_upload_loader').modal('show');
        $(".price-upload-btn").attr("disabled", true);
      },
      success: function(data){
        // $('#loader_modal').modal('hide');
        $(".price-upload-btn").attr("disabled", false);
        if(data.success == true)
        {
          toastr.success('Success!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDivBulk').removeClass('d-none');
          }
          $('.u-b-prices-form')[0].reset();
        }
        if(data.success == "withissues")
        {
          toastr.warning('Warning!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDivBulk').removeClass('d-none');
          }
          $('.u-b-prices-form')[0].reset();
        }
        if(data.success == false)
        {
          toastr.error('Error!', data.msg, {"positionClass": "toast-bottom-right"});
          $('.u-b-prices-form')[0].reset();
        }
      },
      error: function (request, status, error) {
          $('#product_upload_loader').modal('hide');
          $(".price-upload-btn").attr("disabled", false);
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'[]"]').addClass('is-invalid');
          });
        }
    });
  });

  $(document).on('submit','.u-b-prices-form',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-supplier-bulk-prices-job-status') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').removeClass('d-none');
          checkStatusForSupplierBulkPricesImport();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $("#product_upload_loader").modal('hide');
          $(".price-upload-btn").attr("disabled", false);
          checkStatusForSupplierBulkPricesImport();
        }

      },
      error: function(request, status, error){
        $("#product_upload_loader").modal('hide');
      }
    });
  });

  function checkStatusForSupplierBulkPricesImport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-supplier-bulk-prices')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
          function(){
            console.log("Calling Function Again");
            checkStatusForSupplierBulkPricesImport();
          }, 5000);
        }
        else if(data.status == 0)
        {
          $('.export-alert-success-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.u-b-prices-form')[0].reset();
          // $('.link-of-temp-products').removeClass('d-none');
          $("#product_upload_loader").modal('hide');
          $(".price-upload-btn").attr("disabled", false);

          if(data.error_msgs != null && data.error_msgs != '')
          {
            toastr.info('Information!', data.error_msgs, {"positionClass": "toast-bottom-right"});
          }
          if(data.exception != null && data.exception != '')
          {
            $('#errormsg').html(data.exception);
            $('.errormsgDiv').removeClass('d-none');
          }
        }
        else if(data.status == 2)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.u-b-prices-form')[0].reset();

          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          $('.u-b-prices-form')[0].reset();
          $(".price-upload-btn").attr("disabled", false);
          $("#product_upload_loader").modal('hide');
        }
      }
    });
  }

  $("#filteredProducts").submit(function(e) {
    e.preventDefault();
    var primary_category = $('.selecting-primary-cat').val();
    var fill_sub_cat_div = $('.fill_sub_cat_div').val();

    if(primary_category != '' || fill_sub_cat_div != '')
    {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"post",
        url:"{{route('get-filtered-prod-excel')}}",
        data:new FormData(this),
        processData:false,
        contentType:false,
        cache:false,
        beforeSend:function(){
          $('.export-btn').prop('disabled',true);
        },
        success:function(data){
          if(data.status==1)
          {
            $('.export-alert-success').addClass('d-none');
            $('.export-alert').removeClass('d-none');
            $('.export-btn').prop('disabled',true);
            console.log("Calling Function from first part");
            checkStatusForBulkPrice();
          }
          else if(data.status==2)
          {
            $('.export-alert-another-user').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-btn').prop('disabled',true);
            checkStatusForBulkPrice();
          }
        },
        error:function(){
          $('.export-btn').prop('disabled',false);
        }
      });
    }
    else
    {
      swal('Please Select Product Category For Filtering Products');
      e.preventDefault();
      return false;
    }
  });

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-bulk-price')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-alert-success').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForBulkPrice();
        }
      }
    });
  });

  function checkStatusForBulkPrice()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-bulk-price')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForBulkPrice();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);

        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }


  $("#supplierProductsPrices").submit(function(e) {
    e.preventDefault();
    var primary_category = $('.supplier-product-category-prices').val();
    var fill_sub_cat_div = $('.supplier-product-sub-category-prices').val();

    // if(primary_category != '' || fill_sub_cat_div != '')
    // {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"post",
        url:"{{route('download-supplier-all-products')}}",
        data:new FormData(this),
        processData:false,
        contentType:false,
        cache:false,
        beforeSend:function(){
          $('.export-btn').prop('disabled',true);
        },
        success:function(data){
          if(data.status==1)
          {
            $('.pro-export-alert-success').addClass('d-none');
            $('.export-alert').removeClass('d-none');
            $('.export-btn').prop('disabled',true);
            console.log("Calling Function from first part");
            checkStatusForBulkProduct();
          }
          else if(data.status==2)
          {
            $('.export-alert-another-user').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-btn').prop('disabled',true);
            checkStatusForBulkProduct();
          }
        },
        error:function(){
          $('.export-btn').prop('disabled',false);
        }
      });
    // }
    // else
    // {
    //   swal('Please Select Product Category For Filtering Products');
    //   e.preventDefault();
    //   return false;
    // }
  });

  $("#supplierProducts").submit(function(e) {
    e.preventDefault();
    var primary_category = $('.supplier-product-category').val();
    var fill_sub_cat_div = $('.supplier-product-sub-category').val();

    // if(primary_category != '' || fill_sub_cat_div != '')
    // {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"post",
        url:"{{route('download-supplier-all-products')}}",
        data:new FormData(this),
        processData:false,
        contentType:false,
        cache:false,
        beforeSend:function(){
          $('.export-btn').prop('disabled',true);
          $('.example-export-alert-success').addClass('d-none');
        },
        success:function(data){
          if(data.status==1)
          {
            $('.pro-export-alert-success').addClass('d-none');
            $('.export-alert').removeClass('d-none');
            $('.export-btn').prop('disabled',true);
            console.log("Calling Function from first part");
            checkStatusForBulkProduct();
          }
          else if(data.status==2)
          {
            $('.export-alert-another-user').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.export-btn').prop('disabled',true);
            checkStatusForBulkProduct();
          }
        },
        error:function(){
          $('.export-btn').prop('disabled',false);
        }
      });
    // }
    // else
    // {
    //   swal('Please Select Product Category For Filtering Products');
    //   e.preventDefault();
    //   return false;
    // }
  });

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-bulk-product')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.pro-export-alert-success').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForBulkProduct();
        }
      }
    });
  });

  function checkStatusForBulkProduct()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-bulk-product')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForBulkProduct();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.pro-export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);

        }
        else if(data.status==2)
        {
          $('.pro-export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  // $(document).on('change',".selecting-primary-cat",function(){
  //     var category_id=$(this).val();
  //     // var store_sb_cat =$(this);
  //     $.ajax({

  //         url:"{{route('filter-sub-category')}}",
  //         method:"get",
  //         dataType:"json",
  //         data:{category_id:category_id},
  //         beforeSend:function(){
  //           $('#loader_modal').modal({
  //             backdrop: 'static',
  //             keyboard: false
  //           });
  //           $("#loader_modal").modal('show');
  //         },
  //         success:function(data){
  //           $("#loader_modal").modal('hide');
  //             var html_string = '';
  //               html_string+="<option value=''>Select a Sub Category</option>";
  //             for(var i=0;i<data.length;i++){
  //               html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
  //             }
  //             // $("#state_div").remove();
  //             // store_sb_cat.after($("<div></div>").text(html_string));
  //             $(".fill_sub_cat_div").empty();
  //             $(".fill_sub_cat_div").append(html_string);

  //         },
  //         error: function(request, status, error){
  //           $("#loader_modal").modal('hide');
  //           alert('Error');
  //         }

  //     });
  //   });

  function saveSupAccountData(id,field_name,field_value){
    var supplier_id = "{{$id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('save-supp-account-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+'supplier_id='+supplier_id+'&'+field_name+'='+encodeURIComponent(field_value),
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

  function saveSupContactData(id,field_name,field_value){
    var supplier_id = "{{$id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('save-supp-contacts-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+'supplier_id='+supplier_id+'&'+field_name+'='+encodeURIComponent(field_value),
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

  @if(Session::has('successmsg'))
    swal( "{{ Session::get('successmsg') }}");
    @php
     Session()->forget('successmsg');
    @endphp
  @endif

  @if(Session::has('errorMsg'))
    swal( "{{ Session::get('errorMsg') }}");
    @php
     Session()->forget('errorMsg');
    @endphp
  @endif
});

</script>
@stop
