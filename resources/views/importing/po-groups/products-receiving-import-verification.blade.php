@extends('importing.layouts.layout')
@section('title','Products Recieving')

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
          <li class="breadcrumb-item"><a href="{{route('importing-receiving-queue')}}">Product Receiving Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{route('importing-receiving-queue-detail',['id' => $group->id])}}">Group # {{@$group->ref_id}}</a></li>
          <li class="breadcrumb-item active">Bulk Import Changes Verfication</li>
      </ol>
  </div>
</div>

<div class="right-content pt-0">
  <div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="row">
        <div class="col-12">
          <h3>Uploaded Excel Preview</h3>
        </div>
        <div class="col-12">
          <h5><span class="text-danger" style="font-style: italic;">Note :</span> Please verify the changes done in the excel file. If all good click on confirm button to update shipment.</h5>
        </div>
      </div>
    <div class="row">
      <div class="col text-center">
        <span style="background-color: #fff3cd;padding: 1px 20px;margin-right: 5px;"><span style="visibility: hidden;"> a </span></span> Updated Row
        <span style="background-color: #ebd283;padding: 1px 20px;margin-left: 30px;margin-right: 5px;"><span style="visibility: hidden;"> a </span></span> Updated Value
      </div>
    </div>
    <div class="table-responsive" id="sticky-anchor">
      {{-- bulk import --}}
        <div class="col-lg-12 col-md-12 mt-4">
          <div class="alert alert-primary export-alert-bulk d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> Data is Updating Please wait.. </b>
          </div>
          <div class="alert alert-success export-alert-success-bulk d-none"  role="alert">
            <i class=" fa fa-check "></i>
            <b>Data Updated Successfully !!!</b>
          </div>
          <div class="alert alert-primary export-alert-another-user-bulk d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b> Data is updating by another user! Please wait.. </b>
          </div>
        </div>
      {{-- ends here --}}

      <table class="table entriestable table-bordered table-import-preview text-center">
        <thead id="sticky">
          <tr>
            <th>PF#</th>
            <th>Group#</th>
            <th>PO#</th>
            <th>{{$global_terminologies['purchasing_price']}}<br> EUR (W/O Vat) <br> (Old Value)</th>
            <th>{{$global_terminologies['purchasing_price']}}<br> EUR (W/O Vat)<br> (New Value)</th>
            <th>Discount <br> (Old value)</th>
            <th>Discount <br> (New Value)</th>
            <th>{{$global_terminologies['qty']}} Inv  <br> (Old value)</th>
            <th>{{$global_terminologies['qty']}} Inv   <br> (New Value)</th>
            <th>{{$global_terminologies['gross_weight']}} <br> (Old value)</th>
            <th>{{$global_terminologies['gross_weight']}} <br> (New Value)</th>
            <th>Total <br>{{$global_terminologies['gross_weight']}} <br> (Old value)</th>
            <th>Total <br>{{$global_terminologies['gross_weight']}} <br> (New Value)</th>
            <th>Extra Cost (THB) <br> (Old value)</th>
            <th>Extra Cost (THB) <br> (New Value)</th>
            <th>Total Extra Cost (THB) <br> (Old value)</th>
            <th>Total Extra Cost (THB) <br> (New Value)</th>
            <th>{{$global_terminologies['extra_tax_per_billed_unit']}} <br> (Old value)</th>
            <th>{{$global_terminologies['extra_tax_per_billed_unit']}} <br> (New Value)</th>
            <th>Total {{$global_terminologies['extra_tax_per_billed_unit']}} <br> (Old value)</th>
            <th>Total {{$global_terminologies['extra_tax_per_billed_unit']}} <br> (New Value)</th>
            <th>Currency <br>Conversion Rate <br> (Old value)</th>
            <th>Currency <br>Conversion Rate <br> (New Value)</th>
          </tr>
        </thead>

      </table>
    </div>
    <div class="row text-right">
      <div class="col mt-3">
        <button type="button" data-id="{{$id}}" class="btn-color btn float-right confirm-to-stock-btn confirm_group_import">Confirm</button>
      </div>
    </div>
    </div>

  </div>
</div>
</div>

@endsection
  @php
    $hidden_by_default = '';
  @endphp
@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
    var table2 = $('.table-import-preview').DataTable({
    "sPaginationType": "listbox",
      processing: false,
      "language": {
      processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      lengthMenu:[100,200,300,400],
      serverSide: true,
      ajax:{
      beforeSend: function(){
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
        url : "{{ route('get-group-import-data') }}",
        data: function (data) {
          data.id = "{{$id}}"
        },
        method: "post",
      },
      scrollX:true,
      searching: false,
      scrollY : '90vh',
      scrollCollapse: true,

      columns: [
        { data: 'pf', name: 'pf' },
        { data: 'ref_id', name: 'ref_id' },
        { data: 'po', name: 'po' },
        { data: 'unit_price_old', name: 'unit_price_old' },
        { data: 'unit_price', name: 'unit_price' },
        { data: 'discount_old', name: 'discount_old' },
        { data: 'discount', name: 'discount' },
        { data: 'qty_inv_old', name: 'qty_inv_old' },
        { data: 'qty_inv', name: 'qty_inv' },
        { data: 'gross_weight_old', name: 'gross_weight_old' },
        { data: 'gross_weight', name: 'gross_weight' },
        { data: 'total_gross_weight_old', name: 'total_gross_weight_old' },
        { data: 'total_gross_weight', name: 'total_gross_weight' },
        { data: 'extra_cost_old', name: 'extra_cost_old' },
        { data: 'extra_cost', name: 'extra_cost' },
        { data: 'total_extra_cost_old', name: 'total_extra_cost_old' },
        { data: 'total_extra_cost', name: 'total_extra_cost' },
        { data: 'extra_tax_old', name: 'extra_tax_old' },
        { data: 'extra_tax', name: 'extra_tax' },
        { data: 'total_extra_tax_old', name: 'total_extra_tax_old' },
        { data: 'total_extra_tax', name: 'total_extra_tax' },
        { data: 'currency_conversion_rate_old', name: 'currency_conversion_rate_old' },
        { data: 'currency_conversion_rate', name: 'currency_conversion_rate' },
      ],
      initComplete: function () {
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
              $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
      drawCallback: function ( settings ) {
        $('#loader_modal').modal('hide');
      }
    });

    $(document).on('click','.confirm_group_import',function(e){
      var group_id = "{{ $id }}";
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('confirm-group-import-data') }}",
          method: 'get',
          data: {id: group_id},
          beforeSend: function(){
            $('.confirm_group_import').addClass('disabled');
            $('.confirm_group_import').attr('disabled', true);
          },
          success: function(data){
            if(data.status==1)
            {
              $('.export-alert-success-bulk').addClass('d-none');
              $('.export-alert-bulk').removeClass('d-none');
              checkStatusForProductsImport();
            }
            else if(data.status==2)
            {
              $('.export-alert-another-user-bulk').removeClass('d-none');
              $('.export-alert-bulk').addClass('d-none');
              checkStatusForProductsImport();
            }
          },
          error: function (request, status, error) {
                /*$('.form-control').removeClass('is-invalid');
                $('.form-control').next().remove();*/
                $('#loader_modal').modal('hide');
                $('.confirm_group_import').removeClass('disabled');
                $('.confirm_group_import').removeAttr('disabled');
                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                      $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('input[name="'+key+'"]').addClass('is-invalid');
                     $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                     $('textarea[name="'+key+'"]').addClass('is-invalid');


                });
            }
        });
    });
    function checkStatusForProductsImport()
    {
      $.ajax({
        method:"get",
        url:"{{route('recursive-confirm-group-import-data')}}",
        success:function(data){
          if(data.status==1)
          {
            console.log("Status " +data.status);
            setTimeout(
              function(){
                console.log("Calling Function Again");
                checkStatusForProductsImport();
              }, 5000);
          }
          else if(data.status==0)
          {
            $('.export-alert-success-bulk').removeClass('d-none');
            $('.export-alert-bulk').addClass('d-none');
            $('.export-alert-another-user-bulk').addClass('d-none');
            window.location.href = "{{route('importing-receiving-queue-detail',['id' => $id])}}";
            if(data.exception != null && data.exception != '' && data.exception != '<ol>')
            {
              $('#msgs_alerts').html(data.exception);
              $('.errormsgDiv').removeClass('d-none');
            }
          }
          else if(data.status==2)
          {
            $('.export-alert-success-bulk').addClass('d-none');
            $('.export-alert-bulk').addClass('d-none');
            $('.export-alert-another-user-bulk').addClass('d-none');
            toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
          }
        }
      });
    }
  });
</script>
@stop

