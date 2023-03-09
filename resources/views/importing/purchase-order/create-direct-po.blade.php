@extends('users.layouts.layout')

@section('title','Purchase Order | Purchasing')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
.dataTables_scrollBody > table > thead > tr {
    visibility: collapse;
    height: 0px !important;
}
.inputDoubleClick{
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
<form method="post" class="mb-2 purchase_order_form" action="{{ route('action-draft-po') }}" enctype='multipart/form-data'> @csrf

<div class="row mb-3 headings-color">
  
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Draft Purchase Order # <span class="c-ref-id">{{ $id }}</span></h3>
  </div>
  <div class="col-md-4">
    <h3 class="maintitle text-uppercase fontbold">Purchase From</h3>
  </div>
<!-- New Design Starts Here  -->
<div class="col-lg-12">
<div class="row">
  
<div class="col-lg-8">
<!-- <img src="{{asset('public/img/logo.png')}}" alt="logo" class="img-fluid lg-logo"> -->
 @if(@Auth::user()->user_details->image != null && file_exists( public_path() . '/uploads/purchase/' . @Auth::user()->user_details->image))
  <img src="{{asset('public/uploads/purchase/'.@Auth::user()->user_details->image)}}" class="img-fluid" style="width: 65px;height: auto;" align="big-qummy">
  @else
  <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 40px;height: 40px;" align="big-qummy">
  @endif
  <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@Auth::user()->getUserDetail->company_name}}</p>
  <p class="mb-1">{{Auth::user()->getUserDetail->address}} , {{Auth::user()->getUserDetail->country->name}} , {{Auth::user()->getUserDetail->state->name}} , {{Auth::user()->getUserDetail->zip_code}}</p>
  <p class="mb-1"><em class="fa fa-phone"></em> {{Auth::user()->getUserDetail->phone_no}}  <em class="fa fa-envelope"></em>  {{Auth::user()->email}}</p>
  <br>
  
</div>
<div class="col-lg-4">
  <!-- <p class="mb-1">Purchase From</p> -->
  @if($draft_po->getSupplier != NULL)
    <div>
    <div class="d-flex align-items-center mb-1">
      <div>
        @if(@$draft_po->getSupplier->logo != NULL && file_exists( public_path() . '/uploads/sales/customer/logos/' . @$draft_po->getSupplier->logo))
        <img src="{{asset('public/uploads/sales/customer/logos'.'/'.$draft_po->getSupplier->logo)}}" class="img-fluid" align="big-qummy" style="width: 65px; height: auto;">
        @else
        <img src="{{asset('public/uploads/logo/temp-logo.png')}}" class="img-fluid" align="big-qummy" style="width: 65px; height: auto;">
        @endif
      </div>
      <div class="pl-2 comp-name" data-supplier-id="{{@$draft_po->supplier_id}}"><p>{{$draft_po->getSupplier->company}}</p> </div>
    </div>
        
    <p class="mb-1">
      @if($draft_po->getSupplier->address_line_1 !== null) 
      {{ $draft_po->getSupplier->address_line_1.' '.$draft_po->getSupplier->address_line_2 }}, 
      @endif  
      @if($draft_po->getSupplier->country !== null) 
      {{ $draft_po->getSupplier->getcountry->name }}, 
      @endif 
      @if($draft_po->getSupplier->state !== null) 
      {{ $draft_po->getSupplier->getstate->name }}, 
      @endif 
      @if($draft_po->getSupplier->city !== null) 
      {{ $draft_po->getSupplier->city }}, 
      @endif 
      @if($draft_po->getSupplier->postalcode !== null) 
      {{ $draft_po->getSupplier->postalcode }} 
      @endif
    </p>
    <ul class="d-flex list-unstyled">
        <li><i class="fa fa-phone pr-2"></i>{{$draft_po->getSupplier->phone}}</li>
        <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{$draft_po->getSupplier->email}}</li>
    </ul>
    </div>
  @endif

  <input type="hidden" name="selected_supplier_id" id="selected_supplier_id" value="{{$draft_po->supplier_id}}">

  @if($draft_po->getSupplier == NULL)
    <select class="form-control js-states state-tags mb-2 add-supp" name="supplier">
      <option value="new">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option> 
      @if(@$suppliers->count() > 0)
       @foreach($suppliers as $supplier)
       <option value="{{ $supplier->id }}"> @if($supplier->company != null) {{$supplier->company}} @else {{ $supplier->first_name.' '.$supplier->last_name }} @endif</option>
      @endforeach
      @endif
      {{--<option value="new">Add New</option>--}}
    </select>
    <div class="supplier_info"></div>
  @endif

  <ul class="d-flex list-unstyled">
    <li class="pt-2 fontbold">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif: <b style="color: red;">*</b></li>
    <span class="pl-4 pt-2 inputDoubleClick">
      @if($draft_po->target_receive_date != null)
      {{Carbon::parse($draft_po->target_receive_date)->format('d/m/Y')}}
      @else
      <p>@if(!array_key_exists('target_ship_date', $global_terminologies))Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif Here</p>
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none target_receive_date fieldFocus" name="target_receive_date" id="target_receive_date" value="{{@$draft_po->target_receive_date}}">
  </ul>
  <ul class="d-flex list-unstyled">
    <li class="pt-2 fontbold">@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif: <b style="color: red;">*</b></li>
    <span class="pl-4 pt-2 inputDoubleClick">
      @if($draft_po->payment_due_date != null)
      {{Carbon::parse($draft_po->payment_due_date)->format('d/m/Y')}}
      @else
      <p>@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif Here</p>
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none payment_due_date fieldFocus" name="payment_due_date" id="payment_due_date" value="{{@$draft_po->payment_due_date}}">
  </ul>
</div>

<div class="col-lg-12 text-uppercase fontbold">
  <a href="{{url('/')}}">
    <button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color">back</button>
  </a>

  <a href="#">
    <button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button>
  </a>
  
  <a href="#">
    <button type="button" class="btn text-uppercase purch-btn headings-color btn-color d-none">export</button>
  </a>
  
  <div class="pull-right">
    @if($checkDraftPoDocs > 0)
      @php $show = ""; @endphp
    @else
      @php $show = "d-none"; @endphp
    @endif
    <a href="{{ url('getting-draft-po-docs-for-download/'.$id) }}">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
    </a>

    <a href="javascript:void(0);">
    <button type="button" class="btn text-uppercase purch-btn headings-color btn-color" data-toggle="modal" data-target="#addDocumentModal">upload document<i class="pl-1 fa fa-arrow-up"></i>
    </button>
    </a>
  </div>
</div>
</div>
</div>
  <!-- new design ends here -->  
</div>

  <div class="row entriestable-row mt-3">
  <div class="col-12">
  <div class="entriesbg bg-white custompadding customborder">
    
    <table class="table entriestable table-bordered table-ordered-products text-center">
      <thead>
        <tr>
          <th>Action</th>          
          <th>Supplier # </th>
          <th>Item #</th>
          <th>Customer</th>
          <th>Description</th>
          <th>Buy Unit</th>
          <th>QTY</th>
          <th>Unit Price</th>
          <th>Amount</th>
          <th>Order #s</th>      
          <th>Warehouse</th>       
          <th>Total <br> Gross <br> Weight</th>                                             
      </tr>
      </thead>
         
    </table>
        

  <!-- New Design Starts Here  -->
  <div class="row ml-0 mb-4">
  <div class="col-8 pad mt-4">
    <div class="col-6 pad">
      <div class="purch-border input-group custom-input-group">
      <input type="text" name="refrence_code" placeholder="Type Reference number..." 
      data-draft_po_id = "{{$id}}" class="form-control refrence_number" autocomplete="off">
      </div>
    </div>
    <div class="col-12 pad mt-4 mb-4"> 
      <a class="btn purch-add-btn mt-3 fontmed col-2 btn-sale" id="addProduct">Add Product</a> 
    </div>
  </div>
       
  <div class="col-lg-4 pt-4 mt-4">
    <div class="side-table">
    <table class="headings-color purch-last-left-table side-table">
      <tbody>
        <tr>
          <td class="fontbold" width="50%">Total:</td>
          <input type="hidden" name="sub_total" value="{{$sub_total}}" id="sub_total">
          <td class="text-start sub-total fontbold">&nbsp;&nbsp;${{ number_format($sub_total, 2, '.', ',') }}</td>
        </tr>
        <tr class="d-none">
          <td class="text-nowrap fontbold">Paid:</td>
          <td class="fontbold text-start">&nbsp;&nbsp;$0.00</td>
        </tr>
        <tr class="d-none">
          <td class="text-nowrap fontbold">Due:</td>
          <td class="fontbold text-start">&nbsp;&nbsp;$0.00</td>
        </tr>         
      </tbody>
    </table>
    </div> 
  </div>
  </div>

        <div class="row justify-content-end d-flex">
          <div class="col-lg-5 col-md-5 pl-3 pt-md-3">       
       
          <div class="text-right">  
            
            <input type="hidden" name="draft_po_id" id="draft_po_id" value="{{ $id }}">
            <input type="hidden" name="action" value="save">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-success">Save and Close</button>
    </form>
          
          <form method="post" class="d-inline-block mb-2" action="{{ route('action-draft-po') }}" >
            @csrf
            <input type="hidden" name="draft_po_id" id="draft_po_id" value="{{ $id }}">
            <input type="hidden" name="action" value="discard">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-danger">Discard and Close</button>&nbsp;
          </form>
          </div>
          </div>
          </div>

      </div>
        <!-- New Design Ends Here  -->

  
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

<!--  Add Product Modal Start Here -->
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

      <input type="hidden" name="draft_purchase_order_id" value="{{$id}}">

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

@endsection

@section('javascript')
<script type="text/javascript">
  $(".state-tags").select2();

  $(function(e){

  var table = $('.table-ordered-products').DataTable({
       processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      searching: false,
      ordering: false,
      serverSide: true,
      scrollX: true,
      scrollY : '90vh',
      scrollCollapse: true,
      paging: false,
      bInfo : false,
      fixedHeader: true,
      colReorder: {
      realtime: false,
      },
      dom: 'lrtip',
      ajax: "{{ url('get-product-to-list-draft-po') }}"+"/"+{{ $id }},
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
      ]
  });

  $(document).on('click','#addProduct',function(){
    // var supplier_id = $('.comp-name').data('supplier-id');
    var supplier_id = $("#selected_supplier_id").val();
    if(supplier_id == '')
    {
       toastr.info('warning!', 'Please Select Supplier First',{"positionClass": "toast-bottom-right"});
    }
    else
    {
      if($(this).attr("id") == 'addProduct')
      {
        $('#addProductModal').modal('show');
      }
    }
  });

  $('.purchase_order_form').on('submit', function(e){
        var inverror = false;
        var target_receive_date = $(".target_receive_date").val();
        var payment_due_date = $(".payment_due_date").val();
        var supplier = $("#selected_supplier_id").val();

        if(supplier.val() == ''){    
          $('select[name=supplier]').css('border', '1px solid #dc3545');
          inverror = true
        }
        
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

        if(inverror === true){
          $('html, body').animate({
            scrollTop: $("body").offset().top
          }, 100);
          e.preventDefault();
          return false;
        }
    });

  @if(Session::has('errormsg'))
    toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
  @endif     

  });  
  
</script>
<script>
  $(document).ready(function(){

  $('#prod_name').keyup(function(){ 
    var query = $(this).val();
    var supplier_id = $("#selected_supplier_id").val();
    var draft_po_id = $("#draft_po_id").val();
    if(query != '')
    {
     var _token = $('input[name="_token"]').val();
     $.ajax({
      url:"{{ route('autocomplete-fetching-products') }}",
      method:"POST",
      data:{query:query, _token:_token, draft_po_id:draft_po_id, supplier_id:supplier_id},
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

  });

  $(document).on('click', '.add_product_to', function(e){
  var draft_po_id = $(this).data('draft_po_id');
  var prod_id = $(this).data('prod_id');
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
                      
  $.ajax({
      method:"post",
      data:'selected_products='+prod_id+'&draft_po_id='+draft_po_id,
      url:"{{ route('add-prod-to-draft-po') }}",
      success:function(data){
        if(data.success == false){
            $('#addProductModal').modal('hide');
            toastr.error('Error!', data.successmsg ,{"positionClass": "toast-bottom-right"});
            $('#prod_name').text('');      
            $('.table-ordered-products').DataTable().ajax.reload(); 
          } 
          else
          {
            $('#addProductModal').modal('hide');
            $('#prod_name').text('');
            $('.table-ordered-products').DataTable().ajax.reload();
            var sub_total_value = data.sub_total.toFixed(2);
            $('.sub-total').html(sub_total_value);         
            $('#sub_total').val(sub_total_value);              
            // $('.total_products').html(data.total_products);  
      }
        
      }
    });
  }); 

  $(document).on('change','.add-supp',function(){
  var supplier_id = $(this).val();
  var draft_po_id = '{{$id}}';
  var selected_supplier_id = $("#selected_supplier_id").val(supplier_id);
  var _token = $('input[name="_token"]').val();
  $.ajax({
        url:"{{ route('add-supplier-to-draft-po') }}",
        method:"POST",
        data:{_token:_token,supplier_id:supplier_id,draft_po_id:draft_po_id},
        success:function(data){
          console.log(data);
          $('.add-supp').next().addClass('d-none');
          $('.supplier_info').html(data.html);
        }
       });
  });

  $(document).on("change",'.warehouse_id',function(){

    var warehouse_id = $(this).val();
    var draft_po_detail_id = $(this).parents('tr').attr('id');
    var draft_po_id = '{{$id}}';

    $.ajaxSetup({
      headers: 
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
        method: "post",
        url: "{{ url('warehouse-save-in-draft-po') }}",
        dataType: 'json',
        context: this,
        data: {warehouse_id:warehouse_id, draft_po_detail_id:draft_po_detail_id},
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Warehouse Assigned Successfully.' ,{"positionClass": "toast-bottom-right"});
            $('.table-ordered-products').DataTable().ajax.reload();
          }
        },

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
      url: "{{ route('add-draft-purchase-order-document') }}",
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
        if(result.success == true){
          toastr.success('Success!', 'Document Uploaded Successfully',{"positionClass": "toast-bottom-right"});
          $('.addDocumentForm')[0].reset();
          $('.addDocumentModal').modal('hide');
          $('.download-docs').removeClass('d-none');
          // setTimeout(function(){
          //   window.location.href = "{{ route('complete-list-product')}}";
          // }, 2000);
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

  $(document).on('keyup','.refrence_number',function(e){
    if(e.keyCode == 13)
    { 
      if($(this).val() != '')
      {
      var refrence_number = $(this).val();
      var draft_po_id = $(this).data(draft_po_id);

      var formData = {"refrence_number":refrence_number,"draft_po_id":draft_po_id};
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
      $.ajax({
          url: "{{ route('add-prod-by-refrence-number') }}",
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
            $('.table-ordered-products').DataTable().ajax.reload();  
            var sub_total_value = result.sub_total.toFixed(2);
            $('.sub-total').html(sub_total_value);         
            $('#sub_total').val(sub_total_value);         
            // $('.total_products').html(result.total_products);         
          } 
          else
          {
            toastr.error('Error!', result.successmsg ,{"positionClass": "toast-bottom-right"});
            $('.refrence_number').val('');      
            $('.table-ordered-products').DataTable().ajax.reload(); 
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

  // double click editable 
  $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().focus();
  });

  $(document).on("focusout",".fieldFocus",function() { 
      var draft_po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      
      /*if(attr_name == 'note')
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
      }*/
      if(attr_name == 'payment_due_date')
      {
        if($(this).val() == '')
        {
          return false;
        }
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).prev().removeClass('d-none');
      }
      if(attr_name == 'target_receive_date')
      {
        if($(this).val() == '')
        {
          return false;
        }
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
      url: "{{ route('save-draft-po-dates') }}",
      dataType: 'json',
      data: 'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val(),
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
          toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
        }
      }   
    });
  });

  $(document).on('click', '.deleteProd', function(){
    var id = $(this).data('id');
    var draft_po_id = '{{$id}}';
    swal({
        title: "Alert!",
        text: "Are you sure you want to remove this product?",
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
          data:{id:id, draft_po_id:draft_po_id},
          url:"{{ route('remove-draft-po-product') }}",
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
                toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                $('.table-ordered-products').DataTable().ajax.reload();
                $('.sub-total').html(data.sub_total);
                $('#sub_total').val(data.sub_total);
                $('.total_products').html(data.total_products);    
              }
          }
         });
      } 
      else{
          swal("Cancelled", "", "error");
      }
     });  
    });

  $(document).on("dblclick",".inputDoubleClickQuantity",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
  });

  $(document).on('keyup', 'input[type=number]', function(e){
    if(e.keyCode === 13 && $(this).hasClass('active')){

    var draft_po_id = "{{ $id }}";
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
          url: "{{ route('save-draft-po-product-quantity') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val(),
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
                $('.table-ordered-products').DataTable().ajax.reload();
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
          url: "{{ route('update-draft-po-unit-price') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val(),
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
                $('.table-ordered-products').DataTable().ajax.reload();
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

</script>
@stop

