@extends('users.layouts.layout')

@section('title','Suppliers Management | Supply Chain')

@section('content')
<style type="text/css">
p
{
  font-size: small;
  font-style: italic;
  color: red;
}
.selectDoubleClick, .inputDoubleClick{
    font-style: italic;
}
</style>

    <div class="row mb-3">
        <div class="col-md-8 title-col">
            <h3 class="maintitle text-uppercase fontbold">Incomplete Temporary Suppliers <p style="margin-top: 5px;">Note: The ITALIC text is double click editable.</p></h3>
        </div>
        <div class="col-md-4 search-col">
        </div>
    </div>

    <div class="row entriestable-row mt-3">
        <div class="col-12">
            <form  id="form_id" method="post">
                {{csrf_field()}}
            <div class="entriesbg bg-white custompadding customborder">
                <input type="hidden" name="selected_values[]" id="selected_values">
                    <div class="mt-2" style="width: 100%; overflow-x: auto">
                        <table class="table entriestable text-center temp-incomplete-table table-bordered">
                            <thead class="table-bordered">
                            <tr>
                                <th>{{$global_terminologies['company_name']}}</th>
                                <th>Reference Name </th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Country</th>
                                <th>state</th>
                                <th>City</th>
                                <th>Tax ID</th>
                                <th>Postal Code</th>
                                <th>Currency</th>
                                <th>Credit Terms</th>
                                <th>Name</th>
                                <th>Sur Name</th>
                                <th>Contact Email</th>
                                <th>Contact Phone</th>
                                <th>Position</th>
                                <!-- <th>Status</th> -->
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <div style="margin-top: 30px">
                        <a href="javascript:void(0)" id="confirmTempRemove" class="btn btn-danger">Discard All Entries</a>
                        <a href="javascript:void(0)" onclick="moveAllToIncomplete()" class="btn btn-info d-none">Move All Entries to Inventory</a>
                        <input type="button" class="btn btn-success save-btn" value="Save & Close" style="float: right">
                    </div>
            </div>
            </form>
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
@endsection


@section('javascript')
<script>
$(function(e){

    $('.temp-incomplete-table').DataTable({
        "sPaginationType": "listbox",
        oLanguage: {
        sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
        },
         processing: true,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        searching: true,
        ordering: false,
        serverSide: true,
        bInfo: true,
        paging: true,
        dom: 'lrtip',
        pageLength: {{100}},
        lengthMenu: [ 100, 200, 300, 400],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax: "{{ url('get-temp-suppliers-data') }}",
        columns: [
            { data: 'supplier_company', name: 'supplier_company' },
            { data: 'reference_name', name: 'reference_name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'address', name: 'address' },
            { data: 'country', name: 'country' },
            { data: 'state', name: 'state' },
            { data: 'city', name: 'brand_id' },
            { data: 'tax_id', name: 'tax_id' },
            { data: 'postal_code', name: 'postal_code' },
            { data: 'currency', name: 'currency' },
            { data: 'credit_term', name: 'credit_term' },
            { data: 'c_name', name: 'c_name' },
            { data: 'c_sur_name', name: 'c_sur_name' },
            { data: 'c_email', name: 'c_email' },
            { data: 'c_telehone_number', name: 'c_telehone_number' },
            { data: 'c_position', name: 'position' },
            // { data: 'status', name: 'status' },
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
            $('.dataTables_scrollHead').css('overflow', 'auto');
            $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        }
    });

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    $(document).on('click', '#confirmTempRemove', function () {
        swal({
                title: "Alert!",
                text: "Are you sure you want to delete all entries?",
                type: "info",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes!",
                cancelButtonText: "No!",
                closeOnConfirm: true,
                closeOnCancel: false
            },
            function (isConfirm) {
                if (isConfirm) {
                    $('#loader_modal').modal({
                      backdrop: 'static',
                      keyboard: false
                    });
                    $("#loader_modal").modal('show');
                    window.location.href = "{{url('discard-temp-suppliers-data')}}";
                }
                else {
                    swal("Cancelled", "", "error");
                }
            });
    });

    $(document).on('change' ,'.turngreen', function () {
        $(this).removeClass('btn-outline-danger');
        $(this).addClass('btn-outline-success');
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $("#loader_modal").modal('show');
        var sId = $(this).parents('tr').attr('id');
        var new_value = $("option:selected", this).val();
        console.log(sId,new_value,$(this).attr('name'));
        saveTempSupplierData(sId, $(this).attr('name'), $(this).val());
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

       }, 300);
    });

    $(document).on('keypress', 'input[type=text]', function(e){
        if(e.keyCode === 13 && $(this).hasClass('active'))
        {
            var sId = $(this).parents('tr').attr('id');
            if($(this).val() !== '' && $(this).hasClass('active'))
            {
              var new_value = $(this).val();
              $(this).removeClass('active');
              $(this).addClass('d-none');
              $(this).prev().removeClass('d-none');
              $(this).prev().html(new_value);
              $(this).prev().css("color", "");
              saveTempSupplierData(sId, $(this).attr('name'), $(this).val());
            }
        }
    });

    function saveTempSupplierData(supplier_id,field_name,field_value){
      console.log(field_name+' '+field_value+''+supplier_id);
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('save-temp-supplier-data') }}",
        dataType: 'json',
        data: 'supplier_id='+supplier_id+'&'+field_name+'='+field_value,
        beforeSend:function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
        },
        success: function(data)
        {

            $('.temp-incomplete-table').DataTable().ajax.reload();
            setTimeout(function(){
                $("#loader_modal").modal('hide');
            }, 300);

            if(data.completed == 1)
            {
                toastr.success('Success!', 'Information updated successfully. Supplier marked as Active.',{"positionClass": "toast-bottom-right"});
                $('.temp-incomplete-table').DataTable().ajax.reload();
                setTimeout(function(){
                    $("#loader_modal").modal('hide');
                }, 300);
            }
        },
        error: function(request, status, error){
          $("#loader_modal").modal('hide');
        }
      });
    }

    $(document).on('click', '.save-btn', function(e){
        swal({
        title: "Only The Completed Record Will be Inserted to Suppliers The Rest will Still be Here. ",
        text: 'Are You Sure!',
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
            window.location.reload();
          }
          else {
            swal("Cancelled", "", "error");
          }
        });
    });
});
</script>
@endsection
