@extends('warehouse.layouts.layout')
@section('title','Receiving Queue')
<?php
use Carbon\Carbon;
?>

@section('content')
{{-- Content Start from here --}}
<div class="right-content pt-0">
  <div class="row headings-color">
   <input type="hidden" name="id" id="po_group_id" value="{{$po_group->id}}">
    <div class="col-lg-4 col-md-6 d-flex align-items-center">
      <h4>Group No {{$po_group->ref_id != null ? $po_group->ref_id : 'N.A' }}<br>Product Receiving Records</h4>
    </div>  
    <div class="col-lg-5 col-md-2"></div>

    <div class="col-lg-3 col-md-4 d-flex align-items-center bg-white p-2">
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

  <div class="row headings-color">
    <div class="col-lg-4 col-md-4 d-flex align-items-center fontbold mb-3">
      <a href="{{ route('warehouse-incompleted-po-groups') }}">
        <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button>
      </a>  
      <a href="javascript:void(0);" class="d-none">
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf">print</button>
      </a>
@if(@$group_detail < 11)
       <a href="javascript:void(0);" class="ml-1 d-none">
        <button type="button" class="d-none btn-color btn text-uppercase purch-btn headings-color export-pdf2">print</button>
      </a>
      @endif
      <!-- code for print pdf through javascript -->
        <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export_new mr-3">prin t</button>
    </div>
  </div>
  <!-- export pdf form starts -->
  <form class="export-group-form" method="post" action="{{url('importing/export-group-to-pdf')}}">
    @csrf
    <input type="hidden" name="po_group_id" id="group_id_for_pdf" value="{{$po_group->id}}">
  </form>

  <!-- export pdf2 form starts -->
  <form class="export-group-form2" method="post" action="{{url('warehouse/export-group-to-pdf2')}}">
    @csrf
    <input type="hidden" name="po_group_id" value="{{$po_group->id}}">
  </form>
<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class="col-lg-12 p-0">
        <div class="bg-white p-3">
          <table class="table headings-color entriestable text-center table-bordered product_table  table-responsive" id="receive-table" style="width:100%">
            <thead class="sales-coordinator-thead ">
              <tr>
               <th>Po No.</th>
               <th>Order <br>Warehouse</th>
               <th>Order #</th>
               <th>Supplier <br> Reference<br> Name</th>
               <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>
               <th>{{$global_terminologies['our_reference_number']}}</th>
               <th>{{$global_terminologies['product_description']}}</th>
               <th>Buying<br> Unit</th>
               <th>{{$global_terminologies['qty']}} <br>Ordered</th>
               <th>{{$global_terminologies['qty']}} <br>Inv</th>
               <th>{{$global_terminologies['qty']}} <br>Rcvd 1</th>
               <th>Expiration <br>Date 1</th>
               <th>{{$global_terminologies['qty']}} <br>Rcvd 2</th>
               <th>Expiration <br>Date 2</th>
               <th>Goods <br>Condition </th>
               <th>Results </th>
               <th>Goods <br>Type </th>
               <th>{{$global_terminologies['temprature_c']}} </th>
               <th>Checker </th>
               <th>Problem <br>Found  </th>
               <th>Solution </th>
               <th>Authorized <br>Changes</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-3">
      <div class="col-lg-9 col-md-9"></div>
      <div class="col-lg-3 col-md-3">
        <a href="javascript:void(0);">
          <button type="button" data-id="{{$po_group->id}}" class="btn-color btn float-right confirm-po-group"><i class="fa fa-check"></i>Confirm to Stock</button>
        </a>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-lg-6 p-0">
        <div class="table-responsive">
          <table class="my-tablee table-bordered bg-white">
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
              <td style="padding: 5px;">{{$history->get_user->name}}</td>
              <td>{{Carbon::parse(@$history->created_at)->format('d-M-Y, H:i:s')}}</td>                 
              <td>{{@$history->get_pod->product->short_desc}}</td>
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
      <div class="col-lg-6">
        <div class="purchase-order-detail table-responsive">
          <table class="my-tablee table-bordered bg-white">
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

{{-- Greater Quantity Modal --}}
<div class="modal" id="greater_quantity">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order's </h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-po">
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
 $(document).on('click','.condition',function(){
  // alert('hi');
      var value = $(this).val();
      var id = $(this).data('id');

      savePoData(value,id);
  });

  function savePoData(value,id){   
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
       
        url: "{{ url('warehouse/edit-po-goods') }}",
        dataType: 'json',        
        data: 'value='+value+'&'+'id'+'='+id,
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            $("#receive-table").DataTable().ajax.reload(null, false );
            return true;
          }
        },

      });
    }
  var id = $('#po_group_id').val();
    
  var table2 = $('.product_table').DataTable({
     processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    lengthMenu: [ 100, 200, 300, 400],
    ajax:"{{ url('warehouse/get-details-of-po')}}"+"/"+id,
    columns: [
        { data: 'po_number', name: 'po_number' },
        { data: 'order_warehouse', name: 'order_warehouse' },
        { data: 'order_no', name: 'order_no' },
        { data: 'supplier', name: 'supplier' },
        { data: 'reference_number', name: 'reference_number' },
        { data: 'prod_reference_number', name: 'prod_reference_number' },
        { data: 'desc', name: 'desc' },
        { data: 'kg', name: 'kg' },
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

  $(document).on("dblclick",".inputDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
  });

   $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  // to make that field on its orignal state
  $(document).on('keyup focusout','.fieldFocus',function(e) { 
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


  // confirm po button code here
  $(document).on('click','.confirm-po-group', function(e){
    var id = $(this).data('id');   //po_Group id
    swal({
      title: "Are you sure!!!",
      text: "You want to confirm?",
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
          data:'id='+id,
          url: "{{ route('confirm-warehouse-po-group') }}",
          success: function(response){
              if(response.success === true){
              toastr.success('Success!', 'Product Received Into Stock Successfully.',{"positionClass": "toast-bottom-right"});              
              window.location.href = "{{ url('/warehouse')}}";              
            }
          }
        });
      }
      else 
      {
        swal("Cancelled", "", "error");
      }
    });
  });

    // export pdf code
  $(document).on('click', '.export-pdf', function(e){

    var po_group_id = $('#po_group_id').val(); 
    $('.export-group-form')[0].submit();
  });

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

     // export pdf2 code
  $(document).on('click', '.export-pdf2', function(e){

    var po_group_id = $('#po_group_id').val(); 
    $('.export-group-form2')[0].submit();
    /*
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
        });        
    $.ajax({
      method:"post",
      data:'po_group_id='+po_group_id,
      url: "{{ route('export-group-to-pdf') }}",
      success: function(response){
          if(response.success === true){
          toastr.success('Success!', 'Product Received Into Stock Successfully.',{"positionClass": "toast-bottom-right"});              
          window.location.href = "{{ url('/importing')}}";              
        }
      }
    });*/

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
        url: "{{ url('warehouse/export-group-to-pdf2') }}",
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
            margin:       0.3,
            filename:     'Group NO-'+id+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
          };
        //         html2pdf()
        // .set({ html2canvas: { scale: 4 } })
        //   .from(data.view)
        //   .save();
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

