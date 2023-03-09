@extends('importing.layouts.layout')
@section('title','Products Recieving')
<?php
use Carbon\Carbon;
?>
<style type="text/css">
  .supplier_invoice_number_table tr td
  {
    border: 1px solid #aaa;
    padding: 3px 5px;
  }

  .supplier_invoice_number_table tr th
  {
    padding: 3px 5px;
    border: 1px solid #aaa;
  }
  .selectDoubleClick, .inputDoubleClickGroupInfo{
    font-style: italic;
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
@section('content')
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
          <li class="breadcrumb-item"><a href="{{route('importing-receiving-queue')}}">Product Receiving Dashboard</a></li>
          <li class="breadcrumb-item active">Product Receiving Records</li>
      </ol>
  </div>
</div>

<div class="right-content pt-0">

  <div class="row headings-color mb-3">
    <input type="hidden" name="id" id="po_group_id" value="{{$po_group->id}}">
    <input type="hidden" name="po_group_total_gross_weight" id="po_group_total_gross_weight" value="{{$po_group->po_group_total_gross_weight}}">
    <input type="hidden" name="po_group_import_tax_book" id="po_group_import_tax_book" value="{{$po_group->po_group_import_tax_book}}">
    <input type="hidden" name="po_group_vat_actual" id="po_group_vat_actual" value="{{$po_group->po_group_vat_actual}}">
    <div class="col-lg-4 col-md-6 align-items-center">
      <h4>Group No {{$po_group->ref_id}}<br>Product Receiving Records</h4>
      <div class="form-group">
        <label class="mb-1 font-weight-bold">Group Status:</label>
        @if($po_group->is_cancel == 2)
        <span>Cancelled</span>
        @else
        @if($po_group->is_review == 0)
        <span>OPEN</span>
        @else
        <span>CLOSED</span>
        @endif
        @endif
      </div>
    </div>

    <div class="col-lg-5 col-md-1"></div>
    <div class="col-lg-3 col-md-5">
      <div class="row">
        <div class="col-lg-12 text-right mb-3">
          <a onclick="backFunctionality()" class="d-none">
            <!-- <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button> -->
            <span class="vertical-icons export_btn" title="Back">
              <img src="{{asset('public/icons/back.png')}}" width="27px">
            </span>
          </a>

          <a href="javascript:void(0);" class="ml-1">
            <!-- <button id="export_i_r_q_d_s" class="btn recived-button  export-btn" >Create New Export</button>    -->

          <span class="export-btn vertical-icons" id="export_i_r_q_d_s" title="Create New Export">
              <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
          </span>
          </a>
        </div>
        <div class="col-lg-12 align-items-center bg-white p-2">
      <p style="color: red;">Note.* Italic text is Double click editable.</p>
      <table class="table table-bordered mb-0">
        <tbody>
          <tr>
            <th>AWB:B/L</th>
            <td>
              <span class="m-l-15 inputDoubleClickGroupInfo" id="bill_of_landing_or_airway_bill"  data-fieldvalue="{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : ''}}">
                {{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : 'N.A'}}
              </span>

              <input type="text" style="width:100%;" name="bill_of_landing_or_airway_bill" class="fieldFocusGroupInfo d-none" value="{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : ''}}" autocomplete="off">
            </td>
          </tr>
          <tr>
            <th>Courier</th>
            <td>
              <span class="m-l-15 selectDoubleClick" id="courier" data-fieldvalue="{{$po_group->courier}}">
                {{$po_group->courier != NULL ? ($po_group->po_courier != null ? $po_group->po_courier->title : "Select") :"Select"}}
              </span>

              <select name="courier" class="selectFocus form-control d-none">
                <option value="" disabled="" selected="">Choose Type</option>
                @foreach($getCouriers as $courier)
                <option value="{{$courier->id}}" {{ ($po_group->courier == $courier->id ? "selected" : "") }} >{{$courier->title}}</option>
                @endforeach
              </select>
            </td>
          </tr>
          <tr>
            <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
            <td>
              <span class="m-l-15 inputDoubleClickGroupInfo" id="target_receive_date"  data-fieldvalue="{{$po_group->target_receive_date != null ? date('d/m/Y', strtotime($po_group->target_receive_date)) :''}}">
                {{$po_group->target_receive_date != null ? date('d/m/Y', strtotime($po_group->target_receive_date)) :" Insert Date"}}
              </span>
              <input type="text" style="width:100%;" placeholder="Expiration Date" name="target_receive_date" class="d-none target_receive_date" value="{{$po_group->target_receive_date != null ? date('d/m/Y', strtotime($po_group->target_receive_date)) :''}}" readonly="">
            </td>
          </tr>
          <tr>
            <th>Note</th>
            <td>
              <span class="m-l-15 inputDoubleClickGroupInfo" id="note"  data-fieldvalue="{{$po_group->note != null ? $po_group->note : ''}}">
                {{$po_group->note != null ? $po_group->note : 'N.A'}}
              </span>

              <input type="text" name="note" style="width:100%;" class="fieldFocusGroupInfo d-none" value="{{$po_group->note != null ? $po_group->note : ''}}" autocomplete="off">
            </td>
          </tr>
          <tr>
            <th>Supplier Inv.#</th>
            <td>
              @if($pos_supplier_invoice_no->count() > 0)
              @if($pos_supplier_invoice_no->count() == 1)
                @foreach($pos_supplier_invoice_no as $inv)
                  <span><a href="{{route('get-purchase-order-detail',['id'=> $inv->id])}}" target="_blank">{{$inv->invoice_number != null ? $inv->invoice_number : '--'}}</a></span>
                @endforeach
              @else
                <!-- Button to Open the Modal -->
                <a type="button" class="p-0 pl-1 pr-1 font-weight-bold" data-toggle="modal" data-target="#myModal" style="cursor: pointer;">
                  @php
                    $i = 0;
                  @endphp
                  @foreach($pos_supplier_invoice_no as $inv)
                  @if($i < 3)
                  {{$inv->invoice_number}} <br>
                  @endif
                  @php
                  $i++;
                  @endphp
                  @endforeach
                   ...
                </button>
              @endif
              @else
              <span>--</span>
              @endif
            </td>
          </tr>
          @if($allow_custom_invoice_number == 1 && @$po_group->ToWarehouse->is_bonded == 1)
          <tr>
            <th>Custom's Inv.#</th>
            <td>
              <span>
                <input type="text"  name="custom_invoice_number" class="custom_invoice_number fieldFocus" data-id="{{@$po_group->id}}" data-fieldvalue="{{@$po_group->custom_invoice_number}}" value="{{@$po_group->custom_invoice_number}}" style="width:100%">
              </span></td>
          </tr>
          @endif
        </tbody>
        </table>
      </div>
    </div>

    </div>
    <div class=" col-lg-3 col-md-3 input-group mb-3 d-none">
      <input type="text" class="form-control" placeholder="ID/CustomerName">
      <div class="input-group-append ml-3">
        <button class="btn recived-button" type="submit">Search</button>
       </div>
    </div>
  </div>
  <div class="row errormsgDiv d-none">
    <div class="col-lg-8 mt-4">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <div id="msgs_alerts"></div>
      </div>
    </div>
  </div>
  <div class="row headings-color">
    <div class="col-lg-6 col-md-6 d-flex align-items-center fontbold mb-3">
      <a href="javascript:void(0);" class="d-none">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf">export</button>
      </a>
      <a href="javascript:void(0);" class="ml-1 " title="Print">
        <span class="vertical-icons export-pdf2" title="Print">
          <img src="{{asset('public/icons/print.png')}}" width="27px">
        </span>
      </a>
    </div>
    <div class="col-lg-6 col-md-6 fontbold mb-3">
      <div class="pull-right">
        <a href="javascript:void(0);" class="ml-1" title="Bulk Import">
          <span class="vertical-icons" title="Bulk Import" data-toggle="modal" data-target="#import-modal">
            <img src="{{asset('public/icons/bulk_import.png')}}" width="27px">
          </span>
        </a>
        @php
          if($last_downloaded==null)
          $className='d-none';
          else
          $className='';
        @endphp
      <!--   <a download  href="{{asset('storage/app/Importing-Product-Receiving-'.$po_group->id.'.xlsx')}}" title="Download"  class=" {{$className}} download-btn ">
         <span class="vertical-icons">
            <img src="{{asset('public/icons/download.png')}}" width="27px">
        </span>

      </a> -->
        <!-- <b class="float-right download-btn-text ml-1 {{$className}} " ><i>Last created on:  @if($last_downloaded!=null){{Carbon::parse(@$last_downloaded)->format('d/m/Y H:i:s')}} @endif</i> </b> -->
        </div>
    </div>
  </div>
  <!-- export pdf form starts -->
  <form class="export-group-form" method="post" action="{{url('importing/export-group-to-pdf')}}">
    @csrf
    <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
  </form>

  <!-- export pdf2 form starts -->
  <form class="export-group-form2" method="post" action="{{url('warehouse/export-group-to-pdf2')}}">
    @csrf
    <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
    <input type="hidden" name="po_group_supplier_id" id="supplier_group_id_for_pdf" value="">
    <input type="hidden" name="po_group_product_id" id="product_group_id_for_pdf" value="">
    <input type="hidden" name="group_ref_id" id="group_ref_id" value="{{ $po_group->ref_id }}">
    <input type="hidden" name="sort_order" id="sort_order_pdf">
    <input type="hidden" name="column_name" id="column_name_pdf">
    <input type="hidden" name="blade" id="receiving_queue_details">
  </form>

  <div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 col-md-12 p-0">
        <div class="bg-white table-responsive p-3">

          <div class="alert alert-primary export-alert d-none" role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> Export file is being prepared! Please wait.. </b>
          </div>
          <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>
            <b>Export file is ready to download.
              <a class="exp_download" href="{{ url('get-download-xslx','Importing-Product-Receiving-'.$po_group->ref_id.'.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
            </b>
          </div>
          <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> Export file is being prepared by another user! Please wait.. </b>
          </div>
          {{-- updating currency conversion rate --}}
          <div class="col-lg-12 col-md-12 mt-4">
            <div class="alert alert-primary export-alert-recievable d-none"  role="alert">
              <i class="  fa fa-spinner fa-spin"></i>
              <b> Data is Updating Please wait.. </b>
            </div>
            <div class="alert alert-success export-alert-success-recievable d-none"  role="alert">
              <i class=" fa fa-check "></i>
                <b>Data Update Successfully !!!
                </b>
            </div>
          </div>
          {{-- ends here --}}
          {{-- for printing --}}
          <div class="alert alert-primary export-alert-print d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> PDF file is being prepared! Please wait.. </b>
          </div>
          <div class="alert alert-success export-alert-success-print d-none"  role="alert">
            <i class=" fa fa-check "></i>

            <b>PDF file is ready to download.
            <a download href="{{asset('public/uploads/system_pdfs/Group-No-'.$po_group->ref_id.'.pdf')}}"><u>Click Here</u></a>
            </b>
          </div>
          {{-- end print --}}
          {{-- bulk import --}}
          <div class="col-lg-12 col-md-12 mt-4">
            <div class="alert alert-primary export-alert-bulk d-none"  role="alert">
              <i class="  fa fa-spinner fa-spin"></i>
              <b> Data is Updating Please wait.. </b>
            </div>
            <div class="alert alert-success export-alert-success-bulk d-none"  role="alert">
              <i class=" fa fa-check "></i>
              <b>Data Updated Successfully !!!</b>
            </div>
            <div class="alert alert-primary export-alert-another-user-bulk d-none"  role="alert">
              <i class="  fa fa-spinner fa-spin"></i>
              <b> Data is updating by another user! Please wait.. </b>
            </div>
          </div>
          {{-- ends here --}}

          <table class="table headings-color entriestable text-center table-bordered product_table first_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>Detail</th>
                <th>PO #
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_no">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_no">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Order <br>Warehouse
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_warehouse">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_warehouse">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Order #
                  <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span> -->
                </th>
                <th>{{$global_terminologies['suppliers_product_reference_no']}}
                  <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="suppliers_product_reference_no">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="suppliers_product_reference_no">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span> -->
                </th>
                <th>Supplier
                  <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span> -->
                </th>
                <th>{{$global_terminologies['supplier_description']}}</th>
                <th>{{$global_terminologies['our_reference_number']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="our_reference_number">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="our_reference_number">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th> {{$global_terminologies['brand']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="brand">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="brand">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['product_description']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="product_description">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="product_description">
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
                <th>Customer
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th> Buying <br> Unit
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="buying_unit">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="buying_unit">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['qty']}} <br>Ordered
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_ordered">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_ordered">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['qty']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['qty']}} <br>Inv
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_inv">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_inv">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['note_two']}}</th>
                <th>{{$global_terminologies['gross_weight']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="gross_weight">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="gross_weight">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total <br>{{$global_terminologies['gross_weight']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_gross_weight">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_gross_weight">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Extra Cost (THB) <i class="fa fa-close importing-clear-filter clear-values"  id="extra_cost" data-title="extra_cost"  data-id="extra_cost" title="Clear Values"></i>
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="extra_cost">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="extra_cost">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total <br>{{$global_terminologies['extra_cost_per_billed_unit']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_extra_cost">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_extra_cost">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['extra_tax_per_billed_unit']}} <i class="fa fa-close importing-clear-filter clear-values" id="extra_tax"  data-id="extra_tax" data-title="extra_tax" title="Clear Values"></i>
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="extra_tax">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="extra_tax">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total {{$global_terminologies['extra_tax_per_billed_unit']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_extra_tax">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_extra_tax">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <!-- <th>QTY Rcvd</th> -->
                <th>{{$global_terminologies['purchasing_price']}}<br>EUR (W/O Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_price_f_wo_vat">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_price_f_wo_vat">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['purchasing_price']}}<br>EUR (+Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_price_f">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_price_f">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Discount
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="discount">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="discount">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>EUR (W/O Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_purchasing_price_f_wo_vat">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_purchasing_price_f_wo_vat">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>EUR (+Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_purchasing_price_f">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_purchasing_price_f">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Currency Conversion Rate</th>
                <th>{{$global_terminologies['purchasing_price']}}<br>THB (W/O Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_price_thb_f_wo_vat">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_price_thb_f_wo_vat">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['purchasing_price']}}<br>THB (+Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="purchasing_price_thb_f">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="purchasing_price_thb_f">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>THB (W/O Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_purchasing_price_thb_f_wo_vat">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_purchasing_price_thb_f_wo_vat">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total <br>{{$global_terminologies['purchasing_price']}}<br>THB (+Vat)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_purchasing_price_thb_f">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_purchasing_price_thb_f">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Book VAT % <i class="fa fa-close importing-clear-filter clear-values" id="book_vat" data-title="book_vat"  data-id="book_vat" title="Clear Values"></i>
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="book_vat">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="book_vat">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                {{-- <th>VAT %</th> --}}
                <th>Import <br>Tax <br>(Book)%
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="import_tax_book">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="import_tax_book">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['freight_per_billed_unit']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="freight">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="freight">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total Freight
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_freight">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_freight">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['landing_per_billed_unit']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="landing">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="landing">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Total Landing
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_landing">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_landing">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Book VAT Total <br>(THB)</th>
                {{-- <th>VAT % Tax</th> --}}
                <th>VAT Weighted %
                </th>
                <th>Unit Purchasing<br>VAT (THB)</th>
                <th>Total Purchasing<br>VAT (THB)</th>
                {{-- <th>VAT Actual<br>Tax</th> --}}
                <th>Purchasing<br>VAT %</th>
                {{-- <th>VAT Actual<br> Tax %</th> --}}
                <th>Book Import<br>Tax Total</th>
                {{-- <th>Book % Tax</th> --}}
                <th>Import<br>Weighted %
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="import_weighted_percent">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="import_weighted_percent">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                {{-- <th>Weighted %</th> --}}
                <th>{{$global_terminologies['actual_tax']}}</th>
                <th>Total Import Tax (THB)
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_import_tax">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_import_tax">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['import_tax_actual']}}</th>
                <th>Custom's Line#</th>
                <th>COGS Per Unit
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="cogs">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="cogs">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Toal COGS
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_cogs">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_cogs">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
              </tr>
            </thead>
            <tfoot align="right">
              <tr>
                <th>Total</th>
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
                <th class="qty_ordered_f"></th>
                <th></th>
                <th class="qty_inv_f"></th>
                <th></th>
                <th></th>
                <th class="total_gross_weight_f"></th>
                <th></th>
                <th class="total_extra_cost_f"></th>
                <th></th>
                <th class="total_import_tax_thb_f"></th>
                <th class="purchasing_price_f_wo_vat"></th>
                <th class="purchasing_price_f"></th>
                <th></th>
                <th class="total_purchasing_price_f_wo_vat"></th>
                <th class="total_purchasing_price_f"></th>
                <th></th>
                <th class="purchasing_price_thb_f_wo_vat"></th>
                <th class="purchasing_price_thb_f"></th>
                <th class="total_purchasing_price_thb_f_wo_vat"></th>
                <th class="total_purchasing_price_thb_f"></th>
                <th></th>
                <th></th>
                <th class="freight_per_billed_unit_f"></th>
                <th class="freight_per_billed_unit_t"></th>
                <th class="landing_per_billed_unit_f"></th>
                <th class="landing_per_billed_unit_t"></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="book_tax_f"></th>
                <th class="weighted_f"></th>
                <th class="actual_tax_f"></th>
                <th class="actual_tax_t"></th>
                <th class="actual_tax_percent_f"></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-9 col-md-6"></div>
      <div class="col-lg-3 col-md-6 input-group mb-3 ">
        <table class="table table-bordered bg-white">
          <tbody>
            <tr class="d-none">
                <div class="alert alert-primary calculation_loader col-md-12 d-none"  role="alert">
                    <i class="fa fa-spinner fa-spin"></i>
                    <b> Data is Updating Please wait.. </b>
                </div>
            </tr>
            <tr>
              <th>Import Tax</th>
              <td><input type="number" name="tax" placeholder="Import Tax" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->tax}}" value="{{$po_group->tax}}"></td>
            </tr>
            <tr>
              <th>Purchasing VAT</th>
              <td><input type="number" name="vat_actual_tax" placeholder="Purchasing VAT" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->vat_actual_tax}}" value="{{$po_group->vat_actual_tax}}"></td>
            </tr>
            <tr>
              <th>Freight Cost</th>
              <td>
                <input type="number" name="freight" placeholder="Freight Cost" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->freight}}" value="{{$po_group->freight}}" {{$check_bond == 1 ? 'disabled' : ''}}>
              </td>
            </tr>
            <tr>
              <th>Landing Cost</th>
              <td><input type="number" name="landing" placeholder="Landing Cost" class="form-control mr-1 po_group_data active" data-fieldvalue="{{$po_group->landing}}" value="{{$po_group->landing}}" {{$check_bond == 1 ? 'disabled' : ''}}></td>
            </tr>
          </tbody>
        </table>
    </div>
    </div>
    <div class="row mb-3">
      <div class="col-lg-9 col-md-9"></div>
      @if($po_group->is_review == 0)
      <div class="col-lg-3 col-md-3">
        <a href="javascript:void(0);">
          <button type="button" data-id="{{$id}}" class="btn-color btn float-right confirm-to-stock-btn"><i class="fa fa-check"></i> Confirm Cost</button>
        </a>
      </div>
      @endif
    </div>

    <!-- ---------------History------- -->
    <div class="row">
      <div class="col-lg-6">
        <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
          <table class="table-po-group-order-history headings-color entriestable table table-bordered text-center">
            <thead class="sales-coordinator-thead ">
              <tr>
                <th>User </th>
                <th>Date/Time</th>
                <th>Group #</th>
                <th>{{$global_terminologies['our_reference_number']}}</th>
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

{{-- Greater Quantity Modal --}}
<div class="modal" id="greater_quantity">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Purchase Order's </h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="fetched-po">
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

<form id="export_receiving_queue_detail" method="post"  action="{{route('export-importing-product-receiving-record') }}">
  @csrf
  <input type="hidden" name="id" value="{{$po_group->id}}">
  <input type="hidden" name="status" value="OPEN">
  <input type="hidden" name="sort_order" id="sort_order">
  <input type="hidden" name="column_name" id="column_name">
</form>

<!-- The Modal For Supplier Invoice number -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">PO's Supplier Inv.#</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <table width="100%" class="supplier_invoice_number_table">
          <thead>
            <tr>
              <th>S.No</th>
              <th>Supplier Inv.#</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pos_supplier_invoice_no as $inv)
              <tr>
                <td>{{$loop->iteration}}</td>
                <td> <a href="{{route('get-purchase-order-detail',['id'=> $inv->id])}}" target="_blank">{{$inv->invoice_number}}</a> </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

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
          <label class="mt-2">
            <strong>Note : </strong>Please use the downloaded export file for upload only.<span class="text-danger"> Also Don't Upload Empty File.</span>
          </label>
          <form class="upload-excel-form" id="upload-excel-form" enctype="multipart/form-data">
            @csrf
            <input type="file" class="form-control" name="product_excel" id="product_excel" accept=".xls,.xlsx" required="" style="white-space: pre-wrap;"><br>
            <input type="hidden" name="group_id" value="{{$po_group->id}}">
            <button class="btn btn-info product-upload-btn" type="submit">Upload</button>
          </form>
        </div>
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



  var visibility_arr = [];
  var reorder_arr = [];
  var is_child_table = false;
  var is_clear = false;
  var title = '';

  $(".target_receive_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });

$(function(e){


  var hidden_cols = "{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}";
  hidden_cols = hidden_cols.split(',');

  var show_custom_line_number_choice = "{{$show_custom_line_number}}";
  var show_custom_line_number = '';
  if(show_custom_line_number_choice == 1 && "{{$po_group->ToWarehouse->is_bonded}}" == 1)
  {
    show_custom_line_number = true;
    if( hidden_cols.includes("43") )
    {
      show_custom_line_number = false;
    }
  }
  else
  {
    show_custom_line_number = false;
  }

  $(document).on('click','.condition',function(){
    var value = $(this).val();
    var id = $(this).data('id');
    savePoData(value,id);
  });

  function savePoData(value,id){
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('importing/edit-po-goods') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,cust_detail_id:cust_detail_id,new_select_value:new_select_value},
      data: 'value='+value+'&'+'id'+'='+id,
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(data)
      {
        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
          $("#receive-table").DataTable().ajax.reload(null, false );
          return true;
        }
      },
      error: function (request, status, error) {
        swal("Something Went Wrong! Contact System Administrator!");
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
  var id = $('#po_group_id').val();

  visibility_arr = [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}];

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });

  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');


    $('#sort_order').val(order);
    $('#column_name').val(column_name);

    $('#sort_order_pdf').val(order);
    $('#column_name_pdf').val(column_name);

    $('.product_table').DataTable().ajax.reload();

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
  var table2 = $('.product_table').DataTable({
    "sPaginationType": "listbox",
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    fixedHeader: false,
    dom: 'Blfrtip',
    pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    colReorder: {
      realtime: false
    },
    columnDefs: [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9,10,11 ] },
      { className: "dt-body-right", "targets": [12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29] }
    ],
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
    ajax:{
      beforeSend: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      url : "{{ url('importing/get-po-group-product-details')}}"+"/"+id,
      data: function (data) {
        data.sort_order = order,
        data.column_name = column_name
      },
      method: "post",
    },
    columns: [
        {
          "className":      'details-control',
          "orderable":      false,
          "searchable":      false,
          "defaultContent": ''
        },
        { data: 'po_number', name: 'po_number' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'order_no', name: 'order_no' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'supplier', name: 'supplier' },
        { data: 'supplier_description', name: 'supplier_description' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'brand', name: 'brand' },
        { data: 'desc', name: 'desc' },
        { data: 'type', name: 'type' },
        { data: 'customer', name: 'customer' },
        { data: 'unit', name: 'unit' },
        // { data: 'buying_currency', name: 'buying_currency' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'original_qty', name: 'original_qty' },
        { data: 'qty', name: 'qty' },
        { data: 'product_notes', name: 'product_notes' },
        { data: 'pod_unit_gross_weight', name: 'pod_unit_gross_weight' },
        { data: 'pod_total_gross_weight', name: 'pod_total_gross_weight' },
        { data: 'pod_unit_extra_cost', name: 'pod_unit_extra_cost' },
        { data: 'pod_total_extra_cost', name: 'pod_total_extra_cost' },
        { data: 'pod_unit_extra_tax', name: 'pod_unit_extra_tax' },
        { data: 'pod_total_extra_tax', name: 'pod_total_extra_tax' },
        /*{ data: 'qty_receive', name: 'qty_receive' },*/
        { data: 'buying_price_wo_vat', name: 'buying_price_wo_vat' },
        { data: 'buying_price', name: 'buying_price' },
        { data: 'discount', name: 'discount' },
        { data: 'total_buying_price_wo_vat', name: 'total_buying_price_wo_vat' },
        { data: 'total_buying_price', name: 'total_buying_price' },
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
        { data: 'buying_price_in_thb_wo_vat', name: 'buying_price_in_thb_wo_vat' },
        { data: 'buying_price_in_thb', name: 'buying_price_in_thb' },
        { data: 'total_buying_price_in_thb_wo_vat', name: 'total_buying_price_in_thb_wo_vat' },
        { data: 'total_buying_price_in_thb', name: 'total_buying_price_in_thb' },
        { data: 'vat_act', name: 'vat_act' },
        { data: 'import_tax_book', name: 'import_tax_book' },
        { data: 'freight', name: 'freight' },
        { data: 'total_freight', name: 'total_freight' },
        { data: 'landing', name: 'landing' },
        { data: 'total_landing', name: 'total_landing' },

        { data: 'vat_percent_tax', name: 'vat_percent_tax' },
        { data: 'vat_weighted_percent', name: 'vat_weighted_percent' },
        { data: 'vat_act_tax', name: 'vat_act_tax' },
        { data: 'total_vat_act_tax', name: 'total_vat_act_tax' },
        { data: 'vat_act_tax_percent', name: 'vat_act_tax_percent' },

        { data: 'book_tax', name: 'book_tax' },
        { data: 'weighted', name: 'weighted' },
        { data: 'actual_tax', name: 'actual_tax' },
        { data: 'total_actual_tax', name: 'total_actual_tax' },
        { data: 'actual_tax_percent', name: 'actual_tax_percent' },
        { data: 'custom_line_number', name: 'custom_line_number', visible: show_custom_line_number },
        { data: 'product_cost', name: 'product_cost' },
        { data: 'total_product_cost', name: 'total_product_cost' },
    ],
    createdRow: function (row, data, index) {
      if (data.occurrence < 2 ) {
        var td = $(row).find("td:first");
        td.removeClass('details-control');
      }
      if (is_clear) {
        if (title == 'extra_cost') {
          $('#extra_cost').attr('title', 'Undo');
          $('#extra_cost').removeClass('fa-close');
          $('#extra_cost').addClass('fa-undo');
        }
        else if (title == 'extra_tax') {
          $('#extra_tax').attr('title', 'Undo');
          $('#extra_tax').removeClass('fa-close');
          $('#extra_tax').addClass('fa-undo');
        }
        else if (title == 'book_vat') {
          $('#book_vat').attr('title', 'Undo');
          $('#book_vat').removeClass('fa-close');
          $('#book_vat').addClass('fa-undo');
        }
      }
      else{
        if (title == 'extra_cost') {
          $('#extra_cost').attr('title', 'Clear Values');
          $('#extra_cost').removeClass('fa-undo');
          $('#extra_cost').addClass('fa-close');
        }
        else if (title == 'extra_tax') {
          $('#extra_tax').attr('title', 'Clear Values');
          $('#extra_tax').removeClass('fa-undo');
          $('#extra_tax').addClass('fa-close');
        }
        else if (title == 'book_vat') {
          $('#book_vat').attr('title', 'Clear Values');
          $('#book_vat').removeClass('fa-undo');
          $('#book_vat').addClass('fa-close');
        }
      }
    },
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });

      @if($display_prods)
        reorder_arr = [{{ $display_prods->display_order }}];
        table2.colReorder.order( [{{ $display_prods->display_order }}]);
      @endif

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
        url : "{{ url('importing/get-po-group-product-details-footer-values')}}"+"/"+id,
        beforeSend:function(){
          $( '.qty_ordered_f' ).html('Loading...');
          $( '.qty_inv_f' ).html('Loading...');
          $( '.total_gross_weight_f' ).html('Loading...');
          $( '.total_extra_cost_f' ).html('Loading...');
          $( '.total_import_tax_thb_f' ).html('Loading...');
          $( '.purchasing_price_f' ).html('Loading...');
          $( '.total_purchasing_price_f' ).html('Loading...');
          $( '.purchasing_price_thb_f' ).html('Loading...');
          $( '.total_purchasing_price_thb_f' ).html('Loading...');
          // $( '.freight_per_billed_unit_f' ).html('Loading...');
          $( '.freight_per_billed_unit_t' ).html('Loading...');
          // $( '.landing_per_billed_unit_f' ).html('Loading...');
          $( '.landing_per_billed_unit_t' ).html('Loading...');
          $( '.book_tax_f' ).html('Loading...');
          $( '.actual_tax_t' ).html('Loading...');
          $( '.weighted_f' ).html('Loading...');
          $( '.total_purchasing_price_thb_f_wo_vat' ).html('Loading...');
          // $( '.actual_tax_f' ).html('Loading...');
          // $( '.actual_tax_percent_f' ).html('Loading...');
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        },
        success:function(result){
          $( '.qty_ordered_f' ).html(result.qty_ordered_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.qty_inv_f' ).html(result.qty_inv_sum.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_gross_weight_f' ).html(result.total_gross_weight.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_extra_cost_f' ).html(result.total_extra_cost.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_import_tax_thb_f' ).html(result.total_extra_tax.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $('.total_import_tax_thb').html(result.total_extra_tax.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.purchasing_price_f' ).html(result.buying_price.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_purchasing_price_f' ).html(result.total_buying_price.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.purchasing_price_thb_f' ).html(result.buying_price_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          $( '.total_purchasing_price_thb_f' ).html(result.t_buying_price_thb.toFixed(3).toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ","));
          // $( '.freight_per_billed_unit_f' ).html(result.freight.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{2})+(?!\d))/g, ","));
          // $( '.landing_per_billed_unit_f' ).html(result.landing.toFixed(2).toString().replace(/\B(?<!\.\d*)(?=(\d{2})+(?!\d))/g, ","));
          $( '.book_tax_f' ).html(result.book_per_tax);
          $( '.actual_tax_t' ).html(result.total_import_tax_cal);
          $( '.weighted_f' ).html(result.weighted_sum);
          $( '.freight_per_billed_unit_t' ).html(result.total_freight);
          $( '.landing_per_billed_unit_t' ).html(result.total_landing);
          $( '.total_purchasing_price_thb_f_wo_vat' ).html(result.total_unit_price_in_thb);
          // $( '.actual_tax_f' ).html(result.actual_tax_col_sum);
          // $( '.actual_tax_percent_f' ).html(result.actual_tax_per_sum);
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        },
        error: function(){

        }
      });
    },
    drawCallback: function ( settings ) {
      $('#loader_modal').modal('hide');
    }
  });

  table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

    $(".product_table > tbody > tr").each(function () {
      var tr = $(this);
      var row = table2.row(tr);
      var tableId = 'products-' + tr.attr('id');
      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        tr.removeClass('details');
        tr.removeClass('greyRow');
      }
    });

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
      data : {type:'importing_open_product_receiving',column_id:col},
      beforeSend: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(data){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $("#loader_modal").modal('hide');
        if(data.success == true)
        {
          visibility_arr = [];
          visibility_arr = data.cols_arr;
          // table2.ajax.reload();
        }
      },
      error: function(request, status, error)
      {
        $("#loader_modal").modal('hide');
      }
    });
  });

  table2.on( 'column-reorder', function ( e, settings, details ) {
    if(is_child_table == false){
    $(".product_table > tbody > tr").each(function () {
      var tr = $(this);
      var row = table2.row(tr);
      var tableId = 'products-' + tr.attr('id');
      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        tr.removeClass('details');
        tr.removeClass('greyRow');
      }
    });

    reorder_arr = [];
    reorder_arr = table2.colReorder.order();

    $.get({
      url : "{{ route('column-reorder') }}",
      dataType : "json",
      data : "type=importing_open_product_receiving&order="+table2.colReorder.order(),
      beforeSend: function(){

      },
      success: function(data){
        // $('#loader_modal').modal('show');
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      },
      error: function(request, status, error){
        // $("#loader_modal").modal('hide');
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
    var headerCell = $( table2.column( details.to ).header() );
    table2.columns.adjust();
    headerCell.addClass( 'reordered' );
    }
  });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
    if(e.keyCode == 13) {
      table2.search($(this).val()).draw();
    }
  });

  function template( d ) {
  return '<table class="table entriestable table-bordered text-center second_table" id="products-'+d.id+'">'+
  '<thead style="display:none";>'+
  '<tr>'+
    '<th></th>'+
    '<th>PO #</th>'+
    '<th>Order<br>Warehouse</th>'+
    '<th>Order#</th>'+
    '<th>{{$global_terminologies["suppliers_product_reference_no"]}}</th>'+
    '<th>Supplier</th>'+
    '<th>{{$global_terminologies["supplier_description"]}}</th>'+
    '<th>{{$global_terminologies["our_reference_number"]}}</th>'+
    '<th>{{$global_terminologies["brand"]}}</th>'+
    '<th>{{$global_terminologies["product_description"]}}</th>'+
    '<th>{{$global_terminologies["type"]}}</th>'+
    '<th>Customer</th>'+
    '<th>Buying <br>Unit</th>'+
    '<th>{{$global_terminologies["qty"]}} <br>Ordered</th>'+
    '<th>{{$global_terminologies["qty"]}}</th>'+
    '<th>{{$global_terminologies["qty"]}} <br>Inv</th>'+
    '<th>{{$global_terminologies["note_two"]}} <br>Inv</th>'+
    '<th>>Gross <br>Weight</th>'+
    '<th>Total <br>Gross <br>Weight</th>'+
    '<th>Unit <br>Extra <br>Cost</th>'+
    '<th>Total <br>Extra <br>Cost</th>'+
    '<th>Unit <br>Extra <br>Tax</th>'+
    '<th>Total <br>Extra <br>Tax</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}} <br>EUR (W/O Vat)</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}} <br>EUR (+Vat)</th>'+
    '<th>Total <br>{{$global_terminologies["purchasing_price"]}} <br>EUR (W/O Vat)</th>'+
    '<th>Total <br>{{$global_terminologies["purchasing_price"]}} <br>EUR (+Vat)</th>'+
    '<th>Currency <br>Conversion <br>Rate</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}}<br>(THB) (W/O Vat)</th>'+
    '<th>{{$global_terminologies["purchasing_price"]}}<br>(THB) (+Vat)</th>'+
    '<th>Discount</th>'+
    '<th>Total <br>Buying <br>Price<br>(THB) (W/O Vat)</th>'+
    '<th>Total <br>Buying <br>Price<br>(THB) +Vat)</th>'+
    '<th>VAT %</th>'+
    '<th>Import <br>Tax <br>(Book)%</th>'+
    '<th>Freight</th>'+
    '<th>Total Freight</th>'+
    '<th>Landing</th>'+
    '<th>Total Landing</th>'+
    '<th>VAT % Tax</th>'+
    '<th>VAT Weighted %</th>'+
    '<th>VAT Actual<br> Tax</th>'+
    '<th>Total VAT Actual<br> Tax</th>'+
    '<th>VAT Actual<br> Tax %</th>'+
    '<th>Book % Tax</th>'+
    '<th>Weighted %</th>'+
    '<th>Actual<br> Tax</th>'+
    '<th>Total Actual<br> Tax</th>'+
    '<th>Actual<br> Tax %</th>'+
    '<th>Custom\'s Line#</th>'+
    '<th>COGS |Per Unit</th>'+
    '<th>Total COGS</th>'+
  '</tr>'+
  '</thead>'+
  '</table>';
 }

  $('.product_table tbody').on('click', 'td.details-control', function () {
    is_child_table = false;
    var tr = $(this).closest('tr');
    var row = table2.row(tr);
    var tableId = 'products-' + row.data().id;

    if (row.child.isShown()) {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
      tr.removeClass('details');
      tr.removeClass('greyRow');
      is_child_table = false;
    }
    else
    {
      // Open this row
      is_child_table = true;
      row.child(template(row.data())).show();
      initTable(tableId, row.data());
      tr.addClass('shown');
      tr.addClass('details');
      tr.addClass('greyRow');
      tr.next().find('td').addClass('no-padding bg-gray');
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
  });

  function initTable(tableId, data) {
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    var product_id = data.product_id;
    var group_id   = data.po_group_id;
    var supplier_id = data.supplier_id;
    var detail_table = $('#'+tableId).DataTable({
      serverSide: true,
      responsive: true,
      ordering: false,
      searching: false,
      paging: false,
      processing: false,
      bInfo : false,
      retrieve: true,
      colReorder: {
        realtime: false
      },
      scrollX: true,
      scrollY: true,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      columnDefs: [
        { targets: visibility_arr,  visible: false },
        { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9,10,11] },
        { className: "dt-body-right", "targets": [12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29] },
      ],
      ajax:
      {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url: "{!! route('get-po-group-every-product-details-importing')  !!}",
        data: function(data) { data.product_id = product_id,data.group_id = group_id,data.supplier_id = supplier_id } ,
        method: 'POST',
      },
      fixedColumns: true,
      columns: [
        {
          "className":      '',
          "orderable":      false,
          "searchable":      false,
          "defaultContent": ''
        },
        { data: 'po_no', name: 'po_no' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'order_no', name: 'order_no' },
        { data: 'supplier_ref_no', name: 'supplier_ref_no' },
        { data: 'supplier_ref_name', name: 'supplier_ref_name' },
        { data: 'supplier_description', name: 'supplier_description' },
        { data: 'product_ref_no', name: 'product_ref_no' },
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'type', name: 'type' },
        { data: 'customer', name: 'customer' },
        { data: 'buying_unit', name: 'buying_unit' },
        { data: 'quantity_ordered', name: 'quantity_ordered' },
        { data: 'original_qty', name: 'original_qty' },
        { data: 'quantity_inv', name: 'quantity_inv' },
        { data: 'product_notes', name: 'product_notes' },

        { data: 'pod_unit_gross_weight', name: 'pod_unit_gross_weight' },
        { data: 'pod_total_gross_weight', name: 'pod_total_gross_weight' },
        { data: 'unit_extra_cost', name: 'unit_extra_cost' },
        { data: 'total_extra_cost', name: 'total_extra_cost' },
        { data: 'unit_extra_tax', name: 'unit_extra_tax' },
        { data: 'total_extra_tax', name: 'total_extra_tax' },
        { data: 'buying_price_wo_vat', name: 'buying_price_wo_vat' },
        { data: 'buying_price', name: 'buying_price' },
        { data: 'discount', name: 'discount' },
        { data: 'total_buying_price_wo_vat', name: 'total_buying_price_wo_vat' },
        { data: 'total_buying_price_o', name: 'total_buying_price_o' },     //copied
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
        { data: 'buying_price_in_thb_wo_vat', name: 'buying_price_in_thb_wo_vat' },
        { data: 'unit_price_in_thb', name: 'unit_price_in_thb' },
        { data: 'total_buying_price_in_thb_wo_vat', name: 'total_buying_price_in_thb_wo_vat' },
        { data: 'total_buying_price', name: 'total_buying_price' },
        { data: 'vat_act', name: 'vat_act' },
        { data: 'import_tax_book', name: 'import_tax_book' },

        { data: 'freight', name: 'freight' },
        { data: 'total_freight', name: 'total_freight' },
        { data: 'landing', name: 'landing' },
        { data: 'total_landing', name: 'total_landing' },

        { data: 'vat_percent_tax', name: 'vat_percent_tax' },
        { data: 'vat_weighted_percent', name: 'vat_weighted_percent' },
        { data: 'vat_act_tax', name: 'vat_act_tax' },
        { data: 'total_vat_act_tax', name: 'total_vat_act_tax' },
        { data: 'vat_act_tax_percent', name: 'vat_act_tax_percent' },

        { data: 'book_tax', name: 'book_tax' },
        { data: 'weighted', name: 'weighted' },
        { data: 'actual_tax', name: 'actual_tax' },
        { data: 'total_actual_tax', name: 'total_actual_tax' },
        { data: 'actual_tax_percent', name: 'actual_tax_percent' },
        { data: 'empty_col', name: 'empty_col' , visible: show_custom_line_number},
        { data: 'product_cost', name: 'product_cost'},
        { data: 'total_product_cost', name: 'total_product_cost'},
      ],
      initComplete: function ( settings ) {
        $('#loader_modal').modal('hide');


        @if($display_prods)
        //   detail_table.colReorder.order(reorder_arr);
          detail_table.colReorder.order(table2.colReorder.order());
          var sort_arr = [{{ $display_prods->display_order }}];
          $.each(sort_arr, function( index, value ) {
            var headerHeight = $('.first_table tbody tr td:nth-child('+value+')').innerWidth();
            headerHeight -=10;
            $('.second_table tbody tr td:nth-child('+value+')').css('width', headerHeight);
            $('.second_table tbody tr td:nth-child('+value+')').css('max-width', headerHeight);
            $('.second_table tbody tr td:nth-child('+value+')').css('overflow', "hidden");
          });
        @else
          for (var i = 1; i <= 41; i++) {
            var headerHeight = $('.first_table tbody tr td:nth-child('+i+')').innerWidth();
            headerHeight -=10;
            $('.second_table tbody tr td:nth-child('+i+')').css('width', headerHeight);
            $('.second_table tbody tr td:nth-child('+i+')').css('max-width', headerHeight);
            $('.second_table tbody tr td:nth-child('+i+')').css('overflow', 'hidden');
            // $('.first_table tbody tr td:nth-child('+i+')').css('width', headerHeight);
          }
        @endif
        is_child_table = false;
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      },
    })
  }

   var order_id = "{{$id}}";

  $('.table-po-group-order-history').DataTable({
    "sPaginationType": "listbox",
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
    dom: 'Blfrtip',
    pageLength: {{50}},
    lengthMenu: [100, 200, 300, 400],
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],

    ajax: {
      url: "{!! route('get-pogroupproduct-history') !!}",
      data: function(data) {
        data.order_id = order_id
      },
    },
    columns: [
      // { data: 'checkbox', name: 'checkbox' },
      { data: 'user_name', name: 'user_name' },
      { data: 'created_at', name: 'created_at' },
      { data: 'order_no', name: 'order_no' },
      { data: 'item', name: 'item' },
      // { data: 'name', name: 'name' },
      { data: 'column_name', name: 'column_name' },
      { data: 'old_value', name: 'old_value' },
      { data: 'new_value', name: 'new_value' },
    ],
  });

  $(document).on("dblclick",".inputDoubleClicks",function(){
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

  $(document).on('keypress keyup focusout', '.fieldFocuss', function(e){

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
    var pId = $(this).data('id');
    var thisPointer = $(this);

    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }
    else
    {
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
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pId,fieldvalue);
      }
    }
   }
  });

  $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().focus();
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    //$(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocus',function(e) {
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27)
    {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
    }
    if(e.keyCode == 13 || e.which == 0)
    {
      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var pod_id= $(this).data('id');
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id,fieldvalue);
        $(this).data('fieldvalue',thisPointer.val());
      }
    }
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocusDetail',function(e) {
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27)
    {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
    }
    if(e.keyCode == 13 || e.which == 0)
    {
      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var pod_id= $(this).data('id');
        var thisPointer = $(this);
        var pogid = $(this).data('pogid');
        var field_name = thisPointer.attr('name');
        var field_value = thisPointer.val();
        // saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id,fieldvalue);
        $(this).data('fieldvalue',thisPointer.val());

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('importing/edit-po-group-product-details-each') }}",
        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&pogid='+pogid+'&old_value='+fieldvalue,
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          $("#loader_modal").modal('hide');
          if(field_name == 'unit_extra_cost' || field_name == 'total_extra_cost' || field_name == 'unit_extra_tax' || field_name == 'total_extra_tax')
          {
            // $('.product_table').DataTable().ajax.reload();
            $('#pod_unit_extra_cost_'+pod_id).val(data.pod.unit_extra_cost != null ? parseFloat(data.pod.unit_extra_cost).toFixed(2) : (0).toFixed(2));
            $('#pod_total_extra_cost_'+pod_id).val(data.pod.total_extra_cost != null ? parseFloat(data.pod.total_extra_cost).toFixed(2) : (0).toFixed(2));
            $('#pod_unit_extra_tax_'+pod_id).val(data.pod.unit_extra_tax != null ? parseFloat(data.pod.unit_extra_tax).toFixed(2) : (0).toFixed(2));
            $('#pod_total_extra_tax_'+pod_id).val(data.pod.total_extra_tax != null ?  parseFloat(data.pod.total_extra_tax).toFixed(2) : (0).toFixed(2));

            $('#pod_unit_extra_cost_avg_'+pogid).html(parseFloat(data.pogpd.unit_extra_cost).toFixed(2));
            $('#pod_total_extra_cost_avg_'+pogid).html(parseFloat(data.pogpd.total_extra_cost).toFixed(2));
            $('#pod_unit_extra_tax_avg_'+pogid).html(parseFloat(data.pogpd.unit_extra_tax).toFixed(2));
            $('#pod_total_extra_tax_avg_'+pogid).html(parseFloat(data.pogpd.total_extra_tax).toFixed(2));
            $('.table-po-group-order-history').DataTable().ajax.reload();
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

              return true;
          }

            if(data.success == true)
            {
              table2.on('xhr.dt', function() {
                // var position = table2.scroller.page().start;
                table2.one('draw', function() {
                  // table2.row(position).scrollTo(false);
                });
              });
              return true;
            }
        },
        error: function (request, status, error) {
          swal("Something Went Wrong! Contact System Administrator!");
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

  $(document).on('keyup keypress focusout', '.po_group_data', function(e){
    $(this).addClass('active');
  });

  $(document).on('keyup keypress focusout', '.po_group_data', function(e){
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27)
    {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      return false;
    }
    if((e.keyCode == 13 || e.which == 0) && $(this).hasClass('active'))
    {
      $(this).removeClass('active');


      /*The Below Code is necessary because we need to calculate freight and landing which is based on total gross weight*/
      var total_gross_weight = $('#po_group_total_gross_weight').val();
      if(total_gross_weight == 0 && ($(this).attr('name') == 'freight' || $(this).attr('name') == 'landing'))
      {
        swal('Please Enter Total Gross Weight First!');
        $(this).val(fieldvalue);
        return false;
      }

      /*The Below Code is necessary because we need to calculate Actual tax which is based on total Import tax book*/
      var total_gross_weight = $('#po_group_import_tax_book').val();
      var check_bond = "{{$check_bond}}";
      // if(total_gross_weight == 0 && ($(this).attr('name') == 'tax') && check_bond == 0)
      // {
      //   swal('Please Enter Import Tax Book First!');
      //   $(this).val(fieldvalue);
      //   return false;
      // }

      /*The Below Code is necessary because we need to calculate Vat Actual tax which is based on total vat actual*/
      // var total_vat_actual = $('#po_group_vat_actual').val();
      // if(total_vat_actual == 0 && ($(this).attr('name') == 'vat_actual_tax'))
      // {
      //   swal('Please Enter VAT First!');
      //   $(this).val(fieldvalue);
      //   return false;
      // }

      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var gId = "{{$po_group->id}}";
        var attr_name = $(this).attr('name');
        var new_value = $(this).val();
        $(this).removeData('fieldvalue');
        // $(this).data('fieldvalue', new_value);
        $(this).attr("data-fieldvalue",new_value);
        $(this).attr('value', new_value);

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method: 'post',
          url: '{{route("save-po-group-data")}}',
          dataType: 'json',
          data:'gId='+gId+'&'+attr_name+'='+new_value+'&'+'old_value='+fieldvalue,
          beforeSend: function(){
            $('.calculation_loader').removeClass('d-none');
            $('.confirm-to-stock-btn').attr('disabled',true);
            },
          success: function (data) {
            $('#loader_modal').modal('hide');
            if(data.success == true){
                $('.calculation_loader').addClass('d-none');
                $('.confirm-to-stock-btn').attr('disabled',false);
                toastr.success('Success!', 'Value updated successfully.',{"positionClass": "toast-bottom-right"});
                $('.table-po-group-order-history').DataTable().ajax.reload();
            }
          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
        });
      }
    }
  });

  // confirm po button code here
  $(document).on('click','.confirm-to-stock-btn', function(e){
    check_detail = "{{$po_group->po_group_detail->count()}}";
    var check_bond = "{{$check_bond}}";
    var check_to_bond = "{{$check_to_bond}}";
    // alert(check_bond);
    if(check_detail == 0)
    {
      toastr.warning('Sorry!', 'Group have no PO(s).',{"positionClass": "toast-bottom-right"});
      return false;
    }
    var actual_tax = $("input[name=tax]").val();
    var freight = $("input[name=freight]").val();
    var landing = $("input[name=landing]").val();
    var vat_tax = $("input[name=vat_actual_tax]").val();
    if(actual_tax == '' && check_to_bond == 0)
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Actual Tax !</b>'});
      return false;
    }
    if(freight == '' && check_bond == 0)
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Freight !</b>'});
      return false;
    }
    if(landing == '' && check_bond == 0)
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Landing !</b>'});
      return false;
    }
    // if(vat_tax == '')
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Must Fill VAT (Actual) !</b>'});
    //   return false;
    // }

    var id = $(this).data('id');   //po_Group id
    swal({
      title: "Are you sure!!!",
      text: "You want to confirm to stock?",
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
        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });

        $.ajax({
          method:"post",
          data:'id='+id,
          url: "{{ route('confirm-po-group-product-detail-cost') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
             $("#loader_modal").modal('show');
             $(".confirm-to-stock-btn").attr('disabled',true);
          },
          success: function(response){
              if(response.success === true){
              toastr.success('Success!', 'Cost Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('importing/importing-receiving-queue')}}";
            }
          },
          error: function (request, status, error) {
            $("#loader_modal").modal('hide');
          // swal("Something Went Wrong! Contact System Administrator!");
          toastr.success('Success!', 'Cost Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('importing/importing-receiving-queue')}}";

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
  });

  function saveSupData(thisPointer,field_name,field_value,pod_id,fieldvalue){
    var po_group_id = $('#po_group_id').val();

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('importing/edit-po-group-product-details') }}",
        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&po_group_id='+po_group_id+'&old_value='+fieldvalue,
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          $("#loader_modal").modal('hide');
          if(field_name == 'unit_extra_cost' || field_name == 'total_extra_cost' || field_name == 'unit_extra_tax' || field_name == 'total_extra_tax'|| field_name == 'pogpd_vat_actual'|| field_name == 'import_tax_book'|| field_name == 'sup_ref_no')
          {
            $('.product_table').DataTable().ajax.reload();
            $('.table-po-group-order-history').DataTable().ajax.reload();
          }
          if(field_name == 'currency_conversion_rate')
          {
            $("#loader_modal").modal('show');
            $('.table-po-group-order-history').DataTable().ajax.reload();
            if(data.status==1)
            {
              $('.export-alert-success-recievable').addClass('d-none');
              $('.export-alert-recievable').removeClass('d-none');
              console.log("Calling Function from first part");
              checkStatusForReceivingExport();
            }
            else if(data.status==2)
            {
              $('.export-alert-success-recievable').addClass('d-none');
              $('.export-alert-recievable').removeClass('d-none');
              checkStatusForReceivingExport();
            }
          }
          else
          {
            if(data.gross_weight == true)
            {
              $('.product_table').DataTable().ajax.reload();
              $('.table-po-group-order-history').DataTable().ajax.reload();

              $('#po_group_total_gross_weight').val(data.po_group.po_group_total_gross_weight);
              table2.on('xhr.dt', function() {
                // var position = table2.scroller.page().start;
                table2.one('draw', function() {
                  // table2.row(position).scrollTo(false);
                });
              });
              return true;
            }
            if(data.import_tax == true)
            {
              $('#po_group_import_tax_book').val(data.po_group.po_group_import_tax_book);
              table2.on('xhr.dt', function() {
                // var position = table2.scroller.page().start;
                table2.one('draw', function() {
                  // table2.row(position).scrollTo(false);
                });
              });
              return true;
            }
            if(data.success == true)
            {
              table2.on('xhr.dt', function() {
                // var position = table2.scroller.page().start;
                table2.one('draw', function() {
                  // table2.row(position).scrollTo(false);
                });
              });
              return true;
            }
            if(data.custom_invoice_number == true)
            {
              toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
              return true;
            }
          }
        },
        error: function (request, status, error) {
          swal("Something Went Wrong! Contact System Administrator!");
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

  function checkStatusForReceivingExport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-old-data-status')}}",
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
          $('.export-alert-success-recievable').removeClass('d-none');
          $('.export-alert-recievable').addClass('d-none');
          $('.export-btn-recievable').html('Create New Export');
          $('.export-btn-recievable').prop('disabled',false);
          $(".product_table").DataTable().ajax.reload();

          // $('.download-btn-div ').html('');
          // $('.download-btn-div ').append('<a class="btn button-st download-btn" download style="flex: 0.4;" href="'+href+'">Download</a><span> <i> <b>&nbsp;&nbsp;&nbsp;Last Downloaded On : '+last_downloaded+'</b></i></span>');
          // $('.download-btn-div ').removeClass('d-none');
          $('.primary-btn').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success-recievable').addClass('d-none');
          $('.export-alert-recievable').addClass('d-none');
          $('.export-btn-recievable').html('Create New Export');
          $('.export-btn-recievable').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $('#export_i_r_q_d').on('click',function(){
    $("#export_receiving_queue_detail").submit();
  });

  $(document).on('click','.export-btn',function(){
    var form=$('#export_receiving_queue_detail');
    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-importing-product-receiving-record')}}",
      data:form_data,
      beforeSend:function(){
        /*$('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
        // $('.export-btn').html('EXPORT is being Prepared');*/
      },
      success:function(data){
        if(data.status==1)
        {
          //swal("Wait!", "File is getting ready and will be available for download", "warning");
          //$("#loader_modal").modal('hide');
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
          //checkStatusForProducts();
        }

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  function checkStatusForProducts()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-importing-receiving-products')}}",
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
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          $('.download-btn').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-btn').attr('title','EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          checkStatusForProducts();
        }
      }
    });
  });

  $(document).on('click','.download-btn',function(){
    $('.export-alert-success').addClass('d-none');
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
    url: "{{ route('upload-bulk-product-in-group-detail') }}",
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
      $('#loader_modal').modal('hide');
      $('#import-modal').modal('hide');
      $(".product-upload-btn").attr("disabled", false);
      $('.table-po-group-order-history').DataTable().ajax.reload();
      if(data.success == true)
      {
        toastr.success('Success!', data.msg, {"positionClass": "toast-bottom-right"});
        if(data.errorMsg != null && data.errorMsg != '')
        {
          $('#msgs_alerts').html(data.errorMsg);
          $('.errormsgDiv').removeClass('d-none');
        }
        $('.exp_imp_btn').click();
        $('.upload-excel-form')[0].reset();
        $('.product_table').DataTable().ajax.reload();

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
        $('.product_table').DataTable().ajax.reload();
        $('.table-po-group-order-history').DataTable().ajax.reload();
      }
      if(data.success == false)
      {
        toastr.error('Error!', data.msg, {"positionClass": "toast-bottom-right"});
        // $('.exp_imp_btn').click();
        $('.upload-excel-form')[0].reset();
        $('.product_table').DataTable().ajax.reload();
        // $('.table-purchase-order-history').DataTable().ajax.reload();
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

  $(document).on('submit','.upload-excel-form',function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('upload-bulk-product-in-group-detail-job') }}",
      method: 'post',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').removeClass('d-none');
          console.log("Calling Function from first part");
          checkStatusForProductsImport();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          checkStatusForProductsImport();
          //checkStatusForProducts();
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  function checkStatusForProductsImport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-importing-receiving-bulk-products')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForProductsImport();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success-bulk').removeClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
          $('.upload-excel-form')[0].reset();
          $('.product_table').DataTable().ajax.reload();


          if(data.exception != null && data.exception != '' && data.exception != '<ol>')
          {
            // alert(data.exception);
            $('#msgs_alerts').html(data.exception);
            $('.errormsgDiv').removeClass('d-none');
            $('.export-alert-success-bulk').removeClass('d-none');
            $('.export-alert-bulk').addClass('d-none');
             // $('.upload-excel-form')[0].reset();
            // $('.product_table').DataTable().ajax.reload();

          }
        }
        else if(data.status==2)
        {
          $('.export-alert-success-bulk').addClass('d-none');
          $('.export-alert-bulk').addClass('d-none');
          $('.export-alert-another-user-bulk').addClass('d-none');
             $('.upload-excel-form')[0].reset();

          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
           $('.upload-excel-form')[0].reset();
          $('.product_table').DataTable().ajax.reload();
          console.log(data.exception);
        }
      }
    });
  }

  // export pdf2 code
  $(document).on('click', '.export-pdf2', function(e){
      {{-- var po_group_id = $('#po_group_id').val();
      var product_id = $('#supplier_group_id_for_pdf').val($('.product_id').val());
      var supplier_id = $('#product_group_id_for_pdf').val($('.supplier_id').val()); --}}
      var check_detail = '';
      check_detail = "{{$po_group->po_group_detail->count()}}";
      if(check_detail == 0)
      {
        toastr.warning('Sorry!', 'Group have no PO(s).',{"positionClass": "toast-bottom-right"});
        return false;
      }

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
      $.ajax({
        method:"post",
        data:$('.export-group-form2').serialize(),
        url: "{{url('warehouse/export-group-to-pdf2')}}",
        beforeSend:function(){
          $('.export-pdf2').attr('title','Please Wait...');
          $('.export-pdf2').prop('disabled',true);
          // $('.download-btn').addClass('d-none');

        },
        success: function(response){
            if(response.success === true){
            $('.export-alert-success-print').addClass('d-none');
            $('.export-alert-print').removeClass('d-none');
            $('.export-pdf2').attr('title','Please Wait...');
            $('.export-pdf2').prop('disabled',true);
            getPdfStatus();
          }
        },
        error:function()
        {
            $('.export-alert-print').addClass('d-none');
            $('.export-pdf2').attr('title','Print');
            $('.export-pdf2').prop('disabled',false);
            toastr.error('Error!', 'Something went wrong.',{"positionClass": "toast-bottom-right"});

        }
      });
   });

  function getPdfStatus()
  {
    var po_group_id=$('#po_group_id').val();
    console.log(1+' '+po_group_id);
    $.ajax({
      method:"get",
      url:"{{route('get-pdf-status')}}",
      data:{group_id:po_group_id},
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              getPdfStatus();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-print').addClass('d-none');
          $('.export-pdf2').attr('title','Print');
          $('.export-pdf2').prop('disabled',false);
          $('.export-alert-success-print').removeClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-print').addClass('d-none');
          $('.export-pdf2').attr('title','Print');
          $('.export-pdf2').prop('disabled',false);
          $('.export-alert-success-print').removeClass('d-none');
          toastr.error('Error!', 'Something went wrong.',{"positionClass": "toast-bottom-right"});
        }
      }
    });
  }

  $(document).on("dblclick",".inputDoubleClickGroupInfo",function(){
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

  // double click select
  $(document).on("dblclick",".selectDoubleClick",function(){
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

  $(document).on("change",".target_receive_date",function(e) {
    // var old_value = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var thisPointer = $(this);
        thisPointer.addClass('d-none');
        // thisPointer.val(fieldvalue);
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        $(".target_receive_date").datepicker('hide');
    }
    if($(this).val().length < 1)
    {
      return false;
    }
    else
    {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();
      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');

        thisPointer.prev().removeClass('d-none');
        $(this).prev().html(fieldvalue);
      }
      else
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');

        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
        saveGroupInfoData(thisPointer,thisPointer.attr('name'), thisPointer.val());
      }
    }
  });

  $(document).on("change",".selectFocus",function() {

    if($(this).attr('name') == 'courier')
    {
      var new_value = $("option:selected", this).html();
      var thisPointer=$(this);
      thisPointer.addClass('d-none');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
      saveGroupInfoData(thisPointer,thisPointer.attr('name'), thisPointer.val());
    }
  });

  $(document).on(' keypress keyup focusout ','.fieldFocusGroupInfo', function(e){

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

      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
      }
      else
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }
        saveGroupInfoData(thisPointer,thisPointer.attr('name'), thisPointer.val());
      }

    }
  });

  function saveGroupInfoData(thisPointer,field_name,field_value){

    var po_group_id = $('#po_group_id').val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ route('save-po-group-info') }}",
      dataType: 'json',
      data: 'po_group_id='+po_group_id+'&'+field_name+'='+encodeURIComponent(field_value),
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
          toastr.success('Success!', 'Information updated successfully. Product marked as completed.',{"positionClass": "toast-bottom-right"});
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }

  $(document).on('keyup', function(e) {

    if (e.keyCode === 27)
    {
      $(".target_receive_date").datepicker('hide');

      if($('.selectDoubleClick').hasClass('d-none'))
      {
        $('.selectDoubleClick').removeClass('d-none');
        $('.selectDoubleClick').next().addClass('d-none');
      }

      if($('.inputDoubleClickGroupInfo').hasClass('d-none'))
      {
        $('.inputDoubleClickGroupInfo').removeClass('d-none');
        $('.inputDoubleClickGroupInfo').next().addClass('d-none');
      }
    }
  });

  var btn_click = false;
  $(document).on('click',function(e){
    if ($(e.target).closest(".dt-button-collection").length === 1) {
        btn_click = true;
    }

    if(btn_click)
    {
      if ($(e.target).closest(".dt-button-collection").length === 0) {
        btn_click = false;
        $('.product_table').DataTable().ajax.reload();
        // alert('clicked outside');
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
        id = "{{$po_group->id}}";
        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });

        $.ajax({
          method:"post",
          data:{id:id,title:title,action:action},
          url: "{{ route('clear-group-values') }}",
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
              $('.product_table').DataTable().ajax.reload();

              toastr.success('Success!', response.msg,{"positionClass": "toast-bottom-right"});

              $('.table-po-group-order-history').DataTable().ajax.reload();

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

});
</script>
<script>
  function backFunctionality(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    if(history.length > 1)
    {
      return history.go(-1);
    }
    else
    {
      var url = "{{ url('importing/importing-receiving-queue') }}";
      document.location.href = url;
    }
   }
</script>
@stop

