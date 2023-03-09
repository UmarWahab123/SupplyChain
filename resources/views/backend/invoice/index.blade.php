@extends('sales.layouts.layout')

@section('title','Invoice Products | Sales')

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

</style>

{{-- Content Start from here --}}
<form method="post" class="mb-2 draft_quotation_save_form" enctype='multipart/form-data'>

<div class="row mb-3 headings-color">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold mb-0">Draft Quotations # <span class="c-ref-id">{{$id }}</span></h3>
  </div>
  <div class="col-md-4 title-col">
    <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</h5>
  </div>

  <!-- New Design Starts Here  -->
<div class="col-lg-12">
<div class="row">

<div class="col-lg-8">
<!-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo"> -->
<div class="d-flex align-items-center mb-0">
      <div>
        @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . @$company_info->logo))
        <img src="{{asset('public/uploads/logo/'.@$company_info->logo)}}" class="img-fluid" style="width: 80px;height: 80px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 80px;height: 80px;" align="big-qummy">
        @endif
        <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p></div>
    </div>
  <p class="mb-1">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
  <p class="mb-1"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}  <em class="fa fa-envelope"></em>  {{@$company_info->billing_email}}</p>
  <br>
</div>
<div class="col-lg-4">
  @if($order->customer_id != '')
  <div>
    <span><i class="fa fa-edit edit_customer"></i></span>

  <div class="customer-addresses">
    <div class="header">
    <div class="d-flex align-items-center mb-0">
    @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
        <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" class="img-fluid" style="width: 40px;height: 40px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 80px;height: 80px;" align="big-qummy">
    @endif
    <div class="pl-2 comp-name" id="unique" data-customer-id="{{$order->customer->id}}"><p>{{$order->customer->company}}</p></div>
    </div>
    </div>
    <div class="body">
     <p class="edit-functionality"><input type="hidden" value="{{$order->billing_address->id}}">
@if(@$order->customer->getbilling->count() > 1)
      <i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i>
      @endif
     @if($order->billing_address->billing_address !== null) {{$order->billing_address->billing_address}} @endif
       @if($order->billing_address->getcountry->name !== null) {{ $order->billing_address->getcountry->name }}, @endif @if(@$order->billing_address->getstate->name !== null) {{ @$order->billing_address->getstate->name }}, @endif @if($order->billing_address->city !== null) {{ $order->billing_address->billing_city }}, @endif @if($order->billing_address->billing_zip !== null) {{ $order->billing_address->billing_zip }} @endif</p>
    <ul class="d-flex list-unstyled">
      <li><i class="fa fa-phone pr-2"></i>{{$order->billing_address->billing_phone}}</li>
      <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{$order->billing_address->billing_email}}</li>
    </ul>

    <ul class="d-flex list-unstyled">
      <li><b>Tax ID:</b> @if($order->billing_address->tax_id !== null) {{ $order->billing_address->tax_id }} @endif</li>
    </ul>
    </div>
    <div class="selected_address"></div>
  </div>
  <div class="d-none update_customer">
     @if(Auth::user()->role_id == 3)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($customers->count() > 0)
       @foreach($customers as $customer)
       @if($order->billing_address->customer_id == $customer->id)
        <option value="{{ $customer->id }}" selected="true"> @if($customer->company != null) {{$customer->company}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
       @else
       <option value="{{ $customer->id }}"> @if($customer->company != null) {{$customer->company}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endif
      @endforeach
      @endif
      <!-- <option value="new">Add New</option>  -->
    </select>
    @endif
  </div>
    </div>
  @endif
  @if($order->customer_id == '')
  @if(Auth::user()->role_id == 3)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($customers->count() > 0)
       @foreach($customers as $customer)
       <option value="{{ $customer->id }}"> @if($customer->company != null) {{$customer->company}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endforeach
      @endif
      <!-- <option value="new">Add New</option>  -->
    </select>
    @else
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($sales_coordinator_customers->count() > 0)
       @foreach($sales_coordinator_customers as $customer)
       <option value="{{ $customer->id }}"> @if($customer->company != null) {{$customer->company}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endforeach
      @endif
      <!-- <option value="new">Add New</option>  -->
    </select>
    @endif
    <div>
    <span><i class="fa fa-edit edit_customer d-none first_edit"></i></span>
    <div class="customer-addresses">
      <div class="header"></div>
      <div class="body"></div>
      <div class="selected_address"></div>
    </div>
     <div class="d-none update_customer">
     @if(Auth::user()->role_id == 3)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer">
     <option value="">Choose Customer</option>
      @if($customers->count() > 0)
       @foreach($customers as $customer)
       <option value="{{ $customer->id }}"> @if($customer->company != null) {{$customer->company}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>
      @endforeach
      @endif
      <!-- <option value="new">Add New</option>  -->
    </select>
    @endif
  </div>
  </div>

  @endif

  <ul class="d-flex list-unstyled">
    <li class="pt-2 fontbold">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif:</li>
    <span class="pl-4 pt-2 inputDoubleClick">
      @if($order->target_ship_date != null)
      {{Carbon::parse($order->target_ship_date)->format('d/m/Y')}}
      @else
      <p>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif Here</p>
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none target_ship_date fieldFocus" name="target_ship_date" id="target_ship_date" value="{{@$order->target_ship_date}}">
  </ul>

</div>

<div class="col-lg-12 text-uppercase fontbold">
  <a onclick="history.go(-1)"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color">back</button></a>
  <a href="#"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button></a>
  <a href="#"><button type="button" class="btn text-uppercase purch-btn headings-color btn-color d-none">print</button></a>
  <div class="pull-right">
    <button type="button" class="btn text-uppercase purch-btn headings-color btn-color d-none" data-toggle="modal" data-target="#customerAttachments">
      upload document<i class="pl-1 fa fa-arrow-up"></i></button>

<a href="javascript:void(0)" data-id="{{$order->id}}" class="download-documents">
            <button type="button" class="btn text-uppercase purch-btn headings-color btn-color" data-toggle="modal" data-target="#uploadDocument">
              upload document<i class="pl-1 fa fa-arrow-up"></i>
            </button>
          </a>

    </div>
</div>
</div>
</div>
  <!-- new design ends here -->
</div>

<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-ordered-products text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>{{$global_terminologies['our_reference_number']}}</th>
            <th>{{$global_terminologies['product_description']}}</th>
            <th>{{$global_terminologies['brand']}}</th>
            <th>Sale Unit</th>
            <th>#{{$global_terminologies['qty']}}</th>
            <th>#{{$global_terminologies['pieces']}}</th>
            <th>*Sugguested<br> Price</th>
            <th>*{{$global_terminologies['default_price_type']}}</th>
            <th>Default Price</th>
            <th>Total Price</th>
            <th>VAT</th>
            <th> {{$global_terminologies['supply_from']}}</th>
            <th>Notes</th>
          </tr>
        </thead>
      </table>
      <!-- New Design Starts Here  -->
      <div class="row ml-0 mb-4">
        <div class="col-9 pad">
          <div class="col-6 pad">
            <div class="purch-border input-group custom-input-group">
              <input type="text" name="refrence_code" placeholder="Type Reference number..." data-id = "{{$id}}" class="form-control refrence_number">
            </div>
          </div>
          <!-- buttons -->
          <div class="row">
          <div class="col-lg-6 col-md-6 pad pl-2 mt-4 mb-4">
            <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="addProduct">Add Product</a>
            <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="uploadExcelbtn" type="submit">Upload Excel</a>
            <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="addInquiryProductbtn" type="submit">Add New Item</a>
          </div>
          <!-- buttons -->
            <!-- Notes section starts -->
            <div class="col-lg-6 col-md-6">
                  <p class="mt-3"><strong>Comment: </strong><span class="inv-note inputDoubleClick">@if($inv_note != null) {!! $inv_note->note !!} @else {{ 'Add a Comment' }} @endif</span>
                  <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                  </p>
            </div>
            <!-- Notes section ends -->
            </div>
        </div>

        <div class="col-lg-3 pt-4 mt-4">
        <div class="side-table">
          <table class="headings-color purch-last-left-table side-table" style="width: 100%;">
            <tbody>
              <tr>
                <td class="fontbold" width="50%">Sub Total:</td>
                <td class="text-start sub-total fontbold">{{ number_format($sub_total, 2, '.', ',') }}</td>
              </tr>
              <tr>
                <td class="text-nowrap fontbold">Discount:</td>
                <td class="fontbold text-start">
                  <span class="inv-discount mr-2 inputDoubleClick">{{ $order->discount == '' ? 0 : number_format($order->discount, 2, '.', ',') }}</span>
                  <input type="number" data-id="{{ $id }}" min="0" class="form-control mr-2 p-0 d-none fieldFocus input-sm" name="discount" value="{{$order->discount}}" style="width: 90%;min-height: 0;"></td>
              </tr>
              <tr>
                <td class="text-nowrap fontbold">Shipping:</td>
                <td class="fontbold text-start">
                  <span class="inv-shipping mr-2 inputDoubleClick">{{ $order->shipping == '' ? 0 :number_format($order->shipping, 2, '.', ',') }}</span>
                  <input type="number" data-id="{{ $id }}" min="0" max="4000" class="form-control mr-2 p-0 d-none fieldFocus input-sm" name="shipping" value="{{$order->shipping}}" style="width: 90%;min-height: 0;"></td>
              </tr>
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

    <div class="row justify-content-end d-flex">
    <div class="col-lg-7">
    <p class="mb-1 font-weight-bold">Ship To</p>
    @if($order->customer_id != '')
    <div class="customer-addresses-ship">

    <div class="ship-body">
     <p class="edit-functionality-ship">
      @if(@$order->customer->getbilling->count() > 1)
      <i class="fa fa-edit edit-address-ship" data-id="{{$order->customer_id}}"></i>
      @endif
       <!-- <a href="#" data-toggle="modal" data-target="#add_billing_detail_modal-ship">
          <i class="fa fa-plus add-address-ship" title="Add New Address" data-id="{{$order->customer_id}}"></i></a>  -->
    @if($order->shipping_address->billing_address !== null) {{$order->shipping_address->billing_address}} @endif
    <br>  @if($order->shipping_address->getcountry->name !== null) {{ $order->shipping_address->getcountry->name }}, @endif @if(@$order->shipping_address->getstate->name !== null) {{ @$order->shipping_address->getstate->name }}, @endif @if($order->shipping_address->city !== null) {{ $order->shipping_address->billing_city }}, @endif @if($order->shipping_address->billing_zip !== null) {{ $order->shipping_address->billing_zip }} @endif</p>
    <ul class="d-flex list-unstyled">
      <li><i class="fa fa-phone pr-2"></i>{{$order->shipping_address->billing_phone != null ? $order->shipping_address->billing_phone : '--'}}</li>
      <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{$order->shipping_address->billing_email != null ? $order->shipping_address->billing_email : '--'}}</li>
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
          <div class="col-lg-5 col-md-5 pl-3 pt-md-3">
          <div class="text-right">
            @csrf
            <input type="hidden" name="inv_id" value="{{ $order->id }}">
            <input type="hidden" name="action" value="save">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-success draft_quotation_save_btn">Save and Close</button>
          </form>
          <form method="post" class="d-inline-block mb-2 draft_quotation_discard_form" >
            @csrf
            <input type="hidden" name="inv_id" value="{{ $order->id }}">
            <input type="hidden" name="action" value="discard">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-danger">Discard and Close</button>&nbsp;
          </form>
          </div>
          </div>
        </div>

      </div>
      <!-- New Design Ends Here  -->
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
                                <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
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

<!--  Put Add Customer Modal Here -->
<div class="modal" id="addCustomerModal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Add Customer</h3>
        <div class="mt-3">
        {!! Form::open(['method' => 'POST', 'class' => 'add-customer-form', 'enctype' => 'multipart/form-data']) !!}
          <div class="form-row">
            <div class="form-group col-12">
              {!! Form::text('reference_number', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Reference Number | Leave Blank For System Generated']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::text('company', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Billing Name (Required)']) !!}
            </div>
            <div class="form-group col-6">
              {!! Form::text('reference_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Reference Name']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::text('first_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'First Name']) !!}
            </div>
            <div class="form-group col-6">
              {!! Form::text('last_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Last Name']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::email('email', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Email']) !!}
            </div>
            <div class="form-group col-6">
              {!! Form::text('phone', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Phone']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::text('address_line_1', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Address Line 1', 'maxlength' => 100]) !!}
            </div>
            <div class="form-group col-6">
              {!! Form::text('address_line_2', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Address Line 2 (optional)', 'maxlength' => 100]) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::select('country', $countries, null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Choose Country', 'id' => 'country']) !!}
            </div>
            <div class="form-group col-6 ">
              {!! Form::select('state', ['' => 'Choose State'], null, ['class' => 'font-weight-bold form-control-lg form-control fill_states_div', 'id' => 'state']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::text('city', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'City']) !!}
            </div>
            <div class="form-group col-6">
              {!! Form::text('postalcode', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Postal Code']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              {!! Form::select('category_id', $category, null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Choose Category', 'id' => 'category']) !!}
            </div>
            <div class="form-group col-6">
              {!! Form::select('credit_term', ['' => 'Choose a Credit Term','Net 30'=>'Net 30','Net 60'=>'Net 60','Net 90'=>'Net 90'], null, ['class' => 'font-weight-bold form-control-lg form-control', 'id' => 'credit_term']) !!}
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-6">
              <div class="custom-file mb-5">
                <label class="d-block text-left">Logo (<span style="color:blue;">1MB is maximum file size </span>)</label>
                <input type="file" class="form-control form-control-lg" name="logo">
              </div>
            </div>
            <div class="form-group col-6">
              {!! Form::label('payment_types','Payment Types') !!}
              <br>
              @foreach($payment_types as $type)
              {!! Form::label('payment_types', $type->title) !!}
              {!! Form::checkbox('payment_types[]', $type->id) !!}
              @endforeach
            </div>
          </div>

          <div class="form-submit">
            <input type="submit" value="add" class="btn btn-bg save-btn" id="save_cus_btn">
            <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
          </div>
        {!! Form::close() !!}
       </div>
      </div>
    </div>
  </div>
</div>
<!-- Customer Modal End Here -->

<!--  Add Product Modal Start Here -->
<div class="modal addProductModal" id="addProductModal" style="margin-top: 150px;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Search Product</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="form-group" style="margin-top: 10px; margin-bottom: 50px; position:relative;">
        <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
          <input type="text" name="prod_name" id="prod_name" value="" class="form-control form-group mb-0" placeholder="Search by Product Name" style="padding-left:30px;">
          <div id="product_name_div_new"></div>
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

  <!-- Customer Attachments Modal ends here  -->
 {{--Edit Billing Info--}}
<!-- Add Billing detail Modal Started -->
 <!--  <div class="modal fade" id="edit_billing_detail_modal" role="dialog">
    <div class="modal-dialog">


      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add New Addressss</h4>
        </div>
        <div class="modal-body">

          <form id="add-address-form" method="POST">

          {{csrf_field()}}

          <input type="hidden" name="customer_id" id="billing_customer_id" value="{{$order->customer_id}}">
          <input type="hidden" name="quotation_id" id="quotation_id" value="{{$id}}">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="title"> Title:</label>
              <input required="true" type="text" class="form-control" id="billing_title" name="billing_title">
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Billing Contact Name :</label>
              <input required="true" type="text" class="form-control" id="billing_contact_name" name="billing_contact_name1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:</label>
              <input required="true" type="email" class="form-control" id="billing_email" name="billing_email1">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Billing Name :</label>
              <input required="true" type="text" class="form-control" id="company_name" name="company_name1">
            </div>

            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:</label>
              <input required="true" type="text" class="form-control" id="billing_phone" name="billing_phone">
            </div>
            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control" id="billing_fax" name="billing_fax1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:</label>
              <input required="true" type="text" class="form-control" id="billing_address" name="billing_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:</label>
              <input required="true" type="text" class="form-control" id="billing_zip" name="billing_zip">
            </div>


            </div>

            <div class="row">
            <div class="form-group col-md-6 d-none">
              <label for="business_country">Country:</label>
              <select required="true" id="billing_country" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select Country" name="billing_country">
                <option value="217" selected disabled="true">Thailand</option>
              </select>
            </div>


            <div class="form-group col-md-6">
              <label for="business_state">State:</label>

               <select class="form-control state-tags" name="state">
                <option selected="selected">Select State</option>
                @foreach($states as $state)
                <option value="{{$state->id}}">{{$state->name}}</option>
                @endforeach
              </select>
            </div>


            <div class="form-group col-md-6">
              <label for="contact_city">City:</label>
              <input type="text" required="true" id="billing_city" class="form-control " name="billing_city">

            </div>

            <div class="form-group col-md-6 mt-4">
            <div class="row">
            <div class="col-lg-7 mt-3">
            <label for="contact_city">Is Default&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" id="is_default" class=" is_default " name="is_default">
              <input type="hidden" id="is_default_value" class="form-control" name="is_default_value" value='0'>
            </div>
            <div class="col-lg-1 offsets-3">

            </div>
            </div>


            </div>
            </div>


          <div class="form-group col-md-12">
           <button type="button" class="btn btn-primary btn-success" id="add-address-form-btn">Submit</button>
          </div>
          </form>

        </div>

      </div>

    </div>
  </div> -->

<!-- Add Billing Details Modal Ended -->


@endsection

@section('javascript')
<script type="text/javascript">
    $(".state-tags").select2();
$(function(e){

  var table = $('.table-ordered-products').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        searching: false,
        ordering: false,
        serverSide: true,
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        paging: false,
        bInfo : false,
        "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,7,10,11] },
    { className: "dt-body-right", "targets": [6,8,9] }
  ],
        fixedHeader: true,
         colReorder: {
          realtime: false,
        },
        dom: 'lrtip',

        ajax: "{{ url('sales/get-invoice-to-list') }}"+"/"+{{ $id }},
        columns: [
            { data: 'action', name: 'action' },
            { data: 'refrence_code', name: 'refrence_code' },
            { data: 'description', name: 'description' },
            { data: 'brand', name: 'brand' },
            { data: 'sell_unit', name: 'sell_unit' },
            { data: 'quantity', name: 'quantity' },
            { data: 'number_of_pieces', name: 'number_of_pieces' },
            { data: 'exp_unit_cost', name: 'exp_unit_cost' },
            { data: 'margin', name: 'margin' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'total_price', name: 'total_price' },
            { data: 'vat', name: 'vat' },
            { data: 'supply_from', name: 'supply_from' },
            { data: 'notes', name: 'notes' },
        ]
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

        }
    });
});

  $(document).on("dblclick",".inputDoubleClick",function(){
        $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
    });

  $(document).on("focusout",".fieldFocus",function() {
    // alert('hi')
      var quotation_id = "{{ $id }}";
      var attr_name = $(this).attr('name');

      if(attr_name == 'target_ship_date')
      {
        $(this).prev().html($(this).val());
        if($(this).val() == '')
        {
          return false;
        }
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
      }
      if(attr_name == 'comment')
      {
        $(this).prev().html($(this).val());
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
        if(attr_name == 'discount'){
          $('.inv-discount').html(data.discount);
          $('.grand-total').html(data.total);
        }
        else if(attr_name == 'shipping'){
          $('.inv-shipping').html(data.shipping);
          $('.grand-total').html(data.total);
        }
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
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('#loader_modal').modal('hide');
            toastr.success('Success!', 'Inquiry Product added successfully',{"positionClass": "toast-bottom-right"});
            $('.table-ordered-products').DataTable().ajax.reload();
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
        success:function(data){
          var html_string='<div id="state_div">   <label>@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</label>';
          html_string+='  <select id="state" name="state" class="form-control selectpicker" title="Choose State" data-live-search="true" data-select_type="state"><option>Select a State</option>';
          for(var i=0;i<data.length;i++){
            html_string+="<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>";
          }
          html_string+=" </select></div>";

          $("#state").html(html_string);
          $('.selectpicker').selectpicker('refresh');
        },
        error:function(){
          alert('Error');
        }

    });
  });

  $(document).on('click', '#save_cus_btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
       $.ajax({
          url: "{{ route('add-customer') }}",
          method: 'post',
          data: $('.add-customer-form').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              var customer_id = result.customer.id;
              var quotation_id = '{{$id}}';
              var _token = $('input[name="_token"]').val();
              $.ajax({
                  url:"{{ route('add-customer-to-quotation') }}",
                  method:"POST",
                  data:{_token:_token,customer_id:customer_id,quotation_id:quotation_id},
                  success:function(data){
                    $('.add-cust').addClass('d-none');
                    $('.customer-addresses').html(data.html);
                  }
                });
              toastr.success('Success!', 'Customer added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-customer-form')[0].reset();
            }
          },
          error: function (request, status, error) {
            $('.save-btn').val('add');
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
                     $('.sub-total').html(data.sub_total);
                     $('.total-vat').html(data.total_vat);
                     $('.grand-total').html(data.grand_total);
                     $('#total').val(data.grand_total);
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
      }
    });
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
            toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
              window.location.href = "{{ url('/sales')}}";
            }, 500);
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

  });

  $(document).on('submit','.draft_quotation_save_form',function (e) {
      var inverror = false;
      var customer = $(this).find('select[name=customer]');
      var ship_date = $(this).find('input[name=target_ship_date]');
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
              toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
              setTimeout(function(){
                window.location.href = "{{ url('/sales')}}";
              }, 500);
            }
            else if(result.success == false)
            {
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

  $(document).on('click', 'input[type=number]', function(e){
    $(this).removeClass('disabled');
    $(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on('keyup focusout', 'input[type=number], input[type=text]', function(e){
    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();


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
        $(this).addClass('disabled');
        $(this).removeClass('active');
        $(this).attr('readonly','true');
        if($(this).val() !== null)
        {
          var product_row = $(this).parent().parent().attr('id');
          var attr_name = $(this).attr('name');
          UpdateQuotationData(product_row, attr_name, new_value);
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

  $(document).on('change','.warehouse_id',function(e) {

      var product_row = $(this).parent().parent().attr('id');
      var attr_name = $(this).attr('name');
      var new_value = $("option:selected",this).val();
      UpdateQuotationData(product_row, attr_name, new_value);
  });

  function UpdateQuotationData(draft_quotation_id,field_name,field_value){
    // console.log(field_name+' '+field_value);
    if(field_name != 'unit_price'){
      if(field_value<0){
        return false;
      }
    }

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('sales/update-quotation-data') }}",
        dataType: 'json',
        data: 'draft_quotation_id='+draft_quotation_id+'&'+field_name+'='+field_value,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-ordered-products').DataTable().ajax.reload();
            $('.sub-total').html(data.sub_total);
            $('.total-vat').html(data.vat);
            $('.grand-total').html(data.grand_total);
            $('#total').val(data.grand_total);
          }
        },

      });
    }

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
            $("#loader_modal").modal('show');
          },
          success: function(result){
          $("#loader_modal").modal('hide');
          if(result.success == true)
          {
            toastr.success('Success!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.table-ordered-products').DataTable().ajax.reload();
            $('.sub-total').html(result.sub_total);
            $('.total-vat').html(result.total_vat);
            $('.grand-total').html(result.grand_total);
            $('#total').val(result.grand_total);
            $('.total_products').html(result.total_products);
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
          $('.table-ordered-products').DataTable().ajax.reload();
          $('.sub-total').html(data.sub_total);
          $('#total').val(data.grand_total);
          $('.total_products').html(data.total_products);
        }
    });
  });

  @if(Session::has('errormsg'))
    toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
  @endif

  });

</script>
<script>
$(function(e){

$(document).ready(function(){

 $('#prod_name').keyup(function(){
        var query = $(this).val();
        var inv_id = $("#add_product_to_invoice").val();
        if(query != '')
        {
         var _token = $('input[name="_token"]').val();
         $.ajax({
          url:"{{ route('autocomplete-fetch-product') }}",
          method:"POST",
          data:{query:query, _token:_token, inv_id:inv_id},
          success:function(data){
            $('#product_name_div_new').fadeIn();
            $('#product_name_div_new').html(data);
          }
         });
        }
    });
});

$(document).on('click', '.add_product_to', function(e){
    var customer_id = $('#unique').data('customer-id');

    var inv_id = $(this).data('inv_id');
    var prod_id = $(this).data('prod_id');
      $('#prod_name').val($(this).text());

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      method:"post",
      data:'selected_products='+prod_id+'&quotation_id='+inv_id+'&customer_id='+customer_id,
      url:"{{ route('add-prod-to-quotation') }}",
      success:function(data){
        $('#addProductModal').modal('hide');
        $('.table-ordered-products').DataTable().ajax.reload();
        $('.sub-total').html(data.sub_total);
        $('.total-vat').html(data.total_vat);
        $('.grand-total').html(data.grand_total);
        $('#total').val(data.grand_total);
        $('.total_products').html(data.total_products);
        $('#prod_name').val('');
        $('#product_name_div_new').empty();
      }
  });
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
      $.ajax({
        url: "{{ route('add-draft-quotation-note') }}",
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
            data:'id='+id,
            url:"{{ route('enquiry-item-as-new-product') }}",
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
                  toastr.success('Success!', 'Item added as a Inquiry Product to System successfully.' ,{"positionClass": "toast-bottom-right"});
                  $('.table-ordered-products').DataTable().ajax.reload();
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
      }
    }
  });

  });

$('.edit_customer').on("click",function(){
  $('.update_customer').removeClass('d-none');
  $('.customer-addresses').addClass('d-none');
});

</script>
@stop

