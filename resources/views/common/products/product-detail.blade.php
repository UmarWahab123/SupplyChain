@extends($layout.'.layouts.layout')
@section('title','Products Management | Supply Chain')


@section('content')
<?php
use App\Models\Common\ProductCategory;
use App\Models\Common\Unit;
use Carbon\Carbon;
?>
<style type="text/css">
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
</style>

{{-- Content Start from here --}}

<div class="row mb-0">
  <div class="col-lg-4 col-md-6">
    <h5 class="fontbold headings-color mb-0">Product Detail Page</h5>
    <p>Note: The ITALIC text is double click editable.</p>
  </div>
<div class="col-lg-8 col-md-6 title-col p-0">
  <a class="btn text-uppercase purch-btn mr-3 headings-color btn-color pull-right" onclick="history.go(-1)">Back</a>
  <h5 class="maintitle text-uppercase fontbold"></h5>
</div>
</div>

{{--<input type="button" class="tn recived-button check_mkt" name="check_mkt" id="check_mkt" data-id="{{$product->id}}" value="Check MKT">--}}

<!-- new design starts here -->

<!-- Right Content Start Here -->
<div class="right-content pt-0">
<div class="row headings-color">
<div class="col-lg-3 d-flex align-items-center p-0 d-md-none d-lg-block">
  <h4 class="headings-color mb-0">{{@$product->productCategory->title}} / {{@$product->productSubCategory->title}}</h4>
</div>
<div class="col-lg-5 pl-2 d-md-none d-lg-block">
    <h4 class="headings-color mb-0">Product Information</h4>
  </div>
<div class="col-lg-4 mb-2 d-md-none d-lg-block">
  <div class="row">
    <div class="col-10"> <h4 class="headings-color mb-0">Vendor Specific Information</h4></div>
    <div class="col-2 pl-4 pr-0"></div>
  </div>
</div>

</div>
</div>
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

@if($productImagesCount > 0)
<div class="col-xl-3 col-md-6 banner-video">
<div class="h-100">
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="tab1">

      <div class="logo-container2">
      @if(file_exists( public_path() . '/uploads/products/product_'.@$productImages[0]->product_id.'/'.@$productImages[0]->image))
      <img src="{{asset('public/uploads/products/product_'.@$productImages[0]->product_id.'/'.@$productImages[0]->image)}}" class="img-fluid" alt="image" width="100%" id="main-image">
      @else

       <img src="{{ asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid" alt="image" width="100%" id="main-image">
      @endif
      <!-- <div class="overlay2">
        <a href="javascript:void(0)" class="icon img-uploader" data-id="{{$id}}" title="Add Product Images Here" data-toggle="modal" data-target="#productImagesModal"><i class="fa fa-camera"></i></a>
      </div> -->
      </div>

    </div>
  </div>
    <ul class="nav nav-tabs row text-center purchase-product-detail photo-gallery" style="max-width: 100%;" id="real-data" role="tablist">
      @php $imageCounter = 1; @endphp
      @foreach($productImages as $image)
      <li class="active col-lg-3 p-0 mt-1" id="prod-image-{{@$image->id}}" style="position: relative;max-width: 100%;">
        <a href="#tab1" aria-controls="home" role="tab" data-toggle="tab">
          <a data-img_id="{{@$image->id}}" data-prod_id="{{@$image->product_id}}" aria-expanded="true" class="close delete-prod-img-btn delete-product-image d-none" title="Delete">&times;</a>
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
</div>
</div>

@else

<div class="col-xl-3 col-lg-3 col-md-6 banner-video">
  <div class="row">
    <div class="col-lg-3 d-flex align-items-center p-0 d-md-block d-lg-none">
  <h4 class="headings-color mb-0">{{@$product->productCategory->title}} / {{@$product->productSubCategory->title}}</h4>
</div>
  </div>
<div class="h-100">
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="tab1">

    <div class="logo-container2">
      <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid lg-logo" alt="image" width="100%" id="main-image">

      <!-- <div class="overlay2">
        <a href="javascript:void(0)" class="icon img-uploader" data-id="{{$id}}" title="Add Product Images Here" data-toggle="modal" data-target="#productImagesModal"><i class="fa fa-camera"></i></a>
      </div> -->
    </div>

    </div>
  </div>
    <ul class="nav nav-tabs purchase-product-detail photo-gallery row" role="tablist">
      <li class="active col-lg-12 p-0">
        <a href="#tab1" class="col-lg-3 p-0" aria-controls="home" role="tab" data-toggle="tab">
          <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid ml-2 mt-2" alt="image" width="60">
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
</div>
</div>

@endif


<div class="col-lg-5 col-md-6">
    <div class="row">
 <div class="col-lg-5 pl-2 d-md-block d-lg-none">
    <h4 class="headings-color mb-0">Product Information</h4>
  </div>
  </div>
<div class="bg-white pt-3 pl-2 h-100">
  <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
    <tbody>
      <tr>
        <td class="fontbold">Reference <b style="color: red;">*</b></td>
        <td>
        <span class="m-l-15" id="refrence_code"  data-fieldvalue="{{@$product->refrence_code}}">
        {{(@$product->refrence_code!=null)?@$product->refrence_code:'--'}}
        </span>

        <input type="text"  name="refrence_code" class="d-none" value="{{(@$product->refrence_code!=null)?$product->refrence_code:''}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">HS Code</td>
        <td class="text-nowrap">
        {{--<span class="m-l-15 inputDoubleClick" id="hs_code"  data-fieldvalue="{{@$product->hs_code}}">
        {{(@$product->hs_code!=null)?@$product->hs_code:'--'}}
        </span>

        <input type="text"  name="hs_code" class="fieldFocus d-none" value="{{(@$product->hs_code!=null)?$product->hs_code:''}}">--}}

        <span class="m-l-15" id="hs_code"  data-fieldvalue="">
          {{(@$product->productSubCategory->hs_code!=null)?@$product->productSubCategory->hs_code:'--'}}</span>
        </td>
      </tr>

      <tr>
        <td class="fontbold">Description <b style="color: red;">*</b></td>
        <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="short_desc"  data-fieldvalue="{{@$product->short_desc}}">
        {{(@$product->short_desc!=null)?@$product->short_desc:'--'}}
        </span>

        <textarea name="short_desc" class="fieldFocus d-none" cols="20" rows="3">{{(@$product->short_desc!=null)?@$product->short_desc:''}}</textarea>
        </td>
      </tr>

      {{--<tr>
        <td class="fontbold">Name <b style="color: red;">*</b></td>
        <td class="">
        <span class="m-l-15 inputDoubleClick" id="name"  data-fieldvalue="{{@$product->name}}">
        {{(@$product->name!=null)?@$product->name:'--'}}</span>

        <input type="text"  name="name" class="fieldFocus d-none" value="{{(@$product->name!=null)?$product->name:''}}">
        </td>
      </tr>--}}

      {{--<tr>
        <td class="fontbold text-nowrap">Primary @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif <b style="color: red;">*</b></td>
        <td class="text-nowrap">
        @if($product->primary_category == NULL)
        <span class="m-l-15 selectDoubleClick" id="primary_category" data-fieldvalue="{{@$product->productCategory->title}}">Select @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</span>

        <select name="primary_category" class="selectFocus form-control prod-category d-none">
          <option>Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
          @if($primaryCategory)
          @foreach($primaryCategory as $category)
          <option  value="{{$category->id}}">{{$category->title}}</option>
          @endforeach
          @endif
        </select>
        <input type="text" name="primary_category" class="fieldFocus d-none" value="{{@$product->productCategory->title}}">

        @else
        <span class="m-l-15 selectDoubleClick" id="primary_category"  data-fieldvalue="{{@$product->productCategory->title}}">{{@$product->productCategory->title}}</span>

        <select name="primary_category" class="selectFocus form-control prod-category d-none">
          <option>Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
          @if($primaryCategory)
          @foreach($primaryCategory as $category)
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
        <td class="fontbold text-nowrap">@if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif <b style="color: red;">*</b></td>
        <td class="text-nowrap">
        @if($product->category_id == 0)

          @if(@$product->primary_category != NULL)
            <span class="category-span m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="'{{@$product->productSubCategory->title}}">Select @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</span>
            <select name="category_id" class="selectFocus form-control category-id d-none">
              <option>Choose @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</option>
              @if($subCategories)
              @foreach($subCategories as $category)
              <option  value="{{$category->id}}">{{$category->title}}</option>
              @endforeach
              @endif
            </select>
          @else
            <span class="category-span m-l-15" id="category_id"  data-fieldvalue="{{@$product->productSubCategory->title}}">Select Sub-Category</span>
            <select name="category_id" class="selectFocus form-control category-id d-none">
              <option>Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
            </select>
          @endif

        @else

        <span class="m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="{{@$product->productSubCategory->title}}">{{@$product->productSubCategory->title}}</span>
        @if($product->primary_category != NULL)
        <select name="category_id" class="selectFocus form-control category-id d-none">
          <option>Choose @if(!array_key_exists('subcategory', $global_terminologies)) Sub Category @else {{$global_terminologies['subcategory']}} @endif</option>
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
        <td class="fontbold text-nowrap">Type <b style="color: red;">*</b></td>

        <td class="text-nowrap">
        <span class="m-l-15 selectDoubleClick" id="product_type" data-fieldvalue="{{@$product->type_id}}">
        {{(@$product->type_id != null)?@$product->productType->title:'Select Type'}}
        </span>

        <select name="type_id" class="selectFocus form-control d-none">
          <option value="" disabled="" selected="">Choose @if(!array_key_exists('type', $global_terminologies)) Type @else {{$global_terminologies['type']}} @endif</option>
          @foreach($product_type as $type)
          <option value="{{$type->id}}" {{ ($product->type_id == $type->id ? "selected" : "") }} >{{$type->title}}</option>
          @endforeach
        </select>
        </td>
      </tr>

       <tr>
       <th>{{$global_terminologies['brand']}}</th>


        <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="brand"  data-fieldvalue="{{@$product->brand}}">
        {{(@$product->brand!=null)?@$product->brand:'--'}}
        </span>

        <input type="text" name="brand" class="fieldFocus d-none" value="{{(@$product->brand!=null)?$product->brand:''}}">
        </td>
        <!-- <td class="text-nowrap">
        <span class="m-l-15 selectDoubleClick" id="product_brand" data-fieldvalue="{{@$product->brand_id}}">
        {{(@$product->brand_id != null)?@$product->productBrand->title:'Select Brand'}}
        </span>

        <select name="brand_id" class="selectFocus form-control d-none prod-brands">
          <option value="" disabled="" selected="">Choose Brand</option>
          @foreach($product_brand as $brand)
          <option value="{{$brand->id}}" {{ ($product->brand_id == $brand->id ? "selected" : "") }} >{{$brand->title}}</option>
          @endforeach
          <option value="new_brand">Add New</option>
        </select>
        </td> -->
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Temperature (C)</td>
        <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="{{@$product->name}}">
        {{(@$product->product_temprature_c!=null)?@$product->product_temprature_c:'--'}}
        </span>

        <input type="number" name="product_temprature_c" class="fieldFocus d-none" value="{{(@$product->product_temprature_c!=null)?$product->product_temprature_c:''}}">
        </td>
      </tr>

      <tr>
        {{--<td class="fontbold text-nowrap">Billed Unit <b style="color: red;">*</b></td>--}}
        <td class="fontbold text-nowrap">Billed Unit <b style="color: red;">*</b></td>

        <td class="text-nowrap">
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
        {{--<option value="new">Add New</option>--}}

        </select>
        <input type="text" name="buying_unit" class="fieldFocus d-none" value="{{(@$product->buying_unit!=null)?@$product->units->title:'Select'}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Selling Unit <b style="color: red;">*</b></td>

        <td class="text-nowrap">
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
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Stock Unit</td>

        <td class="text-nowrap">
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
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Avg. Weight per piece <br> or box per billed unit (Kg)</td>
        <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="weight"  data-fieldvalue="{{@$product->weight}}">
        {{(@$product->weight!=null)?@$product->weight:'--'}}
        </span>

        <input type="number" name="weight" class="fieldFocus d-none" value="{{(@$product->weight!=null)?$product->weight:''}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Minimum Stock</td>
        <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="min_stock"  data-fieldvalue="{{@$product->min_stock}}">
        {{(@$product->min_stock!=null)?@$product->min_stock:'--'}}
        </span>

        <input type="number" name="min_stock" class="fieldFocus d-none" value="{{(@$product->min_stock!=null)?$product->min_stock:''}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">VAT</td>
        <td class="text-nowrap">
          {{--<span class="m-l-15 inputDoubleClick" id="vat"  data-fieldvalue="{{@$product->vat}}">
          {{(@$product->vat!=null)?@$product->vat:'--'}}</span>

          <input type="number" name="vat" class="fieldFocus d-none" value="{{(@$product->vat!=null)?@$product->vat:''}}">%--}}

          <span class="m-l-15" id="vat"  data-fieldvalue="">
          {{(@$product->productSubCategory->vat!==null)?@$product->productSubCategory->vat.' %':'--'}}</span>
        </td>
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Import Tax (Book)</td>
        <td>
            {{--<span class="m-l-15 inputDoubleClick" id="import_tax_book"  data-fieldvalue="{{@$product->import_tax_book}}">{{(@$product->import_tax_book!=null)?@$product->import_tax_book:'--'}}</span>
            <input type="number" style="width:80%;" name="import_tax_book" class="fieldFocus d-none" value="{{@$product->import_tax_book}}">%--}}

            <span class="m-l-15" id="import_tax_book"  data-fieldvalue="">
          {{(@$product->productSubCategory->import_tax_book!==null)?@$product->productSubCategory->import_tax_book.' %':'--'}}</span>
        </td>
      </tr>

      <tr class="d-none">
        <td class="fontbold text-nowrap">Long Description</td>
        <td class="text-nowrap">
        <span class="m-l-15 inputDoubleClick" id="long_desc"  data-fieldvalue="{{@$product->long_desc}}">
        {{(@$product->long_desc!=null)?@$product->long_desc:'--'}}
        </span>

        <textarea name="long_desc" class="fieldFocus d-none" cols="20" rows="3">{{(@$product->long_desc!=null)?@$product->long_desc:''}}</textarea>
        </td>
      </tr>

    </tbody>
  </table>
</div>

</div>

<div class="col-lg-4 col-md-6">
   <div class="row">
 <div class="col-lg-5 pl-2 d-md-block d-lg-none">
   <h4 class="headings-color mb-0">Vendor Specific Information</h4>
  </div>
  </div>
<div class="bg-white pt-3 pl-2">
  <table id="example" class="table-responsive headings-color table sales-customer-table dataTable const-font" style="width: 100%;">
    <tbody>

      @if($product->supplier_id == 0)
        @php $disableCheck = ""; @endphp
      @else
        @php $disableCheck = "inputDoubleClickFirst"; @endphp
      @endif

      <tr>
        <td class="fontbold">Default/Last Supplier <b style="color: red;">*</b></td>

        <td class="text-nowrap" width="56%">
        @if($product->supplier_id == 0)
        <div class="incomplete-filter">
          <span class="m-l-15 inputDoubleClick" id="supplier_id"  data-fieldvalue="{{@$product->def_or_last_supplier->company}}">Select Supplier</span>

          <select class="selectFocus selectpicker form-control d-none show-tick supplier_id getDataOfProductSupplier" data-live-search="true" title="@if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif" name="supplier_id">

            @if($getSuppliers)
            @foreach($getSuppliers as $sp)
            <option  value="{{$sp->id}}"> {{$sp->company}} </option>
            @endforeach
            <!-- <option value="add-prod-supp">Add New</option> -->
            @endif
          </select>
          </div>

        @else
        <div class="incomplete-filter">
          <span class="m-l-15 inputDoubleClick" id="supplier_id"  data-fieldvalue="{{@$product->def_or_last_supplier->company}}">{{@$product->def_or_last_supplier->company}}</span>

          <select class="selectFocus selectpicker form-control d-none show-tick supplier_id getDataOfProductSupplier" data-live-search="true" title="@if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif" name="supplier_id">

            @if($getSuppliers)
            @foreach($getSuppliers as $sp)
            <option {{ ($sp->id == @$product->supplier_id ? 'selected' : '' )}}  value="{{$sp->id}}"> {{$sp->company}} </option>
            @endforeach
            <!-- <option value="add-prod-supp">Add New</option> -->
            @endif
          </select>
        </div>

        @endif
        </td>
      </tr>

      <input type="hidden" name="default_or_last_supplier_id" id="default_or_last_supplier_id" value="{{@$default_or_last_supplier->id}}">

      <tr>
        <td class="fontbold">Supplier Product Ref #</td>
        <td class="">
          <span class="m-l-15 {{$disableCheck}}" id="product_supplier_reference_no"  data-fieldvalue="{{@$default_or_last_supplier->product_supplier_reference_no}}">{{(@$default_or_last_supplier->product_supplier_reference_no!=null)?@$default_or_last_supplier->product_supplier_reference_no:'--'}}</span>
          <input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->product_supplier_reference_no}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['purchasing_price']}} <br> (EUR)  <b>({{@$default_or_last_supplier->supplier->getCurrency->currency_code}})</b> <b style="color: red;">*</b></td>
        <td class="text-nowrap">
          {{--<span class="m-l-15 {{$disableCheck}}" id="buying_price"  data-fieldvalue="{{@$default_or_last_supplier->buying_price}}">{{(@$default_or_last_supplier->buying_price!=null)?@$default_or_last_supplier->buying_price:'--'}}</span>
          <input type="number" style="width:50%;" name="buying_price" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->buying_price}}">
          </td>--}}

          <span class="m-l-15" id="buying_price"  data-fieldvalue="{{@$default_or_last_supplier->buying_price}}">{{(@$default_or_last_supplier->buying_price!==null)?@$default_or_last_supplier->buying_price:'--'}}</span>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['purchasing_price']}}<b>(THB)</b></td>
        <td class="text-nowrap">
          <span class="m-l-15" id="buying_price_in_thb"  data-fieldvalue="{{@$default_or_last_supplier->buying_price_in_thb}}">{{(@$default_or_last_supplier->buying_price_in_thb!==null)?@$default_or_last_supplier->buying_price_in_thb:'--'}}</span>
      </tr>

      <tr>
        <td class="fontbold">Extra Cost <b>(THB)</b></td>
        <td class="text-nowrap">
          <span class="m-l-15 {{$disableCheck}}" id="extra_cost"  data-fieldvalue="{{@$default_or_last_supplier->extra_cost}}">{{(@$default_or_last_supplier->extra_cost!==null)?@$default_or_last_supplier->extra_cost:'--'}}</span>
          <input type="number" style="width:50%;" name="extra_cost" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->extra_cost}}">
          </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['freight_per_billed_unit']}}</b></td>
        <td class="text-nowrap">
          <span class="m-l-15 {{$disableCheck}}" id="freight"  data-fieldvalue="{{@$default_or_last_supplier->freight}}">{{(@$default_or_last_supplier->freight!==null)?@$default_or_last_supplier->freight:'--'}}</span>
          <input type="number" style="width:50%;" name="freight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->freight}}">
          </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['landing_per_billed_unit']}}</b></td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="landing"  data-fieldvalue="{{@$default_or_last_supplier->landing}}">{{(@$default_or_last_supplier->landing!==null)?@$default_or_last_supplier->landing:'--'}}</span>
          <input type="number" style="width:50%;" name="landing" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->landing}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">Import Tax (Actual)</td>
        <td class="text-nowrap">
          <span class="m-l-15 {{$disableCheck}}" id="import_tax_actual"  data-fieldvalue="{{@$default_or_last_supplier->import_tax_actual}}">{{(@$default_or_last_supplier->import_tax_actual!==null)?@$default_or_last_supplier->import_tax_actual:'--'}}</span>
          <input type="number" style="width:50%;" name="import_tax_actual" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->import_tax_actual}}">%
          </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['gross_weight']}}</td>
        <td class="text-nowrap">
          <span class="m-l-15 {{$disableCheck}}" id="gross_weight"  data-fieldvalue="{{@$default_or_last_supplier->gross_weight}}">{{(@$default_or_last_supplier->gross_weight!==null)?@$default_or_last_supplier->gross_weight:'--'}}</span>
          <input type="number" style="width:50%;" name="gross_weight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->gross_weight}}">
          </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['expected_lead_time_in_days']}} <b style="color: red;">*</b></td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="leading_time"  data-fieldvalue="{{@$default_or_last_supplier->leading_time}}">{{(@$default_or_last_supplier->leading_time!==null)?@$default_or_last_supplier->leading_time:'--'}}</span>
          <input type="number" style="width:50%;" name="leading_time" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->leading_time}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">Supplier Packaging </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="supplier_packaging"  data-fieldvalue="{{@$default_or_last_supplier->supplier_packaging}}">{{(@$default_or_last_supplier->supplier_packaging!==null)?@$default_or_last_supplier->supplier_packaging:'--'}}</span>
          <input type="text" style="width:50%;" name="supplier_packaging" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->supplier_packaging}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">Billed Unit Per Package </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="billed_unit"  data-fieldvalue="{{@$default_or_last_supplier->billed_unit}}">{{(@$default_or_last_supplier->billed_unit!==null)?@$default_or_last_supplier->billed_unit:'--'}}</span>
          <input type="text" style="width:50%;" name="billed_unit" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->billed_unit}}">
        </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['minimum_order_quantity']}} </td>
        <td>
          <span class="m-l-15 {{$disableCheck}}" id="m_o_q"  data-fieldvalue="{{@$default_or_last_supplier->m_o_q}}">{{(@$default_or_last_supplier->m_o_q!==null)?@$default_or_last_supplier->m_o_q:'--'}}</span>
          <input type="number" style="width:100%;" name="m_o_q" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->m_o_q}}">
        </td>
      </tr>

    </tbody>
  </table>
</div>

<div class="bg-white mt-3 pt-3 pl-2">
  <table id="example" class="table-responsive headings-color table sales-customer-table dataTable const-font" style="width: 100%;">
    <tbody>

      <!-- Supplier currency -->
      <tr>
        <td class="fontbold text-nowrap">{{$global_terminologies['cost_price']}}(<b>{{@$default_or_last_supplier->supplier->getCurrency->currency_code}}</b>) <i class="fa fa-question-circle-o buy_unit_cost_price_mark_for_supp" aria-hidden="true"></i>
          <div class="tooltiptext">{{@$IMPcalculation}}</div>
        </td>
        <td class="text-nowrap">
          <span class="m-l-15" id="t_b_u_c_p_of_supplier" data-fieldvalue="{{@$product->t_b_u_c_p_of_supplier}}">{{(@$product->t_b_u_c_p_of_supplier!=null)?number_format((float)@$product->t_b_u_c_p_of_supplier, 2, '.', ''):'0'}}</span>
        </td>
      </tr>

      <!-- THB currency -->
      <tr>
        <td class="fontbold text-nowrap">{{$global_terminologies['cost_price']}} (<b>THB</b>) <i class="fa fa-question-circle-o buy_unit_cost_price_mark" aria-hidden="true"></i>
          <div class="tooltiptext">{{@$IMPcalculation}}</div>
        </td>
        <td class="text-nowrap">
          <span class="m-l-15" id="total_buy_unit_cost_price" data-fieldvalue="{{@$product->total_buy_unit_cost_price}}">{{(@$product->total_buy_unit_cost_price!=null)?number_format((float)@$product->total_buy_unit_cost_price, 2, '.', ''):'0'}}</span>
        </td>
      </tr>

      <tr>
        <td class="fontbold text-nowrap">{{$global_terminologies['unit_conversion_rate']}} <b style="color: red;">*</b></td>
        <td class="text-nowrap">
          @if($product->unit_conversion_rate == null)
            <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate"  data-fieldvalue="{{@$product->unit_conversion_rate}}">--</span>
            <input type="text"  name="unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="">
          @else
            <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate"  data-fieldvalue="{{@$product->unit_conversion_rate}}">{{number_format((float)@$product->unit_conversion_rate, 3, '.', '')}}</span>
            <input type="text"  name="unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="{{number_format((float)@$product->unit_conversion_rate, 3, '.', '')}}">
          @endif
        </td>
      </tr>

      <tr>
        <td class="fontbold text-nowrap">Selling Unit Cost Price</td>
        <td class="text-nowrap">
          <span class="m-l-15" id="selling_price" data-fieldvalue="{{@$product->selling_price}}">{{(@$product->selling_price!=null)?number_format((float)@$product->selling_price, 2, '.', ''):'N/A'}}</span>
        </td>
      </tr>

    </tbody>
  </table>
</div>

</div>


</div>
<div class="row mb-3">

<div class="col-lg-6 col-md-6 headings-color">

<div class="col-lg-12 col-md-12pl-0 pr-0">
<h4 class="pb-2">Stock Card</h4>
</div>

  <!-- Nav tabs -->
<ul class="nav nav-tabs">
@foreach($warehouse_products as $key => $warehouse)
  <li class="nav-item">
    <a class="nav-link click-nav @if($key == 0) active @endif" data-toggle="tab" data-id="{{$warehouse->id}}" href="#id{{$warehouse->id}}">{{ $warehouse->getWarehouse->warehouse_title }}</a>
  </li>
@endforeach
</ul>

<!-- Tab panes -->
<div class="tab-content">
@foreach($warehouse_products as $key => $wh)
<div class="container tab-pane p-0 po-pod-list @if($key == 0) active @endif" id="id{{$wh->id}}">
    <div class="bg-white table-responsive h-100" style="min-height: 235px;">
      <div>
        Current Stock : {{$wh->current_quantity}}
        Stock Unit : {{$product->sellingUnits->title}}
      </div>
      <table class="table headings-color table-po-pod-list" style="width: 100%;">
        <thead>

        </thead>
        <tbody>
          @if($stock_card->count() > 0)
          @foreach($stock_card as $card)
            @if($wh->getWarehouse->id == $card->warehouse_id)
              <tr class="header">
                @if($card->po_id != null)
                <td style="width: 15%">PO # {{$card->po_id}}</td>
                @else
                <td style="width: 15%">Stock Adjustment</td>
                @endif
                <td style="width: 15%">IN: {{$card->quantity_in != null ? $card->quantity_in : 0}}</td>
                <td style="width: 15%">0</td>
                <td style="width: 25%">EXP: {{Carbon::parse(@$card->created_at)->format('d/m/Y')}}</td>
                <td style="width: 15%">{{($card->quantity_in != NULL ? $card->quantity_in : 0)}}</td>
                <td style="width: 10%">
                  <a href="javascript:void(0)" data-id="{{$card->id}}" class="collapse_rows"><button class="btn recived-button view-supplier-btn toggle-btn1" data-toggle="collapse"><span id="sign{{@$card->id}}">+</span></button></a>
                </td>
              </tr>
              <tr class="ddddd" id="ddddd{{$card->id}}">
            <td colspan="5">
            <table width="100%" class="dot-dash">
              <thead>
                <tr>
                  <th>Date </th>
                  <th>Order # </th>
                  <th>Out </th>
                  <th>Balance </th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>14/01/2019</td>
                  <td>1</td>
                  <td>0</td>
                  <td>10</td>
                </tr>
                <tr>
                  <td>14/01/2019</td>
                  <td>1</td>
                  <td>0</td>
                  <td>10</td>
                </tr>
                <tr>
                  <td>14/01/2019</td>
                  <td>1</td>
                  <td>0</td>
                  <td>10</td>
                </tr>
              </tbody>
            </table>
          </td>
          </tr>
            @endif
          @endforeach
          <tr class="header"></tr>
          @else
            <tr>
              <td align="center">No Data Found!!!</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
 @endforeach
</div>

</div>

<div class="col-lg-6 col-md-6">

<div class="col-lg-12 col-md-12 pl-0 pr-0">
@php
if(@$CustomerTypeProductMarginCount == 2)
{
  $class = "d-none";
}
@endphp
<p class="d-none">
  <button class="btn btn-color add-btn pull-right p-0" data-toggle="modal" data-target="#addProdMarginsModal"><i class="fa fa-plus"></i> Add</button>
</p>
<h4 class="pb-2">Product Margins</h4>
</div>

<div class="bg-white table-responsive">
  <table id="example" class="table headings-color dot-dash" style="width: 100%;">
    <thead class="sales-coordinator-thead">
      <th></th>
      <th class="float-right">MKT <i class="fa fa-question-circle-o resturent" aria-hidden="true"></i><div class="tooltiptext">This is Market Price!!!</div></th>


      <th>Resturants</th>
      <th class="float-right">MKT</th>
      <th>Hotels</th>
    </thead>

    <tbody>

      {{--@if($CustomerTypeProductMargin)--}}
      <tr style="background-color: #cccbcb;">
        <td>@if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif Margin</td>
        <td></td>
        <td>{{@$product->customer_type_category_margins[0]->default_value != null ? @$product->customer_type_category_margins[0]->default_value." %": '0 %'}}</td>
        <td></td>
        <td>{{@$product->customer_type_category_margins[1]->default_value != null ? @$product->customer_type_category_margins[1]->default_value." %": '0 %'}}</td>
      </tr>

      <tr>
        <td>Default Product Margin</td>
        <td></td>

        <td id="{{@$product->customer_type_product_margins[0]->id}}">
          <span class="m-l-15 inputDoubleClickPM" id="default_value"  data-fieldvalue="{{@$product->customer_type_product_margins[0]->default_value}}">{{$product->customer_type_product_margins[0]->default_value}}</span>
          <input type="number" style="width:20%;" name="default_value" class="fieldFocusPM d-none" value="{{$product->customer_type_product_margins[0]->default_value}}">%
        </td>
        <td></td>
        <td id="{{@$product->customer_type_product_margins[1]->id}}">
          <span class="m-l-15 inputDoubleClickPM" id="default_value"  data-fieldvalue="{{@$product->customer_type_product_margins[1]->default_value}}">{{$product->customer_type_product_margins[1]->default_value}}</span>
          <input type="number" style="width:20%;" name="default_value" class="fieldFocusPM d-none" value="{{$product->customer_type_product_margins[1]->default_value}}">%
        </td>
      </tr>

      <tr>
        <td>Product Sugguested Price</td>
        <td><input type="checkbox" name="customer_type_id" disabled {{$product->customer_type_product_margins[0]->is_mkt == 1 ? 'checked' : ''}} value="1" class="float-right mt-1 market_price_check"></td>
        <td><span class="restaurant rest-sale-price"> {{number_format($product->selling_price+($product->selling_price*($product->customer_type_product_margins[0]->default_value/100)),2,'.',',')}}</span></td>


        <td><input type="checkbox" name="customer_type_id" disabled {{$product->customer_type_product_margins[1]->is_mkt == 1 ? 'checked' : ''}}  value="2" class="float-right mt-1 market_price_check"></td>
        <td><span class="hotel hotel-sale-price">
          {{number_format($product->selling_price+($product->selling_price*($product->customer_type_product_margins[1]->default_value/100)),2,'.',',')}}</span></td>
      </tr>

      <tr>
        <td>Fixed Price</td>
        <td></td>
        <td id="{{@$product->product_fixed_price[0]->id}}">
          <span class="m-l-15 inputDoubleClickPM"  data-fieldvalue="{{number_format(@$product->product_fixed_price[0]->fixed_price,2,'.',',')}}">{{number_format($product->product_fixed_price[0]->fixed_price,2,'.',',')}}</span>
          <input type="number" step="0.1" class="fieldFocusPM d-none" style="width: 80%;" name="product_fixed_price" value="{{number_format($product->product_fixed_price[0]->fixed_price,2,'.',',')}}">
        </td>
        <td></td>
        <td id="{{@$product->product_fixed_price[1]->id}}">
          <span class="m-l-15 inputDoubleClickPM" data-fieldvalue="{{number_format(@$product->product_fixed_price[1]->fixed_price,2,'.',',')}}">{{number_format($product->product_fixed_price[1]->fixed_price,2,'.',',')}}</span>
          <input type="number" step="0.1" class="fieldFocusPM d-none" style="width: 80%;" name="product_fixed_price" value="{{number_format($product->product_fixed_price[1]->fixed_price,2,'.',',')}}">
        </td>
      </tr>


        @if($product->product_fixed_price[0]->fixed_price != 0)
          @php $doubleClick1 = "inputDoubleClickPM" ; @endphp
        @else
          @php $doubleClick1 = "" ; @endphp
        @endif


      <tr>
        <td>Fixed Price Expiration</td>
        <td></td>
        @php
          $today = date('Y-m-d');
        @endphp
        <td id="{{@$product->product_fixed_price[0]->id}}">
          <span class="m-l-15 {{$doubleClick1}}" data-fieldvalue="{{@$product->product_fixed_price[0]->expiration_date}}">{{$product->product_fixed_price[0]->expiration_date != NULL ? $product->product_fixed_price[0]->expiration_date : "---"}}</span>
          <input type="date" min="{{$today}}" style="width: 85%;" class="fieldFocusPM d-none" name="expiration_date" value="{{@$product->product_fixed_price[0]->expiration_date}}">
        </td>

        @if($product->product_fixed_price[1]->fixed_price != 0)
          @php $doubleClick2 = "inputDoubleClickPM" ; @endphp
        @else
          @php $doubleClick2 = "" ; @endphp
        @endif

        <td></td>
        <td id="{{@$product->product_fixed_price[1]->id}}">
          <span class="m-l-15 {{$doubleClick2}}" data-fieldvalue="{{@$product->product_fixed_price[1]->expiration_date}}">{{$product->product_fixed_price[1]->expiration_date != NULL ? $product->product_fixed_price[1]->expiration_date : "---"}}</span>
          <input type="date" style="width: 85%;" class="fieldFocusPM d-none" name="expiration_date" value="{{@$product->product_fixed_price[1]->expiration_date}}">
        </td>
      </tr>
    </tbody>
  </table>
</div>

</div>

</div>
<div class="row mb-3"> <!-- Product supplier div -->
<!-- left Side Start -->
@if($countOfProductSuppliers !== 0 && $countOfProductSuppliers !== 1)
<div class="col-lg-6 col-md-6 headings-color">

<div class="row">

<div class="col-lg-10 col-md-10 pl-3 pr-0">
  <h4>Product Suppliers</h4>
</div>

<div class="col-lg-1 col-md-1">
  <button class="btn recived-button view-supplier-btn add-bulk-suppliers d-none" type="button" title="Bulk Upload"><i class="fa fa-upload"></i></button>
</div>

<div class="col-lg-1 col-md-1">
  <button class="btn recived-button view-supplier-btn add-supplier" type="button" title="Add Product Supplier"><i class="fa fa-plus"></i></button>
</div>

</div>

<div class="bg-white pt-3 pl-2">

  <div class="table-responsive">
    <table class="table entriestable table-bordered table-product-suppliers text-center purchase-complete-product">
      <thead>
        <tr>
          {{--<th>Action</th>--}}
          <th>Company</th>
          <th>Ref. #</th>
          <th>Import <br> Tax Actual %</th>
          <th>Gross <br> Weight</th>
          <th>Freight</th>
          <th>Landing</th>
          <th>Buying <br> Price</th>
          <th>Lead <br> Days</th>
          <th>Supplier Packaging</th>
          <th>Billed Unit</th>
          <th>MOQ</th>
        </tr>
      </thead>
    </table>
  </div>

</div>

</div>
@endif
<div class="col-lg-6 col-md-6 headings-color">
  <div class="pt-0 mt-0">

<!-- Customer Fixed Prices table  -->
@if($ProductCustomerFixedPrices)
<div class="row">

<div class="col-lg-9 col-md-9 pl-3 pr-0">
  <h4>Customer Fixed Prices </h4>
</div>
 <div class="col-lg-3 col-md-3 d-none">

    <a href="{{url('common/get-customer-fixed-prices/'.@$product->id)}}" class="btn add-btn btn-color pull-right mr-1" id="viewAllFixedPrices">View All</a>

    </div>

</div>

<div class="bg-white table-responsive pt-3 pl-2 pr-2" style="min-height: 230px;">
  <table id="example" class="table headings-color const-font table-bordered text-center" style="width: 100%;">
    <thead class="sales-coordinator-thead">
      <tr>
        <th>Sr. #</th>
        <th>Customer # </th>
        <th>Company</th>
        <th>Fixed Price</th>
        <th>Price Expiration Date</th>
        <!-- <th>Action</th> -->
      </tr>
    </thead>
    <tbody>
      <?php $i=1; ?>
      @if($ProductCustomerFixedPrices->count() > 0)
      @foreach($ProductCustomerFixedPrices as $ProductCustomerFixedPrice)
      <tr id="{{$ProductCustomerFixedPrice->id}}" style="border: 1px solid #eee;">
        <td align="left">{{$i}}</td>
        <td align="left">{{$ProductCustomerFixedPrice->customers->reference_number}}</td>
        <td align="left">{{$ProductCustomerFixedPrice->customers->company}}</td>
        <td align="right">
          <span class="m-l-15 inputDoubleClickFixedPrice" id="fixed_price"  data-fieldvalue="{{$ProductCustomerFixedPrice->fixed_price}}">{{number_format((float)@$ProductCustomerFixedPrice->fixed_price, 2, '.', '')}}</span>
          <input type="number" style="width:50%;" name="fixed_price" class="fieldFocusFixedPrice d-none" value="{{number_format((float)@$ProductCustomerFixedPrice->fixed_price, 2, '.', '')}}">
        </td>
        <td align="left">
          @php
            $today = date('Y-m-d');
          @endphp
          <span class="m-l-15 inputDoubleClickFixedPrice" id="expiration_date"  data-fieldvalue="{{@$ProductCustomerFixedPrice->expiration_date}}">{{@$ProductCustomerFixedPrice->expiration_date}}</span>
          <input type="date" style="width:70%;" min="{{$today}}" name="expiration_date" class="fieldFocusFixedPrice d-none" value="{{($ProductCustomerFixedPrice->expiration_date!=null)?@$ProductCustomerFixedPrice->expiration_date:'--'}}">
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
      <label class="pull-left">Customer*</label>
      <select class="font-weight-bold form-control-lg form-control prod-fixed-cust" name="customers" required="true">
        <option value="">Select Customer</option>
        @if($customers)
        @foreach($customers as $customer)
          <option value="{{$customer->id}}">{{$customer->company}}</option>
        @endforeach
        @endif
      </select>
    </div>

    @php
    $getProdPrice = App\Models\Common\Product::where('id',$product->id)->first();
    @endphp

    <div class="form-group">
      <label class="pull-left">Default Price</label>
      <input type="text" class="font-weight-bold form-control-lg form-control" name="default_price" value="{{(@$getProdPrice->selling_price!=null)?number_format((float)@$getProdPrice->selling_price, 2, '.', ''):'N/A'}}" readonly/>
    </div>

    <div class="form-group">
      <label class="pull-left">Fixed Price</label>
      <input class="font-weight-bold form-control-lg form-control" placeholder="Define fixed price here" name="fixed_price" type="number" required />
    </div>

    @php
      $today = date('Y-m-d');
    @endphp
    <div class="form-group">
      <label class="pull-left">Expiration Date</label>
      <input class="font-weight-bold form-control-lg form-control" name="expiration_date" min="{{$today}}" type="date" required/>
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

    @php
    $customerCategory = App\Models\Common\CustomerCategory::where('is_deleted',0)->get();
    @endphp

    <div class="form-group">
      <label class="pull-left">@if(!array_key_exists('customer_type', $global_terminologies))Customer Type @else {{$global_terminologies['customer_type']}} @endif*</label>
      <select class="font-weight-bold form-control-lg form-control" name="customer_type">
        <option value="">Select Category</option>
        @if($customerCategory)
        @foreach($customerCategory as $cat)
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
        <label class="pull-left">Supplier <b style="color: red;">*</b></label><br>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags product_suplliers" name="supplier">

        </select>
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Product Supplier Ref#</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Import Tax Actual </label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Gross Weight </label>
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
        <label class="pull-left">Purchasing Price (EUR)  <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Buying price" name="buying_price" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Leading Time <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="leading time e.g 2 days" name="leading_time"  type="number">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Supplier Packaging</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Supplier Packaging" name="supplier_packaging" type="text">
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

    @php
    $getSuppliers = App\Models\Common\Supplier::where('status',1)->get();
    @endphp

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Supplier <b style="color: red;">*</b></label>
        <input type="hidden" name="selected_supplier_id" class="selected_supplier_id" id="selected_supplier_id" value="">
        <input type="text" class="font-weight-bold form-control-lg form-control addSuppDropDown" name="supplier" readonly="" value="">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Product Supplier Ref#. <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">Import Tax Actual </label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Gross Weight <b style="color: red;">*</b></label>
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
        <label class="pull-left">Purchasing Price (EUR)  <b style="color: red;">*</b></label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Buying price" name="buying_price" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">Leading Time</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="leading time e.g 2 days" name="leading_time"  type="number">
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
    <form role="form" class="add-prodimage-form" method="post" enctype="multipart/form-data">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12 text-center">
          <div class="row">
            <div class="col-xs-12 col-md-12">
              <div class="col-md-12 col-lg-12 col-xs-12" id="columns">
                <h3 class="form-label">Select the images</h3>
                <div class="desc"><p class="text-center">or drag to box</p></div>
                <div id="uploads" class="row"><!-- Upload Content --></div>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal footer -->
    <div class="modal-footer">
      <input type="hidden" name="product_id" class="img-product-id">
      <button class="btn btn-danger" id="reset" type="button" ><i class="fa fa-history"></i> Clear</button>
      <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-upload"></i> Upload </button>
      <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
    </div>
   </form>

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
@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function(){
  $(".ddddd").toggle();
  $(".toggle-btn").click(function(){
  $(this).next().collapse('toggle');
  });
});

$('.collapse_rows').on('click',function(){
  var id = $(this).data('id');
  $(this).find('#sign'+id).text(function(_, value){return value=='-'?'+':'-'});
  $("#ddddd"+id).toggle();
});

$(document).ready(function(){
var prod_detail_id= "{{$product->id}}";
$('.table-product-suppliers').DataTable({
   processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  searching: false,
  ordering: false,
  scrollX: true,
  scrollY : '90vh',
  scrollCollapse: true,
  //  "columnDefs": [
  //   { className: "dt-body-left", "targets": [ 1,2,3,4,7,8,9,10,11,12 ] },
  //   { className: "dt-body-right", "targets": [5,6,7,8] }
  // ],
  serverSide: true,
  ajax: "{!! url('common/get-common-product-suppliers-data') !!}"+"/"+prod_detail_id,
  columns: [
      // { data: 'action', name: 'action' },
      { data: 'company', name: 'company' },
      { data: 'product_supplier_reference_no', name: 'product_supplier_reference_no' },
      { data: 'import_tax_actual', name: 'import_tax_actual' },
      { data: 'gross_weight', name: 'gross_weight' },
      { data: 'freight', name: 'freight' },
      { data: 'landing', name: 'landing' },
      { data: 'buying_price', name: 'buying_price' },
      { data: 'leading_time', name: 'leading_time' },
      { data: 'supplier_packaging', name: 'supplier_packaging' },
      { data: 'billed_unit', name: 'billed_unit' },
      { data: 'm_o_q', name: 'm_o_q' },
  ],
});

window.onload = function(){
  // alert($("#real-data ul").children('li').size() );
  var num = $("#real-data").find("li").length;
  if (num == 1)
  {
    var source = "{!! asset('public/uploads/Product-Image-Coming-Soon.png') !!}";
    var html_string = "<li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image' width='55'></a></li><li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image' width='55'></a></li><li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image' width='55'></a></li>";

    $("#dummy-images").append(html_string);
  }
  else if (num == 2)
  {
    var source = "{!! asset('public/uploads/Product-Image-Coming-Soon.png') !!}";
    var html_string = "<li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image' width='55'></a></li><li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image' width='55'></a></li>";

    $("#dummy-images").append(html_string);
  }
  else if (num == 3)
  {
    var source = "{!! asset('public/uploads/Product-Image-Coming-Soon.png') !!}";
    var html_string = "<li class='active mt-1'><a href='#' aria-controls='home' role='tab' data-toggle='tab'><img style='cursor:default; height:42px;' src="+source+" class='img-fluid ml-2 mt-2' alt='image' width='55'></a></li>";

    $("#dummy-images").append(html_string);
  }
  else if(num == 4)
  {
    $("#dummy-images").addClass('d-none');
  }
}
});

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
});

</script>
@stop
