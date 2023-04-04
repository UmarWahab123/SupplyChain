@extends('users.layouts.layout')

@section('title','Purchasing Report | Supply Chain')

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

table.dataTable thead .sorting { background: url('public/sort/sort_both.png') no-repeat center right !important; }
table.dataTable thead .sorting_asc { background: url('public/sort/sort_asc.png') no-repeat center right !important; }
table.dataTable thead .sorting_desc { background: url('public/sort/sort_desc.png') no-repeat center right !important; }
/*tbody tr td:nth-last-child(1) {
  text-align:right;
}
tbody tr td:nth-last-child(2) {
  text-align:right;
}
tbody tr td:nth-last-child(3) {
  text-align:right;
}
tbody tr td:nth-last-child(4) {
  text-align:right;
}
tbody tr td:nth-last-child(5) {
  text-align:right;
}*/

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
          <li class="breadcrumb-item active">Purchasing Report Detail</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h5 class="maintitle text-uppercase fontbold">Purchasing Report Detail</h5>
  </div>
  <div class="col-md-4 col-6">
    <div class="pull-right">
      <span class="export_btn vertical-icons" title="Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
  </div>
</div>

{{--Filters start here--}}
<div class="col-md-12 pl-0 pr-0 d-flex align-items-center mb-3 filters_div row">
  {{-- <div class="col-lg-2 col-md-2"></div> --}}

  <div class="col-md-2 col-6 incomplete-filter">
    <label class="pull-left">Choose Category:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags product_category_id" name="category" required="true">
      <option value="" disabled="" selected="">Choose Category</option>
      @if($parentCat)
      @foreach($parentCat as $pcat)
      <optgroup label="{{$pcat->title}}">
        <!-- @php
          $subCat = App\Models\Common\ProductCategory::where('parent_id',$pcat->id)->orderBy('title')->get();
        @endphp
        @foreach($subCat as $scat)
        <option value="{{$scat->id}}">{{$scat->title}}</option>
        @endforeach -->
        @foreach($pcat->get_Child as $scat)
          <option value="{{$scat->id}}">{{$scat->title}}</option>
        @endforeach
      </optgroup>
      @endforeach
      @endif
    </select>
  </div>

  <div class="col-md-2 col-6">
    <label class="pull-left">Stock:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags filter-dropdown" name="filter">
      <option value="" selected="">Select a Filter</option>
      <option value="stock">In Stock</option>
      <option value="reorder">Reorder Items</option>
    </select>
  </div>
  <div class="col-md-2 col-6">
    <label class="pull-left">PO Status:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags status-dropdown" name="status">
      <option value="">Select PO Status</option>
      <option value="pos" selected="">POs</option>
      <option value="40">Manual Pos</option>
      <option value="all">POs and Manual Pos</option>
    </select>
  </div>

  <div class="col-md-2 col-6">
    <label class="pull-left">Suppliers</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags supplier_id supplier-dropdown" name="supplier_filter">
      <option value="" selected="">Select Supplier</option>
      @foreach ($suppliers as $supplier)
        <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-2 col-6">
    <label>Choose Product</label>
    <select class="font-weight-bold form-control-lg form-control product_id state-tags" name="product_id" >
      <option value="" selected="">Choose Product</option>
      @foreach($products as $s)
        <option value="{{$s->id}}">{{$s->refrence_code}} -  {{$s->short_desc}}</option>
      @endforeach
    </select>
  </div>

 <div class="col-md-2 col-6">
  <!-- <label><b style="visibility: hidden;">Reset</b></label>
    <input type="button" value="Reset" class="btn recived-button reset-btn"> -->
<label class="d-block"><b style="visibility: hidden;">Reset</b></label>
    <div class="">
    <!-- <input type="button" value="Export" class="btn recived-button export_btn"> -->

     <span class="reset-btn vertical-icons mr-4" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>

      </div>

  </div>
  <div class="col-lg-2 col-md-2">

  </div>

</div>
<div class="col-md-12 pl-0 pr-0 d-flex row align-items-center mb-3 filters_div">
  <div class="col-md-2 col-6 incomplete-filter">
    <label class="pull-left">Choose Product Type:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags product_type" name="category" required="true">
      <option value="" disabled="" selected="">Choose Product Type</option>
      @if($product_types)
      @foreach($product_types as $pcat)
          <option value="{{$pcat->id}}">{{$pcat->title}}</option>
      @endforeach
      @endif
    </select>
  </div>

  @if (in_array('product_type_2', $product_detail_section))
  <div class="col-md-2 col-6 incomplete-filter">
    <label class="pull-left">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}}@endif:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags product_type_2" name="category" required="true">
      <option value="" disabled="" selected="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
      @if($product_types_2)
      @foreach($product_types_2 as $pcat)
          <option value="{{$pcat->id}}">{{$pcat->title}}</option>
      @endforeach
      @endif
    </select>
  </div>
  @endif

  @if (in_array('product_type_3', $product_detail_section))
  <div class="col-md-2 col-6 incomplete-filter">
    <label class="pull-left">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}}@endif:</label>
    <select class="font-weight-bold form-control-lg form-control js-states state-tags product_type_3" name="category" required="true">
      <option value="" disabled="" selected="">Choose @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
      @if($product_types_3)
      @foreach($product_types_3 as $pcat)
          <option value="{{$pcat->id}}">{{$pcat->title}}</option>
      @endforeach
      @endif
    </select>
  </div>
  @endif

  <div class="col-md-2 col-6">
    <div class="form-group">
      <label class="pull-left">From Date:</label>
      <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
    </div>
  </div>

  <div class="col-md-2 col-6">
    <div class="form-group">
      <label class="pull-left">To Date:</label>
      <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
    </div>
  </div>
  <div class="col-md-1 pb-3 col-6" style="">
      <div class="form-group mb-0">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Filters</button>   -->
        <span class="apply_date common-icons" title="Apply Filters">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
      </span>
      </div>
      </div>
  </div>
  <div class="col-lg-3">
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
        <!-- <a download href="{{url('storage/app/purchasing-report.xlsx')}}"><u>Click Here</u></a> -->
        <a class="exp_download" href="{{ url('get-download-xslx','purchasing-report.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>
      <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is already being prepared by another user! Please wait.. </b>
      </div>
      <table id="po_tabale" class="table entriestable table entriestable table-bordered text-center purchasing-report">
        <thead>
          <tr>
            <th>Confirm Date
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="confirm_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="confirm_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Supplier
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Country
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="country">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="country">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>PO#
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_no">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <!-- Sup-1124 -->
            <th>Supplier invoice#</th>
            <th>Supplier invoice Date</th>
            <!-- END -->
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="4">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="4">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Category
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="category">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="category">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['avg_units_for-sales'] }}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="avg_weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="avg_weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['type']}}
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
            <th>Billing Unit
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="billing_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="billing_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['selling_unit']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['minimum_stock']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="minimum_stock">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="minimum_stock">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
            <th>Sum of <br> {{$global_terminologies['qty']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sum_of_qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sum_of_qty">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <!-- Sup-1124 -->
            <th>Conversion Rate</th>
            <th>QTY Into Stock</th>
            <!-- End -->

            <th>{{$global_terminologies['freight_per_billed_unit']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="freight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="freight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>{{$global_terminologies['landing_per_billed_unit']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="landing">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="landing">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>{{$global_terminologies['import_tax_actual']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="import_tax_actual">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="import_tax_actual">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>{{$global_terminologies['cost_price']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="cost_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="cost_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <th>{{$global_terminologies['product_cost']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="product_cost">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="product_cost">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['sum_pro_cost']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sum_pro_cost">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sum_pro_cost">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['cost_unit_thb']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="cost_unit_thb">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="cost_unit_thb">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>{{$global_terminologies['sum_cost_amnt']}}
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sum_cost_amnt">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sum_cost_amnt">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Vat
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

            <!-- Sup-1124 -->
            <th>VAT Amount (EUR)</th>
            <th>VAT Amount (THB)</th>

            <th>Unit Price <br>Before VAT (EUR)</th>
            <th>Unit Price <br>Before VAT (THB)</th>

            <th>Unit Price <br>After VAT (EUR)</th>
            <th>Unit Price <br>After VAT (THB)</th>

            <th>Discount%</th>

            <th>Sub Total (EUR)</th>
            <th>Sub Total (THB)</th>

            <th>Total Amount <br>After VAT (EUR)</th>
            <th>Total Amount <br>After VAT (THB)</th>
            <!-- END -->

            <th>Custom's Inv.#
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="custom_invoice_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="custom_invoice_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Custom's Line#
              <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="custom_line_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="custom_line_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
          </tr>
        </thead>
        <tbody></tbody>
        @if($units_total->count() > 1)
          @php $showFooter = 'd-none'; @endphp
        @else
          @php $showFooter = ''; @endphp
        @endif
        <tfoot align="right" class="{{$showFooter}}">
          <tr>
            <th id="total_head"></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th id="qty_sum"></th>
            <th id="freight_p_b_unit"></th>
            <th id="landing_p_b_unit"></th>
            <th id="total_allocation"></th>
            <th id="total_unit_cost"></th>
            <th id="unit_euro"></th>
            <th id="total_amount_euro"></th>
            <th id="unit_cost_thb"></th>
            <th id="total_amount_thb"></th>
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

<input type="hidden" name="check_the_hit" id="check_the_hit" value="">

<form id="export_purchasing_report_form" method="post"  action="{{route('export-purchasing-report') }}">
  @csrf
  <input type="hidden" name="product_category_exp" id="product_category_exp">
  <input type="hidden" name="product_type_exp" id="product_type_exp">
  <input type="hidden" name="product_type_2_exp" id="product_type_2_exp">
  <input type="hidden" name="product_type_3_exp" id="product_type_3_exp">
  <input type="hidden" name="filter_dropdown_exp" id="filter_dropdown_exp">
  <input type="hidden" name="status_dropdown_exp" id="status_dropdown_exp">
  <input type="hidden" name="supplier_filter_exp" id="supplier_filter_exp">
  <input type="hidden" name="product_id_filter_exp" id="product_id_filter_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="check_the_hit_exp" id="check_the_hit_exp">
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
</form>

@endsection

@section('javascript')
<script type="text/javascript">

  var show_custom_line_number_choice = "{{@$show_custom_line_number}}";
  var show_custom_line_number = '';
  if(show_custom_line_number_choice == 1 && "{{@$bonded_warehouses->count()}}" > 0)
  {
    show_custom_line_number = true;
  }
  else
  {
    show_custom_line_number = false;
  }

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );

  $('#from_date').datepicker('setDate', last_month);
  $('#to_date').datepicker('setDate', 'today');

  var request = "{{$redirect_request}}";
  var type_req = "{{$type}}";
  if(request != "NULL")
  {
    if(type_req == "group" )
    {
      var data = request.split(',');

      var category_id  = data[0];
      var product_id   = data[5];
      var supplier_id  = data[1];
      var filter_value = data[2];
      var from_date    = data[3];
      var to_date      = data[4];

      from_date = from_date.replace("-","/").replace("-","/");
      to_date   = to_date.replace("-","/").replace("-","/");

      if(from_date != 'NoDate')
      {
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
        to_date_split =  to_date.split("/");
        to_date  = to_date_split[2]+'/'+to_date_split[1]+'/'+to_date_split[0];
        document.querySelector("#to_date").value = to_date;
      }
      else
      {
        document.querySelector("#to_date").value = '';
      }
      if(supplier_id != 'NA')
      {
        $('.supplier_id').val(supplier_id).change();
      }
      if(product_id != 'NA')
      {
        $('.product_id').val(product_id).change();
      }
      if(category_id != 'NA')
      {
        $('.product_category_id').val(category_id).change();
      }
      if(filter_value != 'NA')
      {
        $('.filter-dropdown').val(filter_value).change();
      }
    }
    if(type_req == "list")
    {
      var data = request.split(',');
      var product_id = data[0];
      var on_water   = data[1];

      if(product_id != '')
      {
        $('.product_id').val(product_id).change();
      }
      $('#check_the_hit').val(on_water);
      $('#check_the_hit_exp').val(on_water);
    }
  }

  $(function(e){
    // start point
    var order = 1;
    var column_name = '';

    $('.sorting_filter_table').on('click',function(){
      $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
      $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

      order = $(this).data('order');
      column_name = $(this).data('column_name');

      $('#sortbyparam').val(column_name);
      $('#sortbyvalue').val(order);

      $('.purchasing-report').DataTable().ajax.reload();

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
  // ending point


  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
    $(".state-tags").select2();
    var table2 =  $('.purchasing-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
    processing: false,
    // "language": {
    //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    // dom: 'Blfrtip',
    pageLength: {{100}},
    serverSide: true,
    "lengthMenu": [100,150,200,250],
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 0,1,3,4,5] },
      { className: "dt-body-right", "targets": [2,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35 ] },
    ],
    scrollX:true,
    scrollY : '90vh',
    scrollCollapse: true,
    ajax:
    {
      beforeSend:function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      type:'post',
      url:"{!! route('get-purchase-orders-data-for-report') !!}",
      data: function(data) { data.from_date = $('#from_date').val(), data.to_date = $('#to_date').val(),
        data.prod_category = $('.product_category_id option:selected').val(),
        data.product_type = $('.product_type option:selected').val(),
        data.product_type_2 = $('.product_type_2 option:selected').val(),
        data.product_type_3 = $('.product_type_3 option:selected').val(),
        data.filter = $('.filter-dropdown option:selected').val(),
        data.status = $('.status-dropdown option:selected').val(),
        data.supplier = $('.supplier-dropdown option:selected').val(),
        data.product_id = $('.product_id option:selected').val(),
        data.hit_check = $('#check_the_hit').val(),
        data.sortbyparam = column_name,
        data.sortbyvalue = order
    },
      // method: "get",
    },
    columns: [
      { data: 'confirm_date', name: 'confirm_date' },
      { data: 'supplier', name: 'supplier' },
      { data: 'country', name: 'country' },
      { data: 'ref_id', name: 'ref_id'},

      // Sup-1124
      { data: 'supplier_invoice', name: 'supplier_invoice' },
      { data: 'supplier_invoice_date', name: 'supplier_invoice_date' },
      // End

      { data: 'refrence_code', name: 'refrence_code' },
      { data: 'short_desc', name: 'short_desc' },
      { data: 'category', name: 'category' },
      { data: 'avg_weight', name: 'avg_weight' },
      { data: 'product_type', name: 'product_type' },
      { data: 'product_type_2', name: 'product_type_2' @if (!in_array('product_type_2', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
      { data: 'product_type_3', name: 'product_type_3' @if (!in_array('product_type_3', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
      { data: 'buying_unit', name: 'buying_unit' },
      { data: 'unit', name: 'unit' },
      { data: 'minimum_stock', name: 'minimum_stock' },
      { data: 'sum_qty', name: 'sum_qty' },

      // Sup-1124
      { data: 'conversion_rate', name: 'conversion_rate' },
      { data: 'qty_into_stock', name: 'qty_into_stock' },
      // End

      /*new columns added*/
      { data: 'freight', name: 'freight' },
      { data: 'landing', name: 'landing' },
      { data: 'import_tax_actual', name: 'import_tax_actual' },
      { data: 'seller_price', name: 'seller_price' },
      /*new columns added*/
      { data: 'cost_unit', name: 'cost_unit' },
      { data: 'total_cost', name: 'total_cost' },
      { data: 'cost_unit_thb', name: 'cost_unit_thb' },
      { data: 'sum_cost_amount', name: 'sum_cost_amount' },
      { data: 'vat', name: 'vat' },

      // Sup-1124
      { data: 'vat_amount_euro', name: 'vat_amount_euro' },
      { data: 'vat_amount_thb', name: 'vat_amount_thb' },
      { data: 'unit_price_before_vat_euro', name: 'unit_price_before_vat_euro' },
      { data: 'unit_price_before_vat_thb', name: 'unit_price_before_vat_thb' },
      { data: 'unit_price_after_vat_euro', name: 'unit_price_after_vat_euro' },
      { data: 'unit_price_after_vat_thb', name: 'unit_price_after_vat_thb' },
      { data: 'discount_percent', name: 'discount_percent' },
      { data: 'sub_total_euro', name: 'sub_total_euro' },
      { data: 'sub_total_thb', name: 'sub_total_thb' },
      { data: 'total_amount_sfter_vat_euro', name: 'total_amount_sfter_vat_euro' },
      { data: 'total_amount_sfter_vat_thb', name: 'total_amount_sfter_vat_thb' },
      //END

      { data: 'custom_invoice_number', name: 'custom_invoice_number' , visible: show_custom_line_number },
      { data: 'custom_line_number', name: 'custom_line_number' , visible: show_custom_line_number}

    ],
    initComplete: function () {
      // Enable THEAD scroll bars
      $('.dataTables_scrollHead').css('overflow', 'auto');

      // Sync THEAD scrolling with TBODY
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
      $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      $('body').find('.dataTables_scrollHead').addClass("scrollbar");
    },
    drawCallback: function(){
      $('#loader_modal').modal('hide');
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
    },
    footerCallback: function ( row, data, start, end, display ) {
      var api = this.api();
      $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method:"get",
        dataType:"json",
        url:"{{ route('get-purchase-orders-data-for-report-footer-values') }}",
        data:{
          from_date     : $('#from_date').val(),
          to_date       : $('#to_date').val(),
          prod_category : $('.product_category_id option:selected').val(),
          filter        : $('.filter-dropdown option:selected').val(),
          status        : $('.status-dropdown option:selected').val(),
          supplier      : $('.supplier-dropdown option:selected').val(),
          product_id    : $('.product_id option:selected').val(),
          product_type  : $('.product_type option:selected').val(),
          product_type_2: $('.product_type_2 option:selected').val(),
          product_type_3: $('.product_type_3 option:selected').val(),
        },
        beforeSend:function(){
          $( api.column( 0 ).footer() ).html('Loading...');
          $( api.column( 16 ).footer() ).html('Loading...');
          $( api.column( 19 ).footer() ).html('Loading...');
          $( api.column( 20 ).footer() ).html('Loading...');
          $( api.column( 21 ).footer() ).html('Loading...');
          $( api.column( 22 ).footer() ).html('Loading...');
          $( api.column( 23 ).footer() ).html('Loading...');
          $( api.column( 24 ).footer() ).html('Loading...');
          $( api.column( 25 ).footer() ).html('Loading...');
          $( api.column( 26 ).footer() ).html('Loading...');
          $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
          // $('#total_head').html('Totals');
          // $('#qty_sum').html("Loading...");
          // $('#freight_p_b_unit').html("Loading...");
          // $('#landing_p_b_unit').html("Loading...");
          // $('#total_allocation').html("Loading...");
          // $('#total_unit_cost').html("Loading...");
          // $('#unit_euro').html("Loading...");
          // $('#total_amount_euro').html("Loading...");
          // $('#unit_cost_thb').html("Loading...");
          // $('#total_amount_thb').html("Loading...");
        },
        success:function(result){
          $( api.column( 0 ).footer() ).html('Totals');
          $( api.column( 16 ).footer() ).html(result.qty_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 19 ).footer() ).html(result.freight_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 20 ).footer() ).html(result.landing_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 21 ).footer() ).html(result.total_allocation.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 22 ).footer() ).html(result.total_unit_cost.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 23 ).footer() ).html(result.unit_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 24 ).footer() ).html(result.total_amount_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 25 ).footer() ).html(result.unit_cost_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( api.column( 26 ).footer() ).html(result.total_amount_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
          // $('#qty_sum').html(result.qty_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#freight_p_b_unit').html(result.freight_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#landing_p_b_unit').html(result.landing_p_b_unit.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#total_allocation').html(result.total_allocation.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#total_unit_cost').html(result.total_unit_cost.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#unit_euro').html(result.unit_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#total_amount_euro').html(result.total_amount_euro.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#unit_cost_thb').html(result.unit_cost_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $('#total_amount_thb').html(result.total_amount_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
        },
        error: function(){

        }
      });
    },
  });

   $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
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
      }else if(count>0){
        if(e.type == 'focusout'){
           table2.search(this.value).draw();
              return;
                   }
        }else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
        }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });

  $(document).on('change','.product_category_id',function(){
    $("#apply_filter_btn").val("1");
    $('#product_category_exp').val($('.product_category_id option:selected').val());
    var selected = $(this).val();
  });
  $(document).on('change','.product_type',function(){
    $("#apply_filter_btn").val("1");
    $('#product_type_exp').val($('.product_type option:selected').val());
    // var selected = $(this).val();
  });
  $(document).on('change','.product_type_2',function(){
    $("#apply_filter_btn").val("1");
    $('#product_type_2_exp').val($('.product_type_2 option:selected').val());
    // var selected = $(this).val();
  });
  $(document).on('change','.product_type_3',function(){
    $("#apply_filter_btn").val("1");
    $('#product_type_3_exp').val($('.product_type_3 option:selected').val());
    // var selected = $(this).val();
  });

  $(document).on('change','.filter-dropdown',function(){
    $("#apply_filter_btn").val("1");
    $('#filter_dropdown_exp').val($('.filter-dropdown option:selected').val());
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.purchasing-report').DataTable().ajax.reload();
  });
  $(document).on('change','.status-dropdown',function(){
    $("#apply_filter_btn").val("1");
    $('#status_dropdown_exp').val($('.status-dropdown option:selected').val());
  });

  $(document).on('change','.supplier-dropdown',function(){
    $("#apply_filter_btn").val("1");
    $('#supplier_filter_exp').val($('.supplier-dropdown option:selected').val());
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.purchasing-report').DataTable().ajax.reload();
  });

  $(document).on('change','.product_id',function(){
    $("#apply_filter_btn").val("1");
    $('#product_id_filter_exp').val($('.product_id option:selected').val());
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.purchasing-report').DataTable().ajax.reload();
  });

  $('#from_date').change(function() {
    $("#apply_filter_btn").val("1");
    if($('#from_date').val() != '')
    {
      $('#from_date_exp').val($('#from_date').val());
    }
  });

  $('#to_date').change(function() {
    $("#apply_filter_btn").val("1");
    if($('#to_date').val() != '')
    {
      $('#to_date_exp').val($('#to_date').val());
    }
  });

  $(document).on('click','.apply_date',function(){
    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.purchasing-report').DataTable().ajax.reload();
  });


  $('.reset-btn').on('click',function(){
    $("#apply_filter_btn").val("0");
    $('#from_date').val('');
    $('#to_date').val('');
    $('.product_category_id').val('');
    $('.product_type').val('');
    $('.product_type_2').val('');
    $('.product_type_3').val('');
    $('.filter-dropdown').val('');
    $('.status-dropdown').val('');
    $('.supplier-dropdown').val('');
    $('.product_id').val('');

    $('#product_category_exp').val('');
    $('#filter_dropdown_exp').val('');
    $('#from_date_exp').val('');
    $('#to_date_exp').val('');
    $('#supplier_filter_exp').val('');
    $('#product_id_filter_exp').val('');
    $(".state-tags").select2("", "");


    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.purchasing-report').DataTable().ajax.reload();
  });

  $('.export_btn').on('click',function(){

    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    $("#to_date_exp").val($('#to_date').val());
    $("#from_date_exp").val($('#from_date').val());
    $("#product_id_filter_exp").val($('.product_id option:selected').val());
    $("#supplier_filter_exp").val($('.supplier-dropdown option:selected').val());
    $("#filter_dropdown_exp").val($('.filter-dropdown option:selected').val());
    $("#status_dropdown_exp").val($('.status-dropdown option:selected').val());
    $("#product_category_exp").val($('.product_category_id option:selected').val());
    $("#product_type_exp").val($('.product_type option:selected').val());
    $("#product_type_2_exp").val($('.product_type_2 option:selected').val());
    $("#product_type_3_exp").val($('.product_type_3 option:selected').val());

    var form = $('#export_purchasing_report_form');
    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method:"get",
      url:"{{route('export-purchasing-report')}}",
      data:form_data,
      beforeSend:function(){
        $('.export_btn').attr('title','Please Wait...');
        $('.export_btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export_btn').attr('title','EXPORT is being Prepared');
          $('.export_btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForProducts();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export_btn').prop('disabled',true);
          $('.export_btn').attr('title','EXPORT is being Prepared');
          checkStatusForProducts();
        }
      },
      error:function(){
         $('.export_btn').attr('title','Create New Export');
        $('.export_btn').prop('disabled',false);
      }
    });

    //  $("#export_purchasing_report_form").submit();
    //  $('#loader_modal').modal({
    //     backdrop: 'static',
    //     keyboard: false
    //   });
    //   $('#loader_modal').modal('show');
    //   $('.purchasing-report').DataTable().ajax.reload();
  });

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-purchasing-report')}}",
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
          $('.export_btn').attr('title','Create New Export');
          $('.export_btn').prop('disabled',false);
          $('.download-btn').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export_btn').attr('title','Create New Export');
          $('.export_btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $( document ).ready(function() {

    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-purchasing-report')}}",
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
          $('.export_btn').attr('title','EXPORT is being Prepared');
          $('.export_btn').prop('disabled',true);
          checkStatusForProducts();
        }
      }
    });

    if($('#from_date').val() != '')
    {
      $('#from_date_exp').val($('#from_date').val());
    }
    if($('#to_date').val() != '')
    {
      $('#to_date_exp').val($('#to_date').val());
    }
  });
</script>
@stop
