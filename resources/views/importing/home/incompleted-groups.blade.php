@extends('importing.layouts.layout')
@section('title','Dashboard')


@section('content')
<style type="text/css">
  .inputDoubleClick{
  font-style: italic;
  font-weight: bold;
}
</style>
{{-- Content Start from here --}}


<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
      <div class=" col-xl-4 col-lg-6 col-md-8">
        <h4 class="mb-0 fontbold mt-2">Product Receiving Dashboard</h4>
      </div>
    </div>
    <div class="row mb-3 headings-color">
      <div class="col-xl-2 col-lg-1 col-md-2 d-md-none">
        
      </div>
        <div class="col-xl-3 col-lg-3 col-md-3">
          <div class="form-group">
            <input type="date" class="form-control" name="from_date" id="from_date">
          </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-3">
          <div class="form-group">
            <input type="date" class="form-control" name="to_date" id="to_date">
          </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-md-3">
           <div class="form-group">
                <select class="form-control product_receiving">
                    <option value="0" selected="true">Open Product Receiving</option>
                    <option value="1">Closed Product Receiving</option>
                </select>
            </div>
        </div>
        <div class="col-xl-1 col-lg-2 col-md-3 p-0">
          <div class="input-group-append">
            <button class="btn recived-button reset-btn" type="reset">@if(!array_key_exists('reset', $global_terminologies)) Reset @else {{$global_terminologies['reset']}} @endif</button>  
          </div>
        </div>

        <div class="col-xl-1 col-lg-2 col-md-3 ml-3 p-0">
          <div class="input-group-append">
            <button class="btn recived-button run-script-btn" type="reset">run Script</button>  
          </div>
        </div>


        <div class="col-lg-12 col-md-12">
        
            <div class="bg-white table-responsive">
            <div class="p-4">
              <table class="table headings-color entriestable text-center table-bordered product_receiving_table" style="width:100%">
                    <thead class="sales-coordinator-thead table-bordered">
                        <tr>
                            <th>Action</th>
                            <th>Group#</th>
                            <th>POs#</th>
                            <!-- <th>B/L</th> -->
                            <th>B/L or AWB</th>
                            <!-- <th>AWB</th> -->
                            <th>Courier</th>
                            <th>Supplier</th>
                            <!-- <th>Supplier Ref#</th> -->
                            <th>Purchase <br>{{$global_terminologies['qty']}}</th>
                            <th>Net <br>Weight (KG)</th>
                            <th>Issue <br> Date </th>
                            <th> PO <br>Total(THB)</th>
                            <th>Target <br> Received <br> Date</th>
                            <th>Tax</th>
                            <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
                            <th>{{$global_terminologies['landing_per_billed_unit']}}</th>
                            <th>Warehouse</th>
                            <th>{{$global_terminologies['note_two']}}</th>
                        </tr>
                    </thead>
                </table>
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
// onchange statuses code starts here
  $('.product_receiving').on('change', function(e){
    $('.product_receiving_table').DataTable().ajax.reload();  
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#from_date').change(function() {
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('#to_date').change(function() {
    $('.product_receiving_table').DataTable().ajax.reload();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.reset-btn').on('click',function(){
    $('#from_date').val('').change();
    $('#to_date').val('').change();
    $('.product_receiving').val('0').change();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $('.run-script-btn').on('click',function(){
    $.ajax({
        url: "{{ route('run-group-script') }}",
        method: 'get',
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal2').modal('show');
        },
        success: function(result){
          if(result.success === true){
            $('#loader_modal').modal('hide');
            toastr.success('Success!', 'Script Run successfully',{"positionClass": "toast-bottom-right"});
          }
        },
    });
  });

$(function(e){
  var table2 = $('.product_receiving_table').DataTable({
   processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  serverSide: true,
  scrollX: true,
  scrollY : '90vh',
    scrollCollapse: true,
  dom: 'Blfrtip',
  pageLength: {{100}},
  lengthMenu: [ 100, 200, 300, 400],
  colReorder: {
    realtime: false
  },
  columnDefs: [
    { targets: [{{$hidden_by_default}}], visible: false },
    { className: "dt-body-left", "targets": [ 1,2,3,4,5,6,7,8,10,14,15 ] },
    { className: "dt-body-right", "targets": [9,11,12,13] }
  ],
  buttons: [
    {
      extend: 'colvis',
      columns: ':not(.noVis)',
    }
  ],
  ajax:{
    url:"{!! route('get-incompleted-po-groups') !!}",
    data: function(data) { data.dosortby = $('.product_receiving option:selected').val(),data.from_date = $('#from_date').val(),data.to_date = $('#to_date').val() } ,
  },

  columns: [
      { data: 'action', name: 'action' },
      { data: 'id', name: 'id' },
      { data: 'po_number', name: 'po_number' },
      // { data: 'bill_of_lading', name: 'bill_of_lading' },
      { data: 'bill_of_landing_or_airway_bill', name: 'bill_of_landing_or_airway_bill' },
      // { data: 'airway_bill', name: 'airway_bill' },
      { data: 'courier', name: 'courier' },
      { data: 'supplier_ref_no', name: 'supplier_ref_no' },
      { data: 'quantity', name: 'quantity' },
      { data: 'net_weight', name: 'net_weight' },
      { data: 'issue_date', name: 'issue_date' },
      { data: 'po_total', name: 'po_total' },
      { data: 'target_receive_date', name: 'target_receive_date' },
      { data: 'tax', name: 'tax' },
      { data: 'freight', name: 'freight' },
      { data: 'landing', name: 'landing' },
      { data: 'warehouse', name: 'warehouse' },
      { data: 'bill_of_lading', name: 'bill_of_lading' },
  ],
    initComplete: function () {
      // Enable THEAD scroll bars
        $('.dataTables_scrollHead').css('overflow', 'auto');
        $('.dataTables_scrollHead').on('scroll', function () {
        $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
      }); 
    },
});

  $('.dataTables_filter input').unbind();
  $('.dataTables_filter input').bind('keyup', function(e) {
  if(e.keyCode == 13) {
    table2.search($(this).val()).draw();
  }
  });

  $(document).on("dblclick",".inputDoubleClick",function(){
    // alert(this);
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
  });

    // dropdown double click editable code start here
  $(document).on(' keyup change', 'select.select-common', function(e){
    if (e.keyCode === 27 && $(this).hasClass('active')) {
    //alert('hi');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');        
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    } 
    else{
      var new_value = $("option:selected", this).html();
      var group_id= $(this).data('id');
      var thisPointer = $(this);
      saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),group_id);
      thisPointer.addClass('d-none');        
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
      $(this).prev().html(new_value);
    }
  });
  // to make that field on its orignal state
  $(document).on("keyup focusout",".fieldFocus",function(e) {
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var thisPointer = $(this);
      thisPointer.addClass('d-none');        
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    } 
      if($(this).val().length < 1)
      {
        // swal("Must fill this filed accurately!");
        return false;
      }
     if((e.keyCode === 13 || e.which === 0) && $(this).hasClass('active'))
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
          var group_id= $(this).data('id');
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.prev().removeClass('d-none');
          if(new_value != '')
          {
            $(this).prev().html(new_value);
          }
          saveSupData(thisPointer,thisPointer.attr('name'), thisPointer.val(),group_id);
        }
      } 
  });
  
});

  function saveSupData(thisPointer,field_name,field_value,group_id){      
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('importing/edit-po-group') }}",
      dataType: 'json',
      // data: {field_name:field_name,field_value:field_value,supplier_id:supplier_id,tag_index:tag_index},
      data: 'group_id='+group_id+'&'+field_name+'='+field_value,
      beforeSend: function(){
        // shahsky here
      },
      success: function(data)
      {
        if(data.success == true)
        {
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});

          // if(field_name == "main_tags")
          // {
          //   location.reload();
          // }
          return true;
        }
      },
       error: function (request, status, error) {
        swal('Something Went Wrong! Contact Administrator!');
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
</script>
@stop

