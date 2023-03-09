@extends('users.layouts.layout')

@section('title','Transfer Document | Purchasing')

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

.aStock{
    width: 10%
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
}
.inputDoubleClickQuantity{
  font-style: italic;
  font-weight: bold;
}
.supplier_invoice_number_table thead tr th
{
  border: 1px solid #eee;
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
        @e<li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 10)
          <li class="breadcrumb-item"><a href="{{route('roles-list')}}">Home</a></li>
        @endif
          <li class="breadcrumb-item"><a href="{{route('transfer-document-dashboard')}}">Transfer Document</a></li>
          <li class="breadcrumb-item active">Transfer Document Detail</li>
      </ol>
  </div>
</div>

@php
use Carbon\Carbon;
@endphp

{{-- Content Start from here --}}

<!-- NEW Design -->

<!-- Right Content Start Here -->
<div class="right-content pt-1">

  <div class="row mb-0 headings-color">
    <div class="col-lg-8 col-md-6">
      <h4 class="mb-1 text-uppercase fontbold">{{$global_terminologies['transfer_document']}}</h4>
    </div>

    <div class="col-lg-4 col-md-6">
      <div class="row mx-0 align-items-center">
        <h4 class="maintitle text-uppercase fontbold col-8 px-0">{{$global_terminologies['supply_from']}}</h4>
        <a class="col-2 px-0 text-right mr-2 d-none" onclick="backFunctionality()">
          <!-- <button type="button" class="btn-color btn text-uppercase purch-btn headings-color">back</button> -->
          <span class="vertical-icons export_btn" title="Back">
          <img src="{{asset('public/icons/back.png')}}" width="27px">
          </span>
        </a>
        <span class="vertical-icons export_btn" title="Export" style="padding: 9px;">
          <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
      </div>
      {{-- <h4 class="mb-1 text-uppercase fontbold">{{$global_terminologies['supply_from']}} </h4>
      <a onclick="backFunctionality()">
        <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button>
      </a> --}}
    </div>
  </div>


  <div class="row">

  <input type="hidden" name="po_id" id="po_id" value="{{$id}}">

  <div class="col-lg-8 col-md-6">
    <p class="mb-1">TD # {{ $getPurchaseOrder->ref_id}}</p>
    <p class="mb-1">{{@$company_info->billing_address}},{{@$company_info->country->name}},{{@$company_info->state->name}},{{@$company_info->billing_zip}}</p>
    <p class="mb-1"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}  <em class="fa fa-envelope"></em>  {{@$company_info->billing_email}}</p>
    <p class="mb-1">Status : {{@$getPurchaseOrder->p_o_statuses->title}}</p>
    <p class="mb-1">TD Date: {{Carbon::parse(@$getPurchaseOrder->created_at)->format('d/m/Y')}}</p>
  </div>

  <div class="col-lg-4 col-md-6">
  <div class="d-flex align-items-center mb-1">
  <div>
    @if(@$getPurchaseOrder->PoWarehouse->user_details->image != null && file_exists( public_path() . '/uploads/sales/customer/logos/'.@$getPurchaseOrder->PoWarehouse->user_details->image))
      <img src="{{asset('public/uploads/sales/customer/logos/'.@$getPurchaseOrder->PoWarehouse->user_details->image)}}" class="img-fluid" style="width: 85px;height: 75px;" align="big-qummy">
    @else
      <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 85px;height: 75px;" align="big-qummy">
    @endif
  </div>
  <div class="pl-2 comp-name" id="from_warehouse" data-supplier-id="{{@$getPurchaseOrder->from_warehouse_id}}"><p>{{ @$getPurchaseOrder->PoWarehouse->warehouse_title }}</p> </div>
  </div>
  <p class="mb-1">@if(@$getPurchaseOrder->PoWarehouse->getCompany !== null) {{ @$getPurchaseOrder->PoWarehouse->getCompany->billing_address  }} @endif</p>
  <ul class="d-flex list-unstyled custom-contact-info">
    <li><i class="fa fa-phone pr-lg-2"></i>
      {{ @$getPurchaseOrder->PoWarehouse->getCompany->billing_phone }}
    </li>
    <li class="pl-lg-3"><i class="fa fa-envelope pr-2"></i>
    {{ @$getPurchaseOrder->PoWarehouse->getCompany->billing_email }}
    </li>
  </ul>
@if(Auth::user()->role_id == 6)
    @php
      $classPref = '';
    @endphp
  @else
    @php
      $classPref = 'inputDoubleClick';
    @endphp
  @endif
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class="fontbold" style="width: 180px;">Supply To Warehouse:</li>
    <span class="pl-4">
     {{$getPurchaseOrder->to_warehouse_id != null ? @$getPurchaseOrder->ToWarehouse->warehouse_title : "N.A"}}
    </span>
  </ul>

  @php
    $transfer_date = $getPurchaseOrder->transfer_date != null ? Carbon::parse($getPurchaseOrder->transfer_date)->format('d/m/Y') : "" ;
  @endphp
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 180px;">Transfer Date:<b style="color: red;">*</b></li>
    <span class="pl-4 inputDoubleClick" data-fieldvalue="{{$transfer_date}}">
      @if($getPurchaseOrder->transfer_date != null)
      {{Carbon::parse($getPurchaseOrder->transfer_date)->format('d/m/Y')}}
      @else
      Transfer Date Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none transfer_date" name="transfer_date" id="transfer_date" value="{{$transfer_date}}">
  </ul>
  @php
  $target_receive_date_c = $getPurchaseOrder->target_receive_date != null ? Carbon::parse($getPurchaseOrder->target_receive_date)->format('d/m/Y') : "" ;
@endphp
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class="fontbold" style="width: 180px;">Target Received Date:<b style="color: red;">*</b></li>
    <span class="pl-4 inputDoubleClick">
      @if($getPurchaseOrder->target_receive_date != null)
      {{Carbon::parse($getPurchaseOrder->target_receive_date)->format('d/m/Y')}}
      @else
      Target Received Date Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none target_receive_date" name="target_receive_date" id="target_receive_date" value="{{$target_receive_date_c}}">
  </ul>
  <ul class="d-flex mb-0 pt-2 list-unstyled" style="display: none !important;">
    <li class="fontbold" style="width: 180px;">Payment Terms:</li>
    <span class="pl-4 inputDoubleClick">
      @if($getPurchaseOrder->payment_terms_id != null)
      {{$getPurchaseOrder->pOpaymentTerm->title}}
      @else
      Select Terms Here
      @endif
    </span>
    <select class="form-control payment_terms_id d-none mb-2 select-common" name="payment_terms_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Term</option>';
      @foreach ($paymentTerms as $pm)
        @if($getPurchaseOrder->payment_terms_id == $pm->id)
            <option selected value="{{$pm->id}}">{{$pm->title}}</option>
        @else
            <option value="{{$pm->id}}">{{$pm->title}}</option>
        @endif
      @endforeach
    </select>
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled" style="display: none !important;">
    <li class="fontbold" style="width: 180px;">@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif:</li>
    <span class="pl-4 payment_due_date_term">
      @if($getPurchaseOrder->payment_due_date != null)
      {{Carbon::parse($getPurchaseOrder->payment_due_date)->format('d/m/Y')}}
      @else
      Payment Due Date Here
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none payment_due_date fieldFocus" name="payment_due_date" id="payment_due_date" value="{{@$getPurchaseOrder->payment_due_date}}">
  </ul>

  <ul class="d-flex mb-2 pt-2 list-unstyled" style="display: none !important;">
    <li class="fontbold" style="width: 180px;">Memo:</li>
    <span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$getPurchaseOrder->memo}}">
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

  </form>
  <!-- export pdf form ends -->

 <div class="col-lg-12 text-uppercase fontbold mt-4">

  <a href="javascript:void(0);">
    <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color d-none">print</button>
  </a>

  <a href="javascript:void(0);" class="d-none">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf mr-3">print</button>
  </a>

  <a href="javascript:void(0)" class="d-none">
    <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color"><input type="checkbox" name="show_price" id="show_price" checked="true" style="vertical-align: inherit;scale"> &nbsp;Show Prices</button>
  </a>

  <div class="pull-right">
    <!-- <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color export_btn">export</button> -->
    @if($checkPoDocs > 0)
      @php $show = ""; @endphp
    @else
      @php $show = "d-none"; @endphp
    @endif


    <a href="{{ url('getting-docs-for-download/'.$id) }}" class="d-none">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
    </a>

    <a href="javascript:void(0);">
      <!-- <button type="button" data-toggle="modal" data-target="#addDocumentModal" class="btn-color btn text-uppercase purch-btn headings-color">upload documents<i class="pl-1 fa fa-upload"></i></button> -->
      <span class="vertical-icons" title="Upload Document" data-toggle="modal" data-target="#addDocumentModal">
        <img src="{{asset('public/icons/upload_icon.png')}}" width="27px">
      </span>
    </a>
    <a href="javascript:void(0)" data-id="{{$getPurchaseOrder->id}}" data-toggle="modal" data-target="#file-modal" class="download-documents"><button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">View documents<i class="pl-1 fa fa-download"></i></button></a>
  </div>

</div>

</div>

</div>

{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="addDocumentModal">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">ADD DOCUMENTS</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>


  <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">

    <div class="modal-body">

      <input type="hidden" name="purchase_order_id" value="{{$id}}">

      <div class="form-group">
        <label class="pull-left">Select Documents To Upload</label>
        <input class="font-weight-bold form-control-lg form-control" name="po_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary save-doc-btn" id="addDocBtn">Add</button>
    </div>
  </form>

    </div>
  </div>
</div>
{{-- end modal code--}}


 <div class="col-lg-12 mt-3">
  <div class="bg-white table-responsive custompadding pb-5">
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
        <a class="exp_download" href="{{ url('get-download-xslx','Waiting Conformation TD Export.xlsx') }}" target="_blank" id=""><u>Click Here</u></a>
        </b>
      </div>
  <table class="table entriestable warehouse-po-porducts-details text-center table-bordered" style="width:100%">
    <thead class="sales-coordinator-thead headings-color table-bordered">
      <tr>
        <th>Action</th>
        {{--<th>Supplier # </th>--}}
        <th>{{$global_terminologies['our_reference_number']}}
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
          <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
        </span>
        <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
          <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
        </span>
        </th>
        <th>Customer Reference <br> Name
          <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer">
            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
          </span>
          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer">
            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
          </span>
        </th>
        <th>{{$global_terminologies['brand']}}
          <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="brand">
            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
          </span>
          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="brand">
            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
          </span>
        </th>
        <th>{{$global_terminologies['product_description']}}
          <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="product_description">
            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
          </span>
          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="product_description">
            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
          </span> -->
        </th>
        <th>{{$global_terminologies['type']}}
          <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="type">
            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
          </span>
          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="type">
            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
          </span>
        </th>
        <th>Selling Unit
          <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="selling_unit">
            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
          </span>
          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="selling_unit">
            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
          </span>
        </th>
        <th>{{$global_terminologies['qty']}} Ordered
          <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="qty_inv">
            <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
          </span>
          <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="qty_inv">
            <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
          </span>
        </th>
        <th>{{$global_terminologies['qty']}} Sent</th>
        <th>{{$global_terminologies['qty']}} Received</th>
        {{--<th>Unit Price</th>
        <th>Discount</th>
        <th>Amount</th>--}}
        <th>Order #s</th>
        <th>Supplier Inv# </th>
        <th>Custom's Inv# </th>
        <th>Custom's Line# </th>
        {{--<th>Total <br> Gross <br> Weight</th>--}}
      </tr>
    </thead>
  </table>


  <div class="row ml-4">

    <div class="col-4 mb-4">
        @if ($getPurchaseOrder->status != 22)
      <div class="purch-border input-group custom-input-group">
        <input type="text" name="refrence_code" placeholder="Type Reference number..."
      data-po_id = "{{$id}}" class="form-control refrence_number" autocomplete="off">
      </div>
      @endif
    </div>

    <div class="col-4 mb-4">
    </div>

    <div class="col-lg-4 col-md-4 pt-4 mt-4">
      <table class="headings-color" style="margin-left: 50%; margin-top: 15px;">
        <tbody>

          <tr class="">
            <td class="text-nowrap fontbold">Total QTY:</td>
            <td class="fontbold total-qty">{{ $itemsCount}}</td>
          </tr>

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
    @if ($getPurchaseOrder->status != 22)
    <div class="col-2 mb-2">
      <button class="btn purch-add-btn mt-3 fontmed" type="submit" id="addProduct">Add Product</button>
    </div>
    @endif

    <div class="col-7 mb-7"></div>

    <div class="col-3 mb-2">
    @if($getPurchaseOrder->status == 20)
      <a href="javascript:void(0);">
        <button type="button" data-id={{$id}} class="btn-color btn purchasingSupplybtn mt-3 confirm-po-btn"><i class="fa fa-check"></i> Confirm Transfer</button>
      </a>
    @endif
    </div>
  </div>

  <div class="row ml-4">
    <div class="col-lg-6 col-md-12">
    <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
      <table class="table-purchase-order-history headings-color entriestable table table-bordered text-center">
        <thead class="sales-coordinator-thead ">
          <tr>
            <th>User</th>
            <th>Date/Time</th>
            <th>Order #</th>
            <th>Item #</th>
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

    <div class="col-lg-6 col-md-12">
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
  </div>

  <div class="row mt-3">
  <div class="col-lg-6 col-md-6 ml-5">
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


{{--  Add Product Modal Start Here --}}
<div class="modal addProductModal" id="addProductModal" style="margin-top: 150px;">
  <div class="modal-dialog modal-xl">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">Search Product</h4>
    <p style="color: red;" align="right" class="mr-2">(Note:* Enter atleast 3 characters then press Enter)</p>
    <button type="button" class="close close-modal" data-dismiss="modal">&times;</button>
  </div>

  <div class="modal-body">
    <div class="form-group" style="margin-top: 10px; margin-bottom: 50px; position:relative;">
      <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
      <input type="text" name="prod_name" id="prod_name"  class="form-control form-group mb-0" autocomplete="off" placeholder="Search by Product Reference #-Default Supplier- Product Description  (Press Enter)" style="padding-left:30px;">
    </div>
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
        <h4 class="modal-title">{{$global_terminologies['transfer_document']}} Files</h4>
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

{{-- Stock Qty Modal --}}
<div class="modal stock_Modal" id="stock_Modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title stock_Modal_title">Available Stock</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="fetched-stock-details">
            <div class="d-flex justify-content-center">
              <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
        </div>
      </div>
    </div>
  </div>
  {{-- end modal code--}}

<form id="export_waiting_con_td_form">
  @csrf
  <input type="hidden" name="id" value="{{$id}}">
  <input type="hidden" name="column_name" id="column_name">
  <input type="hidden" name="sort_order" id="sort_order">
</form>

@if($getPurchaseOrder->status == 22)
  @php
    $hide_columns = ', visible : true';
  @endphp
@else
  @php
    $hide_columns = ', visible : false';
  @endphp
@endif

@endsection

@section('javascript')

@if(Auth::user()->role_id == 7)
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

<script type="text/javascript">

  var show_custom_line_number_choice = "{{@$show_custom_line_number}}";
  var show_custom_invoice_number_choice = "{{@$allow_custom_invoice_number}}";
  var show_supplier_invoice_number_choice = "{{@$show_supplier_invoice_number}}";
  var show_custom_line_number = '';
  var show_custom_invoice_number = '';
  var show_supplier_invoice_number = '';
  if(show_custom_line_number_choice == 1)
  {
    show_custom_line_number = true;
  }
  else
  {
    show_custom_line_number = false;
  }
  if(show_custom_invoice_number_choice == 1)
  {
    show_custom_invoice_number = true;
  }
  else
  {
    show_custom_invoice_number = false;
  }
  if(show_supplier_invoice_number_choice == 1)
  {
    show_supplier_invoice_number = true;
  }
  else
  {
    show_supplier_invoice_number = false;
  }
  $("#transfer_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });
  $("#target_receive_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });

  $(document).on("change focusout","#transfer_date,#target_receive_date",function(e) {
    var po_id = "{{ $id }}";
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

    if(attr_name == 'transfer_date')
    {
      if($(this).val() == '')
      {
        $(this).prev().html("Transfer Date Here");
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

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
      type: "post",
      url: "{{ route('save-po-note') }}",
      dataType: 'json',
      data: 'po_id='+po_id+'&'+attr_name+'='+encodeURIComponent($(this).val()),
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
          if(attr_name == 'note')
          {
            $('.po-note').html(data.updateRow.note);
          }

          if(attr_name == 'invoice_date')
          {
            if(data.po.payment_due_date != null)
            {
              // $('.payment_due_date_term').html(data.po.payment_due_date);
              var newDate = $.datepicker.formatDate( "dd/mm/yy", new Date(data.po.payment_due_date));
              $('.payment_due_date_term').html(newDate);
            }
          }
        }
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
    });
  });

  $('#show_price').on('change',function(){
    var checked = $(this).prop('checked');
    if(checked == true){
      $('#show_price_input').val('1');
    }else if(checked == false){
      $('#show_price_input').val('0');
    }
  });

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
  });
  var po_id = $("#po_id").val();
  $(function(e){

// Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.warehouse-po-porducts-details').DataTable().ajax.reload();

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

var table2 = $('.warehouse-po-porducts-details').DataTable({
     processing: false,
    // "language": { processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    searching: false,
    ordering: false,
    serverSide: true,
    bInfo: false,
    paging: false,
    dom: 'lrtip',
    scrollX: true,
    colReorder: true,
    ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url:"{{ url('get-purchase-order-product-detail-td') }}"+"/"+po_id,
        data: function(data) {data.column_name = column_name,data.sort_order = order, data.is_transfer = true},
      },
    columns: [
        { data: 'action', name: 'action'},
        // { data: 'supplier_id', name: 'supplier_id' },
        { data: 'item_ref', name: 'item_ref' },
        { data: 'customer', name: 'customer' },
        { data: 'brand', name: 'brand' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'type', name: 'type' },
        { data: 'selling_unit', name: 'selling_unit' },
        { data: 'quantity', name: 'quantity' },
        { data: 'qty_sent', name: 'qty_sent' {{@$hide_columns}} },
        { data: 'quantity_received', name: 'quantity_received' {{@$hide_columns}} },
        // { data: 'unit_price', name: 'unit_price' },
        // { data: 'discount', name: 'discount' },
        // { data: 'amount', name: 'amount' },
        { data: 'order_no', name: 'order_no' },
        { data: 'supplier_invoice_number', name: 'supplier_invoice_number', visible: show_supplier_invoice_number },
        { data: 'custom_invoice_number', name: 'custom_invoice_number', visible: show_custom_invoice_number },
        { data: 'custom_line_number', name: 'custom_line_number', visible: show_custom_line_number },
        // { data: 'gross_weight', name: 'gross_weight' },

    ],
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
     initComplete: function () {
        // this.api().columns([1,2]).every(function () {
        //     var column = this;
        //     var input = document.createElement("input");
        //     $(input).addClass('form-control');
        //     $(input).attr('type', 'text');
        //     $(input).appendTo($(column.header()))
        //     .on('change', function () {
        //         column.search($(this).val()).draw();
        //     });
        // });

        table2.colReorder.order([{{ @$display_prods->display_order != null ? @$display_prods->display_order : null }}]);
    }
  });

table2.on( 'column-reorder', function ( e, settings, details ) {

    $.get({
    url : "{{ route('column-reorder') }}",
    dataType : "json",
    data : "type=td_detail&order="+table2.colReorder.order(),
    beforeSend: function(){
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
    });
    $('#loader_modal').modal('show');
    },
    success: function(data){
    $('#loader_modal').modal('hide');
    },
    error: function(request, status, error){
    $("#loader_modal").modal('hide');
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

  $('.export_btn').on('click',function(){
    $('#column_name').val(column_name);
    $('#sort_order').val(order);
     $("#export_waiting_con_td_form").submit();
      $('.warehouse-po-porducts-details').DataTable().ajax.reload();
  });

$(document).on('submit', '#export_waiting_con_td_form', function(e) {
    e.preventDefault();
    var url = "{{route('export-waiting-conf-td')}}";
    var data = $(this).serialize();
    export_excel(url, data);
})


  $(document).on('keyup', '.form-control', function(){
    $(this).removeClass('is-invalid');
    $(this).next().remove();
  });

  });

  // dropdown double click editable code start here
  $(document).on('change', 'select.select-common', function(){
  if($(this).attr('name') == "warehouse_id")
  {
    if($(this).val() !== '')
    {
      if($(this).val() > 0)
      {
        var po_id = "{{ $id }}";
        var attr_name = $(this).attr('name');
        var rowId = $(this).parents('tr').attr('id');
        var new_value = $("option:selected", this).html();
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });

        $.ajax({
          type: "post",
          url: "{{ route('save-po-product-warhouse') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val(),
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
                toastr.success('Success!', 'Warehouse Assigned Successfully.',{"positionClass": "toast-bottom-right"});
                /*$('.warehouse-po-porducts-details').DataTable().ajax.reload();*/
            }
          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
        });
      }
    }
  }

  if($(this).attr('name') == "payment_terms_id")
  {
    var target_receive_date = $(".target_receive_date").val();
    if(target_receive_date == '')
    {
      $('.payment_terms_id').val('');
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Target Ship Date First !!!</b>'});
      $('.inputDoubleClick').removeClass('d-none');
      $('.inputDoubleClick').next().addClass('d-none');
      return false;
    }
    else
    {

      var payment_terms_id = $(this).val();
      var po_id = '{{ $id }}';

      $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });

      $.ajax({
        method: "post",
        url: "{{ url('payment-term-save-in-po') }}",
        dataType: 'json',
        context: this,
        data: {payment_terms_id:payment_terms_id, po_id:po_id},
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
            $('.payment_due_date_term').html(data.payment_due_date);
            $(this).prev().html($("option:selected", this).html());
            $(this).removeClass('active');
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
      });

    }
  }
  });

  // double click editable
  $(document).on("click",".inputDoubleClickQuantity",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
    // var num = $(this).next().val();
    // $(this).next().focus().val('').val(num);
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

    if($(this).attr('name') == 'quantity')
    {
      if($(this).val() == null)
      {
        /*swal({ html:true, title:'Alert !!!', text:'<b>Quantity cannot be 0 or less then 0 !!!</b>'});*/
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
          url: "{{ route('save-po-product-quantity') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
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
                toastr.success('Success!', 'QTY Updated Successfully.',{"positionClass": "toast-bottom-right"});
                $('.warehouse-po-porducts-details').DataTable().ajax.reload();
                var total_qty = data.total_qty.toFixed(3);
                var sub_total_value = data.sub_total.toFixed(2);
                $('.total-qty').html(total_qty);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
            }
                  $('.table-purchase-order-history').DataTable().ajax.reload();

          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
        });
      }
    }


    //Discount
    if($(this).attr('name') == 'discount')
    {
      if($(this).val() !== '' && $(this).hasClass('active'))
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
          url: "{{ route('save-po-product-discount') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
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
                toastr.success('Success!', 'Discount Updated Successfully.',{"positionClass": "toast-bottom-right"});
                $('.warehouse-po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(2);
                $('.sub-total').html(sub_total_value);
                // $('#sub_total').val(sub_total_value);
            }
          },
          error: function(request, status, error){
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
          url: "{{ route('update-unit-price') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val()+'&'+'old_value='+old_value,
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
                toastr.success('Success!', 'Unit Price Updated Successfully.',{"positionClass": "toast-bottom-right"});
                $('.warehouse-po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(2);
                $('.sub-total').html(sub_total_value);
                $('#sub_total').val(sub_total_value);
            }
                  $('.table-purchase-order-history').DataTable().ajax.reload();

          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
          }
        });
      }
    }

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
      $(this).next().addClass('d-inline-block');
      // $(this).next().attr('min','0');
      // $(this).next().attr('max','99999');
    }
  });

  $(document).on("keyup focusout",".fieldFocus",function(e) {
      var po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');

      if (e.keyCode === 27 && $(this).hasClass('active') )
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }

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

    if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
      if(attr_name == 'note')
      {
        var fieldvalue = $(this).prev().data('fieldvalue');
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
      url: "{{ route('save-po-note') }}",
      dataType: 'json',
      data: 'po_id='+po_id+'&'+attr_name+'='+$(this).val(),
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
          if(attr_name == 'note')
          {
            $('.po-note').html(data.updateRow.note);
          }
        }
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
    });
  }
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
      url: "{{ route('add-purchase-order-document') }}",
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
          $('.addDocumentModal').modal('hide');
          $('.download-docs').removeClass('d-none');

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

  // delete product from purchasing detail page
  $(document).on('click', '.delete-product-from-list', function(e){

    var order_id = $(this).data('order_id');
    var order_product_id = $(this).data('order_product_id');
    var po_id = $(this).data('po_id');
    var id = $(this).data('id');
    var doc_for = "TD";

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      type: "get",
      url: "{{ route('check-po-product-numbers') }}",
      dataType: 'json',
      data:'po_id='+po_id+'&doc_for='+doc_for,
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
            swal({
              title: "Are you sure!!!",
              text: "You want to revert this item into Purchase list? "+data.msg,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Yes, remove it!",
              cancelButtonText: "Cancel",
              closeOnConfirm: true,
              closeOnCancel: false
              },
            function (isConfirm) {
              if (isConfirm) {
                $.ajax({
                  method:"get",
                  data:'order_id='+order_id+'&'+'order_product_id='+order_product_id+'&'+'po_id='+po_id+'&'+'id='+id,
                  url: "{{ route('delete-product-from-po') }}",
                  success: function(data){
                    if(data.success === true && data.redirect === 'no')
                    {
                      toastr.success('Success!', 'Product Removed Successfully.',{"positionClass": "toast-bottom-right"});
                      $('.warehouse-po-porducts-details').DataTable().ajax.reload();
                      var sub_total_value = data.sub_total.toFixed(2);
                      var total_qty = data.total_qty.toFixed(3);
                      $('.total-qty').html(total_qty);
                      $('.sub-total').html(sub_total_value);
                      $('#sub_total').val(sub_total_value);
                    }
                    else if(data.success === true && data.redirect === 'yes')
                    {
                      toastr.success('Success!', 'Product Removed Successfully.',{"positionClass": "toast-bottom-right"});
                      setTimeout(function(){
                      window.location.href = "{{ route('list-purchasing')}}";
                      }, 1000);
                    }
                  }
                });
              }
              else {
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

  // if order id not exist then this delete code will work
  $(document).on('click', '.delete-item-from-list', function(e){

    var po_id = $(this).data('po_id');
    var id = $(this).data('id');
    var doc_for = "TD";

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      type: "get",
      url: "{{ route('check-po-product-numbers') }}",
      dataType: 'json',
      data:'po_id='+po_id+'&doc_for='+doc_for,
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
          swal({
            title: "Are you sure!!!",
            text: "You want to delete this item from the list? "+data.msg,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, remove it!",
            cancelButtonText: "Cancel",
            closeOnConfirm: true,
            closeOnCancel: false
            },
          function (isConfirm) {
            if (isConfirm) {
              $.ajax({
                method:"get",
                data:'po_id='+po_id+'&'+'id='+id,
                url: "{{ route('delete-product-from-po-detail') }}",
                success: function(data){
                  if(data.success === true && data.redirect === 'no')
                  {
                    toastr.success('Success!', 'Product Removed Successfully.',{"positionClass": "toast-bottom-right"});
                    $('.warehouse-po-porducts-details').DataTable().ajax.reload();
                    var sub_total_value = data.sub_total.toFixed(2);
                    $('.sub-total').html(sub_total_value);
                    var total_qty = data.total_qty.toFixed(3);
                    $('.total-qty').html(total_qty);
                    $('#sub_total').val(sub_total_value);
                  }
                  else if(data.success === true && data.redirect === 'yes')
                  {
                    toastr.success('Success!', 'Product Removed Successfully.',{"positionClass": "toast-bottom-right"});
                    setTimeout(function(){
                    window.location.href = "{{ route('transfer-document-dashboard')}}";
                    }, 1000);
                  }
                }
              });
            }
            else {
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
  $(document).on('click', '.export-pdf', function(e){

    var po_id = $('#po_id_for_pdf').val();
    $('.export-po-form')[0].submit();

  });

  // confirm po button code here
  $(document).on('click','.confirm-po-btn', function(e){

    var id = $(this).data('id');   //purchase order id
    var target_receive_date = $(".target_receive_date").val();
    var transfer_date = $(".transfer_date").val();
    // var target_receive_date = $(".target_receive_date").val();
    // // var payment_due_date = $(".payment_due_date").val();
    // var payment_term = $('.payment_terms_id :selected').val();

    // if(payment_term == '')
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Must Select Payment Term!!!</b>'});
    //   return false;
    // }

    // if(payment_due_date == '')
    // {
    //   swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Payment Due Date!!!</b>'});
    //   return false;
    // }

    if(transfer_date == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Transfer Date!!!</b>'});
      return false;
    }

    if(target_receive_date == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Target Receive Date!!!</b>'});
      return false;
    }

    swal({
      title: "Are you sure!!!",
      text: "You want to confirm this Transfer Document?",
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
          url: "{{ route('confirm-transfer-document') }}",
          beforeSend: function(){
            $('.confirm-po-btn').prop('disabled',true);
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
          },
          success: function(response){
            if(response.success === true)
            {
              toastr.success('Success!', '{{$global_terminologies["transfer_document"]}} Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
              // setTimeout(function(){
              window.location.href = "{{ route('transfer-document-dashboard')}}";
              // }, 1000);
            }
            else if (response.success == false && response.status == "transfer-document-dashboard")
            {
              toastr.error('Error!', response.errorMsg, {
                "positionClass": "toast-bottom-right"
              });
              setTimeout(function() {
                window.location.href = "{{ route('transfer-document-dashboard')}}";
              }, 700);
            }
            else if(response.success == false)
            {
              $('.confirm-po-btn').prop('disabled',true);
              $('#loader_modal').modal('hide');
              toastr.error('Error!', response.errorMsg ,{"positionClass": "toast-bottom-right"});
              $('.confirm-po-btn').prop('disabled',true);
            }
            else if(response.res_error == true)
            {
              $('.confirm-po-btn').prop('disabled',false);
              $('#loader_modal').modal('hide');
              toastr.info('Sorry!', 'Qty ordered for item '+response.item+' does not match to reserved qty!!!' ,{"positionClass": "toast-bottom-right"});
            }
          },
          error: function(request, status, error){
            $('.confirm-po-btn').prop('disabled',true);
            $('#loader_modal').modal('hide');
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });

  });

  // adding product by searching
  $('#prod_name').keyup(function(e){
    var page = "Td";
    var query = $.trim($(this).val());
    var supplier_id = "{{$getPurchaseOrder->supplier_id}}";
    var warehouse_id = "{{$getPurchaseOrder->from_warehouse_id}}";
    var po_id = $("#po_id").val();
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
        // url:"{{ route('autocomplete-fetching-products-for-td') }}",
        method:"POST",
        data:{query:query, _token:_token, inv_id:po_id, supplier_id:supplier_id, warehouse_id:warehouse_id, page:page},
        beforeSend: function(){
          $('#product_name_div').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
        },
        success:function(data){
          $('#product_name_div').fadeIn();
          $('#product_name_div').html(data);
        },
        error: function(request, status, error){
          // $('#loader_modal').modal('hide');
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

  $(document).on('click', '.add_product_to', function(e){
  var po_id = $("#po_id").val();
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
      data:'selected_products='+input+'&po_id='+po_id,
      url:"{{ route('add-prod-to-po-detail') }}",
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
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('.table-purchase-order-history').DataTable().ajax.reload();
        product_ids_array = [];
        $("#tags_div").html("");
        if(data.success == false){
            $('#addProductModal').modal('hide');
            $('#loader_modal').modal('hide');
            toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
            $('#prod_name').text('');
            $('.warehouse-po-porducts-details').DataTable().ajax.reload();
          }
          else
          {
            $('#addProductModal').modal('hide');
            $('#loader_modal').modal('hide');
            $('#prod_name').text('');
            $('.warehouse-po-porducts-details').DataTable().ajax.reload();
            var sub_total_value = data.sub_total.toFixed(2);
            $('.sub-total').html(sub_total_value);
            $('#sub_total').val(sub_total_value);
            // $('.total_products').html(data.total_products);
      }
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
    });
  });

  $(document).on('click','.close-modal, #uploadExcelbtn',function(){
            product_ids_array = [];
            $("#tags_div").html("");
            $('#addProductModal').modal('hide');
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

    $(document).keyup(function(e) {
        if (e.keyCode == 13 && !$('#prod_name').is(':focus') && $('.addProductModal').is(":visible") && product_ids_array.length != 0) {
            $(".add_product_to").trigger('click');
        }
    })

    $(document).on('click','.remove-products',function(e){
        var prod_id = $(this).data('prod_id');

        for( var i = 0; i < product_ids_array.length; i++){
            if ( product_ids_array[i] === prod_id) {
                product_ids_array.splice(i, 1);
            }
        }

        document.getElementById(prod_id).remove();
    });

  $(document).on('click','#addProduct',function(){
    if($(this).attr("id") == 'addProduct')
    {
        $('#prod_name').val();
        $('#product_name_div').html('');
        $('#tags_div').html('');
        product_ids_array = [];
        $('#addProductModal').modal('show');
         $('#prod_name').focus();
    }
  });

  $(document).on('keyup','.refrence_number',function(e){
    if(e.keyCode == 13)
    {
      if($(this).val() !== '')
      {
      var refrence_number = $(this).val();
      var po_id = $(this).data(po_id);

      var formData = {"refrence_number":refrence_number,"po_id":po_id};
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
      $.ajax({
          url: "{{ route('add-prod-by-refrence-number-in-po-detail') }}",
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
          $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
          $('.table-purchase-order-history').DataTable().ajax.reload();
          if(result.success == true)
          {
            toastr.success('Success!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.warehouse-po-porducts-details').DataTable().ajax.reload();
            var sub_total_value = result.sub_total.toFixed(2);
            $('.sub-total').html(sub_total_value);
            $('#sub_total').val(sub_total_value);
            // $('.total_products').html(result.total_products);
          }
          else
          {
            toastr.error('Error!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');
            $('.warehouse-po-porducts-details').DataTable().ajax.reload();
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
      var url = "{{ url('transfer-document-dashboard') }}";
      document.location.href = url;
    }
  }
</script>
<script type="text/javascript">
  $(function(e){

  $(document).on('click', '.download-documents', function(e){
    let sid = $(this).data('id');
    console.log(sid);
    $.ajax({
      type: "post",
      url: "{{ route('get-purchase-order-files') }}",
      data: 'po_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      },
      error: function(request, status, error){
        // $('#loader_modal').modal('hide');
      }
    });
  });

  $(document).on('click', '.delete-purchase-order-file', function(e){
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
                url:"{{ route('remove-purchase-order-file') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $("#loader_modal").modal('show');
                },
                success:function(data){
                    $('#loader_modal').modal('hide');
                    if(data.search('done') !== -1){
                      myArray = new Array();
                      myArray = data.split('-SEPARATOR-');
                      let i_id = myArray[1];
                      $('#purchase-order-file-'+i_id).remove();
                      toastr.success('Success!', 'File deleted successfully.' ,{"positionClass": "toast-bottom-right"});
                    }
                },
                error: function(request, status, error){
                  $('#loader_modal').modal('hide');
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.custom_invoice_number',function(e) {
    // alert('hi');
    // alert('hi');
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
      }
    if(e.keyCode == 13 || e.which == 0){

      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();
      if(fieldvalue == new_value)
      {
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(fieldvalue);
      }
        return false;
      }
      else
      {
        var td_id= $(this).data('id');
        var thisPointer = $(this);
        savePoTdDetail(thisPointer,thisPointer.attr('name'), thisPointer.val(),td_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
    }
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.supplier_invoice_number',function(e) {
    // alert('hi');
    // alert('hi');
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
      }
    if(e.keyCode == 13 || e.which == 0){

      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();
      if(fieldvalue == new_value)
      {
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(fieldvalue);
      }
        return false;
      }
      else
      {
        var td_id= $(this).data('id');
        var thisPointer = $(this);
        savePoTdDetail(thisPointer,thisPointer.attr('name'), thisPointer.val(),td_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
    }
  });

  function saveTdDate(thisPointer,field_name,field_value,pod_id){
    var pod_id = pod_id;
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('/update-po-transfer-document') }}",
        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
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

  });

  var order_id = "{{$id}}";

  $('.table-purchase-order-history').DataTable({
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
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
      url:"{!! route('get-purchase-order-history') !!}",
      data: function(data) { data.order_id = order_id } ,
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
    ]
  });

  $('.table-purchase-order-status-history').DataTable({
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
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
      url:"{!! route('get-purchase-order-status-history') !!}",
      data: function(data) { data.order_id = order_id } ,
      },
    columns: [
      { data: 'user_name', name: 'user_name' },
      { data: 'created_at', name: 'created_at' },
      { data: 'status', name: 'status' },
      { data: 'new_status', name: 'new_status' },
    ]
  });

  $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc

      $("#transfer_date").datepicker('hide');
      $("#target_receive_date").datepicker('hide');
      if($('.inputDoubleClick').hasClass('d-none'))
      {
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.custom_line_number',function(e) {
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
      var fieldvalue = $(this).prev().data('fieldvalue');
      var new_value = $(this).val();
      if(fieldvalue == new_value)
      {
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(fieldvalue);
      }
        return false;
      }
      else
      {
        var td_id= $(this).data('id');
        var thisPointer = $(this);
        savePoTdDetail(thisPointer,thisPointer.attr('name'), thisPointer.val(),td_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
    }
  });

  $(document).on('click','.pay-check',function(){
    var thisPointer = $(this);
    var stock_id = thisPointer.data('stockid');
    var pod_id = thisPointer.data('id');
    var po_id = thisPointer.data('poid');
    // alert('stock id is '+stock_id+' pod id is '+pod_id);
    // return false;
    if($(this).prop("checked") == true)
    {
      var groupSelected = 1;
    }
    else if($(this).prop("checked") == false)
    {
      var groupSelected = 0;
    }
    saveDataForReservingQuantity(thisPointer,thisPointer.attr('name'), thisPointer.val(),groupSelected,po_id,pod_id,stock_id);
  });

  function saveDataForReservingQuantity(thisPointer,field_name,field_value,groupSelected,po_id,pod_id,stock_id){
    var pod_id = pod_id;
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('/update-po-transfer-document-detail-for-reserving') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
      data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&'+'groupSelected='+groupSelected+'&'+'po_id='+po_id+'&'+'pod_id='+pod_id+'&'+'stock_id='+stock_id,
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
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
          $('.warehouse-po-porducts-details').DataTable().ajax.reload();
          return true;
        }
        if(data.available_stock == false)
        {
          toastr.info('Sorry!', 'The available stock no longer exists !!!',{"positionClass": "toast-bottom-right"});
          thisPointer.prop('checked',false);
          $('.warehouse-po-porducts-details').DataTable().ajax.reload();
          return true;
        }

        if(data.success == false)
        {
          toastr.error('Sorry!', 'Something went wrong !!!',{"positionClass": "toast-bottom-right"});
          thisPointer.prop('checked',false);
          return true;
        }

        if(data.reserved_completed == true)
        {
          toastr.info('Sorry!', 'All Qty reserved cannot select anymore group !!!',{"positionClass": "toast-bottom-right"});
          thisPointer.prop('checked',false);
          return true;
        }

        if(data.cannot_select == true)
        {
          toastr.info('Sorry!', 'Cannot select this group because available stock is less than Qty Ordered !!!',{"positionClass": "toast-bottom-right"});
          thisPointer.prop('checked',false);
          return true;
        }

      },
      error: function (request, status, error) {
        $("#loader_modal").modal('hide');
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

  function savePoTdDetail(thisPointer,field_name,field_value,pod_id){
    var pod_id = pod_id;
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('/update-po-transfer-document-detail') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
      data: 'pod_id='+pod_id+'&'+field_name+'='+field_value,
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
          $('.warehouse-po-porducts-details').DataTable().ajax.reload();
          $('.table-purchase-order-history').DataTable().ajax.reload();
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
          return true;
        }
      },
      error: function (request, status, error) {
        $("#loader_modal").modal('hide');
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

  $(document).on('keyup', '.input_required_qty', function() {

        let total_qty = 0;
        $(".input_required_qty").each(function(){
            if ($(this).val() !== "") {
                total_qty += parseFloat($(this).val());
            }
        });
        $('#total_qty_span').html(total_qty);
    });


$(document).on('submit', '#save_qty_Form', function(e) {
    e.preventDefault();
    let po_id = '{{$id}}';
    let formData = new FormData(this);
    formData.append('po_id', po_id);
    formData.append('total_qty', $('#total_qty_span').text());
    formData.append('is_draft', false);
    $.ajax({
        type: "post",
        url: "{{ route('save-available-stock-of-product_in_td') }}",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").modal('show');
            $('#save_qty_in_reserved_table').prop('disabled', true);
        },
        success: function(response){
            $('#stock_Modal').modal('hide');
            $('.warehouse-po-porducts-details').DataTable().ajax.reload();

            $('.total-qty').html(response.total_qty);
        },
        error: function(request, status, error){
            // $('#loader_modal').modal('hide');
        }
    });
});

$(document).on('dblclick', '.td_qty_received', function() {
    $('.stock_Modal_title').text('QTY Received');
        $('#stock_Modal').modal('show');

        let pod_id = $(this).data('id');
        let product_id = $(this).data('product_id');
        let warehouse_id = $(this).data('warehouse_id');
        let po_id = $(this).data('po_id');
        let receiving_side = true;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            type: "post",
            url: "{{ route('get-reserved-stock-of-product') }}",
            data: {product_id:product_id, warehouse_id:warehouse_id, po_id:po_id, pod_id:pod_id,receiving_side:receiving_side, is_draft:false},
            beforeSend: function(){
                var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
                var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
                $('.fetched-stock-details').html(loader_html);
            },
            success: function(response){
                $('.fetched-stock-details').html(response.html);
                $('#total_qty_span').html(response.total_qty);

            },
            error: function(request, status, error){
                // $('#loader_modal').modal('hide');
            }
        });
    })

</script>
<script src="{{ asset('public\site\assets\backend\js\excel-export-ajax.js') }}"></script>
@endsection

