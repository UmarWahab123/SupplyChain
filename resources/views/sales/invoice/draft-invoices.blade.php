@extends('sales.layouts.layout')

@section('title','Draft Invoices Management | Vendor')

@section('content')
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
<div class="align-items-center mb-3 row">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Draft Invoices</h3>
  </div>

  <div class="col-md-4">
  <div class="d-flex dropdown justify-content-end show">
    <a class="btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Draft Invoices <i class="fa fa-angle-down" aria-hidden="true"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
      <a class="dropdown-item" href="{{route('completed-quotations')}}">Quotations</a>
      <a class="dropdown-item" href="{{route('pending-quotations')}}">@if(!array_key_exists('unfinished_quotation', $global_terminologies)) Unfinished Quotation @else {{$global_terminologies['unfinished_quotation']}} @endif s</a>
    </div>
  </div>
  </div>

  
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
          <table class="table entriestable table-bordered table-invoice text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Order#</th>
                      <th>Company</th>
                      <th>Customer#</th>
                      <th>Date Purchase</th>
                      <th>Order Total</th>
                      <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
                      <th>Status</th>
                  </tr>
              </thead>
               
          </table>
        </div>  
    
  </div>
</div>

</div>
<!--  Content End Here -->

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





@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){
     $('.table-invoice').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: false,
        dom: 'ftipr',
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax: "{{ route('get-draft-invoices') }}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'target_ship_date', name: 'target_ship_date' },
            { data: 'status', name: 'status' },
        ]
   });

     $(document).on('click', '.tickIcon', function(e){
        $('.inv-id').val($(this).data('id'));
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

