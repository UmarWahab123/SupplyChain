@extends('sales.layouts.layout')

@section('title','Quotations Products | Sales')
<?php
use Carbon\Carbon;
?>

@section('content')
<style type="text/css">
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
.selectDoubleClick, .inputDoubleClick{
  font-style: italic;
  font-weight: bold;
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
          <li class="breadcrumb-item"><a href="{{route('accounting-dashboard')}}">Accounting Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{route('debit-notes-dashboard')}}">Debit Note Dashboard</a></li>
          <li class="breadcrumb-item active">Debit Note</li>
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
<form method="" class="mb-2 invoice-submit-form" enctype='multipart/form-data'>
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h5 class="maintitle text-uppercase fontbold mb-0">Debit Note # <span class="c-ref-id">{{$order->status_prefix}}{{$order->ref_prefix}}{{$order->ref_id }}</span></h5>
  </div>
  <div class="col-md-4 title-col">
    <!-- <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</span></h5> -->
    <div class="row">
      <div class="col-6">
        <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</h5>
      </div>
      <div class="col-6">
        <a onclick="backFunctionality()" class="pull-right d-none">
          <!-- <button type="button" class="btn text-uppercase purch-btn mr-2 headings-color btn-color">back</button> -->
          <span class="vertical-icons export_btn" title="Back">
            <img src="{{asset('public/icons/back.png')}}" width="27px">
          </span>
        </a>
      </div>
    </div>
  </div>
  <!-- New Design Starts  -->
  <div class="col-lg-12 col-md-12">
    <div class="row">
      <div class="col-lg-8 col-md-6">

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
        <p class="mb-2">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
        <ul class="list-inline list-unstyled pl-0">
          <li class="list-inline-item"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}</li>
          <li class="list-inline-item"><em class="fa fa-envelope"></em> {{@$order->user->user_name}} </li>
        </ul>
        <br>
       <!--  <div class="form-group">
          <label class="mb-1 font-weight-bold">Quotation # </label> <span class="dblclk-edit" data-type="ref_id">{{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{ $order->ref_id }}</span>
        </div> -->
        <div class="form-group">
          <label class="mb-1 font-weight-bold">Debit Note Date:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
        </div>

        <div class="form-group">
          <label class="mb-1 font-weight-bold">Status:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{ $order->statuses->title }}</span>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit_customer mr-3" data-id=""></i></p>
        @if(@$order->customer !== null)
        <div class="update_customer d-none">
        @else
        <div class="update_customer">
        @endif
       @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 4)
    <select class="form-control js-states state-tags mb-2 add-cust" name="customer" data-id="{{@$order->id}}">
     <option value="">Choose Customer</option>
      @if($admin_customers->count() > 0)
       @foreach($admin_customers as $customer)


       <option value="{{ $customer->id }}" {{ ($order->customer_id == $customer->id) ? "selected='true'":" " }}> @if($customer->reference_name != null) {{$customer->reference_name}} @else {{ $customer->first_name.' '.$customer->last_name }} @endif</option>


      @endforeach
      @endif
    </select>
    @endif
  </div>
  @if(@$order->customer !== null)
  <div class="customer-addresses">
        <div class="d-flex align-items-center mb-1">
            @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
            <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">
            @else
            <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">
            @endif

          <div class="pl-2 comp-name" data-customer-id="{{@$order->customer->id}}"><p>{{@$order->customer->reference_name}}</p></div>
        </div>
        </div>
      @endif
        <div class="bill_body">
          @if($billing_address != null)
           <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address mr-3" data-id="{{$order->customer_id}}"></i><span>{{@$billing_address->title}}</span><br>{{@$billing_address->billing_address}},{{@$order->customer->language ==  "en" ? $billing_address->getcountry->name : (@$billing_address->getcountry->thai_name != null ? @$billing_address->getcountry->thai_name : @$billing_address->getcountry->name )}},
            @if(@$order->customer->language == 'en')
            {{@$billing_address->getstate->name}},
            @else
            {{@$billing_address->getstate->thai_name != null ? @$billing_address->getstate->thai_name : @$billing_address->getstate->name}},
            @endif

            {{@$billing_address->billing_city.', '.@$billing_address->billing_zip }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{@$billing_address->billing_phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{@$billing_address->billing_email}}</a></li>
        </ul>
        <ul class="d-flex list-unstyled">
      <li><b>Tax ID:</b> @if($billing_address->tax_id !== null) {{ $billing_address->tax_id }} @endif</li>
    </ul>
          @elseif(@$order->customer !== null)
        <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ $order->customer->address_line_1.' '.$order->customer->address_line_2.', '.$order->customer->getcountry->name .', '.$order->customer->getstate->name.', '.$order->customer->city.', '.$order->customer->postalcode }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
        </ul>
        @endif
        </div>

        <ul class="d-flex list-unstyled">
          <li class="pt-2 fontbold" style="width: 35px;">Memo:</li>
          <span class="pl-4 pt-2 inputDoubleClick" data-fieldvalue="{{$order->memo}}">
            @if($order->memo != null)
            {{$order->memo}}
            @else
            <p>Memo Here</p>
            @endif
          </span>
          <!-- <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$order->memo}}"> -->
          <textarea class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" rows="4" style="resize: none;width: 100%">{{@$order->memo}}</textarea>
       </ul>
      </div>
    <!-- export pdf form starts -->

    <!-- export pdf form ends -->
      <div class="col-lg-12 col-md-12 text-uppercase fontbold">
        <a onclick="history.go(-1)"><button type="button" class="btn text-uppercase purch-btn mr-2 headings-color btn-color d-none">back</button></a>
        <a href="#"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button></a>

        <a href="javascript:void(0);">
          <!-- <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf-proforma-inc-vat">Print</button> -->

          <span class="vertical-icons export-pdf-proforma-inc-vat" title="Print">
            <img src="{{asset('public/icons/print.png')}}" width="27px">
          </span>
        </a>

        <div class="pull-right">
          @if($checkDocs > 0)
            @php $show = ""; @endphp
          @else
            @php $show = "d-none"; @endphp
          @endif

          <a href="javascript:void(0)" data-id="{{$order->id}}" data-toggle="modal" data-target="#file-modal" class="download-documents d-none"><button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">View documents<i class="pl-1 fa fa-download"></i></button></a>

          <a href="{{ route('order-docs-download',$order->id) }}" class="d-none">
            <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
          </a>
          <a href="javascript:void(0)" data-id="{{$order->id}}" class="download-documents">
           <!--  <button type="button" class="btn text-uppercase purch-btn headings-color btn-color" data-toggle="modal" data-target="#uploadDocument">
              upload document<i class="pl-1 fa fa-arrow-up"></i>
            </button> -->

            <span class="vertical-icons" title="Upload Document" data-toggle="modal" data-target="#uploadDocument">
            <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
          </span>
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- New Design Ends -->
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-quotation-product text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th class="inv-head">{{$global_terminologies['our_reference_number']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reference_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reference_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">{{$global_terminologies['product_description']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="description">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="description">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">{{$global_terminologies['brand']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="brand">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="brand">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <!-- <th class="inv-head">Sales <br> Unit </th> -->
            <th class="inv-head">#{{$global_terminologies['qty']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">#{{$global_terminologies['pieces']}} </th>
            <th class="inv-head">*Sugguested<br> Price
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reference_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reference_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
            <th class="inv-head">*{{$global_terminologies['default_price_type']}}</th>
            <th class="inv-head">{{$global_terminologies['default_price_type_wo_vat']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="default_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="default_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">Discount
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="discount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="discount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">VAT
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">{{$global_terminologies['unit_price_vat']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head"> {{$global_terminologies['total_amount_inc_vat']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_amount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_amount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <!-- <th class="inv-head">Total Amount </th> -->
            <th class="inv-head">{{$global_terminologies['supply_from']}}
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supply_from">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supply_from">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th class="inv-head">Notes </th>

          </tr>
        </thead>
      </table>

        <!-- New Design Starts Here  -->
      <div class="row ml-0 mb-4">
        <div class="col-lg-9 col-md-12 pad">
           <div class="col-6 pad mt-2 mb-3">
              <div class="purch-border input-group custom-input-group">
                <input type="text" name="refrence_code" placeholder="Type Reference number..." data-id = "{{$order->id}}" class="form-control refrence_number pl-1">
              </div>
            </div>
            <!-- buttons -->
            <div class="col-12 pad mt-4 mb-4">
              <a class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" id="addProduct">Add Product </a>
              <a class="btn purch-add-btn mt-3 fontmed col-2 btn-sale d-none" id="uploadExcelbtn">Upload Excel</a>
              <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="addInquiryProductbtn" type="submit">Add New Item</a>

             <!--  <button class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" type="submit">
              <a href="#" data-toggle="modal" data-target="#addInquiryProductModal">Add New Item</a>
              </button>   -->
            </div>
            <!-- buttons -->
            <div class="row">


              <div class="col-lg-6 col-md-6 mt-4">
                <p class="mb-0">Ship To</p>

        <div class="ship_body">

           @if($shipping_address != null)
           <p class="mb-2"><input type="hidden" value="2" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i><span> {{ @$shipping_address->title}}</span><br> {{ @$shipping_address->billing_address}},{{@$order->customer->language ==  "en" ? $shipping_address->getcountry->name : (@$shipping_address->getcountry->thai_name != null ? @$shipping_address->getcountry->thai_name : @$shipping_address->getcountry->name )}},
             @if(@$order->customer->language == 'en')
            {{@$shipping_address->getstate->name}},
            @else
            {{@$shipping_address->getstate->thai_name != null ? @$shipping_address->getstate->thai_name : @$shipping_address->getstate->name}},
            @endif
            {{@$shipping_address->billing_city.', '.@$shipping_address->billing_zip }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{@$shipping_address->billing_phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{@$shipping_address->billing_email}}</a></li>
        </ul>
        @elseif(@$order->customer !== null)
        <p class="mb-2"><input type="hidden" value="2" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ $order->customer->address_line_1.' '.$order->customer->address_line_2.', '.$order->customer->getcountry->name .', '.$order->customer->getstate->name.', '.$order->customer->city.', '.$order->customer->postalcode }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
        </ul>
        @endif
        </div>
      </div>
              <!-- <div class="col-lg-6 col-md-6 mt-4">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                      <p><strong>Comment To Customer:</strong><span class="inv-note inputDoubleClick" style="font-weight: normal;">@if($inv_note != null)<br> {!! $inv_note->note !!} @else <br>{{ 'Add a Comment' }} @endif</span>
                    <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" name="comment" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                    </p>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 pr-4">
                      <p><strong>Comment To Warehouse: </strong><span class="inv-note inputDoubleClick" style="font-weight: normal;">@if($warehouse_note != null)<br> {!! $warehouse_note->note !!} @else <br>{{ 'Add a Comment' }} @endif</span>
                        <textarea autocomplete="off" name="comment_warehouse" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" name="comment" maxlength="500">{{ $warehouse_note !== null ? $warehouse_note->note : '' }}</textarea>
                        </p>
                </div>
              </div>

              </div> -->


        </div>
        </div>
        <div class="col-lg-3 col-md-5 pt-4 mt-4">
          <div class="side-table" id="side-table">
            <table class="headings-color purch-last-left-table side-table">
              <tbody>
                <tr>
                  <td class="text-left fontbold">Sub Total:</td>
                  <td class="sub-total text-start fontbold">&nbsp;&nbsp;{{number_format(floor($sub_total*100)/100, 2, '.', ',') }}</td>
                </tr>
                <!-- <tr>
                  <td class="text-nowrap fontbold">Discount:</td>
                  <td class="fontbold text-start">&nbsp;&nbsp;
                    <span class="inv-discount mr-2 inputDoubleClick">{{ $order->discount == '' ? 0 : number_format($order->discount, 2, '.', ',') }}</span>
                    <input type="number" data-id="{{ $id }}" min="0" class="form-control mr-2 d-none fieldFocus" name="discount" value="{{$order->discount}}">
                  </td>
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">Shipping:</td>
                  <td class="fontbold text-start">&nbsp;&nbsp;
                    <span class="inv-shipping mr-2 inputDoubleClick">{{ $order->shipping == '' ? 0 :number_format($order->shipping, 2, '.', ',') }}</span>
                    <input type="number" data-id="{{ $id }}" min="0" class="form-control mr-2 d-none fieldFocus" name="shipping" value="{{$order->shipping}}">
                  </td>
                </tr> -->
                <tr>
                  <td class="text-nowrap fontbold">VAT:</td>
                  <td class="fontbold text-start total-vat">&nbsp;&nbsp;{{number_format(floor($vat*100)/100,2,'.',',')}}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">Total:</td>
                  <td class="fontbold text-start grand-total">&nbsp;&nbsp;{{number_format(floor($grand_total*100)/100,2,'.',',')}}</td>
                </tr>

                 <tr>
                  <td class="text-nowrap fontbold d-none">Paid:</td>
                  <td class="fontbold text-start d-none">&nbsp;&nbsp;20/09/2019</td>
                </tr>
                 <tr>
                  <td class="text-nowrap fontbold d-none">Due:</td>
                  <td class="fontbold text-start d-none">&nbsp;&nbsp;20/09/2019</td>
                </tr>
              </tbody>
            </table>
          </div>
          <input type="hidden" name="inv_id" class="list-id1" value="{{$id}}">

          <div class="row">
            <div class="text-center mt-4 col-lg-12 col-md-12">
            @if(@$order->status == 29)
             <div class="row">
               <div class="col-12 p-0">
                <div class="row">
                  <div class="col-3"></div>
                  <div class="col d-flex">
                     <a type="submit" data-id="{{ $order->id }}" class="btn purch-add-btn ml-auto mr-1 invoice-btn" style="font-size: 10px;">Confirm</a>
                    <a type="submit" data-id="{{ $order->id }}" class="btn purch-add-btn ml-auto mr-1 save-close" style="font-size: 10px;">Save & Close</a>
                  </div>
                  <div class="col">
                  </div>
                </div>


               </div>
              <!--  <div class="col-lg-6 col-md-4 col-sm-6 p-0">
                 <a href="{{url('sales')}}" class="btn purch-add-btn ml-auto mr-1 save-close-btn" style="font-size: 10px;">Save & Close</a>
               </div> -->
             </div>
             @endif

            </div>
          </div>

        </div>
      </div>
        <!-- New Design Ends Here  -->
    </div>
  </div>
</div>
</form>
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
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-inquiry-product-form']) !!}
            <div class="form-group">
              {!!Form::hidden('inv_id', $order->id)!!}
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
          <form method="post" action="{{url('sales/upload-order-excel')}}" class="upload-excel-form" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="form-group">
             <input type="hidden" name="inv_id" value="{{$order->id}}">
             <input type="hidden" id="add_product_to_invoice" name="add_product_to_invoice" value="{{$order->id}}">
            </div>

            <div class="form-group">
              <a href="{{asset('public/site/assets/sales/quotation/Order_Example_file.xlsx')}}" download><span class="btn btn-success" id="examplefilebtn">Download Example File</span></a>
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
          <div class="form-group" style="margin-top: 10px; margin-bottom: 50px;position: relative;">
            <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
            <input type="text" name="prod_name" value="" id="prod_name" class="form-control form-group" placeholder="Search by Product Reference #-Default Supplier- Product Description-Brand  (Press Enter)" autocomplete="off" style="padding-left:30px;">
            <div id="product_name_div_complete">
            </div>
            <input type="hidden" id="product_array">
            <input type="hidden" id="suppliers_array">
            <input type="hidden" id="supplier_id">
            <div id="tags_div" class="tags_div mt-4 mb-4 row ml-2"></div>
            <div>
                <a data-customer_id="{{$order->customer_id}}" data-inv_id="{{$order->id}}" class="btn float-right add_product_to" style="background-color: #5cb85c;">Confirm</a>
            </div>
          </div>
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

<div class="modal" id="createInvoiceModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Payment Details</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <form role="form" class="add-payment-form" method="post" >
      @csrf
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="col-xs-12 col-md-12">
                <div class="form-group">
                  <label>Note <small>(500 Characters Max)</small></label>
                  <textarea class="form-control" placeholder="Add a Note..." rows="6" name="note" maxlength="500"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="invoice_id" class="list-id">
        <button class="btn btn-success" type="submit" class="save-btn"><i class="fa fa-plus"></i> Add </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>
    </div>
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
    <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">
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
       <!--  <div class="modal-footer">
          <button type="submit" class="btn btn-primary save-doc-btn" id="addDocBtn">Upload</button>
        </div> -->
      </form>
  </div>
      </div>

      <!-- <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="order_id" value="{{$order->id}}">
          <div class="form-group">
            <label class="pull-left font-weight-bold">Files <span class="text-danger">*</span></label>
            <input class="font-weight-bold form-control-lg form-control" name="order_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary save-doc-btn" id="addDocBtn">Upload</button>
        </div>
      </form> -->
       <div class="fetched-files">
            <div class="d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
    </div>
  </div>
</div>
{{-- end modal code--}}

<div class="modal" id="file-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Quotation Files</h4>
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

 {{--Add Address Modal--}}
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
          <input type="hidden" name="order_id" id="order_id" value="{{$order->id}}">
          <input type="hidden" name="choice" id="choice" value="">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="title"> Reference Name:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control billing" name="billing_title">
            </div>

              <div class="form-group col-md-6">
              <label for="business_name"> Billing Contact Name :</label>
              <input required="true" type="text" class="form-control" id="billing_contact_name" name="billing_contact_name1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_email">Email:<b style="color:red;">*</b></label>
              <input required="true" type="email" class="form-control" name="billing_email1">
            </div>


            <div class="form-group col-md-6">
              <label for="business_name">Tax ID:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" name="tax_id">
            </div>

            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Phone:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" name="billing_phone">
            </div>
            <div class="form-group col-md-6">
              <label for="business_name">Fax:</label>
              <input required="true" type="text" class="form-control" name="billing_fax1">
            </div>
            </div>

            <div class="row">
            <div class="form-group col-md-6">
              <label for="business_name">Address:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" name="billing_address">
            </div>

            <div class="form-group col-md-6">
              <label for="business_name">Zip:<b style="color:red;">*</b></label>
              <input required="true" type="text" class="form-control" name="billing_zip">
            </div>


            </div>

            <div class="row">
            <div class="form-group col-md-6 d-none">
              <label for="business_country">Country:</label>
              <select required="true" class="font-weight-bold form-control-lg form-control selectpicker" data-live-search="true" title="Select Country" name="billing_country">
                <option value="217" selected disabled="true">Thailand</option>
              </select>
            </div>


            <div class="form-group col-md-6">
              <label for="business_state">State:<b style="color:red;">*</b></label>
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


            <div class="form-group col-md-6">
              <label for="contact_city">City:<b style="color:red;">*</b></label>
              <input type="text" required="true" class="form-control " name="billing_city">

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

  <!-- Modal For Add Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Completed Quotation Product Note</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-compl-quot-note-form" method="post">
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
        <input type="hidden" name="completed_quot_id" class="note-completed-quotation-id">
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
          <h4 class="modal-title">Completed Quotation Product Notes</h4>
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

<form class="export-quot-form-inc-vat" method="post" action="{{url('sales/export-quot-to-pdf-inc-vat/'.$id)}}">
  @csrf
  <input type="hidden" name="quo_id_for_pdf" id="quo_id_for_pdf" value="{{$id}}">
  <input type="hidden" name="is_proforma" class="is_proforma">
  <input type="hidden" name="order" id="order" value="order">
  <input type="hidden" name="column_name" id="column_name" value ="column_name">
</form>

@endsection

@section('javascript')

<script>
    var order = 1;
  var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#order').val(order);
    $('#column_name').val(column_name);

    $('.table-quotation-product').DataTable().ajax.reload();

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
</script>


<!-- When Warehouse User is logged In He/She Can't Edit Product Detail -->
@if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6 )
  <script type="text/javascript">

  </script>
@endif

@if( Auth::user()->role_id == 7)
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.edit-address').removeClass('d-none');
    });
  </script>
@endif

<script type="text/javascript">
$(".state-tags").select2();

  $("#delivery_request_date, #payment_due_date, #target_ship_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });

  $(document).on("change focusout","#delivery_request_date, #payment_due_date, #target_ship_date",function(e) {
    var quotation_id = "{{ $id }}";
    var attr_name = $(this).attr('name');

    var fieldvalue = $(this).prev().data('fieldvalue');
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
      url: "{{ route('save-order-data') }}",
      dataType: 'json',
      data: 'order_id='+order_id+'&'+attr_name+'='+$(this).val(),
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

  if(performance.navigation.type == 2){
    $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
   location.reload(true);
   // $("#side-table").load(location.href + " #side-table");
}

  $(document).on('click', '.export-proforma', function(e){
    var quo_id = $('#quo_id_for_pdf').val();
    $('.export-quot-form-for-proforma')[0].submit();
  });

  $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
  $(function(e){

  var table2 = $('.table-quotation-product').DataTable({
    // "bAutoWidth": false,
     processing: false,
      "language": {
          processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    searching: false,
    ordering: false,
    serverSide: true,
    bInfo: false,
    paging: false,
     "columnDefs": [
  { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,10,12] },
  { className: "dt-body-right", "targets": [8,9,11] }
],
    dom: 'lrtip',
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    ajax: {
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      url:"{{ url('accounting/get-completed-quotation-products-to-list') }}"+"/"+{{ $order->id }},
      data : function(data) {
        data.sort_order = order,
        data.column_name = column_name
      }
    },
    columns: [
      { data: 'action', name: 'action' },
      { data: 'refrence_code', name: 'refrence_code' },
      { data: 'description', name: 'description' },
      { data: 'brand', name: 'brand' },
      // { data: 'sell_unit', name: 'sell_unit' },
      { data: 'quantity', name: 'quantity' },
      { data: 'number_of_pieces', name: 'number_of_pieces' },
      { data: 'exp_unit_cost', name: 'exp_unit_cost' },
      { data: 'margin', name: 'margin' },
      { data: 'unit_price', name: 'unit_price' },
      { data: 'discount', name: 'discount' },
      { data: 'vat', name: 'vat' },
      { data: 'unit_price_with_vat', name: 'unit_price_with_vat' },
      // { data: 'total_price', name: 'total_price' },
      { data: 'total_amount', name: 'total_amount' },
      { data: 'supply_from', name: 'supply_from' },
      { data: 'notes', name: 'notes' },
    ],
    initComplete: function () {
      if("{{Auth::user()->role_id}}" == 7)
      {
        // $('.inputDoubleClick').removeClass('inputDoubleClick');
        // $('.selectDoubleClick').removeClass('selectDoubleClick');
        // $('.add-notes').addClass('d-none');
      }
      // Enable THEAD scroll bars
      $('.dataTables_scrollHead').css('overflow', 'auto');

      // Sync THEAD scrolling with TBODY
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });


    },
       drawCallback: function(){
      $('#loader_modal').modal('hide');
    }
 });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    // alert();
          table2.search($(this).val()).draw();
  }
  });

  $(document).on("dblclick",".inputDoubleClick",function(){
        $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
    });

  $(document).on("focusout",".fieldFocus",function() {
      var order_id = "{{ $id }}";
      var attr_name = $(this).attr('name');

      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();

      if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
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

      // if(attr_name == 'target_ship_date')
      // {
      //   $(this).prev().html($(this).val());
      //   if($(this).val() == '')
      //   {
      //     return false;
      //   }
      //   $(this).prev().html($(this).val());
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
      else if(attr_name == 'comment')
      {

         if($(this).val() == '')
        {
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
            return false;
        }else{
          $(this).prev().html($(this).val());
        }
      }
      else if(attr_name == 'comment_warehouse')
      {
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
      url: "{{ route('save-order-data') }}",
      dataType: 'json',
      data: 'order_id='+order_id+'&'+attr_name+'='+$(this).val(),
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

  $(document).on('click', 'input[type=number]', function(e){
    $(this).removeClass('disabled');
    $(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on('keypress keyup focusout', 'input[type=number], input[type=text], input[type=tel]', function(e){
    // alert('hi');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
    }

    if((e.keyCode === 13 || e.which === 0 ) && $(this).hasClass('active')){
      if($(this).hasClass('fieldFocus')){
        return false;
      }
      else if($(this).val() == $(this).prev().data('fieldvalue'))
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return;
      }
      $(this).addClass('disabled');
      $(this).removeClass('active');
      $(this).attr('readonly','true');
      if($(this).val() !== null)
      {
        var product_row = $(this).parent().parent().attr('id');
        var attr_name = $(this).attr('name');
        var new_value = $(this).val();
        var old_value = $(this).prev().html();
        // alert(old_value);
        // return;
        UpdateOrderQuotationData(product_row, attr_name, new_value,old_value);
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



    $(document).on('change','.selling_unit',function(e) {
        var old_value = $(this).prev().html();
      // alert(old_value);
      // return;
      var product_row = $(this).parent().parent().attr('id');
      var attr_name = $(this).attr('name');
      var new_value = $("option:selected",this).val();
      UpdateOrderQuotationData(product_row, attr_name, new_value,old_value);
  });


  $(document).on('change','.warehouse_id',function(e) {
        var old_value = $(this).prev().html();
     // alert(old_value);
     // return;
      var product_row = $(this).parent().parent().attr('id');
      var attr_name = $(this).attr('name');
      var new_value = $("option:selected",this).val();
      UpdateOrderQuotationData(product_row, attr_name, new_value,old_value);
  });

    $(document).on('click','.condition',function(){
      // alert('hi');
      var value = $(this).val();
      var id = $(this).data('id');
      // alert(id);

      var data = id.split(' ');
      // alert(data[0]);
      var product_row = data[0];
      var new_value = data[1];
      // alert(new_value);
      // return;
      if(value == 'qty')
      {
        UpdateOrderQuotationData(product_row, 'quantity', new_value,'clicked');
      }
      else
      {
        UpdateOrderQuotationData(product_row, 'number_of_pieces', new_value,'clicked');
      }

    });

  function UpdateOrderQuotationData(order_id,field_name,field_value,old_value){
      // alert('hi');
      // return;
    //console.log(field_name+' '+field_value+''+order_id);
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
        url: "{{ url('sales/update-order-quotation-data') }}",
        dataType: 'json',
        data: 'order_id='+order_id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'old_value'+'='+encodeURIComponent(old_value),
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
            toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-quotation-product').DataTable().ajax.reload();
            $('.sub-total').html(data.sub_total);
            $('.total-vat').html(data.total_vat);
            $('.grand-total').html(data.grand_total);
          }
            $('.table-order-history').DataTable().ajax.reload();

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

  $(document).on('keyup','.refrence_number',function(e){
    if(e.keyCode == 13)
    {
      e.preventDefault();
       var customer_id = "{{@$order->customer_id}}";
       // alert(customer_id);
      if(customer_id == '')
      {
        toastr.warning('Alert!', 'Please Select Customer First.' ,{"positionClass": "toast-bottom-right"});
        return false;
      }
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
          url: "{{ route('add-to-order-by-refrence-number') }}",
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
            $('.total_products').html(result.total_products);
            $('.sub-total').html(result.sub_total);
            $('.total-vat').html(result.total_vat);
            $('.grand-total').html(result.grand_total);
            $('.table-quotation-product').DataTable().ajax.reload();
          }
          else
          {
            toastr.error('Error!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.table-quotation-product').DataTable().ajax.reload();
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

  $(document).on('click', '#addInquiryProductbtn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-inquiry-product-to-order') }}",
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
            $('.add-inquiry-product-form')[0].reset();
            $('.table-quotation-product').DataTable().ajax.reload();

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
    });

  $(document).on('click','#addProduct, #uploadExcelbtn',function(){
     var customer_id = "{{@$order->customer_id}}";
       // alert(customer_id);
      if(customer_id == '')
      {
        toastr.warning('Alert!', 'Please Select Customer First.' ,{"positionClass": "toast-bottom-right"});
        return false;
      }
      if($(this).attr("id") == 'addProduct'){
        var page = "Quot";
        var query = "default";
        var inv_id = $("#quo_id_for_pdf").val();
        var _token = $('input[name="_token"]').val();
        var customer = $('.add-cust').val();
        $.ajax({
            url:"{{ route('autocomplete-fetch-product') }}",
            method:"POST",
            data:{query:query, _token:_token, inv_id:inv_id, page:page, customer:customer},
            beforeSend: function(){
            $('#product_name_div_complete').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
            },

            success:function(data){
                $('#product_name_div_complete').fadeIn();
                $('#product_name_div_complete').html(data);
            }
        });
      $('#addProductModal').modal('show');
      $('#prod_name').focus();
      }
    else if($(this).attr("id") == 'uploadExcelbtn'){
      $('#uploadExcel').modal('show');
      }

   });

  $(document).on('submit', '.upload-excel-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
     $.ajax({
        url: "{{ route('upload-order-excel') }}",
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

$(document).ready(function(){
  $('#prod_name').keyup(function(e){
    var page = "Quot";
    var query = $.trim($(this).val());
    if(query == '' || e.keyCode == 8 || 'keyup' )
    {
      $('#product_name_div_complete').empty();
    }
    var inv_id = $("#quo_id_for_pdf").val();
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
          $('#product_name_div_complete').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
          },
          success:function(data){
            $('#product_name_div_complete').fadeIn();
            $('#product_name_div_complete').html(data);
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
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

$(document).on('click', '.add_product_to', function(e){
      var inv_id = $(this).data('inv_id');
      var prod_id = $(this).data('prod_id');

      var customer_id = $(this).data('customer_id');
      var inv_id = $(this).data('inv_id');
      var prod_id = $(this).data('prod_id');
      var supplier_id = $('#supplier_id').val();
       var prod_ids = product_ids_array;
       var sup_ids = suppliers;
        $('#product_array').val(prod_ids);
        $('#suppliers_array').val(sup_ids);
        var input = $('#product_array').val();
        var sup = $('#suppliers_array').val();
      $('#prod_name').val($(this).text());
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

      $.ajax({
              method:"post",
            //   data:'selected_products='+prod_id+'&quotation_id='+inv_id,
            data:'selected_products='+input+'&quotation_id='+inv_id+'&supplier_id='+supplier_id+'&customer_id='+customer_id+'&suppliers='+sup,
              url:"{{ route('add-prod-to-order-quotation') }}",
              beforeSend:function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                $('#addProductModal').modal('hide');
                $('.table-quotation-product').DataTable().ajax.reload();
                $('.sub-total').html(data.sub_total);
                $('.total-vat').html(data.total_vat);
                $('.grand-total').html(data.grand_total);
                $('#sub_total').val(data.sub_total);
                $('.total_products').html(data.total_products);
                $('#prod_name').val('');
                $('#product_name_div_complete').empty();
                $("#tags_div").html("");
                product_ids_array = [];
                suppliers = [];
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
           });
  });

  $(".billing").on("focusout",function(){

    var title = $(this).val();
    var customer_id = $('.comp-name').data('customer-id');
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
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
        success: function(data){
          $("#loader_modal").modal('hide');
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
          $("#loader_modal").modal('hide');
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
    beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success:function(data){
        $("#loader_modal").modal('hide');
        if(data.success == false){
          $('input[name="'+data.field+'"]').after('<span class="invalid-feedback" role="alert"><strong>The '+data.field+' is Already Been taken</strong>');
            $('input[name="'+data.field+'"]').addClass('is-invalid');
        }
        else{
       $('#add_billing_detail_modal').modal('hide');
       if(data.choicee){
        if(data.choicee == 1){
       $('.bill_body').html(data.html);
        }else if(data.choicee ==2){
       $('.ship_body').html(data.html);

        }
      }
       }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
  });
})

  $(document).on('click', '.removeProduct', function(){
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
              url:"{{ route('remove-order-product') }}",
              beforeSend:function(){
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                $("#loader_modal").modal('show');
              },
              success:function(data){
                // $("#loader_modal").modal('hide');
                  if(data.success == true){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    location.reload();
                  $('.sub-total').html(data.sub_total);
                  $('.total-vat').html(data.total_vat);
                  $('.grand-total').html(data.grand_total);
                  $('.table-quotation-product').DataTable().ajax.reload();
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

  // $('.invoice-submit-form').on('submit',function (e) {
  $(document).on('click','.invoice-btn',function (e) {
    // alert('hi');
   var customer_id = "{{@$order->customer_id}}";
   // alert(customer_id);
   // return;
      if(customer_id == '')
      {
        toastr.warning('Alert!', 'Please Select Customer First.' ,{"positionClass": "toast-bottom-right"});
        return false;
      }
    var inverror = false;

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
        url: "{{ route('complete-debit-note') }}",
        method: 'post',
        data: $('.invoice-submit-form').serialize(),
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
            toastr.success('Success!','Debit Note Completed Successfully!',{"positionClass": "toast-bottom-right"});
            // setTimeout(function(){
            //   if(result.direct_invoice == 0){
            //      window.location.href = "{{ url('/sales/invoices')}}";
            //   }else{
            //        window.location.href = "{{ url('sales/account-recievable')}}";
            //   }

            // }, 500);
            window.location.href = "{{ url('accounting/debit-notes-dashboard')}}";
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

  // $(document).on('click', '.invoice-btn', function(e){
  //   $('.list-id').val($(this).data('id'));
  //   <?php
  //     foreach ($order->order_products as $order_product) {
  //     if($order_product->is_billed == 'Incomplete' || $order_product->is_billed == 'Inquiry')
  //     {
  //     // if($order_product->product->status != 1){
  //   ?>
  //       toastr.error('Error!', "Unable to Convert Quotation to Draft Invoice! Contain Inquiry/Incomplete products. Contact Purchasing",{"positionClass": "toast-bottom-right"});
  //   <?php
  //     }
  //     else
  //     {
  //   ?>
  //     $('.add-payment-form').submit();
  //   <?php
  //     }
  //     }
  //   ?>
  // });

@if(Session::has('successmsg'))
  toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
  @php
   Session()->forget('successmsg');
  @endphp
@endif

@if(Session::has('errormsg'))
  toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
  @php
   Session()->forget('errormsg');
  @endphp
@endif


  });
</script>

<script>
  function backFunctionality(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('sales') }}";
       document.location.href = url;
     }
   }
</script>

<script type="text/javascript">
  var prev_ship_body,prev_bill_body;
  $(function(e){

    $('.with_vat').on('click',function(){
      $('#with_vat').val('1');
    });

    $('.without_vat').on('click',function(){
      $('#with_vat').val(null);
    });
  // export pdf code
  $(document).on('click', '.export-pdf', function(e){
    var quo_id = $('#quo_id_for_pdf').val();
    // $('.export-quot-form')[0].submit();
    var orders = "{{$id}}";
    var with_vat = $('#with_vat').val();
    // alert(with_vat);
     var url = "{{url('sales/export-quot-to-pdf')}}"+"/"+orders+"/"+with_vat;
          window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
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
      url: "{{ route('add-order-document') }}",
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
          toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
          $('.addDocumentForm')[0].reset();
          // $('.addDocumentModal').modal('hide');
          // $('.download-docs').removeClass('d-none');
          $('.collapse').collapse("toggle");
          let sid = $("#sid").val();

    $.ajax({
      type: "post",
      url: "{{ route('get-quotation-files') }}",
      data: 'quotation_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      },
      error: function(request, status, error){
        // $("#loader_modal").modal('hide');
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

  $(document).on('click', '.download-documents', function(e){
    let sid = $(this).data('id');
    console.log(sid);
    $.ajax({
      type: "post",
      url: "{{ route('get-quotation-files') }}",
      data: 'quotation_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      },
      error: function(request, status, error){
        // $("#loader_modal").modal('hide');
      }
    });

  });

  $(document).on('click', '.delete-quotation-file', function(e){
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
                url:"{{ route('remove-quotation-file') }}",
                  beforeSend: function(){
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                      });
                    $("#loader_modal").modal('show');
                  },
                success:function(data){
                    $("#loader_modal").modal('hide');
                    if(data.search('done') !== -1){
                      myArray = new Array();
                      myArray = data.split('-SEPARATOR-');
                      let i_id = myArray[1];
                      $('#quotation-file-'+i_id).remove();
                      toastr.success('Success!', 'File deleted successfully.' ,{"positionClass": "toast-bottom-right"});
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

  });

  $(document).on('click','.edit-address',function(){
    prev_bill_body = $('.bill_body').html();
    prev_ship_body = $('.ship_body').html();

  var id = $(this).data('id');

  var pre = $(this).prev().val();
  $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

      $.ajax({
      type: "get",
      url: "{{ route('get-customer-addresses') }}",
      dataType: 'json',
      data: {customer_id:id,choice:pre},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        console.log(data);
        if(pre){
          if(pre == 1){
        $('.bill_body').html(data.html);
          }
          else{
        $('.ship_body').html(data.html);
          }
        }
        $('#loader_modal').modal('hide');

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
    });
});

  //   $(document).on('keyup focusout','.confirm-address',function(e) {
  //   if(e.keyCode == 27 || e.which == 0){
  //     $(this).addClass('d-none');
  //     $(this).removeClass('active');
  //     $(this).prev().removeClass('d-none');
  //   }
  // });


  $(document).on('change keyup focusout','.confirm-address',function(e){
    var pre = $(this).prev().val();

     if(e.keyCode == 27 || e.which == 0){
      $(this).addClass('d-none');
      if(pre == 1){
       $('.bill_body').html(prev_bill_body);
     }else{
      $('.ship_body').html(prev_ship_body);
     }
       return false;
      // $(this).prev().removeClass('d-none');
    }
    if($(this).val() === 'add-new' || $(this).val() === 'add-new-ship'){
      if($(this).val() == 'add-new'){
        document.getElementById('choice').value = 1;
      }
       if($(this).val() == 'add-new-ship'){
        document.getElementById('choice').value = 2;
      }
      $('#add_billing_detail_modal').modal('show');
    }
    else{
    var customer_id = $(this).data('id');
    var pre = $(this).prev().val();
    var order_id = '{{$order->id}}';
    var address_id = $(this).val();

    var quotation_id = '{{$id}}';
    var _token = $('input[name="_token"]').val();
    $.ajax({
      url:"{{ route('edit-customer-address-on-completed-quotation') }}",
      method:"POST",
      data:{_token:_token,order_id:order_id,address_id:address_id,previous:pre},
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
        if(pre){
          if(pre == 1){
        $('.bill_body').html(data.html);
          }else if(pre == 2){
        $('.ship_body').html(data.html);
          }
        }
        // $('.edit-functionality').addClass('d-none');
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  }
  });

  $(document).on('click', '.add-notes', function(e){
      var completed_draft_id = $(this).data('id');
      $('.note-completed-quotation-id').val(completed_draft_id);
      // alert(customer_id);

  });

  $('.add-compl-quot-note-form').on('submit', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
      $.ajax({
        url: "{{ route('add-completed-quotation-prod-note') }}",
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
            $('.table-quotation-product').DataTable().ajax.reload();

            // setTimeout(function(){
            //   window.location.reload();
            // }, 2000);

            $('.add-compl-quot-note-form')[0].reset();
            $('#add_notes_modal').modal('hide');

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
    let compl_quot_id = $(this).data('id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-completed-quotation-prod-note') }}",
      data: 'compl_quot_id='+compl_quot_id,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        // console.log(response);
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

  });

  $(document).on('click', '#delete-compl-note', function(e){
    let note_id = $(this).data('id');
    let compl_quot_id = $(this).data('compl_quot_id');
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    // By Farooq
    swal({
        title: "Alert!",
        text: "Are you sure you want to remove this note?",
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
      type: "get",
      url: "{{ route('delete-completed-quot-prod-note') }}",
      data: 'note_id='+note_id,
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        $.ajax({
            type: "get",
            url: "{{ route('get-completed-quotation-prod-note') }}",
            data: 'compl_quot_id='+compl_quot_id,
            beforeSend: function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            success: function(response){
              $("#loader_modal").modal('hide');
              if(response.no_data == true){
            $('.table-quotation-product').DataTable().ajax.reload();
                $("#notes-modal").modal('hide');
              }
              $('.fetched-notes').html(response);
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
      });
        // window.location.reload();
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
    url: "{{ route('checking-item-shortDesc-in-Op') }}",
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
        swal({ html:true, title:'Alert !!!', text:'<b>Must fill description & unit price, to add this item as a inquiry product!!!</b>'});
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
            url:"{{ route('enquiry-item-as-new-product-op') }}",
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
                  $('.table-quotation-product').DataTable().ajax.reload();
              }else{
                 toastr.error('Error!', 'Something went wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
          });
            }
            else{
                $("#loader_modal").modal('hide');
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

  var order_id = "{{$id}}";

   $('.table-order-history').DataTable({
          processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
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
            url:"{!! route('get-order-history') !!}",
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

              ],
            drawCallback: function ( settings ) {
              $('#loader_modal').modal('hide');
            }
    });

   $(document).on('change', 'select.select-common', function(){

    if($(this).attr('name') == "payment_terms_id")
    {


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
        url: "{{ route('payment-term-save-in-my-quotation') }}",
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
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });


    }

  });

   $('.edit_customer').on("click",function(){
  $('.update_customer').removeClass('d-none');
  $('.customer-addresses').addClass('d-none');
   });

   $(document).on('change keyup focusout','.add-cust',function(e){

     if(e.keyCode == 27 || e.which == 0){
      $(this).addClass('d-none');
      $('.customer-addresses').removeClass('d-none');
       return false;
      // $(this).prev().removeClass('d-none');
    }

    var order_id = '{{$order->id}}';
    var customer_id = $(this).val();

    var _token = $('input[name="_token"]').val();
    $.ajax({
      url:"{{ route('edit_customer_for_order') }}",
      method:"POST",
      data:{_token:_token,order_id:order_id,customer_id:customer_id},
      beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
          },
      success:function(data){
        if(data.success == true){

        $("#loader_modal").modal('hide');
        location.reload();
        }

        // $('.edit-functionality').addClass('d-none');
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

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

    $(document).on('click', '.export-pdf-proforma-inc-vat', function(e){
      var sortorder = $('#order').val();
      var column_name = $('#column_name').val();
      var customer_id = "{{@$order->customer_id}}";
      if(customer_id == '')
      {
        toastr.warning('Alert!', 'Please Select Customer First.' ,{"positionClass": "toast-bottom-right"});
        return false;
      }
    var quo_id = $('#quo_id_for_pdf').val();
    $('.is_proforma').val('yes');
    var orders = "{{$id}}";
    // $('.export-quot-form-inc-vat')[0].submit();
     var is_proforma = $('.is_proforma').val();
    // alert(with_vat);
     var url = "{{url('sales/credit-note-print')}}"+"/"+orders+"/"+column_name+"/"+sortorder+"/"+is_proforma;
          window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
  });

    $(document).on('click','.save-close',function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      window.location.href = "{{ url('accounting/debit-notes-dashboard')}}";
    });

    var product_ids_array = [];
  var suppliers = [];
$(document).on('click', '.add_product_to_tags', function(e){
    var prod_id = $(this).data('prod_id');
    var prod_name = $(this).data('prod_name');
    var prod_desc = $(this).data('prod_description');
    var supplier_id = $(this).data('supplier_id');
    $('#supplier_id').val(supplier_id)
    product_ids_array.push(prod_id);
    suppliers.push(supplier_id);
    $('.tags_div').append('<button id="'+prod_id+'" style="white-space: normal; margin-right:10px;" class="col-3 btn btn-primary mt-2">'+prod_name+' - '+prod_desc+'<i data-prod_id="'+prod_id+'" data-supplier_id="'+supplier_id+'" class="fa fa-close ml-3 remove-products" style="position: absolute; top: 4px; right: 5px;"></i></button>');
});

$(document).on('click','.remove-products',function(e){
    var prod_id = $(this).data('prod_id');
    var supplier_id = $(this).data('supplier_id');

    for( var i = 0; i < product_ids_array.length; i++){
        if ( product_ids_array[i] === prod_id) {
            product_ids_array.splice(i, 1);
        }
    }
    for( var i = 0; i < suppliers.length; i++){
        if ( suppliers[i] === supplier_id) {
            suppliers.splice(i, 1);
        }
    }

    document.getElementById(prod_id).remove();
});
</script>
@stop

