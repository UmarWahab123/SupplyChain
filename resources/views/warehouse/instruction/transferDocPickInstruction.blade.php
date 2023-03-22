@extends('warehouse.layouts.layout')
@section('title','Pick Instruction')


@section('content')

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
          <li class="breadcrumb-item active">Pick Instructions</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}

<div class="right-contentIn">
<input type="hidden" name="id" id="order_id" value="{{$order->id}}">
<div class="row mb-3 headings-color">

<div class="col-lg-3 col-md-4 d-flex align-items-center">
  <h4 class="mb-lg-auto mb-md-auto">Pick Instructions</h4>
</div>
<div class="col-lg-6 col-md-3 d-flex align-items-center fontbold">
</div>
<div class="col-lg-3 col-md-5 d-flex align-items-center bg-white p-2">
  <table class="table table-bordered mb-0">
    <tbody>
      <tr>
        <th>TD. No</th>
        <td>{{$order->ref_id}}</td>
      </tr>
      <tr>
        <th>To Warehouse</th>
        <td>{{$order->to_warehouse_id != null ? $order->ToWarehouse->warehouse_title :" N.A"}}</td>
      </tr>
    </tbody>
    </table>
</div>

<div class="col-lg-12 mb-2 d-none">
  <a href="javascript:void(0);">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf mr-3">print</button>
     <button type="button" class="d-none btn-color btn text-uppercase purch-btn headings-color export_new mr-3">print</button>
  </a>
</div>
<div class="col-lg-12 mt-5">
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
        <th>Location Code
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="location">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="location">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
        </th>
        <th>Unit of <br>{{$global_terminologies['qty']}} Ordered Measure</th>
        <th>Unit Price
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="unit_price">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="unit_price">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
        </th>
        {{--<th>Pcs Ordered</th>
        <th>@if(!array_key_exists('pieces', $global_terminologies)) Pcs @else {{$global_terminologies['pieces']}} @endif Ordered Shipped</th>--}}
        <th>{{$global_terminologies['qty']}} Ordered
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
        </th>
        <th>{{$global_terminologies['qty']}} Shipped
        <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="quantity_shipped">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="quantity_shipped">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
        </th>
        <th>Expiration Date</th>
      </tr>
    </thead>
  </table>

  <div class="col-lg-6 col-md-6 col-sm-6 pr-4 mt-5 d-none">
    <p><strong>Comment To Warehouse: </strong><span class="inv-note" style="font-weight: normal;"></span></p>
  </div>


  @if($order->status != 22)
  <div class="col-lg-4 col-md-5 pull-right pb-3 mt-md-2">

    <div class="input-group-append">
      <button class="btn mr-5 recived-button full_qty_btn" data-id="{{$order->id}}">Full Quantity</button>
      <button class="btn recived-button complete_btn" data-id="{{$order->id}}" type="submit">Complete</button>
    </div>
  </div>
  @endif

</div>

</div>
</div>
</div>

<!-- export pdf form starts -->
<form class="export-pi-form" method="post" action="{{url('warehouse/export-pi-to-pdf/'.$id)}}">
  @csrf
  <input type="hidden" name="pi_id" id="pi_id" value="{{$id}}">
</form>
<!-- export pdf form ends -->


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

{{-- Stock Qty Modal --}}
<div class="modal stock_Modal" id="stock_Modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Quantity Ship</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="fetched-stock-details">
            <div class="d-flex justify-content-center">
              <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
            </div>
        </div>
      </div>
    </div>
  </div>
  {{-- end modal code--}}
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


    // export pdf code
  $(document).on('click', '.export-pdf', function(e){

    var pi_id = $('#pi_id').val();
    $('.export-pi-form')[0].submit();

  });

 $(function(e){

   var id = $('#order_id').val();

  $('.pick-instruction-table').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    // "language": {
    //   processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    lengthMenu:[100, 200, 300, 400],
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 1,2,3,5,6,7] },
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
        url:"{{ url('warehouse/get-transfer-pick-instruction')}}"+"/"+id,
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
      { data: 'unit_price', name: 'unit_price' },
      // { data: 'trasnfer_num_of_pieces', name: 'trasnfer_num_of_pieces' },
      // { data: 'trasnfer_pcs_shipped', name: 'trasnfer_pcs_shipped' },
      { data: 'qty_ordered', name: 'qty_ordered' },
      { data: 'trasnfer_qty_shipped', name: 'trasnfer_qty_shipped' },
      { data: 'trasnfer_expiration_date', name: 'trasnfer_expiration_date' },
    ],
      drawCallback: function(){
        $('.trasnfer_expiration_date').datepicker({
          format: "dd/mm/yyyy",
          autoHide: true,
        });
        $('#loader_modal').modal('hide');
      },
  });

  $(document).on("dblclick",".fieldFocus",function(){
    $(this).removeAttr('disabled');
    $(this).addClass('active');
    if(!$(this).hasClass('trasnfer_expiration_date')){
      $(this).removeAttr('readonly');
    }
    $(this).focus();
  });

  $(document).on("dblclick",".td_shipped_qty",function(){

    $('#stock_Modal').modal({
                backdrop: 'static',
                keyboard: false
            });
    $('#stock_Modal').modal('show');

    let pod_id = $(this).data('id');
    let product_id = $(this).data('product_id');
    let warehouse_id = '{{ $order->from_warehouse_id }}';
    let po_id = '{{$id}}';
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        type: "post",
        url: "{{ route('get-available-stock-of-product') }}",
        // url: "{{ route('get-reserved-stock-of-product') }}",
        data: {product_id:product_id, warehouse_id:warehouse_id, is_draft:false, po_id:po_id, pod_id:pod_id, pi_side:true},
        beforeSend: function(){
            var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
            var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
            $('.fetched-stock-details').html(loader_html);
        },
        success: function(response){
            $('.fetched-stock-details').html(response.html);
            $('#total_qty_span').html(response.shipped_total_qty);
        },
        error: function(request, status, error){
            // $('#loader_modal').modal('hide');
        }
    });
  });

  $(document).on("change",".trasnfer_expiration_date",function(e) {
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

  $(document).on("dblclick",".trasnfer_expiration_date",function(){
    $(this).removeAttr('disabled');
    $(this).addClass('active');
    $('.trasnfer_expiration_date').datepicker({
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
    if($(this).val() != '')
    {
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
          var pod_id= $(this).data('id');
          var thisPointer = $(this);
          saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),pod_id);
          thisPointer.data('fieldvalue',thisPointer.val());
        }
    }
   }
  });

  function saveSupData(thisPointer,field_name,field_value,pod_id){

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
        method: "post",
        url: "{{ url('warehouse/edit-transfer-pick-instruction') }}",
        dataType: 'json',
        data: 'pod_id='+pod_id+'&'+field_name+'='+field_value,
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
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
        },
        error: function(request, status, error){
          $('#loader_modal').modal('hide');
        }

      });
  }

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
      url: "{{ route('full-qty-ship') }}",
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
        // if(response.success === true)
        // {
        //   toastr.success('Success!', 'Pick Instruction Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
        //   window.location.href = "{{ url('warehouse')}}";
        // }
        // else if (response.success === false)
        // {
        //   toastr.error('Error!', response.errorMsg ,{"positionClass": "toast-bottom-right"});
        //   $(".complete_btn").attr('disabled',false);
        // }
      },
      error: function(request, status, error){
        $('#loader_modal').modal('hide');
      }
    });
  });

  $(document).on('click','.complete_btn', function(e){
    var po_id = $(this).data('id');   //po_Group id
    swal({
      title: "Are you sure!!!",
      text: "You want to confirm Pick Instruction? Once you confirmed, no changes will be allowed.",
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
          data:'po_id='+po_id,
          url: "{{ route('confirm-transfer-pick-instruction') }}",
          beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").modal('show');
            $(".complete_btn").attr('disabled',true);
          },
          success: function(response){
            $("#loader_modal").modal('hide');
            if(response.success === true)
            {
              toastr.success('Success!', 'Pick Instruction Confirmed Successfully.',{"positionClass": "toast-bottom-right"});
              window.location.href = "{{ url('warehouse')}}";
            }
            else if (response.success === false)
            {
              toastr.error('Error!', response.errorMsg ,{"positionClass": "toast-bottom-right"});
              $(".complete_btn").attr('disabled',false);
              if(response.redirect == 'dashboard')
              {
                setTimeout(function() {
                  window.location.href = "{{ url('warehouse')}}";
                }, 200);
              }
            }
            if(response.stock_qty == 'less_than_order'){
              toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Less Than Order QTY .',{"positionClass": "toast-bottom-right"});
              $("#loader_modal").modal('hide');
              $(".recived-button").attr('disabled',false);
            }
            if(response.stock_qty == 'less_than_zero'){
              toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Less Than Zero .',{"positionClass": "toast-bottom-right"});
              $("#loader_modal").modal('hide');
              $(".recived-button").attr('disabled',false);
            }
            if(response.stock_qty == 'equals_to_zero'){
              toastr.error('Alert!', 'Stock Qty For '+response.product+' Is Zero .',{"positionClass": "toast-bottom-right"});
              $("#loader_modal").modal('hide');
              $(".recived-button").attr('disabled',false);
            }
            if(response.available_qty == 'less_than_order'){
              toastr.error('Alert!', 'Available Qty For '+response.product+' Is Less Than Order QTY .',{"positionClass": "toast-bottom-right"});
              $("#loader_modal").modal('hide');
              $(".recived-button").attr('disabled',false);
            }
            if(response.available_qty == 'less_than_zero'){
              toastr.error('Alert!', 'Available Qty For '+response.product+' Is Less Than Zero .',{"positionClass": "toast-bottom-right"});
              $("#loader_modal").modal('hide');
              $(".recived-button").attr('disabled',false);
            }
             if(response.available_qty == 'equals_to_zero'){
              toastr.error('Alert!', 'Available Qty For '+response.product+' Is Zero.It Should Be Greater Than Zero .',{"positionClass": "toast-bottom-right"});
              $("#loader_modal").modal('hide');
              $(".recived-button").attr('disabled',false);
            }
          },
          error: function(request, status, error){
            $('#loader_modal').modal('hide');
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
$(document).on('click','.export_new',function(){
  alert('he');
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
          $('#loader_modal').modal('hide');
        }

      });
})

$(document).on('submit', '#save_qty_Form', function(e) {
    e.preventDefault();
    let po_id = '{{$id}}';
    let formData = new FormData(this);
    formData.append('po_id', po_id);
    formData.append('total_qty', $('#total_qty_span').text());
    formData.append('pi_side', true);
    formData.append('is_draft', false);
    $.ajax({
        type: "post",
        url: "{{ route('save-available-stock-of-product_in_td') }}",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").modal('show');
            $("#save_qty_in_reserved_table").prop('disabled', true);
        },
        success: function(response){
          if(response.success == false){
            toastr.error('Error!', response.message ,{"positionClass": "toast-bottom-right"});
            $("#loader_modal").modal('hide');
            $("#save_qty_in_reserved_table").prop('disabled', false);
          }
          else{
            $('#stock_Modal').modal('hide');
            $('.pick-instruction-table').DataTable().ajax.reload();

            $('.total-qty').html(response.total_qty);
          }
        },
        error: function(request, status, error){
            // $('#loader_modal').modal('hide');
        }
    });
});

$(document).on('keyup', '.input_shipped_qty', function() {

    let total_qty = 0;
    $(".input_shipped_qty").each(function(){
        if ($(this).val() !== "") {
            total_qty += parseFloat($(this).val());
        }
    });
    $('#total_qty_span').html(total_qty);
});

</script>
@stop

