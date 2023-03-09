@extends('sales.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')
<?php
use App\Models\Common\ProductCategory;
use App\Models\Common\Unit;
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
.tooltiptext{
    display: none;
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
</style>

{{-- Content Start from here --}}

<div class="row">
  <div class="col-md-8 title-col">
    <a  onclick="history.go(-1)" class="btn mb-3">Back</a>
  </div>
</div>

<!-- new design starts here -->

<!-- Right Content Start Here -->
<div class="right-content pt-0">
  <div class="row headings-color">
  <div class="col-lg-8 d-flex align-items-center">
    <h4>Product Detail
    </h4>
    <p class="span-heading pl-3 text-capitalize">{{@$product->productCategory->title}} / {{@$product->productSubCategory->title}}</p>
  </div>

  <div class="col-lg-4">
    <div class="row">
      <div class="col-12"> <h4 class="headings-color mb-0">Vendor Specific Information</h4></div>
    </div>
  </div>

  </div>
</div>
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

@if($productImagesCount > 0)
<div class="col-xl-3 col-md-3 banner-video">
  <div class="h-100">
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="tab1">

        <div class="logo-container2">
        @if(file_exists( public_path() . '/uploads/products/product_'.@$productImages[0]->product_id.'/'.@$productImages[0]->image))
        <img src="{{asset('public/uploads/products/product_'.@$productImages[0]->product_id.'/'.@$productImages[0]->image)}}" class="img-fluid" alt="image" width="100%" id="main-image">
        @else

         <img src="{{ asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid" alt="image" width="100%" id="main-image">
        @endif
        <div class="overlay2 d-none">
          <a href="javascript:void(0)" class="icon img-uploader" data-id="{{$id}}" title="Add Product Images Here" data-toggle="modal" data-target="#productImagesModal"><i class="fa fa-camera"></i></a>
        </div>
        </div>

      </div>
    </div>
      <ul class="nav nav-tabs row text-center purchase-product-detail photo-gallery" style="max-width: 100%;" id="real-data" role="tablist">
        @php $imageCounter = 1; @endphp
        @foreach($productImages as $image)
        <li class="active col-lg-3 p-0 mt-1" id="prod-image-{{@$image->id}}" style="position: relative;max-width: 100%;">
          <a href="#tab1" aria-controls="home" role="tab" data-toggle="tab">
            <a data-img_id="{{@$image->id}}" data-prod_id="{{@$image->product_id}}" aria-expanded="true" class="close delete-prod-img-btn delete-product-image" title="Delete">&times;</a>
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

<div class="col-xl-3 col-md-3 banner-video">
  <div class="h-100">
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="tab1">

      <div class="logo-container2">
        <img src="{{asset('public/uploads/Product-Image-Coming-Soon.png')}}" class="img-fluid lg-logo" alt="image" width="100%" id="main-image">

        <div class="overlay2 d-none">
          <a href="javascript:void(0)" class="icon img-uploader" data-id="{{$id}}" title="Add Product Images Here" data-toggle="modal" data-target="#productImagesModal"><i class="fa fa-camera"></i></a>
        </div>
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


<div class="col-lg-5">
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
          <span class="m-l-15 inputDoubleClick" id="hs_code"  data-fieldvalue="{{@$product->hs_code}}">
          {{(@$product->hs_code!=null)?@$product->hs_code:'--'}}
          </span>

          <input type="text"  name="hs_code" class="fieldFocus d-none" value="{{(@$product->hs_code!=null)?$product->hs_code:''}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['product_description']}} <b style="color: red;">*</b></td>
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
          <span class="m-l-15 selectDoubleClick" id="primary_category" data-fieldvalue="{{@$product->productCategory->title}}">Select Category</span>

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
          <td class="fontbold text-nowrap"> @if(!array_key_exists('subcategory', $global_terminologies)) Sub-Category @else {{$global_terminologies['subcategory']}} @endif <b style="color: red;">*</b></td>
          <td class="text-nowrap">
          @if($product->category_id == 0)

            @if(@$product->primary_category != NULL)
              <span class="category-span m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="'{{@$product->productSubCategory->title}}">Select @if(!array_key_exists('subcategory', $global_terminologies)) Sub-Category @else {{$global_terminologies['subcategory']}} @endif</span>
              <select name="category_id" class="selectFocus form-control category-id d-none">
                <option>Choose @if(!array_key_exists('subcategory', $global_terminologies)) Sub-Category @else {{$global_terminologies['subcategory']}} @endif</option>
                @if($subCategories)
                @foreach($subCategories as $category)
                <option  value="{{$category->id}}">{{$category->title}}</option>
                @endforeach
                @endif
              </select>
            @else
              <span class="category-span m-l-15" id="category_id"  data-fieldvalue="{{@$product->productSubCategory->title}}">Select @if(!array_key_exists('subcategory', $global_terminologies)) Sub-Category @else {{$global_terminologies['subcategory']}} @endif</span>
              <select name="category_id" class="selectFocus form-control category-id d-none">
                <option>Choose @if(!array_key_exists('category', $global_terminologies)) Category @else {{$global_terminologies['category']}} @endif</option>
              </select>
            @endif

          @else

          <span class="m-l-15 selectDoubleClick" id="category_id"  data-fieldvalue="{{@$product->productSubCategory->title}}">{{@$product->productSubCategory->title}}</span>
          @if($product->primary_category != NULL)
          <select name="category_id" class="selectFocus form-control category-id d-none">
            <option>Choose  @if(!array_key_exists('subcategory', $global_terminologies)) Sub-Category @else {{$global_terminologies['subcategory']}} @endif </option>
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
          <td class="fontbold text-nowrap">{{$global_terminologies['type']}} <b style="color: red;">*</b></td>

          <td class="text-nowrap">
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
        </tr>

         <tr>
          <td class="fontbold text-nowrap">{{$global_terminologies['brand']}} </td>

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
          <td class="fontbold text-nowrap">{{$global_terminologies['temprature_c']}} </td>
          <td class="text-nowrap">
          <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="{{@$product->name}}">
          {{(@$product->product_temprature_c!=null)?@$product->product_temprature_c:'--'}}
          </span>

          <input type="number" name="product_temprature_c" class="fieldFocus d-none" value="{{(@$product->product_temprature_c!=null)?$product->product_temprature_c:''}}">
          </td>
        </tr>

        <tr>
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
          <td class="fontbold text-nowrap">{{$global_terminologies['avg_units_for-sales']}}</td>
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
          <td class="fontbold">VAT <b style="color: red;">*</b></td>
          <td class="text-nowrap">
            <span class="m-l-15 inputDoubleClick" id="vat"  data-fieldvalue="{{@$product->vat}}">
            {{(@$product->vat!=null)?@$product->vat:'--'}}</span>

            <input type="number" name="vat" class="fieldFocus d-none" value="{{(@$product->vat!=null)?@$product->vat:''}}">%
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Import Tax (Book) <b style="color: red;">*</b></td>
          <td>
              <span class="m-l-15 inputDoubleClick" id="import_tax_book"  data-fieldvalue="{{@$product->import_tax_book}}">{{(@$product->import_tax_book!=null)?@$product->import_tax_book:'--'}}</span>
              <input type="number" style="width:80%;" name="import_tax_book" class="fieldFocus d-none" value="{{@$product->import_tax_book}}">%
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

<div class="col-lg-4">

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
              <!-- <option>Choose supplier</option> -->
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
              <!-- <option>Choose supplier</option> -->
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
          <td class="fontbold">{{$global_terminologies['suppliers_product_reference_no']}}</td>
          <td class="">
            <span class="m-l-15 {{$disableCheck}}" id="product_supplier_reference_no"  data-fieldvalue="{{@$default_or_last_supplier->product_supplier_reference_no}}">{{(@$default_or_last_supplier->product_supplier_reference_no!=null)?@$default_or_last_supplier->product_supplier_reference_no:'--'}}</span>
            <input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->product_supplier_reference_no}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['purchasing_price']}} (EUR) </td>
          <td class="text-nowrap">
            <span class="m-l-15 {{$disableCheck}}" id="buying_price"  data-fieldvalue="{{@$default_or_last_supplier->buying_price}}">{{(@$default_or_last_supplier->buying_price!=null)?@$default_or_last_supplier->buying_price:'--'}}</span>
            <input type="number" style="width:100%;" name="buying_price" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->buying_price}}">
            </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['freight_per_billed_unit']}}</td>
          <td class="text-nowrap">
            <span class="m-l-15 {{$disableCheck}}" id="freight"  data-fieldvalue="{{@$default_or_last_supplier->freight}}">{{(@$default_or_last_supplier->freight!=null)?@$default_or_last_supplier->freight:'--'}}</span>
            <input type="number" style="width:100%;" name="freight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->freight}}">
            </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['landing_per_billed_unit']}}</td>
          <td>
            <span class="m-l-15 {{$disableCheck}}" id="landing"  data-fieldvalue="{{@$default_or_last_supplier->landing}}">{{(@$default_or_last_supplier->landing!=null)?@$default_or_last_supplier->landing:'--'}}</span>
            <input type="number" style="width:100%;" name="landing" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->landing}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">Import Tax (Actual)</td>
          <td class="text-nowrap">
            <span class="m-l-15 {{$disableCheck}}" id="import_tax_actual"  data-fieldvalue="{{@$default_or_last_supplier->import_tax_actual}}">{{(@$default_or_last_supplier->import_tax_actual!=null)?@$default_or_last_supplier->import_tax_actual:'--'}}</span>
            <input type="number" style="width:100%;" name="import_tax_actual" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->import_tax_actual}}">%
            </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['gross_weight']}}</td>
          <td class="text-nowrap">
            <span class="m-l-15 {{$disableCheck}}" id="gross_weight"  data-fieldvalue="{{@$default_or_last_supplier->gross_weight}}">{{(@$default_or_last_supplier->gross_weight!=null)?@$default_or_last_supplier->gross_weight:'--'}}</span>
            <input type="number" style="width:100%;" name="gross_weight" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->gross_weight}}">
            </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['expected_lead_time_in_days']}} </td>
          <td>
            <span class="m-l-15 {{$disableCheck}}" id="leading_time"  data-fieldvalue="{{@$default_or_last_supplier->leading_time}}">{{(@$default_or_last_supplier->leading_time!=null)?@$default_or_last_supplier->leading_time:'--'}}</span>
            <input type="number" style="width:100%;" name="leading_time" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->leading_time}}">
          </td>
        </tr>

        <tr>
          <td class="fontbold">{{$global_terminologies['minimum_order_quantity']}} </td>
          <td>
            <span class="m-l-15 {{$disableCheck}}" id="m_o_q"  data-fieldvalue="{{@$default_or_last_supplier->m_o_q}}">{{(@$default_or_last_supplier->m_o_q!=null)?@$default_or_last_supplier->m_o_q:'--'}}</span>
            <input type="number" style="width:100%;" name="m_o_q" class="fieldFocusFirst d-none" value="{{@$default_or_last_supplier->m_o_q}}">
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="bg-white mt-3 pt-3 pl-2">
    <table id="example" class="table-responsive headings-color table sales-customer-table dataTable const-font" style="width: 100%;">
      <tbody>

        <tr>
          <td class="fontbold text-nowrap">Total Billed Unit Cost Price <i class="fa fa-question-circle-o buy_unit_cost_price_mark" aria-hidden="true"></i>
            <div class="tooltiptext">{{@$IMPcalculation}}</div>
          </td>
          <td class="text-nowrap">
            <span class="m-l-15" id="total_buy_unit_cost_price" data-fieldvalue="{{@$product->total_buy_unit_cost_price}}">{{(@$product->total_buy_unit_cost_price!=null)?number_format((float)@$product->total_buy_unit_cost_price, 2, '.', ''):'0'}}</span>
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">{{$global_terminologies['unit_conversion_rate']}}<b style="color: red;">*</b></td>
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

  <div class="col-lg-6 headings-color">

  <div class="col-lg-12 pl-0 pr-0">
  <h4 class="pb-2">Stock Card</h4>
 </div>

    <!-- Nav tabs -->
  <ul class="nav nav-tabs">
  @foreach($warehouses as $key => $warehouse)
    <li class="nav-item">
      <a class="nav-link click-nav @if($key == 0) active @endif" data-toggle="tab" data-id="{{$warehouse->id}}" href="#id{{$warehouse->id}}">{{ $warehouse->name }}</a>
    </li>
  @endforeach
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
  @foreach($warehouses as $key => $wh)
  <div class="container tab-pane p-0 po-pod-list @if($key == 0) active @endif" id="id{{$wh->id}}">
      <div class="bg-white table-responsive h-100" style="min-height: 235px;">
        <table class="table headings-color table-po-pod-list" style="width: 100%;">
          <thead class="">
            <!-- <tr>
              <th>PO#</th>
              <th>Date</th>
              <th>In</th>
              <th>Out</th>
              <th>Balance</th>
              <th>Action</th>
            </tr> -->
          </thead>
          <tbody>
            @if($stock_card->count() > 0)
            @foreach($stock_card as $card)
              @if($wh->id == $card->warehouse_id)
                <tr class="header">
                  <td>PO # {{$card->PurchaseOrder->id}}</td>
                  <td>IN: {{$card->quantity_received != null ? $card->quantity_received : 0}}</td>
                  <td>0</td>
                  <td>EXP: {{Carbon::parse(@$card->created_at)->format('d/m/Y')}}</td>
                  <td>{{($card->quantity_received != NULL ? $card->quantity_received : 0)}}</td>
                  <td>

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
                  </tr>                  <tr>
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

  <div class="col-lg-6">

  <div class="col-lg-12 pl-0 pr-0">
  @php
  if(@$CustomerTypeProductMarginCount == 2)
  {
    $class = "d-none";
  }
  @endphp
  <p class="{{@$class}}">
    <button class="btn btn-color add-btn pull-right p-0" data-toggle="modal" data-target="#addProdMarginsModal"><i class="fa fa-plus"></i> Add</button>
  </p>
  <h4 class="pb-2">Product Margins</h4>
 </div>

  <div class="bg-white table-responsive">
    <table id="example" class="table headings-color dot-dash" style="width: 100%;">
      <thead class="sales-coordinator-thead">
        <th></th>
        <th class="float-right">MKT <i class="fa fa-question-circle-o resturent" aria-hidden="true"></i><div class="tooltiptext">This is some dummy text!!!</div></th>


        <th>Resturants</th>
        <th class="float-right">MKT</th>
        <th>Hotels</th>
      </thead>

      <tbody>

        {{--@if($CustomerTypeProductMargin)--}}
        <tr style="background-color: #cccbcb;">
          <td>Category Margin</td>
          <td></td>
          <td>{{$product->customer_type_category_margins[0]->default_value}}%</td>
          <td></td>
          <td>{{$product->customer_type_category_margins[1]->default_value}}%</td>
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
          <td>Default Sale Price</td>
          <td><input type="checkbox" name="customer_type_id" {{$product->customer_type_product_margins[0]->is_mkt == 1 ? 'checked' : ''}} value="1" class="float-right mt-1 market_price_check"></td>
          <td><span class="restaurant rest-sale-price"> {{number_format($product->selling_price+($product->selling_price*($product->customer_type_product_margins[0]->default_value/100)),2,'.',',')}}</span></td>


          <td><input type="checkbox" name="customer_type_id"  {{$product->customer_type_product_margins[1]->is_mkt == 1 ? 'checked' : ''}}  value="2" class="float-right mt-1 market_price_check"></td>
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
  <div class="col-lg-6 headings-color">

  <div class="row">

  <div class="col-lg-10 pl-3 pr-0">
    <h4>Product Suppliers</h4>
  </div>

  <div class="col-lg-1">
    <button class="btn recived-button view-supplier-btn add-bulk-suppliers d-none" type="button" title="Bulk Upload"><i class="fa fa-upload"></i></button>
  </div>

  <div class="col-lg-1">
    <button class="btn recived-button view-supplier-btn add-supplier btns-hide" type="button" title="Add Product Supplier"><i class="fa fa-plus"></i></button>
  </div>

  </div>

  <div class="bg-white pt-3 pl-2">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-product-suppliers text-center purchase-complete-product">
        <thead>
          <tr>
            <th>Action</th>
            <th>Company</th>
            <th>Ref. #</th>
            <th>Import <br> Tax Actual %</th>
            <th>Gross <br> Weight</th>
            <th>Freight</th>
            <th>Landing</th>
            <th>Buying <br> Price</th>
            <th>Lead <br> Days</th>
          </tr>
        </thead>
      </table>
    </div>

  </div>

  </div>
  @endif
  <div class="col-lg-6 headings-color">
    <div class="pt-0 mt-0">

  <!-- Customer Fixed Prices table  -->
  @if($ProductCustomerFixedPrices)
  <div class="row">

  <div class="col-lg-10 pl-3 pr-0">
    <h4>Customer Fixed Prices </h4>
  </div>

  <div class="col-lg-1">

  </div>

  <div class="col-lg-1">
    <button class="btn recived-button btn-color add-btn pull-right add-cust-fp btns-hide" type="button" title="Add Customer Fixed Prices"><i class="fa fa-plus"></i></button>
  </div>

  </div>

  <div class="bg-white table-responsive pt-3 pl-2" style="min-height: 230px;">
    <table id="example" class="table headings-color const-font dot-dash" style="width: 100%;">
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
        <tr id="{{$ProductCustomerFixedPrice->id}}">
          <td>{{$i}}</td>
          <td>{{$ProductCustomerFixedPrice->customers->reference_number}}</td>
          <td>{{$ProductCustomerFixedPrice->customers->company}}</td>
          <td>
            <span class="m-l-15 inputDoubleClickFixedPrice" id="fixed_price"  data-fieldvalue="{{$ProductCustomerFixedPrice->fixed_price}}">{{$ProductCustomerFixedPrice->fixed_price}}</span>
            <input type="number" style="width:50%;" name="fixed_price" class="fieldFocusFixedPrice d-none" value="{{$ProductCustomerFixedPrice->fixed_price}}">
          </td>
          <td>
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
@endsection

@section('javascript')
<script type="text/javascript">

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
    serverSide: true,
    ajax: "{!! url('sales/get-product-suppliers-record') !!}"+"/"+prod_detail_id,
    columns: [
        { data: 'action', name: 'action' },
        { data: 'company', name: 'company' },
        { data: 'product_supplier_reference_no', name: 'product_supplier_reference_no' },
        { data: 'import_tax_actual', name: 'import_tax_actual' },
        { data: 'gross_weight', name: 'gross_weight' },
        { data: 'freight', name: 'freight' },
        { data: 'landing', name: 'landing' },
        { data: 'buying_price', name: 'buying_price' },
        { data: 'leading_time', name: 'leading_time' },
    ],
  });
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
  });

  $(document).ready(function(){
    $('.btns-hide').addClass('d-none');
    $(".ddddd").toggle();
$('.collapse_rows').on('click',function(){
    var id = $(this).data('id');

       $(this).find('#sign'+id).text(function(_, value){return value=='-'?'+':'-'});
    $("#ddddd"+id).toggle();
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



  });

</script>
@stop
