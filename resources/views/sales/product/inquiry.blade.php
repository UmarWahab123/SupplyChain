@extends('sales.layouts.layout')

@section('title','Products Management | Sales')

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
    <h3 class="maintitle text-uppercase fontbold">Inquiry Products</h3>
  </div>

</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
          <table class="table entriestable table-bordered table-inquiry-products text-center">
              <thead>
                  <tr>
                    <th>{{$global_terminologies['our_reference_number']}}</th>
                    <th width="20%">{{$global_terminologies['product_description']}}</th>
                    <th>{{$global_terminologies['pieces']}} </th>
                    <th>{{$global_terminologies['qty']}}</th>
                    <th>Default Price</th>
                    <th>Added By</th>
                    <th>Quotation</th>
                  </tr>
              </thead>

          </table>
        </div>
        </div>

  </div>
</div>

</div>
<!--  Content End Here -->

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
        $(this).datetimepicker({
            timepicker:false,
            format:'Y-m-d'});
    });
     $('.table-inquiry-products').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
        serverSide: true,
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
         "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,5,6 ] },
    { className: "dt-body-right", "targets": [4] }
  ],
        ajax: "{!! route('get-inquiry-products') !!}",
        columns: [
           { data: 'reference_no', name: 'reference_no' },
            { data: 'short_desc', name: 'short_desc' },
            { data: 'pieces', name: 'pieces' },
            { data: 'qty', name: 'qty' },
            { data: 'default_price', name: 'default_price' },
            { data: 'added_by', name: 'added_by' },
            { data: 'quotation_no', name: 'quotation_no' },
        ]
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });
</script>

@stop

