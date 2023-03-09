@extends('warehouse.layouts.layout')

@section('title','Transfer Document| Warehouse')

@section('content')
<style type="text/css">
.supplier_ref {
      width: 15%;
      word-break: break-all;
  }

  .pf {
      width: 15%;
  }

  .supplier {
      width: 18%;
  }

  .description {
      width: 50%;
  }

  .p_type{
    width: 15%;
  }

  .p_winery{
    width: 15%;
  }

  .p_notes{
    width: 10%;
  }

  .rsv {
      width: 8%;
  }

  .pStock {
      width: 8%;
  }

  .sIcon {
      width: 20px;
  }

/*search styoling up*/

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

.second_drop .select2-container{
  width: 100% !important;
}
</style>
@php
use Carbon\Carbon;
@endphp

{{--novalidate for solve live error--}}

{{-- Content Start from here --}}
{{--my new branch created--}}
<form class="mb-2 purchase_order_form" enctype='multipart/form-data'> 

<div class="row mb-3 headings-color">
  
  <div class=" col-lg-8 col-md-6 title-col">
    <h3 class="maintitle text-uppercase fontbold">Draft {{$global_terminologies['transfer_document']}} # <span class="c-ref-id">{{ $id }}</span></h3>
  </div>
  <div class="col-lg-4 col-md-6">
    <h3 class="maintitle text-uppercase fontbold">{{$global_terminologies['supply_from']}} </h3>
  </div>
<!-- New Design Starts Here  -->
<div class="col-lg-12 col-md-12">
<div class="row">
  
<div class="col-lg-8 col-md-6">
 @if(@$company_info->logo != null && file_exists( public_path() . '/uploads/logo/' . $company_info->logo))
  <img src="{{asset('public/uploads/logo/'.$company_info->logo)}}" class="img-fluid" style="width: 65px;height: auto;" align="big-qummy">
  @else
  <img src="{{asset('public/img/profileImg.jpg')}}" class="img-fluid" style="width: 65px;height: auto;" align="big-qummy">
  @endif
  <p class="comp-name mb-0 pl-2" style="display: inline-block;line-height: 2;">{{@$company_info->company_name}}</p>
  <p class="mb-1">{{@$company_info->billing_address}},{{@$company_info->getcountry->name}},{{@$company_info->getstate->name}},{{@$company_info->billing_zip}}</p>
  <p class="mb-1"><em class="fa fa-phone"></em> {{@$company_info->billing_phone}}  <em class="fa fa-envelope"></em>  {{@$company_info->billing_email}}</p>
  <br>
  
</div>
<div class="col-lg-4 col-md-6">
  <!-- <p class="mb-1">Purchase From</p> -->
  <input type="hidden" name="selected_supplier_id" id="selected_supplier_id" value="{{$draft_po->supplier_id}}">
  <input type="hidden" name="selected_warehouse_id" id="selected_warehouse_id" value="{{$draft_po->from_warehouse_id}}">
  <div class="update-supplier-div"></div>
  @if($draft_po->getFromWarehoue != NULL)

    @if($draft_po->getFromWarehoue != NULL)
    <div class="supplier_selected_info">
    <i class="fa fa-edit edit-address change_supplier" title="Change @if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif" style="cursor: pointer;"></i>
    <div class="d-flex align-items-center mb-1">
      <div>
        <img src="{{asset('public/img/warehouse-logo.png')}}" class="img-fluid" align="big-qummy" style="width: 65px; height: auto;">
      </div>
      <div class="pl-2 comp-name" data-supplier-id="{{@$draft_po->from_warehouse_id}}"><p>{{$draft_po->getFromWarehoue->warehouse_title}}</p></div>
    </div>
        
    <p class="mb-1">
      @if($draft_po->getFromWarehoue->getCompany !== null) 
      {{ $draft_po->getFromWarehoue->getCompany->billing_address }}, 
      @endif  
      @if($draft_po->getFromWarehoue->getCompany->getcountry !== null) 
      {{ $draft_po->getFromWarehoue->getCompany->getcountry->name }}, 
      @endif 
      @if($draft_po->getFromWarehoue->getCompany->getstate !== null) 
      {{ $draft_po->getFromWarehoue->getCompany->getstate->name }}, 
      @endif 
      @if($draft_po->getFromWarehoue->getCompany->city !== null) 
      {{ $draft_po->getFromWarehoue->getCompany->city }}, 
      @endif 
      @if($draft_po->getFromWarehoue->getCompany->postalcode !== null) 
      {{ $draft_po->getFromWarehoue->getCompany->postalcode }} 
      @endif
    </p>
    @if($draft_po->getFromWarehoue->getCompany->billing_email !== null || $draft_po->getFromWarehoue->getCompany->billing_phone !== null)
    <ul class="d-flex list-unstyled">
        @if($draft_po->getFromWarehoue->getCompany->billing_phone !== null)
        <li><i class="fa fa-phone pr-2"></i>{{$draft_po->getFromWarehoue->getCompany->billing_phone}}</li>
        @endif
        @if($draft_po->getFromWarehoue->getCompany->billing_email !== null)
        <li class="pl-3"><i class="fa fa-envelope pr-2"></i>{{$draft_po->getFromWarehoue->getCompany->billing_email}}</li>
        @endif
    </ul>
    @endif
    </div>
    @endif

    <div class="d-none col-lg-12 second_drop p-0">
      <select class="form-control js-states state-tags mb-2 add-supp" name="supplier">
        <option value="new" disabled="" selected="">Choose @if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif</option> 
        @if(@$warehouses->count() > 0)
          <optgroup label="Warehouses"> 
          @foreach($warehouses as $warehouse)
          <option {{$draft_po->from_warehouse_id == $warehouse->id ? "selected" : ""}} value="w-{{ $warehouse->id }}"> {{$warehouse->warehouse_title}} </option>
          @endforeach
          </optgroup>
        @endif
      </select>
      <div class="supplier_info"></div>
    </div>
  @else
    <select class="form-control js-states state-tags mb-2 add-supp" name="supplier">
      <option value="new" disabled="" selected="">Choose @if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif</option> 
      @if(@$warehouses->count() > 0)
        <optgroup label="Warehouses"> 
        @foreach($warehouses as $warehouse)
        <option value="w-{{ $warehouse->id }}"> {{$warehouse->warehouse_title}} </option>
        @endforeach
        </optgroup>
      @endif
      {{--<option value="new">Add New</option>--}}
    </select>
    <div class="supplier_info"></div>
  @endif

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">To Warehouse:</li>
    <span class="pl-4">
      @if($draft_po->to_warehouse_id != null)
      {{$draft_po->getWarehoue->warehouse_title}}
      @else
      <div style="color: red;">Select To Warehouse</div>
      @endif
    </span>
    <select class="form-control warehouse_id d-none mb-2 select-common" name="warehouse_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Warehouse</option>';
      @foreach ($warehousesTo as $w)
        @if($draft_po->to_warehouse_id == $w->id)
            <option selected value="{{$w->id}}">{{$w->warehouse_title}}</option>
        @else
            <option value="{{$w->id}}">{{$w->warehouse_title}}</option>
        @endif
      @endforeach
    </select>
  </ul>

  @php
    $transfer_date = $draft_po->transfer_date != null ? Carbon::parse($draft_po->transfer_date)->format('d/m/Y') : "";
  @endphp
  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">Transfer Date:</li>
    <span class="pl-4 inputDoubleClick" id="transfer_date_span" data-fieldvalue="{{$transfer_date}}">
      @if($draft_po->transfer_date != null)
      {{Carbon::parse($draft_po->transfer_date)->format('d/m/Y')}}
      @else
      Transfer Date Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none transfer_date" name="transfer_date" id="transfer_date" value="{{$transfer_date}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled">
    <li class=" fontbold" style="width: 150px;">Target Received Date:</li>
    <span class="pl-4 inputDoubleClick">
      @if($draft_po->target_receive_date != null)
      {{Carbon::parse($draft_po->target_receive_date)->format('d-M-Y')}}
      @else
       Target Received Date Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none target_receive_date" name="target_receive_date" id="target_receive_date" value="{{@$draft_po->target_receive_date}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled" style="display: none !important;">
    <li class=" fontbold" style="width: 150px;">Payment Terms:</li>
    <span class="pl-4 inputDoubleClick">
      @if($draft_po->payment_terms_id != null)
      {{$draft_po->paymentTerm->title}}
      @else
      Select Term Here
      @endif
    </span>
    <select class="form-control payment_terms_id d-none mb-2 select-common" name="payment_terms_id" style="width: 40%; margin-left: 25px; height: 40px;">
      <option selected disabled value="">Select Term</option>';
      @foreach ($paymentTerms as $pm)
        @if($draft_po->payment_terms_id == $pm->id)
            <option selected value="{{$pm->id}}">{{$pm->title}}</option>
        @else
            <option value="{{$pm->id}}">{{$pm->title}}</option>
        @endif
      @endforeach
    </select>
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled" style="display: none !important;">
    <li class=" fontbold" style="width: 150px;">@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif:</li>
    <span class="pl-4 payment_due_date_term">
      @if($draft_po->payment_due_date != null)
      {{Carbon::parse($draft_po->payment_due_date)->format('d-M-Y')}}
      @else
      --
      @endif
    </span>
    <input type="date" class="ml-4 mt-2 d-none payment_due_date fieldFocus" name="payment_due_date" id="payment_due_date" value="{{@$draft_po->payment_due_date}}">
  </ul>

  <ul class="d-flex mb-0 pt-2 list-unstyled" style="display: none !important;">
    <li class=" fontbold" style="width: 150px;">Memo:</li>
    <span class="pl-4 inputDoubleClick" data-fieldvalue="{{@$draft_po->memo}}">
      @if($draft_po->memo != null)
      {{$draft_po->memo}}
      @else
      Memo Here
      @endif
    </span>
    <input type="text" class="ml-4 mt-2 d-none memo fieldFocus" name="memo" id="memo" value="{{@$draft_po->memo}}">
  </ul>
  
</div>

<div class="col-lg-12 text-uppercase fontbold mt-2">
  <a onclick="backFunctionality()">
    <button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color">back</button>
  </a>

  <a href="#">
    <button type="button" class="btn text-uppercase purch-btn mr-3 headings-color btn-color d-none">print</button>
  </a>
  
  <a href="#">
    <button type="button" class="btn text-uppercase purch-btn headings-color btn-color d-none">print</button>
  </a>
  
  <div class="pull-right">
    @if($checkDraftPoDocs > 0)
      @php $show = ""; @endphp
    @else
      @php $show = "d-none"; @endphp
    @endif
    {{--<a href="{{ url('getting-draft-po-docs-for-download/'.$id) }}">
    <button type="button" class="btn-color btn text-uppercase purch-btn headings-color download-docs {{$show}}">Download documents<i class="pl-1 fa fa-download"></i></button>
    </a>--}}

    <a href="javascript:void(0);" data-id="{{$draft_po->id}}" class="download-documents">
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
          {{--<th>Sup's ref # </th>--}}
          <th>{{$global_terminologies['our_reference_number']}}</th>
          <th>{{$global_terminologies['product_description']}} </th>
          <th>Selling  Unit</th>
          {{--<th>Supplier <br> Packaging</th>--}}
          {{--<th>Billed Unit <br> Per Package</th>--}}
          <th>{{$global_terminologies['qty']}}</th>
          {{--<th>Unit Price</th>--}}
          {{--<th>Discount</th>--}}
          {{--<th>Amount</th>--}}
          {{--<th>Warehouse</th>--}}       
          {{--<th>Unit <br> Gross <br> Weight</th>--}}
          {{--<th>Total <br> Gross <br> Weight</th>--}}                                     
      </tr>
      </thead>
         
    </table>
        

  <!-- New Design Starts Here  -->
  <div class="row ml-0 mb-4">
  <div class="col-8 mt-4">
    <div class="col-6">
      <div class="purch-border input-group custom-input-group">
      <input type="text" name="refrence_code" style="border-bottom: 1px #dee2e6 !important;" placeholder="Type Reference number..." 
      data-draft_po_id = "{{$id}}" class="form-control refrence_number" autocomplete="off">
      </div>
    </div>
    <div class="col-12 mt-4 mb-4"> 
      <a class="btn purch-add-btn mt-3 fontmed col-lg-2 col-md-5 btn-sale" id="addProduct">Add Product</a> 
    </div>

     <div class="row">
    <div class="col-lg-6 col-md-8 ml-5">
      <p>
        <strong>Note: </strong>
        <span class="po-note inputDoubleClick ml-2" data-fieldvalue="{{@$getPoNote->note}}">@if($getPoNote != null) {!! @$getPoNote->note !!} @else {{ 'Double click here to add a note...' }} @endif</span>
        <textarea autocomplete="off" name="note" rows="5" class="form-control d-none r-note fieldFocus" placeholder="Add a note (500 Characters)" maxlength="500">{{ $getPoNote !== null ? @$getPoNote->note : '' }}</textarea>
      </p>
    </div>
    <div class="col-lg-6 col-md-6"></div>
    </div>
  </div>
       
  <div class="col-lg-4 col-md-4 pt-4 mt-4">
    <div class="side-table">
    <table class="headings-color purch-last-left-table side-table">
      <tbody>
        <tr class="d-none">
          <td class="fontbold" width="50%">Total:</td>
          <input type="hidden" name="sub_total" value="{{$sub_total}}" id="sub_total">
          <td class="text-start sub-total fontbold">&nbsp;&nbsp;{{ number_format($sub_total, 3, '.', ',') }}</td>
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
          <div class="col-lg-5 col-md-6 pl-3 pt-md-3">       
       
          <div class="text-right">  
            
            <input type="hidden" name="draft_po_id" id="draft_po_id" value="{{ $id }}">
            <input type="hidden" name="action" value="save">
            <button type="submit" class="btn btn-sm pl-3 pr-3 btn-primary">Save Transfer</button>
    </form>
          
          <form class="d-inline-block mb-2 purchase_order_form_discard" >
            <input type="hidden" name="draft_po_id" value="{{ $id }}">
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
  <div class="modal-dialog modal-lg">
  <div class="modal-content">

  <div class="modal-header">
    <h4 class="modal-title">Search Product</h4>
    <p style="color: red;" align="right" class="mr-2">(Note:* Enter atleast 3 characters then press Enter)</p>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

  <div class="modal-body">
    <div class="form-group" style="margin-top: 10px; margin-bottom: 50px; position:relative;">
      <i class="fa fa-search" aria-hidden="true" style="position: absolute; top: 10px; left: 10px;color:#ccc;"></i>
      <input type="text" name="prod_name" id="prod_name" class="form-control form-group mb-0" placeholder="Search by Product Reference #-Default Supplier- Product Description-Brand  (Press Enter)" autocomplete="off" style="padding-left:30px;">
    </div>
    <div id="product_name_div"></div>
  </div>
    
  </div>
  </div>
</div>

<!-- Upload Document Modal -->
{{-- Add documents modal --}}
<div class="modal addDocumentModal" id="addDocumentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">UPLOAD DOCUMENTS</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="row justify-content-end" style="width: 100%;">  
      <a href="#demo" class="btn btn-primary" data-toggle="collapse" style="margin-top: 7px;">Upload Document</a>
  <div id="demo" class="collapse col-lg-10 offset-1">
    <form id="addDocumentForm" class="addDocumentForm" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
      <input type="hidden" name="draft_purchase_order_id" id="sid" value="{{$id}}">
          
          <div class="row">
                <div class="form-group col-lg-9">
                  <label class="pull-left font-weight-bold">Files <span class="text-danger">*</span></label>
                  <input class="font-weight-bold form-control-lg form-control" name="po_docs[]" type="file" multiple="" accept=".xlsx,.xls,.doc, .docx,.ppt, .pptx,.txt,.pdf" required="">
                </div>
                <div class="col-lg-3">
          <button type="submit" class="btn btn-primary save-doc-btn" style="margin-top: 2rem;" id="addDocBtn">Upload</button>
                  
                </div>
          </div>
        </div>

      </form>
  </div>
      </div>
          

       <div class="fetched-files">
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
  $("#transfer_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });
  $("#target_receive_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });

  $(document).on("change","#transfer_date,#target_receive_date",function(e) {
    var draft_po_id = "{{ $id }}";
    var attr_name = $(this).attr('name');
    var id = $(this).attr('id');

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();
    if(fieldvalue == new_value)
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      $(this).removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      return false;
    }

    if (e.keyCode === 27 && $(this).hasClass('active') ) 
    {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      // $(this).prev().html(fieldvalue);
      $('#'+id).datepicker({autoclose:true});
      $("#transfer_date").datepicker('hide');
      return false;
    }

    if(attr_name == 'payment_due_date')
    {
      if($(this).val() == '')
      {
        $(this).prev().html("Payment Due Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      }
      else
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }
    if(attr_name == 'target_receive_date')
    {
      if($(this).val() == '')
      {
        // alert($(this).val());
        $(this).prev().html("Target Ship Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      }
      else
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }
    if(attr_name == 'invoice_date')
    {
      if($(this).val() == '')
      {
        $(this).prev().html("Invoice Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      }
      else
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
    }
    if(attr_name == 'transfer_date')
    {
      if($(this).val() == '')
      {
        
        $(this).prev().html("Transfer Date Here");
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        return false;
      }
      else
      {
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().removeData('fieldvalue');
        $(this).prev().data('fieldvalue', new_value);
        $(this).attr('value', new_value);
        $(this).prev().html(new_value);
      }
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
      data: 'draft_po_id='+draft_po_id+'&'+attr_name+'='+encodeURIComponent($(this).val()),
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        if(data.success == true && data.draft_note == false)
        {
          toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
          if(attr_name == 'invoice_date')
          {
            if(data.draft_po.payment_due_date != null)
            {
              var newDate = $.datepicker.formatDate( "dd/mm/yy", new Date(data.draft_po.payment_due_date));
              $('.payment_due_date_term').html(newDate);
            }
          }
        }
      }   
    });
  });

  $(".state-tags").select2();
  $(".state-tags-2").select2();
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
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 1,2,3] },
        { className: "dt-body-right", "targets": [4] }
      ],
      fixedHeader: true,
      colReorder: {
      realtime: false,
      },
      dom: 'lrtip',
      ajax: "{{ url('get-product-to-list-draft-po') }}"+"/"+{{ $id }},
      columns: [
          { data: 'action', name: 'action'},
          // { data: 'supplier_id', name: 'supplier_id' },
          { data: 'item_ref', name: 'item_ref' },
          { data: 'short_desc', name: 'short_desc' },
          { data: 'selling_unit', name: 'selling_unit' },

          // { data: 'supplier_packaging', name: 'supplier_packaging' },
          // { data: 'billed_unit_per_package', name: 'billed_unit_per_package' },

          { data: 'quantity', name: 'quantity' },
          // { data: 'unit_price', name: 'unit_price' , visible:false},
          // { data: 'discount', name: 'discount' , visible:false},
          // { data: 'amount', name: 'amount' , visible:false},
          // { data: 'warehouse', name: 'warehouse' },
          // { data: 'unit_gross_weight', name: 'unit_gross_weight' , visible:false},
          // { data: 'gross_weight', name: 'gross_weight' , visible:false},
      ]
  });

  $(document).on('click','#addProduct',function(){
    var supplier_id = $("#selected_supplier_id").val();
    var warehouse_id = $("#selected_warehouse_id").val();
    if(supplier_id == '' && warehouse_id == '')
    {
       swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
    }
    else
    {
      if($(this).attr("id") == 'addProduct')
      {
        $('#addProductModal').modal('show');
        $('#prod_name').val('');
        $('#product_name_div').empty();
        $('#prod_name').focus();
      }
    }
  });

  $('.purchase_order_form_discard').on('submit', function(e){
     e.preventDefault();
      $.ajaxSetup({
        headers: 
        {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('action-draft-po') }}",
        method: 'post',
        data: $('.purchase_order_form_discard').serialize(),
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").modal('show');
        },
        success: function(result)
        {
          $("#loader_modal").modal('hide');
          if(result.success == true)
          {
            toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
            setTimeout(function(){
              window.location.href = "{{ route('transfer-document-dashboard')}}";
            }, 500);
          }
        },
        error: function (request, status, error) 
        {
          $("#loader_modal").modal('hide');
          $('.form-control').removeClass('is-invalid');
          $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
              $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('input[name="'+key+'"]').addClass('is-invalid');
          });
        }
      });

  });

  $(document).on('submit','.purchase_order_form',function (e) {
    
    var inverror = false;
    var supplier = $("#selected_supplier_id").val();
    var from_warehouse = $("#selected_warehouse_id").val();
    var warehouse = $('.warehouse_id :selected').val();
    var target_receive_date = $(".target_receive_date").val();
    var transfer_date = $(".transfer_date").val();
    var payment_due_date = $(".payment_due_date").val();

    if(supplier == '' && from_warehouse == '')
    {    
      swal({ html:true, title:'Alert !!!', text:'<b>Please Select Supply From First!!!</b>'});
      inverror = true;
    }
    else if(warehouse == '')
    {
      swal({ html:true, title:'Alert !!!', text:'<b>Must Select Warehouse For Ordered Items!!!</b>'});
      inverror = true;
    }
    else if(from_warehouse != '' && warehouse != '' )
    {
      if( from_warehouse == warehouse )
      {
        swal({ html:true, title:'Alert !!!', text:'<b>Supply From & To Warehouse Cannot Be Same!!!</b>'});
        inverror = true;
      }
    }
    else
    {
      inverror = false;
    }

    if(inverror == true)
    {
      e.preventDefault();
      return false;
    }
    else
    {
      e.preventDefault();
      $.ajaxSetup({
        headers: 
        {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('action-draft-w-td') }}",
        method: 'post',
        data: $('.purchase_order_form').serialize(),
        context: this,
        beforeSend: function(){
          $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
          $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
          $("#loader_modal").modal('show');
        },
        success: function(result)
        {
          // $("#loader_modal").modal('hide');
          if(result.success == true)
          {
            toastr.success('Success!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
            // setTimeout(function(){
              window.location.href = "{{ route('warehouse-transfer-document-dashboard')}}";
            // }, 500);
          }
          else if(result.success == false)
          {
            toastr.error('Error!', result.errorMsg ,{"positionClass": "toast-bottom-right"});
          }
        },
        error: function (request, status, error) 
        {
          $("#loader_modal").modal('hide');
          $('.form-control').removeClass('is-invalid');
          $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
              $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('input[name="'+key+'"]').addClass('is-invalid');
          });
        }
      });
    }

  });

  @if(Session::has('errormsg'))
    toastr.error('Error!', "{{ Session::get('errormsg') }}",{"positionClass": "toast-bottom-right"});
  @endif     

  });  
  
</script>
<script>
	function backFunctionality() {
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $('#loader_modal').modal('show');
		if (history.length > 1) {
			return history.go(-1);
		} else {
			var url = "{{ url('/') }}";
			document.location.href = url;
		}
	}

</script>
<script>
  $(document).ready(function(){

  $('#prod_name').keyup(function(e){ 
    var page = "Td";
    var query = $.trim($(this).val());

    var supplier_id = $("#selected_supplier_id").val();
    var warehouse_id = $("#selected_warehouse_id").val();
    var draft_po_id = $("#draft_po_id").val();
    if(query == '' || e.keyCode == 8 || 'keyup' )
    {
      $('#product_name_div').empty();
    }
    if(e.keyCode == 13)
    {
      if(query.length > 2)
      {
        var _token = $('input[name="_token"]').val();
        $.ajax({
          url:"{{ route('autocomplete-fetch-product') }}",
          // url:"{{ route('autocomplete-fetching-products-for-transfer') }}",
          method:"POST",
          data:{query:query, _token:_token, inv_id:draft_po_id, supplier_id:supplier_id, warehouse_id:warehouse_id, page:page},
          beforeSend: function(){
          $('#product_name_div').html('<div align="center"><img src="{{asset("public/img/spinner.gif")}}" height="75"></div>');
          },
          success:function(data){
            $('#product_name_div').fadeIn();  
            $('#product_name_div').html(data);
          }
       });
      }
      else
      {
        toastr.error('Error!', 'Please enter atlesat 3 characters then press Enter !!!' ,{"positionClass": "toast-bottom-right"});
      }
    }
  });

  $(document).on('click', 'li', function(){  
    $('#prod_name').val("");  
    $('#product_name_div').fadeOut();  
  });  

  });

  $(document).on('click', '.add_product_to', function(e){
  var draft_po_id = $(this).data('inv_id');
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
  var sup_or_wh_id = $(this).val().split('-');
  var draft_po_id = '{{$id}}';
  if(sup_or_wh_id[0] == 's')
  {
    var selected_supplier_id = $("#selected_supplier_id").val(sup_or_wh_id[1]);
  }
  if(sup_or_wh_id[0] == 'w')
  {
    var selected_warehouse_id = $("#selected_warehouse_id").val(sup_or_wh_id[1]);
  }

  var _token = $('input[name="_token"]').val();
  $.ajax({
    url:"{{ route('add-supplier-to-draft-po') }}",
    method:"POST",
    data:{_token:_token,supplier_id:supplier_id,draft_po_id:draft_po_id},
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
     $("#loader_modal").modal('show');
      },
    success:function(data){
      $("#loader_modal").modal('hide');
      // if(data.type == "PO")
      // {
      //   $("#po_or_td").html("Draft Purchase Order");
      // }
      // else if(data.type == "TD")
      // {
      //   $("#po_or_td").html("Draft Transfer Document");
      // }
      // console.log(data);
      $('.add-supp').next().addClass('d-none');
      $('.supplier_info').removeClass('d-none');
      $('.supplier_info').html(data.html);
    }
   });
  });

  $(document).on('click','.change_supplier',function(){
  var draft_po_id = '{{$id}}';
  var _token = $('input[name="_token"]').val();
  $.ajax({
    url:"{{ route('check-if-products-exist-on-dpo') }}",
    method:"get",
    dataType: 'json',
    data:{_token:_token,draft_po_id:draft_po_id},
    beforeSend:function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
     $("#loader_modal").modal('show');
    },
    success:function(data)
    {
     $("#loader_modal").modal('hide');
      if(data.success == false)
      {
        toastr.error('Error!', 'You Cannot Change Supply From Of This Purchase Order, Becuase Its Contain Products Of That Supply From.',{"positionClass": "toast-bottom-right"});
      }
      if(data.success == true)
      {
        $(".state-tags-2").select2();
        $('.supplier_selected_info').addClass('d-none');
        $('.supplier_selected_info').next().removeClass('d-none');
        $('.supplier_info').addClass('d-none');
        // $('.update-supplier-div').html(data.html);
        $('.add-supp').next().removeClass('d-none');
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
          // $('.addDocumentModal').modal('hide');
          $('.download-docs').removeClass('d-none');
           $('.collapse').collapse("toggle");
          let sid = $('#sid').val();

    $.ajax({
      type: "post",
      url: "{{ route('get-draft-order-files') }}",
      data: 'order_id='+sid,
      beforeSend: function(){
        var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
        var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
        $('.fetched-files').html(loader_html);
      },
      success: function(response){
        $('.fetched-files').html(response);
      }
    });
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
    $(this).next().addClass('active');
    $(this).next().focus();
  });

  $(document).on("keyup focusout",".fieldFocus",function(e) { 
      var draft_po_id = "{{ $id }}";
      var attr_name = $(this).attr('name');
      if (e.keyCode === 27 && $(this).hasClass('active') ) 
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');
        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        return false;
      }
      if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
      if(attr_name == 'note')
      {
        var fieldvalue = $(this).prev().data('fieldvalue');
         var new_value = $(this).val();
         if($(this).val().length < 1)
          {
            return false;
          }
          else if(fieldvalue == new_value)
          {
            // alert('hi');
              var thisPointer = $(this);
              thisPointer.addClass('d-none');
              
              thisPointer.removeClass('active');
              thisPointer.prev().removeClass('d-none');
              return false;
          }
        else if($(this).val() == '')
        {
          $(this).prev().html("Double click here to add a note!!!");
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
          return false;
        }
        else
        {
          $(this).prev().html($(this).val());
          $(this).addClass('d-none');
          $(this).removeClass('active');
          $(this).prev().removeClass('d-none');
        }
      }
      if(attr_name == 'memo')
      {
        if (e.keyCode === 27 && $(this).hasClass('active')) {
          var fieldvalue = $(this).prev().data('fieldvalue');
          var thisPointer = $(this);
            thisPointer.addClass('d-none');
            
            thisPointer.val(fieldvalue);
            thisPointer.removeClass('active');
            thisPointer.prev().removeClass('d-none');
        }
        $(this).prev().html($(this).val());
        if($(this).val() == '')
        {
          return false;
        }
        $(this).prev().html($(this).val());
        $(this).addClass('d-none');
        $(this).removeClass('active');
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
        if(data.success == true && data.draft_note == false)
        {
          toastr.success('Success!', 'Information Updated Successfully.',{"positionClass": "toast-bottom-right"});
          if(attr_name == 'target_receive_date')
          {
            if(data.draft_po.payment_due_date != null)
            {
              $('.payment_due_date_term').html(data.draft_po.payment_due_date);
            }
          }
        }
        if(data.draft_note == true)
        {
          toastr.success('Success!', 'Note Added/Updated Successfully.',{"positionClass": "toast-bottom-right"});
        }
      }   
    });
  }
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

  $(document).on('change', 'select.select-common', function(){

    if($(this).attr('name') == "warehouse_id")
    {

    var warehouse_id = $(this).val();
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
      data: {warehouse_id:warehouse_id, draft_po_id:draft_po_id},
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
          // toastr.success('Success!', 'Warehouse Assigned Successfully.' ,{"positionClass": "toast-bottom-right"});
          $(this).prev().html($("option:selected", this).html());
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
        }
      },
    });

    }

    if($(this).attr('name') == "payment_terms_id")
    {
      var target_receive_date = $(".target_receive_date").val();
      if(target_receive_date == '')
      {
        $('.payment_terms_id').val('');
        swal({ html:true, title:'Alert !!!', text:'<b>Must Fill Target Ship Date First !!!</b>'});
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
        return false;
      }
      else
      {

      var payment_terms_id = $(this).val();
      var draft_po_id = '{{$id}}';
      
      $.ajaxSetup({
      headers: 
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
      });

      $.ajax({
        method: "post",
        url: "{{ url('payment-term-save-in-dpo') }}",
        dataType: 'json',
        context: this,
        data: {payment_terms_id:payment_terms_id, draft_po_id:draft_po_id},
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
            $('.payment_due_date_term').html(data.payment_due_date);
            $(this).prev().html($("option:selected", this).html());
            $(this).removeClass('active');
            $(this).addClass('d-none');
            $(this).prev().removeClass('d-none');
          }
        },
      });

      }
    }

  });

  $(document).on('keyup focusout', 'input[type=number]', function(e){
      var fieldvalue = $(this).prev().data('fieldvalue');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.val(fieldvalue);
          thisPointer.prev().removeClass('d-none');
          thisPointer.removeClass('active');
    }
    if($(this).val() == fieldvalue){
      return false;
    }
    if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

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

    //Discount
     if($(this).attr('name') == 'discount')
    {
      var total = $('#sub_total').val();
      // alert(total);
      // return;
     
      if($(this).val() !== '' && $(this).hasClass('active'))
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
          url: "{{ route('save-draft-po-product-discount') }}",
          dataType: 'json',
          data: 'rowId='+rowId+'&'+'draft_po_id='+draft_po_id+'&'+attr_name+'='+$(this).val()+'&'+'total='+total,
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
                toastr.success('Success!', 'Discount Updated Successfully.',{"positionClass": "toast-bottom-right"});
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

    // unit gross weight 
    if($(this).attr('name') == 'pod_gross_weight')
    {
      if($(this).val() == null)
      {
        $(this).addClass('d-none');
        $(this).removeClass('active');
        $(this).prev().removeClass('d-none');
        $(this).prev().html('--');
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
          url: "{{ route('update-draft-po-unit-gross-weight') }}",
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
                toastr.success('Success!', 'Gross Weight Updated Successfully.',{"positionClass": "toast-bottom-right"});
                $('.table-ordered-products').DataTable().ajax.reload();
            }
          }   
        });
      }
    }

   }

  });

  $(document).on('click', '.download-documents', function(e){

    let sid = $(this).data('id');
    // alert(sid);
    // console.log(sid);
     $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
  });
    $.ajax({
      type: "post",
      url: "{{ route('get-draft-order-files') }}",
      data: 'order_id='+sid,
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

  $(document).on('click', '.deleteFileIcon', function(e){
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
                url:"{{ route('remove-draft-order-file') }}",
                beforeSend:function(){
                },
                success:function(data){
                    if(data.search('done') !== -1){
                      myArray = new Array();
                      myArray = data.split('-SEPARATOR-');
                      let i_id = myArray[1];
                      $('#quotation-file-'+i_id).remove();
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

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27){ // esc

      $("#transfer_date").datepicker('hide');
      $("#target_receive_date").datepicker('hide');
      if($('.inputDoubleClick').hasClass('d-none'))
      {
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none'); 
      }
    }
  });
</script>
@stop