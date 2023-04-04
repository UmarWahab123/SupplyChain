@extends('sales.layouts.layout')
@section('title','Dashboard')
@section('content')
<style type="text/css">
  .select2-results__option
{
  display: block !important;
  overflow:  hidden !important;
  white-space: nowrap !important;
}
</style>
{{-- Content Start from here --}}

<!-- Right Content Start Here -->
<div class="right-contentIn">
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
          <li class="breadcrumb-item"><a href="{{route('accounting-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @endif
            @if(Auth::user()->role_id == 3)
          <li class="breadcrumb-item active">My Draft Invoices</li>
            @else
          <li class="breadcrumb-item active">All Draft Invoices</li>
            @endif
      </ol>
  </div>
</div>
@if(Auth::user()->role_id != 2)
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->
<div class="col-lg-12">
<!-- 1st four box row start -->
<div class="row mb-3">
  @include('sales.layouts.dashboard-boxes')
</div>
<!-- first four box row end-->
</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>
@endif

<div class="row mb-3 headings-color">

<div class="col-md-9 col-12">
  @if(Auth::user()->role_id == 3)
  <h4>My Draft Invoices</h4>
  @else
  <h4>All Draft Invoices</h4>
  @endif
</div>

<div class="col-md-12">
  <form id="form_id" class="filters_div">
  <div class="row">

    <div class="col-md-3 col-6" id="draf-invoice">
      <label><b>Draft Invoices</b></label>
      <div class="form-group">
        <select class="form-control selecting-tables state-tags sort-by-value invoices-select">
            <option value="2" selected>-- Draft Invoices --</option>
             @foreach(@$quotation_statuses as $status)
           <option value="{{@$status->id}}">{{@$status->title}}</option>
           @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-3 col-6" id="customer-group">
      <label><b>Choose Customer</b></label>
      <div class="form-group">
        <select id="choose_customer_dropdown_unqiue2" class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="selecting-customer-group" required="true">
            <option value="">Choose Customer / Group</option>
            @foreach($customer_categories as $cat)
              <option value="{{'cat-'.@$cat->id}}" class="parent" title="{{@$cat->title}}">{{@$cat->title}} {!! $extra_space !!}{{$cat->customer != null ? $cat->customer->pluck('reference_name') : ''}}</option>
                @foreach($cat->customer as $customer)
                  <option value="{{'cus-'.$customer->id}}" class="child-{{@$cat->id}}" title="{{@$cat->title}}" >&nbsp;&nbsp;&nbsp;{{@$customer->reference_name}}{!! $extra_space !!}{{$cat->title}}</option>
                @endforeach
            @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-3 col-6" id="sale-person-1">
      <label><b>Sale Person</b></label>
      <div class="form-group">
        <select id="sale_customer_dropdown_unique2" class="form-control state-tags selecting-sale">
            <option value="">-- Sale person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div>
    <div class="col-md-3 col-6" id="reset-1">
      <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3 reset__phone">
          <span class="reset common-icons" title="Reset">
      <img src="{{asset('public/icons/reset.png')}}" width="27px">
      </span>
      </div>
    </div>

  </div>
  <div class="row">
    <div class="col-md-3 col-5">
      <label><b>From Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-md-3 col-5">
      <label><b>To Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
    </div>

    <div class="col-md-2 col-6 d-flex mt-5" style="width: 20%;">
        <div class="form-check">
          <input type="radio" class="form-check-input dates_changer delivery_date"  name="date_radio" value='2' checked>
          <label class="form-check-label" for="exampleCheck1"><b>Delivery Date</b></label>
        </div>
        <div class="form-check ml-5">
          <input type="radio" class="form-check-input dates_changer target_ship_date"  name="date_radio" value='3'>
          <label class="form-check-label" for="exampleCheck1"><b>Target Ship Date</b></label>
        </div>
    </div>

    <div class="col-md-3 col-2">
      <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3 apply_dates__phone">
      <!-- <button class="btn recived-button apply_date" type="button">Apply Dates</button>   -->

      <span class="apply_date common-icons" title="Apply Date">
        <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
      </span>
      </div>
    </div>
  </div>
  </form>
</div>


<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
  <div class="alert alert-danger d-none not-cancelled-alert col-lg-12 ml-3">

</div>
  @if(Auth::user()->role_id != 7)
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 mb-3 d-none">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm cancel-quotations
      deleteIcon" title="Cancel"><span><i class="fa fa-times" ></i></span></a>

       <a href="javascript:void(0);" class="btn selected-item-btn btn-sm revert-quotations" data-type="quotation" title="Revert Draft Invoice" >
    <i class="fa fa-undo" style=""></i></a>

    <a href="javascript:void(0);">
          <span class="vertical-icons export-pdf-proforma-inc-vat export-pdf-inc-vat" title="Delivery Bill (Inv VAT)">
            <img src="{{asset('public/icons/delivery.png')}}" width="35px">
          </span>
        </a>

        @if($showPrintPickBtn == 1)
        <a href="javascript:void(0);" class="">
          <span class="vertical-icons export-pdf-proforma-exc-vat export-pdf" title="Pro-Forma">
            <img src="{{asset('public/icons/proforma.png')}}" width="27px">
          </span>
        </a>

        <a href="javascript:void(0);">
          <span class="vertical-icons export-pick-instruction" title="Print Pick Instruction">
            <img src="{{asset('public/icons/pickinstruction.png')}}" width="27px">
          </span>
        </a>
        @endif

        <button class="btn btn-primary merge-draft-invoices">Merge</button>
  </div>
  @endif
  <div class="col-12 pr-0 mt-5">
    <div class="entriesbg">
      <table class="table entriestable table-bordered table-quotation text-center">
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <!-- <th>Action</th> -->
            <th>Order#
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="ref_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="ref_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Sales Person
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="user_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="user_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Customer#
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_id">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_id">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Reference Address
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="rederence_address">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="rederence_address">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
            <th>Company Name
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="company">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="company">
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
            <!-- <th>Date Purchase</th> -->
            <th>Discount
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="discount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="discount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Sub Total
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sub_total_amount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sub_total_amount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Order Total
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_amount">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_amount">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Delivery Date
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="delivery_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="delivery_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Due Date
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="due_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="due_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Target Ship Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_ship_date">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_ship_date">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
            </th>
            <th>Remark
            </th>
            <th>Comment To <br> Warehouse
            </th>
            <th>Ref. Po #
            </th>
            <th>Status
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="status">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="status">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Printed
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="printed">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="printed">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
          </tr>
        </thead>
        {{-- <tfoot>
            <tr>
                <th></th>
                <th style="text-align: right;"></th>
                <th style="text-align: right;"></th>
                <th style="text-align: right;"></th>
                <th style="text-align: right;"></th>
                <th style="text-align: right;"></th>

                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>
                <th style="text-align: left;"></th>

            </tr>
        </tfoot> --}}
      </table>
      <table id="footer_table" width="100%" class="table-bordered mt-2">
        <tbody>
          <td class="phone_footer_table" colspan="2" style="text-align: right; padding:5px 15px 5px; font-weight: bold; font-size: 1.1rem;">
              Order Sub Total For All Entries
            </td>
            <td class="phone_footer_table" style="padding:5px 0 5px 15px; font-weight: bold; font-size:1.1rem;" id="sub_total_val_td">
              Loading ...
            </td>
          <td class="phone_footer_table" colspan="2" style="text-align: right; padding:5px 15px 5px; font-weight: bold; font-size: 1.1rem;">Order Total For All Entries</td>
          <td class="phone_footer_table" id="total_val_td" style="padding:5px 0 5px 15px; font-weight: bold; font-size:1.1rem;">Loading ...</td>
        </tbody>
    </table>
    </div>
  </div>
</div>
</div>

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

<input type="hidden" name="customer_id_select" id="customer_id_select" >
<!-- main content end here -->
</div>
@endsection

@php
$hidden_by_default = '';
@endphp

@section('javascript')
<script type="text/javascript">

$('input[type=radio][name=date_radio]').change(function() {
    if (this.value == '2') {
      $('#date_radio_exp').val(this.value);
      document.getElementById('quotation').style.display = "block";
    }
    else if (this.value == '3') {
      $('#date_radio_exp').val(this.value);
      document.getElementById('quotation').style.display = "block";
    }
});
var order = 1;
var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.table-quotation').DataTable().ajax.reload();

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





  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });
  // $('#to_date').val(null);

  $(document).ready(function(){
    @if(Session::has('total_draft'))
      // alert('yes');
      // var last_month = new Date();
      // var first_date = new Date(last_month.getFullYear(), last_month.getMonth(), 1);
      // first_date.setDate( first_date.getDate() + 1 );
      // // alert(first_date.toISOString().substr(0, 10));
      // let today1 = new Date().toISOString().substr(0, 10);
      // document.querySelector("#from_date").value = first_date.toISOString().substr(0, 10);
      // document.querySelector("#to_date").value = today1;

      var currentTime = new Date();
      // First Date Of the month
      var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

      $('#from_date').datepicker('');
      $('#to_date').datepicker('');
    @endif
});
  $(function(e){
    //to fill widget values
    $.ajax({
      method:"get",
      url:"{{ route('get-widgets-values') }}",
      beforeSend:function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
          });
         // $("#loader_modal").modal('show');
      },
      success:function(response){
          // $("#loader_modal").modal('hide');
          $('.admin-quotation-count').html(response.quotation);
          $('.total_amount_of_quotation_admin').html(response.total_amount_of_quotation);
          $('.total_number_of_draft_invoices_admin').html(response.total_number_of_draft_invoices);
          $('.admin_total_sales_draft_admin').html(response.admin_total_sales_draft);
          $('.total_number_of_invoices_admin').html(response.total_number_of_invoices);
          $('.admin_total_sales').html(response.admin_total_sales);
          $('.total_gross_profit').html(response.total_gross_profit);
          $('.total_gross_profit_count').html(response.total_gross_profit_count);
          $('.total_amount_of_overdue_invoices_count').html(response.total_amount_of_overdue_invoices_count);
          $('.total_amount_overdue').html(response.total_amount_overdue);
          $('.company_total_sales').html(response.company_total_sales);
          $('.salesCustomers').html(response.salesCustomers);
          $('.totalCustomers').html(response.totalCustomers);
          $('.sales_coordinator_customers_count').html(response.sales_coordinator_customers_count);
          $('.salesQuotations').html(response.salesQuotations);
          $('.salesCoordinateQuotations').html(response.salesCoordinateQuotations);
          $('.salesDraft').html(response.salesDraft);
          $('.salesCoordinateDraftInvoices').html(response.salesCoordinateDraftInvoices);
          $('.invoice1').html(response.Invoice1);
          $('.salesInvoice').html(response.salesInvoice);
          $('.salesCoordinateInvoices').html(response.salesCoordinateInvoices);
          $('.salesCoordinateInvoicesAmount').html(response.salesCoordinateInvoicesAmount);
      },
      error: function(request, status, error){
        $("#loader_modal2").modal('hide');
      }
  });
    //ends here
    $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

    $('.sort-by-value').on('change', function(e){
        $('.table-quotation').DataTable().ajax.reload();
        document.getElementById('quotation').style.display = "block";
    });

    $('.selecting-customer').on('change', function(e){
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.selecting-customer-group').on('change', function(e){
      // alert($('.selecting-customer option:selected').val());

      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
      document.getElementById('quotation').style.display = "block";

    });


    $('#from_date').change(function() {
      var date = $('#from_date').val();
      // $('.table-quotation').DataTable().ajax.reload();
      // document.getElementById('quotation').style.display = "block";
    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();
      // $('.table-quotation').DataTable().ajax.reload();
      // document.getElementById('quotation').style.display = "block";
    });

    $(document).on('click','.apply_date',function(){
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.reset').on('click',function(){
      $('#customer_id_select').val(null);
      $('#header_customer_search').val('');
      $('#form_id').trigger("reset");
      $('.sort-by-value').val(2);
      // $(".state-tags").select2("destroy");
      // $(".state-tags").select2();
      $(".state-tags").select2("", "");
      $('.invoices-select').val('2');
      $('.table-quotation').DataTable().ajax.reload();

    });

    var table2 = $('.table-quotation').DataTable({
      processing: false,
      colReorder: {
        realtime: false,
      },
      dom: 'lrtip',
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      // "pagingType": "input",
      "sPaginationType": "listbox",
      ordering: false,
      searching:true,
      serverSide: true,
      dom: 'Blfrtip',
      "lengthMenu": [100,200,300,400],
      // dom: 'ftipr',
      buttons: [
        {
          extend: 'colvis',
          columns: ':not(.noVis)',
        }
      ],
      "columnDefs": [
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,7,8,9,10] },
        { className: "dt-body-right", "targets": [6] }
      ],
      scrollX: true,
      scrollY : '90vh',
    scrollCollapse: true,
      ajax:{
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
          $('.selected-item').addClass('d-none');
        },
        url:"{!! route('get-completed-quotation') !!}",
        // data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() ,data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val()} ,
         data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('#customer_id_select').val(), data.className = className,data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),data.type = 'datatable',
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.date_type = $("input[name='date_radio']:checked").val();
         } ,
      },
      columns: [
          { data: 'checkbox', name: 'checkbox' },
          // { data: 'action', name: 'action' },
          { data: 'ref_id', name: 'ref_id' },
          { data: 'sales_person', name: 'sales_person' },
          { data: 'customer_ref_no', name: 'customer_ref_no' },
          { data: 'customer', name: 'customer' },
          { data: 'reference_address', name: 'reference_address' },
          { data: 'customer_company', name: 'customer_company' },
          { data: 'tax_id', name: 'tax_id' },
          { data: 'discount', name: 'discount' },
          { data: 'sub_total_amount', name: 'sub_total_amount' },
          { data: 'total_amount', name: 'total_amount' },
          { data: 'delivery_date', name: 'delivery_date' },
          { data: 'due_date', name: 'due_date' },
          { data: 'target_ship_date', name: 'target_ship_date' },
          { data: 'remark', name: 'remark' },
          { data: 'comment_to_warehouse', name: 'comment_to_warehouse' },
          { data: 'memo', name: 'memo' },
          { data: 'status', name: 'status' },
          { data: 'printed', name: 'printed' },
        ],
         initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
          @if(@$display_draft_invoice)
            table2.colReorder.order( [{{$display_draft_invoice->display_order}}]);
          @endif


          $('body').find('.dataTables_scrollHead').addClass("scrollbar");
          $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        },
        // drawCallback: function(){
        // },
        "fnDrawCallback": function() {
          $('#loader_modal').modal('hide');
        // var api = this.api()
        // var json = api.ajax.json();
        // var total = json.post;
        // var sub_total = json.sub_total;
        // total = parseFloat(total).toFixed(2);
        // total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

        // sub_total = parseFloat(sub_total).toFixed(2);
        // sub_total = sub_total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        // $('#total_val_td').html(total);
        // $('#sub_total_val_td').html(sub_total);
    },
    footerCallback: function ( row, data, start, end, display ) {
        // var api = this.api();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"get",
          dataType:"json",
          url:"{{ route('get-completed-quotation') }}",
          data:{ dosortby : $('.sort-by-value option:selected').val(),selecting_customer : $('.selecting-customer option:selected').val(),from_date : $('#from_date').val(),to_date : $('#to_date').val() ,selecting_sale : $('.selecting-sale option:selected').val(),type : 'invoice',selecting_customer_group : $('.selecting-customer-group option:selected').val(),is_paid : $('.is_paid').val() ,input_keyword : $('#input_keyword').val(),date_type : $("input[name='date_radio']:checked").val(), type: 'footer'},
          beforeSend:function(){
          },
          success:function(result){
            var total = result.post;
            var sub_total = result.sub_total;

            // total = parseFloat(total).toFixed(2);
            // total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            // sub_total = parseFloat(sub_total).toFixed(2);
            // sub_total = sub_total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            $('#total_val_td').html(total);
            $('#sub_total_val_td').html(sub_total);
          },
          error: function(){

          }
        });
      },
   });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13) {
        //  alert($(this).val());
              table2.search($(this).val()).draw();
      }
    });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      var arr = table2.colReorder.order();
      // var all = arr.split(',');
      var all = arr;
      if(all == ''){
        var col = column;
      }else{
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
        data : {type:'draft_invoice_dashboard',column_id:col},
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

    table2.on( 'column-reorder', function ( e, settings, details ) {
      $.get({
      url : "{{ route('column-reorder') }}",
      dataType : "json",
      data : "type=draft_invoice_dashboard&order="+table2.colReorder.order(),
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $('#loader_modal').modal('hide');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
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

    $('.selecting-sale').on('change', function(e){

      $('.table-quotation').DataTable().ajax.reload();

    });

  $(document).on('click', '.check-all1', function () {
        if(this.checked == true){
        $('.check1').prop('checked', true);
        $('.check1').parents('tr').addClass('selected');
        var cb_length = $( ".check1:checked" ).length;
        if(cb_length > 0){
          $('.selected-item').removeClass('d-none');
        }
      }else{
        $('.check1').prop('checked', false);
        $('.check1').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');

      }
    });

   $(document).on('click', '.check1', function () {
    // $(this).removeClass('d-none');
   // $('.cancel-quotations').removeClass('d-none');
   // $('.revert-quotations').removeClass('d-none');
        var selected_quots = [];
        $("input.check1:checked").each(function() {
            selected_quots.push($(this).val());
        });
        $('.selected-item').addClass('d-none');

        var cb_length = $( ".check1:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
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

        if(selected_quots.length > 0)
        {
            $('.selected-item').removeClass('d-none');
        }
    });

   $(document).on('click', '.cancel-quotations', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to cancel selected orders?",
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
          url:"{{ route('cancel-orders') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            $('#loader_modal').modal('hide');
            if(result.success == true)
            {
              //toastr.success('Success!', 'Orders Cancelled Successfully',{"positionClass": "toast-bottom-right"});
                       swal({
                            title: "Success",
                            text: "Orders Cancelled Successfully",
                            type: "success",
                            showCancelButton: true,
                            cancelButtonText:"Close",
                            showCloseButton: true,
                            confirmButtonText: "Cancel Orders Page!",
                            closeOnConfirm: false,
                            showLoaderOnConfirm: true
                          }, function () {
                             window.location = "{{ route('get-cancelled-orders') }}";
                          });
              $('.table-quotation').DataTable().ajax.reload();
              // $('.cancel-quotations').addClass('d-none');
               // $('.revert-quotations').addClass('d-none');
               $('.selected-item').addClass('d-none');
              $('.check-all1').prop('checked',false);
           // window.location.href = "{{ url('sales/get-cancelled-orders')}}";

            }
            if(result.success == false)
            {
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              // $('.cancel-quotations').addClass('d-none');
               // $('.revert-quotations').addClass('d-none');
               $('.selected-item').addClass('d-none');
              $('.check-all1').prop('checked',false);
            }

            if(result.msg != null){
              $('.not-cancelled-alert').html(result.msg);
              $('.not-cancelled-alert').removeClass('d-none');
              // $('.cancel-quotations').addClass('d-none');
               // $('.revert-quotations').addClass('d-none');
               $('.selected-item').addClass('d-none');
              $('.table-quotation').DataTable().ajax.reload();

            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });

   $(document).on('click', '.revert-quotations', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to revert the draft invoice?",
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
          url:"{{ route('revert-draft-invoice') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            $('#loader_modal').modal('hide');
            if(result.success == true)
            {
              toastr.success('Success!', 'Orders Reverted Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              // $('.revert-quotations').addClass('d-none');
              // $('.cancel-quotations').addClass('d-none');
              $('.selected-item').addClass('d-none');
              $('.check-all1').prop('checked',false);
             // window.location.href = "{{ url('/sales')}}";
            }
            if(result.success == false)
            {
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              // $('.cancel-quotations').addClass('d-none');
              $('.selected-item').addClass('d-none');

              $('.check-all1').prop('checked',false);
            }

            // if(result.order_cancelled == true){
            //    toastr.warning('Warning!', 'Orders '+result.orders+' is/are already cancelled!',{"positionClass": "toast-bottom-right"});
            // }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });
   $(document).on('click','.export-pdf',function(e){
      e.preventDefault();
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });
      // console.log(selected_quots[0]);
      var sort = 'id_sort';
      var bank_id = null;
      var orders = selected_quots;
      var with_vat = null;
      var show_discount = 0;
      var page_type = 'quotations';
      var is_proforma = 'yes';
      var is_texica = null;
      var url = "{{url('sales/export-quot-to-pdf-exc-vat')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+is_proforma+"/"+bank_id;
      window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
    });
    $(document).on('click','.export-pdf-inc-vat',function(e){
      e.preventDefault();
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });
      // console.log(selected_quots[0]);
      var sort = 'id_sort';
      var bank_id = null;
      var orders = selected_quots;
      var with_vat = "1";
      var show_discount = 0;
      var page_type = 'quotations';
      var is_proforma = 'yes';
      var is_texica = null;
      var url = "{{url('sales/export-quot-to-pdf-inc-vat')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+is_proforma+"/"+bank_id;
      window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
    });
    $(document).on('click', '.export-pick-instruction', function(e){
      var sort = 'id_sort';
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });
       var orders = selected_quots;
       var page_type = 'draft_invoice';
       var column = column_name != '' ? column_name : 'id';
       var url = "{{url('sales/export-draft-pi')}}"+"/"+orders+"/"+page_type+"/"+column+"/"+sort;
       window.open(url, 'Orders Print', 'width=1200,height=600,scrollbars=yes');
    });

  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
      @php
       Session()->forget('successmsg');
      @endphp
  @endif
  });
$('#header_customer_search').on('click',function(){
  if($('.custom__search_arrows').hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($(this).val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('.custom__search_arrows').on('click',function(){
  if($(this).hasClass('fa-caret-down'))
  {
    var _token = $('input[name="_token"]').val();
    GetCathegoryCustomers($('#header_customer_search').val(),_token);
  }
  else
  {
    $("#myIddd").empty();
  }
});
$('#header_customer_search').keyup(function(event){
      // $('#header_customer_search').unbind("focus");
      keyindex = -1;
      alinks = '';
      var query = $(this).val();

      if(event.keyCode == 13)
      {
        if(query.length > 2)
        {
         var _token = $('input[name="_token"]').val();
         GetCathegoryCustomers(query,_token);
        }
        else if(query.length == 0)
        {
          $('#header_prod_searchh').val('');
          $('#header_prod_searchh').data('prod_id','');
        }
        else
        {
          $('#myIddd').empty();
          toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
        }
      }

    });
  function GetCathegoryCustomers(query=null,_token=null){
    $.ajax({
      url:"{{ route('purchase-fetch-customer') }}",
      method:"POST",
      data:{query:query, _token:_token},
      beforeSend:function(){
        // alert('here');
        $('#loader__custom_search').removeClass('d-none');
        // $('.custom__search_arrows').removeClass('fa-caret-down');
        // $('.custom__search_arrows').addClass('fa-caret-up');
      },
      success:function(data){
        $('#myIddd').html(data);
        $('#loader__custom_search').addClass('d-none');
        $('.custom__search_arrows').removeClass('fa-caret-down');
        $('.custom__search_arrows').addClass('fa-caret-up');

       },
       error: function(){

       }
    });
  }
  var className = '';
  $(document).on("click",".list-data",function() {
      var li_id = $(this).attr('data-id');
       var li_text = $(this).attr('data-value');
       $('.search_customer').val(li_text);
      $("#customer_id_select").val(li_id);
      $("#customer_id_exp").val(li_id);
      $(".select_customer_id").hide();
      $('#header_customer_search').val(li_text);
      className = $(this).hasClass('parent') ? 'parent' : 'child';

    $('.table-quotation').DataTable().ajax.reload();

    // $(".customer_id").val(li_id);


});
  $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   });
 //  $(document).on('click',function(){
 //    if($('.check1:checked').length > 0)
 //    {
 //      $('.selected-item').removeClass('d-none');
 //    }
 //    else
 //    {
 //      $('.selected-item').addClass('d-none');
 //    }
 // });

 $('.merge-draft-invoices').on('click',function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });
    swal({
      title: "Alert!",
      text: "Are you sure you want to merge selected orders?",
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
          data: {order_ids : selected_quots},
          url:"{{ route('merge-draft-invoices') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            if(result.success == true)
            {
              toastr.success('Success', result.msg ,{"positionClass": "toast-bottom-right"});
              window.location.href = result.url;
            }
            if(result.success == false)
            {
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
 });
</script>

@stop
