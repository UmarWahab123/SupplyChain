@extends('users.layouts.layout')

@section('title','Quotations Products | Sales')

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

{{-- Content Start from here --}}
  <div class="row mb-3">
    <div class="col-md-7 title-col">
      <h3 class="maintitle text-uppercase fontbold">Draft Invoice # <span class="c-ref-id">{{$order_invoice->ref_id }}</span></h3>
    </div>  
  </div>

  <div class="justify-content-between row"> 
    <div class="d-flex mb-3 col-md-6 col-lg-5 col-12">
      <div class="invoiceDetail pl-3">
        @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . @$company_info->logo))
      <img src="{{asset('public/uploads/logo/'.@$company_info->logo)}}" class="img-fluid" style="width: 100px;height: 80px;" align="big-qummy">
      @else
      <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 100px;height: 80px;" align="big-qummy">
      @endif
      <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p>
        <!-- <h5 class="font-weight-bold mb-2 text-capitalize">{{ $company_info->company_name }}</h5> -->
         <p class="mb-2">{{ @$company_info->billing_address.', '.@$company_info->getcountry->name.', '.@$company_info->getstate->name.', '.@$company_info->billing_zip }}</p>
        <ul class="list-inline list-unstyled pl-0">
          <li class="list-inline-item"><em class="fa fa-phone"></em> {{ @$company_info->billing_phone }}</li>
          <li class="list-inline-item"><em class="fa fa-envelope"></em> {{ @$company_info->billing_email }} </li>
        </ul>
      </div>
    </div> 

    <div class="col-md-6 col-lg-5 col-12">
      <div class="form-group">
        <label class="mb-1 font-weight-bold">Quotation # </label> <span class="dblclk-edit" data-type="ref_id">{{ $order_invoice->ref_id }}</span>
      </div>
      <div class="form-group"> 
        <label class="mb-1 font-weight-bold">Quotation Date:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{ $order_invoice->created_at }}</span>
      </div> 
    </div>
  </div>

  <!-- export pdf form starts -->
  <form class="export-draft-form" method="post" action="{{url('export-draft-to-pdf/'.$id)}}">
    @csrf
    <input type="hidden" name="draft_id_for_pdf" id="draft_id_for_pdf" value="{{$id}}">
  </form>
  <!-- export pdf form ends -->

  <div class="justify-content-between row mt-3 p-2"> 
    @if($order_invoice->customer !== null)
    <div class="col-md-6 col-lg-5 col-12">
    <h5 class="font-weight-bold mb-2 text-capitalize">{{ $order_invoice->customer->company }}</h5>
    
    <div class="mb-1">
      @if($billing_address->billing_address !== null) {{ $billing_address->billing_address}}, @endif  
      @if($billing_address->getcountry->name !== null) {{ $billing_address->getcountry->name }}, @endif 
      @if($billing_address->getstate->name !== null) {{ $billing_address->getstate->name }}, @endif 
      @if($billing_address->billing_city !== null) {{ $billing_address->billing_city }}, @endif 
      @if($billing_address->billing_zip !== null) {{ $billing_address->billing_zip }} @endif
    </div>
    
    @if($billing_address->billing_email !== null || $billing_address->billing_phone !== null)
    <ul class="list-inline list-unstyled pl-0">
      @if($billing_address->billing_phone !== null)
      <li class="list-inline-item"><em class="fa fa-phone"></em> {{ $billing_address->billing_phone }}</li>
      @endif
      @if($billing_address->billing_email !== null)
      <li class="list-inline-item"><em class="fa fa-envelope"></em> {{ $billing_address->billing_email }}</li>
      @endif
    </ul>
    @endif

    @if($billing_address->tax_id !== null)
    <ul class="list-inline list-unstyled pl-0">
      @if($billing_address->tax_id !== null)
      <li class="list-inline-item"><b>Tax ID:</b>  {{ $billing_address->tax_id }}</li>
      @endif
    </ul>
    @endif

  </div>
  @endif
     
  </div>

  <div class="col-lg-12 text-uppercase fontbold">
    <a href="{{  route('list-draft-invoices') }}"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color">back</button></a>

    <a href="#"><button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button></a>
    
    <a href="javascript:void(0);">
      <button type="button" class="btn text-uppercase purch-btn headings-color btn-color export-pdf">print</button>
    </a>
    
    @if($order_invoice->status == 1 && $order_invoice->invoice_status == 0)
    <a href="javascript:void(0);" data-id="{{ $order_invoice->id }}" class="btn selected-btn mb-1 invoice-btn">Convert to Draft Invoice</a>
    @endif
 </div>

  <div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
     
    <table class="table entriestable table-bordered table-quotation-product text-center">
      <thead>
        <tr>
          <th>{{$global_terminologies['our_reference_number']}}</th>
          <th>Purchasing</th>
          <th>HS Code</th>
          <th>Name</th>
          <th>{{$global_terminologies['category']}}</th>
          <th>Default Supplier</th>
          <th># {{$global_terminologies['qty']}}</th>
          <th>Unit Price</th>
          <th>Total Price</th>
        </tr>
      </thead>
    </table>

    <div class="justify-content-left row">            
      <div class="col-lg-6 col-md-6 pr-0">
         <strong>Total Products</strong> : <span class="total_products">{{ $total_products }}</span>
      </div>
    </div>

    <div class="justify-content-right row">      
      <div class="col-lg-12">
        @if($order_invoice->primary_status == 2)
        <table class="ml-auto w-100">
          <tbody>
            <tr>
              <td class="font-weight-bold pr-3 pb-2 text-right">Sub Total :</td>
              <td class="pb-2">${{ number_format($sub_total, 2, '.', ',') }}</td>
            </tr>
            <tr class="d-none">
              <td class="font-weight-bold pr-3 pb-2 text-right">VAT :</td>
              <td class="pb-2">{{ number_format($order_invoice->vat, 2, '.', ',') }}%</td>
            </tr>
            <tr>
              <td class="font-weight-bold pr-3 pb-2 text-right">Total :</td>
              <td class="pb-2">${{ number_format($order_invoice->total_amount, 2, '.', ',') }}</td>
            </tr>
          </tbody>
        </table>
       @endif                              
      </div>
    </div>

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

  <div class="modal" id="createInvoiceModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Payment Details</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-payment-form" method="post" action="{{ route('make-draft-invoice') }}">
      @csrf
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="col-xs-12 col-md-12">                             
                <div class="form-group"> 
                  <label>Note <small>(500 Characters Max)</small></label>
                  <textarea class="form-control" placeholder="Add a Note..." rows="6" name="note" maxlength="500"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="invoice_id" class="list-id">
        <input type="hidden" name="selected_stones" class="selected-stones">
        <button class="btn btn-success" type="submit" class="save-btn"><i class="fa fa-plus"></i> Add </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

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

  $(function(e){
    $('.table-quotation-product').DataTable({
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
      ajax: "{{ url('get-completed-quotation-products-to-list') }}"+"/"+{{ $order_invoice->id }},       
      columns: [
          { data: 'refrence_code', name: 'refrence_code' },
          { data: 'po_no', name: 'po_no' },
          { data: 'hs_code', name: 'hs_code' },
          { data: 'name', name: 'name' },
          { data: 'category_id', name: 'category_id' },
          { data: 'supplier', name: 'supplier' },
          { data: 'quantity', name: 'quantity' },
          { data: 'unit_price', name: 'unit_price' },
          { data: 'total_price', name: 'total_price' },
      ]
    });

  $(document).on('click', '.invoice-btn', function(e){
    $('.list-id').val($(this).data('id'));
    $('.add-payment-form').submit();     
  });

  $('.custom-control-input').on('click', function(e){
      if($(this).prop('checked') == false){
        //$(this).removeAttr('checked');
         $(this).attr("checked", false);
      }
    });

  // export pdf code
  $(document).on('click', '.export-pdf', function(e){
    var draft_id = $('#draft_id_for_pdf').val();
    $('.export-draft-form')[0].submit();
  });

  });
</script>
@stop

