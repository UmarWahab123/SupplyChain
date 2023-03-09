@extends('backend.layouts.layout')

@section('title','Product Sales Report | Supply Chain')

@section('content')

<style type="text/css">

.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}
.selectDoubleClick, .inputDoubleClick{
    font-style: italic;
}
.dt-buttons
{
  display: inline-block;
  float: left;
  margin-right: 5px;
}
.dataTables_scrollFootInner
{
  width: 100% !important;
}
</style>

<div class="row">
  <div class="col-md-12">
    <a href="{{ url()->previous() }}" class="float-left pt-3">
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>
    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11)
          <li class="breadcrumb-item"><a href="{{route('sales')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 2)
          <li class="breadcrumb-item"><a href="{{route('purchasing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 5)
          <li class="breadcrumb-item"><a href="{{route('importing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 6)
          <li class="breadcrumb-item"><a href="{{route('warehouse-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 7)
          <li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 10)
          <li class="breadcrumb-item"><a href="{{route('roles-list')}}">Home</a></li>
        @endif
        <li class="breadcrumb-item"><a href="{{route('margin-report-10')}}">Margin Report by Supplier</a></li>
          <li class="breadcrumb-item active">Margin Report by Supplier Detail</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-8">
    <h5 class="maintitle text-uppercase fontbold">{{@$supplier->reference_name}} Margin Report Detail</h5>
  </div>
  <div class="col-md-4">
  <!-- <div class="pull-right">
    <span class="export-btn vertical-icons mr-4" title="Create New Export">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span>
  </div> -->
</div>
</div>

<div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is being prepared! Please wait.. </b>
</div>
<div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
 <b> Export file is already being prepared by another user! Please wait.. </b>
</div>
<div class="alert alert-success export-alert-success d-none"  role="alert">
<i class=" fa fa-check "></i>

  <b>Export file is ready to download.
  <!-- <a download href="{{'storage/app/Completed-Product-Report.xlsx'}}"><u>Click Here</u></a> -->
    <a class="exp_download" href="{{asset('storage/app/'.@$file_name->file_name)}}" target="_blank" id="export-download-btn"><u>Click Here</u></a>
  </b>
</div>

<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered text-center product-sales-report">

        <thead>
          <tr>
            <th>Order #</th>
            <th>PF#</th>
            <th>Quantity</th>
            <th>Sales</th>
            <th>Unit Cost</th>
            <th>Total Cost</th>
            <th>Out Against Shipment #</th>
            <th>Out Against PO #</th>
          </tr>
        </thead>
        <tfoot align="right">
          <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
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

<form id="Form-export-margin-report-by-office">
  @csrf
  <input type="hidden" name="primary_filter_exp" id="primary_filter_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="sortbyparam" id="sortbyparam">
  <input type="hidden" name="sortbyvalue" id="sortbyvalue">
</form>

@endsection

@section('javascript')

<script type="text/javascript">
    // Columns Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.product-sales-report').DataTable().ajax.reload();

    if($(this).data('order') ==  '2')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == '1')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });



  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });


  var date = new Date();
date.setDate( date.getDate() - 30 );

$("#from_date").datepicker('setDate',date);
$("#to_date").datepicker('setDate','today');

  // $("#from_date").datepicker('');


  var date = $('#from_date').val();
    $("#from_date_exp").val(date);
  var date = $('#to_date').val();
    $("#to_date_exp").val(date);

  $(function(e){
    $(".state-tags").select2();
    var table2 = $('.product-sales-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      searching:true,
      serverSide: true,
      // "aaSorting": [[3]],
      bSort: false,
      info: true,
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 0, 1, 6, 7 ] },
        { className: "dt-body-right", "targets": [ 2, 3, 4, 5] },
      ],
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      dom: 'Bfrtip',
      buttons: [

      ],
      lengthMenu: [ 100,200,300,400,500],
      ajax: {
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        url:"{!! route('get-margin-report-12') !!}",
        data: function(data) {data.supplier_id = "{{$id}}", data.type = 'datatable',data.from_date = "{{$from_date}}", data.to_date = "{{$to_date}}"},
        method: "get",
      },
      columns: [
        { data: 'order#', name: 'order#' },
        { data: 'product', name: 'product' },
        { data: 'quantity', name: 'quantity' },
        { data: 'sales', name: 'sales' },
        { data: 'unit_cost', name: 'unit_cost' },
        { data: 'cost', name: 'cost' },
        { data: 'shipment_no', name: 'shipment_no' },
        { data: 'po_no', name: 'po_no' },
      ],
      initComplete: function () {
        // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
      footerCallback: function ( row, data, start, end, display ) {
        var api = this.api();
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"get",
          dataType:"json",
          url:"{{ route('get-margin-report-12') }}",
          data:{ supplier_id: "{{$id}}", type: 'footer',from_date : "{{$from_date}}", to_date : "{{$to_date}}" },
          beforeSend:function(){
          },
          success:function(result){
            var sales = result.sales;
            var cost = result.cost;
            var quantity = result.quantity;

             // total = total.toFixed(2);
            sales  = parseFloat(sales).toFixed(2);
            sales  = sales.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            cost = parseFloat(cost).toFixed(2);
            cost = cost.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            quantity = parseFloat(quantity).toFixed(2);
            quantity = quantity.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            $( api.column( 0 ).footer() ).html('Total');
            $( api.column( 2 ).footer() ).html(quantity);
            $( api.column( 3 ).footer() ).html(sales);
            $( api.column( 5 ).footer() ).html(cost);
          },
          error: function(){

          }
        });
      },
   });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13)
      {
        table2.search($(this).val()).draw();
      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });

</script>
<script src="{{asset("public\site\assets\backend\js\margin-report.js")}}"></script>
@stop
