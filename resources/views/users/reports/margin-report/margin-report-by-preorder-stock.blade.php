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

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h5 class="maintitle text-uppercase fontbold">Admin Management & Sales Report</h5>
  </div> 
</div>

@include('users.reports.margin-report.dropdown-boxes')

<div class="row entriestable-row mt-3">
  <div class="col-12">

    <div class="entriesbg bg-white custompadding customborder">
      <table class="table entriestable table-bordered text-center product-preorder-report">
        <thead>
          <tr>
            <th>Preorder</th>
            {{-- <th>{{$global_terminologies['brand']}}</th> --}}
            <th>Sales</th>
            <th>{{$global_terminologies['net_price']}}</th>
            <th>GP</th>
            <th>Margin</th>
          </tr>
        </thead>
        <tfoot align="right">
          <tr>
            <th></th>
            {{-- <th></th> --}}
            <th style="text-align: right;"></th>
            <th style="text-align: right;"></th>
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

<form id="export_sold_product_report_form" method="post">
  @csrf
  <input type="hidden" name="warehouse_id_exp" id="warehouse_id_exp">
  <input type="hidden" name="customer_id_exp" id="customer_id_exp">
  <input type="hidden" name="sales_person_exp" id="sales_person_exp">
  <input type="hidden" name="product_id_exp" id="product_id_exp">
  <input type="hidden" name="prod_category_exp" id="prod_category_exp">
  <input type="hidden" name="filter_exp" id="filter_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
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

    $('.product-preorder-report').DataTable().ajax.reload();

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

  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#from_date").datepicker('setDate',last_month);
  $("#to_date").datepicker('setDate','today');
  

  var date = $('#from_date').val();
    $("#from_date_exp").val(date);
  var date = $('#to_date').val();
    $("#to_date_exp").val(date);

  $(function(e){
    $(".state-tags").select2();
    var table2 = $('.product-preorder-report').DataTable({
      processing: true,
      searching:false,
      oLanguage: 
      {
        sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
      },
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      // ordering: true,
      serverSide: true,
      "aaSorting": [[3]],
      bSort: false,
      info: true,
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 0 ] },
        { className: "dt-body-right", "targets": [ 1,2,3,4] },
      ],
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      // dom: 'Blfrtip',
      // pageLength: {{500}},
      lengthMenu: [ 100,200,300,400,500],
      ajax: {
        url:"{!! route('get-margin-report-7') !!}",
        data: function(data) {
          data.from_date = $('#from_date').val(),
          data.to_date = $('#to_date').val(),
          data.sortbyparam = column_name, 
          data.sortbyvalue = order 
        },
        method: "get",
      },
      columns: [
        { data: 'refrence_code', name: 'refrence_code' },
        // { data: 'brand', name: 'brand'},
        { data: 'sales', name: 'sales' },
        { data: 'cogs', name: 'cogs' },
        { data: 'gp', name: 'gp' },
        { data: 'margins', name: 'margins' },
        // {  
        //   "render" : function(data , type , row){
        //   var total = Number(row['sales'].replace(/,/gi,"")) - Number(row['cogs'].replace(/,/gi,""))
        //   return (isNaN(total.toFixed(2)) == true ? 0.00 : total.toFixed(2))
        //   }
        // },
        // {  
        //   "render" : function(data , type , row){
        //   var total = (Number(row['sales'].replace(/,/gi,"")) - Number(row['cogs'].replace(/,/gi,""))) / Number(row['sales'].replace(/,/gi,""))
        //   return (isNaN(total.toFixed(2)) == true ? 0.00 : total.toFixed(2))
        //   }
        // },
      ],
      initComplete: function () {
        // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');
          $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
      footerCallback: function ( row, data, start, end, display ) {
        var api = this.api();
        var json = api.ajax.json();
        var total_cogs = json.total_cogs;
        var total_sales = json.total_sales;
       
         // total = total.toFixed(2);
        total_cogs = parseFloat(total_cogs).toFixed(2);
        total_cogs = total_cogs.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
        total_sales = parseFloat(total_sales).toFixed(2);
        total_sales = total_sales.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
         
        $( api.column( 0 ).footer() ).html('Total');
        $( api.column( 1 ).footer() ).html(total_sales);
        $( api.column( 2 ).footer() ).html(total_cogs);          
          
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


  $('.product_id').change(function() {
    $("#product_id_exp").val($('.product_id option:selected').val());
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.product-preorder-report').DataTable().ajax.reload();
  });

  $('#from_date').change(function() {
    var date = $('#from_date').val();
    $("#from_date_exp").val(date);
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-preorder-report').DataTable().ajax.reload();
  });

  $('#to_date').change(function() {
    var date = $('#to_date').val();
    $("#to_date_exp").val(date);
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-preorder-report').DataTable().ajax.reload();
  });

  $('.reset-btn').on('click',function(){
    $('.product_id').val('');
    $('#from_date').val('');
    $('#to_date').val('');

    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $(".state-tags").select2("", "");
    $('.product-preorder-report').DataTable().ajax.reload();
  });

  $('#export_s_p_r').on('click',function(){
    $("#export_sold_product_report_form").submit(); 
  });

  $('#dynamic_select').on('change', function () {
    $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      var url = $(this).val(); // get selected value
    if (url) { // require a URL
      window.location = url; // redirect
    }
    return false;
  });

</script>
@stop