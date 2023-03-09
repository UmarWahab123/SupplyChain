@extends('ecom.layouts.layout')
@section('title','Dashboard')
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
          <li class="breadcrumb-item active">My Invoice</li>
      </ol>
  </div>
</div>

<!-- Right Content Start Here -->
<div class="right-contentIn">
{{-- @if(Auth::user()->role_id != 9) --}}
<!-- upper section start -->
<div class="row mb-3">
<!-- left Side Start -->
<div class="col-lg-12">
<!-- 1st four box row start -->
<div class="row mb-3">
  @include('ecom.layouts.dashboard-boxes')
</div>
<!-- first four box row end-->
</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>
{{-- @endif --}}

<div class="row mb-3 headings-color">

<div class="col-lg-9">
  @if(Auth::user()->role_id == 9)
  <h4>My Invoices</h4>
  @else
  <h4>All Invoices</h4>
  @endif
</div>

<div class="col-lg-12 col-md-12">
  <form id="form_id">
  <div class="row">








 <!--    <div class="col-lg col-md-6">
      <div class="form-group">
        <select class="form-control selecting-customer">
            <option value="">-- Customers --</option>
            {{-- @foreach($customers as $customer) --}}
            {{-- <option value="{{$customer->id}}">{{$customer->reference_name}}</option> --}}
            {{-- @endforeach --}}
        </select>
      </div>
    </div> -->

    {{-- <div class="col-lg col-md-6" id="Choose-customer-1">
      <label><b>Customer</b></label>
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer" name="customer" required="true">
            <option value="">Choose Customer</option>
            @foreach($customers as $customer)
            <option value="{{$customer->id}}">{{@$customer->reference_name}}</option>
            @endforeach
        </select>
      </div>
    </div> --}}


{{-- <div class="col-lg col-md-6" id="customer-group-1">
  <label><b>Customer Group</b></label>
      <div class="form-group">
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-customer-group" name="customer" required="true">
            <option value="">@if(!array_key_exists('customer_group', $global_terminologies))Customer Group @else {{$global_terminologies['customer_group']}} @endif</option>
            @foreach($customer_categories as $cat)
            <option value="{{$cat->id}}">{{@$cat->title}}</option>
            @endforeach
        </select>
      </div>
    </div> --}}
    {{-- <div class="col-lg col-md-6" id="sale-person-1">
      <label><b>Sale Person</b></label>
      <div class="form-group">
        <select class="form-control state-tags selecting-sale">
            <option value="">-- Sale person --</option>
           @foreach($sales_persons as $person)
            <option value="{{$person->id}}" {{Auth::user()->role_id == 3 && $person->id == Auth::user()->id ? 'selected' : ''}}>{{$person->name}}</option>
            @endforeach
        </select>
      </div>
    </div> --}}

   {{--  <div class="col-lg col-md-6">
      <label><b>From Delivery Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
    </div>
  </div>

    <div class="col-lg col-md-6">
      <label><b>To Delivery Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
    </div>
  </div> --}}

    <div class="col-lg-2 col-md-6">
        <label><b>From Delivery Date</b></label>
        <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
        </div>
    </div>

    <div class="col-lg-2 col-md-6">
        <label><b>To Delivery Date</b></label>
        <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
        </div>
    </div>

    <div class="col-lg-2 col-md-4">
        <label><b style="visibility: hidden;">Reset</b></label>
        <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button">Apply Dates</button>  -->
        <span class="apply_date common-icons mr-4" title="Apply Date">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>
        <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
        </div>
    </div>

    <div class="col-lg col-md-4 ml-md-auto mr-md-auto pb-md-3" id="reset-1">
      <label><b style="visibility: hidden;">Reset</b></label>
    <div class="input-group-append ml-3">
          <!--     <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span> -->
    </div>
  </div>

  </div>
    {{-- <div class="row"> --}}
     {{-- <div class="col-lg-2 col-md-6">
      <label><b>From Delivery Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg-2 col-md-6">
      <label><b>To Delivery Date</b></label>
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg-2 col-md-4">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <button class="btn recived-button apply_date" type="button">Apply Dates</button>
      </div>
    </div> --}}
  {{-- </div> --}}
  </form>
</div>


<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
  <div class="alert alert-danger d-none not-cancelled-alert col-lg-12 ml-3">

</div>
  @if(Auth::user()->role_id != 7)
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm cancel-quotations
      deleteIcon" title="Cancel"><span><i class="fa fa-times" ></i></span></a>

       <a href="javascript:void(0);" class="btn selected-item-btn btn-sm proceed-invoice d-none" data-type="quotation" title="Proceed" >
    <img src="{{asset('public/menu-icon/proceed.png')}}" alt="" width='15' class="img-fluid"></a>
  </div>
  @endif
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
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
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Payment Image</th>
            <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reference_name">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reference_name">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <!-- <th>Date Purchase</th> -->
            <th>Discount</th>
            <th>Order Total
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="order_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="order_total">
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
            <th>Type</th>
            <th>Note</th>
            <th>Status</th>
          </tr>
        </thead>
           <tfoot>
          <tr>
            <th id="total"></th>
            <th id="total_val_td" style="margin-left: 30px"></th>

          </tr>
        </tfoot>
      </table>
     {{--<table id="footer_table" width="100%" class="table-bordered mt-2">
        <tbody>
            <td colspan="5" style="text-align: right; padding:5px 15px 5px; font-weight: bold; font-size: 1.1rem;">Order Total For All Entries</td>
            <td id="total_val_td" style="padding:5px 0 5px 15px; font-weight: bold; font-size:1.1rem;"></td>
        </tbody>
    </table>--}}
    </div>
  </div>
</div>
</div>

<!-- Modal For Note -->
<div class="modal" id="proceed_modal">
<div class="modal-dialog">
  <div class="modal-content">

    <!-- Modal Header -->
    <div class="modal-header">
      <h4 class="modal-title">Proceed Order</h4>
      <button style="background-color: #FD8585" type="button" class="close close-btn" data-dismiss="modal">&times;</button>
    </div>

    <!-- Modal body -->
    <form role="form" class="add-sup-note-form" enctype="multipart/form-data">
    <div class="modal-body">
      <div class="row">
        <div class="col-md-12">
          <div class="row">
            <div class="col-xs-12 col-md-12">
              {{--<div class="form-group">
                <label>Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" placeholder="Note Title" name="note_title">
              </div>--}}

              <div class="form-group">
                <label>Order Detail <span class="text-danger">*</span> <small>(190 Characters Max)</small></label>
                <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="190"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal footer -->
    <div class="modal-footer">
      <input type="hidden" name="supplier_id_note" class="note-supplier-id">
      <button class="btn btn-success" type="submit" class="save-btn" >Save</button>
      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div>
   </form>

  </div>
</div>
</div>

<div class="modal" id="images-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Payment Image</h4>
          <button style="background-color: #FF5A5A" type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="fetched-images">
          </div>
        </div>
        <div class="modal-footer">
         <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
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

<!-- main content end here -->
</div>
@endsection

@php
$hidden_by_default = '';
@endphp

@section('javascript')

<style>
.button {
  border: none;
  color: white;
  padding: 7px 14px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  transition-duration: 0.4s;
  cursor: pointer;
}

.button1 {
  background-color: white;
  color: black;
  border: 2px solid #4CAF50;
}

.button1:hover {
  background-color: #4CAF50;
  color: white;
}

.button2 {
  background-color: white;
  color: black;
  border: 2px solid #F94444;
}

.button2:hover {
  background-color: #E82E2E;
  color: white;
}

</style>
<script type="text/javascript">

 // Customer Sorting Code Here
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
   $(document).on('click','.cancel_icon',function(){
      alert('cancel button clicked');

  });
   $(document).on('click','.cancel_icon',function(){
      alert('cancel button clicked');

  });

  $(document).on('click','.proceed_icon',function(){
    var supplier_id = $(this).data('id');
    $('.note-supplier-id').val(supplier_id);
  });


    $(document).on('click', '.payment_img', function(e){
    let sid = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-payment-image') }}",
      data: 'prod_id='+sid,
            success: function(response){
        $('.fetched-images').html(response);
      }
    });
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

      $('#from_date').datepicker('');
      $('#to_date').datepicker('');
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
      $('#form_id').trigger("reset");
      $('.sort-by-value').val(2).change();
      $(".state-tags").select2("destroy");
      $(".state-tags").select2();
      $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
      $("#loader_modal").modal('show');
      $(".state-tags").select2("", "");
      $('.table-quotation').DataTable().ajax.reload();
    });

    var table2 = $('.table-quotation').DataTable({
      "sPaginationType": "listbox",
      processing: false,
    //   colReorder: {
  //      realtime: false,
  //    },
    //   dom: 'lrtip',
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      searching:true,
      serverSide: true,
    //   dom: 'Blfrtip',
      "lengthMenu": [100,200,300,400],
      // dom: 'ftipr',
    //   buttons: [
    //     {
    //       extend: 'colvis',
    //       columns: ':not(.noVis)',
    //     }
    //   ],
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
        },
        url:"{!! route('get-invoices-ecom') !!}",
        data: function(data) { data.dosortby = $('.sort-by-value option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(), data.sort_order = order, data.column_name = column_name} ,
      },
      columns: [
          { data: 'checkbox', name: 'checkbox' },
          // { data: 'action', name: 'action' },
          { data: 'ref_id', name: 'ref_id' },
          {
            data: 'sales_person', name: 'sales_person'
              },
          { data: 'customer', name: 'customer' },
          { data: 'discount', name: 'discount' },
          { data: 'total_amount', name: 'total_amount' },
          { data: 'delivery_date', name: 'delivery_date' },
          { data: 'due_date', name: 'due_date' },
          { data: 'order_type', name: 'order_type' },
          { data: 'order_note', name: 'order_note' },

          { data: 'status', name: 'status' },
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
        },
        // drawCallback: function(){
        // },
        "fnDrawCallback": function() {
          $('#loader_modal').modal('hide');
        },

      footerCallback: function ( row, data, start, end, display ) {
        var api = this.api()
        var json = api.ajax.json();
        var total = json.post;
         // total = total.toFixed(2);
         total = parseFloat(total).toFixed(2);
          total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
          // $( api.column( 0 ).footer() ).html('Order Total For All Entries');
          //   $( api.column( 7 ).footer() ).html(total);
        // alert(total);
        // $('#total_val_td').html(total);
         $('#total').html('Orders Total');
        $('#total_val_td').html(total);
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
        $('#loader_modal').modal('show');
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
    // alert('proceed to invoice');

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



     $(document).on('click', '.proceed-invoice', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to proceed these Orders???",
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
          url:"{{ route('proceed-invoice-order') }}",
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
              toastr.success('Success!', 'Orders Proceeded Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.revert-quotations').addClass('d-none');
              $('.cancel-quotations').addClass('d-none');
              $('.proceed-invoice').addClass('d-none');
              $('.check-all1').prop('checked',false);
              window.location.reload();
             // window.location.href = "{{ url('/sales')}}";
            }
            if(result.success == false){
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
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


   $(document).on('click', '.cancel-quotations', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to cancel these Orders?",
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
          url:"{{ route('cancel-ecom-order') }}",
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
              toastr.success('Success!', 'Orders Cancelled Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.revert-quotations').addClass('d-none');
              $('.cancel-quotations').addClass('d-none');
              $('.check-all1').prop('checked',false);
                window.location.reload();
             // window.location.href = "{{ url('/sales')}}";
            }
            if(result.success == false){
              toastr.error('Error!', result.msg ,{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
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

  @if(Session::has('successmsg'))
      toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
      @php
       Session()->forget('successmsg');
      @endphp
  @endif
  });
</script>

@stop
