@extends('importing.layouts.layout')
@section('title','Pick Instruction')


@section('content')

{{-- Content Start from here --}}

<div class="right-contentIn">
<input type="hidden" name="id" id="order_id" value="{{$order->id}}">
<div class="row mb-3 headings-color">

<div class="col-lg-2 d-flex align-items-center">
  <h4>Pick Instructions</h4>
</div>
<div class="col-lg-8 d-flex align-items-center fontbold">
  <ul class="list-unstyled d-flex align-items-center ware-house-list">
  <li>{{$order->ref_id}}</li>
    <li> &nbsp;&nbsp;|&nbsp;&nbsp; </li>
    <li>{{$order->customer->reference_number}}</li>
    <li> &nbsp;&nbsp;|&nbsp;&nbsp; </li>
    <li>{{$order->customer->company}}</li>
    <li> &nbsp;&nbsp;|&nbsp;&nbsp; </li>
    <li>{{$order->target_ship_date}}</li>
    <li>&nbsp;&nbsp;|&nbsp;&nbsp;</li>
    <li>Shipment Date</li>
  </ul>
</div>
<div class="col-lg-2 align-items-center">

</div>


<div class="col-lg-12">
<div class="bg-white table-responsive p-3">
  <table class="table entriestable headings-color pick-instruction-table table-bordered text-center " style="width:100%">
    <thead class="sales-coordinator-thead table-bordered">
      <tr>
        <th>Item#</th>
        <th>Description </th>
        <th>Location Code</th>
        <th>Pcs Ordered</th>
        <th>QTY Ordered</th>
        <th>Unit of Measure</th>
        <th>QTY to Ship</th>
        <th>Unit Price</th>
        <th>Pcs Shipped</th>
        <th>QTY Shipped</th>                                                  
      </tr>
    </thead>    
  </table>
  @if($order->status == 10)
  <div class="col-lg-2 pull-right pb-3">
    <div class="input-group-append">
      <button class="btn recived-button" data-id="{{$order->id}}" type="submit">Complete</button>  
    </div>
  </div>
  @endif
</div>

</div>
</div>
</div>

@endsection


@section('javascript')
<script type="text/javascript">
 $(function(e){  

   var id = $('#order_id').val();
    
  $('.pick-instruction-table').DataTable({
     processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu:[100, 200, 300, 400],
    "columnDefs": [
    { className: "dt-body-left", "targets": [ 0,1,2,3,4,5,6,8,9 ] },
    { className: "dt-body-right", "targets": [7] }
  ],
    ajax:"{{ url('importing/get-pick-instruction')}}"+"/"+id,
    columns: [
        { data: 'item_no', name: 'item_no' },
        { data: 'description', name: 'description' },
        { data: 'location_code', name: 'location_code' },
        { data: 'pcs_ordered', name: 'pcs_ordered' },
        { data: 'qty_ordered', name: 'qty_ordered' },
        { data: 'unit_of_measure', name: 'unit_of_measure' },
        { data: 'qty_to_ship', name: 'qty_to_ship' },
        { data: 'unit_price', name: 'unit_price' },
        { data: 'pcs_shipped', name: 'pcs_shipped' },
        { data: 'qty_shipped', name: 'qty_shipped' },
    ]
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    $(this).addClass('active');
    $(this).removeAttr('readonly');
    $(this).focus();
  });

  $(document).on("keypress",".fieldFocus",function(e) { 
    if($(this).val() != ''){
      if(e.keyCode === 13){
        var fieldvalue = $(this).data('fieldvalue');
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
          $(this).data('fieldvalue',thisPointer.val());
        }
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
        url: "{{ url('importing/edit-pick-instruction') }}",
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
          $('#loader_modal').modal('hide');
          if(data.success == true)
          {
            //$(".pick-instruction-table").DataTable().ajax.reload(null, false );
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }

      });
  }

  /*$(document).on('click','.recived-button', function(e){
    var order_id = $(this).data('id');   //po_Group id
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
          data:'order_id='+order_id,
          url: "{{ route('confirm-order-pick-instruction') }}",
          success: function(response){
              if(response.success === true){
              toastr.success('Success!', 'Pick Instruction Confirmed Successfully.',{"positionClass": "toast-bottom-right"});              
              window.location.href = "{{ url('warehouse')}}";              
            }
          }
        });
      }
      else 
      {
        swal("Cancelled", "", "error");
      }
    });
  });*/

 });
</script>
@stop

