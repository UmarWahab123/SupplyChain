@extends('users.layouts.layout')

@section('title','Suppliers Management | Supply Chain')

@section('content')
@php
use App\Models\Common\ProductCategory;
@endphp
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
          <li class="breadcrumb-item active">Supplier Center</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row mb-0 filters_div">
  <div class="col-lg-7 col-md-6 title-col">
    <h3 class="maintitle text-uppercase fontbold mb-0 mt-1">SUPPLIERS CENTER</h3>
  </div>

  <div class="col-lg-3 col-md-3">
    <select class="font-weight-bold form-control-lg form-control suppliers_status" name="suppliers_status" >
      <option value="" disabled="" selected="">Choose Status</option>
        <option value="1" selected="true">Completed</option>
        @if(session('msg'))
        <option value="0" selected="true">Incomplete</option>
        @else
        <option value="0">{{$global_terminologies['incomplete']}}</option>
        @endif
        <option value="2">Suspended </option>
    </select>
  </div>

  <div class="col-lg-2 col-md-3 d-flex">
    <button class="btn recived-button text-nowrap custom-center mr-2" id="adding-supplier">Add Supplier</button>
    <span class="export-btn common-icons" id="export_suppliers" title="Create New Export" style="padding: 4px 15px;">
        <img src="{{asset('public/icons/export_icon.png')}}" width="27px">
  </span>
  </div>
</div>

<div class="row errormsgDiv mt-2" style="display: none;">
  <div class="container" style="max-width: 100% !important; min-width: 100% !important">
    <div class="alert alert-danger alert-dismissible">
      <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
      <span id="errormsg"></span>
    </div>
  </div>
</div>

<div class="row entriestable-row mt-3">
  <div class="col-12">
    <div class="entriesbg bg-white mt-4">
        <div class="alert alert-primary export-alert d-none"  role="alert">
           <i class="  fa fa-spinner fa-spin"></i>
      <b> Export file is being prepared! Please wait.. </b>
    </div>
    <div class="alert alert-success export-alert-success d-none"  role="alert">
        <i class=" fa fa-check "></i>

        <b>Export file is ready to download.
        <a class="exp_download" href="{{ url('get-download-xslx','Supplier-list-export.xlsx')}}" target="_blank" id=""><u>Click Here</u></a>
         </b>
    </div>
    <div class="alert alert-primary export-alert-another-user d-none"  role="alert">
        <i class="  fa fa-spinner fa-spin"></i>
        <b> Export file is already being prepared by another user! Please wait.. </b>
    </div>
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
        <table class="table entriestable table-bordered table-suppliers text-center">
            <thead>
              <tr>
                <th class="noVis">Action</th>
                <th>{{$global_terminologies['supplier']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_no">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_no">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Reference Name
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="reference_name">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="reference_name">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['company_name']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="company_name">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="company_name">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Country
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="country">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="country">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Address
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="address">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="address">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>District
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="state">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="state">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Zip Code
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="postalcode">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="postalcode">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Tax ID
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="tax_id">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="tax_id">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                {{--<th>Main Tags</th>--}}
                <th>Supplier Since
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="supplier_since">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="supplier_since">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['open_pos']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="open_pos">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="open_pos">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>{{$global_terminologies['total_pos']}}
                  <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="total_pos">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="total_pos">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                </th>
                <th>Last Order Date
                  <!-- <span class="arrow_up sorting_filter_table" data-order="2" data-column_name="last_order_date">
                    <img src="{{url('public/svg/up.svg')}}" alt="up" style="width:10px; height:10px; cursor: pointer;">
                  </span>
                  <span class="arrow_down sorting_filter_table" data-order="1" data-column_name="last_order_date">
                    <img src="{{url('public/svg/down.svg')}}" alt="down" style="width:10px; height:10px; cursor: pointer;">
                  </span> -->
                </th>
                <th>{{$global_terminologies['note_two']}}</th>
                <th>Status
                </th>
              </tr>
            </thead>
        </table>
      </div>
    </div>
  </div>
</div>


<!--  Content End Here -->


{{--Edit Modal--}}
<div class="modal" id="editSupplierModal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>
      <div class="modal-body text-center" id="editSupplierModalForm">

      </div>
    </div>
  </div>
</div>

<!-- Modal For Note -->
<div class="modal" id="add_notes_modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add Supplier Notes</h4>
        <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <form role="form" class="add-sup-note-form" method="post">
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="row">
              <div class="col-xs-12 col-md-12">
                {{--<div class="form-group">
                  <label>Title <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" placeholder="Note Title" name="note_title">
                </div>--}}

                <div class="form-group">
                  <label>Description <span class="text-danger">*</span> <small>(255 Characters Max)</small></label>
                  <textarea class="form-control" placeholder="Note Description" rows="6" name="note_description" maxlength="255"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <input type="hidden" name="supplier_id_note" class="note-supplier-id">
        <button class="btn btn-success" type="submit" class="save-btn" ><i class="fa fa-floppy-o"></i> Save </button>
        <button type="button" class="btn btn-danger close-btn" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
      </div>
     </form>

    </div>
  </div>
</div>

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

{{-- Supplier Notes Modal --}}
<div class="modal" id="notes-modal">
  <div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Supplier Notes</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>

    <div class="modal-body">
      <div class="fetched-notes">
        <div class="adv_loading_spinner3 d-flex justify-content-center">
            <img class="img-spinner" src="{{ url('public/uploads/gif/waiting.gif') }}" style="margin-top: 10px;">
        </div>
      </div>
    </div>

    <div class="modal-footer">
     <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div>
  </div>
  </div>
</div>

<form id="export_supplier_data_form">
    <input type="hidden" name="supplier_status" id="supplier_status">
    <input type="hidden" name="column_name" id="column_name">
    <input type="hidden" name="sort_order" id="sort_value">
    <input type="hidden" name="search_value" id="search_value">
  </form>

@endsection

@section('javascript')
<script type="text/javascript">

  // Customer Sorting Code Here
  var order = 1;
  var column_name = '';

  $('.sorting_filter_table').on('click',function(){
    $('.arrow_up').children('img').attr("src","{{ url('public/svg/up.svg') }}");
    $('.arrow_down').children('img').attr("src","{{ url('public/svg/down.svg') }}");

    order = $(this).data('order');
    column_name = $(this).data('column_name');
    data_id = $(this).data('id');

    $('.table-suppliers').DataTable().ajax.reload();

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
  // adding supplier row on click function
  $(document).on('click','#adding-supplier',function(){

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('adding-supplier') }}",
      method:'get',
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
      },
      success: function(result){
        $('#loader_modal').modal('hide');
        if(result.id != null)
        {
          var id = result.id;
          window.location.href = "{{ url('get-supplier-detail') }}"+"/"+id;
        }

      },
      error: function(request, status, error){
        $("#loader_modal").modal('hide');
      }
      });
  });

  $(document).ready(function(){
    $("#inputTextBox").keypress(function(event){
      var inputValue = event.charCode;
      if(!(inputValue >= 65 && inputValue <= 120) && (inputValue != 32 && inputValue != 0)){
          event.preventDefault();
      }
    });
  });

  $(function(e){

    var table2 = $('.table-suppliers').DataTable({
      "sPaginationType": "listbox",
      processing: false,
      "language": {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      searching:true,
      serverSide: true,
      scrollX: true,
      scrollY : '90vh',
      dom: 'Blfrtip',
      scrollCollapse: true,
      "columnDefs": [
        { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,9,10 ] },
        { targets: [{{ ($table_hide_columns != null) ? $table_hide_columns->hide_columns : null }}], visible: false },

      ],
      buttons: [
        {
            extend: 'colvis',
            columns: ':not(.noVis)',

        }
      ],
      "lengthMenu": [100,200,300,400],
      ajax:
      {
        beforeSend: function(){
          $('#loader_modal').modal('show');
        },
        url: "{!! route('getting-supplier') !!}",
        data: function(data) { data.suppliers_status = $('.suppliers_status option:selected').val(),
                data.sort_order = order,
                data.column_name = column_name
              } ,
      },
      // ajax: "{!! route('getting-supplier') !!}",
      columns: [
        { data: 'action', name: 'action' },
        { data: 'supplier_nunmber', name: 'supplier_nunmber' },
        { data: 'reference_name', name: 'reference_name' },
        { data: 'company', name: 'company' },
        { data: 'country', name: 'country' },
        { data: 'address', name: 'address' },
        { data: 'state', name: 'state' },
        { data: 'postalcode', name: 'postalcode' },
        { data: 'tax_id', name: 'tax_id' },
        // { data: 'product_type', name: 'product_type' },
        { data: 'created_at', name: 'created_at' },
        { data: 'open_pos', name: 'open_pos' },
        { data: 'total_pos', name: 'total_pos' },
        { data: 'last_order_date', name: 'last_order_date' },
        { data: 'notes', name: 'notes' },
        { data: 'status', name: 'status' },
      ],
      initComplete: function () {
      },
      drawCallback: function(){
        $('#loader_modal').modal('hide');
      },
    });

    table2.on( 'column-visibility.dt', function ( e, settings, column, state ) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.post({
        url : "{{ route('toggle-column-display') }}",
        dataType : "json",
        data : {type:'supplier_list',column_id:column},
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data){
          $('#loader_modal').modal('hide');
          if(data.success == true){
            /*toastr.success('Success!', 'Product Column hidden/visible successfully.' ,{"positionClass": "toast-bottom-right"});*/
            // table2.ajax.reload();
          }
        },
        error: function(request, status, error)
        {
          $('#loader_modal').modal('hide');
        }
      });
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

    $(document).on('change','.suppliers_status',function(){

      var selected = $(this).val();
      if($('.suppliers_status option:selected').val() != '')
      {
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        $('.table-suppliers').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      }
  });

  });

  // delete Product
  $(document).on('click', '.deleteSupplier', function(e){

    var id = $(this).data('id');
      swal({
        title: "Are you sure?",
        text: "You want to delete this supplier ?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: false
        },
      function (isConfirm) {
          if (isConfirm) {
            $.ajax({
              method:"get",
              data:'id='+id,
              url: "{{ route('deleting-supplier') }}",
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(response){
                $('#loader_modal').modal('hide');
                if(response.success === true){
                  toastr.success('Success!', 'Supplier Deleted Successfully.',{"positionClass": "toast-bottom-right"});
                  $('.table-suppliers').DataTable().ajax.reload();
                }
              },
              error: function(request, status, error){
                $("#loader_modal").modal('hide');
              }
            });
          }
          else {
              swal("Cancelled", "", "error");
          }
      });
    });

  $(document).on('click', '.add-notes', function(e){
    var supplier_id = $(this).data('id');
    $('.note-supplier-id').val(supplier_id);
  });

  $(document).on('click', '.close-btn', function(e){
    $('.add-sup-note-form')[0].reset();
    $('.invalid-feedback').remove();
    $('textarea').css('border-color', 'black');

  });

  $('.add-sup-note-form').on('submit', function(e){
    e.preventDefault();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.ajax({
        url: "{{ route('add-supplier-notes') }}",
        dataType: 'json',
        type: 'post',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,
        beforeSend: function(){
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(result){
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          $('#loader_modal').modal('hide');
          if(result.success == true){
            toastr.success('Success!', 'Note added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-sup-note-form')[0].reset();
            $('#add_notes_modal').modal('hide');
            $('.table-suppliers').DataTable().ajax.reload();

          }else{
            toastr.error('Error!', result.errormsg,{"positionClass": "toast-bottom-right"});
          }

        },
        error: function (request, status, error) {
              /*$('.form-control').removeClass('is-invalid');
              $('.form-control').next().remove();*/
              $('#loader_modal').modal('hide');
              $('.save-btn').removeClass('disabled');
              $('.save-btn').removeAttr('disabled');
              json = $.parseJSON(request.responseText);
              $.each(json.errors, function(key, value){
                if ($('.invalid-feedback').html() == undefined) {
                 $('textarea[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
                 $('textarea[name="'+key+'"]').addClass('is-invalid');
                 $('textarea[name="'+key+'"]').css('border-color', '#dc3545');
                }
              });
          }
      });
    });

  $(document).on('click', '.show-notes', function(e){
  let sid = $(this).data('id');
  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
  $.ajax({
    type: "post",
    url: "{{ route('get-supplier-notes') }}",
    data: 'supplier_id='+sid,
    beforeSend: function(){
      var loader_img = "{{ url('public/uploads/gif/waiting.gif') }}";
      var loader_html = '<div class="d-flex justify-content-center"><img class="img-spinner" src="'+loader_img+'" style="margin-top: 10px;"></div>';
      $('.fetched-notes').html(loader_html);
    },
    success: function(response){
      $('.fetched-notes').html(response);
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });

  });

  $(document).on('click', '.delete-note', function(e){
  let id = $(this).data('id');

  $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });

  swal({
    title: "Are you sure?",
    text: "You want to delete this Supplier Note?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    closeOnConfirm: true,
    closeOnCancel: false
    },
  function (isConfirm) {
      if (isConfirm) {
         $.ajax({
    type: "post",
    url: "{{ route('delete-supplier-note') }}",
    data: 'note_id='+id,
    beforeSend: function(){
      $('#loader_modal').modal({
        backdrop: 'static',
        keyboard: false
      });
      $('#loader_modal').modal('show');
    },
    success: function(response){
      $('#loader_modal').modal('hide');
      if(response.error == false)
      {
        $("#gem-note-"+id).remove();
      }
    },
    error: function(request, status, error){
      $("#loader_modal").modal('hide');
    }
  });
      }
      else {
          swal("Cancelled", "", "error");
      }
  });



  });

  //Suspend or deactivate customer
  $(document).on('click', '.suspend-supplier', function(){
      var id = $(this).data('id');
      swal({
          title: "Alert!",
          text: "Are you sure you want to suspend this Supplier?",
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
                data:{id:id,type:'supplier'},
                url:"{{ route('supplier-suspension') }}",
                beforeSend:function(){
                   $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                      });
                   $("#loader_modal").modal('show');
                },
                success:function(data){
                  $("#loader_modal").modal('hide');
                    if(data.error == false){
                      toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                      setTimeout(function(){
                        $('.table-suppliers').DataTable().ajax.reload();
                      }, 1500);
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

  $(document).on('click', '.activateIcon', function(){
    var id = $(this).data('id');
    swal({
        title: "Alert!",
        text: "Are you sure you want to activate this Supplier?",
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
              data:{id:id},
              url:"{{ route('supplier-activation') }}",
              beforeSend:function(){
                 $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                 $("#loader_modal").modal('show');
              },
              success:function(data){
                $("#loader_modal").modal('hide');
                  if(data.error == false){
                    toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                    setTimeout(function(){
                      $('.table-suppliers').DataTable().ajax.reload();
                    }, 1500);
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

  $(document).on('click','.delete-incomplete-supplier',function(){
    var supplier_id = $(this).data('id');
    var pos = $(this).data('pos');
    if(pos > 0)
    {
      toastr.info('Sorry!', 'Supplier is bonded to POs cannot delete this supplier !!!',{"positionClass": "toast-bottom-right"});
      return;
    }
    swal({
      title: "Are You Sure?",
      text: "You want to delete this Supplier!!!",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, do it!",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: false
    },
    function (isConfirm) {
      if (isConfirm) {
      $.ajax({
        method: "get",
        url: "{{ url('discard-supplier-from-detail') }}",
        dataType: 'json',
        data: {id:supplier_id},

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
          if(data.success == true)
          {
            toastr.success('Success!', 'Supplier deleted successfully.',{"positionClass": "toast-bottom-right"});
            $('.table-suppliers').DataTable().ajax.reload();
          }
          if(data.success == false)
          {
            $('.errormsgDiv').show();
            $('#errormsg').html(data.errorMsg);
            // toastr.info('Warning!', data.errorMsg ,{"positionClass": "toast-bottom-right"});
            $('.table-suppliers').DataTable().ajax.reload();
          }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
        });
      }
      else
      {
        $('#loader_modal').modal('hide');
        swal("Cancelled", "", "error");
      }
    });
  });

  $(document).on('click', '.closeErrorDiv', function (){
    $('.errormsgDiv').hide();
  });

    $(document).on('click','#export_suppliers',function(){
        $('#supplier_status').val($('.suppliers_status option:selected').val());
        $('#column_name').val(column_name);
        $('#sort_value').val(order);
        $('#search_value').val($('.table-suppliers').DataTable().search());

        var form=$('#export_supplier_data_form');
        var form_data = form.serialize();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
        method:"get",
        url:"{{route('export-suplier-data')}}",
        data:form_data,
        beforeSend:function(){
            $('.export-btn').prop('disabled',true);
        },
        success:function(data){
            if(data.status==1)
            {
                $('.export-alert-success').addClass('d-none');
                $('.export-alert').removeClass('d-none');
                $('.export-btn').prop('disabled',true);
                console.log("Calling Function from first part");
                checkStatusForSupplierList();
            }
            else if(data.status==2)
            {
                $('.export-alert-another-user').removeClass('d-none');
                $('.export-alert').addClass('d-none');
                $('.export-btn').prop('disabled',true);
                checkStatusForSupplierList();
            }
        },
        error:function(){
            $('.export-btn').prop('disabled',false);
        }
        });
    });

    function checkStatusForSupplierList()
    {
        $.ajax({
        method:"get",
        url:"{{route('recursive-export-status-supplier-list')}}",
        success:function(data){
            if(data.status==1)
            {
                console.log("Status " +data.status);
                setTimeout(
                    function(){
                    console.log("Calling Function Again");
                    checkStatusForSupplierList();
                }, 5000);
            }
            else if(data.status==0)
            {
                $('.export-alert-success').removeClass('d-none');
                $('.export-alert').addClass('d-none');
                $('.export-btn').prop('disabled',false);

            }
            else if(data.status==2)
            {
                $('.export-alert-success').addClass('d-none');
                $('.export-alert').addClass('d-none');
                $('.export-alert-another-user').addClass('d-none');
                $('.export-btn').prop('disabled',false);
                toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
                console.log(data.exception);
            }
        }
        });
    }

</script>
@stop

