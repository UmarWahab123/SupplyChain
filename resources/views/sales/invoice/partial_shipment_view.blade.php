@extends('sales.layouts.layout')

@section('title','Invoice Products | Sales')

@section('content')

<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">Partial Shipment Order Process</h3>
  </div>
</div>

<div class="row mb-3 justify-content-center">
  <div class="col-lg-12 col-md-12 col-12 signform-col ">
    <div class="row add-gemstone">
      <div class="col-md-12">
        <div class="bg-white pr-4 pl-4 pt-4 pb-5">
          <h4>Please wait system is generating a Draft Invoice (Through Partial Shipment)...</h4>
          <p>You will be redirected to a Draft Invoice once generated.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loader Modal -->
<div class="modal" id="loader_modal" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait...</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>
    </div>
  </div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
    var order_id = "{{$order_id}}";
    $.ajax({
      url: "{{ url('partial-shipment-order') }}"+'/'+order_id,
      method: 'get',
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      success: function(result){
        toastr.success('Success!', 'Draft invoice created successfully.' ,{"positionClass": "toast-bottom-right"});
        if(result.success == true)
        {
          setTimeout(function(){
            window.location.href = "{{ url('sales/get-completed-draft-invoices') }}"+"/"+result.order_id;
          }, 300);
        }
        else
        {
          $("#loader_modal").modal('hide');
        }
      },
      error: function (request, status, error) {
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
</script>
@stop