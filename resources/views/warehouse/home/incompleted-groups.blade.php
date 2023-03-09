@extends('warehouse.layouts.layout')
@section('title','Receiving Queue')


@section('content')

{{-- Content Start from here --}}


<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-2 col-md-6">
        <h4 class="mb-0 fontbold mt-2">Receiving Queue</h4>
      </div>
    </div>
    <div class="row mb-3 headings-color">
      <div class="col-lg-2 col-md-2 d-md-none">
        
      </div>
        <div class="col-lg-3 col-md-3" >
          <div class="form-group">
            <input type="date" class="form-control" name="from_date" id="from_date">
          </div>
        </div>

        <div class="col-lg-3 col-md-3" >
          <div class="form-group">
            <input type="date" class="form-control" name="to_date" id="to_date">
          </div>
        </div>
        <div class="col-lg-3 col-md-3" >
           <div class="form-group">
                <select class="form-control product_receiving">
                    <option value="0" selected="true">Open Product Receiving</option>
                    <option value="1">Closed Product Receiving</option>
                </select>
            </div>
        </div>
        <div class="col-lg-1 col-md-3 p-0">
          <div class="input-group-append">
            <button class="btn recived-button reset-btn" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>  
          </div>
        </div>


        <div class="col-lg-12 col-md-12">
        
            <div class="bg-white table-responsive ">
            <div class="p-4">
              <table class="table headings-color entriestable text-center table-bordered product_receiving_table " style="width:100%">
                    <thead class="sales-coordinator-thead table-bordered">
                        <tr>
                            <th>Action</th>
                            <th>Group#</th>
                            <th>POs #</th>
                            <th>Supplier <br> Reference Name</th>
                            <!-- <th>Supplier</th> -->
                            <th>QTY <br>Ordered</th>
                            <th>Net <br>Weight<br> (KG)</th>
                            <th>Issue <br>Date </th>
                            <th> PO Total<br>(THB)</th>
                            <th>Target <br>Received<br> Date</th>
                            <th>Warehouse</th>
                        </tr>
                    </thead>
                </table>
            </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('javascript')
<script type="text/javascript">
$(function(e){

  var table2 = $('.product_receiving_table').DataTable({
   processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  serverSide: true,
  pageLength: {{100}},
  scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
  "columnDefs": [
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,8,9 ] },
    { className: "dt-body-right", "targets": [7] }
  ],
  lengthMenu: [ 100, 200, 300, 400],
  ajax:{
          url:"{!! route('get-warehouse-incompleted-po-groups') !!}",
          data: function(data) { data.dosortby = $('.product_receiving option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
        },

  columns: [
      { data: 'action', name: 'action' },
      { data: 'id', name: 'id' },
      { data: 'po_number', name: 'po_number' },
      { data: 'supplier_ref_no', name: 'supplier_ref_no' },
      // { data: 'supplier', name: 'supplier' },
      { data: 'quantity', name: 'quantity' },
      { data: 'net_weight', name: 'net_weight' },
      { data: 'issue_date', name: 'issue_date' },
      { data: 'po_total', name: 'po_total' },
      { data: 'target_receive_date', name: 'target_receive_date' },
      { data: 'warehouse', name: 'warehouse' },
  ],
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      }); 
    },
});

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  $('.product_receiving').on('change', function(e){
    $('.product_receiving_table').DataTable().ajax.reload();  
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();      
  });

  $('#from_date').change(function() {
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#to_date').change(function() {
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.reset-btn').on('click',function(){
    $('#from_date').val('').change();
    $('#to_date').val('').change();
    $('.product_receiving').val('0').change();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });


  
});
</script>
@stop

