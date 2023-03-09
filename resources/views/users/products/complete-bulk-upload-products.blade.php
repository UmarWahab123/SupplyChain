@extends('users.layouts.layout')

@section('title','Products Management | Supply Chain')

@section('content')
@php
use App\Models\Common\Supplier;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\Unit;
use Carbon\Carbon;
@endphp
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
.yellow{
   color:#856404;
   background-color:#fff3cd !important;
}
</style>

    <div class="row align-items-center mb-3">
        <div class="col-md-8 title-col">
            <h3 class="maintitle">Incomplete Temporary Products <p style="margin-top: 5px;">Note: The ITALIC text is double click editable.</p></h3>
        </div>
        <div class="col-md-4 search-col">

        </div>
    </div>
    <div class="row errormsgDiv d-none">
        <div class="container">
          <div class="alert alert-danger alert-dismissible">
            <a href="javascript:void(0)" class="closeErrorDiv">&times;</a>
            <span id="errormsg"></span>
          </div>
        </div>
    </div>

    <div class="row entriestable-row mt-3">
        <div class="col-12">
             <a href="javascript:void(0)" id="moveToIncompleteSelected" class="btn btn-success float-right mt-5 mr-3" style="float: right;">Move Selected To Inventory</a>
            <form  id="form_id" method="post">
                {{csrf_field()}}
            <div class="entriesbg bg-white custompadding customborder">

                <span><strong>Total Products: {{$temp_success_products_count+$temp_failed_products_count}}</strong></span> <br>
                <span style="color: green">{{$temp_success_products_count}} product(s) Had No Issuse!</span><br>
                <span>{{$temp_failed_products_count}} product(s) had <span style="color: red">Empty/Mismatched</span> Values.</span>
                <br>
                <input type="hidden" name="selected_values[]" id="selected_values">
                <a href="javascript:void(0)" style="display: none" id="discardSelected" class="btn btn-danger">Discard Selected</a>
                    <div class="mt-2" style="width: 100%; overflow-x: auto">
                        <table class="table entriestable text-center temp-incomplete-table table-bordered">
                            <thead class="table-bordered">
                            <tr>
                                <th>
                                    <div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                        <input class="custom-control-input check" type="checkbox" id="check_all" name="check_all">
                                        <label class="custom-control-label" for="check_all"></label>
                                    </div>
                                </th>
                                <th>{{$global_terminologies["our_reference_number"]}}</th>
                                <th>System Code</th>
                                <th>Supplier</th>
                                <th>{{$global_terminologies['supplier_description']}} </th>
                                <th>{{$global_terminologies['product_description']}} </th>
                                <th>{{$global_terminologies["avg_units_for-sales"]}}</th>
                                <th>Primary {{$global_terminologies['category']}} </th>
                                <th> {{$global_terminologies['subcategory']}} </th>
                                <th>Good Type</th>
                                <th>Good @if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</th>
                                <th>Good @if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</th>
                                <th>{{$global_terminologies['brand']}} / Origin</th>
                                <th> {{$global_terminologies['temprature_c']}} </th>
                                <th>Supplier <br>Billed Unit</th>
                                <th>Selling<br> Unit</th>
                                <th>Stock<br> Unit</th>
                                <th>{{ $global_terminologies['order_qty_per_piece'] }}</th>
                                <th>{{$global_terminologies['minimum_stock']}} </th>
                                <th>{{$global_terminologies['minimum_order_quantity']}} </th>
                                <th>Ordering <br> Unit</th>
                                <th>Billed Unit <br> Per Package</th>
                                <th>{{$global_terminologies['unit_conversion_rate']}}</th>
                                <th>{{$global_terminologies['suppliers_product_reference_no']}}</th>
                                <th>{{$global_terminologies["purchasing_price"]}} <br> (EUR) </th>
                                <th>{{$global_terminologies['gross_weight']}}</th>
                                <th>{{$global_terminologies['freight_per_billed_unit']}}</th>
                                <th> {{$global_terminologies['landing_per_billed_unit']}} </th>
                                <th>Import <br> Tax <br> Actual</th>
                                <th>{{$global_terminologies['extra_cost_per_billed_unit']}} </th>
                                <th>Extra Tax (THB) </th>
                                <th>{{$global_terminologies['expected_lead_time_in_days']}} </th>
                                <th>{{$global_terminologies['note_two']}}</th>
                                <th>Vat (%)</th>
                                @foreach($customerCategory as $customerCat)
                                  <th>{{$customerCat->title}} Default <br> Fixed <br> Price</th>
                                @endforeach
                            </tr>
                            </thead>


                        </table>
                    </div>
                    <div style="margin-top: 30px">
                        <a href="javascript:void(0)" id="confirmTempRemove" class="btn btn-danger">Discard All Entries</a>
                        <a href="javascript:void(0)" onclick="moveAllToIncomplete()" class="btn btn-info d-none">Move All Entries to Inventory</a>
                        <input type="button" class="btn btn-success save-btn d-none" value="Save & Close" style="float: right">


                    </div>

            </div>
            </form>
        </div>
    </div>

    <!-- Loader Modal -->
    <div class="modal" id="loader_modal" role="dialog">
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

    <!-- Product Upload Loader Modal -->
    <div class="modal" id="product_upload_loader" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-body">
          <h3 style="text-align:center;">Please wait</h3>
          <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>

          <div class="alert alert-primary export-alert-bulk d-none"  role="alert">
            <i class="fa fa-spinner fa-spin"></i>
            <b>Data is updating... </b>
          </div>

          <div class="alert alert-success export-alert-success-bulk d-none"  role="alert">
            <i class=" fa fa-check "></i>
            <b>Data Updated Successfully !!!</b>
          </div>

          <div class="alert alert-primary export-alert-another-user-bulk d-none"  role="alert">
            <i class="  fa fa-spinner fa-spin"></i>
            <b>Data is updating by another user! Please wait... </b>
          </div>
        </div>
      </div>
    </div>
    </div>
@endsection


@section('javascript')
<script>
$(function(e){
    var id = "{{$id}}";
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $('.temp-incomplete-table').DataTable({
        oLanguage: {
        sProcessing: '<img src="{{ asset('public/uploads/gif/waiting.gif') }}">'
        },
        "sPaginationType": "listbox",
         processing: false,
        "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
        searching: true,
        ordering: false,
        serverSide: true,
        bInfo: true,
        paging: true,
        dom: 'lrtip',
        lengthMenu: [ 100, 200, 300, 400,],
        scrollX: true,
        scrollY : '90vh',
        scrollCollapse: true,
        ajax: {
            beforeSend: function(){
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                  });
                $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
                $("#loader_modal").modal('show');
            },
            url:"{!! url('get-temp-product-data') !!}",
            data: function(data) { data.id = id } ,
            method: "post",
            },
        columns: [
            { data: 'action', name: 'action'},
            { data: 'product_ref_no', name: 'product_ref_no' },
            { data: 'system_code', name: 'system_code' },
            { data: 'supplier', name: 'supplier' },
            { data: 'supplier_description', name: 'supplier_description' },
            { data: 'short_desc', name: 'short_desc' },
            { data: 'weight', name: 'weight' },
            { data: 'primary_category', name: 'primary_category' },
            { data: 'category_id', name: 'category_id' },
            { data: 'type_id', name: 'type_id' },
            { data: 'type_2_id', name: 'type_2_id' },
            { data: 'type_3_id', name: 'type_3_id' },
            { data: 'brand_id', name: 'brand_id' },
            { data: 'product_temprature_c', name: 'product_temprature_c' },
            { data: 'buying_unit', name: 'buying_unit' },
            { data: 'selling_unit', name: 'selling_unit' },
            { data: 'stock_unit', name: 'stock_unit' },
            { data: 'order_qty_per_piece', name: 'order_qty_per_piece' },
            { data: 'min_stock', name: 'min_stock' },
            { data: 'm_o_q', name: 'm_o_q' },
            { data: 'supplier_packing_unit', name: 'supplier_packing_unit' },
            { data: 'billed_unit', name: 'billed_unit' },
            { data: 'unit_conversion_rate', name: 'unit_conversion_rate' },
            { data: 'p_s_r', name: 'p_s_r' },
            { data: 'buying_price', name: 'buying_price' },
            { data: 'gross_weight', name: 'gross_weight' },
            { data: 'freight', name: 'freight' },
            { data: 'landing', name: 'landing' },
            { data: 'import_tax_actual', name: 'import_tax_actual' },
            { data: 'extra_cost', name: 'extra_cost' },
            { data: 'extra_tax', name: 'extra_tax' },
            { data: 'lead_time', name: 'lead_time' },
            { data: 'product_notes', name: 'product_notes' },
            { data: 'vat', name: 'vat' },

            @foreach($customerCategory as $cust)
                { data: '{{$cust->title}}', name: '{{$cust->title}}' },
            @endforeach
        ],
        initComplete: function () {
          // Enable THEAD scroll bars
            $('.dataTables_scrollHead').css('overflow', 'auto');
            $('.dataTables_scrollHead').on('scroll', function () {
            $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
          });
        },
        drawCallback: function(){
            $('#loader_modal').modal('hide');
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
            if (isConfirm)
            {
                $.ajax({
                    method: "get",
                    url: "{{url('discard-temp-data')}}",
                    data: {
                        id:"{{ $id }}"
                    },
                    beforeSend:function(){
                      $('#loader_modal').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                       $("#loader_modal").modal('show');
                    },
                    success: function (data)
                    {
                        location.reload();
                    }
                });
                // window.location.href = "{{route('discard-temp-data', $id)}}";
            }
            else
            {
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
        var pId = $(this).parents('tr').attr('id');
        var new_value = $("option:selected", this).val();
        console.log(pId,new_value,$(this).attr('name'));
        saveTempProdData(pId, $(this).attr('name'), $(this).val());
    });

    $(document).on('click', '#check_all', function () {
        if(this.checked == true)
        {
            $('.check_temp').prop('checked', true);
            $('.check_temp').parents('tr').addClass('selected');
            var cb_length = $( ".check_temp:checked" ).length;
            if(cb_length > 0)
            {
              $('#discardSelected').show();

            }
        }
        else
        {
            $('.check_temp').prop('checked', false);
            $('#discardSelected').hide();

            $('.check_temp').parents('tr').removeClass('selected');
        }
    });

    $(document).on('click', '.check_temp', function () {
      if(this.checked == true)
      {
        $('#discardSelected').show();

        $(this).parents('tr').addClass('selected');
      }
      else
      {
        var cb_length = $( ".check_temp:checked" ).length;
        $(this).parents('tr').removeClass('selected');
        if(cb_length == 0)
        {
            $('#discardSelected').hide();
        }
      }
    });

    $(document).on('click', '#discardSelected', function () {
        var selected_products = [];
        $("input.check_temp:checked").each(function () {
            selected_products.push($(this).val());
        });

        swal({
            title: "Alert!",
            text: "Are you sure you want to delete the selected entries?",
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
                $.ajax({
                    method: "get",
                    url: "{{url('delete-selected-temp')}}",
                    data: {
                        prod_ids: selected_products
                    },
                    beforeSend:function(){
                      $('#loader_modal').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                       $("#loader_modal").modal('show');
                    },
                    success: function (data)
                    {
                        if (data == "success")
                        {
                            $('.temp-incomplete-table').DataTable().ajax.reload();
                            // setTimeout(function(){
                            //   window.location.reload();
                            // }, 500);
                            $('#discardSelected').hide();

                        }
                        $("#loader_modal").modal('hide');
                    }
                });
            }
            else {
                swal("Cancelled", "", "error");
            }
        });
    });

    $(document).on('click', '#moveToIncompleteSelected', function () {
        var selected_product = [];
        $("input.check_temp:checked").each(function () {
            selected_product.push($(this).val());
        });
        if(selected_product.length == 0)
        {
            swal('Please Select Products Using Checkbox To Move!');
            return;
        }

        swal({
            title: "Are you sure you want to move the selected entries to Inventory?",
            text: " Selected entries will be saved in complete/incomplete depending on the entered data",
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
                /*var timeout;*/
                $.ajax({
                    method: "get",
                    url: "{{url('move-selected-temp-incomplete')}}",
                    data: {temp_id: selected_product},
                    beforeSend: function(){
                        /*$('#loader_modal').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                        $("#loader_modal").modal('show');
                        timeout = setTimeout(function(){
                            var alertMsg = "<p style='color:red;''>Please be paitent this process will take some time .....</p>";
                            $('#msg').html(alertMsg);
                        }, 5000);*/
                        $('.export-alert-success-bulk').addClass('d-none');
                        $('.export-alert-bulk').addClass('d-none');
                        $('.export-alert-another-user-bulk').addClass('d-none');
                        $('#product_upload_loader').modal({
                          backdrop: 'static',
                          keyboard: false
                        });
                        $("#product_upload_loader").data('bs.modal')._config.backdrop = 'static';
                        $('#product_upload_loader').modal('show');
                        $("#moveToIncompleteSelected").addClass('disabled');
                    },
                    success: function (data) {
                        $('#discardSelected').hide();

                        /*clearTimeout(timeout);*/
                        /*if (data.success == true)
                        {
                            $("#product_upload_loader").modal('hide');
                            $('.temp-incomplete-table').DataTable().ajax.reload();
                            swal('Product Moved to complete/incomplete depending on the entered data Successfully');
                        }
                        else if (data.success == false)
                        {
                            $('html, body').animate({
                                scrollTop: $("body").offset().top
                            }, 900);
                            $('.errormsgDiv').show();
                            $('#errormsg').html(data.errormsg);
                            $("#product_upload_loader").modal('hide');
                            $('.temp-incomplete-table').DataTable().ajax.reload();
                        }
                        else if (data.ref_error == true)
                        {
                          swal('Product Not Found With Ref# '+data.ref_no);
                          $("#product_upload_loader").modal('hide');
                          $('.temp-incomplete-table').DataTable().ajax.reload();
                        }
                        else if (data.duplicate_error == true)
                        {
                          swal('Product With Same Description Already Exist ('+data.short_desc+')');
                          $("#product_upload_loader").modal('hide');
                        }*/
                    },
                    error: function (request, status, error) {
                        $('#discardSelected').hide();
                        $("#product_upload_loader").modal('hide');
                        $("#moveToIncompleteSelected").removeClass('disabled');
                        swal('Error Occured Contact Administrator!');
                    }
                });
            }
            else
            {
                swal("Cancelled", "", "error");
            }
        });
    });

    $(document).on('click','#moveToIncompleteSelected',function(e){

        e.preventDefault();
        var selected_product = [];
        $("input.check_temp:checked").each(function () {
            selected_product.push($(this).val());
        });
        if(selected_product.length == 0)
        {
            return false;
        }

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
          url: "{{ route('move-supplier-bulk-product-job-status') }}",
          method: 'post',
          beforeSend:function(){

          },
          success:function(data){
            if(data.status == 1)
            {
              $('.export-alert-success-bulk').addClass('d-none');
              $('.export-alert-bulk').removeClass('d-none');
              checkStatusForSupplierProductsImport();
            }
            else if(data.status == 2)
            {
              $('.export-alert-another-user-bulk').removeClass('d-none');
              $('.export-alert-bulk').addClass('d-none');
              $("#product_upload_loader").modal('hide');
              $("#moveToIncompleteSelected").removeClass('disabled');
              checkStatusForSupplierProductsImport();
            }

          },
          error: function(request, status, error){
            $("#product_upload_loader").modal('hide');
          }
        });
    });

    function checkStatusForSupplierProductsImport()
    {
        $.ajax({
            method:"get",
            url:"{{route('recursive-export-status-move-supplier-bulk-products')}}",
            success:function(data){
                if(data.status == 1)
                {
                  $('.export-alert-bulk').removeClass('d-none');
                  setTimeout(
                  function(){
                    console.log("Calling Function Again");
                    checkStatusForSupplierProductsImport();
                  }, 5000);
                }
                else if(data.status == 0)
                {
                  $('.export-alert-success-bulk').removeClass('d-none');
                  $('.export-alert-bulk').addClass('d-none');
                  $('.export-alert-another-user-bulk').addClass('d-none');
                  $('.link-of-temp-products').removeClass('d-none');
                  $('.temp-incomplete-table').DataTable().ajax.reload();
                  $("#product_upload_loader").modal('hide');
                  $("#moveToIncompleteSelected").removeClass('disabled');

                  if(data.error_msgs == 0 && data.error_msgs == '0')
                  {
                    swal('Product Moved to complete/incomplete depending on the entered data Successfully');
                    setTimeout(function(){
                      window.location.reload();
                    }, 200);
                  }
                  if(data.exception != null && data.exception != '' && data.exception != '<ol></ol>')
                  {
                    $('#errormsg').html(data.exception);
                    $('.errormsgDiv').removeClass('d-none');
                    // $('.errormsgDiv').show();
                  }
                }
                else if(data.status == 2)
                {
                  $('.export-alert-success-bulk').addClass('d-none');
                  $('.export-alert-bulk').addClass('d-none');
                  $('.export-alert-another-user-bulk').addClass('d-none');
                  $("#moveToIncompleteSelected").removeClass('disabled');
                  toastr.error('Error!', 'Something went wrong. Please Try Again' ,{"positionClass": "toast-bottom-right"});
                  $('.temp-incomplete-table').DataTable().ajax.reload();
                  $("#product_upload_loader").modal('hide');
                }
            }
        });
    }

    $(document).on('click', '.closeErrorDiv', function (){
        $('.errormsgDiv').hide();
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

    $(document).on('keypress keyup focusout', '.fieldFocus', function(e){
        var fieldvalue = $(this).prev().data('fieldvalue');
        if (e.keyCode === 27 && $(this).hasClass('active')) {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.val(fieldvalue);
          thisPointer.prev().removeClass('d-none');
        }
        if((e.keyCode === 13 || e.which === 0 ) && $(this).hasClass('active'))
        {
            if(fieldvalue == $(this).val()){
                return;
            }
            var pId = $(this).parents('tr').attr('id');
            if($(this).val() !== '' && $(this).hasClass('active'))
            {
              var new_value = $(this).val();
              $(this).removeClass('active');
              $(this).addClass('d-none');
              $(this).prev().removeClass('d-none');
              $(this).prev().html(new_value);
              $(this).prev().css("color", "");
              saveTempProdData(pId, $(this).attr('name'), $(this).val());
              $(this).prev().data('fieldvalue', new_value);
            }
        }
    });

    $(document).on('keypress keyup focusout', '.fieldFocusFp', function(e){
        var fieldvalue = $(this).prev().data('fieldvalue');
        var indexval = $(this).prev().data('indexval');
        if (e.keyCode === 27 && $(this).hasClass('active')) {
          var thisPointer = $(this);
          thisPointer.addClass('d-none');
          thisPointer.val(fieldvalue);
          thisPointer.prev().removeClass('d-none');
        }
        if((e.keyCode === 13 || e.which === 0 ) && $(this).hasClass('active'))
        {
            if(fieldvalue == $(this).val())
            {
                return;
            }
            var pId = $(this).parents('tr').attr('id');
            var indexval = $(this).prev().data('indexval');
            if($(this).val() !== '' && $(this).hasClass('active'))
            {
              var new_value = $(this).val();
              $(this).removeClass('active');
              $(this).addClass('d-none');
              $(this).prev().removeClass('d-none');
              $(this).prev().html(new_value);
              $(this).prev().css("color", "");
              saveTempProdDataFp(pId, $(this).attr('name'), $(this).val(), indexval);
              $(this).prev().data('fieldvalue', new_value);
            }
        }
    });

    // this function is only for fixed prices
    function saveTempProdDataFp(prod_detail_id,field_name,field_value,index_value){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('save-temp-products-data-fp') }}",
        dataType: 'json',
        data: 'prod_detail_id='+prod_detail_id+'&'+'field_name='+field_name+'&'+'field_value='+field_value+'&'+'index_value='+index_value,
        beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.completed == 1)
          {
            toastr.success('Success!', 'Information updated successfully. Temp Product marked as completed.',{"positionClass": "toast-bottom-right"});
            $('.temp-incomplete-table').DataTable().ajax.reload();
            // setTimeout(function(){
              // window.location.reload();
            // }, 200);
          }
          else
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
          }

          if(data.reload == 1)
          {
            $('.temp-incomplete-table').DataTable().ajax.reload();
            // setTimeout(function(){
            //   window.location.reload();
            // }, 200);
          }
        },

      });
    }

    function saveTempProdData(prod_detail_id,field_name,field_value){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",
        url: "{{ url('save-temp-products-data') }}",
        dataType: 'json',
        data: 'prod_detail_id='+prod_detail_id+'&'+field_name+'='+field_value,
        beforeSend: function(){
            $('#loader_modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
        },
        success: function(data)
        {
          $("#loader_modal").modal('hide');
          if(data.completed == 1)
          {
            toastr.success('Success!', 'Information updated successfully. Temp Product marked as completed.',{"positionClass": "toast-bottom-right"});
            $('.temp-incomplete-table').DataTable().ajax.reload();
            // setTimeout(function(){
            //   window.location.reload();
            // }, 200);
          }

          else if(data.completed == 2)
          {
            toastr.error('Error!', 'Product Description is already exist.',{"positionClass": "toast-bottom-right"});
          }
          else
          {
            toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
            // setTimeout(function(){
            //   window.location.reload();
            // }, 200);
          }

          if(data.reload == 1)
          {
            $('.temp-incomplete-table').DataTable().ajax.reload();
            // setTimeout(function(){
            //   window.location.reload();
            // }, 200);
          }
        },

      });
    }

    $(document).on('click', '.save-btn', function(e){
        if($('.turngreen').hasClass('btn-outline-danger'))
        {
            swal('Please Select items in the Dropdown');
            return false;
        }
        e.preventDefault();
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
        });
        $.ajax({
            url: "{{ url('save-selected-temp-bulk') }}",
            method: 'post',
            data: $('#form_id').serialize(),
            success: function(result){
                if(result.success == true)
                {
                  window.location.reload();
                }
                else if (result.duplicate_error == true)
                {
                  swal('Product With Same Description Already Exist ('+result.short_desc+')');
                }
                else if (result.ref_error == true)
                {
                  swal('Product Not Found With Ref# ('+result.ref_no+')');
                }
            },
            error: function (request, status, error) {

            }
        });
    });

    @if(Session::has('uploadProductMsg'))
      toastr.success('Success!', "{{ Session::get('uploadProductMsg') }}",{"positionClass": "toast-bottom-right"});
      @php
       Session()->forget('uploadProductMsg');
      @endphp
    @endif
});
</script>
@endsection
