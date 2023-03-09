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
    <!-- <button type="button" class="btn text-uppercase purch-btn mr-2 headings-color btn-color">back</button> -->
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>

    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
          <li class="breadcrumb-item active">Home</li>
      </ol>
  </div>

</div>
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->


@include('sales.layouts.dashboard-boxes')

<!-- upper section end  -->
</div>

<div class="row mb-3 align-items-center">

<div class="col-12 title-col headings-color">
<h4 class="maintitle">@if(!array_key_exists('my_quotation', $global_terminologies)) My Quotations  @else {{$global_terminologies['my_quotation']}} @endif</h4>
</div>
</div>

<div class="row mb-3 headings-color">
<div class="col-lg-12 headings-color">
  <form id="form_id" class="filters_div">
  <div class="row">

    <div class="col-md-3 col-6" id="quotation-1">
      <label><b>Quotations</b></label>
      <div class="form-group">
        <select class="form-control selecting-tables state-tags sort-by-value quot invoices-select" name="quot">
            <option value="1" selected>-- Quotations --</option>
           @foreach(@$quotation_statuses as $status)
           <option value="{{@$status->id}}">{{@$status->title}}</option>
           @endforeach
           <!-- <option value="5">Unfinished Quotation</option> -->
        </select>
      </div>
    </div>

    <div class="col-md-3 col-6" id="customer-group">
      <label><b>Choose Customer</b></label>
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="selecting-customer-group" required="true" id="choose_customer_quoation_unique">
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

    <div class="col-md-3 col-6" id="sale-person">
      <label><b>Sale Person</b></label>
      <div class="form-group">
        <select class="form-control state-tags selecting-sale" name="selecting-sale" id="sale_person_quotation_unique">
            <option value="">-- Sale Person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div>

    <div class="col-md-3 col-6" id="reset-button">
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
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold from_date" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-md-3 col-5">
      <label><b>To Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold to_date" id="to_date" autocomplete="off" >
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

      <span class="apply_date common-icons" title="Apply Dates">
        <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
      </span>
      </div>
    </div>

    <div class="col-md-4"></div>
    <!-- <div class="col-lg col-md-6"></div> -->
  </div>
  </form>
</div>


<div class="row entriestable-row col-lg-12 pr-0 quotation mt-5" id="quotation">
  @if(Auth::user()->role_id != 7)
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none mb-3">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm delete-quotations-orders
      deleteIcon" data-toggle="tooltip" data-type="unfinish-quotation" data-parcel="1" title="delete"><span><i class="fa fa-trash" ></i></span></a>

      <a href="javascript:void(0);">
          <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf without_vat">View</button>
      </a>

      <a href="javascript:void(0);">
        <button type="button" class="btn text-uppercase purch-btn headings-color mr-2 btn-color export-pdf-inc-vat with_vat">View (inc VAT)</button>
      </a>
  </div>
  @endif
  <div class="col-12 pr-0">
    <div class="entriesbg">
          <table class="table entriestable table-bordered table-quotation text-center table-theme-header">
              <thead>
                  <tr>
                      <th class="noVis">
                          <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                            <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                            <label class="custom-control-label" for="check-all"></label>
                          </div>
                      </th>
                      <th class="noVis">Action</th>
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
                      <th>Customer #
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
                      <th>Comment To <br> Warehouse</th>
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
                <th style="text-align: left;"></th>

            </tr>
        </tfoot>  --}}
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
<div class="row entriestable-row col-lg-12 pr-0 unfinish-quotation" id="unfinish-quotation">
 <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm delete-quotations
      deleteIcon" data-toggle="tooltip" data-type="unfinish-quotation" data-parcel="1" title="delete"><span><i class="fa fa-trash" ></i></span></a>
  </div>
      <div class="col-12 pr-0 ">
          <div class="entriesbg bg-white custompadding customborder">
                <table class="table entriestable table-bordered table-unfinish-quotations text-center">
                    <thead>
                        <tr>
                             <th>
                                <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                                <label class="custom-control-label" for="check-all"></label>
                                </div>
                             </th>
                            <th>Action</th>
                            <th>Order#</th>
                            <th>Customer #</th>
                            <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif</th>
                            <th>Number of Products</th>
                            <th>Payment Term</th>
                            <th>Delivery Date</th>
                            <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
                            <th>Total</th>
                            <th>Comment To <br> Warehouse</th>
                            <th>Status</th>
                        </tr>
                    </thead>

              </table>

          </div>
      </div>
</div>
<!-- <div class="modal" id="loader_modal_old" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>
    </div>
  </div>
</div> -->

<input type="hidden" name="apply_filter_btn" id="apply_filter_btn" value="0">
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




if(performance.navigation.type == 2){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
   location.reload(true);
   // $("#side-table").load(location.href + " #side-table");
}
  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });
  // $('#to_date').val(null);

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
    function formatResult(item) {
          if(!item.id) {
            // return `text` for optgroup
            return item.text;
          }

          // return item template
          return $('<span style="font-weight:bold">' + item.text + '</span>');
        }

        $('.selecting-customer-group').select2({
        });

    $('.sort-by-value').on('change', function(e){

      if($('.sort-by-value option:selected').val() == 5)
      {
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      }
      else
      {
        $('.table-quotation').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "none";
        document.getElementById('quotation').style.display = "block";
      }

    });

    $('.selecting-customer').on('change', function(e){
      if($('.sort-by-value option:selected').val() == 5)
      {
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else
      {
        $('.table-quotation').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "none";
        document.getElementById('quotation').style.display = "block";
      }
    });

    $('.selecting-customer-group').on('change', function(e){
      if($('.sort-by-value option:selected').val() == 5)
      {
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else
      {
        $('.table-quotation').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "none";
        document.getElementById('quotation').style.display = "block";
      }
    });

    $('.selecting-sale').on('change', function(e){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal").modal('show');
      if($('.sort-by-value option:selected').val() == 5)
      {
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else
      {
        $('.table-quotation').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "none";
        document.getElementById('quotation').style.display = "block";
      }
    });

     $('.selecting-warehouse').on('change', function(e){
      var id = $(this).val();
        $.ajax({

          method:"get",
          dataType:"json",
          data: {id : id},
          url:"{{ url('sales/get_sales') }}",
          beforeSend: function(){
            $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal2").modal('show');
          },
          success:function(result){
            $("#loader_modal2").modal('hide');
            if(result.error == false){
              $('.selecting-sale').find('option:not(:first)').remove();
              $('.selecting-sale').append(result.options);
            }
          },
          error: function(request, status, error){
            $("#loader_modal2").modal('hide');
          }
        });
    });

    $('#from_date').change(function() {
      $('#loader_modal2').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal2").modal('show');
      var date = $('#from_date').val();
    });

    $('#to_date').change(function() {
      $('#loader_modal2').modal({
        backdrop: 'static',
        keyboard: false
      });
      $("#loader_modal2").modal('show');
      var date = $('#to_date').val();
    });

    $(document).on('click','.apply_date',function(){
      if($('.sort-by-value option:selected').val() == 5){
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else{
      $('.table-quotation').DataTable().ajax.reload();
      $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
      document.getElementById('unfinish-quotation').style.display = "none";
      document.getElementById('quotation').style.display = "block";
      }
    });

    $('.reset').on('click',function(){
      $('.selecting-customer-group').val('');
      $('.selecting-sale').val('');
      $('#header_customer_search').val('');
      // $('#form_id').trigger("reset");
      $('input[type=search]').val('');
      $('.sort-by-value').val(6);
      // $(".state-tags").select2("destroy");
      // $(".state-tags").select2();

      // $('.state-tags').val('');
      $(".state-tags").select2("", "");
      $('.invoices-select').val('1');
      $('.table-quotation').DataTable().ajax.reload();

      // setTimeout(function(){
      //   $('#loader_modal').modal({
      //               backdrop: 'static',
      //               keyboard: false
      //               });
      //   $('.table-quotation').DataTable().search( " " ).draw();
      //  }, 20);
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
        // pageLength: "{{50}}",
        "lengthMenu": [100,200,300,400],
        // dom: 'ftipr',
        buttons: [
          {
            extend: 'colvis',
            columns: ':not(.noVis)',
          }
        ],
        "columnDefs":
        [
          { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : $hidden_by_default }}], visible: false },
          { className: "dt-body-left", "targets": [ 2,3,4,5,6,8,9,10,11 ] },
          { className: "dt-body-right", "targets": [7] }
        ],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax:{
          beforeSend: function(){
            $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
            $('.selected-item').addClass('d-none');
          },
          url:"{!! route('get-completed-quotation') !!}",
          // data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val(), data.className = ($('.selecting-customer-group option:selected').hasClass('parent')) ? 'parent' : 'child'} ,
          data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val(),data.type = 'datatable',
          data.sortbyparam = column_name,
          data.sortbyvalue = order,
          data.date_type = $("input[name='date_radio']:checked").val()
          } ,
        },
        columns: [
            { data: 'checkbox', name: 'checkbox'},
            { data: 'action', name: 'action' },
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
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });

          @if($display_my_quotation)
            table2.colReorder.order( [{{$display_my_quotation->display_order}}]);
          @endif

          $('body').find('.dataTables_scrollBody').addClass("scrollbar");
          $('body').find('.dataTables_scrollHead').addClass("scrollbar");

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
          data:{ dosortby : $('.sort-by-value option:selected').val(),selecting_customer : $('.selecting-customer option:selected').val(),from_date : $('#from_date').val(),to_date : $('#to_date').val() ,selecting_sale : $('.selecting-sale option:selected').val(),type : 'invoice',selecting_customer_group : $('.selecting-customer-group option:selected').val(),is_paid : $('.is_paid').val() ,input_keyword : $('#input_keyword').val(),date_type : $("input[name='date_radio']:checked").val(),type: 'footer'},
          beforeSend:function(){
          },
          success:function(result){
            var total = result.post;
            var sub_total = result.sub_total;

            // total = parseFloat(total).toFixed(2);
            // console.log(sub_total);
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
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
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

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      var arr = table2.colReorder.order();
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
        data : {type:'quotation_dashboard',column_id:col},
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
      data : "type=quotation_dashboard&order="+table2.colReorder.order(),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('hide');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
      },
      error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
    });
     //console.log(table.colReorder.order());
 table2.button(0).remove();
 table2.button().add(0,
 {
   extend: 'colvis',
   autoClose: false,
   fade: 0,
   columns: ':not(.noVis)',
   colVis: { showAll: "Show all" }
 });

 // table2.ajax.reload();
 var headerCell = $( table2.column( details.to ).header() );
 headerCell.addClass( 'reordered' );
});

    $(document).on('click', '.check-all', function () {
      if(this.checked == true)
      {
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
        var cb_length = $( ".check:checked" ).length;
        if(cb_length > 0)
        {
          $('.selected-item').removeClass('d-none');
        }
      }
      else
      {
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');
      }
    });

    $(document).on('click', '.check', function () {
    $('.delete-quotations').removeClass('d-none');
      var cb_length = $( ".check:checked" ).length;
      var st_pieces = $(this).parents('tr').attr('data-pieces');
      if(this.checked == true)
      {
        $('.selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }
      else
      {
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0)
        {
         $('.selected-item').addClass('d-none');
        }
      }
    });

    var table3 = $('.table-unfinish-quotations').DataTable({
        processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
         "lengthMenu": [100,200,300,400],
        // dom: 'ftipr',
        "columnDefs": [
          { className: "dt-body-left", "targets": [ 2,3,4,5,6,7,8 ] },
          { className: "dt-body-right", "targets": [9] }
        ],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax:{
          beforeSend: function(){
            $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
            $('.selected-item').addClass('d-none');
          },
          url:"{!! route('get-pending-quotation') !!}",
          data: function(data) {data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val() } ,
        },
        columns: [
            { data: 'checkbox', name: 'checkbox' },
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'customer', name: 'customer' },
            { data: 'number_of_products', name: 'number_of_products' },
            { data: 'payment_term', name: 'payment_term' },
            { data: 'delivery_date', name: 'delivery_date' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'comment_to_warehouse', name: 'comment_to_warehouse' },
            { data: 'status', name: 'status' },
        ],
        initComplete: function () {
           $('.table-unfinish-quotations').on('search.dt', function() {
   if(window.sessionStorage.getItem('unfinishedQuotationSaleTableSearch')){
            $('.dataTables_filter input').val(window.sessionStorage.getItem('unfinishedQuotationSaleTableSearch'));
          }
            });
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
        "fnDrawCallback": function() {
          $('#loader_modal').modal('hide');
        }
   });

    $(document).on('click', '.delete-quotations', function(){
      var selected_quots = [];
      $("input.check:checked").each(function() {
        selected_quots.push($(this).val());
      });
      // console.log(selected_quots)
      length = selected_quots.length;
      swal({
        title: "Alert!",
        text: "Are you sure to delete all these draft quotations? \n selected draft quotations:"+length,
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
            url:"{{ route('delete-draft-quotations') }}",
            beforeSend: function(){
              $('#loader_modal2').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success:function(result){
              $("#loader_modal2").modal('hide');
              if(result.success == true){

                toastr.success('Success!', 'Draft Quotations deleted Successfully',{"positionClass": "toast-bottom-right"});
                $('.table-unfinish-quotations').DataTable().ajax.reload();
                $('.delete-quotations').addClass('d-none');
                $('.check-all').prop('checked',false);

              }
            },
            error: function(request, status, error){
              $("#loader_modal2").modal('hide');
            }
          });
        }
        else{
            swal("Cancelled", "", "error");
        }
        });
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
   $('.delete-quotations').removeClass('d-none');
        var cb_length = $( ".check1:checked" ).length;
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

    $(document).on('click', '.delete-quotations-orders', function(){
      var selected_quots = [];
      $("input.check1:checked").each(function() {
        selected_quots.push($(this).val());
      });

      length = selected_quots.length;

      swal({
        title: "Alert!",
        text: "Are you sure you want to delete all these quotations? \n selected quotations:"+length,
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
            url:"{{ route('delete-order-quotations') }}",
            beforeSend: function(){
              $('#loader_modal2').modal({
                backdrop: 'static',
                keyboard: false
              });
              $('#loader_modal').modal('show');
            },
            success:function(result){
              $("#loader_modal2").modal('hide');
              if(result.success == true){

                toastr.success('Success!', 'Quotations deleted Successfully',{"positionClass": "toast-bottom-right"});
                $('.table-quotation').DataTable().ajax.reload();
                // $('.delete-quotations-orders').addClass('d-none');
                $('.selected-item').addClass('d-none');
                $('.check-all1').prop('checked',false);

              }
            },
            error: function(request, status, error){
              $("#loader_modal2").modal('hide');
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
      var is_texica = "{{$is_texica}}";

      if(is_texica == 1)
      {
       var url = "{{url('sales/export-quot-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+show_discount+"/"+bank_id+"/"+with_vat;
      }
      else
      {
       var url = "{{url('sales/export-quot-to-pdf-exc-vat')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+is_proforma+"/"+bank_id;
      }
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
      var is_texica = "{{$is_texica}}";
      if(is_texica == 1)
      {
       var url = "{{url('sales/export-quot-to-pdf')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+show_discount+"/"+bank_id+"/"+with_vat;
      }
      else
      {
        var url = "{{url('sales/export-quot-to-pdf-inc-vat')}}"+"/"+orders+"/"+page_type+"/"+sort+"/"+is_proforma+"/"+bank_id;
      }
      window.open(url, 'Orders Receivable Print', 'width=1200,height=600,scrollbars=yes');
    });

    $(document).on('click', '.delete-btn', function(e){
        var id = $(this).data('id');
        swal({
                title: "Alert!",
                text: "Are you sure you want to delete this draft quotation? ",
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
                        url:"{{ route('delete-single-draft-quotation') }}",
                        beforeSend:function(){
                            $('#loader_modal').modal({
                                backdrop: 'static',
                                keyboard: false
                            });
                            $("#loader_modal").modal('show');
                        },
                        success:function(response){
                            $("#loader_modal").modal('hide');
                            if(response.success === true){
                                toastr.success('Success!', 'Draft Quotation Deleted Successfully' ,{"positionClass": "toast-bottom-right"});
                            }else if(respnse.error === true){
                                toastr.error('Error!', 'Something Went Wrong. Please contact support.' ,{"positionClass": "toast-bottom-right"});
                            }
                            $('.table-unfinish-quotations').DataTable().ajax.reload();
                        },
                        error: function(request, status, error){
                          $("#loader_modal2").modal('hide');
                        }
                    });
                }
                else{
                    swal("Cancelled", "", "error");
                }
            });
    });

    $(document).on('click', '.deleteOrder', function(e){
      var id = $(this).data('id');
      swal({
              title: "Alert!",
              text: "Are you sure you want to delete this quotation? ",
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
                      url:"{{ route('delete-single-order-quotation') }}",
                      beforeSend:function(){
                          $('#loader_modal').modal({
                              backdrop: 'static',
                              keyboard: false
                          });
                         $("#loader_modal").modal('show');
                      },
                      success:function(response){
                          $("#loader_modal").modal('hide');
                          if(response.success === true)
                          {
                          swal("Successfully!", "Quotation Deleted!", "success");

                        }
                          $('.table-quotation').DataTable().ajax.reload();
                      },
                      error: function(request, status, error){
                        $("#loader_modal2").modal('hide');
                      }
                  });
              }
              else{
                  swal("Cancelled", "", "error");
              }
          });
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
 //   $(document).on('click',function(){
 //    if($('.check1:checked').length > 0)
 //    {
 //      $('.selected-item').removeClass('d-none');
 //    }
 //    else
 //    {
 //      $('.selected-item').addClass('d-none');
 //    }
 // });
</script>

@stop
