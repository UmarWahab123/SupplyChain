@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

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


.h-100{
  height: 145px !important;
}
.delete-btn-two{
  position: absolute;
  right: 30px;
  top: -10px;
}
th.hide_me, td.hide_me {display: none;}
/*table.dataTable thead .sorting { background: url('public/sort/sort_both.png') no-repeat center right;
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('public/sort/sort_desc.png') no-repeat center right;
  background-size: 5vh;}*/
  @keyframes highlight {
    0% {
        background: #ddd;
    }
    100% {
        background: none;
    }
  }

  .highlight {
    animation: highlight 2s;
  }
.select2-results__option
{
  display: block !important;
  overflow:  hidden !important;
  white-space: nowrap !important;
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
          <li class="breadcrumb-item active">Complete Products</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}

<!-- Sales or Sales Coordinator or Warehouse -->
@if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6)
  @php
    $price_col_visibility = "class=noVis";
    $hide_pricing_columns = ', visible : false';
  @endphp
@endif

@if(Auth::user()->role_id == 6)
  @php
    $restaruant_price_col_visibility = "class=noVis";
    $restaruant_hide_pricing_columns = ', visible : false';
  @endphp
@endif

@if(@$hide_hs_description == 1)
  @php
    $hs_description_config = "class=noVis";
    $hs_description_column = ', visible : false';
  @endphp
@endif

  <!-- Header is here -->



  <div class="row align-items-center mb-3">
    <div class="col-lg-8 col-md-8 col-6 title-col">
      <h4 class="maintitle">COMPLETE PRODUCTS </h4>
    </div>
    <div class="col-lg-4 col-md-4 col-6">
      <div class="row">
        <div class="col">
          <div class="pull-right">

          <span class="export-btn-pos vertical-icons mr-4 d-none" title="Create POS Products Export">
                <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
            </span>

            <span class="export-btn-pos-notes vertical-icons mr-4 d-none" title="Create POS Notes Export">
                <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
              </span>

            <span class="export-btn vertical-icons mr-4" title="Create New Export">
              <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
            </span>


        <!-- </div>
        <div class="col"> -->
          <span class="add_product_to_excel_download vertical-icons" title="Export Web Format">
              <img src="{{asset('public/icons/export_icon.png')}}" width="27px"> <span style="margin-left: 7px;margin-top: 5px;">(<span class="add_product_to_excel_download_span">0</span>)</span>
            </span>
        </div>
        </div>
      </div>

    </div>
    <div class="col-lg-2 col-md-2">
      <input type="button" value="Price Check" class="btn recived-button price-check-btn d-none">
      <input type="button" value="Update Order Qty" class="btn recived-button update-billed-btn d-none">
    </div>
  </div>
  <div class="row align-items-center form-row mb-2 filters_div">
    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>Supplier</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags default_supplier" name="default_supplier">
        <option value="" selected="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
        @foreach($suppliers as $supplier)
          @if(session('id'))
          <option {{($supplier->id==$selected_supplier ? "selected":'') || ($supplier->id == session('id') ? "selected" : '') }} value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
          @else
          <option {{($supplier->id==$selected_supplier ? "selected":'')}} value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
          @endif
        @endforeach
      </select>
    </div>
    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>Primary {{$global_terminologies['category']}}</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_category_primary" name="prod_category_primary" title="Primary Category">
        <option value="" selected="">Choose Primary {{$global_terminologies['category']}}</option>
        @foreach($product_parent_categories as $ppc)
          <option  title = "{{$ppc->title}}" value="{{'pri-'.$ppc->id}}" {{$ppc->id==$primary_category ? "selected" : ''}}>{{$ppc->title}}{!! $extra_space !!}{{$ppc->get_Child != null ? $ppc->get_Child->pluck('title') : ''}}</option>
          @foreach($ppc->get_Child as $sc)
            <option title="{{$sc->title}}" value="{{'sub-'.$sc->id}}"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$sc->title}}
              {!! $extra_space !!} {{$ppc->title}} </option>
          @endforeach
          @endforeach
      </select>
    </div>
    <!-- <div class="col-lg-2 col-md-2">
      <label for=""><b>{{$global_terminologies['subcategory']}}</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_category" name="prod_category">
        <option value="" selected="">Choose {{$global_terminologies['subcategory']}}</option>
          @foreach($product_sub_categories as $pc_child)
            <option {{$pc_child->title==$sub_category ? "selected" : ''}} value="{{$pc_child->title}}" > {{$pc_child->title}} </option>
          @endforeach
      </select>
    </div> -->

    <!-- <div class="col-lg-2 col-md-2 mb-2">
      <label><b>Choose Product Category</b></label>
         <div class="border rounded position-relative custom-input-group autosearch">
          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_product_search" tabindex="0" name="prod_name" placeholder="Choose Primary/Sub Category" autocomplete="off" value="{{$product_category_title}}" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
        </div>
        <span id="loader__custom_search" class="position-absolute d-none" style="right:27px;top:43%;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows custom__search_arrows2" aria-hidden="true"></i>
        <p id="myIddd" class="m-0"></p>
      </div> -->

    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>{{$global_terminologies['type']}}</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_type" name="prod_type bs2">
        <option value="" selected="">Choose {{$global_terminologies['type']}}</option>
        @foreach($product_types as $product_type)
              <option {{$product_type->id==$prod_type ? "selected" : ''}} value="{{$product_type->id}}" > {{$product_type->title}} </option>
        @endforeach
      </select>
    </div>
    @if (in_array('product_type_2', $product_detail_section))
    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_type_2" name="prod_type_2 bs2">
        <option value="" selected="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
        @foreach($product_types_2 as $product_type)
              <option {{$product_type->id==$prod_type_2 ? "selected" : ''}} value="{{$product_type->id}}" > {{$product_type->title}} </option>
        @endforeach
      </select>
    </div>
    @endif

    @if (in_array('product_type_3', $product_detail_section))
    <div class="col-lg-2 col-md-2 col-6">
        <label for=""><b>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</b></label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_type_3" name="prod_type_3 bs3">
          <option value="" selected="">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
          @foreach($product_types_3 as $product_type)
                <option {{$product_type->id==$prod_type_3 ? "selected" : ''}} value="{{$product_type->id}}" > {{$product_type->title}} </option>
          @endforeach
        </select>
    </div>
    @endif

    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>Filter</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags filter-dropdown" name="filter">
        <option value="" selected="">Select a Filter</option>
        <option {{"stock"==$filter ? "selected" : ''}} value="stock">In Stock</option>
        <option {{"reorder"==$filter ? "selected" : ''}} value="reorder">Reorder Items</option>
      </select>
    </div>
    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>Select Supplier Country</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags supplier_country" name="supplier_country">
        <option value="" selected="">Select Supplier Country</option>
        @foreach ($countries as $country)
        <option {{$country->id==$supplier_country ? "selected" : ''}} value="{{ $country->id }}">{{ $country->name }}</option>
        @endforeach
      </select>
    </div>
    @if($ecommerceconfig_status == 1)
    <div class="col-lg-2 col-md-2 col-6">
      <label for=""><b>E-Commerce Filter</b></label>
      <select class="font-weight-bold form-control-lg form-control js-states state-tags ecom-filter" name="ecom-filter">
        @if(Auth::user()->role_id == 9)
          <option {{"all"==$ecom_filter ? "selected" : ''}} value="all">All</option>
          <option value="ecom-enabled" selected="">Ecommerce Enabled</option>
          <option {{"ecom-disable"==$ecom_filter ? "selected" : ''}} value="ecom-disable">Ecommerce Disabled</option>
        @else
          <option value="all" selected="">All</option>
          <option {{"ecom-enabled"==$ecom_filter ? "selected" : ''}} value="ecom-enabled">Ecommerce Enabled</option>
          <option {{"ecom-disable"==$ecom_filter ? "selected" : ''}} value="ecom-disable">Ecommerce Disabled</option>
        @endif
      </select>
    </div>
    @else
    <div class="col-lg-2 col-md-2 col-6"></div>
    @endif
  </div>
  {{-- column for input create  --}}
  @if(Auth::user()->role_id ==10)
  <div class="row align-items-center form-row mb-2 filters_div">
    <div class="col-lg-2 col-md-2 col-6">
        <div class="form-group">
          <label for=""><b>From Date</b></label>
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off" value="{{$from_date ? $from_date:'' }}">
        </div>
      </div>

      <div class="col-lg-2 col-md-2 col-6">
        <div class="form-group">
          <label for=""><b>To Date</b></label>
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off" value="{{$to_date ? $to_date:'' }}">
        </div>
      </div>
  </div>
  @endif

  <div class="row align-items-center mb-4 mt-4">
    @if($total_system_units == 1)
      <div class="col ml-auto d-none">
        <b>Total Unit:</b> <span id="total_unit">--</span>
      </div>
    @endif

    <div class="col ml-auto">
      <!-- <b class="float-right download-btn-text" ><i>Last created on:  @if($last_downloaded!=null){{Carbon::parse(@$last_downloaded)->format('d/m/Y H:i:s')}} @endif</i> </b> -->
    </div>
    <!-- <div class="col-xl-2 col-lg-3 col">
      <a download href="{{'storage/app/Completed-Product-Report.xlsx'}}"  class="btn download-btn recived-button rounded-0">Download</a>
    </div> -->
    <!-- <div class="col-xl-2 col-lg-3 col">
      <button type="button"  class="btn recived-button apply_filters">Apply Filters</button>
    </div> -->
    <div class="col-xl-8 col-lg-12 col-6">
      <!-- <button value="Export" class="btn  recived-button export-btn">Create New Export</button>  -->
      <div class="pull-right">
       <!--  <b class="download-btn-text mr-4" ><i>Last created on:  @if($last_downloaded!=null){{Carbon::parse(@$last_downloaded)->format('d/m/Y H:i:s')}} @endif</i> </b>

      <a class="vertical-icons" href="{{ url('get-download-xslx','Completed-Product-Report.xlsx')}}" target="_blank" title="Download" id=""> -->
       <!-- <a download href="{{asset('storage/app/Sold-Products-Report.xlsx')}}" class="download-btn vertical-icons" id="" title="Download"> -->
        <!-- <span class="">
          <img src="{{asset('public/icons/download.png')}}" width="27px">
        </span>
      </a> -->

      <span class="apply_filters vertical-icons mr-4" title="Apply Filters">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
      </span>

        <span class="reset-btn vertical-icons mr-4" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      {{-- <button id="export_complete_product" class="btn recived-button " >Export</button>  --}}
    </div>
    </div>
    <!-- <div class="col-xl-2 col-lg-3 col"> -->
      <!-- <button type="button"  class="btn recived-button reset-btn">Reset</button> -->
    <!-- </div> -->

  </div>

<div class="row entriestable-row">
  <div class="col-12">
    <div id="ordered_products_alert"></div>
  </div>
  <!-- End -->
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="delete-selected-item catalogue-btn-group d-none">
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)
        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg deactivate-btn d-none" data-toggle="tooltip" title="Deactivate Selected Items"><i class="fa fa-times"></i></a>
      @endif
        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn ecommerce-products-disabled d-none" data-toggle="tooltip" title="Unpublish Selected Items"><img src="{{asset('public/menu-icon/unpublished.png')}}" alt="" width='15' class="img-fluid"></a>

        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg ecommerce-products-enabled d-none" data-toggle="tooltip" title="Click to Enable Selected Products to Ecommerce"><i class="fa fa-globe"></i></a>

        @if($deployment != null && @$deployment->status == 1 && (Auth::user()->role_id == 1 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11))

        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg woocommerce-products-enabled" data-toggle="tooltip" title="Click to Enable Selected Products to Woocommerce"><i class="fa fa-globe"></i></a>
        @endif

        <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg print_btn d-none ecommerce-products-disabled" data-toggle="tooltip" title="Click to print the Products"><i class="fa fa-print"></i></a>


        <!-- <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg refresh-stock d-none" data-toggle="tooltip" title="Click to refresh product stock !!!">
          Refresh Stock
        </a> -->

        <!-- <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg add_product_to_excel" data-toggle="tooltip" title="Click to add products to excel for export !!!">
          Add To excel for download
        </a> -->
        <!-- <a href="javascript:void(0);" class="btn selected-item-btn btn-sm deleteBtnImg" data-toggle="tooltip" title="Click to add products to excel for export !!!" style="border: none;font-size: 14px;font-weight: bold;">
          Items selected : <span class="add_product_to_excel_download_span">0</span>
        </a> -->

      </div>

      <div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is being prepared! Please wait.. </b>
</div>
<div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>

<div class="form-group mb-2 mt-2 d-flex align-items-center justify-content-start pos-prod-div">
    @php $cod = DNS2D::getBarcodeHTML(url('get-download-xslx','Pos-products-export.xlsx'),'QRCODE',2,2,'black', true); @endphp
   <div class="d-none export-alert-success-pos" style="margin-right: 15px;">{!! $cod !!}</div>
    <p class="m-0 d-none export-text">Scan to download excel file.</p>
</div>

<div class="form-group mb-2 mt-2 d-flex align-items-center justify-content-start">
    @php $cod = DNS2D::getBarcodeHTML(url('get-download-xslx','Pos-notes-export.xlsx'),'QRCODE',2,2,'black', true); @endphp
   <div class="d-none export-alert-success-pos-notes" style="margin-right: 15px;">{!! $cod !!}</div>
    <p class="m-0 d-none export-text-pos">Scan to download excel file.</p>
</div>

<div class="alert alert-success export-alert-success d-none"  role="alert">
<i class=" fa fa-check "></i>

  <b>Export file is ready to download.
  <!-- <a download href="{{'storage/app/Completed-Product-Report.xlsx'}}"><u>Click Here</u></a> -->
    <a class="exp_download" href="{{ url('get-download-xslx','Completed-Product-Report.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
  </b>
</div>

<div class="alert alert-success export-alert-success-user d-none"  role="alert">
<i class=" fa fa-check "></i>

  <b>Export file is ready to download.
  <!-- <a download href="{{'storage/app/Completed-Product-Report.xlsx'}}"><u>Click Here</u></a> -->
    <a class="exp_download" href="{{ url('get-download-xslx','User-Selected-Products.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
  </b>
</div>
<!--   <button class="btn btn-primary mb-2 table-reload" style="border-radius: 0px;" title="Click to update total visible stock column"><i class="fa fa-refresh"></i></button> -->
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-product text-center purchase-complete-product" >
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <th class="noVis">Action</th>
            <th><span id="pf_length">{{$global_terminologies['our_reference_number'] }}</span>
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$hs_description_config}}>HS Description
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="hs_description">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="hs_description">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th> @if(!array_key_exists('suppliers_product_reference_no', $global_terminologies)) Sup <br> Reference <br> # @else {{$global_terminologies['suppliers_product_reference_no']}} @endif
               <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_ref_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_ref_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            {{--<th class="">HS Code</th>--}}
            <th>{{$global_terminologies['category']}}/ {{$global_terminologies['subcategory']}}
               <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="category_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="category_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="nowrap">{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="5">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="5">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['note_two']}}
             <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="6">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="6">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['product_note_3']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="7">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="7">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
            </th>
            <th>Picture</th>
            <th>Billed <br> Unit
               <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="billed_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="billed_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Selling <br> Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['type']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="9">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="9">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th @if (!in_array('product_type_2', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="11">
              <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
            </span>
            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="11">
              <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
            </span>
            </th>

            <th @if (!in_array('product_type_3', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="12">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="12">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>


            <th> {{$global_terminologies['brand']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="10">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="10">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <th>{{$global_terminologies['temprature_c']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="temprature_c">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="temprature_c">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['extra_tax_per_billed_unit']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="extra_tax">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="extra_tax">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>Import <br> Tax<br>(Book) %
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="import_tax_book">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="import_tax_book">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>VAT
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Default/Last <br> Supplier
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="14">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="14">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Supplier Country
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_country">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_country">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
            <th>{{$global_terminologies['supplier_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="15">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="15">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['purchasing_price']}} <br>(EUR)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_price_eur">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_price_eur">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['purchasing_price']}} <br> (THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_price_thb">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_price_thb">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>Freight <br>Per Buying <br> Unit (THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="freight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="freight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>Landing <br> Per Buying <br> Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="landing">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="landing">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['cost_price']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="cost_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="cost_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th id="unit_con">{{$global_terminologies['unit_conversion_rate']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_conversion_rate">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_conversion_rate">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th {{@$price_col_visibility}}>{{$global_terminologies['net_price']}} <br>/unit (THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="net_price_per_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="net_price_per_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th id='avg_length'>{{$global_terminologies['avg_units_for-sales']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th id="exp_length">{{$global_terminologies['expected_lead_time_in_days']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="lead_time">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="lead_time">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Last Update Price
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="last_update_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="last_update_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Total Visible Stock
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_visible_stock">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_visible_stock">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>On Water
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="on_water">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="on_water">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{@$global_terminologies['qty_ordered'] != null ? @$global_terminologies['qty_ordered'] : 'Qty Ordered'}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="on_supplier">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="on_supplier">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>Title</th>
            <th>Min Order Qty</th>
            <th>Max Order Qty</th>
            <th class="nowrap">Dimension (L x W x H)</th>
            <th>E-commerce <br>Product <br>weight per unit</th>
            <th>E-commerce <br>Long <br>Description</th>
            <th>E-Com Selling <br>Price</th>
            <th>E-Com Discount <br>Price</th>
            <th>E-Com Discount <br>Expiry</th>
            <th>E-Com Selling <br>Unit</th>
            <th>E-Com Selling Unit <br>Conversion Rate</th>
            <th>E-Com COGS <br>Price</th>
            <th>E-Com Status</th>
            {{-- <th>QTY Ordered</th> --}}
            <!-- <th>On Airplane</th>
            <th>On Delivery</th> -->
            @php $i = 49; @endphp
            @if($getWarehouses->count() > 0)
            @foreach($getWarehouses as $warehouse)
              <th>{{$warehouse->warehouse_title}}<br>{{$global_terminologies['current_qty']}}
                <span class="arrow_up sorting_filter_table" data-id="{{$warehouse->id}}" data-order="2" data-column_name="{{$i}}">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-id="{{$warehouse->id}}" data-order="1" data-column_name="{{$i}}">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>{{$warehouse->warehouse_title}}<br> Available <br>QTY
                <span class="arrow_up sorting_filter_table" data-id="{{$warehouse->id}}" data-order="2" data-column_name="available-qty-{{$i}}">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-id="{{$warehouse->id}}" data-order="1" data-column_name="available-qty-{{$i}}">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
              </th>

              <th>{{$warehouse->warehouse_title}}<br> Reserved <br>QTY
                <span class="arrow_up sorting_filter_table" data-id="{{$warehouse->id}}" data-order="2" data-column_name="reserve-qty-{{$i}}">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-id="{{$warehouse->id}}" data-order="1" data-column_name="reserve-qty-{{$i}}">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
              </th>

            @php $i = $i+2; @endphp
            @endforeach
            @endif

            @php $j = $i; @endphp
            @if($getCategories->count() > 0)
            @foreach($getCategories as $cat)
              <th>{{$cat->title}}<br>( Fixed Price )
                <span class="arrow_up sorting_filter_table" data-id="{{$cat->id}}" data-order="2" data-column_name="fixed-price-{{$j}}">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-id="{{$cat->id}}" data-order="1" data-column_name="fixed-price-{{$j}}">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
              </th>
            @php $j++; @endphp
            @endforeach
            @endif

            @if($getCategoriesSuggested->count() > 0)
            @foreach($getCategoriesSuggested as $cat)
              <th>{{$cat->title}}<br>( Suggested Price )
                <span class="arrow_up sorting_filter_table" data-id="{{$cat->id}}" data-order="2" data-column_name="suggested-price-{{$j}}">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-id="{{$cat->id}}" data-order="1" data-column_name="suggested-price-{{$j}}">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
              </th>
            @php $j++; @endphp
            @endforeach
            @endif
          </tr>
        </thead>
        <tfoot align="right">
          <tr>
            <th>Total:</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <!-- <th></th>
            <th></th> -->
            @foreach($getWarehouses as $warehouse)
            <th></th>
            <th></th>
            <th></th>
            @endforeach

            @foreach($getCategories as $cat)
            <th></th>
            @endforeach

            @foreach($getCategoriesSuggested as $cat)
            <th></th>
            @endforeach
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  </div>
</div>

{{-- Upload excel file --}}

<div class="modal fade" id="uploadExcel">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Upload Excel</h3>
        <div class="mt-3">
          <form method="post" action="{{url('upload-bulk-product')}}" class="upload-excel-form" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group">
              <a href="{{asset('public/site/assets/purchasing/product_excel/Bulk_Products.xlsx')}}" download><span class="btn btn-success" id="examplefilebtn">Download Example File</span></a>
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
          <div class="row fetched-images">
          </div>
        </div>
        <div class="modal-footer">
         <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
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
            <!-- <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="col-md-12 col-lg-12 col-xs-12" id="columns">
                  <h3 class="form-label">Select the images</h3>
                  <div class="desc"><p class="text-center">or drag to box</p></div>
                  <div id="uploads" class="row"></div>
                </div>
                <div class="clearfix"></div>
              </div>
            </div> -->
            <div class="row">
            <input type="hidden" name="product_id_for_cropping" class="product_id_for_cropping">
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

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="product_id" class="img-product-id">
       <!--  <button class="btn btn-danger" id="reset" type="button" ><i class="fa fa-history"></i> Clear</button>
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-upload"></i> Upload </button> -->
        <button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>
<!--  Content End Here -->

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

      <input type="hidden" name="product_id" value="">

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
          <label class="pull-left">{{$global_terminologies['suppliers_product_reference_no']}} <b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Product Supplier Ref#." name="product_supplier_reference_no" type="text">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label class="pull-left">Import Tax Actual </label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Import Tax Actual" name="import_tax_actual" type="number">
        </div>

        <div class="form-group col-6">
          <label class="pull-left">{{$global_terminologies['gross_weight']}} <b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Gross Weight" name="gross_weight" type="number">
        </div>
      </div>

      <div class="form-row">
      <div class="form-group col-6">
        <label class="pull-left">{{$global_terminologies['freight_per_billed_unit']}}</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Freight" name="freight" type="number">
      </div>

      <div class="form-group col-6">
        <label class="pull-left">{{$global_terminologies['landing_per_billed_unit']}}</label>
        <input class="font-weight-bold form-control-lg form-control" placeholder="Landing" name="landing" type="number">
      </div>
      </div>

      <div class="form-row">
        <div class="form-group col-6">
          <label class="pull-left">{{$global_terminologies['purchasing_price']}} (EUR)  <b style="color: red;">*</b></label>
          <input class="font-weight-bold form-control-lg form-control" placeholder="Purchasing Price (EUR) " name="buying_price" type="number">
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

<form id="export_complete_products_form" >
  <input type="hidden" name="default_supplier_exp" id="default_supplier_exp">
  <input type="hidden" name="prod_category_primary_exp" id="prod_category_primary_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="prod_type_exp" id="prod_type_exp">
  <input type="hidden" name="prod_type_2_exp" id="prod_type_2_exp">
  <input type="hidden" name="prod_type_3_exp" id="prod_type_3_exp">
  <input type="hidden" name="filter-dropdown_exp" id="filter-dropdown_exp">
  <input type="hidden" name="ecom-filter_exp" id="ecom-filter_exp">
  <input type="hidden" name="type" id="type" value=1>
  <input type="hidden" name="search_value" id="search_value">
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="product_id_select" id="product_id_select" value="{{$product_category}}">
  <input type="hidden" name="className" id="className" value="{{$className}}">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
  <input type="hidden" name="data_id" id="data_id">
  <input type="hidden" name="product_export_button" id="product_export_button">
  <input type="hidden" name="supplier_country" id="supplier_country" value="{{ $supplier_country }}">
</form>
<input type="hidden" class="products_selected">

  <div id='printarea'>

  </div>
@endsection

@php
  $hidden_by_default = '';
@endphp
@section('javascript')
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
<script type="text/javascript">
  var add_to_excel_products = [];
  // Customer Sorting Code Here
  var order = 1;
  var column_name = '';
  var data_id = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    data_id = $(this).data('id');

    $('.table-product').DataTable().ajax.reload();

    if($(this).data('order') ==  '2')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == '1')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });

  //code for adding product to excel for download
    // var add_to_excel_products = [];
    $('.add_product_to_excel').on('click',function(){
      var added = false;
      $("input.check:checked").each(function() {
        if(add_to_excel_products.indexOf($(this).val()) === -1)
        {
          added = true;
          add_to_excel_products.push($(this).val());
        }
      });
      $('.add_product_to_excel_download_span').html(add_to_excel_products.length);
      if(added)
      {
        toastr.success('Success!', 'Products Added to excel !!!' ,{"positionClass": "toast-bottom-right"});
      }
    });

    //call for export
    $(document).on('click','.add_product_to_excel_download',function(e){
      $.ajax({
        method:"get",
        data:'selected_products='+add_to_excel_products,
        url:"{{ route('export-status-for-ecom-products-excel') }}",
        beforeSend:function(){

        },
        success:function(data){
          // alert('testtest');
          if(data.status==1)
          {
            $('.export-alert-success-user').addClass('d-none');
            $('.export-alert').removeClass('d-none');
            $('.add_product_to_excel_download').attr('title','EXPORT is being Prepared');
            $('.add_product_to_excel_download').prop('disabled',true);
            console.log("Calling Function from first part");
            checkStatusForProductsExcel();
          }
          else if(data.status==2)
          {
            $('.export-alert-another-user').removeClass('d-none');
            $('.export-alert').addClass('d-none');
            $('.add_product_to_excel_download').prop('disabled',true);
            $('.add_product_to_excel_download').attr('title','EXPORT is being Prepared');
            checkStatusForProductsExcel();
          }

        },
        error: function (request, status, error) {
            toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});
        }
     });
    });

    function checkStatusForProductsExcel()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-for-ecom-products-excel')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForProductsExcel();
            }, 5000);
        }
        else if(data.status==0)
        {
          // add_to_excel_products = [];
          // $('.add_product_to_excel_download_span').html(add_to_excel_products.length);
          $('.export-alert-success-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.add_product_to_excel_download').attr('title','Export Web Format');
          $('.add_product_to_excel_download').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success-user').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.add_product_to_excel_download').attr('title','Export Web Format');
          $('.add_product_to_excel_download').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          // add_to_excel_products = [];
          // $('.add_product_to_excel_download_span').html(add_to_excel_products.length);
          console.log(data.exception);
        }
      }
    });
  }
  //end here

  $(document).ready(function(){
    $('.table-reload').on('click',function(){
      $('.table-product').DataTable().ajax.reload();
    });
    var ref = '{{$global_terminologies['our_reference_number']}}';
    var words = $.trim(ref).split(" ");
    var newName = [];
    if(words.length > 1){
      for(var i=0; i< words.length; i++){
        if(i > 0){
          newName.push("<br />");
        }
        newName.push(words[i]);

      }
      ref = newName.join("");
    }
    $('#pf_length').html(ref);

    var avg_weight = '{{$global_terminologies['avg_units_for-sales']}}';
    var word = $.trim(avg_weight).split(" ");
    var newWord = [];
    if(word.length > 1){
      for(var i=0; i< word.length; i++){
        if(i > 0 && (i%3) == 0){
          newWord.push("<br />");
        }
        newWord.push(word[i]);
      }
      avg_weight = newWord.join(" ");
    }
    $('#avg_length').html(avg_weight);

    var exp_lead = '{{$global_terminologies['expected_lead_time_in_days']}}';
    var wordss = $.trim(exp_lead).split(" ");
    var newWords = [];
    if(wordss.length > 1){
      for(var i=0; i< wordss.length; i++){
        if(i > 0 && (i%2) == 0){
          newWords.push("<br />");
        }
        newWords.push(wordss[i]);
      }
      exp_lead = newWords.join(" ");
    }
    $('#exp_length').html(exp_lead);

    var unit_conv = '{{$global_terminologies['unit_conversion_rate']}}';
    var word_unit = $.trim(unit_conv).split(" ");
    var new_unit = [];
    if(word_unit.length > 1){
      for(var i=0; i< word_unit.length; i++){
        if(i > 0){
          new_unit.push("<br />");
        }
        new_unit.push(word_unit[i]);
      }
      unit_conv = new_unit.join("");
    }
    // unit_conv += '<span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_conversion_rate">';
    // unit_conv += '<img src="{{url("public/svg/up.svg")}}" alt="up" style="width:10px; height:10px; cursor: pointer;">';
    // unit_conv +='</span>';
    // unit_conv += '<span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_conversion_rate">';
    // unit_conv += '<img src="{{url("public/svg/down.svg")}}" alt="down" style="width:10px; height:10px; cursor: pointer;">';
    // unit_conv += '</span>';
    $('#unit_con').html(unit_conv);
  });
  var scrollPos = 0;
  $(function(e){


    $(".state-tags").select2();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    var table2 = $('.table-product').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
    searching:true,
    processing: false,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    colReorder: {
      realtime: false
    },
    ordering: false,
    retrieve: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    serverSide: true,
    fixedHeader: false,
    dom: 'Blfrtip',
    "columnDefs": [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      // { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,24,26,27,28,29,30,31,32,33] },
      // { className: "dt-body-right", "targets": [18,19,20,21,22,23,25 ] },

    ],
    // scroller: true,
    // paging: true,
    pageLength: {{50}},
    lengthMenu: [ 50, 100, 150, 200],
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
    ajax: {
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
        scrollPos = $('.table-product').DataTable().scroller().pixelsToRow($('.dataTables_scrollBody').scrollTop());
      },
      url:"{!! route('get-product') !!}",
      // data: function(data) { data.default_supplier = $('.default_supplier option:selected').val(), data.prod_type = $('.prod_type option:selected').val(),
      //     data.prod_category = $('.prod_category option:selected').val(),
      //     data.prod_category_primary = $('.prod_category_primary option:selected').val(),
      //     data.from_date=$("#from_date").val(),
      //     data.to_date=$("#to_date").val(),
      //     data.filter = $('.filter-dropdown option:selected').val(),
      //     @if($ecommerceconfig_status == 1)
      //     data.ecomFilter = $('.ecom-filter option:selected').val(),
      //     @endif
      //     data.sortbyparam = column_name,
      //     data.sortbyvalue = order },
      data: function(data) { data.default_supplier = $('.default_supplier option:selected').val(), data.prod_type = $('.prod_type option:selected').val(),data.prod_type_2 = $('.prod_type_2 option:selected').val(), data.prod_type_3 = $('.prod_type_3 option:selected').val(),
          data.from_date=$("#from_date").val(),
          data.to_date=$("#to_date").val(),
          data.filter = $('.filter-dropdown option:selected').val(),
          @if($ecommerceconfig_status == 1)
          data.ecomFilter = $('.ecom-filter option:selected').val(),
          @endif
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.className = className,
          data.prod_category_primary = $('.prod_category_primary option:selected').val(),
          data.data_id = data_id,
          data.supplier_country = $('.supplier_country option:selected').val()
          },
      method: "post",
    },
      columns: [
        { data: 'checkbox', name: 'checkbox'{{@$hide_pricing_columns}} },
        { data: 'action', name: 'action', searchable: false, orderable: false, visible: false},
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'hs_description', name: 'hs_description', searchable: true, orderable: false {{@$hs_description_column}}},
        { data: 'p_s_reference_number', name: 'p_s_reference_number'},
        // { data: 'hs_code', name: 'hs_code' },
        { data: 'category_id', name: 'category_id'},
        { data: 'short_desc', name: 'short_desc'},
        { data: 'product_notes', name: 'product_notes'},
        { data: 'product_note_3', name: 'product_note_3'},
        { data: 'image', name: 'image'},
        { data: 'buying_unit', name: 'buying_unit'},
        { data: 'selling_unit', name: 'selling_unit' },
        // new added
        { data: 'product_type', name: 'product_type' },
        { data: 'product_type_2', name: 'product_type_2'  @if (!in_array('product_type_2', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif },
        { data: 'product_type_3', name: 'product_type_3'  @if (!in_array('product_type_3', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif },
        { data: 'brand', name: 'brand' },
        { data: 'product_temprature_c', name: 'product_temprature_c'{{@$hide_pricing_columns}}},
        { data: 'extra_tax', name: 'extra_tax'},

        { data: 'import_tax_book', name: 'import_tax_book'{{@$hide_pricing_columns}}},
        { data: 'vat', name: 'vat' },
        { data: 'supplier_id', name: 'supplier_id' },
        { data: 'supplier_country', name: 'supplier_country' },
        { data: 'supplier_description', name: 'supplier_description' },
        { data: 'vendor_price', name: 'vendor_price'{{@$hide_pricing_columns}}},
        { data: 'vendor_price_in_thb', name: 'vendor_price_in_thb'{{@$hide_pricing_columns}}},
        { data: 'freight', name: 'freight'{{@$hide_pricing_columns}}},
        { data: 'landing', name: 'landing'{{@$hide_pricing_columns}}},
        { data: 'total_buy_unit_cost_price', name: 'total_buy_unit_cost_price'{{@$hide_pricing_columns}}},
        { data: 'unit_conversion_rate', name: 'unit_conversion_rate' },
        { data: 'selling_unit_cost_price', name: 'selling_unit_cost_price'{{@$hide_pricing_columns}}},
        { data: 'weight', name: 'weight' },
        { data: 'lead_time', name: 'lead_time' },
        { data: 'last_price_history', name: 'last_price_history' },
        { data: 'total_visible_stock', name: 'total_visible_stock' },
        { data: 'on_water', name: 'on_water' },
        { data: 'on_supplier', name: 'on_supplier' },

        //Ecommerce data
        { data: 'title', name: 'title' },
        { data: 'min_order_qty', name: 'min_order_qty' },
        { data: 'max_order_qty', name: 'max_order_qty' },
        { data: 'dimension', name: 'dimension' },
        { data: 'ecom_product_weight_per_unit', name: 'ecom_product_weight_per_unit' },
        { data: 'long_desc', name: 'long_desc' },
        { data: 'selling_price', name: 'selling_price' },
        { data: 'discount_price', name: 'discount_price' },
        { data: 'discount_expiry', name: 'discount_expiry' },
        { data: 'ecom_selling_unit', name: 'ecom_selling_unit' },
        { data: 'ecom_selling_conversion_rate', name: 'ecom_selling_conversion_rate' },
        { data: 'ecom_cogs_price', name: 'ecom_cogs_price' },
        { data: 'ecom_status', name: 'ecom_status' },
        // { data: 'qty_ordered', name: 'qty_ordered' },
        // { data: 'on_airplane', name: 'on_airplane' },
        // { data: 'on_domestic', name: 'on_domestic' },

        // Dynamic columns start
        @if($getWarehouses->count() > 0)
        @foreach($getWarehouses as $warehouse)
          { data: '{{$warehouse->warehouse_title}}{{"current"}}', name: '{{$warehouse->warehouse_title}}{{"current"}}'},
          { data: '{{$warehouse->warehouse_title}}{{"available"}}', name: '{{$warehouse->warehouse_title}}{{"available"}}'},

          { data: '{{$warehouse->warehouse_title}}{{"reserve"}}', name: '{{$warehouse->warehouse_title}}{{"reserve"}}'},
        @endforeach
        @endif
        // Dynamic columns end

        // Dynamic columns start
        @if($getCategories->count() > 0)
        @foreach($getCategories as $cat)
          { data: '{{$cat->title}}', name: '{{$cat->title}}'},
        @endforeach
        @endif
        // Dynamic columns end

        // Dynamic columns start
        @if($getCategoriesSuggested->count() > 0)
        @foreach($getCategoriesSuggested as $cat)
          { data: 'suggest_{{$cat->title}}', name: 'suggest_{{$cat->title}}'},
        @endforeach
        @endif
        // Dynamic columns end

      ],
      initComplete: function () {
        // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

         @if(@$display_prods)
           table2.colReorder.order( [{{@$display_prods->display_order}}]);
         @endif
          // When Sales/Sales Coordinator and Warehouse User is logged In He/She Can't Edit Product Detail
        @if(Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6 )
          $('.inputDoubleClick').removeClass('inputDoubleClick');
        @endif
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal('hide');

        var api = this.api()
        var json = api.ajax.json();

        // var unit_title = json.title;
        var total_unit = json.total_unit;
        // alert(total_unit);
        if(total_unit != 0)
        {
          $('#total_unit').html(total_unit);
        }
        else
        {
          $('#total_unit').html(0.00);
        }
        table2.on('xhr.dt', function()
        {
          table2.one('draw', function() {
            table2.row(scrollPos).scrollTo(false);
            console.log(table2.row(scrollPos));
            var ind = table2.row(scrollPos).index();
          });
        });

      },
      footerCallback: function ( row, data, start, end, display ) {
        var api              = this.api();
        var json             = api.ajax.json();
        var all_stock_array   = json.all_stock_array;
        var col_id = 49;
        var counter = 0;
        var total_system_units = "{{$total_system_units}}";
    //     var on_water = parseFloat(json.on_water).toFixed(2);
    //    on_water = on_water.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
       $( api.column( 34 ).footer() ).html(json.on_water);
       $( api.column( 35 ).footer() ).html(json.on_supplier);

       //  var on_airplane = parseFloat(json.on_airplane).toFixed(2);
       // on_airplane = on_airplane.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
       // $( api.column( 30 ).footer() ).html(on_airplane);

       //  var on_domestic = parseFloat(json.on_domestic).toFixed(2);
       // on_domestic = on_domestic.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
       // $( api.column( 31 ).footer() ).html(on_domestic);


        if(total_system_units == 1)
        {
          @if($getWarehouses->count() > 0)
          @foreach($getWarehouses as $warehouse)
          $( api.column( col_id++ ).footer() ).html(all_stock_array[counter++]);
          $( api.column( col_id++ ).footer() ).html(all_stock_array[counter++]);
          $( api.column( col_id++ ).footer() ).html(all_stock_array[counter++]);
          @endforeach
          @endif
        }
        // $( api.column( 5 ).footer() ).html(total_quantity);
      },
    });

      $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
     $('#tableSearchField').val(searchField);
     count=searchField.length;
      if(e.keyCode == 13) {

        table2.search($(this).val()).draw();
        return;
      }
      // else if(count>0){
      //   if(e.type == 'focusout'){
      //      table2.search(this.value).draw();
      //         return;
      //              }
      //   }
        else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
        }
 });
    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

      var arr = table2.colReorder.order();
      var all = arr;
      if(all == '')
      {
        var col = column;
      }
      else
      {
        var col = all[column];
      }
      var columns = table2.settings().init().columns;
      var name = columns[col].name;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'completed_products',column_id:col},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
           if(name.toLowerCase().indexOf('current') >= 0)
           {
              table2.ajax.reload();
           }
          if(data.success == true){
            /*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
            // table2.ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    });

    table2.on( 'column-reorder', function ( e, settings, details ) {
       $.get({
         url : "{{ route('column-reorder') }}",
         dataType : "json",
         data : "type=completed_products&order="+table2.colReorder.order(),
         beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
         },
         success: function(data){
          $('#loader_modal').modal('show');
         },
         error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
       });

       table2.button(0).remove();
       table2.button().add(0,
       {
         extend: 'colvis',
         autoClose: false,
         fade: 0,
         columns: ':not(.noVis)',
         colVis: { showAll: "Show all" }
       });
       // table2.ajax.reload();
       var headerCell = $( table2.column( details.to ).header() );
       headerCell.addClass( 'reordered' );
  });

    // dropdown double click editable code start here
  $(document).on('change', 'select.select-common', function(){

    if($(this).val() !== '')
    {
    if($(this).attr('name') == 'supplier_id')
    {
      var old_value = $('.inc-fil-supp').prev().data('fieldvalue');
      var pId = $(this).parents('tr').attr('id');
      var new_value = $("option:selected", this).html();
      $(this).removeClass('active');
      $(this).addClass('d-none');
      $(this).prev().removeClass('d-none');
      $(this).prev().html(new_value);
      $(this).prev().css("color", "");
      saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
    }
    else if($(this).attr('name') == 'category_id')
    {
      var old_value = $('.inc-fil-cat').prev().data('fieldvalue');
      var thisPointer= $(this);
      var pId = $(this).parents('tr').attr('id');
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
            thisPointer.prev().html(new_value);
            saveProdData(pId, thisPointer.attr('name'), thisPointer.val(), old_value);
          }
          else
          {
            $('.table-product').DataTable().ajax.reload();
          }
        }

      );

    }
    else
      {
        var old_value = $(this).prev().data('fieldvalue');
        var pId = $(this).parents('tr').attr('id');
        var new_value = $("option:selected", this).html();
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
    }
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
        // $x.next().next('span').removeClass('d-none');
        // $x.next().next('span').addClass('active');

       }, 300);
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

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

    var old_value = $(this).prev().data('fieldvalue');
    var pId = $(this).parents('tr').attr('id');

    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
    else
    {
      if($(this).attr('name') == 'refrence_code')
      {
        if($(this).val().length > 15)
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Maximum 15 characters allowed for Refrence #</b>'});
          return false;
        }
        var new_value = $(this).val();
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
      if($(this).attr('name') == 'product_fixed_price')
      {
        var new_value = $(this).val();
        var id = $(this).data('id');
        if(new_value == '')
        {
          return false;
        }
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        saveProdData(id, $(this).attr('name'), $(this).val(), old_value);
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var new_value = $(this).val();
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);

        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $(this).prev().css("color", "");
        saveProdData(pId, $(this).attr('name'), $(this).val(), old_value);
      }
    }
   }
  });

    $(document).on('click', '.check-all', function () {

      if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
      {
        toastr.error('Error!', 'Apply Filter first then click on Checkbox to perform any function !!!' ,{"positionClass": "toast-bottom-right"});
        return false;
      }

      @if($ecommerceconfig_status == 1)
      var newval = $('.ecom-filter option:selected').val();
      @else
      var newval = "";
      @endif

      if(this.checked == true)
      {
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
        var cb_length = $( ".check:checked" ).length;
         $("input.check:checked").each(function() {
        if(add_to_excel_products.indexOf($(this).val()) === -1)
        {
          add_to_excel_products.push($(this).val());
        }
      });
    $('.add_product_to_excel_download_span').html(add_to_excel_products.length);
        if(cb_length > 0)
        {
          $('.delete-selected-item').removeClass('d-none');
          if(newval == "all")
          {
            $('.deactivate-btn').removeClass('d-none');
            $('.ecommerce-products-enabled').removeClass('d-none');
            $('.refresh-stock').removeClass('d-none');
            $('.ecommerce-products-disabled').removeClass('d-none');
            // $('.woocommerce-products-enabled').removeClass('d-none');
          }
          else if(newval == "ecom-enabled")
          {
            $('.ecommerce-products-disabled').removeClass('d-none');
            $('.ecommerce-products-enabled').addClass('d-none');
            // $('.woocommerce-products-enabled').addClass('d-none');
            $('.refresh-stock').addClass('d-none');
          }
          else if(newval == "ecom-disable")
          {
            $('.ecommerce-products-enabled').removeClass('d-none');
            // $('.woocommerce-products-enabled').removeClass('d-none');
            $('.refresh-stock').removeClass('d-none');
            $('.ecommerce-products-disabled').addClass('d-none');
          }
          else if(newval == "")
          {
            $('.deactivate-btn').removeClass('d-none');
          }
        }
      }
      else
      {
         add_to_excel_products = [];
      $('.add_product_to_excel_download_span').html(add_to_excel_products.length);
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
        $('.delete-selected-item').addClass('d-none');
        if(newval == "all")
        {
          $('.ecommerce-products-enabled').addClass('d-none');
          // $('.woocommerce-products-enabled').addClass('d-none');
          $('.refresh-stock').addClass('d-none');
        }
        else if(newval == "ecom-enabled")
        {
          $('.ecommerce-products-disabled').addClass('d-none');
        }
        else if(newval == "")
        {
          $('.deactivate-btn').addClass('d-none');
        }
      }
    });

    $(document).on('change','.default_supplier',function(){
      $("#default_supplier_exp").val($('.default_supplier option:selected').val());
      var selected = $(this).val();
      $("#apply_filter_btn").val("1");
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.table-product').DataTable().ajax.reload();
    });
  $(document).on('change','#from_date',function(){
      $("#from_date_exp").val($("#from_date").val());
  });
  $(document).on('change','#to_date',function(){
      $("#to_date_exp").val($("#to_date").val());
  });
    $(document).on('change','.prod_type',function(){
      $("#prod_type_exp").val($('.prod_type option:selected').val());
      var selected = $(this).val();
      $("#apply_filter_btn").val("1");
    });
    $(document).on('change','.prod_type_2',function(){
      $("#prod_type_2_exp").val($('.prod_type_2 option:selected').val());
      var selected = $(this).val();
      $("#apply_filter_btn").val("1");
    });
    $(document).on('change','.prod_type_3',function(){
      $("#prod_type_3_exp").val($('.prod_type_3 option:selected').val());
      var selected = $(this).val();
      $("#apply_filter_btn").val("1");
    });
    $(document).on('change','.supplier_country',function(){
      $("#supplier_country").val($('.supplier_country option:selected').val());
      var selected = $(this).val();
      $("#apply_filter_btn").val("1");
    });

    $(document).on('change','.prod_category',function(){
      $("#prod_category_exp").val($('.prod_category option:selected').val());
      var selected = $(this).val();
      $("#apply_filter_btn").val("1");
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.table-product').DataTable().ajax.reload();
    });

    $(document).on('change','.prod_category_primary',function(){
      $("#prod_category_primary_exp").val($('.prod_category_primary option:selected').val());
      var category_id = $(this).val();
      $("#apply_filter_btn").val("1");
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');

      /*** Code to Filter for Sub Category***/
      // $.ajax({
      //   url:"{{route('filter-sub-category')}}",
      //   method:"get",
      //   dataType:"json",
      //   data:{category_id:category_id},
      //   success:function(data)
      //   {
      //     var html_string = '';
      //     html_string+="<option value=''>Choose Subcategory</option>";
      //     for(var i=0;i<data.length;i++)
      //     {
      //       html_string+="<option value='"+data[i]['id']+"'>"+data[i]['title']+"</option>";
      //     }
      //     $(".prod_category").empty();
      //     $(".prod_category").append(html_string);
      //   },
      //   error:function()
      //   {
      //     alert('Error');
      //   }
      // });
      // $('.prod_category').val('');
      // $('.table-product').DataTable().ajax.reload();

    });

    $(document).on('change','.filter-dropdown',function(){
      $("#filter-dropdown_exp").val($('.filter-dropdown option:selected').val());
      $("#apply_filter_btn").val("1");
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.table-product').DataTable().ajax.reload();
    });

    $(document).on('change','.ecom-filter',function(){
      $("#ecom-filter_exp").val($('.ecom-filter option:selected').val());
      $("#apply_filter_btn").val("1");
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.table-product').DataTable().ajax.reload();
    });

    $(document).on('click','.apply_filters',function(){
        let from_date='';
        let to_date='';
        let current_url='' ;
        let supplier=$(".default_supplier option:selected").val();
        let primary_category=$("#product_id_select").val();
        // let sub_category=$(".prod_category  option:selected").val();
        let prod_type=$(".prod_type option:selected").val();
        let prod_type_2=$(".prod_type_2 option:selected").val();
        let prod_type_3=$(".prod_type_3 option:selected").val();
        let supplier_country=$(".supplier_country option:selected").val();
        let filter=$(".filter-dropdown option:selected").val();
        let ecom_filter = $(".ecom-filter option:selected").val();
        let class_name = className;
        let base_url=current_url.split('?')[0];

        if("{{Auth::user()->role_id}}"==10){
                 from_date=$("#from_date_exp").val();
                to_date=$("#to_date_exp").val();
        }
        // alert(supplier+''+primary_category+''+sub_category+''+prod_type+''+type+''+filter+''+ecom_filter+''+from_date+''+to_date);
      $("#apply_filter_btn").val("0");
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');

    if("{{Auth::user()->role_id}}"==10){

        current_url='?supplier=' + supplier + '&product_category=' + primary_category + '&prod_type=' + prod_type+ '&prod_type_2=' + prod_type_2 + '&filter=' + filter + '&supplier_country=' + supplier_country + '&ecom_filter=' + ecom_filter + '&from_date=' + from_date + '&to_date=' + to_date  + '&className=' + class_name + '&prod_type_3=' + prod_type_3;
    }else{
    current_url= '?supplier=' + supplier + '&product_category=' + primary_category + '&prod_type=' + prod_type+ '&prod_type_2=' + prod_type_2 + '&filter=' + filter + '&supplier_country=' + supplier_country + '&ecom_filter=' +  ecom_filter + '&className=' + class_name + '&prod_type_3=' + prod_type_3;
    }
    $('.table-product').DataTable().ajax.reload();
          $.ajax({
                method:"get",
                url:"{{ route('complete-list-product') }}"+current_url,
                success:function(data){
                    window.history.replaceState(null, null, current_url);

                }

             });

    });
$(document).ready(function(){

});
    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });




  $(document).on('click', '.check', function () {

    $("input.check:checked").each(function() {
        if(add_to_excel_products.indexOf($(this).val()) === -1)
        {
          add_to_excel_products.push($(this).val());
        }
      });
    $('.add_product_to_excel_download_span').html(add_to_excel_products.length);

    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Checkbox to perform any function !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    @if($ecommerceconfig_status == 1)
    var newval = $('.ecom-filter option:selected').val();
    @else
    var newval = "";
    @endif
    /*all and ecom-enabled*/
   if(this.checked == true)
    {
      $('.delete-selected-item').removeClass('d-none');
      $(this).parents('tr').addClass('selected');
      if(newval == "all")
      {
        $('.deactivate-btn').removeClass('d-none');
        $('.ecommerce-products-enabled').removeClass('d-none');
        // $('.woocommerce-products-enabled').removeClass('d-none');
        $('.refresh-stock').removeClass('d-none');
        $('.ecommerce-products-disabled').removeClass('d-none');
      }
      else if(newval == "ecom-enabled")
      {
        $('.ecommerce-products-disabled').removeClass('d-none');
        $('.ecommerce-products-enabled').addClass('d-none');
        $('.refresh-stock').addClass('d-none');
      }
      else if(newval == "ecom-disable")
      {
        $('.ecommerce-products-enabled').removeClass('d-none');
        $('.refresh-stock').removeClass('d-none');
        $('.ecommerce-products-disabled').addClass('d-none');
      }
      else if(newval == "")
      {
        $('.deactivate-btn').removeClass('d-none');
      }
    }
    else
    {
      // alert('here');
      var removeItem = $(this).val();
      add_to_excel_products = jQuery.grep(add_to_excel_products, function(value) {
        return value != removeItem;
      });
      $('.add_product_to_excel_download_span').html(add_to_excel_products.length);
      var cb_length = $( ".check:checked" ).length;
      $(this).parents('tr').removeClass('selected');
      if(cb_length == 0)
      {
        if(newval == "all")
        {
          $('.ecommerce-products-enabled').addClass('d-none');
          // $('.woocommerce-products-enabled').addClass('d-none');
        }
        else if(newval == "ecom-enabled")
        {
          $('.ecommerce-products-disabled').addClass('d-none');
        }
        else if(newval == "")
        {
          $('.deactivate-btn').addClass('d-none');
        }

        $('.delete-selected-item').addClass('d-none');
      }
          $('.refresh-stock').addClass('d-none');

    }
  });

  $(document).on("click",'.deactivate-btn',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to deactivate selected products?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('deactivate-products') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Selected Product(s) Deactivate Successfully.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                      toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                },
                error: function (request, status, error) {
                  $("#loader_modal").modal('hide');
                    toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });

      }); //deactivate products

  $(document).on("click",'.ecommerce-products-enabled',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to enable selected products to Ecommerce?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('products-enable-ecommerce') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  // alert('testtest');
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Selected Product(s) Enabled Successfully.' ,{"positionClass": "toast-bottom-right"});
                      // $('.table-product').DataTable().ajax.reload();
                      $("input.check:checked").each(function() {
                         $(this).closest('tr').remove();
                        });
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                      toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                },
                error: function (request, status, error) {
                  $("#loader_modal").modal('hide');
                    toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });

      }); //Enable Over Ecommerce Product


      $(document).on("click",'.woocommerce-products-enabled',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to enable selected products to Woocommerce?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('products-enable-woocommerce') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Selected Product(s) Enabled Successfully.' ,{"positionClass": "toast-bottom-right"});
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                    $html = '<ul>';
                    $.each( data.prices_error, function( key, value )
                    {
                      $html += '<li>'+value+'</li>';
                    });
                    $html += '<ul>';
                    swal({
                      title: "Products with Missing Price",
                      html: $html,
                      type: "information",
                      showCancelButton: true,
                      confirmButtonColor: "#DD6B55",
                      confirmButtonText: "OK",
                      closeOnConfirm: true,
                      },
                    );
                      toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                },
                error: function (request, status, error) {
                  $("#loader_modal").modal('hide');
                    toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });

      }); //Enable Over Woocommerce Product



  $(document).on("click",'.refresh-stock',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to refresh stock?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('refresh-stock') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  // alert('testtest');
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Stock Refreshed Successfully !!!' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                      toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                },
                error: function (request, status, error) {
                  $("#loader_modal").modal('hide');
                    toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });

      }); //Refresh Stock

  $(document).on("click",'.delete-btn',function(){
    var selected_products = [];
      $("input.check:checked").each(function() {
      selected_products.push($(this).val());
    });

      swal({
          title: "Alert!",
          text: "Are you sure you want to Unpublish selected products?",
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
                data:'selected_products='+selected_products,
                url:"{{ route('products-disable-ecommerce') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Selected Product(s) Unpublished Successfully.' ,{"positionClass": "toast-bottom-right"});
                      // $('.table-product').DataTable().ajax.reload();
                      $("input.check:checked").each(function() {
                         $(this).closest('tr').remove();
                        });
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.success == false)
                  {
                      var ordered_products = "<div class='alert alert-danger alert-dismissible'><a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a><p class=''><strong>Note: </strong>"+data.msg+" </p></div>";

                      $('#ordered_products_alert').html(ordered_products);
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.error == 1)
                  {
                    toastr.error('Error!', 'You cannot Unpublish more than 100 products at a time.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-product').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                  }
                },
                error: function (request, status, error) {
                  $("#loader_modal").modal('hide');
                    toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });

      }); //delete products

  // getting product Image
  $(document).on('click', '.show-prod-image', function(e){
    let sid = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-prod-image') }}",
      data: 'prod_id='+sid,

      success: function(response){
        $('.fetched-images').html(response);
      }
    });
  });

  // delete Product
  $(document).on('click', '.deleteProduct', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete this product ?",
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
              url: "{{ route('delete-product-data') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){
                if(response.success === true){
                  toastr.success('Success!', 'Product Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-product').DataTable().ajax.reload();
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
              toastr.success('Success!', 'Product supplier added successfully',{"positionClass": "toast-bottom-right"});
              $('.table-product').DataTable().ajax.reload();
              $('#addSupplierModalDropDownForm')[0].reset();
              $('.addSupplierModalDropDown').modal('hide');
            }


          },
          error: function (request, status, error) {
                $('.save-prod-sup-drop-down').val('add');
                $('.save-prod-sup-drop-down').removeClass('disabled');
                $('.save-prod-sup-drop-down').removeAttr('disabled');
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

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    // alert($(this).data('id'));
    var str = $(this).data('id');
    if(str !== undefined){
    var res = str.split(" ");
    }
    else{
      var res = null;
    }
   if(res !== null){
    $.ajax({
      type: "get",
      url: "{{ route('get-prod-dropdowns') }}",
      data: 'value='+res[1]+'&choice='+res[0],
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response)
      {
        $("#loader_modal").modal('hide');
        if(response.field == 'type'){
          $('.type_select'+res[2]).append(response.html);
        }
        if(response.field == 'type_2'){
          $('.type_select_2'+res[2]).append(response.html);
        }

        else if(response.field == 'unit'){
          $('.buying_select'+res[2]).append(response.html);
        }
        else if(response.field == 'selling_unit'){
          $('.selling_unit'+res[2]).append(response.html);
        }
         else if(response.field == 'category_id'){
          $('.categories_select'+res[2]).append(response.html);
        }
        // $('.fetched-images').html(response);
        // $('.product_type').empty();
        // $('.product_type').append(response.html);
          $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
        var num = $(this).next().val();
        $(this).next().focus().val('').val(num);
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }
  });

  function saveProdData(prod_detail_id,field_name,field_value,old_value){
      console.log(field_name+' '+field_value+''+prod_detail_id+' '+old_value);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('save-prod-data-incomplete-to-complete') }}",
        dataType: 'json',
        data: 'prod_detail_id='+prod_detail_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
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
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          if(data.error == 1)
          {
            toastr.error('Error!', 'Product Description already exist.',{"positionClass": "toast-bottom-right"});
          }
          if(data.dont_run == 0)
          {
            if(data.completed == 1)
            {
              toastr.success('Success!', 'Information updated successfully. Product marked as completed.',{"positionClass": "toast-bottom-right"});
            }
            else
            {
              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            }
          }

          if(data.reload == 1)
          {
            $('.table-product').DataTable().ajax.reload();
          }

          if(data.fixed_price == true)
          {
          toastr.success('Success!', 'Price Updated Successfully!!!',{"positionClass": "toast-bottom-right"});

          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

  // uploading shahskayssss
  uploadHBR.init({
      "target": "#uploads",
      "max": 4,
      "textNew": "ADD",
      "textTitle": "Click here or drag to upload an image",
      "textTitleRemove": "Click here to remove the image"
    });

  $('#reset').click(function () {
    uploadHBR.reset('#uploads');
  });

  $(document).on('click', '.img-uploader', function(){
    var count = $('#images_count').val();
    var pId = $(this).parents('tr').attr('id');
    $('.img-product-id').val(pId);
    $('.product_id_for_cropping').val(pId);
    $('#upload-demo').croppie('destroy');
    createCroppie();
    $('.add-prodimage-form')[0].reset();

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
        // $('#loader_modal').modal({
        //   backdrop: 'static',
        //   keyboard: false
        // });
        // $('#loader_modal').modal('show');
      },
      success: function(result){
        $('.save-btn').html('Upload');
        $('.save-btn').attr('disabled', true);
        $('.save-btn').removeAttr('disabled');
        // $('#loader_modal').modal('hide');
        if(result.success == true){
          toastr.success('Success!', 'Image(s) added successfully',{"positionClass": "toast-bottom-right"});
          uploadHBR.reset('#uploads');
          $('#productImagesModal').modal('hide');
          $('.table-product').DataTable().ajax.reload();

        }else{
          toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
          uploadHBR.reset('#uploads');
          $('#productImagesModal').modal('hide');
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

  // delete image
  $(document).on('click', '.delete-img-btn', function(e){
      var id = $(this).data('img');
      var prodid = $(this).data('prodId');
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
                  $("#loader_modal").modal('hide');
                  $('.table-product').DataTable().ajax.reload();
                  if(data.search('done') !== -1){
                    myArray = new Array();
                    myArray = data.split('-SEPARATOR-');
                    let i_id = myArray[1];
                    $('#prod-image-'+i_id).remove();
                    toastr.success('Success!', 'Image deleted successfully.' ,{"positionClass": "toast-bottom-right"});
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

  // Getting product category k child
  $(document).on('change', '.prod-category', function(e){
      var p_cat_id = $(this).val();
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
            $(this).closest('td').next('td').find('span').addClass('d-none');
            $(this).closest('td').next('td').find('span').next().removeClass('d-none');
            $(this).closest('td').next('td').find('span').next().focus();
            $(this).closest('td').next('td').find('span').next().html(data.html_string);
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

  // adding product on add product button on click function
  $(document).on('submit', '#save_prod_btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-product') }}",
          method: 'post',
          data: new FormData(this),
          contentType: false,
          cache: false,
          context: this,
          processData:false,
          beforeSend: function(){
            $('.save-prod-btn').val('Please wait...');
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
             $("#loader_modal").modal('show');
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            $('.save-prod-btn').val('Add Product');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-product-div').addClass('d-none');
              $('.product_category_dd').val("").change();
              $('.table-product').DataTable().ajax.reload();
            }
            else if(result.success === false)
            {
              $('.modal').modal('hide');
              toastr.success('Success!', 'Product added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-product-div').addClass('d-none');
              $('.product_category_dd').val("").change();
              $('.table-product').DataTable().ajax.reload();

            }


          },
          error: function (request, status, error) {
                $('.save-prod-btn').val('Add Product');
                $('.save-prod-btn').removeClass('disabled');
                $('.save-prod-btn').removeAttr('disabled');
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

</script>
<script type="text/javascript">
  $(document).on('click','.upload-excel-btn',function(){
      $('#uploadExcel').modal('show');
  });

  $('.reset-btn').on('click',function(){
    $("#apply_filter_btn").val("0");
    $('.default_supplier').val('');
    // $('.prod_category').val('');
    $('.prod_type').val('');
    $('.prod_type_2').val('');
    $('.prod_type_3').val('');
    $('.prod_category_primary').val('');
    $('.filter-dropdown').val('');
    $('#from_date').val('');
    $('#to_date').val('');
    $('.ecom-filter').val('all');
    $('#product_id_select').val(null);
    $('#header_product_search').val('');
    $('.supplier_country').val('');
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $(".state-tags").select2("", "");
    var url = window.location.href;
    url = url.split("?")[0];
    order = 1;
    column_name = '';
    window.history.replaceState(null, null, url);
    $('.table-product').DataTable().ajax.reload();
  });

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc
      if($('.selectDoubleClick').hasClass('d-none')){
        $('.selectDoubleClick').removeClass('d-none');
        $('.selectDoubleClick').next().addClass('d-none');
      }
      if($('.inputDoubleClick').hasClass('d-none')){
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

  $('.price-check-btn').on('click',function(){
    $.ajax({
      method: "get",
      url: "{{ url('getting-product-incorrect-prices') }}",
      dataType: 'json',
      context: this,
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
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $('.update-billed-btn').on('click',function(){
    $.ajax({
      method: "get",
      url: "{{ url('update-billed-qty-script') }}",
      dataType: 'json',
      context: this,
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
        toastr.success('Success!', 'Script runs successfully',{"positionClass": "toast-bottom-right"});
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

</script>
  <input type="hidden" name="default_supplier_exp" id="default_supplier_exp">
  <input type="hidden" name="prod_category_primary_exp" id="prod_category_primary_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="prod_type_exp" id="prod_type_exp">
  <input type="hidden" name="filter-dropdown_exp" id="filter-dropdown_exp">
  <input type="hidden" name="type" id="type" value=1>
  <input type="hidden" name="search_value" id="search_value">
    <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">

<script type="text/javascript">
  $(document).on('click','.export-btn',function(){

    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    // var count=0;
    var form=$('#export_complete_products_form');
    $("#default_supplier_exp").val($('.default_supplier option:selected').val());
    $("#prod_category_primary_exp").val($('.prod_category_primary option:selected').val());
    $("#prod_category_exp").val($('.prod_category option:selected').val());
    $("#prod_type_exp").val($('.prod_type option:selected').val());
    $("#prod_type_2_exp").val($('.prod_type_2 option:selected').val());
    $("#prod_type_3_exp").val($('.prod_type_3 option:selected').val());
    $("#filter-dropdown_exp").val($('.filter-dropdown option:selected').val());
    let from_date=$("#from_date").val();
    let to_date=$("#to_date").val();
    $("#from_date_exp").val(from_date);
    $("#to_date_exp").val(to_date);
    $("#sortbyparam").val(column_name);
    $("#sortbyvalue").val(order);
    $("#data_id").val(data_id);
    $("#product_export_button").val('erp_export');
    $("#search_value").val($('.table-product').DataTable().search());
    $("#product_export_button").val('erp_export');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-status-complete-products')}}",
      data:form_data,
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert-success-pos').addClass('d-none');
          $('.export-text').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').attr('title','EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForProducts();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          $('.export-btn').attr('title','EXPORT is being Prepared');
          checkStatusForProducts();
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $(document).on('click','.export-btn-pos-notes',function(){

    var form=$('#export_complete_products_form');
    $("#default_supplier_exp").val($('.default_supplier option:selected').val());
    $("#prod_category_primary_exp").val($('.prod_category_primary option:selected').val());
    $("#prod_category_exp").val($('.prod_category option:selected').val());
    $("#prod_type_exp").val($('.prod_type option:selected').val());
    $("#prod_type_2_exp").val($('.prod_type_2 option:selected').val());
    $("#prod_type_3_exp").val($('.prod_type_3 option:selected').val());
    $("#filter-dropdown_exp").val($('.filter-dropdown option:selected').val());
    let from_date=$("#from_date").val();
    let to_date=$("#to_date").val();
    $("#from_date_exp").val(from_date);
    $("#to_date_exp").val(to_date);
    $("#sortbyparam").val(column_name);
    $("#sortbyvalue").val(order);
    $("#data_id").val(data_id);
    $("#product_export_button").val('pos_note_export');

    var form_data = form.serialize();
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
    });
    $.ajax({
    method:"get",
    url:"{{route('export-status-complete-products')}}",
    data:form_data,
    beforeSend:function(){

    },
    success:function(data){
        if(data.status==1)
        {
        $('.export-alert-success').addClass('d-none');
        $('.export-text').addClass('d-none');
        $('.export-alert-success-pos-notes').addClass('d-none');
        $('.export-text').addClass('d-none');
        $('.export-alert').removeClass('d-none');
        $('.export-btn').attr('title','EXPORT is being Prepared');
        $('.export-btn').prop('disabled',true);
        console.log("Calling Function from first part");
        checkStatusForPosNoteExport();
        }
        else if(data.status==2)
        {
        $('.export-alert-another-user').removeClass('d-none');
        $('.export-alert').addClass('d-none');
        $('.export-btn').prop('disabled',true);
        $('.export-btn').attr('title','EXPORT is being Prepared');
        checkStatusForPosNoteExport();
        }
    },
    error: function(request, status, error){
        $("#loader_modal").modal('hide');
    }
    });
});

$(document).on('click','.export-btn-pos',function(){

    // var count=0;
    var form=$('#export_complete_products_form');
    $("#default_supplier_exp").val($('.default_supplier option:selected').val());
    $("#prod_category_primary_exp").val($('.prod_category_primary option:selected').val());
    $("#prod_category_exp").val($('.prod_category option:selected').val());
    $("#prod_type_exp").val($('.prod_type option:selected').val());
    $("#prod_type_2_exp").val($('.prod_type_2 option:selected').val());
    $("#prod_type_3_exp").val($('.prod_type_3 option:selected').val());
    $("#filter-dropdown_exp").val($('.filter-dropdown option:selected').val());
    // $("#supplier_country").val($('.supplier_country option:selected').val());
    let from_date=$("#from_date").val();
    let to_date=$("#to_date").val();
    $("#from_date_exp").val(from_date);
    $("#to_date_exp").val(to_date);
    $("#sortbyparam").val(column_name);
    $("#sortbyvalue").val(order);
    $("#data_id").val(data_id);
    $("#product_export_button").val('pos_product_export');

    var form_data = form.serialize();
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
    });
    $.ajax({
    method:"get",
    url:"{{route('export-status-complete-products')}}",
    data:form_data,
    beforeSend:function(){

    },
    success:function(data){
        if(data.status==1)
        {
        $('.export-alert-success').addClass('d-none');
        $('.export-alert-success-pos').addClass('d-none');
        $('.export-text-pos').addClass('d-none');
        $('.export-alert').removeClass('d-none');
        $('.export-btn').attr('title','EXPORT is being Prepared');
        $('.export-btn').prop('disabled',true);
        console.log("Calling Function from first part");
        checkStatusForPosProducts();
        }
        else if(data.status==2)
        {
        $('.export-alert-another-user').removeClass('d-none');
        $('.export-alert').addClass('d-none');
        $('.export-btn').prop('disabled',true);
        $('.export-btn').attr('title','EXPORT is being Prepared');
        checkStatusForPosProducts();
        }
    },
    error: function(request, status, error){
        $("#loader_modal").modal('hide');
    }
    });
});

function checkStatusForPosProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-complete-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForPosProducts();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success-pos').removeClass('d-none');
          $('.export-text').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert-success-pos').addClass('d-none');
          $('.export-text').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

function checkStatusForPosNoteExport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-complete-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForPosNoteExport();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success-pos-notes').removeClass('d-none');
          $('.export-text-pos').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success-pos-notes').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-complete-products')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-btn').attr('title','EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          checkStatusForProducts();
        }
      }
    });
  });

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-complete-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForProducts();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert-success-pos').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).on('click','.download-btn',function(){
    $('.export-alert-success').addClass('d-none');
  });

  $(document).on('click','.file-not-exist-btn',function(){
    swal("Oppsss!", "File doesn't exist!", "error");
  });
  var btn_click = false;
    $(document).on('click',function(e){
      if ($(e.target).closest(".dt-button-collection").length === 1) {
          btn_click = true;
      }

      if(btn_click)
      {
        if ($(e.target).closest(".dt-button-collection").length === 0) {
          btn_click = false;
          $('.table-product').DataTable().ajax.reload();
          // alert('clicked outside');
        }
      }
    });


    resize = null;
  function createCroppie(){
    resize = $('#upload-demo').croppie({
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
  }

  createCroppie();


$('#image').on('change', function () {
  var reader = new FileReader();
    reader.onload = function (e) {
      // alert(e.target.result);
      resize.croppie('bind',{
        url: e.target.result
      }).then(function(){
        // console.log('jQuery bind complete');
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
          $('.add-prodimage-form')[0].reset();
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
  $('.product_id_for_cropping').val();
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
        $('.upload-image').addClass('disabled');
        $('.upload-image').html('<i class="fa fa-circle-o-notch fa-spin"></i> Please Wait To Upload The Image');
        $('.upload-image').attr('disabled', true);
      },
      success: function (data) {
        if(data.status == true)
        {
          toastr.success('Success!', 'Image Uploaded Successfully !!!',{"positionClass": "toast-bottom-right"});
          $('#upload-demo').croppie('destroy');
          createCroppie();
          $('.upload-image').removeClass('disabled');
          $('.upload-image').html('Upload Image');
          $('.upload-image').attr('disabled', false);
          // location.reload();
          $('.add-prodimage-form')[0].reset();
          $('#productImagesModal').modal('hide');
          $('.table-product').DataTable().ajax.reload();
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
// applying date filters

$(document).ready(function(){
    $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });
});
$('#header_product_search').on('click',function(){
  if($('.custom__search_arrows').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetProductCategorySearch($(this).val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('.custom__search_arrows').on('click',function(){
  if($(this).hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetProductCategorySearch($('#header_product_search').val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('#header_product_search').keyup(function(event){
      // $('#header_product_search').unbind("focus");
      keyindex = -1;
      alinks = '';
      var query = $(this).val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
         var _token = $('input[name="_token"]').val();
         GetProductCategorySearch(query,_token);
        }
        else if(query.length == 0)
        {
          $('#header_prod_searchh').val('');
          $('#header_prod_searchh').data('prod_id','');
        }
        else
        {
          $('#myIddd').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });
  function GetProductCategorySearch(query=null,_token=null){
    $.ajax({
      url:"{{ route('purchase-fetch-product-category') }}",
      method:"POST",
      data:{query:query, _token:_token},
      beforeSend:function(){
        $('#loader__custom_search').removeClass('d-none');
      },
      success:function(data){
        $('#myIddd').html(data);
        $('#loader__custom_search').addClass('d-none');
        $('.custom__search_arrows').removeClass('fa-caret-down');
        $('.custom__search_arrows').addClass('fa-caret-up');
       },
       error: function(){

       }
    });
  }
  var className = '';
  $(document).on("click",".list-data",function() {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       $('.search_customer').val(li_text);
      $("#product_id_select").val(li_id);
      $("#customer_id_exp").val(li_id);
      $(".select_customer_id").hide();
      $('#header_product_search').val(li_text);
      className = $(this).hasClass('parent') ? 'parent' : 'child';
      $('#className').val(className);
      $('#myarrows').toggleClass('fa-caret-up fa-caret-down');
});
  $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
      $("#myIddd").empty();
      $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   });

  $('.print_btn').on('click',function(){
    var add_to_excel_products = [];
    $("input.check:checked").each(function() {
        if(add_to_excel_products.indexOf($(this).val()) === -1)
        {
          add_to_excel_products.push($(this).val());
        }
      });
      $('.products_selected').val(add_to_excel_products);
      var fees = $('.products_selected').val();
    var url = "{{url('print-barcode')}}"+"/"+fees;
    window.open(url, 'Product Barcode', 'width=1200,height=600,scrollbars=yes');
  })

</script>
@stop
