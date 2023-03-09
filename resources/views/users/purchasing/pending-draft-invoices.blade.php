@extends('users.layouts.layout')

@section('title','Draft Invoices Management | Purchasing')

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
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Pending Draft Invoices</h3>
  </div>
  <div class="col-md-4">
  <div class="d-flex dropdown justify-content-end show">
    <a class="btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Pending Draft Invoices
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
      <a class="dropdown-item" href="{{ route('list-draft-invoices') }}">Draft Invoices</a>
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
                      <th>Ref ID</th>
                      <th>Customer</th>
                      <th>Number of Products</th>
                      <th>Payment Term</th>
                      <th>Invoice Date</th>
                      <th>Total</th>
                  </tr>
              </thead>
               
          </table>
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
        ajax: "{{ route('get-all-pending-draft-invoices') }}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer', name: 'customer' },
            { data: 'number_of_products', name: 'number_of_products' },
            { data: 'payment_term', name: 'payment_term' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' }
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

