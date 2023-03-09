@extends('sales.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%;
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed;
}

.h-100{
  height: 145px !important;
}
.delete-btn-two{
  position: absolute;
  right: 30px;
  top: -10px;
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
          <li class="breadcrumb-item active">Cancelled Order</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}

<div class="row entriestable-row">
  <!-- Header is here -->
 @if(Auth::user()->role_id == 9)
  <div class="col-lg-12 pl-0 pr-0 d-flex align-items-center mb-3">
    <div class="col-lg-7">
      <h4>E-Commerce {{$global_terminologies['cancelled_order']}}</h4>
    </div>

    <div class="col-lg-5">
      <div class="pull-right">
        <span class="export-btn vertical-icons" title="Create New Export" style="padding: 13px 15px;">
              <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
      </div>
    </div>
  </div>
 @else
  <div class="col-lg-12 pl-0 pr-0 d-flex align-items-center mb-3">
    <div class="col-lg-7">
      <h4>{{$global_terminologies['cancelled_order']}}</h4>
    </div>

    <div class="col-lg-5">
      <div class="pull-right">
        <span class="export-btn vertical-icons" title="Create New Export" style="padding: 13px 15px;">
              <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
        </span>
      </div>
    </div>
  </div>
  @endif
  <div class="col-lg-12 pl-0 pr-0 d-flex align-items-center mb-3 filters_div">
    <div class="col-lg-2">
      <label class="pull-left font-weight-bold">Status:</label>
      <select class="font-weight-bold form-control js-states state-tags filter-dropdown" name="filter">
        <option value="" selected="">Select a Filter</option>
        <option value="draft">Draft</option>
        <option value="invoice">Invoice</option>
      </select>
    </div>
    <div class="col-lg-2">
      <label class="pull-left font-weight-bold"><b>From Date</b></label>
      <div class="form-group mb-0">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg-2">
      <label class="pull-left font-weight-bold"><b>To Date</b></label>
      <div class="form-group mb-0">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
    </div>
    <div class="px-3 mt-auto d-flex" style="width: 20%;">
      <div class="form-check mr-4">
        <input type="radio" class="form-check-input dates_changer"  name="date_radio" value='1' checked>
        <label class="form-check-label" for="exampleCheck1"><b>Target Ship Date</b></label>
      </div>
      <div class="form-check">
        <input type="radio" class="form-check-input dates_changer"  name="date_radio" value='2'>
        <label class="form-check-label" for="exampleCheck1"><b>Cancelled Date</b></label>
      </div>
    </div>
    <div class="col-lg-3 d-flex mt-auto">
      <div class="pull-right">
      <!-- <input type="button" value="Reset" class="btn recived-button reset-btn"> -->
      <!-- <input type="button" value="Export" class="btn recived-button export-btn"> -->
      <span class="vertical-icons reset-btn mr-4" id="reset-btn" title="Reset" style="padding: 13px 15px;">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
    </div>

  </div>
  <div class="col-lg-12 pl-0 pr-0 d-flex align-items-center mb-3">

  <!-- End -->
  <div class="col-12">
    @if(Auth::user()->role_id != 7)
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">

      <a href="javascript:void(0);" class="btn selected-item-btn btn-sm revert-to-order
      deleteIcon" title="Uncancel Order"><span><i class="fa fa-undo" ></i></span></a>

  </div>
  @endif
    <div class="entriesbg bg-white custompadding customborder">
        <div class="alert alert-primary export-alert d-none"  role="alert">
         <i class="  fa fa-spinner fa-spin"></i>
         <b> Export file is being prepared! Please wait.. </b>
         </div>
          <div class="alert alert-success export-alert-success d-none"  role="alert">
          <i class=" fa fa-check "></i>
          <b>Export file is ready to download.
          <!-- <a download href="{{ url('storage/app/cancelled-order-export.xlsx')}}"><u>Click Here</u></a> -->
          <a class="exp_download" href="{{ url('get-download-xslx','cancelled-order-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
          </b>
        </div>
        <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
       <b> Export file is already being prepared by another user! Please wait.. </b>
  </div>
        <div class="table-responsive">
          <table class="table entriestable table-bordered table-quotation text-center">
        <thead>
          <tr>
            <th>
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <th>Action</th>
            <th>Sales Person</th>
            <th>Order#</th>
            <th>Draft#</th>
            <th>Customer#</th>
            <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif</th>
            <th>Date Purchase</th>
            <th>Order Total</th>
            <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
            <th>Cancelled Date</th>
            <th>Memo</th>
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

<!-- <form id="export_cancel_order_form" action="{{route('export-cancelled_order')}}" method="post"> -->
  <form id="export_cancel_order_form">
  @csrf
  <input type="hidden" name="filter_dropdown_exp" id="filter_dropdown_exp">
  <input type="hidden" name="to_date_exp" id="to_date_exp">
  <input type="hidden" name="from_date_exp" id="from_date_exp">
  <input type="hidden" name="date_radio_exp" id="date_radio_exp">
</form>


@endsection
@section('javascript')
<script type="text/javascript">
  $("#from_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });

  $("#to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true
  });
  $(function(){
  $('#date_radio_exp').val($("input[name='date_radio']:checked").val());

      var table2 = $('.table-quotation').DataTable({
        "sPaginationType": "listbox",
       processing: false,
        // "language": {
        //     processing: '<div class="position-relative spinloader"> <i class="spinIcon fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i></div>'},
      ordering: false,
      serverSide: true,
      "lengthMenu": [100,200,300,400],
      // dom: 'ftipr',
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,8,9 ] },
        { className: "dt-body-right", "targets": [7] }
      ],
      scrollX: true,
      scrollY : '90vh',
    scrollCollapse: true,
      ajax:{
        beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
        url:"{!! route('get-cancelled-orders-data') !!}",
        data: function(data){ data.status_filter = $('.filter-dropdown option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.date_type = $("input[name='date_radio']:checked").val()},
      },
      columns: [
          { data: 'checkbox', name: 'checkbox'},
          { data: 'action', name: 'action' },
          { data: 'sales_person', name: 'sales_person' },
          { data: 'in_ref_id', name: 'in_ref_id' },
          { data: 'ref_id', name: 'ref_id' },
          { data: 'customer_ref_no', name: 'customer_ref_no' },
          { data: 'customer', name: 'customer' },
          { data: 'invoice_date', name: 'invoice_date' },
          { data: 'total_amount', name: 'total_amount' },
          { data: 'target_ship_date', name: 'target_ship_date' },
          { data: 'cancelled_date', name: 'cancelled_date' },
          { data: 'memo', name: 'memo' },
          { data: 'status', name: 'status' },
        ],
         initComplete: function () {
          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
        drawCallback: function(){
            $('#loader_modal').modal('hide');
        },
   });
   $('.reset-btn').on('click',function(){
      $('.filter-dropdown').val('');
      $('#from_date').val('');
      $('#to_date').val('');
      $('input[type=search]').val('');
      $('#filter_dropdown_exp').val('');
      $('#from_date_exp').val('');
      $('#to_date_exp').val('');
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
      $("#loader_modal").modal('show');
      $('.table-quotation').DataTable().ajax.reload();
    });

    $('#from_date').change(function() {
      $('#from_date_exp').val($('#from_date').val());
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      $('.table-quotation').DataTable().ajax.reload();
    });

    $('#to_date').change(function() {
      $('#to_date_exp').val($('#to_date').val());
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      $('.table-quotation').DataTable().ajax.reload();
    });

    $(document).on('change','.filter-dropdown',function(){
      $('#filter_dropdown_exp').val($('.filter-dropdown').val());
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
      $('.table-quotation').DataTable().ajax.reload();
    });

    // $('.export-btn').on('click',function(){
    //   // var filter_val_exp = $('#filter_dropdown_exp').val();
    //   // var from_date_exp = $('#from_date_exp').val();
    //   // var to_date_exp = $('#to_date_exp').val();
    //   $("#export_cancel_order_form").submit();
    // });

    $(document).on('click','.export-btn',function(){

     var form=$('#export_cancel_order_form');

    var form_data = form.serialize();
    $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method:"post",
      url:"{{route('export-cancelled_order')}}",
      data:form_data,
      beforeSend:function(){
        $('.export-btn').prop('disabled',true);
      },
      success:function(data){
        if(data.status==1)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').removeClass('d-none');
          $('.export-btn').prop('disabled',true);
          console.log("Calling Function from first part");
          checkStatusForCancelledOrder();
        }
        else if(data.status==2)
        {
          $('.export-alert-another-user').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForCancelledOrder();
        }
      },
      error:function(){
        $('.export-btn').prop('disabled',false);
      }
    });
});
    $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-cancelled-order')}}",
      success:function(data)
      {
        if(data.status==0 || data.status==2)
        {

        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.export-alert-success').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',true);
          checkStatusForCancelledOrder();
        }
      }
    });
  });
  function checkStatusForCancelledOrder()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-export-status-cancelled-order')}}",
      success:function(data){
        if(data.status==1)
        {
          console.log("Status " +data.status);
          setTimeout(
            function(){
              console.log("Calling Function Again");
              checkStatusForCancelledOrder();
            }, 5000);
        }
        else if(data.status==0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);

        }
        else if(data.status==2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.export-alert-another-user').addClass('d-none');
          $('.export-btn').prop('disabled',false);
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          console.log(data.exception);
        }
      }
    });
  }



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

   $(document).on('click', '.check-all1', function () {
        if(this.checked == true){
        $('.check1').prop('checked', true);
        $('.check1').parents('tr').addClass('selected');
        var cb_length = $( ".check1:checked" ).length;
        if(cb_length > 0){
          $('.selected-item').removeClass('d-none');
        }
      }else{
        $('.check1').prop('checked', false);
        $('.check1').parents('tr').removeClass('selected');
        $('.selected-item').addClass('d-none');

      }
    });

   $(document).on('click', '.check1', function () {
    // $(this).removeClass('d-none');
   $('.cancel-quotations').removeClass('d-none');
   $('.revert-quotations').removeClass('d-none');
        var cb_length = $( ".check1:checked" ).length;
        var st_pieces = $(this).parents('tr').attr('data-pieces');
        if(this.checked == true){
        $('.selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }else{
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0){
         $('.selected-item').addClass('d-none');
        }

      }
    });

      $(document).on('click', '.revert-to-order', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to revert selected orders?",
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
          data: {quotations : selected_quots},
          url:"{{ route('revert-invoice-orders') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            if(result.success == true)
            {
              toastr.success('Success!', 'Orders Reverted Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.revert-to-order').addClass('d-none');
              $('.check-all1').prop('checked',false);
              window.location.reload();
             // window.location.href = "{{ url('sales/get-cancelled-orders')}}";
            $('#loader_modal').modal('hide');


            }

            if(result.success == false)
            {
              toastr.error('Sorry!', 'Cannot Revert This Cancelled Order',{"positionClass": "toast-bottom-right"});
              $('.table-quotation').DataTable().ajax.reload();
              $('.revert-to-order').addClass('d-none');
              $('.check-all1').prop('checked',false);
            $('#loader_modal').modal('hide');

            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });

    $('input[type=radio][name=date_radio]').change(function() {
      $('.table-quotation').DataTable().ajax.reload();
      $('#date_radio_exp').val(this.value);
    });

  });
</script>
@stop
