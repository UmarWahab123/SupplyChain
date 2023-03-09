@extends('importing.layouts.layout')
@section('title','Dashboard')


@section('content')

{{-- Content Start from here --}}


<div class="right-content pt-0">
    <div class="row mb-3 headings-color">
        <div class="col-lg-9">
          <h4 class="mb-0 fontbold mt-2">Dashboard</h4>
        </div>
        <div class="col-lg-3">
           <div class="form-group">
                <select class="form-control">
                    <option>Open Product Receiving</option>
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                </select>
            </div>
        </div>


        <div class="col-lg-12">
        
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
                            <th>{{$global_terminologies['qty']}}</th>
                            <th>Net Weight (KG)</th>
                            <th>Issue Date </th>
                            <th> PO Total</th>
                            <th>Target Received Date</th>
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
    <!-- <tbody class="dot-dash">
        <tr>
            <td>#1</td>
            <td>12345</td>
            <td>12345</td>
            <td>dhl</td>
            <td>xyz</td>
            <td>12345</td>
            <td></td>
            <td>5</td>
            <td>14/09/2019</td>
            <td>$12,235</td>
            <td>14/09/2019</td>
            <td>$12</td>
            <td>$140</td>
            <td>$20</td>
            <td>xyz</td>
            <td>abc</td>
           <td class="icon-size">
            <i class="fa fa-eye"></i>
            <i class="fa fa-trash red-trash"></i></td>
        </tr>     
    </tbody>-->

@endsection


@section('javascript')
<script type="text/javascript">
$(function(e){

  $('.product_receiving_table').DataTable({
   processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
  ordering: false,
  serverSide: true,
  ajax:"{{ route('get-po-groups') }}",

  columns: [
      { data: 'action', name: 'action' },
      { data: 'id', name: 'id' },
      { data: 'po_number', name: 'po_number' },
      // { data: 'bill_of_lading', name: 'bill_of_lading' },
      { data: 'bl_awb', name: 'bl_awb' },
      // { data: 'airway_bill', name: 'airway_bill' },
      { data: 'courier', name: 'courier' },
      { data: 'supplier', name: 'supplier' },
      // { data: 'supplier_ref_no', name: 'supplier_ref_no' },
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
  ]
});

  $(document).on("dblclick",".inputDoubleClick",function(){
    // alert(this);
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().focus();
  });

  // to make that field on its orignal state
  $(document).on("focusout",".fieldFocus",function() { 
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

    });
  }
</script>
@stop

