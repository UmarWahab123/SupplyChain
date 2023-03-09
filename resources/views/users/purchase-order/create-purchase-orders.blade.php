@extends('users.layouts.layout')

@section('title','Purchase Order | Supply Chain')

@section('content')
@php
use Carbon\Carbon;
@endphp
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}

</style>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Purchase Orders</h3>
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-po text-center">
          <thead>
            <tr>
              <th>Actions</th>
              <th>PO #</th>
              <th>Supplier</th>
              <th>Supplier Ref#</th>
              <th>Confirm Date</th>
              <th>PO Total</th>
              <th>Target Received Date</th>
              <th>@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif</th>@endif/th>
              <th>Note</th>
              <th>Status</th>
              <th>Customers</th>
            </tr>
          </thead>
      </table>
    </div>  
    </div>
    
  </div>
</div>

</div>

{{-- Customers Images Modal --}}
<div class="modal" id="customer-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order Customers</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">

          <table class="table entriestable table-bordered text-center">
           <thead>
            <tr>
              <th>Sr.#</th>
              <th>Name</th>
            </tr>
           </thead>
           <tbody id="fetched-customers">
            
           </tbody> 
          </table>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
<!--  Content End Here -->

<!-- Loader Modal -->
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

{{-- Content Start from here --}}

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

     $('.table-po').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        ajax: "{!! route('get-purchase-orders-data') !!}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'supplier', name: 'supplier' },
            { data: 'supplier_ref', name: 'supplier_ref' },
            { data: 'confirm_date', name: 'confirm_date' },
            { data: 'po_total', name: 'po_total' },
            { data: 'target_receive_date', name: 'target_receive_date' },
            { data: 'payment_due_date', name: 'payment_due_date' },
            { data: 'note', name: 'note' },
            { data: 'status', name: 'status' },
            { data: 'customer', name: 'customer' },
        ],
         initComplete: function () {
            this.api().columns([]).every(function () {
                var column = this;
                var input = document.createElement("input");
                $(input).addClass('form-control');
                $(input).attr('type', 'text');
                $(input).appendTo($(column.header()))
                .on('change', function () {
                    column.search($(this).val()).draw();
                });
            });
        }
    });
});

  // getting PO Customers
  $(document).on('click', '.show-po-cust', function(e){
    let po_id = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-po-customers') }}",
      data: 'po_id='+po_id,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response){
        $('#loader_modal').modal('hide');
        $('#fetched-customers').html(response.html_string);
        
      }
    });

  });
</script>
@stop