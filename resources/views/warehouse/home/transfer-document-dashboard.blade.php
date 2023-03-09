@extends('warehouse.layouts.layout')
@section('title','Transfer Document Dashboard')

@section('content')

{{-- Content Start from here --}}

<style type="text/css">
  .dataTables_scrollHeadInner{width: 100%!important;}
</style>
<!-- Right Content Start Here -->
<div class="right-content pt-0">

<!-- upper section start -->
<div class="row mb-2">
<!-- left Side Starts -->
<div class="col-lg-12" style="font-size:0.9rem">
<!-- 1st four box row start -->
<div class="row mb-3 headings-color">
  <!-- include -->
@include('warehouse.home.transfer-dashboard-boxes')

</div>
<!-- first four box row end-->

</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>

<div class="row mb-3">

<div class="col-lg-9 headings-color">
  <h4 class="font-weight-bold">{{$global_terminologies['transfer_document']}} Dashboard</h4>
</div>


<div class="col-lg-12 headings-color">
  <div class="row form-row">
    
    <div class="col-lg col-md-6">
      <div class="form-group">
        @if($purchasingStatuses)
        <select class="form-control po-statuses" id="po-statuses">
          <option value="" disabled="">Choose Status</option>
          @foreach($purchasingStatuses as $status)
          @if($status->id == session('td_status'))
            <option value="{{$status->id}}" selected="">{{$status->title}}</option>
          @else
            <option value="{{$status->id}}">{{$status->title}}</option>
          @endif
          @endforeach
          <option value="all">All</option>
        </select>
        @endif
      </div>
    </div>

    <div class="col-lg col-md-6">
      <div class="form-group">
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg col-md-6">
      <div class="form-group">
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
    </div>

    <div class="col-lg p-0" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <button class="btn recived-button apply_date" type="button" style="padding: 8px 0px;">Apply Dates</button>  
      </div>
      </div>
    </div>

    {{-- its status is d-none --}}
    <div class="col d-none">
        <div class="form-group">
        <select class="form-control">
          <option>Status</option>
          <option>Waiting Confirm</option>
          <option>Shipping</option>
          <option>Dispatched</option>
          <option>Recieved into Stock</option>
        </select>
      </div>
    </div>

    <div class="col-lg col-md-6  ml-md-auto mr-md-auto pb-md-3">
      <div class="input-group-append ml-3">
        <button class="btn recived-button reset" type="reset">Reset</button>  
      </div>
    </div>

  </div>
</div>

<!-- Purchase Orders Table with With multiple statuses -->
<div class="col-lg-12" id="table-po">
  <div class="entriesbg bg-white custompadding customborder">
    <div class="delete-selected-item mb-3 d-none">
      <a type="submit" href="javascript:void(0);" title="Delete Selected Items" class="btn selected-item-btn btn-sm delete-btn">
        <i class="fa fa-trash"></i>
      </a>
    </div>
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-po text-center">
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            {{-- <th>Actions</th> --}}
            <th>TD#</th>
            <th>Transfer Date</th>
            <th>Received Date</th>
            <th>{{$global_terminologies['supply_from']}}</th>
            <th>To Warehouse</th>
            <th>Created Date</th>
            {{--<th>PO Total</th>
            <th>Target Received Date</th>
            <th>@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif</th>@endif/th>--}}
            <th>{{$global_terminologies['note_two']}}</th>
            <th>Customers</th>
          </tr>
        </thead>
      </table>
    </div>    
  </div>
</div>

<!-- Draft Purchase Orders Table -->
<div class="col-lg-12" id="table-draft-po">
  <div class="selected-item catalogue-btn-group mt-4 mt-sm-3 ml-3 d-none">
    <a href="javascript:void(0);" class="btn selected-item-btn btn-sm delete-draft-po deleteIcon" data-parcel="1" title="Delete Draft TD"><span><i class="fa fa-trash" ></i></span></a>
  </div>
  <div class="entriesbg bg-white custompadding customborder">
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-draft-po text-center">
        <thead>
          <tr>
            <th>
              <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check-all1" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            {{-- <th>Actions</th> --}}
            <th>TD #</th>
            <th>@if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif</th>
            <th>Supply To</th>
            {{--<th>Supplier Ref#</th>--}}
            <th>Created Date</th>
            {{--<th>PO Total</th>--}} 
            {{--<th>Target Received Date</th>--}}
            {{--<th>@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif</th>@endif/th>--}}
            {{--<th>Status</th>--}}
          </tr>
        </thead>
      </table>
    </div>    
  </div>
</div>


</div>
</div>
<!-- main content end here -->
{{-- Customers Modal --}}
<div class="modal" id="customer-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order Customers</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="table-responsive">
          <table class="table entriestable table-bordered text-center">
           <thead>
            <tr>
              <th>Sr.#</th>
              <th>Name</th>
            </tr>
           </thead>
           <tbody id="fetched-customers">
            
           </tbody> 
          </table>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- Notes Modal --}}
<div class="modal" id="note-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Purchase Order Notes</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
        <table class="table entriestable table-bordered text-center">
         <thead>
          <tr>
            <th>Sr.#</th>
            <th>Note</th>
          </tr>
         </thead>
         <tbody id="fetched-notes">
          
         </tbody> 
        </table>
        </div>
      </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

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

  $(function(e){
  $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

  $('.my-pos').on('click',function(){
    var option = $(this).data('id');
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.po-statuses').val(option).change();
  });
  
  // by default this draft Purchase order table is d-none
  document.getElementById('table-draft-po').style.display = "none";
  // onchange statuses code starts here
  $('.po-statuses').on('change', function(e){

    if($('.po-statuses option:selected').val() == 23)
    {
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      $('.table-draft-po').DataTable().ajax.reload();
      document.getElementById('table-po').style.display = "none";
      document.getElementById('table-draft-po').style.display = "block";
    }
    else 
    {
      $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      $('.table-po').DataTable().ajax.reload();
      document.getElementById('table-po').style.display = "block";
      document.getElementById('table-draft-po').style.display = "none";    
    }
    
  });

  $(document).on("click",'.delete-btn',function(){
    var selected_tds = [];
      $("input.check:checked").each(function() {
      selected_tds.push($(this).val());
    });
      
      swal({
          title: "Alert!",
          text: "Are you sure you want to delete selected Transfer Documents?",
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
                data:'selected_tds='+selected_tds,
                url:"{{ route('delete-w-transfer-documents') }}",
                beforeSend:function(){
                  $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                      toastr.success('Success!', 'Selected Transfer Documents(s) deleted Successfully.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-po').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                      $('.check-all').prop('checked',false);
                  }
                  if(data.error == 1)
                  {
                    toastr.error('Error!', 'You cannot delete more than 100 Transfer Documents at a time.' ,{"positionClass": "toast-bottom-right"});
                      $('.table-po').DataTable().ajax.reload();
                      $('.delete-selected-item').addClass('d-none');
                  }
                },
                error: function (request, status, error) {
                  $("#loader_modal").modal('hide');
                    toastr.error('Error!', 'Something went wrong. Please try again later. If the issue persists, please contact support.' ,{"positionClass": "toast-bottom-right"});

                }
             });
          } 
          else{
              swal("Cancelled", "", "error");
          }
     }); 
 
  });

  $('#from_date').change(function() {
      var date = $('#from_date').val();
      console.log(date, 'change');
      if($('.po-statuses option:selected').val() == 23)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-draft-po').DataTable().ajax.reload();
        document.getElementById('table-po').style.display = "none";
        document.getElementById('table-draft-po').style.display = "block";
      }
      else
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-po').DataTable().ajax.reload();
        document.getElementById('table-po').style.display = "block";
        document.getElementById('table-draft-po').style.display = "none";
      }
    });

  $('#to_date').change(function() {
      var date = $('#to_date').val();
      console.log(date, 'change');
      if($('.po-statuses option:selected').val() == 23)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-draft-po').DataTable().ajax.reload();
        document.getElementById('table-po').style.display = "none";
        document.getElementById('table-draft-po').style.display = "block";
      }
      else
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-po').DataTable().ajax.reload();
        document.getElementById('table-po').style.display = "block";
        document.getElementById('table-draft-po').style.display = "none";
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
   $('.delete-quotations').removeClass('d-none');
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


  setTimeout(function(){ {{Session::forget('td_status')}} }, 50);

  $('.reset').on('click',function(){
    $('#from_date').val("");
    $('#to_date').val("");
    $('.po-statuses').val(20).change();
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $('.table-po').DataTable().ajax.reload();
  });

  var table2 = $('.table-po').DataTable({
    "sPaginationType": "listbox",
    processing: true,
    "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    // pageLength: {{100}},
     "columnDefs": [
    // { className: "dt-body-left", "targets": [ 2,3,4,5,7,8,9,10 ] },
    // { className: "dt-body-right", "targets": [ 6] }
    ],
    lengthMenu: [ 100, 200, 300, 400],
    ajax:{
      url:"{!! route('get-w-transfer-document-data') !!}",
      data: function(data) { data.dosortby = $('.po-statuses option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
    },

    columns: [
      { data: 'checkbox', name: 'checkbox' },
      // { data: 'action', name: 'action' },
      { data: 'ref_id', name: 'ref_id' },
      { data: 'transfer_date', name: 'transfer_date' },
      {data: 'received_date', name: 'received_date'},
      { data: 'supplier', name: 'supplier' },
      { data: 'to_warehouse', name: 'to_warehouse' },
      // { data: 'supplier_ref', name: 'supplier_ref' },
      { data: 'confirm_date', name: 'confirm_date' },
      // { data: 'po_total', name: 'po_total' },
      // { data: 'target_receive_date', name: 'target_receive_date' },
      // { data: 'payment_due_date', name: 'payment_due_date' },
      { data: 'note', name: 'note' },
      // { data: 'status', name: 'status' },
      { data: 'customer', name: 'customer' },
    ],
    initComplete: function () {
      this.api().columns([]).every(function () {
          var column = this;
          var input = document.createElement("input");
          $(input).addClass('form-control');
          $(input).attr('type', 'text');
          $(input).appendTo($(column.header()))
          .on('change', function () {
              column.search($(this).val()).draw();
          });
      });
    },
    drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
  });

  $('.popover-dismiss').popover({
    trigger: 'focus'
  });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
    if(e.keyCode == 13) 
    {
      table2.search($(this).val()).draw();
    }
  });

  $(document).on('click', '.check-all', function () {
      if(this.checked == true){
        $('.check').prop('checked', true);
        $('.check').parents('tr').addClass('selected');
        var cb_length = $( ".check:checked" ).length;
        if(cb_length > 0){
          $('.delete-selected-item').removeClass('d-none');
        }
      }
      else{
        $('.check').prop('checked', false);
        $('.check').parents('tr').removeClass('selected'); 
        $('.delete-selected-item').addClass('d-none');       
      }
    });

  $(document).on('click', '.check', function () {
      if(this.checked == true)
      {
        $('.delete-selected-item').removeClass('d-none');
        $(this).parents('tr').addClass('selected');
      }
      else
      {
        var cb_length = $( ".check:checked" ).length;
        $(this).parents('tr').removeClass('selected');       
        if(cb_length == 0){
         $('.delete-selected-item').addClass('d-none');
        } 
      }
  });

  $('.table-draft-po').DataTable({
    "sPaginationType": "listbox",
    searching: false,
    processing: true,
    "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    // pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    ajax:{
      url:"{!! route('get-w-draft-transfer-data') !!}",
      data: function(data) { data.dosortby = $('.po-statuses option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
    },
    columns: [
      { data: 'checkbox', name: 'checkbox' },
      // { data: 'action', name: 'action' },
      { data: 'po_id', name: 'po_id' },
      { data: 'supplier', name: 'supplier' },
      { data: 'supply_to', name: 'supply_to' },
      { data: 'confirm_date', name: 'confirm_date' },
      // { data: 'target_receive_date', name: 'target_receive_date' },
    ],
    drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
  });

});

  // getting PO Customers
  $(document).on('click', '.show-po-cust', function(e){
    let po_id = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-po-customers') }}",
      data: 'po_id='+po_id,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response){
        $('#loader_modal').modal('hide');
        $('#fetched-customers').html(response.html_string);
        
      }
    });

  });

  

  // getting PO Notes
  $(document).on('click', '.show-po-note', function(e){
    let po_id = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-po-notes') }}",
      data: 'po_id='+po_id,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response){
        $('#loader_modal').modal('hide');
        $('#fetched-notes').html(response.html_string);
        
      }
    });

  });

  // Changing purchase order statuses
  $(document).on('change', '.change-po-status', function(e){
    var id = $(this).val();
    var pId = $(this).parents('tr').attr('id');

    swal({
          title: "Alert!",
          text: "Are you sure you want to change the status of this purchase order?",
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
                data:'id='+id+'&pId='+pId,
                url:"{{ route('changing-status-of-po') }}",
                beforeSend: function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $('#loader_modal').modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                    toastr.success('Success!', 'Status Changed Successfully.' ,{"positionClass": "toast-bottom-right"});
                    $('.table-po').DataTable().ajax.reload();
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

  //For deleting Draft PO
  $(document).on('click', '.delete-draft-po', function(){
    var selected_quots = [];
    $("input.check1:checked").each(function() {
      selected_quots.push($(this).val());
    });

    length = selected_quots.length;

    swal({
      title: "Alert!",
      text: "Are you sure you want to delete all these purchase orders? \n selected draft po's:"+length,
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
          url:"{{ route('delete-draft-po') }}",
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success:function(result){
            $('#loader_modal').modal('hide');
            if(result.success == true){

              toastr.success('Success!', 'Draft Purchase orders deleted Successfully',{"positionClass": "toast-bottom-right"});
              $('.table-draft-po').DataTable().ajax.reload();
              $('.delete-draft-po').addClass('d-none');
              $('.check-all1').prop('checked',false);

            }
          }
        });
      }
      else{
          swal("Cancelled", "", "error");
      }
      });
    });
    $(document).ready(function(){
      // alert($('.po-statuses option:selected').val());
    if($('.po-statuses option:selected').val() == 23)
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-draft-po').DataTable().ajax.reload();
        document.getElementById('table-po').style.display = "none";
        document.getElementById('table-draft-po').style.display = "block";
      }
      else
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-po').DataTable().ajax.reload();
        document.getElementById('table-po').style.display = "block";
        document.getElementById('table-draft-po').style.display = "none";
      }
  });
</script>
@stop

