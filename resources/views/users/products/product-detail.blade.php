@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')
<style type="text/css">
  html {
    scroll-behavior: smooth !important;
}
.barcode_preview_btn:hover{
  cursor: pointer;
}
</style>

<?php
use App\Models\Common\ProductCategory;
use App\Models\Common\Unit;
use App\Models\Common\Product;
use Carbon\Carbon;
?>
<style type="text/css">
.dataTables_scroll{
    max-height: 571px;
    overflow-y: scroll;
  }

.tooltiptext{
  display: none;
}
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
.prodSuppInputDoubleClick{
  font-style: italic;
}
.inputDoubleClickFirst{
  font-style: italic;
}
.inputDoubleClickFixedPrice{
  font-style: italic;
}
.selectDoubleClickPM, .inputDoubleClickPM{
  font-style: italic;
}
.select2-container{
  width: 100% !important;
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

.delete-btn-two{
  position: absolute;
  right: 30px;
  top: -10px;
}

.datepicker-container {z-index: 1100 !important;}

.tableFix { /* Scrollable parent element */
  position: relative;
  overflow: auto;
  height: 40vh;
}

.tableFix table{
  width: 100%;
  border-collapse: collapse;
}

.tableFix th,
.tableFix td{
  padding: 8px;
  text-align: left;
}

.tableFix thead th {
  position: sticky;  /* Edge, Chrome, FF */
  top: 0px;
  background: #fff;  /* Some background is needed */
}
span#short_desc,span#supplier_description
{
  width: auto !important;
  max-width: 100%;
  display: block;
  white-space: normal;
}
span#product_notes{
   width: auto !important;
  max-width: 500px;
  display: block;
  white-space: normal;
}
.product_info tr td{
  border-bottom: 2px solid #eee;
}
.table-theme-header thead
{
  background: {{$sys_color->system_color}};
  color: white !important;
  text-align: center;
}
.table-theme-header thead tr th
{
  border: 1px solid #eee;
  color: white !important;
}
.table-theme-header tbody
{
  text-align: center;
  color: #09355a !important;
}
.table-theme-header tbody tr td
{
  border: 1px solid #eee;
  color: #09355a !important;
}
.table-theme-header tbody tr td:first-child
{
  font-weight: bold;
}
.nav-link.active {
    background-color: white !important;
    color: #09355a !important;
    border-radius: 0px;
}
.tableFix thead th{
  background: {{$sys_color->system_color}} !important;
  color: white !important;
}
.text-to-left tbody
{
  text-align: left;
}
.slider_arrows
{
  color: #09355a;
  background: black;
  padding: 5px 10px;
  border-radius: 50%;
  margin-right: 10px;
}
.slider_arrows:hover
{
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
          <li class="breadcrumb-item"><a href="{{route('complete-list-product')}}">Complete Products</a></li>
          <li class="breadcrumb-item active">Product Detail Page</li>
      </ol>
  </div>
</div>


{{-- Content Start from here --}}
<!-- Sales or Sales Coordinator -->
@if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6)
  @php
    $price_col_visibility = "class=noVis";
    $hide_pricing_columns = ', visible : false';
  @endphp
@endif
<div class="row align-items-center mb-3">
  <div class=" col-lg-4 col-md-6 title-col">
    <h5 class="maintitle fontbold headings-color mb-0">Product Detail Page </h5>
    <p>Note: The ITALIC text is double click editable.</p>
  </div>
  <div class="col-lg-8 col-md-6 title-right-col p-0" >
    <a onclick="backFunctionality()" style="float: right;" class="btn text-uppercase purch-btn mr-3 headings-color btn-color pull-right d-none">Back</a>
    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
    @if($product->status == 1 || $product->status == 2)
      <a style="float: right;" title="Update Prices" class="btn text-uppercase purch-btn mr-3 headings-color btn-color pull-right update_prices d-none">Update Prices</a>
    @endif
    @endif
    <h5 class="maintitle text-uppercase fontbold"></h5>
  </div>
</div>

{{--<input type="button" class="tn recived-button check_mkt" name="check_mkt" id="check_mkt" data-id="{{$product->id}}" value="Check MKT">--}}

<!-- new design starts here -->

<!-- Right Content Start Here -->
<div class="right-content pt-0">
<div class="row headings-color ">
<div class="col-lg-1 col-md-1 d-flex align-items-center p-0">
  <h4 class="headings-color mb-0 d-lg-block d-md-none">{{@$product->productCategory->title}} / {{@$product->productSubCategory->title}}</h4>
</div>
<div class="col-lg-1 col-md-1 p-0 d-lg-block d-md-none">
  <div class="row">
    <div class="col-lg-8 col-md-8 offset-2 p-0">
    @if($productImagesCount < 4)
      @php
        $add_btn_class = '';
      @endphp
      @else
      @php
        $add_btn_class = 'd-none';
      @endphp
    @endif
  <button class="btn recived-button view-supplier-btn btn-size {{$add_btn_class}} pull-right" data-toggle="modal" data-target="#productImagesModal" id="add-product-image-btn" type="button" title="Add Product Images" style="width: 20%" data-id="{{$id}}"><i class="fa fa-plus"></i></button>
  </div>
  </div>
</div>
<div class="col-lg-5 col-md-5 pl-2">
    <h4 class="headings-color mb-0 d-lg-block d-none ">Product Information</h4>
  </div>
<div class="col-lg-5 col-md-4 mb-2">
  <div class="row">
    <div class="col-10"> <h4 class="headings-color mb-0 d-lg-block d-none">Vendor Specific Information</h4></div>
    <div class="col-2 pl-4 pr-0">
    <!-- <button class="btn recived-button view-supplier-btn pull-right add-supplier" type="button" title="Add Product Supplier"><i class="fa fa-plus"></i> ADD</button> -->
    </div>
  </div>
</div>

</div>
</div>
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

@if($productImagesCount > 0)
<div class="col-xl-2 col-lg-2 col-md-12 banner-video">
<div class="h-100 gallery_images" id="my_images" >
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="tab1">

      <div class="logo-container2 text-center">
      @if(file_exists( public_path() . '/uploads/products/product_'.@$productImages[0]->product_id.'/'.@$productImages[0]->image))
      <img  src="{{asset('public/uploads/products/product_'.@$productImages[0]->product_id.'/'.@$productImages[0]->image)}}" class="img-fluid" alt="image" id="main-image" style="max-height: 300px;margin: auto;">
      @else
       <img src="{{ asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid" alt="image" width="100%" id="main-image" style="min-width: 100%">
      @endif
      <div class="overlay2">
        <a href="javascript:void(0)" class="icon img-uploader show-prod-image" data-id="{{$id}}" title="Add Product Images Here" data-toggle="modal" data-target="#images-modal"><i class="fa fa-camera"></i></a>
      </div>
      </div>

    </div>
  </div>
    <ul class="nav nav-tabs row text-center purchase-product-detail photo-gallery image_icons" style="max-width: 100%;" id="real-data" role="tablist">
      @php $imageCounter = 1; @endphp
      @foreach($productImages as $image)
      <li class="active col-lg-3 p-0 mt-1" id="prod-image-{{@$image->id}}" style="position: relative;max-width: 100%;">
        <a href="#tab1" aria-controls="home" role="tab" data-toggle="tab">
      <!--     <a data-img_id="{{@$image->id}}" data-prod_id="{{@$image->product_id}}" aria-expanded="true" class="close delete-prod-img-btn delete-product-image" title="Delete">&times;</a> -->
          @if(file_exists( public_path() . '/uploads/products/product_'.@$image->product_id.'/'.@$image->image))
          <img src="{{asset('public/uploads/products/product_'.@$image->product_id.'/'.@$image->image)}}" class="mt-2 tag{{$imageCounter}}" alt="image" width="80%" height="42">
          @else
          <img src="{{ asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="mt-2 tag{{$imageCounter}}" alt="image" width="80%" height="42">
          @endif
        </a>
      </li>
      @php $imageCounter++; @endphp
      @endforeach
      <div id="dummy-images" class="row ml-1"></div>
    </ul>

    <div class="d-flex justify-content-center mt-3 prev-frwrd">
      <span class="slider_arrows prev-image">
        <i class="fa fa-arrow-left"></i>
      </span>

      <span class="slider_arrows next-image">
        <i class="fa fa-arrow-right"></i>
      </span>
    </div>
    <!-- barcode section -->
    <div class="mt-3">
      <table class="table tble-brdr headings-color const-font">
        <tr>
          <td class="font-weight-bold" style="width: 40%">{{$global_terminologies['bar_code'] ?? 'Barcode'}} :</td>
          <td class="">
              <span class="m-l-15 inputDoubleClick" id="bar_code"  data-fieldvalue="{{@$product->bar_code}}">
                  {{(@$product->bar_code!=null)?@$product->bar_code:'--'}}
              </span>
          <input type="text"  name="bar_code" class="fieldFocus d-none" value="{{(@$product->bar_code!=null)?$product->bar_code:''}}">
          </td>
        </tr>
        <tr>
        <td class="font-weight-bold" style="">Barcode Type :</td>

        <td class="">
            <span class="m-l-15 selectDoubleClick" id="barcode_type" data-fieldvalue="{{@$product->barcode_type}}">
            {{(@$product->barcode_type!=null)?@$product->barcode_type:'Select'}}
            </span>

            <select name="barcode_type" id="barcode_type" class="selectFocus form-control d-none">
                <option selected>Choose Barcode Type</option>
                <option value="Code128" selected>Code 128</option>
                <option value="UPC">UPC</option>
                <option value="EAN13">EAN 13</option>
            </select>
        </td>
        </tr>
        <tr class="barcode_row d-none">
        <td colspan="2" class="text-center">
            @php
            $barcode_data = @$product->bar_code ? @$product->bar_code : @$product->refrence_code;
                if(@$product->barcode_type=='Code128'){
                //   echo DNS1D::getBarcodeHTML(@$product->bar_code ? @$product->bar_code : @$product->refrence_code, 'PDF417',$width ? $width : 3,$height ? $height : 1) . '" alt="barcode"   />';
                    echo DNS1D::getBarcodeHTML(@$barcode_data,'C128');
                }
                else if(@$product->barcode_type=='UPC'){
                    echo DNS1D::getBarcodeHTML((int)$barcode_data,'UPCA');
                }
                else if(@$product->barcode_type=='EAN13'){
                    echo DNS1D::getBarcodeHTML((int)@$barcode_data,'EAN13');
                }
            @endphp
        </td>
      </tr>
        <tr class="show_barcode">
          <td colspan="2" class="text-center">
            <span class="text-danger font-weight-bold barcode_preview_btn"><i>(Show Barcode)</i></span>
          </td>
        </tr>
        <tr class="hide_barcode d-none">
          <td colspan="2" class="text-center">
            <span class="text-danger font-weight-bold barcode_preview_btn"><i>(Hide Barcode)</i></span>
          </td>
        </tr>
      </table>
    </div>
</div>
</div>

@else

<div class="col-xl-2 col-lg-2 col-md-6 banner-video">
  <div class="row">
    <div class="col-md-9 d-lg-none d-md-block">
   <h4 class="headings-color  d-lg-none d-none d-md-block">{{@$product->productCategory->title}} / {{@$product->productSubCategory->title}}</h4>
  </div>
  <div class="col-md-3 d-lg-none d-md-block d-none btn-small">
      @if($productImagesCount < 4)
      @php
        $add_btn_class = '';
      @endphp
      @else
      @php
        $add_btn_class = 'none';
      @endphp
      @endif

  <button class="btn recived-button view-supplier-btn img-uploader {{$add_btn_class}} pull-right" style="width: 20%" data-toggle="modal" data-target="#productImagesModal" id="add-product-image-btn " type="button" title="Add Product Supplier" data-id="{{$id}}"><i class="fa fa-plus"></i></button>
</div>
</div>
<div class="h-100">
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="tab1">

    <div class="logo-container2">
      <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid lg-logo d-block " alt="image" width="100%" id="main-image">

      <div class="overlay2">
        <a href="javascript:void(0)" class="icon img-uploader" data-id="{{$id}}" title="Add Product Images Here" data-toggle="modal" data-target="#images-modal"><i class="fa fa-camera"></i></a>
      </div>
    </div>

    </div>
  </div>
    <ul class="nav nav-tabs purchase-product-detail photo-gallery row image_icons " role="tablist">
      <li class="active col-lg-12 p-0">
        <a href="#tab1" class="col-lg-3 p-0" aria-controls="home" role="tab" data-toggle="tab">
          <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid ml-2 mt-2 " alt="image" width="60">
        </a>
        <a href="#tab2" class="col-lg-3 p-0" aria-controls="home" role="tab" data-toggle="tab">
          <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid ml-2 mt-2" alt="image" width="60">
        </a>
        <a href="#tab3" class="col-lg-3 p-0" aria-controls="home" role="tab" data-toggle="tab">
          <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid ml-2 mt-2" alt="image" width="60">
        </a>
        <a href="#tab4" class="col-lg-3 p-0" aria-controls="home" role="tab" data-toggle="tab">
          <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid ml-2 mt-2" alt="image" width="60">
        </a>
      </li>
    </ul>
    <!-- barcode section -->
    <div class="mt-3">
      <table class="table tble-brdr headings-color const-font">
        <tr>
          <td class="font-weight-bold" style="width: 40%">{{$global_terminologies['bar_code'] ?? 'Barcode'}} :</td>
          <td class="">
              <span class="m-l-15 inputDoubleClick" id="bar_code"  data-fieldvalue="{{@$product->bar_code}}">
                  {{(@$product->bar_code!=null)?@$product->bar_code:'--'}}
              </span>
          <input type="text"  name="bar_code" class="fieldFocus d-none" value="{{(@$product->bar_code!=null)?$product->bar_code:''}}">
          </td>
        </tr>
        <tr>
        <td class="font-weight-bold" style="">Barcode Type :</td>

        <td class="">
            <span class="m-l-15 selectDoubleClick" id="barcode_type" data-fieldvalue="{{@$product->barcode_type}}">
            {{(@$product->barcode_type!=null)?@$product->barcode_type:'Select'}}
            </span>

            <select name="barcode_type" id="barcode_type" class="selectFocus form-control d-none">
                <option selected>Choose Barcode Type</option>
                <option value="Code128" selected>Code 128</option>
                <option value="UPC">UPC</option>
                <option value="EAN13">EAN 13</option>
            </select>
        </td>
        </tr>
        <tr class="barcode_row d-none">
        <td colspan="2" class="text-center">
            @php
            $barcode_data = @$product->bar_code ? @$product->bar_code : @$product->refrence_code;
                if(@$product->barcode_type=='Code128'){
                //   echo DNS1D::getBarcodeHTML(@$product->bar_code ? @$product->bar_code : @$product->refrence_code, 'PDF417',$width ? $width : 3,$height ? $height : 1) . '" alt="barcode"   />';
                    echo DNS1D::getBarcodeHTML(@$barcode_data,'C128');
                }
                else if(@$product->barcode_type=='UPC'){
                    echo DNS1D::getBarcodeHTML((int)$barcode_data,'UPCA');
                }
                else if(@$product->barcode_type=='EAN13'){
                    echo DNS1D::getBarcodeHTML((int)@$barcode_data,'EAN13');
                }
            @endphp
        </td>
      </tr>
        <tr class="show_barcode">
          <td colspan="2" class="text-center">
            <span class="text-danger font-weight-bold barcode_preview_btn"><i>(Show Barcode)</i></span>
          </td>
        </tr>
        <tr class="hide_barcode d-none">
          <td colspan="2" class="text-center">
            <span class="text-danger font-weight-bold barcode_preview_btn"><i>(Hide Barcode)</i></span>
          </td>
        </tr>
      </table>
    </div>
</div>
</div>

@endif

<div class="col-lg-5 col-md-6">
   <h4 class="headings-color  d-lg-none d-md-block">Product Information</h4>
<div class="bg-white">
  <table id="example" class="table tble-brdr headings-color sales-customer-table const-font product_info " style="width: 100%;">
    <tbody>

        {{-- @if () --}}
        <tr>
            <td class="font-weight-bold " style="width: 25% !important;">{{$global_terminologies['our_reference_number'] }}<b style="color: red;">*</b></td>
            <td style="width: 25% !important;">
            @php
              if(@$allow_custom_code_edit == 1)
              {
                $class = 'inputDoubleClick';
              }
              else
              {
                $class = '';
              }

            @endphp

            <span class="m-l-15 {{$class}}" id="refrence_code"  data-fieldvalue="{{@$product->refrence_code}}">
            {{(@$product->refrence_code != null) ? $product->refrence_code:'--'}}
            </span>

            <input type="text"  name="refrence_code" class="d-none fieldFocus" id="refrence_code_input" value="{{(@$product->refrence_code!=null)?$product->refrence_code:''}}">
            </td>
            @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
            @if(in_array('hs_code', $product_detail_section))
                <td class="font-weight-bold" style="width: 25% !important;border-left: 1px solid #eee;">HS Code</td>
                <td class="" style="width: 25% !important;">

                <span class="m-l-15" id="hs_code"  data-fieldvalue="">
                    {{(@$product->productSubCategory->hs_code!=null)?@$product->productSubCategory->hs_code:'--'}}</span>
              </td>
            @else
            <td style="width: 25% !important;border-left: 1px solid #eee;"></td>
            <td  class="m-l-15"></td>
            @endif
            @else
            <td class="font-weight-bold" style="width: 25% !important;border-left: 1px solid #eee;"></td>
            <td class="" style="width: 25% !important;">
              </td>
            @endif
          </tr>
        {{-- @endif --}}


      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      @if(in_array('hs_description', $product_detail_section))
      <tr>
        <td class="fontbold">HS Description</td>
        <td class="">
        <span class="m-l-15 inputDoubleClick" id="hs_description"  data-fieldvalue="{{@$product->hs_description}}">
        {{(@$product->hs_description!=null)?@$product->hs_description:'--'}}
        </span>

        <textarea name="hs_description" class="fieldFocus d-none" cols="20" rows="3">{{(@$product->hs_description!=null)?@$product->hs_description:''}}</textarea>
        </td>
        <td></td>
        <td></td>
      </tr>
      @endif
      @endif

      <tr>
        <td class="font-weight-bold" colspan="1" height="90px">{{$global_terminologies['product_description']}}<b style="color: red;">*</b></td>
        <td class="" colspan="3">
        <span class="m-l-15 inputDoubleClick" id="short_desc"  data-fieldvalue="{{@$product->short_desc}}">
        {{(@$product->short_desc!=null)?@$product->short_desc:'--'}}
        </span>

        <textarea name="short_desc" class="fieldFocus d-none" id="txt_area_desc" cols="30" rows="3">{{(@$product->short_desc!=null)?@$product->short_desc:''}}</textarea>
        </td>
      </tr>
      <!-- <tr>
        <td class="font-weight-bold"  height="90px">{{$global_terminologies['bar_code'] ?? 'Barcode'}}</td>
        <td class="width: 25% !important;border-left: 1px solid #eee;">
            <span class="m-l-15 inputDoubleClick" id="bar_code"  data-fieldvalue="{{@$product->bar_code}}">
                {{(@$product->bar_code!=null)?@$product->bar_code:'--'}}
            </span>
        <input type="text"  name="bar_code" class="fieldFocus d-none" value="{{(@$product->bar_code!=null)?$product->bar_code:''}}">
        </td>
        <td class="font-weight-bold" style="width: 25% !important;border-left: 1px solid #eee;">Barcode Type</td>

        <td class="">
            <span class="m-l-15 selectDoubleClick" id="barcode_type" data-fieldvalue="{{@$product->barcode_type}}">
            {{(@$product->barcode_type!=null)?@$product->barcode_type:'Select'}}
            </span>

            <select name="barcode_type" id="barcode_type" class="selectFocus form-control d-none">
                <option selected>Choose Barcode Type</option>
                <option value="Code128" selected>Code 128</option>
                <option value="UPC">UPC</option>
                <option value="EAN13">EAN 13</option>
            </select>
            {{-- <input type="text" name="barcode_type" class="fieldFocus d-none" value="{{(@$product->barcode_type!=null)?@$product->barcode_type:'Select'}}"> --}}
        </td>
        {{-- <input name="bar_code" class="fieldFocus d-none" id="txt_area_bar_code" value="{{(@$product->bar_code!=null)?@$product->bar_code:''}}"></input> --}}
        {{-- @php
          if(@$product->bar_code != null){
            echo '<img src="data:image/png;base64,' . DNS2D::getBarcodePNG(@$product->bar_code ? @$product->bar_code : @$product->refrence_code, 'PDF417',$width ? $width : 3,$height ? $height : 1) . '" alt="barcode"   />';
          }
        @endphp --}}

      </tr>
      <tr>
        <td colspan="4">
            @php
            $barcode_data = @$product->bar_code ? @$product->bar_code : @$product->refrence_code;
                if(@$product->barcode_type=='Code128'){
                //   echo DNS1D::getBarcodeHTML(@$product->bar_code ? @$product->bar_code : @$product->refrence_code, 'PDF417',$width ? $width : 3,$height ? $height : 1) . '" alt="barcode"   />';
                    echo DNS1D::getBarcodeHTML(@$barcode_data,'C128');
                }
                else if(@$product->barcode_type=='UPC'){
                    echo DNS1D::getBarcodeHTML((int)$barcode_data,'UPCA');
                }
                else if(@$product->barcode_type=='EAN13'){
                    echo DNS1D::getBarcodeHTML((int)@$barcode_data,'EAN13');
                }
            @endphp
        </td>
      </tr> -->
      <tr>
        @if (in_array('note_two', $product_detail_section))
          <td class="font-weight-bold">{{$global_terminologies['note_two']}}</td>
          <td class="" colspan="3">
            <span class="m-l-15 inputDoubleClick" id="product_notes"  data-fieldvalue="{{@$product->product_notes}}" >
            {{(@$product->product_notes!=null)?@$product->product_notes:'--'}}</span>

            <input type="text"  name="product_notes" class="fieldFocus d-none" value="{{(@$product->product_notes!=null)?$product->product_notes:''}}">
          </td>
        @else
        <td></td>
        <td colspan="3"></td>
        @endif

      </tr>

      <tr>
        @if (in_array('product_note_3', $product_detail_section))
          <td class="font-weight-bold" style="width: 25% !important;border-left: 1px solid #eee;">{{$global_terminologies['product_note_3']}}</td>
          <td class="" colspan="3">
            <span class="m-l-15 inputDoubleClick" id="product_note_3"  data-fieldvalue="{{@$product->product_note_3}}" >
            {{(@$product->product_note_3!=null)?@$product->product_note_3:'--'}}</span>

            <input type="text"  name="product_note_3" class="fieldFocus d-none" value="{{(@$product->product_note_3!=null)?$product->product_note_3:''}}">
          </td>
        @else
        <td></td>
        <td colspan="3"></td>
        @endif

      </tr>

      {{--<tr>

        <td class="font-weight-bold">Name <b style="color: red;">*</b></td>
        <td class="">

        <span class="m-l-15 inputDoubleClick" id="name"  data-fieldvalue="{{@$product->name}}">
        {{(@$product->name!=null)?@$product->name: @$product->short_desc}}</span>

        <input type="text"  name="name" class="fieldFocus d-none" value="{{(@$product->name!=null)?$product->name:''}}">
        </td>
      </tr>--}}

      {{--<tr>
        <td class="font-weight-bold ">Primary Category <b style="color: red;">*</b></td>
        <td class="">
        @if($product->primary_category == NULL)
        <span class="m-l-15 selectDoubleClick" id="primary_category" data-fieldvalue="{{@$product->productCategory->title}}">Select Category</span>

        <select name="primary_category" class="selectFocus form-control prod-category d-none">
          <option>Choose Category</option>
          @if($product_parent_category)
          @foreach($product_parent_category as $category)
          <option  value="{{$category->id}}">{{$category->title}}</option>
          @endforeach
          @endif
        </select>
        <input type="text" name="primary_category" class="fieldFocus d-none" value="{{@$product->productCategory->title}}">

        @else
        <span class="m-l-15 selectDoubleClick" id="primary_category"  data-fieldvalue="{{@$product->productCategory->title}}">{{@$product->productCategory->title}}</span>

        <select name="primary_category" class="selectFocus form-control prod-category d-none">
          <option>Choose Category</option>
          @if($product_parent_category)
          @foreach($product_parent_category as $category)
          <option {{ ($category->id == @$product->primary_category ? 'selected' : '' )}} value="{{$category->id}}"> {{$category->title}}</option>
          @endforeach
          @endif
        </select>
        <input type="text" name="primary_category" class="fieldFocus d-none" value="{{@$product->productCategory->title}}">
        @endif
        </td>
      </tr>

      @php
        $subCategories = App\Models\Common\ProductCategory::where('parent_id',@$product->primary_category)->get();
      @endphp

      <tr>
        <td class="font-weight-bold ">Sub-Category <b style="color: red;">*</b></td>
        <td class="">
        @if($product->category_id == 0)

          @if(@$product->primary_category != NULL)
            <span class="category-span m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="'{{@$product->productSubCategory->title}}">Select Sub-Category</span>
            <select name="category_id" class="selectFocus form-control category-id d-none">
              <option>Choose Sub-Category</option>
              @if($subCategories)
              @foreach($subCategories as $category)
              <option  value="{{$category->id}}">{{$category->title}}</option>
              @endforeach
              @endif
            </select>
          @else
            <span class="category-span m-l-15" id="category_id"  data-fieldvalue="{{@$product->productSubCategory->title}}">Select Sub-Category</span>
            <select name="category_id" class="selectFocus form-control category-id d-none">
              <option>Choose Category</option>
            </select>
          @endif

        @else

        <span class="m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="{{@$product->productSubCategory->title}}">{{@$product->productSubCategory->title}}</span>
        @if($product->primary_category != NULL)
        <select name="category_id" class="selectFocus form-control category-id d-none">
          <option>Choose Sub-Category</option>
          @if($subCategories)
          @foreach($subCategories as $category)
          <option {{ ($category->id == @$product->category_id ? 'selected' : '' )}} value="{{$category->id}}">{{$category->title}}</option>
          @endforeach
          @endif
        </select>
        @endif

        @endif
        </td>
      </tr>--}}

      <tr>
        <td class="font-weight-bold "> {{$global_terminologies['category']}} <b style="color: red;">*</b></td>
        <td class="" colspan="3">
        <span class="m-l-15 selectDoubleClick" id="category_id" data-fieldvalue="{{@$product->category_id}}">
        {{(@$product->primary_category != null)?@$product->productCategory->title:'--'}} /
        {{(@$product->category_id != null)?@$product->productSubCategory->title:'--'}}
        </span>

        <div class="incomplete-filter d-none inc-fil-cat">
          <select class="font-weight-bold form-control-lg form-control js-states state-tags selectFocus category_id" name="category_id" required="true">
            <option value="">Choose Category</option>
            @if($product_parent_category->count() > 0)
            @foreach($product_parent_category as $pcat)
            <optgroup label="{{$pcat->title}}">
              @php
                $subCat = App\Models\Common\ProductCategory::where('parent_id',$pcat->id)->orderBy('title')->get();
              @endphp
              @foreach($subCat as $scat)
              <option {{ ($scat->id == @$product->category_id ? 'selected' : '' )}} value="{{$scat->id}}">{{$scat->title}}</option>
              @endforeach
            </optgroup>
            @endforeach
            @endif
          </select>
        </div>
        </td>
      </tr>
       <tr>
        <td class="font-weight-bold " style="border-bottom:0px;">{{$global_terminologies['type']}}
        <b style="color: red;">*</b> </td>
        <td class="" style="border-bottom:0px;">
        <span class="m-l-15 selectDoubleClick" id="product_type" data-fieldvalue="{{@$product->type_id}}">
        {{(@$product->type_id != null)?@$product->productType->title:'Select Type'}}
        </span>

        <select name="type_id" class="selectFocus form-control d-none">
          <option value="" disabled="" selected="">Choose Type</option>
          @foreach($product_type as $type)
          <option value="{{$type->id}}" {{ ($product->type_id == $type->id ? "selected" : "") }} >{{$type->title}}</option>
          @endforeach
        </select>
        </td>

        @if (in_array('product_type_2', $product_detail_section))
            <td class="font-weight-bold " style="border-left: 2px solid #eee;border-bottom: 0px">@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</td>
            <td class="" style="border-bottom:0px;">
            <span class="m-l-15 selectDoubleClick" id="product_type" data-fieldvalue="{{@$product->type_id_2}}">
            @if(@$product->type_id_2 != null)
                {{@$product->productType2->title}}
            @else
                Select @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif
            @endif
            <!-- {{(@$product->type_id_2 != null)?@$product->productType2->title:'Select Type 2'}} -->
            </span>

            <select name="type_id_2" class="selectFocus form-control d-none">
            <option value="" disabled="" selected="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
            @foreach($product_type_2 as $type)
            <option value="{{$type->id}}" {{ ($product->type_id_2 == $type->id ? "selected" : "") }} >{{$type->title}}</option>
            @endforeach
            </select>
            </td>
        @else
        <td style="border-left: 2px solid #eee;border-bottom: 0px"></td>
        <td style="border-bottom:0px;"></td>
        @endif

      </tr>

      <tr>
        @if (in_array('product_type_3', $product_detail_section))
            <td class="font-weight-bold " style="border-left: 2px solid #eee;border-bottom: 0px">@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</td>
            <td class="" style="border-bottom:0px;">
            <span class="m-l-15 selectDoubleClick" id="product_type" data-fieldvalue="{{@$product->type_id_3}}">
            @if(@$product->type_id_3 != null)
                {{@$product->productType3->title}}
            @else
                Select @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif
            @endif
            <!-- {{(@$product->type_id_3 != null)?@$product->productTyp3->title:'Select Type 3'}} -->
            </span>

            <select name="type_id_3" class="selectFocus form-control d-none">
            <option value="" disabled="" selected="">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
            @foreach($product_type_3 as $type)
            <option value="{{$type->id}}" {{ ($product->type_id_3 == $type->id ? "selected" : "") }} >{{$type->title}}</option>
            @endforeach
            </select>
            </td>
        @else
        <td style="border-left: 2px solid #eee;border-bottom: 0px"></td>
        <td style="border-bottom:0px;"></td>
        @endif

        <td colspan="2" style="border-left: 2px solid #eee;border-bottom: 0px"></td>
      </tr>


      <tr>
        @if (in_array('brand', $product_detail_section))

        <td class="font-weight-bold ">{{$global_terminologies['brand']}} </td>
        <td class="">
        <span class="m-l-15 inputDoubleClick" id="brand"  data-fieldvalue="{{@$product->brand}}">
        {{(@$product->brand!=null)?@$product->brand:'--'}}
        </span>

        <input type="text" name="brand" class="fieldFocus d-none" value="{{(@$product->brand!=null)?$product->brand:''}}">
        </td>

        @else

        <td></td>
        <td></td>

        @endif

        @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
        @if (in_array('temprature_c', $product_detail_section))
            <td class="font-weight-bold " style="border-left: 2px solid #eee;">{{$global_terminologies['temprature_c']}} </td>
            <td class="">
            <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="{{@$product->product_temprature_c}}">
            {{(@$product->product_temprature_c!=null)?@$product->product_temprature_c:'--'}}
            </span>

            <input type="number" name="product_temprature_c" class="fieldFocus d-none" value="{{(@$product->product_temprature_c!=null)?$product->product_temprature_c:''}}">
            </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif
        @else
        <td colspan="2"></td>
        @endif
      </tr>
      <!-- @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        <td class="fontbold ">{{$global_terminologies['temprature_c']}} </td>
        <td class="">
        <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="{{@$product->product_temprature_c}}">
        {{(@$product->product_temprature_c!=null)?@$product->product_temprature_c:'--'}}
        </span>

        <input type="number" name="product_temprature_c" class="fieldFocus d-none" value="{{(@$product->product_temprature_c!=null)?$product->product_temprature_c:''}}">
        </td>
        <td></td>
        <td></td>
      </tr>
      @endif -->
      <tr>
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
        <td class="font-weight-bold ">Billed Unit <b style="color: red;">*</b></td>
        <td class="">
        <span class="m-l-15 selectDoubleClick" id="buying_unit" data-fieldvalue="{{@$product->buying_unit}}">
        {{(@$product->buying_unit!=null)?@$product->units->title:'Select'}}
        </span>
        <select name="buying_unit" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$product->buying_unit == $unit->id ? 'selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        </select>
        <input type="text" name="buying_unit" class="fieldFocus d-none" value="{{(@$product->buying_unit!=null)?@$product->units->title:'Select'}}">
        </td>

        <td class="font-weight-bold " style="border-left: 2px solid #eee;">Selling Unit <b style="color: red;">*</b></td>
        <td class="">
        <span class="m-l-15 selectDoubleClick" id="selling_unit" data-fieldvalue="{{@$product->selling_unit}}">
        {{(@$product->selling_unit!=null)?@$product->sellingUnits->title:'Select'}}
        </span>

        <select name="selling_unit" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$product->selling_unit == $unit->id ? 'selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}

        </select>
        <input type="text" name="selling_unit" class="fieldFocus d-none" value="{{(@$product->selling_unit!=null)?@$product->sellingUnits->title:'Select'}}">
        </td>
        @else
        <td class="font-weight-bold ">Selling Unit <b style="color: red;">*</b></td>
        <td class="" colspan="3">
        <span class="m-l-15 selectDoubleClick" id="selling_unit" data-fieldvalue="{{@$product->selling_unit}}">
        {{(@$product->selling_unit!=null)?@$product->sellingUnits->title:'Select'}}
        </span>

        <select name="selling_unit" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$product->selling_unit == $unit->id ? 'selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}

        </select>
        <input type="text" name="selling_unit" class="fieldFocus d-none" value="{{(@$product->selling_unit!=null)?@$product->sellingUnits->title:'Select'}}">
        </td>

      @endif
      </tr>
      <tr>

        @if (in_array('stock_unit', $product_detail_section))

        <td class="font-weight-bold ">Stock Unit</td>
        <td class="">
        <span class="m-l-15 selectDoubleClick" id="stock_unit" data-fieldvalue="{{@$product->stock_unit}}">
        {{(@$product->stock_unit!=null)?@$product->stockUnit->title:'Select'}}
        </span>

        <select name="stock_unit" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$product->stock_unit == $unit->id ? 'selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}

        </select>
        <input type="text" name="stock_unit" class="fieldFocus d-none" value="{{(@$product->stock_unit!=null)?@$product->stockUnit->title:'Select'}}">
        </td>

        @else
        <td></td>
        <td></td>
        @endif

        @if(in_array('minimum_stock', $product_detail_section))
        <td class="font-weight-bold " style="border-left: 2px solid #eee;">{{$global_terminologies['minimum_stock']}}</td>
        <td class="">
        <span class="m-l-15 inputDoubleClick" id="min_stock"  data-fieldvalue="{{@$product->min_stock}}">
        {{(@$product->min_stock!=null)?@$product->min_stock:'--'}}
        </span>

        <input type="number" name="min_stock" class="fieldFocus d-none" value="{{(@$product->min_stock!=null)?$product->min_stock:''}}">
        </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif

      </tr>

      <tr>
        @if (in_array('avg_units_for-sales', $product_detail_section))

        <td class="font-weight-bold" colspan="2" >{{$global_terminologies['avg_units_for-sales'] }}</td>
        <td class="" colspan="3" style="width: 25% !important;border-left: 1px solid #eee;">
        <span class="m-l-15 inputDoubleClick" id="weight"  data-fieldvalue="{{@$product->weight}}">
        {{(@$product->weight!=null)?@$product->weight:'--'}}
        </span>

        <input type="number" name="weight" class="fieldFocus d-none" value="{{(@$product->weight!=null)?$product->weight:''}}">
        </td>

        @else

        <td colspan="2"></td>
        <td colspan="3" style="width: 25% !important;border-left: 1px solid #eee;"></td>

        @endif
      </tr>

      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        @if (in_array('vat', $product_detail_section))
        <td class="font-weight-bold">VAT</td>
        <td class="">
          <span class="m-l-15 inputDoubleClick" id="vat"  data-fieldvalue="{{@$product->vat}}">
            {{(@$product->vat!==null)?@$product->vat:'0'}}

        </span>

          <input id="vat_value"type="number" name="vat" class="fieldFocus d-none" value="{{(@$product->vat!==null)?@$product->vat:''}}">%

          <!-- <span class="m-l-15" id="vat"  data-fieldvalue="">
          {{(@$product->productSubCategory->vat!==null)?@$product->productSubCategory->vat.' %':'--'}}</span> -->
        </td>
        @else
        <td></td>
        <td></td>
        @endif


        @if (in_array('import_tax', $product_detail_section))
        <td class="font-weight-bold " style="border-left: 2px solid #eee;">Import Tax (Book)</td>
        <td>
            <span class="m-l-15 inputDoubleClick" id="import_tax_book"  data-fieldvalue="{{@$product->import_tax_book}}">{{(@$product->import_tax_book!==null)?@$product->import_tax_book:'--'}}</span>
            <input id="import_tax_value" type="number" style="width:80%;" name="import_tax_book" class="fieldFocus d-none" value="{{@$product->import_tax_book}}">%

            <!-- <span class="m-l-15" id="import_tax_book"  data-fieldvalue="">
          {{(@$product->productSubCategory->import_tax_book!==null)?@$product->productSubCategory->import_tax_book.' %':'--'}}</span> -->
        </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif
      </tr>
      @endif
      <tr>
          @if (in_array('added_by', $product_detail_section))
          <td class="font-weight-bold">Added By</td>
          <td class="text-nowrap">
            <span class="m-l-15">
            {{(@$product->added_by!=null)?@$product->added_by->name:'--'}}</span>
          </td>
          @else
          <td></td>
          <td></td>
          @endif

          @if (in_array('created_date', $product_detail_section))
          <td class="font-weight-bold text-nowrap" style="border-left: 2px solid #eee;">Created Date</td>
          <td>
              <span class="m-l-15">{{(@$product->created_at!=null)? \Carbon\Carbon::parse(@$product->created_at)->format('d/m/Y'):'--'}}</span>
          </td>
          @else
          <td style="border-left: 2px solid #eee;"></td>
          <td></td>
          @endif
        </tr>
        <tr>
            @if (in_array('order_qty_per_piece', $product_detail_section))
            <td class="font-weight-bold">{{ $global_terminologies['order_qty_per_piece'] }}</td>
            <td class="text-nowrap">
            <span class="m-l-15 inputDoubleClick"  id="order_qty_per_piece"  data-fieldvalue="{{@$product->order_qty_per_piece}}">
            {{(@$product->order_qty_per_piece!=null)?@$product->order_qty_per_piece:'--'}}</span>
            <input id="order_qty_per_piece_value"type="text" name="order_qty_per_piece" class="fieldFocus d-none" value="{{(@$product->order_qty_per_piece!==null)?@$product->order_qty_per_piece:''}}">
          </td>
          @else
          <td></td>
          <td></td>
          @endif
        </tr>

      <!-- <tr>
        <td class="font-weight-bold ">Stock Unit</td>
        <td class="">
        <span class="m-l-15 selectDoubleClick" id="stock_unit" data-fieldvalue="{{@$product->stock_unit}}">
        {{(@$product->stock_unit!=null)?@$product->stockUnit->title:'Select'}}
        </span>

        <select name="stock_unit" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$product->stock_unit == $unit->id ? 'selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}

        </select>
        <input type="text" name="stock_unit" class="fieldFocus d-none" value="{{(@$product->stock_unit!=null)?@$product->stockUnit->title:'Select'}}">
        </td>
        <td></td>
        <td></td>
      </tr> -->

      <!-- <tr>
        <td class="font-weight-bold ">{{$global_terminologies['minimum_stock']}}</td>
        <td class="">
        <span class="m-l-15 inputDoubleClick" id="min_stock"  data-fieldvalue="{{@$product->min_stock}}">
        {{(@$product->min_stock!=null)?@$product->min_stock:'--'}}
        </span>

        <input type="number" name="min_stock" class="fieldFocus d-none" value="{{(@$product->min_stock!=null)?$product->min_stock:''}}">
        </td>
        <td></td>
        <td></td>
      </tr> -->


    </tbody>
  </table>
</div>

</div>

<div class="col-lg-5 col-md-6 ">
 <h4 class="headings-color d-lg-none d-md-block mt-md-3">Vendor Specific Information</h4>
<div class="bg-white">
  <table id="example" class="headings-color table sales-customer-table const-font product_info" style="width: 100%;">
    <tbody>

      @if($product->supplier_id == 0)
        @php $disableCheck = ""; @endphp
      @else
        @php $disableCheck = "inputDoubleClickFirst"; @endphp
      @endif

      <tr>
        <td class="font-weight-bold">Default/Last @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif <b style="color: red;">*</b></td>

        <td class="">
        @if($product->supplier_id == 0)
        <div class="incomplete-filter-def-supp">
            <span class="m-l-15" id="new_supplier_span" >Add Supplier</span>
                     <div class="add_new_supplier d-none">
                         <select class="font-weight-bold form-control-lg form-control js-states state-tags small" id="new_supplier_id">
                             <option vaalue="" disabled="" selected="">Select Supplier</option>
                             @foreach($suppliers as $supplier)
                             <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
                             @endforeach
                         </select>
                    </div>
          </div>

        @else
        <div class="incomplete-filter-def-supp">
          <span class="m-l-15 {{$disableCheck}} def-last-supp" id="supplier_id"  data-fieldvalue="{{@$product->def_or_last_supplier->reference_name}}">{{@$product->def_or_last_supplier->reference_name}}</span>

          <div class="incomplete-filter-def-supp d-none">
          <select class="font-weight-bold form-control-lg form-control js-states state-tags selectFocus supplier_id getDataOfProductSupplier" name="supplier_id">
            <option value="" disabled="" selected="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>

          </select>
            </div>
        </div>

        @endif
        </td>

        @if (in_array('suppliers_product_reference_no', $default_supplier_detail_section))
            <td class="font-weight-bold" style="border-left: 2px solid #eee;">{{$global_terminologies['suppliers_product_reference_no']}}</td>
            <td class="">
            <span class="m-l-15 {{$disableCheck}}" id="product_supplier_reference_no"  data-fieldvalue="{{@$default_or_last_supplier->product_supplier_reference_no}}">{{(@$default_or_last_supplier->product_supplier_reference_no!=null)?@$default_or_last_supplier->product_supplier_reference_no:'--'}}</span>
            <input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->product_supplier_reference_no}}">
            </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif

      </tr>

      <input type="hidden" name="default_or_last_supplier_id" id="default_or_last_supplier_id" value="{{@$default_or_last_supplier->id}}">

     <!--  <tr>
        <td class="fontbold">{{$global_terminologies['suppliers_product_reference_no']}}</td>
        <td class="">
          <span class="m-l-15 {{$disableCheck}}" id="product_supplier_reference_no"  data-fieldvalue="{{@$default_or_last_supplier->product_supplier_reference_no}}">{{(@$default_or_last_supplier->product_supplier_reference_no!=null)?@$default_or_last_supplier->product_supplier_reference_no:'--'}}</span>
          <input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->product_supplier_reference_no}}">
        </td>
      </tr> -->
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        @if (in_array('country', $default_supplier_detail_section))
        <td class="font-weight-bold">Country</td>
        <td class="" colspan="3">
          <span class="m-l-15" id="supplier_country"  data-fieldvalue="{{@$product->def_or_last_supplier->getcountry->name}}">{{(@$product->def_or_last_supplier->getcountry->name!=null)?@$product->def_or_last_supplier->getcountry->name:'--'}}</span>
        </td>
        @else
        <td></td>
        <td colspan="3"></td>
        @endif

      </tr>
      <tr>
        @if (in_array('supplier_description', $default_supplier_detail_section))
        <td class="font-weight-bold" height="90px">{{$global_terminologies['supplier_description']}}</td>
        <td class="" colspan="3">
          <span class="m-l-15 {{$disableCheck}}" id="supplier_description"  data-fieldvalue="{{@$default_or_last_supplier->supplier_description}}">{{(@$default_or_last_supplier->supplier_description!=null)?@$default_or_last_supplier->supplier_description:'--'}}</span>

          <input type="text" style="width:100%;" name="supplier_description" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->supplier_description}}">
        </td>
        @else
        <td></td>
        <td colspan="3"></td>
        @endif

      </tr>

      <tr>
        @if (in_array('purchasing_price', $default_supplier_detail_section))
            <td>
            <span class="font-weight-bold">{{$global_terminologies['purchasing_price']}}</span>
            <a href="#table-product-history" style="color: blue; text-decoration-line: underline;"><span class="ml-3">Exg: {{(@$default_or_last_supplier->currency_conversion_rate!==null)?number_format((float)@@$default_or_last_supplier->currency_conversion_rate,3,'.',''):'--'}}</span></a>
          </td>
          <td class="">
            <span class="m-l-15" id="buying_price_in_thb"  data-fieldvalue="{{@$default_or_last_supplier->buying_price_in_thb}}">THB {{(@$default_or_last_supplier->buying_price_in_thb!==null)?number_format((float)@$default_or_last_supplier->buying_price_in_thb,3,'.',''):'--'}}</span>
          </td>
        @else
        <td></td>
        <td></td>
        @endif

        <td class="font-weight-bold" style="border-left: 2px solid #eee;">{{$global_terminologies['purchasing_price']}} <b style="color: red;">*</b></td>
        <td class="">
          <span class="ml-0">
            {{@$default_or_last_supplier->supplier->getCurrency->currency_code}} {{(@$default_or_last_supplier->buying_price_before_discount!==null)?number_format((float)@$default_or_last_supplier->buying_price_before_discount,3,'.',''):'--'}}
          </span>
          <span class="ml-3">{{(@$default_or_last_supplier->discount!==null)?-number_format((float)@@$default_or_last_supplier->discount,3,'.',''):'--'}} %</span>
          <span class="ml-3 {{$disableCheck}}" id="buying_price"  data-fieldvalue="{{@$default_or_last_supplier->buying_price}}">{{@$default_or_last_supplier->supplier->getCurrency->currency_code}} {{(@$default_or_last_supplier->buying_price!==null)?number_format((float)@$default_or_last_supplier->buying_price,3,'.',''):'--'}}</span>
          <input type="number" style="width:100%;" name="buying_price" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->buying_price}}">
        </td>
      </tr>
      @endif
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        @if (in_array('extra_cost_per_billed_unit', $default_supplier_detail_section))
        <td class="font-weight-bold">{{$global_terminologies['extra_cost_per_billed_unit']}}</td>
        <td class="">
          <span class="m-l-15 {{$disableCheck}}" id="extra_cost"  data-fieldvalue="{{@$default_or_last_supplier->extra_cost}}">THB {{(@$default_or_last_supplier->extra_cost!==null)?number_format((float)@$default_or_last_supplier->extra_cost,3,'.',''):'--'}}</span>
          <input type="number" style="width:100%;" name="extra_cost" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->extra_cost}}">
        </td>
        @else
        <td></td>
        <td></td>
        @endif


        @if (in_array('ordering_unit', $default_supplier_detail_section))
        <td class="font-weight-bold" style="border-left: 2px solid #eee;">Ordering Unit </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="supplier_packaging" data-place="page" data-fieldvalue="{{@$default_or_last_supplier->supplier_packaging}}">{{(@$default_or_last_supplier->supplier_packaging!==null)?@$default_or_last_supplier->supplier_packaging:'--'}}</span>

          <select name="supplier_packaging" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$default_or_last_supplier->supplier_packaging == $unit->title ? 'selected' : '' ) }} value="{{ $unit->title }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}

        </select>
        </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif
      </tr>
      @else
        <tr>
          @if (in_array('ordering_unit', $default_supplier_detail_section))
          <td class="font-weight-bold" colspan="1" style="border-left: 2px solid #eee;">Ordering Unit </td>
          <td colspan="3">
            <span class="m-l-15 {{$disableCheck}}" id="supplier_packaging" data-place="page" data-fieldvalue="{{@$default_or_last_supplier->supplier_packaging}}">{{(@$default_or_last_supplier->supplier_packaging!==null)?@$default_or_last_supplier->supplier_packaging:'--'}}</span>

            <select name="supplier_packaging" class="selectFocus form-control d-none">
          <option value="" disabled="" selected="">Choose Unit</option>
          @if($getProductUnit->count() > 0)
          @foreach($getProductUnit as $unit)
          <option {{ (@$default_or_last_supplier->supplier_packaging == $unit->title ? 'selected' : '' ) }} value="{{ $unit->title }}">{{ $unit->title }}</option>
          @endforeach
          @endif
          {{--<option value="new">Add New</option>--}}

          </select>
          </td>
          @else
          <td style="border-left: 2px solid #eee;"></td>
          <td colspan="3"></td>
          @endif
        </tr>
      @endif
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        @if (in_array('extra_tax_per_billed_unit', $default_supplier_detail_section))
            <td>
            <span class="font-weight-bold">
              {{$global_terminologies['extra_tax_per_billed_unit']}}
            </span>
            <span class="{{$disableCheck}} ml-3" id="extra_tax_percent"  data-fieldvalue="{{@$default_or_last_supplier->import_tax_actual}}">{{$extra_tax_percentage}}</span>
            <input type="number" style="width:50%;" name="extra_tax_percent" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->extra_tax_percent}}">%
          </td>
          <td class="">
            <span class="m-l-15 {{$disableCheck}}" id="extra_tax"  data-fieldvalue="{{@$default_or_last_supplier->extra_tax}}">THB {{(@$default_or_last_supplier->extra_tax!==null)?number_format((float)@$default_or_last_supplier->extra_tax,3,'.',''):'--'}}</span>
            <input type="number" style="width:100%;" name="extra_tax" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->extra_tax}}">
            </td>
        @else
        <td></td>
        <td></td>
        @endif

        @if (in_array('order_qty_unit', $default_supplier_detail_section))
            <td class="font-weight-bold" style="border-left: 2px solid #eee;">{{$global_terminologies['order_qty_unit']}} </td>
            <td>
            <span class="m-l-15 {{$disableCheck}}" id="billed_unit"  data-fieldvalue="{{@$default_or_last_supplier->billed_unit}}">{{(@$default_or_last_supplier->billed_unit!==null)?number_format((float)@$default_or_last_supplier->billed_unit,3,'.',''):'--'}}</span>
            <input type="text" style="width:100%;" name="billed_unit" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->billed_unit}}">
            </td>
        @else
            <td style="border-left: 2px solid #eee;"></td>
            <td></td>
        @endif
      </tr>
      @else
        <tr>

        @if (in_array('order_qty_unit', $default_supplier_detail_section))
            <td class="font-weight-bold">{{$global_terminologies['order_qty_unit']}} </td>
            <td colspan="3">
            <span class="m-l-15 {{$disableCheck}}" id="billed_unit"  data-fieldvalue="{{@$default_or_last_supplier->billed_unit}}">{{(@$default_or_last_supplier->billed_unit!==null)?number_format((float)@$default_or_last_supplier->billed_unit,3,'.',''):'--'}}</span>
            <input type="text" style="width:100%;" name="billed_unit" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->billed_unit}}">
            </td>
        @else
            <td></td>
            <td colspan="3"></td>
        @endif


        </tr>
      @endif
      <tr>
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)


        @if (in_array('product_detail_import_tax_actual', $default_supplier_detail_section))
            <td>
            <span class="font-weight-bold">{{ $imported ? 'Import Tax (actual)' : 'Import Tax (Book)' }} <i class="fa fa-question-circle-o" aria-hidden="true"  title="Note, products COGS will automatically calculate using BOOK import tax rates if no 'Actual' import tax values are entered at the time of import"></i></span>
            <span class="{{$disableCheck}} ml-3" id="import_tax_actual"  data-fieldvalue="{{@$default_or_last_supplier->import_tax_actual}}">{{$import_tax_percentage}}</span>
            <input type="number" style="width:50%;" name="import_tax_actual" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->import_tax_actual}}">%
          </td>
          <td class="">THB
            <span class="{{$disableCheck}}" id="unit_import_tax"  data-fieldvalue="{{@$default_or_last_supplier->unit_import_tax}}">{{$import_tax_actual_in_tbh}}</span>
            <input type="number" style="width:100%;" name="unit_import_tax" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->unit_import_tax}}">
          </td>
        @else
        <td></td>
        <td></td>
        @endif

        @if (in_array('minimum_order_quantity', $default_supplier_detail_section))
        <td class="font-weight-bold" style="border-left: 2px solid #eee;"> {{$global_terminologies['avg_order_qty']}} </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="m_o_q"  data-fieldvalue="{{@$default_or_last_supplier->m_o_q}}">{{(@$default_or_last_supplier->m_o_q!==null)?@$default_or_last_supplier->m_o_q:'--'}}</span>
          <input type="number" style="width:100%;" name="m_o_q" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->m_o_q}}">
        </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif

      @else

      @if (in_array('minimum_order_quantity', $default_supplier_detail_section))
      <td class="font-weight-bold"> {{$global_terminologies['avg_order_qty']}} </td>
        <td colspan="3">
          <span class="m-l-15 {{$disableCheck}}" id="m_o_q"  data-fieldvalue="{{@$default_or_last_supplier->m_o_q}}">{{(@$default_or_last_supplier->m_o_q!==null)?@$default_or_last_supplier->m_o_q:'--'}}</span>
          <input type="number" style="width:100%;" name="m_o_q" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->m_o_q}}">
        </td>
      @else
      <td></td>
      <td colspan="3"></td>
      @endif
      @endif
      </tr>
      <tr>
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
        @if (in_array('freight_per_billed_unit', $default_supplier_detail_section))
        <td class="font-weight-bold">{{$global_terminologies['freight_per_billed_unit']}}</td>
        <td class="">
          <span class="m-l-15 {{$disableCheck}}" id="freight"  data-fieldvalue="{{@$default_or_last_supplier->freight}}">THB {{(@$default_or_last_supplier->freight!==null)?number_format((float)@$default_or_last_supplier->freight,3,'.',''):'--'}}</span>
          <input type="number" style="width:100%;" name="freight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->freight}}">
          </td>
        @else
        <td></td>
        <td></td>
        @endif


        @if (in_array('gross_weight', $default_supplier_detail_section))
            <td class="font-weight-bold" style="border-left: 2px solid #eee;">{{$global_terminologies['gross_weight']}}</td>
            <td class="">
            <span class="m-l-15 {{$disableCheck}}" id="gross_weight"  data-fieldvalue="{{@$default_or_last_supplier->gross_weight}}">{{(@$default_or_last_supplier->gross_weight!==null)?number_format((float)@$default_or_last_supplier->gross_weight,3,'.',''):'--'}}</span>
            <input type="number" style="width:100%;" name="gross_weight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->gross_weight}}">
            </td>
        @else
        <td style="border-left: 2px solid #eee;"></td>
        <td></td>
        @endif
      @else


        @if (in_array('gross_weight', $default_supplier_detail_section))
            <td class="font-weight-bold" colspan="1" style="border-left: 2px solid #eee;">{{$global_terminologies['gross_weight']}}</td>
            <td class="" colspan="3">
            <span class="m-l-15 {{$disableCheck}}" id="gross_weight"  data-fieldvalue="{{@$default_or_last_supplier->gross_weight}}">{{(@$default_or_last_supplier->gross_weight!==null)?number_format((float)@$default_or_last_supplier->gross_weight,3,'.',''):'--'}}</span>
            <input type="number" style="width:100%;" name="gross_weight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->gross_weight}}">
            </td>
        @else
        <td></td>
        <td></td>
        @endif

      @endif
      </tr>
      <tr>
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)

      @if (in_array('landing_per_billed_unit', $default_supplier_detail_section))
        <td class="font-weight-bold">{{$global_terminologies['landing_per_billed_unit']}}</td>
        <td>
            <span class="m-l-15 {{$disableCheck}}" id="landing"  data-fieldvalue="{{@$default_or_last_supplier->landing}}">THB {{(@$default_or_last_supplier->landing!==null)?number_format((float)@$default_or_last_supplier->landing,3,'.',''):'--'}}</span>
            <input type="number" style="width:100%;" name="landing" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->landing}}">
        </td>
      @else
      <td></td>
      <td></td>
      @endif


        <td class="font-weight-bold" style="border-left: 2px solid #eee;">{{$global_terminologies['expected_lead_time_in_days']}} <b style="color: red;">*</b></td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="leading_time"  data-fieldvalue="{{@$default_or_last_supplier->leading_time}}">{{(@$default_or_last_supplier->leading_time!==null)?@$default_or_last_supplier->leading_time:'--'}}</span>
          <input type="number" style="width:50%;" name="leading_time" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->leading_time}}">
        </td>
      @else
      <td class="font-weight-bold">{{$global_terminologies['expected_lead_time_in_days']}} <b style="color: red;">*</b></td>
        <td colspan="3">
          <span class="m-l-15 {{$disableCheck}}" id="leading_time"  data-fieldvalue="{{@$default_or_last_supplier->leading_time}}">{{(@$default_or_last_supplier->leading_time!==null)?@$default_or_last_supplier->leading_time:'--'}}</span>
          <input type="number" style="width:50%;" name="leading_time" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->leading_time}}">
        </td>
      @endif
      </tr>
      <tr>

        @if (in_array('date_of_last_import', $default_supplier_detail_section))
            <td class="font-weight-bold"> Date of Last Import </td>
            <td>
            <a href="#table-product-history" style="color: blue; text-decoration-line: underline;"><span class="m-l-15" id="last_date_import_z">
                {{($product->last_date_import != null ? Carbon::parse($product->last_date_import)->format('d/m/Y') :'--')}}
            </span></a>
            </td>
        @else
        <td></td>
        <td></td>
        @endif

        @if (in_array('price_last_modified', $default_supplier_detail_section))
            <td class="font-weight-bold" style="border-left: 2px solid #eee;"> Price Last Modified </td>
            <td>
            <a href="#table-product-history" style="color: blue; text-decoration-line: underline;"><span class="m-l-15" id="last_price_updated_date_z">
                {{($product->last_price_updated_date != null ? Carbon::parse($product->last_price_updated_date)->format('d/m/Y') :'--')}}
            </span></a>
            </td>
        @else
        <td></td>
        <td></td>
        @endif
      </tr>
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
          @if (in_array('cost_price', $default_supplier_detail_section))
          <td class="font-weight-bold " style="background-color: #b2e0f3;">{{$global_terminologies['cost_price']}} <i class="fa fa-question-circle-o buy_unit_cost_price_mark" aria-hidden="true"></i>
            <div class="tooltiptext">{{@$IMPcalculation}}</div>
          </td>
          <td class="" style="background-color: #b2e0f3;">
            <span class="m-l-15" id="total_buy_unit_cost_price" data-fieldvalue="{{@$product->total_buy_unit_cost_price}}">THB {{(@$product->total_buy_unit_cost_price!=null)?number_format((float)@$product->total_buy_unit_cost_price, 3, '.', ''):'0'}} / {{@$product->units->title}}</span>
          </td>
          @else
          <td style="background-color: #b2e0f3;"></td>
          <td style="background-color: #b2e0f3;"></td>
          @endif

          @if (in_array('purchasing_vat', $default_supplier_detail_section))
          <td class="font-weight-bold" style="border-left: 2px solid #eee;">Purchasing VAT %</td>
          <td class="">
            <span class="m-l-15 {{$disableCheck}}" id="vat_actual"  data-fieldvalue="{{@$default_or_last_supplier->vat_actual}}">{{(@$default_or_last_supplier->vat_actual !== null)?number_format((float)@$default_or_last_supplier->vat_actual,3,'.',''):'--'}}</span>
            <input type="number" style="width:100%;" name="vat_actual" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->vat_actual}}">%
          </td>
          @else
          <td style="border-left: 2px solid #eee;"></td>
          <td></td>
          @endif

      </tr>
      @endif

      <!-- @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>

        <td colspan="2"></td>
      </tr>
      @endif -->
      <tr>
        <td class="font-weight-bold"> @if(!array_key_exists('unit_conversion_rate', $global_terminologies))Unit Conversion Rate <b style="color: red;">*</b> @else {{$global_terminologies['unit_conversion_rate']}} <b style="color: red;">*</b> @endif</td>
        <td class="" colspan="3">
          @if($product->unit_conversion_rate == null)
            <span class="m-l-15 inputDoubleClick font-weight-bold" id="unit_conversion_rate"  data-fieldvalue="{{@$product->unit_conversion_rate}}">--</span>
            <input type="text"  name="unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="">
            <span class="ml-3" style="color: #959191;">( {{(@$product->total_buy_unit_cost_price!=null)?number_format((float)@$product->total_buy_unit_cost_price, 3, '.', ''):'0'}} {{@$product->units->title}} * 1 = {{(@$product->selling_price!=null)?number_format((float)@$product->selling_price, 3, '.', ''):'N/A'}} {{@$product->sellingUnits->title}} )</span>
          @else
            <span class="m-l-15 inputDoubleClick font-weight-bold" id="unit_conversion_rate"  data-fieldvalue="{{@$product->unit_conversion_rate}}">{{number_format((float)@$product->unit_conversion_rate, 5, '.', '')}}</span>
            <input type="text"  name="unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="{{number_format((float)@$product->unit_conversion_rate, 5, '.', '')}}">
            <span class="ml-3" style="color: #959191;">( {{(@$product->total_buy_unit_cost_price!=null)?number_format((float)@$product->total_buy_unit_cost_price, 3, '.', ''):'0'}} {{@$product->units->title}} * {{number_format((float)@$product->unit_conversion_rate, 5, '.', '')}} = {{(@$product->selling_price!=null)?number_format((float)@$product->selling_price, 3, '.', ''):'N/A'}} {{@$product->sellingUnits->title}} )</span>
          @endif
        </td>
      </tr>

      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        @if (in_array('net_price', $default_supplier_detail_section))
            <td class="font-weight-bold">{{$global_terminologies['net_price']}} /unit <b>(THB)</b></td>
            <td class="" colspan="3">
            <span class="m-l-15" id="selling_price" data-fieldvalue="{{@$product->selling_price}}">{{(@$product->selling_price!=null)?number_format((float)@$product->selling_price, 3, '.', ''):'N/A'}} / {{@$product->sellingUnits->title}}</span>
            </td>
        @else
        <td></td>
        <td colspan="3"></td>
        @endif

      </tr>
      @endif


      <!-- <tr>
        <td class="fontbold">{{$global_terminologies['gross_weight']}}</td>
        <td class="">
          <span class="m-l-15 {{$disableCheck}}" id="gross_weight"  data-fieldvalue="{{@$default_or_last_supplier->gross_weight}}">{{(@$default_or_last_supplier->gross_weight!==null)?number_format((float)@$default_or_last_supplier->gross_weight,3,'.',''):'--'}}</span>
          <input type="number" style="width:50%;" name="gross_weight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->gross_weight}}">
          </td>
      </tr> -->

<!--       <tr>
        <td class="fontbold">{{$global_terminologies['expected_lead_time_in_days']}} <b style="color: red;">*</b></td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="leading_time"  data-fieldvalue="{{@$default_or_last_supplier->leading_time}}">{{(@$default_or_last_supplier->leading_time!==null)?@$default_or_last_supplier->leading_time:'--'}}</span>
          <input type="number" style="width:50%;" name="leading_time" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->leading_time}}">
        </td>
      </tr> -->

      <!-- <tr>
        <td class="fontbold">Ordering Unit </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="supplier_packaging" data-place="page" data-fieldvalue="{{@$default_or_last_supplier->supplier_packaging}}">{{(@$default_or_last_supplier->supplier_packaging!==null)?@$default_or_last_supplier->supplier_packaging:'--'}}</span>

          <select name="supplier_packaging" class="selectFocus form-control d-none">
        <option value="" disabled="" selected="">Choose Unit</option>
        @if($getProductUnit->count() > 0)
        @foreach($getProductUnit as $unit)
        <option {{ (@$default_or_last_supplier->supplier_packaging == $unit->title ? 'selected' : '' ) }} value="{{ $unit->title }}">{{ $unit->title }}</option>
        @endforeach
        @endif
        {{--<option value="new">Add New</option>--}}

        </select>
        </td>
      </tr> -->

      <!-- <tr>
        <td class="fontbold">{{$global_terminologies['order_qty_unit']}} </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="billed_unit"  data-fieldvalue="{{@$default_or_last_supplier->billed_unit}}">{{(@$default_or_last_supplier->billed_unit!==null)?number_format((float)@$default_or_last_supplier->billed_unit,3,'.',''):'--'}}</span>
          <input type="text" style="width:50%;" name="billed_unit" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->billed_unit}}">
        </td>
      </tr> -->

      <!-- <tr>
        <td class="fontbold"> {{$global_terminologies['avg_order_qty']}} </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="m_o_q"  data-fieldvalue="{{@$default_or_last_supplier->m_o_q}}">{{(@$default_or_last_supplier->m_o_q!==null)?@$default_or_last_supplier->m_o_q:'--'}}</span>
          <input type="number" style="width:100%;" name="m_o_q" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->m_o_q}}">
        </td>
      </tr> -->
    </tbody>
  </table>
</div>

<!-- <div class="bg-white mt-3 pt-3 pl-2">
  <table id="example" class="headings-color table sales-customer-table const-font" style="width: 100%;">
    <tbody> -->

      <!-- Supplier currency -->
      <!-- <tr>
        <td class="fontbold text-nowrap">Cost price (<b>{{@$default_or_last_supplier->supplier->getCurrency->currency_code}}</b>) <i class="fa fa-question-circle-o buy_unit_cost_price_mark_for_supp" aria-hidden="true"></i>
          <div class="tooltiptext">{{@$IMPcalculation}}</div>
        </td>
        <td class="text-nowrap">
          <span class="m-l-15" id="t_b_u_c_p_of_supplier" data-fieldvalue="{{@$product->t_b_u_c_p_of_supplier}}">{{(@$product->t_b_u_c_p_of_supplier!=null)?number_format((float)@$product->t_b_u_c_p_of_supplier, 2, '.', ''):'0'}}</span>
        </td>
      </tr> -->

      <!-- THB currency -->
     <!--  @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        <td class="fontbold " style="width: 50% !important;">{{$global_terminologies['cost_price']}} <i class="fa fa-question-circle-o buy_unit_cost_price_mark" aria-hidden="true"></i>
          <div class="tooltiptext">{{@$IMPcalculation}}</div>
        </td>
        <td class="" style="width: 50% !important;">
          <span class="m-l-15" id="total_buy_unit_cost_price" data-fieldvalue="{{@$product->total_buy_unit_cost_price}}">{{(@$product->total_buy_unit_cost_price!=null)?number_format((float)@$product->total_buy_unit_cost_price, 3, '.', ''):'0'}}</span>
        </td>
      </tr>
      @endif -->

      <!-- <tr>
        <td class="fontbold"> @if(!array_key_exists('unit_conversion_rate', $global_terminologies))Unit Conversion Rate <b style="color: red;">*</b> @else {{$global_terminologies['unit_conversion_rate']}} <b style="color: red;">*</b> @endif</td>
        <td class="">
          @if($product->unit_conversion_rate == null)
            <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate"  data-fieldvalue="{{@$product->unit_conversion_rate}}">--</span>
            <input type="text"  name="unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="">
          @else
            <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate"  data-fieldvalue="{{@$product->unit_conversion_rate}}">{{number_format((float)@$product->unit_conversion_rate, 5, '.', '')}}</span>
            <input type="text"  name="unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="{{number_format((float)@$product->unit_conversion_rate, 5, '.', '')}}">
          @endif
        </td>
      </tr> -->

      <!-- @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <tr>
        <td class="fontbold">{{$global_terminologies['net_price']}} /unit <b>(THB)</b></td>
        <td class="">
          <span class="m-l-15" id="selling_price" data-fieldvalue="{{@$product->selling_price}}">{{(@$product->selling_price!=null)?number_format((float)@$product->selling_price, 3, '.', ''):'N/A'}}</span>
        </td>
      </tr>
      @endif -->

<!--     </tbody>
  </table>
</div> -->

</div>

</div>
<!-- Ecommerce Section -->
<div class="row mt-4">
  @if((Auth::user()->role_id == 9 || Auth::user()->role_id == 1 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11))
<div class="col-lg-12 col-md-12">
  <div class="col-lg-10 col-md-9 pl-3 pr-0">
    @if($ecommerceconfig_status==1)
  <h4 class="font-weight-bold" style="color: #09355a;">Ecommerce Section <span class="ml-5">
   <input  data-id="{{@$product->id}}" {{$product->ecommerce_enabled == 1 ? 'checked' : ''}} type="checkbox" value="{{@$product->ecommerce_enabled}}" name="ecommerce_enabled" id="ecommerce_enabled"> <span class="ml-3 font-weight-normal">Share to website</span>
  <input type="text" hidden id="hidden_check" value=1>
  </span>
  </h4>
  @else
  <h4 class="font-weight-bold" style="color: #09355a;">Ecommerce Section </h4>
  @endif
</div>

<div class="row desktop-ecom-table" >
  <div class="col-3 d-flex pr-0 border-right">
    <div class="bg-white h-100 w-100">
    <table id="example" style="visibility: none" class="table headings-color table-responsive sales-customer-table dataTable const-font bg-white table-theme-header text-to-left" style="width: 100%;text-align: left;">
      <tbody>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Title</td>
          <td style="border-left: 0px;">
            <span class="m-l-15 inputDoubleClick" id="name"  data-fieldvalue="{{@$product->name}}">
            {{(@$product->name!=null)?@$product->name: @$product->short_desc}}</span>
            <input type="text"  name="name" class="fieldFocus d-none" value="{{(@$product->name!=null)?$product->name:''}}">
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Min Order QTY</td>
          <td style="border-left: 0px;">
            <span class="m-l-15 inputDoubleClick" id="min_o_qty"  data-fieldvalue="{{@$product->min_o_qty}}">{{(@$product->min_o_qty!==null)?@$product->min_o_qty:'--'}}</span>
            <input  id="min_o_qty_ecomm" type="number" style="width:80%;" name="min_o_qty" class="non-negative fieldFocus d-none ecomm_enabled_fields" value="{{@$product->min_o_qty}}">
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Max Order QTY</td>
          <td style="border-left: 0px;">
            <span class="m-l-15 inputDoubleClick" id="max_o_qty"  data-fieldvalue="{{@$product->max_o_qty}}">{{(@$product->max_o_qty!==null)?@$product->max_o_qty:'--'}}</span>
            <input id="max_o_qty_ecomm" type="number" style="width:80%;" name="max_o_qty" class="fieldFocus non-negative d-none ecomm_enabled_fields" value="{{@$product->max_o_qty}}">
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;white-space: nowrap;">Dimension (L x W x H)</td>
          <td style="border-left: 0px;">
            <div class="d-flex justify-content-between" style="white-space: nowrap;">
            <div class="pl-2 pr-2">
              <span class="m-l-15 inputDoubleClick" id="length"  data-fieldvalue="{{@$product->length}}" title="Length">{{(@$product->length!==null)?@$product->length:'--'}}</span>
              <input type="number" class="fieldFocus d-none" name="length" placeholder="Length" value="{{@$product->length}}" style="width: 80%">
              cm
            </div>
            <div class="pl-2 pr-2" title="Separator">
              x
            </div>
            <div class="pl-2 pr-2">
              <span class="m-l-15 inputDoubleClick" id="width"  data-fieldvalue="{{@$product->width}}" title="Width">{{(@$product->width!==null)?@$product->width:'--'}}</span>
              <input type="number" class=" fieldFocus d-none" name="width" placeholder="Width" style="width: 80%" value="{{@$product->width}}">
              cm
            </div>
            <div class="pl-2 pr-2" title="Separator">
              x
            </div>
            <div class="pl-2 pr-2">
              <span class="m-l-15 inputDoubleClick" id="height"  data-fieldvalue="{{@$product->height}}" title="Height">{{(@$product->height!==null)?@$product->height:'--'}}</span>
              <input type="number" class="fieldFocus d-none" name="height" placeholder="Height" value="{{@$product->height}}" style="width: 80%">
              cm
            </div>
            <div style="width: 30%;"></div>

            </div>
          </td>
        </tr>
      </tbody>
    </table>

    </div>
  </div>



  <div class="col-3 p-0 d-flex border-right">
    <div class="bg-white h-100 w-100">
    <table id="example" class="table headings-color sales-customer-table dataTable const-font bg-white table-theme-header text-to-left" style="width: 100%;">
      <tbody>
        <tr>
          <td class="font-weight-bold" style="border: 0px; border-top: 1px solid #eee;border-left: 1px solid #eee;">E-commerce Long Description</td>
        </tr>
        <tr>
          <td style="border: 0px;border-left: 1px solid #eee;" height="58px">
            <span class="m-l-15 inputDoubleClick" id="long_desc"  data-fieldvalue="{{@$product->long_desc}}">
            {{(@$product->long_desc!=null)?@$product->long_desc:'--'}}
            </span>

            <textarea id="long_desc_ecomm"name="long_desc" rows="5" class="fieldFocus d-none ecomm_enabled_fields w-100" cols="20">{{(@$product->long_desc!=null)?@$product->long_desc:''}}</textarea>
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border: 0px; border-top: 1px solid #eee;border-left: 1px solid #eee;">
            <div class="row p-0">
              <div class="col-7">
              <span>E-commerce Product weight per unit</span>
              </div>
              <div class="col-5 p-0">
                <span class="m-l-15 inputDoubleClick" id="ecom_product_weight_per_unit"  data-fieldvalue="{{@$product->ecom_product_weight_per_unit}}">
                {{(@$product->ecom_product_weight_per_unit!=null)?@$product->ecom_product_weight_per_unit:'--'}}
              </span>
              <input id="ecom_product_weight_per_unit" type="number" style="width:40%;" name="ecom_product_weight_per_unit" class="fieldFocus non-negative d-none" value="{{@$product->ecom_product_weight_per_unit}}"> kg
              </div>

              <!-- <span></span> -->
            </div>

          </td>
        </tr>
        <tr>
          <td style="border: 0px;border-left: 1px solid #eee;" height="58px">

          </td>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
  <div class="col-3 p-0 d-flex border-right">
    <div class="bg-white h-100 w-100">
    <table id="example" class="table headings-color sales-customer-table dataTable const-font bg-white table-theme-header text-to-left" style="width: 100%;">
      <tbody>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Selling Price</td>
          <td style="border-left: 0px;">
            <span class="m-l-15 inputDoubleClick " id="ecommerce_price_span"  data-fieldvalue="{{@$product->ecommerce_price}}">
            @if(@$product->ecommerce_price!=null)
             {{ @$product->ecommerce_price }}&nbsp
            @else
            --  &nbsp
            @endif
          </span>
           <input id="ecommerce_price" type="number" style="width:80%;" name="ecommerce_price" class="non-negative fieldFocus d-none " value="{{@$product->ecommerce_price}}" min="0" max="100">
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Discount Price</td>
          <td style="border-left: 0px;">
            <span class="m-l-15 inputDoubleClick "  id="discount_price_span" data-fieldvalue="{{@$product->discount_price}}" >
              {{@$product->discount_price? @$product->discount_price:'--'}}
            </span>
            <input id="discount_price" type="number" style="width:80%;" name="discount_price" class="non-negative fieldFocus d-none " value="{{@$product->discount_price}}" min="0" max="100">
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Discount Expiry</td>
          <td style="border-left: 0px;">
            @php
              $today = date('Y-m-d');
            @endphp
            <span class="m-l-15 inputDoubleClick" data-fieldvalue="{{(@$product->discount_expiry_date? @$product->discount_expiry_date:'')}}">{{   $product->discount_expiry_date ? @$product->discount_expiry_date:'--' }}</span>
            <input type="text" style="width: 85%;"data-id="{{@$product->id}}" class=" discount_expiration_date_dp d-none" name="discount_expiry_date"  value="{{  @$product->discount_expiry_date!=null? @$product->discount_expiry_date:''}}">
          </td>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
  <div class="col-3 pl-0 d-flex">
    <div class="bg-white h-100 w-100">
    <table id="example" class="table headings-color sales-customer-table dataTable const-font bg-white table-theme-header text-to-left" style="width: 100%;">
      <tbody>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">E-Com Selling Unit</td>
          <td style="border-left: 0px;">
           <span class="m-l-15 inputDoubleClick" data-fieldvalue="{{(@$product->ecom_selling_unit? @$product->ecom_selling_unit:'')}}">  {{($product->ecom_selling_unit!=null)?@$product->ecomSellingUnits->title:'Select'}}</span>
            <select name="ecom_selling_unit" class=" selectFocus form-control d-none">
            <option value="" disabled="" selected="">Choose Unit</option>
            @if($getProductUnit->count() > 0)
            @foreach($getProductUnit as $unit)
            <option {{ (@$product->ecom_selling_unit == $unit->id ? 'Selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
            @endforeach
             @endif
            </select>
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">Selling Unit Conversion Rate</td>
          <td style="border-left: 0px;">
            <span class="m-l-15 inputDoubleClick "   data-fieldvalue="{{@$product->selling_unit_conversion_rate}}">
           {{@$product->selling_unit_conversion_rate? @$product->selling_unit_conversion_rate:'--'}}
            </span>
           <input id="selling_unit_conversion_rate" type="number" style="width:80%;" name="selling_unit_conversion_rate" class="non-negative fieldFocus  d-none" value="{{@$product->selling_unit_conversion_rate}}">
          </td>
        </tr>
        <tr>
          <td class="font-weight-bold" style="border-right: 0px;">E-commerce COGS Price</td>
          <td style="border-left: 0px;">
            <span id="ecommr_cogs_price_span" class="m-l-15" data-fieldvalue="{{@$ecomCogs}}">
            {{ number_format($product->selling_unit_conversion_rate * $product->selling_price,3,'.',',')}}
            </span>
           <input id="ecommr_cogs_price" type="number" style="width:80%;" name="ecommr_cogs_price" class="non-negative fieldFocus d-none " value="{{@$ecomCogs}}">
          </td>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
</div>
</div>

{{-- phone View Table Start--}}
{{-- <div class="col-lg-5 col-md-6 mobile-phone-ui d-none"> --}}
<div class="col-lg-12 col-md-12 mobile-phone-ui d-none">
 <div class="bg-white">
   <table id="example" class="table tble-brdr headings-color sales-customer-table const-font product_info " style="width: 100%;">
     <tbody>

        <tr>
            <td class="font-weight-bold" colspan="1" height="90px">Title</td>
            <td class="" colspan="2">
                <span class="m-l-15 inputDoubleClick" id="name"  data-fieldvalue="{{@$product->name}}">
                    {{(@$product->name!=null)?@$product->name: @$product->short_desc}}</span>
                <input type="text"  name="name" class="fieldFocus d-none" value="{{(@$product->name!=null)?$product->name:''}}">
            </td>
       </tr>

        <tr>
            <td class="font-weight-bold" colspan="1">Min Order QTY</td>
            <td class="" colspan="2">
              <span class="m-l-15 inputDoubleClick" id="min_o_qty"  data-fieldvalue="{{@$product->min_o_qty}}">{{(@$product->min_o_qty!==null)?@$product->min_o_qty:'--'}}</span>
              <input  id="min_o_qty_ecomm" type="number" style="width:80%;" name="min_o_qty" class="non-negative fieldFocus d-none ecomm_enabled_fields" value="{{@$product->min_o_qty}}">
            </td>
        </tr>
        <tr>
            <td class="font-weight-bold" colspan="1">Max Order QTY</td>
            <td style="border-left: 0px;cursor: pointer !important;" colspan="2">
                <span class="m-l-15 inputDoubleClick" id="max_o_qty"  data-fieldvalue="{{@$product->max_o_qty}}">{{(@$product->max_o_qty!==null)?@$product->max_o_qty:'--'}}</span>
                <input id="max_o_qty_ecomm" type="number" style="width:80%;" name="max_o_qty" class="fieldFocus non-negative d-none ecomm_enabled_fields" value="{{@$product->max_o_qty}}">
            </td>
        </tr>
        <tr>
            <td class="font-weight-bold" colspan="1">Dimension (L x W x H)</td>
            <td style="border-left: 0px;" colspan="2">
              <div class="d-flex justify-content-between" style="white-space: nowrap;">
              <div class="pl-2 pr-2">
                <span class="m-l-15 inputDoubleClick" id="length"  data-fieldvalue="{{@$product->length}}" title="Length">{{(@$product->length!==null)?@$product->length:'--'}}</span>
                <input type="number" class="fieldFocus d-none" name="length" placeholder="Length" value="{{@$product->length}}" style="width: 80%">
                cm
              </div>
              <div class="pl-2 pr-2" title="Separator">
                x
              </div>
              <div class="pl-2 pr-2">
                <span class="m-l-15 inputDoubleClick" id="width"  data-fieldvalue="{{@$product->width}}" title="Width">{{(@$product->width!==null)?@$product->width:'--'}}</span>
                <input type="number" class=" fieldFocus d-none" name="width" placeholder="Width" style="width: 80%" value="{{@$product->width}}">
                cm
              </div>
              <div class="pl-2 pr-2" title="Separator">
                x
              </div>
              <div class="pl-2 pr-2">
                <span class="m-l-15 inputDoubleClick" id="height"  data-fieldvalue="{{@$product->height}}" title="Height">{{(@$product->height!==null)?@$product->height:'--'}}</span>
                <input type="number" class="fieldFocus d-none" name="height" placeholder="Height" value="{{@$product->height}}" style="width: 80%">
                cm
              </div>
              <div style="width: 30%;"></div>

              </div>
            </td>
          </tr>

            <tr>
                <td class="font-weight-bold" colspan="1">E-commerce Long Description</td>
                <td class="" colspan="2">
                    <span class="m-l-15 inputDoubleClick" id="long_desc"  data-fieldvalue="{{@$product->long_desc}}">
                    {{(@$product->long_desc!=null)?@$product->long_desc:'--'}}
                    </span>

                    <textarea id="long_desc_ecomm"name="long_desc" rows="5" class="fieldFocus d-none ecomm_enabled_fields w-100" cols="20">{{(@$product->long_desc!=null)?@$product->long_desc:''}}</textarea>
                </td>
            </tr>
            <tr>
                <td class="font-weight-bold" colspan="1">
                    <span>E-commerce Product weight per unit</span>
                </td>
                <td class="" colspan="2">
                    <span class="m-l-15 inputDoubleClick" id="ecom_product_weight_per_unit"  data-fieldvalue="{{@$product->ecom_product_weight_per_unit}}">
                        {{(@$product->ecom_product_weight_per_unit!=null)?@$product->ecom_product_weight_per_unit:'--'}}
                    </span>
                    <input id="ecom_product_weight_per_unit" type="number" style="width:40%;" name="ecom_product_weight_per_unit" class="fieldFocus non-negative d-none" value="{{@$product->ecom_product_weight_per_unit}}"> kg
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold" colspan="1">Selling Price</td>
                <td class="" colspan="2">
                    <span class="m-l-15 inputDoubleClick " id="ecommerce_price_span"  data-fieldvalue="{{@$product->ecommerce_price}}">
                    @if(@$product->ecommerce_price!=null)
                    {{ @$product->ecommerce_price }}&nbsp
                    @else
                    --  &nbsp
                    @endif
                    </span>
                    <input id="ecommerce_price" type="number" style="width:80%;" name="ecommerce_price" class="non-negative fieldFocus d-none " value="{{@$product->ecommerce_price}}" min="0" max="100">
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold"  colspan="1">Discount Price</td>
                <td class="" colspan="2">
                  <span class="m-l-15 inputDoubleClick "  id="discount_price_span" data-fieldvalue="{{@$product->discount_price}}" >
                    {{@$product->discount_price? @$product->discount_price:'--'}}
                  </span>
                  <input id="discount_price" type="number" style="width:80%;" name="discount_price" class="non-negative fieldFocus d-none " value="{{@$product->discount_price}}" min="0" max="100">
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold" colspan="1">Discount Expiry</td>
                <td class="" colspan="2">
                    @php
                    $today = date('Y-m-d');
                    @endphp
                    <span class="m-l-15 inputDoubleClick" data-fieldvalue="{{(@$product->discount_expiry_date? @$product->discount_expiry_date:'')}}">{{   $product->discount_expiry_date ? @$product->discount_expiry_date:'--' }}</span>
                    <input type="text" style="width: 85%;"data-id="{{@$product->id}}" class=" discount_expiration_date_dp d-none" name="discount_expiry_date"  value="{{  @$product->discount_expiry_date!=null? @$product->discount_expiry_date:''}}">
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold" colspan="1">E-Com Selling Unit</td>
                <td class="" colspan="2">
                    <span class="m-l-15 inputDoubleClick" data-fieldvalue="{{(@$product->ecom_selling_unit? @$product->ecom_selling_unit:'')}}">  {{($product->ecom_selling_unit!=null)?@$product->ecomSellingUnits->title:'Select'}}</span>
                    <select name="ecom_selling_unit" class=" selectFocus form-control d-none">
                    <option value="" disabled="" selected="">Choose Unit</option>
                    @if($getProductUnit->count() > 0)
                    @foreach($getProductUnit as $unit)
                    <option {{ (@$product->ecom_selling_unit == $unit->id ? 'Selected' : '' ) }} value="{{ $unit->id }}">{{ $unit->title }}</option>
                    @endforeach
                    @endif
                    </select>
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold" colspan="1">Selling Unit Conversion Rate</td>
                <td class="" colspan="2">
                    <span class="m-l-15 inputDoubleClick "   data-fieldvalue="{{@$product->selling_unit_conversion_rate}}">
                        {{@$product->selling_unit_conversion_rate? @$product->selling_unit_conversion_rate:'--'}}
                    </span>
                    <input id="selling_unit_conversion_rate" type="number" style="width:80%;" name="selling_unit_conversion_rate" class="non-negative fieldFocus  d-none" value="{{@$product->selling_unit_conversion_rate}}">
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold" colspan="1">E-commerce COGS Price</td>
                <td style="" colspan="2">
                    <span id="ecommr_cogs_price_span" class="m-l-15" data-fieldvalue="{{@$ecomCogs}}">
                        {{ number_format($product->selling_unit_conversion_rate * $product->selling_price,3,'.',',')}}
                    </span>
                    <input id="ecommr_cogs_price" type="number" style="width:80%;" name="ecommr_cogs_price" class="non-negative fieldFocus d-none " value="{{@$ecomCogs}}">
                </td>
            </tr>

     </tbody>
   </table>
 </div>

 </div>
{{-- phone View Table End --}}

@endif
</div>
<div class="row mt-4">
{{-- <div class="col-lg-8 col-md-6 mt-3"> --}}
<div class="col-lg-8 col-md-12 mt-3">

<div class="col-lg-12 col-md-12 pl-0 pr-0">
<h4 class="pb-2 font-weight-bold" style="color: #09355a;">Product Markup</h4>
</div>

<div class="bg-white table-responsive" style="min-height: 250px;">
  <table id="example" class="table headings-color mb-0 table-theme-header table_cust_category" style="width: 100%;">
    <thead class="sales-coordinator-thead font-size">
      <th>Customer<br>Category</th>
      @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
      <th style="">Category<br>Markup</th>
      <th>Product<br>Markup</th>
      @endif
      {{-- <th>MKT <i class="fa fa-question-circle-o resturent" aria-hidden="true"></i><div class="tooltiptext">This is Market Price!!!</div></th> --}}
      <th>Reference<br>Price</th>
      <th>Fixed<br>Price
          <input type="checkbox" name="fixed_price_check" class="fixed_price_check" id="fixed_price_check" value="{{$product->fixed_price_check}}" {{$product->fixed_price_check == 1 ? 'checked' : ''}}>
      </th>
      <th>Fixed Price<br>Expiration</th>
    </thead>

    <tbody class="font-size bg-white">
    @if($customerCategories->count() > 0)
      @foreach($customerCategories as $ccats)
        @php
          $getRecord      = new Product;
          $custCatMargin  = $getRecord->getDataOfProductMargins($product->id, $ccats->id, "custCatMargin");
          $custProdMargin = $getRecord->getDataOfProductMargins($product->id, $ccats->id, "custProdMargin");
          $prodRefPrice   = $getRecord->getDataOfProductMargins($product->id, $ccats->id, "prodRefPrice");
          $prodFixPrice   = $getRecord->getDataOfProductMargins($product->id, $ccats->id, "prodFixPrice");
          $mktCheck       = $getRecord->getDataOfProductMargins($product->id, $ccats->id, "mktCheck");
          $last_updated   = $getRecord->getDataOfProductMargins($product->id, $ccats->id, "last_updated");
        @endphp

      <tr>
        <td>{{$ccats->title}}</td>

        @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
        <td>{{$custCatMargin != "N.A" ? $custCatMargin->default_value : 0}}</td>

        <td id="{{$custProdMargin != 'N.A' ? $custProdMargin->id : ''}}">
          <span class="m-l-15 inputDoubleClickPM" id="default_value"  data-fieldvalue="{{$custProdMargin != 'N.A' ? $custProdMargin->default_value : ''}}">{{$custProdMargin != 'N.A' ? $custProdMargin->default_value : 0}}</span>
          <input type="number" style="width:50%;" name="default_value" class="fieldFocusPM d-none" value="{{$custProdMargin != 'N.A' ? $custProdMargin->default_value : 0}}">%
        </td>
        @endif

        {{-- <td>
          <input type="checkbox" name="customer_type_id" {{$mktCheck != 'N.A' ? $mktCheck->is_mkt == 1 ? 'checked' : '' : ''}} value="{{$ccats->id}}" class="mt-1 market_price_check">
        </td> --}}

        <td>
          <span class="{{strtolower($ccats->title)}}" {{$redHighlighted}} title="{{$tooltip}}">{{$prodRefPrice}}</span>
          <input type="hidden" name="pos" value="{{$checkItemPo}}">
        </td>

        <td id="{{$prodFixPrice != 'N.A' ? $prodFixPrice->id : ''}}">
          <span class="m-l-15 inputDoubleClickPM"  data-fieldvalue="{{$prodFixPrice != 'N.A' ? number_format($prodFixPrice->fixed_price,3,'.',',') : ''}}">{{$prodFixPrice != 'N.A' ? number_format($prodFixPrice->fixed_price,3,'.',',') : '--'}}</span>
          <input type="number" step="0.1" class="fieldFocusPM d-none" style="width: 80%;" name="product_fixed_price" value="{{$prodFixPrice != 'N.A' ? number_format($prodFixPrice->fixed_price,3,'.','') : ''}}">
        </td>

        <td id="{{$prodFixPrice != 'N.A' ? $prodFixPrice->id : ''}}">
          @if($prodFixPrice !== 'N.A')
            @if($prodFixPrice->fixed_price != 0)
              @php $doubleClick1 = "inputDoubleClickPM" ; @endphp
            @else
              @php $doubleClick1 = "" ; @endphp
            @endif
          @else
            @php $doubleClick1 = "" ; @endphp
          @endif
          @php
            $today = date('Y-m-d');
          @endphp
          <span class="m-l-15 {{$doubleClick1}}" data-fieldvalue="{{$prodFixPrice != 'N.A' ? (($prodFixPrice->expiration_date!=null)? Carbon::parse(@$prodFixPrice->expiration_date)->format('d/m/Y') :'') : ''}}">{{$prodFixPrice != 'N.A' ? (($prodFixPrice->expiration_date!=null)? Carbon::parse(@$prodFixPrice->expiration_date)->format('d/m/Y') :'---') : '---'}}</span>
          <input disabled="" type="text" style="width: 85%;" class="d-none expiration_date_dp" name="expiration_date" value="{{$prodFixPrice != 'N.A' ? (($prodFixPrice->expiration_date!=null)? Carbon::parse(@$prodFixPrice->expiration_date)->format('d/m/Y') :'') : ''}}">

          <a href="javascript:void(0)" style="cursor: pointer;" data-fieldvalue="{{$prodFixPrice != 'N.A' ? (($prodFixPrice->expiration_date!=null)? Carbon::parse(@$prodFixPrice->expiration_date)->format('d/m/Y') :'') : ''}}" class="expiration_date_null" title="Click here to empty Expiry"><i class="fa fa-times-circle pull-left"></i></a>

        </td>
      </tr>
      @endforeach
    @endif
    </tbody>
  </table>
</div>

</div>
<div class="col-lg-4 col-md-12 headings-color mt-3">
  <div class="pt-0 mt-0">

<!-- Customer Fixed Prices table  -->
@if($ProductCustomerFixedPrices)
<div class="row mb-2">

<div class="col-lg-8 col-md-8 pl-3 pr-0">
  <h4 class="font-weight-bold" style="color: #09355a;">Customer Fixed Prices </h4>
</div>


<div class="col-lg-2 col-md-2">
  <a href="{{url('sales/get-customer-documents/'.@$customer->id)}}" class="btn d-none add-btn btn-color pull-right mr-1" id="viewAllDocuments">View All</a>
</div>
<div class="col-lg-2 col-md-2 col-2 btn-sm-right">
  <button class="btn recived-button btn-color add-btn pull-right add-cust-fp btn-small" type="button" title="Add Customer Fixed Prices" id="add-cust-fp-btn"><i class="fa fa-plus"></i></button>
</div>



</div>

<div class="bg-white table-responsive" style="max-height: 323.3px; min-height: 323.3px;">
  <table id="example" class="table headings-color bg-white text-center const-font table-bordered table-theme-header" style="width: 100%;">
    <thead class="sales-coordinator-thead table-bordered">
      <tr>
        <th>Sr. #</th>
        <th>Customer # </th>
        <th>{{$global_terminologies['company_name']}}</th>
        <th>Customer <br> Price</th>
        <th>Discount %</th>
        <th>Price After <br> Discount</th>
        <th>Price <br> Expiration Date</th>
        <!-- <th>Action</th> -->
      </tr>
    </thead>
    <tbody>
      <?php $i=1; ?>
      @if($ProductCustomerFixedPrices->count() > 0)
      @foreach($ProductCustomerFixedPrices as $ProductCustomerFixedPrice)
      <tr id="{{$ProductCustomerFixedPrice->id}}" style="border:1px solid #eee;">
        <td align="left">{{$i}}</td>
        <td  align="left">{{$ProductCustomerFixedPrice->customers->reference_number}}</td>
        <td  align="left">{{$ProductCustomerFixedPrice->customers->company}}</td>
        <td  align="right">
          <span class="m-l-15 inputDoubleClickFixedPrice" id="fixed_price"  data-fieldvalue="{{$ProductCustomerFixedPrice->fixed_price}}">{{number_format((float)@$ProductCustomerFixedPrice->fixed_price, 3, '.', '')}}</span>
          <input type="number" style="width:100%;" name="fixed_price" class="fieldFocusFixedPrice d-none" value="{{number_format((float)@$ProductCustomerFixedPrice->fixed_price, 3, '.', '')}}">
        </td>

        <td align="right">
            <span class="m-l-15 inputDoubleClickFixedPrice" id="discount"  data-fieldvalue="{{@$ProductCustomerFixedPrice->discount}}">{{(@$ProductCustomerFixedPrice->discount!=null)?number_format(@$ProductCustomerFixedPrice->discount,2,'.',','):'N.A'}}</span>
            <input type="number" name="discount" class="d-none fieldFocusFixedPrice" data-id="{{@$ProductCustomerFixedPrice->products->id}}" value="{{(@$ProductCustomerFixedPrice->discount!=null)?number_format($ProductCustomerFixedPrice->discount,2,'.',''):''}}">
            </td>
            <td align="right">{{(@$ProductCustomerFixedPrice->price_after_discount!=null)?number_format(@$ProductCustomerFixedPrice->price_after_discount,2,'.',','):'N.A'}}</td>
        <td align="left">
          @php
            $today = date('Y-m-d');
          @endphp
          <span class="m-l-15 inputDoubleClickFixedPrice" id="expiration_date"  data-fieldvalue="{{($ProductCustomerFixedPrice->expiration_date!=null)? Carbon::parse(@$ProductCustomerFixedPrice->expiration_date)->format('d/m/Y') :''}}">{{($ProductCustomerFixedPrice->expiration_date!=null)? Carbon::parse(@$ProductCustomerFixedPrice->expiration_date)->format('d/m/Y') :'--'}}</span>
          <input type="text" style="width:100%;" name="expiration_date" class="expiration_date_fp d-none exp_date_fp" value="{{($ProductCustomerFixedPrice->expiration_date!=null)? Carbon::parse(@$ProductCustomerFixedPrice->expiration_date)->format('d/m/Y') :''}}">
        </td>
        <!-- <td>
          <button type="button" style="cursor: pointer;" class="btn-xs btn-danger delete_cust" data-row_id="{{$ProductCustomerFixedPrice->id}}" name="delete_cust" id="delete_cust"><i class="fa fa-trash"></i></button>
        </td> -->
      </tr>
      <?php $i++; ?>
      @endforeach
      @else
      <tr>
      <td colspan="6" align="center">No Data Found</td>
      </tr>
      @endif
    </tbody>
  </table>
</div>
@endif

</div>
</div>
</div>
<div class="row mb-3 mt-4">

<div class="col-lg-12 col-md-12 headings-color mt-3">

<div class="col-lg-12 col-md-12 pl-0 pr-0 ">
  <div class="row">
    <div class="col-lg-11 col-md-10">
      <h4 class="pb-2 font-weight-bold" style="color: #09355a;">Stock Card</h4>
    </div>
    <div class="col-lg-1 col-md-2 btn-sm-right">
      {{-- <form id="stock-detail-report" action="{{url('stock-detail-report')}}" method="POST" style="display: none;" target="_blank">
        @csrf
        <input type="hidden" name="product_id" value="{{$product->id}}">
      </form> --}}
      {{-- <button class="btn recived-button view-supplier-btn stock-detail-report" type="button" title="See History" onclick="event.preventDefault();document.getElementById('stock-detail-report').submit();"><i class="fa fa-history"></i></button> --}}
      <a href="{{route('stock-detail-report',['id'=>$product->id])}}" class="btn recived-button bg-btn" type="button" target="_blank" title="See History" ><i class="fa fa-history"></i></a>
    </div>
  </div>
</div>

  <!-- Nav tabs -->
<ul class="nav nav-tabs warehouses-tab" style="background-color: {{$sys_color->system_color}};color: white;">
@foreach($warehouse_products as $key => $warehouse)
  <li class="nav-item">
    <a class="nav-link click-nav font-size @if($key == 0) active @endif" data-toggle="tab" id="default_id{{$warehouse->getWarehouse->id}}" data-id="{{$warehouse->getWarehouse->id}}" data-wp="{{$warehouse->id}}" href="#id{{$warehouse->id}}">{{ $warehouse->getWarehouse->warehouse_title }}</a>
  </li>
@endforeach
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2 ||  Auth::user()->role_id == 4 || Auth::user()->role_id == 9 || Auth::user()->role_id == 11)
  <li class="align-items-center d-flex ml-auto pr-2">
    <a href="javascript:void(0)" class="btn add-btn btn-color bg-white font-size add-new-stock-btn pull-right" data-warehouse_id="{{@$wh->warehouse_id}}" data-id="parent_stock" title="Add Manual Stock" style="color: #09355a;">Add Stock</a>
  </li>
  @endif
</ul>

<!-- Tab panes -->
<div class="tab-content bg-white">
@foreach($warehouse_products as $key => $wh)
<div class="tab-pane p-0 po-pod-list @if($key == 0) active @endif" id="id{{$wh->id}}">
    <div class="bg-white table-responsive h-100 d-flex justify-content-center p-2">
      <i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span>
    </div>
  </div>
 @endforeach
</div>

</div>

</div>
<div class="row mb-3"> <!-- Product supplier div -->
<!-- left Side Start -->

<div class="col-lg-12 col-md-12 headings-color mt-3">

<div class="row">

<div class="col-lg-10 col-md-9 pl-3 pr-0">
  <h4 class="font-weight-bold" style="color: #09355a;">Product Suppliers</h4>
</div>

<div class="col-lg-1 col-md-1">
  <button class="btn recived-button view-supplier-btn add-bulk-suppliers d-none" type="button" title="Bulk Upload"><i class="fa fa-upload"></i></button>
</div>

<div class="col-lg-1 col-md-2">
  <button class="btn recived-button view-supplier-btn add-supplier btn-size btn-sm-right-product" type="button" title="Add Product Supplier"><i class="fa fa-plus"></i></button>
</div>

</div>

<div class="bg-white">

  <div class="table-responsive">
    <table class="table entriestable table-bordered table-product-suppliers text-center purchase-complete-product table-theme-header">
      <thead>
        <tr>
          <th>Action</th>
          <th>Reference Name </th>
          <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>
          <th {{@$price_col_visibility}}>{{$global_terminologies['supplier_description']}}</th>
          <th {{@$price_col_visibility}}>{{$global_terminologies['import_tax_actual']}}</th>
          <th {{@$price_col_visibility}}>Purchasing VAT %</th>
          <th> Gross <br> Weight</th>
          <th {{@$price_col_visibility}}> {{$global_terminologies['freight_per_billed_unit']}}</th>
          <th {{@$price_col_visibility}}> {{$global_terminologies['landing_per_billed_unit']}} </th>

          <th {{@$price_col_visibility}}>Purchasing<br>Price </th>
            <th {{@$price_col_visibility}}>Purchasing <br> Price <br> In  <b>  (THB)</b></th>
          <th>{{$global_terminologies['extra_cost_per_billed_unit']}}</th>
          <th>{{$global_terminologies['extra_tax_per_billed_unit']}}</th>
          <th>Unit Import Tax</th>
          <th>Lead <br> Days</th>
          <th> Ordering Unit</th>
          <th>{{$global_terminologies['order_qty_unit']}}</th>
          <th>{{$global_terminologies['minimum_order_quantity']}}</th>
        </tr>
      </thead>
    </table>
  </div>

</div>

</div>


</div>
<div class="row mb-3"> <!-- Product supplier div -->
<!-- left Side Start -->

@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
<div class="col-lg-12 col-md-12 headings-color">

<div class="row">

<div class="col-lg-10 col-md-9 pl-3 pr-0">
  <h4>Product History</h4>
</div>

<div class="col-lg-1 col-md-1"></div>

<div class="col-lg-1 col-md-2"></div>

</div>

<div class="bg-white">
  <!-- product history table -->
  <div class="product-update-history pt-2 pb-3 pr-3 pl-3">

    <table id="table-product-history" class="table-product-history entriestable table table-bordered text-center table-theme-header" style="width: 98%;font-size: 12px;overflow: hidden;">
      <thead>
        <tr>
          <th>User  </th>
          <th>Date/time </th>
          <th>Updated From </th>
          <th>Column</th>
          <th>Old Value</th>
          <th>New Value</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- product history table -->
</div>

</div>
@endif

</div>
<!-- main content end here -->
</div><!-- main content end here -->
<!-- new design ends here -->

{{-- Add Product fixed price modal --}}
<div class="modal addProdFixedPriceModal" id="addProdFixedPriceModal">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
  <h4 class="modal-title">ADD CUSTOMER FIXED PRICE</h4>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>


<form id="addProdFixedPriceForm" method="POST">

  <div class="modal-body">

    <input type="hidden" name="product_id" value="{{$product->id}}">

    <div class="form-group">
      <label class="pull-left">Customer <b style="color: red;">*</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod-fixed-cust" name="customers" required="true">
        <option value="">Select Customer</option>
        @if($customers)
        @foreach($customers as $customer)
          <option value="{{$customer->id}}">{{$customer->company}}</option>
        @endforeach
        @endif
      </select>
    </div>

    <div class="form-group">
      <label class="pull-left">Price <b style="color: red;">*</b></label>
      <input type="text" class="font-weight-bold form-control-lg form-control" id="product_default_price" name="default_price" value="" readonly/>
    </div>

    <div class="form-group">
      <label class="pull-left">Customer Price <b style="color: red;">*</b></label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Define Customer Price Here" name="fixed_price" type="number" id="customer_price" step="any"/>
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

    @php
      $today = date('d/m/Y');
    @endphp
    <div class="form-group">
      <label class="pull-left">Expiration Date</label>
      <input class="font-weight-bold form-control-lg form-control expiration_date_fp" placeholder="Expiration Date" name="expiration_date" autocomplete="off"/>
    </div>

  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary save-fixed-price" id="addProdFixedPriceBtn">Add</button>
  </div>
</form>

</div>
</div>
</div>

{{-- Add Product margins --}}
<div class="modal addProdMarginsModal" id="addProdMarginsModal">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
  <h4 class="modal-title">ADD PRODUCT MARGINS</h4>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>


<form id="addProdMarginsForm" method="POST">

  <div class="modal-body">
     <input type="hidden" name="product_id" value="{{$product->id}}">
      <div class="form-group">
      <label class="pull-left">@if(!array_key_exists('customer_type', $global_terminologies))Customer Type @else {{$global_terminologies['customer_type']}} @endif*</label>
      <select class="font-weight-bold form-control-lg form-control" name="customer_type">
        <option value="">Select Category</option>
        @if($customerCategories)
        @foreach($customerCategories as $cat)
          <option value="{{$cat->id}}">{{$cat->title}}</option>
        @endforeach
        @endif
      </select>
    </div>
    <div class="form-group">
      <label class="pull-left">Default Margin</label>
      <select class="font-weight-bold form-control-lg form-control" name="default_margin">
        <option value="">Select Margin</option>
        <option value="Fixed">Fixed</option>
        <option value="Percentage">Percentage</option>
      </select>
    </div>

    <div class="form-group">
      <label class="pull-left">Default Value</label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Default Value" name="default_value"  type="text">
    </div>

    <div class="form-group">
      <label class="pull-left">Expiry</label>
      <input class="font-weight-bold form-control-lg form-control" name="prod_expiry"  type="date">
    </div>

  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary save-prod-marg" id="addProdMargBtn">Add</button>
  </div>
</form>

</div>
</div>
</div>

{{-- Add Supplier modal --}}
<div class="modal addSupplierModal" id="addSupplierModal">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
  <h4 class="modal-title">ADD PRODUCT SUPPLIER</h4>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>


<form id="addSupplierForm" method="POST">

  <div class="modal-body">

    <input type="hidden" name="product_id" value="{{$product->id}}">

    <div class="form-row">
      <div class="form-group col-6 supplier-select-span">
        <label class="pull-left">@if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif <b style="color: red;">*</b></label><br>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags product_suplliers" name="supplier">

        </select>
      </div>

      <div class="form-group col-6">
        <label class="pull-left"> @if(!array_key_exists('suppliers_product_reference_no', $global_terminologies)) Product Supplier Ref# @else {{$global_terminologies['suppliers_product_reference_no']}} @endif</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">{{$global_terminologies['import_tax_actual']}}</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">@if(!array_key_exists('gross_weight', $global_terminologies)) Gross Weight @else {{$global_terminologies['gross_weight']}} @endif </label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Gross Weight" name="gross_weight" type="number">
      </div>
    </div>

    <div class="form-row">
    <div class="form-group col-6">
      <label class="pull-left">Freight</label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Freight" name="freight" type="number">
    </div>

    <div class="form-group col-6">
      <label class="pull-left">Landing</label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Landing" name="landing" type="number">
    </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Purchasing Price <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Purchasing Price" name="buying_price" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Extra Cost </label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Extra Cost" name="extra_cost"  type="number">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Ordering Unit</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Ordering Unit" name="supplier_packaging" type="text">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Billed Unit</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Billed Unit" name="billed_unit"  type="text">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">@if(!array_key_exists('minimum_order_quantity', $global_terminologies)) MOQ @else {{$global_terminologies['minimum_order_quantity']}} @endif</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Minimum Order QTY" name="m_o_q" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Leading Time <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="leading time e.g 2 days" name="leading_time"  type="number">
      </div>
    </div>

  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary save-prod-sup" id="addSupplierBtn">Add</button>
  </div>
</form>

</div>
</div>
</div>

{{-- Add Supplier for dropdown add new modal --}}
<div class="modal addSupplierModalDropDown" id="addSupplierModalDropDown">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
  <h4 class="modal-title" id="headingSupplier"></h4>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<form id="addSupplierModalDropDownForm" method="POST">

  <div class="modal-body">

    <input type="hidden" name="product_id" value="{{$product->id}}">

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Supplier <b style="color: red;">*</b></label>
        <input type="hidden" name="selected_supplier_id" class="selected_supplier_id" id="selected_supplier_id" value="">
        <input type="text" class="font-weight-bold form-control-lg form-control addSuppDropDown" name="supplier" readonly="" value="">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Product Supplier Ref#.</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">{{$global_terminologies['import_tax_actual']}}</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Gross Weight</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Gross Weight" name="gross_weight" type="number">
      </div>
    </div>

    <div class="form-row">
    <div class="form-group col-6">
      <label class="pull-left">Freight</label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Freight" name="freight" type="number">
    </div>

    <div class="form-group col-6">
      <label class="pull-left">Landing</label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Landing" name="landing" type="number">
    </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Purchasing Price <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Purchasing Price" name="buying_price" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Extra Cost </label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Extra Cost" name="extra_cost"  type="number">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Ordering Unit</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Ordering Unit" name="supplier_packaging" type="text">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Billed Unit</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Billed Unit" name="billed_unit"  type="text">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">MOQ</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Minimum Order QTY" name="m_o_q" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Leading Time <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="leading time e.g 2 days" name="leading_time"  type="number">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Supplier Description</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Supplier Description" name="supplier_description" type="text">
      </div>
    </div>

  </div>

  <div class="modal-footer">
    <button type="submit" class="btn btn-primary save-prod-sup-drop-down" id="addSupplierBtnDropDown">Add</button>
  </div>
</form>

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
    </div>
  </div>
</div>
</div>

<!-- New Brand add modal -->
<div class="modal" id="brand_modal" role="dialog">
<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Add New @if(!array_key_exists('brand', $global_terminologies)) Brand @else {{$global_terminologies['brand']}} @endif</h4>
      <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
    </div>

    <form role="form" id="add-brands-form" class="add-brands-form" method="post">
    <div class="modal-body">

      <div class="form-row">
        <div class="form-group col-3">
          <label class="pull-right mt-3 font-weight-bold">Brand Title:</label>
        </div>

        <div class="form-group col-6">
          <input type="text" name="brand_title" class="font-weight-bold form-control-lg form-control" placeholder="Brand Title Here" required="">
        </div>

        <div class="form-group col-3"></div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary addBrandBtn" id="addBrandBtn">Add</button>
      </div>

    </div>
    </form>

  </div>
</div>
</div>

<!-- Modal For Image Uploading -->
<div class="modal" id="productImagesModal">
<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Add Product Images</h4>
      <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
    </div>
    <!-- Modal body -->
    <!-- <form role="form" class="add-prodimage-form" method="post" enctype="multipart/form-data">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12 text-center">
          <div class="row">
            <div class="col-xs-12 col-md-12">
              <div class="col-md-12 col-lg-12 col-xs-12" id="columns">
                <h3 class="form-label">Select the images</h3>
                <div class="desc"><p class="text-center">or drag to box</p></div>
                <p style="color: red;">Note: Maximum number of upload images is 4
                  @if(@$productImagesCount > 0)
& {{@$productImagesCount}} is/are already uploaded.
              @endif</p>
                <div id="uploads" class="row"></div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="modal-footer">
      <input type="hidden" name="product_id" class="img-product-id">
      <button class="btn btn-danger" id="reset" type="button" ><i class="fa fa-history"></i> Clear</button>
      <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-upload"></i> Upload </button>
      <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
    </div>
   </form>
   -->
   <div class="row">
        <input type="hidden" name="product_id_for_cropping" value="{{$id}}" class="product_id_for_cropping">
        <div class="col-md-12 text-center mt-2">
        <div id="upload-demo"></div>
        </div>
        <div class="col-md-12" style="padding:5%;">
        <strong>Select image to crop:</strong>
        <input type="file" id="image" accept=".png, .jpg, .jpeg">

        <button class="btn btn-primary btn-block upload-image" style="margin-top:2%">Upload Image</button>
        </div>
      </div>

  </div>
</div>
</div>

{{-- Upload Bulk suppliers excel file --}}
<div class="modal fade" id="uploadExcel">
<div class="modal-dialog modal-dialog-centered parcelpop">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">×</button>
    </div>
    <div class="modal-body text-center">
      <h3 class="text-capitalize fontmed">Upload Excel</h3>
      <div class="mt-3">
        <form method="post" action="{{url('upload-bulk-product-suppliers')}}" class="upload-excel-form" enctype="multipart/form-data">
          {{csrf_field()}}

          <div class="form-group">
            <a href="{{asset('public/site/assets/purchasing/prod_suppliers/upload_bulk_suppliers.xlsx')}}" download><span class="btn btn-success" id="examplefilebtn">Download Example File</span></a>
          </div>

          <div class="form-group">
            <input type="file" name="excel" class="font-weight-bold form-control-lg form-control" required="">
          </div>

          <div class="form-submit">
            <input type="submit" value="upload" class="btn btn-bg save-btn">
            <input type="reset" value="close" class="btn btn-danger close-btn">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>

{{-- Product Images Modal --}}
<div class="modal" id="images-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Product Images</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <p><span class="text-danger">Note :</span> <i>If no image is selected by default first image will show on Ecommerce.</i></p>
          <div class="row fetched-images">
          </div>
        </div>
        <div class="modal-footer">
         <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>
@endsection

@section('javascript')
<script>

    $(document).ready(function(){
        var rows = $(".table_cust_category tr");

        rows.eq(5).insertBefore(rows.eq(3));
    });

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
</script>
  <!-- When Warehouse User is logged In He/She Can't Edit Product Detail -->
@if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6 || Auth::user()->role_id == 7 )
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.inputDoubleClick').removeClass('inputDoubleClick');
      $('.selectDoubleClick').removeClass('selectDoubleClick');
      $('.prodSuppInputDoubleClick').removeClass('prodSuppInputDoubleClick');
      $('.inputDoubleClickFirst').removeClass('inputDoubleClickFirst');
      $('.inputDoubleClickFixedPrice').removeClass('inputDoubleClickFixedPrice');
      $('.selectDoubleClickPM').removeClass('selectDoubleClickPM');
      $('.inputDoubleClickPM').removeClass('inputDoubleClickPM');
      $('.market_price_check').attr('disabled',true);
      $('#add-product-image-btn').hide();
      $('#add-cust-fp-btn').hide();
      $('.add-supplier').hide();



    });
  </script>
@endif
<script>
  function backFunctionality(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('complete-list-product') }}";
       document.location.href = url;
     }
   }
</script>
<script type="text/javascript">
  var toggleEvent=false;
  var stock_table = $('.stock_table').DataTable({
    searching: false,
    "bPaginate": false,
    "bLengthChange": false,
    "bFilter": true,
    "bInfo": false,
    "bAutoWidth": false,
    ordering: false,
  });
$(".expiration_date_sc").datepicker({
  format: "dd/mm/yyyy",
  autoHide: true,
});

$(".expiration_date_dp").datepicker({
  format: "dd/mm/yyyy",
  autoHide: true,
  startDate: new Date(),
});

$(".discount_expiration_date_dp").datepicker({
  format: "dd/mm/yyyy",
  autoHide: true,
  startDate: new Date(),
});

$(".expiration_date_fp").datepicker({
  format: "dd/mm/yyyy",
  autoHide: true,
  startDate: new Date(),
});

$(document).ready(function(){
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
});

// getting product Image
$(document).on('click', '.show-prod-image', function(e){
  let sid = $(this).data('id');
  $.ajax({
    type: "get",
    url: "{{ route('get-prod-image') }}",
    data: 'prod_id='+sid,
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
    },
    success: function(response){
      $("#loader_modal").modal('hide');
      $('.fetched-images').html(response);
    },
    error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
  });
});

// delete image
$(document).on('click', '.delete-img-btn', function(e){
      var id = $(this).data('img');
      var prodid = $(this).data('prodid');
      swal({
          title: "Alert!",
          text: "Are you sure you want to delete this image?",
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
                data:'id='+id+'&prodid='+prodid,
                url:"{{ route('remove-prod-image') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  // $('.show-prod-image').trigger('click');
                  $("#loader_modal").modal('hide');
                  if(data.search('done') !== -1){
                    myArray = new Array();
                    myArray = data.split('-SEPARATOR-');
                    let i_id = myArray[1];
                    let count = myArray[2];

                    $('.fetched-images #prod-image-'+i_id).remove();
                    $('#my_images').load(document.URL +  ' #my_images');
                    toastr.success('Success!', 'Image deleted successfully.' ,{"positionClass": "toast-bottom-right"});
                    if(count == 0){
                      location.reload();
                    }
                    if(count < 4)
                    {
                      $('#add-product-image-btn').removeClass('d-none');
                        let sid = prodid;
                          $.ajax({
                            type: "get",
                            url: "{{ route('get-prod-image') }}",
                            data: 'prod_id='+sid,
                            beforeSend:function(){
                              $('#loader_modal').modal({
                                backdrop: 'static',
                                keyboard: false
                              });
                              $("#loader_modal").modal('show');
                            },
                            success: function(response){
                              $("#loader_modal").modal('hide');
                              $('.fetched-images').html(response);
                            },
                            error: function(request, status, error){
                                $("#loader_modal").modal('hide');
                              }
                          });
                    }
                  }
                },
                error: function(request, status, error){
                  $("#loader_modal").modal('hide');
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
  });

$(document).ready(function(){
  // $(".ddddd").toggle();
  $(".toggle-btn").click(function(){
  $(this).next().collapse('toggle');
  });
});

// $('.collapse_rows').on('click',function(){
$(document).on('click','.collapse_rows',function(){
  var id = $(this).data('id');
  // alert(id);
  $(this).find('#sign'+id).text(function(_, value){return value=='-'?'+':'-'});
  $("#ddddd"+id).toggle();
  $('#add-new-stock-btn'+id).toggle();
});

$(".state-tags").select2();

$(document).ready(function(){
  // MAKE SURE YOUR SELECTOR MATCHES SOMETHING IN YOUR HTML!!!
    $('i.resturent').qtip({
      content: {
        text: $('i.resturent').next('.tooltiptext')
      }
    });

    $('i.buy_unit_cost_price_mark').qtip({
      content: {
        text: $('i.buy_unit_cost_price_mark').next('.tooltiptext')
      }
    });

    $('i.buy_unit_cost_price_mark_for_supp').qtip({
      content: {
        text: $('i.buy_unit_cost_price_mark_for_supp').next('.tooltiptext')
      }
    });

    $(document).on('click','.add-bulk-suppliers',function(){
      $('#uploadExcel').modal('show');
    });

    $(document).on('click','.add-new-stock-btn',function(){
      var stock_id = $(this).data('id');
      if(stock_id == 'parent_stock')
      {
        var warehouse_id = $(".warehouses-tab li a.active").data("id");
      }
      else
      {
        var warehouse_id = $(this).data('warehouse_id');
      }
      var prod_id  = "{{ $id }}";
      swal({
        title: "Are you sure?",
        text: "You want to Make Manual Stock Adjustment!!!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: " #006400",
        confirmButtonText: "Yes, do it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
        function (isConfirm) {
            if (isConfirm) {
              $.ajax({
                method:"get",
                data:'prod_id='+prod_id+'&warehouse_id='+warehouse_id+'&stock_id='+stock_id,
                url: "{{ route('make-manual-stock-adjustment') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                },
                success: function(response){
                  $("#loader_modal").modal('hide');
                  if(response.parent_stock === true){
                    window.location.reload();
                  }
                  if(response.success === true){
                    $("#stock-detail-table"+stock_id+" tbody > tr:first").before(response.html_string);
                    $('.table-product-history').DataTable().ajax.reload();

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

    $(document).on("change",".expiration_date_sc",function(e) {
      var old_value = $(this).prev().data('fieldvalue');
      if (e.keyCode === 27 && $(this).hasClass('active')) {
        var fieldvalue = $(this).prev().data('fieldvalue');
        var thisPointer = $(this);
          thisPointer.addClass('d-none');
          // thisPointer.val(fieldvalue);
          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');
          $(".expiration_date_sc").datepicker('hide');
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
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else if($(this).val() !== '' && $(this).hasClass('active'))
        {
          var id = $(this).data('id');
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).removeData('fieldvalue');
            $(this).prev().data('fieldvalue', new_value);
            $(this).attr('value', new_value);
            // alert(new_value);
            $(this).prev().html(new_value);
            $.ajax({
              method: "post",
              url: "{{ url('update-stock-record') }}",
              dataType: 'json',
              data: 'id='+id+'&'+$(this).attr('name')+'='+$(this).val()+'&'+'old_value='+old_value,
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
                  $('.table-product-history').DataTable().ajax.reload();
                  // $('#quantity_out').removeClass('inputDoubleClickFirst');
                  // $('#quantity_in').removeClass('inputDoubleClickFirst');
                  window.location.reload();
                }
                if(data.expiration_date == true)
                {
                  toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-product-history').DataTable().ajax.reload();
                }

                if(data.quantity_out_reserved == true)
                {
                  toastr.error('Sorry!', 'Cannot update the stock , because some orders/shipments are out/in from/to this stock !!!',{"positionClass": "toast-bottom-right"});
                }
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
            });
          }
        }
      }
    });

    $(document).on('click','.deleteStock',function(e){
      var id = $(this).data('id');
      // alert(id);
      swal({
      title: "Are you sure?",
      text: "You want to delete this stock entry!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#006400",
      confirmButtonText: "Yes, Delete it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
      },
      function (isConfirm) {
        if(isConfirm)
        {
          $.ajax({
            method: "post",
            url: "{{ url('delete-stock-record') }}",
            dataType: 'json',
            data: {id:id},
            beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            success: function(data)
            {
              // $("#loader_modal").modal('hide');
              if(data.success == true)
              {
                toastr.success('Success!', 'Stock deleted successfully.',{"positionClass": "toast-bottom-right"});
                location.reload();
                {{-- window.location.reload(); --}}
              }

               if(data.already_out == true)
              {
                toastr.error('Sorry!', 'Stock cannot be deleted as it is already out against an order.',{"positionClass": "toast-bottom-right"});
                // location.reload();
                {{-- window.location.reload(); --}}
              $("#loader_modal").modal('hide');

              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
        else
        {
          swal("Cancelled", "", "error");
        }
      }
    );
    });

  $(document).on('keypress keyup focusout', '.fieldFocusStock', function(e){
    var old_value = $(this).prev().data('fieldvalue');

    if (e.keyCode === 27 && $(this).hasClass('active'))  //27 is for pressing esc
    {
      var fieldvalue = $(this).prev().data('fieldvalue');
      $(this).addClass('d-none');
      $(this).val(fieldvalue);
      $(this).removeClass('active');
      $(this).prev().removeClass('d-none');
    }

  if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

    if($(this).val().length < 1)
    {
      return false;
    }
    else
    {
      var expiry_id=$(this).data('smi');
      var warehouse_id=$(this).data('warehouse_id');
      var type=$(this).data('type');
      var current_stock=$('.current-quantity-'+warehouse_id).val();
      var iddd = $(this).data('id');
      console.log(iddd);
      console.log('wh'+warehouse_id);
      console.log(expiry_id);
      console.log(type);

      var out=$('.expiry-out-value-'+expiry_id).val();
      var inn=$('.expiry-in-value-'+expiry_id).val();
      var balance=$('.expiry-balance-value-'+expiry_id).val();
      console.log(inn,out,balance);
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();
      var type_in = false;
      var type_out = false;
      if(type=='in')
      {
        type_in = true;
        var new_in_value=parseInt(inn)+parseInt(new_value)-old_value;
        $('.expiry-in-value-'+expiry_id).val(new_in_value);
        var new_balance_value=parseInt(balance)+parseInt(new_value)-old_value;
        $('.expiry-balance-value-'+expiry_id).val(new_balance_value);

        var new_current_stock=parseInt(current_stock)+parseInt(new_value)-old_value;
        // $('.current-quantity-'+warehouse_id).val(new_current_stock);
        // $('.span-current-quantity-'+warehouse_id).html(new_current_stock);

        // $('.span-expiry-in-value-'+expiry_id).html(new_in_value);
        // $('.span-expiry-balance-value-'+expiry_id).html(new_balance_value);
      }
      else if(type=='out')
      {
        type_out = true;
        console.log('wh'+warehouse_id);
        console.log(expiry_id);
        var new_out_value=parseInt(out)-parseInt(new_value)-old_value;
        console.log(new_out_value);
        var new_balance_value=parseInt(balance)-parseInt(new_value)-old_value;
        $('.expiry-out-value-'+expiry_id).val(new_out_value);
        $('.expiry-balance-value-'+expiry_id).val(new_balance_value);

        var new_current_stock=parseInt(current_stock)-parseInt(new_value)-old_value;
        // $('.current-quantity-'+warehouse_id).val(new_current_stock);
        // $('.span-current-quantity-'+warehouse_id).html(new_current_stock);

        // $('.span-expiry-out-value-'+expiry_id).html(new_out_value);
        // $('.span-expiry-balance-value-'+expiry_id).html(new_balance_value);
      }


      if(fieldvalue == new_value)
      {
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(fieldvalue);
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var id = $(this).data('id');
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          // alert(new_value);
          $(this).prev().html(new_value);
          $.ajax({
            method: "post",
            url: "{{ url('update-stock-record') }}",
            dataType: 'json',
            data: 'id='+id+'&'+$(this).attr('name')+'='+$(this).val()+'&'+'old_value='+old_value,
            beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            success: function(data)
            {
              // stock_table.fnPageChange("first",1);

              $("#loader_modal").modal('hide');
              if(data.success == true)
              {
                toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
                $('.current-quantity-'+warehouse_id).val(data.current_stock);
                $('.span-current-quantity-'+warehouse_id).html(data.current_stock);
                $('.span-expiry-out-value-'+expiry_id).html(data.total_out_in_exp);
                $('.span-expiry-in-value-'+expiry_id).html(data.total_in_in_exp);
                $('.span-expiry-balance-value-'+expiry_id).html(data.final_balance);

                $('.table-product-history').DataTable().ajax.reload();
                $('#manual_order_'+data.id).html(data.order_no);
                if(type_out == true)
                {
                  $('#quantity_out').addClass('inputDoubleClickFirst');
                  $("#quantity_in_span_"+data.id).removeClass('inputDoubleClickFirst');
                }
                if(type_in == true)
                {
                  $("#quantity_out_span_"+data.id).removeClass('inputDoubleClickFirst');
                  $('#quantity_in').addClass('inputDoubleClickFirst');
                }
                // $(this).prev().addClass('inputDoubleClickFirst');
                // $('.disableDoubleOutClick-'+iddd).removeClass('inputDoubleClickFirst');
                // $('.disableDoubleInClick-'+iddd).removeClass('inputDoubleClickFirst');
                {{-- window.location.reload(); --}}
              }
              if(data.expiration_date == true)
              {
                toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
                $('.table-product-history').DataTable().ajax.reload();
              }
              if(data.success == false && data.cannot_add == true)
              {
                if(data.quantity_out)
                {
                  $("#quantity_out_"+data.id).val('');
                  $("#quantity_out_span_"+data.id).html('--');

                }
                else
                {
                  $("#quantity_in_"+data.id).val('');
                  $("#quantity_in_span"+data.id).html('--');

                }
                toastr.info('Sorry!', 'Single adjustment can be used either for quantity in or out not for both !!!.',{"positionClass": "toast-bottom-right"});
              }

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
    }
  }
});

$(document).on("change",".selectFocusStock",function() {

    var old_value = $(this).prev().data('fieldvalue');
    var id = $(this).data('id');
    var thisPointer= $(this);
    swal({
      title: "Are you sure?",
      text: "Are you sure you want to update the Reason!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#006400",
      confirmButtonText: "Yes, Update it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
      },
      function (isConfirm) {
        if(isConfirm)
        {
          var new_value = $("option:selected", thisPointer).html();
          thisPointer.addClass('d-none');
          thisPointer.prev().html(new_value);
          thisPointer.prev().removeClass('d-none');
          $.ajax({
            method: "post",
            url: "{{ url('update-stock-record') }}",
            dataType: 'json',
            data: 'id='+id+'&'+thisPointer.attr('name')+'='+new_value+'&'+'old_value='+old_value,
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
                $('.table-product-history').DataTable().ajax.reload();
                // $('#quantity_out').removeClass('inputDoubleClickFirst');
                // $('#quantity_in').removeClass('inputDoubleClickFirst');
                {{-- window.location.reload(); --}}
              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
        else
        {
          swal("Cancelled", "", "error");
        }
      }
    );


});

});

$(document).ready(function(){
  // $('#default_id1').trigger('click');
var current_image = 1;

$('.next-image').on('click',function(){
  var total_images = "{{$productImagesCount}}";
  if(current_image < total_images)
  {
    current_image++;
  }

  $('.tag'+current_image).trigger('click');
});
$('.prev-image').on('click',function(){
  var total_images = "{{$productImagesCount}}";
  if(current_image > 1)
  {
    current_image--;
  }

  $('.tag'+current_image).trigger('click');
});
var prod_detail_id= "{{$product->id}}";
$(".state-tags").select2({dropdownCssClass : 'bigdrop'});
$('.table-product-suppliers').DataTable({
  drawCallback: function() {
   $('.state-tags').select2();
   $('.incomp-select2').select2({dropdownCssClass : 'bigdrop'});
   // $('#default_id1').trigger('click');
  },
   processing: true,
  "language": {
  processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  searching: false,
  "lengthChange": false,
  "bPaginate": false,
  "bInfo": false,
  ordering: false,
  scrollX: true,
  scrollCollapse: true,
  serverSide: true,
  ajax: "{!! url('get-product-suppliers-record') !!}"+"/"+prod_detail_id,
  columns: [
      { data: 'action', name: 'action' {{@$hide_pricing_columns}} },
      { data: 'company', name: 'company' },
      { data: 'product_supplier_reference_no', name: 'product_supplier_reference_no' },
      { data: 'supplier_description', name: 'supplier_description' {{@$hide_pricing_columns}} },
      { data: 'import_tax_actual', name: 'import_tax_actual' {{@$hide_pricing_columns}} },
      { data: 'vat_actual', name: 'vat_actual' {{@$hide_pricing_columns}} },
      { data: 'gross_weight', name: 'gross_weight' },
      { data: 'freight', name: 'freight' {{@$hide_pricing_columns}} },
      { data: 'landing', name: 'landing' {{@$hide_pricing_columns}} },
      { data: 'buying_price', name: 'buying_price' {{@$hide_pricing_columns}} },
      { data: 'buying_price_in_thb', name: 'buying_price_in_thb' {{@$hide_pricing_columns}} },
      { data: 'extra_cost', name: 'extra_cost' {{@$hide_pricing_columns}} },
      { data: 'extra_tax', name: 'extra_tax' {{@$hide_pricing_columns}} },
      { data: 'unit_import_tax', name: 'unit_import_tax' {{@$hide_pricing_columns}} },
      { data: 'leading_time', name: 'leading_time' },
      { data: 'supplier_packaging', name: 'supplier_packaging' },
      { data: 'billed_unit', name: 'billed_unit' },
      { data: 'm_o_q', name: 'm_o_q' },
  ],
  initComplete: function () {
        $('#default_id1').trigger('click');
  }
});

window.onload = function(){
  // alert($("#real-data ul").children('li').size() );
  var num = $("#real-data").find("li").length;
  if (num == 1)
  {
    var source = "{!! asset('public/uploads/Product-Image-Coming-Soon.png') !!}";
    var html_string = "<li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image'></a></li><li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image'></a></li><li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image'></a></li>";

    $("#dummy-images").append(html_string);
  }
  else if (num == 2)
  {
    var source = "{!! asset('public/uploads/Product-Image-Coming-Soon.png') !!}";
    var html_string = "<li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image'></a></li><li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image'></a></li>";

    $("#dummy-images").append(html_string);
  }
  else if (num == 3)
  {
    var source = "{!! asset('public/uploads/Product-Image-Coming-Soon.png') !!}";
    var html_string = "<li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image'></a></li>";

    $("#dummy-images").append(html_string);
  }
  else if(num == 4)
  {
    $("#dummy-images").addClass('d-none');
  }
}

// uploading function
var count = "{{$productImagesCount}}";
var num = 4 - count;
uploadHBR.init({
    "target": "#uploads",
    "max": num,
    "textNew": "ADD",
    "textTitle": "Click here or drag to upload an image",
    "textTitleRemove": "Click here to remove the image"
  });

$('#reset').click(function () {
  uploadHBR.reset('#uploads');
});

$(document).on('click', '.img-uploader', function(){
  var pId = $(this).data('id');
  $('.img-product-id').val(pId);
});

// adding product images
$('.add-prodimage-form').on('submit', function(e){
e.preventDefault();
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
});
$.ajax({
    url: "{{ route('add-product-image') }}",
    dataType: 'json',
    type: 'post',
    data: new FormData(this),
    contentType: false,
    cache: false,
    processData:false,
    beforeSend: function(){
      $('.save-btn').html('Please wait...');
      $('.save-btn').addClass('disabled');
      $('.save-btn').attr('disabled', true);
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
    },
    success: function(result){
      $('.save-btn').html('Upload');
      $('.save-btn').attr('disabled', true);
      $('.save-btn').removeAttr('disabled');
      // $('#lo/ $('#loader_modal').modal('hide');
      if(result.success == true){
        toastr.success('Success!', 'Image(s) added successfully',{"positionClass": "toast-bottom-right"});
        uploadHBR.reset('#uploads');
        $('#productImagesModal').modal('hide');
        location.reload();

      }else{
        toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
        uploadHBR.reset('#uploads');
        $('#productImagesModal').modal('hide');
        // location.reload();
        $('#loader_modal').modal('hide');
      }
    },
    error: function (request, status, error) {
        // $('#loader_modal').modal('hide');
        $('.save-btn').html('Upload');
        $('.save-btn').removeClass('disabled');
        $('.save-btn').removeAttr('disabled');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
          $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
          $('input[name="'+key+'[]"]').addClass('is-invalid');
        });
      }
  });
  });

$('.tag1').on('click',function(){
  var imgSrc = $('.tag1').attr('src');
  // alert(imgSrc);
  document.getElementById('main-image').src = imgSrc;
});

$('.tag2').on('click',function(){
  var imgSrc = $('.tag2').attr('src');
  // alert(imgSrc);
  document.getElementById('main-image').src = imgSrc;
});

$('.tag3').on('click',function(){
  var imgSrc = $('.tag3').attr('src');
  // alert(imgSrc);
  document.getElementById('main-image').src = imgSrc;
});

$('.tag4').on('click',function(){
  var imgSrc = $('.tag4').attr('src');
  // alert(imgSrc);
  document.getElementById('main-image').src = imgSrc;
});

$('.dashboardlink').on('click', function(){
  $('ul.photo-gallery').toggleClass('purchase-product-detail');
});

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

$('#brand_modal').hide();

// dropdowns double click editable
$(document).on("change",".selectFocus",function() {
    // alert($('#barcode_type').val());
    if($(this).attr('name') == 'category_id' || $(this).attr('name') == 'supplier_id' )
    {
      var old_value = $(this).parent().parent().find('span').data('fieldvalue');
    }
    else
    {
      var old_value = $(this).prev().data('fieldvalue');
    }
    if($(this).attr('name') == 'primary_category')
    {
      var new_value = $("option:selected", this).html();
      var p_cat_id = $("option:selected", this).val();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val() , old_value);
      $.ajax({
          method: "get",
          url: "{{ url('getting-product-category-childs') }}",
          dataType: 'json',
          context: this,
          data: {p_cat_id:p_cat_id},
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
              $(".category-id").removeClass("d-none");
              $(".category-id").prev().addClass("d-none");
              $(".category-id").focus();
              $(".category-id").html(data.html_string);
            }
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
          });
    }

    else if($(this).attr('name') == 'category_id')
    {
      var thisPointer= $(this);
      swal({
        title: "Are you sure?",
        text: "Are you sure you want to update the Category of this product? This will update its Reference #, and their prices",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Update it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
        },
        function (isConfirm) {
          if(isConfirm)
          {
            var new_value = $("option:selected", thisPointer).html();
            thisPointer.addClass('d-none');
            thisPointer.prev().removeClass('d-none');
            $(this).prev().html(new_value);
            saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
          }
          else
          {
            // swal("Cancelled", "", "error");
            // $('.category_id').val('{{$product->category_id}}').change();
            // $('.inc-fil-cat').addClass('d-none');
            // $('.inc-fil-cat').prev().removeClass('d-none');
            window.location.reload();
          }
        }

      );

    }
    // else if($(this).attr('name') == 'category_id')
    // {
    //   var new_value = $("option:selected", this).html();
    //   var thisPointer=$(this);
    //   thisPointer.addClass('d-none');
    //   thisPointer.prev().removeClass('d-none');
    //   $(this).prev().html(new_value);
    //   saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    // }
    else if($(this).attr('name') == 'buying_unit' || $(this).attr('name') == 'barcode_type' )
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'selling_unit')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'stock_unit')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'ecom_selling_unit')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'supplier_id')
    {
      var prod_id = "{{$product->id}}";
      var new_value = $("option:selected", this).html();
      var add_val_check = $("option:selected", this).val();
      if(add_val_check != null)
      {
        $.ajax({
        method: "get",
        url: "{{ route('check-supp-exist-in-prod-suppliers') }}",
        dataType: 'json',
        context:this,
        data: {add_val_check:add_val_check,prod_id:prod_id},
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
          if(data.check == true)
          {
            var new_value = $("option:selected", this).html();
            var thisPointer = $(this);
            thisPointer.addClass('d-none');
            thisPointer.prev().removeClass('d-none');
            $(this).prev().html(new_value);
            saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
          }
          else
          {
            var new_value = $("option:selected", this).html();
            $('#addSupplierModalDropDownForm')[0].reset();
            $('.addSuppDropDown').val(new_value);
            $('#headingSupplier').html("Supplier Information of "+new_value+"");
            $('.selected_supplier_id').val(add_val_check);
            $("#addSupplierModalDropDown").modal('show');
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
        });

      }

    }
    else if($(this).attr('name') == 'type_id')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'type_id_2')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'type_id_3') {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == 'brand_id')
    {
      var sel_value = $("option:selected", this).val();
      var new_value = $("option:selected", this).html();
      if(sel_value == 'new_brand')
      {
        $('#brand_modal').show();
        return false;
      }
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
    }

    else if($(this).attr('name') == 'supplier_packaging')
    {
      var place = $(this).prev().data('place');
      if(place == "page")
      {
        var id = $("#default_or_last_supplier_id").val();
      }
      else if(place == "listing")
      {
        var id = $(this).parents('tr').attr('id');
      }

      var thisPointer = $(this);

      var sel_value = $("option:selected", this).val();
      var new_value = $("option:selected", this).html();

      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveProdSuppData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
      // if(sel_value == 'new_brand')
      // {
      //   $('#brand_modal').show();
      //   return false;
      // }
      // var thisPointer=$(this);
      // thisPointer.addClass('d-none');
      // thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(new_value);
      // saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    }

});

// adding brands form dropdown
$('.add-brands-form').on('submit', function(e){
e.preventDefault();
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
});
$.ajax({
    url: "{{ route('purchasing-add-brands') }}",
    method: 'post',
    data: $('.add-brands-form').serialize(),
    beforeSend: function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
    },
    success: function(data){
      $('#loader_modal').modal('hide');
      if(data.success == true)
      {
        let new_option = '<option value="'+data.recentAdded.id+'">'+data.recentAdded.title+'</option>';
        $(".prod-brands option:last").before(new_option);
        toastr.success('Success!', 'Brand added successfully',{"positionClass": "toast-bottom-right"});
        $('#brand_modal').hide();
        $('#add-brands-form')[0].reset();
      }
      else
      {
        toastr.error('Error!', data.errormsg,{"positionClass": "toast-bottom-right"});
        $('#brand_modal').hide();
        $('#add-brands-form')[0].reset();
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

// to make that field on its orignal state
// $(document).on("focusout",".fieldFocus",function() {
$(document).on(' keypress keyup focusout ','.fieldFocus', function(e){
  /*alert($(this).attr('id'));*/

  var old_value = $(this).prev().data('fieldvalue');
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    var fieldvalue = $(this).prev().data('fieldvalue');
    var old_value = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

      if($.trim($(this).val()).length > 0 && $.trim($(this).val()).length < 3 && $(this).attr('name') == 'refrence_code')
      {
        toastr.error('Warning!', 'Please Enter Atleast 3 Characters.',{"positionClass": "toast-bottom-right"});
        return false;
      }

    if($(this).attr('name') == 'max_o_qty' || $(this).attr('name') == 'min_o_qty' || $(this).attr('name') == 'selling_unit_conversion_rate' )
{

  if( $(this).val() < 0 )
    {

      swal({
            title: "Alert!",
            text: "Field value should n't be negative",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
          return false;
    }

if( $(this).val()==0 )
    {

      swal({
            title: "Alert!",
            text: "Field value shouldn't be zero",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
          return false;
    }


 }
 //----------------------check over discounted price--------------------
var ecom_p=parseInt($('#ecommerce_price_span').data('fieldvalue'));
var disc_p=parseInt($('#discount_price').val());
if($(this).attr('id')=='discount_price' && disc_p > ecom_p && ecom_p!=null)
{
   swal({
            title: "Alert!",
            text: "Discounted Price shouldn't be greater than the Ecommerce Price",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
  return false;

}
//--------------------------check over ecommerce_price-----------------------
var discount_price=parseInt($('#discount_price_span').data('fieldvalue'));
var ecommerce_price=parseInt($('#ecommerce_price').val());
if($(this).attr('id')=='ecommerce_price' && discount_price > ecommerce_price && discount_price!=null)
{
   swal({
            title: "Alert!",
            text: "Discounted Price shouldn't be greater than the Ecommerce Price",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
  return false;

}

//----------------------check over minimum ordered qunatity-------------------
var max_qty=parseInt($('#max_o_qty').data('fieldvalue'));
if($(this).attr('id')=='min_o_qty_ecomm' && $('#min_o_qty_ecomm').val() >max_qty )
{
   swal({
            title: "Alert!",
            text: "Minimum ordered quantity should be less than the maximum ordered quantity",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
  return false;

}
//----------------------check over maximum ordered qunatity-------------------
var min_qty=parseInt($('#min_o_qty').data('fieldvalue'));
if($(this).attr('id')=='max_o_qty_ecomm' && $('#max_o_qty_ecomm').val() < min_qty )
{
   swal({
            title: "Alert!",
            text: "maximum ordered quantity should be greater than the minimum ordered quantity",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
  return false;

}

//--------------------check over percentage discount---------------------

 if(($(this).attr('id')=='discount' && $('#discount').val()>100)|| ($(this).attr('id')=='discount'&& $('#discount').val()<0))
 {
  swal({
            title: "Alert!",
            text: "Enter valid percentage!",
            type: "info",
            confirmButtonClass: "btn-danger",
            closeOnConfirm: true,
            closeOnCancel: false
          }
          );
          return false;
 }

    if($.trim($(this).val()).length < 1 && $(this).attr('name') !== 'refrence_code')
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
        saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(),old_value);
    }

    }
});


$(document).on("change",".discount_expiration_date_dp",function(e) {
  var old_value = $(this).prev().data('fieldvalue');
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
      thisPointer.addClass('d-none');
      // thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      $(".expiration_date_fp").datepicker('hide');
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
      saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(),old_value);
    }
  }
});


//rarec
function saveProdData(thisPointer,field_name,field_value,old_value){

    console.log(thisPointer+' '+' '+field_name+' '+field_value+' '+old_value);
    var prod_detail_id= "{{$product->id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('save-prod-data-prod-detail-page') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,prod_detail_id:prod_detail_id},
      data: 'prod_detail_id='+prod_detail_id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'old_value'+'='+encodeURIComponent(old_value),
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

        if(data.find == true)
        {
          $('#refrence_code').html(data.prod_code);
          toastr.warning('Warning!', 'Reference Code Already Exists.',{"positionClass": "toast-bottom-right"});
          $("#loader_modal").modal('hide');
          thisPointer.attr('value', data.prod_code);
          thisPointer.val(data.prod_code);
          thisPointer.prev().removeData('fieldvalue');
          thisPointer.prev().data('fieldvalue', new_value);
          return;
        }
        if(field_name == "selling_unit")
        {
          $("#total_buy_unit_cost_price").html(data.buying_p);
          $("#unit_conversion_rate").html(data.unit_cr);
          $("#selling_price").html(data.selling_p);
        }
        if(field_name == "buying_unit")
        {
          $("#total_buy_unit_cost_price").html(data.buying_p);
          $("#unit_conversion_rate").html(data.unit_cr);
          $("#selling_price").html(data.selling_p);
        }
        if(field_name == "import_tax_book")
        {
          $("#total_buy_unit_cost_price").html(data.buying_p);
          $("#unit_conversion_rate").html(data.unit_cr);
          $("#selling_price").html(data.selling_p);
        }
        if(field_name == "supplier_id")
        {
          $('.table-product-suppliers').DataTable().ajax.reload();
          $("#total_buy_unit_cost_price").html(data.buying_p);
          $("#t_b_u_c_p_of_supplier").html(data.buying_p_of_supp);
          $("#unit_conversion_rate").html(data.unit_cr);
          $("#selling_price").html(data.selling_p);
        }
        if(field_name == "unit_conversion_rate")
        {
          $("#total_buy_unit_cost_price").html(data.buying_p);
          $("#unit_conversion_rate").html(data.unit_cr);
          $("#selling_price").html(data.selling_p);
        }
        if(field_name == "selling_unit_conversion_rate")
        {
          $("#ecommr_cogs_price_span").html(data.ecomCogs);
        }
        if(field_name == "category_id")
        {
          setTimeout(function(){
              window.location.reload();
            }, 1000);
        }
        if(data.completed == 1)
        {
          toastr.success('Success!', 'Information updated successfully. Product marked as completed.',{"positionClass": "toast-bottom-right"});
            /*setTimeout(function(){
              window.location.reload();
            }, 2000);*/
        }
        if(field_name == 'refrence_code')
        {
          // alert(data.prod_code);
          $('#refrence_code').html(data.prod_code);
          $("#refrence_code_input").val(data.prod_code);
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }
        else if(data.find == "short_desc")
        {
          toastr.error('Error!', 'Information not updated successfully. Product description already exist.',{"positionClass": "toast-bottom-right"});
          $('#short_desc').text(old_value);
          // $('#txt_area_desc').val(old_value);
          location.reload();
        }
        else
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }

        if(data.reload == 1)
        {
          window.location.reload();
        }

        $('.table-product-history').DataTable().ajax.reload();

      },
      error: function(request, status, error){

        $("#loader_modal").modal('hide');
      }
    });
  }

// to make fields double click editable for fixed price
$(document).on("dblclick",".inputDoubleClickFixedPrice",function(){
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

// to make that field on its orignal state for fixed price
// $(document).on("focusout",".fieldFocusFixedPrice",function() {
$(document).on('keypress keyup focusout', '.fieldFocusFixedPrice', function(e){
  var old_value = $(this).prev().data('fieldvalue');
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');
        // thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        $(".expiration_date_fp").datepicker('hide');
    }
  if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
    // alert($(this).val());
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
        saveProdFixedPriceData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
      }
    }
  }
});

$(document).on("change",".exp_date_fp",function(e) {
  var old_value = $(this).prev().data('fieldvalue');
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
      thisPointer.addClass('d-none');
      // thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      $(".expiration_date_fp").datepicker('hide');
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
      saveProdFixedPriceData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
  }
});


function saveProdFixedPriceData(id,field_name,field_value,old_value){
    var prod_detail_id= "{{$product->id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('edit-prod-fixed-price-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+'prod_detail_id='+prod_detail_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
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
        $('.table-product-history').DataTable().ajax.reload();
        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
          location.reload();
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
}

// to make fields double click editable for product margins
$(document).on("dblclick",".inputDoubleClickPM",function(){
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

// to make that field on its orignal state for product margins
// $(document).on("focusout",".fieldFocusPM",function() {
$(document).on('keypress keyup focusout', '.fieldFocusPM', function(e){

    var old_value = $(this).prev().data('fieldvalue');
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
  if($(this).val().length < 1)
  {
    return false;
  }
  else
  {
    if(fieldvalue == new_value)
    {
      // alert('here');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
    else if($(this).attr('name') == "product_fixed_price")
    {
      var id = $(this).closest('td').attr('id');
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
        $(this).closest('td').next('td').find('span').addClass('inputDoubleClickPM');
        // $(this).closest('tr').next('tr').next('td').find('span').html('--');
      }
      saveProdMarginData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else if($(this).attr('name') == "default_margin")
    {
      var id = $(this).closest('td').attr('id');
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
        // $(this).closest('tr').next('tr').next('td').find('span').html('--');
      }
      saveProdMarginData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    else
    {
      var id = $(this).closest('td').attr('id');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      if(new_value != '')
      {
        $(this).removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
      saveProdMarginData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
    }
  }

});

// to make that field on its orignal state for product margins for dropdown
$(document).on("change",".selectFocusPM",function() {
  var old_value = $(this).prev().data('fieldvalue');
  if($(this).attr('name') == 'default_margin')
  {
    var id = $(this).closest('td').attr('id');
    var new_value = $("option:selected", this).html();
    var thisPointer = $(this);
    thisPointer.addClass('d-none');
    thisPointer.prev().removeClass('d-none');
    $(this).prev().html(new_value);
    saveProdMarginData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
  }
});


$(document).on('click','.expiration_date_null', function(e){
  var old_value = $(this).prev().prev().data('fieldvalue');
  var id = $(this).closest('td').attr('id');
  var thisPointer = $(this);
  var null_value = "";
  var attr_name  = "expiration_date";
  thisPointer.removeData('fieldvalue');
  thisPointer.attr('data-fieldvalue', '');

  thisPointer.prev().prev().removeData('fieldvalue');
  thisPointer.prev().prev().attr('data-fieldvalue', '');

  thisPointer.prev().attr('value', '');
  thisPointer.prev().prev().html('---');

  saveProdMarginData(id,attr_name, null_value, old_value);
});


$(document).on("change",".expiration_date_dp",function(e) {
  var old_value = $(this).prev().data('fieldvalue');
  if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
      thisPointer.addClass('d-none');
      // thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      $(".expiration_date_dp").datepicker('hide');
  }
  if($(this).val().length < 1 && $(this).val().length != 0 )
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
      var id = $(this).closest('td').attr('id');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');

      thisPointer.prev().removeClass('d-none');
      if(new_value != '')
      {
        $(this).prev().removeData('fieldvalue');
        $(this).prev().attr('data-fieldvalue', new_value);

        // below two fields for cancel button
        $(this).next().removeData('fieldvalue');
        $(this).next().attr('data-fieldvalue', new_value);
        // $(this).attr("data-fieldvalue",new_value);

        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
      saveProdMarginData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
    }
  }
});

function saveProdMarginData(id,field_name,field_value , old_value){
    console.log(id+' '+field_name+' '+field_value+' '+old_value);
    var prod_detail_id= "{{$product->id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('edit-prod-margin-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+'prod_detail_id='+prod_detail_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
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
        if(field_name == "product_fixed_price")
        {
          // location.reload();
        }

        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }

        if(data.redirect)
        {
          setTimeout(function(){
            window.location.reload();
          }, 300);
        }

        if(data.updatedRow)
        {

          if(data.updatedRow.customer_type_id == 1)
          {
            // $(".rest-sale-price").html(data.selling_p);
            $(".restaurant").html(data.selling_p+'<br>'+data.last_updated);
          }

          if(data.updatedRow.customer_type_id == 2)
          {
            // $(".hotel-sale-price").html(data.selling_p);
            $(".hotel").html(data.selling_p+'<br>'+data.last_updated);
          }

          if(data.updatedRow.customer_type_id == 3)
          {
            $(".retail").html(data.selling_p+'<br>'+data.last_updated);
          }

          if(data.updatedRow.customer_type_id == 4)
          {
            $(".private").html(data.selling_p+'<br>'+data.last_updated);
          }

          if(data.updatedRow.customer_type_id == 5)
          {
            $(".catering").html(data.selling_p+'<br>'+data.last_updated);
          }

        }

        $('.table-product-history').DataTable().ajax.reload();
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
}

// to make fields double click editable for product supplier
$(document).on("dblclick",".prodSuppInputDoubleClick",function(){
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
      $.fn.dataTable
    .tables( { visible: true, api: true } )
    .columns.adjust();

     }, 300);
});

$(document).on('change', 'select.select-common', function(){
  if($(this).attr('name') == 'supplier_id')
  {
    var old_value = $(this).prev().data('fieldvalue');
    var id = $(this).closest('tr').attr('id');
    var thisPointer = $(this);
    saveProdSuppData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
  }

});

// to make that field on its orignal state for product supplier edit
// $(document).on("focusout",".prodSuppFieldFocus",function() {
$(document).on('keypress keyup focusout', '.prodSuppFieldFocus', function(e){
    var old_value = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
      if($(this).val().length < 1)
      {
        return false;
      }
      else
      {
        var fieldvalue = $(this).prev().html();
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
            $(this).removeData('fieldvalue');
            $(this).prev().data('fieldvalue', new_value);
            $(this).attr('value', new_value);
            $(this).prev().html(new_value);
          }
          saveProdSuppData(id,thisPointer.attr('name'), thisPointer.val(), old_value);
        }
      }
    }

});

// to make fields double click editable for product supplier first record
$(document).on("dblclick",".inputDoubleClickFirst",function(){
  if($(this).attr('id') == "supplier_id")
  {
    var type = $(this).attr('id');
    var prod_id= "{{$product->id}}";

    $.ajax({
    type: "get",
    url: "{{ route('get-supplier-dropdowns') }}",
    content: this,
    data: 'type='+type+'&prod_id='+prod_id,
    beforeSend: function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
     $("#loader_modal").modal('show');
    },
    success: function(response){
      $("#loader_modal").modal('hide');
      if(response.success == true)
      {
        $('.supplier_id').html(response.html);
      }
      $('.incomplete-filter-def-supp').prev().addClass('d-none');
      $('.incomplete-filter-def-supp').removeClass('d-none');
      $('.incomplete-filter-def-supp').addClass('active');
      $('.incomplete-filter-def-supp').focus();
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
    });
  }
  else
  {
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
  }
});

// to make that field on its orignal state for product supplier edit first record
// $(document).on("focusout",".fieldFocusFirst",function() {
$(document).on('keypress keyup focusout', '.fieldFocusFirst', function(e){

  var old_value = $(this).prev().data('fieldvalue');
  if (e.keyCode === 27 && $(this).hasClass('active'))
  {
    var fieldvalue = $(this).prev().data('fieldvalue');
    var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
  }

  if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

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
        thisPointer.prev().removeClass('d-none');
        $(this).prev().html(fieldvalue);
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var id = $("#default_or_last_supplier_id").val();
        var thisPointer = $(this);
        thisPointer.removeClass('active');
        thisPointer.addClass('d-none');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).removeData('fieldvalue');
          if($(this).attr('name') == 'product_supplier_reference_no')
          {

          }
          else if ($(this).attr('name') == 'supplier_description')
          {

          }
          else
          {
            var v = parseFloat(new_value);
            new_value = (isNaN(v)) ? '' : v.toFixed(3);
          }

          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          // alert(new_value);
          $(this).prev().html(new_value);
          saveProdSuppData(id,thisPointer.attr('name'), thisPointer.val() , old_value);
        }
      }
    }
  }
});

function saveProdSuppData(id,field_name,field_value,old_value){
    var prod_detail_id= "{{$product->id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('edit-prod-supp-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+'prod_detail_id='+prod_detail_id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'old_value'+'='+encodeURIComponent(old_value),
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(data)
      {
        // $('.table-product-history').DataTable().ajax.reload();

        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
          $('.table-product-suppliers').DataTable().ajax.reload();
          if(data.buying_p != null)
          {
            $("#total_buy_unit_cost_price").html(data.buying_p);
            $("#t_b_u_c_p_of_supplier").html(data.buying_p_of_supp);
            $("#selling_price").html(data.selling_p);
          }
          if(data.reload == 1)
          {
            window.location.reload();
          }
          $("#loader_modal").modal('hide');
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }

$(document).on('mousedown',function(e) {
  if( (e.which == 1) || (e.which == 2) || (e.which == 3) )
  {
    if($('.def-last-supp').hasClass('d-none'))
    {
      $('.def-last-supp').removeClass('d-none');
      $('.def-last-supp').next().addClass('d-none');
    }
  }
});

$(document).on('keyup', function(e) {

  if (e.keyCode === 27){ // esc
    $(".expiration_date_fp").datepicker('hide');
    $(".expiration_date_dp").datepicker('hide');
    $(".expiration_date_sc").datepicker('hide');

    if($('.selectDoubleClick').hasClass('d-none')){
      $('.selectDoubleClick').removeClass('d-none');
      $('.selectDoubleClick').next().addClass('d-none');
    }
    if($('.inputDoubleClick').hasClass('d-none')){
      $('.inputDoubleClick').removeClass('d-none');
      $('.inputDoubleClick').next().addClass('d-none');
    }
    if($('.prodSuppInputDoubleClick').hasClass('d-none')){
      $('.prodSuppInputDoubleClick').removeClass('d-none');
      $('.prodSuppInputDoubleClick').next().addClass('d-none');
    }
    if($('.inputDoubleClickFirst').hasClass('d-none')){
      $('.inputDoubleClickFirst').removeClass('d-none');
      $('.inputDoubleClickFirst').next().addClass('d-none');
    }
    if($('.inputDoubleClickPM').hasClass('d-none')){
      $('.inputDoubleClickPM').removeClass('d-none');
      $('.inputDoubleClickPM').next().addClass('d-none');
    }
    if($('.inputDoubleClickFixedPrice').hasClass('d-none')){
      $('.inputDoubleClickFixedPrice').removeClass('d-none');
      $('.inputDoubleClickFixedPrice').next().addClass('d-none');
    }
  }
});

function numberWithCommas(number)
{
  var parts = number.toString().split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.join(".");
}

// add supplier product modal from dropdown
$(document).on('click', '#addSupplierBtnDropDown', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ route('add-product-suppliers-dropdown') }}",
        method: 'post',
        data: $('#addSupplierModalDropDownForm').serialize(),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
         $("#loader_modal").modal('show');
        },
        success: function(data){
          $("#loader_modal").modal('hide');
          if(data.success === true){
            // let new_option = '<option value="'+data.supplier.id+'">'+data.supplier.company+'</option>';
            // $(".getDataOfProductSupplier option:last").before(new_option);
            toastr.success('Success!', 'Product supplier added successfully',{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
              window.location.reload();
            }, 1500);
            $('#addSupplierModalDropDownForm')[0].reset();
            $('.addSupplierModalDropDown').modal('hide');

            $("#default_or_last_supplier_id").val(data.supplier.id);

            if(data.supplier.product_supplier_reference_no != null)
            {
              $("#product_supplier_reference_no").html(data.supplier.product_supplier_reference_no);
              $('input[name=product_supplier_reference_no]').val(data.supplier.product_supplier_reference_no);
            }
            else
            {
              $("#product_supplier_reference_no").html("--");
              $('input[name=product_supplier_reference_no]').val("--");
            }

            if(data.supplier.import_tax_actual != null)
            {
              $("#import_tax_actual").html(data.supplier.import_tax_actual);
              $('input[name=import_tax_actual]').val(data.supplier.import_tax_actual);
            }
            else
            {
              $("#import_tax_actual").html("--");
              $('input[name=import_tax_actual]').val("--");
            }

            $("#supplier_description").html(data.supplier.supplier_description ? data.supplier.supplier_description : "--");
            $('input[name=supplier_description]').val(data.supplier.supplier_description ? data.supplier.supplier_description : "");
            $("#supplier_country").html(data.supplier.getcountry ? data.supplier.getcountry.name : "--");


            $("#extra_cost").html(data.supplier.extra_cost ? data.supplier.extra_cost : "--");
            $('input[name=extra_cost]').val(data.supplier.extra_cost ? data.supplier.extra_cost : "");

            $("#extra_tax").html(data.supplier.extra_tax ? data.supplier.extra_tax : "--");
            $('input[name=extra_tax]').val(data.supplier.extra_tax ? data.supplier.extra_tax : "");

            $("#supplier_packaging").html(data.supplier.supplier_packaging ? data.supplier.supplier_packaging : "--");
            $('input[name=supplier_packaging]').val(data.supplier.supplier_packaging ? data.supplier.supplier_packaging : "");

            $("#billed_unit").html(data.supplier.billed_unit ? data.supplier.billed_unit : "--");
            $('input[name=billed_unit]').val(data.supplier.billed_unit ? data.supplier.billed_unit : "");

            $("#buying_price").html(data.supplier.buying_price ? data.supplier.buying_price : "--");
            $('input[name=buying_price]').val(data.supplier.buying_price ? data.supplier.buying_price : "");

            $("#buying_price_in_thb").html(data.supplier.buying_price_in_thb ? data.supplier.buying_price_in_thb : "--");

            $("#freight").html(data.supplier.freight ? data.supplier.freight : "--");
            $('input[name=freight]').val(data.supplier.freight ? data.supplier.freight : "");

            $("#landing").html(data.supplier.landing ? data.supplier.landing : "--");
            $('input[name=landing]').val(data.supplier.landing ? data.supplier.landing : "");

            $("#leading_time").html(data.supplier.leading_time ? data.supplier.leading_time : "--");
            $('input[name=leading_time]').val(data.supplier.leading_time ? data.supplier.leading_time : "");

            $("#m_o_q").html(data.supplier.m_o_q ? data.supplier.m_o_q : "--");
            $('input[name=m_o_q]').val(data.supplier.m_o_q ? data.supplier.m_o_q : "");

          }


        },
        error: function (request, status, error) {
              $('.save-prod-sup-drop-down').val('add');
              $('.save-prod-sup-drop-down').removeClass('disabled');
              $('.save-prod-sup-drop-down').removeAttr('disabled');
              $('.form-control').removeClass('is-invalid');
              // $('.form-control').next().remove();
              json = $.parseJSON(request.responseText);
              $.each(json.errors, function(key, value){
                  $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                   $('input[name="'+key+'"]').addClass('is-invalid');
              });
          }
      });
  });

// $(document).on('click', '#addSupplierBtn', function(e){
$(document).on('click', '.add-supplier', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
    url: "{{ route('add-product-suppliers') }}",
    method: 'post',
    data: $('#addSupplierForm').serialize(),
    beforeSend: function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
    },
    success: function(result){
      $("#loader_modal").modal('hide');
      if(result.success == true && result.reload == 0)
      {
        toastr.success('Success!', 'Product supplier added successfully',{"positionClass": "toast-bottom-right"});
        $('#addSupplierForm')[0].reset();
        $('.addSupplierModal').modal('hide');
        $('.table-product-suppliers').DataTable().ajax.reload();
      }
      else if(result.success == true && result.reload == 1)
      {
        toastr.success('Success!', 'Product supplier added successfully',{"positionClass": "toast-bottom-right"});
        setTimeout(function(){
          location.reload();
        }, 1000);
      }
    },
    error: function (request, status, error) {
      $("#loader_modal").modal('hide');
      json = $.parseJSON(request.responseText);
      $.each(json.errors, function(key, value){
        $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
        $('input[name="'+key+'"]').addClass('is-invalid');
        $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
        $('select[name="'+key+'"]').addClass('is-invalid');
      });
      }
    });
  });

$(document).on('click', '#addProdMargBtn', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ route('add-product-margins') }}",
      method: 'post',
      data: $('#addProdMarginsForm').serialize(),
      beforeSend: function(){
        $('.save-prod-marg').val('Please wait...');
        $('.save-prod-marg').addClass('disabled');
        $('.save-prod-marg').attr('disabled', true);
      },
      success: function(result){
        $('.save-prod-marg').val('add');
        $('.save-prod-marg').attr('disabled', true);
        $('.save-prod-marg').removeAttr('disabled');
        if(result.success === true){
          toastr.success('Success!', 'Product margin added successfully',{"positionClass": "toast-bottom-right"});
          $('#addProdMarginsForm')[0].reset();
          $('.addProdMarginsModal').modal('hide');
          window.location.reload();
        }


      },
      error: function (request, status, error) {
            $('.save-prod-sup').val('add');
            $('.save-prod-sup').removeClass('disabled');
            $('.save-prod-sup').removeAttr('disabled');
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

// customer based product fixed prices
$(document).on('submit', '#addProdFixedPriceForm', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ route('add-product-fixed-price') }}",
        method: 'post',
        data: $('#addProdFixedPriceForm').serialize(),
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
            toastr.success('Success!', 'Product Fixed Price Customer Wise added successfully',{"positionClass": "toast-bottom-right"});
            $('#addProdFixedPriceForm')[0].reset();
            $('.addProdFixedPriceModal').modal('hide');
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

$(document).on('click', '#delete_sup', function(e){

  var prodSupId = $(this).data('prodisupid');
  var pordId = $(this).data('prodid');
  var rowId = $(this).data('rowid');

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });

  swal({
    title: "Are you sure?",
    text: "You want to Delete!!!",
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
            data:'pordId='+pordId+'&prodSupId='+prodSupId+'&rowId='+rowId,
            url: "{{ route('delete-prod-supplier') }}",
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
                toastr.success('Success!', response.msg ,{"positionClass": "toast-bottom-right"});
                $('.table-product-suppliers').DataTable().ajax.reload();
              } else {
                toastr.error('Error!', response.msg ,{"positionClass": "toast-bottom-right"});
                $('.table-product-suppliers').DataTable().ajax.reload();
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

// get supplier data according to onchange function
$(document).on("change",".getDataOfProductSupplier",function(){
  if($(this).attr('name') == 'supplier_id')
    {
      var thisPointer = $(this).val();
      showSupplierRecord(thisPointer);
    }
});

// show add suppliers of product onclick function
$(document).on("click",".add-supplier11", function(){
  var prod_id  = "{{ $id }}";
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('get-product-suppliers-exist') }}",
      method: 'post',
      dataType: 'json',
      data: {prod_id:prod_id},
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
          $('#addSupplierForm')[0].reset();
          $('.product_suplliers').html(result.html_string);
          $("#addSupplierModal").modal("show");
        }

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

});

// show customers onclick function
$(document).on("click",".add-cust-fp", function(){
  var prod_id  = "{{ $id }}";

  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('get-customer-fixed-price-data') }}",
      method: 'post',
      dataType: 'json',
      data: {prod_id:prod_id},
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
          $('#addProdFixedPriceForm')[0].reset();
          $('.prod-fixed-cust').html(result.html_string);
          $("#addProdFixedPriceModal").modal("show");
        }

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

});

function showSupplierRecord(supplier_id){
var product_id= "{{$product->id}}";

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
});

$.ajax({
  method: "get",
  url: "{{ url('show-single-supplier-record') }}",
  dataType: 'json',
  data: {supplier_id:supplier_id,product_id:product_id},
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
    console.log(data);
    if(data.error == false)
    {
      $("#default_or_last_supplier_id").val(data.prodSuppliers.id);

      if(data.prodSuppliers.product_supplier_reference_no != null)
      {
        $("#product_supplier_reference_no").html(data.prodSuppliers.product_supplier_reference_no);
        $('input[name=product_supplier_reference_no]').val(data.prodSuppliers.product_supplier_reference_no);
      }
      else
      {
        $("#product_supplier_reference_no").html("--");
        $('input[name=product_supplier_reference_no]').val("--");
      }

      if(data.prodSuppliers.import_tax_actual != null)
      {
        $("#import_tax_actual").html(data.prodSuppliers.import_tax_actual);
        $('input[name=import_tax_actual]').val(data.prodSuppliers.import_tax_actual);
      }
      else
      {
        $("#import_tax_actual").html("--");
        $('input[name=import_tax_actual]').val("--");
      }

      $("#supplier_description").html(data.prodSuppliers.supplier_description ? data.prodSuppliers.supplier_description : "--");
      $('input[name=supplier_description]').val(data.prodSuppliers.supplier_description ? data.prodSuppliers.supplier_description : "");

      $("#extra_cost").html(data.prodSuppliers.extra_cost ? data.prodSuppliers.extra_cost : "--");
      $('input[name=extra_cost]').val(data.prodSuppliers.extra_cost ? data.prodSuppliers.extra_cost : "");

      $("#extra_tax").html(data.prodSuppliers.extra_tax ? data.prodSuppliers.extra_tax : "--");
      $('input[name=extra_tax]').val(data.prodSuppliers.extra_tax ? data.prodSuppliers.extra_tax : "");

      $("#supplier_packaging").html(data.prodSuppliers.supplier_packaging ? data.prodSuppliers.supplier_packaging : "--");
      $('input[name=supplier_packaging]').val(data.prodSuppliers.supplier_packaging ? data.prodSuppliers.supplier_packaging : "");

      $("#billed_unit").html(data.prodSuppliers.billed_unit ? data.prodSuppliers.billed_unit : "--");
      $('input[name=billed_unit]').val(data.prodSuppliers.billed_unit ? data.prodSuppliers.billed_unit : "");

      $("#buying_price").html(data.prodSuppliers.buying_price ? data.prodSuppliers.buying_price : "--");
      $('input[name=buying_price]').val(data.prodSuppliers.buying_price ? data.prodSuppliers.buying_price : "");

      $("#buying_price_in_thb").html(data.prodSuppliers.buying_price_in_thb ? data.prodSuppliers.buying_price_in_thb : "--");

      $("#freight").html(data.prodSuppliers.freight ? data.prodSuppliers.freight : "--");
      $('input[name=freight]').val(data.prodSuppliers.freight ? data.prodSuppliers.freight : "");

      $("#landing").html(data.prodSuppliers.landing ? data.prodSuppliers.landing : "--");
      $('input[name=landing]').val(data.prodSuppliers.landing ? data.prodSuppliers.landing : "");

      $("#leading_time").html(data.prodSuppliers.leading_time ? data.prodSuppliers.leading_time : "--");
      $('input[name=leading_time]').val(data.prodSuppliers.leading_time ? data.prodSuppliers.leading_time : "");

      $("#m_o_q").html(data.prodSuppliers.m_o_q ? data.prodSuppliers.m_o_q : "--");
      $('input[name=m_o_q]').val(data.prodSuppliers.m_o_q ? data.prodSuppliers.m_o_q : "");
    }
  },
  error: function(request, status, error){
    $("#loader_modal").modal('hide');
  }
});
}

$(document).on('click', '.delete-prod-img-btn', function(e){

  var img_id = $(this).data('img_id');
  var prod_id = $(this).data('prod_id');
      swal({
        title: "Are you sure?",
        text: "You want to Delete!!!",
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
                data: {img_id:img_id,prod_id:prod_id},
                url: "{{ route('delete-prod-img-from-detail') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                },
                success: function(response){
                  $("#loader_modal").modal('hide');
                  // if(response.success === true){
                    // toastr.success('Success!', 'Removed Successfully.',{"positionClass": "toast-bottom-right"});
                    // window.location.reload();
                    if(response.search('done') !== -1){
                      myArray = new Array();
                      myArray = response.split('-SEPARATOR-');
                      let i_id = myArray[1];
                      $('#prod-image-'+i_id).remove();
                      toastr.success('Success!', 'Image deleted successfully.' ,{"positionClass": "toast-bottom-right"});
                    }
                    if(response.search('no_img') !== -1)
                    {
                      location.reload();
                    }
                  // }
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

$(document).on('click', '.delete_cust', function(e){

  var id = $(this).data('row_id');
      swal({
        title: "Are you sure?",
        text: "You want to Delete!!!",
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
                url: "{{ route('delete-prod-fixed-price-row') }}",
                success: function(response){
                  if(response.success === true){
                    toastr.success('Success!', 'Removed Successfully.',{"positionClass": "toast-bottom-right"});
                    window.location.reload();
                  }
                },
                error: function(request, status, error){

                }
              });
            }
            else {
                swal("Cancelled", "", "error");
            }
        });
  });

$('.fixed_price_check').click(function(){
  var product_id  = "{{@$product->id}}";
  var thisPointer = $(this);
  var old_value   = "";
  var checkbox    = "";
  var opt_val     = "";

  if($(this).prop("checked") == true)
  {
    checkbox = 1;
  }
  else if($(this).prop("checked") == false)
  {
    checkbox = 0;
  }

  if(checkbox == 0)
  {
    opt_val = "Disabled";
  }
  else
  {
    opt_val = "Enabled";
  }

  swal({
      title: "Are you sure?",
      text: "You want to "+opt_val+" Fixed Prices check!!!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, Change it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function (isConfirm) {
      if (isConfirm)
      {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"post",
          dataType: 'json',
          data:{ product_id:product_id, checkbox:checkbox },
          url:"{{ route('update-fixed-prices-check') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(response)
          {
            if(response.success == true)
            {
              $("#loader_modal").modal('hide');
              toastr.success('Success!', opt_val+' Successfully.',{"positionClass": "toast-bottom-right"});
            }
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
        });
      }
      else
      {
        if(thisPointer.prop("checked") == true)
        {
          thisPointer.prop("checked", false);
        }
        else if(thisPointer.prop("checked") == false)
        {
          thisPointer.prop("checked", true);
        }
        swal("Cancelled", "", "error");
      }
    }
  );
});

$('.market_price_check').click(function(){
  var thisPointer = $(this);
  var old_value = "";
  if($(this).prop("checked") == true)
  {
    var checkbox = 1;
  }
  else if($(this).prop("checked") == false)
  {
    var checkbox = 0;
  }
  swal({
      title: "Are you sure?",
      text: "You want to change Market Price!!!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, Change it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function (isConfirm) {
      if (isConfirm)
      {
        saveProdData(thisPointer,thisPointer.attr('name'), thisPointer.val(), old_value);
      }
      else
      {
        if(thisPointer.prop("checked") == true)
        {
          thisPointer.prop("checked", false);
        }
        else if(thisPointer.prop("checked") == false)
        {
          thisPointer.prop("checked", true);
        }
        swal("Cancelled", "", "error");
      }
    }
  );
});



//get stock card data
let wp = '';
$('.click-nav').on('click',function(e){
var warehouse_id = $(this).data('id');
wp = $(this).data('wp');
// alert('clicked');
  $.ajax({
    method:"get",
    data:{ warehouse_id:warehouse_id, product_id:prod_detail_id, wp:wp },
    url:"{{ route('get-html-of-stock-data') }}",
    beforeSend:function(){
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $("#loader_modal").modal('show');
    },
    success:function(data){
      // $("#loader_modal").modal('hide');
      // alert(data.success);

      if(data.success == true)
      {
        $('#id'+data.wp).html(data.html);

        $(".expiration_date_sc").datepicker({
          format: "dd/mm/yyyy",
          autoHide: true,
        });
        // setTimeout(() => {
        //     recusrsiveCallForStockCardJob();
        // }, 5000);
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });

});
$(document).on('click','.show_card_detail',function(){
var id = $(this).data('id');
  $.ajax({
    method:"get",
    data:{ id:id },
    url:"{{ route('get-html-of-stock-data-card') }}",
    beforeSend:function(){
    },
    success:function(data){
      if(data.success == true)
      {
        $('#stock-detail-table-body'+id).html(data.html);

        $(".expiration_date_sc").datepicker({
          format: "dd/mm/yyyy",
          autoHide: true,
        });
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });

});

function recusrsiveCallForStockCardJob() {
    $.ajax({
    method:"get",
    url:"{{ route('recursive_call_for_stock_card_job') }}",
    success:function(data){
      if (data.status == 1) {
        setTimeout(() => {
            recusrsiveCallForStockCardJob();
        }, 5000);
      }
      else if (data.status == 2){
      }
      else{
        $('#id'+wp).html(data.html);

        $(".expiration_date_sc").datepicker({
          format: "dd/mm/yyyy",
          autoHide: true,
        });
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });
}

$('.check_mkt').on('click',function(){
  var id = $(this).data('id');

  $.ajax({
    method:"get",
    dataType: 'json',
    data:{ id:id },
    url:"{{ route('check-mkt-status') }}",
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
    },
    success:function()
    {
      $("#loader_modal").modal('hide');
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });

});

$('.prod-fixed-cust').on('change',function(){
  var id = $(this).val();
  var product_id= "{{$product->id}}";

  $.ajax({
    method:"get",
    dataType: 'json',
    data:{ id:id, product_id:product_id },
    url:"{{ route('get-sale-price-for-selected-customer') }}",
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
    },
    success:function(data)
    {
      $("#loader_modal").modal('hide');
      if(data.success == true)
      {
        $("#product_default_price").val(data.value);
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });

});

$('.update_prices').on('click',function(){
  var product_id= "{{$product->id}}";
  swal({
    title: "Are you sure?",
    text: "You want to Update the price of this product!!!",
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
        data:'product_id='+product_id,
        url: "{{ route('update-single-product-price') }}",
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(response){
          $("#loader_modal").modal('hide');
          if(response.success === true)
          {
            toastr.success('Success!', 'Prices Updated Successfully.',{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
              location.reload();
            }, 500);
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }
    else
    {
      swal("Cancelled", "", "error");
    }
  });

});

var product_id = "{{@$product->id}}";

$('.table-product-history').DataTable({
  "sPaginationType": "listbox",
  processing: true,
  "language": {
  processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:false,
  "lengthChange": true,
  serverSide: true,
  "scrollX": true,
  "bPaginate": true,
  "bInfo":false,
  lengthMenu: [ 5, 10, 20, 40],
  "columnDefs": [
    { className: "dt-body-left", "targets": [] },
    { className: "dt-body-right", "targets": [] },
  ],
         ajax: {
            url:"{!! route('get-product-history') !!}",
            data: function(data) { data.product_id = product_id } ,
            },
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'user_name', name: 'user_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'updated_from', name: 'updated_from' },
            { data: 'column_name', name: 'column_name' },
            { data: 'old_value', name: 'old_value' },
            { data: 'new_value', name: 'new_value' },

              ],
              initComplete: function () {
              // Enable THEAD scroll bars
              $('.dataTables_scrollHead').css('overflow', 'auto');

              // Sync THEAD scrolling with TBODY
              $('.dataTables_scrollHead').on('scroll', function () {
                  $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
              });

      }
    });
$("#featured_product").change(function(e){
/*var featured_product=$(this).val();
var ecommerce_enabled=$('#ecommerce_enabled').val();*/
var ecommerce_enabled = '';
            if($('#ecommerce_enabled').is(':checked'))
            {
              ecommerce_enabled = 1;
            }
            else
            {
              ecommerce_enabled = 0;
            }
 var featured_product = '';
            if($('#featured_product').is(':checked') && $('#ecommerce_enabled').is(':checked'))
            {
              featured_product = 1;
            }
            else if($('#ecommerce_enabled').is(':checked'))
            {
              featured_product = 0;
            }
            else{
            swal("Cancelled", "Enable the product over Ecommerce first", "error");
            if( $('#featured_product').is(':checked') )
            {   $( "#featured_product" ).prop( "checked", false );
            }
          else{
                $( "#featured_product" ).prop( "checked", true );
            }
              return ;
            }

          swal(
          {
          title: "Alert!",
          text: "Do you wish the change?",
          type: "info",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "Yes!",
          cancelButtonText: "No!",
          closeOnConfirm: true,
          closeOnCancel: false
        }
        ,
         function(isConfirm)
         {
           if (isConfirm)
          {

            $.ajax({
            url:'{{route("check-ecommerce-enabled")}}',
            method:"GET",
            data:'product_id='+product_id+'&ecommerce_enabled='+ecommerce_enabled+'&featured_product='+featured_product,
            success: function(data)
            {
              if(data.success==true)
              {
                  toastr.success('Success!', 'Ecommerce Status Updated Successfully.',{"positionClass":"toast-bottom-right"});
              }
            }

            });
          }else{
            swal("Cancelled", "", "error");
            return;
          }
        }    );


});

$("#ecommerce_enabled").change(function(e){
  var field_name=" ";
  // var featured_product=$("#featured_product").val();
  /*if($("#max_o_qty_ecomm").val()==="" )
  {
    field_name=field_name+" "+"Max Quantity";
  }
   if($("#min_o_qty_ecomm").val()==="")
  {
    field_name=field_name+" "+"Min Quantity";
  }
   if ($("#long_desc_ecomm").val()==="")
   {
    field_name=field_name+" "+"Long Description";
    }

  if($("#long_desc_ecomm").val()==="" || $("#min_o_qty_ecomm").val()==="" || $("#max_o_qty_ecomm").val()==="")
    {

    swal(
    {
              title: "Alert!",
              text: field_name+" is / are empty",
              type:  "info",
              confirmButtonClass: "btn-danger",
              closeOnConfirm: true,
              closeOnCancel: false
            }
            );

    $( "#ecommerce_enabled" ).prop( "checked", false );
    return ;
    }*/

  var ecommerce_enabled = '';
            if($('#ecommerce_enabled').is(':checked'))
            {
              ecommerce_enabled = 1;


            }
            else
            {
              ecommerce_enabled = 0;
            }
   var featured_product = '';
            if($('#featured_product').is(':checked')&& $('#ecommerce_enabled').is(':checked'))
            {
              featured_product = 1;
            }
            else
            {
              featured_product = 0;
            }

  swal(
  {
          title: "Alert!",
          text: "Are you sure you want Enable/Disable it for Ecommerce?",
          type: "info",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "Yes!",
          cancelButtonText: "No!",
          closeOnConfirm: true,
          closeOnCancel: false
        }
        ,
         function(isConfirm)
         {
           if (isConfirm)
          {
            $("#min_o_qty_ecomm").val(1);
            $("#discount_price").val(0);
           var MinOrder= $("#min_o_qty_ecomm").val();
             var discount= $("#discount_price").val();
            $.ajax({
            url:'{{route("check-ecommerce-enabled")}}',
            method:"GET",
            data:'product_id='+product_id+'&ecommerce_enabled='+ecommerce_enabled+'&featured_product='+featured_product+'&discount='+discount+'&MinOrder='+MinOrder,
            success: function(data)
            {
              if(data.success==true)
              {
                  toastr.success('Success!', 'Ecommerce Status Updated Successfully.',{"positionClass":"toast-bottom-right"});
              }
            }
            });
            }
          else
            {
               swal("Cancelled", "", "error");

              if(ecommerce_enabled==0)
               $( "#ecommerce_enabled" ).prop( "checked", true );
             else
               $( "#ecommerce_enabled" ).prop( "checked", false );
            }
            }
            );
  });







    $(document).on('keypress keyup focusout',".fieldFocusCost",function(e) {
    // alert('hi');
    var id = $(this).data('id');
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
          saveStockCost(thisPointer,thisPointer.attr('name'), thisPointer.val(),fieldvalue,id);
        }
      }
    }

  });

      function saveStockCost(thisPointer,field_name,field_value,new_select_value,id){

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",

        url: "{{ route('update-stock-card-cogs') }}",
        dataType: 'json',
        data: 'id='+id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'new_select_value'+'='+encodeURIComponent(new_select_value),
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
            return true;
          }

        },

      });
    }

  var resize = $('#upload-demo').croppie({
    enableExif: true,
    enableOrientation: true,
    viewport: { // Default { width: 100, height: 100, type: 'square' }
        width: 400,
        height: 400,
        type: 'square' //square
    },
    boundary: {
        width: 500,
        height: 500
    },
    enforceBoundary: false
});

$('#image').on('change', function () {
  var reader = new FileReader();
    reader.onload = function (e) {
      // alert(e.target.result);
      resize.croppie('bind',{
        url: e.target.result
      }).then(function(){
       $('.cr-slider').attr({'min':0.1, 'max':5});
      });
    }
    reader.readAsDataURL(this.files[0]);
    var selectedFile = this.files[0];
    var idxDot = selectedFile.name.lastIndexOf(".") + 1;
    var extFile = selectedFile.name.substr(idxDot, selectedFile.name.length).toLowerCase();
    if (extFile == "jpg" || extFile == "jpeg" || extFile == "png" || extFile == "svg" || extFile == "gif") {
       //do whatever want to do
    } else {
         // alert("Only jpg/jpeg, png, gif and svg files are allowed!");
          $('#image').val('');
          toastr.error('Sorry!', 'Upload Only Image File !!!',{"positionClass": "toast-bottom-right"});
          return false;

    }
});


$('.upload-image').on('click', function (ev) {
   var check_image = $('#image').val();
  if(check_image == '' || check_image == null)
  {
    toastr.error('Sorry!', 'Please select image first !!!',{"positionClass": "toast-bottom-right"});
    return false;
  }
  var id = $('.product_id_for_cropping').val();
  resize.croppie('result', {
    type: 'canvas',
    size: 'viewport'
  }).then(function (img) {
    $.ajax({
      url: "{{route('crop-image')}}",
      type: "POST",
      data: {"image":img,id:id},
      beforeSend: function(){
        // $('#loader_modal').modal({
        //   backdrop: 'static',
        //   keyboard: false
        // });
        // $("#loader_modal").modal('show');
        $('.upload-image').addClass('disabled');
        $('.upload-image').html('<i class="fa fa-circle-o-notch fa-spin"></i> Please Wait To Upload The Image');
        $('.upload-image').attr('disabled', true);
      },
      success: function (data) {
        if(data.status == true)
        {
          toastr.success('Success!', 'Image Uploaded Successfully !!!',{"positionClass": "toast-bottom-right"});
          location.reload();
        }
        if(data.completed == true)
        {
          toastr.info('Sorry!', 'Maximum 4 images can be uploaded per product !!!',{"positionClass": "toast-bottom-right"});
        }
        // html = '<img src="' + img + '" />';
        // $("#preview-crop-image").html(html);
      }
    });
  });
});

  $(document).on('click','.is_enable',function(){
    var img = $(this).data('id');
    var id = $(this).data('product');
    // alert('image id is '+img+' product id is '+id);

    $.ajax({
      url: "{{route('set-default-image-ecom')}}",
      type: "POST",
      data: {"image":img,id:id},
      beforeSend: function(){
        // $('#loader_modal').modal({
        //   backdrop: 'static',
        //   keyboard: false
        // });
        // $("#loader_modal").modal('show');
      },
      success: function (data) {
        if(data.success == true)
        {
          toastr.success('Success!', 'Information Updated Successfully !!!',{"positionClass": "toast-bottom-right"});
        }
      }
    });
  })
});

// on default supplier add supplier click
$( "#new_supplier_span" ).dblclick(function() {
  $(".add_new_supplier").removeClass("d-none");
  $("#new_supplier_span").addClass("d-none")
});
// inserting new supplier in product and supplier_products

$( "#new_supplier_id" ).change(function() {
    let supplier_id=$("#new_supplier_id").val();
    let product_id=$("input[name=product_id]").val();
    $.ajax({
      url: "{{route('setDefaultSupplier')}}",
      type: "POST",
      dataType:'json',
      data: {supplier_id:supplier_id,product_id:product_id},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
    },
    success: function (data) {
        $("#loader_modal").modal('hide');
        if(data.success == true)
        {
            location.reload();
            toastr.success('Success!', 'Information Updated Successfully !!!',{"positionClass": "toast-bottom-right"});
        }else{
            toastr.success('error!', 'Information Updation Failed !!!',{"positionClass": "toast-bottom-right"});
        }
      }
    });
});

//show barcode function
$(document).on('click','.barcode_preview_btn',function(e){
  $('.barcode_row').toggleClass('d-none');
  $('.show_barcode').toggleClass('d-none');
  $('.hide_barcode').toggleClass('d-none');
});



</script>
@stop
