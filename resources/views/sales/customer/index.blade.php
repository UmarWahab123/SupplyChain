@extends('sales.layouts.layout')

@section('title','Customer Management | Supply Chain')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}
table.dataTable thead .sorting { background: url('../public/sort/sort_both.png') no-repeat center right;
  background-size: 5vh;}
table.dataTable thead .sorting_asc { background: url('../public/sort/sort_asc.png') no-repeat center right;
  background-size: 5vh; }
table.dataTable thead .sorting_desc { background: url('../public/sort/sort_desc.png') no-repeat center right;
  background-size: 5vh;}
  .view-sp-btn{
    margin-right: 10px;
  }
  .customer-secondary-user-table thead tr th{
    border: 1px solid #eee;
    text-align: center;
}

.customer-secondary-user-table tbody tr td{
    border: 1px solid #eee;
    text-align: center;
}
.secondary-user-delete:hover
{
    color: red;
    transition: 0.5s all;
    cursor: pointer;
}

.description_wrap{
    max-width: 300px;
    width: 300px;
    display: block;
    white-space: normal
}
</style>

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
          <li class="breadcrumb-item active">Customer List</li>
      </ol>
  </div>
</div>

  <!-- header starts -->
  <div class="row align-items-center left-right-padding mb-2 form-row">
    @if(Auth::user()->role_id == 9)
    <div class=" col-lg-3 col-md-4">
      <h4>E-Commerce {{$global_terminologies['customer_list']}}</h4>
    </div>
    @else
    <div class=" col-lg-3 col-md-4">
      <h3 class="custom-customer-list">@if(!array_key_exists('customer_list', $global_terminologies))Customers List @else {{$global_terminologies['customer_list']}} @endif</h3>
    </div>
    @endif

    <div class="col-lg-9 col-md-8">
      <div class="pull-right d-flex">
        @if(Auth::user()->role_id !== 7 && Auth::user()->role_id !== 9)
          <button class="btn recived-button text-nowrap mr-2" id="adding-customer">Add Customer</button>
        @endif
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 9 || Auth::user()->role_id == 11)
        <span class="export-btn common-icons" id="export_customers" title="Create New Export" style="padding: 4px 15px;">
              <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
        @endif
      </div>
    </div>
  </div>

  <div class="row d-flex align-items-center left-right-padding mb-2 form-row filters_div">
    <div class="col-md-3">
      <div class="selected-item catalogue-btn-group mb-2 d-none">
          <a href="javascript:void(0);" class="btn-color btn text-uppercase purch-btn headings-color assigned_to_sales custom-Assign-Customers" data-toggle="modal" data-target="#sales_modal" data-parcel="1" title="Assign Sale Person"><span>Assign Sale Person</span></a>

          <a href="javascript:void(0);" style="margin-top: 4px; height: 35px; width: 35px;" class="btn selected-item-btn btn-sm deleteBtnImg delete-btn" title="Delete Selected Customer(s)"><i class="fa fa-trash"></i></a>
      </div>
    </div>
{{-- Show Secondary Suppliers --}}
<div class="modal ShowSecondarySalesPerson" id="ShowSecondarySalesPerson">
    <div class="modal-dialog">
    <div class="modal-content">

    <div class="modal-header">
      <h4 class="modal-title">Secondary Sale Persons</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
      <div class="modal-body">
          <table class="table customer-secondary-user-table" width="100%" >
             <thead>
              <tr>
                  <th>Sr.No</th>
                  <th>Secondary Sales Person</th>
                  <th>Remove</th>
              </tr>
          </thead>
          <tbody id="secondarySalesPersonTable">

          </tbody>
          </table>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary save-fixed-price" data-dismiss="modal">Close</button>
      </div>


      </div>
    </div>
  </div>
    {{-- <div class="selected-item-old catalogue-btn-group-old mb-2 d-none">
        <a href="javascript:void(0);" class="btn-color btn text-uppercase purch-btn headings-color delete_selected_customers" title="Delete Customers"><span>Delete Selected Customers</span></a>
    </div> --}}

    <div class="col-md-1 col-lg-1 col-6">
      <select class="font-weight-bold form-control-lg form-control customers_status bx-shdw" name="customers_status" >
        <option value="" disabled="">Choose Status</option>
        <option value="1" selected="true">Completed</option>
         @if(session('msg'))
          <option value="0" >Incomplete</option>
          @else
          <option value="0">Incomplete</option>
          @endif

        <option value="2">Suspend</option>
      </select>
    </div>
    <div class="col-md-1 col-lg-1 col-6">
      <select class="font-weight-bold form-control-lg form-control sales_persons bx-shdw" name="sales_persons" >
        <option value="" selected="true">Choose Sale Person</option>
        @if(@$users)
            @foreach($users as $user)
            <option value="{{$user->id}}" {{ ($user->id == Auth::user()->id ? "selected" : "") }}>{{$user->name}}</option>
            @endforeach
            @endif
      </select>
    </div>
    @if(Auth::user()->role_id != 9)
     <div class="col-md-1 col-lg-1 col-6 mrgin-top">
      <select class="font-weight-bold form-control-lg form-control customers_type bx-shdw" name="customers_type" >
        <option value="" disabled="true" selected="true">Choose Sales Person Type</option>
        <option value="1">Primary Sales Person</option>
        <option value="0">Secondary Sales Person</option>
      </select>
    </div>
    @endif
    @if(Auth::user()->role_id == 9)
    <div class="col-md-1 col-lg-1 col-6 mrgin-top">
      <select class="font-weight-bold form-control-lg form-control selecting-customer-group bx-shdw" name="customer" required="true">
          <option value="" disabled="" selected="true">@if(!array_key_exists('customer_group', $global_terminologies))Customer Groups @else {{$global_terminologies['customer_group']}} @endif</option>
          @foreach($customer_categories as $cat)
          @if($cat->id == 4 || $cat->id == 6)
          <option value="{{$cat->id}}">{{@$cat->title}}</option>
          @endif
          @endforeach
      </select>
    </div>
    @else
        <div class="col-md-1 col-lg-1 col-6 mrgin-top">
      <select class="font-weight-bold form-control-lg form-control selecting-customer-group bx-shdw" name="customer" required="true">
          <option value="">@if(!array_key_exists('customer_group', $global_terminologies))Customer Group @else {{$global_terminologies['customer_group']}} @endif</option>
          @foreach($customer_categories as $cat)
          <option value="{{$cat->id}}">{{@$cat->title}}</option>
          @endforeach
      </select>
    </div>
    @endif
    {{-- <div class="col-md-10 col-10  offset-10 d-none block-display" ></div> --}}
    <div class="col-6 col-md-1 col-lg-1 mrgin-top ">
      <div class="pull-left">
      <!-- <button type="button" class="btn recived-button reset-btn">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button> -->
        <span class="common-icons reset-btn mr-4 bx-shdw" id="reset-btn" title="Reset" style="padding: 13px 15px;">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>

    </div>
    </div>
    <div class="col">
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 9 || Auth::user()->role_id == 11)
        <!-- <button class="btn recived-button text-nowrap export-btn" id="export_customers">Export</button> -->
      @endif
    </div>
  </div>

  {{--Error msgs div--}}
  <div class="row errormsgDiv mt-2" style="display: none;">
    <div class="container" style="max-width: 100% !important; min-width: 100% !important">
      <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
        <span id="errormsg"></span>
      </div>
    </div>
  </div>


  <!-- header ends -->
    @if(Auth::user()->role_id == 9)
    {{--<div class="selected-item catalogue-btn-group mt-4 mt-sm-3 d-none">
       <a href="javascript:void(0);" class="btn selected-item-btn btn-sm private-to-ecom" data-type="quotation" title="Access Customer Private to E-com" >
    <span><i class="fa fa-exchange" ></i></span></a>
   </div>--}}
  @endif
<div class="row entriestable-row">
  <div class="col-12">
    <div class="entriesbg bg-white mt-4">
         <div class="alert alert-primary export-alert d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is being prepared! Please wait.. </b>
      </div>
        <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>

          <b>Export file is ready to download.
          <!-- <a download href="{{ url('storage/app/customer-list-export.xlsx')}}"><u>Click Here</u></a> -->
          <a class="exp_download" href="{{ url('get-download-xslx','customer-list-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
          </b>
        </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>
      <div class="table-responsive">
        <table class="table entriestable table-bordered table-customers text-center" style="table-layout: fixed !important; white-space: word-wrap: break-word !important;">
          <thead>
            <tr>
              <th class="noVis" width="20px">
                <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
                </div>
             </th>
              <th width="20px">Action</th>
              <th>Customer #
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_no">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_no">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Reference<br> Name
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th> {{$global_terminologies['company_name']}}
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="4">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="4">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Email
                <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="email">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="email">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span> -->
              </th>
              <th>Primary<br>Sales<br> Person
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="primary_sale_person">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="primary_sale_person">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Secondary <br>Sale<br> Person </th>
              <th>Primary Contact
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="primary_contact">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="primary_contact">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>District
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="district">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="district">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>City
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="city">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="city">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Classification
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="classification">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="classification">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Country
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="country">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="country">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Address Reference
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="address_reference">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="address_reference">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Address
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="address">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="address">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Zip Code
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="zip_code">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="zip_code">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Tax ID
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="tax_id">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="tax_id">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Payment Terms
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="payment_terms">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="payment_terms">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Customer<br> Since
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_since">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_since">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>Draft<br> Orders
               <!--  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="draft_orders">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="draft_orders">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span> -->
              </th>
              <th>Total<br> Orders
                <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_orders">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_orders">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span> -->
              </th>
              <th>Last <br>Order<br> Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="5">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="5">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
              <th>{{$global_terminologies['note_two']}}</th>
              <th>Status</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!--  Content End Here -->

<!-- Modal For Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Customer Notes</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-cust-note-form" method="post">
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                      <div class="row">
                          <div class="col-xs-12 col-md-12">
                              <div class="form-group d-none">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Note Title" value="note" name="note_title">
                              </div>
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
        <input type="hidden" name="customer_id" class="note-customer-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

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

{{-- Customer Notes Modal --}}
<div class="modal" id="notes-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Customer Notes</h4>
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
 <!-- main content end here -->
</div><!-- main content end here -->


<div class="modal" id="sales_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Select Sales</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>
        <!-- Modal body -->
      <form role="form" class="" method="post">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="row">
                <div class="col-xs-12 col-md-12">
                   <div class="form-group">
                    <div class="incomplete-filter">
                      <select class="font-weight-bold form-control-lg form-control js-states state-tags sale_person_type" name="category" required="true">
                        <option value="" disabled="">--Select One--</option>
                        <option value="1" selected="">Assign As Primary Sale Person</option>
                        <option value="2">Assign As Secondary Sale Person</option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="incomplete-filter">
                      <select class="font-weight-bold form-control-lg form-control js-states state-tags sales_assign_select" name="category" required="true">
                        <option value="" disabled="" selected="">Choose Sales</option>
                        @if(@$users)
                        <optgroup label='Sales'>
                          @foreach($users as $user)
                          @if($user->role_id == 3)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                            @endif
                          @endforeach
                          </optgroup>
                        @endif
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- <form id="export_customer_data_form" action="{{ route('export-customer-data') }}"> -->
  <form id="export_customer_data_form">
  <input type="hidden" name="customers_status_exp" id="customers_status_exp">
  <input type="hidden" name="sales_person_exp" id="sales_person_exp">
  <input type="hidden" name="customers_type_exp" id="customers_type_exp">
  <input type="hidden" name="selecting_customer_group_exp" id="selecting_customer_group_exp">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
  <input type="hidden" name="search_value" id="search_value">
</form>


@endsection
@php
      $hidden_by_default = '';
 @endphp
@section('javascript')
<script type="text/javascript">
$(function(e){
    // Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#sortbyparam').val(column_name);
    $('#sortbyvalue').val(order);

    $('.table-customers').DataTable().ajax.reload();

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

  $(document).on('click','#adding-customer',function(){

    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
     $.ajax({
        url: "{{ route('add-customer-new') }}",
        method:'get',
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('#loader_modal').modal('hide');
          if(result.id != null)
          {
            var id = result.id;
            window.location.href = "{{ url('sales/get-customer-detail') }}"+"/"+id;
          }
        },
        error: function (request, status, error)
        {
          $('#loader_modal').modal('hide');
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
      }else{
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');

      }
    });

    $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc
      if($('.selectDoubleClick').hasClass('d-none')){
        $('.selectDoubleClick').removeClass('d-none');
        $('.selectDoubleClick').next().addClass('d-none');
      }
      if($('.inputDoubleClick').hasClass('d-none')){
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

    $(document).on("dblclick",".inputDoubleClick",function(){
    // alert($(this).data('id'));
    var str = $(this).data('id');
    if(str !== undefined){

    var res = str.split(" ");
  }else{
    var res = null;
  }
   if(res !== null){
    $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      var num = $(this).next().val();
      $(this).next().focus().val('').val(num);

     $.ajax({
    type: "get",
    url: "{{ route('get-salesperson') }}",
    data: 'value='+res[1]+'&choice='+res[0],
     beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
    success: function(response){
      if(response.field == 'salesperson'){
        console.log(res[2]);
        $('.primary_salespersons_select'+res[2]).empty();
        $('.primary_salespersons_select'+res[2]).append(response.html);
      }
      else if(response.field == 'secondary_salesperson'){
        console.log(res[2]);
        $('.secondary_salespersons_select'+res[2]).empty();
        $('.secondary_salespersons_select'+res[2]).append(response.html);
      }

      // $(this).addClass('d-none');
      // $(this).next().removeClass('d-none');
      // $(this).next().addClass('active');
      // $(this).next().focus();
      // var num = $(this).next().val();
      // $(this).next().focus().val('').val(num);

      $('#loader_modal').modal('hide');
    },
    error: function(request, status, error){
      $('#loader_modal').modal('hide');
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

  $(document).on('click', '.check', function () {
    // $(this).removeClass('d-none');
   // $('.assigned_to_sales').removeClass('d-none');
   // $('.delete_selected_customers').removeClass('d-none');
        var cb_length = $( ".check:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){
        $('.selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.selected-item').addClass('d-none');
        }

      }
    });

$(document).on('click','.delete_selected_customers',function(){
  // custDeleteIcon
  // alert($('.check-all').val());
   var selected_oi = [];
      var total_received = [];
        $("input.check:checked").each(function() {
          selected_oi.push($(this).val());
        });

        alert(selected_oi);
})

    // $(document).on("focus", ".datepicker", function(){
    //     $(this).datetimepicker({
    //         timepicker:false,
    //         format:'Y-m-d'});

    // });
    $(".state-tags").select2({});
    var role = "{{Auth::user()->role_id}}";
    var show = false;
    if(role == 1 || role == 9){
      show = true;
    }

  // New Table Entries
  var table2 =  $('.table-customers').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    // "pagingType":"input",
    ordering: false,
    dom: 'Blfrtip',
    pageLength: {{50}},
    serverSide: true,
    "lengthMenu": [50,100,150,200],
    "columnDefs": [
      { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
      { className: "dt-body-left", "targets": [ 2,3,4,5,6,7,8,9,10,11,12,13,14,15 ] },
      { className: "dt-body-right", "targets": [ 13,14 ] },
      {
        "targets": [ 0 ],
        "visible": show,
        "searchable": false
      }
    ],
    scrollX:true,
    scrollY : '90vh',
    scrollCollapse: true,
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',

      }
    ],
    ajax:
    {
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      url: "{!! route('get-customer') !!}",
      data: function(data) { data.customers_status = $('.customers_status option:selected').val(),
      data.user_id = $('.sales_persons option:selected').val(),
      data.customers_type = $('.customers_type option:selected').val(),
      data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),
      data.sortbyparam = column_name,
      data.sortbyvalue = order } ,
    },
    columns: [
        { data: 'checkbox', name: 'checkbox' },
        { data: 'action', name: 'action' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'reference_name', name: 'reference_name' },
        { data: 'company', name: 'company' },
        { data: 'email', name: 'email' },
        { data: 'user_id', name: 'user_id' },
        { data: 'secondary_sp', name:'secondary_sp'},
        { data: 'phone', name: 'phone' },
        { data: 'city', name: 'city' },
        { data: 'state', name: 'state' },
        { data: 'category', name: 'category' },
        { data: 'country', name: 'country' },
        { data: 'address_reference', name: 'address_reference' },
        { data: 'address', name: 'address' },
        { data: 'postalcode', name: 'postalcode' },
        { data: 'tax_id', name: 'tax_id' },
        { data: 'credit_term', name: 'credit_term' },
        { data: 'created_at', name: 'created_at' },
        { data: 'draft_orders', name: 'draft_orders' },
        { data: 'total_orders', name: 'total_orders' },
        { data: 'last_order_date', name: 'last_order_date' },
        { data: 'notes', name: 'notes' },
        { data: 'status', name: 'status' },
    ],
    initComplete: function () {
      $('body').find('.dataTables_scrollBody').addClass("scrollbar");
      $('body').find('.dataTables_scrollHead').addClass("scrollbar");
    // Enable THEAD scroll bars
    $('.dataTables_scrollHead').css('overflow', 'auto');
    // Sync THEAD scrolling with TBODY
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      });
    },
    drawCallback: function(){
      $('#loader_modal').modal('hide');
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
        data : {type:'customer_list',column_id:column},
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
        error: function(request, status, error)
        {
          $('#loader_modal').modal('hide');
        }
      });
    });

 $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
     $('#tableSearchField').val(searchField);
     count=searchField.length;
      if(e.keyCode == 13) {

        table2.search($(this).val()).draw();
        return;
      }else if(count>0){
        if(e.type == 'focusout'){
           table2.search(this.value).draw();
              return;
                   }
        }else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
        }
    });

  $(document).on('change','.customers_status',function(){
    $(".customers_type").val($(".customers_type option:first").val());
    var selected = $(this).val();
    if($('.customers_status option:selected').val() != '')
    {
      $('#customers_status_exp').val($('.customers_status option:selected').val());
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      $('.table-customers').DataTable().ajax.reload();
    }
  });

  $(document).on('change','.customers_type',function(){
    var selected = $(this).val();
    if($('.customers_type option:selected').val() != '')
    {
      $('#customers_type_exp').val($('.customers_type option:selected').val());
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      $('.table-customers').DataTable().ajax.reload();
    }
  });

  $(document).on('change','.selecting-customer-group',function(){
    var selected = $(this).val();
    if($('.selecting-customer-group').val() != '')
    {
      $('#selecting_customer_group_exp').val($('.selecting-customer-group').val());
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      $('.table-customers').DataTable().ajax.reload();
    }
  });

  $(document).on('click', '.add-notes', function(e){
      var customer_id = $(this).data('id');
      $('.note-customer-id').val(customer_id);
      // alert(customer_id);

    });

  $('.add-cust-note-form').on('submit', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-customer-note') }}",
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
              /*setTimeout(function(){
                window.location.reload();
              }, 2000);*/

              $('.add-cust-note-form')[0].reset();
              $('#add_notes_modal').modal('hide');
              table2.ajax.reload();

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
    let sid = $(this).data('id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "post",
      url: "{{ route('get-customer-note') }}",
      data: 'customer_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){

      }
    });

  });

  $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  // delete Customer
  $(document).on('click', '.custDeleteIcon', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete this Customer ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              method:"get",
              data:'id='+id,
              url: "{{ route('delete-customer') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){
                $('#loader_modal').modal('hide');
                if(response.success == true){
                  toastr.success('Success!', 'Customer Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  // window.location.reload();
                  $('.table-customers').DataTable().ajax.reload();
                }
                else
                {
                  toastr.error('Error!', 'Customer Can\'t be deleted invoices Attached!',{"positionClass": "toast-bottom-right"});
                }
              },
              error: function(request, status, error){
                $('#loader_modal').modal('hide');
              }
            });
          }
          else {
              swal("Cancelled", "", "error");
          }
      });
    });

  // delete Customer Shipping Info
  $(document).on('click', '.delete-note', function(e){
    var id = $(this).data('id');
    swal({
      title: "Are you sure?",
      text: "You want to delete this Customer Note?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
      },
    function (isConfirm) {
        if (isConfirm) {
          $.ajax({
            method:"get",
            data:'id='+id,
            url: "{{ route('delete-customer-note-info') }}",
            beforeSend: function(){
              $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success: function(response){
              $('#loader_modal').modal('hide');
              if(response.success == true){
               $("#cust-note-"+id).remove();
               table2.ajax.reload();
              }
            },
            error: function(request, status, error){
              $('#loader_modal').modal('hide');
            }
          });
        }
        else {
            swal("Cancelled", "", "error");
        }
    });
  });

    //Suspend or deactivate customer
  $(document).on('click', '.suspend-customer', function(){
    var id = $(this).data('id');
    swal({
        title: "Alert!",
        text: "Are you sure you want to suspend this Customer?",
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
              data:{id:id,type:'customer'},
              url:"{{ route('customer-suspension') }}",
              beforeSend:function(){
                 $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                 $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                  if(data.error == false){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    setTimeout(function(){
                      window.location.reload();
                    }, 2000);
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

  $(document).on('click', '.activateIcon', function(){
    var id = $(this).data('id');
    swal({
        title: "Alert!",
        text: "Are you sure you want to activate this Customer?",
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
              url:"{{ route('customer-activation') }}",
              beforeSend:function(){
                 $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                 $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                  if(data.error == false){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    setTimeout(function(){
                      window.location.reload();
                    }, 2000);
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

});
</script>

<script type="text/javascript">
$(".state-tags").select2({
  tags: true
});

// Delete customer
$(document).on('click','.delete-customer',function(){
  var customer_id = $(this).data('id');
  swal({
    title: "Are You Sure?",
    text: "You want to delete customer!!!",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, do it!",
    cancelButtonText: "Cancel",
    closeOnConfirm: true,
    closeOnCancel: false
    },
  function (isConfirm) {
    if (isConfirm)
    {
      $.ajax({
      method: "get",
      url: "{{ url('sales/delete-customer') }}",
      dataType: 'json',
      data: {id:customer_id},
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
            toastr.success('Success!', 'Customer deleted successfully.',{"positionClass": "toast-bottom-right"});
            // window.location.href = "{{ url('sales/customer') }}";
            $('.table-customers').DataTable().ajax.reload();
          }
         if(data.success == false)
         {
             toastr.warning('warning!', ' Customer can not be delete, Orders exist.',{"positionClass": "toast-bottom-right"});
             $('.table-customers').DataTable().ajax.reload();
         }
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
      });
    }
    else
    {
      $('#loader_modal').modal('hide');
      swal("Cancelled", "", "error");
      check[0].checked = false;
      if(check[1]){
      check[1].checked = false;
      }
      document.getElementById('is_default_value').value = 0;
    }
  });
});

 $(document).on('click', '.private-to-ecom', function(){
    var selected_quots = [];
    $("input.check:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to change customer Private to E-com???",
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
          data: {quotations : selected_quots},
          url:"{{ route('change-private-to-ecom') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            $('#loader_modal').modal('hide');
            if(result.success == true){
              toastr.success('Success!', 'Customer Changed Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-customers').DataTable().ajax.reload();
              $('.private-to-ecom').addClass('d-none');
              $('.check-all1').prop('checked',false);
              // window.location.reload();
             // window.location.href = "{{ url('/sales')}}";
            }
            if(result.success == false){
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-customers').DataTable().ajax.reload();
              $('.cancel-quotations').addClass('d-none');
              $('.check-all1').prop('checked',false);
            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });

$(document).on('change','.customers_status',function(){
    $(".sales_persons").val($(".sales_persons option:first").val());
    $(".customers_type").val($(".customers_type option:first").val());

    var selected = $(this).val();
    if($('.customers_status option:selected').val() != '')
    {
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      $('.table-customers').DataTable().ajax.reload();
    }
  });

$(document).on('change','.sales_persons',function(){
  $(".customers_type").val($(".customers_type option:first").val());
  var selected = $(this).val();
  $("#sales_person_exp").val($('.sales_persons option:selected').val());
  $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
  $("#loader_modal").modal('show');
  $('.table-customers').DataTable().ajax.reload();

});

$(".sales_assign_select").select2().on("select2:close", function(e) {
// alert($(this).val());
var user_id = $(this).val();
var user_as = $('.sale_person_type').val();

if(user_id == null){
  return;
}
    var selected_options = [];
      $("input.check:checked").each(function() {
        selected_options.push($(this).val());
      });
    length = selected_options.length;

      swal({
        title: "Alert!",
        text: "Are you sure to want to assign these customers to other sales person!",
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
                      data: {customers : selected_options,user_id:user_id,user_as: user_as},
                      url:"{{ route('assign-customers-to-sale') }}",
                      beforeSend: function(){
                        $('#loader_modal').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                        $('#loader_modal').modal('show');
                      },
                      success:function(result){
                        $('#loader_modal').modal('hide');
                          if(result.success == true){

                              toastr.success('Success!', 'Customers Assigned Successfully',{"positionClass": "toast-bottom-right"});
                              // window.location.reload();
                              $('.table-customers').DataTable().ajax.reload();
                              // $('.assigned_to_sales').addClass('d-none');
                              $('.selected-item').addClass('d-none');

                              $('#sales_modal').modal('hide');
                              $('.check-all').prop('checked',false);

                              // location.reload();

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

// delete Customer
$(document).on('click', '.delete-btn', function(e){
  var selected_options = [];
  $("input.check:checked").each(function() {
    selected_options.push($(this).val());
  });

  swal({
    title: "Are you sure?",
    text: "You want to delete selected Customer(s) ?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    closeOnConfirm: true,
    closeOnCancel: false
  },
  function (isConfirm) {
    if (isConfirm) {
      $.ajax({
        method:"get",
        data:'customers='+selected_options,
        url: "{{ route('delete-customers-permanent') }}",
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
            toastr.success('Success!', 'Customer(s) deleted successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-customers').DataTable().ajax.reload();
            $('.selected-item').addClass('d-none');
          }
          else
          {
            $('.errormsgDiv').show();
            $('#errormsg').html(data.errorMsg);
            // toastr.error('Error!', 'Some Customer(s) can\'t be deleted, they exist in following Orders.',{"positionClass": "toast-bottom-right"});
            $('.table-customers').DataTable().ajax.reload();
            $('.selected-item').addClass('d-none');
          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
      });
    }
    else {
        swal("Cancelled", "", "error");
    }
  });
});

$(document).on('click', '.closeErrorDiv', function (){
  $('.errormsgDiv').hide();
});

$('.reset-btn').on('click',function(){
  // $('.customers_status').val('');
  $('.sales_persons').val('');
  $('.customers_type').val('');
  $('.selecting-customer-group').val('');

  $('#selecting_customer_group_exp').val('');
  $('#customers_status_exp').val('');
  $('#customers_type_exp').val('');
  $("#sales_person_exp").val('');
  $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
  $("#loader_modal").modal('show');
  $(".state-tags").select2("", "");
  $('.table-customers').DataTable().ajax.reload();
});

// $('#export_customers').on('click',function(){
  $(document).on('click','#export_customers',function(){
  $('#selecting_customer_group_exp').val($('.selecting-customer-group').val());
  $('#customers_status_exp').val($('.customers_status option:selected').val());
  $('#customers_type_exp').val($('.customers_type option:selected').val());
  $("#sales_person_exp").val($('.sales_persons option:selected').val());
  //$("#export_customer_data_form").submit();

  var form=$('#export_customer_data_form');
    $('#search_value').val($('.table-customers').DataTable().search());
    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-customer-data')}}",
      data:form_data,
      beforeSend:function(){
        $('.export-btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForCustomerList();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForCustomerList();
        }
      },
      error:function(){
        $('.export-btn').prop('disabled',false);
      }
    });
});

    $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-customer-list')}}",
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
          checkStatusForCustomerList();
        }
      }
    });
  });
  function checkStatusForCustomerList()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-customer-list')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForCustomerList();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);

        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

$(document).on('change', 'select.select-common', function(){

if($(this).val() !== '')
{
if($(this).attr('name') == 'primary_salesperson_id')
{
  var attributeName='primary_salesperson_id';
  var old_value = $(this).parent().prev().data('fieldvalue');
  var rId = $(this).data('row_id');
  var new_value = $("option:selected", this).val();
  console.log(old_value,rId,new_value);
  swal({
    title: "Are you sure?",
    text: "You want to update the primary sale person for this customer!",
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
        // thisPionter.addClass('d-none');
        // thisPionter.prev().removeClass('d-none');
        // thisPionter.prev().html(new_value);
        // thisPionter.removeClass('active');
        saveProdData(rId, attributeName, new_value, old_value);
      }
      else
      {
        //$('.table-product').DataTable().ajax.reload();
      }
    }

  );
}
else if($(this).attr('name') == 'secondary_salesperson_id')
{
  var attributeName='secondary_salesperson_id';


  var new_value = $("option:selected", this).val();
  var old_value = $(this).parent().prev().data('fieldvalue');
  var thisPointer= $(this);
  var rId = $(this).data('row_id');

  swal({
    title: "Are you sure?",
    text: "You want to update the secondary sale person for this customer!",
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
        //var new_value = $("option:selected", thisPointer).html();
        // thisPointer.addClass('d-none');
        // thisPointer.prev().removeClass('d-none');
        // thisPointer.prev().html(new_value);
        saveProdData(rId, attributeName, new_value, old_value);
      }
      else
      {
        $('.table-product').DataTable().ajax.reload();
      }
    }

  );

}
else
  {
    var old_value = $(this).parent().prev().data('fieldvalue');

    var rId = $(this).parents('tr').attr('id');
    var new_value = $("option:selected", this).html();
    $(this).removeClass('active');
    $(this).addClass('d-none');
    $(this).prev().removeClass('d-none');
    $(this).prev().html(new_value);
    $(this).prev().css("color", "");
    saveProdData(rId, $(this).attr('name'), $(this).val(), old_value);

  }
}
});
function saveProdData(rId,field_name,field_value,old_value){
      console.log(field_name+' '+field_value+''+rId+' '+old_value);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "get",
         url: "{{ route('save-customer-data') }}",
        dataType: 'json',
        data: 'id='+rId+'&field_name='+field_name+'&new_value='+field_value+'&'+'old_value'+'='+old_value,
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
            toastr.success('Success!', 'Customer updated successfully.',{"positionClass": "toast-bottom-right"});
            // $('.table-customers').DataTable().ajax.reload();
            if(field_name == 'primary_salesperson_id')
            {
              $('.primary_select_'+rId).addClass('d-none');
              $('.primary_span_'+rId).removeClass('d-none');
              $('.primary_span_'+rId).html(data.user.name);
              $('.primary_span_'+rId).attr('data-id','salesperson '+field_value+' '+rId).data('id','salesperson '+field_value+' '+rId);
              $('.primary_span_'+rId).attr('data-fieldvalue',field_value).data('fieldvalue',field_value);
            }
            else if(field_name == 'secondary_salesperson_id')
            {
              $('.secondary_select_'+rId).addClass('d-none');
              $('.secondary_span_'+rId).removeClass('d-none');
              $('.secondary_span_'+rId).html('Add');
              $('.secondary_span_'+rId).attr('data-id','salesperson '+field_value+' '+rId).data('id','salesperson '+field_value+' '+rId);
              $('.secondary_span_'+rId).attr('data-fieldvalue',field_value).data('fieldvalue',field_value);
            }
            $('.table-customers').DataTable().ajax.reload();

          }
          else
          {
            toastr.success('Error!', 'Something Went Wrong.',{"positionClass": "toast-bottom-right"});
            // $('.table-customers').DataTable().ajax.reload();

          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }
      });
    }
    // shows customer related secondary supplier
$(document).on("click","#Show-Secondary-Suppliers", function(){
  var cust_detail_id = $(this).data("id");

  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
  $.ajax({
      url: "{{ route('CustomerSecondarySalesPersons') }}",
      method: 'post',
      dataType: 'json',
      data: {cust_detail_id:cust_detail_id},
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(result){
        $("#loader_modal").modal('hide');
        if(result.success==true)
        {
            $('#secondarySalesPersonTable').empty();
            let counter=0;
            for(let i=0;i<result.customerSecondarySalesPersons.length;i++){
                counter++;
                var row = $('<tr id="rowGenerated"><td>' +counter+ '</td><td>' + result.customerSecondarySalesPersons[i].secondary_sales_persons.name + '</td><td><i class="fa fa-trash secondary-user-delete secondarySalepersonDelete" id='+result.customerSecondarySalesPersons[i].id+' title="Delete"></i></td></tr>');
                $('#secondarySalesPersonTable').append(row);

            }

          $("#ShowSecondarySalesPerson").modal("show");
        }

      },
      error: function(request, status, error){
        $("#ShowSecondarySalesPerson").modal('hide');
      }

    });
  });
  // delete existing sales person of customer
$(document).on("click",".secondarySalepersonDelete", function(){

let salesPersonRecordId=this.id;
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  }
 });
 $.ajax({
  url: "{{ route('deleteSalesPersonRecord') }}",
  method: 'post',
//   dataType: 'json',
  data: {'salesPersonRecordId':salesPersonRecordId},
  beforeSend: function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
  },
  success: function(result){
    $("#loader_modal").modal('hide');
    if(result.success==true){
    $("#ShowSecondarySalesPerson").modal('hide');
        toastr.success('Success!', 'Sales Person Deleted',{"positionClass": "toast-bottom-right"});
        $('.table-customers').DataTable().ajax.reload();
    }else{
    $("#ShowSecondarySalesPerson").modal('hide');
        toastr.warning('Warning!', 'Sales Person Can Not Be Deleted',{"positionClass": "toast-bottom-right"});
    }
  },
  error: function(request, status, error){

    $("#ShowSecondarySalesPerson").modal('hide');
  }

});
});

$(document).on('click', '.snyc_with_ecom', function(){
    let id = $(this).data('id');
    swal({
      title: "Are you sure?",
      text: "You want to Enable this Customer to Ecom?",
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
          $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });
           $.ajax({
              url: "{{ route('sync-customer-to-ecom') }}",
              method: 'post',
              dataType: 'json',
              data: 'id='+id,
              beforeSend: function(){

              },
              success: function(result){
                if(result.success == true)
                {
                  toastr.success('Success!', 'Operation Succeeded !!!',{"positionClass": "toast-bottom-right"});
                }
                else
                {
                  toastr.error('Sorry!', 'This Customer Already Exists in Ecom Side !!!',{"positionClass": "toast-bottom-right"});
                }
              },
              error: function (request, status, error) {
              }
            });
        }
      }
    );

});

</script>
@stop

