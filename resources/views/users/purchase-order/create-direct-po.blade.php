@extends('users.layouts.layout')

@section('title','Purchase Order | Purchasing')

@section('content')
<style type="text/css">
.supplier_ref {
    width: 15%;
    word-break: break-all;
}

.pf {
    width: 15%;
}

.supplier {
    width: 18%;
}

.description {
    width: 50%;
}

.p_type{
  width: 15%;
}

.p_winery{
  width: 15%;
}

.p_notes{
  width: 10%;
}

.rsv {
    width: 8%;
}

.pStock {
    width: 8%;
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
.inputDoubleClick{
    font-style: italic;
}

.inputDoubleClickQuantity{
  font-style: italic;
  font-weight: bold;
}

.second_drop .select2-container{
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
          <li class="breadcrumb-item active">Draft Purchase Order</li>
      </ol>
  </div>
</div>

@php
use Carbon\Carbon;
@endphp

<div class="row mb-3 headings-color">

  <div class=" col-lg-8 col-md-6 title-col">
    <h3 class="maintitle text-uppercase fontbold">Draft Purchase Order # <span class="c-ref-id">{{ $id }}</span></h3>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="row mx-0 align-items-center">
      <h3 class="maintitle text-uppercase fontbold col-8 px-0">{{$global_terminologies['supply_from']}}</h3>
      <a class="col-4 px-0 text-right mb-3 d-none" onclick="backFunctionality()">
        <!-- <button type="button" class="btn-color btn text-uppercase purch-btn headings-color">back</button> -->
        <span class="vertical-icons" title="Back">
          <img src="{{asset('public/icons/back.png')}}" width="27px">
        </span>
      </a>
    </div>

  </div>

<!-- New Design Starts Here  -->
<div class="col-lg-12 col-md-12">
<div class="row">

<div class="col-lg-8 col-md-6">
 @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . $company_info->logo))
  <img src="{{asset('public/uploads/logo/'.$company_info->logo)}}" class="img-fluid" style="height: 80px;" align="big-qummy">
  @else
  <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="height: 80px;" align="big-qummy">
  @endif
  <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p>
  <p class="mb-1">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
  <p class="mb-1"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}  <em class="fa fa-envelope"></em>  {{@$company_info->billing_email}}</p>
  <br>

  <div class="row errormsgDiv d-none">
    <div class="col-lg-8 mt-4">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <div id="msgs_alerts"></div>
      </div>
    </div>
  </div>

</div>
<div class="col-lg-4 col-md-6">
  <form class="mb-2 purchase_order_form" enctype='multipart/form-data'>
  <input type="hidden" name="copy_and_update" class="copy_and_update">
  <!-- <p class="mb-1">Purchase From</p> -->
  <input type="hidden" name="selected_supplier_id" id="selected_supplier_id" value="{{$draft_po->supplier_id}}">
  <input type="hidden" name="selected_warehouse_id" id="selected_warehouse_id" value="{{$draft_po->from_warehouse_id}}">
  <div class="update-supplier-div"></div>
  @if($draft_po->getSupplier != NULL || $draft_po->getFromWarehoue != NULL)

    @if($draft_po->getSupplier != NULL)
    <div class="supplier_selected_info">
    <i class="fa fa-edit edit-address change_supplier" title="Change Supply From" style="cursor: pointer;"></i>
    <div class="d-flex align-items-center mb-1">
      <div>
        @if(@$draft_po->getSupplier->logo != NULL && file_exists( public_path() . '/uploads/sales/customer/logos/' . @$draft_po->getSupplier->logo))
        <img src="{{asset('public/uploads/sales/customer/logos'.'/'.$draft_po->getSupplier->logo)}}" class="img-fluid" align="big-qummy" style="width: 65px; height: auto;">
        @else
        <img src="{{asset('public/uploads/logo/temp-logo.png')}}" class="img-fluid" align="big-qummy" style="width: 65px; height: auto;">
        @endif
      </div>
      <div class="pl-2 comp-name" data-supplier-id="{{@$draft_po->supplier_id}}"><p>{{$draft_po->getSupplier->reference_name}}</p> </div>
    </div>

    <p class="mb-1">
      @if($draft_po->getSupplier->address_line_1 !== null)
      {{ $draft_po->getSupplier->address_line_1.' '.$draft_po->getSupplier->address_line_2 }},
      @endif
      @if($draft_po->getSupplier->country !== null)
      {{ $draft_po->getSupplier->getcountry->name }},
      @endif
      @if($draft_po->getSupplier->state !== null)
      {{ $draft_po->getSupplier->getstate->name }},
      @endif
      @if($draft_po->getSupplier->city !== null)
      {{ $draft_po->getSupplier->city }},
      @endif
      @if($draft_po->getSupplier->postalcode !== null)
      {{ $draft_po->getSupplier->postalcode }}
      @endif
    </p>
    @if($draft_po->getSupplier->email !== null || $draft_po->getSupplier->phone !== null)
    <ul class="d-flex list-unstyled">
        @if($draft_po->getSupplier->phone !== null)
        <li><i class="fa fa-phone pr-2"></i>{{$draft_po->getSupplier->phone}}</li>
        @endif
        @if($draft_po->getSupplier->email !== null)
        <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{$draft_po->getSupplier->email}}</li>
        @endif
    </ul>
    @endif
    </div>
    @endif

    @if($draft_po->getFromWarehoue != NULL)
    <div class="supplier_selected_info">
    <i class="fa fa-edit edit-address change_supplier" title="Change Supply From" style="cursor: pointer;"></i>
    <div class="d-flex align-items-center mb-1">
      <div>
        <img src="{{asset('public/img/warehouse-logo.png')}}" class="img-fluid" align="big-qummy" style="width: 65px; height: auto;">
      </div>
      <div class="pl-2 comp-name" data-supplier-id="{{@$draft_po->from_warehouse_id}}"><p>{{$draft_po->getFromWarehoue->warehouse_title}}</p></div>
    </div>

    <p class="mb-1">
      @if($draft_po->getFromWarehoue->getCompany !== null)
      {{ $draft_po->getFromWarehoue->getCompany->billing_address }},
      @endif
      @if($draft_po->getFromWarehoue->getCompany->getcountry !== null)
      {{ $draft_po->getFromWarehoue->getCompany->getcountry->name }},
      @endif
      @if($draft_po->getFromWarehoue->getCompany->getstate !== null)
      {{ $draft_po->getFromWarehoue->getCompany->getstate->name }},
      @endif
      @if($draft_po->getFromWarehoue->getCompany->city !== null)
      {{ $draft_po->getFromWarehoue->getCompany->city }},
      @endif
      @if($draft_po->getFromWarehoue->getCompany->postalcode !== null)
      {{ $draft_po->getFromWarehoue->getCompany->postalcode }}
      @endif
    </p>
    @if($draft_po->getFromWarehoue->getCompany->billing_email !== null || $draft_po->getFromWarehoue->getCompany->billing_phone !== null)
    <ul class="d-flex list-unstyled">
        @if($draft_po->getFromWarehoue->getCompany->billing_phone !== null)
        <li><i class="fa fa-phone pr-2"></i>{{$draft_po->getFromWarehoue->getCompany->billing_phone}}</li>
        @endif
        @if($draft_po->getFromWarehoue->getCompany->billing_email !== null)
        <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{$draft_po->getFromWarehoue->getCompany->billing_email}}</li>
        @endif
    </ul>
    @endif
    </div>
    @endif

    <div class="d-none col-lg-12 second_drop p-0">
      <select class="form-control js-states state-tags mb-2 add-supp" name="supplier">
        <option value="new" disabled="" selected="">Choose Supply From</option>
        <!-- @if(@$warehouses->count() > 0)
          <optgroup label="Warehouses">
          @foreach($warehouses as $warehouse)
          <option {{$draft_po->from_warehouse_id == $warehouse->id ? "selected" : ""}} value="w-{{ $warehouse->id }}"> {{$warehouse->warehouse_title}} </option>
          @endforeach
          </optgroup>
        @endif -->
        @if(@$suppliers->count() > 0)
          <optgroup label="Suppliers">
          @foreach($suppliers as $supplier)
          <option {{$draft_po->supplier_id == $supplier->id ? "selected" : ""}} value="s-{{ $supplier->id }}"> {{$supplier->reference_name}} </option>
          @endforeach
          </optgroup>
        @endif
      </select>
      <div class="supplier_info"></div>
    </div>
  @else
    <select class="form-control js-states state-tags mb-2 add-supp" name="supplier">
      <option value="new" disabled="" selected="">Choose Supply From</option>
      <!-- @if(@$warehouses->count() > 0)
        <optgroup label="Warehouses">
        @foreach($warehouses as $warehouse)
        <option value="w-{{ $warehouse->id }}"> {{$warehouse->warehouse_title}} </option>
        @endforeach
        </optgroup>
      @endif -->
      @if(@$suppliers->count() > 0)
        <optgroup label="Suppliers">
        @foreach($suppliers as $supplier)
        <option value="s-{{ $supplier->id }}"> {{$supplier->reference_name}} </option>
        @endforeach
        </optgroup>
      @endif
      {{--<option value="new">Add New</option>--}}
    </select>
    <div class="supplier_info"></div>
  @endif

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">To Warehouse: <b style="color: red;">*</b></li>
    <span class="pl-4 inputDoubleClick" id="select_to_warehouse_id">
      @if($draft_po->to_warehouse_id != null)
      {{$draft_po->getWarehoue->warehouse_title}}
      @else
      <div style="color: red;">Select To Warehouse</div>
      @endif
    </span>
    <select class="form-control warehouse_id d-none mb-2 select-common" name="warehouse_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Warehouse</option>';
      @foreach ($warehouses as $w)
        @if($draft_po->to_warehouse_id == $w->id)
            <option selected value="{{$w->id}}">{{$w->warehouse_title}}</option>
        @else
            <option value="{{$w->id}}">{{$w->warehouse_title}}</option>
        @endif
      @endforeach
    </select>
  </ul>

  @php
    $target_receive_date = $draft_po->target_receive_date != null ? Carbon::parse($draft_po->target_receive_date)->format('d/m/Y') : "";
  @endphp
		@if($targetShipDate['target_ship_date']==1)
    <ul class="d-flex mb-0 pt-2 list-unstyled">
      <li class=" fontbold" style="width: 150px;">@if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_receiving_date']}} @endif:</li>
      <span class="pl-4 inputDoubleClick" id="target_ship_date" data-fieldvalue="{{$target_receive_date}}">
        @if($draft_po->target_receive_date != null)
        {{Carbon::parse($draft_po->target_receive_date)->format('d/m/Y')}}
        @else
        @if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_receiving_date']}} @endif Here
        @endif
      </span>
      <input type="text" class="ml-4 mt-2 d-none target_receive_date" name="target_receive_date" id="target_receive_date" value="{{$target_receive_date}}">
    </ul>
  @endif
  @php
    $invoice_date = $draft_po->invoice_date != null ? Carbon::parse($draft_po->invoice_date)->format('d/m/Y') : "" ;
  @endphp
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">Invoice Date:</li>
    <span class="pl-4 inputDoubleClick" id="Invoice_date_span" data-fieldvalue="{{$invoice_date}}">
      @if($draft_po->invoice_date != null)
      {{Carbon::parse($draft_po->invoice_date)->format('d/m/Y')}}
      @else
      Invoice Date Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none invoice_date" name="invoice_date" id="invoice_date" value="{{$invoice_date}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">Payment Terms:</li>
    <span class="pl-4 inputDoubleClick" id="sup_payment_term">
      @if($draft_po->payment_terms_id != null)
      {{$draft_po->paymentTerm->title}}
      @else
      Select Term Here
      @endif
    </span>
    <select class="form-control payment_terms_id d-none mb-2 select-common" name="payment_terms_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Term</option>';
      @foreach ($paymentTerms as $pm)
        @if($draft_po->payment_terms_id == $pm->id)
            <option selected value="{{$pm->id}}">{{$pm->title}}</option>
        @else
            <option value="{{$pm->id}}">{{$pm->title}}</option>
        @endif
      @endforeach
    </select>
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">Invoice exchange rate:</li>
    @php
      if($draft_po->exchange_rate !== null)
      {
        $exchange = (1 / $draft_po->exchange_rate);
      }
      else
      {
        $exchange = $draft_po->exchange_rate;
      }
    @endphp
    <span class="pl-4 inputDoubleClick" id="exchange_rate_span" data-fieldvalue="{{@$exchange}}">
      @if($exchange !== null)
      {{@$exchange}}
      @else
      Invoice Exchange Rate Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none exchange_rate fieldFocus" name="exchange_rate" id="exchange_rate" value="{{@$exchange}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif:</li>
    <span class="pl-4 payment_due_date_term" data-fieldvalue="{{Carbon::parse($draft_po->payment_due_date)->format('d/m/Y')}}">
      @if($draft_po->payment_due_date != null)
      {{Carbon::parse($draft_po->payment_due_date)->format('d/m/Y')}}
      @else
      --
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none payment_due_date" name="payment_due_date" id="payment_due_date" value="{{@$draft_po->payment_due_date}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">Memo:</li>
    <span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$draft_po->memo}}">
      @if($draft_po->memo != null)
      {{$draft_po->memo}}
      @else
      Memo Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$draft_po->memo}}">
  </ul>
</div>


<div class="col-lg-12 text-uppercase fontbold mt-4">
  <div class="pull-left">
    <a href="javascript:void(0);">
     <span class="export-pdf vertical-icons ml-3" title="Print">
          <img src="{{asset('public/icons/print.png')}}" width="27px">
      </span>
  </a>

  @if ($config->server != 'lucilla')
  <a href="javascript:void(0);">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pf-pdf mr-3" id="PfLogo">PF logo</button>
  </a>
  @endif
  <a href="javascript:void(0)">
    <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color" style="background-color: transparent;border: none;color: black;"><input type="checkbox" name="show_price" id="show_price" checked="true" style="vertical-align: inherit;scale"> &nbsp;Show Prices</button>
  </a>

  </div>
  <div class="pull-right">

    <span class="vertical-icons mr-4" title="Bulk Import" data-toggle="modal" data-target="#import-modal">
      <img src="{{asset('public/icons/bulk_import.png')}}" width="27px">
    </span>

    <span class="vertical-icons mr-4" title="Download Sample File" id="example_export_btn">
      <img src="{{asset('public/icons/sample_export_icon.png')}}" width="27px">
    </span>
    <span class="vertical-icons export_btn" title="Export">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span>

    <span class="vertical-icons download-documents" data-id="{{$draft_po->id}}" title="Upload Document" data-toggle="modal" data-target="#addDocumentModal">
      <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
    </span>

    @if($checkDraftPoDocs > 0)
      @php $show = ""; @endphp
    @else
      @php $show = "d-none"; @endphp
    @endif
  </div>
</div>
</div>
</div>
  <!-- new design ends here -->
</div>

  <div class="row entriestable-row mt-3">
  <div class="col-12">
  <div class="entriesbg bg-white custompadding customborder table-responsive">
    <div class="alert alert-primary export-alert d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is being prepared! Please wait.. </b>
      </div>
    <div class="alert alert-success export-alert-success d-none"  role="alert">
        <i class=" fa fa-check "></i>
        <b>Export file is ready to download.
            @php
                $current_date = date("Y-m-d");
            @endphp
        <a class="exp_download" href="{{ url('get-download-xslx','Draft PO Export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>

    <table class="table entriestable table-bordered table-ordered-products text-center">
      <thead>
        <tr>
          <th @if(in_array(0,$hidden_columns_by_admin)) class="noVis" @endif>Action</th>
          <th @if(in_array(1,$hidden_columns_by_admin)) class="noVis" @endif>Notes</th>
          <th @if(in_array(2,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['suppliers_product_reference_no']}}
          </th>
          <th @if(in_array(3,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['our_reference_number']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(4,$hidden_columns_by_admin)) class="noVis" @endif>Customer <br>Reference <br>Name
          </th>
          <th @if(in_array(5,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['brand']}}
          </th>
          <th @if(in_array(6,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['product_description']}}
          </th>
          <th @if(in_array(7,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['type']}}</th>
          <th @if(in_array(8,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['supplier_description']}}
          </th>
          <th @if(in_array(9,$hidden_columns_by_admin)) class="noVis" @endif> {{$global_terminologies['expected_lead_time_in_days']}}
          </th>
          <th @if(in_array(10,$hidden_columns_by_admin)) class="noVis" @endif>Gross Weight
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="gross_weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="gross_weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(11,$hidden_columns_by_admin)) class="noVis" @endif>Order<br>{{$global_terminologies['qty']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(12,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['order_qty_unit']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_qty_unit">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_qty_unit">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(13,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['quantity']}}</th>
          <th @if(in_array(14,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['pcs']}}</th>
          <th @if(in_array(15,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['quantity']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_inv">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_inv">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(16,$hidden_columns_by_admin)) class="noVis" @endif>Unit Price
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(17,$hidden_columns_by_admin)) class="noVis" @endif>Purchasing<br>Vat%
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(18,$hidden_columns_by_admin)) class="noVis" @endif>Unit Price (+Vat)
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price_plus_vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price_plus_vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(19,$hidden_columns_by_admin)) class="noVis" @endif>Price Date
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="price_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="price_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(20,$hidden_columns_by_admin)) class="noVis" @endif>Discount</th>
          <th @if(in_array(21,$hidden_columns_by_admin)) class="noVis" @endif>Total Amount<br>(W/O Vat)</th>
          <th @if(in_array(22,$hidden_columns_by_admin)) class="noVis" @endif>Total Amount<br>(Inv Vat)</th>
          <th @if(in_array(23,$hidden_columns_by_admin)) class="noVis" @endif>Order #</th>
          <th @if(in_array(24,$hidden_columns_by_admin)) class="noVis" @endif>Total <br> {{$global_terminologies['gross_weight']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_gross_weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_gross_weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(25,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['avg_units_for-sales'] }}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="avg_units_for_sales">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="avg_units_for_sales">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
      </tr>
      </thead>
    </table>


  <!-- New Design Starts Here  -->
  <div class="row ml-0 mb-4">
  <div class="col-6 mt-4">
    <div class="col-6">
      <div class="purch-border input-group custom-input-group">
      <input type="text" name="refrence_code" style="border-bottom: 1px #dee2e6 !important;" placeholder="Type Reference number..."
      data-draft_po_id = "{{$id}}" class="form-control refrence_number" autocomplete="off">
      </div>
    </div>
    <div class="col-12 mt-4 mb-4">
      <a class="btn purch-add-btn mt-3 fontmed col-lg-3 col-md-5 btn-sale" id="addProduct">Add Product</a>
      <a class="btn purch-add-btn mt-3 fontmed col-lg-2 col-md-5 btn-sale" data-id="{{$id}}" type="button" id="addItem">Add Item</a>
    </div>

    <!-- ---------------History------- -->
    <div class="row">
      <div class="col-lg-12">
        <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
          <table class="table-purchase-order-history headings-color entriestable table table-bordered text-center">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>User </th>
                <th>Date/Time</th>
                <!-- <th>Order #</th> -->
                <th>{{$global_terminologies['our_reference_number']}}</th>
                <th>Column</th>
                <th>Old Value</th>
                <th>New Value</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-----------------/History------- -->

    <div class="row">
      <div class="col-lg-6 col-md-8 ml-5">
        <p>
          <strong>Note: </strong>
          <span class="po-note inputDoubleClick ml-2" data-fieldvalue="{{@$getPoNote->note}}">@if($getPoNote != null) {!! @$getPoNote->note !!} @else {{ 'Click here to add a note...' }} @endif</span>
          <textarea autocomplete="off" name="note" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a note (500 Characters)" maxlength="500">{{ $getPoNote !== null ? @$getPoNote->note : '' }}</textarea>
        </p>
      </div>
      <div class="col-lg-6 col-md-6"></div>
    </div>
  </div>

  <div class="col-lg-5 col-md-4 pt-4 mt-4">
    <div class="side-table">
      <table class="headings-color purch-last-left-table side-table pull-right">
        <tbody>
          <tr class="d-none">
            <td class="fontbold" width="50%">Total QTY:</td>
            <td class="text-start total-qty fontbold">&nbsp;&nbsp;{{ number_format($draft_po->total_quantity, 3, '.', ',') }}</td>
          </tr>
          <tr>
            <td class="fontbold" width="50%">Total Amount @if(!in_array(17,$hidden_columns_by_admin)) (W/O Vat) @endif:</td>
            <input type="hidden" name="sub_total" value="{{$sub_total}}" id="sub_total">
            <td class="text-start sub-total fontbold pl-3">{{ number_format($sub_total, 3, '.', ',') }}</td>
          </tr>
          @if(!in_array(17,$hidden_columns_by_admin))
          <tr>
            <td class="fontbold" width="50%">Vat Amount:</td>
            <input type="hidden" name="vat_amount" value="{{$draft_po->vat_amount_total}}" id="vat_amount">
            <td class="text-start vat-amount fontbold pl-3">{{ number_format($draft_po->vat_amount_total, 3, '.', ',') }}</td>
          </tr>
          <tr>
            <td class="fontbold" width="50%">Total Amount (+Vat):</td>
            <input type="hidden" name="amount_with_vat" value="{{$draft_po->total_with_vat}}" id="amount_with_vat">
            <td class="text-start amount-with-vat fontbold pl-3">{{ number_format($draft_po->total_with_vat, 3, '.', ',') }}</td>
          </tr>
          @endif
          <tr class="d-none">
            <td class="text-nowrap fontbold">Paid:</td>
            <td class="fontbold text-start">&nbsp;&nbsp;$0.00</td>
          </tr>
          <tr class="d-none">
            <td class="text-nowrap fontbold">Due:</td>
            <td class="fontbold text-start">&nbsp;&nbsp;$0.00</td>
          </tr>
        </tbody>
      </table>
    </div>
    {{-- Start panga here --}}
    <div class="w-100 text-right d-flex mt-4 justify-content-right">
      <div class="pull-right w-100 mt-4">
        <input type="hidden" name="draft_po_id" id="draft_po_id" value="{{ $id }}">
        <input type="hidden" name="action" value="save">
        <button form="purchase_order_form_discard text-danger" type="submit" class="btn btn-sm btn-danger btn_discard_close" id="discard_and_close_id">Discard and Close</button>&nbsp;
        <button type="submit" class="btn btn-sm pl-3 pr-3 btn-success draft_po_save_btn" id="save_and_close_id" style="height: 34px;">Save and Close</button>
        <button type="submit" class="btn btn-sm pl-3 pr-3 btn-success draft_po_copy_btn" id="copy_and_update_id" style="height: 34px;">Copy and Update</button>
        </form>
        <form class="d-inline-block mb-2 purchase_order_form_discard" id="purchase_order_form_discard">
          <input type="hidden" name="draft_po_id" value="{{ $id }}">
          <input type="hidden" name="action" value="discard">

        </form>
      </div>
    </div>
    {{-- End Panga --}}
  </div>

    </div>
  </div>

  </div>

    <div class="row justify-content-end d-flex">

    </div>

  </div>
  <!-- New Design Ends Here  -->


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
    <div class="form-group" style="margin-top: 10px; margin-bottom: 50px; position:relative;">
      <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
      <input type="text" name="prod_name" id="prod_name" class="form-control form-group mb-0" autocomplete="off" placeholder="Search by Product Reference #-Default Supplier- Product Description  (Press Enter)" style="padding-left:30px;">
      <div id="product_name_div"></div>
      <input type="hidden" id="product_array">
        <input type="hidden" id="supplier_id">
        <div id="tags_div" class="tags_div mt-4 mb-4 row ml-2"></div>
        <div>
            <a class="btn float-right add_product_to" style="background-color: #5cb85c;">Confirm</a>
        </div>
    </div>

  </div>


  </div>
  </div>
</div>

{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="addDocumentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">UPLOAD DOCUMENTS</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="row justify-content-end" style="width: 100%;">
        <a href="#demo" class="btn btn-primary" data-toggle="collapse" style="margin-top: 7px;">Upload Document</a>
        <div id="demo" class="collapse col-lg-10 offset-1">
          <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">
              <div class="modal-body">
            <input type="hidden" name="draft_purchase_order_id" id="sid" value="{{$id}}">

                <div class="row">
                      <div class="form-group col-lg-9">
                        <label class="pull-left font-weight-bold">Files <span class="text-danger">*</span></label>
                        <input class="font-weight-bold form-control-lg form-control" name="po_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
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


<!-- Modal for Import Items  -->
<div class="modal" id="import-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">BULK IMPORT</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div align="center">
          <!-- <a class="btn text-uppercase purch-btn headings-color btn-color" style="border-radius: 5px;" href="{{asset('public/site/assets/purchasing/po_items_excel/Example_File.xlsx')}}" download="">Download Example File</a><br> -->
          <label class="mt-2">
            <strong>Note : </strong>Please use the downloaded export file for upload only.<span class="text-danger"> Also Don't Upload Empty File.</span>
          </label>
          <form class="upload-excel-form" id="upload-excel-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="d_po_id" value="{{$id}}">
            <input type="hidden" name="d_supplier_id" id="d_supplier_id" value="{{$draft_po->supplier_id}}">
            <input type="file" class="form-control" name="product_excel" id="product_excel" accept=".xls,.xlsx" required="" style="white-space: pre-wrap;"><br>
            <button class="btn btn-info product-upload-btn" type="submit">Upload</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal for Showing Notes  -->
<div class="modal" id="notes-modal" style="width:600px; margin-left: 400px;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Item Notes</h4>
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

<!-- Modal For Add Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Notes</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-purchase-list-note-form" method="post">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="form-group">
                  <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                  <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="purchase_list_id" class="note-purchase-list-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>
<form id="export_draft_po_form">
  @csrf
  <input type="hidden" name="id" id="export_po_id" value="{{$id}}">
  <input type="hidden" name="column_name" id="column_name">
  <input type="hidden" name="sort_order" id="sort_order">
  <input type="hidden" name="type" id="type" value="data">
</form>
 <!-- export pdf form starts -->
  <form class="export-po-form">
    @csrf
    <input type="hidden" name="po_id_for_pdf" id="po_id_for_pdf" value="{{$id}}">
    <input type="hidden" name="show_price_input" id="show_price_input" value="1">
    <input type="hidden" name="pf_logo" id="pf_logo" value="">
  </form>
  <!-- export pdf form ends -->
@endsection
@php
$hidden_by_default = '';
@endphp
@section('javascript')
<script type="text/javascript">

// $('#import-modal').on('hidden.bs.modal', function () {
//   $(this).find('form')[0].reset();
// });

  var view = null;
  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').addClass('d-none');
  });

  $("#target_receive_date, #invoice_date, #payment_due_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  }).on("pick.datepicker",function(e) {
    view = e.view;
    if(view == 'day')
    {
    $(document).on("change focusout","#target_receive_date, #invoice_date, #payment_due_date",function(e) {
    if(view == 'day')
    {
      // console.log(view);
    var draft_po_id = "{{ $id }}";
    var attr_name = $(this).attr('name');
    var id = $(this).attr('id');
    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();
    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      $(this).removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      return false;
    }

    if (e.keyCode === 27 && $(this).hasClass('active') )
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      $('#'+id).datepicker({autoclose:true});
      return false;
    }

    if(attr_name == 'payment_due_date')
    {
      if($(this).val() == '')
      {
        $(this).prev().html("Payment Due Date Here");
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
    if(attr_name == 'target_receive_date')
    {
      if($(this).val() == '')
      {
        // alert($(this).val());
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
    if(attr_name == 'invoice_date')
    {
      if($(this).val() == '')
      {
        $(this).prev().html("Invoice Date Here");
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
      url: "{{ route('save-draft-po-dates') }}",
      dataType: 'json',
      data: 'draft_po_id='+draft_po_id+'&'+attr_name+'='+encodeURIComponent($(this).val()),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        view = null;
        if(data.success == true && data.draft_note == false)
        {
          toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
          if(attr_name == 'invoice_date')
          {
            if(data.draft_po.payment_due_date != null)
            {
              // $('.payment_due_date_term').html(data.draft_po.payment_due_date);
              // var date = new Date(data.draft_po.payment_due_date);
              // var newDate = date.toString('dd/mm/yy');
              var newDate = $.datepicker.formatDate( "dd/mm/yy", new Date(data.draft_po.payment_due_date));
              $('.payment_due_date_term').html(newDate);
            }
          }
        }
      }
    });
    }
  });
    }
  });

  $(".state-tags").select2();
  $(".state-tags-2").select2();
  $(function(e){

// Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#column_name').val(column_name);
    $('#sort_order').val(order);

    $('.table-ordered-products').DataTable().ajax.reload();

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

  var table = $('.table-ordered-products').DataTable({
    processing: false,
    // "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    searching: false,
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    paging: false,
    bInfo : false,
    colReorder: true,
      dom: 'Blfrtip',
		  buttons: [
		 	{
		  	extend: 'colvis',
        columns: ':not(.noVis)',
		  }
		 ],
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 1,2,3,4,5 ] },
      { className: "dt-body-right", "targets": [6,7,8,9,10,11,12] },
			{
			"targets": [{{ $user_plus_admin_hidden_columns != null ? $user_plus_admin_hidden_columns : $hidden_by_default }}],
			 visible: false
			}
    ],
    fixedHeader: true,
    ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url:"{{ url('get-product-to-list-draft-po') }}"+"/"+{{ $id }},
        data: function(data) {data.is_transfer = false, data.column_name = column_name,data.sort_order = order},
      },
    columns: [
        { data: 'action', name: 'action'},
        { data: 'item_notes', name: 'item_notes' },
        { data: 'supplier_id', name: 'supplier_id' },
        { data: 'item_ref', name: 'item_ref' },
        { data: 'customer', name: 'customer' },
        { data: 'brand', name: 'brand' },
        { data: 'product_description', name: 'product_description' },
        { data: 'type', name: 'type' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'leading_time', name: 'leading_time' },
        { data: 'unit_gross_weight', name: 'unit_gross_weight' },
        { data: 'supplier_packaging', name: 'supplier_packaging' },
        { data: 'billed_unit_per_package', name: 'billed_unit_per_package' },
        { data: 'customer_qty', name: 'customer_qty' },
        { data: 'customer_pcs', name: 'customer_pcs' },
        { data: 'quantity', name: 'quantity' },
        { data: 'unit_price', name: 'unit_price' },
        { data: 'purchasing_vat', name: 'purchasing_vat' },
        { data: ("{{in_array(17,$hidden_columns_by_admin)}}" == true ? 'unit_price' : 'unit_price_with_vat'), name: ("{{in_array(17,$hidden_columns_by_admin)}}" == true ? 'unit_price' : 'unit_price_with_vat') },
        { data: 'last_updated_price_on', name: 'last_updated_price_on' },
        { data: 'discount', name: 'discount' },
        { data: 'amount', name: 'amount' },
        { data: ("{{in_array(17,$hidden_columns_by_admin)}}" == true ? 'amount' : 'amount_with_vat'), name: ("{{in_array(17,$hidden_columns_by_admin)}}" == true ? 'amount' : 'amount_with_vat') },
        { data: 'order_no', name: 'order_no' },
        { data: 'gross_weight', name: 'gross_weight' },
        { data: 'weight', name: 'weight' },
    ],
    drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
    initComplete: function() {
  			table.colReorder.order([{{ @$display_prods->display_order != null ? @$display_prods->display_order : $columns_prefrences }}]);
  			// table.colReorder.disable();
		}
  });

  var order_id = "{{$id}}";

  $('.table-purchase-order-history').DataTable({
    processing: false,
    // "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
    // },
    ordering: false,
    searching: false,
    "lengthChange": false,
    serverSide: true,
    "scrollX": true,
    "scrollY": '50vh',
    scrollCollapse: true,
    // "bPaginate": false,
    // "bInfo": false,
    pageLength: {{5}},
    lengthMenu: [5, 20, 40, 50],

    ajax: {
      url: "{!! route('get-draft-purchase-order-history') !!}",
      data: function(data) {
        data.order_id = order_id
      },
    },
    columns: [
      // { data: 'checkbox', name: 'checkbox' },
      {
        data: 'user_name',
        name: 'user_name'
      },
      {
        data: 'created_at',
        name: 'created_at'
      },
      // {
      //   data: 'order_no',
      //   name: 'order_no'
      // },
      {
        data: 'item',
        name: 'item'
      },
      // { data: 'name', name: 'name' },
      {
        data: 'column_name',
        name: 'column_name'
      },
      {
        data: 'old_value',
        name: 'old_value'
      },
      {
        data: 'new_value',
        name: 'new_value'
      },

    ],

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
			data : {type:'draft_po_detail',column_id:column},
			beforeSend: function(){

			},
			success: function(data){
			if(data.success == true){
				/*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
				// table2.ajax.reload();
			}
			}
		});
	});

  table.on( 'column-reorder', function ( e, settings, details ) {
			var arr = "{{@$display_purchase_list->display_order}}";
      var all = arr.split(',');
			$.get({
			url : "{{ route('column-reorder') }}",
			dataType : "json",
			data : "type=draft_po_detail&order="+table.colReorder.order(),
			beforeSend: function(){

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

  $(document).on('click', '#example_export_btn', function() {
    $('#type').val('example');
      $("#export_draft_po_form").submit();
  })

  $('.export_btn').on('click',function(e){


    var inverror = false;
    var supplier = $("#selected_supplier_id").val();
    var from_warehouse = $("#selected_warehouse_id").val();
    var warehouse = $('.warehouse_id :selected').val();
    var target_receive_date = $(".target_receive_date").val();
    var payment_due_date = $(".payment_due_date").val();
    var po_id = $("#export_po_id").val();
    if(supplier == '' && from_warehouse == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
      inverror = true;
    }
    else if(warehouse == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Select Warehouse For Ordered Items!!!</b>'});
      inverror = true;
    }
    else if(from_warehouse != '' && warehouse != '' )
    {
      if( from_warehouse == warehouse )
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Supply From & To Warehouse Cannot Be Same!!!</b>'});
        inverror = true;
      }
    }
    else
    {
      inverror = false;
    }

    if(inverror == true)
    {
      return false;
    }
    else
    {
      $('#type').val('data');
      $("#export_draft_po_form").submit();
      // e.preventDefault();
      // $.ajaxSetup({
      //   headers:
      //   {
      //     'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      //   }
      // });
      // $.ajax({
      //   url: "{{ route('check-qty-export-draft-po') }}",
      //   method: 'post',
      //   data: 'draft_id='+po_id,
      //   context: this,
      //   beforeSend: function(){
      //     $('#loader_modal').modal({
      //         backdrop: 'static',
      //         keyboard: false
      //       });
      //     $("#loader_modal").modal('show');
      //   },
      //   success: function(result)
      //   {
      //     if(result.success == true)
      //     {
      //       $("#export_draft_po_form").submit();
      //       $('.table-ordered-products').DataTable().ajax.reload();
      //     }
      //     else if(result.success == false)
      //     {
      //       $("#loader_modal").modal('hide');
      //       toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
      //     }
      //   },
      //   error: function(request, status, error){
      //     $("#loader_modal").modal('hide');
      //   }
      // });
    }
  });

  $(document).on('submit', '#export_draft_po_form', function (e) {
    e.preventDefault();
    var url = "{{route('export-draft-po')}}";
    var data = $(this).serialize();
    export_excel(url, data);
    })

  $(document).on('click', '.add-notes', function(e){
    var id = $(this).data('id');
    $('.note-purchase-list-id').val(id);
  });

  $('.add-purchase-list-note-form').on('submit', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-draft-po-item-note') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
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
          // $('.table-ordered-products').DataTable().ajax.reload();

          $('.add-purchase-list-note-form')[0].reset();
          $('#add_notes_modal').modal('hide');

          $('.note_'+result.id).removeClass('d-none');

        }else{
          toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
        }

      },
        error: function (request, status, error)
        {
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
    let id = $(this).data('id');
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-draft-po-detail-note') }}",
      data: 'id='+id,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response)
      {
        $('.fetched-notes').html(response);
      },
      error: function(){

      }
    });

  });

  $(document).on('click','#addProduct',function(){
    // console.log('here');
    var supplier_id = $("#selected_supplier_id").val();
    if(supplier_id == '')
    {
       swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
    }
    else
    {
      if($(this).attr("id") == 'addProduct')
      {
        var page = "draft_Po";
        var query = 'Po';
        var supplier_id = $("#selected_supplier_id").val();
        var warehouse_id = $(".warehouse_id").val();
        var draft_po_id = $("#draft_po_id").val();
        var _token = $('input[name="_token"]').val();
        $.ajax({
            url:"{{ route('autocomplete-fetch-product') }}",
            method:"POST",
            data:{query:query, _token:_token, inv_id:draft_po_id, supplier_id:supplier_id, warehouse_id:warehouse_id, page:page},
            beforeSend: function(){
            $('#product_name_div').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
            },
            success:function(data){
                $('#product_name_div').fadeIn();
                $('#product_name_div').html(data);
            }
        });

        $('#addProductModal').modal('show');
        // $('#prod_name').val('');
        // $('#product_name_div').empty();
        // $('#prod_name').focus();
      }
    }
  });

  $(document).on('click','.btn_discard_close',function(e){
    e.preventDefault();
    $('.purchase_order_form_discard').submit();
  });

  $('.purchase_order_form_discard').on('submit', function(e){
     e.preventDefault();
      $.ajaxSetup({
        headers:
        {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('action-draft-po') }}",
        method: 'post',
        data: $('.purchase_order_form_discard').serialize(),
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
              window.location.href = "{{ route('purchasing-dashboard')}}";
            }, 500);
          }
        },
        error: function (request, status, error)
        {
          $("#loader_modal").modal('hide');
          $('.btn_discard_close').porp('disabled',false);
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

  $(document).on('click','.draft_po_copy_btn',function(e){
    e.preventDefault();
    $('.copy_and_update').val('yes');
    $('.purchase_order_form').submit();
  });

  $(document).on('submit','.purchase_order_form',function (e) {

    var inverror = false;
    var supplier = $("#selected_supplier_id").val();
    var from_warehouse = $("#selected_warehouse_id").val();
    var warehouse = $('.warehouse_id :selected').val();
    var target_receive_date = $(".target_receive_date").val();
    var payment_due_date = $(".payment_due_date").val();

    if(supplier == '' && from_warehouse == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
      inverror = true;
    }
    else if(warehouse == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Select Warehouse For Ordered Items!!!</b>'});
      inverror = true;
    }
    else if(from_warehouse != '' && warehouse != '' )
    {
      if( from_warehouse == warehouse )
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Supply From & To Warehouse Cannot Be Same!!!</b>'});
        inverror = true;
      }
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
        url: "{{ route('action-draft-po') }}",
        method: 'post',
        data: $('.purchase_order_form').serialize(),
        context: this,
        beforeSend: function(){
          $('.draft_po_save_btn').prop('disabled',true);
          $('.draft_po_copy_btn').prop('disabled',true);
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
              window.location.href = "{{ route('purchasing-dashboard')}}";
            }, 500);
          }
          else if(result.success == false)
          {
            $("#loader_modal").modal('hide');
            $('.draft_po_save_btn').prop('disabled',false);
          $('.draft_po_copy_btn').prop('disabled',false);
            toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
          }
        },
        error: function (request, status, error)
        {
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

  });

  @if(Session::has('errormsg'))
    toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
  @endif

  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
    @php
      Session()->forget('successmsg');
    @endphp
  @endif

  $('.upload-excel-form').on('submit',function(e){
    // $('#import-modal').modal('hide');
    e.preventDefault();
    var supplier = $("#selected_supplier_id").val();
    if(supplier == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
      return false;
    }
    else
    {
      $('#d_supplier_id').val(supplier);
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('upload-bulk-product-in-pos') }}",
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
          $(".product-upload-btn").attr("disabled", true);
        },
        success: function(data){
          $('#import-modal').modal('hide');
          $('#loader_modal').modal('hide');
          $(".product-upload-btn").attr("disabled", false);
          if(data.success == true)
          {
            toastr.success('Success!', data.msg, {"positionClass": "toast-bottom-right"});
            if(data.errorMsg != null && data.errorMsg != '')
            {
              $('#msgs_alerts').html(data.errorMsg);
              $('.errormsgDiv').removeClass('d-none');
            }
            // $('.exp_imp_btn').click();
            $('.upload-excel-form')[0].reset();
            var sub_total_value = data.sub_total.toFixed(3);
            $('.sub-total').html(sub_total_value);
            $('#sub_total').val(sub_total_value);
            var vat_total_amount = data.vat_total.toFixed(3);
            $('.vat-amount').html(vat_total_amount);
            $('#vat_amount').val(vat_total_amount);
            var amount_with_vat = data.total.toFixed(3);
            $('.amount-with-vat').html(amount_with_vat);
            $('#amount_with_vat').val(amount_with_vat);

            $('.table-ordered-products').DataTable().ajax.reload();
          }
          if(data.success == "withissues")
          {
            toastr.warning('Warning!', data.msg, {"positionClass": "toast-bottom-right"});
            if(data.errorMsg != null && data.errorMsg != '')
            {
              $('#msgs_alerts').html(data.errorMsg);
              $('.errormsgDiv').removeClass('d-none');
            }
            $('.upload-excel-form')[0].reset();
            var sub_total_value = data.sub_total.toFixed(3);
            $('.sub-total').html(sub_total_value);
            $('#sub_total').val(sub_total_value);
            var vat_total_amount = data.vat_amout.toFixed(3);
            $('.vat-amount').html(vat_total_amount);
            $('#vat_amount').val(vat_total_amount);
            var amount_with_vat = data.total_w_v.toFixed(3);
            $('.amount-with-vat').html(amount_with_vat);
            $('#amount_with_vat').val(amount_with_vat);
            $('.table-ordered-products').DataTable().ajax.reload();
          }
          if(data.success == false)
          {
            toastr.error('Error!', data.msg, {"positionClass": "toast-bottom-right"});
            $('.upload-excel-form')[0].reset();
            var sub_total_value = data.sub_total.toFixed(3);
            $('.sub-total').html(sub_total_value);
            $('#sub_total').val(sub_total_value);
            var vat_total_amount = data.vat_amout.toFixed(3);
            $('.vat-amount').html(vat_total_amount);
            $('#vat_amount').val(vat_total_amount);
            var amount_with_vat = data.total_w_v.toFixed(3);
            $('.amount-with-vat').html(amount_with_vat);
            $('#amount_with_vat').val(amount_with_vat);
            $('.table-ordered-products').DataTable().ajax.reload();
          }
        },
        error: function (request, status, error) {
            $('#loader_modal').modal('hide');
            $(".product-upload-btn").attr("disabled", false);
            $('.table-ordered-products').DataTable().ajax.reload();
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
              $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('input[name="'+key+'[]"]').addClass('is-invalid');
            });
          }
      });
    }
  });

  });

</script>
<script>
  $(document).ready(function(){
    $(document).keyup(function(e) {
        if (e.keyCode == 13 && !$('#prod_name').is(':focus') && $('.addProductModal').is(":visible") && product_ids_array.length != 0) {
            $(".add_product_to").trigger('click');
        }
    })

  $('#prod_name').keyup(function(e){

    var page = "draft_Po";

    var query = $.trim($(this).val());
    var supplier_id = $("#selected_supplier_id").val();
    var warehouse_id = $(".warehouse_id").val();
    var draft_po_id = $("#draft_po_id").val();
    if(query == '' || e.keyCode == 8 || 'keyup' )
    {
      $('#product_name_div').empty();
    }
    if(e.keyCode == 13)
    {
      if(query.length > 2)
      {
        var _token = $('input[name="_token"]').val();
        $.ajax({
          url:"{{ route('autocomplete-fetch-product') }}",
          // url:"{{ route('autocomplete-fetching-products') }}",
          method:"POST",
          data:{query:query, _token:_token, inv_id:draft_po_id, supplier_id:supplier_id, warehouse_id:warehouse_id, page:page},
          beforeSend: function(){
          $('#product_name_div').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
          },
          success:function(data){
            $('#product_name_div').fadeIn();
            $('#product_name_div').html(data);
          }
       });
      }
      else
      {
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
  });

  $(document).on('click', 'li', function(){
    $('#prod_name').val("");
    // $('#product_name_div').fadeOut();
  });

  });

  $(document).on('click','.close-modal, #uploadExcelbtn',function(){
        product_ids_array = [];
        $("#tags_div").html("");
        $('#addProductModal').modal('hide');
    });

  $(document).on('click', '.add_product_to', function(e){
    var draft_po_id = $("#draft_po_id").val();
    var prod_ids = product_ids_array;
    $('#product_array').val(prod_ids);
    var input = $('#product_array').val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method:"post",
      data:'selected_products='+input+'&draft_po_id='+draft_po_id+'&purchasing_vat='+"{{in_array(17,$hidden_columns_by_admin)}}",
      url:"{{ route('add-prod-to-draft-po') }}",
      beforeSend: function(){
        $('#addProductModal').modal('hide');
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      success:function(data){
        product_ids_array = [];
        $("#tags_div").html("");
        $('#addProductModal').modal('hide');
        $('.table-ordered-products').DataTable().ajax.reload();
        if(data.success == false)
        {
          $('#loader_modal').modal('hide');
          $('#addProductModal').modal('hide');
          toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
          $('#prod_name').text('');
          $('.table-ordered-products').DataTable().ajax.reload();

        }
        else
        {
          $('#loader_modal').modal('hide');
          $('#addProductModal').modal('hide');
          $('#prod_name').text('');
          $('.table-ordered-products').DataTable().ajax.reload();
          $('.table-purchase-order-history').DataTable().ajax.reload();
          var sub_total_value = data.sub_total.toFixed(3);
          $('.sub-total').html(sub_total_value);
          $('#sub_total').val(sub_total_value);
          var vat_total_amount = data.vat_amout.toFixed(3);
          $('.vat-amount').html(vat_total_amount);
          $('#vat_amount').val(vat_total_amount);
          var amount_with_vat = data.total_w_v.toFixed(3);
          $('.amount-with-vat').html(amount_with_vat);
          $('#amount_with_vat').val(amount_with_vat);
          // $('.total_products').html(data.total_products);
        }
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

  $(document).on('change','.add-supp',function(){
  var supplier_id = $(this).val();
  var sup_or_wh_id = $(this).val().split('-');
  var draft_po_id = '{{$id}}';
  if(sup_or_wh_id[0] == 's')
  {
    var selected_supplier_id = $("#selected_supplier_id").val(sup_or_wh_id[1]);
  }
  if(sup_or_wh_id[0] == 'w')
  {
    var selected_warehouse_id = $("#selected_warehouse_id").val(sup_or_wh_id[1]);
  }

  var _token = $('input[name="_token"]').val();
  $.ajax({
    url:"{{ route('add-supplier-to-draft-po') }}",
    method:"POST",
    data:{_token:_token,supplier_id:supplier_id,draft_po_id:draft_po_id},
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
     $("#loader_modal").modal('show');
      },
    success:function(data){
      $("#loader_modal").modal('hide');
      // if(data.type == "PO")
      // {
      //   $("#po_or_td").html("Draft Purchase Order");
      // }
      // else if(data.type == "TD")
      // {
      //   $("#po_or_td").html("Draft Transfer Document");
      // }
      // console.log(data);
      console.log(data.payment_term);
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

      $('#exchange_rate_span').data('fieldvalue', data.exchange_rate);
      $('#exchange_rate_span').html(data.exchange_rate);
      $('#exchange_rate').val('');

      $('#target_ship_date').data('fieldvalue', '');
      $('#target_receive_date').val('');
      $('#target_ship_date').html('Target Ship Date Here');

      $('#Invoice_date_span').data('fieldvalue', '');
      $('#Invoice_date_span').html('Invoice Date Here');
      $('.invoice_date').val('');

      $('.payment_due_date_term').html('--');

      $('.add-supp').next().addClass('d-none');
      $('.supplier_info').removeClass('d-none');
      $('.supplier_info').html(data.html);
    }
   });
  });

  $(document).on('click','.change_supplier',function(){
  var draft_po_id = '{{$id}}';
  var _token = $('input[name="_token"]').val();
  $.ajax({
    url:"{{ route('check-if-products-exist-on-dpo') }}",
    method:"get",
    dataType: 'json',
    data:{_token:_token,draft_po_id:draft_po_id},
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
     $("#loader_modal").modal('show');
    },
    success:function(data)
    {
     $("#loader_modal").modal('hide');
      if(data.success == false)
      {
        toastr.error('Error!', 'You Cannot Change Supply From Of This Purchase Order, Becuase Its Contain Products Of That Supply From.',{"positionClass": "toast-bottom-right"});
      }
      if(data.success == true)
      {
        $(".state-tags-2").select2();
        $('.supplier_selected_info').addClass('d-none');
        $('.supplier_selected_info').next().removeClass('d-none');
        $('.supplier_info').addClass('d-none');
        // $('.update-supplier-div').html(data.html);
        $('.add-supp').next().removeClass('d-none');
      }
    }
   });
  });

  // document upload
  $('.addDocumentForm').on('submit', function(e){

  e.preventDefault();
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  $.ajax({
    url: "{{ route('add-draft-purchase-order-document') }}",
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
      $('.save-doc-btn').html('Upload');
      $('.save-doc-btn').attr('disabled', true);
      $('.save-doc-btn').removeAttr('disabled');
      if(result.success == true){
        toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
        $('.addDocumentForm')[0].reset();
        // $('.addDocumentModal').modal('hide');
        $('.download-docs').removeClass('d-none');
         $('.collapse').collapse("toggle");
        let sid = $('#sid').val();

        $.ajax({
          type: "post",
          url: "{{ route('get-draft-order-files') }}",
          data: 'order_id='+sid,
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

  $(document).on('keyup','.refrence_number',function(e){
    if(e.keyCode == 13)
    {
      if($(this).val() != '')
      {
      var refrence_number = $(this).val();
      var draft_po_id = $(this).data(draft_po_id);

      var formData = {"refrence_number":refrence_number,"draft_po_id":draft_po_id,"purchasing_vat": "{{in_array(17,$hidden_columns_by_admin)}}"};
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
      $.ajax({
          url: "{{ route('add-prod-by-refrence-number') }}",
          method: 'post',
          data: formData,
          beforeSend: function(){
             $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
          },
          success: function(result){
          $("#loader_modal").modal('hide');
          if(result.success == true)
          {
            toastr.success('Success!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.table-ordered-products').DataTable().ajax.reload();
            var sub_total_value = result.sub_total.toFixed(3);
            $('.sub-total').html(sub_total_value);
            $('#sub_total').val(sub_total_value);
            var vat_total_amount = data.vat_amout.toFixed(3);
            $('.vat-amount').html(vat_total_amount);
            $('#vat_amount').val(vat_total_amount);
            var amount_with_vat = data.total_w_v.toFixed(3);
            $('.amount-with-vat').html(amount_with_vat);
            $('#amount_with_vat').val(amount_with_vat);
            // $('.total_products').html(result.total_products);
            $('.table-purchase-order-history').DataTable().ajax.reload();
          }
          else
          {
            toastr.error('Error!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.table-ordered-products').DataTable().ajax.reload();
            $('.table-purchase-order-history').DataTable().ajax.reload();
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

  // double click editable
  $(document).on("click",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
    var num = $(this).next().val();
    $(this).next().focus().val('').val(num);
    if($(this).next().attr('name') == 'discount')
    {
      // $(this).next().addClass('d-inline-block');
      // $(this).next().attr('min','0');
      // $(this).next().attr('max','99999');
    }
  });

  $(document).on("keyup focusout",".fieldFocus",function(e) {
      var draft_po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();

      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        // $(this).prev().html(fieldvalue);
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

      if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

      if(attr_name == 'note')
      {
        var new_value = $(this).val();
        if($(this).val().length < 1)
        {
          return false;
        }
        else if($(this).val() == '')
        {
          $(this).prev().html("Click here to add a note!!!");
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
      if(attr_name == 'memo')
      {
        if($(this).val() == '')
        {
          $(this).prev().html("Memo Here");
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
      if(attr_name == 'exchange_rate')
      {
        if($(this).val() == '')
        {
          $(this).prev().html("Invoice Exchange Rate Here");
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
      url: "{{ route('save-draft-po-dates') }}",
      dataType: 'json',
      data: 'draft_po_id='+draft_po_id+'&'+attr_name+'='+encodeURIComponent($(this).val()),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        if(data.success == true && data.draft_note == false)
        {
          toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
          if(attr_name == 'invoice_date')
          {
            if(data.draft_po.payment_due_date != null)
            {
              // $('.payment_due_date_term').html(data.draft_po.payment_due_date);
              // var date = new Date(data.draft_po.payment_due_date);
              // var newDate = date.toString('dd/mm/yy');
              // $('.payment_due_date_term').html(newDate);
              var newDate = $.datepicker.formatDate( "dd/mm/yy", new Date(data.draft_po.payment_due_date));
              $('.payment_due_date_term').html(newDate);
            }
          }
        }
        if(data.draft_note == true)
        {
          toastr.success('Success!', 'Note Added/Updated Successfully.',{"positionClass": "toast-bottom-right"});
        }
      }
    });
  }
  });

  $(document).on('click', '.draf_po_delete', function(){
    var id = $(this).data('id');
    var draft_po_id = '{{$id}}';
    swal({
        title: "Alert!",
        text: "Are you sure you want to delete this note?",
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
          data:{id:id, draft_po_id:draft_po_id},
          url:"{{ route('delete-draft-po-note') }}",
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
              toastr.success('Success!', 'Note Deleted Successfully.',{"positionClass": "toast-bottom-right"});
              $("#notes-modal").modal('hide');
              $('.table-ordered-products').DataTable().ajax.reload();
            }
          }
         });
      }
      else{
          swal("Cancelled", "", "error");
      }
     });
    });

  $(document).on('click', '.deleteProd', function(){
    var id = $(this).data('id');
    var draft_po_id = '{{$id}}';
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
          data:{id:id, draft_po_id:draft_po_id},
          url:"{{ route('remove-draft-po-product') }}",
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
                toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                $('.table-ordered-products').DataTable().ajax.reload();
                $('.table-purchase-order-history').DataTable().ajax.reload();
                var sub_total = data.sub_total.toFixed(3);
                $('.sub-total').html(sub_total);
                $('#sub_total').val(sub_total);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);
                $('#vat_amount').val(vat_total_amount);
                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);
                $('#amount_with_vat').val(amount_with_vat);
                $('.total_products').html(data.total_products);
              }
          }
         });
      }
      else{
          swal("Cancelled", "", "error");
      }
     });
    });

  $(document).on("click",".inputDoubleClickQuantity",function(){
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
    }, 300);
  });

  $(document).on('keyup focusout', 'input[type=text]', function(e){
    if($(this).attr('name') == 'target_receive_date' || $(this).attr('name') == 'invoice_date' || $(this).attr('name') == 'payment_due_date')
    {
      return false;
    }
    var fieldvalue = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
      thisPointer.removeClass('active');
    }

    if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
      var fieldvalue = $(this).prev().data('fieldvalue');
      if($(this).val() == fieldvalue)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.prev().removeClass('d-none');
        thisPointer.removeClass('active');
      }

      var po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      var rowId = $(this).parents('tr').attr('id');

      if($(this).attr('name') == 'billed_desc')
      {
        if($(this).val() == null)
        {
          return false;
        }
        else if($(this).val() !== '' && $(this).hasClass('active'))
        {
          var old_value = $(this).prev().html();

          $(this).prev().html($(this).val());
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');

          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });

          $.ajax({
            type: "post",
            url: "{{ route('save-dpo-product-desc') }}",
            dataType: 'json',
            data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+encodeURIComponent($(this).val())+'&'+'old_value='+old_value,
            beforeSend: function(){
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(data){
              $('#loader_modal').modal('hide');
              if(data.success == true)
              {
                toastr.success('Success!', 'Billed Description Update Successfully.',{"positionClass": "toast-bottom-right"});
                $('.table-ordered-products').DataTable().ajax.reload();
              }

            }
          });
        }
      }
    }
  });

  $(document).on('change', 'select.select-common', function(){

    if($(this).attr('name') == "warehouse_id")
    {

    var warehouse_id = $(this).val();
    var draft_po_id = '{{$id}}';

    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method: "post",
      url: "{{ url('warehouse-save-in-draft-po') }}",
      dataType: 'json',
      context: this,
      data: {warehouse_id:warehouse_id, draft_po_id:draft_po_id},
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
          // toastr.success('Success!', 'Warehouse Assigned Successfully.' ,{"positionClass": "toast-bottom-right"});
          $(this).prev().html($("option:selected", this).html());
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
        }
      },
    });

    }

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
      var draft_po_id = '{{$id}}';

      $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });

      $.ajax({
        method: "post",
        url: "{{ url('payment-term-save-in-dpo') }}",
        dataType: 'json',
        context: this,
        data: {payment_terms_id:payment_terms_id, draft_po_id:draft_po_id},
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
              // var date = new Date(data.payment_due_date);
              // var newDate = date.toString('dd/mm/yy');
              // $('.payment_due_date_term').html(newDate);
              var newDate = $.datepicker.formatDate( "dd/mm/yy", new Date(data.payment_due_date));
              $('.payment_due_date_term').html(newDate);
            }
            $(this).prev().html($("option:selected", this).html());
            $(this).removeClass('active');
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
          }
        },
      });


    }

  });

  $(document).on('keyup focusout', 'input[type=number]', function(e){
    var fieldvalue = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      // thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
      thisPointer.removeClass('active');
    }

    if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

      if($(this).val() == fieldvalue) {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.prev().removeClass('d-none');
        thisPointer.removeClass('active');
      }

      var draft_po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      var rowId = $(this).parents('tr').attr('id');

    if($(this).attr('name') == 'quantity')
    {
      var old_value = $(this).prev().html();
      if($(this).val() < 0 || $(this).val() == null)
      {
        swal({ html:true, title:'Alert !!!', text:'<b>QTY cannot be 0 or less then 0 !!!</b>'});
        return false;
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('save-draft-po-product-quantity') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'QTY Updated Successfully.',{"positionClass": "toast-bottom-right"});
              // $('.table-ordered-products').DataTable().ajax.reload();
              // $('.table-purchase-order-history').DataTable().ajax.reload();
              var sub_total_value = data.sub_total.toFixed(3);
              var total_qty       = data.total_qty.toFixed(3);
              $('.total-qty').html(total_qty);
              $('.sub-total').html(sub_total_value);
              $('#sub_total').val(sub_total_value);
              var vat_total_amount = data.vat_amout.toFixed(3);
              $('.vat-amount').html(vat_total_amount);
              $('#vat_amount').val(vat_total_amount);
              var amount_with_vat = data.total_w_v.toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);
              $('#amount_with_vat').val(amount_with_vat);

              $('.amount_'+data.id).html(data.total_amount_wo_vat);
              $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

              $('.unit_price_span_'+data.id).html(data.unit_price);
              $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
              $('.unit_price_field_'+data.id).val(data.unit_price);
              $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
              $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
              $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

              $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
              $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
              $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

              $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
              $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

              $('.quantity_span_'+data.id).html(data.quantity);
              $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
              $('.quantity_field_'+data.id).val(data.quantity);

              $('.desired_qty_span_'+data.id).html(data.desired_qty);
              $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
              $('.desired_qty_field_'+data.id).val(data.desired_qty);

              // $('.entriestable').css("table-layout","fixed");
            }
          }
        });
      }
    }

    if($(this).attr('name') == 'pod_vat_actual')
    {
      var old_value = $(this).prev().html();
      // if($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null)
      // {
      //   swal({ html:true, title:'Alert !!!', text:'<b>QTY cannot be 0 or less then 0 !!!</b>'});
      //   return false;
      // }
      if($(this).val() !== '' && $(this).hasClass('active'))
      {
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('save-draft-po-pod-vat-actual-quantity') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Purchasing Vat Updated Successfully.',{"positionClass": "toast-bottom-right"});
              // $('.table-ordered-products').DataTable().ajax.reload();
              // $('.table-purchase-order-history').DataTable().ajax.reload();
              var sub_total_value = data.sub_total.toFixed(3);
              var total_qty       = data.total_qty.toFixed(3);
              $('.total-qty').html(total_qty);
              $('.sub-total').html(sub_total_value);
              $('#sub_total').val(sub_total_value);
              var vat_total_amount = data.vat_amout.toFixed(3);
              $('.vat-amount').html(vat_total_amount);
              $('#vat_amount').val(vat_total_amount);
              var amount_with_vat = data.total_w_v.toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);
              $('#amount_with_vat').val(amount_with_vat);

              $('.amount_'+data.id).html(data.total_amount_wo_vat);
              $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

              $('.unit_price_span_'+data.id).html(data.unit_price);
              $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
              $('.unit_price_field_'+data.id).val(data.unit_price);
              $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
              $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
              $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

              $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
              $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
              $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

              $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
              $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

              $('.quantity_span_'+data.id).html(data.quantity);
              $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
              $('.quantity_field_'+data.id).val(data.quantity);

              $('.desired_qty_span_'+data.id).html(data.desired_qty);
              $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
              $('.desired_qty_field_'+data.id).val(data.desired_qty);

              // $('.entriestable').css("table-layout","fixed");
            }
          }
        });
      }
    }

    if($(this).attr('name') == 'billed_unit_per_package')
    {
      if ($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null) {
					swal({
						html: true,
						title: 'Alert !!!',
						text: '<b>Avg Order Qty cannot be 0 or less then 0 !!!</b>'
					});
					return false;
				}
        if ($(this).val() !== '' && $(this).hasClass('active')) {
          var old_value = $(this).prev().html();

					$(this).prev().html($(this).val());
					$(this).removeClass('active');
					$(this).addClass('d-none');
					$(this).prev().removeClass('d-none');

          $.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
						}
					});

          $.ajax({
            type: "post",
						url: "{{ route('update-draft-po-billed-unit-per-package') }}",
						dataType: 'json',
						data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
            beforeSend: function(){
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('hide');
            },
            success: function(data) {
							$('#loader_modal').modal('hide');
							if (data.success == true) {
								toastr.success('Success!', 'Avg Order Qty Updated Successfully.', {
									"positionClass": "toast-bottom-right"
								});
                // $('.table-ordered-products').DataTable().ajax.reload();
                // $('.table-purchase-order-history').DataTable().ajax.reload();
                var total_qty = data.total_qty.toFixed(3);
                var sub_total_value = data.sub_total.toFixed(3);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);
                $('#vat_amount').val(vat_total_amount);
                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);
                $('#amount_with_vat').val(amount_with_vat);

                $('.amount_'+data.id).html(data.total_amount_wo_vat);
                $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

                $('.unit_price_span_'+data.id).html(data.unit_price);
                $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
                $('.unit_price_field_'+data.id).val(data.unit_price);
                $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
                $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
                $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

                $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
                $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
                $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

                $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
                $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

                $('.quantity_span_'+data.id).html(data.quantity);
                $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
                $('.quantity_field_'+data.id).val(data.quantity);

                $('.desired_qty_span_'+data.id).html(data.desired_qty);
                $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
                $('.desired_qty_field_'+data.id).val(data.desired_qty);

                // $('.entriestable').css("table-layout","fixed");
							}

						}
          });
        }
    }

    if($(this).attr('name') == 'desired_qty')
    {
      if ($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null) {
          swal({
            html: true,
            title: 'Alert !!!',
            text: '<b>Order Qty cannot be 0 or less then 0 !!!</b>'
          });
          return false;
        }
      if ($(this).val() !== '' && $(this).hasClass('active'))
      {
        var old_value = $(this).prev().html();

					$(this).prev().html($(this).val());
					$(this).removeClass('active');
					$(this).addClass('d-none');
					$(this).prev().removeClass('d-none');
          $.ajaxSetup({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
						}
					});
          $.ajax({
						type: "post",
						url: "{{ route('update-draft-po-desired-qty') }}",
						dataType: 'json',
						data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
						beforeSend: function() {
              // $('#loader_modal').modal({
              // backdrop: 'static',
              // keyboard: false
              // });
              // $('#loader_modal').modal('show');
						},
						success: function(data) {

              $('#loader_modal').modal('hide');
              if(data.success == true)
              {
                toastr.success('Success!', 'Desired Qty Updated Successfully.',{"positionClass": "toast-bottom-right"});
                // $('.table-ordered-products').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(3);
                var total_qty = data.total_qty.toFixed(3);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);
                $('#vat_amount').val(vat_total_amount);
                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);
                $('#amount_with_vat').val(amount_with_vat);

                $('.amount_'+data.id).html(data.total_amount_wo_vat);
                $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

                $('.unit_price_span_'+data.id).html(data.unit_price);
                $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
                $('.unit_price_field_'+data.id).val(data.unit_price);
                $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
                $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
                $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

                $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
                $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
                $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

                $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
                $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

                $('.quantity_span_'+data.id).html(data.quantity);
                $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
                $('.quantity_field_'+data.id).val(data.quantity);

                $('.desired_qty_span_'+data.id).html(data.desired_qty);
                $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
                $('.desired_qty_field_'+data.id).val(data.desired_qty);

                // $('.entriestable').css("table-layout","fixed");
              }
							$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
							// $('.table-purchase-order-history').DataTable().ajax.reload();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

						}
					});
      }
    }

    //Discount
    if($(this).attr('name') == 'discount')
    {
      var total = $('#sub_total').val();
      var old_value =$(this).prev().html();
      // alert(old_value);
      // return false;

      if($(this).hasClass('active'))
      {
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('save-draft-po-product-discount') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'total='+total+'&'+'old_value='+old_value,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Discount Updated Successfully.',{"positionClass": "toast-bottom-right"});
              $('.table-ordered-products').DataTable().ajax.reload();
              $('.table-purchase-order-history').DataTable().ajax.reload();
              var sub_total_value = data.sub_total.toFixed(3);
              $('.sub-total').html(sub_total_value);
              $('#sub_total').val(sub_total_value);
              var vat_total_amount = data.vat_amout.toFixed(3);
              $('.vat-amount').html(vat_total_amount);
              $('#vat_amount').val(vat_total_amount);
              var amount_with_vat = data.total_w_v.toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);
              $('#amount_with_vat').val(amount_with_vat);

              $('.amount_'+data.id).html(data.total_amount_wo_vat);
              $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

              $('.unit_price_span_'+data.id).html(data.unit_price);
              $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
              $('.unit_price_field_'+data.id).val(data.unit_price);
              $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
              $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
              $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

              $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
              $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
              $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

              $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
              $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

              $('.quantity_span_'+data.id).html(data.quantity);
              $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
              $('.quantity_field_'+data.id).val(data.quantity);

              $('.desired_qty_span_'+data.id).html(data.desired_qty);
              $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
              $('.desired_qty_field_'+data.id).val(data.desired_qty);

                // $('.entriestable').css("table-layout","fixed");
            }
          },
          error: function (request, status, error) {
            $('#loader_modal').modal('hide');
          }
        });
      }
    }

    // unit price
    if($(this).attr('name') == 'unit_price')
    {
      if($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null)
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Unit Price cannot be 0 or less then 0 !!!</b>'});
        return false;
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var old_value = $(this).prev().html();

        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('update-draft-po-unit-price') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Unit Price Updated Successfully.',{"positionClass": "toast-bottom-right"});
              // $('.table-ordered-products').DataTable().ajax.reload();
              // $('.table-purchase-order-history').DataTable().ajax.reload();
              var sub_total_value = data.sub_total.toFixed(3);
              $('.sub-total').html(sub_total_value);
              $('#sub_total').val(sub_total_value);
              var vat_total_amount = data.vat_amout.toFixed(3);
              $('.vat-amount').html(vat_total_amount);
              $('#vat_amount').val(vat_total_amount);
              var amount_with_vat = data.total_w_v.toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);
              $('#amount_with_vat').val(amount_with_vat);

              $('.amount_'+data.id).html(data.total_amount_wo_vat);
              $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

              $('.unit_price_span_'+data.id).html(data.unit_price);
              $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
              $('.unit_price_field_'+data.id).val(data.unit_price);
              $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
              $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
              $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

              $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
              $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
              $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

              $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
              $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

              $('.quantity_span_'+data.id).html(data.quantity);
              $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
              $('.quantity_field_'+data.id).val(data.quantity);

              $('.desired_qty_span_'+data.id).html(data.desired_qty);
              $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
              $('.desired_qty_field_'+data.id).val(data.desired_qty);

              // $('.entriestable').css("table-layout","fixed");
            }
          }
        });
      }
    }

    // unit price with vat
    if($(this).attr('name') == 'pod_unit_price_with_vat')
    {
      if($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null)
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Unit Price(+Vat) cannot be 0 or less then 0 !!!</b>'});
        return false;
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var old_value = $(this).prev().html();
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });

        $.ajax({
          type: "post",
          url: "{{ route('update-draft-po-unit-price-vat') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
              toastr.success('Success!', 'Unit Price Updated Successfully.',{"positionClass": "toast-bottom-right"});
              // $('.table-ordered-products').DataTable().ajax.reload();
              // $('.table-purchase-order-history').DataTable().ajax.reload();
              var sub_total_value = data.sub_total.toFixed(3);
              $('.sub-total').html(sub_total_value);
              $('#sub_total').val(sub_total_value);
              var vat_total_amount = data.vat_amout.toFixed(3);
              $('.vat-amount').html(vat_total_amount);
              $('#vat_amount').val(vat_total_amount);
              var amount_with_vat = data.total_w_v.toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);
              $('#amount_with_vat').val(amount_with_vat);

              $('.amount_'+data.id).html(data.total_amount_wo_vat);
              $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

              $('.unit_price_span_'+data.id).html(data.unit_price);
              $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
              $('.unit_price_field_'+data.id).val(data.unit_price);
              $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
              $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
              $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

              $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
              $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
              $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

              $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
              $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

              $('.quantity_span_'+data.id).html(data.quantity);
              $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
              $('.quantity_field_'+data.id).val(data.quantity);

              $('.desired_qty_span_'+data.id).html(data.desired_qty);
              $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
              $('.desired_qty_field_'+data.id).val(data.desired_qty);

              // $('.entriestable').css("table-layout","fixed");
            }
          }
        });
      }
    }

    // unit gross weight
    if($(this).attr('name') == 'pod_gross_weight')
    {
      if($(this).val() == null)
      {
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().html('--');
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        var old_value = $(this).prev().html();
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('update-draft-po-unit-gross-weight') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value'+'='+old_value,
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('hide');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
                toastr.success('Success!', 'Gross Weight Updated Successfully.',{"positionClass": "toast-bottom-right"});
                // $('.table-ordered-products').DataTable().ajax.reload();
                // $('.table-purchase-order-history').DataTable().ajax.reload();

                $('.amount_'+data.id).html(data.total_amount_wo_vat);
                $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);

                $('.unit_price_span_'+data.id).html(data.unit_price);
                $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
                $('.unit_price_field_'+data.id).val(data.unit_price);
                $('.unit_price_with_vat_span_'+data.id).html(data.unit_price_w_vat);
                $('.unit_price_with_vat_span_'+data.id).attr('data-fieldvalue',data.unit_price_w_vat);
                $('.unit_price_with_vat_field_'+data.id).val(data.unit_price_w_vat);

                $('.unit_gross_weight_'+data.id).html(data.unit_gross_weight);
                $('.unit_gross_weight_'+data.id).attr('data-fieldvalue',data.unit_gross_weight);
                $('.unit_gross_weight_field_'+data.id).val(data.unit_gross_weight);

                $('.total_gross_weight_'+data.id).html(data.total_gross_weight);
                $('.total_gross_weight_'+data.id).attr('data-fieldvalue',data.total_gross_weight);

                $('.quantity_span_'+data.id).html(data.quantity);
                $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
                $('.quantity_field_'+data.id).val(data.quantity);

                $('.desired_qty_span_'+data.id).html(data.desired_qty);
                $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
                $('.desired_qty_field_'+data.id).val(data.desired_qty);

                // $('.entriestable').css("table-layout","fixed");

            }
          }
        });
      }
    }

   }

  });

  $(document).on('click', '.download-documents', function(e){

    let sid = $(this).data('id');
    // alert(sid);
    // console.log(sid);
     $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
    $.ajax({
      type: "post",
      url: "{{ route('get-draft-order-files') }}",
      data: 'order_id='+sid,
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
                url:"{{ route('remove-draft-order-file') }}",
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
          else{
              swal("Cancelled", "", "error");
          }
     });
  });

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc

      $("#target_receive_date").datepicker('hide');
      $("#invoice_date").datepicker('hide');
      $("#payment_due_date").datepicker('hide');

      if($('.inputDoubleClick').hasClass('d-none'))
      {
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

  $(document).on('click','#addItem',function(){
    var id = $(this).data('id');
    var supplier_id = $("#selected_supplier_id").val();
    if(supplier_id == '')
    {
       swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
    }
    else
    {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('add-billed-item-in-dpo') }}",
        method: 'post',
        data:{id:id, supplier_id:supplier_id},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('#loader_modal').modal('hide');
          if(result.success == true)
          {
            toastr.success('Success!', 'Billed Item Added Successfully',{"positionClass": "toast-bottom-right"});
            $('.table-ordered-products').DataTable().ajax.reload();
          }
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
       var url = "{{ url('/') }}";
       document.location.href = url;
     }
   }
</script>
<script>
  // export pdf code
  $(document).on('click', '.export-pdf', function(e) {
    var po_id = $('#po_id_for_pdf').val();
    // $('.export-po-form').submit();
    getDraftPoPrint();
  });

  $(document).on('click', '.export-pf-pdf', function(e) {
    var po_id = $('#po_id_for_pdf').val();
    $('#pf_logo').val(1);
    // $('.export-po-form').submit();
    getDraftPoPrint();
    $('#pf_logo').val("");
  });

   $('#show_price').on('change', function() {
    var checked = $(this).prop('checked');
    if (checked == true) {
      $('#show_price_input').val('1');
    } else if (checked == false) {
      $('#show_price_input').val('0');
    }
  });
   function getDraftPoPrint() {
    var id = $('#po_id_for_pdf').val();
    var show_price_input = $('#show_price_input').val();
    var pf_logo = $('#pf_logo').val();
    var column = $('#column_name').val();
    column = (column != null && column != '') ? column : 0;
    var sort = $('#sort_order').val();
    sort = (sort != null && sort != '') ? sort : 0;

     var url = "{{url('export-draft-po-to-pdf')}}"+"/"+id+"/"+show_price_input+"/"+column+"/"+sort+"/"+pf_logo;
     window.open(url, 'Draft Po Print', 'width=1200,height=600,scrollbars=yes');
   }
</script>
<script src="{{ asset('public\site\assets\backend\js\excel-export-ajax.js') }}"></script>
@stop
