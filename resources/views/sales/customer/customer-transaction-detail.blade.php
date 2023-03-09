@extends('sales.layouts.layout')

@section('title','Transaction Detail | Supply Chain')

@section('content')
<style type="text/css">
  a
  {
    cursor:pointer;
  }
</style>
<?php
  $customer_id = $customer->id;
?>
<!-- Right Content Start Here -->
<div class="row d-flex align-items-center left-right-padding mb-2 form-row">
  <h4>Customer: {{$customer->reference_name}}</h4>
</div>


  <div class="row d-flex align-items-center left-right-padding mb-2 form-row">
    <div class=" col-lg">
      <h4 class="custom-customer-list">Account Transactions</h4>
    </div>
 

    <div class="col-xl-2 col-lg-3 col-md-4 ml-lg-auto ml-md-auto" ></div>
    <div class="col-xl-2 col-lg-3 col-md-4"></div>
  </div>

<div class="row d-flex align-items-center left-right-padding form-row ">
    
      
      <div class="col-2">
        <div class="form-group">
          <label>Select Customer</label>
          <select class="form-control selecting-customer-t state-tags">
              <option value="">-- Customers --</option>
              @foreach($customers as $customer)
              <option value="{{$customer->id}}" {{ ( $customer->id == $customer_id) ? 'selected' : '' }}>{{$customer->reference_name}}</option>
              @endforeach
          </select>
        </div>
      </div>

      <div class="col-2">

        <div class="form-group mr-2">
          <label>Select Order</label>
          <input type="text" class="form-control" id="order_no_t" placeholder="Order#" name="text2">
        </div>

      </div>

    <div class="col-2">

      <div class="form-group">
        <label>From Date</label>
          <input type="text" placeholder="From Date" name="from_date_t" class="form-control font-weight-bold" id="from_date_t" autocomplete="off">
      </div>

    </div>
    
    <div class="col-2">
    
      <div class="form-group">
        <label>To Date</label>
          <input type="text" placeholder="To Date" name="to_date_t" class="form-control font-weight-bold" id="to_date_t" autocomplete="off">
      </div>

    </div>

    <!-- <div class="col-2">
        <div class="form-group">
        <label></label>
        <input type="button" value="Reset" class="btn recived-button reset-btn-t">
        </div>
      </div> -->
  <div class="input-group-append ml-3">
       <!--  <button class="btn recived-button reset" type="reset"> Reset         </button>   -->
        <span class="reset-btn-t common-icons mt-2" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
  </div>

<div class="entriesbg bg-white custompadding customborder">

<div class="table-responsive ">
  <table class="table entriestable table-bordered text-center invoices-table ">
        <thead>    

            <tr>
              <th>Invoice#</th>
              <th>Reference<br>Name</th>
              <th>Reference<br>Number</th>
                <th>Invoice Total</th>
                <th>Total Paid</th>
                <th>Payment<br>method</th>
                <th>Payment<br>reference</th>
                <th>Received<br>Date</th>
            </tr>
        </thead>        
    </table>
</div>
</div>
<!-- export pdf form starts -->
     <!--  <form class="export-payment-receipt" method="post" action="{{url('export-payment-receipt-pdf')}}">
        @csrf
       <input type="hidden" name="payment_id" class="payment_id">
      </form> -->
    <!-- export pdf form ends -->



@endsection

@section('javascript')
<script type="text/javascript">

  $("#from_date_t").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date_t").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $(document).ready(function(){
  $(document).on('click','.download_transaction',function(){
    var id = $(this).data('id');
    $('.payment_id').val(id);
    // $('.export-payment-receipt')[0].submit();
var payment_id = id;
     var url = "{{url('sales/export-payment-receipt-pdf')}}"+"/"+payment_id;
          window.open(url, 'Orders Receipt Print', 'width=1200,height=600,scrollbars=yes');
  });
    $(".state-tags").select2({dropdownCssClass : 'bigdrop'});


    $('.invoices-table').DataTable({
      "sPaginationType": "listbox",
       processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      "searching": false,
      "lengthMenu": [50,100,200,300,400],
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      "orderable": false,
      columnDefs: [
          { className: "dt-body-left", "targets": [ 0,1,2,5,6 ] },
          { className: "dt-body-right", "targets": [ 3,4,7] },

      ],

      ajax:{
          url:"{!! route('get-payment-ref-invoices-for-receivable') !!}",
          data: function(data) { data.from_date = $('#from_date_t').val(),data.to_date = $('#to_date_t').val(), data.selecting_customer = $('.selecting-customer-t option:selected').val(),data.order_no = $('#order_no_t').val()},
        },

      columns: [
        { data: 'ref_no', name: 'ref_no' },
        { data: 'reference_name', name: 'reference_name' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'invoice_total', name: 'invoice_total' },
        { data: 'total_paid', name: 'total_paid' },
        { data: 'payment_type', name: 'payment_type' },
        { data: 'payment_reference_no', name: 'payment_reference_no' },
        { data: 'received_date', name: 'received_date' },
      ],

      initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');

      $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
      }
    });




   

    $('#from_date_t').change(function() {
      $('.invoices-table').DataTable().ajax.reload();
    });

    $('#to_date_t').change(function() {
      $('.invoices-table').DataTable().ajax.reload();
    });

    $('.selecting-customer-t').on('change', function(e){
      $('.invoices-table').DataTable().ajax.reload();
    });


    $(document).on('keyup' , '#order_no_t' ,function(e){
      if(event.which == 13)
      {
        $('.invoices-table').DataTable().ajax.reload();
      }
    });

  
    

 

    $('.reset-btn-t').on('click',function(){
      $('#order_no_t').val('');
      // $('.selecting-customer-t').val('');
      $('#from_date_t').val('');
      $('#to_date_t').val('');
      $(".state-tags").select2("", "");

      $('.invoices-table').DataTable().ajax.reload(); 
    });

});
</script>
@stop