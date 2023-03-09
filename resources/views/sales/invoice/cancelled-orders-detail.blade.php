@extends('sales.layouts.layout')

@section('title','Quotations Products | Sales')
<?php
use Carbon\Carbon;
?>
@section('content')
<style type="text/css">
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
.disabled {
    pointer-events: none;
    cursor: default;
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
          <li class="breadcrumb-item"><a href="{{route('get-cancelled-orders')}}">Cancelled Order</a></li>
          <li class="breadcrumb-item active">Cancelled Order Details</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<form class="export-quot-form" method="get" action="{{url('sales/export-quot-to-pdf-cancelled/'.$id)}}">
  @csrf
  <input type="hidden" name="quo_id_for_pdf" id="quo_id_for_pdf" value="{{$id}}">
  <input type="hidden" name="print_bank_id" id="print_bank_id" value="">
</form>
<form method="post" class="mb-2 invoice-submit-form" enctype='multipart/form-data'>
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h5 class="maintitle text-uppercase fontbold mb-0">@if(!array_key_exists('cancelled_order', $global_terminologies))Cancelled Order @else {{$global_terminologies['cancelled_order']}} @endif # <span class="c-ref-id">
      @if(@$order->in_status_prefix !== null)
      {{@$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id}}
      @elseif(@$order->status_prefix !== null)
      {{@$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id}}
    @else
    {{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{@$order->ref_id}}
    @endif
  </span></h5>
  </div>
  <div class="col-lg-4 title-col">
    <div class="row mx-0 align-items-center">
      <h3 class="maintitle text-uppercase fontbold col-8 px-0">Bill To</h3>
      <!-- <a class="col-4 px-0 text-right" onclick="backFunctionality()">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color">back</button>
      </a> -->
    </div>
    {{-- <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</span></h5>
    <a onclick="backFunctionality()">
      <button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color">back</button>
    </a> --}}
  </div>
  <!-- New Design Starts  -->
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
        <p class="mb-2">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
        <ul class="list-inline list-unstyled pl-0">
          <li class="list-inline-item"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}</li>
          <li class="list-inline-item"><em class="fa fa-envelope"></em> {{@$order->user->user_name}} </li>
        </ul>
        <br>
        <div class="form-group">
          <label class="mb-1 font-weight-bold">Order Status:</label> <span class="dblclk-edit" data-type="ref_id">{{ @$order->statuses->title }}</span>
        </div>
        <div class="form-group">
          <label class="mb-1 font-weight-bold">Cancelled Order Date:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{$order->created_at != null ? Carbon::parse($order->created_at)->format('d/m/Y') : "--"}}</span>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-flex align-items-center mb-1">
            @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/sales/images/' . @Auth::user()->user_details->image))
            <img src="{{asset('public/uploads/sales/images/'.@Auth::user()->user_details->image)}}" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">
            @else
            <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">
            @endif

          <div class="pl-2 comp-name" data-customer-id="{{$order->customer->id}}"><p>{{$order->customer->company}}</p></div>
        </div>
        <div class="bill_body">
          @if($billing_address != null)
           <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address mr-3" data-id="{{$order->customer_id}}"></i>{{@$billing_address->billing_address.','.@$billing_address->getcountry->name .', '.@$billing_address->getstate->name.', '.@$billing_address->billing_city.', '.@$billing_address->billing_zip }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{@$billing_address->billing_phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{@$billing_address->billing_email}}</a></li>
        </ul>
        <ul class="d-flex list-unstyled">
      <li><b>Tax ID:</b> @if($billing_address->tax_id !== null) {{ $billing_address->tax_id }} @endif</li>
    </ul>
          @else
        <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ @$order->customer->address_line_1.' '.@$order->customer->address_line_2.', '.@$order->customer->getcountry->name .', '.@$order->customer->getstate->name.', '.@$order->customer->city.', '.@$order->customer->postalcode }}</p>
        <ul class="d-flex list-unstyled">
          <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
          <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
        </ul>
        @endif
        </div>
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

        <ul class="d-flex list-unstyled">
    <li class="pt-2 fontbold">Memo:</li>
    <span class="pl-4 pt-2 inputDoubleClick">
      @if($order->memo != null)
      {{$order->memo}}
      @else
      <p>No Memo</p>
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$order->memo}}">
  </ul>

  <ul class="d-flex list-unstyled">
          <li class="pt-2 fontbold" style="width: 180px;">Choose Bank For Prints:</li>
          <select class="form-control company-banks mb-2" name="company-banks" style="width: 40%; margin-left: 25px; height: 40px;">
            <option disabled value="">Choose Bank</option>
            @foreach ($company_banks as $bank)
              <option value="{{$bank->getBanks->id}}">{{$bank->getBanks->title}}</option>
            @endforeach
          </select>
        </ul>
      </div>
    <!-- export pdf form starts -->

    <!-- export pdf form ends -->
      <div class="col-lg-12 text-uppercase fontbold">
        <a href="#"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button></a>
        <a href="javascript:void(0);">
          <!-- <button type="button" class="btn text-uppercase purch-btn headings-color btn-color export-pdf">print</button> -->

          <span class="vertical-icons export-pdf" title="Print">
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
            <th>{{$global_terminologies['our_reference_number']}}</th>
            <th>HS Code</th>
            <th>{{$global_terminologies['product_description']}} </th>
            <th>{{$global_terminologies['note_two']}}</th>
            <th>{{$global_terminologies['category']}} </th>
            <th>{{$global_terminologies['type']}}</th>
            <th>{{$global_terminologies['brand']}}</th>
            <th>{{$global_terminologies['temprature_c']}}</th>
            <th>{{$global_terminologies['supply_from']}}</th>
            <!-- <th class="inv-head">Sales <br> Unit </th> -->
            <th class="inv-head"># {{$global_terminologies['qty']}}<br>Ordered</th>
            <th class="inv-head"># {{$global_terminologies['qty']}} <br>Sent </th>
            <th class="inv-head"># {{$global_terminologies['pieces']}}<br>Ordered </th>
            <th class="inv-head"># {{$global_terminologies['pieces']}} <br>Sent</th>

            <th class="inv-head">*{{$global_terminologies['reference_price']}} </th>
            <th class="inv-head">* {{$global_terminologies['default_price_type']}} </th>
            <th class="inv-head">{{$global_terminologies['default_price_type_wo_vat']}} </th>
            <th class="inv-head">Discount </th>
            <th class="inv-head">VAT </th>
            <th class="inv-head">{{$global_terminologies['unit_price_vat']}}</th>
            <th class="inv-head"> {{$global_terminologies['total_amount_inc_vat']}}</th>
            <th class="inv-head">PO QTY </th>
            <th class="inv-head">PO No </th>

          </tr>
        </thead>
      </table>

        <!-- New Design Starts Here  -->
      <div class="row ml-0 mb-4">
        <div class="col-9 pad">
           <div class="col-6 pad mt-2 mb-3">
              <div class="purch-border input-group custom-input-group" id="search_by_refno">
                <input type="text" name="refrence_code" placeholder="Type Reference number..." data-id = "{{$order->id}}" class="form-control refrence_number pl-1">
              </div>
            </div>
            <!-- buttons -->
            <div class="col-12 pad mt-4 mb-4">
              <a class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" id="addProduct">Add Product </a>
              <a class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" id="uploadExcelbtn">Upload Excel</a>
              <a class="btn purch-add-btn mt-3 fontmed col-3 btn-sale" id="addInquiryProductbtn" type="submit">Add New Item</a>

             <!--  <button class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" type="submit">
              <a href="#" data-toggle="modal" data-target="#addInquiryProductModal">Add New Item</a>
              </button>   -->
            </div>
            <!-- buttons -->
            <div class="row">


                <div class="col-lg-6 mt-4">
                    <p class="mb-0">Ship To</p>

                    <div class="ship_body">
                        @if($shipping_address != null)
                            <p class="mb-2"><input type="hidden" value="2" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ @$shipping_address->billing_address.', '.@$shipping_address->getcountry->name .', '.@$shipping_address->getstate->name.', '.@$shipping_address->billing_city.', '.@$shipping_address->billing_zip }}</p>
                            <ul class="d-flex list-unstyled">
                                <li><a href="#"><i class="fa fa-phone pr-2"></i> {{@$shipping_address->billing_phone}}</a></li>
                                <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{@$shipping_address->billing_email}}</a></li>
                            </ul>
                        @else
                            <p class="mb-2"><input type="hidden" value="2" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ $order->customer->address_line_1.' '.@$order->customer->address_line_2.', '.@$order->customer->getcountry->name .', '.@$order->customer->getstate->name.', '.@$order->customer->city.', '.$order->customer->postalcode }}</p>
                            <ul class="d-flex list-unstyled">
                                <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
                                <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6 mt-4">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <p id="comments_section"><strong>Remark: </strong><span class="inv-note inputDoubleClick" style="font-weight: normal;">@if($inv_note != null)<br> {!! $inv_note->note !!} @else<br> {{ 'Add a Comment' }} @endif</span>
                                <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" name="comment" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                            </p>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 pr-4">
                            <p><strong class="d-block">Comment To Warehouse: </strong><span class="inv-note inputDoubleClick" style="font-weight: normal;">@if($warehouse_note != null)<br> {!! $warehouse_note->note !!} @else <br>{{ 'Add a Comment' }} @endif</span>
                                <textarea autocomplete="off" name="comment_warehouse" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" name="comment" maxlength="500">{{ $warehouse_note !== null ? $warehouse_note->note : '' }}</textarea>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
              <div class="col-lg-7">
                <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">

                <table class="table-order-history entriestable table table-bordered text-center" style="width: 100%;font-size: 12px;">
                   <thead class="sales-coordinator-thead ">
                    <tr>
                      <th>User  </th>
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

              <div class="col-lg-5">
                <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3 h-100">
                  <table class="my-tablee dot-dash">
                   <thead class="sales-coordinator-thead ">
                     <tr>
                      <th>User  </th>
                      <th>Date/time </th>
                      <th>Status </th>
                      <th>New Status</th>
                     </tr>
                   </thead>
                   <tbody>
                      @foreach($status_history as $history)
                      <tr>
                        <td>{{$history->get_user->name}}</td>
                        <td>{{Carbon::parse(@$history->created_at)->format('d/m/Y')}}</td>
                        <td>{{$history->status}}</td>
                        <td>{{$history->new_status}}</td>
                      </tr>
                      @endforeach
                   </tbody>
                  </table>
                </div>
              </div>

        </div>
        </div>
        <div class="col-lg-3 pt-4 mt-4">
          <div class="side-table">
            <table class="headings-color purch-last-left-table side-table">
              <tbody>
                <tr>
                  <td class="text-right fontbold">Sub Total:</td>
                  <td class="sub-total text-start fontbold">&nbsp;&nbsp;{{number_format($sub_total, 2, '.', ',') }}</td>
                </tr>
                <tr>
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
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">VAT:</td>
                  <td class="fontbold text-start total-vat">&nbsp;&nbsp;{{number_format($vat,2,'.',',')}}</td>
                </tr>
                <tr>
                  <td class="text-nowrap fontbold">Total:</td>
                  <td class="fontbold text-start grand-total">&nbsp;&nbsp;{{number_format($grand_total,2,'.',',')}}</td>
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
            <div class="text-center confirm-btn mt-4 col-lg-12">
             @if($order->primary_status == 1 && $order->status == 6)
                <a type="submit" data-id="{{ $order->id }}" class="btn purch-add-btn ml-auto mr-1 invoice-btn">Confirm Quotation</a>
                <a href="{{url('sales')}}" class="btn ml-auto mr-4 btn-success save-close-btn">Save & Close</a>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Search Product</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group" style="margin-top: 10px; margin-bottom: 50px;position: relative;">
          <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
          <input type="text" name="prod_name" value="" id="prod_name" class="form-control form-group" placeholder="Search by Product Reference #-Default Supplier- Product Description-Brand  (Press Enter)" style="padding-left:30px;">
          <div id="product_name_div_complete">
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
                  <label>{{$global_terminologies['note_two']}} <small>(500 Characters Max)</small></label>
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
        <h4 class="modal-title">UPLOADED DOCUMENTS</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="row justify-content-end" style="width: 100%;">
      <a href="#demo" class="btn btn-primary" data-toggle="collapse" id="upload_doc_btn" style="margin-top: 7px;">Upload Document</a>
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


@endsection

@section('javascript')
@if(Auth::user()->role_id)
  <script type="text/javascript">
    $( document ).ready(function(){
      $('#addInquiryProductbtn').hide();
      $('#uploadExcelbtn').hide();
      $('#addProduct').hide();
      $('#search_by_refno').hide();
      // $('#comments_section').addClass('d-none');
      $('#upload_doc_btn').addClass('d-none');
      $('.edit-address').addClass('d-none');
    });
  </script>
@endif
<script type="text/javascript">
  $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
  $(function(e){

    $('.table-quotation-product').DataTable({
      // "bAutoWidth": false,
       processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      searching: false,
      ordering: false,
      serverSide: true,
      bInfo: false,
      paging: false,
       "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,9,15] },
    { className: "dt-body-right", "targets": [8,10,11,12,13,14,16,17,18,19,20,21,22] }
  ],
      dom: 'lrtip',
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      ajax: "{{ url('sales/get-completed-quotation-products-to-list-cancel') }}"+"/"+{{ $order->id }},
      columns: [
        { data: 'action', name: 'action' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'hs_code', name: 'hs_code' },
        { data: 'description', name: 'description' },
        { data: 'notes', name: 'notes' },
        { data: 'category', name: 'category' },
        { data: 'type_id', name: 'type_id' },
        { data: 'brand', name: 'brand' },
        { data: 'temprature', name: 'temprature' },
        { data: 'supply_from', name: 'supply_from' },
        // { data: 'sell_unit', name: 'sell_unit' },
        { data: 'quantity', name: 'quantity' },
        { data: 'quantity_ship', name: 'quantity_ship' },
        { data: 'number_of_pieces', name: 'number_of_pieces' },
        { data: 'pcs_shipped', name: 'pcs_shipped' },
        { data: 'exp_unit_cost', name: 'exp_unit_cost' },
        { data: 'margin', name: 'margin' },
        { data: 'unit_price', name: 'unit_price' },
        { data: 'discount', name: 'discount' },
        { data: 'vat', name: 'vat' },
        { data: 'unit_price_with_vat', name: 'unit_price_with_vat' },
        // { data: 'total_price', name: 'total_price' },
        { data: 'total_amount', name: 'total_amount' },
        { data: 'po_quantity', name: 'po_quantity' },
        { data: 'po_number', name: 'po_number' },
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
       var url = "{{ url('/sales/get-cancelled-orders') }}";
       document.location.href = url;
     }
   }
</script>
<script type="text/javascript">
  $(function(e){
  // export pdf code
  $(document).on('click', '.export-pdf', function(e){
    var bank = "{{@$bank}}";
    var bank_id = $('.company-banks').val();
    if(bank_id == null && bank != '')
    {
      toastr.warning('Please!', 'Select Bank First !!!',{"positionClass": "toast-bottom-right"});
      return false;
    }
    $('#print_bank_id').val(bank_id);
    var quo_id = $('#quo_id_for_pdf').val();
    $('.export-quot-form')[0].submit();
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

      }
    });

  });

//   $(document).on('click', '.delete-quotation-file', function(e){
//       var id = $(this).data('id');
//       swal({
//           title: "Alert!",
//           text: "Are you sure you want to delete this file? You won't be able to undo this.",
//           type: "info",
//           showCancelButton: true,
//           confirmButtonClass: "btn-danger",
//           confirmButtonText: "Yes!",
//           cancelButtonText: "No!",
//           closeOnConfirm: true,
//           closeOnCancel: false
//         },
//          function(isConfirm) {
//            if (isConfirm){
//              $.ajax({
//                 method:"get",
//                 data:'id='+id,
//                 url:"{{ route('remove-quotation-file') }}",
//                 beforeSend:function(){
//                 },
//                 success:function(data){
//                     if(data.search('done') !== -1){
//                       myArray = new Array();
//                       myArray = data.split('-SEPARATOR-');
//                       let i_id = myArray[1];
//                       $('#quotation-file-'+i_id).remove();
//                       toastr.success('Success!', 'File deleted successfully.' ,{"positionClass": "toast-bottom-right"});
//                     }
//                 }
//              });
//           }
//           else{
//               swal("Cancelled", "", "error");
//           }
//      });
//   });

//   $(document).on('click','.edit-address',function(){

//   var id = $(this).data('id');

//   var pre = $(this).prev().val();
//   $.ajaxSetup({
//           headers: {
//             'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
//           }
//       });

//       $.ajax({
//       type: "get",
//       url: "{{ route('get-customer-addresses') }}",
//       dataType: 'json',
//       data: {customer_id:id,choice:pre},
//       beforeSend: function(){
//         $('#loader_modal').modal({
//           backdrop: 'static',
//           keyboard: false
//         });
//         $('#loader_modal').modal('show');
//       },
//       success: function(data){
//         console.log(data);
//         if(pre){
//           if(pre == 1){
//         $('.bill_body').html(data.html);
//           }
//           else{
//         $('.ship_body').html(data.html);
//           }
//         }
//         $('#loader_modal').modal('hide');

//         }
//     });
// });

//   $(document).on('change','.confirm-address',function(){
//     if($(this).val() === 'add-new' || $(this).val() === 'add-new-ship'){
//       if($(this).val() == 'add-new'){
//         document.getElementById('choice').value = 1;
//       }
//        if($(this).val() == 'add-new-ship'){
//         document.getElementById('choice').value = 2;
//       }
//       $('#add_billing_detail_modal').modal('show');
//     }
//     else{
//     var customer_id = $(this).data('id');
//     var pre = $(this).prev().val();
//     var order_id = '{{$order->id}}';
//     var address_id = $(this).val();

//     var quotation_id = '{{$id}}';
//     var _token = $('input[name="_token"]').val();
//     $.ajax({
//       url:"{{ route('edit-customer-address-on-completed-quotation') }}",
//       method:"POST",
//       data:{_token:_token,order_id:order_id,address_id:address_id,previous:pre},
//       beforeSend: function(){
//             $('#loader_modal').modal({
//                 backdrop: 'static',
//                 keyboard: false
//               });
//             $("#loader_modal").modal('show');
//           },
//       success:function(data){
//         $("#loader_modal").modal('hide');
//         $('.confirm-address').addClass('d-none');
//         if(pre){
//           if(pre == 1){
//         $('.bill_body').html(data.html);
//           }else if(pre == 2){
//         $('.ship_body').html(data.html);
//           }
//         }
//         // $('.edit-functionality').addClass('d-none');
//       }
//     });
//   }
//   });

//   $(document).on('click', '.add-notes', function(e){
//       var completed_draft_id = $(this).data('id');
//       $('.note-completed-quotation-id').val(completed_draft_id);
//       // alert(customer_id);

//   });

//   $('.add-compl-quot-note-form').on('submit', function(e){
//     e.preventDefault();
//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
//         }
//     });
//       $.ajax({
//         url: "{{ route('add-completed-quotation-prod-note') }}",
//         dataType: 'json',
//         type: 'post',
//         data: new FormData(this),
//         contentType: false,
//         cache: false,
//         processData:false,
//         beforeSend: function(){
//           $('.save-btn').addClass('disabled');
//           $('.save-btn').attr('disabled', true);
//           $('#loader_modal').modal({
//             backdrop: 'static',
//             keyboard: false
//           });
//           $('#loader_modal').modal('show');
//         },
//         success: function(result){
//           $('.save-btn').attr('disabled', true);
//           $('.save-btn').removeAttr('disabled');
//           $('#loader_modal').modal('hide');
//           if(result.success == true){
//             toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
//             $('.table-quotation-product').DataTable().ajax.reload();

//             // setTimeout(function(){
//             //   window.location.reload();
//             // }, 2000);

//             $('.add-compl-quot-note-form')[0].reset();
//             $('#add_notes_modal').modal('hide');

//           }else{
//             toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
//           }

//         },
//         error: function (request, status, error) {
//               /*$('.form-control').removeClass('is-invalid');
//               $('.form-control').next().remove();*/
//               $('#loader_modal').modal('hide');
//               $('.save-btn').removeClass('disabled');
//               $('.save-btn').removeAttr('disabled');
//               json = $.parseJSON(request.responseText);
//               $.each(json.errors, function(key, value){
//                     $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
//                     $('input[name="'+key+'"]').addClass('is-invalid');
//                     $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
//                     $('textarea[name="'+key+'"]').addClass('is-invalid');


//               });
//           }
//       });
//   });

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

              ]
    });

 });
</script>
@stop

