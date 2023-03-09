@extends('importing.layouts.layout')

@section('title','Purchase Order Detail | Purchasing')

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
.inputDoubleClickQuantity{
  font-style: italic;
  font-weight: bold;
}
@php
use Carbon\Carbon;
@endphp
</style>

{{-- Content Start from here --}}

<!-- NEW Design -->

<!-- Right Content Start Here -->
<div class="right-contentIn">

<div class="row mb-0 headings-color">
    <div class="col-lg-8"> 
  <h4 class="mb-1">Purchase Order</h4>
  </div>
  <div class="col-lg-4">
<h4 class="mb-1">Purchase From</h4>
   </div>
  </div>
  <div class="row">
  
  <input type="hidden" name="po_id" id="po_id" value="{{$id}}">

  <div class="col-lg-8">
    <p class="mb-1">PO # {{ $getPurchaseOrder->ref_id}}</p>
    <p class="mb-1">PO Date: {{Carbon::parse(@$getPurchaseOrder->created_at)->format('d/m/Y')}}</p>
    <p class="mb-1">Supplier Ref#: {{ $getPurchaseOrder->PoSupplier->reference_number}}</p>
    {{--<p class="mb-1">AWB or B/L</p>
    <p class="mb-1">Exp. Arrival Date</p>--}}
  </div>

  <div class="col-lg-4">
  <div class="d-flex align-items-center mb-1">
  <div>
   <!--  <img src="{{url('public/uploads/sales/customer/logos/'.$getPurchaseOrder->PoSupplier->logo)}}" class="img-fluid" align="big-qummy" style="width: 85px;
    height: 75px;"> -->
     @if(@$getPurchaseOrder->PoSupplier->logo != null && file_exists( public_path() . '/uploads/sales/customer/logos/'.@$getPurchaseOrder->PoSupplier->logo))
        <img src="{{asset('public/uploads/sales/customer/logos/'.@$getPurchaseOrder->PoSupplier->logo)}}" class="img-fluid" style="width: 85px;height: 75px;" align="big-qummy">
        @else
        <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 85px;height: 75px;" align="big-qummy">
        @endif
  </div>
  <div class="pl-2 comp-name" data-supplier-id="{{$getPurchaseOrder->supplier_id}}"><p>{{ $getPurchaseOrder->PoSupplier->company }}</p> </div>
  </div>

  <p class="mb-1">@if($getPurchaseOrder->PoSupplier->address_line_1 !== null) {{ $getPurchaseOrder->PoSupplier->address_line_1.' '.$getPurchaseOrder->PoSupplier->address_line_2 }}, @endif  @if($getPurchaseOrder->PoSupplier->country !== null) {{ $getPurchaseOrder->PoSupplier->getcountry->name }}, @endif @if($getPurchaseOrder->PoSupplier->state !== null) {{ $getPurchaseOrder->PoSupplier->getstate->name }}, @endif @if($getPurchaseOrder->PoSupplier->city !== null) {{ $getPurchaseOrder->PoSupplier->city }}, @endif @if($getPurchaseOrder->PoSupplier->postalcode !== null) {{ $getPurchaseOrder->PoSupplier->postalcode }} @endif</p>

  @if($getPurchaseOrder->PoSupplier->email !== null || $getPurchaseOrder->PoSupplier->phone !== null)
  <ul class="d-flex list-unstyled">
    @if($getPurchaseOrder->PoSupplier->phone !== null)
    <li><i class="fa fa-phone pr-2"></i> 
      {{ $getPurchaseOrder->PoSupplier->phone }}
    </li>
    @endif
    @if($getPurchaseOrder->PoSupplier->phone !== null)</li>
    <li class="pl-3"><i class="fa fa-envelope pr-2"></i> 
    {{ $getPurchaseOrder->PoSupplier->email }}
    </li>
    @endif
  </ul>
  <ul class="d-flex list-unstyled">
    <li class="pt-2 fontbold">@if(!array_key_exists('target_ship_date', $global_terminologies))Target Ship Date Here @else {{$global_terminologies['target_ship_date']}} @endif: <b style="color: red;">*</b></li>
    <span class="pl-4 pt-2 inputDoubleClick">
      @if($getPurchaseOrder->target_receive_date != null)
      {{Carbon::parse($getPurchaseOrder->target_receive_date)->format('d/m/Y')}}
      @else
      <p> @if(!array_key_exists('target_ship_date', $global_terminologies))Target Ship Date  @else {{$global_terminologies['target_ship_date']}} @endif Here</p>
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none target_receive_date fieldFocus" name="target_receive_date" id="target_receive_date" value="{{@$getPurchaseOrder->target_receive_date}}">
  </ul>
  @endif
  <ul class="d-flex list-unstyled">
    <li class="pt-2 fontbold">@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif : <b style="color: red;">*</b></li>
    <span class="pl-4 pt-2 inputDoubleClick">
      @if($getPurchaseOrder->payment_due_date != null)
      {{Carbon::parse($getPurchaseOrder->payment_due_date)->format('d/m/Y')}}
      @else
      <p>@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif Here</p>
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none payment_due_date fieldFocus" name="payment_due_date" id="payment_due_date" value="{{@$getPurchaseOrder->payment_due_date}}">
  </ul>
  </div>

  <!-- export pdf form starts -->
  <form class="export-po-form" method="post" action="{{url('export-po-to-pdf/'.$id)}}">
    @csrf
    <input type="hidden" name="po_id_for_pdf" id="po_id_for_pdf" value="{{$id}}">
  </form>
  <!-- export pdf form ends -->

 <div class="col-lg-12 text-uppercase fontbold">
  <a onclick="history.go(-1)">
    <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color">back</button>
  </a>

  <a href="#">
    <button type="button" class="btn-color btn text-uppercase purch-btn mr-3 headings-color d-none">print</button>
  </a>
  
  <a href="javascript:void(0);">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color export-pdf">print</button>
  </a>
  
  <div class="pull-right">
    @if($checkPoDocs > 0)
      @php $show = ""; @endphp
    @else
      @php $show = "d-none"; @endphp
    @endif
    <a href="javascript:void(0)" data-id="{{$getPurchaseOrder->id}}" data-toggle="modal" data-target="#file-modal" class="download-documents"><button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">View documents<i class="pl-1 fa fa-download"></i></button></a>

    <a href="{{ url('getting-docs-for-download/'.$id) }}" class="d-none">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
    </a>
   
    <a href="javascript:void(0);">
      <button type="button" data-toggle="modal" data-target="#addDocumentModal" class="btn-color btn text-uppercase purch-btn headings-color d-none">upload documents<i class="pl-1 fa fa-upload"></i></button>
    </a>
  </div>

</div>

  </div>

</div>

{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="addDocumentModal">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">ADD DOCUMENTS</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

      
  <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">

    <div class="modal-body">

      <input type="hidden" name="purchase_order_id" value="{{$id}}">

      <div class="form-group">
        <label class="pull-left">Select Documents To Upload</label>
        <input class="font-weight-bold form-control-lg form-control" name="po_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
      </div>

    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary save-doc-btn" id="addDocBtn">Add</button>
    </div>
  </form>

    </div>
  </div>
</div>
{{-- end modal code--}}


 <div class="col-lg-12 mt-3">
  <div class="bg-white table-responsive pb-5">
  <table class="table po-porducts-details text-center table-bordered" style="width:100%">
    <thead class="sales-coordinator-thead headings-color table-bordered">
      <tr>
        <th>Action</th>          
        <th>Supplier # </th>
        <th>Item #</th>
        <th>Customer</th>
        <th>@if(!array_key_exists('product_description', $global_terminologies)) Product Description @else {{$global_terminologies['product_description']}} @endif</th>
        <th>Buy Unit</th>
        <th>@if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif</th>
        <th>Unit Price</th>
        <th>Amount</th>
        <th>Order #s</th>      
        <th>Warehouse</th>     
        <th>Total <br> Gross <br> Weight</th>                                               
      </tr>
    </thead>
  </table>


    <div class="row ml-4">
    <div class="col-8 mb-4">

    <div class="row">
    <div class="col-12">

    <div class="col-4 mb-4 d-none">
      <div class="purch-border input-group custom-input-group">  
        <input type="text" name="refrence_code" placeholder="Type Reference number..." 
      data-po_id = "{{$id}}" class="form-control refrence_number" autocomplete="off">
      </div>
    </div>

    <div class="col-4 mb-4 d-none">
      <button class="btn purch-add-btn mt-3 fontmed" type="submit" id="addProduct">Add Product</button>
    </div>
           
    </div>

    <div class="col-lg-6 d-none">
    <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3">
      <table class="table headings-color">
        <tbody>
          <tr>
            <td class="text-nowrap">Created by:</td>
            <td>Admin at 18/09/2019   08:00 PM</td>
          </tr>

          <tr>
            <td class="text-nowrap">Created by:</td>
            <td>Admin at 18/09/2019   08:00 PM</td>
          </tr>

          <tr>
            <td class="text-nowrap">Created by:</td>
            <td>Admin at 18/09/2019   08:00 PM</td>
          </tr>

          <tr>
            <td class="text-nowrap">Created by:</td>
            <td>Admin at 18/09/2019   08:00 PM</td>
          </tr>
        </tbody>
      </table>
    </div>
    </div>

    <div class="col-lg-6 d-none">
      <div class="purchase-order-detail pt-2 pb-3 pr-3 pl-3 h-100">
      <table class="table headings-color">
       <thead class="sales-coordinator-thead ">
         <tr>                
           <th>User  </th>
           <th>Date/time </th>
           <th>Status </th>
           <th>New Status</th>
         </tr>
       </thead>
       <tbody>
         <tr>
           <td>abc</td>            
           <td>10/10/19</td>            
           <td>Active </td>             
           <td> Active</td>
         </tr>
       </tbody>
      </table>
      </div>
    </div>
      
    </div>      
    </div>

    <div class="col-lg-4 pt-4 mt-4">
      <table class="table headings-color purch-last-left-table table-bordered" style="width: 70%">
        <tbody>
          <tr>
            <td class="text-nowrap fontbold" align="center">Total:</td>
            <td class="fontbold sub-total" align="center">{{ number_format(@$getPurchaseOrder->total, 2, '.', ',') }}</td>
          </tr>
          <tr class="d-none">
            <td class="text-nowrap fontbold">Paid:</td>
            <td class="fontbold">20/09/2019</td>
          </tr>
          <tr class="d-none">
            <td class="text-nowrap fontbold">Due:</td>
            <td class="fontbold">20/09/2019</td>
          </tr>
        </tbody>
      </table>
    </div>

    </div>

    <div class="row">
    <div class="col-lg-6 ml-5">
      <p>
        <strong>Note: </strong>
        <span class="po-note inputDoubleClick ml-2">@if($getPoNote != null) {!! @$getPoNote->note !!} @else {{ 'Double click here to add a note...' }} @endif</span>
        <textarea autocomplete="off" name="note" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a note (500 Characters)" maxlength="500">{{ $getPoNote !== null ? @$getPoNote->note : '' }}</textarea>
      </p>
    </div>
    <div class="col-lg-6"></div>
    </div>

    @if($getPurchaseOrder->status == 12)
    <div class="row d-none">
    <div class="col-lg-9"></div>
    <div class="col-lg-3">
      <a href="javascript:void(0);">
        <button type="button" data-id={{$id}} class="btn-color btn purchasingSupplybtn confirm-po-btn"><i class="fa fa-check"></i> Confirm Purchase Order</button>
      </a>
    </div>
    </div>
    @endif
   
</div>

</div>

</div>

</div><!-- main content end here -->
<!-- New Design End here -->
</div>


{{--  Add Product Modal Start Here --}}
<div class="modal addProductModal" id="addProductModal" style="margin-top: 150px;">
  <div class="modal-dialog">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">Search Product</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

  <div class="modal-body">
    <div class="form-group" style="margin-top: 10px; margin-bottom: 50px; position:relative;">
      <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
      <input type="text" name="prod_name" id="prod_name" class="form-control form-group mb-0" placeholder="Search by Product Name" style="padding-left:30px;">
    </div>
    <div id="product_name_div"></div>
  </div>
    
  </div>
  </div>
</div>

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

<div class="modal" id="file-modal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order Files</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="fetched-files">
            <div class="d-flex justify-content-center">
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

@endsection

@section('javascript')
<script type="text/javascript">
  $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
  var po_id = $("#po_id").val();
  $(function(e){
     $('.po-porducts-details').DataTable({
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        searching: false,
        ordering: false,
        serverSide: true,
        bInfo: false,
        paging: false,
        dom: 'lrtip',
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax: "{{ url('importing/get-purchase-order-product-detail') }}"+"/"+po_id,
        columns: [
            { data: 'action', name: 'action'},
            { data: 'supplier_id', name: 'supplier_id' },
            { data: 'item_ref', name: 'item_ref' },
            { data: 'customer', name: 'customer' },
            { data: 'short_desc', name: 'short_desc' },
            { data: 'buying_unit', name: 'buying_unit' },
            { data: 'quantity', name: 'quantity' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'amount', name: 'amount' },
            { data: 'order_no', name: 'order_no' },
            { data: 'warehouse', name: 'warehouse' },
            { data: 'gross_weight', name: 'gross_weight' },

        ],
        //  initComplete: function () {
        //     this.api().columns([1,2]).every(function () {
        //         var column = this;
        //         var input = document.createElement("input");
        //         $(input).addClass('form-control');
        //         $(input).attr('type', 'text');
        //         $(input).appendTo($(column.header()))
        //         .on('change', function () {
        //             column.search($(this).val()).draw();
        //         });
        //     });
        // }
   });

     $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

  });

  // dropdown double click editable code start here
  $(document).on('change', 'select.select-common', function(){
    if($(this).val() !== '')
    {
      if($(this).val() > 0)
      {
        var po_id = "{{ $id }}";
        var attr_name = $(this).attr('name');
        var rowId = $(this).parents('tr').attr('id');
        var new_value = $("option:selected", this).html();
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        $(this).prev().html(new_value);
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });

        $.ajax({
          type: "post",
          url: "{{ route('save-po-product-warhouse') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
                toastr.success('Success!', 'Warehouse Assigned Successfully.',{"positionClass": "toast-bottom-right"});
                /*$('.po-porducts-details').DataTable().ajax.reload();*/
            }
          }   
        });
      }
    }
  });

  // double click editable 
  $(document).on("dblclick",".inputDoubleClickQuantity",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
    // var num = $(this).next().val();        
    // $(this).next().focus().val('').val(num);
  });

  $(document).on('keyup', 'input[type=number]', function(e){
    if(e.keyCode === 13 && $(this).hasClass('active')){

    var po_id = "{{ $id }}";
    var attr_name = $(this).attr('name');
    var rowId = $(this).parents('tr').attr('id');
    
    if($(this).attr('name') == 'quantity')
    {
      if($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null)
      {
        swal({ html:true, title:'Alert !!!', text:'<b>QTY cannot be 0 or less then 0 !!!</b>'});
        return false;
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('save-po-product-quantity') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
                toastr.success('Success!', 'QTY Updated Successfully.',{"positionClass": "toast-bottom-right"});
                $('.po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(2);
                $('.sub-total').html(sub_total_value);         
                $('#sub_total').val(sub_total_value);
            }
          }   
        });
      }
    }
    // unit price 
    if($(this).attr('name') == 'unit_price')
    {
      if($(this).val() == 0 || $(this).val() < 0 || $(this).val() == null)
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Unit Price cannot be 0 or less then 0 !!!</b>'});
        return false;
      }
      else if($(this).val() !== '' && $(this).hasClass('active'))
      {
        $(this).prev().html($(this).val());
        $(this).removeClass('active');
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
        
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

        $.ajax({
          type: "post",
          url: "{{ route('update-unit-price') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'po_id='+po_id+'&'+attr_name+'='+$(this).val(),
          beforeSend: function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $('#loader_modal').modal('show');
          },
          success: function(data){
            $('#loader_modal').modal('hide');
            if(data.success == true)
            {
                toastr.success('Success!', 'Unit Price Updated Successfully.',{"positionClass": "toast-bottom-right"});
                $('.po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(2);
                $('.sub-total').html(sub_total_value);         
                $('#sub_total').val(sub_total_value);
            }
          }   
        });
      }
    }

   }

  });

  // double click editable 
  $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().focus();
  });

  $(document).on("focusout",".fieldFocus",function() { 
      var po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      
      if(attr_name == 'note')
      {
        if($(this).val() == '')
        {
          $(this).prev().html("Double click here to add a note!!!");
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
          return false;
        }
        else
        {
          $(this).prev().html($(this).val());
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
        }
      }
      if(attr_name == 'payment_due_date')
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
      }
      if(attr_name == 'target_receive_date')
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
      }

      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });

    $.ajax({
      type: "post",
      url: "{{ route('save-po-note') }}",
      dataType: 'json',
      data: 'po_id='+po_id+'&'+attr_name+'='+$(this).val(),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        if(data.success == true)
        {
          if(attr_name == 'note')
          {
            $('.po-note').html(data.updateRow.note);
          }
        }
      }   
    });
  });

  // document upload
  $('.addDocumentForm').on('submit', function(e){
  e.preventDefault();
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
   $.ajax({
      url: "{{ route('add-purchase-order-document') }}",
      dataType: 'json',
      type: 'post',
      data: new FormData(this), 
      contentType: false,       
      cache: false,             
      processData:false,   
      beforeSend: function(){
        $('.save-doc-btn').html('Please wait...');
        $('.save-doc-btn').addClass('disabled');
        $('.save-doc-btn').attr('disabled', true);
      },
      success: function(result){
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').attr('disabled', true);
        $('.save-doc-btn').removeAttr('disabled');
        if(result.success == true)
        {
          toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
          $('.addDocumentForm')[0].reset();
          $('.addDocumentModal').modal('hide');
          $('.download-docs').removeClass('d-none');
          
        }
      },
      error: function (request, status, error) {
        $('.save-doc-btn').html('Upload');
        $('.save-doc-btn').removeClass('disabled');
        $('.save-doc-btn').removeAttr('disabled');
        json = $.parseJSON(request.responseText);
        $.each(json.errors, function(key, value){
              $('input[name="'+key+'[]"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
             $('input[name="'+key+'[]"]').addClass('is-invalid');
          
        });
        }
    });
    });

  // delete product from purchasing detail page
  $(document).on('click', '.delete-product-from-list', function(e){

    var order_id = $(this).data('order_id');
    var order_product_id = $(this).data('order_product_id');
    var po_id = $(this).data('po_id');
    var id = $(this).data('id');

    swal({
      title: "Are you sure!!!",
      text: "You want to remove this product?",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, remove it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
      },
    function (isConfirm) {
        if (isConfirm) {
          $.ajax({
            method:"get",
            data:'order_id='+order_id+'&'+'order_product_id='+order_product_id+'&'+'po_id='+po_id+'&'+'id='+id,
            url: "{{ route('delete-product-from-po') }}",
            success: function(data){
              if(data.success === true)
              {
                toastr.success('Success!', 'Product Removed Successfully.',{"positionClass": "toast-bottom-right"});
                $('.po-porducts-details').DataTable().ajax.reload();
                var sub_total_value = data.sub_total.toFixed(2);
                $('.sub-total').html(sub_total_value);         
                $('#sub_total').val(sub_total_value);
              }
              else if(data.redirect === 'yes')
              {
                toastr.success('Success!', 'Product Removed Successfully.',{"positionClass": "toast-bottom-right"});
                setTimeout(function(){
                window.location.href = "{{ route('list-purchasing')}}";
                }, 1000);
              }
            }
          });
        }
        else {
            swal("Cancelled", "", "error");
        }
      });
    });

  // export pdf code
  $(document).on('click', '.export-pdf', function(e){

    var po_id = $('#po_id_for_pdf').val();
    $('.export-po-form')[0].submit();

  });

  // confirm po button code here
  $(document).on('click','.confirm-po-btn', function(e){

    var id = $(this).data('id');   //purchase order id

    var target_receive_date = $(".target_receive_date").val();
    var payment_due_date = $(".payment_due_date").val();
    if(target_receive_date == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Target Ship Date!!!</b>'});
      return false;
    }

    if(payment_due_date == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Payment Due Date!!!</b>'});
      return false;
    }
    // else
    // {
    //   $.ajaxSetup({
    //   headers: {
    //     'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    //   }
    //   });
      
    //   $.ajax({
    //     method:"post",
    //     type: 'post',
    //     data:'id='+id+'&'+'receive_date='+target_receive_date,
    //     url: "{{ route('set-target-receive-date') }}",
    //     success: function(response){
    //        // do nothing here
    //     }
    //   });
    // }

    swal({
      title: "Are you sure!!!",
      text: "You want to confirm this purchase order?",
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
          url: "{{ route('confirm-purchase-order') }}",
          success: function(response){
              if(response.success === true){
              toastr.success('Success!', 'Purchase Order Confirmed.',{"positionClass": "toast-bottom-right"});
              setTimeout(function(){
              window.location.href = "{{ route('purchasing-dashboard')}}";
              }, 1500);
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

  // adding product by searching
  $('#prod_name').keyup(function(){ 
    var query = $(this).val();
    var supplier_id = $('.comp-name').data('supplier-id');
    var po_id = $("#po_id").val();
    if(query != '')
    {
     var _token = $('input[name="_token"]').val();
     $.ajax({
      url:"{{ route('autocomplete-fetching-products-for-po') }}",
      method:"POST",
      data:{query:query, _token:_token, po_id:po_id, supplier_id:supplier_id},
      success:function(data){
        $('#product_name_div').fadeIn();  
        $('#prod_name').val();
        $('#product_name_div').html(data);
      }
     });
    }
  });

  $(document).on('click', 'li', function(){  
    $('#prod_name').val("");  
    $('#product_name_div').fadeOut();  
  }); 

  $(document).on('click', '.add_product_to', function(e){
  var po_id = $("#po_id").val();
  var prod_id = $(this).data('prod_id');
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
                      
  $.ajax({
      method:"post",
      data:'selected_products='+prod_id+'&po_id='+po_id,
      url:"{{ route('add-prod-to-po-detail') }}",
      success:function(data){
        if(data.success == false){
            $('#addProductModal').modal('hide');
            toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
            $('#prod_name').text('');      
            $('.po-porducts-details').DataTable().ajax.reload(); 
          } 
          else
          {
            $('#addProductModal').modal('hide');
            $('#prod_name').text('');
            $('.po-porducts-details').DataTable().ajax.reload();
            var sub_total_value = data.sub_total.toFixed(2);
            $('.sub-total').html(sub_total_value);         
            $('#sub_total').val(sub_total_value);              
            // $('.total_products').html(data.total_products);  
      }
        
      }
    });
  }); 

  $(document).on('click','#addProduct',function(){
    if($(this).attr("id") == 'addProduct')
    {
      $('#addProductModal').modal('show');
    }
  });

  $(document).on('keyup','.refrence_number',function(e){
    if(e.keyCode == 13)
    { 
      if($(this).val() !== '')
      {
      var refrence_number = $(this).val();
      var po_id = $(this).data(po_id);

      var formData = {"refrence_number":refrence_number,"po_id":po_id};
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
      $.ajax({
          url: "{{ route('add-prod-by-refrence-number-in-po-detail') }}",
          method: 'post',
          data: formData,  
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
            toastr.success('Success!', result.successmsg ,{"positionClass": "toast-bottom-right"});        
            $('.refrence_number').val('');      
            $('.po-porducts-details').DataTable().ajax.reload();  
            var sub_total_value = result.sub_total.toFixed(2);
            $('.sub-total').html(sub_total_value);         
            $('#sub_total').val(sub_total_value);         
            // $('.total_products').html(result.total_products);         
          } 
          else
          {
            toastr.error('Error!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');      
            $('.po-porducts-details').DataTable().ajax.reload(); 
          }           
                     
            },
          error: function (request, status, error) {                
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');
                });
            }
        });
     }
   }
  });

  $(window).keydown(function(e){
    if(e.keyCode == 13) {
      e.preventDefault();
      return false;
    }
  });
</script>
<script type="text/javascript">
  $(function(e){
  
  $(document).on('click', '.download-documents', function(e){
    let sid = $(this).data('id');
    console.log(sid);
    $.ajax({
      type: "post",
      url: "{{ route('get-purchase-order-files-importing') }}",
      data: 'po_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      }
    });
  });

  $(document).on('click', '.delete-purchase-order-file', function(e){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to delete this file? You won't be able to undo this.",
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
                data:'id='+id,
                url:"{{ route('remove-purchase-order-file') }}",
                beforeSend:function(){
                },
                success:function(data){
                    if(data.search('done') !== -1){
                      myArray = new Array();
                      myArray = data.split('-SEPARATOR-');
                      let i_id = myArray[1];
                      $('#purchase-order-file-'+i_id).remove();
                      toastr.success('Success!', 'File deleted successfully.' ,{"positionClass": "toast-bottom-right"});
                    }
                }
             });
          } 
          else{
              swal("Cancelled", "", "error");
          }
     });  
  });  

});

</script>
@stop

