@extends('backend.layouts.layout')

@section('title','Product Sales Report | Supply Chain')

@section('content')

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
.dataTables_scrollFootInner
{
  margin-top: 10px;
}

/*table.dataTable thead .sorting { background: url('public/sort/sort_both.png') no-repeat center right;
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('public/sort/sort_desc.png') no-repeat center right;
  background-size: 5vh;}*/

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
          <li class="breadcrumb-item active">Product Sales Report</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h4 class="maintitle text-uppercase fontbold">Product Sales Report</h4>
  </div>
  <div class="col-md-4 col-6">
    <div class="pull-right">
      <span class="export_btn vertical-icons" title="Create New Export">
        <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
  </div>
</div>


{{--Filters start here--}}
<div class="row mb-2 filters_div">
  <div class="col-md-12 title-col mb-2">
    <div class="d-sm-flex row">

      <div class="col-md-2 col-6">
        <label>Choose Supplier</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags supplier_id" name="supplier_id" required="true">
          <option value="" selected="">Choose Supplier</option>
          @foreach($suppliers as $s)
          <option value="{{$s->id}}">{{$s->reference_name}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6">
        <label>Select Customer / Group</label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags customer_group" name="filter">
          <option value="" selected="">Select a Customer / Group</option>
          @foreach($customer_categories as $cc)
          <option value="{{'cat-'.$cc->id}}" title="{{@$cc->title}}">{{$cc->title}} {!! $extra_space !!} {{$cc->customer != null ? $cc->customer->pluck('reference_name') : ''}}</option>
          @foreach(@$cc->customer as $s)
          <option value="{{'cus-'.$s->id}}" title="{{@$s->reference_name}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$s->reference_name != null ? $s->reference_name : ($s->first_name.' '.$s->last_name)}}</option>
          @endforeach
          @endforeach
        </select>
      </div>
      <!-- <div class="col-2">
        <label>Choose Customer</label>
        <select class="font-weight-bold form-control-lg form-control customer_id state-tags" name="customer_id" >
          <option value="" >Choose Customer</option>
          @foreach($customers as $s)
          @php $id = Session::get('customer_id');@endphp
          <option value="{{$s->id}}" {{ ($s->id == @$id )? "selected='true'":" " }}>{{$s->reference_name != null ? $s->reference_name : ($s->first_name.' '.$s->last_name)}}</option>
          @endforeach
        </select>
      </div> -->

      <!-- <div class="col-md-3 ml-0">
        <label>Choose Customers</label>
         <div class="border rounded custom-input-group autosearch position-relative">
          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_customer_search" tabindex="0" name="prod_name" placeholder="Choose Customer / Customer Group" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
        </div>
        <span id="loader__custom_search_customer" class="position-absolute d-none" style="right:27px;top:43%;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows_customer custom__search_arrows_customer2" aria-hidden="true"></i>
        <p id="myIddd" class="m-0"></p>
      </div> -->

      <!-- <div class="col-md-2 ml-0">
      <label><b>Choose Primary/Sub Category</b></label>
         <div class="border rounded custom-input-group autosearch position-relative">
          <input type="text" class="font-weight-bold form-control-lg form-control search_product_with_cat" id="header_product_search" tabindex="0" name="prod_name_category" placeholder="Choose Primary / Sub Category" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
        </div>
         <span id="loader__custom_search" class="position-absolute d-none" style="right:27px;top:43%;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows custom__search_arrows2" aria-hidden="true"></i>
        <p id="product-ul-div" class="m-0"></p>
      </div> -->

      <div class="col-md-2 col-6">
        <label>Choose Category</label>
        <select class="font-weight-bold form-control-lg form-control category_id state-tags" name="category_id" >
         <option value="" selected="">Choose Category</option>
         <hr>
          @foreach($product_parent_categories as $ppc)
          <option value="{{'pri-'.$ppc->id}}" title="{{@$ppc->title}}" data-title="{{@$ppc->title}}">{{$ppc->title}}{!! $extra_space !!}{{$ppc->get_Child != null ? $ppc->get_Child->pluck('title') : ''}}</option>
          @foreach($ppc->get_Child as $sc)
            <option value="{{'sub-'.$sc->id}}" title="@$sc->title"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$sc->title}}
              {!! $extra_space !!} {{$ppc->title}} </option>
          @endforeach
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6">
        <label>Choose Product</label>
        <select class="font-weight-bold form-control-lg form-control product_id state-tags" name="product_id" id="choose_product_select">
         <option value="" selected="">Choose Product</option>
          @foreach($products as $s)
          <option value="{{$s->id}}">{{$s->refrence_code}} - {{$s->short_desc}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6">
        <label>Choose Product Type</label>
        <select class="font-weight-bold form-control-lg form-control product_type state-tags" name="product_id" >
         <option value="" selected="">Choose Product Type</option>
          @foreach($product_types as $s)
          <option value="{{$s->id}}">{{$s->title}}</option>
          @endforeach
        </select>
      </div>

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


      <!-- <div class="col-2">
        <label>Choose Sub Category</label>
        <select class="font-weight-bold form-control-lg form-control sub_category_id state-tags" name="sub_category_id" >
         <option value="" selected="">Choose Sub Category</option>
          @foreach($product_sub_categories as $psc)
          <option value="{{$psc->title}}">{{$psc->title}}</option>
          @endforeach
        </select>
      </div> -->

    </div>
  </div>

  <div class="col-md-12 title-col mb-2">
    <div class="d-sm-flex row">
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

      @if(Auth::user()->role_id !== 3)
      <div class="col-md-2 col-6">
        <label>Select Sales Person</label>
        <select class="font-weight-bold form-control-lg form-control js-states sales_person state-tags" name="sales_person" >
          <option value="" selected="">Select Sales Person</option>
          @foreach($sales_persons as $sp)
          <option value="{{$sp->id}}">{{$sp->name}}</option>
          @endforeach
        </select>
      </div>
      @endif

      <div class="col-md-2 col-6">
        <label>Select Order Status</label>
        <select class="font-weight-bold form-control-lg form-control order_type state-tags" name="order_type" >
          <option value="">All (Draft & Invoices)</option>
          <option value="2" >Draft Invoice</option>
          <option value="3" selected="">All Invoice</option>
        </select>
      </div>
      <div class="col-md-2 col-6">
        <label>Choose Warehouse</label>
        <select class="font-weight-bold form-control-lg form-control js-states warehouse state-tags" name="sales_person" >
          <option value="all" selected="">Choose Warehouse</option>
          @foreach($warehouses as $warehouse)
          <option value="{{$warehouse->id}}">{{$warehouse->warehouse_title}}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6">
        <div class="form-group">
          <label>From Date</label>
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="form-group">
          <label>To Date</label>
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
        </div>
      </div>
      <div class="col-md-4 col-6 d-flex mt-5">
        <div class="form-check ml-2">
          <input type="radio" class="form-check-input dates_changer created_date_input"  name="date_radio" value='2' checked>
          <label class="form-check-label" for="exampleCheck1"><b>Created Date</b></label>
        </div> &nbsp;
        <div class="form-check ml-2">
          <input type="radio" class="form-check-input dates_changer delivery_date_input"  name="date_radio" value='1'>
          <label class="form-check-label" for="exampleCheck1"><b>Delivery Date</b></label>
        </div>
      </div>
      @if(Auth::user()->role_id == 3)
      <div class="col-md-2 col-6"></div>
      @endif

    </div>
  </div>

  <div class="col-md-12 title-col mb-2">
    <div class="d-sm-flex row justify-content-between">

      <div class="col-md-2 col-6"></div>
      <div class="col-md-2 col-6"></div>
      <div class="col-md-2 col-6"></div>
      <div class="col-md-2 col-6">
        <div class="form-group mb-0">
         <label><b style="visibility: hidden;">Reset</b></label>
        <div class="input-group-append ml-1">
          <!-- <button class="btn recived-button apply_date" type="button">Apply Filters</button>   -->
        </div>
        </div>
      </div>
      <div class="col-md-1 col-6">
        <label style="visibility: hidden;">reset</label>
        <div class="input-group-append ml-1">
          <!-- <button class="btn recived-button reset-btn rounded" type="reset">Reset</button>   -->
        </div>
      </div>
      <div class="col-md-3 col-6">
        <label style="visibility: hidden;">Hidden</label>
        <div class="input-group-append ml-1 pull-right">
          <!-- <button id="export_s_p_r" class="btn recived-button export_btn" >Export</button> -->
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
<div class="row">
  <div class="col-12 d-flex">
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="supplier"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="customer_group"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="category_span"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="product"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="product_type"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="product_type_2"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="product_type_3"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="sales_person"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="customer"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="order_status"></span>
    <span class="d-none font-weight-bold  alert alert-primary mr-2 mb-0" id="warehouse"></span>
  </div>
</div>

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
        <!-- <a download href="{{ url('storage/app/product-sale-export.xlsx')}}"><u>Click Here</u></a> -->
        <a class="exp_download" href="{{ url('get-download-xslx','product-sale-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>
      <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is already being prepared by another user! Please wait.. </b>
      </div>
       <!-- <button class="btn btn-primary mb-2 table-reload" style="border-radius: 0px;" title="Click to update total visible stock column"><i class="fa fa-refresh"></i></button> -->

      <table class="table entriestable table-bordered text-center product-sales-report">
        <thead>
          <tr>
            <th class="noVis">View</th>
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>@if(!array_key_exists('type', $global_terminologies))Product Type @else {{$global_terminologies['type']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="type">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="type">
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
            <th>
              {{$global_terminologies['brand']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="brand">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="brand">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="nowrap">{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Selling<br>Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Total <br>{{$global_terminologies['qty']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="5">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="5">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Total <br>Pieces
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="6">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="6">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            {{--<th>Avg <br>Unit <br>Cost</th>--}}
            {{--<th>Avg <br>Unit <br> Price</th>--}}
            <th>Sub Total
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sub_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sub_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Total<br>Amount
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="7">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="7">
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
            <th>Total <br>Stock
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_stock">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_stock">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Total<br>Visible Stock
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_visible_stock">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_visible_stock">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th class="unit_cogs_index">{{$global_terminologies['net_price']}} <br> (THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="net_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="net_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="unit_cogs_index">Total {{$global_terminologies['net_price']}} <br> (THB)
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_net_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_net_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            @if($warehouses->count() > 0)
            @foreach($warehouses as $warehouse)
              <th>{{$warehouse->warehouse_title}}<br>{{$global_terminologies['current_qty']}}
                <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="{{$warehouse->warehouse_title}}">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="{{$warehouse->warehouse_title}}">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
              </th>
            @endforeach
            @endif
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
            {{--<th></th>--}}
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            @if($warehouses->count() > 0)
            @foreach($warehouses as $warehouse)
              <th></th>
            @endforeach
            @endif
            @if($getCategories->count() > 0)
            @foreach($getCategories as $cat)
              <th></th>
            @endforeach
            @endif
          </tr>
        </tfoot>
      </table>
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

<!-- <form id="export_product_sale_report" method="post" action="{{route('export-product-sales-report')}}"> -->
<form id="export_product_sale_report">
  @csrf
  <input type="hidden" name="order_type_exp" id="order_type_exp">
  <input type="hidden" name="supplier_id_exp" id="supplier_id_exp">
  <input type="hidden" name="customer_group_id_exp" id="customer_group_id_exp">
  <input type="hidden" name="sales_person_exp" id="sales_person_exp">
  <!-- <input type="hidden" name="customer_id_exp" id="customer_id_exp"> -->
  <input type="hidden" name="product_id_exp" id="product_id_exp">
  <input type="hidden" name="product_type_exp" id="product_type_exp">
  <input type="hidden" name="product_type_2_exp" id="product_type_2_exp">
  <input type="hidden" name="product_type_3_exp" id="product_type_3_exp">
  <input type="hidden" name="category_id_exp" id="category_id_exp">
  <!-- <input type="hidden" name="sub_category_id_exp" id="sub_category_id_exp"> -->
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="date_type_exp" id="date_type_exp" value='2'>
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="customer_id_select" id="customer_id_select">
  <input type="hidden" name="className" id="className">
  <input type="hidden" name="product_id_select" id="product_id_select">
  <input type="hidden" name="productClassName" id="productClassName">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
  <input type="hidden" name="warehouse_id" id="warehouse_id">
</form>

@endsection

@php
  $hidden_by_default = '';
@endphp

@section('javascript')
<script type="text/javascript">
      $('.table-reload').on('click',function(){
      $('.product-sales-report').DataTable().ajax.reload();
    });
    // Columns Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#sortbyparam').val(column_name);
    $('#sortbyvalue').val(order);

    $('.product-sales-report').DataTable().ajax.reload();

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

  $("#from_date").datepicker('setDate',last_month);
  $("#to_date").datepicker('setDate','today');



  $(document).ready(function(){
    @if(Session::has('find'))
      var id = "{{Session::get('customer_id')}}";
      var dat = "{{Session::get('month')}}";
      var full_date = dat.split('-');
      var year = full_date[0];
      var month = full_date[1];
      var datee = '01';
      var year1 = full_date[0];
      var month1 = full_date[1];
      var getDaysInMonth = function(month,year) {
        return new Date(year, month, 0).getDate();
      };
      var datee1 = getDaysInMonth(month1, year1);
      var from_date =  datee+ "/" + month + "/" + year;
      var to_date =  datee1+ "/" + month1 + "/" + year1;
      document.querySelector("#from_date").value = from_date;
      document.querySelector("#to_date").value = to_date;
    @endif
  });

  var date = $('#from_date').val();
    $("#from_date_exp").val(date);
  var date = $('#to_date').val();
    $("#to_date_exp").val(date);

  $('input[type=radio][name=date_radio]').change(function() {
    $("#apply_filter_btn").val("1");
    if (this.value == '1')
    {
      $('#date_type_exp').val(this.value);
      // $('.product-sales-report').DataTable().ajax.reload();
      // $('#loader_modal').modal({
      //     backdrop: 'static',
      //     keyboard: false
      //   });
      // $("#loader_modal").modal('show');
    }
    else if (this.value == '2')
    {
      $('#date_type_exp').val(this.value);
     //  $('.product-sales-report').DataTable().ajax.reload();
     //  $('#loader_modal').modal({
     //    backdrop: 'static',
     //    keyboard: false
     //  });
     // $("#loader_modal").modal('show');
    }
  });

  var hidden_cols = "{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}";
  hidden_cols = hidden_cols.split(',');

  $(function(e){
    var show_total_cost = false;
    var role = "{{Auth::user()->role_id}}";
    if(role == 1 || role == 2 || role == 7)
    {
      show_total_cost = true;
      if( hidden_cols.includes("13") )
      {
        var show_total_cost = false;
      }
    }
    $(".state-tags").select2();
    var table2 = $('.product-sales-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      searching:false,
    //   oLanguage:
    //   {
    //     sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
    //   },
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      serverSide: true,
      dom: 'Blfrtip',
      "aaSorting": [[3]],
      bSort: false,
      info: true,
      "columnDefs": [
       { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 1,2,3,4 ] },
        { className: "dt-body-right", "targets": [ 5,6,7] },
      ],
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      lengthMenu: [ 100,200,300,400,500],
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
      },
        url:"{!! route('get-product-sales-report-data') !!}",
        // data: function(data) {data.from_date = $('#from_date').val(),
        //  data.to_date = $('#to_date').val(), data.warehouse_id = $('.warehouse_id option:selected').val(),
        //  data.customer_id = $('.customer_id option:selected').val(),data.product_id = $('.product_id option:selected').val(),
        //  data.sales_person = $('.sales_person option:selected').val(),data.supplier_id = $('.supplier_id option:selected').val(),
        //  data.customer_group = $('.customer_group option:selected').val(),
         // data.category_id = $('.category_id option:selected').val(),
        //  data.sub_category_id = $('.sub_category_id option:selected').val(),
        //  data.sortbyparam = column_name,
        //  data.order_type = $('.order_type option:selected').val(),
        //  data.sortbyvalue = order,  data.date_type = $("input[name='date_radio']:checked").val() },

         data: function(data) {data.from_date = $('#from_date').val(),
         data.to_date = $('#to_date').val(), data.warehouse_id = $('.warehouse_id option:selected').val(),
         data.sales_person = $('.sales_person option:selected').val(),data.supplier_id = $('.supplier_id option:selected').val(),
         data.customer_id_select = $('#customer_id_select').val(),
         data.product_id_select = $('#product_id_select').val(),
         data.product_id = $('.product_id option:selected').val(),
         data.product_type = $('.product_type option:selected').val(),
         data.product_type_2 = $('.product_type_2 option:selected').val(),
         data.product_type_3 = $('.product_type_3 option:selected').val(),
         data.sortbyparam = column_name,
         data.order_type = $('.order_type option:selected').val(),
         data.sortbyvalue = order,  data.date_type = $("input[name='date_radio']:checked").val(),
         data.className = $('#className').val(),
         data.productClassName = $('#productClassName').val(),
         data.category_id = $('.category_id option:selected').val(),
         data.customer_group = $('.customer_group option:selected').val(),
         data.warehouse_id = $('.warehouse option:selected').val(),
         data.type = 'datatable'
          },
        method: "get",
      },
      columns: [
        { data: 'view', name: 'view'},
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'product_type', name: 'product_type' },
        { data: 'product_type_2', name: 'product_type_2' @if (!in_array('product_type_2', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
        { data: 'product_type_3', name: 'product_type_3' @if (!in_array('product_type_3', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'selling_unit', name: 'selling_unit' },
        { data: 'total_quantity', name: 'total_quantity' },
        { data: 'total_pieces', name: 'total_pieces' },
        // { data: 'total_cost', name: 'total_cost', visible: show_total_cost},
        // { data: 'avg_unit_price', name: 'avg_unit_price' },
        { data: 'sub_total', name: 'sub_total' },
        { data: 'total_amount', name: 'total_amount' },
        { data: 'vat_thb', name: 'vat_thb' },
        { data: 'total_stock', name: 'total_stock' },
        { data: 'total_visible_stock', name: 'total_visible_stock' },
        { data: 'cogs', name: 'cogs' ,visible: show_total_cost},
        { data: 'total_cogs', name: 'total_cogs'},
        // Dynamic columns start
        @if($warehouses->count() > 0)
        @foreach($warehouses as $warehouse)
          { data: '{{$warehouse->warehouse_title}}{{"current"}}', name: '{{$warehouse->warehouse_title}}{{"current"}}'},
        @endforeach
        @endif
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
          $('.dataTables_scrollHead').css('overflow', 'auto');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
          $('body').find('.dataTables_scrollBody').addClass("scrollbar");
          $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
      footerCallback: function ( row, data, start, end, display ) {
        var api  = this.api();
        $.ajax({
           url:"{!! route('get-product-sales-report-data') !!}",
           data: {
             from_date : $('#from_date').val(),
             to_date : $('#to_date').val(), warehouse_id : $('.warehouse_id option:selected').val(),
             customer_id : $('.customer_id option:selected').val(),product_id : $('.product_id option:selected').val(),
             sales_person : $('.sales_person option:selected').val(),supplier_id : $('.supplier_id option:selected').val(),
             customer_group : $('.customer_group option:selected').val(),
             category_id : $('.category_id option:selected').val(),
             sub_category_id : $('.sub_category_id option:selected').val(),
             sortbyparam : column_name,
             order_type : $('.order_type option:selected').val(),
             sortbyvalue : order,  date_type : $("input[name='date_radio']:checked").val(),
             product_id_select: $('#product_id_select').val(),
             type: 'footer'
           },
          method: "get",
          success: function (result) {
            var total_quantity   = result.total_quantity;
            var total_pieces   = result.total_pieces;
            var total_cost       = result.total_cost;
            var total_amount     = result.total_amount;
            var avg_unit_price   = result.avg_unit_price;
            var vat_total_amount = result.vat_total_amount;
            var total_price_sub  = result.total_price_sub;

            total_quantity   = parseFloat(total_quantity).toFixed(2);
            total_quantity   = total_quantity.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            total_pieces   = parseFloat(total_pieces).toFixed(2);
            total_pieces   = total_pieces.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            total_cost       = parseFloat(total_cost).toFixed(2);
            total_cost       = total_cost.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            total_amount     = parseFloat(total_amount).toFixed(2);
            total_amount     = total_amount.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            avg_unit_price   = parseFloat(avg_unit_price).toFixed(2);
            avg_unit_price   = avg_unit_price.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            vat_total_amount = parseFloat(vat_total_amount).toFixed(2);
            vat_total_amount = vat_total_amount.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            total_price_sub  = parseFloat(total_price_sub).toFixed(2);
            total_price_sub  = total_price_sub.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            $( api.column( 0 ).footer() ).html('Total');
            $( api.column( 8 ).footer() ).html(total_quantity);
            $( api.column( 9 ).footer() ).html(total_pieces);
            // if(show_total_cost == true){
            //   $( api.column( 6 ).footer() ).html(total_cost);
            // }
            // $( api.column( 7 ).footer() ).html(avg_unit_price);
            $( api.column( 10 ).footer() ).html(total_price_sub);
            $( api.column( 11 ).footer() ).html(total_amount);
            $( api.column( 12 ).footer() ).html(vat_total_amount);
          }
        });


        // var json             = api.ajax.json();


      },
   });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      var columns = table2.settings().init().columns;
       var name = columns[column].name;
       // alert(name);
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.post({
      url : "{{ route('toggle-column-display') }}",
      dataType : "json",
      data : {type:'product_sale_report',column_id:column},
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
      error: function(request, status, error)
      {
        $('#loader_modal').modal('hide');
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


  $('.order_type').change(function() {
    $("#apply_filter_btn").val("1");
    var val = $('.order_type option:selected').val();
    $("#order_type_exp").val($('.order_type option:selected').val());
    var selected = $('.order_type option:selected').val();
    var final_text = $('.order_type option:selected').text();

    if(selected == '')
    {
      $("#order_status").removeClass('d-none');
      $("#order_status").html('Order Status: '+final_text);
    }
    else
    {
      $("#order_status").removeClass('d-none');
      $("#order_status").html('Order Status: '+final_text);
    }
  });

  $('.warehouse').change(function() {
    $("#apply_filter_btn").val("1");
    $("#warehouse_id_exp").val($('.warehouse_id option:selected').val());

    var selected = $('.warehouse option:selected').val();
    var final_text = $('.warehouse option:selected').text();

    if(selected === 'all')
    {
      $("#warehouse").addClass('d-none');
    }
    else
    {
      $("#warehouse").removeClass('d-none');
      $("#warehouse").html('Warehouse: '+final_text);
    }

    // if($('.warehouse_id').val() != '')
    // {
    //   $('#loader_modal').modal({
    //       backdrop: 'static',
    //       keyboard: false
    //     });
    //   $("#loader_modal").modal('show');
    //   $('.product-sales-report').DataTable().ajax.reload();
    // }
  });

  // $('.customer_id').change(function() {
  //   $("#apply_filter_btn").val("1");
  //   // $("#customer_id_exp").val($('.customer_id option:selected').val());
  //   var value = $(this).val();
  //   // $('#loader_modal').modal({
  //   //     backdrop: 'static',
  //   //     keyboard: false
  //   //   });
  //   // $("#loader_modal").modal('show');
  //   // $('.product-sales-report').DataTable().ajax.reload();

  //   if(value == '')
  //   {
  //     $("#customer").addClass('d-none');
  //   }
  //   else
  //   {
  //     // var name = $('.customer_id option:selected').text();
  //     $("#customer").removeClass('d-none');
  //     $("#customer").html('Customer: '+value);
  //   }
  // });

  $('.category_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#category_id_exp").val($('.category_id option:selected').val());
    var selected = $('.category_id option:selected').val();
    var text = $('.category_id option:selected').text();
    var result = text.split(/\s+\s+\s+/);
    var final_text = result[1];
    if(result[0] !== '')
    {
      final_text = result[0];
    }
    // console.log(result);
    if(selected == '')
    {
      $("#category_span").addClass('d-none');
    }
    else
    {
      $("#category_span").removeClass('d-none');
      $("#category_span").html('Category: '+final_text);
    }
  });

  $('.sub_category_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#sub_category_id_exp").val($('.sub_category_id option:selected').val());
    var selected = $('.sub_category_id option:selected').val();
    // $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    // $("#loader_modal").modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $('.product_id').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_id_exp").val($('.product_id option:selected').val());
    var selected = $('.product_id option:selected').val();
    // $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    // $("#loader_modal").modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();

    if(selected == '')
    {
      $("#product").addClass('d-none');
    }
    else
    {
      var name = $('.product_id option:selected').text();
      $("#product").removeClass('d-none');
      $("#product").html('Product Desc: '+name);
    }
  });

  $('.product_type').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_type_exp").val($('.product_type option:selected').val());
    var selected = $('.product_type option:selected').val();
    if(selected == '')
    {
      $("#product_type").addClass('d-none');
    }
    else
    {
      var name = $('.product_type option:selected').text();
      $("#product_type").removeClass('d-none');
      $("#product_type").html('Product Type: '+name);
    }
  });
  $('.product_type_2').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_type_2_exp").val($('.product_type_2 option:selected').val());
    var selected = $('.product_type_2 option:selected').val();
    if(selected == '')
    {
      $("#product_type_2").addClass('d-none');
    }
    else
    {
      var name = $('.product_type_2 option:selected').text();
      $("#product_type_2").removeClass('d-none');
      $("#product_type_2").html('Product Type 2: '+name);
    }
  });

  $('.product_type_3').change(function() {
    $("#apply_filter_btn").val("1");
    $("#product_type_3_exp").val($('.product_type_3 option:selected').val());
    var selected = $('.product_type_3 option:selected').val();
    if(selected == '')
    {
      $("#product_type_3").addClass('d-none');
    }
    else
    {
      var name = $('.product_type_3 option:selected').text();
      $("#product_type_3").removeClass('d-none');
      $("#product_type_3").html('Product Type 3: '+name);
    }
  });

  $(document).on('change','.sales_person',function(){
    $("#apply_filter_btn").val("1");
    $("#sales_person_exp").val($('.sales_person option:selected').val());
    var selected = $('.sales_person option:selected').val();
    // $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    // $("#loader_modal").modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();

    if(selected == '')
    {
      $("#sales_person").addClass('d-none');
    }
    else
    {
      var name = $('.sales_person option:selected').text();
      $("#sales_person").removeClass('d-none');
      $("#sales_person").html('Sales Person Name: '+name);
    }
  });

  $(document).on('change','.supplier_id',function(){
    $("#apply_filter_btn").val("1");
    $("#supplier_id_exp").val($('.supplier_id option:selected').val());

    var selected = $(this).val();
    if(selected == '')
    {
      $("#supplier").addClass('d-none');
    }
    else
    {
      var name = $('.supplier_id option:selected').text();
      $("#supplier").removeClass('d-none');
      $("#supplier").html('Supplier: '+name);
    }
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $(document).on('change','.customer_group',function(){
    $("#apply_filter_btn").val("1");
    $("#customer_group_id_exp").val($('.customer_group option:selected').val());
    var selected = $('.customer_group option:selected').val();
    var text = $('.customer_group option:selected').text();
    var result = text.split(/\s+\s+\s+/);
    var final_text = result[1];
    if(result[0] !== '')
    {
      final_text = result[0];
    }
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();

    if(selected == '' || result[0] == 'cat')
    {
      $("#customer_group").addClass('d-none');
    }
    else
    {
      var name = $('.customer_group option:selected').text();
      $("#customer_group").removeClass('d-none');
      $("#customer_group").html('Customer Group: '+final_text);
    }

  });

  $('#from_date').change(function() {
    $("#apply_filter_btn").val("1");
    var date = $('#from_date').val();
    $('#from_date_exp').val(date);
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $('#to_date').change(function() {
    $("#apply_filter_btn").val("1");
    var date = $('#to_date').val();
    $('#to_date_exp').val(date);
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $(document).on('click','.apply_date',function(){
    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.reset-btn').on('click',function(){
    $('#product-ul-div').hide();

    $('#product_id_select').val(null);
    $('#header_product_search').val('');

    $('#myIddd').hide();

    $('#customer_id_select').val(null);
    $('#header_customer_search').val('');

    $("#apply_filter_btn").val("0");
    $('.customer_id').val('');
    $('.product_id').val('');
    $('.product_type').val('');
    $('.product_type_2').val('');
    $('.product_type_3').val('');
    $('.warehouse_id').val('');
    $('.sub_category_id').val('');
    $('.category_id').val('');
    $('.sales_person').val('');
    $('.supplier_id').val('');
    $('.order_type').val('');
    $('.customer_group').val('');
    $('#from_date').val('');
    $('#to_date').val('');
    $(".state-tags").select2("", "");
    $(".warehouse").val('all').trigger('change');
    // $('#date_type_exp').val('');
    $('#to_date_exp').val('');
    $('#from_date_exp').val('');
    $('#sub_category_id_exp').val('');
    $('#category_id_exp').val('');
    $('#product_id_exp').val('');
    $('#product_type_exp').val('');
    $('#customer_id_exp').val('');
    $('#sales_person_exp').val('');
    $('#supplier_id_exp').val('');
    $('#order_type_exp').val('');
    $('#customer_group_id_exp').val('');
    $('#warehouse_id').val('');
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
    $("#supplier").addClass('d-none');
    $("#customer_group").addClass('d-none');
    $("#sales_person").addClass('d-none');
    $("#customer").addClass('d-none');
    $("#product").addClass('d-none');
    $("#product_type").addClass('d-none');
    $("#product_type_2").addClass('d-none');
    $("#category_span").addClass('d-none');
    $("#order_status").addClass('d-none');
  });

  // $('.export_btn').on('click',function(){
  //   $("#export_product_sale_report").submit();
  // });

  $(document).on('click','.export_btn',function(){
    // alert('here');
    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    $("#order_type_exp").val($('.order_type option:selected').val());
    $("#customer_group_id_exp").val($('.customer_group option:selected').val());
    $("#supplier_id_exp").val($('.supplier_id option:selected').val());
    $("#sales_person_exp").val($('.sales_person option:selected').val());
    $("#product_id_exp").val($('.product_id option:selected').val());
    $("#product_type_exp").val($('.product_type option:selected').val());
    $("#product_type_2_exp").val($('.product_type_2 option:selected').val());
    $("#product_type_3_exp").val($('.product_type_3 option:selected').val());
    $("#sub_category_id_exp").val($('.sub_category_id option:selected').val());
    $("#category_id_exp").val($('.category_id option:selected').val());
    $("#customer_id_exp").val($('.customer_id option:selected').val());
    $("#warehouse_id").val($('.warehouse option:selected').val());
    $("#from_date_exp").val($('#from_date').val());
    $("#to_date_exp").val($('#to_date').val());
    $("#to_date_exp").val($('#to_date').val());

    var form=$('#export_product_sale_report');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"post",
      url:"{{route('export-product-sales-report')}}",
      data:form_data,
      beforeSend:function(){
        $('.export_btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export_btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForProductReport();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export_btn').prop('disabled',true);
          checkStatusForProductReport();
        }
      },
      error:function(){
        $('.export_btn').prop('disabled',false);
      }
    });
});

    $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-product-report')}}",
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
          $('.export_btn').prop('disabled',true);
          checkStatusForProductReport();
        }
      }
    });
  });
  function checkStatusForProductReport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-product-report')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForProductReport();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').prop('disabled',false);

        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
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
          $('.product-sales-report').DataTable().ajax.reload();
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

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
         var _token = $('input[name="_token"]').val();
         GetCathegoryCustomers(query,_token);
         $('#myIddd').show();
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
  function GetCathegoryCustomers(query=null,_token=null){
    $.ajax({
      url:"{{ route('purchase-fetch-customer') }}",
      method:"POST",
      data:{query:query, _token:_token},
      beforeSend:function(){
        $('#loader__custom_search_customer').removeClass('d-none');
      },
      success:function(data){
        $('#myIddd').html(data);
        $('#loader__custom_search_customer').addClass('d-none');
        $('.custom__search_arrows_customer').removeClass('fa-caret-down');
        $('.custom__search_arrows_customer').addClass('fa-caret-up');
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
    $("#customer_id_select").val(li_id);
    $("#customer_id_exp").val(li_id);
    $(".select_customer_id").hide();
    $('#header_customer_search').val(li_text);
    className = $(this).hasClass('parent') ? 'parent' : 'child';
    $('#className').val(className);
    if(li_text == '')
    {
      $("#customer").addClass('d-none');
    }
    else
    {
      // var name = $('.customer_id option:selected').text();
      $("#customer").removeClass('d-none');
      $("#customer").html('Customer: '+li_text);
    }
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
  var productClassName = '';
  var parent_li = true;
  $(document).on("click",".list-data-category",function() {
    if (parent_li) {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       $('.search_product_with_cat').val(li_text);
      $("#product_id_select").val(li_id);
      $('#header_product_search').val(li_text);
      productClassName = $(this).hasClass('parent') ? 'parent' : 'child';
      $('#productClassName').val(productClassName);
      if(li_text == '')
      {
        $("#product").addClass('d-none');
      }
      else
      {
        $("#product").removeClass('d-none');
        $("#product").html('Product: '+li_text);
      }
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
      productClassName = 'child_product';
      $('#productClassName').val(productClassName);
      if(li_text == '')
      {
        $("#product").addClass('d-none');
      }
      else
      {
        $("#product").removeClass('d-none');
        $("#product").html('Product: '+li_text);
      }
});
   $(document).on('click', function (e) {
    if($("#product-ul-div").is(":visible")){
        $("#product-ul-div").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   });
   $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows_customer').addClass('fa-caret-down');
        $('.custom__search_arrows_customer').removeClass('fa-caret-up');
    }
   });

</script>
@stop
