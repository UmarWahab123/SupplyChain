@extends('backend.layouts.layout')
@section('title','Quotattion Config')
@section('content')

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
          <li class="breadcrumb-item active">Product Detail Page Configuration</li>
      </ol>
  </div>
</div>

<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-flex justify-content-between">
            <h4 class="text-uppercase fontbold">Product Detail Configuration <span class="h5">(Page Confiugration) </span></h4>
            {{-- @if(Auth::user()->role_id == 8) --}}
            <div class="mb-0 ">
                <a href="#" class="btn button-st d-inline" data-toggle="modal" data-target="#addSettingModal">ADD Setting</a>
                <a href="#" class="btn button-st d-inline" data-toggle="modal" data-target="#deleteSettingModal">Delete Setting</a>
            </div>
            {{-- @endif --}}
        </div>
    </div>
</div>


<div class="row entriestable-row mt-2">
     <div class="col-6">
        @if($product_detail_page)
        <div class="bg-white card h-100">
        <h5 class="card-header">Product Information</h5>
        <div class="card-body">
        <div class="ml-2">
            <ul style="list-style:none">
            @php
            $settings = unserialize($product_detail_page->print_prefrences);
            @endphp
            {{-- {{dd($settings)}} --}}
            @foreach($settings as $item)
            <li>
            <div class="form-check custom-checkbox custom-control">
            <input @if($item['status']==1) checked @endif value="{{$item['slug']}}" class="form-check-input global-access" type="checkbox" id="id_{{$item['slug']}}" />
            <label class="form-check-label" for="id_{{$item['slug']}}">{{$item['title']}}</label>
            </div>
            </li>
            @endforeach
            </ul>
        </div>
        <div class="" style="">
        <button class="btn btn-primary save-btn-prod-detail float-right">Save</button>
        </div>
        </div>
        </div>
        @endif
    </div>

    <div class="col-6">
        @if($product_detail_page_supplier_detail)
        <div class="bg-white card h-100">
        <h5 class="card-header">Supplier Information</h5>
        <div class="card-body">
        <div class="ml-2">
        <ul style="list-style:none">
        @php
        $settings = unserialize($product_detail_page_supplier_detail->print_prefrences);
        @endphp
        @foreach($settings as $item)
        <li>
        <div class="form-check custom-checkbox custom-control">
        <input @if($item['status']==1) checked @endif value="{{$item['slug']}}" class="form-check-input global-access-supplier" type="checkbox" id="id_{{$item['slug']}}" />
        <label class="form-check-label" for="id_{{$item['slug']}}">{{$item['title']}}</label>
        </div>
        </li>
        @endforeach
        </ul>
        </div>
        <div class="" style="">
        <button class="btn btn-primary save-btn-supplier-detail float-right">Save</button>
        </div>
        </div>
        </div>
        @endif
    </div>

</div>
{{-- {{dd($global_terminologies)}} --}}
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
                <select name="title" class="form-control selectpicker">
                    <option value="" selected>Select Column</option>
                    @foreach($global_terminologies as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                    @endforeach

                </select>
            </div>

            <div class="form-group">
                <select name="pages" class="form-control selectpicker">
                    <option value="" selected>Select Section</option>
                    <option value="product_information">Product Information</option>
                    <option value="supplier_information">Supplier Information</option>
                </select>
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

<!--  Delete Modal Start Here -->
<div class="modal fade" id="deleteSettingModal">
    <div class="modal-dialog modal-dialog-centered parcelpop">
      <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
        </div>
        <div class="modal-body text-center">
          <h3 class="text-capitalize fontmed">Delete Setting</h3>
          <div class="mt-5">
          {!! Form::open(['class' => 'delete-setting-form']) !!}

            <div class="form-group">
                <select name="pages" class="form-control" id= "section_select">
                    <option value="" selected>Select Section</option>
                    <option value="0">Product Information</option>
                    <option value="1">Supplier Information</option>
                </select>
            </div>
            <div class="form-group">
                <select name="title" class="form-control" id="configuration_select">
                    <option value="" selected>Select Column</option>
                </select>
            </div>

            <div class="form-submit">
              <input type="submit" value="Delete" class="btn btn-bg delete-setting-btn">
              <input type="reset" value="close" class="btn btn-danger close-btn">
            </div>
          {!! Form::close() !!}
         </div>
        </div>
      </div>
    </div>
  </div>

@endsection



@section('javascript')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    var value = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];
    var index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    var orderAbleValue = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    var update = false;
    console.log(index);
    $(function() {
        var listId = null;
        $("#sortable").sortable({
            update: function(event, ui) {
                update = false;
                index = [];
                value = [];
                orderAbleValue = [];
                $('#sortable li').each(function(i, el) {
                    value.push($(this).data("id"));
                    index.push(i);
                    orderAbleValue.push($(this).data("id"));
                });

                value.push(11, 12, 13, 14, 15, 16, 17, 18, 19);
            },

        });
        $("#sortable").disableSelection();
    });

    $('.save-btn').on('click', function() {
        var columns = [];
        $.each($('.orderable-checkbox'), function() {
            if ($(this).prop("checked") == false) {
                columns.push($(this).val());
            }
        });
        $.ajax({
            url: "{{ route('qutotaion-config-order') }}",
            method: 'get',
            data: {
                columns: columns,
                value: value,
                index: index,
                orderAbleValue: orderAbleValue,
                update: update
            },
            beforeSend: function() {
                $('.save-menus-btn').text('Please wait...');
                $('.save-menus-btn').attr('disabled', true);
            },
            success: function(result) {
                $('.save-menus-btn').text('Save');
                $('.save-menus-btn').removeAttr('disabled');
                if (result.success === true) {
                    toastr.success('Success!', 'Qutation Confiugration updated successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                }
            },
            error: function(request, status, error){
            }
        });
    });


     $('.save-btn-prod-detail').on('click', function() {
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
            url: "{{ route('update-product-detail-config') }}",
            method: 'get',
            data: {
                menus: menus,
                menu_stat: menu_stat
            },
            beforeSend: function() {
                $('.save-btn-prod-detail').text('Please wait...');
                $('.save-btn-prod-detail').attr('disabled', true);
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
            },
            success: function(result) {
                $('.save-btn-prod-detail').text('Save');
                $('.save-btn-prod-detail').removeAttr('disabled');
                if (result.success === true) {
                    toastr.success('Success!', 'Page Settings Updated Successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
            }
        });
    });

    $('.save-btn-supplier-detail').on('click', function() {
        var menus = [];
        var menu_stat = [];

        $.each($('.global-access-supplier:not(:checked), .global-access-supplier:checked'), function() {
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
            url: "{{ route('update-supplier-detail-config') }}",
            method: 'get',
            data: {
                menus: menus,
                menu_stat: menu_stat
            },
            beforeSend: function() {
                $('.save-btn-supplier-detail').text('Please wait...');
                $('.save-btn-supplier-detail').attr('disabled', true);
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
            },
            success: function(result) {
                $('.save-btn-supplier-detail').text('Save');
                $('.save-btn-supplier-detail').removeAttr('disabled');
                if (result.success === true) {
                    toastr.success('Success!', 'Page Settings Updated Successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
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
          url: "{{ route('add-product-detail-config') }}",
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
            } else if(result.success === false) {
                toastr.error('Error!', 'Select a valid section to update !!!',{"positionClass": "toast-bottom-right"});
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

    $(document).on('click','.cat-check',function(){
        var id = $(this).data('id');
        var thisPointer = $(this);
        var title = 'fixed';

        if($(this).prop("checked") == true)
        {
          var catCheck = 1;
        }
        else if($(this).prop("checked") == false)
        {
            var catCheck = 0;
        }
        updateCustomerCategory(thisPointer,thisPointer.attr('name'), thisPointer.val(),catCheck,id,title);
    });

    $(document).on('click','.cat-check-suggest',function(){
        var id = $(this).data('id');
        var thisPointer = $(this);
        var title = 'suggest';
        if($(this).prop("checked") == true)
        {
          var catCheck = 1;
        }
        else if($(this).prop("checked") == false)
        {
            var catCheck = 0;
        }
        updateCustomerCategory(thisPointer,thisPointer.attr('name'), thisPointer.val(),catCheck,id,title);
    });

    function updateCustomerCategory(thisPointer,field_name,field_value,new_select_value,id,title){
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
      });
      $.ajax({
        method: "post",

        url: "{{ route('update-customer-category-config') }}",
        dataType: 'json',
        data: 'id='+id+'&'+field_name+'='+encodeURIComponent(field_value)+'&'+'new_select_value'+'='+encodeURIComponent(new_select_value)+'&'+'title='+title,
        beforeSend: function(){
          $('#loader_modal').modal({
            backdrop: 'static',
            keyboard: false
          });
          $('#loader_modal').modal('show');
        },
        success: function(data)
        {
            if(data.success == true)
            {
              toastr.success('Success!', 'Information Updated Successfully!!!',{"positionClass": "toast-bottom-right"});
            }
            if(data.success == false)
            {
              toastr.error('Sorry!', 'Cannot Update Information!!!',{"positionClass": "toast-bottom-right"});
            }
          $("#loader_modal").modal('hide');
        },

      });
    }

    $(document).on('change', '#section_select', function(e){
       $.ajax({
          url: "{{ route('get_selected_section_config') }}",
          method: 'get',
          data: {value:$(this).val()},
          beforeSend: function(){
            $('#loader_modal').modal('show');
          },
          success: function(result){
            $('#configuration_select').html(result.html);
          $('#loader_modal').modal('hide');
          }
        });
    });

    $(document).on('submit', '.delete-setting-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('delete-product-detail-config') }}",
          method: 'post',
          data: $('.delete-setting-form').serialize(),
          beforeSend: function(){
            $('.delete-setting-btn').val('Please wait...');
            $('.delete-setting-btn').addClass('disabled');
            $('.delete-setting-btn').attr('disabled', true);
          },
          success: function(result){
            $('.delete-setting-btn').val('Delete');
            $('.delete-setting-btn').attr('disabled', true);
            $('.delete-setting-btn').removeAttr('disabled');
            if(result.success === true){
              $('.modal').modal('hide');
              toastr.success('Success!', 'Setting Deleted Successfully',{"positionClass": "toast-bottom-right"});
              $('.delete-setting-form')[0].reset();
              setTimeout(function(){
                window.location.reload();
              }, 150);
            } else if(result.success === false) {
                toastr.error('Error!', 'Select a valid section to update !!!',{"positionClass": "toast-bottom-right"});
            }
          },
          error: function (request, status, error) {
                $('.delete-setting-btn').val('add');
                $('.delete-setting-btn').removeClass('disabled');
                $('.delete-setting-btn').removeAttr('disabled');
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

</script>
@endsection
