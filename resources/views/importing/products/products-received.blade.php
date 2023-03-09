@extends('importing.layouts.layout')
@section('title','Products Recieving')
<?php
use Carbon\Carbon;
?>

@section('content')
<style type="text/css">

button.buttons-columnVisibility:not(.active){
  /*background-color: red;*/
  background:#aaa;
  box-shadow: 0px 0px 5px 0px #eee inset;
  opacity: 0.5;
}
</style>
{{-- Content Start from here --}}
<div class="right-content pt-0">
  <div class="row headings-color mb-3">
   <input type="hidden" name="id" id="po_group_id" value="{{$id}}">
    <div class="col-lg-3 d-flex align-items-center">
      <h4>Group No {{$po_group->ref_id}}<br>Product Received Records</h4>
    </div>  
    <div class="col-lg-6"></div>

    <div class="col-lg-3 d-flex align-items-center bg-white p-2">
      <table class="table table-bordered mb-0">
        <tbody>
          <tr>
            <th>AWB:B/L</th>
            <td>{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : 'N.A'}}</td>
          </tr>
          <tr>
            <th>Courier</th>
            <td>{{$po_group->po_courier != null ? $po_group->po_courier->title :" N.A"}}</td>
          </tr>
          <tr>
            <th>Note</th>
            <td>N.A</td>
          </tr>
        </tbody>
        </table>
    </div>
  </div>

<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 p-0">
        <div class="bg-white table-responsive p-3">
          <table class="table headings-color entriestable text-center table-bordered product_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>Po No.</th>
               <th>{{$global_terminologies["suppliers_product_reference_no"]}}</th>
               <th>Supplier</th>
               <th>{{$global_terminologies["our_reference_number"]}}</th>
               <th>{{$global_terminologies['product_description']}}</th>
               <th>Buying<br> Unit</th>
               <th>{{$global_terminologies['qty']}} <br>Ordered</th>
               <th>{{$global_terminologies['qty']}} <br>Inv</th>
               <th>Total <br>Gross <br>Weight</th>
               <th>Total <br>Extra <br>Cost</th>
               <th>{{$global_terminologies['purchasing_price']}}</th>
               <th>Currency <br>Conversion <br>Rate</th>
               <th>{{$global_terminologies['purchasing_price']}} <br>in THB</th>
               <th>Total</th>
               <th>Import <br>Tax <br>(Book)%</th>
               <th>{{$global_terminologies["freight_per_billed_unit"]}}</th>
               <th>{{$global_terminologies["landing_per_billed_unit"]}}</th>
               <th>Book % Tax</th>
               <th>Weighted %</th>
               <th>Actual<br> Tax</th>
               <th>Actual<br> Tax %</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>

  <div class="row headings-color">
    <div class="col-lg-9 d-flex align-items-center fontbold">
    </div>
    <div class="col-lg-3 input-group">
      <table class="table table-bordered bg-white mb-0">
        <tr>
          <th>Actual Tax</th>
          <td>{{$po_group->tax != null ? $po_group->tax :'N.A'}}</td>
        </tr>
        <tr>
          <th>Freight Cost</th>
          <td>{{$po_group->freight != null ? $po_group->freight :'N.A'}}</td>
        </tr>
        <tr>
          <th>Landing Cost</th>
          <td>{{$po_group->landing != null ? $po_group->landing :'N.A'}}</td>
        </tr>
      </table>       
    </div>
  </div>

    <div class="row mb-3">
      <div class="col-lg-6">
        <div class="pt-2 pb-3 pr-3 pl-3 h-100">
          <table class="my-tablee dot-dash bg-white">
           <thead class="sales-coordinator-thead ">
             <tr>                
              <th>User  </th>
              <th>Date/time </th>
              <th>Product </th>
              <th>Column </th>
              <th>Old Value</th>
              <th>New Value</th>
             </tr>
           </thead>
           <tbody>
            @if($product_receiving_history->count() > 0)
            @foreach($product_receiving_history as $history)
            <tr>
              <td>{{$history->get_user->name}}</td>
              <td>{{Carbon::parse(@$history->created_at)->format('d-M-Y, H:i:s')}}</td>                 
              <td>{{$history->get_pod->product->name}}</td>
              <td>{{$history->term_key}}</td>
              <td>{{$history->old_value == null ? 0 : $history->old_value}}</td>                 
              <td>{{$history->new_value}}</td>                 
            </tr>                 
            @endforeach  
            @else
            <tr>
              <td colspan="6"><center>No Data Available in Table</center></td>
            </tr> 
            @endif
           </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
</div>

@endsection
 @php
      $hidden_by_default = '';
 @endphp

@section('javascript')
<script type="text/javascript">
$(function(e){  
 
  var id = $('#po_group_id').val();
    
  $('.product_table').DataTable({
     processing: true,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    dom: 'Blfrtip',
    colReorder: {
      realtime: false
    },
    columnDefs: [
      { targets: [{{$hidden_by_default}}], visible: false },
      { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,13] },
    { className: "dt-body-right", "targets": [8,9,10,11,12,14,15,16,17,18,19] }
    ],
    buttons: [
      {
        extend: 'colvis',
        columns: ':not(.noVis)',
      }
    ],
    ajax:"{{ url('importing/get-details-of-completed-po')}}"+"/"+id,
    columns: [
        { data: 'po_number', name: 'po_number' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'supplier', name: 'supplier' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'desc', name: 'desc' },
        { data: 'kg', name: 'kg' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'qty', name: 'qty' },
        { data: 'pod_total_gross_weight', name: 'pod_total_gross_weight' },
        { data: 'pod_total_extra_cost', name: 'pod_total_extra_cost' },
        { data: 'buying_price', name: 'buying_price' },
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
        { data: 'buying_price_in_thb', name: 'buying_price_in_thb' },
        { data: 'total_buying_price', name: 'total_buying_price' },
        { data: 'import_tax_book', name: 'import_tax_book' },
        { data: 'freight', name: 'freight' },
        { data: 'landing', name: 'landing' },
        { data: 'book_tax', name: 'book_tax' },
        { data: 'weighted', name: 'weighted' },
        { data: 'actual_tax', name: 'actual_tax' },
        { data: 'actual_tax_percent', name: 'actual_tax_percent' },
    ],
      initComplete: function () {
        // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        }); 
      },
  });
});
</script>
@stop

