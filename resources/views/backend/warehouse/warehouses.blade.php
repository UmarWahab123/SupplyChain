@extends('backend.layouts.layout')

@section('title','Warehouse Management | Supply Chain')

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
          <li class="breadcrumb-item active">Warehouse</li>
      </ol>
  </div>
</div>

@if( (Auth::user()->role_id != 9 || Auth::user()->role_id != 1) && @$ecommerceconfig_status == 0 )
  @php
    $hide_pricing_columns = ', visible : false';
  @endphp
@endif

{{-- Content Start from here --}}
 <div class="row mb-0">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold">WAREHOUSE</h4>
      <div class="mb-0">
        <a class="btn button-st"  data-toggle="modal" data-target="#addWarehouseModal">
          Add Warehouse
        </a>
      </div>
    </div>
  </div>
</div> 
<!--  Warehouse Modal Start Here -->
<div class="modal fade" id="addWarehouseModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Warehouse</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-warehouse-form']) !!}
            <div class="form-group">
              {!! Form::text('title', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Title']) !!}
            </div>
            <div class="form-group">
              {!! Form::text('location_code', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Location Code']) !!}
            </div>

            <div class="form-check d-flex flex-row">
            <input type="checkbox" name="is_bonded" class="form-check-input is_bonded_warehouse" id="is_bonded" value="0">
            <input type="hidden" name="is_bonded_warehouse" class="set_is_bonded">
            <label class="form-check-label" for="is_bonded">Is Bonded</label>
          </div>
            
            {{--<div class="form-group mb-4 pb-1">
              {!! Form::email('email', $value = null, ['class' => 'font-weight-bold form-control-lg form-control', 'placeholder' => 'Email']) !!}
            </div>

            <div class="form-group mb-4 pb-1">
                {!! Form::select('is_default', ['' => 'Select as Default','1'=>'YES','0'=>'NO'], null, ['class' => 'font-weight-bold form-control-lg form-control', 'id' => 'is_default']) !!}
            </div>--}}
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

<!-- Warehouse Modal End Here -->

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      <div class="table-responsive">
        <table class="table entriestable table-bordered table-warehouses text-center">
          <thead>
            <tr>
              <th>Actions</th>
              <th>Title</th>
              <th>Location Code</th>
              <th width="20%">Is Bonded</th>
              <th>Zipcode</th>
              <th>Default Shipping</th>
              <th>Preference Zipcodes</th>
              <th>Created At</th>
              <th>Updated At</th>
            </tr>
          </thead>
        </table>
      </div>  
    </div>
    
  </div>
</div>

<div class="row mt-4">
  <div class="col-md-12 title-col">
    <div class="d-sm-flex justify-content-between">
      <h4 class="text-uppercase fontbold mb-0">WAREHOUSE CONFIGURATION</h4>
      @if(Auth::user()->role_id == 8)
        <div class="mb-0">
          <a href="#" class="btn button-st" data-toggle="modal" data-target="#addSettingModal">ADD Setting</a>
        </div>
      @endif
    </div>
  </div>
</div> 

<div class="row entriestable-row mt-2">
  <div class="col-12">
    <div class="entriesbg bg-white custompadding customborder">
      @if(@$page_settings)
       
        <ul style="list-style:none" class="p-0">
          @php
            $settings = unserialize(@$page_settings->print_prefrences);
          @endphp
          @foreach($settings as $item)
            <li>
              <div class="form-check custom-checkbox custom-control">
                <input @if($item['status']==1) checked @endif value="{{$item['slug']}}" class="form-check-input global-access" type="checkbox" id="id_{{$item['slug']}}" />
                <label class="form-check-label" for="id_{{$item['slug']}}">{{$item['title']}}</label>
              </div>
            </li>
          @endforeach
        </ul>
     
        <div class="mt-2">
          <button class="btn btn-primary save-btn-quot-config">Save</button>
        </div>
      @endif
    </div>
  </div>
</div>

</div>
<!--  Content End Here -->
<!--  Setting Modal Start Here -->
  <div class="modal fade" id="addSettingModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Add Setting</h3>
          <div class="mt-5">
          {!! Form::open(['method' => 'POST', 'class' => 'add-setting-form']) !!}
            <div class="form-group">
              <input type="text" name="slug" class="font-weight-bold form-control-lg form-control" placeholder="Enter Slug" autocomplete="off">
            </div>

            <div class="form-group">
              <input type="text" name="title" class="font-weight-bold form-control-lg form-control" placeholder="Enter Title" autocomplete="off">
            </div>
            
            <div class="form-submit">
              <input type="submit" value="add" class="btn btn-bg save-setting-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div> 
        </div>
      </div>
    </div>
  </div>

  {{-- Modal for showing binded users of warehouse --}}
  <div class="modal fade" id="warehouse-users-modal">
    <div class="modal-dialog modal-dialog-centered parcelpop modal-lg">
      <div class="modal-content">   
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div> 
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">List of the users bind with <span class="w-name" style="font-weight: bold;"></span> warehouse</h3>
          <div class="mt-5" id="dynamic-users">
          
          </div>
          <form id="change-user-warehouse-form" class="change-user-warehouse-form">
            <div class="mt-3 row">
              <p style="color: red;">Note: In order to suspend a <span class="w-name" style="font-weight: bold;"></span> warehouse you must assign these user(s) a new warehouse.</p>
              <div class="col-md-4"></div>
              <input type="hidden" name="users_ids" id="users_ids" value="">
              <input type="hidden" name="old_w_id" id="old_w_id" value="">
              <div class="col-md-4">
                <select name="new_warehouse" id="new_warehouse" class="select-common form-control new_warehouse" required="">
                  
                </select>
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-primary change_warehouse" id="change_warehouse">Change Warehouse</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

    // to make fields double click editable
  $(document).on("dblclick",".inputDoubleClick",function(){
    $(this).addClass('d-none');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('active');
    $(this).next().focus();
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });

  $(document).on("focus", ".datepicker", function(){
    $(this).datetimepicker({
      timepicker:false,
      format:'Y-m-d'
    });
  });

  var table2 = $('.table-warehouses').DataTable({
    "sPaginationType": "listbox",
    processing: false,
    // "language": {
    // processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span> '},
    ordering: false,
    serverSide: true,
    scrollX: true,
    scrollY : '90vh',
    scrollCollapse: true,
    "columnDefs": [
      { className: "dt-body-left", "targets": [ 1,2,3,4 ] },
      { className: "dt-body-right", "targets": [] }
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
     url:"{!! route('get-all-warehouse') !!}", 
   },
    columns: [
      { data: 'action', name: 'action' },
      { data: 'title', name: 'title' },
      { data: 'code', name: 'code' },
      { data: 'is_bonded', name: 'is_bonded', width: '20%' },
      { data: 'default_zipcode', name: 'default_zipcode'{{@$hide_pricing_columns}}},
      { data: 'default_shipping', name: 'default_shipping'{{@$hide_pricing_columns}}},
      { data: 'associated_zip_codes', name: 'associated_zip_codes'{{@$hide_pricing_columns}}},
      { data: 'created_at', name: 'created_at' },
      { data: 'updated_at', name: 'updated_at' }
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

  $(document).on('click', '.save-btn', function(e){
    e.preventDefault();

    if($('.is_bonded_warehouse').prop("checked") == true)
    {
      $('.set_is_bonded').val(1);
    }
    else
    {
      $('.set_is_bonded').val(0);
    }

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-warehouse') }}",
      method: 'post',
      data: $('.add-warehouse-form').serialize(),
      beforeSend: function(){
        $('.save-btn').val('Please wait...');
        $('.save-btn').addClass('disabled');
        $('.save-btn').attr('disabled', true);
      },

      success: function(result)
      {
        if(result.success == false)
        {
          $('select[name="is_default"]').after('<span class="invalid-feedback" role="alert"><strong>'+result.message+'</strong>');
          $('select[name="is_default"]').addClass('is-invalid');
        }
        else
        {
          $('.save-btn').val('add');
          $('.save-btn').attr('disabled', true);
          $('.save-btn').removeAttr('disabled');
          if(result.success === true)
          {
            $('.modal').modal('hide');
            toastr.success('Success!', 'Warehouse added successfully',{"positionClass": "toast-bottom-right"});
            $('.add-warehouse-form')[0].reset();
            $('.table-warehouses').DataTable().ajax.reload();
          }
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

  $(document).on('click', '.ResetdefaultIcon', function(){
    var id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to Reset Default of  this Warehouse?",
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
        $.ajax({
          method:"get",
          dataType:"json",
          data:{id:id,type:'warehouse'},
          url:"{{ route('reset-default') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.error == false)
            {
              toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
              setTimeout(function(){
                window.location.reload();
              }, 2000);
            }
          }
        });
      } 
      else
      {
        swal("Cancelled", "", "error");
      }
    });  
  });

  $(document).on('click', '.MakedefaultIcon', function(){
    var id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to Make Default this Warehouse?",
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
            data:{id:id,type:'warehouse'},
            url:"{{ route('set-default') }}",
            beforeSend:function(){
              $('#loader_modal').modal({
                  backdrop: 'static',
                  keyboard: false
                });
              $("#loader_modal").modal('show');
            },
            success:function(data){
              $("#loader_modal").modal('hide');
              if(data.error == false)
              {
                toastr.success('Success!', data.successmsg ,{"positionClass": "toast-bottom-right"});
                setTimeout(function(){
                  window.location.reload();
                }, 2000);
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

  //warehouse edit
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

    if (e.keyCode === 27 && $(this).hasClass('active')) {
      var fieldvalue = $(this).prev().data('fieldvalue');
      var thisPointer = $(this);
      thisPointer.addClass('d-none');
      thisPointer.val(fieldvalue);
      thisPointer.removeClass('active');
      thisPointer.prev().removeClass('d-none');
    }

    if( (e.keyCode === 13 || e.which === 0) && $(this).val().length > 0 && $(this).hasClass('active'))
    {  
      if($(this).val().length < 1)
      {
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
          thisPointer.removeClass('active');
          thisPointer.prev().removeClass('d-none');
          $(this).prev().html(fieldvalue);
        }
        else
        {
          var id = $(this).data('id');
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
      url: "{{ url('admin/save-warehouse-data') }}",
      dataType: 'json',
      data: 'id='+id+'&'+field_name+'='+field_value,
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
        $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
        if(data.success == true)
        {

          toastr.success('Success!', 'Information updated successfully.',{"positionClass": "toast-bottom-right"});
        }
      },

    });
  }

  $(document).on('click', '.suspend-warehouse', function(e){
    var warehouse_id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to suspend this warehouse?",
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
        $.ajax({
          method:"get",
          data: 'warehouse_id='+warehouse_id,
          url:"{{ route('suspend-selected-warehouse') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.ecom_enabled_warhouse ==  true)
            {
              swal({ html:true, title:'Alert !!!', text:'<b>This warehouse is Enabled for Ecommerce, if you want to suspend this warehouse please change the Ecommerce warehouse first !!!</b>'});
              return false;
            }
            if(data.success ==  false)
            {
              $('.w-name').html(data.w_name);
              $('#dynamic-users').html(data.html_string);
              $('#new_warehouse').html(data.warehouses_str);
              $('#users_ids').val(data.users_array);
              $('#old_w_id').val(data.w_id);
              $('#warehouse-users-modal').modal('show');
            }
            if(data.success ==  true)
            {
              toastr.success('Success!', 'Warehouse disabled successfully',{"positionClass": "toast-bottom-right"});
              $('.table-warehouses').DataTable().ajax.reload();
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

  $(document).on('click', '.enable-warehouse', function(e){
    var warehouse_id = $(this).data('id');
    swal({
      title: "Alert!",
      text: "Are you sure you want to activate this warehouse?",
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
        $.ajax({
          method:"get",
          data: 'warehouse_id='+warehouse_id,
          url:"{{ route('activate-selected-warehouse') }}",
          beforeSend:function(){
            $('#loader_modal').modal({
              backdrop: 'static',
              keyboard: false
            });
            $("#loader_modal").data('bs.modal')._config.backdrop = 'static';
            $("#loader_modal").modal('show');
          },
          success:function(data){
            $("#loader_modal").modal('hide');
            if(data.success ==  true)
            {
              toastr.success('Success!', 'Warehouse activated successfully',{"positionClass": "toast-bottom-right"});
              $('.table-warehouses').DataTable().ajax.reload();
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

  $(document).on('submit', '.change-user-warehouse-form', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('change-users-warehouse') }}",
      method: 'post',
      data: $('.change-user-warehouse-form').serialize(),
      beforeSend: function(){
        $('.change_warehouse').html('Please wait...');
        $('.change_warehouse').addClass('disabled');
        $('.change_warehouse').attr('disabled', true);
      },
      success: function(result){
        $('.change_warehouse').html('Change Warehouse');
        $('.change_warehouse').attr('disabled', true);
        $('.change_warehouse').removeClass('disabled');
        $('.change_warehouse').removeAttr('disabled');
        if(result.success === true)
        {
          $('#warehouse-users-modal').modal('hide');
          toastr.success('Success!', 'New warehouse assigned successfully, also warehouse disabled successfully',{"positionClass": "toast-bottom-right"});
          $('.table-warehouses').DataTable().ajax.reload();
          $('.change-user-warehouse-form')[0].reset();
        }
      },
      error: function (request, status, error) {
        $('.change_warehouse').html('Change Warehouse');
        $('.change_warehouse').removeClass('disabled');
        $('.change_warehouse').removeAttr('disabled');
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

  $(document).on('change',".is_bonded",function(){
    var is_bonded = $(this).val();
    var warehouse_id = $(this).data('id');
    $.ajax({
      url:"{{route('update-warehouse')}}",
      method:"get",
      dataType:"json",
      data:{is_bonded:is_bonded,warehouse_id:warehouse_id},
      success:function(data){
        if(data.success == true)
        {
          toastr.success('Success!', 'Warehouse Updated successfully !!!' ,{"positionClass": "toast-bottom-right"});

          $('.table-warehouses').DataTable().ajax.reload();
        }
      },
      error:function()
      {
        alert('Error');
      }
    });
  });

  $(document).on('submit', '.add-setting-form', function(e){
    e.preventDefault();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('add-warehouse-setting') }}",
      method: 'post',
      data: $('.add-setting-form').serialize(),
      beforeSend: function(){
        $('.save-setting-btn').val('Please wait...');
        $('.save-setting-btn').addClass('disabled');
        $('.save-setting-btn').attr('disabled', true);
      },
      success: function(result){
        $('.save-setting-btn').val('add');
        $('.save-setting-btn').attr('disabled', true);
        $('.save-setting-btn').removeAttr('disabled');
        if(result.success === true){
          $('.modal').modal('hide');
          toastr.success('Success!', 'Setting Added Successfully',{"positionClass": "toast-bottom-right"});
          $('.add-setting-form')[0].reset();
          setTimeout(function(){
            window.location.reload();
          }, 150);
        }
      },
      error: function (request, status, error) {
        $('.save-setting-btn').val('add');
        $('.save-setting-btn').removeClass('disabled');
        $('.save-setting-btn').removeAttr('disabled');
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

  $('.save-btn-quot-config').on('click', function() {
    var menus = [];
    var menu_stat = [];
    $.each($('.global-access:not(:checked), .global-access:checked'), function() {
      menus.push($(this).val());
      if($(this).prop("checked") == true)
      {
        menu_stat.push(1);
      }
      else if($(this).prop("checked") == false)
      {
        menu_stat.push(0);
      }
    });

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
      }
    });
    $.ajax({
      url: "{{ route('update-warehouse-config') }}",
      method: 'get',
      data: {
        menus: menus,
        menu_stat: menu_stat
      },
      beforeSend: function() {
        $('.save-btn-quot-config').text('Please wait...');
        $('.save-btn-quot-config').attr('disabled', true);
      },
      success: function(result) {
        $('.save-btn-quot-config').text('Save');
        $('.save-btn-quot-config').removeAttr('disabled');
        if (result.success === true) 
        {
          toastr.success('Success!', 'Page Settings Updated Successfully', {
              "positionClass": "toast-bottom-right"
          });
          setTimeout(() => {
              window.location.reload();
          }, 1000);
        }
      },
    });
  });

  $(document).on('keyup', function(e) {
    if (e.keyCode === 27) { // esc
      if ($('.inputDoubleClick').hasClass('d-none')) {
        $('.inputDoubleClick').removeClass('d-none');
        $('.inputDoubleClick').next().addClass('d-none');
      }
    }
  });

</script>
@stop

