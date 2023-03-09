@extends($layout.'.layouts.layout')


@section('title','Suppliers Management | Supply Chain')

@section('content')
@php
use App\Models\Common\ProductCategory;
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
<div class="row mb-0">
  <div class="col-md-10 title-col">
    <h3 class="maintitle text-uppercase fontbold mb-0 mt-1">SUPPLIERS CENTER</h3>
  </div>
  
  <div class="col-md-2">
    <!-- <a class="btn recived-button" href data-toggle="modal" data-target="#addSupplierModal">
      Add Supplier
    </a> -->
  </div>
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-suppliers text-center">
          <thead>
            <tr>
              <th>Action</th>  
              <th>{{$global_terminologies['company_name']}}</th>
              <th>Country</th>
              <th>Contact Name</th>
              {{--<th>Main Tags</th>--}}
              <th>Supplier Since</th>
              <th>{{$global_terminologies['open_pos']}}</th>
              <th> {{$global_terminologies['total_pos']}}</th>
              <th>Last Order Date</th>
              <th>Note</th>
              <th>Status</th>
            </tr>
          </thead>
      </table>
    </div>  

    </div>
    
  </div>
</div>


</div>
<!--  Content End Here -->



<!-- Loader Modal -->
<div class="modal" id="loader_modal_old" role="dialog">
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
  // adding supplier row on click function

  $(function(e){

    $('.table-suppliers').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        ordering: false,
        serverSide: true,
        "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7 ] }
  ],
        pageLength: {{100}},
        lengthMenu:[100,200,300,400],
        ajax: "{!! route('get-common-supplier-list-data') !!}",
        columns: [
            { data: 'action', name: 'action' },
            { data: 'company', name: 'company' },
            { data: 'country', name: 'country' },
            { data: 'name', name: 'name' },
            // { data: 'product_type', name: 'product_type' },
            { data: 'created_at', name: 'created_at' },
            { data: 'open_pos', name: 'open_pos' },
            { data: 'total_pos', name: 'total_pos' },
            { data: 'last_order_date', name: 'last_order_date' },
            { data: 'notes', name: 'notes' },
            { data: 'status', name: 'status' },
        ],
        initComplete: function () {
          this.api().columns([9]).every(function () {
            var column = this;
            var select = document.createElement("select");
            $(select).append('<option value="">All</option><option value="Active">Active</option><option value="Suspended">Suspended</option>');
            $(select).addClass('form-control');
            $(select).appendTo($(column.header()))
            .on('change', function () {
                column.search($(this).val()).draw();
            });
          });
        }
    });

   

  });
</script>

@stop

