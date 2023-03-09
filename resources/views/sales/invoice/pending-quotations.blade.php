@extends('sales.layouts.layout')

@section('title','Completed Quotations Management | Sales')

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
    <h3 class="maintitle text-uppercase fontbold">@if(!array_key_exists('unfinished_quotation', $global_terminologies)) Unfinished Quotations @else {{$global_terminologies['unfinished_quotation']}} @endif</h3>
  </div>

  <div class="col-md-4">
  <div class="d-flex dropdown justify-content-end show">
    <a class="btn dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    @if(!array_key_exists('unfinished_quotation', $global_terminologies)) Unfinished Quotations @else {{$global_terminologies['unfinished_quotation']}} @endif <i class="fa fa-angle-down" aria-hidden="true"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
      <a class="dropdown-item" href="{{route('completed-quotations')}}">Quotations</a>
      <a class="dropdown-item" href="{{route('draft-invoices')}}">Draft Invoices</a>
    </div>
  </div>
  </div>
  
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
          <table class="table entriestable table-bordered table-pending-quotations text-center">
              <thead>
                  <tr>
                      <th>Action</th>
                      <th>Order#</th>
                      <th>@if(!array_key_exists('company_name', $global_terminologies)) Company @else {{$global_terminologies['company_name']}} @endif</th>
                      <th>Customer #</th>
                      <th>Number of Products</th>
                      <th>Payment Term</th>
                      <th>Invoice Date</th>
                      <th>Total</th>
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
     $('.table-pending-quotations').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: false,
        dom: 'ftipr',
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax: "{{ route('get-pending-quotation') }}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'ref_id', name: 'ref_id' },
            { data: 'customer', name: 'customer' },
            { data: 'customer_ref_no', name: 'customer_ref_no' },
            { data: 'number_of_products', name: 'number_of_products' },
            { data: 'payment_term', name: 'payment_term' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total_amount', name: 'total_amount' },
            { data: 'status', name: 'status' },
        ]
   });

     $(document).on('click', '.tickIcon', function(e){
        $('.inv-id').val($(this).data('id'));
     });

      $(document).on('click', '.deleteIcon', function(){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to void this invoice?",
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
                url:"{{ route('get-completed-quotation') }}",
                beforeSend:function(){
                   $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                      });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                    if(data.success == true){
                      toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                      setTimeout(function(){
                        window.location.reload();
                      }, 2000);
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

