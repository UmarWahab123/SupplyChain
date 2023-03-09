@extends('backend.layouts.layout')

@section('title','Currency | Supply Chain')

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
          <li class="breadcrumb-item active">Currencies</li>
      </ol>
  </div>
</div>

<!-- prod-cat = Product-category -->
{{-- Content Start from here --}}
{{-- <div class="row mb-0">
  <div class="col-md-12 mb-2 title-col">
    <div class="align-items-center d-sm-flex justify-content-between">
      <h3 class="maintitle text-uppercase fontbold">Currencies</h3>
      <div class="d-flex mb-0">
        <a class="btn button-st update_prices_on_product_level btn-title" href="javascript:void(0);">Update On Product Level</a>
        <a class="btn button-st update_exchange_rates btn-title" href="javascript:void(0);">Update Exchange Rates</a>
        <a class="btn button-st btn-title" href data-toggle="modal" id="addCurrency">ADD Currency</a>
      </div>
    </div>
  </div>
</div> --}}

<div class="row align-items-center mb-3">
  <div class="col-md-4 title-col">
    <h4 class="maintitle">Currencies</h4>
  </div>
  <div class="col-md-8 text-right title-right-col">
    <div class="d-flex mb-0 justify-content-end">
     {{--  <a class="btn button-st update_prices_on_product_level btn-title btn-wd" href="javascript:void(0);">Update On Product Level</a> --}}
     <a class="btn button-st update_exchange_rates btn-title btn-wd" href="javascript:void(0);">Update Exchange Rates</a>
     <a class="btn button-st btn-title btn-wd" href data-toggle="modal" id="addCurrency">ADD Currency</a>
   </div>
 </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="alert alert-primary export-alert d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
      <b> Currency is updating on product level! Please wait.. </b>
    </div>
    <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
      <i class="  fa fa-spinner fa-spin"></i>
      <b> Currency is already updating on product level by another user! Please wait.. </b>
    </div>
    <div class="alert alert-success export-alert-success d-none"  role="alert">
      <i class=" fa fa-check "></i>
      <b>Currency is updated on product level Successfully.</b>
    </div>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
        <table class="table entriestable table-bordered table-currency text-center">
          <thead>
            <tr>
              <!-- <th>Action</th> -->
              <th>Currency Name</th>
              <th>Currency Code</th>
              <th>Currency Symbol</th>
              <th>Conversion Rate</th>
              <th>Conversion Rate CUR=>THB</th>
              <th>Last Updated Date</th>
              <th>Last Update By</th>
              <th>Update On Product Level</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Currency History Table -->
@if(Auth::user()->role_id == 1 || Auth::user()->role_id == 10 || Auth::user()->role_id == 8)
<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="row mt-4">
    <div class="col-lg-10 col-md-9">
      <h4>Currency History</h4>
      </div>
    </div>
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
         <table class="table-currency-history entriestable table table-bordered text-center table-theme-header" style="width: 98%;font-size: 12px;overflow: hidden;">
          <thead>
            <tr>
              <th>User  </th>
              <th>Date/time </th>
              <th>Column</th>
              <th>Old Value</th>
              <th>New Value</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
@endif


<!--  Content End Here -->

<!--  Payment Type Modal Start Here -->
<div class="modal fade" id="addCurrency">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Add Currency</h3>
        <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-currency-form']) !!}
          <div class="form-group">
            {!! Form::text('currency_name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Currency Name']) !!}
          </div>

          <div class="form-group">
            {!! Form::text('currency_code', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Currency Code']) !!}
          </div>

          <div class="form-group">
            {!! Form::text('currency_symbol', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Currency Symbol']) !!}
          </div>

          <div class="form-group">
            {!! Form::text('conversion_rate', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Conversion Rate']) !!}
          </div>

          <div class="form-submit">
            <input type="submit" value="add" class="btn btn-bg save-btn">
            <input type="reset" value="close" class="btn btn-danger close-btn">
          </div>
          {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
</div>
<!-- add Payment Term Modal End Here -->

<!-- Edit modal -->
<div class="modal fade" id="editCurrencyModal">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Edit Currency</h3>
        <div class="mt-5">
          <form method="POST" action="{{route('edit-currency')}}" class="edit-currency-form">
            {{ csrf_field() }}
            <div class="form-group mb-4 pb-1">
              <input type="text" name="currency_name" class="font-weight-bold form-control-lg form-control currency_name" placeholder="Enter Currency Name" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="currency_code" class="font-weight-bold form-control-lg form-control currency_code" placeholder="Enter Currency Code" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="currency_symbol" class="font-weight-bold form-control-lg form-control currency_symbol" placeholder="Enter Currency Symbol" required="true">
            </div>

            <div class="form-group mb-4 pb-1">
              <input type="text" name="conversion_rate" class="font-weight-bold form-control-lg form-control conversion_rate" placeholder="Enter Conversion Rate" required="true">
            </div>

            <div class="form-submit">
              <input type="hidden" name="editid">
              <input type="submit" value="Update" class="btn btn-bg save-btn">
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Edit modal End -->

<!-- Loader Modal -->
<div class="modal" id="loader_modal2" role="dialog">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
        <div id="msg"></div>
      </div>
    </div>
  </div>
</div>

<div id="progressModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Processing</h4>
      </div>
      <div class="modal-body">
        Please Wait while the process is running.
        <ul>
          <li>Do Not perform any actions while the process is running.</li>
          <li>Do Not close this tab/window while the process is running.</li>
          <li>Do Not Refresh / Reload the page while the process is running.</li>
        </ul>
        <div class="progress">
          <div class="progress-p" id="update-p" style="left: 49%; position: absolute; color: black; font-weight: bold;">2 %</div>
          <div class="progress-bar progress-bar-striped progress-bar-animated" id="update-b" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 2%"></div>
        </div>
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
        format:'Y-m-d'
      });
    });

    var table2 = $('.table-currency').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      serverSide: true,
      pageLength: {{100}},
      lengthMenu: [ 100, 200, 300, 400],
      ajax: {
        beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
              });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
          },
        url:"{!! route('get-currency') !!}",
      },
      columns: [
        // { data: 'action', name: 'action' },
        { data: 'currency_name', name: 'currency_name' },
        { data: 'currency_code', name: 'currency_code' },
        { data: 'currency_symbol', name: 'currency_symbol' },
        { data: 'conversion_rate', name: 'conversion_rate' },
        { data: 'conversion_rate_2', name: 'conversion_rate_2' },
        { data: 'last_updated_date', name: 'last_updated_date' },
        { data: 'last_update_by', name: 'last_update_by' },
        { data: 'update_on_product_level', name: 'update_on_product_level' }

      ],
      drawCallback: function(){
      $('#loader_modal').modal('hide');
    },
    });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13)
      {
        table2.search($(this).val()).draw();
      }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    $(document).on('click','#addCurrency',function(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('add-currency') }}",
        method: 'post',
        data: $('.add-currency-form').serialize(),
        beforeSend: function(){
          $('#loader_modal2').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal2').modal('show');
        },
        success: function(result){
          if(result.success === true){
            $('#loader_modal2').modal('hide');
            toastr.success('Success!', 'Currency added successfully',{"positionClass": "toast-bottom-right"});
            $('.table-currency').DataTable().ajax.reload();
            $('.table-currency-history ').DataTable().ajax.reload();

          }
        },
        error: function (request, status, error) {
          $('.save-btn').val('add');
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
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

    $(document).on('submit', '.edit-currency-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('edit-currency') }}",
        method: 'post',
        data: $('.edit-currency-form').serialize(),
        beforeSend: function(){
          $('.save-btn').val('Please wait...');
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
        },
        success: function(result){
          $('.save-btn').val('add');
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          if(result.success === true)
          {
            $('.modal').modal('hide');
            toastr.success('Success!', 'Currency added successfully',{"positionClass": "toast-bottom-right"});
            $('.edit-currency-form')[0].reset();
            setTimeout(function(){
              window.location.reload();
            }, 2000);
          }


        },
        error: function (request, status, error) {
          $('.save-btn').val('add');
          $('.save-btn').removeClass('disabled');
          $('.save-btn').removeAttr('disabled');
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

    $(document).on('click', '.edit-icon',function(e){
      var sId = $(this).parents('tr').attr('id');
      var oldName = $(this).parents('td').next().text();
      var oldCode = $(this).parents('td').next().next().text();
      var oldSymbol = $(this).parents('td').next().next().next().text();
      var oldConversion = $(this).parents('td').next().next().next().next().text();
      $('.currency_name').val(oldName);
      $('.currency_code').val(oldCode);
      $('input[name=editid]').val(sId);
      $('.currency_symbol').val(oldSymbol);
      $('.conversion_rate').val(oldConversion);
      $('#editCurrencyModal').modal('show');
    });

    $(document).on('click', '.update_exchange_rates', function(){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      swal({
        title: "Alert!",
        text: "Are you sure you want to Update Exchange Rates?",
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
          method:"post",
          dataType:"json",
          url: "{{ route('update-currency-exchange-rates') }}",
          beforeSend:function(){
            $('#loader_modal2').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal2").modal('show');
          },
          success:function(data){
            $("#loader_modal2").modal('hide');
            if(data.success == true){
              toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
              $('.table-currency').DataTable().ajax.reload();
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

  // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $x = $(this);
    $(this).addClass('d-none');
    $(this).after('<span class="spinner"><i class="fa fa-spinner"></i></span>');

    setTimeout(function(){

      $('.spinner').remove();
      $x.next().removeClass('d-none');
      $x.next().addClass('active');
      $x.next().focus();
      var num = $x.next().val();
      $x.next().focus().val('').val(num);
      $x.next().next('span').removeClass('d-none');
      $x.next().next('span').addClass('active');

    }, 300);
  });

  $(document).on('keypress keyup focusout', '.fieldFocus', function(e){

    var id = $(this).prev().data('id');
    var old_value = $(this).prev().data('fieldvalue');
    if($(this).attr('name') == 'currency_name'){
      var fieldvalue = $('.currency-name-'+id).text();
    }else if($(this).attr('name') === 'currency_code'){
      var fieldvalue = $('.currency-code-'+id).text();
    }else if($(this).attr('name') == 'currency_symbol'){
      var fieldvalue = $('.currency-symbol-'+id).text();
    }else if($(this).attr('name') == 'conversion_rate'){
      var fieldvalue = $('.conversion-rate-'+id).text();
    }

    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.prev().removeClass('d-none');
      thisPointer.removeClass('active');
    }

    else if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){
      var pId = $(this).parents('tr').attr('id');
      var new_value = $(this).val();

      if($(this).val() !== '' && $(this).val() !== '0' && $(this).hasClass('active'))
      {
        if(fieldvalue == new_value)
        {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');

        }
        else
        {
          $(this).removeClass('active');
          $(this).addClass('d-none');
          $(this).prev().removeClass('d-none');
          $(this).prev().html(new_value);
          $(this).prev().css("color", "");
          saveCurrencyData(pId, $(this).attr('name'), $(this).val(), old_value);
        }
      }
    }


  });

  function saveCurrencyData(currency_det_id,field_name,field_value,old_value)
  {
    console.log(field_name+' '+field_value+''+currency_det_id);
      // return false;
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ route('save-currency-data') }}",
        dataType: 'json',
        data: 'currency_det_id='+currency_det_id+'&'+field_name+'='+field_value+'&'+'old_value='+old_value,
        beforeSend: function(){
          // shahsky here
        },
        success: function(data)
        {
          if(data.completed == 1)
          {
            toastr.success('Success!', 'Currency marked as completed' ,{"positionClass": "toast-bottom-right"});
          }
          else
          {
            toastr.success('Success!', 'Information Updated Successfully' ,{"positionClass": "toast-bottom-right"});
            // toastr.error('Error!', data.errormsg ,{"positionClass": "toast-bottom-right"});
          }

          $('.table-currency').DataTable().ajax.reload();
          $('.table-currency-history').DataTable().ajax.reload();
        },

      });
    }

  // $(document).on('click', '.update_prices_on_product_level', function(e)
  // {
  //   var currency_id = $(this).data('id');
  //    swal({
  //     title: "Alert!",
  //     text: "Are you sure you want to update prices on Product level?",
  //     type: "info",
  //     showCancelButton: true,
  //     confirmButtonClass: "btn-danger",
  //     confirmButtonText: "Yes!",
  //     cancelButtonText: "No!",
  //     closeOnConfirm: true,
  //     closeOnCancel: false
  //   },
  //   function(isConfirm) {
  //   if (isConfirm)
  //   {
  //     $('#updateModal').modal('toggle');
  //     $('#progressModal').modal({backdrop: 'static', keyboard: false});
  //     $('#progressModal').modal('toggle');
  //     update_currency(currency_id);
  //   }
  //   else
  //   {
  //     swal("Cancelled", "", "error");
  //   }
  //   });
  // });

  // function update_currency(currency_id)
  // {

  //   $.ajaxSetup({
  //     headers: {
  //       'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
  //     }
  //   });
  //   var timeout;

  //   $.ajax({
  //     url: "{{ route('update-prices-on-product-level') }}",
  //     method: 'post',
  //     dataType: 'json',
  //     data:{currency_id:currency_id},
  //     cache: false,
  //     beforeSend: function()
  //     {

  //     },
  //     success: function(result)
  //     {
  //       if(result.percent)
  //       {
  //         $('#update-b').css('width',result.percent+'%');
  //         $('#update-p').html(result.percent+' %');

  //         setTimeout(function(e){
  //           update_currency();
  //         }, 10000);
  //       }

  //       if(result.success == true)
  //       {
  //         $('#update-b').css('width','100%');
  //         $('#update-p').html('100 %');
  //         setTimeout(function(e){
  //           $('.modal').modal('hide');
  //           toastr.success('Success!', 'Products Prices Updated Successfully',{"positionClass": "toast-bottom-right"});
  //           $('#update-b').css('width','2%');
  //           $('#update-p').html('2 %');
  //         }, 1000);
  //       }
  //     },
  //     error: function (request, status, error)
  //     {
  //       $('.modal').modal('hide');
  //       swal('Something went wrong !');
  //       // toastr.error('Error!', 'Something went wrong!!!',{"positionClass": "toast-bottom-right"});
  //     }
  //   });


  // }

  /*New code for update rates by a JOB*/
  $(document).on('click','.update_prices_on_product_level',function(){

    var currency_id = $(this).data('id');

    swal({
      title: "Alert!",
      text: "Are you sure you want to update prices on Product level?",
      type: "info",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "Yes!",
      cancelButtonText: "No!",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function(isConfirm) {
      if (isConfirm)
      {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          method:"get",
          url:"{{route('currency-update-job-status')}}",
          data:{currency_id:currency_id},
          beforeSend:function(){

          },
          success:function(data){
            if(data.status == 1)
            {
              $('.export-alert-success').addClass('d-none');
              $('.export-alert').removeClass('d-none');
              $('.update_prices_on_product_level').css('pointer-events', 'none');
              checkStatusForCurrencyUpdate();
            }
            else if(data.status == 2)
            {
              $('.export-alert-another-user').removeClass('d-none');
              $('.export-alert').addClass('d-none');
              $('.update_prices_on_product_level').css('pointer-events', 'none');
              checkStatusForCurrencyUpdate();
            }
          },
          error: function(request, status, error){
            toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          }
        });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
    });
  });

  $(document).ready(function(){
    $.ajax({
      method:"get",
      url:"{{route('check-status-for-first-time-currencies-update')}}",
      success:function(data)
      {
        if(data.status == 0 || data.status == 2)
        {
          // Do Nothing For Now
        }
        else
        {
          $('.export-alert').removeClass('d-none');
          $('.update_prices_on_product_level').css('pointer-events', 'none');
          checkStatusForCurrencyUpdate();
        }
      }
    });
  });

  function checkStatusForCurrencyUpdate()
  {
    $.ajax({
      method:"get",
      url:"{{route('recursive-job-status-currency-update')}}",
      success:function(data){
        if(data.status==1)
        {
          setTimeout(
            function(){
              checkStatusForCurrencyUpdate();
            }, 5000);
        }
        else if(data.status == 0)
        {
          $('.export-alert-success').removeClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.update_prices_on_product_level').css('pointer-events', '');
          $('.export-alert-another-user').addClass('d-none');
          $('.table-currency').DataTable().ajax.reload();
        }
        else if(data.status == 2)
        {
          $('.export-alert-success').addClass('d-none');
          $('.export-alert').addClass('d-none');
          $('.update_prices_on_product_level').css('pointer-events', '');
          $('.export-alert-another-user').addClass('d-none');
          toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          $('.table-currency').DataTable().ajax.reload();

        }
      }
    });
  }

// Currency History
$('.table-currency-history').DataTable({
  "sPaginationType": "listbox",
  processing: false,
//   "language": {
//     processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    searching:false,
    serverSide: true,
    "bPaginate": true,
    "bInfo":false,
    lengthMenu: [ 5, 10, 20, 40],
    ajax: {
      url:"{!! route('get-currency-history') !!}",
    },
    columns: [
            // { data: 'checkbox', name: 'checkbox' },
            { data: 'user_name', name: 'user_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'column_name', name: 'column_name' },
            { data: 'old_value', name: 'old_value' },
            { data: 'new_value', name: 'new_value' },

            ]
          });

        </script>
        @stop

