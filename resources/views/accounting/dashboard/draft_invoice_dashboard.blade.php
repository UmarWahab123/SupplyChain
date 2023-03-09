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
          <li class="breadcrumb-item active">All Draft Invoices</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}

<!-- Right Content Start Here -->
<div class="right-contentIn">
@if(Auth::user()->role_id != 2)
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->
<div class="col-lg-12">
<!-- 1st four box row start -->
<div class="row mb-3">
  @include('accounting.layouts.dashboard-boxes')
</div>
<!-- first four box row end-->
</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>
@endif

<div class="row mb-3 headings-color">

<div class="col-lg-9">
  @if(Auth::user()->role_id == 3)
  <h4>My Draft Invoices</h4>
  @else
  <h4>All Draft Invoices</h4>
  @endif
</div>

<div class="col-lg-12 col-md-12">
  <form id="form_id">
  <div class="row">

    <div class="col-lg col-md-6" id="draf-invoice">
      <div class="form-group">
        <select class="form-control selecting-tables sort-by-value">
            <option value="2" selected>-- Draft Invoices --</option>
            <option value="7">Selecting Vendors</option>
            <option value="8">Purchasing</option>
            <option value="9">Importing</option>
            <option value="10">Delivery</option>
        </select>
      </div>
    </div>

   <!--  <div class="col-lg col-md-6" id="Choose-customer-1">
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer" name="customer" required="true">
            <option value="">Choose Customer</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{@$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div>
<div class="col-lg col-md-6" id="customer-group-1">
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
            <option value="">Customer Group</option>
            @foreach($customer_categories as $cat)
            <option value="{{$cat->id}}">{{@$cat->title}}</option>
            @endforeach
        </select>
      </div>
    </div> -->

   <!--  <div class="col-lg col-md-8">
         <div class="border rounded position-relative custom-input-group autosearch">
          <input type="text" class="font-weight-bold form-control-lg form-control customer_id search_customer" id="header_customer_search" tabindex="0" name="prod_name" placeholder="Choose Customer / Customer Group" autocomplete="off" value="" data-prod_id="" style="min-height:34px;border:1px solid #aaa; padding-top:8px;">
          <span id="loader__custom_search" class="position-absolute d-none" style="right:0;top:0;"><div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="35"></div></span>
          <i class="fa fa-caret-down custom__search_arrows" aria-hidden="true"></i>
        </div>
        <p id="myIddd" class="m-0"></p>
      </div> -->
      <div class="col-2" id="customer-group">
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
            <option value="">Choose Customer / Group</option>
            @foreach($customer_categories as $cat)
              <option value="{{'cat-'.@$cat->id}}" class="parent" title="{{@$cat->title}}">{{@$cat->title}} {!! $extra_space !!}{{$cat->customer != null ? $cat->customer->pluck('reference_name') : ''}}</option>
                @foreach($cat->customer as $customer)
                  <option value="{{'cus-'.$customer->id}}" class="child-{{@$cat->id}}" title="{{@$customer->reference_name}}" >&nbsp;&nbsp;&nbsp;{{@$customer->reference_name}}{!! $extra_space !!}{{$cat->title}}</option>
                @endforeach
            @endforeach
        </select>
      </div>
    </div>
    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
    <div class="col-lg col-md-6" id="sale-person-1">
      <div class="form-group">
        <select class="form-control selecting-sale">
            <option value="">-- Sale person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}">{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div>
    @endif

    <div class="col-lg col-md-6">
      <div class="form-group">
        <input type="text" placeholder="To Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
    </div>
  </div>

    <div class="col-lg col-md-6">
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
    </div>
  </div>

    <div class="col-lg col-md-4 ml-md-auto mr-md-auto pb-md-3" id="reset-1">
    <div class="input-group-append ml-3">
      <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
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
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm cancel-quotations
      deleteIcon" title="Cancel"><span><i class="fa fa-times" ></i></span></a>

       <a href="javascript:void(0);" class="btn selected-item-btn btn-sm revert-quotations" data-type="quotation" title="Revert Draft Invoice" >
    <i class="fa fa-undo" style=""></i></a>
  </div>
  @endif
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-quotation text-center">
        <thead>
          <tr>
            <th>
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <!-- <th>Action</th> -->
            <th>Order#
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <th>Sales Person
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="sales_person">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="sales_person">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <th>Customer#
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_no">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_no">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <th>Reference Name
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="customer_reference_name">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="customer_reference_name">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <!-- <th>Date Purchase</th> -->
            <th>Order Total
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_total">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_total">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <th>Delivery Date
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="draft_delivery_date">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="draft_delivery_date">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <!-- <th>Target Ship Date</th> -->
            <th>Memo
            <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="memo">
                        <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                      </span>
                      <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="memo">
                        <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                      </span>
            </th>
            <th>Status</th>
          </tr>
        </thead>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;"></th>

                <th colspan="4" style="text-align: left;"></th>

            </tr>
        </tfoot>
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


@section('javascript')
<script type="text/javascript">


var order = 1;
  var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    // $value = $('#sortbyvalue').val(order);
    // $value2 = $('#sortbyparam').val(column_name);

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

      $('#from_date').datepicker('setDate', startDateFrom);
      $('#to_date').datepicker('setDate', 'today');
    @endif
});
  $(function(e){
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
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('#to_date').change(function() {
      var date = $('#to_date').val();
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('quotation').style.display = "block";
    });

    $('.reset').on('click',function(){
      $('#customer_id_select').val(null);
      $('#header_customer_search').val('');
      $('#form_id').trigger("reset");
      $('.sort-by-value').val(2).change();
      $('.selecting-customer-group').val("").change();
    });

    var table2 = $('.table-quotation').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      // "language": {
      // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      searching:true,
      serverSide: true,
      "lengthMenu": [100,200,300,400],
      // dom: 'ftipr',
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 1,2,3,4,6,7,8] },
        { className: "dt-body-right", "targets": [5] }
      ],
      scrollX: true,
      scrollY : '90vh',
    scrollCollapse: true,
      ajax:{
        beforeSend: function(){
          $('#loader_modal').modal('show');
        },
        url:"{!! route('get-completed-quotation') !!}",
        // data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.selecting_customer = $('.selecting-customer option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() ,data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val()} ,
        data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_sale = $('.selecting-sale option:selected').val(),data.selecting_customer_group = $('.selecting-customer-group option:selected').val(), data.className = className,
          data.sortbyparam = column_name,
            data.sortbyvalue = order
        } ,
      },
      columns: [
          { data: 'checkbox', name: 'checkbox' },
          // { data: 'action', name: 'action' },
          { data: 'ref_id', name: 'ref_id' },
          { data: 'sales_person', name: 'sales_person' },
          { data: 'customer_ref_no', name: 'customer_ref_no' },
          { data: 'customer', name: 'customer' },
          // { data: 'invoice_date', name: 'invoice_date' },
          { data: 'total_amount', name: 'total_amount' },
          { data: 'delivery_date', name: 'delivery_date' },
          // { data: 'target_ship_date', name: 'target_ship_date' },
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
        },
        // drawCallback: function(){
        // },
        "fnDrawCallback": function() {
          $('#loader_modal').modal('hide');

        var api = this.api()
        var json = api.ajax.json();
        var total = json.post;
         // total = total.toFixed(2);
         total = parseFloat(total).toFixed(2);
          total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
          $( api.column( 0 ).footer() ).html('Order Total For All Entries');
            $( api.column( 7 ).footer() ).html(total);
        // alert(total);
    }
   });

    $('.dataTables_filter input').unbind();
$('.dataTables_filter input').bind('keyup', function(e) {
if(e.keyCode == 13) {
  //  alert($(this).val());
        table2.search($(this).val()).draw();
}
});

    $('.selecting-sale').on('change', function(e){
      if($('.sort-by-value option:selected').val() == 5){
        $('.table-unfinish-quotations').DataTable().ajax.reload();
        document.getElementById('unfinish-quotation').style.display = "block";
        document.getElementById('quotation').style.display = "none";
      }
      else{
      $('.table-quotation').DataTable().ajax.reload();
      document.getElementById('unfinish-quotation').style.display = "none";
      document.getElementById('quotation').style.display = "block";
      }
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
   $('.cancel-quotations').removeClass('d-none');
   $('.revert-quotations').removeClass('d-none');
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
          success:function(result){
            if(result.success == true)
            {
              toastr.success('Success!', 'Orders Cancelled Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.cancel-quotations').addClass('d-none');
               $('.revert-quotations').addClass('d-none');
              $('.check-all1').prop('checked',false);
             window.location.href = "{{ url('sales/get-cancelled-orders')}}";

            }
            if(result.success == false)
            {
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.cancel-quotations').addClass('d-none');
               $('.revert-quotations').addClass('d-none');
              $('.check-all1').prop('checked',false);
            }

            if(result.msg != null){
              $('.not-cancelled-alert').html(result.msg);
              $('.not-cancelled-alert').removeClass('d-none');
              $('.cancel-quotations').addClass('d-none');
               $('.revert-quotations').addClass('d-none');
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
          success:function(result){
            if(result.success == true)
            {
              toastr.success('Success!', 'Orders Reverted Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.revert-quotations').addClass('d-none');
              $('.cancel-quotations').addClass('d-none');
              $('.check-all1').prop('checked',false);
             // window.location.href = "{{ url('/sales')}}";
            }
            if(result.success == false)
            {
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.cancel-quotations').addClass('d-none');
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
      url:"{{ route('accounting-fetch-customer') }}",
      method:"POST",
      data:{query:query, _token:_token},
      beforeSend:function(){
        $('#loader__custom_search').removeClass('d-none');
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
      $('#className').val(className);

    $('.table-quotation').DataTable().ajax.reload();

    // $(".customer_id").val(li_id);


});
  $(document).on('click', function (e) {
    if($("#myIddd").is(":visible")){
        $("#myIddd").empty();
        $('.custom__search_arrows').addClass('fa-caret-down');
        $('.custom__search_arrows').removeClass('fa-caret-up');
    }
   })
</script>
@stop
