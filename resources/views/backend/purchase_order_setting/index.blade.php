@extends('backend.layouts.layout')

@section('title','Purchase Order Setting Management | Supply Chain')

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

{{-- Content Start from here --}}


<div class="col-lg-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1">
    @if(@$po_setting->logo != Null )
    <div class="logo-container">
    <img src="{{asset('public/uploads/logo/'.$po_setting->logo)}}" style="border-radius:60%;height:78px;" alt="logo" class="img-fluid lg-logo">
    
    </div>
      @else
      <div class="logo-container">
      <img src="{{asset('public/uploads/logo/temp-logo.png')}}" alt="Avatar" class="image">
      
    </div>
    @endif
</div>
<div class="col-lg-9 p-0">
    <h4 class="mb-0 mt-4">Purchase Order Setting</h4>
  </div>
  <div class="col-lg-2 p-0">
    <div class="mb-0">
        <a href="javascript:void(0);" data-id="{{@$po_setting->id}}"  class="btn button-st edit-icon">Edit </a> 
    </div>
  </div>
</div>

</div>

<div class="row mb-3">
<!-- left Side Start -->




<div class="col-lg-5">
  <div class="bg-white pt-3 pl-2 h-100">
    <table id="example" class="table table-responsive headings-color sales-customer-table dataTable const-font" style="width: 100%;">
      <tbody>
        <tr>
          <td class="fontbold">{{$global_terminologies['company_name']}}<b style="color: red;">*</b></td>
          <td> 
            {{@$po_setting->company_name}}
              
          </td>
        </tr>

        <tr>
          <td class="fontbold">Email </td>
          <td class="text-nowrap">
            {{@$po_setting->billing_email}}
              
          </td>
        </tr>

        <tr>
          <td class="fontbold">Phone<b style="color: red;">*</b></td>
          <td class="text-nowrap">
            {{@$po_setting->billing_phone}}
              
          </td>
        </tr>

        

        <tr>
          <td class="fontbold text-nowrap">Fax<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
            {{@$po_setting->billing_fax}}
              
          </td>
        </tr>

         <tr>
          <td class="fontbold text-nowrap">Address<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
            {{@$po_setting->billing_address}}
              
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Zip<b style="color: red;">*</b></td>
          <td class="text-nowrap"> 
            {{@$po_setting->billing_zip}}
              
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Country<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
            {{@$po_setting->country->name}}
                              
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
            {{@$po_setting->state->name}}
                            
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">City<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
            {{@$po_setting->billing_city}}
              
          </td>
        </tr>

        <!-- <tr>
          <td class="fontbold text-nowrap">Created By<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
            {{@$po_setting->user->name}}
                            
          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Created At<b style="color: red;">*</b></td>
          
          <td class="text-nowrap">
              {{@$po_setting->created_at}}

          </td>
        </tr>

        <tr>
          <td class="fontbold text-nowrap">Updated At<b style="color: red;">*</b></td>
          <td class="text-nowrap"> 
              {{@$po_setting->updated_at}}
            
          </td>
        </tr> -->


      </tbody>
    </table>
  </div>
  
</div>



</div>



<!--  Configurations Edit Modal Start Here -->

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
      // console.log('hello');
          var id = $(this).data('id');
          $.ajax({
             method: "get",
              data:{id:id},
              url:"{{ route('edit-po-setting') }}",
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
              }
          });
      });

    $(document).on('submit', '.edit_con_form', function(e){
          e.preventDefault();
          
            // alert(e_notification);
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
          });
          $.ajax({
              url: "{{ route('update-po-setting') }}",
              method: 'post',
              data: new FormData(this), 
              contentType: false,       
              cache: false,             
              processData:false,
              beforeSend: function(){
                  $('#edit_con_btn').val('Please wait...');
                  $('#edit_con_btn').addClass('disabled');
                  $('#edit_con_btn').attr('disabled', true);
              },
              success: function(result){
                  $('#edit_con_btn').val('add');
                  $('#edit_con_btn').removeClass('disabled');
                  $('#edit_con_btn').removeAttr('disabled');
                  if(result.success === true){
                      $('.modal').modal('hide');
                      toastr.success('Success!', 'Configurations Updated successfully',{"positionClass": "toast-bottom-right"});
                      $('.edit_con_form')[0].reset();
                      setTimeout(function(){
                          window.location.reload();
                      }, 2000);

                  }


              },
              error: function (request, status, error) {
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
      // alert('here');
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
      });

      $(document).on('keyup', function(e) {
        if (e.keyCode === 27){ // esc
          if($('.selectDoubleClick').hasClass('d-none')){
            $('.selectDoubleClick').removeClass('d-none');
            $('.selectDoubleClick').next().addClass('d-none'); 
          }
          if($('.inputDoubleClick').hasClass('d-none')){
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
            // alert(fieldvalue);
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
              // saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val());
            saveInvoiceData(thisPointer,thisPointer.attr('name'), thisPointer.val());

            }
          }
          
      });

      function saveInvoiceData(thisPointer,field_name,field_value){
          console.log(thisPointer+' '+' '+field_name+' '+field_value);
          var po_setting_id= "{{@$po_setting->id}}";

          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
          });
          $.ajax({
            method: "post",
            // url: "{{ url('save-supp-data-supp-detail-page') }}",
            url: "{{ url('purchase-order-setting-update') }}",

            dataType: 'json',
            // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
            data: 'po_setting_id='+po_setting_id+'&'+field_name+'='+field_value,
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
          // var country_id = $(this).val();

          // alert(country_id);
            if($(this).val != '')
            {
                
                var country_id = $(this).val();

                $.ajax({
                    url: "{{ route('fetch-states') }}",
                    method: "get",
                    data: { country_id:country_id},
                    success:function(result)
                    {
                        $('#billing_state').html(result);
                        //alert(result);
                    }
                })

            }
        });


    
  });
</script>
@stop

