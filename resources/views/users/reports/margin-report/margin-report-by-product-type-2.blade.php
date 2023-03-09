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
          <li class="breadcrumb-item active">Margin Report by @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col col-6">
    <h5 class="maintitle text-uppercase fontbold">Admin Management & Sales Report</h5>
  </div>
  <div class="col-md-4 col-6">
  <div class="pull-right">
    <span class="export-btn vertical-icons mr-4" title="Create New Export">
      <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
    </span>
  </div>
</div>
</div>

@include('users.reports.margin-report.dropdown-boxes')

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
            <th>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="product_type_2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="product_type_2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>VAT Out
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat_out">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat_out">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Sales
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="1">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="1">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>% Sales
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="percent_sales">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="percent_sales">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>VAT In
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="vat_in">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="vat_in">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>{{$global_terminologies['net_price']}}
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="2">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="2">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>GP
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="gp">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="gp">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>% GP
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="percent_gp">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="percent_gp">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
            </th>
            <th>Margin
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="3">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="3">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
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
    var table2 = $('.product-sales-report').DataTable({
      // "pagingType": "input",
      "sPaginationType": "listbox",
      processing: false,
      searching:true,
    //   oLanguage:
    //   {
    //     sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
    //   },
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      // ordering: true,
      serverSide: true,
      "aaSorting": [[3]],
      bSort: false,
      info: true,
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 0 ] },
        { className: "dt-body-right", "targets": [ 1,2,3,4] },
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : '' }}], visible: false },
      ],
      retrieve: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      fixedHeader: true,
      // dom: 'Blfrtip',
      // pageLength: {{500}},
      dom: 'Bfrtip',
      buttons: [
        'colvis'
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
        url:"{!! route('get-margin-report-11') !!}",
        data: function(data) {data.from_date = $('#from_date').val(),
         data.to_date = $('#to_date').val(), data.product_id = $('.product_id option:selected').val(),
         data.sortbyparam = column_name,
         data.sortbyvalue = order },
        method: "get",
      },
      columns: [
        { data: 'title', name: 'title' },
        { data: 'vat_out', name: 'vat_out' },
        { data: 'sales', name: 'sales' },
        { data: 'percent_sales', name: 'percent_sales' },
        { data: 'vat_in', name: 'vat_in' },
        { data: 'cogs', name: 'cogs' },
        { data: 'gp', name: 'gp' },
        { data: 'percent_gp', name: 'percent_gp' },
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
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
      // footerCallback: function ( row, data, start, end, display ) {
      //   var api = this.api();
      //   var json = api.ajax.json();
      //   var total_cogs = json.total_cogs;
      //   var total_sales = json.total_sales;
      //   var total_gp = total_sales - total_cogs;

      //   var total_sale_percent = json.total_sale_percent;
      //   var total_vat_out = json.total_vat_out;
      //   var total_vat_in = json.total_vat_in;
      //   var total_gp_percent = json.total_gp_percent;
      //   var total_margin_percent = total_gp / total_sales * 100;

      //    // total = total.toFixed(2);
      //   total_cogs = parseFloat(total_cogs).toFixed(2);
      //   total_cogs = total_cogs.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
      //   total_sales = parseFloat(total_sales).toFixed(2);
      //   total_sales = total_sales.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
      //   total_gp    = parseFloat(total_gp).toFixed(2);
      //   total_gp    = total_gp.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

      //   total_sale_percent    = parseFloat(total_sale_percent).toFixed(2);
      //   total_sale_percent    = total_sale_percent.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

      //   total_vat_out    = parseFloat(total_vat_out).toFixed(2);
      //   total_vat_out    = total_vat_out.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

      //   total_vat_in    = parseFloat(total_vat_in).toFixed(2);
      //   total_vat_in    = total_vat_in.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

      //   total_gp_percent    = parseFloat(total_gp_percent).toFixed(2);
      //   total_gp_percent    = total_gp_percent.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

      //   total_margin_percent    = parseFloat(total_margin_percent).toFixed(2);
      //   total_margin_percent    = total_margin_percent.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

      //   $( api.column( 0 ).footer() ).html('Total');
      //   $( api.column( 1 ).footer() ).html(total_vat_out);
      //   $( api.column( 2 ).footer() ).html(total_sales);
      //   $( api.column( 3 ).footer() ).html(total_sale_percent + '%');
      //   $( api.column( 4 ).footer() ).html(total_vat_in);
      //   $( api.column( 5 ).footer() ).html(total_cogs);
      //   $( api.column( 6 ).footer() ).html(total_gp);
      //   $( api.column( 7 ).footer() ).html(total_gp_percent + '%');
      //   $( api.column( 8 ).footer() ).html(total_margin_percent + '%');

      //   },
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
          url:"{{ route('get-margin-report-11-footer') }}",
          data:{ from_date: $('#from_date').val(),to_date: $('#to_date').val(),product_id: $('.product_id option:selected').val()},
          beforeSend:function(){
          },
          success:function(result){
            var total_cogs = result.total_cogs;
            var total_sales = result.total_sales;
            var total_gp = total_sales - total_cogs;

            var total_sale_percent = result.total_sale_percent;
            var total_vat_out = result.total_vat_out;
            var total_vat_in = result.total_vat_in;
            var total_gp_percent = result.total_gp_percent;
            var total_margin_percent = total_gp / total_sales * 100;

             // total = total.toFixed(2);
            total_cogs = parseFloat(total_cogs).toFixed(2);
            total_cogs = total_cogs.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            total_sales = parseFloat(total_sales).toFixed(2);
            total_sales = total_sales.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
            total_gp    = parseFloat(total_gp).toFixed(2);
            total_gp    = total_gp.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            total_sale_percent    = parseFloat(total_sale_percent).toFixed(2);
            total_sale_percent    = total_sale_percent.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            total_vat_out    = parseFloat(total_vat_out).toFixed(2);
            total_vat_out    = total_vat_out.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            total_vat_in    = parseFloat(total_vat_in).toFixed(2);
            total_vat_in    = total_vat_in.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            total_gp_percent    = parseFloat(total_gp_percent).toFixed(2);
            total_gp_percent    = total_gp_percent.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            total_margin_percent    = parseFloat(total_margin_percent).toFixed(2);
            total_margin_percent    = total_margin_percent.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

            $( api.column( 0 ).footer() ).html('Total');
            $( api.column( 1 ).footer() ).html(total_vat_out);
            $( api.column( 2 ).footer() ).html(total_sales);
            $( api.column( 3 ).footer() ).html(total_sale_percent + '%');
            $( api.column( 4 ).footer() ).html(total_vat_in);
            $( api.column( 5 ).footer() ).html(total_cogs);
            $( api.column( 6 ).footer() ).html(total_gp);
            $( api.column( 7 ).footer() ).html(total_gp_percent + '%');
            $( api.column( 8 ).footer() ).html(total_margin_percent + '%');
          },
          error: function(){

          }
        });
      },
   });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {

      var arr = '';
      var all = arr;
      if(all == '')
      {
        var col = column;
      }
      else
      {
        var col = all[column];
      }
      var columns = table2.settings().init().columns;
      var name = columns[col].name;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'margin_report_by_product_type',column_id:col},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
           if(name.toLowerCase().indexOf('current') >= 0)
           {
              table2.ajax.reload();
           }
          if(data.success == true){
            /*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
            // table2.ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    });

     $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup focusout', function(e) {
      let searchSession;
      let searchField;
      let count;
     searchField=$(this).val();
     searchField=searchField.trim();
     $('#tableSearchField').val(searchField);
     count=searchField.length;
      if(e.keyCode == 13) {
        table2.search($(this).val()).draw();
        return;
      }else if(count>0){
        if(e.type == 'focusout'){
           table2.search(this.value).draw();
              return;
                   }
        }else if( searchField==""){
                 $('input[type=search]').empty();
                 return;
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
    $('.product-sales-report').DataTable().ajax.reload();
  });

  $('#from_date').change(function() {
    // var date = $('#from_date').val();
    // $("#from_date_exp").val(date);
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $('#to_date').change(function() {
    // var date = $('#to_date').val();
    // $("#to_date_exp").val(date);
    // $('#loader_modal').modal({
    //   backdrop: 'static',
    //   keyboard: false
    // });
    // $('#loader_modal').modal('show');
    // $('.product-sales-report').DataTable().ajax.reload();
  });

  $(document).on('click','.apply_date',function(){
    var date_from = $('#from_date').val();
    $("#from_date_exp").val(date_from);

    var date_to = $('#to_date').val();
    $("#to_date_exp").val(date_to);
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.product-sales-report').DataTable().ajax.reload();
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
    $('.product-sales-report').DataTable().ajax.reload();
  });

  $('#export_s_p_r').on('click',function(){
    $("#export_sold_product_report_form").submit();
  });



  // this is new code
  $(document).on('click', '.s_p_report_w_pm', function(){
    var prod_id   = 'null';
    var from_date = $('#from_date').val();
    from_date = from_date.replace(/\//g, "_");
    var to_date   = $('#to_date').val();
    to_date = to_date.replace(/\//g, "_");
    var mg_report = "Margin";
    var w_id = 'null';
    var cat_id = $(this).data('catid');
    var cust_id = 'null';
    var c_ty_id = 'null';
    var saleid = 'null';
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    setTimeout(function(){
      if(from_date == '')
      {
        from_date = null;
      }
      if(to_date == '')
      {
        to_date = null;
      }
      var url = "{{ url('sold-product-report-with-param')}}"+"/"+prod_id+"/"+from_date+"/"+to_date+"/"+mg_report+"/"+w_id+"/"+cat_id+"/"+cust_id+"/"+c_ty_id+"/"+saleid;
      window.open(url, '_blank');
      $("#loader_modal").modal('hide');
    }, 500);

  });

$(document).on('click','.export-btn',function(){

    let from_date=$("#from_date").val();
    let to_date=$("#to_date").val();
    let filter = 'product_type 2';
    let product_id = $('.product_id option:selected').val();
    product_id = (product_id != undefined) ? product_id : null;

    if (from_date == '') {
      toastr.error('Error!', 'Please Select From date first !!!' ,{"positionClass": "toast-bottom-right"});
      return;
    }
    if (to_date == '') {
      toastr.error('Error!', 'Please Select To date first !!!' ,{"positionClass": "toast-bottom-right"});
      return;
    }
    let start_date = from_date.split('/');
    start_date = start_date[2] + '-' + start_date[1] + "-" + start_date[0];
    let end_date = to_date.split('/');
    end_date = end_date[2] + '-' + end_date[1] + "-" + end_date[0];
    var start = new Date(start_date);
    var end   = new Date(end_date);
    var diff = new Date(end - start);
    var days = diff/1000/60/60/24;
    if (days > 91) {
      toastr.error('Error!', 'Only 3 months filter is allowed for export!!!' ,{"positionClass": "toast-bottom-right"});
      return;
    }

    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"get",
      url:"{{route('export-status-margin-report-by-product-type-2')}}",
      data:{from_date_exp:from_date, to_date_exp:to_date, filter:filter, product_id:product_id, sortbyvalue:order, sortbyparam:column_name},
      beforeSend:function(){

      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').attr('title','EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          $('.export-btn').attr('title','EXPORT is being Prepared');
        }
        checkStatusForMarginExport();
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  let type = 'margin_report_by_product_type_2';
  function checkStatusForMarginExport()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-margin-reports')}}",
      data: {type:type},
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForMarginExport();
            }, 5000);
        }
        else if(data.status==0)
        {
          var href="{{ url('get-download-xslx')}}"+"/"+data.file_name;
          $('#export-download-btn').attr("href",href);
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').attr('title','Create New Export');
          $('.export-btn').prop('disabled',false);
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-margin-reports')}}",
      data: {type:type},
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-btn').attr('title','EXPORT is being Prepared');
          $('.export-btn').prop('disabled',true);
          checkStatusForMarginExport();
        }
      }
    });
  });
</script>
<script src="{{asset("public\site\assets\backend\js\margin-report.js")}}"></script>
@stop
