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
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Draft Invoice # <span class="c-ref-id">{{$order_invoice->ref_id }}</span></h3>
  </div>  
</div>


<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="justify-content-between row"> 
      <div class="d-flex mb-3 col-md-6 col-lg-5 col-12">
        <div class="invoiceImg">
          
          <img src="{{ asset('public/uploads/logo/sales/100X100/'.Auth::user()->getUserDetail->image) }}" class="border img-fluid" alt="">
         
        </div>
        <div class="invoiceDetail pl-3">
          <h5 class="font-weight-bold mb-2 text-capitalize">{{ Auth::user()->getUserDetail->company_name }}</h5>
           <p class="mb-2">{{ Auth::user()->getUserDetail->address.', '.Auth::user()->getUserDetail->country->name.', '.Auth::user()->getUserDetail->state->name.', '.Auth::user()->getUserDetail->zip_code }}</p>
          <ul class="list-inline list-unstyled pl-0">
            <li class="list-inline-item"><em class="fa fa-phone"></em> {{ Auth::user()->getUserDetail->phone_no }}</li>
            <li class="list-inline-item"><em class="fa fa-envelope"></em> {{ Auth::user()->email }} </li>
          </ul>
        </div>
      </div> 
    </div>
    
    <div class="justify-content-between row mt-3"> 
    @if($order_invoice->customer !== null)
    <div class="col-md-6 col-lg-5 col-12">
    <h5 class="font-weight-bold mb-2 text-capitalize">{{ $order_invoice->customer->company }}</h5>
    <div class="mb-1"><label class="mb-0">Customer Name: </label>{{ $order_invoice->customer->first_name.' '.$order_invoice->customer->last_name }}</div>
    <div class="mb-1"><label class="mb-0">@if($order_invoice->customer->address_line_1 !== null) {{ $order_invoice->customer->address_line_1.' '.$order_invoice->customer->address_line_2 }}, @endif  @if($order_invoice->customer->country !== null) {{ $order_invoice->customer->getcountry->name }}, @endif @if($order_invoice->customer->state !== null) {{ $order_invoice->customer->getstate->name }}, @endif @if($order_invoice->customer->city !== null) {{ $order_invoice->customer->city }}, @endif @if($order_invoice->customer->postalcode !== null) {{ $order_invoice->customer->postalcode }} @endif</div>
    
    @if($order_invoice->customer->email !== null || $order_invoice->customer->phone !== null)
    <ul class="list-inline list-unstyled pl-0">
      @if($order_invoice->customer->phone !== null)
      <li class="list-inline-item"><em class="fa fa-phone"></em> {{ $order_invoice->customer->phone }}</li>
      @endif
      @if($order_invoice->customer->phone !== null)
      <li class="list-inline-item"><em class="fa fa-envelope"></em> {{ $order_invoice->customer->email }}</li>
      @endif
    </ul>
    @endif

  </div>
  @endif
      <div class="col-md-6 col-lg-5 col-12">
        <div class="form-group">
          <label class="mb-1 font-weight-bold">Quotation # </label> <span class="dblclk-edit" data-type="ref_id">{{ $order_invoice->ref_id }}</span>
        </div>
        <div class="form-group"> 
          <label class="mb-1 font-weight-bold">Quotation Date:</label> <span class="dblclk-edit memo-date" data-type="date_picker">{{ $order_invoice->created_at }}</span>
        </div> 
    </div>
  </div>
  
    

  <div class="row mb-3">
    <div class="col-12 align-items-center">
    <a href="{{  route('pending-list-draft-invoices') }}" class="btn mb-1">Back</a>
    
    @if($order_invoice->status == 1 && $order_invoice->invoice_status == 0)
    <a href="javascript:void(0);" data-id="{{ $order_invoice->id }}" class="btn selected-btn mb-1 invoice-btn">Convert to Draft Invoice</a>
    @endif
  </div>
 </div> 

          @if(@$print_fields)
           @php
            $fields = explode(',', $print_fields->printable_columns)
           @endphp
          @else
           @php
            $fields = ['ref_no', 'stone_type', 'color', 'shape', 'origin','treatment', 'dimensions', 'weight', 'number_of_pieces', 'unit_price', 'total_price'];
           @endphp
          @endif
          <table class="table entriestable table-bordered table-quotation-product text-center">
              <thead>
                  <tr>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox">
                          <input type="checkbox" @if(in_array('ref_no', $fields)) checked @endif class="custom-control-input checked" value="ref_no" id="ref_no" name="table_col">
                          <label class="custom-control-label" for="ref_no">&nbsp; Reference  #</label>
                        </div></th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('stone_type', $fields)) checked @endif class="custom-control-input checked" value="stone_type" id="stone_type" name="table_col">
                          <label class="custom-control-label" for="stone_type">&nbsp;HS Code</label>
                        </div> </th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('color', $fields)) checked @endif class="custom-control-input checked" value="color" id="color">
                          <label class="custom-control-label" for="color">&nbsp;Name</label>
                        </div> </th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('shape', $fields)) checked @endif class="custom-control-input checked" value="shape" id="shape">
                          <label class="custom-control-label" for="shape">&nbsp;Product Type</label>
                        </div> </th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('origin', $fields)) checked @endif class="custom-control-input checked" value="origin" id="origin">
                          <label class="custom-control-label" for="origin">&nbsp;Default Supplier</label>
                        </div> </th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('treatment', $fields)) checked @endif class="custom-control-input checked" value="treatment" id="treatment">
                          <label class="custom-control-label" for="treatment">&nbsp;#  @if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif</label>
                        </div> </th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('dimensions', $fields)) checked @endif class="custom-control-input checked" value="dimensions" id="dimensions">
                          <label class="custom-control-label" for="dimensions">&nbsp;Unit Price</label>
                        </div> </th>
                      <th class="inv-head"><div class="custom-control custom-checkbox custom-checkbox1 d-inline-block thead-checkbox ">
                          <input type="checkbox" @if(in_array('weight', $fields)) checked @endif class="custom-control-input checked" value="weight" id="weight">
                          <label class="custom-control-label" for="weight">&nbsp;Total Price</label>
                        </div> </th>
                      
                  </tr>
              </thead>
               
          </table>
          <div class="justify-content-left row">            
            <div class="col-lg-6 col-md-6 pr-0">
               <strong>Total Products</strong> : <span class="total_products">{{ $total_products }}</span>
            </div>
          </div>

          <div class="row d-flex">      
            <div class="col-lg-4 col-md-5 pl-3 pt-md-3">
                @if($order_invoice->status == 1)

                <table class="avoid-invoice-table ml-auto w-100">
                  <tbody>
                    <tr>
                      <td class="font-weight-bold pr-3 pb-2 text-right">Sub Total :</td>
                      <td class="pb-2">${{ number_format($sub_total, 2, '.', ',') }}</td>
                    </tr>
                    <tr>
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
  </div>
</div>

</div>
<!--  Content End Here -->


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
        ajax: "{{ url('get-completed-quotation-pending-products-to-list') }}"+"/"+{{ $order_invoice->id }},       
        columns: [
            { data: 'refrence_code', name: 'refrence_code' },
            { data: 'hs_code', name: 'hs_code' },
            { data: 'name', name: 'name' },
            { data: 'product_type', name: 'product_type' },
            { data: 'supplier', name: 'supplier' },
            { data: 'quantity', name: 'quantity' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'total_price', name: 'total_price' },
        ]
   });

     $(document).on('click', '.check-all', function () {
        if(this.checked == true){
        $('.check').prop('checked', true);
        var cb_length = $( ".check:checked" ).length;
        if(cb_length > 0){
          $('.selected-btn').removeClass('d-none');
        }
      }else{
        $('.check').prop('checked', false);
        $('.selected-btn').addClass('d-none');
        
      }
    });

   $(document).on('click', '.check', function () {
        if(this.checked == true){
        $('.selected-btn').removeClass('d-none');
      }else{
        var cb_length = $( ".check:checked" ).length;
        if(cb_length == 0){
         $('.selected-btn').addClass('d-none');
        }
        
      }
    });

    $(document).on('keyup', function(e) {
      if (e.keyCode === 27){ // esc
        if($('.list-bank').hasClass('d-none')){
          this_element = $('.list-bank');
          this_element.removeClass('d-none');
          this_element.siblings('select').addClass('d-none');
        }
        if ( $( ".u-ref-id" ).length ){
          let newVal = $('.u-ref-id').val();
            $('.u-ref-id').parent('span').addClass('dblclk-edit');
            $('.u-ref-id').parent('span').html(newVal);
            $('.c-ref-id').html(newVal);
          }  
        
        if($('.list-discount').hasClass('d-none')){
          $('input[name=discount]').addClass('d-none');
          $('input[name=discount]').prev().removeClass('d-none'); 
      }
        if($('.list-vat').hasClass('d-none')){
          $('input[name=vat]').addClass('d-none');
          $('input[name=vat]').prev().removeClass('d-none');
      }
      if($('.inv-shipping').hasClass('d-none')){
          $('input[name=shipping_cost]').addClass('d-none');
          $('input[name=shipping_cost]').prev().removeClass('d-none');
      }
       if($('.list-broker-fee').hasClass('d-none')){
          $('input[name=broker_fee]').addClass('d-none');
          $('input[name=broker_fee]').prev().removeClass('d-none');
       }
       if($('.memo-broker-name').hasClass('d-none')){
          $('input[name=broker_name]').addClass('d-none');
          $('input[name=broker_name]').prev().removeClass('d-none');
       }
       if($('.list-unit-price').hasClass('d-none')){
          $('input[name=unit_price]').addClass('d-none');
          $('input[name=unit_price]').prev().removeClass('d-none');
       }

       if($('.list-total-price').hasClass('d-none')){
          $('input[name=price_sold_on]').addClass('d-none');
          $('input[name=price_sold_on]').prev().removeClass('d-none');
       }

       if($('.inv-note').hasClass('d-none')){
          $('textarea[name=note]').addClass('d-none');
          $('textarea[name=note]').prev().removeClass('d-none');
       }

       if($('.u-list-date').length ){
        let newVal = $('.u-list-date').val();
          $('.u-list-date').parent('span').addClass('dblclk-edit');
          $('.u-list-date').parent('span').html(newVal);
       }
       if($('.u-payment-term').length){
          $('.u-payment-term').parent('span').addClass('dblclk-edit');
          $('.u-payment-term').parent('span').html($('.u-payment-term option:selected').text());
       }


       }
       
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

  });
</script>
@stop

