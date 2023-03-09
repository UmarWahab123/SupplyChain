@extends('users.layouts.layout')
@section('title','Dashboard')
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
          <li class="breadcrumb-item active">All POs</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<!-- Right Content Start Here -->
<div class="right-content pt-0">

<!-- upper section start -->
<div class="row mb-2">
<!-- left Side Start -->
<div class="col-lg-12" style="font-size:0.9rem">
<!-- 1st four box row start -->
<div class="row mb-3 headings-color">
  <!-- include -->
@include('users.purchasing.layouts.dashboard-boxes')

</div>
<!-- first four box row end-->

</div>
<!-- left Side End -->
<!-- upper section end  -->
</div>

<div class="row mb-3">

<div class="col-lg-9 headings-color">
  <h4 class="font-weight-bold">All POs</h4>
</div>


<div class="col-lg-12 headings-color">
  <div class="row form-row filters_div">

    <div class="col-md-3 col-6">
      <div class="form-group">
        <label for=""><b>Supplier</b></label>
        <select class="font-weight-bold form-control-lg form-control js-states state-tags selecting-suppliers" name="category" required="true">
          <option value="">Choose @if(!array_key_exists('supplier', $global_terminologies)) Supplier @else {{$global_terminologies['supplier']}} @endif</option>
          @foreach($suppliers as $supplier)
          <option value="{{$supplier->id}}">{{@$supplier->reference_name}}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- @if($targetShipDate['target_ship_date'] == 1)
    <div class="col-md-3 col-6">
      <div class="form-group">
        <label for=""><b>From @if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Receiving Date @else {{$global_terminologies['target_receiving_date']}} @endif</b></label>
        <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
      </div>
    </div>

    <div class="col-md-3 col-6">
      <div class="form-group">
        <label for=""><b>To @if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Receiving Date @else {{$global_terminologies['target_receiving_date']}} @endif</b></label>
        <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
      </div>
    </div>
    @endif --}}

    <!-- Sup-945: Add Invoice date filter date in PO dashboard -->
    <div class="col-lg col-md-6 col-6" style="padding-top: 28px">
        <div class="form-group">
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
        </div>
      </div>
      <div class="col-lg col-md-6 col-6" style="padding-top: 28px">
        <div class="form-group">
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
        </div>
      </div>
      <div class="col-lg col-md-8 col-6" style="margin-top: 2%; padding-left: 35px">
        <div class="d-flex">
        @if($targetShipDate['target_ship_date'] == 1)
          <input type="radio" class="form-check-input dates_changer" id="target_shipped_date" name="date_radio" value='1' checked>
          <label class="form-check-label" for="exampleCheck1"><b>
              @if(!array_key_exists('target_receiving_date', $global_terminologies)) Target Receiving Date @else {{$global_terminologies['target_receiving_date']}} @endif
          </b></label>
        @endif
          <div style="margin-left: 50px !important;" class="d-flex">
              <input type="radio" class="form-check-input dates_changer" id="invoice_date" name="date_radio" value='2' @if($targetShipDate['target_ship_date'] != 1) checked @endif>
              <label class="form-check-label" for="exampleCheck1"><b>Invoice Date</b></label>
          </div>
        </div>
      </div>
      <!-- Ends here -->

    <div class="col-md-3 col-6" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date" type="button">Apply Dates</button>   -->
        {{-- @if($targetShipDate['target_ship_date'] == 1) --}}
        <span class="apply_date common-icons mr-4" title="Apply Date">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>
        {{-- @endif --}}
        <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span>
      </div>
      </div>
    </div>

    {{-- <div class="col" style="margin-top: 26px;">
      <div class="input-group-append ml-3">
           <!--      <span class="reset common-icons" title="Reset">
          <img src="{{asset('public/icons/reset.png')}}" width="27px">
        </span> -->
      </div>
    </div> --}}

  </div>
</div>

<!-- Purchase Orders Table with With multiple statuses -->
<div class="col-lg-12" id="table-po">
  <div class="entriesbg bg-white custompadding customborder">
    @if(Auth::user()->role_id != 7)
    <div class="delete-selected-item mb-3 d-none">
      <a type="submit" href="javascript:void(0);" title="Group To Shipment" class="btn selected-item-btn btn-sm success-btn group_to_po" data-toggle="modal" data-target="#group_to_po" ><img src="{{ asset('public\site\assets\purchasing\img\move.png') }}" alt="Group To Shipment" style="width:30px; height:30px;"></a>
      <a type="button" href="javascript:void(0);" title="Revert To @if(!array_key_exists('waiting_confrimation', $global_terminologies)) Waiting Confirmation @else {{$global_terminologies['waiting_confrimation']}} @endif" class="btn selected-item-btn btn-sm success-btn revert_to_wc"><img src="{{ asset('public\site\assets\purchasing\img\undo.png') }}" alt="Revert To @if(!array_key_exists('waiting_confrimation', $global_terminologies)) Waiting Confirmation @else {{$global_terminologies['waiting_confrimation']}} @endif" style="width:30px; height:30px;"></a>
    </div>
    @endif
    <div class="table-responsive">
      <table class="table entriestable table-bordered table-po text-center">
        <thead>
          <tr>
            <th class="noVis">
              <div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input check-all" name="check_all" id="check-all">
                <label class="custom-control-label" for="check-all"></label>
              </div>
            </th>
            <th>Actions</th>
            <th>PO #
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_number">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_number">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>@if(!array_key_exists('supply_from', $global_terminologies)) Supply From  @else {{$global_terminologies['supply_from']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supply_from">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supply_from">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Customers</th>
            {{--<th>Supplier Ref#</th>--}}
            <th>Confirm Date
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="confirm_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="confirm_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>PO Total
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="po_total">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="po_total">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Invoice Date
                <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="invoice_date">
                  <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                </span>
                <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="invoice_date">
                  <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                </span>
              </th>
            <th>Target Received Date
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="target_receiving_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="target_receiving_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>@if(!array_key_exists('payment_due_date', $global_terminologies))Payment Due Date @else {{$global_terminologies['payment_due_date']}} @endif
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="payment_due_date">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="payment_due_date">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
            <th>Note</th>
            {{--<th>Status</th>--}}

            <th>To Warehouse
              <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="warehouse">
                <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
              </span>
              <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="warehouse">
                <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
              </span>
            </th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th colspan="5" style="text-align: right;"></th>
            <th colspan="4" style="text-align: left;"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>


</div>
</div>
<!-- main content end here -->

{{-- Customers Images Modal --}}
<div class="modal" id="customer-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Purchase Order Customers</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
        <table class="table entriestable table-bordered text-center">
         <thead>
          <tr>
            <th>Sr.#</th>
            <th>Name</th>
          </tr>
         </thead>
         <tbody id="fetched-customers">

         </tbody>
        </table>
        </div>
      </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

{{-- Notes Modal --}}
<div class="modal" id="note-modal">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Purchase Order Notes</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <div class="table-responsive">
          <table class="table entriestable table-bordered text-center">
           <thead>
            <tr>
              <th>Sr.#</th>
              <th>Note</th>
            </tr>
           </thead>
           <tbody id="fetched-notes">

           </tbody>
          </table>
          </div>
        </div>

      <div class="modal-footer">
       <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>



<!--  Group to Po Modal Start Here -->
<div class="modal fade" id="group_to_po">
  <div class="modal-dialog modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Group Details</h3>
        <div class="mt-3">
          <form method="post" class="group_to_po_form">
            <div class="form-group">
              <input type="hidden" name="selected_ids" class="form-control">
            </div>

            <!-- <div class="form-group">
              <input type="text" name="bill_of_lading" class="form-control" placeholder="B/L or AWB">
            </div> -->

            <!-- <div class="form-group">
              <input type="text" name="airway_bill" class="form-control" placeholder="AWB">
            </div> -->

            <div class="form-group">
              <label class="pull-left">Airway Or Bill of landing </label>
              <input type="text" name="bl_awb" class="form-control" placeholder="B/L or AWB">
            </div>

            <div class="form-group">
              <label class="pull-left">Choose Courier</label>
              <select class="form-control" id="courier-select" title="Choose Courier" name="courier"  >
                <option value="" selected="" disabled="">Choose Courier</option>
                @if($couriers)
                @foreach($couriers as $courier)
                <option value="{{@$courier->id}}">{{@$courier->title}}</option>
                @endforeach
                @endif
               <option value="0" class="courier-select" id='selectedCourier'>
                Add new
               </option>
              </select>
            </div>

            <div class="form-group">
              <label class="pull-left">@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</label>
              <input type="date" name="target_receive_date" class="form-control" >
            </div>

            <div class="form-submit">
              <input type="submit" value="Submit" class="btn btn-bg save-btn po-grp-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          </form>
       </div>
      </div>
    </div>
  </div>
</div>

<!--  Add courier Modal Start Here -->
<div class="modal fade" id="addCourier">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Courier</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-courier']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Enter Title']) !!}
            </div>

            <div class="form-submit">
              <input type="button" value="add" class="btn btn-bg save-btn add-courier-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
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
      <h3 style="text-align:center;">Please wait</h3>
      <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
    </div>

    </div>
  </div>
</div>
@endsection


@section('javascript')
<script type="text/javascript">
  //for sorting
  var order = '';
  var column_name = '';
  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');

    $('.table-po').DataTable().ajax.reload();

    if($(this).data('order') ==  '2')
    {
      $(this).next('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_down.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/up.svg') }}");
    }
    else if($(this).data('order') == '1')
    {
      $(this).prev('.sorting_filter_table').children('img').attr("src","{{ url('public/svg/not_active_up.svg') }}");
      $(this).children('img').attr("src","{{ url('public/svg/down.svg') }}");
    }
  });
  $("#from_date, #to_date").datepicker({
    format: "dd/mm/yyyy",
    autoHide: true,
  });

  var currentTime = new Date();
  // First Date Of the month
  var startDateFrom = new Date(currentTime.getFullYear(),currentTime.getMonth(),1);

  $('#from_date').datepicker('setDate', startDateFrom);
  $('#to_date').datepicker('setDate', 'today');
  // $('#to_date').val(null);


  $(function(e){
  $(".state-tags").select2({dropdownCssClass : 'bigdrop'});

  // onchange statuses code starts here
  $('.po-statuses').on('change', function(e){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
      });
      $("#loader_modal").modal('show');
    $('.table-po').DataTable().ajax.reload();
  });

  $('#courier-select').change(function(){
    if($('#courier-select option:selected').val() == 0)
    {
      $("#addCourier").modal('show');
    }
  });

  $(document).on('click', '.add-courier-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-courier-purchase') }}",
          method: 'post',
          data: $('.add-courier').serialize(),
          beforeSend: function(){
            $('.save-btn').val('Please wait...');
            $('.save-btn').addClass('disabled');
            $('.save-btn').attr('disabled', true);
          },
          success: function(result){
            $('.save-btn').val('add');
            $('.save-btn').attr('disabled', true);
            $('.save-btn').removeAttr('disabled');
            if(result.success === true){
              // alert(result.id);
              // alert(result.courier);
              let new_option = '<option selected value="'+result.id+'">'+result.courier+'</option>';
              // alert(new_option);
              $("#courier-select option:last").before(new_option);
              toastr.success('Success!', 'Courier added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-courier')[0].reset();
              $('#addCourier').modal('hide');
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

  $('#from_date').change(function() {
    var date = $('#from_date').val();
    // $('#loader_modal').modal({
    // backdrop: 'static',
    // keyboard: false
    // });
    // $("#loader_modal").modal('show');
    // $('.table-po').DataTable().ajax.reload();
  });

  $('#to_date').change(function() {
    var date = $('#to_date').val();

    // $('#loader_modal').modal({
    // backdrop: 'static',
    // keyboard: false
    // });
    // $("#loader_modal").modal('show');
    // $('.table-po').DataTable().ajax.reload();
  });

  var date_radio = '1';
  $(document).on('click','.apply_date',function(){
    date_radio = $('input[name="date_radio"]:checked').val();
    $('#loader_modal').modal({
    backdrop: 'static',
    keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.table-po').DataTable().ajax.reload();
  });

  $('.selecting-suppliers').on('change', function(e){
    $('#loader_modal').modal({
      backdrop: 'static',
      keyboard: false
    });
    $("#loader_modal").modal('show');
    $('.table-po').DataTable().ajax.reload();
  });

  // $('.my-pos').on('click',function(){
  //   alert('check');
  //   $("#loader_modal").modal('show');
  //   var sort = $(this).data("id");
  //   $('.check-all').prop('checked',false);
  //   if(sort != 13){
  //     $('.delete-selected-item').addClass('d-none');
  //   }

  //   $('.po-statuses').val(sort).change();
  //   setTimeout(function(){ $("#loader_modal").modal('hide'); }, 300);
  // });

  $('.reset').on('click',function(){

    $('#from_date').val("");
    $('#to_date').val("");
    $('.selecting-suppliers').val('');
    $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
    $("#loader_modal").modal('show');
    $(".state-tags").select2("", "");
    $('.table-po').DataTable().ajax.reload();
  });

  var config_target_ship = "{{@$targetShipDate['target_ship_date']}}";
  var show_target = '';
  if(config_target_ship == 1)
  {
    show_target = true;
  }
  else
  {
    show_target = false;
  }

  var table2 = $('.table-po').DataTable({
    processing: false,
    "language": {
    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    // "pagingType":"input",
    "sPaginationType": "listbox",
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    pageLength: {{100}},
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 2,3,4,5,7,8,9,10 ] },
      { className: "dt-body-right", "targets": [ 6] }
    ],
    lengthMenu: [ 1,100, 200, 300, 400],
    ajax:{
      beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
      url:"{!! route('get-all-pos-data') !!}",
      data: function(data) { data.dosortby = $('.po-statuses option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val(),data.selecting_suppliers = $('.selecting-suppliers option:selected').val(),
        data.sortbyparam = column_name,
        data.sortbyvalue = order,
        data.date_radio = date_radio } ,
    },

    columns: [
      { data: 'checkbox', name: 'checkbox' },
      { data: 'action', name: 'action', visible: false },
      { data: 'ref_id', name: 'ref_id' },
      { data: 'supplier', name: 'supplier' },
      { data: 'customer', name: 'customer' },
      // { data: 'supplier_ref', name: 'supplier_ref' },
      { data: 'confirm_date', name: 'confirm_date' },
      { data: 'po_total', name: 'po_total' },
      { data: 'invoice_date', name: 'invoice_date'},
      { data: 'target_receive_date', name: 'target_receive_date', visible: show_target },
      { data: 'payment_due_date', name: 'payment_due_date' },
      { data: 'note', name: 'note' },
      // { data: 'status', name: 'status' },
      { data: 'to_warehouse', name: 'to_warehouse' },
    ],
    initComplete: function () {
        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
        $('body').find('.dataTables_scrollHead').addClass("scrollbar");
      // this.api().columns([]).every(function () {
      //   var column = this;
      //   var input = document.createElement("input");
      //   $(input).addClass('form-control');
      //   $(input).attr('type', 'text');
      //   $(input).appendTo($(column.header()))
      //   .on('change', function () {
      //       column.search($(this).val()).draw();
      //   });
      // });
    },
    fnDrawCallback: function() {
      $('#loader_modal').modal('hide');
      var api = this.api()
      var json = api.ajax.json();
      var total = json.post;
      total = parseFloat(total).toFixed(3);
      total = total.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
      $( api.column( 0 ).footer() ).html('PO Total For All Entries');
      $( api.column( 7 ).footer() ).html(total);
    },
    // drawCallback: function(){
    //   $('#loader_modal').modal('hide');
    // },
  });

  $('.popover-dismiss').popover({
    trigger: 'focus'
  })

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
    if(e.keyCode == 13)
    {
      table2.search($(this).val()).draw();
    }
  });

  $(document).on('click', '.check-all', function () {
    if(this.checked == true){
      $('.check').prop('checked', true);
      $('.check').parents('tr').addClass('selected');
      var cb_length = $( ".check:checked" ).length;
      if(cb_length > 0){
        $('.delete-selected-item').removeClass('d-none');
      }
    }
    else{
      $('.check').prop('checked', false);
      $('.check').parents('tr').removeClass('selected');
      $('.delete-selected-item').addClass('d-none');
    }
  });

  $(document).on('click', '.check', function () {
    if(this.checked == true)
    {
      $('.delete-selected-item').removeClass('d-none');
      $(this).parents('tr').addClass('selected');
    }
    else
    {
      var cb_length = $( ".check:checked" ).length;
      $(this).parents('tr').removeClass('selected');
      if(cb_length == 0){
       $('.delete-selected-item').addClass('d-none');
      }
    }
  });

  $('form.group_to_po_form').on("submit",function(e){
    e.preventDefault();
    var selected_ids = [];

    $("input.check:checked").each(function(){
      selected_ids.push($(this).val());
    });
    $('input[name=selected_ids]').val(selected_ids);
    console.log(selected_ids);
    $.ajaxSetup({
      headers:
      {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });

    $.ajax({
        method: "post",
        url: "{{ route('create-po-group') }}",
        dataType: 'json',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
          $('.po-grp-btn').attr('disabled', true);
        },
        success: function(data)
        {
          $('.po-grp-btn').removeAttr('disabled');
          $('#loader_modal').modal('hide');
          if(data.success == true)
          {
            toastr.success('Success!', 'PO Group Created Successfully.' ,{"positionClass": "toast-bottom-right"});
            $('#group_to_po').modal('hide');
            $('.group_to_po_form')[0].reset();
            $('.delete-selected-item').addClass('d-none');
            $('.table-po').DataTable().ajax.reload();
            setTimeout(function(){
            window.location.href = "{{ route('dispatch-from-supplier')}}";
            }, 1000);
          }
          else if(data.success == false){
            $('#group_to_po').modal('hide');
            $('.group_to_po_form')[0].reset();
            swal({ html:true, title:'Warehouse Alert !!!', text:'<b>Please Select Same Warehouse For Selected Purchase Orders !!!</b>'});
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
  });

});

  // getting PO Customers
  $(document).on('click', '.show-po-cust', function(e){
    let po_id = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-po-customers') }}",
      data: 'po_id='+po_id,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response){
        $('#loader_modal').modal('hide');
         $('#fetched-customers').html(response.html_string);
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

  });

  // getting PO Notes
  $(document).on('click', '.show-po-note', function(e){
    let po_id = $(this).data('id');
    $.ajax({
      type: "get",
      url: "{{ route('get-po-notes') }}",
      data: 'po_id='+po_id,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(response){
        $('#loader_modal').modal('hide');
         $('#fetched-notes').html(response.html_string);
      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
    });

  });

  // Changing purchase order statuses
  $(document).on('change', '.change-po-status', function(e){
    var id = $(this).val();
    var pId = $(this).parents('tr').attr('id');

    swal({
          title: "Alert!",
          text: "Are you sure you want to change the status of this purchase order?",
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
                data:'id='+id+'&pId='+pId,
                url:"{{ route('changing-status-of-po') }}",
                beforeSend: function(){
                  $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                  $('#loader_modal').modal('show');
                },
                success:function(data){
                  // $("#loader_modal").modal('hide');
                  if(data.success == true)
                  {
                    toastr.success('Success!', 'Status Changed Successfully.' ,{"positionClass": "toast-bottom-right"});
                    $('.table-po').DataTable().ajax.reload();
                  }
                },
                error: function(request, status, error){
                  $("#loader_modal").modal('hide');
                }
             });
          }
          else{
              swal("Cancelled", "", "error");
          }
     });

  });

   // delete image
  $(document).on('click', '.revert_to_wc', function(e){

    var selected_ids = [];
    $("input.check:checked").each(function(){
      selected_ids.push($(this).val());
    });

    swal({
      title: "Alert!",
      text: "Are you sure you want to revert selected PO(s) ?",
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
          data:'selected_ids='+selected_ids,
          url:"{{ route('revert-po-status-to-wc') }}",
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
                toastr.success('Success!', 'PO(s) Reverted Successfully.' ,{"positionClass": "toast-bottom-right"});
                setTimeout(function(){
                  window.location.href = "{{ route('purchasing-dashboard')}}";
                }, 800);
              }
            },
            error: function(request, status, error){
              $("#loader_modal").modal('hide');
            }
         });
      }
      else
      {
        swal("Cancelled", "", "error");
      }
     });
  });

  @if(Session::has('successmsg'))
    toastr.success('Success!', "{{ Session::get('successmsg') }}",{"positionClass": "toast-bottom-right"});
    @php
     Session()->forget('successmsg');
    @endphp
  @endif
</script>
@stop

