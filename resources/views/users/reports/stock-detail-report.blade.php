@extends('users.layouts.layout')
@section('title','Users Management | Supply Chain')

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
          <li class="breadcrumb-item"><a href="{{route('complete-list-product')}}">Complete Products</a></li>
          <li class="breadcrumb-item"><a href="{{url()->previous()}}">Product Detail Page</a></li>
          <li class="breadcrumb-item active">Stock Detail Report</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-0 ml-3">
  <h4 class="text-uppercase fontbold">Stock Detail Report (By Item)</h4>
  <div class="col-7"></div>
  <div class="col-1 px-0 mb-2 text-right ml-3">
    <button class="btn recived-button back-btn rounded d-none" type="reset">Back</button>
  </div>

</div>
<div class="row mb-0">
  <div class="col-lg-12 col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <div class="col-lg-3 col-md-3">
        <select class="font-weight-bold form-control-lg form-control warehouse_id state-tags" name="warehouse_id" >
          <option value="">All Warehouses</option>
          @foreach($warehouses as $w)
          <option {{$w->id==$warehouse_id ? "selected":''}} value="{{$w->id}}">{{$w->warehouse_title}}</option>

          @endforeach
        </select>
      </div>
      @foreach($warehouses as $w)
        <input type="hidden" name="is_bonded" id="is_bonded_{{$w->id}}" value="{{@$w->is_bonded}}">
        @if($w->is_bonded == 1)
        <input type="hidden" name="is_bonded_find" id="is_bonded_find" value="{{@$w->is_bonded}}">
        @endif
      @endforeach
      <div class="col-lg-3 col-md-3">
        <div class="form-group">
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off" value="{{$from_date ? $from_date :''}}">
        </div>
      </div>


      <div class="col-lg-3 col-md-3">
        <div class="form-group">
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off" value="{{$to_date ? $to_date :''}}">
        </div>
      </div>
      <!-- <div class="col-lg-1 col-md-1"></div> -->
      <div class="col-lg col-md-4 ml-md-auto mr-md-auto pb-md-3" id="reset-1">
        <div class="input-group-append ml-3">
          <span class="reset-btn common-icons" title="Reset">
              <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
        </div>
      </div>

    </div>
  </div>
</div>
<div class="row mb-0">
  <div class="col-lg-1 col-md-1 ml-3">
    <div class="input-group-append">

    </div>
  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

    <div class="table-responsive">
      <table class="table entriestable table-bordered table-stock-report text-center">
        <thead>
          <tr>
            <!-- <th>
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                  <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
              <label class="custom-control-label" for="check-all"></label>
              </div>
            </th> -->
            <th>{{$global_terminologies['our_reference_number']}}</th>
            <th>Description</th>
            <th>{{$global_terminologies['brand']}}</th>
            <th>Unit</th>
            <th>Start <br> Count</th>
            <th>IN</th>
            <th>OUT</th>
            <th>Balance</th>
          </tr>
        </thead>
      </table>
    </div>
    </div>

  </div>
</div>

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-stock-detail-report text-center">
        <thead>
          <tr>
            <th>Warehouse</th>
            <th>Date</th>
            <th>Customer ref #</th>
            <th>Reason</th>
            <th>Starting Balance</th>
            <th>IN</th>
            <th>OUT</th>
            <th>Ending Balance</th>
            <th>Exp. Date</th>
            <!-- Note : please must update cogs column index after adding a column -->
            <th class="cogs_index" data-index="9">COGS</th>
            <th>Note</th>
            <th>In/Out From</th>
            <th>Custom's Inv.#</th>
            <th>Custom's Line#</th>
          </tr>
        </thead>
            <tfoot>
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
  var wareh = $('#is_bonded_find').val();
  var show_custom_line_number_choice = "{{@$show_custom_line_number}}";
  var show_custom_line_number = '';
  if(show_custom_line_number_choice == 1 && wareh == 1)
  {
    show_custom_line_number = true;
  }
  else
  {
    show_custom_line_number = false;
  }

  // alert(show_custom_line_number+' '+wareh);

  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });


  var currentTime = new Date();
  // First Date Of the month
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  var last_month = new Date();
  last_month.setDate( last_month.getDate() - 30 );

  var is_type = "{{$is_type}}";
  var p_id    = "{{$p_id}}";
  var o_id    = "{{$o_id}}";


  $('#from_date').datepicker('setDate', last_month);
  $('#to_date').datepicker('setDate', 'today');


  if("{{$from_date}}")
      $('#from_date').val('{{$from_date}}');

  if("{{$to_date}}")
      $('#to_date').val('{{$to_date}}');

  $(function(e){
    var role = "{{Auth::user()->role_id}}";
    var to_hide = '';
    if(role == 3 || role == 4 || role == 6)
    {
      var index = $('.cogs_index').index();
      to_hide = [index];
    }
    if(role == 3 || role == 4 || role == 6)
    {
      var cogs_show1 = false;
    }
    else
    {
      var cogs_show1 = true;
    }

    $(".state-tags").select2();
    var product_id = "{{$product_id}}";
    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'});
    });

    var table2 = $('.table-stock-report').DataTable({
      processing: false,
      searching: false,
      paging: false,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      "columnDefs": [
      { className: "dt-body-left", "targets": [ 0,1,2,3] },
      { className: "dt-body-right", "targets": [4,5,6,7 ] },

    ],
      serverSide: false,
      ajax:
        {
            beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
          url: "{!! route('get-stock-report-from-product-detail') !!}",
          data: function(data) { data.warehouse_id = $('.warehouse_id option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.product_id = product_id, data.p_id = p_id, data.o_id = o_id } ,
        },
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      "bInfo" : false,

      columns: [
        // { data: 'checkbox', name: 'checkbox' },
        { data: 'refrence_code', name: 'refrence_code' },
        { data: 'short_desc', name: 'short_desc' },
        { data: 'brand', name: 'brand' },
        { data: 'selling_unit', name: 'selling_unit' },
        { data: 'start_count', name: 'start_count' },
        { data: 'stock_in', name: 'stock_in' },
        { data: 'stock_out', name: 'stock_out' },
        { data: 'stock_balance', name: 'stock_balance' },
      ],
     initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

      },
    //   drawCallback: function(){
    //     $('#loader_modal').modal('hide');

    //   },
    });

    var table3 = $('.table-stock-detail-report').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      searching: false,
      paging: true,
      lengthMenu:[50,100,150,200],
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      "columnDefs": [
      { className: "dt-body-left", "targets": [ 0,1,2,3,8] },
      { className: "dt-body-right", "targets": [4,5,6,7 ] },
      {
        targets: to_hide,
        className: 'noVis'
      }

    ],
      serverSide: true,
      ajax:
        {
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
          },
          url: "{!! route('get-stock-detail-report') !!}",
          data: function(data) {  data.warehouse_id = $('.warehouse_id option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.product_id = product_id, data.p_id = p_id, data.o_id = o_id } ,
        },
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,

      columns: [
        // { data: 'checkbox', name: 'checkbox' },
        { data: 'warehouse_title', name: 'warehouse_title' },
        { data: 'created_date', name: 'created_date' },
        { data: 'customer_ref_name', name: 'customer_ref_name' },
        { data: 'reason', name: 'reason' },
        { data: 'stock_balance_start', name: 'stock_balance_start' },
        { data: 'in', name: 'in' },
        { data: 'out', name: 'out' },
        { data: 'stock_balance', name: 'stock_balance' },
        { data: 'exp_date', name: 'exp_date' },
        { data: 'cogs', name: 'cogs', visible: cogs_show1 },
        { data: 'note', name: 'note' },
        { data: 'in_out_from', name: 'in_out_from' },
        { data: 'custom_invoice_number', name: 'custom_invoice_number' , visible: show_custom_line_number},
        { data: 'custom_line_number', name: 'custom_line_number' , visible: show_custom_line_number},
      ],
     initComplete: function () {
      $('.dataTables_scrollHead').css('overflow', 'auto');
      $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
        // alert(show_custom_line_number);
          table3.columns([11,12,13]).visible(show_custom_line_number);

          var api = this.api()
          var json = api.ajax.json();

          var total_in = json.total_in;
          total_in = parseFloat(total_in).toFixed(2);
          total_in = total_in.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

          var total_out = json.total_out;
          total_out = parseFloat(total_out).toFixed(2);
          total_out = total_out.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

          $( api.column( 5 ).footer() ).html(total_in);
          $( api.column( 6 ).footer() ).html(total_out);
      },
    });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13) {
        table2.search($(this).val()).draw();
      }
    });

  $(document).on('change','.warehouse_id',function(){

    if($('.warehouse_id option:selected').val() != '')
        {
          wareh = $('#is_bonded_'+$('.warehouse_id option:selected').val()).val();
          // alert(wareh);
          show_custom_line_number_choice = "{{@$show_custom_line_number}}";
          show_custom_line_number = '';
          if(show_custom_line_number_choice == 1 && wareh == 1)
          {
            show_custom_line_number = true;
          }
          else
          {
            show_custom_line_number = false;
          }
        }

    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    const params = new URLSearchParams(window.location.search);
    let query_string='?id='+params.get('id')+'&warehouse_id='+$(".warehouse_id option:selected").val()+'&from_date='+$("#from_date").val()+'&to_date='+$("#to_date").val();
    window.history.replaceState(null, null, query_string);
    // $.ajax({
    // type: "get",
    // url: "{{ route('stock-detail-report') }}"+query_string,
    // success: function(response){
    //   $('.table-stock-report').DataTable().ajax.reload();
    // $('.table-stock-detail-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    // },
    // });
    $('.table-stock-report').DataTable().ajax.reload();
    $('.table-stock-detail-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

  });

  $(document).on('change','.supplier_id',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.table-stock-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on('change','.prod_category',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    $('.table-stock-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#from_date').change(function() {
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
      const params = new URLSearchParams(window.location.search);
    let query_string='?id='+params.get('id')+'&warehouse_id='+$(".warehouse_id option:selected").val()+'&from_date='+$("#from_date").val()+'&to_date='+$("#to_date").val();
    window.history.replaceState(null, null, query_string);
    // $.ajax({
    // type: "get",
    // url: "{{ route('stock-detail-report') }}"+query_string,
    // success: function(response){
    //   $('.table-stock-report').DataTable().ajax.reload();
    // $('.table-stock-detail-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    // },
    // });

      $('.table-stock-report').DataTable().ajax.reload();
    $('.table-stock-detail-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

  });

  $('#to_date').change(function() {
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
      const params = new URLSearchParams(window.location.search);
    let query_string='?id='+params.get('id')+'&warehouse_id='+$(".warehouse_id option:selected").val()+'&from_date='+$("#from_date").val()+'&to_date='+$("#to_date").val();
    window.history.replaceState(null, null, query_string);
    // $.ajax({
    // type: "get",
    // url: "{{ route('stock-detail-report') }}"+query_string,
    // success: function(response){
    // $('.table-stock-report').DataTable().ajax.reload();
    // $('.table-stock-detail-report').DataTable().ajax.reload();
    // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    // },
    // });
    $('.table-stock-report').DataTable().ajax.reload();
    $('.table-stock-detail-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

  });

  $('.reset-btn').on('click',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
    // $('#from_date').datepicker('setDate', startDateFrom);
    // $('#to_date').datepicker('setDate', 'today');
    $('#from_date').val("");
    $('#to_date').val("");
    $('.warehouse_id').val('');
    $(".state-tags").select2("", "");
    $('.table-stock-report').DataTable().ajax.reload();
    $('.table-stock-detail-report').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.back-btn').on('click',function(){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
     if(history.length > 1){
       return history.go(-1);
     }else{
       var url = "{{ url('/') }}";
       document.location.href = url;
     }
  });

  });

$(document).ready(function(e) {
  $(document).on('click','.load_in_out_detail',function(){
      var id = $(this).data('id');
      $("#in_out_modal_"+id).data('bs.modal')._config.backdrop = 'static';
      $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });
      $.ajax({
          url: "{{ route('get-custom-invoice-number') }}",
          method: 'post',
          dataType: 'json',
          data: {id:id},
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success: function(result){
            $("#loader_modal").modal('hide');
            if(result.success == true)
            {
              $('.in_out_detail_table').empty();
              $('.in_out_detail_table').append(result.html);
            }

            if(result.from_group == true)
            {
              $('.in_out_detail_table').empty();
              $('.in_out_detail_table').append(result.html);
            }

          },
          error: function(request, status, error){
            $("#loader_modal").modal('hide');
          }

        });
  });

  $(document).on('keypress keyup focusout',".fieldFocus",function(e) {
    // alert('hi');
    // return false;
    var name = $(this).attr('name');
    $(this).addClass('active');
    // alert(name);

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
        var fieldvalue = $(this).data('fieldvalue');
        var new_value = $(this).val();
        // alert(new_value);
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          return false;
        }
        else
        {
          var thisPointer = $(this);

          $(this).data('fieldvalue', new_value);
          saveStockDate(thisPointer,thisPointer.attr('name'), thisPointer.val(),new_value);
        }
    }

  });

  function saveStockDate(thisPointer,field_name,field_value,new_select_value){
      var id = thisPointer.data('id');
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",

        url: "{{ route('update-stock-invoice-number') }}",
        dataType: 'json',
        data: 'id='+id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'new_select_value'+'='+new_select_value,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-stock-detail-report').DataTable().ajax.reload();
              return false;
          }

          if(data.group_find == true)
            {
              toastr.warning('Alert!', 'Please update invoice / line # from shipment!!!',{"positionClass": "toast-bottom-right"});
              return false;
            }

        },

      });
    }

    $(document).on('click','.refresh-table',function(){
      $('.table-stock-detail-report').DataTable().ajax.reload();
    });
});
</script>
@stop

