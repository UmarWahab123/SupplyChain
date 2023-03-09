@extends('importing.layouts.layout')
@section('title','Dashboard')


@section('content')

{{-- Content Start from here --}}
<!-- Right Content Start Here -->
<div class="right-contentIn">

<div class="row mb-3 headings-color">

<div class="col-lg-9">
  <h4>Pick Instruction</h4>
</div>

<div class="col-lg-3 mb-3">
      <select class="font-weight-bold form-control-lg form-control orders-status" name="order_status" >
        <option value="" disabled="" >Choose Status</option>
        <option value="all" selected>All</option>
        <option value="10">Delivery</option>
        <option value="9">Importing</option>
        <option value="8">Purchasing</option>
        <option value="7">Selecting Vendors</option>
      </select>
</div>

<div class="row entriestable-row col-lg-12 pr-0 quotation" id="quotation">
  <div class="col-12 pr-0">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered table-quotation text-center">
        <thead>
          <tr>
            <th>Action</th>
            <th>Order#</th>
            <th>Sales Person</th>
            <th>Company</th>
            <th>Customer #</th>
            <th>Date Purchase</th>
            <th>Order Total(THB)</th>
            <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
            <th>Status</th>
          </tr>
        </thead>               
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

<!-- main content end here -->
</div>

@endsection


@section('javascript')
<script type="text/javascript">
$(function(e){  
  $('.table-quotation').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: false,
        lengthMenu:[100, 200, 300, 400],
        dom: 'ftipr',
        "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,7,8] },
    { className: "dt-body-right", "targets": [6] }
  ],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax: 
        {
          url: "{!! route('get-draft-invoices-importing') !!}",
          data: function(data) { data.orders_status = $('.orders-status option:selected').val() } ,
        },
        columns: [
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'user_id', name: 'user_id' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'target_ship_date', name: 'target_ship_date' },
            { data: 'status', name: 'status' },
        ]
   });

  $(document).on('change','.orders-status',function(){
    var selected = $(this).val();
    if($('.orders-status option:selected').val() != '')
    {
      $('.table-quotation').DataTable().ajax.reload();
    }
  });

});
</script>
@stop

