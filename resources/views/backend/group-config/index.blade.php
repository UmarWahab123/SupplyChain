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
          <li class="breadcrumb-item active">Groups Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}

<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-sm-flex justify-content-between">
            <h4 class="text-uppercase fontbold">Groups Configuration <span class="h5">(Page Confiugration) </span></h4>
           @if(Auth::user()->role_id == 8)
            <div class="mb-0">
                <a href="#" class="btn button-st" data-toggle="modal" data-target="#addSettingModal">ADD Setting</a>
            </div>
            @endif
            
        </div>
    </div>
</div>

<div class="row entriestable-row mt-2">
    <div class="col-6 d-none">
        <div class="bg-white card">
            <h5 class="card-header">Products Table Columns <small></small></h5>
            <div class="card-body">
                <div class="ml-2">

                </div>
                <div class="float-right">
                    <button class="btn  btn-primary save-btn ">Save</button>
                </div>
            </div>

        </div>

    </div>

<!-- 
    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Products <small>(Configuration)</small></h5>
            <div class="card-body">
                <div class="ml-2">
                    <ul style="list-style:none">
                       
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input class="form-check-input global-access" type="checkbox" id="allow_edit" />
                                <label class="form-check-label" for="allow_edit">Allow Reference Code Edit</label>
                            </div>
                        </li>

                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-quot-config">Save</button>
                </div>
            </div>

        </div>
    </div> -->
    @if($warehouse->count() > 0)
     <div class="col-6">
        @if($page_settings)
        <div class="bg-white card">
        <h5 class="card-header">Enable These Columns For Bonded Warehouses</h5>
        <div class="card-body">
        <div class="ml-2">
        <ul style="list-style:none">
        @php
        $settings = unserialize($page_settings->print_prefrences);
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
        </div>
        <div class=" float-right">
        <button class="btn btn-primary save-btn-quot-config">Save</button>
        </div>
        </div>
        </div>
        @endif
    </div>
    @else
        <div class="col-6">
            <div class="card bg-white p-4">
            <h5>No bonded warehouse found. Please <a href="{{url('admin/show-warehouses')}}" style="color: red;font-style: italic;">Click Here</a> to add bonded warehouse.</h5>
            </div>
        </div>
    @endif
</div>

</div>

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


<!--  Content End Here -->


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
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
            },
            success: function(result) {
                $('.save-menus-btn').text('Save');
                $('.save-menus-btn').removeAttr('disabled');
                if (result.success === true) {
                    toastr.success('Success!', 'Qutation Confiugration updated successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    // setTimeout(() => {
                    // 	window.location.reload();
                    // }, 1500);
                } else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
            }
        });
    });
    // $('.save-print-btn').on('click',function(){
    // 	var columns=[1,3,9,10,13,16];
    // 	$.each($('.printable-checkbox:checked'), function(){
    // 		columns.push($(this).val());		
    // 	});
    // 	console.log(columns);
    // 	$.ajax({
    // 			{{--url: "{{ route('qutotaion-config-print') }}",--}}				
    // 			method: 'get',
    // 			data: {columns:columns},
    // 			beforeSend: function() {
    // 				$('.save-menus-btn').text('Please wait...');
    // 				$('.save-menus-btn').attr('disabled', true);
    // 			},
    // 			success: function(result) {
    // 				$('.save-menus-btn').text('Save');
    // 				$('.save-menus-btn').removeAttr('disabled');
    // 				if (result.success === true) {
    // 					toastr.success('Success!', 'Qutation Confiugration updated successfully', {
    // 						"positionClass": "toast-bottom-right"
    // 					});
    // 					// setTimeout(() => {
    // 					// 	window.location.reload();
    // 					// }, 1500);
    // 				}
    // 			},
    // 		});
    // });


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

        // if(menus.length == 0)
        // {
        //     toastr.error('Error!', 'Please check atleast one checkbox first!!!' ,{"positionClass": "toast-bottom-right"});
        //     return false;
        // }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('update-groups-config') }}",
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
                if (result.success === true) {
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

    $(document).on('submit', '.add-setting-form', function(e){
      e.preventDefault();
      $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
          }
      });
       $.ajax({
          url: "{{ route('add-groups-setting') }}",
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

</script>
@endsection
