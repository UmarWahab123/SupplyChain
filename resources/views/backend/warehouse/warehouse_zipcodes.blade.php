@extends('backend.layouts.layout')

@section('title','Users Management | Supply Chain')

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
          <li class="breadcrumb-item"><a href="{{route('show-warehouses')}}">Warehouse</a></li>
          <li class="breadcrumb-item active">Warehouse Zipcodes</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
    <h4 class="maintitle">{{$warehouse->warehouse_title}} Zipcodes</h4>
  </div>    
  <div class="col-md-4 text-right title-right-col">
    <a class="btn" href data-toggle="modal" data-target="#addPurchasingModal">
      Add Zipcode
    </a>
  </div>
</div>


<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">

      <div class="table-responsive" id="sticky-anchor">
        <table class="table entriestable table-bordered table-warehouse text-center">
          <thead id="sticky">
            <tr>
              <th>Action</th>
              <th>Name</th>
              <th>Zipcode</th>
              <th>Shipping Charges</th>
              <th>Enable Free Shipment 
                  <br>
                <div class="d-flex pl-4 justify-content-center mt-2">
                <div class="mr-5">All</div>
                <div><input type="checkbox" class="enable_free_shipment_for_all" name="enable_free_shipment_for_all" placeholder="Enable Free Shipment For All" {{$checked}} /></div>
                <div class="pl-4"><input type="number" class="maximum_amount_for_all fieldFocusShipmentForAll" data-id="{{$w_id}}" name="maximum_amount_for_all" {{$disabled}} value="{{$value}}" data-fieldvalue="{{$value}}" /> </div>
                </div>
              </th>
            </tr>
          </thead>

        </table>
      </div>
    </div>

  </div>
</div>

</div>
<!--  Content End Here -->

<!--  Purchasing Modal Start Here -->
<div class="modal" id="addPurchasingModal">
  <div class="modal-dialog modal-lg modal-dialog-centered parcelpop">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body text-center">
        <h3 class="text-capitalize fontmed">Add Zipcode</h3>
        <div class="mt-2">

          {!! Form::open(['method' => 'POST', 'class' => 'add-zipcodes-form']) !!}

          <div class="form-row">
            <div class="form-group col-6 input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                </div>
              </div>
              {!! Form::text('name', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Name']) !!}
            </div>
            {!! Form::hidden('warehouse_id', $value = $warehouse->id) !!}
            <div class="form-group col-6 input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                </div>
              </div>
              {!! Form::text('zipcode', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Zipcode']) !!}
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-6 input-group">
              <div class="input-group-prepend">
                <div class="input-group-text">
                  <i class="fa fa-asterisk" style="color: red;font-size: 5px;"></i>
                </div>
              </div>
              {!! Form::text('shipping_charges', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Shipping Charges']) !!}
            </div>
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

<!-- Purchasing Modal End Here -->

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

    $(document).on("dblclick",".inputDoubleClick",function(){
      $(this).addClass('d-none');
      $(this).next().removeClass('d-none');
      $(this).next().addClass('active');
      $(this).next().focus();
      $($.fn.dataTable.tables(true)).DataTable()
      .columns.adjust();
    });

    var warehouse_id = "{{$w_id}}";
    var full_path = $('#site_url').val()+'/';

    $(document).on("focus", ".datepicker", function(){
      $(this).datetimepicker({
        timepicker:false,
        format:'Y-m-d'});
    });

    var table2 = $('.table-warehouse').DataTable({
      "sPaginationType": "listbox",
     processing: false,
     // "language": {
     //  processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
      ordering: false,
      lengthMenu:[100,200,300,400],
      serverSide: true,
      "columnDefs": 
      [
      { "width": "20%", "targets": [0,1,2,3,4] }
      ],

      ajax: {
        beforeSend: function(){
        $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
        $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
        $("#loader_modal").modal('show');
      },
        url: "{!! route('get-warehousezipcodes') !!}",
        data: function(data) {data.id = warehouse_id } ,
      },
      scrollX:true,
      scrollY : '90vh',
      scrollCollapse: true,
      columns: [
      { data: 'action', name: 'action' },
      { data: 'name', name: 'name' },
      { data: 'zipcode', name: 'zipcode' },
      { data: 'shipping_charges', name: 'shipping_charges' },
      { data: 'free_shipment', name: 'free_shipment' },
      ],
      initComplete: function () {
        $('.dataTables_scrollHead').css('overflow', 'auto');

        $('.dataTables_scrollHead').on('scroll', function () {
          $('.dataTables_scrollBody').scrollLeft($(this).scrollLeft());
        });
      },
    drawCallback: function(){
      $('#loader_modal').modal('hide');
    }
    });

    $('.dataTables_filter input').unbind();
    $('.dataTables_filter input').bind('keyup', function(e) {
      if(e.keyCode == 13) {
  // alert();
  table2.search($(this).val()).draw();
}
});

    $(document).on('keyup', '.form-control', function(){
      $(this).removeClass('is-invalid');
      $(this).next().remove();
    });

    $(document).on('change','#company',function(){
      var val = $(this).children("option:selected").val();

      if(val == 1)  //company have id 1 in DB
      {
        $('#sales_warehouse').removeClass('d-none');
      }
      else
      {
        $('#sales_warehouse').addClass('d-none');
      }

    });

    $(document).on('click', '.save-btn', function(e){
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        url: "{{ route('add-zipcodes-form') }}",
        method: 'post',
        data: $('.add-zipcodes-form').serialize(),
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $("#loader_modal").modal('show');
          $('.save-btn').val('Please wait...');
          $('.save-btn').addClass('disabled');
          $('.save-btn').attr('disabled', true);
        },
        success: function(result){
          $("#loader_modal").modal('hide');
          $('.save-btn').val('add');
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
            // alert(result.success);
            // return false;
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'User added successfully',{"positionClass": "toast-bottom-right"});
              $('.add-zipcodes-form')[0].reset();
              setTimeout(function(){
                $('.table-warehouse').DataTable().ajax.reload();
              }, 300);

            }else if (result.success === false) {
              toastr.error('Alert', result.message ,{"positionClass": "toast-bottom-right"});

            }


          },
          error: function (request, status, error) {
            $("#loader_modal").modal('hide');
            $('.save-btn').val('add');
            $('.save-btn').removeClass('disabled');
            $('.save-btn').removeAttr('disabled');
            $('.form-control').removeClass('is-invalid');
            $('.form-control').next().remove();
            json = $.parseJSON(request.responseText);
            $.each(json.errors, function(key, value){
              $('input[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('input[name="'+key+'"]').addClass('is-invalid');

              $('select[name="'+key+'"]').after('<span class="invalid-feedback" role="alert"><strong>'+value+'</strong>');
              $('select[name="'+key+'"]').addClass('is-invalid');
            });

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


    }, 300);
  });

  $(document).on('click', '.deleteWarehouseZipcode', function(e){

    var id = $(this).data('id');

    swal({
      title: "Are you sure?",
      text: "You want to delete this record ?",
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
          url: "{{ route('delete-warehousezipcde') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');            
          },
          success: function(response){
            $("#loader_modal").modal('hide');
            if(response.success === true){
              toastr.success('Success!', 'Product Deleted Successfully.',{"positionClass": "toast-bottom-right"});
              $('.table-warehouse').DataTable().ajax.reload();
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

  var id = 0;

  $(document).on('keypress keyup focusout', '.fieldFocus', function(e){
    id = $(this).data('id');
    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');

      thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }

    var fieldvalue = $(this).prev().data('fieldvalue');
    var new_value = $(this).val();

    if( (e.keyCode === 13 || e.which === 0) && $(this).hasClass('active')){

      if($(this).val().length < 1)
      {
        return false;
      }
      else if(fieldvalue == new_value)
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
      }
      else
      {
        var thisPointer = $(this);
        thisPointer.addClass('d-none');

        thisPointer.removeClass('active');
        thisPointer.prev().removeClass('d-none');
        if(new_value != '')
        {
          $(this).prev().removeData('fieldvalue');
          $(this).prev().data('fieldvalue', new_value);
          $(this).attr('value', new_value);
          $(this).prev().html(new_value);
        }

        saveWarehouseData(id,thisPointer.attr('name'), thisPointer.val());
      }

    }
  });
  $(document).on('keypress keyup focusout','.fieldFocusShipment',function(e){
    var id = $(this).data('id');
    var thisPointer = $(this);
    if( (e.keyCode === 13 || e.which === 0) )
    {
      var value = thisPointer.val();
      if(value == '')
      {
        value = 0;
      }
      saveWarehouseData(id,thisPointer.attr('name'), value);
    }
  });
  function saveWarehouseData(id,field_name,field_value)
  {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('admin/save-warehousezipcode-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+field_name+'='+field_value,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        $('.table-warehouse').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
        if(data.success == true){
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }else{
          toastr.error('Alert!', data.message ,{"positionClass": "toast-bottom-right"});
        }
      },

    });
  }

  $(document).on('keypress keyup focusout','.fieldFocusShipmentForAll',function(e){
    var id = $(this).data('id');
    var thisPointer = $(this);
    var fieldvalue  = $(this).data('fieldvalue');
    if( (e.keyCode === 13 || e.which === 0) )
    {
      var value = thisPointer.val();

      if(fieldvalue == value)
      {
        return false;
      }
      else
      {
        if(value == '')
        {
          value = 0;
        }
        $(this).removeData('fieldvalue');
        $(this).data('fieldvalue', value);
        saveWarehouseDataForAllRegions(id,thisPointer.attr('name'), value);
      }
    }
  });
  function saveWarehouseDataForAllRegions(id,field_name,field_value)
  {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      method: "post",
      url: "{{ url('admin/save-warehousezipcode-data-for-all-regions') }}",
      dataType: 'json',
      data: 'id='+id+'&'+field_name+'='+field_value,
      beforeSend: function(){
        $('#loader_modal').modal({
          backdrop: 'static',
          keyboard: false
        });
        $('#loader_modal').modal('show');
      },
      success: function(data){
        $('#loader_modal').modal('hide');
        $('.table-warehouse').DataTable().ajax.reload();
        $($.fn.dataTable.tables(true)).DataTable()
        .columns.adjust();
        if(data.success == true){
          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }else{
          toastr.error('Alert!', data.message ,{"positionClass": "toast-bottom-right"});
        }
      },

    });
  }

  $(document).on('click','.enable_free_shipment',function(){
    var thisPointer = $(this);
    var id = $(this).data('id');
    if($('.enable_free_shipment_for_all').prop('checked'))
    {
      // return ;
    }
    if($(this).prop("checked") == true)
    {
      $('#maximum_amount_'+id).removeAttr('disabled');
      saveWarehouseData(id,thisPointer.attr('name'), 1);
    }
    else if($(this).prop("checked") == false)
    {
      $('#maximum_amount_'+id).attr('disabled','disabled');
      saveWarehouseData(id,thisPointer.attr('name'), 0);
    }
  });

  $(document).on('click','.enable_free_shipment_for_all',function(){
    var thisPointer = $(this);
    if($(this).prop("checked") == true)
    {
      $('.maximum_amount_for_all').removeAttr('disabled');
    }
    else if($(this).prop("checked") == false)
    {
      $('.maximum_amount_for_all').attr('disabled','disabled');
      var maximum_amount_for_all = $('.fieldFocusShipmentForAll').val();

      if(maximum_amount_for_all > 0)
      {
        var id = "{{$w_id}}";
        var field_name = 'maximum_amount_for_all';
        var field_value = 'clear';
        swal({
          title: "Are you sure?",
          text: "You want to clear free shipment for all regions ?",
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes!",
          cancelButtonText: "Cancel",
          closeOnConfirm: true,
          closeOnCancel: false
        },
        function (isConfirm) {
          if (isConfirm) {
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
              }
            });
            $.ajax({
              method: "post",
              url: "{{ url('admin/save-warehousezipcode-data-for-all-regions') }}",
              dataType: 'json',
              data: 'id='+id+'&'+field_name+'='+field_value,
              beforeSend: function(){
                $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
                $('#loader_modal').modal('show');
              },
              success: function(data){
                $('#loader_modal').modal('hide');
                $('.table-warehouse').DataTable().ajax.reload();
                $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
                if(data.success == true){
                  toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
                }else{
                  toastr.error('Alert!', data.message ,{"positionClass": "toast-bottom-right"});
                }
              },

            });
          }
          else {

            if(thisPointer.prop("checked") == true)
            {
              thisPointer.prop("checked", false);
              $('.maximum_amount_for_all').attr('disabled','disabled');
            }
            else if(thisPointer.prop("checked") == false)
            {
              $('.maximum_amount_for_all').removeAttr('disabled');
              thisPointer.prop("checked", true);
            }
            swal("Cancelled", "", "error");
          }
        });
      }
    }
  });

</script>
@stop

