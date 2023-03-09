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
.dataTables_scrollBody:not(.dataTables_scrollFoot)::-webkit-scrollbar {
  display: none;
}
.dataTables_scrollHeadInner > .table-order-history{
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
          <li class="breadcrumb-item active">Quotation Details</li>
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
<form method="post" class="mb-2 invoice-submit-form" enctype='multipart/form-data'>
  <input type="hidden" name="user_id" id="user_id" @if($order->user_id != null) value="{{$order->user_id}}" @endif>

<div class="row mb-3">

  <div class="col-md-4 title-col">
    <!-- <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</span></h5> -->

  </div>
  <!-- New Design Starts  -->
  <div class="col-lg-12 col-md-12">
    <div class="row">
      <div class="col-lg-8 col-md-6 col-sm-12">
        <div class="col-md-8 title-col p-0 mb-3">
          <h5 class="maintitle text-uppercase fontbold mb-0">Quotations # <span class="c-ref-id">{{$order->status_prefix}}-{{$order->ref_prefix}}{{$order->ref_id }}</span></h5>
        </div>
        <!-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo"> -->
        <div class="d-flex align-items-center mb-0">
      <div class="phone__div">
         @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . @$company_info->logo))
        <img src="{{asset('public/uploads/logo/'.@$company_info->logo)}}" class="img-fluid" style="height: 80px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="height: 80px;" align="big-qummy">
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
          <label class="mb-1 font-weight-bold">Quotation Date:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
        </div>

        @if($showRadioButtons == 1)
          <input type="radio" id="vat" name="is_vat" value="0" {{$order->is_vat == 0 ? 'checked' : ''}}>
          <label for="vat">Vat</label><br>
          <input type="radio" id="non_vat" name="is_vat" value="1" {{$order->is_vat == 1 ? 'checked' : ''}}>
          <label for="nonvat">Non-Vat</label>
        @endif

         <table>
            <tbody>
              <tr>
                <td style="width: 52%;">
                  <table width="100%" class="table-bordered" style="font-size: 12px;">
                    <tbody>
                      <tr>
                        <th>View</th>
                        <td>
                          @if(@$view_last_date->created_at != '')
                            {{$view_last_date->user->name}}
                            @endif
                          </td>
                           <td>
                          @if(@$view_last_date->created_at != '')
                            {{$view_last_date->created_at}}
                            @endif
                          </td>
                      </tr>
                      <tr>
                        <th>View(Inc VAT)</th>
                        <td>
                          @if(@$view_incvat_last_date->created_at != '')
                            {{$view_incvat_last_date->user->name}}
                          @endif
                        </td>
                        <td>
                          @if(@$view_incvat_last_date->created_at != '')
                            {{$view_incvat_last_date->created_at}}
                          @endif
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
                <td style="width: 48%;">
                </td>
              </tr>
            </tbody>
          </table>

      </div>
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="row mb-3 title-col billing__div">
          <div class="col-4">
            <h5 class="maintitle text-uppercase fontbold mb-0">Bill To</h5>
          </div>
          <div class="col-8">
            <div class="pull-right">
            <a onclick="backFunctionality()" class="d-none">
              <!-- <button type="button" class="btn text-uppercase purch-btn mr-2 headings-color btn-color">back
              </button> -->
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
        <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit_customer mr-3" data-id=""></i></p>
        <div class="update_customer d-none">
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

       @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11)
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
  <div class="customer-div">
  <div class="customer-addresses phone__div">
  <div class="d-flex align-items-center mb-1">
      @if(@$order->customer->logo != null && file_exists( public_path() . '/uploads/sales/customer/logos/' . @$order->customer->logo))
      <img src="{{asset('public/uploads/sales/customer/logos/'.@$order->customer->logo)}}" class="img-fluid mb-5" style="height: 60px;" align="big-qummy">
      @else
      <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid mb-5" style="height: 60px;" align="big-qummy">
      @endif

    <div class="pl-2 comp-name" data-customer-id="{{$order->customer->id}}"><p><a href="{{url('sales/get-customer-detail/'.@$order->customer->id)}}" target="_blank">{{$order->customer->reference_name}}</a></p></div>
  </div>
  </div>
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
    @else
  <p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ @$order->customer->address_line_1.' '.@$order->customer->address_line_2.', '.@$order->customer->getcountry->name .', '.@$order->customer->getstate->name.', '.@$order->customer->city.', '.@$order->customer->postalcode }}</p>
  <ul class="d-flex list-unstyled">
    <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
    <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
  </ul>
  @endif
  </div>
</div>

        @php
          $delivery_request_date = $order->delivery_request_date != null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : "";
        @endphp
        <ul class="d-flex list-unstyled mb-0">
          <li class="pt-2 fontbold" style="width: 180px;">@if(!array_key_exists('delivery_request_date', $global_terminologies))Delivery Request Date: @else {{$global_terminologies['delivery_request_date']}} @endif <span style="color: red;">*</span></li>
          @php
           if($order->delivery_request_date == null)
              {
                  $style = "color:red;";
              }
              else
              {
                  $style = "";
              }
          @endphp
          <span class="pl-4 pt-2 inputDoubleClick" id="delivery_request_date_span" data-fieldvalue="{{$delivery_request_date}}">
            @if($order->delivery_request_date != null)
            {{Carbon::parse($order->delivery_request_date)->format('d/m/Y')}}
            @else
            <p style="font-weight: 100; {{@$style}}">@if(!array_key_exists('delivery_request_date', $global_terminologies))Delivery Request Date @else {{$global_terminologies['delivery_request_date']}} @endif Here</p>
            @endif
          </span>
          <input type="text" class="ml-4 mt-2 d-none delivery_request_date iphone__input" name="delivery_request_date" id="delivery_request_date" value="{{$delivery_request_date}}">
        </ul>

        <ul class="d-flex mb-0 pt-2 list-unstyled">
          <li class=" fontbold" style="width: 180px;">Payment Terms:</li>
          <span class="pl-4 inputDoubleClick" id="sup_payment_term">
            @if($order->payment_terms_id !== null)
            {{@$order->paymentTerm->title}}
            @else
            Select Term Here
            @endif
          </span>
          <select class="form-control payment_terms_id d-none mb-2 select-common iphone__input" name="payment_terms_id" style="width: 40%; margin-left: 25px; height: 40px;">
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
          <input type="text" class="ml-4 mt-2 d-none payment_due_date iphone__input" name="payment_due_date" id="payment_due_date" value="{{$payment_due_date}}">
        </ul>
        @php
          $target_ship_date = $order->target_ship_date != null ? Carbon::parse($order->target_ship_date)->format('d/m/Y') : "";
        @endphp
  @if($targetShipDate['target_ship_date']==1)

        <ul class="d-flex list-unstyled mb-0">
          <li class="pt-2 fontbold" style="width: 180px;">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif:@if($targetShipDate['target_ship_date_required']==1) <span style="color: red;">*</span> @endif</li>
           @if($targetShipDate['target_ship_date_required']==1)
           @php
           if($order->target_ship_date == null)
                {
                    $style1 = "color:red;";
                }
                else
                {
                    $style1 = "";
                }
          @endphp
          @endif
          <span class="pl-4 pt-2 inputDoubleClick" data-fieldvalue="{{$target_ship_date}}">
            @if($order->target_ship_date != null)
            {{Carbon::parse($order->target_ship_date)->format('d/m/Y')}}
            @else
            @if($targetShipDate['target_ship_date_required']==1)

            <p style="color: red;font-weight: 100;">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif Here</p>
            @else
            <p style="font-weight: 100;">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif Here</p>
            @endif
            @endif
          </span>
          <input type="text" class="ml-4 mt-2 d-none target_ship_date iphone__input" name="target_ship_date" id="target_ship_date" value="{{$target_ship_date}}">
        </ul>
@endif
        <ul class="d-flex list-unstyled">
          <li class="pt-2 fontbold" style="width: 180px;">Ref. Po #:</li>
          <span class="pl-4 pt-2 inputDoubleClick ref_class" data-fieldvalue="{{$order->memo}}">
            @if($order->memo != null)
            {{$order->memo}}
            @else
            <p class="m-0">Ref. Po # Here</p>
            @endif
          </span>
          <!-- <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$order->memo}}"> -->
          <textarea class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" rows="4" style="resize: none;width: 100%">{{@$order->memo}}</textarea>
       </ul>

        <ul class="d-flex mb-0 list-unstyled mb-2">
          <li class=" fontbold" style="width: 180px;">Order Warehouse: <span style="color: red;">*</span></li>
          <span class="pl-4 inputDoubleClick" id="from_warehouse_id">
            @if(@$order->from_warehouse_id != null)
            {{@$order->from_warehouse->warehouse_title}}
            @else
            Select Warehouse Here
            @endif
          </span>
          <select class="form-control from_warehouse_id d-none mb-2 select-common iphone__input" name="from_warehouse_id" style="width: 40%; margin-left: 25px; height: 40px;">
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
        <li class="pt-2 fontbold" style="width: 180px;">Choose Bank For Prints:</li>
        <div style="width: 40%; margin-left: 25px">
          <select class="form-control company-banks state-tags mb-2 iphone__input" name="company-banks" id="company_banks_select">
            <option disabled value="">Choose Bank</option>
            @foreach ($company_banks as $bank)
              <option value="{{$bank->getBanks->id}}">{{$bank->getBanks->title}}</option>
            @endforeach
          </select>
        </div>
      </ul>

        <div class=" @if(array_key_exists(7, $print_prefrences) && $print_prefrences[7]['status'] != '1') d-none @endif">
         <ul class="d-flex list-unstyled">
          <li class="pt-2 fontbold" style="width: 180px;">Choose Sales Person:</li>
          <div style="width: 40%; margin-left: 25px">
            <select class="form-control sales_person state-tags mb-2 iphone__input" name="sales_person" id="sales_person_select">
            <option disabled selected value="">Choose Sales Person</option>
            @if($order->customer_id != null)
            <optgroup label = "Primary Sale Person">
              <option @if($order->user_id == $sales_person->primary_sale_id) selected @endif value = "{{$sales_person->primary_sale_id}}">{{$sales_person->primary_sale_person != null ? $sales_person->primary_sale_person->name: ''}}</option>
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
    <!-- export pdf form starts -->

    <!-- export pdf form ends -->
      <div class="col-lg-12 col-md-12 text-uppercase fontbold">
        <a onclick="history.go(-1)"><button type="button" class="btn text-uppercase purch-btn mr-2 headings-color btn-color d-none">back</button></a>
        <a href="#"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button></a>
        <a href="javascript:void(0);">
          <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf without_vat iphone__print__btn ">View</button>
        </a>

        <input type="hidden" name="show_discount_input" id="show_discount_input" value="1">

        <a href="javascript:void(0);">
          <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf-inc-vat with_vat iphone__print__btn ">View (inc VAT)</button>
        </a>

        <!-- <a href="javascript:void(0);">
          <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color add_row">Add Row</button>
        </a> -->

        @if($showDiscount == 1)
        <a href="javascript:void(0)">
          <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color"><input type="checkbox" name="show_price" id="show_price" checked="true" style="vertical-align: inherit;scale"> &nbsp;Show Discount</button>
        </a>
        @endif

        <!-- <a href="javascript:void(0);">
          <button type="button" class="btn text-uppercase purch-btn headings-color  btn-color export-proforma">Print Pro-Forma</button>
        </a> -->
        <div class="pull-right iphone__print__icons">
          @if($checkDocs > 0)
            @php $show = ""; @endphp
          @else
            @php $show = "d-none"; @endphp
          @endif

          <a href="javascript:void(0)" data-id="{{$order->id}}" data-toggle="modal" data-target="#file-modal" class="download-documents d-none"><button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">View documents<i class="pl-1 fa fa-download"></i></button></a>

          <a href="{{ route('order-docs-download',$order->id) }}" class="d-none">
            <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
          </a>
        <!--   <a href="javascript:void(0)" class="export_btn">

          </a> -->

          <span class="vertical-icons" title="Bulk Import" data-toggle="modal" data-target="#import-modal">
            <img src="{{asset('public/icons/bulk_import.png')}}" width="27px">
          </span>

          <span class="vertical-icons quotation_copy_btn" title="Copy & Update">
            <img src="{{asset('public/icons/copy.png')}}" width="27px">
          </span>

                  <!-- <a type="submit" class="btn purch-add-btn ml-auto mr-1 quotation_copy_btn" style="font-size: 10px;">Copy & Update</a> -->


<!--           <a href="javascript:void(0)" data-id="{{$order->id}}" class="download-documents">
            <button type="button" class="btn text-uppercase purch-btn headings-color btn-color" data-toggle="modal" data-target="#uploadDocument">
              upload document<i class="pl-1 fa fa-arrow-up"></i>
            </button>
          </a>
 -->
          <span class="vertical-icons download-documents" data-id="{{$order->id}}" title="Upload Document" data-toggle="modal" data-target="#uploadDocument">
            <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
          </span>
        </div>
      </div>
    </div>
  </div>
  <!-- New Design Ends -->
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
    <a class="exp_download" href="{{ url('get-download-xslx','Order Export'.$id.'.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
    </b>
    </div>
    <!-- ends here -->
    <div class="entriesbg bg-white custompadding customborder table-responsive">
      <table class="table entriestable table-bordered table-quotation-product text-center" style="table-layout: fixed !important">
        <thead>
          <tr>
            <th @if(in_array(0,$hidden_columns_by_admin)) class="noVis" @endif>Action
            </th>
            <th @if(in_array(1,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['our_reference_number']}}
            <!-- <input type="hidden" name="column_name" id="column_name" value="reference_code"> -->
              <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="2" data-column_name="reference_code">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="2" data-column_name="reference_code">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(2,$hidden_columns_by_admin)) class="noVis" @endif>HS Code
            </th>
            <th @if(in_array(3,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['product_description']}}
            <!-- <input type="hidden" name="column_name" id="column_name" value="short_desc"> -->
              <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="3" data-column_name="short_desc">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="3" data-column_name="short_desc">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(4,$hidden_columns_by_admin)) class="noVis" @endif>Note
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="7">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="7">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(5,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['category']}} / {{$global_terminologies['subcategory']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="6">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="6">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(6,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['type']}}
            <!-- <input type="hidden" name="column_name" id="column_name" value="type_id"> -->
            <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="4" data-column_name="type_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc"  data-index="4" data-column_name="type_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(7,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['brand']}}
            <!-- <input type="hidden" name="column_name" id="column_name" value="brand">  -->
            <span class="arrow_up sorting_filter_table" data-ord="asc"  data-index="5" data-column_name="brand">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="5" data-column_name="brand">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(8,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['temprature_c']}}</th>
            <th @if(in_array(9,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['supply_from']}}
            <!-- <input type="hidden" name="column_name" id="column_name" value="supply_from">  -->
            <span class="arrow_up sorting_filter_table" data-ord="asc" data-index="1" data-column_name="supply_from">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-index="1" data-column_name="supply_from">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th @if(in_array(10,$hidden_columns_by_admin)) class="noVis" @endif>Available <br>{{$global_terminologies['qty']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="7">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="7">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(11,$hidden_columns_by_admin)) class="noVis" @endif>PO QTY
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(12,$hidden_columns_by_admin)) class="noVis" @endif>PO No
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="12">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="12">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <!-- <th @if(in_array(0,$hidden_columns_by_admin)) class="noVis" @endif>Total Amount </th> -->
            <th @if(in_array(13,$hidden_columns_by_admin)) class="noVis" @endif>Customer <br> Last <br> Price
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="9">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="9">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(14,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['qty']}}<br>Ordered
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="10">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="10">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(15,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['qty']}} Sent
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="15">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="15">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(16,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['pieces']}}<br>Ordered
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="11">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="11">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(17,$hidden_columns_by_admin)) class="noVis" @endif># {{$global_terminologies['pieces']}} Sent
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="17">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="17">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(18,$hidden_columns_by_admin)) class="noVis" @endif>*{{$global_terminologies['reference_price']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="12">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="12">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(19,$hidden_columns_by_admin)) class="noVis" @endif>*{{$global_terminologies['default_price_type']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="13">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="13">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(20,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['default_price_type_wo_vat']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="14">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="14">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(21,$hidden_columns_by_admin)) class="noVis" @endif>Price Date
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="15">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="15">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(22,$hidden_columns_by_admin)) class="noVis" @endif>Discount
             {{-- <span class="arrow_up sorting_filter_table" data-order="asc" data-column_name="2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="desc" data-column_name="2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>  --}}
            </th>
            <th @if(in_array(23,$hidden_columns_by_admin)) class="noVis" @endif>Unit Price <br>(After Discount)
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="17">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="17">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(24,$hidden_columns_by_admin)) class="noVis" @endif>VAT
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="18">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="18">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(25,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['unit_price_vat']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="19">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="19">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>  -->
            </th>

            <th @if(in_array(26,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['total_amount_inc_vat']}}
            <!--<span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="20">
                 <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="20">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>

            <th @if(in_array(27,$hidden_columns_by_admin)) class="noVis" @endif>Restaurant Price
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="21">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="21">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>
            <th @if(in_array(28,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['note_two']}}
            <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="22">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="22">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
            </th>

             <th @if(in_array(29,$hidden_columns_by_admin)) class="noVis" @endif>{{$global_terminologies['total_price_after_discount_without_vat']}}
             <!-- <span class="arrow_up sorting_filter_table" data-ord="asc" data-column_name="23">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-ord="desc" data-column_name="23">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span> -->
             </th>
          </tr>
        </thead>
      </table>

        <!-- New Design Starts Here  -->
      <div class="row ml-0 mb-4">
        <div class="col-lg-9 col-md-12 pad col-12 col-sm-6">
           <div class="col-6 pad mt-2 mb-3">
              <div class="purch-border input-group custom-input-group">
                <input type="text" name="refrence_code" placeholder="Type Reference number..." data-id = "{{$order->id}}" class="form-control refrence_number pl-1 iphone__input">
              </div>
            </div>
            <!-- buttons -->
            <div class="col-12 pad mt-4 mb-4">
              <a class="btn purch-add-btn mt-3 fontmed btn-sale iphone__print__btn" id="addProduct">Add Product </a>
              <!-- <a class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" id="uploadExcelbtn">Upload Excel</a> -->
              <a class="btn purch-add-btn mt-3 fontmed btn-sale iphone__print__btn" id="addInquiryProductbtn" type="submit">Add New Item</a>

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
                        @else
                            <p class="mb-2"><input type="hidden" value="2" name=""><i class="fa fa-edit edit-address" data-id="{{$order->customer_id}}"></i> {{ @$order->customer->address_line_1.' '.@$order->customer->address_line_2.', '.@$order->customer->getcountry->name .', '.@$order->customer->getstate->name.', '.@$order->customer->city.', '.@$order->customer->postalcode }}</p>
                            <ul class="d-flex list-unstyled">
                                <li><a href="#"><i class="fa fa-phone pr-2"></i> {{$order->customer->phone}}</a></li>
                                <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> {{$order->customer->email}}</a></li>
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 mt-4">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <p><strong>{{$global_terminologies['comment_to_customer'] }}:</strong>
                              <span class="inv-note inputDoubleClick remarks_field" data-fieldvalue="{{$inv_note !== null ? $inv_note->note : ''}}"style="font-weight: normal;"><br>
                                {{ $inv_note !== null ? ($inv_note->note !== null ? $inv_note->note : 'Add a Remark') : 'Add a Remark' }}
                              </span>
                              <textarea autocomplete="off" name="comment" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" maxlength="500">{{ $inv_note !== null ? $inv_note->note : '' }}</textarea>
                            </p>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 pr-4">
                            <p><strong>Comment To Warehouse: </strong>
                              <span class="inv-note inputDoubleClick comment_field" data-fieldvalue="{{$warehouse_note !== null ? $warehouse_note->note : ''}}" style="font-weight: normal;"><br>
                              {{ $warehouse_note !== null ? ($warehouse_note->note !== null ? $warehouse_note->note : 'Add a Comment') : 'Add a Comment' }}
                              </span>
                            <textarea autocomplete="off" name="comment_warehouse" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a Comment (500 Characters)" maxlength="500">{{ $warehouse_note !== null ? $warehouse_note->note : '' }}</textarea>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="col-lg-3 col-md-5 pt-4 mt-4 col-12 col-sm-5">
          <div class="side-table" id="side-table">
            <table class="headings-color purch-last-left-table side-table">
              <tbody>
                <tr>
                <td class="fontbold" width="70%">Sub Total w/o Discount:</td>
                <td class="text-start sub_total_without_discount fontbold">&nbsp;&nbsp;{{number_format(floor($sub_total_without_discount*100)/100,2,'.',',')}}</td>
              </tr>
              <tr>
                <td class="text-nowrap fontbold">Discount:</td>
                <td class="fontbold text-start item_level_dicount">
                  &nbsp;&nbsp;{{ number_format(floor($item_level_dicount*100)/100, 2, '.', ',') }}
              </tr>
                <tr>
                  <td class="text-left fontbold">Sub Total:</td>
                  <td class="sub-total text-start fontbold">&nbsp;&nbsp;{{number_format($sub_total, 2, '.', ',') }}</td>
                </tr>
                <!-- <tr>
                  <td class="text-nowrap fontbold">Discount:</td>
                  <td class="fontbold text-start">&nbsp;&nbsp;
                    <span class="inv-discount mr-2 inputDoubleClick">{{ $order->discount == '' ? 0 : number_format(floor($order->discount*100)/100, 2, '.', ',') }}</span>
                    <input type="number" data-id="{{ $id }}" class="form-control mr-2 d-none fieldFocus" name="discount" value="{{$order->discount}}">
                  </td>
                </tr> -->
             <!--    <tr>
                  <td class="text-nowrap fontbold">Shipping:</td>
                  <td class="fontbold text-start">&nbsp;&nbsp;
                    <span class="inv-shipping mr-2 inputDoubleClick">{{ $order->shipping == '' ? 0 :number_format(floor($order->shipping*100)/100, 2, '.', ',') }}</span>
                    <input type="number" data-id="{{ $id }}" class="form-control mr-2 d-none fieldFocus" name="shipping" value="{{$order->shipping}}">
                  </td>
                </tr> -->
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
        </div>
        <div class="col-12">
          <div class="row">
            <div class="text-center mt-4 col-lg-12 col-md-12">
             @if($order->primary_status == 1 && $order->status == 6)
             <div class="desktop__confirm_btn">
              <div class="">
                <button type="button" class="btn btn-sm pl-3 pr-3 btn-danger btn_discard_close iphone__input discard__btn" style="">Discard and Close</button>&nbsp;
              </div>
              <div class="">

               <a href="{{url('sales')}}" class="btn purch-add-btn ml-auto mr-1 save-close-btn iphone__input" style="font-size: 10px;">Save & Close</a>
               </div>

               <div class="">
                 <a type="submit" data-id="{{ $order->id }}" class="btn purch-add-btn ml-auto mr-1 invoice-btn iphone__input" style="font-size: 10px;">Confirm Quotation</a>
               </div>

                  <!-- <div class="col-lg-4 col-md-4 col-sm-4 p-0">
                  <a type="submit" class="btn purch-add-btn ml-auto mr-1 quotation_copy_btn" style="font-size: 10px;">Copy & Update</a>
                  </div> -->
             </div>


              @endif
            </div>
          </div>
          </div>
      </div>
      <div class="row">
              <div class="col-lg-7 col-md-5">
                <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">

                 <table class="table-order-history entriestable table table-bordered text-center" style="font-size: 12px;">
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

              <div class="col-lg-5 col-md-7">
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
                        <td>{{Carbon::parse(@$history->created_at)->format('d/m/Y H:i:s')}}</td>
                        <td>{{$history->status}}</td>
                        <td>{{$history->new_status}}</td>
                      </tr>
                      @endforeach
                   </tbody>
                  </table>
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
          <input type="text" name="prod_name" value="" id="prod_name" class="form-control form-group" autocomplete="off" placeholder="Search by Product Reference #-Default Supplier- Product Description-Brand  (Press Enter)" style="padding-left:30px;">
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
        <input type="hidden" name="completed_quot_id" class="note-completed-quotation-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger close-btn" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
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
            <input type="hidden" name="order_id" value="{{$order->id}}">
            <input type="hidden" name="customer_id" id="customer_id" value="{{$order->customer_id}}">
            <input type="file" class="form-control" name="product_excel" id="product_excel" accept=".xls,.xlsx" required="" style="white-space: pre-wrap;"><br>
            <button class="btn btn-info product-upload-btn" type="submit">Upload</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
<form id="export_complete_quotations_form">
	@csrf
  <input type="hidden" name="id" id="export_quotation_id" value="{{$id}}">
  <input type="hidden" name="table_hide_columns" value="{{$user_plus_admin_hidden_columns}}">
  <input type="hidden" name="default_sort" id="default_sort" value="id_sort">
  <input type="hidden" name="type" id="type" value="data">
  <input type="hidden" name="column_name" id="column_name" value="column_name">

</form>
<input type="hidden" name="order_from_warehouse_id" id="order_from_warehouse_id" value="{{$order->from_warehouse_id}}">
@endsection
@php
$hidden_by_default = '';
@endphp
@section('javascript')

<!-- When Warehouse User is logged In He/She Can't Edit Product Detail -->
@if(Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6 )
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.inputDoubleClick').removeClass('inputDoubleClick');
      $('.selectDoubleClick').removeClass('selectDoubleClick');
      $('.prodSuppInputDoubleClick').removeClass('prodSuppInputDoubleClick');
      $('.inputDoubleClickFirst').removeClass('inputDoubleClickFirst');
      $('.inputDoubleClickFixedPrice').removeClass('inputDoubleClickFixedPrice');
      $('.selectDoubleClickPM').removeClass('selectDoubleClickPM');
      $('.inputDoubleClickPM').removeClass('inputDoubleClickPM');
      $('.inputDoubleClickContacts').removeClass('inputDoubleClickContacts');
      $('.market_price_check').attr('disabled',true);
      $('.pay-check').attr('disabled',true);
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

@if( Auth::user()->role_id == 7)
  <script type="text/javascript">
    $( document ).ready(function() {
      $('.edit-address').removeClass('d-none');
    });
  </script>
@endif

<script type="text/javascript">
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

//   $('#import-modal').on('hidden.bs.modal', function () {
//   $(this).find('form')[0].reset();
// });




  $(document).on('click','.quotation_copy_btn',function(e){
      e.preventDefault();
      var order_id = '{{$id}}';
      var _token = $('input[name="_token"]').val();

      swal({
      title: "Alert!",
      text: "Are you sure you want to create COPY of this Quotation ?",
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
          data:{_token:_token,order_id:order_id},
          url:"{{ route('copy-quotation') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            toastr.success('Success!', 'Quotation created successfully',{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
            window.location.href = "{{ url('sales/get-completed-quotation-products') }}"+"/"+data.order_id;
            }, 1000);
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
      // return;
    });
       });
    $(document).on('click','.btn_discard_close',function(e){
      e.preventDefault();
      var order_id = '{{$id}}';
      var _token = $('input[name="_token"]').val();

      swal({
      title: "Alert!",
      text: "Are you sure you want to discard this Quotation ?",
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
          data:{_token:_token,order_id:order_id},
          url:"{{ route('discard-quotation') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            // $("#loader_modal").modal('hide');
            toastr.success('Success!', 'Quotation discarded successfully !!!',{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
            window.location.href = "{{ url('sales') }}";
            }, 1000);
          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
      // return;
    });
       });

  $(".state-tags").select2();

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
      text: "Are you sure you want to change the VAT/NON-VAT of this Quotation ?",
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
          url:"{{ route('change-quotation-vat') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            $('.table-quotation-product').DataTable().ajax.reload();
            $('.table-order-history').DataTable().ajax.reload();
            toastr.success('Success!', 'Information Updated Successfully.' ,{"positionClass": "toast-bottom-right"});
            $('.sub-total').html(data.sub_total);
            $('.total-vat').html(data.total_vat);
            $('.grand-total').html(data.grand_total);
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

  $(document).on('click','.save-close-btn', function(){
    $('.save-close-btn').prop('disabled', 'true');
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
  });

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  $(function(e){

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

    $('.table-quotation-product').DataTable().order([index, default_sort]).draw();

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

  var table2 = $('.table-quotation-product').DataTable({
    // "bAutoWidth": true,
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    searching: false,
    ordering: true,
    colReorder: true,
    serverSide: false,
    bInfo: false,
    paging: false,
    // colReorder: {
    //   realtime: false,
    // },
    "columnDefs": [
      {	className: "dt-body-left",	"targets": [ 1,2,3,4,5,6,7,8,11,13]	},
			{	className: "dt-body-right",	"targets": [9,10,12] },
      {
      "targets": [{{ $user_plus_admin_hidden_columns != null ? $user_plus_admin_hidden_columns : $hidden_by_default }}],
        visible: false
      },
      { targets: [0,1,2,3,4,5,6,7,8,9,11,10,12,13,14,15,16,17,18,19,20], orderable: false }

		],
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
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
        url:"{{ url('sales/get-completed-quotation-products-to-list') }}"+"/"+{{ $order->id }},
        data: function(data) {
          // data.index = index,
          data.default_sort = default_sort
          },
      },
    columns: [
      { data: 'action', name: 'action',orderable:false },
      { data: 'refrence_code', name: 'refrence_code',orderable:false },
      { data: 'hs_code', name: 'hs_code',orderable:false },
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
    initComplete: function () {
      table2.columns.adjust().draw();
      if("{{Auth::user()->role_id}}" == 7)
      {
        $('.inputDoubleClick').removeClass('inputDoubleClick');
        $('.selectDoubleClick').removeClass('selectDoubleClick');
        $('.add-notes').addClass('d-none');

      }
      // Enable THEAD scroll bars
      $('.dataTables_scrollHead').css('overflow', 'scroll');

      // Sync THEAD scrolling with TBODY
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
        table2.colReorder.order([{{ @$display_prods->display_order != null ? @$display_prods->display_order : $columns_prefrences }}]);
        // table2.colReorder.disable();


    }
 });

  table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
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
      data : {type:'complete_quotation_product',column_id:col},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        var bodyWidth = $('.dataTables_scrollBody table').outerWidth();
          $('.dataTables_scrollHeadInner table').css('width', bodyWidth - 1);
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

  table2.on( 'column-reorder', function ( e, settings, details ) {
    var arr = "{{@$display_purchase_list->display_order}}";
    var all = arr.split(',');
    $.get({
    url : "{{ route('column-reorder') }}",
    dataType : "json",
    data : "type=complete_quotation_product&order="+table2.colReorder.order(),
    beforeSend: function(){

    },
    success: function(data){
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

  {{-- $(document).on("dblclick",".inputDoubleClick",function(){
    alert(123);
        $(this).addClass('d-none');
        $(this).next().removeClass('d-none');
        $(this).next().addClass('active');
        $(this).next().focus();
    }); --}}

  $(document).on("focusout",".fieldFocus",function() {
      // var fieldvalue ="--";
      var order_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      var fieldvalue = $(this).prev().data('fieldvalue');
      var old_value = $(this).prev().data('fieldvalue');
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
      else if(attr_name == 'comment')
      {
        // if($(this).val() == '')
        // {
        //   $(this).addClass('d-none');
        //   $(this).prev().removeClass('d-none');
        //   return false;
        // }
        // else
        // {
          $(this).prev().html($(this).val());
        // }
      }
      else if(attr_name == 'comment_warehouse')
      {
        // $(this).addClass('d-none');
        // $(this).prev().removeClass('d-none');
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
      url: "{{ route('save-order-data') }}",
      dataType: 'json',
      data: 'order_id='+order_id+'&'+attr_name+'='+encodeURIComponent($(this).val())+'&'+'old_value='+encodeURIComponent(old_value),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        $('.table-order-history').DataTable().ajax.reload();
        if(attr_name == 'delivery_request_date')
        {
          if(data.order.payment_due_date != null)
          {
            var p_due_date = $.datepicker.formatDate("d/m/yy", new Date(data.order.payment_due_date));
            $('.payment_due_date_term').html(p_due_date);
          }
        }
        if(attr_name == 'discount')
        {
          $('.inv-discount').html(data.discount);
          $('.grand-total').html(data.total);
        }
        else if(attr_name == 'shipping')
        {
          $('.inv-shipping').html(data.shipping);
          $('.grand-total').html(data.total);
        }
        else if(data.value == 'remmark')
        {
          $('.remarks_field').removeData('fieldvalue');
          $('.remarks_field').data('fieldvalue',new_value);
          if($.trim(new_value) === '' || $.trim(new_value) === null)
          {
            $('.remarks_field').html('<br>Add a Remark')
          }
        }
        else if(data.value == 'warehouse_comment')
        {
          $('.comment_field').removeData('fieldvalue');
          $('.comment_field').data('fieldvalue',new_value);
          if($.trim(new_value) === '' || $.trim(new_value) === null)
          {
            $('.comment_field').html('<br>Add a Comment')
          }
        }
        else if(attr_name == 'memo')
        {
          $('.ref_class').data('fieldvalue',new_value);
        }
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $(document).on('click', '.export_btn', function(e){
    $('#type').val('data');
    $('#export_complete_quotations_form').submit();
  });
  $(document).on('click', '#example_export_btn', function(e){
    $('#type').val('example');
    $('#export_complete_quotations_form').submit();
  });

  $(document).on('click', 'input[type=number]', function(e){
    $(this).removeClass('disabled');
    $(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on('keypress keyup focusout', 'input[type=number], input[type=text], input[type=tel]', function(e){
    // alert('hi');
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
        swal({ html:true, title:'Alert !!!', text:'<b>Default Price (w/o VAT) cannot be less then 0 !!!</b>'});
        return false;
    }
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
      // $(this).addClass('disabled');
      $(this).addClass('d-none');
      $(this).removeClass('active');
      // $(this).attr('readonly','true');
       $(this).prev().data('fieldvalue',$(this).val());
      if($(this).val() !== null)
      {
        var product_row = $(this).parent().parent().attr('id');
        var attr_name = $(this).attr('name');
        var new_value = $(this).val();
        var old_value = $(this).prev().html();
         $(this).prev().html($(this).val());
        $(this).prev().removeClass('d-none');
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

$(document).on('keyup focusout','.product_type',function(e) {
    if(e.keyCode == 27 || e.which == 0){
      $(this).addClass('d-none');
      $(this).removeClass('active');
      $(this).prev().removeClass('d-none');
    }
  });

  $(document).on('change','.product_type',function(e) {
      var old_value = $(this).prev().html();
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
        $('#pieces'+product_row).prop('checked',false);
        $('#pieces'+product_row).attr('disabled',false);
        $(this).attr('disabled',true);
        UpdateOrderQuotationData(product_row, 'quantity', new_value,'clicked');
      }
      else
      {
        $('#is_retail'+product_row).prop('checked',false);
        $('#is_retail'+product_row).attr('disabled',false);
        $(this).attr('disabled',true);
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
          $('#loader_modal').modal('hide');
          $('input[type=number]').prop('disabled',true);
        },
        success: function(data)
        {
          $('input[type=number]').prop('disabled',false);
          // table2.columns.adjust();
          $('#loader_modal').modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
            // $('.table-quotation-product').DataTable().ajax.reload();
            // $('.table-order-history').DataTable().ajax.reload(null,false);
            $('.sub-total').html(data.sub_total);
            $('.total-vat').html(data.total_vat);
            $('.grand-total').html(data.grand_total);
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
            $('#input_quantity_'+data.id).val(data.quantity);
            $('#is_retail'+data.id).attr('data-id',data.id+' '+data.quantity);
            $('#is_retail'+data.id).data('id',data.id+' '+data.quantity);

            $('.quantity_span_'+data.id).css("color",'black');
            $('.description_'+data.id).css("color",'black');

            $('.pcs_span_'+data.id).html(data.pcs);
            $('.pcs_span_'+data.id).attr('data-fieldvalue',data.pcs);
            $('.pcs_span_'+data.id).data('fieldvalue',data.pcs);
            $('#input_number_of_pieces_'+data.id).val(data.pcs);
            $('#pieces'+data.id).attr('data-id',data.id+' '+data.pcs);
            $('#pieces'+data.id).data('id',data.id+' '+data.pcs);

            $('.product_type_'+data.id).html(data.type);
            $('.product_type_'+data.id).attr('data-fieldvalue',data.type_id);
            $('.product_type_'+data.id).data('fieldvalue',data.type_id);
          }

        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
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
          url: "{{ route('add-to-order-by-refrence-number') }}",
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
          if(result.already_invoice == true)
          {
            toastr.info('Sorry!!','Cannot Add Product Order Already Converted Into Invoice !!!',{"positionClass": "toast-bottom-right"});
            window.location.href = "{{ url('/sales/invoices')}}";
            return false;
          }
          if(result.success == true)
          {
            toastr.success('Success!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.total_products').html(result.total_products);
            $('.sub-total').html(result.sub_total);
            $('.total-vat').html(result.total_vat);
            $('.grand-total').html(result.grand_total);
            // $('.table-quotation-product').DataTable().ajax.reload();
            // console.log(result.getColumns.action);

            table2.row.add( {
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
            // table2.draw();
              table2.order([2, default_sort]).draw();
            // table2.columns.adjust().draw();

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
            $('#loader_modal').modal('hide');
          },
          success: function(result){
            $('#loader_modal').modal('hide');
            toastr.success('Success!', 'Inquiry Product added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-inquiry-product-form')[0].reset();
            // $('.table-quotation-product').DataTable().ajax.reload();
            table2.row.add( {
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
            table2.draw();

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

    $(document).on('click','.close-modal, #uploadExcelbtn',function(){
        product_ids_array = [];
        suppliers = [];
        $("#tags_div").html("");
        $('#addProductModal').modal('hide');
    });

  $(document).on('click','#addProduct, #uploadExcelbtn',function(){
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
        },
        });
        $('#addProductModal').modal('show');
        $('#prod_name').focus();
      }
    else if($(this).attr("id") == 'uploadExcelbtn'){
      $('#uploadExcel').modal('show');
      }

   });

  $(document).on('submit', '.upload-excel-form-old', function(e){
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
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
        },
        success: function(result){
          $('#loader_modal').modal('hide');
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
          $('#loader_modal').modal('hide');
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
            // $('#loader_modal').modal({
            //   backdrop: 'static',
            //   keyboard: false
            // });
            // $('#loader_modal').modal('show');
          $('#product_name_div_complete').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
          },
          success:function(data){
            // $('#loader_modal').modal('hide');
            $('#product_name_div_complete').fadeIn();
            $('#product_name_div_complete').html(data);
          },
          error: function(request, status, error){
            // $("#loader_modal").modal('hide');
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

      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

      $.ajax({
              method:"post",
              data:'selected_products='+input+'&quotation_id='+inv_id+'&supplier_id='+supplier_id+'&customer_id='+customer_id+'&suppliers='+sup,
              url:"{{ route('add-prod-to-order-quotation') }}",
            //   beforeSend:function(){
            //     $('#loader_modal').modal({
            //       backdrop: 'static',
            //       keyboard: false
            //     });
            //     $('#loader_modal').modal('hide');
            //   },
              success:function(data){
                product_ids_array = [];
                suppliers = [];
                $("#tags_div").html("");
                $('#addProductModal').modal('hide');
                $('.table-quotation-product').DataTable().ajax.reload();
                if (data.success == false) {
                    toastr.error('Error!', data.msg ,{"positionClass": "toast-bottom-right"});
                    return;
                }
                if(data.already_invoice == true)
                {
                    toastr.info('Sorry!!','Cannot Add Product Order Already Converted Into Invoice !!!',{"positionClass": "toast-bottom-right"});
                    window.location.href = "{{ url('/sales/invoices')}}";
                    return false;
                }
                $('#loader_modal').modal('hide');
                // $('#addProductModal').modal('hide');
                // $('.table-quotation-product').DataTable().ajax.reload();
                $('.sub-total').html(data.sub_total);
                $('.total-vat').html(data.total_vat);
                $('.grand-total').html(data.grand_total);
                $('#sub_total').val(data.sub_total);
                $('.total_products').html(data.total_products);
                $('#prod_name').val('');
                $('#product_name_div_complete').empty();

                table2.row.add( {
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
            // table2.draw();
              table2.order([2, default_sort]).draw();
            // table2.order([0, 'desc']).draw();
            // table2.columns.adjust().draw();
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
           });
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
    $(this).blur();
});

$(document).keyup(function(e) {
    if (e.keyCode == 13 && !$('#prod_name').is(':focus') && $('.addProductModal').is(":visible") && product_ids_array.length != 0) {
        $(".add_product_to").trigger('click');
    }
})

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
        beforeSend:function(){
          $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
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
          $('#loader_modal').modal('hide');
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
    beforeSend:function(){
      $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    },
      success:function(data){
        $('#loader_modal').modal('hide');
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
                $("#loader_modal").modal('hide');
                  if(data.success == true){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    // location.reload();
                  $('.sub-total').html(data.sub_total);
                  $('.sub_total_without_discount').html(data.sub_total_without_discount);
                  $('.item_level_dicount').html(data.item_level_dicount);
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
    var check_inquiry = "{{@$check_inquiry}}";
    var inverror = false;
    // var ship_date = $(this).find('input[name=target_ship_date]');
    // var delivery_date = $(this).find('input[name=delivery_request_date]');
    var ship_date = $('#target_ship_date').val();
    var delivery_date = $('#delivery_request_date').val();
    var from_w_id = $('#order_from_warehouse_id').val();

    var order__id = "{{$order->id}}";

    @if($targetShipDate['target_ship_date_required']==1)
    if(ship_date == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Please Add a Target Ship Date!!!</b>'});
      inverror = true;
    }

    @endif
    if(delivery_date == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Please Add a Delivery Request Date!!!</b>'});
      inverror = true;
    }

    if(from_w_id == '')
    {
          swal({ html:true, title:'Alert !!!', text:'<b>Please Select Order Warehouse!!!</b>'});
          inverror = true;
    }

    if(inverror == true)
    {
      e.preventDefault();
      return false;
    }
    if(check_inquiry > 0){
      toastr.error('Error!', 'Unable to Convert Quotation to Draft Invoice! Contain Inquiry/Incomplete products. Contact Purchasing!!!' ,{"positionClass": "toast-bottom-right"});
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
          url: "{{ route('check-customer-credit-limit') }}",
          method: 'post',
          data: {customer_id:"{{$order->customer_id}}",id: "{{$order->id}}",type: "order"},
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
                      url: "{{ route('make-draft-invoice') }}",
                      method: 'post',
                      data: $('.invoice-submit-form').serialize(),
                      context: this,
                      beforeSend: function(){
                        $('.invoice-btn').prop('disabled',true);
                        $('#loader_modal').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
                        $("#loader_modal").modal('show');
                      },
                      success: function(result)
                      {
                        $("#loader_modal").modal('hide');
                        if(result.already_invoice == true)
                        {
                          toastr.info('Sorry!!','Order Already Converted Into Invoice !!!',{"positionClass": "toast-bottom-right"});
                          window.location.href = "{{ url('/sales/invoices')}}";
                        }
                        if(result.already_draft_invoice == true)
                        {
                          toastr.info('Sorry!!','Order Already Converted Into Draft Invoice !!!',{"positionClass": "toast-bottom-right"});
                          window.location.href = "{{ url('/sales/draft_invoices')}}";
                        }
                        if(result.success == true)
                        {
                          toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                          setTimeout(function(){
                            if(result.direct_invoice == 0){
                               window.location.href = "{{ url('/sales/get-completed-invoices-details')}}/"+order__id;
                            }else{
                                 window.location.href = "{{ url('/sales/get-completed-draft-invoices')}}/"+order__id;
                            }

                          }, 500);
                        }
                        else if(result.success == false)
                        {
                          $('.invoice-btn').prop('disabled',false);
                          toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                        }
                      },
                      error: function (request, status, error)
                      {
                        $('.invoice-btn').prop('disabled',false);
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
                  url: "{{ route('make-draft-invoice') }}",
                  method: 'post',
                  data: $('.invoice-submit-form').serialize(),
                  context: this,
                  beforeSend: function(){
                    $('.invoice-btn').prop('disabled',true);
                    $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                    $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
                    $("#loader_modal").modal('show');
                  },
                  success: function(result)
                  {
                    $("#loader_modal").modal('hide');
                    if(result.success == true)
                    {
                      toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                      setTimeout(function(){
                        if(result.direct_invoice == 0){
                           // window.location.href = "{{ url('/sales/invoices')}}";
                           window.location.href = "{{ url('/sales/get-completed-invoices-details')}}/"+order__id;
                        }else{
                             // window.location.href = "{{ url('/sales/draft_invoices')}}";
                             window.location.href = "{{ url('/sales/get-completed-draft-invoices')}}/"+order__id;

                        }

                      }, 500);
                    }
                    else if(result.success == false)
                    {
                      $('.invoice-btn').prop('disabled',false);
                      toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
                    }
                  },
                  error: function (request, status, error)
                  {
                    $('.invoice-btn').prop('disabled',false);
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
      $("#loader_modal").modal('show');
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('sales') }}";
       document.location.href = url;
     }
   }
</script>

<script type="text/javascript">

  function tableColumnWidth() {
    var bodyWidth = $('.dataTables_scrollBody table').outerWidth();
    $('.dataTables_scrollHeadInner table').css('width', bodyWidth - 1);
  }
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
    var page_type = 'quotations';
    var is_proforma = 'yes';
    var is_texica = "{{$is_texica}}";

    if(is_texica == 1)
    {
     var url = "{{url('sales/export-quot-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+show_discount+"/"+bank_id+"/"+with_vat;
    }
    else
    {
     var url = "{{url('sales/export-quot-to-pdf-exc-vat')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+is_proforma+"/"+bank_id;
    }
     window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
  });
  // export pdf code
  $(document).on('click', '.export-pdf-inc-vat', function(e){
    var quo_id = $('#quo_id_for_pdf').val();
        var sort = $('#default_sort').val();
        var column_name = $('#column_name').val();
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
    var page_type = 'quotations';
    var is_proforma = 'yes';
    var is_texica = "{{$is_texica}}";
    if(is_texica == 1)
    {
     var url = "{{url('sales/export-quot-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+show_discount+"/"+bank_id+"/"+with_vat;
    }
    else
    {
      var url = "{{url('sales/export-quot-to-pdf-inc-vat')}}"+"/"+orders+"/"+page_type+"/"+column_name+"/"+sort+"/"+is_proforma+"/"+bank_id;
    }
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
        $('.invoice-btn').prop('disabled',true);
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
        $('.save-doc-btn').html('Please wait...');
        $('.save-doc-btn').addClass('disabled');
        $('.save-doc-btn').attr('disabled', true);
      },
      success: function(result){
        $('#loader_modal').modal('hide');
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
        $("#loader_modal").modal('hide');
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
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $('#loader_modal').modal('show');
                },
                success:function(data){
                  $('#loader_modal').modal('hide');
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

  $(document).on('click', '.close-btn', function(e){
      $('.invalid-feedback').remove();
      $('textarea').css('border-color', 'black');
      $('.add-compl-quot-note-form').reset();
  });



  $('.add-compl-quot-note-form').on('submit', function(e){
    e.preventDefault();
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
        url: "{{ route('add-completed-quotation-prod-note') }}",
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
                    if ($('.invalid-feedback').val() == undefined) {
                      $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                      $('input[name="'+key+'"]').addClass('is-invalid');
                      $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                      $('textarea[name="'+key+'"]').addClass('is-invalid');
                      $('textarea[name="'+key+'"]').css('border-color', '#dc3545');

                    }
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
        // $("#loader_modal").modal('hide');
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
      beforeSend:function(){
        $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      },

      success: function(response){
        $('#loader_modal').modal('hide');
        $.ajax({
            type: "get",
            url: "{{ route('get-completed-quotation-prod-note') }}",
            data: 'compl_quot_id='+compl_quot_id,
            success: function(response){
              if(response.no_data == true){
            $('.table-quotation-product').DataTable().ajax.reload();
                $("#notes-modal").modal('hide');
              }
              $('.fetched-notes').html(response);
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
      url: "{{ route('update-completed-quot-prod-note') }}",
      data: {note_id: note_id, show_on_invoice: checkbox},
      beforeSend:function(){
        $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      },
      success: function(response){
        $('#loader_modal').modal('hide');
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
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
                $('.inquiry-body').empty();
                 $('.inquiry-body').append(data.suppliers);
                 $(".state-tags").select2({dropdownCssClass : 'bigdrop'});
                 $('.inquiry_modal').trigger('click');
              }else{
                 toastr.error('Error!', 'Something went wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
              }
            }
          });
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
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
            url:"{{ route('enquiry-item-as-new-product-op') }}",
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
                  $('.table-quotation-product').DataTable().ajax.reload();
                  $('#inquiryModal').modal('hide');
              }else{
                 toastr.error('Error!', 'Something went wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
              }
            }
          });
            }
            else{
                $("#loader_modal").modal('hide');
                swal("Cancelled", "", "error");
            }
       });
  });

  var order_id = "{{$id}}";

  $('.table-order-history').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    // "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '
    // },
    ordering: false,
    searching:false,
    "lengthChange": true,
    serverSide: true,
    "scrollX": true,
    "bPaginate": true,
    "bInfo":false,
    pageLength: {{25}},
    lengthMenu: [ 25, 50, 75, 100],
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

      $(document).on("click",".inputDoubleClick",function(){
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
        // $x.next().next('span').removeClass('d-none');
        // $x.next().next('span').addClass('active');

       }, 300);
  });
 $(document).on("dblclick",".inputDoubleClick",function(){
     //alert($(this).data('id'));
    var str = $(this).data('id');
    if(str !== undefined){

    var res = str.split(" ");
  }else{
    var res = null;
  }
   if(res !== null){

     $.ajax({
    type: "get",
    url: "{{ route('get-prod-dropdowns') }}",
    data: 'value='+res[1]+'&choice='+res[0],
    beforeSend:function(){
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
    },
    success: function(response){
      $("#loader_modal").modal('hide');
     if(response.field == 'type'){
        $('.type_select'+res[2]).append(response.html);

      }

      else if(response.field == 'unit'){
        $('.buying_select'+res[2]).append(response.html);
      }
      else if(response.field == 'selling_unit'){
        $('.selling_unit'+res[2]).append(response.html);
      }
       else if(response.field == 'category_id'){

        $('.categories_select'+res[2]).append(response.html);
      }
      // $('.fetched-images').html(response);
      // $('.product_type').empty();
      // $('.product_type').append(response.html);
        $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      var num = $(this).next().val();
      $(this).next().focus().val('').val(num);
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });


 }
   else{
    $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      var num = $(this).next().val();
      $(this).next().focus().val('').val(num);
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
    else if($(this).attr('name') == 'category_id')
    {
      var old_value = $('.inc-fil-cat').prev().data('fieldvalue');
      var thisPointer= $(this);
      var pId = $(this).parents('tr').attr('id');
      swal({
        title: "Are you sure?",
        text: "Are you sure you want to update the Category of this product?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, Update it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
        },
        function (isConfirm) {
          if(isConfirm)
          {
            var new_value = $("option:selected", thisPointer).html();
            thisPointer.addClass('d-none');
            thisPointer.prev().removeClass('d-none');
            thisPointer.prev().html(new_value);
            saveProdData(pId, thisPointer.attr('name'), thisPointer.val(), old_value);
          }
          else
          {
            $('.table-quotation-product').DataTable().ajax.reload();
          }
        }

      );

    }
    else if($(this).attr('name') == "from_warehouse_id")
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
        url: "{{ route('from-warehouse-id-save-in-my-quotation') }}",
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
            $('#order_from_warehouse_id').val(data.from_warehouse_id);
            $('.table-quotation-product').DataTable().ajax.reload();
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
  function saveProdData(order_product_id,field_name,field_value,old_value){
      console.log(field_name+' '+field_value+''+order_product_id+' '+old_value);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ route('order-product-edits') }}",
        dataType: 'json',
        data: 'order_product_id='+order_product_id+'&'+field_name+'='+field_value+'&'+'old_value'+'='+old_value,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          if(data.success==true)
          {
            $("#loader_modal").modal('hide');
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-quotation-product').DataTable().ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }
   $('.edit_customer').on("click",function(){
  $('.update_customer').removeClass('d-none');
  $('.customer-addresses').addClass('d-none');
});

   $(document).on('change keyup focusout','.add-cust',function(e){

     if(e.keyCode == 27 || e.which == 0){
      $('.update_customer').addClass('d-none');
      $('.customer-addresses').removeClass('d-none');
       return false;
      // $(this).prev().removeClass('d-none');
    }

    var order_id = '{{$order->id}}';
    var customer_id = $(this).val();
    var customerDropdown = $(this);
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
        if(data.success == true)
        {
          // customerDropdown.addClass('d-none');
          $('.update_customer').addClass('d-none');
          $("#loader_modal").modal('hide');
          $('.customer-div').html('');
          $('.ship_body').html('');
          // location.reload();
          $('.customer-div').html(data.html);
          $('.ship_body').html(data.shipping_html);

          $("#sales_person_select").children().remove("optgroup");
          $('#sales_person_select').append(data.sales_person_html);
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
    $(window).resize(function(){
      setTimeout(function(){
        tableColumnWidth();
    }, 300);
    });
    $(window).on('load', function() {
      setTimeout(function(){
        // alert('On Load Function Run');
        tableColumnWidth();
    }, 7000);
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
      url: "{{ route('upload-bulk-product-in-order-detail') }}",
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
        $('#upload-excel-form')[0].reset();
        $('#loader_modal').modal('hide');
        $('#import-modal').modal('hide');
        if(data.paid == true)
        {
          toastr.warning('Warning!', 'Invoice is already paid!!!', {"positionClass": "toast-bottom-right"});
          return false;
        }
        if (data.success)
        {
          toastr.success('Success!', 'Information Updated Successfully.' ,{"positionClass": "toast-bottom-right"});
        }
        $(".product-upload-btn").attr("disabled", false);
        $('.table-quotation-product').DataTable().ajax.reload();
        $('.table-order-history').DataTable().ajax.reload();
        $('.sub-total').html(data.sub_total);
        $('.total-vat').html(data.total_vat);
        $('.grand-total').html(data.grand_total);
        $('.sub_total_without_discount').html(data.sub_total_without_discount);
        $('.item_level_dicount').html(data.item_level_dicount);
        if(data.errors != null)
        {

          toastr.warning('Warning!', data.errors, {"positionClass": "toast-bottom-right"});
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

    var type = 'qoutation';
    var id = '{{$id}}';
    var config = {
        routes: {
            zone: "{{ route('save-sale-person') }}"
        }
    };

    $(document).on('submit', '#export_complete_quotations_form', function (e) {
        e.preventDefault();
        $.ajax({
            method: "post",
            url: "{{route('export-complete-quotation')}}",
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

