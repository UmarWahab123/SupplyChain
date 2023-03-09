
@extends('backend.layouts.layout')

@section('title','Sold Products Report | Supply Chain')

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
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
}
.dt-buttons
{
  display: inline-block;
  float: left;
  margin-right: 5px;
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
table.dataTable tfoot th
{
  padding: 5px 2px !important;
}
.dataTables_scrollFootInner
{
  margin-top: 10px;
}

table.dataTable thead .sorting { background: url('../public/sort/sort_both.png') no-repeat center right;
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('../public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('../public/sort/sort_desc.png') no-repeat center right;
  background-size: 5vh;}

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
          <li class="breadcrumb-item active">Product Sales Report-Detail</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h5 class="maintitle text-uppercase fontbold">Product sales report - Detail</h5>
  </div>
  @php
    $className='';
    if($last_downloaded==null)
    {
      $className='d-none';
    }
  @endphp
    @if(Auth::user()->role_id != 9)
 <!--  <div class="col-2  ">
    <label>
      <b class=" download-btn-text {{$className}}" ><i>Last created on: {{Carbon::parse($last_downloaded)->format('d/m/Y H:i:s')}}</i> </b>
    </label>
    <div class="input-group-append"> -->

      <!-- <a download href="{{'storage/app/Sold-Products-Report.xlsx'}}"  class="btn download-btn recived-button rounded {{$className}}">Download</a>  -->

      <!-- <a class="vertical-icons {{$className}}" href="{{ url('get-download-xslx','Sold-Products-Report.xlsx')}}" target="_blank" title="Download" id=""> -->
       <!-- <a download href="{{asset('storage/app/Sold-Products-Report.xlsx')}}" class="download-btn vertical-icons" id="" title="Download"> -->
            <!-- <span class=""> -->
                <!-- <img src="{{asset('public/icons/download.png')}}" width="27px"> -->
            <!-- </span> -->
          <!-- </a> -->
    <!-- </div> -->
  <!-- </div> -->

  <div class="col-md-4 col-6">
    <div class="pull-right">
    <!-- <label style="visibility: hidden;">nothing</label> -->
    <div class="input-group-append">
      <!-- <button id="export_s_p_r" class="btn recived-button " >Export</button>   -->
      <!-- <button value="Export" class="btn recived-button rounded export-btn">Create New Export</button>  -->

      <span class="export-btn vertical-icons" title="Create New Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
    </div>
  </div>
   @endif
</div>


{{--Filters start here--}}


<div class="row mb-0 filters_div">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex row justify-content-between">
      <div class="col-md-2 col-6">
        <label>Choose Category</label>
        <select class="font-weight-bold form-control-lg form-control product_category_id state-tags" name="category_id" >
         <option value="" selected="">Choose Category</option>
         <hr>
          @foreach($parentCat as $ppc)
          <option value="{{'pri-'.$ppc->id}}" title="{{@$ppc->title}}">{{$ppc->title}}{!! $extra_space !!}{{$ppc->get_Child != null ? $ppc->get_Child->pluck('title') : ''}}</option>
          @foreach($ppc->get_Child as $sc)
            <option value="{{'sub-'.$sc->id}}" title="{{@$sc->title}}">{{$sc->title}}
              {!! $extra_space !!} {{$ppc->title}} </option>
          @endforeach
          @endforeach
        </select>
      </div>
      <!-- <div class="col-lg-4 col-md-4">
      <label><b>Choose Product</b></label>
         <div class="border rounded custom-input-group autosearch position-relative">
          <input type="text" class="font-weight-bold form-control-lg form-control search_product_with_cat" id="header_product_search" tabindex="0" name="prod_name_category" placeholder="Choose Product / Primary Category / Sub Category" autocomplete="off" value="{{$product_code}}" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
        </div>
        <span id="loader__custom_search" class="position-absolute d-none" style="right:27px;top:43%;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows custom__search_arrows2" aria-hidden="true"></i>
        <p id="product-ul-div" class="m-0"></p>
      </div> -->

      <div class="col-md-2 col-6">
        <label>Sales Person</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting_sale_person">
            <option value="">All Sales People</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}" {{$person->id == @$saleid ? 'selected' : '' }}>{{$person->name}}</option>
            @endforeach
        </select>
      </div>

      <div class="col-md-2 col-6">
        <label>All Items</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags filter-dropdown" name="filter">
          <option value="" selected="">All Items</option>
          <option value="stock">In Stock</option>
          <option value="reorder">Reorder Items</option>
          <option value="dicount_items">Discounted Items</option>
        </select>
      </div>

      <div class="col-md-2 col-6">
        <label>Select Order Status</label>
        <select class="font-weight-bold form-control-lg form-control order_type state-tags" name="order_type" >
          @if($inv_ty != '')
            <option value="">All (Draft & Invoices)</option>
            <option value="2">Draft Invoice</option>
            <option value="10" >Reserved</option>
            <option value="3" selected="">All Invoice</option>
            <option value="37">All Manual Orders</option>
            <option value="38">Invoices & Manual Orders</option>
            @if($showRadioButtons == 1)
              <option value="0">Tax Invoices</option>
              <option value="1">Non Tax Invoices</option>
            @endif
          @else
            <option value="">All (Draft & Invoices)</option>
            <option value="2"  >Draft Invoice</option>
            @if($draft != '' || $draft != NULL)
              <option value="10" {{$draft}}>Reserved</option>
              <option value="3">All Invoice</option>
            @else
              <option value="10">Reserved</option>
              <option value="3" selected="">All Invoice</option>
            @endif
            <option value="37">All Manual Orders</option>
            <option value="38">Invoices & Manual Orders</option>
            @if($showRadioButtons == 1)
              <option value="0">Tax Invoices</option>
              <option value="1">Non Tax Invoices</option>
            @endif
          @endif
        </select>
      </div>

      <div class="col-md-2 col-6">
        <label> Select Warehouse </label>
         <select class="font-weight-bold form-control-lg form-control warehouse_id state-tags" name="warehouse_id" >
          @if(Auth::user()->role_id == 9)
          <option value="">All Warehouses</option>
          @foreach($warehouses as $w)
          @if($w->id == 1)
          <option value="{{$w->id}}" selected="" {{ ( $w->id == $warehouse_id) ? 'selected' : '' }} >{{$w->warehouse_title}}</option>
          @else
          <option value="{{$w->id}}" {{ ( $w->id == $warehouse_id) ? 'selected' : '' }} >{{$w->warehouse_title}}</option>
          @endif
          @endforeach
          @else
          <option value="">All Warehouses</option>
          @foreach($warehouses as $w)
          @if($w_id != '')
          <option value="{{$w->id}}" {{ ( $w->id == $w_id) ? 'selected' : '' }} >{{$w->warehouse_title}}</option>
          @else
          <option value="{{$w->id}}" {{ ( $w->id == $warehouse_id) ? 'selected' : '' }} >{{$w->warehouse_title}}</option>
          @endif
          @endforeach
          @endif
        </select>
      </div>
      <div class="col-md-2 col-6">
        <label>Choose Product Type</label>
        <select class="font-weight-bold form-control-lg form-control product_type state-tags" name="product_type" >
         <option value="" selected="">Choose Product Type</option>
          @foreach($product_types as $s)
          <option value="{{$s->id}}">{{$s->title}}</option>
          @endforeach
        </select>
      </div>

      <!-- <div class="col-2"></div> -->
    </div>
  </div>
  <div class="col-md-12 title-col">
    <div class="d-sm-flex row justify-content-between">



      <!-- <div class="col-2">
        <label>Choose Customers</label>
         <div class="border rounded position-relative custom-input-group autosearch">

          <span class="input-group-text fa fa-search"></span>

          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_customer_search" tabindex="0" name="prod_name" placeholder="Choose Customers / Customer Group" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">




          <input class="searchbox form-control pl-1 d-none" id="myInput" type="search" name="mysearches" value="" maxlength="1000" autocapitalize="off" autocomplete="off" placeholder="Search Products">

        </div>
        <span id="loader__custom_search_customer" class="position-absolute d-none" style="right:27px;top:43%;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows_customer custom__search_arrows_customer2" aria-hidden="true"></i>
        <p id="myIddd" class="m-0"></p>
      </div> -->
      @if (in_array('product_type_2', $product_detail_section))
      <div class="col-md-2 col-6">
        <label>Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</label>
        <select class="font-weight-bold form-control-lg form-control product_type_2 state-tags" name="product_type_2" >
         <option value="" selected="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
          @foreach($product_types_2 as $s)
          <option value="{{$s->id}}">{{$s->title}}</option>
          @endforeach
        </select>
      </div>
      @endif

      @if (in_array('product_type_3', $product_detail_section))
      <div class="col-md-2 col-6">
        <label>Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</label>
        <select class="font-weight-bold form-control-lg form-control product_type_3 state-tags" name="product_type_3" >
         <option value="" selected="">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
          @foreach($product_types_3 as $s)
          <option value="{{$s->id}}">{{$s->title}}</option>
          @endforeach
        </select>
      </div>
      @endif

      <div class="col-md-2 col-6" id="customer-group">
        <label>Choose Customers</label>
        <div class="form-group">
          <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
              <option value="">Choose Customer / Group</option>
              @foreach($getCategories as $cat)
                <option value="{{'cat-'.@$cat->id}}" class="parent" title="{{@$cat->title}}">{{@$cat->title}} {!! $extra_space !!}{{$cat->customer != null ? $cat->customer->pluck('reference_name') : ''}}</option>
                  @foreach($cat->customer as $customer)
                    <option value="{{'cus-'.$customer->id}}" class="child-{{@$cat->id}}" title="{{@$customer->reference_name}}" >&nbsp;&nbsp;&nbsp;{{@$customer->reference_name}}{!! $extra_space !!}{{$cat->title}}</option>
                  @endforeach
              @endforeach
          </select>
        </div>
      </div>

      <div class="col-md-2 col-6">
        <label>Choose Product</label>
        <select class="font-weight-bold form-control-lg form-control product_id state-tags" name="product_id" id="choose_product_select">
          <option value="" selected="">Choose Product</option>
          @foreach($products as $s)
          @if($p_id != '')
            <option value="{{$s->id}}" {{ ( $s->id == $p_id) ? 'selected' : '' }}>{{$s->refrence_code}} -  {{$s->short_desc}}</option>
          @else
            <option value="{{$s->id}}" {{ ( $s->id == $product_id) ? 'selected' : '' }}>{{$s->refrence_code}} -  {{$s->short_desc}}</option>
          @endif
          @endforeach
        </select>
      </div>

      @if(Auth::user()->role_id != 9)
      <div class="col-md-2 col-6">
        <label>Choose Supplier</label>
        <select class="font-weight-bold form-control-lg form-control supplier_id state-tags" name="supplier_id" >
          <option value="" selected="">Choose Supplier</option>
          @foreach($suppliers as $sup)
          <option value="{{$sup->id}}">{{$sup->reference_name}}</option>
          @endforeach
        </select>
      </div>
      @endif

      <!-- <div class="col-2">
        <label>Invoice Status</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags inv-st-dropdown" name="inv-dropdown">
          @if($inv_ty != '')
          <option value="all">Both</option>
          <option value="draft_inv">Draft Invoice</option>
          <option value="invoice" selected="">Invoice</option>
          @else
          <option value="all" selected="">Both</option>
          <option value="draft_inv">Draft Invoice</option>
          <option value="invoice">Invoice</option>
          @endif
        </select>
      </div> -->
      <div class="col-md-2"></div>
      <div class="col-md-2"></div>
    </div>
  </div>
  <div class="col-md-12 title-col">
    <div class="d-sm-flex row justify-content-between">
      <div class="form-group col-md-2 col-6">
        <label>From Date</label>
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>

      <div class="form-group col-md-2 col-6">
        <label>To Date</label>
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>

      <div class="col-md-1 col-5" style="margin-top: 2%; padding-left: 35px">
        <input type="radio" class="form-check-input dates_changer created_date_input" id="create_date" name="date_radio" value='2' {{$from_supplier_margin_id != null ? 'checked' : ''}}>
        <label class="form-check-label" for="exampleCheck1"><b>Created Date</b></label>
      </div>
      <div class="col-md-7 col-5 d-flex" style="margin-top: 2%;">
        <input type="radio" class="form-check-input dates_changer delivery_date_input" id="delivery_date"  name="date_radio" value='1' {{$from_supplier_margin_id != null ? '' : 'checked'}}>
        <label class="form-check-label" for="exampleCheck1"><b>Delivery Date</b></label>
        <div style="margin-left: 50px !important;">
            <input type="radio" class="form-check-input dates_changer target_ship_date_input" id="target_ship_date"  name="date_radio" value='0' {{$from_supplier_margin_id != null ? '' : 'checked'}}>
            <label class="form-check-label" for="exampleCheck1"><b>Target Ship Date</b></label>
        </div>
      </div>
      </div>
  </div>

  <div class="col-md-12 title-col">
    <div class="d-sm-flex row justify-content-between">

      <div class="col-md-2"></div>

      <div class="col-md-2 col-6" style="">
       <!--  <div class="form-group">
         <label><b style="visibility: hidden;">Reset</b></label>
        <div class="input-group-append ml-3">
          <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Filters</button>
        </div>
        </div> -->
      </div>

      <div class="col-md-2 col-6" style="margin-top: 6px;">
        <label></label>
        <div class="input-group-append pull-right">
          <!-- <button class="btn recived-button reset-btn rounded" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>   -->

           <span class="apply_date vertical-icons mr-4" title="Apply Filters" id="apply_filter">
            <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
          </span>
          <span class="vertical-icons reset-btn" id="reset-btn" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
        </div>
      </div>

    </div>
  </div>
</div>
{{--Filters ends here--}}


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="alert alert-primary export-alert d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is being prepared! Please wait.. </b>
      </div>
      <div class="alert alert-success export-alert-success d-none"  role="alert">
        <i class=" fa fa-check "></i>

        <b>Export file is ready to download.
        <!-- <a download href="{{'storage/app/Sold-Products-Report.xlsx'}}"><u>Click Here</u></a> -->
        <a class="exp_download" href="{{ url('get-download-xslx','Sold-Products-Report.csv')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>
      <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is already being prepared by another user! Please wait.. </b>
      </div>
      <table class="table entriestable table-bordered text-center sold-products-report no-footer">

        <thead>
          <tr>
            <th>Order#
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Status
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="status">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="status">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Ref. Po#
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="ref_po_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="ref_po_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>PO #
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Customer #</th>
            <th>Customer
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Billing Name</th>
            <th>Tax ID</th>
            <th>Reference Address</th>
            <th>Sale Person
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sale_person">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sale_person">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Delivery <br> Date
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="delivery_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="delivery_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Created <br> Date
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="created_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="created_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Target Ship <br> Date
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_ship_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_ship_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['supply_from']}}
             <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="5">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="5">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['category']}} / {{$global_terminologies['subcategory']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="category">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="category">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Product Type
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="type">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="type">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['brand']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="6">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="6">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th> {{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="4">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="4">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if (!in_array('product_type_2', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="type_2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="type_2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if (!in_array('product_type_3', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="type_3">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="type_3">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
            <th class="dynamic_header">Available Stock<br> (All Available Warehouse)
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="available_stock">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="available_stock">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Selling<br>Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['qty']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Pieces
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="pieces">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="pieces">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Unit Price
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Discount %
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="discount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="discount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="unit_cogs_index">{{$global_terminologies['net_price']}} <br> (THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="net_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="net_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="total_cogs_index">Total {{$global_terminologies['net_price']}} <br> (THB)
             <!--  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_net_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_net_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Sub Total
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="7">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="7">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Total<br>Amount
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="8">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="8">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Vat(THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat_thb">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat_thb">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Vat %
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat_percent">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat_percent">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['note_two']}}
             <!--  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="note_two">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="note_two">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Total Margin
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_margin">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_margin">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>Margin %
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="margin_percent">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="margin_percent">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            @if($getCategories->count() > 0)

            @foreach($getCategories as $cat)
              <th>{{$cat->title}}<br>( Fixed Price )
                <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="{{$cat->title}}">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="{{$cat->title}}">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
              </th>
            @endforeach
            @endif
          </tr>
        </thead>

        <tfoot align="right">
          <tr>
            <th id="total"></th>
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
            <th style="text-align: right !important;" id="total_quantity"></th>
            <th style="text-align: right !important;" id="total_pieces"></th>
            <th style="text-align: right !important;" id="total_cost_unit_total"></th>
            <th ></th>
            <th style="text-align: right !important;" id="cogs_per_line"></th>
            <th style="text-align: right !important;" id="total_cogs"></th>
            <th style="text-align: right !important;" id="sub_total"></th>
            <th style="text-align: right !important;" id="total_amount"></th>
            <th style="text-align: right !important;" id="vat_total"></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            @if($getCategories->count() > 0)

            @foreach($getCategories as $cat)
              <th>
              </th>
            @endforeach
            @endif
          </tr>
        </tfoot>

      </table>
    </div>
  </div>
</div>
@php
  if($draft == 'selected')
  {
    $transfer_class = '';
  }
  else
  {
    $transfer_class = 'd-none';
  }
@endphp
<div class="row entriestable-row mt-3 {{$transfer_class}} transfer-reserved-table">
  <div class="col-12">

    <div class="entriesbg bg-white custompadding customborder">
    <div>
      <h4>Transfers</h4>
    </div>
      <table class="table entriestable table-bordered text-center sold-products-report-transfer" style="width: 100%;">

        <thead>
          <tr>
            <th>TD#</th>
            <th>Inbound PO(s)</th>
            <th>Status</th>
            <th>{{$global_terminologies['our_reference_number']}}</th>
            <th>{{$global_terminologies['product_description']}}</th>
            <th>Quantity</th>
            <th>Supply From</th>
            <th>Supply To</th>
          </tr>
        </thead>

        <tfoot align="right">
          <tr>
           <th id="total_transfer"></th>
           <th></th>
           <th></th>
           <th></th>
           <th></th>
           <th id="total_quantity_transfer"></th>
           <th></th>
           <th></th>
          </tr>
        </tfoot>

      </table>
    </div>
  </div>
</div>
<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="row mt-4">
    <div class="col-lg-10 col-md-9">
      <h4>History</h4>
      </div>
    </div>
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
         <table class="table-history entriestable table table-bordered text-center table-theme-header" style="width: 98%;font-size: 12px;overflow: hidden;">
          <thead>
            <tr>
              <th>User  </th>
              <th>Date/time </th>
              <th>Order # </th>
              <th>Product </th>
              <th>Column</th>
              <th>Old Value</th>
              <th>New Value</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!--  Content End Here -->
<div class="modal cogs_detail" id="cogs_detail">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
  <h4 class="modal-title">COGS Details</h4>
  <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">
  <div id="loader_show">
    <h3 style="text-align:center;">Please wait</h3>
    <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
  </div>
  <div id="live_data"></div>
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
<input type="hidden" name="customer_id_select" id="customer_id_select" value="{{$cust_id != 'null' && $cust_id != null ? 'cus-'.$cust_id : ''}}">

<form id="export_sold_product_report_form" method="post"  action="{{route('export-sold-products-report') }}">
  @csrf
  <input type="hidden" name="warehouse_id_exp" id="warehouse_id_exp">
  <input type="hidden" name="product_type_exp" id="product_type_exp">
  <input type="hidden" name="product_type_2_exp" id="product_type_2_exp">
  <input type="hidden" name="product_type_3_exp" id="product_type_3_exp">
  <input type="hidden" name="order_type_exp" id="order_type_exp">
  <input type="hidden" name="customer_id_exp" id="customer_id_exp">
  <input type="hidden" name="product_id_exp" id="product_id_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <!-- <input type="hidden" name="prod_sub_category_exp" id="prod_sub_category_exp"> -->
  <input type="hidden" name="sale_person_id_exp" id="sale_person_id_exp">
  <input type="hidden" name="filter_exp" id="filter_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="supplier_id_exp" id="supplier_id_exp">
  <input type="hidden" name="date_radio_exp" id="date_radio_exp">
  <input type="hidden" name="available_stock_val" id="available_stock_val" value="Available Stock (All Available Warehouse)">
  <input type="hidden" name="p_c_id_exp" id="p_c_id_exp" value="{{$cat_id}}">
  <input type="hidden" name="c_ty_id_exp" id="c_ty_id_exp" value="{{$c_ty_id}}">
  <input type="hidden" name="saleid_exp" id="saleid_exp" value="{{$saleid}}">
  <input type="hidden" name="draft" id="draft" value="{{$draft}}">
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="product_id_select" id="product_id_select" value="{{$product_class}}">
  <input type="hidden" name="className" id="className">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
  <input type="hidden" name="from_supplier_margin_id" id="sortbyvalue" value="{{$from_supplier_margin_id}}">
@php
  $filter=null;
  if(isset($_GET['filter']))
  {
    $filter=$_GET['filter'];
  }
@endphp
  <input type="hidden" name="filter" id="filter" value="{{$filter}}">
</form>
<!-- Modal for Showing Notes  -->
<div class="modal" id="notes-modal" style="width:600px; margin-left: 400px;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Notes</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-notes">
            <div class="adv_loading_spinner3 d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
@endsection
@php
  $hidden_by_default = '';
@endphp
@section('javascript')
<script type="text/javascript">



  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });


  var filter = "{{$filter}}";
  var from_date = "{{$from_date}}";
  var to_date = "{{$to_date}}";
  if(filter !== "no")
  {
    var f_date = "{{$f_date}}";
    var t_date = "{{$t_date}}";
    if(f_date != 'NoDate' && f_date != 'null')
    {
      $("#from_date").datepicker('setDate',f_date);
    }
    else
    {
      $('#from_date').val('');
    }

    if(t_date != 'NoDate' && t_date != 'null')
    {
      $("#to_date").datepicker('setDate',t_date);
    }
    else
    {
      $('#to_date').val('');
    }

    if(f_date != '' && t_date != '')
    {
      if(f_date != 'NoDate' && t_date != 'NoDate')
      {
        $("#from_date").datepicker('setDate',f_date);
        $("#to_date").datepicker('setDate',t_date);
      }
      else
      {
        $('#from_date').val('');
        $('#to_date').val('');
      }
    }
    else
    {
      // $('#from_date').val('');
      // $('#to_date').val('');
      // var date = new Date();
      // $("#from_date").datepicker('setDate',new Date(date.getFullYear(), date.getMonth(), 1));
      // $("#to_date").datepicker('setDate',new Date());
      $("#from_date").datepicker('setDate', last_month);
        $("#to_date").datepicker('setDate',t_date);
    }
  }
  else if(filter == 'no')
  {
    $('#from_date').val('');
    $('#to_date').val('');
  }
  else
  {
    if(from_date !== null || from_date !== '')
    {
      $("#from_date").datepicker('setDate',from_date);
    }
    if(to_date !== null || to_date !== '')
    {
      $("#to_date").datepicker('setDate',to_date);
    }
  }

  @if(Session::has('find'))
  var customer_id = "cus-{{Session::get('customer_id')}}";
  var customer_name = "{{Session::get('customer_name')}}";
  var supplier_id = "{{Session::get('supplier_id')}}"
  var product_id  = "{{Session::get('product_id')}}";
  var from_date  = "{{Session::get('from_date')}}";
  var to_date  = "{{Session::get('to_date')}}";
  var date_type  = "{{Session::get('date_type')}}";

  if(from_date != 'NoDate')
  {
    from_date = from_date.replace("-","/").replace("-","/");
    from_date_split =  from_date.split("/");
    from_date  = from_date_split[2]+'/'+from_date_split[1]+'/'+from_date_split[0];
    document.querySelector("#from_date").value = from_date;
  }
  else
  {
    document.querySelector("#from_date").value = '';
  }
  if(to_date != 'NoDate')
  {
    to_date = to_date.replace("-","/").replace("-","/");
    to_date_split =  to_date.split("/");
    to_date  = to_date_split[2]+'/'+to_date_split[1]+'/'+to_date_split[0];
    document.querySelector("#to_date").value = to_date;
  }
  else
  {
    document.querySelector("#to_date").value = '';
  }

  if(date_type == 2)
  {
    document.getElementById("create_date").checked = true;
  }
  else if(date_type == 1)
  {
    document.getElementById("delivery_date").checked = true;
  }
  else
  {
    document.getElementById("target_ship_date").checked = true;
  }
  if(customer_id != 'NA')
  {
    $('.customer_id').val(customer_name).change();
    $('.customer_id').attr('data-id',customer_id );
    $("#customer_id_exp").val(customer_id);
  }
  if(supplier_id != 'NA')
  {
    $('.supplier_id').val(supplier_id).change();
    $("#supplier_id_exp").val(supplier_id);
  }

  $('.product_id').val(product_id).change();
  $("#product_id_exp").val(product_id);

  @endif

  var from_complete_list = "{{$from_complete_list}}";
  if(from_complete_list == 'yes'){
    $('#from_date').val('');
    $('#to_date').val('');
  }

  var date = $('#from_date').val();
  $("#from_date_exp").val(date);
  var date = $('#to_date').val();
  $("#to_date_exp").val(date);


  $('input[type=radio][name=date_radio]').change(function() {
    $("#apply_filter_btn").val("1");
    if (this.value == '1')
    {
      $('#date_radio_exp').val(this.value);
      // $("#loader_modal").modal('show');
      // $('.sold-products-report').DataTable().ajax.reload();
    }
    else if (this.value == '2')
    {
      $('#date_radio_exp').val(this.value);
      // $("#loader_modal").modal('show');
      // $('.sold-products-report').DataTable().ajax.reload();
    }
    else if (this.value == '0')
    {
      $('#date_radio_exp').val(this.value);
      // $("#loader_modal").modal('show');
      // $('.sold-products-report').DataTable().ajax.reload();
    }
  });

  var p_c_id  = "{{$cat_id}}";
  var c_ty_id = "{{$c_ty_id}}";
  var saleid  = "{{$saleid}}";
  var draft   = "{{$draft}}";

  var hidden_cols = "{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}";
  hidden_cols = hidden_cols.split(',');

  $(function(e){
    var role = "{{Auth::user()->role_id}}";
    var to_hide = '';
    if(role == 3 || role == 4 || role == 6)
    {
      var unit_cogs_index = $('.unit_cogs_index').index();
      var total_cogs_index = $('.total_cogs_index').index();
      to_hide = [unit_cogs_index,total_cogs_index];
    }
    if(role == 3 || role == 4 || role == 6)
    {
      var cogs_show1 = false;
      var cogs_show2 = false;
    }
    else
    {
      var cogs_show1 = true;
      var cogs_show2 = true;

      if( hidden_cols.includes("18") )
      {
        var cogs_show1 = false;
      }
      if( hidden_cols.includes("19") )
      {
        var cogs_show2 = false;
      }

    }

    $(".state-tags").select2();
    var from_supplier_margin_from_date = "{{$from_supplier_margin_from_date}}";
    var from_supplier_margin_to_date = "{{$from_supplier_margin_to_date}}";
    if(from_supplier_margin_from_date != null && from_supplier_margin_from_date != '')
      $("#from_date").datepicker('setDate',from_supplier_margin_from_date);
    if(from_supplier_margin_to_date != null && from_supplier_margin_to_date != '')
      $("#to_date").datepicker('setDate',from_supplier_margin_to_date);
    // alert(from_supplier_margin_from_date);
    var from_supplier_margin = "{{$from_supplier_margin}}";
    if(from_supplier_margin)
      var table_url = "{!! route('get-sold-product-data-for-supplier-report') !!}";
    else
      var table_url = "{!! route('get-sold-product-data-for-report') !!}";
    var table2 = $('.sold-products-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      searching:false,
      ordering: false,
      colReorder: {
      realtime: false
      },
      dom: 'Blfrtip',
      // buttons: [
      //   {
      //     extend: 'colvis',
      //     // columns: ':eq(18),:eq(19)'
      //   }
      // ],
    //   oLanguage:
    //   {
    //     sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
    //   },
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      serverSide: true,
      "aaSorting": [[3]],
      bSort: false,
      info: true,
      "columnDefs": [
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,10,11 ] },
        { className: "dt-body-right", "targets": [ 12 ] },
        {
          targets: to_hide,
          className: 'noVis'
        }
      ],
      buttons: [
        {
          extend: 'colvis',
          columns: ':not(.noVis)'
        }
      ],
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      // "pageLength": 10,
      lengthMenu: [ 100,200,300,400,500],
      ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        url:table_url,
        data: function(data) {
          data.from_date = $('#from_date').val(),
          data.to_date = $('#to_date').val(),
          data.warehouse_id = $('.warehouse_id option:selected').val(),
          data.product_type = $('.product_type option:selected').val(),
          data.product_type_2 = $('.product_type_2 option:selected').val(),
          data.product_type_3 = $('.product_type_3 option:selected').val(),
          // data.customer_id = $('#customer_id_select').val(),
          data.customer_id = $('.selecting-customer-group option:selected').val(),
          data.supplier_id = $('.supplier_id option:selected').val(),
          data.product_idd = $('#header_prod_searchh').data('prod_id'),
          data.product_id = $('.product_id option:selected').val(),
          data.order_type = $('.order_type option:selected').val(),
          data.product_id_select = $('#product_id_select').val(),
          data.sale_person_id = $('.selecting_sale_person option:selected').val(),
          data.filter = $('.filter-dropdown option:selected').val(),
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.date_type =  $("input[name='date_radio']:checked").val(),
          data.p_c_id = p_c_id,
          data.c_ty_id = c_ty_id,
          data.saleid = saleid,
          data.draft = draft,
          data.prod_category = $('.product_category_id option:selected').val(),
          data.className = className,
          data.type = 'datatable',
          data.from_supplier_margin_id = "{{@$from_supplier_margin_id}}"
        },
        method: "post",
      },
      columns: [
        { data: 'ref_id', name: 'ref_id'},
        { data: 'status', name: 'status'},
        { data: 'ref_po_no', name: 'ref_po_no'},
        { data: 'po_no', name: 'po_no'},
        { data: 'customer_no', name: 'customer_no'},
        { data: 'customer', name: 'customer' },
        { data: 'billing_name', name: 'billing_name' },
        { data: 'tax_id', name: 'tax_id' },
        { data: 'reference_address', name: 'reference_addresser' },
        { data: 'sale_person', name: 'sale_person' },
        { data: 'delivery_date', name: 'delivery_date' },
        { data: 'created_date', name: 'created_date' },
        { data: 'target_ship_date', name: 'target_ship_date'},
        { data: 'supply_from', name: 'supply_from','orderable': true },
        // { data: 'warehouse', name: 'warehouse' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'primary_sub_cat', name: 'primary_sub_cat' },
        { data: 'product_type', name: 'product_type' },
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'vintage', name: 'vintage' @if (!in_array('product_type_2', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif },
        { data: 'product_type_3', name: 'product_type_3' @if (!in_array('product_type_3', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
        { data: 'available_stock', name: 'available_stock' },
        { data: 'unit', name: 'unit' },
        { data: 'sum_qty', name: 'sum_qty' },
        { data: 'sum_piece', name: 'sum_piece' },
        { data: 'cost_unit', name: 'cost_unit' },
        { data: 'discount_value', name: 'discount_value' },

        { data: 'item_cogs', name: 'item_cogs', visible: cogs_show1  },
        { data: 'cogs', name: 'cogs', visible: cogs_show2  },
        { data: 'sub_total', name: 'sub_total' },
        { data: 'total_cost', name: 'total_cost' },
        { data: 'vat_thb', name: 'vat_thb' },
        { data: 'vat', name: 'vat' },
        { data: 'note', name: 'note' },
        { data: 'total_margin', name: 'total_margin' },
        { data: 'margin_percent', name: 'margin_percent' },

         // Dynamic columns start
        @if($getCategories->count() > 0)
        @foreach($getCategories as $cat)
          { data: '{{$cat->title}}', name: '{{$cat->title}}'},
        @endforeach
        @endif
        // Dynamic columns end
      ],
      initComplete: function () {
        // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow-x', 'scroll');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

        @if(@$display_prods)
           table2.colReorder.order( [{{@$display_prods->display_order}} ]);
        @endif

        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");

      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
        $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
      },
      rowCallback: function( row, data, index ) {
        if (data["sum_qty"] <= 0)
        {
          $(row).hide();
        }
      },
      footerCallback: function ( row, data, start, end, display ) {
        // var api = this.api();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"post",
          dataType:"json",
          url:table_url,
          data:{
            from_date : $('#from_date').val(),
            to_date : $('#to_date').val(),
            warehouse_id : $('.warehouse_id option:selected').val(),
            product_type : $('.product_type option:selected').val(),
            product_type_2 : $('.product_type_2 option:selected').val(),
            product_type_3 : $('.product_type_3 option:selected').val(),
            //customer_id : $('.customer_id option:selected').val(),
            // customer_id : $('#customer_id_select').val(),
            customer_id : $('.selecting-customer-group option:selected').val(),
            product_idd : $('#header_prod_searchh').data('prod_id'),
            product_id : $('.product_id option:selected').val(),
            supplier_id : $('.supplier_id option:selected').val(),
            order_type : $('.order_type option:selected').val(),
            date_type :  $("input[name='date_radio']:checked").val(),
            product_id_select : $('#product_id_select').val(),
            sale_person_id : $('.selecting_sale_person option:selected').val(),
            filter : $('.filter-dropdown option:selected').val(),
            p_c_id : p_c_id,
            c_ty_id : c_ty_id,
            saleid : saleid,
            prod_category : $('.product_category_id option:selected').val(),
            draft : draft,
            type: 'footer',
            from_supplier_margin_id : "{{@$from_supplier_margin_id}}"
          },
          beforeSend:function(){

            $('tfoot th#total').html('Total');
            $('tfoot th#total_quantity').html('Loading...');
            $('tfoot th#total_pieces').html('Loading...');
            $('tfoot th#sub_total').html('Loading...');
            $('tfoot th#total_amount').html('Loading...');
            $('tfoot th#vat_total').html('Loading...');
            $('tfoot th#total_cogs').html('Loading...');
            /*$('#total').html('Total');
            $('#total_quantity').html('Loading...');
            $('#sub_total').html('Loading...');
            $('#total_amount').html('Loading...');
            $('#vat_total').html('Loading...');*/
            /*$( api.column( 0 ).footer() ).html('Total');
            $( api.column( 16 ).footer() ).html('Loading...');*/
            /*$( api.column( 16 ).footer() ).html('Loading...');
            $( api.column( 19 ).footer() ).html('Loading...');*/
            /*$( api.column( 20 ).footer() ).html('Loading...');
            $( api.column( 21 ).footer() ).html('Loading...');
            $( api.column( 22 ).footer() ).html('Loading...');*/
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          },
          success:function(result){
            var total_quantity = result.total_quantity;
            var total_quantity2 = result.total_quantity2;
            var total_manual = result.total_manual;
            total_quantity = total_quantity+total_quantity2+total_manual;

            var total_cogs_order = result.grand_cogs;
            var total_cogs_manual = result.total_cogs_manual;

            var grand_cogs = total_cogs_order + total_cogs_manual;

            // var total_quantity = result.manual_quantity;

            $('tfoot th#total').html('Total');
            $('tfoot th#total_quantity').html(total_quantity.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            $('tfoot th#total_pieces').html(result.total_pieces.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            $('tfoot th#sub_total').html(result.sub_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            $('tfoot th#total_amount').html(result.total_price_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            $('tfoot th#vat_total').html(result.total_vat_thb.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            $('tfoot th#total_cogs').html(grand_cogs.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));

            // $( api.column( 0 ).footer() ).html('Total');
            // $( api.column( 16 ).footer() ).html(total_quantity.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            /*$( api.column( 16 ).footer() ).html(result.cost_unit_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));*/
            /*$( api.column( 19 ).footer() ).html(result.grand_cogs.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));*/
            // $( api.column( 20 ).footer() ).html(result.sub_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            // $( api.column( 21 ).footer() ).html(result.total_price_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            // $( api.column( 22 ).footer() ).html(result.total_vat_thb.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

            // $('#total').html('Total');
            // $('#total_quantity').html(total_quantity.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            // $('#total_cost_unit_total').html(result.cost_unit_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            // $('#sub_total').html(result.sub_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
            // $('#total_amount').html(result.total_price_total.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));


          },
          error: function(){

          }
        });
      },

    });

    table2.on( 'column-reorder', function ( e, settings, details ) {
      $.get({
        url : "{{ route('column-reorder') }}",
        dataType : "json",
        data : "type=product_sales_report_detail&order="+table2.colReorder.order(),
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
      //console.log(table.colReorder.order());
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
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'sold_product_report',column_id:col},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
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

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13)
      {
        table2.search($(this).val()).draw();
      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });
  // For Sorting Start Code
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#sortbyparam').val(column_name);
    $('#sortbyvalue').val(order);

    // $('.sold-products-report').DataTable().ajax.reload();
    $('.sold-products-report').DataTable().ajax.reload();

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
  // For Sorting Ending Code

  $('.warehouse_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#warehouse_id_exp").val($('.warehouse_id option:selected').val());
  });
  $('.product_type').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_type_exp").val($('.product_type option:selected').val());
  });
  $('.product_type_2').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_type_2_exp").val($('.product_type_2 option:selected').val());
  });
  $('.product_type_3').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_type_3_exp").val($('.product_type_3 option:selected').val());
  });

  $('.supplier_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#supplier_id_exp").val($('.supplier_id option:selected').val());

    // if($('.supplier_id').val() != '')
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //       keyboard: false
    //     });
    //   $("#loader_modal").modal('show');
    //   $('.sold-products-report').DataTable().ajax.reload();
    // }
  });

  /*$('.customer_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#

    ").val($('.customer_id option:selected').val());

    // if($('.customer_id').val() != '')
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //       keyboard: false
    //     });
    //   $("#loader_modal").modal('show');
    //   $('.sold-products-report').DataTable().ajax.reload();
    // }
  });*/

  $('.product_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_id_exp").val($('.product_id option:selected').val());

    // if($('.product_id').val() != '')
    // {
    //   $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    //   $("#loader_modal").modal('show');
    //   $('.sold-products-report').DataTable().ajax.reload();
    //   $('.sold-products-report-transfer').DataTable().ajax.reload();
    // }
  });

  $('.order_type').change(function() {
    $("#apply_filter_btn").val("1");
    var val = $('.order_type option:selected').val();
    $("#order_type_exp").val($('.order_type option:selected').val());

    // if($('.order_type').val() != '')
    // {
    //   $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    //   $("#loader_modal").modal('show');
    //   $('.sold-products-report').DataTable().ajax.reload();

    //   if(val == 10)
    //   {
    //     $('.sold-products-report-transfer').DataTable().ajax.reload();
    //     $('.transfer-reserved-table').removeClass('d-none');
    //   }
    // }
    // else
    // {
    //   $('.sold-products-report').DataTable().ajax.reload();
    //   if(val == 10)
    //   {
    //     $('.sold-products-report-transfer').DataTable().ajax.reload();
    //     $('.transfer-reserved-table').removeClass('d-none');
    //   }
    // }
  });

  $(document).on('change','.product_category_id',function(){
    $("#apply_filter_btn").val("1");
    $("#prod_category_exp").val($('.product_category_id option:selected').val());
    var selected = $(this).val();
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.sold-products-report').DataTable().ajax.reload();
    // $('.sold-products-report-transfer').DataTable().ajax.reload();
  });

  $(document).on('change','.sub_category_id',function(){
    $("#apply_filter_btn").val("1");
    $("#prod_sub_category_exp").val($('.sub_category_id option:selected').val());
    var selected = $(this).val();
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.sold-products-report').DataTable().ajax.reload();
    // $('.sold-products-report-transfer').DataTable().ajax.reload();
  });

  $(document).on('change','.selecting_sale_person',function(){
    $("#apply_filter_btn").val("1");
    $("#sale_person_id_exp").val($('.selecting_sale_person option:selected').val());
    var selected = $(this).val();
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.sold-products-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('change','.filter-dropdown',function(){
    $("#apply_filter_btn").val("1");
    $("#filter_exp").val($('.filter-dropdown option:selected').val());
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.sold-products-report').DataTable().ajax.reload();
  });

  $('#from_date').change(function() {
    $("#apply_filter_btn").val("1");
    var date = $('#from_date').val();
    $("#from_date_exp").val(date);
  });

  $('#to_date').change(function() {
    $("#apply_filter_btn").val("1");
    var date = $('#to_date').val();
    $("#to_date_exp").val(date);
  });
  $(document).on('click','.apply_date',function(){
    $("#apply_filter_btn").val("0");
      var flagges = false;
      var val = $('.order_type option:selected').val();
      if(val == 10)
      {
        flagges = true;
        if('{{$draft}}' == 'selected'){
        $('.sold-products-report-transfer').DataTable().ajax.reload();
        }
        $('.transfer-reserved-table').removeClass('d-none');
      }

      /*for a category and sub category*/
      if(($('.sub_category_id').val() != '' || $('.product_category_id').val() != '' || $('.product_id').val() != '' || $('.dynamic_header').length <= 0) && flagges == false)
      {
        if('{{$draft}}' == 'selected'){
        $('.sold-products-report-transfer').DataTable().ajax.reload();
        }
      }

      /*This if condition is for a dynamic name change if warhouse filter is selected*/
      if($('.dynamic_header').length > 0)
      {
        var header_value = $('.dynamic_header').html();
        if($('.warehouse_id').val() != '')
        {
          var val = $('.warehouse_id option:selected').text();
          if(header_value.includes("("))
          {
            var new_val = header_value.split("(")[0];
          }
          else
          {
            var new_val = header_value;
          }
          var value = new_val+'('+val+' Available )';
          $('.dynamic_header').html(value);
          $('#available_stock_val').val(value.replace(/(<|&lt;)br\s*\/*(>|&gt;)/g,' '));
          if('{{$draft}}' == 'selected'){
          $('.sold-products-report-transfer').DataTable().ajax.reload();
          }
        }
        else
        {
          if(header_value.includes("("))
          {
            var new_val = header_value.split("(")[0];
          }
          else
          {
            var new_val = header_value;
          }
          var value = new_val+'(All Available Warehouse)';
          $('.dynamic_header').html(value);
          $('#available_stock_val').val(value.replace(/(<|&lt;)br\s*\/*(>|&gt;)/g,' '));
          if('{{$draft}}' == 'selected'){
          $('.sold-products-report-transfer').DataTable().ajax.reload();
          }
        }
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        // $('.sold-products-report').DataTable().ajax.reload();
        // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      }
      $('.sold-products-report').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
});

  $('.reset-btn').on('click',function(){

    $('#product-ul-div').hide();

    $('#product_id_select').val(null);
    $('#header_product_search').val('');
    var role = "{{Auth::user()->role_id}}";
    $("#apply_filter_btn").val("0");
    $('.customer_id').val('');
    $('.product_id').val('');
    if(role == 9){
       $('.warehouse_id').val('1');
    }else{
      $('.warehouse_id').val('');
    }
    $('.supplier_id').val('');
    $('.product_type').val('');
    $('.product_type_2').val('');
    $('.product_type_3').val('');
    $('.order_type').val('');
    $('.product_category_id').val('');
    $('.sub_category_id').val('');
    $('.selecting_sale_person').val('');
    $('.filter-dropdown').val('');
    $('.selecting-customer-group').val('');

    $('#from_date').val('');
    $('#to_date').val('');

    $('#header_prod_searchh').val('');
    $('#header_prod_searchh').data('prod_id','');
    $('#available_stock_val').val('Available Stock (All Available Warehouse)');
    $('#warehouse_id_exp').val('');
    $('#customer_id_exp').val('');
    $('#order_type_exp').val('');
    $('#product_id_exp').val('');
    $('#product_type_exp').val('');
    $('#product_type_2_exp').val('');
    $('#product_idd_exp').val('');
    $('#prod_category_exp').val('');
    $('#filter_exp').val('');
    $('#from_date_exp').val('');
    $('#to_date_exp').val('');
    $('#supplier_id_exp').val('');
    $(".state-tags").select2("", "");

    var header_value = $('.dynamic_header').html();
    if(header_value.includes("("))
    {
      var new_val = header_value.split("(")[0];
    }
    else
    {
      var new_val = header_value;
    }
    var value = new_val+'(All Available Stock)';
    $('.dynamic_header').html(value);
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $(".state-tags").select2("", "");
    $('.sold-products-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#export_s_p_r').on('click',function(){
    $("#export_sold_product_report_form").submit();
  });

  $('#header_prod_searchh').keyup(function(event){
      keyindex = -1;
      alinks = '';
      var query = $(this).val();
      var inv_id = $("#quo_id_for_pdf").val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
          var _token = $('input[name="_token"]').val();
          $.ajax({
            url:"{{ route('purchase-fetch-product-spr') }}",
            method:"POST",
            data:{query:query, _token:_token, inv_id:inv_id},
            beforeSend: function(){
              $('#purchase_loader_productt').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div>');
            },
            success:function(data){
              $('#purchase_loader_productt').empty();
              $('#myIddd').html(data);

              alinks = $('#myIddd').find('a');
              if (alinks.length === 0)
              {
                keyindex = -1;
              }
              if (event.keyCode == 40)
              {
                event.preventDefault();
                if (alinks.length > 0 && keyindex == -1)
                {
                  keyindex = 0;
                  $('#myIddd').find('a')[keyindex++].focus();
                  var dat = $('#myIddd').find('a')[keyindex-1].text;
                  document.getElementById('header_prod_searchh').value = dat;
                  // alert(dat);
                }
              }
             },
             error: function(){

             }
          });
        }
        else if(query.length == 0)
        {
          $('#header_prod_searchh').val('');
          $('#header_prod_searchh').data('prod_id','');
          $('.sold-products-report').DataTable().ajax.reload();
        }
        else
        {
          $('#myIddd').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });

  $('#myIddd').keydown(function(e) {
      alinks = $('#myIddd').find('a');
      if (e.keyCode == 40)
      {
        e.preventDefault();
        if (keyindex == -1)
        {
          keyindex = 1;
        }
        if (alinks.length > 0 && keyindex < alinks.length)
        {
          $('#myIddd').find('a')[keyindex++].focus();
          var dat = $('#myIddd').find('a')[keyindex-1].text;
          document.getElementById('header_prod_searchh').value = dat;
          // alert(dat);
        }
      }
      if (e.keyCode == 38)
      {
        e.preventDefault();
        if (keyindex == alinks.length)
        {
          keyindex = keyindex - 2;
        }

        if (alinks.length > 0 && keyindex < alinks.length && keyindex >= 0)
        {
          if(keyindex == 0)
          {
            document.getElementById('header_prod_searchh').value = '';
            document.getElementById('header_prod_searchh').focus();
          }
          else
          {
            $('#myIddd').find('a')[--keyindex].focus();
            var dat = $('#myIddd').find('a')[keyindex].text;
            document.getElementById('header_prod_searchh').value = dat;
          }

        }
      }
    });

  $(document).ready(function(){
    $(document).on('click' , '.search_product' , function(){
      $('#header_prod_searchh').val($(this).data('prod_ref_code'));
      $('#header_prod_searchh').html($(this).data('prod_ref_code'));
      $('#header_prod_searchh').data('prod_id',$(this).data('prod_id'));

      $("#product_id_exp").val($(this).data('prod_id'));
      $('.sold-products-report').DataTable().ajax.reload();

    });
  });

  $(document).on('click','.export-btn',function(){

    // alert($('#DataTables_Table_0_filter').find('input').val());

    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    if($('#from_date').val() == '' && "{{$product_class}}" == "")
    {
      toastr.info('Info!', 'Please Select From Date' ,{"positionClass": "toast-bottom-right"});
      return;
    }
    else if($('#to_date').val() == '' && "{{$product_class}}" == "")
    {
      toastr.info('Info!', 'Please Select To Date' ,{"positionClass": "toast-bottom-right"});
      return;
    }

    var from_date = $('#from_date').val();
    var to_date = $('#to_date').val();
    from_date = from_date.split('/');
    from_date = from_date[2] + '-' + from_date[1] + "-" + from_date[0];
    to_date = to_date.split('/');
    to_date = to_date[2] + '-' + to_date[1] + "-" + to_date[0];
    var start = new Date(from_date);
    var end   = new Date(to_date);
    var diff = new Date(end - start);
    var days = diff/1000/60/60/24;
    // if (days > 91) {
    //   toastr.error('Error!', 'Only 3 months filter is allowed for export!!!' ,{"positionClass": "toast-bottom-right"});
    //   return;
    // }


    $("#supplier_id_exp").val($('.supplier_id option:selected').val());
    $("#warehouse_id_exp").val($('.warehouse_id option:selected').val());
    $("#product_type_exp").val($('.product_type option:selected').val());
    $("#product_type_2_exp").val($('.product_type_2 option:selected').val());
    $("#product_type_3_exp").val($('.product_type_3 option:selected').val());
    // alert($('.customer_id').data('id'));
    $("#customer_id_exp").val($('.selecting-customer-group option:selected').val());
    $("#product_id_exp").val($('.product_id option:selected').val());
    $("#order_type_exp").val($('.order_type option:selected').val());
    $("#prod_category_exp").val($('.product_category_id option:selected').val());
    $("#prod_sub_category_exp").val($('.sub_category_id option:selected').val());
    $("#sale_person_id_exp").val($('.selecting_sale_person option:selected').val());
    $("#filter_exp").val($('.filter-dropdown option:selected').val());
    $("#from_date_exp").val($('#from_date').val());
    $("#to_date_exp").val($('#to_date').val());
    $("#date_radio_exp").val($('input[name="date_radio"]:checked').val());

    var form=$('#export_sold_product_report_form');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-status-sold-products')}}",
      data:form_data,
      beforeSend:function(){
        $('.export-btn').attr('title','Please Wait...');
        $('.export-btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
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
      error:function(){
         $('.export-btn').attr('title','Create New Export');
        $('.export-btn').prop('disabled',false);
      }
    });
  });
  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-sold-products')}}",
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
      url:"{{route('recursive-export-status-sold-products')}}",
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
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.download-btn').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).on('click','.download-btn',function(){
    $('.export-alert-success').addClass('d-none');
  });

  $(document).on('click', '.purchase_report_w_pm', function(){
    var prod_id  = $(this).data('pid');
    var order_id = $(this).data('oid');
    var is_type  = "filter";
    // alert('yo');
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    setTimeout(function(){
      var url = "{{ url('stock-report-with-params')}}"+"/"+prod_id+"/"+order_id+"/"+is_type;
      // alert(url);
      window.open(url, '_blank');
      $("#loader_modal").modal('hide');
    }, 500);
  });
  // to check if clicked from complete products reserve column value is clicked
if('{{$draft}}' == 'selected'){
  var table5 = $('.sold-products-report-transfer').DataTable({
    // "pagingType": "input",
    "sPaginationType": "listbox",
    processing: false,
    searching:false,
    ordering: false,
    colReorder: {
    realtime: false
    },
    dom: 'Blfrtip',
    buttons: [
      {
        extend: 'colvis'
      }
    ],
    // oLanguage:
    // {
    //   sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
    // },
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    serverSide: true,
    "aaSorting": [[4]],
    bSort: false,
    info: true,
    "columnDefs": [

    ],
    retrieve: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    fixedHeader: true,
    lengthMenu: [ 100,200,300,400,500],
    ajax: {
      beforeSend: function(){
        // $('#loader_modal').modal({
        //   backdrop: 'static',
        //   keyboard: false
        // });
        // $('#loader_modal').modal('show');
      },
      url:"{!! route('get-sold-product-data-for-report-transfer') !!}",
      // data: function(data) {data.from_date = $('#from_date').val(), data.to_date = $('#to_date').val(), data.warehouse_id = $('.warehouse_id option:selected').val(),data.customer_id = $('.customer_id option:selected').val(),data.supplier_id = $('.supplier_id option:selected').val(),data.product_idd = $('#header_prod_searchh').data('prod_id'),data.product_id = $('.product_id option:selected').val(),data.order_type = $('.order_type option:selected').val(),data.prod_category = $('.product_category_id option:selected').val(),data.prod_sub_category = $('.sub_category_id option:selected').val(),data.sale_person_id = $('.selecting_sale_person option:selected').val()
      //   data.filter = $('.filter-dropdown option:selected').val(),
      //   data.sortbyparam = column_name,
      //   data.sortbyvalue = order, data.date_type =  $("input[name='date_radio']:checked").val(), data.p_c_id = p_c_id, data.c_ty_id = c_ty_id, data.saleid = saleid,
      //   data.draft = draft },

      data: function(data) {
          data.from_date = $('#from_date').val(),
          data.to_date = $('#to_date').val(),
          data.warehouse_id = $('.warehouse_id option:selected').val(),
          data.product_type = $('.product_type option:selected').val(),
          data.product_type_2 = $('.product_type_2 option:selected').val(),
          data.product_type_3 = $('.product_type_3 option:selected').val(),
          // data.customer_id = $('#customer_id_select').val(),
          data.customer_id = $('.selecting-customer-group option:selected').val(),
          data.supplier_id = $('.supplier_id option:selected').val(),
          data.product_idd = $('#header_prod_searchh').data('prod_id'),
          data.product_id = $('.product_id option:selected').val(),
          data.order_type = $('.order_type option:selected').val(),
          data.product_id_select = $('#product_id_select').val(),
          data.sale_person_id = $('.selecting_sale_person option:selected').val(),
          data.filter = $('.filter-dropdown option:selected').val(),
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.date_type =  $("input[name='date_radio']:checked").val(),
          data.p_c_id = p_c_id,
          data.c_ty_id = c_ty_id,
          data.saleid = saleid,
          data.draft = draft,
          data.className = className,
          data.prod_category = $('.product_category_id option:selected').val()
        },


      method: "get",
    },
    columns: [
      { data: 'ref_id', name: 'ref_id'},
      { data: 'inbound_po', name: 'inbound_po'},
      { data: 'status', name: 'status'},
      { data: 'refrence_code', name: 'refrence_code'},
      { data: 'short_desc', name: 'short_desc'},
      { data: 'quantity', name: 'quantity'},
      { data: 'from_warehouse', name: 'from_warehouse'},
      { data: 'to_warehouse', name: 'to_warehouse' },
    ],
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow-x', 'scroll');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
       $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      $('body').find('.dataTables_scrollHead').addClass("scrollbar");

    },
    drawCallback: function(){
      // $('#loader_modal').modal('hide');
      $($.fn.dataTable.tables(true)).DataTable()
      .columns.adjust();
    },
    rowCallback: function( row, data, index ) {
    },
    footerCallback: function ( row, data, start, end, display ) {
      $.ajaxSetup({
        headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });
      $.ajax({

        method:"post",
        dataType:"json",
        url:"{{ route('get-sold-product-report-transfer-footer-values') }}",
        data:{
          from_date : $('#from_date').val(),
          to_date : $('#to_date').val(),
          warehouse_id : $('.warehouse_id option:selected').val(),
          product_type : $('.product_type option:selected').val(),
          product_type_2 : $('.product_type_2 option:selected').val(),
          product_type_3 : $('.product_type_3 option:selected').val(),
          //customer_id : $('.customer_id option:selected').val(),
          customer_id : $('.selecting-customer-group option:selected').val(),
          // customer_id : $('#customer_id_select').val(),
          product_idd : $('#header_prod_searchh').data('prod_id'),
          product_id : $('.product_id option:selected').val(),
          supplier_id : $('.supplier_id option:selected').val(),
          order_type : $('.order_type option:selected').val(),
          date_type :  $("input[name='date_radio']:checked").val(),
          product_id_select :  $('#product_id_select').val(),
          sale_person_id : $('.selecting_sale_person option:selected').val(),
          filter : $('.filter-dropdown option:selected').val(),
          p_c_id : p_c_id,
          c_ty_id : c_ty_id,
          saleid : saleid,
          prod_category : $('.product_category_id option:selected').val(),
          draft : draft
        },

        success:function(result){
          var total_quantity_transfer = result.total_quantity_transfer;
          // alert(total_quantity);
          $('#total_transfer').html('Total');
          $('#total_quantity_transfer').html(total_quantity_transfer.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));

        },
        error: function(){

        }
      });
    },

  });
  }
  var btn_click = false;
  $(document).on('click',function(e){
      if ($(e.target).closest(".dt-button-collection").length === 1) {
          btn_click = true;
      }

      if(btn_click)
      {
        if ($(e.target).closest(".dt-button-collection").length === 0) {
          btn_click = false;
          $('.sold-products-report').DataTable().ajax.reload();
          // alert('clicked outside');
        }
      }
    });
$('#header_customer_search').on('click',function(){
  if($('.custom__search_arrows_customer').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($(this).val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('.custom__search_arrows_customer').on('click',function(){
  if($(this).hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($('#header_customer_search').val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
  $('#header_customer_search').keyup(function(event){
      // $('#header_customer_search').unbind("focus");
      keyindex = -1;
      alinks = '';
      var query = $(this).val();
      var inv_id = $("#quo_id_for_pdf").val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
         var _token = $('input[name="_token"]').val();
         GetCathegoryCustomers(query,inv_id,_token);
        }
        else if(query.length == 0)
        {
          $('#header_prod_searchh').val('');
          $('#header_prod_searchh').data('prod_id','');
          $('.sold-products-report').DataTable().ajax.reload();
        }
        else
        {
          $('#myIddd').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });
  function GetCathegoryCustomers(query=null,inv_id=null,_token=null){

          $.ajax({
            url:"{{ route('purchase-fetch-customer') }}",
            method:"POST",
            data:{query:query, _token:_token, inv_id:inv_id},
            beforeSend:function(){
              $('#loader__custom_search_customer').removeClass('d-none');
            },
            success:function(data){
              $('#purchase_loader_productt').empty();
              $('#myIddd').html(data);
              $('#loader__custom_search_customer').addClass('d-none');
              $('.custom__search_arrows_customer').removeClass('fa-caret-down');
              $('.custom__search_arrows_customer').addClass('fa-caret-up');

              // alinks = $('#myIddd').find('a');
              // if (alinks.length === 0)
              // {
              //   keyindex = -1;
              // }
              // if (event.keyCode == 40)
              // {
              //   event.preventDefault();
              //   if (alinks.length > 0 && keyindex == -1)
              //   {
              //     keyindex = 0;
              //     $('#myIddd').find('a')[keyindex++].focus();
              //     var dat = $('#myIddd').find('a')[keyindex-1].text;
              //     // document.getElementById('header_prod_searchh').value = dat;
              //     alert(dat);
              //   }
              // }
             },
             error: function(){

             }
          });
  }
  $(document).on("click",".list-data",function() {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       $('.search_customer').val(li_text);

      var test= $('.search_customer').attr('data-id',li_id );
      $("#customer_id_select").val(li_id);
      // console.log(test);

    $("#apply_filter_btn").val("1");
    $("#customer_id_exp").val(li_id);
    // $(".customer_id").val(li_id);


});
  $(document).on('click', '.show-notes', function(e){
    let compl_quot_id = $(this).data('id');
    sold = true;
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-completed-quotation-prod-note') }}",
      data: 'compl_quot_id='+compl_quot_id+'&'+'sold='+sold,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        // console.log(response);
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){
        //$("#loader_modal").modal('hide');
      }
    });

  });

  $(document).on('click', '.btn-edit', function () {
    var id = $(this).data('id');
    var value = $('#cogs-'+id).data('value');
    $('#cogs-'+id).addClass('d-none');
    $('#edit-cogs-'+id).addClass('d-none');
    $('#input-cogs-'+id).removeClass('d-none');
    $('#input-cogs-'+id).addClass('active');
    $('#input-cogs-'+id).val(value);
  });

  $(document).on('keypress', '.edit-input', function (e) {
    var id = $(this).data('id');
    var old_value = $('#cogs-'+id).data('value');
    var value = $(this).val();
    var column = 'COGS Price (THB)';

    if (e.keyCode === 27 && $(this).hasClass('active'))  //27 is for pressing esc
    {
      $('#cogs-'+id).val(old_value);
      $('#cogs-'+id).removeClass('d-none');
      $('#edit-cogs-'+id).removeClass('d-none');
      $('#input-cogs-'+id).addClass('d-none');
      $('#input-cogs-'+id).removeClass('active');
      return false;
    }

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
      if($(this).val().length < 1)
      {
        return false;
      }
      else
      {
        $.ajax({
          type: "post",
          url: "{{ route('update-cogs-from-report') }}",
          data: {id:id, value:value, old_value:old_value, column:column},
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
          },
          success: function(response){
            if (response.success) {
              value = parseFloat(value).toFixed(2);
              $('#cogs-'+id).html('<b>'+value+'</b>');
              $('#cogs-'+id).removeClass('d-none');
              $('#edit-cogs-'+id).removeClass('d-none');
              $('#input-cogs-'+id).addClass('d-none');
              $('#input-cogs-'+id).removeClass('active');
              $('#cogs-'+id).data('value', value);
              var qty = $('#qty-'+id).html();
              var total_cogs = 0;
              if(qty != 'N.A')
              {
                 total_cogs = parseFloat(qty * value).toFixed(2);
              }
              $('#total-cogs-'+id).html(total_cogs);
              $("#loader_modal").modal('hide');
              history_table.ajax.reload();
              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

            }
          }
        });
      }
    }
  });

  // History
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
  var history_table =$('.table-history').DataTable({
    // "pagingType": "input",
    "sPaginationType": "listbox",
    processing: false,
    ordering: false,
    searching:false,
    serverSide: true,
    "bPaginate": true,
    "bInfo":false,
    lengthMenu: [ 5, 10, 20, 40],

    ajax: {
      url:"{!! route('get-product-sale-report-detail-history') !!}",
      method:"post",
      dataType:"json"
    },
    columns: [
      { data: 'user_name', name: 'user_name' },
      { data: 'created_at', name: 'created_at' },
      { data: 'order_no', name: 'order_no' },
      { data: 'product', name: 'product' },
      { data: 'column_name', name: 'column_name' },
      { data: 'old_value', name: 'old_value' },
      { data: 'new_value', name: 'new_value' }
      ],
      initComplete: function () {
       $('body').find('.dataTables_scrollBody').addClass("scrollbar");
       $('body').find('.dataTables_scrollHead').addClass("scrollbar");

      },
    });
$('#header_product_search').on('click',function(){
  if($('.custom__search_arrows').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetProductCategorySearch($(this).val(),_token);
  }
  else
  {
    $("#product-ul-div").empty();
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
    $("#product-ul-div").empty();
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
         $('#product-ul-div').show();
        }
        else if(query.length == 0)
        {
          $('#header_product_search').val('');
          $('#header_product_search ').data('prod_id','');
        }
        else
        {
          $('#product-ul-div').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });
  function GetProductCategorySearch(query=null,_token=null){
    $.ajax({
      url:"{{ route('fetch-product-with-category') }}",
      method:"POST",
      data:{query:query, _token:_token},
      beforeSend:function(){
        $('#loader__custom_search').removeClass('d-none');
      },
      success:function(data){
        $('#product-ul-div').html(data);
        $('#loader__custom_search').addClass('d-none');
        $('.custom__search_arrows').removeClass('fa-caret-down');
        $('.custom__search_arrows').addClass('fa-caret-up');

       },
       error: function(){

       }
    });
  }
  var className = '';
  var parent_li = true;
  $(document).on("click",".list-data-category",function() {
    if (parent_li) {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       $('.search_product_with_cat').val(li_text);
      $("#product_id_select").val(li_id);
      $('#header_product_search').val(li_text);
      className = $(this).hasClass('parent') ? 'parent' : 'child';
      $('#className').val(className);
    }
    else{
      parent_li = true;
    }
});
   $(document).on("click",".child-list-data",function() {
      parent_li = false;
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       console.log(li_id);
       console.log(li_text);
       $('.search_product_with_cat').val(li_text);
      $("#product_id_select").val(li_id);
      $('#header_product_search').val(li_text);
      className = 'child_product';
      $('#className').val(className);
});
   $(document).on('click', function (e) {
    if($("#product-ul-div").is(":visible")){
        $("#product-ul-div").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows_customer').addClass('fa-caret-down');
        $('.custom__search_arrows_customer').removeClass('fa-caret-up');
    }
   })
</script>
@stop
