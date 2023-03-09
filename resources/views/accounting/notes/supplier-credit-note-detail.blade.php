@extends('users.layouts.layout')

@section('title','Credit Note Detail | Accounting')

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

  .disabled:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .selectDoubleClick,
  .inputDoubleClick {
    font-style: italic;
  }

  .inputDoubleClickQuantity {
    font-style: italic;
    font-weight: bold;
  }

  input[type="checkbox"] {
    cursor: pointer;
    -webkit-appearance: block;
    appearance: block;
    background: white;
    border-radius: 1px;
    box-sizing: border-box;
    position: relative;
    box-sizing: content-box;
    width: 14px;
    height: 14px;
    border-width: 0;
    transition: all .3s linear;
  }

  input[type="checkbox"]:checked {
    background-color: #2ECC71;
  }

  input[type="checkbox"]:focus {
    outline: 0 none;
    box-shadow: none;
  }

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

  table.dataTable {
    width: 100% !important;
    margin: 0 auto;
    clear: both;
    border-collapse: separate;
    border-spacing: 0;
    position: relative !important;
  }
  .importing-clear-filter
  {
    background: #13436c;
    color: white;
    padding: 5px;
    border-radius: 50%;
    font-size: 12px !important;
    margin-left: 10px;
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
          <li class="breadcrumb-item active">Credit Note Detail</li>
      </ol>
  </div>
</div>

@php
use Carbon\Carbon;
@endphp

{{-- Content Start from here --}}

<!-- NEW Design -->

<!-- Right Content Start Here -->
<div class="right-content">
<div class="row mb-0">
  <div class="col-12">
    <div class="pull-right">
      <a class="col-4 px-0 text-right">

          <button type="button" title="Back" onclick="backFunctionality()" class="btn-color btn text-uppercase purch-btn headings-color mr-2 d-none">back</button>

          <!-- <span class="vertical-icons" title="Back" onclick="backFunctionality()">
            <img src="{{asset('public/icons/back.png')}}" width="27px">
          </span> -->

        </a>
    </div>
  </div>
</div>
  <div class="row mb-0 headings-color mb-1">
    <div class="col-lg-8 col-md-6">
      <h3 class="maintitle text-uppercase fontbold">Credit Note <span id="po-no-id">{{$getPurchaseOrder->p_o_statuses->parent->prefix . $getPurchaseOrder->ref_id}}</span></h3>
    </div>

    <div class="col-lg-4 col-md-6">
      <div class="row mx-0 align-items-center">
        <h3 class="maintitle text-uppercase fontbold col-8 px-0">Choose Supplier</h3>

      </div>
    </div>
  </div>

  <div class="row">

    <input type="hidden" name="po_id" id="po_id" value="{{$id}}">

    <div class="col-lg-8 col-md-6">
      @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . $company_info->logo))
      <img src="{{asset('public/uploads/logo/'.$company_info->logo)}}" class="img-fluid" style="height: 80px;" align="big-qummy">
      @else
      <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="height: 80px;" align="big-qummy">
      @endif
      <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p>
      <p class="mb-1">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
      <p class="mb-1"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}} <em class="fa fa-envelope"></em> {{@$company_info->billing_email}}</p>
      {{--<p class="mb-1">AWB or B/L</p>
    <p class="mb-1">Exp. Arrival Date</p>--}}
      <p class="mb-1">Status : {{@$getPurchaseOrder->p_o_statuses->title}}</p>
      <p class="mb-1">Created Date : {{@$getPurchaseOrder->created_at->format('d/m/Y')}}</p>
      @if(@$getPurchaseOrder->status == 14)
      <p class="mb-1">Confirm Date : {{Carbon::parse(@$getPurchaseOrder->confirm_date)->format('d/m/Y')}}</p>
      @endif

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
      {{-- <div class="d-flex align-items-center mb-1"> --}}
        <div>

    @if (@$getPurchaseOrder->supplier_id == null && Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11)

    <select class="form-control js-states state-tags mb-2 add-supplier" name="supplier" data-id="{{@$getPurchaseOrder->id}}">
        <option value="">Choose Supplier</option>
          @foreach($suppliers as $supplier)
          <option value="{{ $supplier->id }}" {{ ($getPurchaseOrder->supplier_id == $supplier->id) ? "selected='true'":" " }}> @if($supplier->reference_name != null) {{$supplier->reference_name}} @else {{ $supplier->first_name.' '.$supplier->last_name }} @endif</option>
         @endforeach
       </select>

    @endif
    @if(@$getPurchaseOrder->supplier_id != null)
     @if(@$getPurchaseOrder->PoSupplier->logo != null && file_exists( public_path() . '/uploads/sales/customer/logos/'.@$getPurchaseOrder->PoSupplier->logo))
        <img src="{{asset('public/uploads/sales/customer/logos/'.@$getPurchaseOrder->PoSupplier->logo)}}" class="img-fluid" style="width: 85px;height: 75px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 85px;height: 75px;" align="big-qummy">
        @endif
        @endif
  </div>
  @if(@$getPurchaseOrder->supplier_id != null)
  <div class="pl-2 comp-name" data-supplier-id="{{$getPurchaseOrder->supplier_id}}"><p>{{ $getPurchaseOrder->PoSupplier->reference_name }}</p> </div>

  <p class="mb-1">@if($getPurchaseOrder->PoSupplier->address_line_1 !== null) {{ $getPurchaseOrder->PoSupplier->address_line_1.' '.$getPurchaseOrder->PoSupplier->address_line_2 }}, @endif  @if($getPurchaseOrder->PoSupplier->country !== null) {{ @$getPurchaseOrder->PoSupplier->getcountry->name }}, @endif @if($getPurchaseOrder->PoSupplier->state !== null) {{ @$getPurchaseOrder->PoSupplier->getstate->name }}, @endif @if($getPurchaseOrder->PoSupplier->city !== null) {{ $getPurchaseOrder->PoSupplier->city }}, @endif @if($getPurchaseOrder->PoSupplier->postalcode !== null) {{ $getPurchaseOrder->PoSupplier->postalcode }} @endif</p>

  @if($getPurchaseOrder->PoSupplier->email !== null || $getPurchaseOrder->PoSupplier->phone !== null)
  <ul class="d-flex list-unstyled">
    @if($getPurchaseOrder->PoSupplier->phone !== null)
    <li><i class="fa fa-phone pr-2"></i>
      {{ $getPurchaseOrder->PoSupplier->phone }}
    </li>
    @endif
    @if($getPurchaseOrder->PoSupplier->phone !== null)
    <li class="pl-3"><i class="fa fa-envelope pr-2"></i>
    {{ $getPurchaseOrder->PoSupplier->email }}
    </li>
    @endif
  </ul>
  @endif
  @endif

  @php
    $status = $getPurchaseOrder->status;
    if($status == 12 || $status == 14 || $status == 13 || $status == 15)
    {
      $classs = 'inputDoubleClick';
    }
    else
    {
      $classs = 'inputDoubleClick';
    }
  @endphp

  @php
    $status = $getPurchaseOrder->status;
    if($status <= 14)
    {
      $classss = 'inputDoubleClick';
    }
    else
    {
      $classss = 'inputDoubleClick';
    }
  @endphp

  @php
    $invoice_date = $getPurchaseOrder->invoice_date != null ? Carbon::parse($getPurchaseOrder->invoice_date)->format('d/m/Y') : "" ;
  @endphp
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class="fontbold" style="width: 180px;">Credit Note Date: <b style="color: red;">*</b></li>
    <span class="pl-4 {{$classs}}" data-fieldvalue="{{$invoice_date}}" data-spname="invoice_date">
      @if($getPurchaseOrder->invoice_date != null)
      {{Carbon::parse($getPurchaseOrder->invoice_date)->format('d/m/Y')}}
      @else
      Credit Note Date Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none invoice_date" name="invoice_date" id="invoice_date" value="{{$invoice_date}}">
  </ul>


  <ul class="d-flex mb-2 pt-2 list-unstyled">
    <li class="fontbold" style="width: 180px;">Memo:</li>
    <span class="pl-4 pt-2 inputDoubleClick" data-fieldvalue="{{@$getPurchaseOrder->memo}}" data-spname="memo">
      @if($getPurchaseOrder->memo != null)
      {{$getPurchaseOrder->memo}}
      @else
      Memo Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$getPurchaseOrder->memo}}">
  </ul>

  </div>

  <!-- export pdf form starts -->
  <form class="export-po-form" method="post" action="{{url('export-po-to-pdf/'.$id)}}">
    @csrf
    <input type="hidden" name="po_id_for_pdf" id="po_id_for_pdf" value="{{$id}}">
    <input type="hidden" name="show_price_input" id="show_price_input" value="1">
    <input type="hidden" name="pf_logo" id="pf_logo" value="">
    <input type="hidden" name="sort_order" id="sort_order">
    <input type="hidden" name="column_name" id="column_name">
  </form>
  <!-- export pdf form ends -->

 <div class="col-lg-12 text-uppercase fontbold mt-4">


  {{--@if($getPurchaseOrderDetail->count() <= 15)--}}
  <a href="javascript:void(0);">
    <!-- <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf mr-3">
    print</button> -->
     <span class="export-pdf vertical-icons ml-3" title="Print">
          <img src="{{asset('public/icons/print.png')}}" width="27px">
      </span>
  </a>

  {{-- <a href="javascript:void(0);">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pf-pdf mr-3">PF logo</button>
  </a> --}}

  {{-- <a href="javascript:void(0)">
    <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color" style="background-color: transparent;border: none;color: black;"><input type="checkbox" name="show_price" id="show_price" checked="true" style="vertical-align: inherit;scale"> &nbsp;Show Prices</button>
  </a> --}}
  {{--@endif--}}

  <div class="pull-right">
    {{-- <span class="vertical-icons mr-4 @if($getPurchaseOrder->status != 12) d-none @endif" title="Download Sample File" id="example_export_btn">
      <img src="{{asset('public/icons/sample_export_icon.png')}}" width="27px">
    </span> --}}
  <!--   <button type="button" class="btn text-uppercase purch-btn headings-color btn-color mr-3" data-toggle="modal" data-target="#import-modal">Bulk Import</button> -->
    {{-- <span class="vertical-icons export_btn" title="Export">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span> --}}

    {{-- @if($getPurchaseOrder->status == 12)
    <span class="vertical-icons" title="Bulk Import" data-toggle="modal" data-target="#import-modal"  @if($getPurchaseOrder->status == 15) style="pointer-events: none; cursor: default;" @endif>
      <img src="{{asset('public/icons/bulk_import.png')}}" width="27px">
    </span>
    @endif --}}
    <!-- <button type="button" class="btn text-uppercase purch-btn headings-color btn-color mr-3 export_btn">Export</button> -->

    @if($checkPoDocs > 0)
      @php $show = ""; @endphp
    @else
      @php $show = "d-none"; @endphp
    @endif
    <a href="javascript:void(0)" data-id="{{$getPurchaseOrder->id}}" data-toggle="modal" data-target="#file-modal" class="download-documents d-none"><button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">View documents<i class="pl-1 fa fa-download"></i></button></a>

    <a href="{{ url('getting-docs-for-download/'.$id) }}" class="d-none">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
    </a>

   <!--  <a href="javascript:void(0);">
      <button type="button" data-toggle="modal" data-target="#addDocumentModal" class="btn-color btn text-uppercase purch-btn headings-color">upload documents<i class="pl-1 fa fa-upload"></i></button>
    </a> -->

<!--         <a href="javascript:void(0);" data-id="{{$getPurchaseOrder->id}}" class="download-documents">
          <button type="button" class="btn text-uppercase purch-btn headings-color btn-color" data-toggle="modal" data-target="#addDocumentModal">upload document<i class="pl-1 fa fa-arrow-up"></i>
          </button>
        </a> -->

        <span class="vertical-icons download-documents" title="Upload Documents" data-id="{{$getPurchaseOrder->id}}" data-toggle="modal" data-target="#addDocumentModal">
          <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
      </span>
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
              <input type="hidden" name="purchase_order_id" id="sid" value="{{$id}}">

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

<div class="col-lg-12 mt-3">
  <div class="bg-white table-responsive pb-5 custompadding">
    <table class="table entriestable po-porducts-details text-center table-bordered" style="width:100%">
      <thead class="sales-coordinator-thead headings-color table-bordered">
        <tr>
          <th @if(in_array(0,$hidden_columns_by_admin)) class="noVis" @endif>Action</th>
          <th @if(in_array(1,$hidden_columns_by_admin)) class="noVis" @endif>Notes</th>
          <th @if(in_array(2,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['suppliers_product_reference_no']}} </th>
          <th @if(in_array(3,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['our_reference_number']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(4,$hidden_columns_by_admin)) class="noVis" @endif>Customer <br>Reference <br> Name</th>
          <th @if(in_array(5,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['brand']}}</th>
          <th @if(in_array(6,$hidden_columns_by_admin)) class="noVis" @endif> {{$global_terminologies['product_description']}}</th>
          <th @if(in_array(7,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['type']}}</th>
          <th @if(in_array(8,$hidden_columns_by_admin)) class="noVis" @endif> {{$global_terminologies['supplier_description']}}
           <!--  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_description">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_description">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
          </th>
          <th @if(in_array(9,$hidden_columns_by_admin)) class="noVis" @endif> {{$global_terminologies['expected_lead_time_in_days']}}</th>
          <th @if(in_array(10,$hidden_columns_by_admin)) class="noVis" @endif>Gross Weight
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="gross_weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="gross_weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(11,$hidden_columns_by_admin)) class="noVis" @endif>Order <br> {{$global_terminologies['qty']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(12,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['order_qty_unit']}}</th>
          <th @if(in_array(13,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['qty']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(14,$hidden_columns_by_admin)) class="noVis" @endif>PCS</th>
          <th @if(in_array(15,$hidden_columns_by_admin)) class="noVis" @endif> {{$global_terminologies['qty']}} <br> Inv
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_inv">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_inv">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(16,$hidden_columns_by_admin)) class="noVis" @endif>Unit Price <br> (EUR)
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(17,$hidden_columns_by_admin)) class="noVis" @endif>Purchasing<br>Vat% <i class="fa fa-close importing-clear-filter clear-values" id="purchasing_vat" data-title="purchasing_vat"  data-id="purchasing_vat" title="Clear Values"></i>
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(18,$hidden_columns_by_admin)) class="noVis" @endif> Unit Price <br>(EUR) (+Vat)
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price_w_vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price_w_vat">
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
          <th @if(in_array(20,$hidden_columns_by_admin)) class="noVis" @endif>Discount
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="discount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="discount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(21,$hidden_columns_by_admin)) class="noVis" @endif>Total Amount<br>(EUR)<br>(W/O Vat) </th>
          <th @if(in_array(22,$hidden_columns_by_admin)) class="noVis" @endif>Total Amount<br>(EUR)<br>(Inc Vat)</th>
          <th @if(in_array(23,$hidden_columns_by_admin)) class="noVis" @endif>Total <br>{{$global_terminologies['gross_weight']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_gross_weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_gross_weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(24,$hidden_columns_by_admin)) class="noVis" @endif>Order #
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
          <th @if(in_array(25,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['avg_units_for-sales'] }}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="weight">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="weight">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
          </th>
        </tr>
      </thead>
    </table>


    <div class="row ml-4 mt-4">
      <div class="col-4 mt-2 mb-4">
        <div class="purch-border input-group custom-input-group">
          @if($getPurchaseOrder->status == 26 || $getPurchaseOrder->status == 27)
          <input type="text" name="refrence_code" style="border-bottom: 1px #dee2e6 !important;" placeholder="Type Reference number..." data-po_id="{{$id}}" class="form-control refrence_number" autocomplete="off">
          @endif
        </div>
      </div>

      <div class="col-4 mt-2 mb-4"></div>

      <div class="col-4 mt-2 mb-4 text-right">
        <table class="headings-color" width="100%" style="margin-top: 15px;">
          <tbody>
            <tr class="d-none">
              <td class="text-nowrap fontbold" align="center">Total QTY:</td>
              <td class="fontbold font-weight-bold" align="center"> </td>
              <td class="fontbold total-qty" align="center"> {{ number_format(@$getPurchaseOrder->total_quantity, 3, '.', ',') }}</td>
            </tr>
            <tr>
              <td class="text-nowrap fontbold" align="right">Total Amount @if(!in_array(17,$hidden_columns_by_admin)) (W/O Vat) @endif:</td>

              <td class="fontbold sub-total" align="left" style="width: 200PX;padding-left: 15px;">{{@$supplier_currency_logo}} {{ number_format(@$getPurchaseOrder->total, 3, '.', ',') }}</td>
            </tr>
            @if(!in_array(17,$hidden_columns_by_admin))
            <tr>
              <td class="text-nowrap fontbold" align="right">Vat Amount:</td>

              <td class="fontbold vat-amount" align="left" style="width: 200PX;padding-left: 15px;"> {{ number_format(@$getPurchaseOrder->vat_amount_total, 3, '.', ',') }}</td>
            </tr>
            <tr>
              <td class="text-nowrap fontbold" align="right">Total Amount (+Vat):</td>

              <td class="fontbold amount-with-vat" align="left" style="width: 200PX;padding-left: 15px;">{{@$supplier_currency_logo}} {{ number_format(@$getPurchaseOrder->total_with_vat, 3, '.', ',') }}</td>
            </tr>
            @endif
            <tr class="d-none">
              <td class="text-nowrap fontbold">Paid:</td>
              <td class="fontbold">20/09/2019</td>
            </tr>
            <tr class="d-none">
              <td class="text-nowrap fontbold">Due:</td>
              <td class="fontbold">20/09/2019</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <div class="row ml-4">
      <div class="col-4 mb-2">
        @if($getPurchaseOrder->status == 26 || $getPurchaseOrder->status == 27)
        <button class="btn-color btn purchasingSupplybtn mt-3" type="submit" id="addProduct">Add Product</button>
        @endif
        @if($getPurchaseOrder->status == 26 || $getPurchaseOrder->status == 27 || $getPurchaseOrder->status == 31)
        <button class="btn-color btn purchasingSupplybtn mt-3" data-id={{$id}} type="button" id="addItem">Add Item</button>
        @endif
      </div>

      <div class="col-2 mb-5"></div>

      @if($getPurchaseOrder->status == 26)
      <div class="col-lg-6 mt-3">
        <a href="javascript:void(0);" style="float: right;">
          <button type="button" data-id={{$id}} class="btn-color btn-danger btn purchasingSupplybtn border-0 cancel-po-btn" style="background:red"><i class="fa fa-times"></i> Cancel </button>
          <button type="button" data-id={{$id}} class="btn-color btn purchasingSupplybtn confirm-po-btn"><i class="fa fa-check"></i> Confirm</button>
        </a>
      </div>
      @endif

    </div>

    {{-- <div class="row ml-4">
      <div class="col-lg-6">
        <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
          <table class="table-purchase-order-history headings-color entriestable table table-bordered text-center">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>User </th>
                <th>Date/Time</th>
                <th>Order #</th>
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

      <div class="col-lg-6">
        <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
          <table class="table-purchase-order-status-history headings-color entriestable table table-bordered text-center">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>User</th>
                <th>Date/Time</th>
                <th>Status</th>
                <th>New Status</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
      </div>
    </div> --}}

    <div class="row mt-2">
      <div class="col-lg-6 ml-5">
        <p>
          <strong>Note: </strong>
          <span class="po-note inputDoubleClick ml-2" data-fieldvalue="{{@$getPoNote->note}}">@if($getPoNote != null) {!! @$getPoNote->note !!} @else {{ 'Click here to add a note...' }} @endif</span>
          <textarea autocomplete="off" name="note" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a note (500 Characters)" maxlength="500">{{ $getPoNote !== null ? @$getPoNote->note : '' }}</textarea>
        </p>
      </div>
    </div>

  </div>

</div>

</div>

</div><!-- main content end here -->
<!-- New Design End here -->
</div>


{{-- Add Product Modal Start Here --}}
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
          <input type="text" name="prod_name" id="prod_name" class="form-control form-group mb-0" autocomplete="off" placeholder="Search by Product Reference #-Default Supplier- Product Description (Press Enter)" style="padding-left:30px;">
        </div>
        <div id="product_name_div"></div>
      </div>

    </div>
  </div>
</div>

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

<div class="modal" id="file-modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Purchase Order Files</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="fetched-files">
          <div class="d-flex justify-content-center">
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
                    <textarea id="note_description" class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <input type="hidden" name="purchase_list_id" class="note-purchase-list-id">
          <input type="hidden" name="pod_id" class="pod_id">
          <button class="btn btn-success" type="submit" class="save-btn"><i class="fa fa-floppy-o"></i> Save </button>
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>
      </form>

    </div>
  </div>
</div>

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
            <input type="hidden" name="d_po_id" value="{{$getPurchaseOrder->id}}">
            <input type="hidden" name="d_supplier_id" id="d_supplier_id" value="{{$getPurchaseOrder->supplier_id}}">
            <input type="file" class="form-control" name="product_excel" id="product_excel" accept=".xls,.xlsx" required="" style="white-space: pre-wrap;"><br>
            <button class="btn btn-info product-upload-btn" type="submit">Upload</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<form id="export_waiting-con_po_form" action="{{route('export-waiting-conformation-po')}}" method="post">
  @csrf
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="column_name" id="column_name_exp">
  <input type="hidden" name="sort_order" id="sort_order_exp">
  <input type="hidden" name="type" id="type" value="data">
</form>

@endsection
@php
$hidden_by_default = '';
@endphp
@section('javascript')

@if(Auth::user()->role_id == 7)
<script type="text/javascript">
  $(document).ready(function() {
    $('.inputDoubleClick').removeClass('inputDoubleClick');
    $('.selectDoubleClick').removeClass('selectDoubleClick');
    $('.prodSuppInputDoubleClick').removeClass('prodSuppInputDoubleClick');
    $('.inputDoubleClickFirst').removeClass('inputDoubleClickFirst');
    $('.inputDoubleClickFixedPrice').removeClass('inputDoubleClickFixedPrice');
    $('.selectDoubleClickPM').removeClass('selectDoubleClickPM');
    $('.inputDoubleClickPM').removeClass('inputDoubleClickPM');
    $('.inputDoubleClickContacts').removeClass('inputDoubleClickContacts');
    $('.market_price_check').attr('disabled', true);
    $('.pay-check').attr('disabled', true);
    $('#add-product-image-btn').hide();
    $('#add-cust-fp-btn').hide();
    $('.btn').addClass('d-none');
    $('.default_supplier').addClass('d-none');
    $('#upload-div').addClass('d-none');
    $('.purch-border').addClass('d-none');
    $('.edit-address').addClass('d-none');
  });

</script>
@endif

<script type="text/javascript">

$('.state-tags').select2();
$('#add_notes_modal').on('hidden.bs.modal', function () {
  $('#note_description').removeClass("is-invalid");
  $('.invalid-feedback').hide();
  $(this).find('form')[0].reset();
});


  var is_clear = false;
  var title = '';
  var view = null;
  $("#target_receive_date, #invoice_date, #payment_due_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  }).on("pick.datepicker",function(e) {
    view = e.view;
  });

  $(document).on("change focusout", "#target_receive_date, #invoice_date, #payment_due_date", function(e) {
    if(view == 'day')
    {
    var po_id = "{{ $id }}";
    var attr_name = $(this).attr('name');
    var id = $(this).attr('id');

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();
    if (fieldvalue == new_value) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      $(this).removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      return false;
    }

    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      $('#' + id).datepicker({
        autoclose: true
      });
      return false;
    }

    if (attr_name == 'payment_due_date') {
      if ($(this).val() == '') {
        $(this).prev().html("Payment Due Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      } else {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }
    if (attr_name == 'target_receive_date') {
      if ($(this).val() == '') {
        $(this).prev().html("Target Ship Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      } else {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }
    if (attr_name == 'invoice_date') {
      if ($(this).val() == '') {
        $(this).prev().html("Credit Note Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      } else {
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
      url: "{{ route('save-po-note') }}",
      dataType: 'json',
      data: 'po_id=' + po_id + '&' + attr_name + '=' + encodeURIComponent($(this).val()),
      beforeSend: function() {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data) {
        $('#loader_modal').modal('hide');
        view = null;
        if (data.success == true) {
          if (attr_name == 'note') {
            $('.po-note').html(data.updateRow.note);
          }

          if (attr_name == 'invoice_date') {
            if (data.po.payment_due_date != null) {
              // $('.payment_due_date_term').html(data.po.payment_due_date);
              var newDate = $.datepicker.formatDate("dd/mm/yy", new Date(data.po.payment_due_date));
              $('.payment_due_date_term').html(newDate);
            }
          }
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }
  });

  $(document).on('click', '#addItem', function() {
    var supplier_id = "{{$getPurchaseOrder->supplier_id}}";
    var id = $(this).data('id');

    if (supplier_id != null) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('add-billed-item-in-po') }}",
        method: 'post',
        data: {
          id: id,
          supplier_id: supplier_id
        },
        beforeSend: function() {
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result) {
          $('#loader_modal').modal('hide');
          if (result.success == true) {
            toastr.success('Success!', 'Billed Item Added Successfully', {
              "positionClass": "toast-bottom-right"
            });
            $('.po-porducts-details').DataTable().ajax.reload();
          }
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          $('.table-purchase-order-history').DataTable().ajax.reload();
        },
        error: function(request, status, error) {
          $('#loader_modal').modal('hide');
          $('.form-control').removeClass('is-invalid');
          $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value) {
            $('input[name="' + key + '"]').after('<span class="invalid-feedback" role="alert"><strong>' + value + '</strong>');
            $('input[name="' + key + '"]').addClass('is-invalid');
          });
        }
      });
    }
  });

  $(document).on('click', '.add-notes', function(e) {
    var id = $(this).data('id');
    var pod_id = $(this).data('pod_id');
    $('.note-purchase-list-id').val(id);
    $('.pod_id').val(pod_id);
  });

  $('.add-purchase-list-note-form').on('submit', function(e) {
    e.preventDefault();
    console.log($('.invalid-feedback').val());
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-purchase-list-prod-note') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
      beforeSend: function() {
        $('.save-btn').addClass('disabled');
        $('.save-btn').attr('disabled', true);
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(result) {
        $('.save-btn').attr('disabled', true);
        $('.save-btn').removeAttr('disabled');
        $('#loader_modal').modal('hide');
        if (result.success == true) {
          toastr.success('Success!', 'Note added successfully', {
            "positionClass": "toast-bottom-right"
          });
          // $('.po-porducts-details').DataTable().ajax.reload();

          $('.add-purchase-list-note-form')[0].reset();
          $('#add_notes_modal').modal('hide');

          $('.note_'+result.id).removeClass('d-none');

        } else {
          toastr.error('Error!', result.errormsg, {
            "positionClass": "toast-bottom-right"
          });
        }

      },
      error: function(request, status, error) {
        $('#loader_modal').modal('hide');
        $('.save-btn').removeClass('disabled');
        $('.save-btn').removeAttr('disabled');
        $('.form-control').removeClass('is-invalid');
        $('.form-control').next().remove();
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value) {
          // $('.invalid-feedback').val() == 'defined';
          if ($('.invalid-feedback').val() == undefined) {
            // console.log($('.invalid-feedback').val());
            $('input[name="' + key + '"]').after('<span class="invalid-feedback" role="alert"><strong>' + value + '</strong>');
            $('input[name="' + key + '"]').addClass('is-invalid');
            $('textarea[name="' + key + '"]').after('<span class="invalid-feedback" role="alert"><strong>' + value + '</strong>');
            $('textarea[name="' + key + '"]').addClass('is-invalid');
            // $('textarea[name="'+key+'"]').css('border-color', '#dc3545');
          }
        });
      }
    });
  });

  $(document).on('click', '.show-notes', function(e) {
    let id = $(this).data('id');
    let pod_id = $(this).data('pod_id');
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      type: "get",
      url: "{{ route('get-purchase-list-prod-note') }}",
      data: 'id=' + id + '&' + 'pod_id=' + pod_id,
      beforeSend: function() {
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="' + loader_img + '" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response) {
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){

      }
    });

  });

  $(document).on('click', '.delete_po_detail_note', function() {
    var id = $(this).data('id');
    var pod_id = $(this).data('pod_id');
    var po_id = '{{$id}}';
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
        if (isConfirm) {
          $.ajax({
            method: "get",
            dataType: "json",
            data: {
              id: id,
              po_id: po_id,
              pod_id: pod_id
            },
            url: "{{ route('delete-po-detail-note') }}",
            beforeSend: function() {
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $("#loader_modal").modal('show');
            },
            success: function(data) {
              $("#loader_modal").modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Note Deleted Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                $("#notes-modal").modal('hide');
                $('.po-porducts-details').DataTable().ajax.reload();
              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        } else {
          swal("Cancelled", "", "error");
        }
      });
  });

  $('#show_price').on('change', function() {
    var checked = $(this).prop('checked');
    if (checked == true) {
      $('#show_price_input').val('1');
    } else if (checked == false) {
      $('#show_price_input').val('0');
    }
  });

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  var po_id = $("#po_id").val();
  var scrollPos = 0;
  $(function(e) {

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
      $('#column_name_exp').val(column_name);
      $('#sort_order_exp').val(order);


    $('.po-porducts-details').DataTable().ajax.reload();

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

    var table2 = $('.po-porducts-details').DataTable({
      processing: false,
      searching: true,
      scrollX: true,
      scrollY: '90vh',
      scrollCollapse: true,
      scroller: true,
      ordering: false,
      serverSide: true,
      fixedHeader: false,
      "columnDefs": [{
          className: "dt-body-left",
          "targets": [1, 2, 3, 4, 5, 6, 7, 8, 9]
        },
        {
          className: "dt-body-right",
          "targets": [10, 11, 12,13]
        }
      ],
      colReorder: true,
      dom: 'Blfrtip',
      buttons: [

      ],
      ajax: {
        beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        // scrollPos = $('.po-porducts-details').DataTable().scroller().pixelsToRow($('.dataTables_scrollBody').scrollTop());

        $('#loader_modal').modal('show');
      },
        url: "{{ url('get-purchase-order-product-detail') }}" + "/" + po_id,
        data: function(data) {data.column_name = column_name,data.sort_order = order},

      },
      columns: [
        { data: 'action', name: 'action' },
        { data: 'remarks', name: 'remarks' },
        { data: 'supplier_id', name: 'supplier_id' },
        { data: 'item_ref', name: 'item_ref'},
        { data: 'customer', name: 'customer' },
        { data: 'brand', name: 'brand' },
        { data: 'product_description', name: 'product_description' },
        { data: 'type', name: 'type' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'leading_time', name: 'leading_time' },
        { data: 'unit_gross_weight', name: 'unit_gross_weight' },
        { data: 'desired_qty', name: 'desired_qty' },
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
        { data: 'gross_weight', name: 'gross_weight' },
        { data: 'order_no', name: 'order_no' },
        { data: 'weight', name: 'weight' },
      ],
      createdRow: function (row, data, index) {
      if (is_clear) {
        $('#purchasing_vat').attr('title', 'Undo');
        $('#purchasing_vat').removeClass('fa-close');
        $('#purchasing_vat').addClass('fa-undo');
      }
      else{
        $('#purchasing_vat').attr('title', 'Clear Values');
        $('#purchasing_vat').removeClass('fa-undo');
        $('#purchasing_vat').addClass('fa-close');
      }
    },
      initComplete: function() {
        if ("{{Auth::user()->role_id}}" == 7) {
          $('.inputDoubleClick').removeClass('inputDoubleClick');
          $('.selectDoubleClick').removeClass('selectDoubleClick');

        }
        $('.dataTables_scrollHead').css('overflow', 'auto');

        // Sync THEAD scrolling with TBODY
        $('.dataTables_scrollHead').on('scroll', function() {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
        @if($columns_prefrences)
        table2.colReorder.order([{{$columns_prefrences}}]);
        table2.colReorder.disable();
      @endif
      },
      drawCallback: function(){

        table2.on('xhr.dt', function()
        {
          table2.one('draw', function() {
            table2.row(scrollPos).scrollTo(false);
            console.log(table2.row(scrollPos));
            var ind = table2.row(scrollPos).index();
          });
        });

        $('.dts_label').prev('div').css({
          display: 'none',
        });
        $('#loader_modal').modal('hide');
      },
    });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        // $('#search_value').val($(this).val());
        table2.search($(this).val()).draw();
      }
    });

    // table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
    //   // var arr = "{{@$display_purchase_list->display_order}}";
    //   // var all = arr.split(',');

    //   var arr = table2.colReorder.order();
    //   var all = arr;
    //   if(all == '')
    //   {
    //     var col = column;
    //   }
    //   else
    //   {
    //     var col = all[column];
    //   }
    //   $.ajaxSetup({
    //     headers: {
    //     'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    //     }
    //   });
    //   $.post({
    //     url : "{{ route('toggle-column-display') }}",
    //     dataType : "json",
    //     data : {type:'po_detail',column_id:col},
    //     beforeSend: function(){
    //       $('#loader_modal').modal({
    //         backdrop: 'static',
    //         keyboard: false
    //       });
    //       $('#loader_modal').modal('show');
    //     },
    //     success: function(data){
    //     $('#loader_modal').modal('hide');
    //     if(data.success == true){
    //       /*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
    //       // table2.ajax.reload();
    //     }
    //     },
    //     error: function(request, status, error){
    //       $("#loader_modal").modal('hide');
    //     }
    //   });
    // });

    // table2.on( 'column-reorder', function ( e, settings, details ) {

    //   $.get({
    //   url : "{{ route('column-reorder') }}",
    //   dataType : "json",
    //   data : "type=po_detail&order="+table2.colReorder.order(),
    //   beforeSend: function(){
    //     $('#loader_modal').modal({
    //         backdrop: 'static',
    //         keyboard: false
    //     });
    //     $('#loader_modal').modal('show');
    //   },
    //   success: function(data){
    //     $('#loader_modal').modal('hide');
    //   },
    //   error: function(request, status, error){
    //     $("#loader_modal").modal('hide');
    //   }
    //   });
    //   table2.button(0).remove();
    //   table2.button().add(0,
    //   {
    //     extend: 'colvis',
    //     autoClose: false,
    //     fade: 0,
    //     columns: ':not(.noVis)',
    //     colVis: { showAll: "Show all" }
    //   });

    //   table2.ajax.reload();

    //   var headerCell = $( table2.column( details.to ).header() );
    //   headerCell.addClass( 'reordered' );

    // });

    $('.export_btn').on('click',function(){
      $('#type').val('data');
      $("#export_waiting-con_po_form").submit();
      $('.po-porducts-details').DataTable().ajax.reload();
    });
    $('#example_export_btn').on('click',function(){
      $('#type').val('example');
      $("#export_waiting-con_po_form").submit();
      $('.po-porducts-details').DataTable().ajax.reload();
    });

    $(document).on('keyup', '.form-control', function() {
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  // dropdown double click editable code start here
  $(document).on('change', 'select.select-common', function(e) {

    if ($(this).attr('name') == "warehouse_id") {
      if ($(this).val() !== '') {
        if ($(this).val() > 0) {
          var po_id = "{{ $id }}";
          var attr_name = $(this).attr('name');
          var rowId = $(this).parents('tr').attr('id');
          var customPointer = $(this);
          var pos_count = "{{$pos_count}}";
          if(pos_count > 1)
          {
            var msg = '.This PO will be reverted back to shipping';
          }
          else
          {
            var msg = ' warehouse of the PO';
          }
          var thisPointer = $(this);
          // Change warehouse
          swal({
              title: "Are you sure!!!",
              text: "You want to change"+msg,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Yes, change it!",
              cancelButtonText: "Cancel",
              closeOnConfirm: true,
              closeOnCancel: false
            },
            function(isConfirm) {
              if (isConfirm) {
                $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
              });

          $.ajax({
            type: "post",
            url: "{{ route('save-po-product-warhouse') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'selected_ids=' + po_id + '&' + attr_name + '=' + thisPointer.val() + '&' + 'pos_count=' + pos_count,
            beforeSend: function() {
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                var new_value = $("option:selected", customPointer).html();
                customPointer.addClass('d-none');
                customPointer.prev().removeClass('d-none');
                customPointer.prev().html(new_value);

                toastr.success('Success!', 'Warehouse Assigned Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                /*$('.po-porducts-details').DataTable().ajax.reload();*/
              }
              if(data.cannot_change_warehouse == true)
              {
                customPointer.addClass('d-none');
                customPointer.prev().removeClass('d-none');
                swal({
                  html: true,
                  title: 'Sorry !!!',
                  text: '<b>Group already created. Please revert PO before changing warehouse !!!</b>'
                });
              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
              } else {
                swal("Cancelled", "", "error");
              }
            });
        }
      }
    }

    if ($(this).attr('name') == "payment_terms_id") {
      var target_receive_date = $(".target_receive_date").val();
      if (target_receive_date == '') {
        $('.payment_terms_id').val('');
        swal({
          html: true,
          title: 'Alert !!!',
          text: '<b>Must Fill Target Ship Date First !!!</b>'
        });
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
        return false;
      } else {

        var payment_terms_id = $(this).val();
        var po_id = '{{ $id }}';

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });

        $.ajax({
          method: "post",
          url: "{{ url('payment-term-save-in-po') }}",
          dataType: 'json',
          context: this,
          data: {
            payment_terms_id: payment_terms_id,
            po_id: po_id
          },
          beforeSend: function() {
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success: function(data) {
            $("#loader_modal").modal('hide');
            if (data.success == true) {
              // $('.payment_due_date_term').html(data.payment_due_date);
              var newDate = $.datepicker.formatDate("dd/mm/yy", new Date(data.payment_due_date));
              $('.payment_due_date_term').html(newDate);
              $(this).prev().html($("option:selected", this).html());
              $(this).removeClass('active');
              $(this).addClass('d-none');
              $(this).prev().removeClass('d-none');
            }
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
        });

      }
    }

  });

  // double click editable
  $(document).on("click", ".inputDoubleClickQuantity", function() {

    $x = $(this);
    $(this).addClass('d-none');
    $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');

    setTimeout(function() {

      $('.spinner').remove();
      $x.next().removeClass('d-none');
      $x.next().addClass('active');
      $x.next().focus();
      var num = $x.next().val();
      $x.next().focus().val('').val(num);
    }, 300);

  });

  $(document).on('keyup focusout', 'input[type=text]', function(e) {
    if($(this).attr('name') == 'target_receive_date' || $(this).attr('name') == 'invoice_date' || $(this).attr('name') == 'payment_due_date')
    {
      return false;
    }
    var fieldvalue = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      // thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
      thisPointer.removeClass('active');
    }

    if ((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      if ($(this).val() == fieldvalue) {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.prev().removeClass('d-none');
        thisPointer.removeClass('active');
      }

      var po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      var rowId = $(this).parents('tr').attr('id');

      if ($(this).attr('name') == 'billed_desc') {
        if ($(this).val() == null) {
          return false;
        } else if ($(this).val() !== '' && $(this).hasClass('active')) {
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
            url: "{{ route('save-po-product-desc') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + encodeURIComponent($(this).val()) + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Billed Description Update Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                $('.po-porducts-details').DataTable().ajax.reload();
              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
    }
  });

  $(document).on('keyup focusout', 'input[type=number]', function(e) {
    e.preventDefault();
    var fieldvalue = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      // thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
      thisPointer.removeClass('active');
    }

    if ((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')) {

      var fieldvalue = $(this).prev().data('fieldvalue');
      if ($(this).val() == fieldvalue) {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.val(fieldvalue);
        thisPointer.prev().removeClass('d-none');
        thisPointer.removeClass('active');
      }

      var po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      var rowId = $(this).parents('tr').attr('id');
      // quantity
      if ($(this).attr('name') == 'quantity')
      {
        // if ($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null) {
        if ($(this).val() < 0 || $(this).val() == null) {
          swal({
            html: true,
            title: 'Alert !!!',
            text: '<b>Qty Inv cannot be 0 or less then 0 !!!</b>'
          });
          return false;
        }
        else if ($(this).val() !== '' && $(this).hasClass('active'))
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
            url: "{{ route('save-po-product-quantity') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // alert('here');
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('hide');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true)
              {
                toastr.success('Success!', 'QTY Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });

                // $(".po-porducts-details").DataTable().ajax.reload(null, false );
                // setTimeout(function(){
                //   $('#'+rowId).addClass('highlight');
                // }, 1);

                var total_qty = data.total_qty.toFixed(3);
                var sub_total_value = data.sub_total.toFixed(3);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

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
                $('.quantity_span_'+data.id).css("color","red");
                $('.quantity_span_'+data.id).attr('data-fieldvalue',data.quantity);
                $('.quantity_field_'+data.id).val(data.quantity);

                $('.desired_qty_span_'+data.id).html(data.desired_qty);
                $('.desired_qty_span_'+data.id).attr('data-fieldvalue',data.desired_qty);
                $('.desired_qty_field_'+data.id).val(data.desired_qty);

                // $('.entriestable').css("table-layout","auto");

              }
              // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
      //purchasing vat
       if ($(this).attr('name') == 'pod_vat_actual')
      {
        if ($(this).val() == null)
        {
          /*swal({ html:true, title:'Alert !!!', text:'<b>Quantity cannot be 0 or less then 0 !!!</b>'});*/
          return false;
        }
        else if ($(this).val() !== '' && $(this).hasClass('active'))
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
            url: "{{ route('save-po-pod-vat-actual-quantity') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true)
              {
                toastr.success('Success!', 'Purchasing Vat Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });

                // $(".po-porducts-details").DataTable().ajax.reload(null, false );
                setTimeout(function(){
                  $('#'+rowId).addClass('highlight');
                }, 1);

                var total_qty = data.total_qty.toFixed(3);
                var sub_total_value = data.sub_total.toFixed(3);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

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

              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
      // Discount
      if ($(this).attr('name') == 'discount')
      {
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
            url: "{{ route('save-po-product-discount') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Discount Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                // $('.po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(3);
                $('.sub-total').html(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

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

                // $('#sub_total').val(sub_total_value);
              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
      // unit price
      if ($(this).attr('name') == 'unit_price')
      {
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
            url: "{{ route('update-unit-price') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Unit Price Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                // $('.po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(3);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

                $('.amount_'+data.id).html(data.total_amount_wo_vat);
                $('.amount_with_vat_'+data.id).html(data.total_amount_w_vat);
                $('.unit_price_span_'+data.id).html(data.unit_price);
                $('.unit_price_span_'+data.id).attr('data-fieldvalue',data.unit_price);
                $('.unit_price_field_'+data.id).val(data.unit_price);
                if(data.old_value !== '' && data.old_value !== null && data.old_value !== '--')
                {
                  $('.unit_price_with_vat_span_'+data.id).css("color","red");
                  $('.unit_price_span_'+data.id).css("color","red");
                }
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

              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }

      // unit price with vat
      if ($(this).attr('name') == 'pod_unit_price_with_vat')
      {
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
            url: "{{ route('update-unit-price-with-vat') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Unit Price Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                // $('.po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(3);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

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
              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }

      // desired Qty
      if ($(this).attr('name') == 'desired_qty')
      {
        if ($(this).val() < 0 || $(this).val() == null) {
          swal({
            html: true,
            title: 'Alert !!!',
            text: '<b> Ordered Qty cannot be 0 or less then 0 !!!</b>'
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
            url: "{{ route('update-desired-qty') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Desired Qty Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                // $('.po-porducts-details').DataTable().ajax.reload();
                var total_qty = data.total_qty.toFixed(3);
                var sub_total_value = data.sub_total.toFixed(3);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

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

              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
      // Pkg->Qty Inv (est.) is basically a billed_unit_per_package
      if ($(this).attr('name') == 'billed_unit_per_package')
      {
        if ($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null) {
          swal({
            html: true,
            title: 'Alert !!!',
            text: '<b>Pkg->Qty Inv cannot be 0 or less then 0 !!!</b>'
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
            url: "{{ route('update-billed-unit-per-package') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Pkg->Qty Inv (est.) Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                // $('.po-porducts-details').DataTable().ajax.reload();
                var total_qty = data.total_qty.toFixed(3);
                var sub_total_value = data.sub_total.toFixed(3);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
                var vat_total_amount = data.vat_amout.toFixed(3);
                $('.vat-amount').html(vat_total_amount);

                var amount_with_vat = data.total_w_v.toFixed(3);
                $('.amount-with-vat').html(amount_with_vat);

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

              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
              // $('.table-purchase-order-history').DataTable().ajax.reload();

            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        }
      }
      // unit Gross Weight
      if ($(this).attr('name') == 'pod_gross_weight')
      {
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
            url: "{{ route('update-pod-gross-weight-price') }}",
            dataType: 'json',
            data: 'rowId=' + rowId + '&' + 'po_id=' + po_id + '&' + attr_name + '=' + $(this).val() + '&' + 'old_value=' + old_value,
            beforeSend: function() {
              // $('#loader_modal').modal({
              //   backdrop: 'static',
              //   keyboard: false
              // });
              // $('#loader_modal').modal('show');
            },
            success: function(data) {
              $('#loader_modal').modal('hide');
              if (data.success == true) {
                toastr.success('Success!', 'Gross Weight Updated Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                // $('.table-purchase-order-history').DataTable().ajax.reload();
                // $('.po-porducts-details').DataTable().ajax.reload();
                // var sub_total_value = data.sub_total.toFixed(3);
                // $('.sub-total').html(sub_total_value);
                // $('#sub_total').val(sub_total_value);

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
              }
              $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

              // $('.table-purchase-order-history').DataTable().ajax.reload();


            }
          });
        }
      }

    }

  });
  });
  // double click editable
  $(document).on("click", ".inputDoubleClick", function() {
    var status = "{{$getPurchaseOrder->status}}";
    var spname = $(this).data('spname');
    if(status == "40")
    {
      swal({
          html: true,
          title: 'Cannot Update Data In Manual PO !!!',
          text: '<b>This is a manual purchase Order !!!</b>'
        });
      return false;
    }
    if(spname == "to_warehouse_id")
    {
      if(status == "15" || status == "40")
      {
        swal({
          html: true,
          title: 'Cannot Update To Warehouse !!!',
          text: '<b>This Purchase Order is already Received Into Stock !!!</b>'
        });
        return false;
      }
    }
    if(spname == "exchange_rate")
    {
      // if(status == "14")
      // {
      //   swal({
      //     html: true,
      //     title: 'Cannot Update Exchange Rate !!!',
      //     text: '<b>You can update the exchange rate by going to the group/shipment of this Purchase Order !!!</b>'
      //   });
      //   return false;
      // }
      if(status == "15")
      {
        swal({
          html: true,
          title: 'Cannot Update Exchange Rate !!!',
          text: '<b>This Purchase Order is already Received Into Stock !!!</b>'
        });
        return false;
      }
    }

    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
    var num = $(this).next().val();
    $(this).next().focus().val('').val(num);
    if ($(this).next().attr('name') == 'discount') {
      // $(this).next().addClass('d-inline-block');
      // $(this).next().attr('min','0');
      // $(this).next().attr('max','99999');
    }
  });

  $(document).on("keyup focusout", ".fieldFocus", function(e) {
    var po_id = "{{ $id }}";
    var attr_name = $(this).attr('name');

    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      return false;
    }

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();
    if (fieldvalue == new_value) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      $(this).removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      return false;
    }

    if ((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')) {
      if (attr_name == 'note') {
        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        if ($(this).val().length < 1) {
          return false;
        } else if ($(this).val() == '') {
          $(this).prev().html("Click here to add a note!!!");
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          return false;
        } else {
          $(this).prev().html($(this).val());
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
      }

      if (attr_name == 'memo') {
        if ($(this).val() == '') {
          $(this).prev().html("Memo Here");
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          return false;
        } else {
          $(this).prev().html($(this).val());
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
      }

      if (attr_name == 'invoice_number') {
        if ($(this).val() == '') {
          $(this).prev().html("Invoice Number Here");
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          return false;
        } else {
          $(this).prev().html($(this).val());
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
      }

      if (attr_name == 'exchange_rate') {
        if ($(this).val() == '') {
          $(this).prev().html("Invoice Exchange Rate Here");
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          return false;
        } else {
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
        url: "{{ route('save-po-note') }}",
        dataType: 'json',
        data: 'po_id=' + po_id + '&' + attr_name + '=' + encodeURIComponent($(this).val()),
        beforeSend: function() {
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data) {
          $('#loader_modal').modal('hide');
          if (data.success == true) {
            if (attr_name == 'note') {
              $('.po-note').html(data.updateRow.note);
            }

            if (attr_name == 'invoice_date') {
              if (data.po.payment_due_date != null) {
                // $('.payment_due_date_term').html(data.po.payment_due_date);
                var newDate = $.datepicker.formatDate("dd/mm/yy", new Date(data.po.payment_due_date));
                $('.payment_due_date_term').html(newDate);
              }
            }
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }
  });

  // document upload
  $('.addDocumentForm').on('submit', function(e) {
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-purchase-order-document') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this),
      contentType: false,
      cache: false,
      processData: false,
      beforeSend: function() {
        $('.save-doc-btn').html('Please wait...');
        $('.save-doc-btn').addClass('disabled');
        $('.save-doc-btn').attr('disabled', true);
      },
      success: function(result) {
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').attr('disabled', true);
        $('.save-doc-btn').removeAttr('disabled');
        if (result.success == true) {
          toastr.success('Success!', 'Document Uploaded Successfully', {
            "positionClass": "toast-bottom-right"
          });
          $('.addDocumentForm')[0].reset();
          $('.collapse').collapse("toggle");
          // $('.addDocumentModal').modal('hide');
          $('.download-docs').removeClass('d-none');
          let sid = $('#sid').val();

          $.ajax({
            type: "post",
            url: "{{ route('get-purchase-order-files') }}",
            data: 'po_id=' + sid,
            beforeSend: function() {
              var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
              var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="' + loader_img + '" style="margin-top: 10px;"></div>';
              $('.fetched-files').html(loader_html);
            },
            success: function(response) {
              $('.fetched-files').html(response);
            },
            error: function (request, status, error) {
              // $('#loader_modal').modal('hide');
              $('.fetched-files').html(response);
            }
          });
        }
      },
      error: function(request, status, error) {
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').removeClass('disabled');
        $('.save-doc-btn').removeAttr('disabled');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value) {
          $('input[name="' + key + '[]"]').after('<span class="invalid-feedback" role="alert"><strong>' + value + '</strong>');
          $('input[name="' + key + '[]"]').addClass('is-invalid');

        });
      }
    });
  });

  // delete product from purchasing detail page this is for a order id exist if
  $(document).on('click', '.delete-product-from-list', function(e) {

    var order_id = $(this).data('order_id');
    var order_product_id = $(this).data('order_product_id');
    var po_id = $(this).data('po_id');
    var id = $(this).data('id');
    var doc_for = "PO";

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      type: "get",
      url: "{{ route('check-po-product-numbers') }}",
      dataType: 'json',
      data: 'po_id=' + po_id + '&doc_for=' + doc_for,
      beforeSend: function() {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data) {
        $('#loader_modal').modal('hide');
        if (data.success == true) {
          swal({
              title: "Are you sure!!!",
              text: "You want to revert this item into Purchase list? " + data.msg,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Yes, remove it!",
              cancelButtonText: "Cancel",
              closeOnConfirm: true,
              closeOnCancel: false
            },
            function(isConfirm) {
              if (isConfirm) {
                $.ajax({
                  method: "get",
                  data: 'order_id=' + order_id + '&' + 'order_product_id=' + order_product_id + '&' + 'po_id=' + po_id + '&' + 'id=' + id,
                  url: "{{ route('delete-product-from-po') }}",
                  beforeSend: function() {
                    $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                    $('#loader_modal').modal('show');
                  },
                  success: function(data) {
                    $("#loader_modal").modal('hide');
                    if (data.success === true && data.redirect === 'no') {
                      toastr.success('Success!', 'Product Removed Successfully.', {
                        "positionClass": "toast-bottom-right"
                      });
                      $('.po-porducts-details').DataTable().ajax.reload();
                      $('.table-purchase-order-history').DataTable().ajax.reload();
                      var sub_total_value = data.sub_total.toFixed(2);
                      $('.sub-total').html(sub_total_value);
                      $('#sub_total').val(sub_total_value);
                      var vat_total_amount = data.vat_amout.toFixed(3);
                      $('.vat-amount').html(vat_total_amount);

                      var amount_with_vat = data.total_w_v.toFixed(3);
                      $('.amount-with-vat').html(amount_with_vat);

                    } else if (data.success === true && data.redirect === 'yes') {
                      toastr.success('Success!', 'Product Removed Successfully.', {
                        "positionClass": "toast-bottom-right"
                      });
                      setTimeout(function() {
                        window.location.href = "{{ route('list-purchasing')}}";
                      }, 1000);
                    }
                  },
                  error: function(request, status, error){
                    $("#loader_modal").modal('hide');
                  }
                });
              } else {
                swal("Cancelled", "", "error");
              }
            });
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

  });

  // if order id not exist then this delete code will work
  $(document).on('click', '.delete-item-from-list', function(e) {

    var po_id = $(this).data('po_id');
    var id = $(this).data('id');
    var doc_for = "PO";

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      type: "get",
      url: "{{ route('check-po-product-numbers') }}",
      dataType: 'json',
      data: 'po_id=' + po_id + '&doc_for=' + doc_for,
      beforeSend: function() {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data) {
        $('#loader_modal').modal('hide');
        if (data.success == true) {
          swal({
              title: "Are you sure!!!",
              text: "You want to delete this item from the list? " + data.msg,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Yes, remove it!",
              cancelButtonText: "Cancel",
              closeOnConfirm: true,
              closeOnCancel: false
            },
            function(isConfirm) {
              if (isConfirm) {
                $.ajax({
                  method: "get",
                  data: 'po_id=' + po_id + '&' + 'id=' + id,
                  url: "{{ route('delete-product-from-po-detail') }}",
                  success: function(data) {
                    if (data.success === true && data.redirect === 'no') {
                      toastr.success('Success!', 'Product Removed Successfully.', {
                        "positionClass": "toast-bottom-right"
                      });
                      $('.table-purchase-order-history').DataTable().ajax.reload();
                      $('.po-porducts-details').DataTable().ajax.reload();
                      var sub_total_value = data.sub_total.toFixed(2);
                      $('.sub-total').html(sub_total_value);
                      $('#sub_total').val(sub_total_value);
                      var vat_total_amount = data.vat_amout.toFixed(3);
                      $('.vat-amount').html(vat_total_amount);

                      var amount_with_vat = data.total_w_v.toFixed(3);
                      $('.amount-with-vat').html(amount_with_vat);

                    } else if (data.success === true && data.redirect === 'yes') {
                      toastr.success('Success!', 'Product Removed Successfully.', {
                        "positionClass": "toast-bottom-right"
                      });
                      setTimeout(function() {
                        window.location.href = "{{ route('purchasing-dashboard')}}";
                      }, 1000);
                    }
                  },
                  error: function(request, status, error){

                  }
                });
              } else {
                swal("Cancelled", "", "error");
              }
            });
        }
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
    });

  });

  // export pdf code
  $(document).on('click', '.export-pdf', function(e) {
    var po_id = $('#po_id_for_pdf').val();
    $('.export-po-form')[0].submit();
  });

  $(document).on('click', '.export-pf-pdf', function(e) {
    var po_id = $('#po_id_for_pdf').val();
    $('#pf_logo').val(1);
    $('.export-po-form')[0].submit();
    $('#pf_logo').val("");
  });

  // confirm po button code here
  $(document).on('click', '.confirm-po-btn', function(e) {

    var id = $(this).data('id'); //purchase order id

    var target_receive_date = $(".target_receive_date").val();
    var invoice_date = $(".invoice_date").val();
    // var payment_due_date = $(".payment_due_date").val();
    var payment_term = $('.payment_terms_id :selected').val();
    @if($targetShipDate['target_ship_date_required']==1)
    if (target_receive_date == '') {
      swal({
        html: true,
        title: 'Alert !!!',
        text: '<b>Must Fill Target Ship Date!!!</b>'
      });
      return false;
    }
    @endif
    if (invoice_date == '') {
      swal({
        html: true,
        title: 'Alert !!!',
        text: '<b>Must Fill Credit Note Date!!!</b>'
      });
      return false;
    }

    if (payment_term == '') {
      swal({
        html: true,
        title: 'Alert !!!',
        text: '<b>Must Select Payment Term!!!</b>'
      });
      return false;
    }

    // if(payment_due_date == '')
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Payment Due Date!!!</b>'});
    //   return false;
    // }
    // else
    // {
    //   $.ajaxSetup({
    //   headers: {
    //     'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    //   }
    //   });

    //   $.ajax({
    //     method:"post",
    //     type: 'post',
    //     data:'id='+id+'&'+'receive_date='+target_receive_date,
    //     url: "{{ route('set-target-receive-date') }}",
    //     success: function(response){
    //        // do nothing here
    //     }
    //   });
    // }

    swal({
        title: "Are you sure!!!",
        text: "You want to confirm this Credit Note?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, confirm it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
      },
      function(isConfirm) {
        if (isConfirm) {
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });

          $.ajax({
            method: "post",
            // data: 'id=' + id,
            data: {id:id, type: 'credit_note'},
            url: "{{ route('confirm-purchase-order') }}",
            beforeSend:function(){
              $('.confirm-po-btn').prop('disabled',true);
              $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(response) {
              // $('#loader_modal').modal('hide');
              if (response.success === true && response.status == "Complete")
              {
                toastr.success('Success!', 'Credit Note Confirmed Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                setTimeout(function() {
                  window.location.href = "{{ route('accounting-dashboard')}}?type=supplier";
                }, 800);
              }
              else if (response.success == false)
              {
                $('.confirm-po-btn').prop('disabled',false);
                $('#loader_modal').modal('hide');
                toastr.error('Error!', response.errorMsg, {
                  "positionClass": "toast-bottom-right"
                });
              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
        } else {
          swal("Cancelled", "", "error");
        }
      });

  });
  $(document).on('click', '.cancel-po-btn', function(e) {
    var id = $(this).data('id'); //purchase order id
    swal({
        title: "Are you sure!!!",
        text: "You want to cancel this purchase order?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
      },
      function(isConfirm) {
        if (isConfirm) {
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });

          $.ajax({
            method: "post",
            data: 'id=' + id,
            url: "{{ route('cancel-purchase-order') }}",
            beforeSend:function(){
              $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(response) {
              // $('#loader_modal').modal('hide');
              if (response.success === true && response.status == "Deleted") {
                toastr.success('Success!', 'Purchase Order Canceled Successfully.', {
                  "positionClass": "toast-bottom-right"
                });
                setTimeout(function() {
                  window.location.href = "{{ route('purchasing-dashboard')}}";
                }, 800);
              }
            },
            error: function(request, status, error){
              $('#loader_modal').modal('hide');
            }
          });
        } else {
          swal("Cancelled", "", "error");
        }
      });

  });

  // adding product by searching
  $('#prod_name').keyup(function(e) {
    var page = "Po";
    var query = $.trim($(this).val());
    var supplier_id = "{{$getPurchaseOrder->supplier_id}}";
    var warehouse_id = "{{$getPurchaseOrder->from_warehouse_id}}";
    var po_id = $("#po_id").val();
    if (query == '' || e.keyCode == 8 || 'keyup') {
      $('#product_name_div').empty();
    }
    if (e.keyCode == 13)
    {
      if (query.length > 2)
      {
        var _token = $('input[name="_token"]').val();
        $.ajax({
          url: "{{ route('autocomplete-fetch-product') }}",
          // url: "{{ route('autocomplete-fetching-products-for-po') }}",
          method: "POST",
          data: {
            query: query,
            _token: _token,
            inv_id: po_id,
            supplier_id: supplier_id,
            warehouse_id: warehouse_id,
            page: page
          },
          beforeSend: function() {
            $('#product_name_div').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
          },
          success: function(data) {
            $('#product_name_div').fadeIn();
            $('#product_name_div').html(data);
          },
          error: function(request, status, error){

          }
        });
      }
      else
      {
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!', {
          "positionClass": "toast-bottom-right"
        });
      }
    }
  });

  $(document).on('click', 'li', function() {
    $('#prod_name').val("");
    $('#product_name_div').fadeOut();
  });

  $(document).on('click', '.add_product_to', function(e) {
    var po_id = $("#po_id").val();
    var prod_id = $(this).data('prod_id');
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method: "post",
      data: 'selected_products=' + prod_id + '&po_id=' + po_id + '&purchasing_vat='+ "{{in_array(17,$hidden_columns_by_admin)}}",
      url: "{{ route('add-prod-to-po-detail') }}",
      beforeSend: function(){
        $('#addProductModal').modal('hide');
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      success: function(data) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('.table-purchase-order-history').DataTable().ajax.reload();
        if (data.success == false) {
          $('#addProductModal').modal('hide');
          $('#loader_modal').modal('hide');
          toastr.error('Error!', data.successmsg, {
            "positionClass": "toast-bottom-right"
          });
          $('#prod_name').text('');
          $('.po-porducts-details').DataTable().ajax.reload();
        } else {
          toastr.success('Success!', data.successmsg, {
            "positionClass": "toast-bottom-right"
          });
          $('#addProductModal').modal('hide');
          $('#loader_modal').modal('hide');
          $('#prod_name').text('');
          $('.po-porducts-details').DataTable().ajax.reload();
          var sub_total_value = data.sub_total.toFixed(2);
          $('.sub-total').html(sub_total_value);
          $('#sub_total').val(sub_total_value);
          var vat_total_amount = data.vat_amout.toFixed(3);
          $('.vat-amount').html(vat_total_amount);

          var amount_with_vat = data.total_w_v.toFixed(3);
          $('.amount-with-vat').html(amount_with_vat);

          // $('.total_products').html(data.total_products);
        }

      },
      error: function(request, status, error){
        $('#addProductModal').modal('hide');
      }
    });
  });

  $(document).on('click', '#addProduct', function() {
    if ($(this).attr("id") == 'addProduct') {
      $('#addProductModal').modal('show');
      $('#prod_name').focus();
    }
  });

  $(document).on('keyup', '.refrence_number', function(e) {
    if (e.keyCode == 13) {
      if ($(this).val() !== '') {
        var refrence_number = $(this).val();
        var po_id = $(this).data(po_id);

        var formData = {
          "refrence_number": refrence_number,
          "po_id": po_id,
          "purchasing_vat": "{{in_array(17,$hidden_columns_by_admin)}}"
        };
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          url: "{{ route('add-prod-by-refrence-number-in-po-detail') }}",
          method: 'post',
          data: formData,
          beforeSend: function() {
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success: function(result) {
            $("#loader_modal").modal('hide');
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            $('.table-purchase-order-history').DataTable().ajax.reload();
            if (result.success == true) {
              toastr.success('Success!', result.successmsg, {
                "positionClass": "toast-bottom-right"
              });
              $('.refrence_number').val('');
              $('.po-porducts-details').DataTable().ajax.reload();
              var sub_total_value = result.sub_total.toFixed(2);
              $('.sub-total').html(sub_total_value);
              $('#sub_total').val(sub_total_value);
              var vat_total_amount = data.vat_amout.toFixed(3);
              $('.vat-amount').html(vat_total_amount);

              var amount_with_vat = data.total_w_v.toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);

              // $('.total_products').html(result.total_products);
            } else {
              toastr.error('Error!', result.successmsg, {
                "positionClass": "toast-bottom-right"
              });
              $('.refrence_number').val('');
              $('.po-porducts-details').DataTable().ajax.reload();
            }

          },
          error: function(request, status, error) {
            $("#loader_modal").modal('hide');
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value) {
              $('input[name="' + key + '"]').after('<span class="invalid-feedback" role="alert"><strong>' + value + '</strong>');
              $('input[name="' + key + '"]').addClass('is-invalid');
            });
          }
        });
      }
    }
  });

  $(window).keydown(function(e) {
    if (e.keyCode == 13) {
      e.preventDefault();
      return false;
    }
  });

  // $('.errormsgDiv').hide();

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').addClass('d-none');
  });

  $('.upload-excel-form').on('submit',function(e){
    $('#import-modal').modal('hide');
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-bulk-product-in-pos-detail') }}",
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
        console.log(data);
        $('#loader_modal').modal('hide');
        $('#import-modal').modal('hide');
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
          var sub_total_value = data.sub_total.toFixed(2);
          $('.sub-total').html(sub_total_value);
          $('#sub_total').val(sub_total_value);
          var vat_total_amount = data.vat_total.toFixed(3);
          $('.vat-amount').html(vat_total_amount);

          var amount_with_vat = data.total.toFixed(3);
          $('.amount-with-vat').html(amount_with_vat);

          $('.po-porducts-details').DataTable().ajax.reload();
          $('.table-purchase-order-history').DataTable().ajax.reload();
        }
        if(data.success == "withissues")
        {
          toastr.warning('Warning!', data.msg, {"positionClass": "toast-bottom-right"});
          if(data.errorMsg != null && data.errorMsg != '')
          {
            $('#msgs_alerts').html(data.errorMsg);
            $('.errormsgDiv').removeClass('d-none');
          }
          // $('.exp_imp_btn').click();
          $('.upload-excel-form')[0].reset();
          var sub_total_value = data.sub_total.toFixed(2);
          $('.sub-total').html(sub_total_value);
          $('#sub_total').val(sub_total_value);
          var vat_total_amount = data.vat_amout.toFixed(3);
          $('.vat-amount').html(vat_total_amount);

          var amount_with_vat = data.total_w_v.toFixed(3);
          $('.amount-with-vat').html(amount_with_vat);

          $('.po-porducts-details').DataTable().ajax.reload();
          $('.table-purchase-order-history').DataTable().ajax.reload();
        }
        if(data.success == false)
        {
          toastr.error('Error!', data.msg, {"positionClass": "toast-bottom-right"});
          // $('.exp_imp_btn').click();
          $('.upload-excel-form')[0].reset();
          var sub_total_value = data.sub_total.toFixed(2);
          $('.sub-total').html(sub_total_value);
          $('#sub_total').val(sub_total_value);
          var vat_total_amount = data.vat_amout.toFixed(3);
          $('.vat-amount').html(vat_total_amount);

          var amount_with_vat = data.total_w_v.toFixed(3);
          $('.amount-with-vat').html(amount_with_vat);

          $('.po-porducts-details').DataTable().ajax.reload();
          $('.table-purchase-order-history').DataTable().ajax.reload();
        }
      },
      error: function (request, status, error) {
          $('#loader_modal').modal('hide');
          $(".product-upload-btn").attr("disabled", false);
          $('.po-porducts-details').DataTable().ajax.reload();
          $('.table-purchase-order-history').DataTable().ajax.reload();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'[]"]').addClass('is-invalid');
          });
        }
    });
  });

</script>
<script>
  function backFunctionality() {
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    if (history.length > 1) {
      return history.go(-1);
    } else {
      var url = "{{ url('/') }}";
      document.location.href = url;
    }
  }

</script>
<script type="text/javascript">
  $(function(e) {

    $(document).on('click', '.download-documents', function(e) {
      let sid = $(this).data('id');
      console.log(sid);
      $.ajax({
        type: "post",
        url: "{{ route('get-purchase-order-files') }}",
        data: 'po_id=' + sid,
        beforeSend: function() {
          var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
          var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="' + loader_img + '" style="margin-top: 10px;"></div>';
          $('.fetched-files').html(loader_html);
        },
        success: function(response) {
          $('.fetched-files').html(response);
        },
        error: function(request, status, error){

        }
      });
    });

    $(document).on('click', '.delete-purchase-order-file', function(e) {
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
          if (isConfirm) {
            $.ajax({
              method: "get",
              data: 'id=' + id,
              url: "{{ route('remove-purchase-order-file') }}",
              beforeSend: function() {
                $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
              },
              success: function(data) {
                $('#loader_modal').modal('hide');
                if (data.search('done') !== -1) {
                  myArray = new Array();
                  myArray = data.split('-SEPARATOR-');
                  let i_id = myArray[1];
                  $('#purchase-order-file-' + i_id).remove();
                  toastr.success('Success!', 'File deleted successfully.', {
                    "positionClass": "toast-bottom-right"
                  });
                }
              },
              error: function(request, status, error){
                $('#loader_modal').modal('hide');
              }
            });
          } else {
            swal("Cancelled", "", "error");
          }
        });
    });

  });
  var order_id = "{{$id}}";

//   $('.table-purchase-order-history').DataTable({
//     "sPaginationType": "listbox",
//     processing: false,
//     // "language": {
//     //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
//     // },
//     ordering: false,
//     searching: false,
//     "lengthChange": false,
//     serverSide: true,
//     "scrollX": true,
//     "scrollY": '50vh',
//     scrollCollapse: true,
//     "lengthChange": true,
//     // "bPaginate": false,
//     // "bInfo": false,
//     // lengthMenu: [100, 200, 300, 400],
//     pageLength: {{5}},
//     lengthMenu: [ 25, 50, 75, 100],

//     ajax: {
//       url: "{!! route('get-purchase-order-history') !!}",
//       data: function(data) {
//         data.order_id = order_id
//       },
//     },
//     columns: [
//       // { data: 'checkbox', name: 'checkbox' },
//       {
//         data: 'user_name',
//         name: 'user_name'
//       },
//       {
//         data: 'created_at',
//         name: 'created_at'
//       },
//       {
//         data: 'order_no',
//         name: 'order_no'
//       },
//       {
//         data: 'item',
//         name: 'item'
//       },
//       // { data: 'name', name: 'name' },
//       {
//         data: 'column_name',
//         name: 'column_name'
//       },
//       {
//         data: 'old_value',
//         name: 'old_value'
//       },
//       {
//         data: 'new_value',
//         name: 'new_value'
//       },

//     ],

//   });

//   $('.table-purchase-order-status-history').DataTable({
//     processing: false,
//     // "language": {
//     //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
//     // },
//     ordering: false,
//     searching: false,
//     "lengthChange": false,
//     serverSide: true,
//     "scrollX": true,
//     "bPaginate": false,
//     "bInfo": false,
//     lengthMenu: [100, 200, 300, 400],
//     "columnDefs": [{
//         className: "dt-body-left",
//         "targets": []
//       },
//       {
//         className: "dt-body-right",
//         "targets": []
//       },
//     ],
//     ajax: {
//       url: "{!! route('get-purchase-order-status-history') !!}",
//       data: function(data) {
//         data.order_id = order_id
//       },
//     },
//     columns: [{
//         data: 'user_name',
//         name: 'user_name'
//       },
//       {
//         data: 'created_at',
//         name: 'created_at'
//       },
//       {
//         data: 'status',
//         name: 'status'
//       },
//       {
//         data: 'new_status',
//         name: 'new_status'
//       },

//     ]
//   });
  $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27) { // esc

      $("#target_receive_date").datepicker('hide');
      $("#invoice_date").datepicker('hide');
      $("#payment_due_date").datepicker('hide');

      if ($('.inputDoubleClick').hasClass('d-none')) {
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

  $(document).on('click','.clear-values',function(e){
    var action = $(this).attr('title');
    var text = '';
    if (action == 'Undo') {
      text = 'You want to undo values !!!';
    }
    else{
      text = "You want to clear values !!!";
    }

    title = $(this).data('title');
    swal({
      title: "Are you sure!!!",
      text: text,
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, confirm it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function (isConfirm) {
      if (isConfirm) {
        var po_id = {{$id}};
        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });

        $.ajax({
          url: "{{ route('clear-revert-po-purchasing-vat') }}",
          data:{po_id:po_id,title:title,action:action},
          method:"post",
          beforeSend:function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
             $("#loader_modal").modal('show');
          },
          success: function(response){
            if(response.success === true)
            {
              if (action == 'Undo') {
                is_clear = false;
              }
              else{
                is_clear = true;
              }
              var sub_total_value = parseFloat(response.sub_total).toFixed(3);
              $('.sub-total').html(sub_total_value);
              var vat_total_amount = parseFloat(response.vat_amout).toFixed(3);
              $('.vat-amount').html(vat_total_amount);
              var amount_with_vat = parseFloat(response.total_w_v).toFixed(3);
              $('.amount-with-vat').html(amount_with_vat);

              toastr.success('Success!', response.msg,{"positionClass": "toast-bottom-right"});
              $('.po-porducts-details').DataTable().ajax.reload();
              $('.table-purchase-order-history').DataTable().ajax.reload();
            }
            if(response.success === false)
            {
              toastr.error('Sorry!', response.msg,{"positionClass": "toast-bottom-right"});
            }
          },
          error: function (request, status, error) {
        }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

$(document).on('change', '.add-supplier', function(){
// $('.add-supplier').change(function(){
    $.ajax({
        url: "{{ route('add-supplier-to-credit-note')}}",
        data: {supplier_id: $(this).val(), po_id: {{ $id }}},
        success: function(data){
            location.reload();
        }
    });
});

</script>
@stop
