@extends('backend.layouts.layout')

@section('title','Invoice Setting Management | Supply Chain')

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

</style>

<div class="row">
  <div class="col-md-12">
    <a href="{{ url()->previous() }}" class="float-left pt-3">
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>
    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4)
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
          <li class="breadcrumb-item active">Invoice Settings</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}


<div class="col-lg-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1">
    
    @if($invoice_setting)
      @if($invoice_setting->logo != Null )
      <div class="logo-container">
        <img src="{{asset('public/uploads/logo/'.$invoice_setting->logo)}}" alt="Avatar" class="image">
      </div>
      @else
      <div class="logo-container">
        <img src="{{asset('public/uploads/logo/temp-logo.png')}}" alt="Avatar" class="image">  
      </div>
      @endif
    @else
      <div class="logo-container">
        <img src="{{asset('public/uploads/logo/temp-logo.png')}}" alt="Avatar" class="image">  
      </div>
    @endif

  </div>

  <div class="col-lg-9 p-0">
    <h4 class="mb-0 mt-4">Invoice Setting</h4>
  </div>
  
  <div class="col-lg-2 p-0">
    <div class="mb-0">
      <a href="javascript:void(0);" data-id="{{@$invoice_setting->id}}"  class="btn button-st btn-wd edit-icon">Edit </a> 
    </div>
  </div>

</div>

</div>

<div class="row mb-3">
  <div class="col-lg-5">
    <div class="bg-white pt-3 pl-2 h-100">
      <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
        <tbody>

          <tr>
            <td class="fontbold">{{$global_terminologies['company_name']}}<b style="color: red;">*</b></td>
            <td> 
              {{$invoice_setting != NULL ? $invoice_setting->company_name : 'N.A'}}
            </td>
          </tr>

          <tr>
            <td class="fontbold">Email </td>
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->billing_email : 'N.A'}}
                
            </td>
          </tr>

          <tr>
            <td class="fontbold">Phone<b style="color: red;">*</b></td>
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->billing_phone : 'N.A'}}
            </td>
          </tr>

          

          <tr>
            <td class="fontbold text-nowrap">Fax<b style="color: red;">*</b></td>
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->billing_fax : 'N.A'}}
            </td>
          </tr>

           <tr>
            <td class="fontbold text-nowrap">Address<b style="color: red;">*</b></td>
            
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->billing_address : 'N.A'}}
            </td>
          </tr>

          <tr>
            <td class="fontbold text-nowrap">Zip<b style="color: red;">*</b></td>
            <td class="text-nowrap"> 
              {{$invoice_setting != NULL ? $invoice_setting->billing_zip : 'N.A'}}
            </td>
          </tr>

          <tr>
            <td class="fontbold text-nowrap">Country<b style="color: red;">*</b></td>
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->country->name : 'N.A'}}
            </td>
          </tr>

          <tr>
            <td class="fontbold text-nowrap">@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif<b style="color: red;">*</b></td>
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->state->name : 'N.A'}}
            </td>
          </tr>

          <tr>
            <td class="fontbold text-nowrap">City<b style="color: red;">*</b></td>
            <td class="text-nowrap">
              {{$invoice_setting != NULL ? $invoice_setting->billing_city : 'N.A'}}
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>
</div>



<!-- Configurations Edit Modal Start Here -->
<div class="modal" id="editCustomerModal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center" id="editCustomerModalForm">

      </div>
    </div>
  </div>
</div>
<!-- Configurations Edit Modal End Here -->

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
  $(function(e){

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
        timepicker:false,
        format:'Y-m-d'});
      });
    $('.table-configuration').DataTable({
      processing: true,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      ajax: "{!! route('get-configuration') !!}",
      columns: [
        { data: 'action', name: 'action' },
        { data: 'company_name', name: 'name' },
        { data: 'image', name: 'logo' },
        { data: 'email_notification', name: 'email_notification' },
        { data: 'currency_code', name: 'currency_code' },
        { data: 'currency_symbol', name: 'currency_symbol' },
        { data: 'quotation_prefix', name: 'quotation_prefix' },
        { data: 'draft_invoice_prefix', name: 'draft_invoice_prefix' },
        { data: 'invoice_prefix', name: 'invoice_prefix' },
        { data: 'system_email', name: 'system_email' },
        { data: 'created_at', name: 'created_at' },
        { data: 'updated_at', name: 'updated_at' }
      ]
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    $(document).on('click', '.edit-icon', function() {
      var id = $(this).data('id');
      if(id == '')
      {
        url_link = "{{ route('add-invoice') }}";
        p_method = "post";
      }
      else
      {
        url_link = "{{ route('edit-invoice') }}";
        p_method = "get";
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: p_method,
        data: {id:id},
        url: url_link,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
          $('#loader_modal').modal('hide');
          $('#editCustomerModalForm').html(data);
          $('#editCustomerModal').modal();
        },
        error: function(){
          $('#loader_modal').modal('hide');
        }
      });
    });

    $(document).on('submit', '.add-invoice-settings', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('add-new-invoice-setting') }}",
        method: 'post',
        data: new FormData(this), 
        contentType: false,       
        cache: false,             
        processData:false,
        beforeSend: function(){
          $('.add-inv-btn').val('Please wait...');
          $('.add-inv-btn').addClass('disabled');
          $('.add-inv-btn').attr('disabled', true);
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('#loader_modal').modal('hide');
          $('.add-inv-btn').val('ADD');
          $('.add-inv-btn').removeClass('disabled');
          $('.add-inv-btn').removeAttr('disabled');
          if(result.success === true)
          {
            $('.modal').modal('hide');
            toastr.success('Success!', 'Invoice Setting Added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-invoice-settings')[0].reset();
            setTimeout(function(){
              window.location.reload();
            }, 500);
          }
        },
        error: function (request, status, error) {
          $('#loader_modal').modal('hide');
          $('.add-inv-btn').val('ADD');
          $('.add-inv-btn').removeClass('disabled');
          $('.add-inv-btn').removeAttr('disabled');
          $('.form-control').removeClass('is-invalid');
          $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('select[name="'+key+'"]').addClass('is-invalid');
            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'"]').addClass('is-invalid');
          });
        }
      });
    });

    $(document).on('submit', '.edit_con_form', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('update-invoice') }}",
        method: 'post',
        data: new FormData(this), 
        contentType: false,       
        cache: false,             
        processData:false,
        beforeSend: function(){
          $('#edit_con_btn').val('Please wait...');
          $('#edit_con_btn').addClass('disabled');
          $('#edit_con_btn').attr('disabled', true);
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('#edit_con_btn').val('add');
          $('#edit_con_btn').removeClass('disabled');
          $('#edit_con_btn').removeAttr('disabled');
          if(result.success === true){
            $('.modal').modal('hide');
            toastr.success('Success!', 'Invoice Setting Updated successfully',{"positionClass": "toast-bottom-right"});
            $('.edit_con_form')[0].reset();
            setTimeout(function(){
                window.location.reload();
            }, 2000);
          }
          else
          {
            $('#loader_modal').modal('hide');
          }
        },
        error: function (request, status, error) {
          $('#loader_modal').modal('hide');
          $('#edit_con_btn').val('update');
          $('#edit_con_btn').removeClass('disabled');
          $('#edit_con_btn').removeAttr('disabled');
          $('.form-control').removeClass('is-invalid');
          $('.form-control').next().remove();
          json = $.parseJSON(request.responseText);
          $.each(json.errors, function(key, value){
            $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('select[name="'+key+'"]').addClass('is-invalid');
            $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
            $('input[name="'+key+'"]').addClass('is-invalid');
          });
        }
      });
    });

    $(document).on("dblclick",".inputDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
    });

    $(document).on('keyup', function(e) {
      if (e.keyCode === 27)
      {
        if($('.selectDoubleClick').hasClass('d-none'))
        {
          $('.selectDoubleClick').removeClass('d-none');
          $('.selectDoubleClick').next().addClass('d-none'); 
        }
        if($('.inputDoubleClick').hasClass('d-none'))
        {
          $('.inputDoubleClick').removeClass('d-none');
          $('.inputDoubleClick').next().addClass('d-none'); 
        }
      }
    });

    $(document).on("focusout",".invoice-fieldFocus",function() { 
      if($(this).val().length < 1)
      {
        // swal("Must fill this filed accurately!");
        return false;
      }
      else
      {
        var fieldvalue = $(this).prev().data('fieldvalue');
        var new_value = $(this).val();
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).prev().html(new_value);
          }
          saveInvoiceData(thisPointer,thisPointer.attr('name'), thisPointer.val());
        }
      }
    });

    function saveInvoiceData(thisPointer,field_name,field_value){
      var invoice_setting_id= "{{@$invoice_setting->id}}";
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        // url: "{{ url('save-supp-data-supp-detail-page') }}",
        url: "{{ url('invoice-setting-update') }}",

        dataType: 'json',
        // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
        data: 'invoice_setting_id='+invoice_setting_id+'&'+field_name+'='+field_value,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.success == true)
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            return true;
          }
        },
      });
    }

    $(document).on("change",".billing_country",function(){
      if($(this).val != '')
      {
        var country_id = $(this).val();
        $.ajax({
          url: "{{ route('fetch-states') }}",
          method: "get",
          data: { country_id:country_id},
          beforeSend: function(){
            $('#load_opt').html('<i class="fa fa-spinner"></i>');
            $('.billing_state').attr('disabled',true);
          },
          success:function(result)
          {
            $('#load_opt').html('');
            $('.billing_state').attr('disabled',false);
            $('#billing_state').html(result);
          }
        });
      }
    });
    
  });
</script>
@stop

