@extends('warehouse.layouts.layout')
@section('title','Pick Instruction')


@section('content')

{{-- Content Start from here --}}

<div class="right-contentIn">
<input type="hidden" name="id" id="order_id" value="{{$order->id}}">
<div class="row mb-3 headings-color">

<div class="col-lg-3 col-md-4 d-flex align-items-center">
  <h4 class="mb-lg-auto mb-md-auto">Pick Instructions</h4>
</div>
<div class="col-lg-2 col-md-3 d-flex align-items-center fontbold">
</div>
<div class="col-lg-3 d-flex align-items-center bg-white p-2 mr-3 ml-5">
  <table class="table table-bordered mb-0">
    <tbody>
      <tr>
        <th>Customer Name</th>
        <td>{{$order->customer->company != null ? $order->customer->company :" N.A"}}</td>
      </tr>
      <tr>
        <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif</th>
        @if($order->ecommerce_order == 1)
        <td>{{$order->ecommerce_order}} {{$order->customer->first_name}} {{$order->customer->last_name}}</td>
        @else
        <td>{{$order->customer->reference_name != null ? $order->customer->reference_name:" N.A"}}</td>
        @endif
      </tr>
      <tr>
        <th>Address</th>
        <td>

          @if($order->customer->getbilling != null)
            @if($order->customer->getbilling->where('is_default',1)->first() != null)
              {{$order->customer->getbilling->where('is_default',1)->first()->billing_address}}
            @else
              {{$order->customer->getbilling->first() != null ? $order->customer->getbilling->first()->billing_address :" N.A"}}
            @endif
          @endif
        </td>
      </tr>
    </tbody>
  </table>
</div>
<!-- <div class="col-lg-3 col-md-5 d-flex align-items-center bg-white p-2 mr-3 ml-3">
  <table class="table table-bordered mb-0">
    <tbody>
      <tr>
        <th>Order<br> No</th>
        <td>{{@$order->customer->primary_sale_person->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{$order->ref_id}}</td>
      </tr>
      <tr>
        <th>Customer <br> No</th>
        <td>{{$order->customer != null ? $order->customer->reference_number :" N.A"}}</td>
      </tr>
      {{-- <tr>
        <th>Customer Name</th>
        <td>{{$order->customer->company != null ? $order->customer->company :" N.A"}}</td>
      </tr> --}}
      <tr>
        <th>Request <br> Delivery <br> Date</th>
        <td>{{$order->target_ship_date != null ? date('d/m/Y', strtotime($order->target_ship_date)) :" N.A"}}</td>
      </tr>
    </tbody>
  </table>
</div> -->

  <div class="col-lg-3 col-md-5 d-flex align-items-center bg-white p-2">
    <table class="table table-bordered mb-0">
      <tbody>
        <tr>
          <th>Order #</th>
          <td>
            @php
              $ret = $order->get_order_number_and_link($order);
              $ref_no = $ret[0];
              $link = $ret[1];
            @endphp
            <a target="_blank" href="{{route($link,$order->id)}}" title="View Detail" class=""><b>{{@$ref_no}}</b></a>
          </td>
        </tr>
        <tr>
          <th>Customer No</th>
          <td>{{$order->customer != null ? $order->customer->reference_number :" N.A"}}</td>
        </tr>
        <tr>
          <th> @if(!array_key_exists('delivery_request_date', $global_terminologies)) Request Delivery Date @else {{$global_terminologies['delivery_request_date']}} @endif</th>
          <td>{{$order->delivery_request_date != null ? date('d/m/Y', strtotime($order->delivery_request_date)) :" N.A"}}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="col-lg-12 mb-2">
    <a href="javascript:void(0);">
      <!-- <button type="button" class="export-pdf"> -->
      <span class="common-icons export-pdf" title="Print" style="padding: 12px 15px;">
            <img src="{{asset('public/icons/print.png')}}" width="27px">
          </span>
    <!-- </button> -->
      <button type="button" class="btn-color btn text-uppercase purch-btn headings-color stricker-pdf mr-3">Create Sticker</button>
      <button type="button" class="d-none btn-color btn text-uppercase purch-btn headings-color export_new mr-3">print</button>
    </a>
  </div>

<div class="col-lg-12">
<div class="bg-white table-responsive p-3">
  <table class="table entriestable headings-color pick-instruction-table table-bordered text-center " style="width:100%">
    <thead class="sales-coordinator-thead table-bordered">
      <tr>
        <th>{{$global_terminologies['our_reference_number']}}
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reference_no">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reference_no">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
        </th>
        <th>{{$global_terminologies['product_description']}}
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="description">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="description">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
        </th>
        <th>Location<br> Code
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="location">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="location">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
        <th>Unit <br>of {{$global_terminologies['qty']}}<br> Measure</th>
        <!-- <th>QTY to Ship</th> -->
        <th>Unit <br>Price
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
        <th>Note</th>
        <th>{{$global_terminologies['pieces']}} <br>Ordered
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="pieces">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="pieces">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
        <th>{{$global_terminologies['pieces']}} <br>Shipped</th>
        <th> {{$global_terminologies['qty']}} <br>Ordered
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>

        <th> {{$global_terminologies['qty']}} <br>Shipped</th>
        <th>Expiration<br> Date</th>
        <th>Current<br> {{$global_terminologies['qty']}}
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="current_quantity">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="current_quantity">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>
        <th>Reserved<br> {{$global_terminologies['qty']}}
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reserved_quantity">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reserved_quantity">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span></th>

      </tr>
    </thead>
  </table>
    <div class="col-lg-5 col-md-5 pull-right pb-3 mt-md-2">
      <div class="input-group-append">
        <button class="btn recived-button mr-5 full_pcs_btn" data-id="{{$order->id}}">Full Pieces</button>
        <button class="btn recived-button mr-5 full_qty_btn" data-id="{{$order->id}}">Full Quantity</button>
        <button class="btn recived-button complete_btn" data-id="{{$order->id}}" data-pi_redirection_config="{{ $pi_redirection_config != null ? $pi_redirection_config->display_prefrences : ''}}" type="submit">Complete</button>
      </div>
    </div>

  <div class="row pt-5">

    <div class="col-lg-4 col-md-4 col-sm-6 pr-4 mt-5">
      <p><strong>{{$global_terminologies['comment_to_customer'] }}: </strong><span class="inv-note" style="font-weight: normal;">{{@$comment_to_customer != null ? @$comment_to_customer->note : 'No Note'}}</span>
      </p>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-6 pr-4 mt-5">
      <p><strong>Comment To Warehouse: </strong><span class="inv-note" style="font-weight: normal;">{{@$comment != null ? @$comment->note : 'No Note'}}</span>
      </p>
    </div>
  </div>
  <div class="row pt-5">

    <div class="col-lg-8 col-md-8 pb-md-3">
      <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">

      <table class="table-order-history entriestable table table-bordered text-center" style="width: 100%;font-size: 12px;">
         <thead class="sales-coordinator-thead ">
          <tr>
            <th>User  </th>
            <th>Date/time </th>
            <th>Item#</th>
            <th>Column</th>
            <th>Old Value</th>
            <th>New Value</th>
          </tr>
         </thead>
       </table>
      </div>

    </div>
  </div>




</div>

</div>
</div>
</div>

<!-- export pdf form starts -->
  <form class="export-pi-form" method="post" action="{{url('warehouse/export-pi-to-pdf/'.$id)}}">
    @csrf
    <input type="hidden" name="pi_id" id="pi_id" value="{{$id}}">
    <input type="hidden" name="order" id="order" value="order">
    <input type="hidden" name="column_name" id="column_name" value ="column_name">
  </form>
  <!-- export pdf form ends -->

  <!-- Modal For Add Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Product Note</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-compl-quot-note-form" method="post">
      <div class="modal-body">
        <div class="row">
              <div class="col-md-12">
                      <div class="row">
                          <div class="col-xs-12 col-md-12">
                              <div class="form-group">
                                <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                                <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                              </div>
                          </div>
                      </div>
              </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="completed_quot_id" class="note-completed-quotation-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>

<!-- Modal for Showing Notes  -->
<div class="modal" id="notes-modal" style="width:600px; margin-left: 400px;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Product Notes</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-notes">
            <div class="adv_loading_spinner3 d-flex justify-content-center">
                <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

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

  var order = 1;
  var column_name = '';

$('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    $('#order').val(order);
    $('#column_name').val(column_name);


    $('.pick-instruction-table').DataTable().ajax.reload();

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


  // $( document ).ready(function() {
  //   $("#expiration_date").datepicker({
      // format: "dd/mm/yyyy",
      // autoHide: true,
  //   });
  // });


  // export pdf code
  // $(document).on('click', '.export-pdf', function(e){
  //   var pi_id = $('#pi_id').val();
  //   $('.export-pi-form')[0].submit();

  // });

  $(document).on('click','.stricker-pdf',function(e){
    // var pi_id = $('#pi_id').val();
    var orders = "{{$id}}";
    // alert(orders);
    var url = "{{url('warehouse/export-stricker-to-pdf')}}"+"/"+orders;
    window.open(url, 'Orders Sticket', 'width=1200,height=600,scrollbars=yes');
  });

  $(document).on('click', '.export-pdf', function(e){
    // alert('hi');
    var pi_id = $('#pi_id').val();
    var default_sort = $('#order').val();
    var column_name = $('#column_name').val();
    // $('.export-pi-form')[0].submit();

     var orders = "{{$id}}";

     var url = "{{url('warehouse/export-pi-to-pdf')}}"+"/"+orders+"/"+column_name+"/"+default_sort;
          window.open(url, 'Orders Print', 'width=1200,height=600,scrollbars=yes');
  });

  $(function(e){

   var id = $('#order_id').val();

  var table2 = $('.pick-instruction-table').DataTable({
    "sPaginationType": "listbox",
    processing: true,
    colReorder: {
          realtime: false,
        },
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu:[100, 200, 300, 400],
    "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,5,6,7,8,9] },
    { className: "dt-body-right", "targets": [4] }
  ],
    ajax:{
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        url:"{{ url('warehouse/get-pick-instruction')}}"+"/"+id,
        data : function(data) {
        data.sort_order = order,
        data.column_name = column_name
      }

        },

    columns: [
        { data: 'item_no', name: 'item_no' },
        { data: 'description', name: 'description' },
        { data: 'location_code', name: 'location_code' },
        { data: 'unit_of_measure', name: 'unit_of_measure' },
        // { data: 'qty_to_ship', name: 'qty_to_ship' },
        { data: 'unit_price', name: 'unit_price' },
        { data: 'notes', name: 'notes' },
        { data: 'pcs_ordered', name: 'pcs_ordered' },
        { data: 'pcs_shipped', name: 'pcs_shipped' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'qty_shipped', name: 'qty_shipped' },
        { data: 'expiration_date', name: 'expiration_date' },
        { data: 'current_qty', name: 'current_qty' },
        { data: 'reserved_qty', name: 'reserved_qty' },

    ],initComplete: function () {
          @if($display_my_quotation)
            table2.colReorder.order( [{{$display_my_quotation->display_order}}]);
          @endif

          // Enable THEAD scroll bars
          $('.dataTables_scrollHead').css('overflow', 'auto');

          // Sync THEAD scrolling with TBODY
          $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
    },
    fnDrawCallback:function(){ $('.expiration_date').datepicker({
      format: "dd/mm/yyyy",
      autoHide: true,
    })},
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
  });

  table2.on( 'column-reorder', function ( e, settings, details ) {
    $.get({
      url : "{{ route('column-reorder') }}",
      dataType : "json",
      data : "type=pick_instruction_detail&order="+table2.colReorder.order(),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('hide');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
      },
      error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
    });
     //console.log(table.colReorder.order());
     table2.button(0).remove();
     table2.button().add(0,
     {
       extend: 'colvis',
       autoClose: false,
       fade: 0,
       columns: ':not(.noVis)',
       colVis: { showAll: "Show all" }
     });

 // table2.ajax.reload();
 var headerCell = $( table2.column( details.to ).header() );
   headerCell.addClass( 'reordered' );
  });

  $('.table-order-history').DataTable({
    "sPaginationType": "listbox",
       processing: false,
    //   "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      searching:false,
      "lengthChange": false,
      serverSide: true,
      "scrollX": true,
      scrollY : '50vh',
      scrollCollapse: true,
      "bPaginate": false,
      "bInfo":false,
      lengthMenu: [ 100, 200, 300, 400],
      "columnDefs": [
        { className: "dt-body-left", "targets": [] },
        { className: "dt-body-right", "targets": [] },
      ],
       ajax: {

          url:"{!! route('get-order-history') !!}",
          data: function(data) { data.order_id = id } ,
          },
        columns: [
          // { data: 'checkbox', name: 'checkbox' },
          { data: 'user_name', name: 'user_name' },
          { data: 'created_at', name: 'created_at' },
          { data: 'item', name: 'item' },
          // { data: 'name', name: 'name' },
          { data: 'column_name', name: 'column_name' },
          { data: 'old_value', name: 'old_value' },
          { data: 'new_value', name: 'new_value' },
          ]
      });

  $(document).on('click','.full_pcs_btn', function(e){
    var po_id = $(this).data('id');
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
      type: "post",
      data: {id: po_id},
      url: "{{ route('full-pcs-ship-importing') }}",
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        toastr.success('Success!', 'All Pieces Shipped Updated Successfully',{"positionClass": "toast-bottom-right"});
        $('.pick-instruction-table').DataTable().ajax.reload();
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });
  $(document).on('click','.full_qty_btn', function(e){
    var po_id = $(this).data('id');
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
      type: "post",
      data: {id: po_id},
      url: "{{ route('full-qty-ship-importing') }}",
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        toastr.success('Success!', 'All QTY Shipped Updated Successfully',{"positionClass": "toast-bottom-right"});
        $('.pick-instruction-table').DataTable().ajax.reload();
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    $(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on("dblclick",".expiration_date",function(){
    $(this).removeAttr('disabled');
    $(this).addClass('active');
    $('.expiration_date').datepicker({
      format: "dd/mm/yyyy",
      autoHide: true,
    })
    // $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on('keypress keyup focusout',".fieldFocus",function(e) {
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled',true);
      thisPointer.removeClass('active');
      thisPointer.attr('readonly',true);
    }
    if($(this).val() != ''){
      if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
        var fieldvalue = $(this).data('fieldvalue');
        $(this).removeClass('active');
        $(this).attr('disabled','true');
        $(this).attr('readonly','true');
        if($(this).val().length < 1 || $(this).val() == fieldvalue)
        {
          return false;
        }
        else
        {
          var order_product_id= $(this).data('id');
          var thisPointer = $(this);
          saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),order_product_id);
          thisPointer.data('fieldvalue',thisPointer.val());
        }
    }
   }
  });

  $(document).on("change",".expiration_date",function(e) {
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled',true);
      thisPointer.removeClass('active');
      // thisPointer.attr('readonly',true);
    }
    if($(this).val() != '')
    {
      var fieldvalue = $(this).data('fieldvalue');
      $(this).removeClass('active');
      $(this).attr('disabled','true');
      // $(this).attr('readonly','true');
      if($(this).val().length < 1 || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var order_product_id= $(this).data('id');
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),order_product_id);
        thisPointer.data('fieldvalue',thisPointer.val());
      }
    }
  });

  function saveSupData(thisPointer,field_name,field_value,order_product_id){

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/edit-pick-instruction') }}",
        dataType: 'json',
        data: 'order_product_id='+order_product_id+'&'+field_name+'='+field_value,
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            {{-- $(".pick-instruction-table").DataTable().ajax.reload(); --}}
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

            return true;
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  }
  var pi_redirection_config = null;
  $(document).on('click','.complete_btn', function(e){
    var order_id = $(this).data('id');   //po_Group id
    var page_info = "pickInstruction";
    pi_redirection_config = $(this).data('pi_redirection_config');
    swal({
      title: "Are you sure!!!",
      text: "You want to confirm Pick Instruction?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, confirm it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
      },
    function (isConfirm) {
      if (isConfirm) {
        $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });

        $.ajax({
          method:"post",
          data:'order_id='+order_id+'&page_info='+page_info,
          url: "{{ route('confirm-order-pick-instruction') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
              //  $("#loader_modal").modal('show');
              $('#loader_modal_old').modal({
                  backdrop: 'static',
                  keyboard: false
                });
             $("#loader_modal_old").modal('show');
             $(".complete_btn").attr('disabled',true);
          },
          success: function(response){

            // if (response.success === true) {
            //     setTimeout(
            //         function(){
            //         console.log("Calling Function Again");
            //             recursiveCallForPIJob();
            //         }, 1000);
            // }
            if(response.success === true){
                toastr.success('Success!', 'Pick Instruction Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
                if (pi_redirection_config == 1) {
                    swal({
                        title: "Are you sure?",
                        text: "Do you need to go to inv# "+response.full_inv_no+" now ?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        closeOnConfirm: true,
                        closeOnCancel: false
                    },
                    function (isConfirm) {
                        if (isConfirm) {
                            window.location.href = "{{ url('sales/get-completed-invoices-details')}}/"+response.order_id;
                        }
                        else {
                            window.location.href = "{{ url('warehouse')}}";
                        }
                    });
                }
                else{
                    window.location.href = "{{ url('warehouse')}}";
                }
            }
            if(response.already_confirmed == true)
            {
              toastr.info('Alert!', 'Pick Instruction Already Confirmed !!!',{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('warehouse')}}";
            }

            if(response.qty_shipped == 'is_null'){
              toastr.warning('Alert!', 'Qty Shipped Cannot Be Null For '+response.product+' .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.pcs_shipped == 'is_null'){
              toastr.warning('Alert!', 'Pcs Shipped & Qty Shipped Cannot Be Null For '+response.product+' .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }

            if(response.stock_qty == 'less_than_order'){
              toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Less Than Order QTY .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.stock_qty == 'less_than_zero'){
              toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Less Than Zero .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.stock_qty == 'equals_to_zero'){
              toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Zero .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.available_qty == 'less_than_order'){
              toastr.error('Alert!', 'Available Qty For '+response.product+' Is Less Than Order QTY .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.available_qty == 'less_than_zero'){
              toastr.error('Alert!', 'Available Qty For '+response.product+' Is Less Than Zero .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.available_qty == 'equals_to_zero'){
              toastr.error('Alert!', 'Available Qty For '+response.product+' Is Zero.It Should Be Greater Than Zero .',{"positionClass": "toast-bottom-right"});
            //   $("#loader_modal").modal('hide');
              $("#loader_modal_old").modal('hide');
              $(".complete_btn").attr('disabled',false);
            }
            if(response.duplicate == true)
            {
             // $(".complete_btn").attr('disabled',false);
              // $('.complete_btn').trigger('click');
              swal({
                title: "Duplication!",
                text: "There is a chance of generating duplicate invoice. Please try again to avoid duplication !!!",
                type: "info",
                showCancelButton: false,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "OK",
                closeOnConfirm: true,
                closeOnCancel: false
              },
                function(isConfirm){
                  if(isConfirm){
                    window.location.reload();
                  }
                }
              );
            }

          },
          error: function(request, status, error){
            // $("#loader_modal").modal('hide');
            $("#loader_modal_old").modal('hide');
            $(".complete_btn").attr('disabled',true);
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

 });

 function recursiveCallForPIJob()
  {
    $.ajax({
          method:"get",
          url: "{{ route('recersive_call_for_pi_job') }}",
          success: function(response){

            if (response.status == 1) {
                setTimeout(
                    function(){
                    console.log("Calling Function Again");
                        recursiveCallForPIJob();
                    }, 1000);
            }
            else if (response.status == 2)
            {
                $('#loader_modal').modal('hide');
                toastr.error('Error!', 'Somthing went wrong!',{"positionClass": "toast-bottom-right"});
            }
            else{
                $('#loader_modal').modal('hide');

                if(response.success === true){
                    toastr.success('Success!', 'Pick Instruction Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
                    let pi_redirection_config = "{{ $pi_redirection_config != null ? $pi_redirection_config->display_prefrences : ''}}";
                    if (pi_redirection_config == 1) {
                        swal({
                            title: "Are you sure?",
                            text: "Do you need to go to inv# "+response.full_inv_no+" now ?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Yes",
                            cancelButtonText: "No",
                            closeOnConfirm: true,
                            closeOnCancel: false
                        },
                        function (isConfirm) {
                            if (isConfirm) {
                                window.location.href = "{{ url('sales/get-completed-invoices-details')}}/"+response.order_id;
                            }
                            else {
                                window.location.href = "{{ url('warehouse')}}";
                            }
                        });
                    }
                    else{
                        window.location.href = "{{ url('warehouse')}}";
                    }
                }
                if(response.already_confirmed == true)
                {
                    toastr.info('Alert!', 'Pick Instruction Already Confirmed !!!',{"positionClass": "toast-bottom-right"});
                    window.location.href = "{{ url('warehouse')}}";
                }

                if(response.qty_shipped == 'is_null'){
                    toastr.warning('Alert!', 'Qty Shipped Cannot Be Null For '+response.product+' .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.pcs_shipped == 'is_null'){
                    toastr.warning('Alert!', 'Pcs Shipped & Qty Shipped Cannot Be Null For '+response.product+' .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }

                if(response.stock_qty == 'less_than_order'){
                    toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Less Than Order QTY .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.stock_qty == 'less_than_zero'){
                    toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Less Than Zero .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.stock_qty == 'equals_to_zero'){
                    toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Zero .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.available_qty == 'less_than_order'){
                    toastr.error('Alert!', 'Available Qty For '+response.product+' Is Less Than Order QTY .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.available_qty == 'less_than_zero'){
                    toastr.error('Alert!', 'Available Qty For '+response.product+' Is Less Than Zero .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.available_qty == 'equals_to_zero'){
                    toastr.error('Alert!', 'Available Qty For '+response.product+' Is Zero.It Should Be Greater Than Zero .',{"positionClass": "toast-bottom-right"});
                    $("#loader_modal").modal('hide');
                    $(".complete_btn").attr('disabled',false);
                }
                if(response.duplicate == true)
                {
                // $(".complete_btn").attr('disabled',false);
                // $('.complete_btn').trigger('click');
                    swal({
                        title: "Duplication!",
                        text: "There is a chance of generating duplicate invoice. Please try again to avoid duplication !!!",
                        type: "info",
                        showCancelButton: false,
                        confirmButtonClass: "btn-danger",
                        confirmButtonText: "OK",
                        closeOnConfirm: true,
                        closeOnCancel: false
                    },
                        function(isConfirm){
                            if(isConfirm){
                                window.location.reload();
                            }
                        }
                    );
                }
            }
          },
          error: function (request, status, error)
          {
            toastr.error('Error!', 'Somthing went wrong!',{"positionClass": "toast-bottom-right"});
          }
        });
  }

  $(document).on('click','.export_new',function(){
    var id = "{{@$id}}";
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/export-pi-to-pdf') }}"+'/'+id,
        dataType: 'json',
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.success == true)
          {
            //$(".pick-instruction-table").DataTable().ajax.reload(null, false );
            // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            // return true;
                  var opt = {
            margin:       0.3,
            filename:     'myfile.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
          };
        //         html2pdf()
        // .set({ html2canvas: { scale: 4 } })
        //   .from(data.view)
        //   .save();
          html2pdf().set(opt).from(data.view).save();

          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  })
  $(document).on('click', '.add-notes', function(e){
      var completed_draft_id = $(this).data('id');
      $('.note-completed-quotation-id').val(completed_draft_id);
      // alert(customer_id);

  });
  $('.add-compl-quot-note-form').on('submit', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
      $.ajax({
        url: "{{ route('add-completed-quotation-prod-note') }}",
        dataType: 'json',
        type: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          $('#loader_modal').modal('hide');
          if(result.success == true){
            toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
            $('.table-quotation-product').DataTable().ajax.reload();

            // setTimeout(function(){
            //   window.location.reload();
            // }, 2000);

            $('.add-compl-quot-note-form')[0].reset();
            $('#add_notes_modal').modal('hide');

          }else{
            toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
          }

        },
        error: function (request, status, error) {
              /*$('.form-control').removeClass('is-invalid');
              $('.form-control').next().remove();*/
              $('#loader_modal').modal('hide');
              $('.save-btn').removeClass('disabled');
              $('.save-btn').removeAttr('disabled');
              json = $.parseJSON(request.responseText);
              $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                    $('input[name="'+key+'"]').addClass('is-invalid');
                    $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                    $('textarea[name="'+key+'"]').addClass('is-invalid');


              });
          }
      });
  });

  //Show notes modal
  $(document).on('click', '.show-notes', function(e){
    let compl_quot_id = $(this).data('id');
    $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    $.ajax({
      type: "get",
      url: "{{ route('get-completed-quotation-prod-note') }}",
      data: 'compl_quot_id='+compl_quot_id,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-notes').html(loader_html);
      },
      success: function(response){
        // console.log(response);
        $('.fetched-notes').html(response);
      },
      error: function(request, status, error){

      }
    });

  });

  //Delete Note
  $(document).on('click', '#delete-compl-note', function(e){
    let note_id = $(this).data('id');
    let compl_quot_id = $(this).data('compl_quot_id');
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
    // By Farooq
    swal({
        title: "Alert!",
        text: "Are you sure you want to remove this note?",
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
      type: "get",
      url: "{{ route('delete-completed-quot-prod-note') }}",
      data: 'note_id='+note_id,
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").modal('show');
      },
      success: function(response){
        $("#loader_modal").modal('hide');
        $.ajax({
            type: "get",
            url: "{{ route('get-completed-quotation-prod-note') }}",
            data: 'compl_quot_id='+compl_quot_id,
            success: function(response){
              if(response.no_data == true){
            $('.table-quotation-product').DataTable().ajax.reload();
                $("#notes-modal").modal('hide');
              }
              $('.fetched-notes').html(response);
            }
      });
        // window.location.reload();
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });
        }
        else{
          swal("Cancelled", "", "error");
        }
     });

  });

  $(document).on('keyup', function(e) {
  if (e.keyCode === 27){ // esc
    $(".expiration_date").datepicker('hide');
  }
});
</script>
@stop

