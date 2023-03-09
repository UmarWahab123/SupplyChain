@extends('warehouse.layouts.layout')
@section('title','Receiving Queue')
<?php
use Carbon\Carbon;
?>

@section('content')
{{-- Content Start from here --}}
<style type="text/css">
  .dataTables_scroll{
    max-height: 571px;
    overflow-y: scroll;
  }
</style>
<div class="right-content pt-0">
  <div class="row headings-color">
   <input type="hidden" name="id" id="po_group_id" value="{{$po_group->id}}">
    <div class="col-lg-3 d-flex align-items-center">
      <h4>Group No {{$po_group->ref_id}}<br>Product Receiving Records</h4>
    </div>  
    <div class="col-lg-6">
      
    </div>
    <div class="col-lg-3 d-flex align-items-center bg-white p-2">
      <table class="table table-bordered mb-0">
        <tbody>
          <tr>
            <th>AWB:B/L</th>
            <td>{{$po_group->bill_of_landing_or_airway_bill != null ? $po_group->bill_of_landing_or_airway_bill : "N.A" }}</td>
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

  <div class="row headings-color">
    <div class="col-lg-4 d-flex align-items-center fontbold mb-3">
      <a href="{{ route('warehouse-incompleted-po-groups') }}">
        <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button>
      </a>  
     
      @if(@$group_detail < 11)
      <a href="javascript:void(0);" class="d-none">
        <button type="button" class="d-none btn-color btn text-uppercase purch-btn headings-color export-pdf">print</button>
      </a>
      @endif
       <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export_new mr-3">print</button>
    </div>
  </div>
  <!-- export pdf form starts -->
  <form class="export-group-form" method="post" action="{{url('warehouse/completed-export-group-to-pdf')}}">
    @csrf
    <input type="hidden" name="po_group_id" id="group_id_for_pdf" value="{{$po_group->id}}">
  </form>

<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 p-0">
        <div class="bg-white table-responsive p-3">
          <table class="table headings-color entriestable text-center table-bordered product_table" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>Po No.</th>
               <th>Order Warehouse</th>
               <th>Order #</th>
               <th>Supplier <br> Reference Name</th>
               <th>Sup's Reference #</th>
               <th>{{$global_terminologies['our_reference_number']}}</th>
               <th>{{$global_terminologies['product_description']}}</th>
               <th>Billed Unit</th>
               <th>{{$global_terminologies['qty']}} Ordered</th>
               <th>{{$global_terminologies['qty']}} Inv</th>
               <th>{{$global_terminologies['qty']}} Rcvd 1</th>
               <th>Expiration Date 1</th>
               <th>{{$global_terminologies['qty']}} Rcvd 2</th>
               <th>Expiration Date 2</th>
               <th>Goods Condition </th>
               <th>Results </th>
               <th>Goods Type </th>
               <th>{{$global_terminologies['temprature_c']}} </th>
               <th>Checker </th>
               <th>Problem Found  </th>
               <th>Solution </th>
               <th>Authorized Changes</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-lg-3 col-md-3 offset-9">
        <a href="javascript:void(0);">
          <button type="button" class="btn float-right btn-success save-and-close-btn" onclick="window.history.back();">Save & Close</button>
        </a>
      </div>
    </div>


    <div class="row mb-3">
      <div class="col-lg-6 p-0">
        <div class="bg-white p-3">
          <div style="max-height: 571px;overflow-y: scroll;">
          <table class="my-tablee table-bordered text-center">
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
            <tr style="border: 1px solid #eee;">
              <td align="left" style="padding: 5px;">{{$history->get_user->name}}</td>
              <td align="left">{{Carbon::parse(@$history->created_at)->format('d-M-Y, H:i:s')}}</td>                 
              <td align="left">{{@$history->get_pod->product->short_desc}}</td>
              <td align="left">{{$history->term_key}}</td>
              <td align="left">{{$history->old_value == null ? 0 : $history->old_value}}</td>                 
              <td align="left">{{$history->new_value}}</td>                 
            </tr>                 
            @endforeach  
            @else
            <tr style="border: 1px solid #eee;">
              <td colspan="6"><center>No Data Available in Table</center></td>
            </tr> 
            @endif
           </tbody>
          </table>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="purchase-order-detail table-responsive">
          <div class="bg-white p-3">
            <table class="my-tablee table-bordered">
             <thead class="sales-coordinator-thead ">
               <tr>                
                <th>User  </th>
                <th>Date/time </th>
                <th>Status </th>
                <th>New Status</th>
               </tr>
             </thead>
             <tbody>
                @if($status_history->count() > 0)
                @foreach($status_history as $history)
                <tr>
                  <td>{{$history->get_user->name}}</td>
                  <td>{{Carbon::parse(@$history->created_at)->format('d-M-Y, H:i:s')}}</td>                 
                  <td>{{$history->status}}</td>
                  <td>{{$history->new_status}}</td>                 
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
</div>
<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
<div class="modal-dialog modal-sm">
  <div class="modal-content">
    <div class="modal-body">
      <h3 style="text-align:center;">Downloading...</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
    </div>
  </div>
</div>
</div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(e){  

    // export pdf code
  $(document).on('click', '.export-pdf', function(e){
    var po_group_id = $('#po_group_id').val(); 
    $('.export-group-form')[0].submit();
  });

  $(document).on('click', '.save-and-close-btn', function(e){
    setTimeout(function(){
      window.location.href = "{{ url('warehouse/warehouse-incompleted-po-groups')}}";
    }, 500);
  });

  var id = $('#po_group_id').val();
    
  var table2 = $('.product_table').DataTable({
     processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
     scrollX: true,
     // scrollY : '90vh',
    scrollCollapse: true,
  pageLength: {{100}},
    lengthMenu: [ 100, 200, 300, 400],
    "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21 ] },
    { className: "dt-body-right", "targets": [] }
  ],
    
    ajax:"{{ url('warehouse/get-details-of-completed-po-group')}}"+"/"+id,
    columns: [
        { data: 'po_number', name: 'po_number' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'order_no', name: 'order_no' },
        { data: 'supplier', name: 'supplier' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'desc', name: 'desc' },
        { data: 'unit', name: 'unit' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'qty_inv', name: 'qty_inv' },
        { data: 'qty_receive', name: 'qty_receive' },
        { data: 'expiration_date', name: 'expiration_date' },
        { data: 'quantity_received_2', name: 'quantity_received_2' },
        { data: 'expiration_date_2', name: 'expiration_date_2' },
        { data: 'goods_condition', name: 'goods_condition' },
        { data: 'results', name: 'results' },
        { data: 'goods_type', name: 'goods_type' },
        { data: 'goods_temp', name: 'goods_temp' },
        { data: 'checker', name: 'checker' },
        { data: 'problem_found', name: 'problem_found' },
        { data: 'solution', name: 'solution' },
        { data: 'changes', name: 'changes' },
    ],
    initComplete: function () {
        // Enabled THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });

      }
  });

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocus',function(e) { 
    console.log(e.keyCode+' '+e.which);
    var fieldvalue = $(this).data('fieldvalue');
    if (e.keyCode == 27) {
      var thisPointer = $(this);
      thisPointer.val(fieldvalue);
      thisPointer.attr('disabled','true');
      thisPointer.attr('readonly','true');
      }
    if(e.keyCode == 13 || e.which == 0){
      
      if($(this).val() == '' || $(this).val() == fieldvalue)
      {
        return false;
      }
      else
      {
        var pod_id= $(this).data('id');    
        var thisPointer = $(this);
        saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id);
        $(this).data('fieldvalue',thisPointer.val());
      }
      $(this).attr('disabled','true');
      $(this).attr('readonly','true');
    } 
  });

  function saveSupData(thisPointer,field_name,field_value,pod_id){   
    var po_group_id = $('#po_group_id').val();   
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/edit-po-group-details') }}",
        dataType: 'json',
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value+'&po_group_id='+po_group_id,
        success: function(data)
        {
          if(data.success == true)
          {
            //$(".product_table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
          else if(data.success == false){
            var extra_quantity = data.extra_quantity;
            $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                  }
              });
            $.ajax({
              type: "post",
              url: "{{ route('get-incomplete-pos') }}",
              data: 'pod_id='+pod_id+'&extra_quantity='+extra_quantity,
              
              success: function(response){
                if(response.success == true){
                  $('#greater_quantity').modal('show');
                  $('.fetched-po').html(response.html_string);
                }
                else{
                  $(".product_table").DataTable().ajax.reload(null, false );
                  if(response.extra_quantity == true){
                  swal('The QTY You Entered Cannot be divided accordingly');                    
                  }
                  else{                    
                  swal('You Cannot Enter QTY Greater Than the Required QTY');
                  }
                }
              }
            });

           
          }
        },

      });
  }

});

 $(document).on('click','.export_new',function(){
  // alert('he');
  var id = "{{@$id}}";
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/completed-export-group-to-pdf') }}",
        dataType: 'json',
        data:{po_group_id:id},
         beforeSend:function(){
                          $('#loader_modal').modal({
                              backdrop: 'static',
                              keyboard: false
                          });
                          $("#loader_modal").modal('show');
                      },
        success: function(data)
        {
          if(data.success == true)
          {
            //$(".pick-instruction-table").DataTable().ajax.reload(null, false );
            // toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            // return true;
                  var opt = {
            margin:       0.1,
            filename:     'Group NO-'+id+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' },
            pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
          };
       
          html2pdf().set(opt).from(data.view).save().then(function(){
          $("#loader_modal").modal('hide');
            // alert('done');
          });


          }
        },

      });
})
</script>
@stop

