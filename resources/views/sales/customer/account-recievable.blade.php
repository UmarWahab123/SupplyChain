@extends('sales.layouts.layout')

@section('title','Account Recievable | Supply Chain')

@section('content')
<style type="text/css">
  a
  {
    cursor:pointer;
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
          <li class="breadcrumb-item active">Account Receivable</li>
      </ol>
  </div>
</div>
@if((@$config->server == 'lucilla' && @auth()->user()->role_id ) || @$config->server != 'lucilla' )
<div class="row mb-3">
  <div class="col-lg col-md-4 pb-md-3 ">
    <div class="bg-white box1 pt-4 pb-4">
      <div class="d-flex align-items-center justify-content-center">
        <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
        <div class="title pl-2">
          <h6 class="mb-0 headings-color number-size">{{number_format($open_invoices, 2, '.', ',')}}</h6>
          <span class="span-color">Open Invoices </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg col-md-4 pb-md-3 ">
    <div class="bg-white box2 pt-4 pb-4 dashboard-boxes-shadow">
      <div class="d-flex align-items-center justify-content-center">
        <img src="{{asset('public/site/assets/sales/img/img1.jpg')}}" class="img-fluid">
        <div class="title pl-2">
          <h6 class="mb-0 headings-color number-size">{{number_format($payments, 2, '.', ',')}}</h6>
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
          <h6 class="mb-0 headings-color number-size"> {{number_format($account_receivable, 2, '.', ',')}}</h6>
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
          <h6 class="mb-0 headings-color number-size"> {{$delete_transaction}}</h6>
          <span class="span-color">Delete Transaction</span>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
<!-- Right Content Start Here -->
@if(Auth::user()->role_id != 9)
<div class="row d-flex align-items-center left-right-padding mb-2 form-row">
  <h4>Account Receivable</h4>
</div>
<div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg-3 col-md-4">
      <h4 class="custom-customer-list">Open Invoices</h4>
    </div>


    <!-- <div class="col-xl-2 col-lg-3 col-md-4 ml-lg-auto ml-md-auto" ></div> -->
    <div class="col-lg-9 col-md-9">
      <div class="pull-right">
          <span class="export-btn-recievable vertical-icons" id="export_s_p_r" title="Create New Export">
            <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
          </span>
      </div>
    </div>
  </div>

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row filters_div">
      <div class="col-lg col-md-6">
        <div class="form-group">
          <label>Select Customer</label>
          <select class="form-control selecting-customer state-tags">
              <option value="">-- Customers --</option>
              @foreach($customers as $customer)
              <option value="{{$customer->id}}">{{$customer->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>

      <div class="col-lg col-md-4" id="sale-person">
        <label>Sale Person</label>
        <div class="form-group">
          <select class="form-control selecting-sale">
              <option value="">-- Sale person --</option>
             @foreach($sales_persons as $person)
              <option value="{{$person->id}}">{{$person->name}}</option>
              @endforeach
          </select>
        </div>
      </div>

      <div class="col-lg col-md-6">

        <div class="form-group mr-2">
          <label>Invoice #</label>
          <input type="text" class="form-control invoice_hash" id="order_no" placeholder="Invoice#" name="text2">
        </div>

      </div>

      <div class="col-lg col-md-6">

        <div class="form-group mr-2">
          <label>Search</label>
          <input type="number" class="form-control search_value" id="search_by_val" placeholder="Search by Value" name="text2">
        </div>

      </div>

  </div>
  <div class="row d-flex align-items-center left-right-padding mb-2 form-row filters_div">
      <div class="col-lg-3 col-md-5">

        <div class="form-group">
          <label>From Date ( Invoice Date )</label>
           <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date_open_invoices" id="from_date" autocomplete="off">
        </div>

      </div>
      <div class="col-lg-3 col-md-5">

        <div class="form-group">
          <label>To Date ( Invoice Date )</label>
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date_open_invoices" id="to_date" autocomplete="off">
        </div>

      </div>

      <div class="col-lg-2 col-md-2 p-0" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Dates" id="apply_filter">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>

      <div class="col-lg-2 col-md-6">

        <div class="form-group">
          <label>Receipt/Billing Date</label>
          <input type="text" placeholder="Receipt/Billing Date" name="receipt_date" class="form-control font-weight-bold receipt_date" id="receipt_date" autocomplete="off">
        </div>

      </div>

      <div class="col-lg-2">
        <div class="form-group">
        <label></label>
        <!-- <button type="button" value="Reset" class="btn recived-button reset-btn">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button> -->
        <div class="input-group-append ml-3">
        <span class="common-icons reset-btn" id="reset-btn" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
        </div>
      </div>

      {{-- <div class="col-lg-2 " id="reset-2">
        <div class="form-group">
          <label></label>
            <button id="export_s_p_r" class="btn recived-button " >Export</button>
        </div>
      </div> --}}




  </div>
  <div class="row mb-2">
    <div class="col-2">
        <!-- <button class="btn button-st export-btn-recievable" id="export_s_p_r" >Create New Export</button> -->
        <div class="input-group-append ml-3">

        </div>
      </div>

      @php
        if($file_name==null)
        $className='d-none';
        else
        $className='';
      @endphp
     <!--  <div class="col-6 download-btn-div d-flex justify-content-left align-items-center {{$className}}">
        <a download href="{{asset('storage/app/'.@$file_name->file_name)}}" class="download-btn common-icons" title="Download">
          <a class="download-btn common-icons" href="{{ url('get-download-xslx/'.@$file_name->file_name)}}" target="_blank" title="Download">
          <span class="">
                <img src="{{asset('public/icons/download.png')}}" width="27px">
            </span> </a>
        @if($file_name != null)
        <span> <i> <b>&nbsp;&nbsp;&nbsp;Last Downloaded On : {{$file_name != null ? $file_name->last_downloaded : ''}} </b></i></span>
        @endif
      </div> -->
  </div>
<div class="right-content pt-0">

<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->


 <div class="col-lg-12 col-md-12 ">
  <div class="alert alert-primary export-alert-recievable d-none"  role="alert">
    <i class="  fa fa-spinner fa-spin"></i>
    <b> Export file is being prepared! Please wait.. </b>
  </div>

  <div class="alert alert-success export-alert-success-recievable d-none"  role="alert">
  <i class=" fa fa-check "></i>
    <b>Export file is ready to download.
      <a class="exp_download" href="{{ url('get-download-xslx/'.@$file_name->file_name)}}" target="_blank" id=""><u>Click Here</u></a>
    </b>
  </div>
<div class="entriesbg bg-white custompadding customborder">

  <div class="row d-flex align-items-center left-right-padding mb-1 form-row">

      <div class="col">
        <span>  Received Date </span>

          <input type="text" placeholder="Received Date" name="oi_received_date" class="form-control font-weight-bold" id="oi_received_date" autocomplete="off">

      </div>
      <div class="col">
        <span> Payment Reference </span>
          <input type="text" name="oi_payment_reference_no" id="oi_payment_reference_no" class="form-control payment_reference_input" @if($ref_no_config && $ref_no_config->display_prefrences == 1) disabled @endif>
      </div>

      <div class="col">
        <span> Total Paid </span>
          <input type="text" name="paid_amount" id="paid_amount" readonly disabled="true" class="form-control">
      </div>

      <div class="col">
        <span>Payment Methods</span>
            <select class="font-weight-bold  oi_payment_method form-control" name="oi_payment_method" required="true" style="height: 27px;width: 100%">
                <option value="">Payment Method</option>
                @foreach($payment_methods as $payment_method)
                <option value="{{$payment_method->id}}">{{@$payment_method->title}}</option>
                @endforeach
            </select>
      </div>

      <div class="col">
        <span>Remarks</span>
        <input type="text" name="remarks" id="remarks" class='form-control' placeholder="Remarks">
      </div>
      <div class="col">
        <label></label>
        <a class="received_amount btn recived-button btn-sm">Received Amount</a>
      </div>
      <div class="col">
        <label></label>
       <a class="btn recived-button btn-sm view_orders">{{$global_terminologies['billing']}}</a></div>

       <div class="col">
        <label></label>
       <a class="btn recived-button btn-sm view_orders_receipt">{{$global_terminologies['receipt']}} </a>
     </div>


  </div>




<div class="table-responsive ">
  <table class="table entriestable table-bordered text-center open-invoice-receivable" >
        <thead>
            <tr>
                <th>Invoice<br>Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
                <th>Delivery<br>Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="del_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="del_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
                <th>Invoice #
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>
                <th>Sales<br>Person
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sales_person">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sales_person">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

                <th>{{$global_terminologies['company_name']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="company_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="company_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>

                <th>Reference<br>Name
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="ref_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="ref_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>

                <th>Inv.#
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="inv_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="inv_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>

                <th>VAT Inv (-1)</th>
                <th>VAT </th>
                <th>Inv.#
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
                </th>

                <th>Non VAT <br>Inv (-2) </th>
                <!-- <th>Shipping</th> -->
                <!-- <th>Discount</th> -->
                <th>Sub<br>Total
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sub_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sub_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

                <th>Order<br>Total
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>

                <!-- <th>Payment<br>Method</th> -->
                <th>Total<br>Amount<br>Paid
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_amount_paid">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_amount_paid">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>

                <th>Total<br>Amount<br>Due
                <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_amount_due">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_amount_due">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>

                <!-- <th>Surcharge </th> -->
                <th>{{$global_terminologies['payment_due_date']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="paymemt_due_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="paymemt_due_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
             </th>

                <!-- <th>Total Sent</th> -->
                <!-- <th>Received<br>Date</th> -->
                <!-- <th>Payment<br>Reference#</th> -->
                <th style="width: 10%;">Total<br>Received (VAT)</th>
                <th style="width: 10%;">Total<br>Received (Non VAT)</th>
                <!-- <th>Action</th> -->
                <th>Action</th>
               <!--  <th></th>
                <th></th>
                <th></th>
                <th></th> -->

            </tr>
        </thead>
        <tfoot>
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
        <tbody></tbody>


    </table>

</div>
</div>


</div>

</div>
@endif






<div class="row mb-3">
<!-- left Side Start -->
  <div class="col-lg-6 col-md-12 mb-md-3">

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg">
      <h4 class="custom-customer-list">Account Receivables</h4>
    </div>

  </div>

  <div class="row d-flex align-items-center left-right-padding form-row filters_div">
    {{-- <div class="col-lg col-md-6">
    </div> --}}

    <div class="col">
        <div class="form-group">
          <label>Select Customer</label>
          <select class="form-control selecting-customer-accR state-tags">
              <option value="">-- Customers --</option>
              @foreach($customers as $customer)
              <option value="{{$customer->id}}">{{$customer->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>

      <div class="col">
        <div class="form-group">
        <label></label>
        <!-- <input type="button" value="Reset" class="btn recived-button reset-btn-accR"> -->
        <div class="input-group-append pull-left">
        <span class="common-icons reset-btn-accR" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
        </div>
      </div>
      </div>
  </div>
  <div class="entriesbg bg-white custompadding customborder">

<div class="table-responsive">
    <table  class="table entriestable table-bordered text-center customer-orders-table">
      <thead>
        <tr>
        <th>{{$global_terminologies['company_name']}}</th>
        <th style="width: 10%;" >Reference<br>Name</th>
          <th>Total</th>
          <th>Total Due</th>
          <th>Total Not Due </th>
          <th>Overdue</th>
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
          <th></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Billing Notes History -->
<div class="row d-flex align-items-center left-right-padding mb-2 form-row mt-4">
    <div class=" col-lg">
      <h4 class="custom-customer-list">Billing Notes History</h4>
    </div>
</div>
<div class="row d-flex align-items-center left-right-padding form-row filters_div">
    <div class="col">
        <div class="form-group">
            <label>Select Customer</label>
            <select class="form-control selecting-customer-billing-note state-tags">
                <option value="">-- Customers --</option>
                @foreach($customers as $customer)
                <option value="{{$customer->id}}">{{$customer->reference_name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col">
        <div class="form-group">
            <label>Invoice #</label>
            <input type="text" class="form-control billing_invoice" placeholder="Invoice #">
        </div>
    </div>
    <div class="col">
        <div class="form-group">
        <div class="input-group-append pull-left">
        <span class="common-icons reset-btn-billing-note" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
            </span>
        </div>
        </div>
    </div>
</div>
<div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
        <table  class="table entriestable table-bordered text-center billing_notes_table">
          <thead>
            <tr>
            <th style="width: 10%;" >Reference<br>Name</th>
            <th>Invoice #</th>
            <th>Billing<br>Reference #</th>
            <th>Action </th>
            </tr>
          </thead>
        </table>
    </div>
</div>
<!-- Ends Here -->

</div>
 <div class="col-lg-6 col-md-12">

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg">
      <h4 class="custom-customer-list">{{$global_terminologies['accounts_transactions']}} </h4>
    </div>


    <div class="col-xl-2 col-lg-3 col-md-4 ml-lg-auto ml-md-auto" ></div>
    <div class="col-xl-2 col-lg-3 col-md-4">
      <div class="pull-right">
        <span class="export-btn vertical-icons" title="Create New Export Account Transactions" id="export_a_t">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
      </div>
    </div>
  </div>

<div class="row d-flex align-items-center left-right-padding form-row filters_div">
    <div class="col-3">
        <div class="form-group">
          <label>Select Customer</label>
          <select class="form-control selecting-customer-t state-tags">
              <option value="">-- Customers --</option>
              @foreach($customers as $customer)
              <option value="{{$customer->id}}">{{$customer->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>
      <div class="col-2">

        <div class="form-group mr-2">
          <label>Invoice#</label>
          <input type="text" class="form-control invoice_hash_accT" id="order_no_t" placeholder="Invoice#" name="text2">
        </div>

      </div>

       <div class="col-2">

        <div class="form-group mr-2">
          <label>Reference#</label>
          <input type="text" class="form-control reference_hash_accT" id="reference" placeholder="Reference#" name="reference">
        </div>

      </div>

    <div class="col-4">
        <div class="form-group">
        <label style="visibility: hidden;">Reset</label>
      <!--   <input type="button" value="Reset" class="btn recived-button reset-btn-t" style="
    margin-top: 7px;
    font-size: 11px;"> -->
          <div class="input-group-append">
          <span class="common-icons reset-btn-t" id="reset-btn" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
          </div>
        </div>
      </div>
  </div>
  <div class="row d-flex align-items-center left-right-padding form-row filters_div">
     <div class="col-3">

      <div class="form-group">
        <label>From Date</label>
          <input type="text" placeholder="From Date" name="from_date_t" class="form-control font-weight-bold from_date_accT" id="from_date_t" autocomplete="off">
      </div>

    </div>

    <div class="col-3">

      <div class="form-group">
        <label>To Date</label>
        {{--<input type="date" class="form-control" name="to_date_t" id="to_date_t">--}}
        <input type="text" placeholder="To Date" name="to_date_t" class="form-control font-weight-bold to_date_accT" id="to_date_t" autocomplete="off">
      </div>

    </div>
    <div class="col-6" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append">
        <!-- <button class="btn recived-button apply_date_t" type="button" style="width: auto;">Apply Dates</button>   -->
        <span class="apply_date_t common-icons mr-4" title="Apply Dates" id="apply_filter">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>

<!--      <div class="col-3" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append">
        <button id="export_a_t" class="btn recived-button export-btn">Export</button>
      </div>
      </div>
    </div> -->

  </div>


  <div class="alert alert-primary export-alert d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is being prepared! Please wait.. </b>
      </div>
          <div class="alert alert-success export-alert-success d-none"  role="alert">
           <i class=" fa fa-check "></i>
          <b>Export file is ready to download
          <!-- <a download href="{{ url('storage/app/Account-Transaction-Export.xlsx')}}"><u>Click Here</u></a> -->
          <a class="exp_download" href="{{ url('get-download-xslx','Account-Transaction-Export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
          </b>
         </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>

<div class="entriesbg bg-white custompadding customborder">

<div class="table-responsive ">
  <table class="table entriestable table-bordered text-center invoices-table ">
        <thead>

            <tr>
                <th>Action</th>
                <th>Payment<br>reference</th>
                <th>Received Date</th>
                <th>Invoice#</th>
                <th>Reference<br>Name</th>
                <th>{{$global_terminologies['company_name']}}</th>
                <th>Delivery<br>Date</th>
                <th>Invoice Total</th>
                <th>Total Paid <br> Vat</th>
                <th>Total Paid <br> Non Vat</th>
                <th>Total Paid</th>
                <th>{{$global_terminologies['difference']}}</th>
                <th>Payment<br>method</th>
                <th>Sales Person</th>
                <th>Remarks</th>
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
          <th>Invoice#</th>
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

    <table class="table-transaction-del-history entriestable table table-bordered text-center del-trans-history-table" style="width: 100%;font-size: 12px;">
      <thead>
        <tr>
          <th>User  </th>
          <th>Date/time </th>
          <th>Invoice#</th>
          <th>Payment<br>reference</th>
          <th>Total Paid</th>
          <th>Reason</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- <div class="entriesbg bg-white custompadding customborder">

<div class="table-responsive ">
  <table class="table entriestable table-bordered text-center invoices-table ">
        <thead>

            <tr>
              <th>Invoice#</th>
              <th>Reference <br> Name</th>
              <th>Reference <br> Number</th>
                <th>Invoice Total</th>
                <th>Total Paid</th>
                <th>Payment<br>method</th>
                <th>Reference #</th>
                <th>Received Date</th>
            </tr>
        </thead>
    </table>
</div>
</div> -->

</div>

</div>
 <!-- main content end here -->
</div><!-- main content end here -->

<!-- export pdf form starts -->
      <form class="export-account-received-form" method="post" action="{{route('export-account-received-pdf')}}">
        @csrf
       <input type="hidden" name="payment_id" class="payment_id">
       <input type="hidden" name="orders" class="orders">
      </form>
    <!-- export pdf form ends -->

    <form class="export-orders-receipt-pdf" method="post" action="{{route('export-orders-receipt-pdf')}}">
        @csrf
       <input type="hidden" name="customer_id" class="customer_id">
       <input type="hidden" name="total_received" class="total_received">
       <input type="hidden" name="orders_a" class="orders_a">
      </form>

      <!-- export pdf form starts -->
      <form class="export-payment-receipt" method="post" action="{{url('sales/export-payment-receipt-pdf')}}">
        @csrf
       <input type="hidden" name="payment_id" class="payment_id">
      </form>
    <!-- export pdf form ends -->

    <div class="modal" id="deleteTransactionReason">
    <div class="modal-dialog modal-md modal-dialog-centered parcelpop">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>

        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Reason</h3>
          <div class="mt-2">

          {!! Form::open(['method' => 'POST', 'class' => 'delete-order-transaction']) !!}

            <div class="form-row">
              <div class="form-group col input-group">
                  {!! Form::text('delete_reason', $value = null, ['class' => 'font-weight-bold form-control-lg form-control delete_reason', 'placeholder' => 'Reason' , 'required'=>'required']) !!}
              </div>
              <input type="hidden" name="dot_id" id="dot_id" value="">
            </div>

            <div class="form-submit">
              <input type="button" value="Delete" class="btn btn-bg dot-delete-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div>
        </div>
      </div>
    </div>
  </div>

<div class="modal" id="loader_modal2" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
        <div id="msg"></div>
      </div>
    </div>
  </div>
</div>

<form id="export_account_receivable">
  @csrf
  <input type="hidden" name="selecting_customerx" id="selecting_customerx">
  <input type="hidden" name="selecting_salex" id="selecting_salex">
  <input type="hidden" name="order_nox" id="order_nox">
  <input type="hidden" name="from_datex" id="from_datex">
  <input type="hidden" name="to_datex" id="to_datex">
  <input type="hidden" name="search_by_valx" id="search_by_valx">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
</form>

<!-- <form id="export_account_tr" method="post" action="{{route('export-account-transaction')}}"> -->
<form id="export_account_tr" method="post">
  @csrf
  <input type="hidden" name="customer_account_tr" id="customer_account_tr">
  <input type="hidden" name="invoice_account_tr" id="invoice_account_tr">
  <input type="hidden" name="reference_account_tr" id="reference_account_tr">
  <input type="hidden" name="from_account_tr_date" id="from_account_tr_date">
  <input type="hidden" name="to_account_tr_date" id="to_account_tr_date">


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

    $('#sortbyvalue').val(order);
    $('#sortbyparam').val(column_name);

    $('.open-invoice-receivable').DataTable().ajax.reload();

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

  //  $(document).on('click','.download_transaction',function(){
  //   var id = $(this).data('id');
  //   alert(id);
  //   $('.payment_id').val(id);
  //   $('.export-payment-receipt')[0].submit();
  // });

     $(document).on('click','.download_transaction',function(){
    var id = $(this).data('id');
    $('.payment_id').val(id);
    // $('.export-payment-receipt')[0].submit();
var payment_id = id;
     var url = "{{url('sales/export-payment-receipt-pdf')}}"+"/"+payment_id;
          window.open(url, 'Orders Receipt Print', 'width=1200,height=600,scrollbars=yes');
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#receipt_date").datepicker({
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
  $(document).on('click','.delete_order_transaction',function(e){
      $('.delete-order-transaction')[0].reset();
      $('#dot_id').val($(this).data('id'));
      $('#deleteTransactionReason').modal('show');

  });

  $('.delete-order-transaction').submit(function() {
    return false;
  });

    $(document).on('click','.dot-delete-btn',function(e){
        e.preventDefault();
        var reason = $('.delete_reason').val();
        if(reason == "")
        {
          toastr.error('Alert!', 'Reason cannot be empty.',{"positionClass": "toast-bottom-right"});
          return false;

        }
        $('#deleteTransactionReason').modal('hide');
        var payment_id = $(this).data('id');
        // alert(payment_id);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            method: "post",
            url: "{{ route('delete_order_transaction') }}",
            dataType: 'json',
            data: $('.delete-order-transaction').serialize(),
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
                  $('.open-invoice-receivable').DataTable().ajax.reload();
                  $('.invoices-table').DataTable().ajax.reload();
                  $('.customer-orders-table').DataTable().ajax.reload();
                  $('.table-transaction-del-history').DataTable().ajax.reload();

               }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
            });
    });

    $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

    // $('#export_s_p_r').on('click',function(){
    //   $("#export_account_receivable").submit();
    // });

     $(document).on('click','#export_s_p_r',function(e){
      e.preventDefault();
      var count=0;
      if($('#from_datex').val()!='' && $('#to_datex').val()!='')
      {
        count=1;
      }
      if(count==0)
      {
        // toastr.info('Info!', 'Please select/apply date filter first!!!' ,{"positionClass": "toast-bottom-right"});
        // return;
      }
      var form_data=$('#export_account_receivable').serialize();
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
        $.ajax({
            method:"get",
            url:"{{route('export-ar-invoice-table')}}",
            data:form_data,
            beforeSend:function(){

                /*$('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
                $("#loader_modal").modal('show');*/
              $('#export_s_p_r').attr('title','Please Wait...');
              $('#export_s_p_r').prop('disabled',true);

            },
            success:function(data){
                if(data.status==1)
                {
                  //swal("Wait!", "File is getting ready and will be available for download", "warning");

                    //$("#loader_modal").modal('hide');
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').removeClass('d-none');
                    $('export_s_p_r').attr('title','EXPORT is being Prepared');
                    $('export_s_p_r').prop('disabled',true);
                    $('.download-btn-div ').addClass('d-none');

                    console.log("Calling Function from first part");
                    checkStatusForReceivingExport();
                }
                else if(data.status==2)
                {
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').removeClass('d-none');
                    $('#export_s_p_r').attr('title',data.msg);
                    $('#export_s_p_r').prop('disabled',true);
                    $('.download-btn-div ').addClass('d-none');

                  checkStatusForReceivingExport();
                  //checkStatusForProducts();
                }

            },
            error: function(request, status, error){
                {{-- $("#loader_modal").modal('hide'); --}}
                $('.export-btn-recievable').attr('title','Create New Export');
                $('.export-btn-recievable').prop('disabled',false);

            }
      });

});

     $(document).ready(function(){
      $.ajax({
        method:"get",
        url:"{{route('check-status-for-first-time-account-receivable-export')}}",
        success:function(data)
        {
          if(data.status==0 || data.status==2)
          {

          }
          else
          {
            $('.export-alert-recievable').removeClass('d-none');
            $('.export-btn-recievable').attr('title','EXPORT is being Prepared');
            $('.export-btn-recievable').prop('disabled',true);
            $('.download-btn-div ').addClass('d-none');
            checkStatusForReceivingExport();
          }
        }
      });
    });
    // $('#export_a_t').on('click',function(){
    //   $("#export_account_tr").submit();
    // });


     $(document).on('click', '#export_a_t', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
      $('.export-alert-success').addClass('d-none');
      var timeout;
       $.ajax({
          url: "{{ route('export-account-transaction') }}",
          method: 'post',
          data: $('#export_account_tr').serialize(),
          beforeSend: function(){
            $('.modal').modal('hide');
            $('.export-btn').prop('disabled',true);
            // $('#loader_modal2').modal('show');
            // $('#loader_modal2').modal({
            //   backdrop: 'static',
            //   keyboard: false
            // });

            //    timeout = setTimeout(function(){
            //   var alertMsg = "<p style='color:green;''>Please be patient File is exporting .....</p>";
            //   $('#msg').html(alertMsg);
            // }, 1500);

          },
          success: function(result){
            if(result.status==1)
            {
                $('.export-alert-success').addClass('d-none');
                $('.export-alert').removeClass('d-none');
                $('.export-btn').prop('disabled',true);
              checkStatusTransactionExp();
            }

             else if(data.status==2)
                {
                $('.export-alert-another-user').removeClass('d-none');
                $('.export-alert').addClass('d-none');
                $('.export-btn').prop('disabled',true);
                  checkStatusTransactionExp();
                }

            // $('#loader_modal2').modal('hide');
            // if(result.success === true)
            // {
            //   toastr.success('Success!', 'Customer Category added successfully',{"positionClass": "toast-bottom-right"});
            //   $('.add-cust-cat-form')[0].reset();
            //   $('.table-cust-cat').DataTable().ajax.reload();
            // }


          },
          error: function (request, status, error) {
                $('#loader_modal2').modal('hide');
                $('.export-btn').prop('disabled',false);
                $('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();
            }
        });
    });

      $(document).ready(function(){
      $.ajax({
        method:"get",
        url:"{{route('check-status-for-first-time-transaction-exp')}}",
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
            checkStatusTransactionExp();
          }
        }
      });
    });

      function checkStatusTransactionExp()
   {
  $.ajax({
    method:"get",
    url:"{{route('recursive-transaction-exp-status')}}",
    success:function(data){
      if(data.status==1)
      {
        console.log("Status " +data.status);
        setTimeout(
        function(){
          console.log("Calling Function Again");
          checkStatusTransactionExp();
        }, 500);
      }
      else if(data.status == 0)
      {
         $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);

       //  $('#loader_modal2').modal('hide');
       // $('.export-alert-success').removeClass('d-none');


      }
      else if(data.status == 2)
      {
        // $('#loader_modal2').modal('hide');
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',false);
       toastr.error('error!', 'Something went wrong.',{"positionClass": "toast-bottom-right"});
          $('.invoices-table').DataTable().ajax.reload();
      }
    }
  });
}


    // $(document).on('click', '#export_a_t', function(e){
    //   e.preventDefault();
    //    $.ajax({
    //       url: "{{ route('export-account-transaction') }}",
    //       method: 'post',
    //       data: $('#export_account_tr').serialize(),
    //       beforeSend: function(){
    //         $('#loader_modal').modal({
    //           backdrop: 'static',
    //           keyboard: false
    //         });
    //         $("#loader_modal").modal('show');
    //       },
    //       success: function(result){
    //         if(result){
    //         $("#loader_modal").modal('hide');
    //         toastr.success('success!', 'file is exported.',{"positionClass": "toast-bottom-right"});
    //       }


    //       },
    //       error: function (request, status, error) {
    //             $("#loader_modal").modal('hide');

    //         }
    //     });
    // });

  function checkStatusForReceivingExport()
  {
    $.ajax({
            method:"get",
            url:"{{route('recursive-export-status-account-receivable')}}",
            success:function(data){
                  if(data.status==1)
                  {
                    console.log("Status " +data.status);
                    setTimeout(
                      function(){
                        console.log("Calling Function Again");
                        checkStatusForReceivingExport();
                      }, 5000);
                  }
                  else if(data.status==0)
                  {
                    console.log(data);
                    var href="{{asset('storage/app')}}"+"/"+data.file_name;
                    var last_downloaded = data.last_downloaded;
                    $('.export-alert-success-recievable').removeClass('d-none');
                    $('.export-alert-recievable').addClass('d-none');
                    $('.export-btn-recievable').attr('title','Create New Export');
                    $('.export-btn-recievable').prop('disabled',false);
                    $('.download-btn-div ').html('');
                    $('.download-btn-div ').append('<a class="btn button-st download-btn" download style="flex: 0.4;" href="'+href+'">Download</a><span> <i> <b>&nbsp;&nbsp;&nbsp;Last Downloaded On : '+last_downloaded+'</b></i></span>');
                    $('.download-btn-div ').removeClass('d-none');
                    $('.primary-btn').addClass('d-none');
                  }
                  else if(data.status==2)
                  {
                    $('.export-alert-success-recievable').addClass('d-none');
                    $('.export-alert-recievable').addClass('d-none');
                    $('.export-btn-recievable').attr('title','Create New Export');
                    $('.export-btn-recievable').prop('disabled',false);
                    $('.export-alert-another-user').addClass('d-none');
                    toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
                    console.log(data.exception);
                  }
              }
          });
  }

    $('.selecting-sale').on('change', function(e){
      $("#selecting_salex").val($('.selecting-sale option:selected').val());

      $('.open-invoice-receivable').DataTable().ajax.reload();

    });

    $('.table-transaction-history').DataTable({
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
            url:"{!! route('get-transaction-history') !!}",
            },
        columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'user_name', name: 'user_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'invoice_no', name: 'invoice_no' },
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

    $('.table-transaction-del-history').DataTable({
      "sPaginationType": "listbox",
      // $('.table-transaction-del-history').addClass("del_trans_history_datatable").removeClass('dataTables_scrollBody');
      processing: false,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      searching:false,
      "lengthChange": true,
      serverSide: true,
      "scrollX": true,
      "bPaginate": true,
      "bInfo":false,
      lengthMenu: [25, 50, 100, 200],
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
        url:"{!! route('get-transaction-del-history') !!}",
        },
      columns: [
        // { data: 'checkbox', name: 'checkbox' },
        { data: 'user_name', name: 'user_name' },
        { data: 'created_at', name: 'created_at' },
        { data: 'invoice_no', name: 'invoice_no' },
        { data: 'payment_reference_no', name: 'payment_reference_no' },
        { data: 'total_paid', name: 'total_paid' },
        { data: 'reason', name: 'reason' },

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



    var table2 = $('.open-invoice-receivable').DataTable({
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
      dom: 'Blfrtip',
      "columnDefs": [
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,9,14 ] },
        { className: "dt-body-right", "targets": [7,8,10,11,12,13] }
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
          url:"{!! route('get-open-invoices-for-receivable') !!}",
          data: function(data) { data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.order_no = $('#order_no').val(),data.order_total = $('#search_by_val').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),
            data.sortbyparam = column_name,
            data.sortbyvalue = order
          },
        },
      columns: [
        { data: 'invoice_date', name: 'invoice_date' },
        { data: 'delivery_date', name: 'delivery_date' },
        { data: 'ref_no', name: 'ref_no' },
        { data: 'sales_person', name: 'sales_person' },
        { data: 'customer_company', name: 'customer_company' },
        { data: 'customer_reference_name', name: 'customer_reference_name' },
        { data: 'reference_id_vat', name: 'reference_id_vat' },
        { data: 'sub_total_1', name: 'sub_total_1' },
        { data: 'vat_1', name: 'vat_1' },
        { data: 'reference_id_vat_2', name: 'reference_id_vat_2' },
        { data: 'sub_total_2', name: 'sub_total_2' },
        // { data: 'shipping', name: 'shipping' },
        // { data: 'discount', name: 'discount' },
        { data: 'sub_total_amount', name: 'sub_total_amount' },
        { data: 'invoice_total', name: 'invoice_total' },
        // { data: 'payment_method', name: 'payment_method' },
        { data: 'amount_paid', name: 'amount_paid' },
        { data: 'amount_due', name: 'amount_due' },
        { data: 'payment_due_date', name: 'payment_due_date' },
        // { data: 'received_date', name: 'received_date' },
        // { data: 'payment_reference_no', name: 'payment_reference_no' },
        { data: 'total_received', name: 'total_received' },
        { data: 'total_received_non_vat', name: 'total_received_non_vat' },
        // { data: 'actions', name: 'actions' },
        { data: 'checkbox', name: 'checkbox'},

      ],

      initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      }, drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
      footerCallback: function ( row, data, start, end, display ) {
        var api              = this.api();
        var json             = api.ajax.json();
        var total_amount     = json.total;
        var sub_total     = json.sub_t;

        sub_total     = parseFloat(sub_total).toFixed(2);
        sub_total     = sub_total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

        total_amount     = parseFloat(total_amount).toFixed(2);
        total_amount     = total_amount.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

        $( api.column( 11 ).footer() ).html(sub_total);
        $( api.column( 12 ).footer() ).html(total_amount);
      },
    });


    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'account_receivable',column_id:column},
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
          url:"{!! route('get-invoices-for-receivable') !!}",
          data: function(data) { data.from_date = $('#from_date_t').val(),data.to_date = $('#to_date_t').val(), data.selecting_customer = $('.selecting-customer-t option:selected').val(),data.order_no = $('#order_no_t').val(),data.reference = $('#reference').val()},
        },

      columns: [
        { data: 'action', name: 'action' },
        { data: 'payment_reference_no', name: 'payment_reference_no' },
        { data: 'received_date', name: 'received_date' },
        { data: 'ref_no', name: 'ref_no' },
        { data: 'customer_reference_name', name: 'customer_reference_name' },
        { data: 'customer_company', name: 'customer_company' },
        { data: 'delivery_date', name: 'delivery_date' },
        { data: 'invoice_total', name: 'invoice_total' },
        { data: 'vat_total_paid', name: 'vat_total_paid' },
        { data: 'non_vat_total_paid', name: 'non_vat_total_paid' },
        { data: 'total_paid', name: 'total_paid' },
        { data: 'difference', name: 'difference' },
        { data: 'payment_type', name: 'payment_type' },
        { data: 'sales_person', name: 'sales_person' },
        { data: 'remarks', name: 'remarks' },
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

    $('.customer-orders-table').DataTable({
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
          url:"{!! route('get-customer-orders') !!}",
          data: function(data) { data.selecting_customer = $('.selecting-customer-accR option:selected').val()},
        },

      columns: [
        { data: 'customer_company', name: 'customer_company' },
        { data: 'customer_reference_name', name: 'customer_reference_name' },
        { data: 'total', name: 'total' },
        { data: 'total_due', name: 'total_due' },
        { data: 'total_not_due', name: 'total_not_due' },
        { data: 'overdue', name: 'overdue' },
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
            .column( 2 )
            .data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );

       var total_due = api
            .column( 3 )
            .data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );

       var total_not_due = api
            .column( 4 )
            .data()
            .reduce( function (a, b) {
                return intVal(a) + intVal(b);
            }, 0 );

            // Update footer by showing the total with the reference of the column index
        $( api.column( 0 ).footer() ).html('Total');
        $( api.column( 2 ).footer() ).html(total.toFixed(2));
        $( api.column( 3 ).footer() ).html(total_due.toFixed(2));
        $( api.column( 4 ).footer() ).html(total_not_due.toFixed(2));
      },
      drawCallback: function ( settings ) {
        $('#loader_modal').modal('hide');
      }
    });

    // Billing Notes Hisory Datatable
    $('.billing_notes_table').DataTable({
      "sPaginationType": "listbox",
       processing: false,
      ordering: false,
      serverSide: true,
      "searching": false,
      "lengthMenu": [10,20,30,40,50],
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
          url:"{!! route('get-billing_notes_history') !!}",
          data: function(data) {
            data.selecting_customer = $('.selecting-customer-billing-note option:selected').val(),
            data.inv_no = $('.billing_invoice').val()
          },
        },
      columns: [
        { data: 'reference_name', name: 'reference_name' },
        { data: 'inv_no', name: 'inv_no' },
        { data: 'billing_ref_no', name: 'billing_ref_no' },
        { data: 'action', name: 'action' }
      ],
      initComplete: function () {
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
      }, drawCallback: function(){
        $('#loader_modal').modal('hide');
      }
    });
    // Ends Here

    $(document).on('click','.view_orders',function(e){
      e.preventDefault();
       var selected_oi = [];
      var total_received = [];
        $("input.check1:checked").each(function() {
          selected_oi.push($(this).val());
        });

        var receipt_date = $('.receipt_date').val();

        receipt_date = receipt_date.replace(/\//g, "_");

        if(selected_oi == ''){
            toastr.error('Error!', 'Select Orders First.',{"positionClass": "toast-bottom-right"});
        return false;
        }else{
        $('.orders').val(selected_oi);

        var orders = $('.orders').val();
        // console.log(orders);
        // return false;
         // $('.export-account-received-form')[0].submit();
         var url = "{{url('sales/export-account-received-pdf')}}"+"/"+orders+"/new_billing_note/"+receipt_date;
          window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
       }
        // console.log(selected_oi);
       setTimeout(() => {
        $('.billing_notes_table').DataTable().ajax.reload();
       }, 5000);

    })

    $(document).on('click' , '.received_amount' , function(e){
      e.preventDefault();

      var s_payment_methods = "";
      var selected_oi = [];
      var total_received = [];
        $("input.check1:checked").each(function() {
          selected_oi.push($(this).val());
        });


      selected_oi.forEach(function(order_id) {
      var total_rec = Math.abs($('#oi_total_received_'+order_id).val());
      var total_rec_non_vat = Math.abs($('#oi_total_received_non_vat_'+order_id).val());
      total_received.push(total_rec);
      total_received.push(total_rec_non_vat);
     // alert(total_rec);
      if(total_rec === '')
      {
        toastr.error('Error!', 'Enter Total Received.',{"positionClass": "toast-bottom-right"});
        return false;
      }

      });

      var received_date = $('#oi_received_date').val();
      var payment_reference_no = $('#oi_payment_reference_no').val();
      var payment_method = $('.oi_payment_method').val();


       if(received_date == '')
      {
        toastr.error('Error!', 'Select Received Date.',{"positionClass": "toast-bottom-right"});
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
      // return false;

      swal({
          title: "Are You Sure?",
          text: "You want to receive amount!!!",
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
            url: "{{ route('get-open-invoice-received-amount') }}",
            dataType: 'json',
            data: {"_token": "{{ csrf_token() }}",order_id:selected_oi,payment_method:payment_method,received_date:received_date,total_received:total_received,payment_reference_no:payment_reference_no},
            beforeSend: function(){
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(data)
            {
              // $('#loader_modal').modal('hide');
              if(data.success == true)
               {
                  toastr.success('Success!', 'Amount received successfully.',{"positionClass": "toast-bottom-right"});
                  $('.open-invoice-receivable').DataTable().ajax.reload();
                  $('.invoices-table').DataTable().ajax.reload();
                  $('.customer-orders-table').DataTable().ajax.reload();
                  // $('.invoices-table').DataTable().ajax.reload();
                  $('.open-invoice-receivable').DataTable().ajax.reload();

                  $('.payment_id').val(data.payment_id);


                   swal({
                      title: "",
                      text: "Do you want to continue to Print this receipt ",
                      type: "success",
                      showCancelButton: true,
                      confirmButtonColor: "#DD6B55",
                      confirmButtonText: "Yes",
                      cancelButtonText: "Cancel",
                      closeOnConfirm: true,
                      closeOnCancel: false
                      },
                      function (isConfirm) {
                      if(isConfirm){
                        payment_id = data.payment_id;
                        // alert(payment_id);
                        var url = "{{url('sales/export-payment-receipt-pdf')}}"+"/"+payment_id;
                        window.open(url, 'Orders Receipt Print', 'width=1200,height=600,scrollbars=yes');
                      }else {
                        $('#loader_modal').modal('hide');
                        swal("Cancelled", "", "error");
                      }
                    });

                    $('#order_no').val('');
                    $('#search_by_val').val('');
                    $('.selecting-sale').val('');
                    $('#order_nox').val('');
                    $('#search_by_valx').val('');
                    $('.selecting_salex').val('');
                    $('.selecting_customerx').val('');
                    $('#customer_account_tr').val('');
                    $('#invoice_account_tr').val('');
                    $('#reference_account_tr').val('');
                    $(".state-tags").select2("", "");
                    $(".oi_payment_method").val("");
                    $("#paid_amount").val("");
                    $('.selecting-customer').val('');
                    $('#oi_payment_reference_no').val('');
                    $(".selecting-sale").select("", "");
                    $("#oi_received_date").val("");
                    $("#order_no").val("");
                    $("#search_by_val").val("");
                    $("#from_date").val("");
                    $("#to_date").val("");
                    $("#receipt_date").val("");


                }

            if(data.payment_reference_no == 'exists')
               {
                  toastr.warning('Alert!', 'Payment Refernece number already used. Please use another one.',{"positionClass": "toast-bottom-right"});
                  $('#loader_modal').modal('hide');
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

    $('.selecting-customer').on('change', function(e){
      $("#selecting_customerx").val($('.selecting-customer option:selected').val());
      $('.open-invoice-receivable').DataTable().ajax.reload();
    });

     $('.selecting-customer-t').on('change', function(e){
      $("#customer_account_tr").val($('.selecting-customer-t option:selected').val());
      $('.invoices-table').DataTable().ajax.reload();
    });

      $(document).on('keyup' , '#order_no_t' ,function(e){
      $("#invoice_account_tr").val($('#order_no_t').val());

      if(event.which == 13)
      {
        $('.invoices-table').DataTable().ajax.reload();
      }
    });

     $(document).on('keyup' , '#reference' ,function(e){
    $("#reference_account_tr").val($('#reference').val());

    if(event.which == 13)
    {
      $('.invoices-table').DataTable().ajax.reload();
    }
  });

    $('#from_date').change(function() {
      // var date = $('#from_date').val();
      // $("#from_datex").val(date);
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.open-invoice-receivable').DataTable().ajax.reload();
    });

    $('#to_date').change(function() {
      // var date = $('#to_date').val();
      // $("#to_datex").val(date);
      // $('#loader_modal').modal({
      //   backdrop: 'static',
      //   keyboard: false
      // });
      // $('#loader_modal').modal('show');
      // $('.open-invoice-receivable').DataTable().ajax.reload();
    });

    $(document).on('click','.apply_date',function(){
    var date_from = $('#from_date').val();
    $("#from_datex").val(date_from);

    var date_to = $('#to_date').val();
    $("#to_datex").val(date_to);
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      $('.open-invoice-receivable').DataTable().ajax.reload();
  });

    $(document).on('keyup' , '#order_no' ,function(e){
      $("#order_nox").val($('#order_no').val());

      if(event.which == 13)
      {
        $('.open-invoice-receivable').DataTable().ajax.reload();
      }
    });

    $(document).on('keyup' , '#search_by_val' ,function(e){
      $("#search_by_valx").val($('#search_by_val').val());

      if(event.which == 13)
      {
        $('.open-invoice-receivable').DataTable().ajax.reload();
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

      var date_from = $('#from_date_t').val();
    $("#from_account_tr_date").val(date_from);

    var date_to = $('#to_date_t').val();
    $("#to_account_tr_date").val(date_to);

    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.invoices-table').DataTable().ajax.reload();
  });



    $('.selecting-customer-t').on('change', function(e){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('.invoices-table').DataTable().ajax.reload();
    });


    // $(document).on('keyup' , '#order_no_t' ,function(e){
    //   if(event.which == 13)
    //   {
    //     $('#loader_modal').modal({
    //       backdrop: 'static',
    //       keyboard: false
    //     });
    //     $("#loader_modal").modal('show');
    //     $('.invoices-table').DataTable().ajax.reload();
    //   }
    // });


    // $(document).on('keyup' , '#reference' ,function(e){

    //   if(event.which == 13)
    //   {
    //     $('.invoices-table').DataTable().ajax.reload();
    //   }
    // });

    $('.selecting-customer-accR').on('change', function(e){
      $('.customer-orders-table').DataTable().ajax.reload();
    });


    $('.reset-btn').on('click',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('#order_no').val('');
      $('#search_by_val').val('');
      $('.selecting-sale').val('');
      $('.selecting-customer').val('');
      $('#from_date').val('');
      $('#to_date').val('');
      $('#receipt_date').val('');

      $('#order_nox').val('');
      $('#search_by_valx').val('');
      $('.selecting_salex').val('');
      $('.selecting_customerx').val('');
      $('#from_datex').val('');
      $('#to_datex').val('');
      $(".state-tags").select2("", "");



      $('.open-invoice-receivable').DataTable().ajax.reload();
    });

    $('.reset-btn-t').on('click',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      $('#order_no_t').val('');
      $('#reference').val('');
      $('.selecting-customer-t').val('');
      $('#from_date_t').val('');
      $('#customer_account_tr').val('');
      $('#invoice_account_tr').val('');
      $('#reference_account_tr').val('');
      $('#from_account_tr_date').val('');
      $('#to_account_tr_date').val('');
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
      $('.selecting-customer-accR').val('');
      $(".state-tags").select2("", "");

      $('.customer-orders-table').DataTable().ajax.reload();
    });

    //  $('.invoices-table').DataTable({
    //    processing: true,
    //     "language": {
    //         processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    //   ordering: false,
    //   serverSide: true,
    //   "searching": false,
    //   "lengthMenu": [50,100,200,300,400],
    //   scrollX:true,
    //   scrollY : '90vh',
    //   scrollCollapse: true,

    //   ajax:{
    //       url:"{!! route('get-payment-ref-invoices-for-receivable-last-five') !!}",
    //       data: function(data) { data.from_date = $('#from_date_t').val(),data.to_date = $('#to_date_t').val(), data.selecting_customer = $('.selecting-customer-t option:selected').val(),data.order_no = $('#order_no_t').val()},
    //     },

    //   columns: [
    //     { data: 'ref_no', name: 'ref_no' },
    //     { data: 'reference_name', name: 'reference_name' },
    //     { data: 'reference_number', name: 'reference_number' },
    //     { data: 'invoice_total', name: 'invoice_total' },
    //     { data: 'total_paid', name: 'total_paid' },
    //     { data: 'payment_type', name: 'payment_type' },
    //     { data: 'payment_reference_no', name: 'payment_reference_no' },
    //     { data: 'received_date', name: 'received_date' },
    //   ],

    //   initComplete: function () {
    //   $('.dataTables_scrollHead').css('overflow', 'auto');

    //   $('.dataTables_scrollHead').on('scroll', function () {
    //           $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
    //       });
    //   }
    // });


    $(document).on('click','.check1',function(){

      var customer = $('.selecting-customer').val();
      if(customer == '')
      {
        toastr.warning('Alert!', 'Please Select Customer First..',{"positionClass": "toast-bottom-right"});
        return false;
      }

      // alert();
       var selected_oi = [];
      // var total_received = [];
      var total = 0;
        $("input.check1:checked").each(function() {
          selected_oi.push($(this).val());
        });

            selected_oi.forEach(function(order_id) {
          var total_rec = $('#oi_total_received_'+order_id).val();
          var total_rec_non_vat = $('#oi_total_received_non_vat_'+order_id).val();
          // alert(total_rec);
          // total_received.push(total_rec);
          total += total_rec ? parseFloat(total_rec) : parseFloat(0);
          total += total_rec_non_vat ? parseFloat(total_rec_non_vat) : parseFloat(0);

          });

          // alert(total);
          var total_comma = total.toFixed(4);
          var nf = new Intl.NumberFormat();
          // nf.format(number);
          $('#paid_amount').val(nf.format(total_comma));
          if ($(this).is(":checked"))
          {
            @if ($ref_no_config && $ref_no_config->display_prefrences == 1)
              $.ajax({
                  method: "get",
                  url: "{{ route('get_auto_ref_no') }}",
                  dataType: 'json',
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
                    $('.payment_reference_input').val(data.payment_ref_no);
                }
              });
            @endif
          }
    });

    $(document).on("dblclick",".inputDoubleClick",function(){
      $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
        var num = $(this).next().val();
        $(this).next().focus().val('').val(num);
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
        url: "{{ route('save-transaction-data') }}",
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
            $('.open-invoice-receivable').DataTable().ajax.reload();
            $('.invoices-table').DataTable().ajax.reload();
            $('.customer-orders-table').DataTable().ajax.reload();
            $('.table-transaction-history').DataTable().ajax.reload();
          }

          else if(data.success == "vat_zero")
          {
            toastr.error('Alert!', 'Order does not have vat items..',{"positionClass": "toast-bottom-right"});
            $('.invoices-table').DataTable().ajax.reload();

          }
          else if(data.success == "non_vat_zero")
          {
            toastr.error('Alert!', 'Order does not have non vat items..',{"positionClass": "toast-bottom-right"});
            $('.invoices-table').DataTable().ajax.reload();

          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

    $(document).on('click' , '.view_orders_receipt' , function(e){
      e.preventDefault();

      var s_payment_methods = "";
      var selected_oi = [];
      var total_received = [];
        $("input.check1:checked").each(function() {
          selected_oi.push($(this).val());
        });

      var receipt_date = $('.receipt_date').val();

      receipt_date = receipt_date.replace(/\//g, "_");

      var selecting_customer = $('.selecting-customer option:selected').val();

      if(selecting_customer == '')
      {
        toastr.error('Error!', 'Select Customer.',{"positionClass": "toast-bottom-right"});
        return false;
      }

       if(selected_oi == '')
      {
        toastr.error('Error!', 'Select atleast one order.',{"positionClass": "toast-bottom-right"});
        return false;
      }

      selected_oi.forEach(function(order_id) {

       var total_rec = Math.abs($('#oi_total_received_'+order_id).val());
      var total_rec_non_vat = Math.abs($('#oi_total_received_non_vat_'+order_id).val());
      total_received.push(total_rec);
      total_received.push(total_rec_non_vat);

      });
      // return false;
     $('.orders_a').val(selected_oi);
     $('.customer_id').val(selecting_customer);
     $('.total_received').val(total_received);
     // $('.export-orders-receipt-pdf')[0].submit();

        var customer_id = selecting_customer;
        var total_received = total_received;
        var orders_a = selected_oi;
        // console.log(orders);
        // return false;
         // $('.export-account-received-form')[0].submit();
         var url = "{{url('sales/export-orders-receipt-pdf')}}"+"/"+customer_id+"/"+total_received+"/"+orders_a+"/"+receipt_date;
          window.open(url, 'Orders Receipt Print', 'width=1200,height=600,scrollbars=yes');
    });

    $(document).on('keydown', 'input[pattern]', function(e){
      var input = $(this);
      var oldVal = input.val();
      var regex = new RegExp(input.attr('pattern'), 'g');

      setTimeout(function(){
        var newVal = input.val();
        if(!regex.test(newVal)){
          input.val(oldVal);
        }
      }, 0);
    });
    @if (session('errorMsg'))
      swal("{{ session('errorMsg') }}");
      @php
       Session()->forget('errorMsg');
      @endphp
    @endif
});

//Billing Note View
$(document).on('click','.btn_view_billing_note',function(e){
    var orders = $(this).data('order_ids');
    var ref_no = $(this).data('ref_no');
    var receipt_date = $('.receipt_date').val();

    receipt_date = receipt_date.replace(/\//g, "_");
    var url = "{{url('sales/export-account-received-pdf')}}"+"/"+orders+"/"+ref_no+"/"+receipt_date;
    window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
})

//Billing Note reset Button
$(document).on('click','.reset-btn-billing-note',function(){
    $('.billing_invoice').val('');
    $('.selecting-customer-billing-note').val('').trigger('change');
    $('.billing_notes_table').DataTable().ajax.reload();
})

//Billing Note Customer filter
$(document).on('change','.selecting-customer-billing-note',function(){
    $('.billing_notes_table').DataTable().ajax.reload();
})

//Billing Note Invoice # filter
$(document).on('keyup', '.billing_invoice',function(e){
    if (e.keyCode == 13) {
        $('.billing_notes_table').DataTable().ajax.reload();
    }
})

//Billing Note Delete Button
$(document).on('click', '.btn_delete_billing_note',function(e){
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
    var id = $(this).data('id');
    swal({
        title: "",
        text: "Do you want to Delete the Billing Note Record?",
        type: "success",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
        function (isConfirm) {
        if(isConfirm){
            $.ajax({
                method: "post",
                url: "{{ route('delete-billing-note-history') }}",
                dataType: 'json',
                data: 'id='+id,
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
                        toastr.success('Success!', 'Billing Note Deleted successfully..',{"positionClass": "toast-bottom-right"});
                        $('.billing_notes_table').DataTable().ajax.reload();
                    }
                },
                error: function(request, status, error){
                    $("#loader_modal").modal('hide');
                }
            });
        }else {
            $('#loader_modal').modal('hide');
            swal("Cancelled", "", "error");
        }
    });
})


</script>
@stop
