@extends('users.layouts.layout')

@section('title','Users Management | Supply Chain')

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
          <li class="breadcrumb-item active">Stock Movement Report</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
{{-- <div class="row mb-0 ml-3">
  <h4 class="text-uppercase fontbold">Stock Movement Report</h4>
</div> --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-8">
    <h5 class="maintitle text-uppercase fontbold">Stock Movement Report</h5>
  </div>
  @php
    $className='';
    if($last_downloaded==null)
    {
      $className='d-none';
    }
  @endphp
  @if(Auth::user()->role_id != 9)
  <div class="col-2  ">
   <!--  <label>
      <b class=" download-btn-text {{$className}}" ><i>Last created on: {{Carbon::parse($last_downloaded)->format('d/m/Y H:i:s')}}</i> </b>
    </label> -->
    <!-- <div class="input-group-append">

      <a download href="{{'storage/app/Stock-Movement-Report.xlsx'}}"  class="download-btn common-icons {{$className}}" title="Download">
        <span class="">
          <img src="{{asset('public/icons/download.png')}}" width="27px">
      </span>
      </a>

    </div> -->
  </div>

  <div class="col-2">
    <div class="pull-right">
    <!-- <label style="visibility: hidden;">nothing</label> -->
    <div class="input-group-append">
      <!-- <button id="export_s_p_r" class="btn recived-button " >Export</button>   -->
      <!-- <button value="Export" class="btn recived-button rounded export-btn">Create New Export</button>  -->
      <span class="export-btn vertical-icons pull-right" title="Create New Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
      </span>
    </div>
    </div>
  </div>
  @endif
</div>
<div class="row mb-0 filters_div">
  <div class="col-lg-12 col-md-12 title-col">
    <div class="row justify-content-between">

      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control warehouse_id state-tags" name="warehouse_id" >
          @if(Auth::user()->role_id == 9)
          <option value="">All Warehouses</option>
          @foreach($warehouses as $w)
          @if($w->id == 1)
          <option value="{{$w->id}}" selected="">{{$w->warehouse_title}}</option>
          @else
          <option value="{{$w->id}}">{{$w->warehouse_title}}</option>
          @endif
          @endforeach
          @else
          <option value="">All Warehouses</option>
          @foreach($warehouses as $w)
          <option value="{{$w->id}}">{{$w->warehouse_title}}</option>
          @endforeach
          @endif
        </select>
      </div>

      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control all_movement state-tags" name="warehouse_id" >
          <option value="">All Movement</option>
          <option value="1">In Stock</option>
          <option value="2">Re-Order Items</option>
          <option value="3">Need Re-Order</option>
        </select>
      </div>

      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control unit_id state-tags" name="unit_id" >
          <option value="">Units</option>
          @foreach($units as $u)
          <option value="{{$u->id}}">{{$u->title}}</option>
          @endforeach
        </select>
      </div>
      @if(Auth::user()->role_id != 9)
      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control supplier_id state-tags" name="supplier_id" >
          <option value="" selected="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
          @foreach($suppliers as $s)
          <option value="{{$s->id}}">{{$s->reference_name}}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control product_type state-tags" name="product_type" >
          <option value="" disabled="" selected="">Choose Product Type</option>
          @if($product_types)
          @foreach($product_types as $pcat)
              <option value="{{$pcat->id}}">{{$pcat->title}}</option>
          @endforeach
          @endif
        </select>
      </div>
      @if (in_array('product_type_2', $product_detail_section))
      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control product_type_2 state-tags" name="product_type_2" >
          <option value="" disabled="" selected="">Choose @if(!array_key_exists('product_type_2', $global_terminologies)) Product Type 2 @else {{$global_terminologies['product_type_2']}} @endif </option>
          @if($product_types_2)
          @foreach($product_types_2 as $pcat)
              <option value="{{$pcat->id}}">{{$pcat->title}}</option>
          @endforeach
          @endif
        </select>
      </div>
      @endif

      @if (in_array('product_type_3', $product_detail_section))
      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control product_type_3 state-tags" name="product_type_3" >
          <option value="" disabled="" selected="">Choose @if(!array_key_exists('product_type_3', $global_terminologies))Product Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
          @if($product_types_2)
          @foreach($product_types_3 as $pcat)
              <option value="{{$pcat->id}}">{{$pcat->title}}</option>
          @endforeach
          @endif
        </select>
      </div>
      @endif

      <div class="col-md-2 col-6">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags prod_category" name="prod_category">
          <option value="" selected="">Choose Category</option>
          @foreach($product_parent_categories as $product_parent_category)
            <optgroup label='{{$product_parent_category->title}}'>
              @foreach($product_parent_category->get_Child as $pc_child)
                <option value="{{$pc_child->id}}"> {{$pc_child->title}} </option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
      </div>
      <?php
      // $month = date('m');
      // $day = '01';
      // $year = date('Y');
      // $start_of_month = $year . '-' . $month . '-' . $day;
      $start_of_month = date("Y-m-d",strtotime("-1 month"));
      ?>
      <div class="col-md-2 col-6 mr-6">
        <div class="form-group">
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
        </div>
      </div>
      <?php
      $month = date('m');
      $day = date('d');
      $year = date('Y');

      $today = $year . '-' . $month . '-' . $day;
      ?>
      <div class="col-md-2 col-6">
        <div class="form-group">
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
        </div>
      </div>
      <div class="col-md-2 col-6" style="">
      <div class="form-group">
      <div class="input-group-append ml-1">
        <!-- <button class="btn recived-button apply_date" type="button">Apply Filters</button>   -->
         <span class="apply_date vertical-icons mr-4" title="Apply Filters">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
      </span>
      <span class="reset-btn vertical-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
      </div>
      </div>
    </div>
    <div class="col-md-2 col-6"></div>
    <div class="col-md-2 col-6"></div>
    <div class="col-md-2 col-6"></div>
    </div>
  </div>
</div>

<div class="row mb-0 filters_div">
  <div class="col-lg-12 col-md-12 title-col">
    <div class="row justify-content-between">
      <!-- <div class="col-lg-2 col-md-2"></div> -->
      <!-- <div class="col"> -->
        <!-- <div class="input-group-append"> -->
          <!-- <button class="btn recived-button reset-btn rounded" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>   -->

          <!-- <span class="reset-btn common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span> -->
        <!-- </div> -->
      <!-- </div> -->

    </div>
  </div>
  <div class="col-lg-12 col-md-12 title-col">
    <div class="d-sm-flex">
      <div class="col-lg-3 col-md-3">
        <b>Unit:</b> <span id="unit_title">--</span>
      </div>
      <div class="col-lg-3 col-md-3">
        <b>Total Unit:</b> <span id="total_unit">--</span>
      </div>
    </div>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg custompadding">
       <div class="alert alert-primary export-alert d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is being prepared! Please wait.. </b>
      </div>
        <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>

          <b>Export file is ready to download.
          <!-- <a download href="{{'storage/app/Stock-Movement-Report.xlsx'}}"><u>Click Here</u></a> -->
            <a class="exp_download" href="{{ url('get-download-xslx','Stock-Movement-Report.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>

          </b>
        </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-stock-report text-center">
        <thead>
          <tr>
            <!-- <th>
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                  <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
              <label class="custom-control-label" for="check-all"></label>
              </div>
            </th> -->
            <th>{{$global_terminologies['our_reference_number']}}
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="pf">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="pf">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['product_description']}}
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="product_description">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="product_description">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['brand']}}
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="brand">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="brand">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Supplier
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="supplier">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="supplier">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['type']}}
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="type">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="type">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if (!in_array('product_type_2', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="type_2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="type_2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if (!in_array('product_type_3', $product_detail_section)) class="noVis" @endif>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="type_3">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="type_3">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>Minimum <br>Stock
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="min_stock">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="min_stock">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Unit
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Start <br> Count
              <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="start_count">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="start_count">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>In(From <br> Purchase)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="in_from_purchase">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="in_from_purchase">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>In(Manual <br> Adjustment)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="in_manual_adjustment">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="in_manual_adjustment">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>In(Transfer <br> Document)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="in_transfer_document">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="in_transfer_document">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>In(Order <br> Update)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="in_order_update">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="in_order_update">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>IN(Total)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="in_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="in_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Out(Order)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="out_order">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="out_order">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Out(Manual <br> Adjustment)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="out_manual_adjustment">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="out_manual_adjustment">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Out(Transfer <br> Document)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="out_transfer_document">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="out_transfer_document">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>OUT(Total)
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="out_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="out_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th>Balance
              <!-- <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="balance">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="balance">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
            <th>COGS
             <!--  <span class="arrow_up sorting_filter_table" data-order="ASC" data-column_name="cogs">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="DESC" data-column_name="cogs">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            @endif
            <th>History</th>
          </tr>
        </thead>
      </table>
    </div>
    </div>

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

<div>

</div>
<form id="export_sold_product_report_form" method="post">
  @csrf
  <input type="hidden" name="warehouse_id_exp" id="warehouse_id_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="supplier_id_exp" id="supplier_id_exp">
  <input type="hidden" name="unit_id_exp" id="unit_id_exp">
  <input type="hidden" name="all_movement_exp" id="all_movement_exp">
  <input type="hidden" name="product_type_exp" id="product_type_exp">
  <input type="hidden" name="product_type_2_exp" id="product_type_2_exp">
  <input type="hidden" name="product_type_3_exp" id="product_type_3_exp">
  {{-- <input type="hidden" name="tableSearchField" id="tableSearchField"> --}}
  <input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
  <input type="hidden" name="column_name" id="column_name">
  <input type="hidden" name="sort_order" id="sort_order">
</form>


@endsection

@section('javascript')
<script type="text/javascript">

  $("#from_date").datepicker({
     format: "dd/mm/yyyy",
    autoHide: true,


  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  var currentTime = new Date();
  // First Date Of the month
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', startDateFrom);
  $('#to_date').datepicker('setDate', 'today');

var sort_order = 1;
var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    sort_order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#column_name').val(column_name);
    $('#sort_order').val(sort_order);

    $('.table-stock-report').DataTable().ajax.reload();

    if($(this).data('order') ==  'ASC')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == 'DESC')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });

  $(function(e){
    $(".state-tags").select2();

    $(".prod_category").select2({
      matcher: function(params, data) {
          var original_matcher = $.fn.select2.defaults.defaults.matcher;
          var result = original_matcher(params, data);
          if (result && data.children && result.children && data.children.length != result.children.length) {
               result.children = data.children;
          }
          return result;
      }
    });

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

    var table2 = $('.table-stock-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      ordering: false,
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 0,1,2,3] },
        { className: "dt-body-right", "targets": [4,5,6,7 ] },
      ],
      lengthMenu:[50,100,150,200],
      serverSide: true,
      ajax:
      {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url: "{!! route('get-stock-report') !!}",
        data: function(data) {
          data.warehouse_id = $('.warehouse_id option:selected').val(),
          data.supplier_id = $('.supplier_id option:selected').val(),
          data.prod_category = $('.prod_category option:selected').val(),
          data.from_date = $('#from_date').val(),
          data.to_date = $('#to_date').val(),
          data.unit_id = $('.unit_id option:selected').val(),
          data.all_movement = $('.all_movement option:selected').val(),
          data.product_type = $('.product_type option:selected').val(),
          data.product_type_2 = $('.product_type_2 option:selected').val(),
          data.product_type_3 = $('.product_type_3 option:selected').val(),
          data.column_name = column_name,
          data.sort_order = sort_order
         },
      },
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      dom: 'Blfrtip',
      "columnDefs": [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : '' }}], visible: false },
      ],
      buttons: [
        {
          extend: 'colvis',
          columns: ':not(.noVis)',
        }
      ],

      columns: [



        // { data: 'checkbox', name: 'checkbox' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'brand', name: 'brand' },
        { data: 'supplier', name: 'supplier' },
        { data: 'product_type', name: 'product_type' },
        { data: 'product_type_2', name: 'product_type_2' @if (!in_array('product_type_2', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
        { data: 'product_type_3', name: 'product_type_3' @if (!in_array('product_type_3', $product_detail_section)) ,searchable: false, orderable: false, visible: false @endif},
        { data: 'min_stock', name: 'min_stock' },
        { data: 'selling_unit', name: 'selling_unit' },
        { data: 'start_count', name: 'start_count' },
        { data: 'in_purchase', name: 'in_purchase' },
        { data: 'in_manualAdjusment', name: 'in_manualAdjusment' },
        { data: 'in_transferDocument', name: 'in_transferDocument' },
        { data: 'in_orderUpdate', name: 'in_orderUpdate' },
        { data: 'stock_in', name: 'stock_in' },
        { data: 'out_order', name: 'out_order' },
        { data: 'out_manual_adjustment', name: 'out_manual_adjustment' },
        { data: 'out_transfer_document', name: 'out_transfer_document' },

        { data: 'stock_out', name: 'stock_out' },
        { data: 'stock_balance', name: 'stock_balance' },
        @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
          { data: 'cogs', name: 'cogs' },
        @endif
        { data: 'history', name: 'history' },
      ],
     initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

      $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
        var api = this.api()
        var json = api.ajax.json();

        var unit_title = json.title;
        var total_unit = json.total_unit;
        if(unit_title != '')
        {
          $('#unit_title').html(unit_title);
        }
        if(total_unit != 0)
        {
          $('#total_unit').html(total_unit);
        }
        else
        {
          $('#total_unit').html(0.00);
        }
      },
      "rowCallback": function( row, data, index ) {
        var api = this.api()
        var json = api.ajax.json();

        var stock_items = json.stock_items;

        var api = this.api()
        var json = api.ajax.json();

        var stock_min_current = json.stock_min_current;

        if(stock_items == true)
        {
          if (data["stock_balance"] <= 0)
          {
              $(row).remove();
          }
        }

        if(stock_min_current == true)
        {

          if(Number(data['stock_balance']) > Number(data['min_stock']) )
          {
            $(row).remove();
          }
        }

      },
    });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

      var arr = '';
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
        data : {type:'stock_movement_report',column_id:col},
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

    $(document).on('change','.warehouse_id',function(){
      $("#apply_filter_btn").val("1");
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.table-stock-report').DataTable().ajax.reload();
      // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });

  $(document).on('change','.all_movement',function(){
    $("#apply_filter_btn").val("1");
  });
  $(document).on('change','.product_type',function(){
    $("#apply_filter_btn").val("1");
  });
  $(document).on('change','.product_type_2',function(){
    $("#apply_filter_btn").val("1");
  });
  $(document).on('change','.product_type_3',function(){
    $("#apply_filter_btn").val("1");
  });

  $(document).on('change','.unit_id',function(){
    $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-stock-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('change','.supplier_id',function(){
    $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-stock-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('change','.prod_category',function(){
    $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-stock-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#from_date').change(function() {
    // $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-stock-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#to_date').change(function() {
    // $("#apply_filter_btn").val("1");
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.table-stock-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('click','.apply_date',function(){

    if($('#from_date').val()=='')
    {
       toastr.info('Info!', 'Please Select From Date' ,{"positionClass": "toast-bottom-right"});
      return;
    }else if($('#to_date').val() =='')
    {
       toastr.info('Info!', 'Please Select To Date' ,{"positionClass": "toast-bottom-right"});
      return;
    }
    else
    {

    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
    backdrop: 'static',
    keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.table-stock-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }

  });

  $('.reset-btn').on('click',function(){
    $("#apply_filter_btn").val("0");
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('#from_date').datepicker('setDate', startDateFrom);
    $('input[type=search]').val('');
    $('#to_date').datepicker('setDate', 'today');
    // $('#from_date').val("");
    // $('#to_date').val("");
    $('.warehouse_id').val('');
    $('.all_movement').val('');
    $('.product_type').val('');
    $('.product_type_2').val('');
    $('.product_type_3').val('');
    $('.prod_category').val('');
    $('.unit_id').val('');
    $('.supplier_id').val('');
    $(".state-tags").select2("", "");
    sort_order = 1;
    column_name = '';
    $('.table-stock-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });


  });

  $(document).on('click','.export-btn',function(){

    if($("#apply_filter_btn").val() == 1 || $("#apply_filter_btn").val() == "1")
    {
      toastr.error('Error!', 'Apply Filter first then click on Export !!!' ,{"positionClass": "toast-bottom-right"});
      return false;
    }

    if($('#from_date').val()=='')
    {
      toastr.info('Info!', 'Please Select From Date' ,{"positionClass": "toast-bottom-right"});
      return;
    }
    else if($('#to_date').val()=='')
    {
      toastr.info('Info!', 'Please Select To Date' ,{"positionClass": "toast-bottom-right"});
      return;
    }

    $("#supplier_id_exp").val($('.supplier_id option:selected').val());
    $("#warehouse_id_exp").val($('.warehouse_id option:selected').val());
    $("#prod_category_exp").val($('.prod_category option:selected').val());
    $("#all_movement_exp").val($('.all_movement option:selected').val());
    $("#product_type_exp").val($('.product_type option:selected').val());
    $("#product_type_2_exp").val($('.product_type_2 option:selected').val());
    $("#product_type_3_exp").val($('.product_type_3 option:selected').val());
    $("#unit_id_exp").val($('.unit_id option:selected').val());

    $("#from_date_exp").val($('#from_date').val());
    $("#to_date_exp").val($('#to_date').val());


    var form=$('#export_sold_product_report_form');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-status-stock-movement-report')}}",
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

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-stock-movement-report')}}",
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


</script>
@stop

