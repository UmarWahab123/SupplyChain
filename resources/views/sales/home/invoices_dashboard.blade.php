@extends('sales.layouts.layout')
@section('title','Dashboard')
@section('content')
{{-- <style type="text/css">
  .dt-buttons{
    float: right !important;

  }

  .dt-buttons button{
      background: #13436c;
    color: #fff;
    border-radius: 0px;
    font-size: 11px;
    max-height: 34px;
  }
  .dt-buttons button:hover:not(.disabled){
     background-color: #13436c !important;
    color: #fff;
    background-image: none !important;
  }


</style> --}}
<style type="text/css">
  .select2-results__option
{
  display: block !important;
  overflow:  hidden !important;
  white-space: nowrap !important;
}
</style>
{{-- Content Start from here --}}

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
          <li class="breadcrumb-item active">My Invoices</li>
      </ol>
  </div>
</div>

<!-- Right Content Start Here -->
<div class="right-contentIn">

<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->

<div class="col-lg-12">
  <!-- 1st four box row start -->
  <div class="row mb-3">
    @if(Auth::user()->role_id == 7)
    @include('accounting.layouts.dashboard-boxes')
    @else
    @include('sales.layouts.dashboard-boxes')
    @endif
  </div>
  <!-- first four box row end-->
</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>

<div class="d-flex headings-color justify-content-between mb-3 row">

<div class="col-lg-9 col-md-8 col-6">
  @if(Session::has('status'))
  @php $status = Session::get('status') @endphp
  @if(@$status == 11)
  <h4>My UnPaid Invoices</h4>
  @else
  <h4>My Paid Invoices</h4>
  @endif
  @else
  <h4>My Invoices</h4>
  @endif
</div>
<div class="col-6 col-lg-3 col-md-4 text-right">
  <div class="input-group-append d-block">
    <!-- <button id="export_s_p_r" class="btn recived-button px-5 btn-wd export-btn" >Export</button>   -->
    <span class="vertical-icons export_btn" title="Export" id="export_s_p_r">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span>
  </div>
</div>


<div class="col-lg-12 col-md-12 my-3">
  <form id="form_id" class="filters_div">
  <div class="row">
    <div class="col-md-3 col-6" id="invoice-1" style="width:20%;">
      <label><b>Invoices</b></label>
      <div class="form-group mb-0">
        <select class="form-control selecting-tables state-tags sort-by-value invoices-select">
            <option value="3" selected>-- Invoices --</option>
             @foreach(@$quotation_statuses as $status)
           <option value="{{@$status->id}}">{{@$status->title}}</option>
           @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-3 col-6" id="sale-person-2" style="width: 20%;">
      <label><b>Sale Person</b></label>
      <div class="form-group mb-0">
        <select class="form-control state-tags selecting-sale">
            <option value="">-- Sale person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div>
    <!-- <div class="col-lg col-md-6">
      <div class="form-group">
        <select class="form-control selecting-customer">
            <option value="">-- Customers --</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div> -->
<!-- <div class="px-3" id="Choose-customer-2" style="width: 20%;">
  <label><b>Customer</b></label>
      <div class="form-group mb-0">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer" name="customer" required="true">
            <option value="">Choose Customer</option>
            @foreach($customers as $customer)
            @php $id = Session::get('customer_id'); @endphp
            <option value="{{$customer->id}}" {{ ($customer->id == @$id )? "selected='true'":" " }}>{{@$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div> -->

    <!-- <div class="px-3" id="customer-group-2" style="width: 20%;">
      <label><b>Customer Group</b></label>
      <div class="form-group mb-0">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
            <option value="">@if(!array_key_exists('customer_group', $global_terminologies))Customer Group @else {{$global_terminologies['customer_group']}} @endif</option>
            @foreach($customer_categories as $cat)
            <option value="{{$cat->id}}">{{@$cat->title}}</option>
            @endforeach
        </select>
      </div>
    </div> -->

    <!-- <div class="col-lg col-md-4">
        <label>Choose Customers</label>
         <div class="border rounded position-relative custom-input-group autosearch">
          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_customer_search" tabindex="0" name="prod_name" placeholder="Choose Customer / Customer Group" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
          <span id="loader__custom_search" class="position-absolute d-none" style="right:0;top:0;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows" aria-hidden="true"></i>
        </div>
        <p id="myIddd" class="m-0"></p>
      </div> -->
      @php $id = Session::get('customer_id'); @endphp
      <div class="col-md-3 col-6" id="customer-group">
      <label><b>Choose Customer</b></label>
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="selecting-customer-group" required="true">
            <option value="">Choose Customer / Group</option>
            @foreach($customer_categories as $cat)
              <option value="{{'cat-'.@$cat->id}}" class="parent" title="{{@$cat->title}}">{{@$cat->title}} {!! $extra_space !!}{{$cat->customer != null ? $cat->customer->pluck('reference_name') : ''}}</option>
                @foreach($cat->customer as $customer)
                  <option value="{{'cus-'.$customer->id}}" class="child-{{@$cat->id}}" title="{{@$cat->title}}" {{ ($customer->id == @$id )? "selected='true'":" " }} >&nbsp;&nbsp;&nbsp;{{@$customer->reference_name}}{!! $extra_space !!}{{$cat->title}}</option>
                @endforeach
            @endforeach
        </select>
      </div>
    </div>

    <div class="col-md-3 col-6" style="width: 20%;">
      <label><b>Type</b></label>
      <div class="form-group mb-0">
        <input type="text" placeholder="Order# / Draft#" name="input_keyword" class="form-control font-weight-bold order-search" id="input_keyword" autocomplete="off">
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-3 col-6" style="width: 20%;">
      <label id="from_date_label"><b>From Date</b></label>
      <div class="form-group mb-0">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
      </div>

    </div>

    <div class="col-md-3 col-6" style="width: 20%;">
      <label id="to_date_label"><b>To Date</b></label>
      <div class="form-group mb-0">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off">
      </div>
    </div>

    <div class="col-md-3 col-6 mt-auto d-flex" style="width: 20%;">
      <div class="form-check mr-4">
        <input type="radio" class="form-check-input dates_changer invoice_date"  name="date_radio" value='1' checked>
        <label class="form-check-label" for="exampleCheck1"><b>Invoice Date</b></label>
      </div>
      <div class="form-check mr-4">
        <input type="radio" class="form-check-input dates_changer delivery_date"  name="date_radio" value='2'>
        <label class="form-check-label" for="exampleCheck1"><b>Delivery Date</b></label>
      </div>
      <div class="form-check">
        <input type="radio" class="form-check-input dates_changer target_ship_date"  name="date_radio" value='3'>
        <label class="form-check-label" for="exampleCheck1"><b>Target Ship Date</b></label>
      </div>
    </div>

    <div class="col-md-3 col-6" style="width: 20%">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3  reset__phone apply_dates__phone">
        <!-- <button class="btn recived-button apply_date" type="button">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Date">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>

        <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
    </div>
    <div class="col-md-3 col-6 mt-auto " style="width: 20%;">
      <div class="d-block input-group-append" id="reset-2">
       <!--  <button class="btn recived-button reset px-5 btn-wd" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>   -->


      </div>


    </div>
  </div>
  </form>

</div>


<div class="row entriestable-row col-lg-12 pr-0 quotation mt-5" id="quotation">
  <div class="alert alert-danger d-none not-cancelled-alert col-lg-12 ml-3">

  </div>
  @if(Auth::user()->role_id != 7)
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none mb-3">

      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm cancel-quotations
      deleteIcon" title="Cancel"><span><i class="fa fa-times" ></i></span></a>

      <a href="javascript:void(0);">
          <span class="vertical-icons export-pdf-proforma-inc-vat export-pdf-inc-vat" title="Delivery Bill (Inv VAT)">
            <img src="{{asset('public/icons/delivery.png')}}" width="35px">
          </span>
        </a>
        @if($showPrintPickBtn == 1)
        <a href="javascript:void(0);" class="">
          <span class="vertical-icons export-pdf-proforma-exc-vat export-pdf" title="Pro-Forma">
            <img src="{{asset('public/icons/proforma.png')}}" width="27px">
          </span>
        </a>
        @endif
         <a href="javascript:void(0);">
          <span class="vertical-icons export-proforma" title="Print">
            <img src="{{asset('public/icons/print.png')}}" width="27px">
          </span>
        </a>

  </div>
  @endif
  <div class="col-12 pr-0">
    <div class="entriesbg">
             <div class="alert alert-primary export-alert d-none"  role="alert">
                  <i class="  fa fa-spinner fa-spin"></i>
             <b> Export file is being prepared! Please wait.. </b>
            </div>
              <div class="alert alert-success export-alert-success d-none"  role="alert">
                <i class=" fa fa-check "></i>
            <b>Export file is ready to download.
            <a class="exp_download" href="{{ url('get-download-xslx','invoice-sale-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
            </b>
          </div>
          <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
   <b> Export file is already being prepared by another user! Please wait.. </b>
  </div>
          <table class="table entriestable table-bordered table-quotation text-center">
              <thead>
                  <tr>
                      <th class="noVis">
                        <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                          <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                          <label class="custom-control-label" for="check-all"></label>
                        </div>
                      </th>
                      <!-- <th>Action</th> -->
                      <th>Order#
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="in_ref_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="in_ref_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Sales Person
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="user_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="user_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Customer #
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Reference Name
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Reference Address
                        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="rederence_address">
                            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                        </span>
                        <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="rederence_address">
                            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                        </span>
                      </th>
                      <th>Company Name
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="company">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="company">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Tax ID
                        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="tax_id">
                            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                            </span>
                            <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="tax_id">
                            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                        </span>
                        </th>
                      <th>Draft#
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="ref_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="ref_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Inv.#
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="in_ref_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="in_ref_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>VAT Inv (-1)</th>
                      <th>VAT</th>
                      <th>Inv.#
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="in_ref_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="in_ref_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Non VAT <br>Inv (-2)</th>
                      <!-- <th>Date Purchase</th> -->
                      <th>Discount
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="discount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="discount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Sub Total
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sub_total_amount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sub_total_amount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Order Total
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_amount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_amount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Payment Reference
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="payment_reference">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="payment_reference">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Received Date
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="recv_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="recv_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Delivery Date
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="delivery_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="delivery_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Invoice Date
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="inv_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="inv_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Due Date
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="due_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="due_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Target Ship Date
                        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_ship_date">
                            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                          </span>
                          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_ship_date">
                            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                          </span>
                      </th>
                      <th>Remark</th>
                      <th width="10%" style="overflow: hidden;">Comment To <br> Warehouse</th>
                      <th>Ref. Po #</th>
                      <th>Status
                      <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="status">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="status">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                      </th>
                      <th>Printed
                        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="printed">
                          <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                        </span>
                        <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="printed">
                          <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                        </span>
                      </th>
                  </tr>
              </thead>
                <!-- <tfoot>
                  <tr>
                    {{-- <th colspan="5" style="text-align: right;"></th> --}}
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
                    <th style="text-align: right;"></th>
                    <th  style="text-align: left;"></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    {{-- <th colspan="4" style="text-align: left;"></th> --}}
                  </tr>
                </tfoot> -->
          </table>
          <table id="footer_table" width="100%" class="table-bordered mt-2">
              <tbody>
                <td class="phone_footer_table" colspan="2" style="text-align: right; padding:5px 15px 5px; font-weight: bold; font-size: 1.1rem;">
                  Order Sub Total For All Entries
                </td>
                <td class="phone_footer_table" style="padding:5px 0 5px 15px; font-weight: bold; font-size:1.1rem;" id="sub_total_val_td">
                  Loading ...
                </td>
                <td class="phone_footer_table" colspan="2" style="text-align: right; padding:5px 15px 5px; font-weight: bold; font-size: 1.1rem;">Order Total For All Entries</td>
                <td class="phone_footer_table" id="total_val_td" style="padding:5px 0 5px 15px; font-weight: bold; font-size:1.1rem;">Loading...</td>
              </tbody>
          </table>
          <input type="hidden" name="is_paid" class="is_paid" value="0">
        </div>
  </div>
</div>

</div>
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

 <!-- main content end here -->
</div>

<!-- <form id="export_sold_product_report_form" method="post"  action="{{route('export-invoice-table') }}"> -->
  <form id="export_sold_product_report_form">
  @csrf
  <input type="hidden" name="dosortbyx" id="dosortbyx">
  <!-- <input type="hidden" name="selecting_customerx" id="selecting_customerx"> -->
  <input type="hidden" name="from_datex" id="from_datex">
  <input type="hidden" name="to_datex" id="to_datex">
  @if(Auth::user()->role_id == 3)
  <input type="hidden" name="selecting_salex" id="selecting_salex" value="{{Auth::user()->id}}">
  @else
  <input type="hidden" name="selecting_salex" id="selecting_salex">
  @endif
  <input type="hidden" name="typex" id="typex" value="invoice">
  <input type="hidden" name="selecting_customer_groupx" id="selecting_customer_groupx">
  <input type="hidden" name="is_paidx" id="is_paidx" value="0">
  <input type="hidden" name="date_radio_exp" id="date_radio_exp" >
  <input type="hidden" name="input_keyword_exp" id="input_keyword_exp" >
  <input type="hidden" name="customer_id_select" id="customer_id_select" >
  <input type="hidden" name="className" id="className" >

</form>

@endsection

@php
    $hidden_by_default = '';
@endphp

@section('javascript')
<script type="text/javascript">

var order = 1;
var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.table-quotation').DataTable().ajax.reload();

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


  // search of a invoice code start here


  $('#input_keyword').keyup(function(event){
    $("#input_keyword_exp").val($('#input_keyword').val());
    var query = $(this).val();

    if(event.keyCode == 13)
    {
      if(query.length > 2)
      {

        // alert(query);
        $('.table-quotation').DataTable().ajax.reload();
      }
      else if(query.length == 0)
      {
        $('.table-quotation').DataTable().ajax.reload();
      }
      else
      {
        // $('#input_keyword').empty();
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
  });
  // search of a invoice code ends here

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });
  $('#to_date').val(null);


  $(document).ready(function(){
  $('#date_radio_exp').val($("input[name='date_radio']:checked").val());
  // var last_month = new Date();
  // last_month.setDate( last_month.getDate() - 30 );
  // let today = new Date().toISOString().substr(0, 10);
  // document.querySelector("#to_date").value = today;
  // document.querySelector("#from_date").value = last_month.toISOString().substr(0, 10);

  var currentTime = new Date();
  // First Date Of the month
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', startDateFrom);
  $('#to_date').val(null);

$('#from_datex').val($('#from_date').val());

@if(Session::has('total_invoices'))
// // alert('yes');
// var last_month = new Date();
//   var first_date = new Date(last_month.getFullYear(), last_month.getMonth(), 1);
//   first_date.setDate( first_date.getDate() + 1 );
//   // alert(first_date.toISOString().substr(0, 10));
//   let today1 = new Date().toISOString().substr(0, 10);
//   document.querySelector("#from_date").value = first_date.toISOString().substr(0, 10);
//   document.querySelector("#to_date").value = today1;

  var currentTime = new Date();
  // First Date Of the month
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', startDateFrom);
  $('#to_date').datepicker('setDate', null);

@elseif(Session::has('find'))
var id = "{{Session::get('customer_id')}}";
var dat = "{{Session::get('month')}}";

var full_date = dat.split('-');
var year = full_date[0];
var month = full_date[1];
var datee = '01';

var year1 = full_date[0];
var month1 = full_date[1];


var getDaysInMonth = function(month,year) {
  // Here January is 1 based
  //Day 0 is the last day in the previous month
 return new Date(year, month, 0).getDate();
// Here January is 0 based
// return new Date(year, month+1, 0).getDate();
};

var datee1 = getDaysInMonth(month1, year1);

var from_date =  datee+ "/" + month + "/" + year;
var to_date =  datee1+ "/" + month1 + "/" + year1;
// alert(dateStr);
document.querySelector("#from_date").value = from_date;
document.querySelector("#to_date").value = to_date;


@endif
@if(Session::has('status')){
  var status = "{{Session::get('status')}}";
  if(status == 11){
    $('.is_paid').val(11);
    $('.sort-by-value').val(11);
        $('#is_paidx').val(11);

    // alert($('.is_paid').val());
    // return;
     // $('.table-quotation').DataTable().ajax.reload();
  }
   if(status == 24){
    $('.is_paid').val(24);
    $('.sort-by-value').val(24);
        $('#is_paidx').val(24);

    // alert($('.is_paid').val());
    // return;
     // $('.table-quotation').DataTable().ajax.reload();
  }
}
@endif
@if(Session::has('year'))
  var year = "{{Session::get('year')}}";
  var from_date = year+'-01-01';
  var to_date = year+'-12-31';
  console.log(from_date,to_date);
  document.querySelector("#from_date").value = from_date;
  document.querySelector("#to_date").value = to_date;

@endif

$('input[type=radio][name=date_radio]').change(function() {
    if (this.value == '1') {
      $('#date_radio_exp').val(this.value);
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    }
    else if (this.value == '2') {
      $('#date_radio_exp').val(this.value);
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    }
});

});
  $(function(e){

    //to fill widget values
    $.ajax({
      method:"get",
      url:"{{ route('get-widgets-values') }}",
      beforeSend:function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
          });
         // $("#loader_modal").modal('show');
      },
      success:function(response){
          // $("#loader_modal").modal('hide');
          $('.admin-quotation-count').html(response.quotation);
          $('.total_amount_of_quotation_admin').html(response.total_amount_of_quotation);
          $('.total_number_of_draft_invoices_admin').html(response.total_number_of_draft_invoices);
          $('.admin_total_sales_draft_admin').html(response.admin_total_sales_draft);
          $('.total_number_of_invoices_admin').html(response.total_number_of_invoices);
          $('.admin_total_sales').html(response.admin_total_sales);
          $('.total_gross_profit').html(response.total_gross_profit);
          $('.total_gross_profit_count').html(response.total_gross_profit_count);
          $('.total_amount_of_overdue_invoices_count').html(response.total_amount_of_overdue_invoices_count);
          $('.total_amount_overdue').html(response.total_amount_overdue);
          $('.company_total_sales').html(response.company_total_sales);
          $('.salesCustomers').html(response.salesCustomers);
          $('.totalCustomers').html(response.totalCustomers);
          $('.sales_coordinator_customers_count').html(response.sales_coordinator_customers_count);
          $('.salesQuotations').html(response.salesQuotations);
          $('.salesCoordinateQuotations').html(response.salesCoordinateQuotations);
          $('.salesDraft').html(response.salesDraft);
          $('.salesCoordinateDraftInvoices').html(response.salesCoordinateDraftInvoices);
          $('.invoice1').html(response.Invoice1);
          $('.salesInvoice').html(response.salesInvoice);
          $('.salesCoordinateInvoices').html(response.salesCoordinateInvoices);
          $('.salesCoordinateInvoicesAmount').html(response.salesCoordinateInvoicesAmount);
      },
      error: function(request, status, error){
        $("#loader_modal2").modal('hide');
      }
  });
    //ends here
    $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

    $("#dosortbyx").val($('.sort-by-value option:selected').val());

    $('.sort-by-value').on('change', function(e){
      $("#dosortbyx").val($('.sort-by-value option:selected').val());
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.selecting-customer').on('change', function(e){
      $("#selecting_customerx").val($('.selecting-customer option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });


    $('.selecting-customer-group').on('change', function(e){
      $("#selecting_customer_groupx").val($('.selecting-customer-group option:selected').val());

      $("#selecting_customer_groupx").val($('.selecting-customer-group option:selected').val());

      // alert($('.selecting-customer option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
      document.getElementById('quotation').style.display = "block";

    });

    $('#from_date').change(function() {
      var date = $('#from_date').val();
      $("#from_datex").val(date);

      // $('.table-quotation').DataTable().ajax.reload();
      // document.getElementById('quotation').style.display = "block";
    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();
      $("#to_datex").val(date);

      // $('.table-quotation').DataTable().ajax.reload();
      // document.getElementById('quotation').style.display = "block";
    });

    $(document).on('click','.apply_date',function(){
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.reset').on('click',function(){
      // $('.selecting-customer').val('');
      // $('.selecting-customer-group').val('');
      // $('.sort-by-value').val(3);
      $('.selected-item').addClass('d-none');
      $('#customer_id_select').val(null);
      $('#header_customer_search').val('');
      $('#input_keyword').empty();
      $('#form_id').trigger("reset");

      $('#dosortbyx').val(3);
      // $('#selecting_customerx').val(null);
      $('#from_datex').val(null);
      $('#to_datex').val(null);
      $('#input_keyword_exp').val(null);
      $('#selecting_salex').val(null);
      // $('#selecting_customer_groupx').val(null);

      // $(".state-tags").select2("destroy");
      // $(".state-tags").select2();
      $('.state-tags').val('').trigger('change');
      $('.invoices-select').val('3').trigger('change');
      // $('.table-quotation').DataTable().fnDraw();
    });

    // $('#export_s_p_r').on('click',function(){
    //   $("#export_sold_product_report_form").submit();


    // });


    // $(document).on('click', '.exp_download',function(){

    // });

    $(document).on('click','#export_s_p_r',function(){

      // var d = new Date();
      // var strDate = d.getFullYear() + "/" + (d.getMonth()+1) + "/" + d.getDate();
      // var new_date = new Date($.now());
      // var down_link = "{{ url('storage/app/invoice-sale-export.xlsx?version=2')}}";
      // $('.exp_download').attr('href',down_link+strDate);
      // alert(new_date);

    $('#dosortbyx').val($('.sort-by-value option:selected').val());
    // $("#selecting_customerx").val($('.selecting-customer option:selected').val());
    var date = $('#from_date').val();
    $("#from_datex").val(date);
    var date = $('#to_date').val();
    $("#to_datex").val(date);
    $("#selecting_salex").val($('.selecting-sale option:selected').val());
    $("#selecting_customer_groupx").val($('.selecting-customer-group option:selected').val());
    $('#date_radio_exp').val($("input[name='date_radio']:checked").val());
    $("#input_keyword_exp").val($('#input_keyword').val());

     var form=$('#export_sold_product_report_form');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"post",
      url:"{{route('export-invoice-table')}}",
      data:form_data,
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
          checkStatusForInvoiceTable();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForInvoiceTable();
        }
      },
      error:function(){
        $('.export-btn').prop('disabled',false);
      }
    });
});

    $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-invoice-table')}}",
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
          checkStatusForInvoiceTable();
        }
      }
    });
  });
  function checkStatusForInvoiceTable()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-invoice-table')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForInvoiceTable();
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

    var table2 = $('.table-quotation').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      searching:false,
      colReorder: {
				realtime: false,
			},
      dom: 'lrtip',
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      // bSort: false,
			// bInfo: false,
			// paging: false,
      responsive: true,

      dom: 'Blfrtip',
      "lengthMenu": [100,200,300,400],

        // dom: 'ftipr',
        // dom: 'Bfrtip',
      "columnDefs": [
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,10,13,14 ] },
        { className: "dt-body-right", "targets": [8,9,11,12] }
      ],
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      buttons: [
        {
          extend: 'colvis',
          columns: ':not(.noVis)',
        }
      ],

      ajax:{
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
          $('.selected-item').addClass('d-none');
        },
        url:"{!! route('get-completed-quotation') !!}",
        // data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() ,data.selecting_sale = $('.selecting-sale option:selected').val(),data.type = 'invoice',data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),data.is_paid = $('.is_paid').val() ,data.input_keyword = $('#input_keyword').val(),data.date_type = $("input[name='date_radio']:checked").val()} ,
        data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('#customer_id_select').val(), data.className = className,data.is_paid = $('.is_paid').val() ,data.input_keyword = $('#input_keyword').val(),data.date_type = $("input[name='date_radio']:checked").val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),data.type = 'datatable',
          data.sortbyparam = column_name,
          data.sortbyvalue = order
        } ,
      },
      columns: [
          { data: 'checkbox', name: 'checkbox' },
          // { data: 'action', name: 'action' },
          { data: 'inv_no', name: 'inv_no' },
          { data: 'sales_person', name: 'sales_person' },
          { data: 'customer_ref_no', name: 'customer_ref_no' },
          { data: 'customer', name: 'customer' },
          { data: 'reference_address', name: 'reference_address' },
          { data: 'customer_company', name: 'customer_company' },
          { data: 'tax_id', name: 'tax_id' },
          { data: 'ref_id', name: 'ref_id' },
          { data: 'reference_id_vat', name: 'reference_id_vat' },
          { data: 'sub_total_1', name: 'sub_total_1' },
          { data: 'vat_1', name: 'vat_1' },
          { data: 'reference_id_vat_2', name: 'reference_id_vat_2' },
          { data: 'sub_total_2', name: 'sub_total_2' },
          { data: 'discount', name: 'discount' },
          { data: 'sub_total_amount', name: 'sub_total_amount' },
          { data: 'total_amount', name: 'total_amount' },
          { data: 'payment_reference_no', name: 'payment_reference_no' },
          { data: 'received_date', name: 'received_date' },
          { data: 'delivery_date', name: 'delivery_date' },

          { data: 'invoice_date', name: 'invoice_date' },
          { data: 'target_ship_date', name: 'target_ship_date' },
          { data: 'due_date', name: 'due_date' },
          { data: 'remark', name: 'remark' },
          { data: 'comment_to_warehouse', name: 'comment_to_warehouse' },
          { data: 'memo', name: 'memo' },
          { data: 'status', name: 'status' },
          { data: 'printed', name: 'printed' },
      ],
      initComplete: function () {
        // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');

        // Sync THEAD scrolling with TBODY
        $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
        @if(@$display_purchase_list)
					table2.colReorder.order( [{{$display_purchase_list->display_order}}]);
				@endif


          $('body').find('.dataTables_scrollHead').addClass("scrollbar");
          $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      },
      // drawCallback: function(){
      // },
      "fnDrawCallback": function() {
        $('#loader_modal').modal('hide');
      },
      footerCallback: function ( row, data, start, end, display ) {
        // var api = this.api();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"get",
          dataType:"json",
          url:"{{ route('get-completed-quotation') }}",
          data:{ dosortby : $('.sort-by-value option:selected').val(),selecting_customer : $('.selecting-customer option:selected').val(),from_date : $('#from_date').val(),to_date : $('#to_date').val() ,selecting_sale : $('.selecting-sale option:selected').val(),type : 'invoice',selecting_customer_group : $('.selecting-customer-group option:selected').val(),is_paid : $('.is_paid').val() ,input_keyword : $('#input_keyword').val(),date_type : $("input[name='date_radio']:checked").val(),type: 'footer'},
          beforeSend:function(){
          },
          success:function(result){
            var total = result.post;
            var sub_total = result.sub_total;

            // total = parseFloat(total).toFixed(2);
            // total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            // sub_total = parseFloat(sub_total).toFixed(2);
            // sub_total = sub_total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            $('#total_val_td').html(total);
            $('#sub_total_val_td').html(sub_total);
          },
          error: function(){

          }
        });
      },

   });

   table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      // var arr = "{{@$display_purchase_list->display_order}}";
      var arr = table2.colReorder.order();
			// var all = arr.split(',');
      var all = arr;
			if(all == ''){
				var col = column;
			}else{
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
        data : {type:'my_invoices',column_id:col},
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
          }
        }
      });
   });
  table2.on( 'column-reorder', function ( e, settings, details ) {
    $.get({
    url : "{{ route('column-reorder') }}",
    dataType : "json",
    data : "type=my_invoices&order="+table2.colReorder.order(),
    beforeSend: function(){
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $('#loader_modal').modal('hide');
    },
    success: function(data){
      $('#loader_modal').modal('hide');
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
    table2.ajax.reload();
    var headerCell = $( table2.column( details.to ).header() );
    headerCell.addClass( 'reordered' );

	});

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
    if(e.keyCode == 13) {
      // alert();
            table2.search($(this).val()).draw();
    }
    });

    $('.selecting-sale').on('change', function(e){

    $("#selecting_salex").val($('.selecting-sale option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();

    });

    $(document).on('click', '.check-all1', function () {
        if(this.checked == true){
        $('.check1').prop('checked', true);
        $('.check1').parents('tr').addClass('selected');
        var cb_length = $( ".check1:checked" ).length;
        if(cb_length > 0){
          $('.selected-item').removeClass('d-none');
        }
      }else{
        $('.check1').prop('checked', false);
        $('.check1').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');

      }
    });

   $(document).on('click', '.check1', function () {
    // $(this).removeClass('d-none');
   // $('.cancel-quotations').removeClass('d-none');
   // $('.revert-quotations').removeClass('d-none');
   $('.selected-item').removeClass('d-none');
        var cb_length = $( ".check1:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){
        $('.selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.selected-item').addClass('d-none');
        }

      }
    });

   $(document).on('click','.export-pdf',function(e){
      e.preventDefault();
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });
      // console.log(selected_quots[0]);
      var sort = 'id_sort';
      var bank_id = null;
      var orders = selected_quots;
      var with_vat = null;
      var show_discount = 0;
      var page_type = 'complete-invoice';
      var is_proforma = 'yes';
      var is_texica = null;
      var url = "{{url('sales/export-quot-to-pdf-exc-vat')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+is_proforma+"/"+bank_id;
      window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
    });
    $(document).on('click','.export-pdf-inc-vat',function(e){
      e.preventDefault();
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });
      // console.log(selected_quots[0]);
      var sort = 'id_sort';
      var bank_id = null;
      var orders = selected_quots;
      var with_vat = "1";
      var show_discount = 0;
      var page_type = 'complete-invoice';
      var is_proforma = 'yes';
      var is_texica = null;
      var url = "{{url('sales/export-quot-to-pdf-inc-vat')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+is_proforma+"/"+bank_id;
      window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
    });

    $(document).on('click', '.export-proforma', function(e){
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });
      var sort = 'id_sort';

      var bank = "{{@$bank}}";
      var bank_id = null;

      var orders = selected_quots;
      var page_type = 'complete-invoice';
      var column = column_name != '' ? column_name : 'id';
      var url = "{{url('sales/export-proforma-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+column+"/"+sort+"/"+bank_id;
          window.open(url, 'Orders Print', 'width=1200,height=600,scrollbars=yes');
    });


   $(document).on('click', '.cancel-quotations', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to cancel selected orders?",
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
          dataType:"json",
          data: {quotations : selected_quots},
          url:"{{ route('cancel-invoice-orders') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            $('#loader_modal').modal('hide');
            if(result.success == true)
            {
              // toastr.success('Success!', 'Orders Cancelled Successfully',{"positionClass": "toast-bottom-right"});
              //delete will redrect to cancel order page.
                    swal({
                            title: "Success",
                            text: "Go to Cancelled Order Page to Restore.",
                            type: "success",
                            showCancelButton: true,
                            cancelButtonText:"Close",
                            showCloseButton: true,
                            confirmButtonText: "Cancelled Orders Page",
                            closeOnConfirm: false,
                            showLoaderOnConfirm: true
                          }, function () {
                             window.location = "{{ route('get-cancelled-orders') }}";
                          });

              $('.table-quotation').DataTable().ajax.reload();
              // $('.cancel-quotations').addClass('d-none');
               // $('.revert-quotations').addClass('d-none');
               $('.selected-item').addClass('d-none');
              $('.check-all1').prop('checked',false);
             // window.location.href = "{{ url('sales/get-cancelled-orders')}}";

            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });



  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
      @php
       Session()->forget('successmsg');
      @endphp
  @endif
  });
$('#header_customer_search').on('click',function(){
  if($('.custom__search_arrows').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($(this).val(),_token);
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
        // alert('here');
        $('#loader__custom_search').removeClass('d-none');
        // $('.custom__search_arrows').removeClass('fa-caret-down');
        // $('.custom__search_arrows').addClass('fa-caret-up');
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
      $("#customer_id_select").val(li_id);
      $("#customer_id_exp").val(li_id);
      $(".select_customer_id").hide();
      $('#header_customer_search').val(li_text);
      className = $(this).hasClass('parent') ? 'parent' : 'child';
      $('#className').val(className);

    $('.table-quotation').DataTable().ajax.reload();

    // $(".customer_id").val(li_id);


});
  $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   })
</script>
@stop
