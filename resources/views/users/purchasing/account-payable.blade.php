@extends('users.layouts.layout')

@section('title','Account Payable | Supply Chain')

@section('content')
<style type="text/css">
  a
  {
    cursor:pointer;
  }
  .input_style{
    border: 1px solid #ced4da;
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
          <li class="breadcrumb-item active">Account Payable</li>
      </ol>
  </div>
</div>
<div class="row mb-3">
  <div class="col-lg col-md-4 pb-md-3 ">
    <div class="bg-white box1 pt-4 pb-4 dashboard-boxes-shadow">
      <div class="d-flex align-items-center justify-content-center">
        <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
        <div class="title pl-2">
          <h6 class="mb-0 headings-color number-size">{{number_format($purchase_order, 2, '.', '')}}</h6>
          <span class="span-color">Purchase Orders</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg col-md-4 pb-md-3 ">
    <div class="bg-white box2 pt-4 pb-4 dashboard-boxes-shadow">
      <div class="d-flex align-items-center justify-content-center">
        <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
        <div class="title pl-2">
          <h6 class="mb-0 headings-color number-size">{{number_format($payments, 2, '.', '')}}</h6>
          <span class="span-color">Payments</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg col-md-4 pb-md-3 ">
    <div class="bg-white box3 pt-4 pb-4 dashboard-boxes-shadow">
      <div class="d-flex align-items-center justify-content-center">
        <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
        <div class="title pl-2">
          <h6 class="mb-0 headings-color number-size">{{number_format($account_receivable, 2, '.', '')}}</h6>
          <span class="span-color">Account Receivable</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg col-md-4 pb-md-3 ">
    <div class="bg-white box4 pt-4 pb-4 dashboard-boxes-shadow">
      <div class="d-flex align-items-center justify-content-center">
        <img src="{{asset('public/site/assets/sales/img/img5.1.jpg')}}" class="img-fluid">
        <div class="title pl-2">
          <h6 class="mb-0 headings-color number-size">{{$delete_transaction}}</h6>
          <span class="span-color">Delete Transaction</span>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Right Content Start Here -->
<div class="row d-flex align-items-center left-right-padding mb-2 form-row">
  <h4>Account Payable</h4>
</div>
<div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg-3 col-md-4">
      <h4 class="custom-supplier-list">Purchase Orders</h4>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 ml-lg-auto ml-md-auto" ></div>
    <div class="col-xl-2 col-lg-3 col-md-4">

    <div class="col-6 col-lg-3 col-md-4 text-right float-right mr-4">
      <div class="input-group-append d-block">
        <span class="vertical-icons export_btn" title="Export" id="export_s_p_r">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
      </div>
  </div>

    </div>
  </div>

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row filters_div">
      <div class="col-lg col-md-6 mt-2 ">
        <div class="form-group">
          <label>Select Suppliers</label>
          <select class="form-control selecting-supplier state-tags">
              <option value="">-- Suppliers --</option>
              @foreach($suppliers as $supplier)
              <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>
      <div class="col-lg col-md-6">

        <div class="form-group mr-2">
          <label>Select PO</label>
          <input type="text" class="form-control select-po-input" id="order_no" placeholder="PO #" name="text2">
        </div>

      </div>

      <div class="col-lg col-md-6">

        <div class="form-group mr-2">
          <label>Search by value</label>
          <input type="number" class="form-control select-value-input" id="search_by_val" placeholder="Search by Value" name="text2">
        </div>

      </div>


      <div class="col-lg col-md-6">

        <div class="form-group">
          <label>From Target Ship Date</label>
           <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date_target" id="from_date" autocomplete="off">
        </div>

      </div>
      <div class="col-lg col-md-6">

        <div class="form-group">
          <label>To Target Ship Date</label>
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date_target" id="to_date" autocomplete="off">
        </div>

      </div>

      <div class="col-lg p-0" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Dates" id="apply_filter">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
        </span>
        <span class="common-icons reset-btn" id="reset-btn" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>

      <!-- <div class="col">
        <div class="form-group mb-0">
        <label><b style="visibility: hidden;">Reset</b></label>
        <input type="button" value="Reset" class="btn recived-button reset-btn">
        </div>
      </div> -->

  </div>
<div class="right-content pt-0">

<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->


 <div class="col-lg-12 col-md-12 ">




<div class="entriesbg bg-white custompadding customborder">
  <div class="row d-flex align-items-center left-right-padding mb-4 form-row">
      <!-- <div class="col">
      </div> -->

      <div class="col-2">
        <span>  Date </span>
          <input type="text" placeholder="Date" name="oi_received_date" class="form-control font-weight-bold" id="oi_received_date" autocomplete="off" style="min-height: 27px;height: 27px;border-radius: 0px;">
      </div>


      <div class="col-2">
        <span> Payment Reference </span>
          <input type="text" name="oi_payment_reference_no" id="oi_payment_reference_no" class="input_style">
      </div>

      <div class="col-2">
        <span> Total Paid </span>
          <input type="text" name="paid_amount" id="paid_amount" readonly disabled="true" class="input_style">
      </div>

      <div class="col-2">
        <span>Payment Methods</span>
            <select class="font-weight-bold  oi_payment_method input_style" name="oi_payment_method" required="true" style="height: 27px;width: 100%">
                <option value="">Payment Method</option>
                @foreach($payment_methods as $payment_method)
                <option value="{{$payment_method->id}}">{{@$payment_method->title}}</option>
                @endforeach
            </select>
      </div>


      <div class="col-2">
        <label></label>
        <a class="received_amount btn recived-button btn-sm">@if(!array_key_exists('paid_amount', $global_terminologies))Paid Amount @else {{$global_terminologies['paid_amount']}} @endif</a>
      </div>
      <!-- <div class="col-2">
        <label></label>
       <a class="btn recived-button btn-sm view_orders">View</a></div>    -->


  </div>
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
             <div class="alert alert-primary export-alert d-none"  role="alert">
                  <i class="  fa fa-spinner fa-spin"></i>
             <b> Export file is being prepared! Please wait.. </b>
            </div>
              <div class="alert alert-success export-alert-success d-none"  role="alert">
                <i class=" fa fa-check "></i>
            <b>Export file is ready to download.
            <a class="exp_download" href="{{ url('get-download-xslx','account-payable-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
            </b>
          </div>
          <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
   <b> Export file is already being prepared by another user! Please wait.. </b>
  </div>
<div class="table-responsive ">
  <table class="table entriestable table-bordered text-center account-payable-purchase-orders" >
        <thead>
            <tr>
              <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_receive_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_receive_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
              </th>
                <th>PO #
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>PO Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="created_at">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="created_at">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>Invoice Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>@if(!array_key_exists('supplier', $global_terminologies))Supplier @else {{$global_terminologies['supplier']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>Supplier Invoice<br>Number
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
                <th>Memo
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="memo">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="memo">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>Supplier Currency
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_currency">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_currency">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>Payment Term
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="payment_terms">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="payment_terms">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>Payment<br>Due Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="payment_due_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="payment_due_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
                <th>Total Amount<br>(W/O VAT)
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
                <th>Total Amount<br>(+VAT)
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_total_with_vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_total_with_vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
                <th> @if(!array_key_exists('invoice_exg_rate', $global_terminologies))Invoice Exg Rate @else {{$global_terminologies['invoice_exg_rate']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_exchange_rate">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_exchange_rate">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th> @if(!array_key_exists('posted_amount', $global_terminologies))Posted Amount @else {{$global_terminologies['posted_amount']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_total_in_thb">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_total_in_thb">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th> @if(!array_key_exists('paid_amount', $global_terminologies))Paid Amount @else {{$global_terminologies['paid_amount']}} @endif
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="amount_paid">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="amount_paid">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th> @if(!array_key_exists('payment_exchange_rate', $global_terminologies))Payment Exchange<br> Rate @else {{$global_terminologies['payment_exchange_rate']}} @endif
                {{-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="exchange_rate">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="exchange_rate">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> --}}
            </th>
                <th>Total <br>Payment
                </th>
                <th>@if(!array_key_exists('difference', $global_terminologies))Difference @else {{$global_terminologies['difference']}} @endif

                </th>
                <!-- <th>Total <br> to be paid</th> -->
                <th>Action</th>
               <!--  <th></th>
                <th></th>
                <th></th>
                <th></th> -->

            </tr>
        </thead>
        <tbody></tbody>


    </table>

</div>
</div>


</div>

</div>











<div class="row mb-3">
<!-- left Side Start -->
  <div class="col-lg-6 col-md-12 mb-md-3">

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg">
      <h4 class="custom-supplier-list">Account Payables</h4>
    </div>

  </div>

  <div class="row d-flex align-items-center left-right-padding form-row filters_div">


    <div class="col">
        <div class="form-group">
          <label>Select Supplier</label>
          <select class="form-control selecting-supplier-accR state-tags">
              <option value="">-- Suppliers --</option>
              @foreach($suppliers as $supplier)
              <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>

      <div class="col">
        <div class="form-group" style="margin-top: 22px;">
        <label></label>
        <!-- <input type="button" value="Reset" class="btn recived-button reset-btn-accR"> -->
        <span class="common-icons reset-btn-accR" title="Reset" style="padding-top: 10px;padding-bottom: 10px;">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
        </div>
      </div>
  </div>
  <div class="entriesbg bg-white custompadding customborder">

<div class="table-responsive">
    <table  class="table entriestable table-bordered text-center supplier-orders-table">
      <thead>
        <tr>
          <th style="width: 10%;" >Reference<br>Name</th>
          <th>Total</th>
          <th>Total Due</th>
          <th>Total Not Due </th>
          <th>Action </th>
        </tr>
      </thead>

      <tfoot>
        <tr>
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
 <div class="col-lg-6 col-md-12">

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg">
      <h4 class="custom-supplier-list">@if(!array_key_exists('accounts_transactions', $global_terminologies))Accounts Transactions @else {{$global_terminologies['accounts_transactions']}} @endif</h4>
    </div>


    <!-- <div class="col-xl-2 col-lg-3 col-md-4 ml-lg-auto ml-md-auto" ></div>
    <div class="col-xl-2 col-lg-3 col-md-4"></div> -->
  </div>

<div class="row d-flex align-items-center left-right-padding form-row filters_div">
    <div class="col-2">
        <div class="form-group">
          <label>Select Supplier</label>
          <select class="form-control selecting-supplier-t state-tags">
              <option value="">-- Suppliers --</option>
              @foreach($suppliers as $supplier)
              <option value="{{$supplier->id}}">{{$supplier->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>
      <div class="col-2">

        <div class="form-group mr-2">
          <label>Reference#</label>
          <input type="text" class="form-control reference_accT" id="order_no_t" placeholder="Payment Ref#" name="text2">
        </div>

      </div>

    <div class="col-2">

      <div class="form-group">
        <label>From Date</label>
         <input type="text" placeholder="From Date" name="from_date_t" class="form-control font-weight-bold from_date_accT" id="from_date_t" autocomplete="off">
      </div>

    </div>

    <div class="col-2">

      <div class="form-group">
        <label>To Date</label>
        <input type="text" placeholder="To Date" name="to_date_t" class="form-control font-weight-bold to_date_accT" id="to_date_t" autocomplete="off">
      </div>

    </div>
    <div class="col-2 p-0" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date_t" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date_t common-icons mr-4" title="Apply Dates" id="apply_filter">
          <img src="{{asset('public/icons/apply_filters.png')}}" width="27px">
        </span>

        <span class="common-icons reset-btn-t" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>
<!--     <div class="col">
        <div class="form-group">
        <label></label>
        <input type="button" value="Reset" class="btn recived-button reset-btn-t">
        </div>
      </div> -->
  </div>
<div class="entriesbg bg-white custompadding customborder">

<div class="table-responsive ">
  <table class="table entriestable table-bordered text-center invoices-table ">
        <thead>

            <tr>
              <th>Action</th>
               <th>PO#</th>
                <th>Reference<br>Name</th>
                <th>Purchase Total</th>
                <th>Total Paid</th>
                <th>@if(!array_key_exists('difference', $global_terminologies))Difference @else {{$global_terminologies['difference']}} @endif</th>
                <th>Payment<br>method</th>
                <th>Payment Reference</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</div>
</div>

<div class="row">

<div class="col-lg-10 col-md-9 pl-3 pr-0">
  <h4>Transaction History</h4>
</div>

<div class="col-lg-1 col-md-1"></div>

<div class="col-lg-1 col-md-2"></div>

</div>
<div class="bg-white pt-3 pl-2 pb-3 pr-2">
  <!-- product history table -->
  <div class="product-update-history pt-2 pb-3 pr-3 pl-3">

    <table class="table-transaction-history entriestable table table-bordered text-center" style="width: 100%;font-size: 12px;">
      <thead>
        <tr>
          <th>User  </th>
          <th>Date/time </th>
          <th>PO#</th>
          <th>Column</th>
          <th>Old Value</th>
          <th>New Value</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- product history table -->
</div>

 <div class="row">

<div class="col-lg-10 col-md-9 pl-3 pr-0">
  <h4>Deleted Transaction</h4>
</div>

<div class="col-lg-1 col-md-1"></div>

<div class="col-lg-1 col-md-2"></div>

</div>
<div class="bg-white pt-3 pl-2 pb-3 pr-2">
  <div class="product-update-history pt-2 pb-3 pr-3 pl-3">

    <table class="table-transaction-del-history entriestable table table-bordered text-center" style="width: 100%;font-size: 12px;">
      <thead>
        <tr>
          <th>User  </th>
          <th>Date/time </th>
          <th>PO#</th>
          <th>Payment<br>reference</th>
          <th>Total Paid</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

</div>

</div>
 <!-- main content end here -->
</div><!-- main content end here -->



<form id="export_account_payable_form">
  @csrf
  <input type="hidden" name="dosortby" id="dosortby">
  <input type="hidden" name="select_supplier" id="select_supplier">
  <input type="hidden" name="select_po" id="select_po">
  <input type="hidden" name="select_by_value" id="select_by_value">
  <input type="hidden" name="from_date" id="from_date">
  <input type="hidden" name="to_date" id="to_date">
  <input type="hidden" name="type" id="type" value="account_payable">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" id="search_value" name="search_value" value="search_value">
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
    $value = $('#sortbyvalue').val(order);
    $value2 = $('#sortbyparam').val(column_name);

    $('.account-payable-purchase-orders').DataTable().ajax.reload();

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



$(document).on('click','#export_s_p_r',function(){

$('#select_supplier').val($('.selecting-supplier option:selected').val());
$("#select_po").val($('.order_no').val());
$("#select_by_value").val($('.select-value-input').val());
var date = $('#from_date').val();
$("#from_date").val(date);
var date = $('#to_date').val();
$("#to_date").val(date);
$("#search_value").val($('.account-payable-purchase-orders').DataTable().search());


var form=$('#export_account_payable_form');

var form_data = form.serialize();
$.ajaxSetup({
headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
}
});
$.ajax({
method:"post",
url:"{{route('export-account-payable-table')}}",
data:form_data,
success:function(data){
  if(data.status==1)
  {
    $('.export-alert-success').addClass('d-none');
    $('.export-alert').removeClass('d-none');
    checkStatusForAccountPayableTable();
  }
  else if(data.status==2)
  {
    $('.export-alert-another-user').removeClass('d-none');
    $('.export-alert').addClass('d-none');
    checkStatusForAccountPayableTable();
  }
},
});
});

function checkStatusForAccountPayableTable()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-account-payable-table')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForAccountPayableTable();
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




  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date_t").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date_t").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
    // format: "mm",
    // viewMode: "months",
    // minViewMode: "months",
    // autoHide: true
  });

  $("#oi_received_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

$(document).ready(function(){

  $('.table-transaction-history').DataTable({
    "sPaginationType": "listbox",
  processing: false,
//   "language": {
//   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:false,
  "lengthChange": false,
  serverSide: true,
  "scrollX": true,
  "bPaginate": false,
  "bInfo":false,
  lengthMenu: [ 100, 200, 300, 400],
  "columnDefs": [
    { className: "dt-body-left", "targets": [] },
    { className: "dt-body-right", "targets": [] },
  ],
         ajax: {
          beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            url:"{!! route('get-po-transaction-history') !!}",
            },
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'user_name', name: 'user_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'po_id', name: 'po_id' },
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
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

    $('.table-transaction-del-history').DataTable({
      "sPaginationType": "listbox",
  processing: false,
//   "language": {
//   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  searching:false,
  "lengthChange": false,
  serverSide: true,
  "scrollX": true,
  "bPaginate": false,
  "bInfo":false,
  lengthMenu: [ 100, 200, 300, 400],
  "columnDefs": [
    { className: "dt-body-left", "targets": [] },
    { className: "dt-body-right", "targets": [] },
  ],
         ajax: {
          beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            url:"{!! route('get-po-transaction-del-history') !!}",
            },
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'user_name', name: 'user_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'po_id', name: 'po_id' },
            { data: 'payment_reference_no', name: 'payment_reference_no' },
            { data: 'total_paid', name: 'total_paid' },

              ],
              initComplete: function () {
              // Enable THEAD scroll bars
              $('.dataTables_scrollHead').css('overflow', 'auto');

              // Sync THEAD scrolling with TBODY
              $('.dataTables_scrollHead').on('scroll', function () {
                  $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
              });

      },
      drawCallback: function ( settings ) {
          $('#loader_modal').modal('hide');
      }
    });

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
  });

  $(document).on('keypress keyup focusout',".fieldFocus",function(e) {
  var po_id = $(this).data('id');
  // return false;

      if (e.keyCode === 27 && $(this).hasClass('active')) {
          var fieldvalue = $(this).prev().data('fieldvalue');
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.val(fieldvalue);
          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');
        }

        if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active')){
          var str = $(this).val();
          if($(this).val().length < 1 ||  !str.replace(/\s/g, '').length)
          {
            swal({ html:true, title:'Alert !!!', text:'<b>Please Enter At Least 1 number !!!</b>'});
            return false;
          }
          else
          {
            $(this).removeClass('active');
            var fieldvalue = $(this).prev().data('fieldvalue');
            var new_value = $(this).val();
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
              saveCustData(thisPointer,thisPointer.attr('name'), thisPointer.val(),po_id);
            }
          }
        }

  });

  $(document).on('keypress focusout',".fieldFocusTotalReceived",function(e) {
  var po_id = $(this).data('id');
  // return false;
    if (e.keyCode === 13 ) {
      var thisPointer = $(this);
      saveCustData(thisPointer,thisPointer.attr('name'), thisPointer.val(),po_id);

    }
  });

    function saveCustData(thisPointer,field_name,field_value,po_id){


      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",

        url: "{{ url('/save-account-payable-data') }}",
        dataType: 'json',
        data: 'cust_detail_id='+po_id+'&'+field_name+'='+encodeURIComponent(field_value),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $('#loader_modal').modal('hide');
          if(data.success == true){
             toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              $('.account-payable-purchase-orders').DataTable().ajax.reload();
          }

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

  $(document).on('keypress keyup focusout', '.fieldFocusTran', function(e){

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
    var tId = $(this).parents('tr').attr('id');

    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }

    if($(this).val() !== '' && $(this).hasClass('active'))
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
      saveTransactionData(tId, $(this).attr('name'), $(this).val(), old_value);
    }
   }
  });

  function saveTransactionData(trans_id,field_name,field_value,old_value){
      console.log(field_name+' '+field_value+''+trans_id+' '+old_value);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('/save-account-payable-tran-data') }}",
        dataType: 'json',
        data: 'trans_id='+trans_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
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
            toastr.success('Success!', 'Information updated successfully..',{"positionClass": "toast-bottom-right"});
            $('.account-payable-purchase-orders').DataTable().ajax.reload();
            $('.invoices-table').DataTable().ajax.reload();
            $('.supplier-orders-table').DataTable().ajax.reload();
            $('.table-transaction-history').DataTable().ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

    $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

    var table = $('.account-payable-purchase-orders').DataTable({
      "sPaginationType": "listbox",
       processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      "searching": true,
      "lengthMenu": [50,100,200,300,400],
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      dom: 'Blfrtip',
      "columnDefs": [
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9 ] },
        { className: "dt-body-right", "targets": [10,11,12,13,14,16] }
      ],
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
            $("#loader_modal").modal('show');
          },
          url:"{!! route('get-account-payable-purchase-orders') !!}",
          data: function(data) {
            data.selecting_supplier = $('.selecting-supplier option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.order_no = $('#order_no').val(),data.order_total = $('#search_by_val').val(),
             data.sortbyparam = column_name,
            data.sortbyvalue = order
            },
        },
      columns: [
        {  data: 'target_ship_date', name:'target_ship_date'},
        { data: 'po_id', name: 'po_id' },
        { data: 'created_at', name: 'created_at' },
        { data: 'invoice_date', name: 'invoice_date' },
        { data: 'supplier', name: 'supplier' },
        { data: 'invoice_number', name: 'invoice_number' },
        { data: 'memo', name: 'memo' },
        { data: 'supplier_currency', name: 'supplier_currency' },
        { data: 'payment_terms', name: 'payment_terms' },
        { data: 'payment_due_date', name: 'payment_due_date' },
        { data: 'po_total', name: 'po_total' },
        { data: 'po_total_with_vat', name: 'po_total_with_vat' },

        { data: 'po_exchange_rate', name: 'po_exchange_rate' },
        { data: 'po_total_in_thb', name: 'po_total_in_thb' },
        { data: 'amount_paid', name: 'amount_paid' },
        { data: 'exchange_rate', name: 'exchange_rate' },
        { data: 'total_received', name: 'total_received' },
        { data: 'difference', name: 'difference' },
        // { data: 'total_received', name: 'total_received' },
        // { data: 'payment_method', name: 'payment_method' },
        // { data: 'received_date', name: 'received_date' },
        // { data: 'payment_reference_no', name: 'payment_reference_no' },
        // { data: 'total_received', name: 'total_received' },
        { data: 'actions', name: 'actions' },
      ],

      initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      },
        drawCallback: function ( settings ) {
          $('#loader_modal').modal('hide');
        }
    });

    table.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'account_payable',column_id:column},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
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

    $('.invoices-table').DataTable({
      "sPaginationType": "listbox",
       processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      "searching": false,
      "lengthMenu": [50,100,200,300,400],
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,

      ajax:{
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
          url:"{!! route('get-invoices-for-payable') !!}",
          data: function(data) { data.from_date = $('#from_date_t').val(),data.to_date = $('#to_date_t').val(), data.selecting_supplier = $('.selecting-supplier-t option:selected').val(),data.order_no = $('#order_no_t').val()},
        },

      columns: [
        { data: 'action', name: 'action' },
        { data: 'ref_no', name: 'ref_no' },
        { data: 'supplier_company', name: 'supplier_company' },
        // { data: 'reference_number', name: 'reference_number' },
        { data: 'invoice_total', name: 'invoice_total' },
        { data: 'total_paid', name: 'total_paid' },
        { data: 'difference', name: 'difference' },
        { data: 'payment_type', name: 'payment_type' },
        { data: 'payment_reference_no', name: 'payment_reference_no' },
        { data: 'received_date', name: 'received_date' },
      ],

      initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      },
        drawCallback: function ( settings ) {
          $('#loader_modal').modal('hide');
        }
    });

    $('.supplier-orders-table').DataTable({
      "sPaginationType": "listbox",
       processing: false,
        // "language": {
        //     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      "searching": false,
      "lengthMenu": [50,100,200,300,400],
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,

      ajax:{
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
          url:"{!! route('get-supplier-orders') !!}",
          data: function(data) { data.selecting_supplier = $('.selecting-supplier-accR option:selected').val()},
        },

      columns: [
        { data: 'supplier_company', name: 'supplier_company' },
        { data: 'total', name: 'total' },
        { data: 'total_due', name: 'total_due' },
        { data: 'total_not_due', name: 'total_not_due' },
        { data: 'action', name: 'action' },
      ],

      initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      },

      footerCallback: function ( row, data, start, end, display ) {
            var api = this.api(), data;

            // converting to interger to find total
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };

            // computing column Total the complete result
            var total = api
                .column( 1 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

           var total_due = api
                    .column( 2 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );

           var total_not_due = api
                    .column( 3 )
                    .data()
                    .reduce( function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0 );




            // Update footer by showing the total with the reference of the column index
      $( api.column( 0 ).footer() ).html('Total');
      $( api.column( 1 ).footer() ).html(total.toFixed(2));
      $( api.column( 2 ).footer() ).html(total_due.toFixed(2));
      $( api.column( 3 ).footer() ).html(total_not_due.toFixed(2));
        },
        drawCallback: function ( settings ) {
          $('#loader_modal').modal('hide');
        }
    });

    $(document).on('keyup' , '#search_by_val' ,function(e){
      if(event.which == 13)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.account-payable-purchase-orders').DataTable().ajax.reload();
      }
    });

    $(document).on('click' , '.received_amount' , function(e){
      e.preventDefault();

      var s_payment_methods = "";
      var selected_oi = [];
      var total_received = [];
      $("input.check1:checked").each(function() {
        selected_oi.push($(this).val());
      });

      if(selected_oi.length == 0)
      {
        toastr.error('Error!', 'Please Choose PO(s) First !!!',{"positionClass": "toast-bottom-right"});
        return false;
      }
      var found = false;

      selected_oi.forEach(function(order_id) {
        var total_rec = $('#po_total_received_'+order_id).val();
        total_received.push(total_rec);
        if(total_rec == '' || total_rec == 0)
        {
          toastr.error('Error!', 'Enter Payment Exchange Rate.',{"positionClass": "toast-bottom-right"});
          found = true;
          return false;
        }
      });

      if(found == true)
      {
        return false;
      }

      var received_date = $('#oi_received_date').val();
      var payment_reference_no = $('#oi_payment_reference_no').val();
      var payment_method = $('.oi_payment_method').val();
      if(received_date == '')
      {
        toastr.error('Error!', 'Select Payment Date.',{"positionClass": "toast-bottom-right"});
        return false;
      }
      if(payment_reference_no == '')
      {
        toastr.error('Error!', 'Enter Payment Reference Number.',{"positionClass": "toast-bottom-right"});
        return false;
      }
      if(payment_method == '')
      {
        toastr.error('Error!', 'Select Payment Method.',{"positionClass": "toast-bottom-right"});
        return false;
      }

      swal({
        title: "Are You Sure?",
        text: "You want to paid amount!!!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
      },
      function (isConfirm) {
        if (isConfirm)
        {
          $.ajax({
          method: "get",
          url: "{{ route('get-purchase-order-received-amount') }}",
          dataType: 'json',
          data: {po_id:selected_oi,total_received:total_received,payment_method:payment_method,received_date:received_date,payment_reference_no:payment_reference_no},
          beforeSend: function(){
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
              if(data.already_paid != '')
              {
                toastr.warning('Warning!', data.already_paid+' are already paid !!!',{"positionClass": "toast-bottom-right"});
              }
              else
              {
                toastr.success('Success!', 'Amount paid successfully.',{"positionClass": "toast-bottom-right"});
              }
              $("#oi_received_date").val("");
              $("#oi_payment_reference_no").val("");
              $("#paid_amount").val("");
              $(".oi_payment_method").val("");

              $('.account-payable-purchase-orders').DataTable().ajax.reload();
              $('.invoices-table').DataTable().ajax.reload();
              $('.supplier-orders-table').DataTable().ajax.reload();

            }
            $('#loader_modal').modal('hide');

            if(data.payment_reference_no == 'exists')
            {
              toastr.warning('Alert!', 'Payment Refernece number already used. Please use another one.',{"positionClass": "toast-bottom-right"});
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

    $('.selecting-supplier').on('change', function(e){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.account-payable-purchase-orders').DataTable().ajax.reload();
    });

    $('#from_date').change(function() {
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $("#loader_modal").modal('show');
      // $('.account-payable-purchase-orders').DataTable().ajax.reload();
    });

    $('#to_date').change(function() {
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $("#loader_modal").modal('show');
      // $('.account-payable-purchase-orders').DataTable().ajax.reload();
    });

    $(document).on('click','.apply_date',function(){
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.account-payable-purchase-orders').DataTable().ajax.reload();
  });

    $(document).on('keyup' , '#order_no' ,function(e){
      if(event.which == 13)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.account-payable-purchase-orders').DataTable().ajax.reload();
      }
    });

    $('#from_date_t').change(function() {
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $("#loader_modal").modal('show');
      // $('.invoices-table').DataTable().ajax.reload();
    });

    $('#to_date_t').change(function() {
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $("#loader_modal").modal('show');
      // $('.invoices-table').DataTable().ajax.reload();
    });

    $(document).on('click','.apply_date_t',function(){
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.invoices-table').DataTable().ajax.reload();
  });

    $('.selecting-supplier-t').on('change', function(e){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.invoices-table').DataTable().ajax.reload();
    });


    $(document).on('keyup' , '#order_no_t' ,function(e){
      if(event.which == 13)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.invoices-table').DataTable().ajax.reload();
      }
    });

    $('.selecting-supplier-accR').on('change', function(e){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.supplier-orders-table').DataTable().ajax.reload();
    });


    $('.reset-btn').on('click',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('#order_no').val('');
      $('#search_by_val').val('');
      $('.selecting-supplier').val('');
      $('#from_date').val('');
      $('#to_date').val('');
      $(".state-tags").select2("", "");

      $('.account-payable-purchase-orders').DataTable().ajax.reload();
    });

    $('.reset-btn-t').on('click',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('#order_no_t').val('');
      $('.selecting-supplier-t').val('');
      $('#from_date_t').val('');
      $('#to_date_t').val('');
      $(".state-tags").select2("", "");

      $('.invoices-table').DataTable().ajax.reload();
    });

    $('.reset-btn-accR').on('click',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.selecting-supplier-accR').val('');
      $(".state-tags").select2("", "");

      $('.supplier-orders-table').DataTable().ajax.reload();
    });

    $(document).on('click','.check1',function(){
      var supplier = $('.selecting-supplier').val();
      if(supplier == '')
      {
        toastr.warning('Alert!', 'Please Select Supplier First..',{"positionClass": "toast-bottom-right"});
        return false;
      }

      var selected_oi = [];
      var total_received = [];
      var total = 0;
      $("input.check1:checked").each(function() {
        selected_oi.push($(this).val());
      });

      selected_oi.forEach(function(order_id) {
        var total_rec = $('#po_total_received_'+order_id).val();
        total_received.push(total_rec);
        total += parseFloat(total_rec);
      });
      $('#paid_amount').val(total.toFixed(2));
    });

    $(document).on('click','.delete_po_transaction',function(e){
        e.preventDefault();
        var payment_id = $(this).data('id');
        // alert(payment_id);
        swal({
          title: "Are You Sure?",
          text: "You want to delete Transaction!!!",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes",
          cancelButtonText: "Cancel",
          closeOnConfirm: true,
          closeOnCancel: false
          },
        function (isConfirm) {
          if (isConfirm)
          {
            $.ajax({
            method: "post",
            url: "{{ route('delete_po_transaction') }}",
            dataType: 'json',
            data: {"_token": "{{ csrf_token() }}",id:payment_id},
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
                  toastr.success('Success!', 'Transaction deleted successfully.',{"positionClass": "toast-bottom-right"});
                  $('.account-payable-purchase-orders').DataTable().ajax.reload();
                  $('.invoices-table').DataTable().ajax.reload();
                  $('.supplier-orders-table').DataTable().ajax.reload();
                  $('.table-transaction-del-history').DataTable().ajax.reload();

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



});
</script>
@stop
