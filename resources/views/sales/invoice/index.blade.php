@extends('sales.layouts.layout')

@section('title','Invoice Products | Sales')

@section('content')
<?php
  use Carbon\Carbon;
?>
<style type="text/css">

div.dt-button-collection button.dt-button, div.dt-button-collection div.dt-button, div.dt-button-collection a.dt-button {
  margin-right: 5px;
}

.supplier_ref {
    width: 12%;
    word-break: break-all;
}

.pf {
    width: 12%;
}

.supplier {
    width: 18%;
}

.description {
    width: 40%;
}

.p_type{
  width: 10%;
}

.p_winery{
  width: 12%;
}

.p_notes{
  width: 10%;
}

.rsv {
    width: 10%;
}

.pStock {
    width: 10%;
}

.aStock {
    width: 10%;
}

.sIcon {
    width: 20px;
}

/*search styoling up*/

.invalid-feedback {
  font-size: 100%;
}

.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}
.dataTables_scrollBody > table > thead > tr {
  visibility: collapse;
  height: 0px !important;
}
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
  font-weight: bold;
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
.select2-container {
  width: 100% !important;
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
          <li class="breadcrumb-item active">Draft Quotation</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<form class="export-quot-form" method="post" action="{{url('sales/export-quot-to-pdf/'.$id)}}">
  @csrf
  <input type="hidden" name="quo_id_for_pdf" id="quo_id_for_pdf" value="{{$id}}">
  <input type="hidden" name="with_vat" value="" id="with_vat">
</form>

<form class="export-quot-form-for-proforma" method="post" action="{{url('sales/export-proforma-to-pdf/'.$id)}}">
  @csrf
  <input type="hidden" name="quo_id_for_pdf" id="quo_id_for_pdf" value="{{$id}}">
</form>

<form method="post" class="mb-2 draft_quotation_save_form" enctype='multipart/form-data'>
<input type="hidden" name="copy_and_update" class="copy_and_update">
<input type="hidden" name="user_id" id="user_id" @if($order->user_id != null) value="{{$order->user_id}}" @endif>
<div class="row mb-3 headings-color">
  <div class="col-md-8 title-col">
    <h5 class="maintitle text-uppercase fontbold mb-0">Draft Quotations # <span class="c-ref-id">{{$id }}</span></h5>
  </div>
  <div class="col-md-4 title-col">
    <!-- <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</h5> -->
    <div class="row">
      <div class="col-6">
        <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</h5>
      </div>
      <div class="col-6 mb-4">
        <div class="pull-right">
        <a onclick="backFunctionality()" class="d-none">
          <!-- <button type="button" class="btn text-uppercase purch-btn mr-2 headings-color btn-color">back</button> -->
          <span class="vertical-icons" title="Back">
            <img src="{{asset('public/icons/back.png')}}" width="27px">
          </span>
        </a>

        <span class="vertical-icons mr-4" title="Download Sample File" id="example_export_btn">
          <img src="{{asset('public/icons/sample_export_icon.png')}}" width="27px">
        </span>

        <span class="vertical-icons export_btn" title="Export">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
        </div>
      </div>
    </div>
  </div>

  <!-- New Design Starts Here  -->
<div class="col-lg-12 col-md-12">
<div class="row">

<div class="col-lg-8 col-md-8">
  <!-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo"> -->
  <div class="d-flex align-items-center mb-0">
    <div>
      @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . @$company_info->logo))
      <img src="{{asset('public/uploads/logo/'.@$company_info->logo)}}" class="img-fluid" style="height: 80px;" align="big-qummy">
      @else
      <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="height: 80px;" align="big-qummy">
      @endif
      <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p></div>
  </div>
  <p class="mb-1">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
  <p class="mb-1"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}  <em class="fa fa-envelope"></em>  {{@$order->user->user_name}}</p>
  <br>

  @if(@$showRadioButtons == 1)
  <input type="radio" id="vat" name="is_vat" value="0" {{$order->is_vat == 0 ? 'checked' : ''}}>
  <label for="vat">Vat</label><br>
  <input type="radio" id="non_vat" name="is_vat" value="1" {{$order->is_vat == 1 ? 'checked' : ''}}>
  <label for="nonvat">Non-Vat</label>

  {{--<ul class="list-unstyled manual_ref_div d-none" style="display: flex;">
    <li class="pt-2 fontbold" style="width: 100px;">Manual Ref#:</li>
    <span class="pt-2 inputDoubleClick" data-fieldvalue="{{$order->manual_ref_no}}">
      @if($order->manual_ref_no != null)
        <p style="font-weight: normal !important;">{{$order->manual_ref_no}}</p>
      @else
        <p style="font-weight: normal !important;">Manual Ref#. Here</p>
      @endif
    </span>
    <input type="text" class="mt-2 d-none manual_ref_no fieldFocus" name="manual_ref_no" id="manual_ref_no" value="{{$order->manual_ref_no}}">
  </ul>--}}
  @endif

</div>
<div class="col-lg-4 col-md-4">
<div>
    <span><i class="fa fa-edit edit_customer"></i></span>
  @if($order->customer_id != '')
  <div class="customer-addresses">
    <div class="header">
    <div class="d-flex align-items-center mb-0">
    @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" class="img-fluid" style="width: 40px;height: 40px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 80px;height: 80px;" align="big-qummy">
    @endif
    <div class="pl-2 comp-name" id="unique" data-customer-id="{{$order->customer->id}}"><p><a href="{{url('sales/get-customer-detail/'.@$order->customer->id)}}" target="_blank">{{$order->customer->reference_name}}</a></p></div>
    </div>
    </div>
    <div class="body">
     <p class="edit-functionality"><input type="hidden" value="{{@$order->billing_address->id}}">
@if(@$order->customer->getbilling->count() > 1)
      <i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i>
      @endif
     @if(@$order->billing_address->billing_address !== null) {{@$order->billing_address->billing_address}} @endif
       @if(@$order->billing_address->getcountry->name !== null) {{ @$order->billing_address->getcountry->name }}, @endif @if(@$order->billing_address->getstate->name !== null) {{ @$order->billing_address->getstate->name }}, @endif @if(@$order->billing_address->city !== null) {{ @$order->billing_address->billing_city }}, @endif @if(@$order->billing_address->billing_zip !== null) {{ @$order->billing_address->billing_zip }} @endif</p>
    <ul class="d-flex list-unstyled">
      <li><i class="fa fa-phone pr-2"></i>{{@$order->billing_address->billing_phone}}</li>
      <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{@$order->billing_address->billing_email}}</li>
    </ul>

    <ul class="d-flex list-unstyled">
      <li><b>Tax ID:</b> @if(@$order->billing_address->tax_id !== null) {{ @$order->billing_address->tax_id }} @endif</li>
    </ul>
    </div>
    <div class="selected_address"></div>
  </div>
  @else
  <div class="customer-addresses">
    <div class="header">

    </div>
    <div class="body">

    </div>
    <div class="selected_address"></div>
  </div>
  @endif
  <div class="update_customer {{$dropdownClass}}">
     @if(Auth::user()->role_id == 3)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($customers->count() > 0)
       @foreach($customers as $customer)
       @if($order->customer_id == $customer->id)
        <option value="{{ $customer->id }}" selected="true"> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
       @else
       <option value="{{ $customer->id }}"> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endif
      @endforeach
      @endif
      <!-- <option value="new">Add New</option>  -->
    </select>
    @endif
    @if(Auth::user()->role_id == 4)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($sales_coordinator_customers->count() > 0)
       @foreach($sales_coordinator_customers as $customer)

       @if($order->customer_id == $customer->id)
        <option value="{{ $customer->id }}" selected="true"> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
       @else
       <option value="{{ $customer->id }}"> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endif

      @endforeach
      @endif
    </select>
    @endif

       @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($admin_customers->count() > 0)
       @foreach($admin_customers as $customer)

       @if($order->customer_id == $customer->id)
        <option value="{{ $customer->id }}" selected="true"> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
       @else
       <option value="{{ $customer->id }}"> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endif

      @endforeach
      @endif
    </select>
    @endif
  </div>
</div>

  @php
    $delivery_request_date = $order->delivery_request_date != null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : "";
  @endphp
  <div>
  <ul class="d-flex list-unstyled mb-0">
    <li id="delivery_date_id" class="pt-2 fontbold" style="width: 180px;">@if(!array_key_exists('delivery_request_date', $global_terminologies))Delivery Request Date: @else {{$global_terminologies['delivery_request_date']}} @endif</li>
    <span class="pl-4 pt-2 inputDoubleClick" id="delivery_request_date_span" data-fieldvalue="{{$delivery_request_date}}">
      @if($order->delivery_request_date != null)
      {{Carbon::parse($order->delivery_request_date)->format('d/m/Y')}}
      @else
      <p>Delivery request Date Here</p>
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none delivery_request_date" name="delivery_request_date" id="delivery_request_date" value="{{$delivery_request_date}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li id="payment_terms_id" class=" fontbold" style="width: 180px;">Payment Terms:</li>
    <span class="pl-4 inputDoubleClick" id="sup_payment_term">
      @if(@$order->payment_terms_id != null)
      {{@$order->paymentTerm->title}}
      @else
      Select Term Here
      @endif
    </span>
    <select class="form-control payment_terms_id d-none mb-2 select-common" name="payment_terms_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Term</option>';
      @foreach ($payment_term as $pm)
        @if($order->payment_terms_id == $pm->id)
            <option selected value="{{$pm->id}}">{{$pm->title}}</option>
        @else
            <option value="{{$pm->id}}">{{$pm->title}}</option>
        @endif
      @endforeach
    </select>
  </ul>
  @php
    $payment_due_date = $order->payment_due_date != null ? Carbon::parse($order->payment_due_date)->format('d/m/Y') : "";
  @endphp
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 180px;">@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif:</li>
    <span class="pl-4 payment_due_date_term">
      @if($order->payment_due_date != null)
      {{Carbon::parse($order->payment_due_date)->format('d/m/Y')}}
      @else
      --
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none payment_due_date" name="payment_due_date" id="payment_due_date" value="{{$payment_due_date}}">
  </ul>
  @php
    $target_ship_date = $order->target_ship_date != null ? Carbon::parse($order->target_ship_date)->format('d/m/Y') : "";
  @endphp
  @if($targetShipDate['target_ship_date']==1)
    <ul class="d-flex list-unstyled mb-0">
    <li id="target_ship_date_id" class="pt-2 fontbold" style="width: 180px;">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif:</li>
    <span class="pl-4 pt-2 inputDoubleClick" id="target_ship_date_span" data-fieldvalue="{{$target_ship_date}}">
      @if($order->target_ship_date != null)
      {{Carbon::parse($order->target_ship_date)->format('d/m/Y')}}
      @else
      <p>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif Here</p>
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none target_ship_date" name="target_ship_date" id="target_ship_date" value="{{$target_ship_date}}">
  </ul>
  @endif

  <ul class="d-flex list-unstyled">
    <li id="ref_id" class="pt-2 fontbold" style="width: 180px;">Ref. PO #:</li>
    <span class="pl-4 pt-2 inputDoubleClick" data-fieldvalue="{{$order->memo}}">
      @if($order->memo != null)
      {{$order->memo}}
      @else
      <p class="m-0">Ref. PO # Here</p>
      @endif
    </span>
<!--     <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$order->memo}}"> -->

     <textarea class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" rows="4" style="resize: none;width: 100%">{{@$order->memo}}</textarea>
  </ul>

    <ul class="d-flex mb-0 list-unstyled mb-2">
    <li id="order_wearhouse_id" class=" fontbold" style="width: 180px;">Order Warehouse:</li>
    <span class="pl-4 inputDoubleClick" id="from_warehouse_id">
      @if(@$order->from_warehouse_id != null)
      {{@$order->from_warehouse->warehouse_title}}
      @else
      Select Warehouse Here
      @endif
    </span>
    <select class="form-control from_warehouse_id d-none mb-2 select-common" name="from_warehouse_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Warehouse</option>';
      @foreach ($warehouses as $war)
        @if($order->from_warehouse_id == $war->id)
            <option selected value="{{$war->id}}">{{$war->warehouse_title}}</option>
        @else
            <option value="{{$war->id}}">{{$war->warehouse_title}}</option>
        @endif
      @endforeach
    </select>
  </ul>
  <ul class="d-flex list-unstyled">
    <li id="choose_bank_id" class="pt-2 fontbold" style="width: 180px;">Choose Bank For Prints:</li>
    <div style="width: 40%; margin-left: 25px">
      <select class="form-control company-banks state-tags mb-2" name="company-banks" id="company_banks_select">
        <option disabled value="">Choose Bank</option>
        @foreach ($company_banks as $bank)
          <option value="{{$bank->getBanks->id}}">{{$bank->getBanks->title}}</option>
        @endforeach
      </select>
    </div>
  </ul>

  <div class=" @if(array_key_exists(7, $print_prefrences) && $print_prefrences[7]['status'] != '1') d-none @endif">
   <ul class="d-flex list-unstyled">
    <li id="choose_sales_person_id" class="pt-2 fontbold" style="width: 180px;">Choose Sales Person:</li>
    <div style="width: 40%; margin-left: 25px">
      <select class="form-control sales_person state-tags mb-2" name="sales_person" id="sales_person_select">
      <option disabled selected value="">Choose Sales Person</option>
      @if($order->customer_id != null)
      <optgroup label = "Primary Sale Person">
        <option @if($order->user_id == $sales_person->primary_sale_id) selected @endif value = "{{$sales_person->primary_sale_id}}">{{$sales_person->primary_sale_person != null?$sales_person->primary_sale_person->name:''}}</option>
      </optgroup>
      @if($secondary_sales->count() != 0)
          <optgroup label = "Secondary Sales Person">;
          @foreach ($secondary_sales as $secondary)
            <option @if($order->user_id == $secondary->user_id) selected @endif value = "{{$secondary->user_id}}">{{$secondary->secondarySalesPersons->name}}</option>
          @endforeach
        </optgroup>
      @endif
      @endif
    </select>
    </div>
  </ul>
  </div>

</div>
</div>
<div class="col-lg-12 col-md-12 text-uppercase fontbold">
  <a onclick="history.go(-1)"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">back</button></a>
  <a href="#"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button></a>
  <a href="#"><button type="button" class="btn text-uppercase purch-btn headings-color btn-color d-none">print</button></a>

  <div class="pull-left mt-4">
      <a href="javascript:void(0);">
      <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf without_vat">View</button>

    </a>

    <input type="hidden" name="show_discount_input" id="show_discount_input" value="1">

    <a href="javascript:void(0);">
      <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf-inc-vat with_vat">View (inc VAT)</button>
    </a>

      <!-- @if($showDiscount == 1)
      <a href="javascript:void(0)">
        <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color"><input type="checkbox" name="show_price" id="show_price" checked="true" style="vertical-align: inherit;scale"> &nbsp;Show Discount</button>
      </a>
      @endif -->

  </div>

  <div class="pull-right mt-4">

    <span class="vertical-icons" title="Bulk Import" data-toggle="modal" data-target="#import-modal-quotation">
        <img src="{{asset('public/icons/bulk_import.png')}}" width="27px">
      </span>

    <!-- <button type="button" class="btn text-uppercase purch-btn headings-color btn-color export_btn mr-3">Export</button> -->
    <span class="vertical-icons draft_quotation_copy_btn" title="Copy & Update">
      <img src="{{asset('public/icons/copy.png')}}" width="27px">
    </span>

    <span class="vertical-icons download-documents" data-id="{{$order->id}}" title="Upload Document" data-toggle="modal" data-target="#uploadDocument">
      <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
    </span>
  <!--   <button type="button" class="btn text-uppercase purch-btn headings-color btn-color d-none" data-toggle="modal" data-target="#customerAttachments">upload document<i class="pl-1 fa fa-arrow-up"></i></button>
    <a href="javascript:void(0)" data-id="{{$order->id}}" class="download-documents">
      <button type="button" class="btn text-uppercase purch-btn headings-color btn-color" data-toggle="modal" data-target="#uploadDocument">upload document<i class="pl-1 fa fa-arrow-up"></i></button>
    </a> -->

    </div>
</div>
</div>
</div>
  <!-- new design ends here -->
</div>

<div class="row entriestable-row mt-3">
  <div class="col-12">
    <!-- Excel file alert divs -->
        <div class="alert alert-primary excel-alert d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> Excel file is being prepared! Please wait.. </b>
        </div>
        <div class="alert alert-success excel-alert-success d-none"  role="alert">
        <i class=" fa fa-check "></i>

        <b>Excel file is ready to download.
        <a class="exp_download" href="{{ url('get-download-xslx','Draft Quotation Export'.$id.'.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
        </div>
        <!-- ends here -->
    <div class="entriesbg bg-white custompadding customborder table-responsive">
      <table class="table entriestable table-bordered table-ordered-products text-center">
        <thead>
          <tr>
            <th @if(in_array(0,$hidden_columns_by_admin)) class="noVis" @endif>Action</th>
            <th @if(in_array(1,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['our_reference_number']}}
            <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="2" data-column_name="reference_code">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="2" data-column_name="reference_code">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(2,$hidden_columns_by_admin)) class="noVis" @endif>HS Code</th>
            <th @if(in_array(3,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['product_description']}}
            <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="3" data-column_name="short_desc">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="3" data-column_name="short_desc">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(4,$hidden_columns_by_admin)) class="noVis" @endif>Note</th>
            <th @if(in_array(5,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['category']}} </th>
            <th @if(in_array(6,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['type']}}
            <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="4" data-column_name="type_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc"  data-index="4" data-column_name="type_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(7,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['brand']}}
            <span class="arrow_up sorting_filter_table" data-ord="asc"  data-index="5" data-column_name="brand">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="5" data-column_name="brand">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(8,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['temprature_c']}}</th>
            <th @if(in_array(9,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['supply_from']}}
            <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="1" data-column_name="supply_from">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="1" data-column_name="supply_from">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <!-- <th>Sale Unit</th> -->
            <th @if(in_array(10,$hidden_columns_by_admin)) class="noVis" @endif>Available <br>{{$global_terminologies['qty']}}</th>
            <th @if(in_array(11,$hidden_columns_by_admin)) class="noVis" @endif>PO QTY</th>
            <th @if(in_array(12,$hidden_columns_by_admin)) class="noVis" @endif>PO No</th>
            <th @if(in_array(13,$hidden_columns_by_admin)) class="noVis" @endif>Customer <br> Last <br> Price</th>
            <th @if(in_array(14,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['qty']}}<br>Ordered</th>
            <th @if(in_array(15,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['qty']}} Sent</th>
            <th @if(in_array(16,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['pieces']}}<br>Ordered</th>
            <th @if(in_array(17,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['pieces']}} Sent</th>
            <th @if(in_array(18,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['reference_price']}} </th>
            <th @if(in_array(19,$hidden_columns_by_admin)) class="noVis" @endif>*{{$global_terminologies['default_price_type']}}</th>
            <th @if(in_array(20,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['default_price_type_wo_vat']}}</th>
            <th @if(in_array(21,$hidden_columns_by_admin)) class="noVis" @endif>Price Date</th>
            <th @if(in_array(22,$hidden_columns_by_admin)) class="noVis" @endif>Discount</th>
            <th @if(in_array(23,$hidden_columns_by_admin)) class="noVis" @endif>Unit Price <br>(After Discount)</th>
            <th @if(in_array(24,$hidden_columns_by_admin)) class="noVis" @endif>VAT</th>
            <th @if(in_array(25,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['unit_price_vat']}}</th>
            <th @if(in_array(26,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['total_amount_inc_vat']}}</th>

            <th @if(in_array(27,$hidden_columns_by_admin)) class="noVis" @endif>Restaurant Price</th>
            <th @if(in_array(28,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['note_two']}}</th>
            <th @if(in_array(29,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['total_price_after_discount_without_vat']}}</th>
          </tr>
        </thead>
      </table>
      <!-- New Design Starts Here  -->
      <div class="row ml-0 mb-4">
        <div class="col-9 pad">
          <div class="col-6 pad">
            <div class="purch-border input-group custom-input-group">
              <input type="text" name="refrence_code" placeholder="Type Reference number..." data-id = "{{$id}}" class="form-control refrence_number" id="refrence_number">
            </div>
          </div>
          <!-- buttons -->
          <div class="row">
          <div class="col-lg-6 col-md-12 pad pl-2 mt-4 mb-4">
            <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="addProduct">Add Product</a>
            <!-- <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="uploadExcelbtn" type="submit">Upload Excel</a> -->
            <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="addInquiryProductbtn" type="submit">Add New Item</a>
          </div>
          <!-- buttons -->
            <!-- Notes section starts -->
           <!--  <div class="col-lg-6 col-md-6">
                  <p class="mt-3"><strong>Comment: </strong><span class="inv-note inputDoubleClick">@if($inv_note != null) {!! $inv_note->note !!} @else {{ 'Add a Comment' }} @endif</span>
                  <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                  </p>
            </div> -->

              <div class="col-lg-6 col-md-12">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                      <p><strong>Remark:</strong><span class="inv-note inputDoubleClick" style="font-weight: normal;">@if($inv_note != null)<br> {!! $inv_note->note !!} @else <br>{{ 'Add a Remark' }} @endif</span>
                    <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Remark (500 Characters)" name="comment" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                    </p>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 pr-4">
                      <p><strong>Comment To Warehouse: </strong><span class="inv-note inputDoubleClick" style="font-weight: normal;">@if($warehouse_note != null)<br> {!! $warehouse_note->note !!} @else <br>{{ 'Add a Comment' }} @endif</span>
                        <textarea autocomplete="off" name="comment_warehouse" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" name="comment" maxlength="500">{{ $warehouse_note !== null ? $warehouse_note->note : '' }}</textarea>
                        </p>
                </div>
              </div>

              </div>
            <!-- Notes section ends -->
            </div>
        </div>

        <div class="col-lg-3  col-md-3 pt-4 mt-4">
        <div class="side-table m-0">
          <table class="headings-color purch-last-left-table side-table m-0" style="width: 100%;">
            <tbody>
              <tr>
                <td class="fontbold" width="70%">Sub Total w/o Discount:</td>
                <td class="text-start sub_total_without_discount fontbold">{{number_format(floor($sub_total_without_discount*100)/100,2,'.',',')}}</td>


              </tr>
              <tr>
                <td class="text-nowrap fontbold">Discount:</td>
                <td class="fontbold text-start item_level_dicount">
                  {{ number_format(floor($item_level_dicount*100)/100, 2, '.', ',') }}
                  <!-- <input type="number" data-id="{{ $id }}" class="form-control mr-2 p-0 d-none fieldFocus input-sm" name="discount" value="{{$order->discount}}" style="width: 90%;min-height: 0;"></td> -->
              </tr>
              <tr>
                <td class="fontbold" width="50%">Sub Total:</td>
                <td class="text-start sub-total fontbold">{{ number_format($sub_total, 2, '.', ',') }}</td>
              </tr>

              <!-- <tr>
                <td class="text-nowrap fontbold">Shipping:</td>
                <td class="fontbold text-start">
                  <span class="inv-shipping mr-2 inputDoubleClick">{{ $order->shipping == '' ? 0 :number_format(floor($order->shipping*100)/100, 2, '.', ',') }}</span>
                  <input type="number" data-id="{{ $id }}" min="0" max="4000" class="form-control mr-2 p-0 d-none fieldFocus input-sm" name="shipping" value="{{$order->shipping}}" style="width: 90%;min-height: 0;"></td>
              </tr> -->
              <tr>
                <td class="text-nowrap fontbold">VAT:</td>
                <td class="fontbold text-start total-vat">{{number_format($vat, 2, '.', ',')}}</td>
              </tr>
              <tr>
                <td class="text-nowrap fontbold">Total:</td>
                <input type="hidden" name="total" value="{{$total}}" id="total">
                <td class="fontbold text-start grand-total">{{number_format($total, 2, '.', ',')}}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      </div>

    <div class="row">
    <div class="col-lg-7 col-md-4">
    <p class="mb-1 font-weight-bold">Ship To</p>
    @if($order->customer_id != '')
    <div class="customer-addresses-ship">

    <div class="ship-body">
     <p class="edit-functionality-ship">
      @if(@$order->customer->getbilling->count() > 1)
      <i class="fa fa-edit edit-address-ship" data-id="{{$order->customer_id}}"></i>
      @endif
    @if(@$order->shipping_address->billing_address !== null) {{@$order->shipping_address->billing_address}} @endif
    <br>  @if(@$order->shipping_address->getcountry->name !== null) {{ @$order->shipping_address->getcountry->name }}, @endif @if(@$order->shipping_address->getstate->name !== null) {{ @$order->shipping_address->getstate->name }}, @endif @if(@$order->shipping_address->city !== null) {{ @$order->shipping_address->billing_city }}, @endif @if(@$order->shipping_address->billing_zip !== null) {{ @$order->shipping_address->billing_zip }} @endif</p>
    <ul class="d-flex list-unstyled">
      <li><i class="fa fa-phone pr-2"></i>{{@$order->shipping_address->billing_phone != null ? @$order->shipping_address->billing_phone : '--'}}</li>
      <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{@$order->shipping_address->billing_email != null ? @$order->shipping_address->billing_email : '--'}}</li>
    </ul>
    </div>
    <div class="selected_address_ship"></div>
    </div>
    @else
    <div class="customer-addresses-ship">
      <div class="ship-header"></div>
      <div class="ship-body"></div>
      <div class="selected_address_ship"></div>
    </div>

  @endif

      </div>
    </div>
    <div class="row">
          <div class="col-lg-12 col-md-12 pl-3 pt-md-3 d-flex justify-content-end">
          <div class="">
            @csrf
            <input type="hidden" name="inv_id" value="{{ $order->id }}">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="direct_draft_invoice" class="direct-invoice">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-success draft_quotation_save_btn" id="save-and-close-btn">Save and Close</button>
            <button type="button" class="btn btn-sm pl-3 pr-3 btn-success draft_quotation_save_btn  direct_invoice" id="confirm-quotation-btn" data-ty="direct">Confirm Quotation</button>
          </form>
          <form method="post" class="d-inline-block mb-2 draft_quotation_discard_form pull-left" style="position:relative;">
            @csrf
            <input type="hidden" name="inv_id" value="{{ $order->id }}">
            <input type="hidden" name="action" value="discard">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-danger btn_discard_close" id="discard-and-close-btn" style="color: red;">Discard and Close</button>&nbsp;
          </form>
          </div>
          </div>
          </div>
        </div>


      <!-- New Design Ends Here  -->

   <div class="row mt-4">


                      <div class="col-lg-7 col-md-5">
                <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">

                 <table class="table-Quotation-history entriestable table table-bordered text-center" style="width: 100%;font-size: 12px;">
                   <thead class="sales-coordinator-thead">
                    <tr>
                      <th>User</th>
                      <th>Date/time </th>
                       <th>{{$global_terminologies['our_reference_number']}}</th>
                      <th>Column</th>
                      <th>Old Value</th>
                      <th>New Value</th>
                    </tr>
                  </thead>
                </table>
                </div>

              </div>









              <div class="col-lg-6">
                <p><strong>Comment: </strong><span class="inv-note inputDoubleClick">@if($inv_note != null) {!! $inv_note->note !!} @else {{ 'Add a Comment' }} @endif</span>
                <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" name="comment" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                </p>
              </div>
                </div>

<!-- Upload Document Modal -->
{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="uploadDocument" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">UPLOAD DOCUMENTS</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="row justify-content-end" style="width: 100%;">
      <a href="#demo" class="btn btn-primary" data-toggle="collapse" style="margin-top: 7px;">Upload Document</a>
  <div id="demo" class="collapse col-lg-10 offset-1">
    <form id="addDocumentForm2" class="addDocumentForm2" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="order_id" id="sid" value="{{$order->id}}">
          <div class="row">
                <div class="form-group col-lg-9">
                  <label class="pull-left font-weight-bold">Files <span class="text-danger">*</span></label>
                  <input class="font-weight-bold form-control-lg form-control" name="order_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
                </div>
                <div class="col-lg-3">
          <button type="submit" class="btn btn-primary save-doc-btn" style="margin-top: 2rem;" id="addDocBtn">Upload</button>

                </div>
          </div>
        </div>

      </form>
  </div>
      </div>


       <div class="fetched-files">
            <div class="d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
    </div>
  </div>
</div>
{{-- end modal code--}}

{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="customerAttachments" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">UPLOAD DOCUMENTS</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="order_id" value="{{$order->id}}">
          <div class="form-group">
            <label class="pull-left">Select Documents To Upload</label>
            <input class="font-weight-bold form-control-lg form-control" name="order_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary save-doc-btn" id="addDocBtn">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- end modal code--}}
  </div>
  </div>
</div>
<!--  Content End Here -->

<!--  Inquiry Product Modal Start Here -->
<div class="modal fade" id="addInquiryProductModal">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Add Inquiry Product</h3>
        <div class="mt-3">
        {!! Form::open(['method' => 'POST', 'class' => 'add-inquiry-product-form']) !!}
          <div class="form-group">
            {!!Form::hidden('inv_id', $id)!!}
          </div>

          <div class="form-group">
            {!! Form::text('product_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
          </div>

          <div class="form-group">
            {!! Form::textarea('description', $value = null, ['class' => 'font-weight-bold form-control-lg form-control','rows' => 3, 'placeholder' => 'Enter short description']) !!}
          </div>

          <div class="form-submit">
            <input type="submit" value="add" class="btn btn-bg save-btn">
            <input type="reset" value="close" class="btn btn-danger close-btn">
          </div>
        {!! Form::close() !!}
       </div>
      </div>
    </div>
  </div>
</div>

<!-- Upload excel file  -->
<div class="modal fade" id="uploadExcel">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Upload Excel</h3>
        <div class="mt-3">
          <form method="post" action="{{url('sales/upload-excel')}}" class="upload-excel-form" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="form-group">
             <input type="hidden" name="inv_id" value="{{$id}}">
             <input type="hidden" id="add_product_to_invoice" name="add_product_to_invoice" value="{{$id}}">
            </div>

            <div class="form-group">
              <a href="{{asset('public/site/assets/sales/quotation/Example_file.xlsx')}}" download><span class="btn btn-success" id="examplefilebtn">Download Example File</span></a>
            </div>

            <div class="form-group">
              <input type="file" name="excel" class="font-weight-bold form-control-lg form-control" >
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

<!-- Modal For Add Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Draft Quotation Note</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-draft-quot-note-form" method="post">
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                      <div class="row">
                          <div class="col-xs-12 col-md-12">
                              <div class="form-group">
                                <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                                <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255" required id="note_description"></textarea>
                              </div>
                          </div>
                          <div class="col-xs-12 col-md-12">
                            <div class="form-group">
                              {{-- <label class="mb-0">Show on Invoice</label> --}}
                              <input class="ml-2" type="hidden" name="show_note_invoice" id="show_note_invoice" style="vertical-align: middle;" checked/>
                            </div>
                          </div>
                      </div>
              </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="draft_quot_id" class="note-draft-quot-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>

<!-- Modal for Showing Notes  -->
<div class="modal" id="notes-modal" style="width:600px; margin-left: 400px;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Quotation Product Notes</h4>
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

<!--  Add Product Modal Start Here -->
<div class="modal addProductModal" id="addProductModal" style="margin-top: 150px;">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Search Product</h4>
        <p style="color: red;" align="right" class="mr-2">(Note:* Enter atleast 3 characters then press Enter)</p>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="form-group" style="margin-top: 10px; margin-bottom: 10px; position:relative;">
        <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
          <input type="text" name="prod_name" id="prod_name" value="" class="form-control form-group mb-0" autocomplete="off" placeholder="Search by Product Reference #-Default Supplier- Product Description (Press Enter)" style="padding-left:30px;">
          <input type="hidden" id="product_array">
          <input type="hidden" id="supplier_id">
          <div id="product_name_div_new"></div>
        </div>
        <div id="tags_div" class="tags_div mt-4 mb-4 row ml-2"></div>
        <div>
            <a data-customer_id="{{$order->customer_id}}" data-inv_id="{{$order->id}}" class="btn float-right add_product_to" style="background-color: #5cb85c;">Confirm</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Customer Attachments Modal -->
<div class="modal fade" id="customerAttachments" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
  <!-- Customer Attachments Modal ends here  -->
 {{--Add Billing Info--}}
<!-- Add Billing detail Modal Started -->
  <div class="modal fade" id="add_billing_detail_modal" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New Address</h4>
        </div>
        <div class="modal-body">

          <form  action="" id="add-address-form" method="POST">

          {{csrf_field()}}

          <input type="hidden" name="customer_id" id="billing_customer_id" value="{{$order->customer_id}}">
          <input type="hidden" name="quotation_id" id="quotation_id" value="{{$id}}">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="title"> Reference Name:<b style="color: red;">*</b></label>
              <input required="true" type="text" class="form-control billing"  name="billing_title">
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Billing Contact Name :</label>
              <input required="true" type="text" class="form-control"  name="billing_contact_name1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:<b style="color:red;">*</b></label>
              <input required="true" type="email" class="form-control"  name="billing_email1">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Tax ID:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control"  name="tax_id">
            </div>

            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:<b style="color: red;">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_phone">
            </div>
            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control"  name="billing_fax1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:<b style="color: red;">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:<b style="color: red;">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_zip">
            </div>


            </div>

            <div class="row">
            <div class="form-group col-md-6 d-none">
              <label for="business_country">Country:</label>
              <select required="true"  class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select Country" name="billing_country">
                <option value="217" selected disabled="true">Thailand</option>
              </select>
            </div>


            <div class="form-group col-md-6 customer-state">
              <div>
              <label for="business_state">State:<b style="color: red;">*</b></label><br>
             <!--  <select required="true" id="billing_state" name="billing_state" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select State">
                 <option>choose state</option>
              </select> -->
               <select class="form-control state-tags" name="state">
                <option selected="selected">Select State</option>
                @foreach($states as $state)
                <option value="{{$state->id}}">{{$state->name}}</option>
                @endforeach
              </select>
              </div>
            </div>


            <div class="form-group col-md-6">
              <label for="contact_city">City:<b style="color: red;">*</b></label>
              <input type="text" required="true"  class="form-control " name="billing_city">

            </div>
            </div>


          <div class="form-group col-md-12">
           <button type="button" class="btn btn-primary btn-success" id="add-address-form-btn">Submit</button>
          </div>
          </form>

        </div>

      </div>

    </div>
  </div>

<!-- Add Billing Details Modal Ended -->

 <!-- Customer Attachments Modal ends here  -->
 {{--Add Billing Info--}}
<!-- Add Billing detail Modal Started -->
  <div class="modal fade" id="add_billing_detail_modal-ship" role="dialog">
    <div class="modal-dialog">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New Address</h4>
        </div>
        <div class="modal-body">

          <form  action="" id="add-address-form-ship" method="POST">

          {{csrf_field()}}

          <input type="hidden" name="customer_id" id="billing_customer_id_bill" value="{{$order->customer_id}}">
          <input type="hidden" name="quotation_id" id="quotation_id" value="{{$id}}">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="title"> Reference Name:<b style="color:red">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_title">
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Billing Contact Name :</label>
              <input required="true" type="text" class="form-control" id="billing_contact_name" name="billing_contact_name1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:<b style="color:red">*</b></label>
              <input required="true" type="email" class="form-control"  name="billing_email1">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Tax ID:<b style="color:red">*</b></label>
              <input required="true" type="text" class="form-control"  name="tax_id">
            </div>

            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:<b style="color:red">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_phone">
            </div>
            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control"  name="billing_fax1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:<b style="color:red">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:<b style="color:red">*</b></label>
              <input required="true" type="text" class="form-control"  name="billing_zip">
            </div>


            </div>

            <div class="row">
           <div class="form-group col-md-6 d-none">
              <label for="business_country">Country:</label>
              <select required="true" id="billing_country" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select Country" name="billing_country">
                <option value="217" selected disabled="true">Thailand</option>
              </select>
            </div>


            <div class="form-group col-md-6 customer-state">
              <div>
              <label for="business_state">State:<b style="color:red">*</b></label><br>
             <!--  <select required="true" id="billing_state" name="billing_state" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select State">
                 <option>choose state</option>
              </select> -->
               <select class="form-control state-tags" name="state">
                <option selected="selected">Select State</option>
                @foreach($states as $state)
                <option value="{{$state->id}}">{{$state->name}}</option>
                @endforeach
              </select>
              </div>
            </div>


            <div class="form-group col-md-6">
              <label for="contact_city">City:<b style="color:red">*</b></label>
              <input type="text" required="true"  class="form-control " name="billing_city">

            </div>

            </div>


          <div class="form-group col-md-12">
           <button type="button" class="btn btn-primary btn-success" id="add-address-form-btn-ship">Submit</button>
          </div>
          </form>

        </div>

      </div>

    </div>
  </div>

<!-- Add Billing Details Modal Ended -->

<!-- Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Select Default Supplier(Optional)</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body inquiry-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary add_as_inquiry_product_btn">Add as Inquiry Product</button>
      </div>
    </div>
  </div>
</div>

<form id="export_draft_quotations_form">
  @csrf
  <input type="hidden" name="id" id="export_quotation_id" value="{{$id}}">
  <input type="hidden" name="table_hide_columns" value="{{$user_plus_admin_hidden_columns}}">
  <input type="hidden" name="default_sort" id="default_sort" value="id_sort">
  <input type="hidden" name="type" id="type" value="data">
  <input type="hidden" name="column_name" id="column_name" value="column_name">
</form>


<!-- Modal for Import Items  -->
<div class="modal" id="import-modal-quotation">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">BULK IMPORT</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div align="center">
            <label class="mt-2">
              <strong>Note : </strong>Please use the downloaded export file for upload only.<span class="text-danger"> Also Don't Upload Empty File.</span>
            </label>
            <form class="upload-quotation-excel-form" id="upload-quotation-excel-form" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="order_id" value="{{$order->id}}">
              <input type="hidden" name="customer_id" id="customer_id" value="{{$order->customer_id}}">
              <input type="file" class="form-control" name="product_excel" id="product_excel" accept=".xls,.xlsx" required="" style="white-space: pre-wrap;"><br>
              <button class="btn btn-info draft-quotation-upload-btn" type="submit">Upload</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

@endsection

@section('javascript')
<script type="text/javascript">

$('#import-modal-quotation').on('hidden.bs.modal', function () {
  $(this).find('form')[0].reset();
});

$('#uploadDocument').on('hidden.bs.modal', function () {
  $(this).find('form')[0].reset();
});


  /*$( document ).ready(function() {
    var is_vat = '{{$order->is_vat}}';
    if(is_vat == 1)
    {
      $('.manual_ref_div').removeClass('d-none');
    }
    else
    {
      $('.manual_ref_div').addClass('d-none');
    }
  });*/

    $(".state-tags").select2();

$(function(e){


  $("input[name='is_vat']").change(function(){
    var val = $(this).val();
    if(val == 1)
    {
      $('.manual_ref_div').removeClass('d-none');
    }
    else
    {
      $('.manual_ref_div').addClass('d-none');
    }

    var quotation_id = '{{$id}}';
    var _token = $('input[name="_token"]').val();
    swal({
      title: "Alert!",
      text: "Are you sure you want to change the VAT/NON-VAT of this Draft Quotation ?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm)
      {
        $.ajax({
          method:"POST",
          dataType:"json",
          data:{_token:_token,quotation_id:quotation_id,val:val},
          url:"{{ route('change-draft-vat') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            $('.table-ordered-products').DataTable().ajax.reload();
            toastr.success('Success!', 'Information Updated Successfully.' ,{"positionClass": "toast-bottom-right"});
            $('.sub-total').html(data.sub_total);
            $('.total-vat').html(data.vat);
            $('.grand-total').html(data.grand_total);
            $('#total').val(data.grand_total);
            $('.sub_total_without_discount').html(data.sub_total_without_discount);
            $('.item_level_dicount').html(data.item_level_dicount);
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
        if(val == 0)
        {
          $('input[name=is_vat][value=1]').prop('checked', 'checked');
        }
        else
        {
          $('input[name=is_vat][value=0]').prop('checked', 'checked');
        }
      }
     });
  });

  $("#delivery_request_date, #payment_due_date, #target_ship_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });

  $(document).on("change focusout","#delivery_request_date, #payment_due_date, #target_ship_date",function(e) {
    var quotation_id = "{{ $id }}";
    var attr_name = $(this).attr('name');

    var fieldvalue = $(this).prev().data('fieldvalue');
    // alert(fieldvalue);

    var new_value = $(this).val();
    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      $(this).removeClass('active');
      thisPointer.prev().removeClass('d-none');
      return false;
    }

    if (e.keyCode === 27 && $(this).hasClass('active') )
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      return false;
    }

    if(attr_name == 'target_ship_date')
    {
      if($(this).val() == '')
      {
        $(this).prev().html("Target Ship Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      }
      else
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }
    if(attr_name == 'delivery_request_date')
    {
      if($(this).val() == '')
      {
        // alert($(this).val());
        $(this).prev().html("Delivery request Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      }
      else
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      type: "post",
      url: "{{ route('save-quotation-discount') }}",
      dataType: 'json',
      data: 'quotation_id='+quotation_id+'&'+attr_name+'='+$(this).val(),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        if (data.success == false) {
          toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
        }
        else
        {
          if(attr_name == 'delivery_request_date')
          {
            if(data.order.payment_due_date != null)
            {
              var p_due_date = $.datepicker.formatDate("d/m/yy", new Date(data.order.payment_due_date));
              $('.payment_due_date_term').html(p_due_date);
            }
          }
          if(attr_name == 'discount'){
            $('.inv-discount').html(data.discount);
            $('.grand-total').html(data.total);
          }
          else if(attr_name == 'shipping'){
            $('.inv-shipping').html(data.shipping);
            $('.grand-total').html(data.total);
          }
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  // Customer Sorting Code Here
 var default_sort = null;
  var column_name = null;
  var index = null;

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    let sort = $(this).data('ord');
    default_sort = sort;
    column_name = $(this).data('column_name');
    $('#default_sort').val(default_sort);
    $('#column_name').val(column_name);

    index = $(this).data('index');

    $('.table-ordered-products').DataTable().order([index, default_sort]).draw();

    if($(this).data('ord') ==  'asc')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('ord') == 'desc')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });

  var table = $('.table-ordered-products').DataTable({
    "bAutoWidth": true,
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    searching: false,
    ordering: true,
    serverSide: false,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    paging: false,
    bInfo : false,
    "columnDefs": [
      { targets: [{{ $user_plus_admin_hidden_columns != null ? $user_plus_admin_hidden_columns : $hidden_by_default }}], visible: false },
      { targets: [0,1,2,3,4,5,6,7,8,9,11,10,12,13,14,15,16,17,18,19,20], orderable: false },
      { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,11] },
      { className: "dt-body-right", "targets": [10,12,13,14,15,16,17,18,19,20] }
    ],
    fixedHeader: true,
    colReorder: true,
    dom: 'lrtip',
    dom: 'Bfrtip',
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
          $("#loader_modal").modal('show');
        },
        url:"{{ url('sales/get-invoice-to-list') }}"+"/"+{{ $id }},
        data: function(data) {data.default_sort = default_sort},
      },
    columns: [
      { data: 'action', name: 'action' },
      { data: 'refrence_code', name: 'refrence_code' },
      { data: 'hs_code', name: 'hs_code' },
      { data: 'description', name: 'description' },
      { data: 'notes', name: 'notes' },
      { data: 'category_id', name: 'category_id' },
      { data: 'type_id', name: 'type_id' },
      { data: 'brand', name: 'brand' },
      { data: 'temprature', name: 'temprature' },
      { data: 'supply_from', name: 'supply_from' },
      // { data: 'sell_unit', name: 'sell_unit' },
      { data: 'available_qty', name: 'available_qty' },
        { data: 'po_quantity', name: 'po_quantity' },
      { data: 'po_number', name: 'po_number' },
      // { data: 'total_price', name: 'total_price' },
      { data: 'last_price', name: 'last_price' },
      { data: 'quantity', name: 'quantity' },
      { data: 'quantity_ship', name: 'quantity_ship' },
      { data: 'number_of_pieces', name: 'number_of_pieces' },
      { data: 'pcs_shipped', name: 'pcs_shipped' },
      { data: 'exp_unit_cost', name: 'exp_unit_cost' },
      { data: 'margin', name: 'margin' },
      { data: 'unit_price', name: 'unit_price' },
      { data: 'last_updated_price_on', name: 'last_updated_price_on' },
      { data: 'discount', name: 'discount' },
      { data: 'unit_price_discount', name: 'unit_price_discount' },
      { data: 'vat', name: 'vat' },
      { data: 'unit_price_with_vat', name: 'unit_price_with_vat' },
      { data: 'total_amount', name: 'total_amount' },
      { data: 'restaurant_price', name: 'restaurant_price' },
      { data: 'size', name: 'size' },
      { data: 'total_price', name: 'total_price' },
    ],
    "preDrawCallback": function( settings ) {
    pageScrollPos = $('body').scrollTop();
    },
    drawCallback: function(){
      setTimeout(function(){
      $('.entriestable').DataTable().columns.adjust();
     }, 3000);
      $('#loader_modal').modal('hide');
       $('body').scrollTop(pageScrollPos);
    },
    initComplete:function(){
      table.columns.adjust().draw();
      // $($.fn.dataTable.tables( true ) ).css('width', '100%');
        table.colReorder.order([{{ @$display_prods->display_order != null ? @$display_prods->display_order : $columns_prefrences }}]);
        // table.colReorder.disable();

    }
    });

    table.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      var arr = table.colReorder.order();
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
        data : {type:'draft_quotation_product',column_id:col},
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

  table.on( 'column-reorder', function ( e, settings, details ) {
    var arr = "{{@$display_purchase_list->display_order}}";
    var all = arr.split(',');
    $.get({
    url : "{{ route('column-reorder') }}",
    dataType : "json",
    data : "type=draftt_quotation_product&order="+table.colReorder.order(),
    beforeSend: function(){
      $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('hide');
    },
    success: function(data){
    }
    });
    table.button(0).remove();
    table.button().add(0,
    {
      extend: 'colvis',
      autoClose: false,
      fade: 0,
      columns: ':not(.noVis)',
      colVis: { showAll: "Show all" }
    });

    table.ajax.reload();

    var headerCell = $( table.column( details.to ).header() );
    headerCell.addClass( 'reordered' );

  });

  $(document).on('keyup focusout','.confirm-address',function(e){
      if (e.keyCode === 27)
      {
        $('.customer-addresses .body').removeClass('d-none');
        $('.customer-addresses .selected_address').addClass('d-none');
      }
  });

  $(document).on('click','.edit-address',function(){

  $('.customer-addresses .body').addClass('d-none');
  $('.customer-addresses .selected_address').removeClass('d-none');

  var id = $(this).data('id');
  var current_Address = $(this).prev().val();
  $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

      $.ajax({
      type: "get",
      url: "{{ route('get-customer-addresses') }}",
      dataType: 'json',
      data: {customer_id:id,current_Address:current_Address},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        console.log(data);
        $('.customer-addresses .body').addClass('d-none');
        $('.customer-addresses .selected_address').html(data.html);
        $('#loader_modal').modal('hide');

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
});

  $(document).on('click','.edit-address-ship',function(){

  $('.customer-addresses-ship .ship-body').addClass('d-none');
  $('.customer-addresses-ship .selected_address_ship').removeClass('d-none');

  var id = $(this).data('id');
  var current_Address = $(this).prev().val();

  $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
        type: "get",
        url: "{{ route('get-customer-addresses-ship') }}",
        dataType: 'json',
        data: {customer_id:id,current_Address:current_Address},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          console.log(data);
          // $('.customer-addresses-ship .ship-body');
          $('.customer-addresses-ship .selected_address_ship').html(data.html);
          $('#loader_modal').modal('hide');

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

  $(document).on("click",".inputDoubleClick",function(){
        $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
  });

  $(document).on("focusout",".fieldFocus",function() {
    // alert('hi')
      var quotation_id = "{{ $id }}";
      var attr_name = $(this).attr('name');

      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();

      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        // thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }

      if (e.keyCode === 27 && $(this).hasClass('active') )
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        // thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }

      // if(attr_name == 'target_ship_date')
      // {
      //   $(this).prev().html($(this).val());
      //   if($(this).val() == '')
      //   {
      //     return false;
      //   }
      //   var t_s_date = $.datepicker.formatDate("d/m/yy", new Date($(this).val()));
      //   $(this).prev().html(t_s_date);
      //   $(this).addClass('d-none');
      //   $(this).prev().removeClass('d-none');
      // }
      // else if(attr_name == 'delivery_request_date')
      // {
      //   $(this).prev().html($(this).val());
      //   if($(this).val() == '')
      //   {
      //     return false;
      //   }
      //   var d_r_date = $.datepicker.formatDate("d/m/yy", new Date($(this).val()));
      //   console.log(d_r_date);
      //   $(this).prev().html(d_r_date);
      //   $(this).addClass('d-none');
      //   $(this).prev().removeClass('d-none');
      // }
      if(attr_name == 'manual_ref_no')
      {
        var val = $(this).val();
        if(val == '')
        {
          new_value = 'Manual Ref#. Here';
        }
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
      if(attr_name == 'memo')
      {
        // if($(this).val() == '')
        // {
        //   $(this).prev().html("Memo Here");
        //   $(this).addClass('d-none');
        //   $(this).removeClass('active');
        //   $(this).prev().removeClass('d-none');
        //   return false;
        // }
        // else
        // {
          var val = $(this).val();
          // alert(val);
          if(val == '')
          {
            // alert('empty');
            new_value = 'Memo Here';
          }
          // $(this).prev().html($(this).val());
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        // }
      }
      if(attr_name == 'comment')
      {
        // $(this).prev().html($(this).val());
         if($(this).val() == '')
        {
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
            return false;
        }else{
          $(this).prev().html($(this).val());
        }
      }
      if(attr_name == 'comment_warehouse')
      {
        // $(this).prev().html($(this).val());
         if($(this).val() == '')
        {
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
            return false;
        }else{
          $(this).prev().html($(this).val());
        }
      }
      $(this).addClass('d-none');
      $(this).prev().removeClass('d-none');

      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

      $.ajax({
      type: "post",
      url: "{{ route('save-quotation-discount') }}",
      dataType: 'json',
      data: 'quotation_id='+quotation_id+'&'+attr_name+'='+encodeURIComponent($(this).val()),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        if(attr_name == 'delivery_request_date')
          {
            if(data.order.payment_due_date != null)
            {
              var p_due_date = $.datepicker.formatDate("d/m/yy", new Date(data.order.payment_due_date));
              $('.payment_due_date_term').html(p_due_date);
            }
          }
        if(attr_name == 'discount'){
          $('.inv-discount').html(data.discount);
          $('.grand-total').html(data.total);
        }
        else if(attr_name == 'shipping'){
          $('.inv-shipping').html(data.shipping);
          $('.grand-total').html(data.total);
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $(document).on('click','#addProduct , #uploadExcelbtn , #addInquiryProductbtn',function(){
    var customer_id = $('#unique').data('customer-id');
    if(customer_id == null)
    {
      toastr.info('warning!', 'Please Select Customer First',{"positionClass": "toast-bottom-right"});
    }
    else
    {
      if($(this).attr("id") == 'addProduct')
      {
        var query = "default";
        var page = "draft_Quot";
        var inv_id = $("#add_product_to_invoice").val();
        var _token = $('input[name="_token"]').val();
        var customer = $('.add-cust').val();
        // console.log(customer);
        $.ajax({
        url:"{{ route('autocomplete-fetch-product') }}",
        method:"POST",
        data:{query:query, _token:_token, inv_id:inv_id, page:page, customer:customer},
        beforeSend: function(){
          $('#product_name_div_new').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
        },
        success:function(data){
          $('#product_name_div_new').fadeIn();
          $('#product_name_div_new').html(data);
        }
       });

        $('#addProductModal').modal('show');
        $('#prod_name').focus();
      }
      else if($(this).attr("id") == 'uploadExcelbtn')
      {
        $('#uploadExcel').modal('show');
      }
      else if($(this).attr("id") == 'addInquiryProductbtn')
      {
        // $('#addInquiryProductModal').modal('show');
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          url: "{{ route('add-inquiry-product') }}",
          method: 'post',
          data: $('.add-inquiry-product-form').serialize(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(result){
            $('#loader_modal').modal('hide');
            if (result.success == false) {
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              return;
            }
            toastr.success('Success!', 'Inquiry Product added successfully',{"positionClass": "toast-bottom-right"});
            // $('.table-ordered-products').DataTable().ajax.reload();
            table.row.add( {
                "action":       result.getColumns.action,
                "refrence_code":   result.getColumns.refrence_code,
                "hs_code":     result.getColumns.hs_code,
                "description": result.getColumns.description,
                "notes":     result.getColumns.notes,
                "category_id":       result.getColumns.category_id,
                "type_id":       result.getColumns.type_id,
                "brand":       result.getColumns.brand,
                "temprature":       result.getColumns.temperature,
                "supply_from":       result.getColumns.supply_from,
                "available_qty":       result.getColumns.available_qty,
                "po_quantity":       result.getColumns.po_quantity,
                "po_number":       result.getColumns.po_number,
                "last_price":       result.getColumns.last_price,
                "quantity":       result.getColumns.quantity,
                "quantity_ship":       result.getColumns.quantity_ship,
                "number_of_pieces":       result.getColumns.number_of_pieces,
                "pcs_shipped":       result.getColumns.pcs_shipped,
                "exp_unit_cost":       result.getColumns.exp_unit_cost,
                "margin":       result.getColumns.margin,
                "unit_price":       result.getColumns.unit_price,
                "last_updated_price_on":       result.getColumns.last_updated_price_on,
                "discount":       result.getColumns.discount,
                "unit_price_discount":       result.getColumns.unit_price_discount,
                "vat":       result.getColumns.vat,
                "unit_price_with_vat":       result.getColumns.unit_price_with_vat,
                "total_amount":       result.getColumns.total_amount,
                "restaurant_price":       result.getColumns.restaurant_price,
                "size":       result.getColumns.size,
                "total_price":       result.getColumns.total_price,
            } ).node().id = result.getColumns.id;
              table.draw();
          },
          error: function (request, status, error) {
            $('#loader_modal').modal('hide');
            $('.form-control').removeClass('is-invalid');
            $('.form-control').next().remove();
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
              $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('input[name="'+key+'"]').addClass('is-invalid');
            });
          }
        });
      }
    }
  });

  $(document).on('change', '.add-cust', function(e){
    if($(this).val() === 'new'){
      $('#addCustomerModal').modal('show');
    }

  });

      //This one is for the Create modal
  $(document).on('change',"#country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({
        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        beforeSend:function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success:function(data){
          $("#loader_modal").modal('hide');
          var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
          html_string+='  <select id="state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
          for(var i=0;i<data.length;i++){
            html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
          }
          html_string+=" </select></div>";

          $("#state").html(html_string);
          $('.selectpicker').selectpicker('refresh');
        },
        error:function(request, status, error){
          $("#loader_modal").modal('hide');
          // alert('Error');
        }

    });
  });

  $(document).on('click', '.deleteIcon', function(){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to remove this product?",
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
                data:{id:id},
                url:"{{ route('remove-invoice-product') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                    if(data.success == true){
                      toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                     $('.table-ordered-products').DataTable().ajax.reload();
                     $('.table-Quotation-history').DataTable().ajax.reload();
                     $('.sub-total').html(data.sub_total);
                     $('.total-vat').html(data.total_vat);
                     $('.grand-total').html(data.grand_total);
                     $('#total').val(data.grand_total);
                     $('.total_products').html(data.total_products);
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

  $(document).on('change','.add-cust',function(){
    var customer_id = $(this).val();
    if(customer_id == 'new' || customer_id == '')
    {
      return false;
    }
    var quotation_id = '{{$id}}';
    var _token = $('input[name="_token"]').val();
    $.ajax({
      url:"{{ route('add-customer-to-quotation') }}",
      method:"POST",
      data:{_token:_token,customer_id:customer_id,quotation_id:quotation_id},
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success:function(data){
        $("#loader_modal").modal('hide');

        if (data.success == false) {
          toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
        }
        else{
          location.reload();
          return false;
          $('.add-cust').next().addClass('d-none');
          $('.customer-addresses .header').html(data.html);
          $('.customer-addresses .body').html(data.html_body);
          // $('.customer-addresses-ship .ship-header').html(data.html_2);
          $('.customer-addresses-ship .ship-body').html(data.html_2_body);
          document.getElementById('billing_customer_id').value = data.customer_id;
          document.getElementById('billing_customer_id_bill').value = data.customer_id;
          $('.customer-addresses').removeClass('d-none');
          $('.update_customer').addClass('d-none');
          $('.update_customer span').removeClass('d-none');
          $('.first_edit').removeClass('d-none');

          if(data.payment_term != null)
          {
            $('#sup_payment_term').html(data.payment_term.title);
            $('.payment_terms_id option[value='+data.payment_term.id+']').attr("selected", true);
          }
          else
          {
            $('#sup_payment_term').html('Select Term Here');
            $('.payment_terms_id ').val('');
          }
          $('.payment_due_date_term').html('--');
          $('#delivery_request_date_span').html('Delivery Request Date Here');

          $("#sales_person_select").children().remove("optgroup");
          $('#sales_person_select').append(data.sales_person_html);

          var select = $('#sales_person_select').val();
         $('#sales_person_select').val(select).trigger('change');

          // $('update_customer').html(data.edit_customer);
          // location.reload();
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $(document).on('change', 'select.select-common', function(){

    if($(this).attr('name') == "payment_terms_id")
    {
      // var target_receive_date = $(".target_receive_date").val();
      // if(target_receive_date == '')
      // {
      //   $('.payment_terms_id').val('');
      //   swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Target Ship Date First !!!</b>'});
      //   $('.inputDoubleClick').removeClass('d-none');
      //   $('.inputDoubleClick').next().addClass('d-none');
      //   return false;
      // }
      // else
      // {}


      var payment_terms_id = $(this).val();
      var order_id = '{{$order->id}}';

      $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });

      $.ajax({
        method: "post",
        url: "{{ route('payment-term-save-in-draft-quotation') }}",
        dataType: 'json',
        context: this,
        data: {payment_terms_id:payment_terms_id, order_id:order_id},
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
            if(data.payment_due_date != null)
            {
              var p_due_date = $.datepicker.formatDate("d/m/yy", new Date(data.payment_due_date));
              $('.payment_due_date_term').html(p_due_date);
            }
            $(this).prev().html($("option:selected", this).html());
            $(this).removeClass('active');
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
          }
          else{
            toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });


    }

    if($(this).attr('name') == "from_warehouse_id")
    {
      var from_warehouse_id = $(this).val();
      var order_id = '{{$order->id}}';

      $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });

      $.ajax({
        method: "post",
        url: "{{ route('from-warehouse-save-in-draft-quotation') }}",
        dataType: 'json',
        context: this,
        data: {from_warehouse_id:from_warehouse_id, order_id:order_id},
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
            $(this).prev().html($("option:selected", this).html());
            $('.table-ordered-products').DataTable().ajax.reload();
            $(this).removeClass('active');
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
          }
          else
          {
            toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });


    }

  });

  $(document).on('change','.confirm-address',function(e){

    $('.customer-addresses .body').removeClass('d-none');
    $('.customer-addresses .selected_address').addClass('d-none');

    if($(this).val() === 'add-new')
    {
      $('#add_billing_detail_modal').modal('show');
    }
    else{
      var customer_id = $(this).data('id');
      // alert(customer_id);
      var address_id = $(this).val();
      var quotation_id = '{{$id}}';
      var _token = $('input[name="_token"]').val();
      $.ajax({
        url:"{{ route('edit-customer-address') }}",
        method:"POST",
        data:{_token:_token,customer_id:customer_id,quotation_id:quotation_id,address_id:address_id},
        beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
        success:function(data){
          $("#loader_modal").modal('hide');
          $('.confirm-address').addClass('d-none');
          $('.customer-addresses .body').html(data.html_body);
          $('.edit-functionality').addClass('d-none');
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }
  });

  $(document).on('keyup focusout','.confirm-address-ship',function(e){
    if (e.keyCode === 27)
    {
      $('.customer-addresses-ship .ship-body').removeClass('d-none');
      $('.customer-addresses-ship .selected_address_ship').addClass('d-none');
    }
  });

  $(document).on('change','.confirm-address-ship',function(){

    $('.customer-addresses-ship .ship-body').removeClass('d-none');
    $('.customer-addresses-ship .selected_address_ship').addClass('d-none');

     if($(this).val() === 'add-new'){
        $('#add_billing_detail_modal-ship').modal('show');
      }
    else{
      var customer_id = $(this).data('id');

      var address_id = $(this).val();
      var quotation_id = '{{$id}}';
      var _token = $('input[name="_token"]').val();
      $.ajax({
        url:"{{ route('edit-customer-address-ship') }}",
        method:"POST",
        data:{_token:_token,customer_id:customer_id,quotation_id:quotation_id,address_id:address_id},
        beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
        success:function(data){
          $("#loader_modal").modal('hide');
          $('.confirm-address-ship').addClass('d-none');
          $('.customer-addresses-ship .ship-body').html(data.html_body);
          $('.edit-functionality-ship').addClass('d-none');
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }
  });

  // $('form').on('submit', function(e){

  $('.draft_quotation_discard_form').on('submit', function(e){
     e.preventDefault();
      $.ajaxSetup({
        headers:
        {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('action-invoice') }}",
        method: 'post',
        data: $('.draft_quotation_discard_form').serialize(),
        beforeSend: function(){
          $('.btn_discard_close').prop('disabled',true);
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');

        },
        success: function(result)
        {
          //$("#loader_modal").modal('hide');
          if(result.success == true)
          {
            toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
              window.location.href = "{{ url('/sales')}}";
            }, 500);
          }
          else{
            toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
          }

        },
        error: function (request, status, error)
        {
          $("#loader_modal").modal('hide');
          $('.btn_discard_close').prop('disabled',false);
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

  // $(document).on('click','.draft_quotation_copy_btn',function(e){
  //     e.preventDefault();
  //     $('.copy_and_update').val('yes');
  //     $('.draft_quotation_save_form').submit();
  //     // return;
  // });

  $('.draft_quotation_copy_btn').click(function (e) {
    e.preventDefault();
      $('.copy_and_update').val('yes');
      $('.draft_quotation_save_form').submit();
  });

  $('.direct_invoice').on('click',function(e){
    e.preventDefault();
    var ship_date = $('#target_ship_date').val();
    var delivery_date = $('#delivery_request_date').val();
     @if($targetShipDate['target_ship_date_required']==1)
        if(ship_date == '')
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Please Add a Target Ship Date!!!</b>'});
          inverror = true;
          return false;
        }

        @endif
        if(delivery_date == '')
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Please Add a Delivery Request Date!!!</b>'});
          inverror = true;
          return false;
        }
        $('.direct-invoice').val('direct-draft-invoice');
    $('.draft_quotation_save_form').submit();
  });

  $(document).on('submit','.draft_quotation_save_form',function (e) {
  // $('.draft_quotation_save_form').submit(function (e) {
      var inverror = false;
      var customer = $(this).find('select[name=customer]');
      var ship_date = $(".target_ship_date").val();
      if(customer.val() == '')
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Please Select Customer First!!!</b>'});
        inverror = true;
      }
      // else if(ship_date.val() == '')
      // {
      //   swal({ html:true, title:'Alert !!!', text:'<b>Please Add a Target Ship Date!!!</b>'});
      //   inverror = true;
      // }
      else
      {
        inverror = false;
      }

      if(inverror == true)
      {
        e.preventDefault();
        return false;
      }
      else
      {
        // var customer_total_dues = "{{$customer_total_dues}}";
        // var customer_credit_limit = "{{$customer_credit_limit}}";
        // alert(customer_credit_limit);
        // var find_dues = customer_total_dues - customer_credit_limit;
          e.preventDefault();
          $.ajaxSetup({
            headers:
            {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });
          $.ajax({
          url: "{{ route('check-customer-credit-limit') }}",
          method: 'post',
          data: {customer_id:"{{$order->customer_id}}",id: "{{$order->id}}",type: "draft"},
          context: this,
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
          },
          success: function(result)
          {
            // console.log(result);
            // return;
            $("#loader_modal").modal('hide');
            var customer_total_dues = result.customer_total_dues;
            var customer_credit_limit = result.customer_credit_limit;
            var find_dues = customer_total_dues - customer_credit_limit;

              if(customer_credit_limit !== null && find_dues > 0 && customer_credit_limit !== '')
              {
                  e.preventDefault();
                  swal({
                  title: "Alert!",
                  text: "The customer has reached the limit!! Are you sure to make the quotation for this customer.",
                  type: "info",
                  showCancelButton: true,
                  confirmButtonClass: "btn-danger",
                  confirmButtonText: "Yes!",
                  cancelButtonText: "No!",
                  closeOnConfirm: true,
                  closeOnCancel: false
                  },
                function(isConfirm) {
                  if (isConfirm)
                  {
                    e.preventDefault();
                    $.ajaxSetup({
                      headers:
                      {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                      }
                    });
                    $.ajax({
                      url: "{{ route('action-invoice') }}",
                      method: 'post',
                      data: $('.draft_quotation_save_form').serialize(),
                      context: this,
                      beforeSend: function(){
                        $(".draft_quotation_copy_btn").prop('disabled', true);
                        $(".draft_quotation_save_btn").prop('disabled', true);
                        $('#loader_modal').modal({
                            backdrop: 'static',
                            keyboard: false
                          });
                        $("#loader_modal").modal('show');
                      },
                      success: function(result)
                      {
                        // $("#loader_modal").modal('hide');
                        if(result.success == true)
                        {
                          // alert(result.direct_d_inv);
                          toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                          if(result.direct_d_inv == true)
                          {
                            setTimeout(function(){
                            window.location.href = "{{ url('/sales/draft_invoices')}}";
                            }, 500);
                          }
                          else
                          {
                            setTimeout(function(){
                            window.location.href = "{{ url('/sales')}}";
                            }, 500);
                          }

                        }
                        else if(result.success == false)
                        {
                          toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                          $(".draft_quotation_copy_btn").prop('disabled', false);
                          $(".draft_quotation_save_btn").prop('disabled', false);
                          $("#loader_modal").modal('hide');
                        }
                      },
                      error: function (request, status, error)
                      {
                        $(".draft_quotation_copy_btn").prop('disabled', false);
                        $(".draft_quotation_save_btn").prop('disabled', false);
                        $("#loader_modal").modal('hide');
                        $('.form-control').removeClass('is-invalid');
                        $('.form-control').next().remove();
                        json = $.parseJSON(request.responseText);
                        $.each(json.errors, function(key, value){
                            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                            $('input[name="'+key+'"]').addClass('is-invalid');
                        });
                      }
                    });
                  }
                  else
                  {
                    swal("Cancelled", "", "error");
                  }
                });
              }
              else
              {
                e.preventDefault();
                $.ajaxSetup({
                  headers:
                  {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                  }
                });
                $.ajax({
                url: "{{ route('action-invoice') }}",
                method: 'post',
                data: $('.draft_quotation_save_form').serialize(),
                context: this,
                beforeSend: function(){
                  $(".draft_quotation_copy_btn").prop('disabled', true);
                  $(".draft_quotation_save_btn").prop('disabled', true);
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                  $("#loader_modal").modal('show');
                },
                success: function(result)
                {
                  // $("#loader_modal").modal('hide');
                  if(result.success == true)
                  {
                    toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                    if(result.direct_d_inv == true)
                    {
                      setTimeout(function(){
                      window.location.href = "{{ url('/sales/draft_invoices')}}";
                      }, 500);
                    }
                    else
                    {
                      setTimeout(function(){
                      window.location.href = "{{ url('/sales')}}";
                      }, 500);
                    }
                  }
                  else if(result.success == false)
                  {
                    toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                    $(".draft_quotation_copy_btn").prop('disabled', false);
                    $(".draft_quotation_save_btn").prop('disabled', false);
                    $("#loader_modal").modal('hide');
                  }
                },
                error: function (request, status, error)
                {
                  $(".draft_quotation_copy_btn").prop('disabled', false);
                  $(".draft_quotation_save_btn").prop('disabled', false);
                  $("#loader_modal").modal('hide');
                  $('.form-control').removeClass('is-invalid');
                  $('.form-control').next().remove();
                  json = $.parseJSON(request.responseText);
                  $.each(json.errors, function(key, value){
                      $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                      $('input[name="'+key+'"]').addClass('is-invalid');
                  });
                }
              });
              }
          },
          error: function (request, status, error)
          {
          }
        });


      }
  });

  $(document).on('click', 'input[type=number]', function(e){
    $(this).removeClass('disabled');
    $(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on('keyup focusout', 'input[type=number], input[type=text]', function(e){
    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();

    if ($(this).attr('name') === 'quantity' && ($(this).val() == null || $(this).val() < 0)) {
        swal({ html:true, title:'Alert !!!', text:'<b>QTY ordered cannot be less then 0 !!!</b>'});
        return false;
    }
    else if ($(this).attr('name') === 'number_of_pieces' && ($(this).val() == null || $(this).val() < 0)) {
        swal({ html:true, title:'Alert !!!', text:'<b># Pieces ordered cannot be less then 0 !!!</b>'});
        return false;
    }
    else if ($(this).attr('name') === 'discount' && ($(this).val() == null || $(this).val() < 0)) {
        swal({ html:true, title:'Alert !!!', text:'<b>Discount cannot be less then 0 !!!</b>'});
        return false;
    }
    else if ($(this).attr('name') === 'unit_price' && ($(this).val() == null || $(this).val() < 0)) {
        swal({ html:true, title:'Alert !!!', text:'<b>Discount cannot be less then 0 !!!</b>'});
        return false;
    }

    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
    }

    if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

      if($(this).hasClass('fieldFocus'))
      {
        return false;
      }
      if($(this).val().length < 1)
      {
        return false;
      }
      else if(fieldvalue == new_value)
      {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');

          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');
      }
      else
      {
        // $(this).addClass('disabled');
        $(this).addClass('d-none');
        $(this).removeClass('active');
        // $(this).attr('readonly','true');
        // alert($(this).val());
        $(this).prev().data('fieldvalue',$(this).val());
        $(this).prev().html($(this).val());
        $(this).prev().removeClass('d-none');
        if($(this).val() !== null)
        {
          var product_row = $(this).parent().parent().attr('id');
          var attr_name = $(this).attr('name');
          // alert(product_row);
          // return;
          UpdateQuotationData(product_row, attr_name, new_value,fieldvalue);
        }
      }
    }
  });

  $(document).on('keyup focusout','.warehouse_id',function(e) {
    if(e.keyCode == 27 || e.which == 0){
      $(this).addClass('d-none');
      $(this).removeClass('active');
      $(this).prev().removeClass('d-none');
    }
  });
  $(document).on('keyup focusout','.selling_unit',function(e) {
    if(e.keyCode == 27 || e.which == 0){
      $(this).addClass('d-none');
      $(this).removeClass('active');
      $(this).prev().removeClass('d-none');
    }
  });
  $(document).on('change','.warehouse_id',function(e) {

      var fieldvalue = $(this).prev().data('fieldvalue');
      var product_row = $(this).parent().parent().attr('id');
      var attr_name = $(this).attr('name');
      var new_value = $("option:selected",this).val();
      UpdateQuotationData(product_row, attr_name, new_value,fieldvalue);
  });

  $(document).on('change','.selling_unit',function(e) {

      var product_row = $(this).parent().parent().attr('id');
      var attr_name = $(this).attr('name');
      var new_value = $("option:selected",this).val();
      UpdateQuotationData(product_row, attr_name, new_value);
  });

    $(document).on('keyup focusout','.product_type',function(e) {
    if(e.keyCode == 27 || e.which == 0){
      $(this).addClass('d-none');
      $(this).removeClass('active');
      $(this).prev().removeClass('d-none');
    }
  });

  $(document).on('change','.product_type',function(e) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var product_row = $(this).parent().parent().attr('id');
      var attr_name = $(this).attr('name');
      var new_value = $("option:selected",this).val();
      UpdateQuotationData(product_row, attr_name, new_value,fieldvalue);
  });

  $(document).on('click','.condition',function(){
    var value = $(this).val();
    var id = $(this).data('id');

    var data = id.split(' ');
    // alert(data[0]);
    var product_row = data[0];
    var new_value = data[1];
    // return;
    // alert(value);
      if(value == 'qty'){
        // alert($('#pieces'+product_row).prop('checked'));
        $('#pieces'+product_row).prop('checked',false);
        $('#pieces'+product_row).attr('disabled',false);
        $(this).attr('disabled',true);
        UpdateQuotationData(product_row, 'quantity', new_value,'clicked');
      }else{
        $('#is_retail'+product_row).prop('checked',false);
        $('#is_retail'+product_row).attr('disabled',false);
        $(this).attr('disabled',true);
        UpdateQuotationData(product_row, 'number_of_pieces', new_value,'clicked');
      }
  // $.ajaxSetup({
  //       headers: {
  //         'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  //       }
  //     });
  //    $.ajax({
  //   type: "post",
  //   url: "{{ route('update_order_products') }}",
  //   data: 'prod_id='+id+'&'+'value='+value,

  //   success: function(response){
  //     if(response.success == true){
  //       $(".table-ordered-products").DataTable().ajax.reload();
  //     }
  //   }
  // });

  });
  // $(function (e) {
  // $(document).on('submit', '#myForm', function (event) { /* handler */ }
  var scrollingY = window.scrollY;
  function UpdateQuotationData(draft_quotation_id,field_name,new_value,field_value)
 {
    if(field_name != 'unit_price'){
      if(field_value<0){
        return false;
      }
    }
    UpdateQuotationData
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('sales/update-quotation-data') }}",
        dataType: 'json',
        data: 'draft_quotation_id='+draft_quotation_id+'&'+field_name+'='+encodeURIComponent(new_value)+'&'+'old_value='+encodeURIComponent(field_value),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('hide');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            table.columns.adjust();
            // $('.table-ordered-products').css('table-layout', '');
            // $('.table-ordered-products').css('table-layout', 'fixed');
            toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
            // $('.table-ordered-products').DataTable().ajax.reload();
            // $('.table-Quotation-history').DataTable().ajax.reload();
            $('.sub-total').html(data.sub_total);
            $('.total-vat').html(data.vat);
            $('.grand-total').html(data.grand_total);
            $('#total').val(data.grand_total);
            $('.sub_total_without_discount').html(data.sub_total_without_discount);
            $('.item_level_dicount').html(data.item_level_dicount);
            $('.total_amount_wo_vat_'+data.id).html(data.total_amount_wo_vat);
            $('.total_amount_w_vat_'+data.id).html(data.total_amount_w_vat);
            $('.unit_price_after_discount_'+data.id).html(data.unit_price_after_discount);
            $('.unit_price_'+data.id).html(data.unit_price);
            $('.unit_price_'+data.id).attr('data-fieldvalue',data.unit_price);
            $('.unit_price_'+data.id).data('fieldvalue',data.unit_price);
            $('.unit_price_field_'+data.id).val(data.unit_price);
            $('.unit_price_w_vat_'+data.id).html(data.unit_price_w_vat);
            $('.unit_price_w_vat_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
            $('.unit_price_w_vat_'+data.id).data('fieldvalue',data.unit_price_w_vat);
            $('.unit_price_w_vat_field'+data.id).val(data.unit_price_w_vat);

            $('.supply_from_'+data.id).html(data.supply_from);
            $('.quantity_span_'+data.id).html(data.quantity);
            $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
            $('.quantity_span_'+data.id).data('fieldvalue',data.quantity);
            $('#draft_quotation_qty_'+data.id).val(data.quantity);
            $('#is_retail'+data.id).attr('data-id',data.id+' '+data.quantity);
            $('#is_retail'+data.id).data('id',data.id+' '+data.quantity);

            $('.quantity_span_'+data.id).css("color", "black");
            $('.description_'+data.id).css("color", "black");


            $('.pcs_span_'+data.id).html(data.pcs);
            $('.pcs_span_'+data.id).attr('data-fieldvalue',data.pcs);
            $('.pcs_span_'+data.id).data('fieldvalue',data.pcs);
            $('#draft_quotation_pieces_'+data.id).val(data.pcs);
            $('#pieces'+data.id).attr('data-id',data.id+' '+data.pcs);
            $('#pieces'+data.id).data('id',data.id+' '+data.pcs);

            $('.product_type_'+data.id).html(data.type);
            $('.product_type_'+data.id).attr('data-fieldvalue',data.type_id);
            $('.product_type_'+data.id).data('fieldvalue',data.type_id);

            document.body.scrollTop = scrollingY;
          } else {
            toastr.error('Error!', 'Please Enter the positive value.',{"positionClass": "toast-bottom-right"});
          }
        },

        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }

      });
  }
  // });

  $(document).on('keyup','.refrence_number',function(e){
    if(e.keyCode == 13)
    {
      if($(this).val() !== ''){
      var refrence_number = $(this).val();
      var id = $(this).data(id);
      var formData = {"refrence_number":refrence_number,"id":id};
      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
      $.ajax({
          url: "{{ route('add-by-refrence-number') }}",
          method: 'post',
          data: formData,
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('hide');
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            if(result.success == true)
            {
              toastr.success('Success!', result.successmsg ,{"positionClass": "toast-bottom-right"});
              $('.refrence_number').val('');
              // $('.table-ordered-products').DataTable().ajax.reload();
              $('.table-Quotation-history').DataTable().ajax.reload();
              $('.sub-total').html(result.sub_total);
              $('.total-vat').html(result.total_vat);
              $('.grand-total').html(result.grand_total);
              $('#total').val(result.grand_total);
              $('.total_products').html(result.total_products);

              table.row.add( {
                "action":       result.getColumns.action,
                "refrence_code":   result.getColumns.refrence_code,
                "hs_code":     result.getColumns.hs_code,
                "description": result.getColumns.description,
                "notes":     result.getColumns.notes,
                "category_id":       result.getColumns.category_id,
                "type_id":       result.getColumns.type_id,
                "brand":       result.getColumns.brand,
                "temprature":       result.getColumns.temperature,
                "supply_from":       result.getColumns.supply_from,
                "available_qty":       result.getColumns.available_qty,
                "po_quantity":       result.getColumns.po_quantity,
                "po_number":       result.getColumns.po_number,
                "last_price":       result.getColumns.last_price,
                "quantity":       result.getColumns.quantity,
                "quantity_ship":       result.getColumns.quantity_ship,
                "number_of_pieces":       result.getColumns.number_of_pieces,
                "pcs_shipped":       result.getColumns.pcs_shipped,
                "exp_unit_cost":       result.getColumns.exp_unit_cost,
                "margin":       result.getColumns.margin,
                "unit_price":       result.getColumns.unit_price,
                "last_updated_price_on":       result.getColumns.last_updated_price_on,
                "discount":       result.getColumns.discount,
                "unit_price_discount":       result.getColumns.unit_price_discount,
                "vat":       result.getColumns.vat,
                "unit_price_with_vat":       result.getColumns.unit_price_with_vat,
                "total_amount":       result.getColumns.total_amount,
                "restaurant_price":       result.getColumns.restaurant_price,
                "size":       result.getColumns.size,
                "total_price":       result.getColumns.total_price,
            } ).node().id = result.getColumns.id;
              // table.order([2, default_sort]).draw();
              if (default_sort != null) {
                table.order([2, default_sort]).draw();
              }
              else{
                table.order([0, 'desc']).draw();
              }
            }
            else
            {
              toastr.error('Error!', result.successmsg ,{"positionClass": "toast-bottom-right"});
              $('.refrence_number').val('');
              $('.table-ordered-products').DataTable().ajax.reload();
            }
          },
          error: function (request, status, error) {
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
              $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('input[name="'+key+'"]').addClass('is-invalid');
            });
          }
        });
     }
   }
  });

  $(window).keydown(function(e){
    if(e.keyCode == 13) {
      e.preventDefault();
      return false;
    }
  });

  $(document).on('click', '.draft_quotation_save_btn11', function(e){
    var quotation_id = "{{$order->id}}";
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('check-if-inquiry-prod-exist') }}",
      method: 'get',
      dataType: 'json',
      data:'id='+quotation_id,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(result){
        $('#loader_modal').modal('hide');
        if(result.success == false)
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Cannot save draft quotation, because its have Incomplete/Inquiry item, must full description and unit price!!!</b>'});
        }
        else
        {
          swal({ html:true, title:'Alert !!!', text:'<b>Must fill description, to add this item into inquiry product!!!</b>'});
        }
      },
      error: function (request, status, error) {
        $("#loader_modal").modal('hide');
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

  $(document).on('submit', '.upload-excel-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
     $.ajax({
        url: "{{ route('upload-excel') }}",
        method: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('.save-btn').val('Please wait...');
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
        },
        success: function(result){
          $('.save-btn').val('upload');
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          $('.modal').modal('hide');
          toastr.success('Success!', 'Bulk Uploaded',{"positionClass": "toast-bottom-right"});
          $('.upload-excel-form')[0].reset();
          swal("Bulk Uploaded successfully");
          setTimeout(function(){
              window.location.reload();
            }, 2000);
        },
        error: function (request, status, error) {
          $("#loader_modal").modal('hide');
          $('.save-btn').val('upload');
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
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

  $(document).on('click','.prod-search',function(){
    var hs_code = $('#hs-code').val();
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        method:"post",
        data:'hs_code='+hs_code,
        url:"{{ route('search-product') }}",
        success:function(data){
          var html_str = ``;
              for(var i = 0; i < data.product.length; i++){
              html_str +=   `<tr>
                <td><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check" value="`+data.product[i].id+`" id="product_check_`+data.product[i].id+`">
                <label class="custom-control-label" for="product_check_`+data.product[i].id+`"></label>
                </div></td>
                <td>`+data.product[i].refrence_code+`</td>
                <td>`+data.product[i].hs_code+`</td>
                <td>`+data.product[i].short_desc+`</td>
              </tr>`;
          }
          $('#addProductModal tbody').empty();
          $('#addProductModal tbody').append(html_str);
          $('.table-responsive').removeClass('d-none');
      }
    });
  });

  $(document).on('click', '.check-all', function () {
    if(this.checked == true){
      $('.check').prop('checked', true);
      $('.check').parents('tr').addClass('selected');
      var cb_length = $( ".check:checked" ).length;
      if(cb_length > 0){
        $('.selected-item').removeClass('d-none');
      }
    }
    else{
      $('.check').prop('checked', false);
      $('.check').parents('tr').removeClass('selected');
      $('.selected-item').addClass('d-none');
    }
  });

  $(document).on('click', '.check', function () {
      var cb_length = $( ".check:checked" ).length;
      if(this.checked == true){
      $('.selected-item').removeClass('d-none');
      $(this).parents('tr').addClass('selected');
    }
    else{
      $(this).parents('tr').removeClass('selected');
      if(cb_length == 0){
        $('.selected-item').addClass('d-none');
      }
    }
  });

  $(document).on('click', '.quotation-btn', function(e){
      var selected_products = [];
      var quotation_id = "{{$order->id}}";
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });

      $("input.check:checked").each(function() {
        selected_products.push($(this).val());
      });
      $.ajax({
        method:"post",
        data:'selected_products='+selected_products+'&quotation_id='+quotation_id,
        url:"{{ route('add-prod-to-quotation') }}",
        success:function(data){
          $('#addProductModal').modal('hide');
          $('.addProductModal')[0].reset();
          if (data.success == false) {
            toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
            return;
          }
          // $('.table-Quotation-history').DataTable().ajax.reload();
          // $('.table-ordered-products').DataTable().ajax.reload();
          $('.sub-total').html(data.sub_total);
          $('#total').val(data.grand_total);
          $('.total_products').html(data.total_products);

          table.row.add( {
                "action":       data.getColumns.action,
                "refrence_code":   data.getColumns.refrence_code,
                "hs_code":     data.getColumns.hs_code,
                "description": data.getColumns.description,
                "notes":     data.getColumns.notes,
                "category_id":       data.getColumns.category_id,
                "type_id":       data.getColumns.type_id,
                "brand":       data.getColumns.brand,
                "temprature":       data.getColumns.temperature,
                "supply_from":       data.getColumns.supply_from,
                "available_qty":       data.getColumns.available_qty,
                "po_quantity":       data.getColumns.po_quantity,
                "po_number":       data.getColumns.po_number,
                "last_price":       data.getColumns.last_price,
                "quantity":       data.getColumns.quantity,
                "quantity_ship":       data.getColumns.quantity_ship,
                "number_of_pieces":       data.getColumns.number_of_pieces,
                "pcs_shipped":       data.getColumns.pcs_shipped,
                "exp_unit_cost":       data.getColumns.exp_unit_cost,
                "margin":       data.getColumns.margin,
                "unit_price":       data.getColumns.unit_price,
                "last_updated_price_on":       data.getColumns.last_updated_price_on,
                "discount":       data.getColumns.discount,
                "unit_price_discount":       data.getColumns.unit_price_discount,
                "vat":       data.getColumns.vat,
                "unit_price_with_vat":       data.getColumns.unit_price_with_vat,
                "total_amount":       data.getColumns.total_amount,
                "restaurant_price":       data.getColumns.restaurant_price,
                "size":       data.getColumns.size,
                "total_price":       data.getColumns.total_price,
            } ).node().id = data.getColumns.id;
              // table.draw();
              if (default_sort != null) {
                table.order([2, default_sort]).draw();
              }
              else{
                table.order([0, 'desc']).draw();
              }
              // table.columns.adjust().draw();
        }
    });
  });

  @if(Session::has('errormsg'))
    toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
  @endif

  });

</script>
<script>
  function backFunctionality(){
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('sales') }}";
       document.location.href = url;
     }
   }
</script>
<script>
$(function(e) {

$(document).ready(function(){
    $(document).keyup(function(e) {
        if (e.keyCode == 13 && !$('#prod_name').is(':focus') && $('.addProductModal').is(":visible") && product_ids_array.length != 0) {
            $(".add_product_to").trigger('click');
        }
    })

 $('#prod_name').keyup(function(e){
    var page = "draft_Quot";

    var query = $.trim($(this).val());
    if(query == '' || e.keyCode == 8 || 'keyup' )
    {
      $('#product_name_div_new').empty();
    }
    var inv_id = $("#add_product_to_invoice").val();
    if(e.keyCode == 13)
    {
      if(query.length > 2)
      {
       var _token = $('input[name="_token"]').val();
       $.ajax({
        url:"{{ route('autocomplete-fetch-product') }}",
        method:"POST",
        data:{query:query, _token:_token, inv_id:inv_id, page:page},
        beforeSend: function(){
          $('#product_name_div_new').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
        },
        success:function(data){
          $('#product_name_div_new').fadeIn();
          $('#product_name_div_new').html(data);
        },
        error: function(request, status, error){
          //$("#loader_modal").modal('hide');
        }
       });
      }
      else
      {
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
  });
});

    $(document).on('click','.close-modal, #uploadExcelbtn',function(){
        // console.log('here');
        product_ids_array = [];
        $("#tags_div").html("");
        $('#addProductModal').modal('hide');
    });

$(document).on('click', '.add_product_to', function(e){
    e.preventDefault();
    var customer_id = $(this).data('customer_id');
    var supplier_id = $('#supplier_id').val();
    var inv_id = $(this).data('inv_id');
    var prod_ids = product_ids_array;
    $('#product_array').val(prod_ids);
    var input = $('#product_array').val();
    // $('#prod_name').val($(this).text());

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method:"post",
      data:'selected_products='+input+'&quotation_id='+inv_id+'&customer_id='+customer_id+'&supplier_id='+supplier_id,
      url:"{{ route('add-prod-to-quotation') }}",
      success:function(data){
        $('#addProductModal').modal('hide');
        $('.table-ordered-products').DataTable().ajax.reload();
        product_ids_array = [];
        $("#tags_div").html("");
        if (data.success == false) {
          toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
          return;
        }
        // $('.table-ordered-products').DataTable().ajax.reload();
        // $('.table-Quotation-history').DataTable().ajax.reload();
        $('.sub-total').html(data.sub_total);
        $('.total-vat').html(data.total_vat);
        $('.grand-total').html(data.grand_total);
        $('#total').val(data.grand_total);
        $('.total_products').html(data.total_products);
        $('#prod_name').val('');
        $('#product_name_div_new').empty();
        var table = $('.table-ordered-products').DataTable();
        table.row.add( {
                "action":       data.getColumns.action,
                "refrence_code":   data.getColumns.refrence_code,
                "hs_code":     data.getColumns.hs_code,
                "description": data.getColumns.description,
                "notes":     data.getColumns.notes,
                "category_id":       data.getColumns.category_id,
                "type_id":       data.getColumns.type_id,
                "brand":       data.getColumns.brand,
                "temprature":       data.getColumns.temperature,
                "supply_from":       data.getColumns.supply_from,
                "available_qty":       data.getColumns.available_qty,
                "po_quantity":       data.getColumns.po_quantity,
                "po_number":       data.getColumns.po_number,
                "last_price":       data.getColumns.last_price,
                "quantity":       data.getColumns.quantity,
                "quantity_ship":       data.getColumns.quantity_ship,
                "number_of_pieces":       data.getColumns.number_of_pieces,
                "pcs_shipped":       data.getColumns.pcs_shipped,
                "exp_unit_cost":       data.getColumns.exp_unit_cost,
                "margin":       data.getColumns.margin,
                "unit_price":       data.getColumns.unit_price,
                "last_updated_price_on":       data.getColumns.last_updated_price_on,
                "discount":       data.getColumns.discount,
                "unit_price_discount":       data.getColumns.unit_price_discount,
                "vat":       data.getColumns.vat,
                "unit_price_with_vat":       data.getColumns.unit_price_with_vat,
                "total_amount":       data.getColumns.total_amount,
                "restaurant_price":       data.getColumns.restaurant_price,
                "size":       data.getColumns.size,
                "total_price":       data.getColumns.total_price,
            } ).node().id = data.getColumns.id;
              // table.draw();
              table.columns.adjust().draw();
      }
  });
});

var product_ids_array = [];
$(document).on('click', '.add_product_to_tags', function(e){
    var prod_id = $(this).data('prod_id');
    var prod_name = $(this).data('prod_name');
    var prod_desc = $(this).data('prod_description');
    var supplier_id = $(this).data('supplier_id');
    $('#supplier_id').val(supplier_id)
    product_ids_array.push(prod_id);
    $('.tags_div').append('<button id="'+prod_id+'" style="white-space: normal; margin-right:10px;" class="col-3 btn btn-primary mt-2">'+prod_name+' - '+prod_desc+'<i data-prod_id="'+prod_id+'" class="fa fa-close ml-3 remove-products" style="position: absolute; top: 4px; right: 5px;"></i></button>');
    $(this).blur();
});

$(document).on('click','.remove-products',function(e){
    var prod_id = $(this).data('prod_id');

    for( var i = 0; i < product_ids_array.length; i++){
        if ( product_ids_array[i] === prod_id) {
            product_ids_array.splice(i, 1);
        }
    }

    document.getElementById(prod_id).remove();
});

$(document).on('click','.export_btn', function(e){
  var inverror = false;
  var customer = $('.add-cust').val();
  var ship_date = $(".target_ship_date").val();
  var draft_quot_id = $('#export_quotation_id').val();
  if(customer == '')
  {
    swal({ html:true, title:'Alert !!!', text:'<b>Please Select Customer First!!!</b>'});
    inverror = true;
  }
  else
  {
    inverror = false;
  }

  if(inverror == true)
  {
    e.preventDefault();
    return false;
  }
  else
  {
    e.preventDefault();
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('check_product_qty_draft') }}",
      method: 'post',
      data: 'draft_quot_id='+draft_quot_id,
      context: this,
      beforeSend: function(){
        $(".export_btn").prop('disabled', true);
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(result)
      {
        $("#loader_modal").modal('hide');
        if(result.success == true)
        {
          $('#type').val('data');
          $("#export_draft_quotations_form").submit();
            $('.table-ordered-products').DataTable().ajax.reload();
            $('.export_btn').prop('disabled',false);
        }
        else if(result.success == false)
        {
          toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
          $('.export_btn').prop('disabled',false);
        }
      },
      error: function (request, status, error)
      {
        $("#loader_modal").modal('hide');
        $('.export_btn').prop('disabled',false);
      }
    });
  }
});
 // document upload
$('.addDocumentForm2').on('submit', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ route('add-draft-quotation-document') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData:false,
      beforeSend: function(){
        $('.save-doc-btn').html('Please wait...');
        $('.save-doc-btn').addClass('disabled');
        $('.save-doc-btn').attr('disabled', true);
      },
      success: function(result){
        $('.addDocumentForm2')[0].reset();
        $('#uploadDocument').modal('hide');
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').attr('disabled', true);
        $('.save-doc-btn').removeAttr('disabled');
        if(result.success == true)
        {
          $("#loader_modal").modal('hide');
          toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
          $('.addDocumentForm')[0].reset();
          // $('.addDocumentModal').modal('hide');
           $('.collapse').collapse("toggle");
          let sid = $("#sid").val();

    $.ajax({
      type: "post",
      url: "{{ route('get-draft-quotation-files') }}",
      data: 'quotation_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      }
    });
        }
      },
      error: function (request, status, error) {
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').removeClass('disabled');
        $('.save-doc-btn').removeAttr('disabled');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
              $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
             $('input[name="'+key+'[]"]').addClass('is-invalid');

        });
        }
    });
});

$(".billing").on("focusout",function(){

    var title = $(this).val();
    var customer_id = $('#unique').data('customer-id');
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
     $.ajax({
        url: "{{ url('sales/check-duplicate-address') }}",
        method: 'post',
        dataType:'json',
        data:{customer_id:customer_id,title:title},

        success: function(data){

          if(data.success == false)
          {
            $('input[name="'+data.field+'"]').after('<span class="invalid-feedback" role="alert"><strong>The '+data.field+' is Already Been taken</strong>');
            $('input[name="'+data.field+'"]').addClass('is-invalid');
          }
          else if(data.success == true)
          {
            $('input[name="'+data.field+'"]').removeClass('is-invalid');
          }

        },
        error: function (request, status, error) {
        }
      });
});

$(document).on('click','#add-address-form-btn',function(e){
   e.preventDefault();
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      url:"{{ url('sales/save-customer-billing')}}",
          method: 'post',
    data: $('#add-address-form').serialize(),
      success:function(data){
        if(data.success == false)
          {
            $('input[name="'+data.field+'"]').after('<span class="invalid-feedback" role="alert"><strong>The '+data.field+' is Already Been taken</strong>');
            $('input[name="'+data.field+'"]').addClass('is-invalid');
          }
          else{
       $('#add_billing_detail_modal').modal('hide');
       $('.customer-addresses .body').html(data.html);
      }
      }
  });
});

$(document).on('click','#add-address-form-btn-ship',function(e){
   e.preventDefault();
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      url:"{{ url('sales/save-customer-billing-ship')}}",
          method: 'post',
    data: $('#add-address-form-ship').serialize(),
      success:function(data){
        console.log(data.html);
       $('#add_billing_detail_modal-ship').modal('hide');
       $('.customer-addresses-ship .ship-body').html(data.html);
      }
  });
})

});

$(document).on('change',"#billing_country",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){

            var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
            html_string+='  <select id="billing_state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div>";

            $("#billing_state").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(){
          alert('Error');
        }

    });
});

$(document).on('change',"#billing_country_ship",function(){
    var country_id=$(this).val();
    var store_state =$(this);
    $.ajax({

        url:"{{url('common/filter-state')}}",
        method:"get",
        dataType:"json",
        data:{country_id:country_id},
        success:function(data){

            var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
            html_string+='  <select id="billing_state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
            for(var i=0;i<data.length;i++){
                html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
            }
            html_string+=" </select></div>";

            $("#billing_state_ship").html(html_string);
            $('.selectpicker').selectpicker('refresh');

        },
        error:function(){
          alert('Error');
        }

    });
});

$(document).on('click', '.add-notes', function(e){
  var draft_quot_id = $(this).data('id');
  $('.note-draft-quot-id').val(draft_quot_id);

});

$('.add-draft-quot-note-form').on('submit', function(e){
    e.preventDefault();
    var val = $('#note_description').val();
    if(val == '' || val == undefined)
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must fill note description!!!</b>'});
      return false;
    }
    var checkbox=null;
    if($('#show_note_invoice').prop('checked'))
    checkbox=1
    else
    checkbox=0;
    var formData=new FormData(this);
    formData.append("show_note_invoice", checkbox);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
      $.ajax({
        url: "{{ route('add-draft-quotation-note') }}",
        dataType: 'json',
        type: 'post',
        data: formData,
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          $('#loader_modal').modal('hide');
          if(result.success == true){
            toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
            $('#add_notes_modal').modal('hide');
            $('.table-ordered-products').DataTable().ajax.reload();

            $('.add-draft-quot-note-form')[0].reset();

          }else{
            toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
          }

        },
        error: function (request, status, error) {
              /*$('.form-control').removeClass('is-invalid');
              $('.form-control').next().remove();*/
              $('#loader_modal').modal('hide');
              $('.save-btn').removeClass('disabled');
              $('.save-btn').removeAttr('disabled');
              json = $.parseJSON(request.responseText);
              $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                    $('input[name="'+key+'"]').addClass('is-invalid');
                    $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                    $('textarea[name="'+key+'"]').addClass('is-invalid');


              });
          }
      });
  });

$(document).on('click', '.show-notes', function(e){
    let draft_quot_id = $(this).data('id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-draft-quotation-note') }}",
      data: 'draft_quot_id='+draft_quot_id,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        $('.fetched-notes').html(response);
      }
    });

  });

$(document).on('click', '#delete-draft-note', function(e){
    let note_id = $(this).data('id');
    let compl_quot_id = $(this).data('compl_quot_id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('delete-draft-quot-prod-note') }}",
      data: 'note_id='+note_id,

      success: function(response){

        $.ajax({
            type: "get",
            url: "{{ route('get-draft-quotation-note') }}",
            data: 'compl_quot_id='+compl_quot_id,
            success: function(response){
              $('.fetched-notes').html(response);
            }
      });
        // window.location.reload();
      }
    });

});

$(document).on('click', '#show_note_checkbox', function(e){
    let note_id = $(this).data('id');
    let compl_quot_id = $(this).data('compl_quot_id');
    var checkbox=null;
    if($('#show_note_checkbox').prop('checked'))
    checkbox=1
    else
    checkbox=0;
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('update-draft-quot-prod-note') }}",
      data: {note_id: note_id, show_on_invoice: checkbox},

      success: function(response){

        // $.ajax({
        //     type: "get",
        //     url: "{{ route('get-draft-quotation-note') }}",
        //     data: 'compl_quot_id='+compl_quot_id,
        //     success: function(response){
        //       $('.fetched-notes').html(response);
        //     }
      // });
        // window.location.reload();
      }
    });

});

$(document).on('click', '.download-documents', function(e){

    let sid = $(this).data('id');
    // alert(sid);
    console.log(sid);
     $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
    $.ajax({
      type: "post",
      url: "{{ route('get-draft-quotation-files') }}",
      data: 'quotation_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      }
    });

  });

$(document).on('click', '.deleteFileIcon', function(e){
  var id = $(this).data('id');

  swal({
    title: "Alert!",
    text: "Are you sure you want to delete this file? You won't be able to undo this.",
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
        data:'id='+id,
        url:"{{ route('remove-draft-quotation-file') }}",
        beforeSend:function(){
        },
        success:function(data){
          if(data.search('done') !== -1){
            myArray = new Array();
            myArray = data.split('-SEPARATOR-');
            let i_id = myArray[1];
            $('#quotation-file-'+i_id).remove();
            toastr.success('Success!', 'File deleted successfully.' ,{"positionClass": "toast-bottom-right"});
          }
        }
    });
    }
    else
    {
      swal("Cancelled", "", "error");
    }
  });
});

//Add Enquiry item as new product
$(document).on("click",'.add-as-product',function(){

  var id = $(this).data('id');
  $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  $.ajax({
    type: "get",
    dataType: 'json',
    url: "{{ route('checking-item-shortDesc') }}",
    data: 'id='+id,
    beforeSend: function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
    },
    success: function(response){
      $("#loader_modal").modal('hide');
      if(response.success == false)
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Must fill description, to add this item as a inquiry product!!!</b>'});
      }
      else
      {
        $.ajax({
            method:"get",
            dataType: 'json',
            data:'id='+id,
            url:"{{ route('fetch-suppliers-for-inquiry-product') }}",
            beforeSend:function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
               $("#loader_modal").modal('show');
            },
            success:function(data){
              $("#loader_modal").modal('hide');
              if(data.success == true){
                console.log(data.suppliers);
                 $('.inquiry-body').append(data.suppliers);
                 $(".state-tags").select2({dropdownCssClass : 'bigdrop'});
                 $('.inquiry_modal').trigger('click');
              }else{
                 toastr.error('Error!', 'Something went wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
              }
            }
          });

      }
    }
  });

  });

  $(document).on('click','.add_as_inquiry_product_btn',function(){
      var supplier_id = $('.inquiry-product-supplier').val();
      var id = $('.inquiry_id').val();

      swal({
          title: "Alert!",
          text: "Are you sure you want selected item as new Product in System?",
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
            dataType: 'json',
            data:{supplier_id:supplier_id,id:id},
            url:"{{ route('enquiry-item-as-new-product') }}",
            beforeSend:function(){
              $('#inquiryModal').modal('hide');
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
               $("#loader_modal").modal('show');
            },
            success:function(data){
              $("#loader_modal").modal('hide');
              if(data.success == true){
                  toastr.success('Success!', 'Item added as a Inquiry Product to System successfully.' ,{"positionClass": "toast-bottom-right"});
                  $('.table-ordered-products').DataTable().ajax.reload();
                  $('#inquiryModal').modal('hide');
              }else{
                 toastr.error('Error!', 'Something went wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
              }
            }
          });
            }
            else{
                swal("Cancelled", "", "error");
            }
       });
  });

$('.edit_customer').on("click",function(){
  $('.update_customer').removeClass('d-none');
  $('.customer-addresses').addClass('d-none');
});

 // $(function() {
 //          $(document).ready(function () {
 //            var todaysDate = new Date();
 //            var year = todaysDate.getFullYear();
 //            var month = ("0" + (todaysDate.getMonth() + 1)).slice(-2);
 //            var day = ("0" + todaysDate.getDate()).slice(-2);
 //            var maxDate = (year +"-"+ month +"-"+ day);
 //            //this create an error (An invalid form control with name='target_ship_date' is not focusable.)
 //            //$('.target_ship_date').attr('min',maxDate);
 //          });
 //        });

$(document).on('keyup', function(e) {
  if (e.keyCode === 27){ // esc

    $("#delivery_request_date").datepicker('hide');
    $("#payment_due_date").datepicker('hide');
    $("#target_ship_date").datepicker('hide');

    if($('.inputDoubleClick').hasClass('d-none'))
    {
      $('.inputDoubleClick').removeClass('d-none');
      $('.inputDoubleClick').next().addClass('d-none');
    }
  }
});



var order_id = "{{$id}}";
  // alert(order_id);

  $('.table-Quotation-history').DataTable({
    processing: false,
    // "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    "sPaginationType": "listbox",
    ordering: false,
    searching:false,
    "lengthChange": true,
    serverSide: true,
    "scrollX": true,
    "bInfo":false,
    lengthMenu: [ 25, 50, 75, 100],
    "columnDefs": [
      { className: "dt-body-left", "targets": [] },
      { className: "dt-body-right", "targets": [] },
    ],
    ajax: {
      url:"{!! route('get-quotation-history') !!}",
      data: function(data) { data.order_id = order_id } ,
    },
    columns: [
      // { data: 'checkbox', name: 'checkbox' },
      { data: 'user_name', name: 'user_name' },
      { data: 'created_at', name: 'created_at' },
      { data: 'item', name: 'item' },
      // { data: 'name', name: 'name' },
      { data: 'column_name', name: 'column_name' },
      { data: 'old_value', name: 'old_value' },
      { data: 'new_value', name: 'new_value' },
    ]
  });


  $('.upload-quotation-excel-form').on('submit',function(e){


    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-Quotation-excel') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $('#loader_modal').modal('show');
        $(".draft-quotation-upload-btn").attr("disabled", true);
      },
      success: function(data){
        $('.upload-quotation-excel-form')[0].reset();
        $('#import-modal-quotation').modal('hide');
        $('#loader_modal').modal('hide');
        $('#import-modal').modal('hide');
        if(data.success == true)
        {
          toastr.success('Success!', data.msg, {"positionClass": "toast-bottom-right"});
        }
        else{
          toastr.warning('Warning!', data.msg, {"positionClass": "toast-bottom-right"});
        }
        $(".draft-quotation-upload-btn").attr("disabled", false);
        $('.table-ordered-products').DataTable().ajax.reload();
      }
    });
  });

$('#show_price').on('change', function() {
    var checked = $(this).prop('checked');
    if (checked == true)
    {
      $('#show_discount_input').val('1');
    }
    else if (checked == false)
    {
      $('#show_discount_input').val('0');
    }
  });

// export pdf code
  $(document).on('click', '.export-pdf', function(e){
    var sort = $('#default_sort').val();
    var column_name = $('#column_name').val();
    var quo_id = $('#quo_id_for_pdf').val();
    // $('.export-quot-form')[0].submit();
    var bank_id = $('.company-banks').val();
     var bank = "{{@$bank}}";
    if(bank_id == null && bank != '')
    {
      toastr.warning('Please!', 'Select Bank First !!!',{"positionClass": "toast-bottom-right"});
      return false;
    }
    var orders = "{{$id}}";
    var with_vat = $('#with_vat').val();
    var show_discount = $('#show_discount_input').val();
    var page_type = 'draft quotations';
    var is_proforma = 'yes';
    var is_texica = "{{$is_texica}}";

    if(is_texica == 1)
    {
     var url = "{{url('sales/export-draft-quot-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+show_discount+"/"+bank_id+"/"+with_vat;
    }
    else
    {
     var url = "{{url('sales/export-draft-quot-to-pdf-exc-vat')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+show_discount+"/"+bank_id;
    }
     window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
  });

  // export pdf code
  $(document).on('click', '.export-pdf-inc-vat', function(e){
    var sort = $('#default_sort').val();
    var quo_id = $('#quo_id_for_pdf').val();
    var column_name = $('#column_name').val();
    var bank_id = $('.company-banks').val();
     var bank = "{{@$bank}}";
    if(bank_id == null && bank != '')
    {
      toastr.warning('Please!', 'Select Bank First !!!',{"positionClass": "toast-bottom-right"});
      return false;
    }
    var orders = "{{$id}}";
    var show_discount = $('#show_discount_input').val();
    var page_type = 'draft quotations';
    var is_texica = "{{$is_texica}}";
    if(is_texica == 1)
    {
     var url = "{{url('sales/export-draft-quot-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+show_discount+"/"+bank_id+"/"+with_vat;
    }
    else
    {
      var url = "{{url('sales/export-draft-quot-to-pdf-inc-vat')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+show_discount+"/"+bank_id;
    }
     window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
  });

  var type = 'draft_qoutation';
  var id = '{{$id}}';
  var config = {
      routes: {
          zone: "{{ route('save-sale-person') }}"
      }
  };

  $(document).on('click', '#example_export_btn', function(e) {
    $('#type').val('example');
    $("#export_draft_quotations_form").submit();
  })
  $(document).on('submit', '#export_draft_quotations_form', function (e) {
        e.preventDefault();
        $.ajax({
            method: "post",
            url: "{{route('export-draft-quotation')}}",
            data: $(this).serialize(),
            beforeSend: function() {
                $('.excel-alert').removeClass('d-none');
                $('.excel-alert-success').addClass('d-none');
            },
            success: function(data) {
                if (data.success == true) {
                    $('.excel-alert').addClass('d-none');
                    $('.excel-alert-success').removeClass('d-none');
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error) {
                $("#loader_modal").modal('hide');
            }
        });
    })
</script>
<script src="{{asset('public/site/assets/backend/js/save_sales_person.js')}}"></script>
@stop

