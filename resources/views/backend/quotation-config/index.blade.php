@extends('backend.layouts.layout')
@section('title','Quotattion Config')
@section('content')


{{-- Content Start from here --}}

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
          <li class="breadcrumb-item active">Quotation Configuration</li>
      </ol>
  </div>
</div>

<div class="row mb-0">
    <div class="col-md-12 title-col">
        <div class="d-sm-flex justify-content-between">
            <h4 class="text-uppercase fontbold">Draft Quotation/ Quotation/ Draft Invoice/ Invoice <span class="h5">(Detail Page Confiugration) </span></h4>

            @if(Auth::user()->role_id == 1)
            <div class="mb-0">
                <a href="#" class="btn button-st" data-toggle="modal" data-target="#addSettingModal">ADD Setting</a>
            </div>
            @endif
        </div>
    </div>
</div>
<div class="row entriestable-row mt-2">
    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Table Columns <small></small></h5>
            <div class="card-body">
                <div class="ml-2">
                    @if(!$quotationColumns->isEmpty())
                    <ul id="sortable" style="list-style:none">
                        Orderable Columns
                        <li data-id="0" class="d-none">
                            <div class="card card-primary pl-3 col-5 mt-1 ">
                                <div class="card-body py-1">
                                    <input value="0" class="form-check-input orderable-checkbox" checked type="checkbox" id="id-0" />
                                    <label class="form-check-label" for="id-0">Action</label>
                                </div>
                            </div>
                        </li>

                        @foreach($quotationColumns as $qc)
                        <li data-id="{{$qc->column_id}}"  >
                            <div class="card card-primary pl-3 col-5 mt-1" style="cursor:grabbing">
                                <div class="card-body py-1">
                                    <input @if(!in_array($qc->column_id,$showedColumns)) checked @endif value="{{$qc->column_id}}" class="form-check-input orderable-checkbox" type="checkbox" id="id-{{$qc->column_id}}" />
                                    <label class="form-check-label" for="id-{{$qc->column_id}}">@if($qc->slug!=null){{$global_terminologies[$qc->slug]}} @else {{$qc->column_name}} @endif</label>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <ul style="list-style:none">
                        Non Orderable Columns
                        {{-- <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(10,$showedColumns)) checked @endif value="10" class="form-check-input orderable-checkbox" type="checkbox" id="id-10" />
                                    <label class="form-check-label" for="id-10">Available {{$global_terminologies['qty']}}</label>
                                </div>
                            </div>
                        </li> --}}
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(14,$showedColumns)) checked @endif value="14" class="form-check-input orderable-checkbox" type="checkbox" id="id-14" />
                                    <label class="form-check-label" for="id-14">#{{$global_terminologies['qty']}} Ordered</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(15,$showedColumns)) checked @endif value="15" class="form-check-input orderable-checkbox" type="checkbox" id="id-15" />
                                    <label class="form-check-label" for="id-15">#{{$global_terminologies['qty']}} Sent</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(16,$showedColumns)) checked @endif value="16" class="form-check-input orderable-checkbox" type="checkbox" id="id-16" />
                                    <label class="form-check-label" for="id-16">#{{$global_terminologies['pieces']}} Ordered</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(17,$showedColumns)) checked @endif value="17" class="form-check-input orderable-checkbox" type="checkbox" id="id-17" />
                                    <label class="form-check-label" for="id-17">#{{$global_terminologies['pieces']}} Sent</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(18,$showedColumns)) checked @endif value="18" class="form-check-input orderable-checkbox" type="checkbox" id="id-18" />
                                    <label class="form-check-label" for="id-18">{{$global_terminologies['reference_price']}}</label>
                                </div>
                            </div>
                        </li>
						<li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(19,$showedColumns)) checked @endif value="19" class="form-check-input orderable-checkbox" type="checkbox" id="id-19" />
                                    <label class="form-check-label" for="id-19">{{$global_terminologies['default_price_type']}}</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(20,$showedColumns)) checked @endif value="20" class="form-check-input orderable-checkbox" type="checkbox" id="id-20" />
                                    <label class="form-check-label" for="id-20">{{$global_terminologies['default_price_type_wo_vat']}}</label>
                                </div>
                            </div>
                        </li>
                          <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(21,$showedColumns)) checked @endif value="21" class="form-check-input orderable-checkbox" type="checkbox" id="id-21" />
                                    <label class="form-check-label" for="id-21">Price Date</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(22,$showedColumns)) checked @endif value="22" class="form-check-input orderable-checkbox" type="checkbox" id="id-22" />
                                    <label class="form-check-label" for="id-22">Discount</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(23,$showedColumns)) checked @endif value="23" class="form-check-input orderable-checkbox" type="checkbox" id="id-23" />
                                    <label class="form-check-label" for="id-23">Unit Price <br>(After Discount)</label>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(24,$showedColumns)) checked @endif value="24" class="form-check-input orderable-checkbox" type="checkbox" id="id-24" />
                                    <label class="form-check-label" for="id-24">Vat</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(25,$showedColumns)) checked @endif value="25" class="form-check-input orderable-checkbox" type="checkbox" id="id-25" />
                                    <label class="form-check-label" for="id-25">{{$global_terminologies['unit_price_vat']}}</label>
                                </div>
                            </div>
                        </li>

                      

                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(26,$showedColumns)) checked @endif value="26" class="form-check-input orderable-checkbox" type="checkbox" id="id-26" />
                                    <label class="form-check-label" for="id-26">{{$global_terminologies['total_amount_inc_vat']}}</label>
                                </div>
                            </div>
                        </li>
                        {{-- <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(23,$showedColumns)) checked @endif value="23" class="form-check-input orderable-checkbox" type="checkbox" id="id-23" />
                                    <label class="form-check-label" for="id-23">PO QTY</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(24,$showedColumns)) checked @endif value="24" class="form-check-input orderable-checkbox" type="checkbox" id="id-24" />
                                    <label class="form-check-label" for="id-24">PO No</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(25,$showedColumns)) checked @endif value="25" class="form-check-input orderable-checkbox" type="checkbox" id="id-25" />
                                    <label class="form-check-label" for="id-25">Customer Last Price</label>
                                </div>
                            </div>
                        </li> --}}
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(27,$showedColumns)) checked @endif value="27" class="form-check-input orderable-checkbox" type="checkbox" id="id-27" />
                                    <label class="form-check-label" for="id-27">Restaurant Price</label>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(28,$showedColumns)) checked @endif value="28" class="form-check-input orderable-checkbox" type="checkbox" id="id-28" />
                                    <label class="form-check-label" for="id-28">{{$global_terminologies['note_two']}}</label>
                                </div>
                            </div>
                        </li>
                          <li>
                            <div class="card card-primary pl-3 col-5 mt-1">
                                <div class="card-body py-1">
                                    <input @if(!in_array(29,$showedColumns)) checked @endif value="29" class="form-check-input orderable-checkbox" type="checkbox" id="id-29" />
                                    <label class="form-check-label" for="id-29">{{$global_terminologies['total_price_after_discount_without_vat']}}</label>
                                </div>
                            </div>
                        </li>
                    </ul>

                </div>
                <div class="float-right">
                    <button class="btn  btn-primary save-btn ">Save</button>
                </div>
            </div>

        </div>

    </div>

    <div class="col-6">
        @if($page_settings)
        @if($page_settings->print_prefrences != null)
        <div class="bg-white card">
            <h5 class="card-header">Page Settings</h5>
            <div class="card-body">
                <div class="ml-2">
                    <ul style="list-style:none">
                        @php
                            $settings = unserialize($page_settings->print_prefrences);
                        @endphp
                        @foreach($settings as $item)
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input @if($item['status']==1) checked @endif value="{{$item['slug']}}" class="form-check-input global-access @if($item['slug']=='target_ship_date') target-ship-date @endif" type="checkbox" id="id_{{$item['slug']}}" />
                                <label class="form-check-label  " for="id_{{$item['slug']}}">{{$item['title']}}</label>
                            </div>
                            
                        </li>
                        @endforeach
                        @if(!empty($target_ship_date))
                        <li>
                            <div class="form-check custom-checkbox custom-control">
                                <input @if($target_ship_date['target_ship_date']==1)checked @endif id="target_ship_date"  value="target_ship_date" class="form-check-input target-ship-date" name="target_ship_date" type="checkbox" />
                                <label class="form-check-label" for="target_ship_date">Show Target Ship Date</label>
                            </div>
                            <ul style="list-style:none" class="target-ship-date-required-list d-none">
                                <li>
                                    <div class="form-check custom-checkbox custom-control">
                                        <input @if($target_ship_date['target_ship_date_required']==1)checked @endif id="target_ship_date_required"  value="target_ship_date_required" class="form-check-input  target-ship-date-required" name="target_ship_date_required" type="checkbox" />
                                        <label class="form-check-label" for="target_ship_date_required">Required</label>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary save-btn-quot-config">Save</button>
                </div>
            </div>
        </div>
        @endif
        @endif
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

@endsection



@section('javascript')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>

    var value = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18,19,20,21,22,23,24,25,26,27,28,29];
    var index = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11, 12, 13];
    var orderAbleValue = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,10,11, 12, 13];
    var update = false;
    console.log(index);
    
    $(function() {
        var listId = null;
        $("#sortable").sortable({
            update: function(event, ui) {
                update = true;
                index = [];
                value = [];
                orderAbleValue = [];
                $('#sortable li').each(function(i, el) {
                    value.push($(this).data("id"));
                    index.push(i);
                    orderAbleValue.push($(this).data("id"));
                });
                value.push(14, 15, 16, 17, 18,19,20,21,22,23,24,25,26,27,28,29);
                console.log('value '+value);
                console.log('index '+index);
                console.log('Column Id '+orderAbleValue);
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
                $('.save-btn').text('Please wait...');
                $('.save-btn').attr('disabled', true);
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
            },
            success: function(result) {
                $('.save-btn').text('Save');
                $('.save-btn').removeAttr('disabled');
                if (result.success === true) {
                    toastr.success('Success!', 'Qutation Confiugration updated successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                    	window.location.reload();
                    }, 1500);
                }else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
            }
        });
    });

    $('.target-ship-date').on('change',function(){
        if($(this).prop('checked')==true)
        {
            $('.target-ship-date-required-list').removeClass('d-none');
        }
        else
        { 
            $('.target-ship-date-required-list').addClass('d-none');
            $('#target_ship_date_required').prop('checked',false);

        }
    });
    $(document).ready(function(){
        if($('.target-ship-date').prop('checked')==true)
        {
            $('.target-ship-date-required-list').removeClass('d-none');
        }
        else
        {
            $('.target-ship-date-required-list').addClass('d-none');
        }
    });
    $('.save-btn-quot-config').on('click', function() {
    var target_ship_date=[];
    var target_ship_date_status=[];
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
        if($('.target-ship-date').prop('checked')==true)
        {
            target_ship_date.push($('.target-ship-date').val());
            target_ship_date_status.push(1);
            if($('.target-ship-date-required').prop('checked')==true)
            {
                target_ship_date.push($('.target-ship-date-required').val());
                target_ship_date_status.push(1);
            }
            else
            {
                target_ship_date.push($('.target-ship-date-required').val());
                target_ship_date_status.push(0);
            }
        }
        else
        {
            target_ship_date.push($('.target-ship-date').val());
            target_ship_date_status.push(0);
            target_ship_date.push($('.target-ship-date-required').val());
            target_ship_date_status.push(0);
        } 
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
            url: "{{ route('update-quote-config') }}",
            method: 'get',
            data: {
                menus: menus,
                menu_stat: menu_stat,
                target_ship_date:target_ship_date,
                target_ship_date_status:target_ship_date_status
            },
            beforeSend: function() {
                $('.save-btn-quot-config').text('Please wait...');
                $('.save-btn-quot-config').attr('disabled', true);
                $('#loader_modal').modal({
                    backdrop: 'static',
                    keyboard: false
                    });
                $("#loader_modal").modal('show');
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
                }else{
                    $("#loader_modal").modal('hide');
                }
            },
            error: function(request, status, error){
                $("#loader_modal").modal('hide');
            }
        });
    });

    $('.save-btn-print-config').on('click', function() {
        var menus = [];

        $.each($('.global-access-print:checked'), function() {
            menus.push($(this).val());
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{ route('update-print-config') }}",
            method: 'get',
            data: {
                menus: menus
            },
            beforeSend: function() {
                $('.save-btn-quot-config').text('Please wait...');
                $('.save-btn-quot-config').attr('disabled', true);
            },
            success: function(result) {
                $('.save-btn-quot-config').text('Save');
                $('.save-btn-quot-config').removeAttr('disabled');
                if (result.success === true) {
                    toastr.success('Success!', 'Configuration updated Successfully', {
                        "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(request, status, error){
                
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
          url: "{{ route('add-page-setting') }}",
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
